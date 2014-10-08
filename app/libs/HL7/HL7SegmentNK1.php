<?php
App::import('Lib', 'HL7Segment', array('file'=>'HL7Segment.php'));

/**
 * The NK1 segment is for next of kin info.  We use it currently just for emergency contact name and phone.
 * This is whatever is in the last NK1 segment encountered.
 */
class HL7SegmentNK1 extends HL7Segment {
	const SEGMENT_TYPE = 'NK1';
	public $last_name;				// NK1.2.XPN.1
	public $first_name;				// NK1.2.XPN.2
	public $middle_name;			// NK1.2.XPN.3
	public $suffix;					// NK1.2.XPN.4
	public $relationship;			// NK1.3.CE0063
	public $phone;					// NK1.5.XTN

	/**
	 * interpret the given parsed $segment into $this properties
	 *
	 * @param array $segments			Parsed HL7 message (with correct message_type)
	 * @return false|HL7Message
	 */
	function interpret($segment) {
		if( $segment[0] != self::SEGMENT_TYPE )
			throw new Exception("Message segment must start with ".self::SEGMENT_TYPE);
		while( count($segment) < 6 ) {
			$segment[] = null;
		}
		HL7Message::interpretXPN( $segment[ 2], $this->last_name, $this->first_name, $this->middle_name, $this->suffix );
		$this->relationship = HL7Message::interpretCE(	$segment[3], '0063' );
		$this->phone        = HL7Message::interpretXTN(	$segment[5] );
	}

	/**
	 * Make the segment text for the NK1 segment to be sent.
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The NK1 segment text
	 */
	public function produceSegment( HL7Message $msh, $seqno ) {
		$fd = $msh->field_delimiter;
		$cd = $msh->component_delimiter;
		$s  = self::SEGMENT_TYPE;
		$s .= $fd . $seqno;
		$s .= $fd . $this->last_name . $cd . $this->first_name . $cd . $this->middle_name . $cd . $this->suffix;
		$s .= $fd . HL7Message::encodeCE( $this->relationship, $cd, '0063' );
		$s .= $fd;
		$s .= $fd . $this->phone;
		return $s;
	}

	/**
	 * commit this segment
	 *
	 * @param string 	$patient_id
	 * @return string	log of actions taken
	 */
	public function commitSegment( $patient_id ) {
		$mn = 'PatientDemographic';
		$m 	= ClassRegistry::init( $mn );
		$get = $m->findByPatientId( $patient_id );
		if( false === $get )
			return false;
		$name = '';
		foreach( array( 'first_name', 'middle_name', 'last_name', 'suffix' ) as $fn )
			if( count( $this->$fn ) > 0 )
				$name .= ' ' . $this->$fn;
		$name = ltrim( $name );
		$goal = array( 
				$mn => array( 
						'patient_id' => $patient_id, 
						'emergency_contact' => $name, 
						'emergency_phone' => $this->phone 
				) 
		);
		$story = $mn . HL7Message::commitData( $m, $goal, array( 'patient_id' ));
		$get = $m->findByPatientId( $m->id );
		$this->event_timestamp = $get[$mn]['modified_timestamp'];
		return $story;
	}
	
	/**
	 * create a new HL7SegmentNK1 array from emergency contact info where patient_id is $patient_id
	 *
	 * @param int $patient_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return false|array
	 */
	public static function createFromDb( $patient_id, $receiver, $processing_id ) {
		$cl = get_called_class();
		$me = new $cl();
		$mn = 'PatientDemographic';
		$m  = ClassRegistry::init( $mn );
		$get = $m->findByPatientId( $patient_id );
		if( false === $get )
			return false;
		$pd = $get[$mn];
		$me->last_name = $me->first_name = $me->middle_name = $me->suffix = '';
		$names = explode( ' ', $pd['emergency_contact'] );
		switch( count( $names )) {
			case 0: 	break;
			case 1: 	$me->last_name = $names[0]; break;
			case 2: 	$me->first_name = $names[0]; $me->last_name = $names[1]; break;
			case 3: 	$me->first_name = $names[0]; $me->last_name = $names[2]; $me->middle_name = $names[1]; break;
			default: 	$me->first_name = $names[0]; $me->last_name = $names[2]; $me->middle_name = $names[1]; $me->suffix = $names[3]; break;
		}
		$me->relationship	= 'Emergency contact';
		$me->phone			= $pd['emergency_phone'];
		$me->event_timestamp = $pd['modified_timestamp'];
		return array( $me );
	}
}