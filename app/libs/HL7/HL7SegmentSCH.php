<?php
App::import('Lib', 'HL7Segment', array('file'=>'HL7Segment.php'));

class HL7SegmentSCH extends HL7Segment {
	const SEGMENT_TYPE = 'SCH';
	public $calendar_id;			// our calendar_id
	public $placer_id;				// SCH.1.EI, the placer appointment ID
	public $event_reason;			// SCH.6.CE
	public $appointment_reason;		// SCH.7.CE0276
	public $start_time;				// SCH.11.TQ.4
	public $end_time;				// SCH.11.TQ.5
	public $duration;				// duration in minutes, calculated from SCH.9.NM, SCH.10.CE, and SCH.11

	/**
	 * interpret the given parsed $segment into $this properties
	 *
	 * @param array $segments			Parsed HL7 message (with correct message_type)
	 * @return false|HL7Message
	 */
	function interpret($segment) {
		if( $segment[0] != self::SEGMENT_TYPE )
			throw new Exception("Message segment must start with ".self::SEGMENT_TYPE);
		while( count($segment) < 12 ) {
			$segment[] = null;
		}
		$this->placer_id			= HL7Message::interpretEI(	$segment[ 1] );
		$this->event_reason			= HL7Message::interpretCE( 	$segment[ 6] );
		$this->appointment_reason	= HL7Message::interpretCE(	$segment[ 7], '0276' );
		$duration_n					= 							$segment[ 9];
		$units						= HL7Message::interpretCE(	$segment[10] );
		$this->start_time			= HL7Message::interpretTQ(	$segment[11], $this->end_time, $alt_duration_n );

		$this->duration				= HL7Message::interpretDuration( $this->start_time, $this->end_time, $duration_n, $units, $alt_duration_n );		
	}

	/**
	 * Make the segment text for the SCH segment to be sent.
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The SCH segment text
	 */
	public function produceSegment( HL7Message $msh ) {
		return self::produceSegmentP( $msh, $this->event_reason, $this->appointment_reason );
	}
	
	/**
	 * Make the segment text for the SCH segment to be sent.
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @param string $sch6		The text to use for field SCH.6
	 * @param string $sch7		The text to use for filed SCH.7
	 * @return string			The SCH segment text
	 */
	protected function produceSegmentP( HL7Message $msh, $sch6, $sch7 ) {
		$fd = $msh->field_delimiter;
		$cd = $msh->component_delimiter;
		$s  = self::SEGMENT_TYPE;
		$s .= $fd . $this->calendar_id;
		$s .= $fd . $fd . $fd . $fd;
		$s .= $fd . HL7Message::encodeCE( $sch6, $cd );
		$s .= $fd . HL7Message::encodeCE( $sch7, $cd, '0276' );
		$s .= $fd;
		$s .= $fd . $this->duration;
		$s .= $fd . 'min';
		$s .= $fd . str_repeat( $cd, 3 ) . ( isset( $this->start_time ) ? $this->start_time->format( 'YmdHi' ) : '' ) . $cd . ( isset( $this->end_time ) ? $this->end_time->format( 'YmdHi' ) : '' );
		$s .= str_repeat( $fd, 14 );
		return $s;
	}
}