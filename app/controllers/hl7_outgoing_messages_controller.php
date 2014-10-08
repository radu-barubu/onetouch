<?php
App::import( 'Lib',   'HL7Message', 	array('file'=>'HL7Message.php'));

class Hl7OutgoingMessagesController extends AppController {
	public $name = 'Hl7OutgoingMessages';
	public $helpers = Array( 'Html', 'Form' );
	public $limit = 300;

	public function index() {
		$this->set( 'limit', $this->limit );
		$this->set( 'hl7_outgoing_messages', $this->Hl7OutgoingMessage->find(
				'all',
				array( 'order' => array( 'Hl7OutgoingMessage.modified_timestamp DESC', 'Hl7OutgoingMessage.outgoing_message_id DESC' ), 'limit' => $this->limit )
				));
	}
}