<?php
/**
 * Subclass to override standard behavior for MacPractice DFT (only difference is in outgoing PID and FT1)
*/
class HL7MessageDFT_MP extends HL7MessageDFT {
	/**
	 * create a new HL7MessageDFT from data source where key = $encounter_id
	 *
	 * @param int $encounter_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return HL7Message|false
	 */
	public static function createFromDb( $encounter_id, $receiver = 'MacPractice', $processing_id = HL7Message::PT_DEBUGGING ) {
		return self::createFromDbP( $encounter_id, $receiver, $processing_id, 'HL7SegmentPID_MP', 'HL7SegmentFT1_MP' );
	}

	/**
	 * interpret this DFT message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type DFT)
	 * @return false|HL7MessageADT
	 */
	static protected function interpretMessage( $base, $segments ) {
		return self::interpretMessageP( $base, $segments, 'HL7SegmentPID_MP', 'HL7SegmentFT1_MP' );
	}
}
