<?php
App::import( 'Lib', 'HL7SegmentFT1', array( 'file' => 'HL7SegmentFT1.php' ));

/**
 * Subclass to override standard behavior for MDConnection FT1 (use npi)
 */
class HL7SegmentFT1_MDC extends HL7SegmentFT1 {
	
	/**
	 * create a new array HL7MessageFT1 from the encounter where key = $encounter_id
	 *
	 * @param int $encounter_id			Primary key id for EncounterMaster model
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return array|false
	 */
	public static function createFromDb( $encounter_id, $receiver = null, $processing_id = HL7Message::PT_DEBUGGING ) {
		return self::createFromDbP( $encounter_id, '1', 'npi' );
	}
}