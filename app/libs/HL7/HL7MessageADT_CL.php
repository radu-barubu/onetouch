<?php

/**
 * Subclass to override standard behavior for Compulink ADT (only difference is in incoming PID)
*/
class HL7MessageADT_CL extends HL7MessageADT {

	/**
	 * interpret this ADT message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type ADT)
	 * @return false|HL7MessageADT
	 */
	static protected function interpretMessage( $base, $segments ) {
		return self::interpretMessageP( $base, $segments, 'HL7SegmentPID_CL' );
	}
}