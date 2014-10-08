<?php
/**
 * Subclass to override standard behavior for Compulink SIU (only difference is in incoming PID)
*/
class HL7MessageSIU_CL extends HL7MessageSIU {

	/**
	 * interpret this SIU (scheduling info unsolicited) message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type SIU)
	 * @return false|HL7MessageSIU
	 */
	static protected function interpretMessage( $base, $segments ) {
		return self::interpretMessageP( $base, $segments, 'HL7SegmentPID_CL', 'HL7SegmentSCH' );
	}
}
