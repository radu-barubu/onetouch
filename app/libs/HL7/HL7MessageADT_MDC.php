<?php

/**
 * Subclass to override standard behavior for MDConnection ADT (only difference is in outgoing PID)
*/
class HL7MessageADT_MDC extends HL7MessageADT {

	/**
	 * create a new HL7MessageADT_MDC from data source where key = $encounter_id
	 *
	 * @param int $patient_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return HL7Message|false
	 */
	public static function createFromDb( $patient_id, $receiver, $processing_id = HL7Message::PT_DEBUGGING ) {
		return self::createFromDbP( $patient_id, $receiver, $processing_id, 'HL7SegmentPID_MDC' );
	}

	/**
	 * interpret this ADT message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type ADT)
	 * @return false|HL7MessageADT
	 */
	static protected function interpretMessage( $base, $segments ) {
		return self::interpretMessageP( $base, $segments, 'HL7SegmentPID_MDC' );
	}
}