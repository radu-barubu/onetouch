<?php
App::import( 'Lib',   'HL7Message', 	array('file'=>'HL7Message.php'));
App::import( 'Lib',   'HL7MessageNull',	array('file'=>'HL7MessageNull.php'));
App::import( 'Lib',   'HL7SegmentEVN', 	array('file'=>'HL7SegmentEVN.php'));
App::import( 'Lib',   'HL7SegmentPID', 	array('file'=>'HL7SegmentPID.php'));
App::import( 'Lib',   'HL7SegmentPV1', 	array('file'=>'HL7SegmentPV1.php'));
App::import( 'Lib',   'HL7SegmentFT1', 	array('file'=>'HL7SegmentFT1.php'));
App::import( 'Lib',   'HL7SegmentGT1', 	array('file'=>'HL7SegmentGT1.php'));
App::import( 'Lib',   'HL7SegmentIN1', 	array('file'=>'HL7SegmentIN1.php'));

class HL7MessageDFT extends HL7Message {
	const VERSION = '3153d';			// Change this whenever new functionality is added to our DFT message functionality (6-char limit)
	const RECORD_TYPE = 'Encounter';
	public $evn;	// HL7SegmentEVN
	public $pid;	// HL7SegmentPID
	public $pv1;	// HL7SegmentPV1
	public $ft1;	// array of FT1
	public $gt1;	// array of guarantors
	public $in1;	// array of insurance
	
	/**
	 * get the next record id that has not been sent
	 *
	 * @param string $receiver
	 * @return number
	 */
	public static function getNextId( $receiver ) {
		$mn = 'Hl7OutgoingMessage';
		$m = ClassRegistry::init( $mn );
		
		// First, look for encounters that have been unlocked, modified, and relocked/posted since the last DFT was sent
		$q = 'SELECT em.encounter_id FROM encounter_master AS em WHERE';
		$q .= ' ((em.encounter_status = "Closed" AND (em.hl7_status IS NULL OR em.hl7_status <> "2" )) OR em.hl7_status = "1")'; 
		$q .= ' AND em.modified_timestamp > ( SELECT MAX( hl7.event_timestamp ) FROM hl7_outgoing_messages AS hl7';
		$q .= '  WHERE hl7.record_id = em.encounter_id';
		$q .= '  AND hl7.record_type = "' . self::RECORD_TYPE . '"';
		$q .= '  AND hl7.receiving_application = "' . $receiver . '"';
		$q .= ' ) ORDER BY em.encounter_id LIMIT 1;';
		$result = $m->query( $q, $cachequeries = false );
		if( isset( $result[0] ))
			return $result[0]['em']['encounter_id'];
		
		// Then, look for superbills, POCs, plans that have been modified since last DFT was sent
		// FIXME: could dispense with the second query if encounter_master.modified_timestamp was updated when changing superbill or other tables
		$q = 'SELECT em.encounter_id FROM encounter_master AS em, _table_name_ AS x WHERE';
		$q .= ' x.encounter_id = em.encounter_id AND ((em.encounter_status = "Closed" AND (em.hl7_status IS NULL OR em.hl7_status <> "2" )) OR em.hl7_status = "1")'; 
		$q .= ' AND x.modified_timestamp > ( SELECT MAX( hl7.event_timestamp ) FROM hl7_outgoing_messages AS hl7';
		$q .= '  WHERE hl7.record_id = em.encounter_id';
		$q .= '  AND hl7.record_type = "' . self::RECORD_TYPE . '"';
		$q .= '  AND hl7.receiving_application = "' . $receiver . '"';
		$q .= ' ) ORDER BY em.encounter_id LIMIT 1;';
		foreach( array( 
					'encounter_superbill', 
					'encounter_point_of_care', 
					'encounter_plan_labs', 
					'encounter_plan_radiology', 
					'encounter_plan_procedures' 
					) as $table_name ) {
			$q1 	= str_replace( '_table_name_', $table_name, $q );
			$result = $m->query( $q1, $cachequeries = false );
			if( isset( $result[0] ))
				return $result[0]['em']['encounter_id'];
		}
		
		// Then, look to see if there are any new superbills that have yet to have a DFT generated
		$q = 'SELECT em.encounter_id FROM encounter_master AS em, encounter_superbill AS sb WHERE';
		$q .= ' sb.encounter_id = em.encounter_id AND ((em.encounter_status = "Closed" AND (em.hl7_status IS NULL OR em.hl7_status <> "2" )) OR em.hl7_status = "1")'; 
		$q .= ' AND em.encounter_id NOT IN';
		$q .= ' ( SELECT hl7.record_id from hl7_outgoing_messages hl7 where hl7.record_type = "' . self::RECORD_TYPE . '" AND hl7.receiving_application = "' . $receiver . '")';
		$q .= ' ORDER BY em.encounter_id LIMIT 1;';
		$result = $m->query( $q, $cachequeries = false );
		if( isset( $result[0] ))
			return $result[0]['em']['encounter_id'];
		
		// otherwise, nothing to do
		return -1;
	}
	
	/**
	 * create a new HL7MessageDFT from data source where key = $encounter_id
	 *
	 * @param int $encounter_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return HL7Message|false
	 */
	public static function createFromDb( $encounter_id, $receiver = null, $processing_id = HL7Message::PT_DEBUGGING ) {
		if( $receiver === 'MacPractice' ) {
			$mpclass = get_called_class() . '_MP';
			return $mpclass::createFromDb( $encounter_id, $receiver, $processing_id );
		}
		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		if( $practice_settings->hl7_engine === 'MDConnection' ) {
			$theclass = get_called_class() . '_MDC';
			return $theclass::createFromDb( $encounter_id, $receiver, $processing_id );
		}
		return self::createFromDbP( $encounter_id, $receiver, $processing_id, 'HL7SegmentPID', 'HL7SegmentFT1' );
	}

	/**
	 * create a new HL7MessageDFT from data source where key = $encounter_id
	 *
	 * @param int $encounter_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @param string $pid_class			The name of the class to use for the PID segment
	 * @return HL7Message|false
	 */
	protected static function createFromDbP( $encounter_id, $receiver, $processing_id, $pid_class, $ft1_class ) {
		// check if encounter exists and if a superbill has been done
		$mn = 'EncounterMaster';
		$m = ClassRegistry::init( $mn );
		$get = $m->findByEncounterId( $encounter_id );
		if( false === $get )
			return false;		// no such encounter id (yet)
		$event_timestamp = $get[$mn]['modified_timestamp'];
		if( 'Open' == $get[$mn]['encounter_status'] && '1' != $get[$mn]['hl7_status'] )
			return new HL7MessageNull( $encounter_id, self::RECORD_TYPE, $receiver, $event_timestamp, self::VERSION . 'Open' );		// encounter not closed so ignore any superbill for now
		if( 'Closed' == $get[$mn]['encounter_status'] && '2' == $get[$mn]['hl7_status'] )
			return new HL7MessageNull( $encounter_id, self::RECORD_TYPE, $receiver, $event_timestamp, self::VERSION . '!Pst' );		// encounter not closed so ignore any superbill for now
		$m = ClassRegistry::init( 'EncounterSuperbill' );
		$sb = $m->findByEncounterId( $encounter_id );
		if( false === $sb )
			return new HL7MessageNull( $encounter_id, self::RECORD_TYPE, $receiver, $event_timestamp, self::VERSION . 'NoSB' ); 	// encounter that does not have a superbill, so generate no DFT

		// pick up latest timestamp from associated records
		if( $sb['EncounterSuperbill']['modified_timestamp'] > $event_timestamp )
			$event_timestamp = $sb['EncounterSuperbill']['modified_timestamp'];
		foreach( array( 'EncounterPointOfCare', 'EncounterPlanLab', 'EncounterPlanRadiology', 'EncounterPlanProcedure' ) as $mn ) {
			$m = ClassRegistry::init( $mn );
			$x = $m->findAllByEncounterId( $encounter_id );
			if( false !== $x )
				foreach( $x as $y )
					if( $y[$mn]['modified_timestamp'] > $event_timestamp )
						$event_timestamp = $y[$mn]['modified_timestamp'];
		}
 
		$me 				= parent::createFromDb( $encounter_id, $receiver, $processing_id );
		$me->message_type	= array( 'DFT', 'P03' );

		$me->ft1 = $ft1_class::createFromDb( $encounter_id, $receiver, $processing_id );
		if( false === $me->ft1 )
			return false;
		if( !count( $me->ft1 ))
			return new HL7MessageNull( $encounter_id, self::RECORD_TYPE, $receiver, $event_timestamp, self::VERSION . 'None' );		// valid superbill, but apparently nothing for which we can charge

		$me->pid = $pid_class::createFromDb( $me->ft1[0]->patient_id, $receiver, $processing_id );
		if( false === $me->pid )
			return false;

		$me->pv1 = HL7SegmentPV1::createFromDb( $me->ft1[0]->calendar_id, $receiver, $processing_id );

		$me->evn 					= new HL7SegmentEVN();
		$me->evn->event_type		= 'P03';
		$me->evn->event_timestamp	= $event_timestamp;
		$me->evn->recorded_datetime	= new DateTime( $event_timestamp );
		
		// See if this message is a duplicate of previously produced one (e.g., so we don't produce one message on Billable and the same one again on Closed status)
		$logs = HL7Message::logFindOutgoing( $encounter_id, self::RECORD_TYPE, $receiver );
		if( count( $logs )) {
			foreach( $logs as $log ) {
				// skip empty placeholder records
				if( empty( $log['message_text'] ))
					continue;
				// found a non-null message, so see if there have been any changes since then
				$prior = HL7MessageIncoming::createFromMessageText( $log['message_text'] );
				if( $me->equal( $prior ))
					return new HL7MessageNull( $encounter_id, self::RECORD_TYPE, $receiver, $event_timestamp, self::VERSION . 'Same' );
				break;  // not equal, so we need to send $me DFT
			}
		}
		// add on gt1 and in1 segments (not part of equality testing so don't bother until we are sure we are not returning a null message above)
		$me->gt1 = HL7SegmentGT1::createFromDb( $me->ft1[0]->patient_id, $receiver, $processing_id );
		$me->in1 = HL7SegmentIN1::createFromDb( $me->ft1[0]->patient_id, $receiver, $processing_id );
		return $me;
	}
	
	/**
	 * Is this semantically the same as other?
	 * 
	 * @param unknown $other
	 * @return boolean true if the same
	 */
	public function equal( $other ) {
		if( !parent::equal( $other ))
			return false;
		/*if( $other->pid->patient_id != $this->pid->patient_id )
			return false; FIXME: patient_id may not have been looked up...for our current purposes we know the patient is the same, so no worries */
		if( count( $other->ft1 ) != count( $this->ft1 ))
			return false;
		for( $i = 0; $i < count( $other->ft1 ); $i++ ) {
			if( !$this->ft1[$i]->equal( $other->ft1[$i] ))
				return false;
		}
		return true;
	}
	

	/**
	 * interpret this DFT message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type DFT)
	 * @return false|HL7MessageADT
	 */
	static protected function interpretMessage( $base, $segments ) {
		if( $base->sending_application === 'MacPractice' ) {
			$theclass = get_called_class() . '_MP';
			return $theclass::interpretMessage( $base, $segments );
		}
		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		if( $practice_settings->hl7_engine === 'MDConnection' ) {
			$theclass = get_called_class() . '_MDC';
			return $theclass::interpretMessage( $base, $segments );
		}
		return self::interpretMessageP( $base, $segments, 'HL7SegmentPID', 'HL7SegmentFT1' );
	}
		
	/**
	 * interpret this DFT message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type ADT)
	 * @param string $pid_class			The name of the class to use for the PID segment
	 * @param string $ft1_class			The name of the class to use for the FT1 segments
	 * @return false|HL7MessageADT
	 */
	static protected function interpretMessageP( $base, $segments, $pid_class, $ft1_class ) {
		$myclass = get_called_class();
		$me      = new $myclass();
		foreach( $base as $key => $value ) {
			$me->$key = $value;
		}
		$me->ft1 = array();
		foreach( $segments as $segment ) {
			switch( $segment[0] ) {
				case 'EVN': $me->evn 	= new HL7SegmentEVN( $segment ); break;
				case 'PID': $me->pid 	= new $pid_class( $segment ); break;
				case 'PV1': $me->pv1 	= new HL7SegmentPV1( $segment ); break;
				case 'FT1': $me->ft1[]	= new $ft1_class( $segment ); break;
			}
		}
		if( !isset( $me->pid ))
			return false;
		return $me;
	}
			
	/**
	 * return the HL7 message to send
	 *
	 * @return false|string
	 */
	public function produceMessage( $version = self::VERSION ) {
		$msg  = parent::produceMessage( $version );
		$event_timestamp = $this->evn->event_timestamp;
		foreach( array( 'evn', 'pid', 'pv1' ) as $seg )
			$msg .= $this->{$seg}->produceSegment( $this ) . self::SEGMENT_DELIMITER;
		foreach( $this->ft1 as $seqno => $ft1 )
			$msg .= $ft1->produceSegment( $this, $seqno + 1 ) . self::SEGMENT_DELIMITER;
		foreach( $this->gt1 as $seqno => $gt1 )
			$msg .= $gt1->produceSegment( $this, $seqno + 1 ) . self::SEGMENT_DELIMITER;
		foreach( $this->in1 as $seqno => $in1 )
			$msg .= $in1->produceSegment( $this, $seqno + 1 ) . self::SEGMENT_DELIMITER;
		$this->logOutgoing( $this->ft1[0]->encounter_id, self::RECORD_TYPE, $msg, $event_timestamp, $version );
		return $msg;
	}
}

App::import( 'Lib',   'HL7MessageDFT_MP', array( 'file' => 'HL7MessageDFT_MP.php' ));
App::import( 'Lib',   'HL7SegmentPID_MP', array( 'file' => 'HL7SegmentPID_MP.php' ));
App::import( 'Lib',	  'HL7SegmentFT1_MP', array( 'file' => 'HL7SegmentFT1_MP.php' ));
App::import( 'Lib',   'HL7MessageDFT_MDC', array( 'file' => 'HL7MessageDFT_MDC.php' ));
App::import( 'Lib',   'HL7SegmentPID_MDC', array( 'file' => 'HL7SegmentPID_MDC.php' ));
App::import( 'Lib',	  'HL7SegmentFT1_MDC', array( 'file' => 'HL7SegmentFT1_MDC.php' ));
