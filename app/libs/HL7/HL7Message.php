<?php
/**
 * Base class for an HL7 message, see http://www.HL7.org.  A message consists of segments and is used
 * for communicating between cooperating applications.  Messages are either incoming or outgoing and
 * the contents depend on the message type which is modelled as class specialization of this class.
 * There is an implicit translation between the message_text and the program model of its components.
 *
 */
class HL7Message {
	// Our version
	const VERSION = '2893';
	
	// Record type key for hl7_produce, e.g., Encounter, Patient, Appointment
	const RECORD_TYPE = '';

	// Delimiters.  Note all except SEGMENT_DELIMITER may be overridden in MSH header according to HL7 spec.
	const SEGMENT_DELIMITER        = "\r";		// HL7 standard is CR, but code is in place for incoming messages to filter \r\n=>\r and \n=>\r
	public $field_delimiter        = '|';
	public $component_delimiter    = '^';
	public $repeat_delimiter       = '~';
	public $escape_character       = '\\';
	public $subcomponent_delimiter = '&';

	public $message_text; // HL7 message
//	public $segments;     // array of segments, typically as text
	public $message_type; // e.g., [ 'ADT', 'A04' ] (MSH.9)

	public $sending_application;	// MSH. 3
	public $sending_facility;		// MSH. 4
	public $receiving_application;	// MSH. 5
	public $receiving_facility;		// MSH. 6
	public $date_time_of_message;	// MSH. 7
	public $processing_id;			// MSH.11
	public $version_id;				// MSH.12

	// (http://www.hl7.org/special/committees/vocab/v26_appendix_a.pdf)
	const PT_DEBUGGING  = 'D';
	const PT_TRAINING   = 'T';
	const PT_PRODUCTION = 'P';

	/**
	 * record the event depicted in this message, e.g., create/modify a patient in database
	 */
	public function commitMessage() {
//		if( $this->processing_id != ??? ) ... FIX: check the processing_id and only commit if it matches the type of environment we are currently

		// subclasses are responsible for talking to their corresponding data models in their implementation of this method
		return $this->message_type[0]. ' ' . $this->message_type[1] . ' ignored';
	}

	/**
	 * construct from a basic message, i.e., one that has been parsed but only the message header interpreted
	 *
	 * @param  HL7Message $base
	 * @return self       the subclass object
	 */
	static protected function interpretMessage($base, $segments) {
		return $base;
	}

	/**
	 * commit the data in $goal to model $m to a record keyed by $goal fields named in $key_field_names
	 *
	 * @param AppModel $m				the model to update, if a record with same key exists, modify the first one, otherwise create a new one
	 * @param array $goal				e.g., array( $m->name ==> array( 'patient_id' => '27', ...
	 * @param array $key_field_names	e.g., array( 'patient_id', 'last_name', 'first_name' )
	 * @return string					Log of actions
	 */
	public function commitData( $m, $goal, $key_field_names, $order = null ) {
		$mn = $m->name;
		$story = '';

		$key = null;
		foreach( $key_field_names as $fn )
			if( is_string( $goal[$mn][$fn] ))
				$key[$mn . '.' . $fn] = $goal[$mn][$fn];

		$params = array( 'conditions' => $key );
		if( !is_null( $order ))
			$params['order'] = $order;
		$get = $m->find( 'first', $params );
		$is_new = false === $get;
		if( $is_new )
			$m->create();
		else
			$m->id = $get[$mn][$m->primaryKey];

		$set = array( $mn => null );
		foreach( array_keys( $goal[$mn] ) as $fn )
			if( !is_null( $goal[$mn][$fn] ) && ( $is_new || $goal[$mn][$fn] != $get[$mn][$fn] ))
				$set[$mn][$fn] = $goal[$mn][$fn];

		// story of what we found
		if( !$is_new ) {
			$story_delim = '(';
			foreach( $key as $fn => $fv ) {
				$story .= $story_delim . substr( $fn, strlen( $mn ) + 1 ) . '=' . $fv;
				$story_delim = ';';
			}
			$story .= ')';
		}

		// if up-to-date, then done
		if( is_null( $set[$mn] ))
			return $story . ' no changes';

		// otherwise, save changes
		$success = $m->save( $set );

		// story of what changed
		$story .= ( $success === false ? ' failed to save' : ( $is_new ? ' created' : ' updated' ) );
		if( $is_new )
			foreach( array_keys( $set[$mn] ) as $fn )
				$get[$mn][$fn] = '';
		foreach( array_keys( $set[$mn] ) as $fn )
			$story .= ' ' . $fn . '(' . $get[$mn][$fn] . '-->' . $set[$mn][$fn] . ')';
		return $story;
	}

	/**
	 * create a new HL7Message from data source where key = $id
	 *
	 * @param int $id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return HL7Message|false
	 */
	public static function createFromDb( $id, $receiver = null, $processing_id = HL7Message::PT_DEBUGGING ) {
		$myclass					= get_called_class();
		$me							= new $myclass();
		$me->sending_application	= 					// MSH. 3
		$me->sending_facility		= 'ONETOUCHEMR';	// MSH. 4
		$me->receiving_application	= 					// MSH. 5
		$me->receiving_facility		= $receiver;		// MSH. 6
		$me->date_time_of_message	= new DateTime();	// MSH. 7
		$me->processing_id			= $processing_id;	// MSH.11
		$me->version_id				= '2.4';			// MSH.12
	 	return $me;
	}
	
	/**
	 * Is this semantically the same as other?
	 * 
	 * @param unknown $other
	 * @return boolean true if the same
	 */
	public function equal( $other ) {
		if( !is_object( $other ))
			return false;
		if( get_class( $this ) != get_class( $other ))
			return false;
		return true;
	}

	/**
	 * get the next record id that has not been sent
	 *
	 * @param string $receiver
	 * @return number
	 */
	public static function getNextId( $receiver ) {
		$myclass = get_called_class();
		$mn = 'Hl7OutgoingMessage';
		$m = ClassRegistry::init( $mn );
		$get = $m->find( 'first', array( 'conditions' => array( $mn . '.record_type' => $myclass::RECORD_TYPE, $mn . '.receiving_application' => $receiver ), 'order' => array( $mn . '.record_id DESC' )));
		if( false === $get )
			return 1;
		return $get[$mn]['record_id'] + 1;
	}

	/**
	 * return the HL7 message to send
	 *
	 * @return false|string
	 */
	public function produceMessage( $version = self::VERSION ) {
		static $i = 0;
		$fd   = $this->field_delimiter;
		$cd   = $this->component_delimiter;
		$msh  = 'MSH';
		$msh .= $fd . $cd . $this->repeat_delimiter . $this->escape_character . $this->subcomponent_delimiter;
		$msh .= $fd . $this->sending_application;
		$msh .= $fd . $this->sending_facility;
		$msh .= $fd . $this->receiving_application;
		$msh .= $fd . $this->receiving_facility;
		$msh .= $fd . $this->date_time_of_message->format( 'YmdHis');
		$msh .= $fd . 'NO SECURITY';
		$msh .= $fd . $this->message_type[0] . $cd . $this->message_type[1];
		$msh .= $fd . $this->date_time_of_message->format( 'YmdHi' ) . '-' . ++$i;
		$msh .= $fd . $this->processing_id;
		$msh .= $fd . $this->version_id;
		$msh .= str_repeat( $fd, 6 ) . 'UNICODE UTF-8' . str_repeat( $fd, 3 );
		return $msh . self::SEGMENT_DELIMITER;
	}

	/**
	 * persist a record of the outgoing HL7 message
	 *
	 * @param number $record_id
	 * @param string $msg_text
	 */
	protected function logOutgoing( $record_id, $record_type, $msg_text, $event_timestamp = null, $version = self::VERSION ) {
		$this->message_text = $msg_text;
		$mn = 'Hl7OutgoingMessage';
		$m = ClassRegistry::init( $mn );
		$m->create();
		$m->save( array( $mn => array(
				'record_type' 			=> $record_type,
				'record_id'				=> $record_id,
				'message_type'			=> $this->message_type[0],
				'event_type'			=> $this->message_type[1],
				'receiving_application'	=> $this->receiving_application,
				'message_text'			=> $msg_text,
				'event_timestamp'		=> $event_timestamp,
				'version'				=> $version )));
	}
	
	/**
	 * swap sender/receiver fields (e.g., to mimic having sent out a message that came in)
	 */
	public function swapSenderWithReceiver() {
		$temp 							= $this->receiving_application;
		$this->receiving_application 	= $this->sending_application;
		$this->sending_application 		= $temp;
		
		$temp 							= $this->receiving_facility;
		$this->receiving_facility 		= $this->sending_facility;
		$this->sending_facility 		= $temp;
	}
	
	/**
	 * Find all outgoing messages for given $record_id and $record_type
	 * 
	 * @param string $record_id
	 * @param string $record_type
	 * @param string $receiver
	 * @return array of outgoing logs that match
	 */
	public static function logFindOutgoing( $record_id, $record_type, $receiver = null ) {
		$mn = 'Hl7OutgoingMessage';
		$m = ClassRegistry::init( $mn );

		// construct key
		$key = null;
		$key[$mn . '.record_id'] 	= $record_id;
		$key[$mn . '.record_type'] 	= $record_type;
		if( !is_null( $receiver ))
			$key[$mn . '.receiving_application'] = $receiver;
		
		$logs = $m->find( 'all', array( 'conditions' => $key, 'order' => "$mn.outgoing_message_id DESC" ));
		
		$ret = null;
		if( false !== $logs )
			foreach( $logs as $log )
				$ret[] = $log[$mn];
		return $ret;
	}

	/**
	 * persist a record of the incoming HL7 message
	 *
	 * @param number $record_id
	 * @param string $log
	 */
	protected function logIncoming( $record_id, $log, $event_timestamp = null, $version = self::VERSION ) {
		$myclass = get_called_class();
		$mn = 'Hl7IncomingMessage';
		$m = ClassRegistry::init( $mn );
		$m->create();
		$m->save( array( $mn => array(
				'record_type' 			=> $myclass::RECORD_TYPE,
				'record_id'				=> $record_id,
				'message_type'			=> $this->message_type[0],
				'event_type'			=> $this->message_type[1],
				'sending_application'	=> $this->sending_application,
				'message_text'			=> $this->message_text,
				'log'					=> $log,
				'event_timestamp'		=> $event_timestamp,
				'version'				=> $version )));
	}

	/*
	 * SEGMENT INTERPRETATION ROUTINES:
	 */

	/**
	 * model interpretation of message header segment (assumed to be in $this->segments[0])
	 */
	protected function interpretMSH($msh) {
		while( count($msh) < 12 ) {
			$msh[] = null;
		}
		$this->sending_application   = self::interpretHD( $msh[ 2]);
		$this->sending_facility      = self::interpretHD( $msh[ 3]);
		$this->receiving_application = self::interpretHD( $msh[ 4]);
		$this->receiving_facility    = self::interpretHD( $msh[ 5]);
		$this->date_time_of_message  = self::interpretTS( $msh[ 6]);
		$this->message_type          = self::interpretMSG($msh[ 8]);
		$this->processing_id         = self::interpretPT( $msh[10]);
		$this->version_id            = $msh[11];
	}

	/**
	 * interpret an NTE segment
	 *
	 * @param HL7 $segment
	 * @return string  note text (or null if empty/invalid)
	 */
	public static function interpretNTE( $segment ) {
		if( $segment[0] != 'NTE' )
			return null;
		return $segment[2];
	}

	/*
	 * DATATYPE INTERPRETATION/ENCODING ROUTINES:
	 */

	/**
	 * interpret HL7 CE field (usually refers to a specific data table, see HL7 spec appendix A)
	 *
	 * @param HL7 $field
	 * @param string $table	Name of data table, e.g., '0002' for marital status; the function self::interpretnnnn must exist, e.g., interpret0002
	 * @return string		The best textual interpretation of the given field
	 */
	public static function interpretCE( $field, $table = null ) {
		$code = null;	// $code is CE.1, if present or the whole field if no components
		$text = null;	// $text is CE.2, if present
		if( is_array( $field )) {
			$field = $field[0];		// First repeat
			if( is_array( $field )) {
				$code = $field[0];	// CE.1
				$text = $field[1];	// CE.2
			}
		}
		if( is_string( $text ))	// prefer text component if present, otherwise translate code per HL7 table
			return $text;
		if( is_string( $field ))
			$code = $field;
		if( is_null( $table ))
			return $code;
		return call_user_func( 'self::interpret' . $table, $code );
	}

	/**
	 * encode a HL7 CE datatype field
	 *
	 * @param string $value					Translated code (e.g., from corresponding interpretCE)
	 * @param string $component_delimiter	Character used to delimit components, typically '^'
	 * @param string $table					HL7 table to use, e.g., '0005'
	 * @return string						Value to put into HL7 field
	 */
	public static function encodeCE( $value, $component_delimiter, $table = null ) {
		if( is_null( $value ) || is_null( $table ))
			return $value;
		$code = call_user_func( 'self::encode' . $table, $value );
		if( !is_null( $code ))
			return $code;
		return $component_delimiter . $value . $component_delimiter;   // CE.2 Text
	}

	/**
	 * interpret HL7 CX datatype
	 *
	 * @param HL7 $field
	 * @return string or null
	 */
	public static function interpretCX($field) {
		return is_array($field) ? $field[0][0] : $field;
	}

	/**
	 * interpret HL7 EI datatype
	 *
	 * @param HL7 $field
	 * @return string or null
	 */
	public static function interpretEI($field) {
		return is_array($field) ? $field[0][0] : $field;
	}

	/**
	 * interpret HL7 XON datatype
	 *
	 * @param HL7 $field
	 * @return string or null
	 */
	public static function interpretXON($field) {
		while( is_array($field) ) {
			$field = $field[0];
		}
		return $field;
	}

	/**
	 * interpret HL7 HD datatype
	 *
	 * @param HL7 $field
	 * @return string or null
	 */
	public static function interpretHD($field) {
		while( is_array($field) ) {
			$field = $field[0];
		}
		return $field;
	}

	/**
	 * interpret HL7 DLN (driver license) datatype
	 *
	 * @param HL7 $field		DLN.1 license number, [DLN.2] state, [DLN.3] expiration date
	 * @param &string &$state
	 * @return string driver license number
	 */
	public static function interpretDLN($field, &$state = null ) {
		if( is_string($field) ) {
			return $field;
		}
		$state = $field[0][1];
		return $field[0][0];
	}

	/**
	 * interpret HL7 TQ (time quantity) datatype
	 *
	 * @param HL7 $tq		(... [TQ.4] timing_start, [TQ.5] timing_end
	 * @param string &$end	timing_end component
	 */
	public static function interpretTQ( $tq, &$end_time = null, &$duration = null ) {
		if( is_null( $tq ))
			return null;
		if( isset( $tq[0][4] ))
			$end_time =	self::interpretTS( $tq[0][4] );
		$duration = $tq[0][2];
		return self::interpretTS( $tq[0][3] );
	}

	/**
	 * interpret HL7 TS (timestamp) datatype (if there are repeats, only first is interpreted)
	 *
	 * @param HL7 $dtm   (TS.1 DTM time, [TS.2] degree of precision, ignored)
	 * @return DateTime
	 */
	public static function interpretTS($dtm) {
		while( is_array($dtm) ) {
			$dtm = $dtm[0];
		}
		switch(strlen($dtm)) {
			case  8: $format = 'Ynd|';		break;		// 20130515
			case 13: $format = 'YndO|';		break;		// 20130515-0500
			case 10: $format = 'YndH|';		break;		// 2013051514
			case 15: $format = 'YndHO|';	break;		// 2013051514-0500
			case 12: $format = 'YndHi|';	break;		// 201305151430
			case 17: $format = 'YndHiO|';	break;		// 201305151430-0500
			case 14: $format = 'YndHis|';	break;		// 20130515143000
			case 19: $format = 'YndHisO|';	break;		// 20130515143000-0500
			default: $format = 'YmdHi+';	break; 		// cheesy, should pick up more precision if present (and not ignore any tz offset)
		}
		$ret = DateTime::createFromFormat($format, $dtm);
		if( !$ret )
			return null;
		return $ret;
	}
	
	/**
	 * interpret HL7 DR datatype (if there are repeats, only first is interpreted)
	 *
	 * @param HL7 $dtm   (DR.1 DTM start date, [DR.2] DTM end date)
	 * @param DateTime &$start
	 * @param DateTime &$end
	 * @return DateTime  $start
	 */
	public static function interpretDR( $dtm, &$start, &$end ) {
		if( !is_array( $dtm )) {
			$start 	= HL7Message::interpretTS( $dtm );
			$end	= null;
		} else {
			$start 	= HL7Message::interpretTS( $dtm[0][0] );
			$end	= HL7Message::interpretTS( $dtm[0][1] );
		}
		return $start;
	}

	/**
	 * interpret HL7 paired table like this:  key1^val1~key2^val2 etc.
	 *
	 * @param HL7 $dtm
	 * @param array &$keys
	 * @param array &$values
	 * @return int count
	 */
	public static function interpretPairedTable( $dtm, &$keys, &$values ) {
		$keys = array();
		$values = array();
		if( !is_array( $dtm )) {
			$keys[] 	= $dtm;
			$values[]	= null;
		} else {
			foreach( $dtm as $pair ) {
				$keys[]		= $pair[0];
				$values[]	= ( isset( $pair[1] ) ? $pair[1] : null );
			}
		}
		if( count( $keys ) == 0 ) {
			$keys = null;
			$values = null;
			return 0;
		}
		if( count( $keys ) == 1 ) {
			$keys = $keys[0];
			$values = $values[0];
			return 1;
		}
		return count( $keys );
	}

	/**
	 * interpret HL7 MSG (message type) datatype
	 *
	 * @param HL7 field  (MSG.1 message code, MSG.2 trigger event, [MSG.3] message structure)
	 * @return array     e.g., [ "ADT", "A08" ]
	 */
	public static function interpretMSG($msg) {
		if( is_array($msg) ) {
			return $msg[0];
		}
		return $msg;   // in case of ill-formed, i.e., no components
	}

	/**
	 * interpret HL7 PT (processing type) datatype (if there are repeats, only first is interpreted)
	 *
	 * @param HL7 field ([PT.1] processing id, [PT.2] processing mode, ignored )
	 * @return string   one of PT_* constants
	 */
	public static function interpretPT($pt) {
		while( is_array($pt) ) {
			$pt = $pt[0];
		}
		switch( $pt ) {
			case 'D': return self::PT_DEBUGGING;
			case 'T': return self::PT_TRAINING;
			default:
			case 'P': return self::PT_PRODUCTION;
		}
	}

	/**
	 * interpret HL7 XTN (phone number) datatype (if there are repeats, only first is interpreted)
	 *
	 * @param HL7 $field   			[XTN.1] telephone number (any format), [XTN.4] email, [XTN.5] country code, [XTN.6] area code, [XTN.7] local number, [XTN.8] extension
	 * @param &string &$extension
	 * @param &string &$email
	 * @return string     			The American canonical form as AAA-EEE-NNNN
	 */
	public static function interpretXTN($field, &$extension = null, &$email = null) {
		if( !is_array($field) ) {
			$extension = null;
			$email     = null;
			$number    = $field;
		} else {
			$field     = $field[0];  // first repeat
			while( count($field) < 8 ) {
				$field[] = null;
			}
			$extension = $field[7];
			$email     = $field[3];
			$number    = is_string($field[0]) ? $field[0] : $field[4].$field[5].$field[6];
		}
		if( strlen($number) < 4 ) {
			return null;
		}
		// strip non-digits from phone number string
		$buffer = '';
		for( $i = 0; $i < strlen($number); $i++ ) {
			if( ctype_digit($number[$i]) ) {
				$buffer .= $number[$i];
			}
		}
		// format as we like it
		$number = substr($buffer, -7, 3).'-'.substr($buffer, -4, 4);  // assume at least 7 or it will just be nonesense anyway
		$buffer = substr($buffer, 0, strlen($buffer) - 7 );
			if( strlen($buffer) >= 3 ) {
			$number = substr($buffer, -3, 3).'-'.$number;
			$buffer = substr($buffer, 0, strlen($buffer) - 3 );
		}
		if( strlen($buffer) > 0 ) {
			$number = '('.$buffer.')'.$number;
		}
		return $number;
	}

	/**
	 * interpret HL7 XPN (patient name) datatype (if there are repeats, only first is interpreted)
	 *
	 * @param HL7 $field [XPN.1] family name, [XPN.2] given name, [XPN.3] second/additional given names, [XPN.4] suffix, (ignored: [XPN.5] prefix, ...)
	 * @param &string &$last_name
	 * @param &string &$first_name
	 * @param &string &$middle_name
	 * @param &string &$suffix
	 * @return string				full name
	 */
	public static function interpretXPN($field, &$last_name = null, &$first_name = null, &$middle_name = null, &$suffix = null) {
		if( !is_array($field[0]) ) {
			$last_name = $field;
			$first_name = $middle_name = $suffix = null;
		} else {
			$field = $field[0];
			while( count($field) < 4 ) {
				$field[] = null;
			}
			$last_name   = $field[0];
			$first_name  = $field[1];
			$middle_name = $field[2];
			$suffix      = $field[3];
		}

		$full_name = null;
		if( is_string($first_name) ) {
			$full_name[] = $first_name;
		}
		if( is_string($middle_name) ) {
			$full_name[] = $middle_name;
		}
		if( is_string($last_name) ) {
			$full_name[] = $last_name;
		}
		if( is_string($suffix) ) {
			$full_name[] = $suffix;
		}
		if( is_null($full_name) ) {
			return null;
		}
		return implode(' ', $full_name);
	}

	/**
	 * interpret HL7 XCN (caregiver name) datatype (if there are repeats, only first is interpreted)
	 *
	 * @param HL7 $field [XCN.1] id, [XCN.2] family name, [XPN.3] given name, [XPN.4] second/additional given names, [XPN.5] suffix, (ignored: [XPN.6] prefix, ...)
	 * @param &string &$last_name
	 * @param &string &$first_name
	 * @param &string &$middle_name
	 * @param &string &$suffix
	 * @param &string &$id
	 * @return string				full name
	 */
	public static function interpretXCN($field, &$last_name = null, &$first_name = null, &$middle_name = null, &$suffix = null, &$id = null) {
		if( !is_array($field[0]) ) {
			$last_name = $field;
			$first_name = $middle_name = $suffix = $id = null;
		} else {
			$field = $field[0];
			while( count($field) < 5 ) {
				$field[] = null;
			}
			$last_name   = $field[1];
			$first_name  = $field[2];
			$middle_name = $field[3];
			$suffix      = $field[4];
			$id          = $field[0];
		}

		$full_name = null;
		if( is_string($first_name) ) {
			$full_name[] = $first_name;
		}
		if( is_string($middle_name) ) {
			$full_name[] = $middle_name;
		}
		if( is_string($last_name) ) {
			$full_name[] = $last_name;
		}
		if( is_string($suffix) ) {
			$full_name[] = $suffix;
		}
		if( is_null($full_name) ) {
			return null;
		}
		return implode(' ', $full_name);
	}

	/**
	 * interpret HL7 PL (person location) datatype
	 *
	 * @param HL7 $field [PL.4] facility
	 * @param &string &$room
	 * @return string facility
	 */
	public static function interpretPL( $field, &$room = null ) {
		$room = isset($field[0][1]) ? $field[0][1] : null;
		return isset( $field[0][3] ) ? $field[0][3] : null;
	}

	/**
	 * interpret HL7 XAD (address) datatype (if there are repeats, only first is interpreted)
	 *
	 * @param HL7 $field [XAD.1] street address (address1), [XAD.2] other designation (address2), [XAD.3] city, [XAD.4] state, [XAD.5] postal code (ignored:...)
	 * @param &string &$address1
	 * @param &string &$address2
	 * @param &string &$city
	 * @param &string &$state
	 * @param &string &$postal_code
	 */
	public static function interpretXAD($field, &$address1, &$address2 = null, &$city = null, &$state = null, &$postal_code = null) {
		if( !is_array($field[0]) ) {
			$address1 = $field;
		} else {
			$field = $field[0];
			$address1 = isset($field[0]) ? $field[0] : null;
		}
		$address2    = isset($field[1]) ? $field[1] : null;
		$city        = isset($field[2]) ? $field[2] : null;
		$state       = isset($field[3]) ? $field[3] : null;
		$postal_code = isset($field[4]) ? $field[4] : null;
		// work-around for hl7 content that has &'s that have not been properly escaped
		if( is_array( $address1 )) {
			$address1 = implode( '&', $address1 );
		}
		if( is_array( $address2 )) {
			$address2 = implode( '&', $address2 );
		}
	}

	/*
	 * DATA TABLE INTERPRETATION/ENCODING ROUTINES:
	 * @todo	Put these tables in the database
	 */

	/**
	 * interpret HL7 table 0001, gender
	 *
	 * @param HL7 $field
	 * @return string or null  'M', 'F', or null
	 */
	public static function interpret0001($field) {
		while( is_array($field) ) {
			$field = $field[0];
		}
		return $field == 'M' ? 'M' : ($field == 'F' ? 'F' : null);
	}

	/**
	 * encode HL7 table 0001, gender
	 *
	 * @param string $value	The interpreted value
	 * @return string		The HL7 field
	 */
	public static function encode0001( $value ) {
		switch( $value ) {
			case 'M': return $value;
			case 'F': return $value;
			default:  return 'U';  // unknown
		}
	}

	/**
	 * interpret HL7 table 0002, marital status
	 *
	 * @param HL7 $field
	 * @return string or null  'Married', 'Single', 'Divorced', 'Separated', 'Domestic Partner', 'Widowed', or null
	 */
	public static function interpret0002($field) {
		switch($field) {
			case 'E':
			case 'I':
			case 'A': return 'Separated';

			case 'D': return 'Divorced';

			case 'C':
			case 'M': return 'Married';

			case 'R':
			case 'P': return 'Domestic Partner';

			case 'G':
			case 'B':
			case 'N':
			case 'S': return 'Single';

			case 'W': return 'Widowed';

			case 'O':
			case 'T':
			case 'U':
			default: return null;
		}
	}

	/**
	 * encode HL7 table 0002, marital status
	 *
	 * @param string $value	The interpreted value
	 * @return string		The HL7 field
	 */
	public static function encode0002( $value ) {
		switch( $value ) {
			case 'Separated':			return 'A';
			case 'Divorced': 			return 'D';
			case 'Married':				return 'M';
			case 'Domestic Partner':	return 'P';
			case 'Single':				return 'S';
			case 'Widowed':				return 'W';
			default:  					return 'U';  // unknown
		}
	}

	/**
	 * interpret HL7 table 0005, race
	 *
	 * @param HL7 $field
	 * @return string or null textual description of race
	 */
	public static function interpret0005( $field ) {
		switch( $field ) {
			case '1002-5': return 'American Indian or Alaska Native';
			case '2028-9': return 'Asian';
			case '2054-5': return 'Black or African American';
			case '2076-8': return 'Native Hawaiian or Other Pacific Islander';
			case '2106-3': return 'White';
			case '2131-1': return 'Other Race';
		}
		return $field;
	}

	/**
	 * encode HL7 table 0005, race
	 *
	 * @param string $value	The interpreted value
	 * @return string		The HL7 field
	 */
	public static function encode0005( $value ) {
		switch( $value ) {
			case 'American Indian or Alaska Native':			return '1002-5';
			case 'Asian':										return '2028-9';
			case 'Black or African American':					return '2054-5';
			case 'Native Hawaiian or Other Pacific Islander':	return '2076-8';
			case 'White':										return '2106-3';
			case 'Other Race':									return '2131-1';
			case 'Not Given/Specified':							return '';
			default:  											return null;
		}
	}

	/**
	 * interpret HL7 table 0063, relationship
	 *
	 * @param HL7 $field
	 * @return string or null textual description of employment status
	 */
	public static function interpret0063($field) {
		switch($field) {
			case 'ASC': return 'Associate';
			case 'BRO': return 'Brother';
			case 'CGV': return 'Care giver';
			case 'CHD': return 'Child';
			case 'DEP': return 'Handicapped dependent';
			case 'DOM': return 'Life partner';
			case 'EMC': return 'Emergency contact';
			case 'EME': return 'Employee';
			case 'EMR': return 'Employer';
			case 'EXF': return 'Extended family';
			case 'FCH': return 'Foster child';
			case 'FND': return 'Friend';
			case 'FTH': return 'Father';
			case 'GCH': return 'Grandchild';
			case 'GRD': return 'Guardian';
			case 'GRP': return 'Grandparent';
			case 'MGR': return 'Manager';
			case 'MTH': return 'Mother';
			case 'NCH': return 'Natural child';
			case 'NON': return 'None';
			case 'OAD': return 'Other adult';
			case 'OTH': return 'Other';
			case 'OWN': return 'Owner';
			case 'PAR': return 'Parent';
			case 'SCH': return 'Stepchild';
			case 'SEL': return 'Self';
			case 'SIB': return 'Sibling';
			case 'SIS': return 'Sister';
			case 'SPO': return 'Spouse';
			case 'TRA': return 'Trainer';
			case 'UNK': return 'Unknown';
			case 'WRD': return 'Ward of court';
		}
		return $field;				// unknown code, just use that (questionable)
	}

	/**
	 * encode HL7 table 0063, relationship
	 *
	 * @param string $value	The interpreted value
	 * @return string		The HL7 field
	 */
	public static function encode0063( $value ) {
		switch( $value ) {
			case 'Associate':				return 'ASC';
			case 'Brother':					return 'BRO';
			case 'Care giver':				return 'CGV';
			case 'Child':					return 'CHD';
			case 'Handicapped dependent':	return 'DEP';
			case 'Life partner':			return 'DOM';
			case 'Emergency contact':		return 'EMC';
			case 'Employee':				return 'EME';
			case 'Employer':				return 'EMR';
			case 'Extended family':			return 'EXF';
			case 'Foster child':			return 'FCH';
			case 'Friend':					return 'FND';
			case 'Father':					return 'FTH';
			case 'Grandchild':				return 'GCH';
			case 'Guardian':				return 'GRD';
			case 'Grandparent':				return 'GRP';
			case 'Manager':					return 'MGR';
			case 'Mother':					return 'MTH';
			case 'Natural child':			return 'NCH';
			case 'None':					return 'NON';
			case 'Other adult':				return 'OAD';
			case 'Other':					return 'OTH';
			case 'Owner':					return 'OWN';
			case 'Parent':					return 'PAR';
			case 'Stepchild':				return 'SCH';
			case 'Self':					return 'SEL';
			case 'Sibling':					return 'SIB';
			case 'Sister':					return 'SIS';
			case 'Spouse':					return 'SPO';
			case 'Trainer':					return 'TRA';
			case 'Unknown':					return 'UNK';
			case 'Ward of court':			return 'WRD';
			default:  						return null;
		}
	}

	/**
	 * interpret HL7 table 0066, employment status
	 *
	 * @param HL7 $field
	 * @return string or null textual description of employment status
	 */
	public static function interpret0066($field) {
		switch($field) {
			case '1': return 'Full time employed';
			case '2': return 'Part time employed';
			case '3': return 'Unemployed';
			case '4': return 'Self-employed,';
			case '5': return 'Retired';
			case '6': return 'On active military duty';
			case '9': return 'Unknown';
			case 'C': return 'Contract, per diem';
			case 'L': return 'Leave of absence';
			case 'O': return 'Other';
			case 'T': return 'Temporarily unemployed';
		}
		return $field;
	}

	/**
	 * encode HL7 table 0066, employment status
	 *
	 * @param string $value	The interpreted value
	 * @return string		The HL7 field
	 */
	public static function encode0066( $value ) {
		switch( $value ) {
			case 'Full time employed':		return '1';
			case 'Part time employed':		return '2';
			case 'Unemployed':				return '3';
			case 'Self-employed,':			return '4';
			case 'Retired':					return '5';
			case 'On active military duty':	return '6';
			case 'Unknown':					return '9';
			case 'Contract, per diem':		return 'C';
			case 'Leave of absence':		return 'L';
			case 'Other':					return 'O';
			case 'Temporarily unemployed':	return 'T';
			default:						return null;
		}
	}

	/**
	 * interpret HL7 table 0189, ethnic group
	 *
	 * @param HL7 $field
	 * @return string or null textual description of ethnic group
	 */
	public static function interpret0189( $field ) {
		switch( $field ) {
			case 'H': return 'Hispanic or Latino';
			case 'N': return 'Not Hispanic or Latino';
			case 'U': return 'Not Given/Specified';
		}
		return $field;
	}

	/**
	 * encode HL7 table 0189, ethnic group
	 *
	 * @param string $value	The interpreted value
	 * @return string		The HL7 field
	 */
	public static function encode0189( $value ) {
		switch( $value ) {
			case 'Hispanic or Latino':		return 'H';
			case 'Not Hispanic or Latino':	return 'N';
			case 'Not Given/Specified':
			case 'Unknown':					return 'U';
			default:						return null;
		}
	}

	/**
	 * interpret HL7 table 0276, appointment reason code
	 *
	 * @param HL7 $field
	 * @return string or null textual description
	 */
	public static function interpret0276( $field ) {
		switch( $field ) {
			case 'WALKIN':
				return 'WalkIn';
			case 'CHECKUP':
			case 'EMERGENCY':
			case 'FOLLOWUP':
			case 'ROUTINE':
			default:
				return ucfirst( strtolower( $field ));
		}
	}

	/**
	 * encode HL7 table 0276, appointment reason code
	 *
	 * @param string $value	The interpreted value
	 * @return string		The HL7 field
	 */
	public static function encode0276( $value ) {
		switch( $value ) {
			case 'Checkup':
			case 'Emergency':
			case 'Followup':
			case 'Routine':
			case 'WalkIn':
				return strtoupper($value);
			default:	return null;
		}
	}

	/**
	 * interpret HL7 table 0296, primary language
	 *
	 * @param HL7 $field
	 * @return string or null
	 */
	public static function interpret0296($field) {
		if( is_array($field) ) {
			$field = $field[0];		// First repeat
			if( is_array($field) ) {
				$field = $field[1];	// CE.2
			}
		}
		return $field;	// FIXME: filter against our preferred_language table?  Probably not.
	}

	/**
	 * encode HL7 table 0296, primary language
	 *
	 * @param string $value	The interpreted value
	 * @return string		The HL7 field
	 */
	public static function encode0296( $value ) {
		return $value;
	}

	/*
	 * MISCELLANEOUS INTERPRETATION ROUTINES:
	 */

	/**
	 * interpret HL7 social security number
	 *
	 * @param HL7 $field
	 * @return string or null (xxx-xx-xxxx format)
	 */
	public static function interpretSsn($field) {
		if( strlen($field) == 9 ) {
			return substr($field, 0, 3).'-'.substr($field, 3, 2).'-'.substr($field, 5, 4);
		}
		return $field;
	}

	/**
	 * figure out duration from start_time and one of end_time or duration_n/units
	 *
	 * @param DateTime &$start_time		Set to current time if absent/null
	 * @param DateTime &$end_time		Set to $start_time + $return_value (or used to calculate $return_value if no $duration_n)
	 * @param int $duration_n			Number of $units from $start_time to $end_time (if conflicting, this one wins)
	 * @param string $units				Something starting with 's' or 'S' will be considered seconds, 'h' or 'H' hours, everything else minutes
	 * @param int $alt_duration_n		Same as $duration_n, but used if $duration_n is null, if both null, then $end_time is used to determine duration
	 * @return int 						Number of minutes between the (possibly new) values of $start_time and $end_time
	 */
	public function interpretDuration( &$start_time, &$end_time, $duration_n = null, $units = null, $alt_duration_n = null ) {
		if( is_null( $start_time ))
			$start_time = new DateTime();
		if( is_null( $duration_n ))
			$duration_n = $alt_duration_n;
		if( !is_null( $duration_n )) {
			switch( strtolower( substr( $units, 0, 1 ))) {
				case 's': 	$duration = (int) $duration_n / 60; break;
				case 'h': 	$duration = (int) $duration_n * 60; break;
				default:	$duration = (int) $duration_n;		break;
			}
			$end_time = clone $start_time;
			$end_time->add( new DateInterval( 'PT' . $duration . 'M' ) );
		} else {
			if( is_null( $end_time ))
				$end_time = clone $start_time;
			$iv = date_diff( $start_time, $end_time );
			$duration = $iv->i + 60 * ( $iv->h + 24 * $iv->d );
		}
		return $duration;
	}

	/**
	 * lookup corresponding emdeon relationship code for given relationship
	 *
	 * @param string $relationship - e.g., "Spouse"
	 * @return string - e.g., "01"
	 */
	public function getRelationshipCode( $relationship )
	{
		$rel_mn   = 'EmdeonRelationship';
		$rel_m    = ClassRegistry::init( $rel_mn );
		$rel_desc = is_null( $relationship ) ? 'Unknown' : $relationship;
		$rel_get  = $rel_m->findByDescription( $rel_desc );
		if( $rel_get === false )
			$rel_get = $rel_m->findByDescription( 'Unknown' );
		if( $rel_get === false )
			$rel_code = null;
		else
			$rel_code = $rel_get[$rel_mn]['code'];
		return $rel_code;
	}
	
	/**
	 * lookup corresponding emdeon relationship description for given code
	 * 
	 * @param string $code - e.g., "01"
	 * @return string - e.g., "Spouse"
	 */
	public function getRelationshipDescription( $code )
	{
		if( empty( $code ))
			return 'Unknown';
		$rel_mn   = 'EmdeonRelationship';
		$rel_m    = ClassRegistry::init( $rel_mn );
		$rel_get  = $rel_m->findByCode( $code );
		if( $rel_get === false )
			return 'Unknown';
		return $rel_get[$rel_mn]['description'];
	}
	
	/**
	 * Run the hl7_ignore console script
	 * 
	 * @param array $db_config (from a model's DataSource)
	 * @param string $receiver name of receiving partner application
	 * @param boolean $patients cause past patient records to be ignored
	 * @param boolean $encounters cause past encounters to be ignored
	 * @param boolean $appointments caus past appointment records to be ignored
	 */
	public function runHL7Ignore( $db_config, $receiver, $patients = false, $encounters = false, $appointments = false ) {
		$shellcommand = "php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app \"".APP."\" hl7_ignore ".$db_config['database']." -receiver \"$receiver\" ";
		if( $patients )
			$shellcommand .= '-patients ';
		if( $encounters )
			$shellcommand .= '-encounters ';
		if( $appointments )
			$shellcommand .= '-appointments ';
		$shellcommand .= '>> /dev/null 2>&1 &';
		exec( $shellcommand );
		
	}
}
