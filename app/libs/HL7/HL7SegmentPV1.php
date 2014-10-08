<?php
App::import('Lib', 'HL7Segment', array('file'=>'HL7Segment.php'));

class HL7SegmentPV1 extends HL7Segment {
	const SEGMENT_TYPE = 'PV1';
	public $room;						// PV1.3.2.IS
	public $facility;					// PV1.3.4.HD
	public $attending_doctor_id;		// our provider_id (from UserAccounts)
	public $attending_doctor_last;		// PV1.7.XCN.2
	public $attending_doctor_first;		// PV1.7.XCN.3
	public $referring_doctor_id;		// our referred_by (from UserAccounts)
	public $referring_doctor_last;		// PV1.8.XCN.2
	public $referring_doctor_first;		// PV1.8.XCN.3
	public $alternate_id;			// PV1.50.CX

	/**
	 * interpret the given parsed $segment into $this properties
	 *
	 * @param array $segments			Parsed HL7 message (with correct message_type)
	 * @return false|HL7Message
	 */
	function interpret($segment) {
		if( $segment[0] != self::SEGMENT_TYPE )
			throw new Exception("Message segment must start with ".self::SEGMENT_TYPE);
		while( count($segment) < 51 ) {
			$segment[] = null;
		}
		$this->facility = HL7Message::interpretPL( $segment[ 3], $this->room );
		$this->alternate_id	= HL7Message::interpretCX( $segment[50] );
		HL7Message::interpretXCN($segment[7], $this->attending_doctor_last, $this->attending_doctor_first);
		HL7Message::interpretXCN($segment[8], $this->referring_doctor_last, $this->referring_doctor_first);
	}

	/**
	 * create a new HL7SegmentPV1 from ScheduleCalendar model where calendar_id is $calendar_id
	 *
	 * @param int $calendar_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return false|HL7MessagePV1
	 */
	public static function createFromDb( $calendar_id, $receiver, $processing_id ) {
		$cl = get_called_class();
		$me = new $cl();
		$mn = 'ScheduleCalendar';
		$m  = ClassRegistry::init( $mn );
		$get = $m->findByCalendarId( $calendar_id );
		if( false === $get )
			return false;
		$user_model = ClassRegistry::init( 'UserAccount' );
		foreach( array( 'provider_id' => 'attending_doctor_', 'referred_by' => 'referring_doctor_' ) as $ufn => $myprefix ) {
			$doc = $user_model->getUserById( $get[$mn][$ufn] );
			if( $doc ) {
				$me->{$myprefix . 'id'}	= $get[$mn][$ufn];
				$me->{$myprefix . 'last'} 	= $doc->lastname;
				$me->{$myprefix . 'first'}	= $doc->firstname;
			}
		}
		$me->alternate_id = $get[$mn]['alternate_id'];
		$me->room = $get['ScheduleRoom']['room'];
		$me->facility = $get['ScheduleCalendar']['location'];
		$me->event_timestamp = $get[$mn]['modified_timestamp'];
		return $me;
	}

	/**
	 * Make the segment text for the PV1 segment to be sent.
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The EVN segment text
	 */
	public function produceSegment( HL7Message $msh ) {
		$fd = $msh->field_delimiter;
		$cd = $msh->component_delimiter;
		$q = $fd . $cd . $msh->repeat_delimiter . $msh->subcomponent_delimiter;
		$s  = self::SEGMENT_TYPE;
		$s .= $fd . '1';
		$s .= $fd . 'O';		// PV1.2.IS0004, patient class: outpatient
		$s .= $fd . '1' . $cd . addcslashes( $this->room, $q ) . $cd . $cd . addcslashes( $this->facility, $q );
		$s .= str_repeat( $fd, 3 );
		$s .= $fd . $this->attending_doctor_id . $cd . $this->attending_doctor_last . $cd . $this->attending_doctor_first;
		$s .= $fd . $this->referring_doctor_id . $cd . $this->referring_doctor_last . $cd . $this->referring_doctor_first;
		$s .= str_repeat( $fd, 41 );
		$s .= $fd . addcslashes( $this->alternate_id, $q );
		return $s;
	}
}

?>