<?php
App::import( 'Lib', 'HL7SegmentSCH', array( 'file' => 'HL7SegmentSCH.php' ));

/**
 * Subclass to override standard behavior for MacPractice SCH (only difference is semantics of appointment_reason and event_reason)
 */
class HL7SegmentSCH_MP extends HL7SegmentSCH {

	/**
	 * interpret the given parsed $segment into $this properties
	 *
	 * @param array $segments			Parsed HL7 message (with correct message_type)
	 * @return false|HL7Message
	 */
	public function interpret( $segment ) {
		parent::interpret( $segment );
		// for MacPractice we swap the semantics of appointment_reason and event_reason
		$swap = $this->event_reason;
		$this->event_reason = $this->appointment_reason;
		$this->appointment_reason = $swap;
	}

	/**
	 * Make the segment text for the SCH segment to be sent (MacPractice-specific: swap SCH.6 and SCH.7).
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The PID segment text
	 */
	public function produceSegment( HL7Message $msh ) {
		return self::produceSegmentP( $msh, $this->appointment_reason, $this->event_reason );
	}
}
