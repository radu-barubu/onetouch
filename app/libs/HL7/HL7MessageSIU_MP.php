<?php
/**
 * Subclass to override standard behavior for MacPractice SIU (only difference is in outgoing PID)
*/
class HL7MessageSIU_MP extends HL7MessageSIU {

	/**
	 * create a new HL7MessageSIU from data source where key = $calendar_id
	 *
	 * @param int $calendar_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return HL7Message|false
	 */
	public static function createFromDb( $calendar_id, $receiver = 'MacPractice', $processing_id = HL7Message::PT_DEBUGGING ) {
		return self::createFromDbP( $calendar_id, $receiver, $processing_id, 'HL7SegmentPID_MP', 'HL7SegmentSCH_MP' );
	}

	/**
	 * interpret this SIU (scheduling info unsolicited) message from the given parsed segments
	 *
	 * @param HL7Message $base			Initial object before factory-based dispatching (so clone its values)
	 * @param array $segments			Parsed HL7 message (with message_type SIU)
	 * @return false|HL7MessageSIU
	 */
	static protected function interpretMessage( $base, $segments ) {
		return self::interpretMessageP( $base, $segments, 'HL7SegmentPID_MP', 'HL7SegmentSCH_MP' );
	}
}
