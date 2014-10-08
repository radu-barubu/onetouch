<?php
App::import( 'Lib',   'HL7Message', 	array('file'=>'HL7Message.php'));

class HL7MessageNull extends HL7Message {
	public $record_id;
	public $record_type;
	public $event_timestamp;
	public $version;

	/**
	 * ctor (null message, but has enough info to make a placeholder in hl7_outgoing_messages)
	 * 
	 * @param string $record_id
	 * @param string $record_type
	 * @param string $receiving_application
	 */
	public function HL7MessageNull( $record_id = null, $record_type = null, $receiving_application = null, $event_timestamp = null, $version = self::VERSION ) {
		$this->record_id = $record_id;
		$this->record_type = $record_type;
		$this->receiving_application = $receiving_application;
		$this->message_type = array( null, null );
		$this->event_timestamp = $event_timestamp;
		$this->version = $version;
		return $this;
	}
	
	public function produceMessage( $version = null ) {
		$msg_text = '';
		$this->logOutgoing( $this->record_id, $this->record_type, $msg_text, $this->event_timestamp, ( is_null($version) ? $this->version : $version ));
		return $msg_text;
	}
}