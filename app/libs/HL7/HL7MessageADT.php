<?php
App::import( 'Lib',   'HL7Message', 	array('file'=>'HL7Message.php'));
App::import( 'Lib',   'HL7SegmentEVN', 	array('file'=>'HL7SegmentEVN.php'));
App::import( 'Lib',   'HL7SegmentPID', 	array('file'=>'HL7SegmentPID.php'));
App::import( 'Lib',   'HL7SegmentPV1', 	array('file'=>'HL7SegmentPV1.php'));
App::import( 'Lib',   'HL7SegmentGT1', 	array('file'=>'HL7SegmentGT1.php'));
App::import( 'Lib',   'HL7SegmentIN1', 	array('file'=>'HL7SegmentIN1.php'));
App::import( 'Lib',	  'HL7SegmentNK1',	array('file'=>'HL7SegmentNK1.php'));

class HL7MessageADT extends HL7Message {
	const VERSION = '3132a';			// Change this whenever new functionality is added to our ADT message consumption
	const RECORD_TYPE = 'Patient';
	public $evn;	// event
	public $pid;	// patient id
	public $pv1;	// patient visit
	public $gt1;	// array of guarantors
	public $in1;	// array of insurance
	public $nk1;	// next of kin (emergency contact)

	/**
	 * get the next record id that has not been sent
	 *
	 * @param string $receiver
	 * @return number
	 */
	public static function getNextId( $receiver ) {
		$mn = 'Hl7OutgoingMessage';
		$m = ClassRegistry::init( $mn );
		
		// First, look to see if there are any new patients that have yet to have an ADT generated
		$q = 'SELECT p.patient_id FROM patient_demographics AS p WHERE';
		$q .= ' p.patient_id NOT IN';
		$q .= ' ( SELECT hl7.record_id from hl7_outgoing_messages hl7 where hl7.record_type = "' . self::RECORD_TYPE . '" AND hl7.receiving_application = "' . $receiver . '")';
		$q .= ' ORDER BY p.patient_id LIMIT 1;';
		$result = $m->query( $q, $cachequeries = false );
		if( isset( $result[0] ))
			return $result[0]['p']['patient_id'];

		// Then, look for patients whose demographics have been modified since the last ADT was sent
		// Then, look for patients whose guarantor or insurance info records have been modified since the last ADT was sent
		// FIXME: could dispense with the two queries for guarantor and insurance info if we updated patient_demographics.modified_timestamp on any update to subordinate tables (e.g., marital status works like this)
		$q = 'SELECT p.patient_id FROM _table_name_ AS p WHERE';
		$q .= ' p.modified_timestamp > ( SELECT MAX( hl7.event_timestamp ) FROM hl7_outgoing_messages AS hl7';
		$q .= '  WHERE hl7.record_id = p.patient_id';
		$q .= '  AND hl7.record_type = "' . self::RECORD_TYPE . '"';
		$q .= '  AND hl7.receiving_application = "' . $receiver . '"';
		$q .= ' ) ORDER BY p.patient_id LIMIT 1;';
		foreach( array( 'patient_demographics', 'patient_guarantors', 'patient_insurance_info' ) as $table_name ) {
			$q1 	= str_replace( '_table_name_', $table_name, $q );
			$result = $m->query( $q1, $cachequeries = false );
			if( isset( $result[0] ))
				return $result[0]['p']['patient_id'];
		}
		
		// otherwise, nothing to do
		return -1;
	}
	
	/**
	 * An ADT message is always taken to mean a new/updated patient record
	 *
	 * @return string|false		Text describing actions taken
	 */
	public function commitMessage() {
//		if( $this->processing_id != ??? ) ... FIXME: check the processing_id and only commit if it matches the type of environment we are currently

		$story = $this->message_type[0] . ' ' . $this->message_type[1];

		// get the patient added or updated
		$story .= $this->pid->commitSegment();
		$id = $this->pid->patient_id;
		$event_timestamp = $this->pid->event_timestamp;

		// FIXME: do we want to record the consulting or referring doctor in the PV1 segment?

		// add any guarantors
		foreach( $this->gt1 as $gt1 ) {
			$story .= "\n\t" . $gt1->commitSegment( $id );
			if( strcmp( $event_timestamp, $gt1->event_timestamp ) < 0 )
				$event_timestamp = $gt1->event_timestamp;
		}

		// add any insurance
		foreach( $this->in1 as $i => $in1 ) {
			$story .= "\n\t" . $in1->commitSegment( $id, $i + 1 );
			if( strcmp( $event_timestamp, $in1->event_timestamp ) < 0 )
				$event_timestamp = $in1->event_timestamp;
		}		
		
		// add any next of kin
		foreach( $this->nk1 as $k => $nk1 ) {
			$story .= "\n\t" . $nk1->commitSegment( $id );
			if( strcmp( $event_timestamp, $nk1->event_timestamp ) < 0 )
				$event_timestamp = $nk1->event_timestamp;
		}
				
		$this->logIncoming( $id, $story, $event_timestamp, self::VERSION );
		
		// record an outgoing message in log without actually sending it so we won't replicate these changes for outbound production to same receiver
		$this->swapSenderWithReceiver();
		$msg_text = $this->produceMessage( self::VERSION . '-in' );

		return $story . "\n";
	}

	/**
	 * interpret this ADT message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type ADT)
	 * @return false|HL7MessageADT
	 */
	static protected function interpretMessage( $base, $segments ) {
		if( $base->sending_application === 'MacPractice' ) {
			$theclass = get_called_class() . '_MP';
			return $theclass::interpretMessage( $base, $segments );
		} elseif( $base->sending_application === 'Compulink' ) {
			$theclass = get_called_class() . '_CL';
			return $theclass::interpretMessage( $base, $segments );
		}
		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		if( $practice_settings->hl7_engine === 'MDConnection' ) {
			$theclass = get_called_class() . '_MDC';
			return $theclass::interpretMessage( $base, $segments );
		}
		return self::interpretMessageP( $base, $segments, 'HL7SegmentPID' );
	}

	/**
	 * interpret this ADT message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type ADT)
	 * @param string $pid_class			The name of the class to use for the PID segment
	 * @return false|HL7MessageADT
	 */
	static protected function interpretMessageP( $base, $segments, $pid_class ) {
		$myclass = get_called_class();
		$me      = new $myclass();
		foreach( $base as $key => $value ) {
			$me->$key = $value;
		}
		$me->gt1 = $me->in1 = $me->nk1 = array();
		foreach( $segments as $segment ) {
			switch( $segment[0] ) {
				case 'EVN': $me->evn 	= new HL7SegmentEVN( $segment ); break;
				case 'PID': $me->pid 	= new $pid_class( $segment ); break;
				case 'PV1': $me->pv1 	= new HL7SegmentPV1( $segment ); break;
				case 'GT1': $me->gt1[]	= new HL7SegmentGT1( $segment ); break;
				case 'IN1': $me->in1[]	= new HL7SegmentIN1( $segment ); break;
				case 'NK1': $me->nk1[]	= new HL7SegmentNK1( $segment ); break;
			}
		}
		if( !isset( $me->pid ))
			return false;
		return $me;
	}

	/**
	 * create a new HL7MessageADT from data source where key = $patient_id
	 *
	 * @param int $patient_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return HL7Message|false
	 */
	public static function createFromDb( $patient_id, $receiver = null, $processing_id = HL7Message::PT_DEBUGGING ) {
		if( $receiver === 'MacPractice' ) {
			$mpclass = get_called_class() . '_MP';
			return $mpclass::createFromDb( $patient_id, $receiver, $processing_id );
		}
		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		if( $practice_settings->hl7_engine === 'MDConnection' ) {
			$theclass = get_called_class() . '_MDC';
			return $theclass::createFromDb( $patient_id, $receiver, $processing_id );
		}
		return self::createFromDbP( $patient_id, $receiver, $processing_id, 'HL7SegmentPID' );
	}

	/**
	 * create a new HL7MessageADT from data source where key = $patient_id
	 *
	 * @param int $calendar_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @param string $pid_class			The name of the class to use for the PID segment
	 * @return HL7Message|false
	 */
	protected static function createFromDbP( $patient_id, $receiver, $processing_id, $pid_class ) {
		$me = parent::createFromDb( $patient_id, $receiver, $processing_id );
		
		$me->message_type = array( 'ADT' );
		$logs = HL7Message::logFindOutgoing( $patient_id, self::RECORD_TYPE, $receiver );
		if( count( $logs ))	
			$me->message_type[] = 'A08';		// we've sent this patient before to $receiver, so this is an update
		else
			$me->message_type[] = 'A04';		// never sent before, so this looks like a new patient to $receiver

		$me->pid = $pid_class::createFromDb( $patient_id, $receiver, $processing_id );
		if( false === $me->pid )
			return false;

		$me->evn 					= new HL7SegmentEVN();
		$me->evn->event_type		= $me->message_type[1];
		$me->evn->event_timestamp	= $me->pid->event_timestamp;
		$me->evn->recorded_datetime	= new DateTime( $me->pid->event_timestamp );

		$me->pv1 = new HL7SegmentPV1();
		$me->gt1 = HL7SegmentGT1::createFromDb( $patient_id, $receiver, $processing_id );
		$me->in1 = HL7SegmentIN1::createFromDb( $patient_id, $receiver, $processing_id );
		$me->nk1 = HL7SegmentNK1::createFromDb( $patient_id, $receiver, $processing_id );

		return $me;
	}

	/**
	 * return the HL7 message to send
	 *
	 * @return false|string
	 */
	public function produceMessage( $version = self::VERSION ) {
		$msg  = parent::produceMessage( $version );
		$event_timestamp = '1900-01-01';
		foreach( array( 'evn', 'pid', 'pv1' ) as $seg ) {
			if( !is_null( $this->{$seg} )) {
				$msg .= $this->{$seg}->produceSegment( $this ) . self::SEGMENT_DELIMITER;
				if( strcmp( $event_timestamp, $this->{$seg}->event_timestamp ) < 0 )
					$event_timestamp = $this->{$seg}->event_timestamp;
			}
		}
		foreach( $this->gt1 as $seqno => $gt1 ) {
			$msg .= $gt1->produceSegment( $this, $seqno + 1 ) . self::SEGMENT_DELIMITER;
			if( strcmp( $event_timestamp, $gt1->event_timestamp ) < 0 )
				$event_timestamp = $gt1->event_timestamp;
		}
		foreach( $this->in1 as $seqno => $in1 ) {
			$msg .= $in1->produceSegment( $this, $seqno + 1 ) . self::SEGMENT_DELIMITER;
			if( strcmp( $event_timestamp, $in1->event_timestamp ) < 0 )
				$event_timestamp = $in1->event_timestamp;
		}
		foreach( $this->nk1 as $seqno => $nk1 ) {
			$msg .= $nk1->produceSegment( $this, $seqno + 1 ) . self::SEGMENT_DELIMITER;
			if( strcmp( $event_timestamp, $nk1->event_timestamp ) < 0 )
				$event_timestamp = $nk1->event_timestamp;
		}
		$this->logOutgoing( $this->pid->patient_id, self::RECORD_TYPE, $msg, $event_timestamp, $version );
		return $msg;
	}
}

App::import( 'Lib',	  'HL7MessageADT_MP',	array('file'=>'HL7MessageADT_MP.php'));
App::import( 'Lib',   'HL7SegmentPID_MP', array( 'file' => 'HL7SegmentPID_MP.php' ));
App::import( 'Lib',	  'HL7MessageADT_CL',	array('file'=>'HL7MessageADT_CL.php'));
App::import( 'Lib',   'HL7SegmentPID_CL', array( 'file' => 'HL7SegmentPID_CL.php' ));
App::import( 'Lib',	  'HL7MessageADT_MDC',	array( 'file' => 'HL7MessageADT_MDC.php' ));
App::import( 'Lib',   'HL7SegmentPID_MDC',	array( 'file' => 'HL7SegmentPID_MDC.php' ));
