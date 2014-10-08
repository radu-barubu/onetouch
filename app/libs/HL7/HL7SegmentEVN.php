<?php
App::import('Lib', 'HL7Segment', array('file'=>'HL7Segment.php'));

class HL7SegmentEVN extends HL7Segment {
	const SEGMENT_TYPE = 'EVN';
	public $event_type;				// EVN.1.ID0003
	public $recorded_datetime;		// EVN.2.TS

	/**
	 * interpret the given parsed $segment into $this properties
	 *
	 * @param array $segments			Parsed HL7 message (with correct message_type)
	 * @return false|HL7Message
	 */
	function interpret($segment) {
		if( $segment[0] != self::SEGMENT_TYPE )
			throw new Exception("Message segment must start with ".self::SEGMENT_TYPE);
		$this->event_type        = $segment[1];
		$this->recorded_datetime = HL7Message::interpretTS($segment[2]);
	}

	/**
	 * Make the segment text for the EVN segment to be sent.
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The EVN segment text
	 */
	public function produceSegment( HL7Message $msh ) {
		$fd = $msh->field_delimiter;
		$cd = $msh->component_delimiter;
		$s  = self::SEGMENT_TYPE;
		$s .= $fd . $this->event_type;
		$s .= $fd . $this->recorded_datetime->format( 'YmdHis' );
		return $s;
	}
}