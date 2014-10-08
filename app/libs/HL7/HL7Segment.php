<?php
class HL7Segment {
	/**
	 * HL7 3-character code for this message segment, e.g., 'EVN', 'PV1'
	 * @var string SEGMENT_TYPE
	 */
	const SEGMENT_TYPE = '';
	
	/**
	 * @var string This indicates the latest modified_timestamp for any database records associated with this segment.
	 */
	public $event_timestamp;

	/**
	 * Construct a HL7Segment
	 *
	 * @param array $segment	Calls $this->interpret( $segment ) to fill in properties
	 */
	function __construct( $segment = null ) {
		if( !is_null( $segment ))
			$this->interpret( $segment );
	}

	/**
	 * interpret the given parsed $segment into $this properties
	 *
	 * @param array $segments			Parsed HL7 message (with correct message_type)
	 * @return false|HL7Message
	 */
	protected function interpret($segment) {
		return false;
	}

	/**
	 * commit this segment
	 *
	 * @param string $patient_id
	 * @param int $priority			priority number where 1 is 'primary', 2 is 'secondary', etc.
	 * @return string				log of actions taken
	 */
	public function commitSegment( $patient_id, $priority = 1 ) {
		return get_called_class() .  '::commitSegment not implemented';
	}

	/**
	 * create a new HL7MessageXXX (or array if multiple) from data source where key = $id
	 *
	 * @param int $id					Primary key id for our corresponding data model
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return HL7Message|array|false
	 */
	public static function createFromDb( $id, $receiver = null, $processing_id = HL7Message::PT_DEBUGGING ) {
		return false;
	}

	/**
	 * return the HL7 message text that corresponds to this object
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @param int $seqno		The ordinal number within the message for this FT1
	 * @return string			The segment text
	 */
	public function produceSegment( HL7Message $msh, $seqno = 1 ) {
		$fd = $msh->field_delimiter;
		$cd = $msh->component_delimiter;
		$s  = self::SEGMENT_TYPE;
		$s .= $fd;
		return $s;
	}
}

?>