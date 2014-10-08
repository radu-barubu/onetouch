<?php
App::import( 'Lib',   'HL7Message', 	array('file'=>'HL7Message.php'));

class Hl7IncomingMessagesController extends AppController {
	public $name = 'Hl7IncomingMessages';
	public $helpers = Array( 'Html', 'Form' );
	public $limit = 300;

	public function index() {
		$this->set( 'limit', $this->limit );
		$this->set( 'hl7_incoming_messages', $this->Hl7IncomingMessage->find(
				'all',
				array( 'order' => array( 'Hl7IncomingMessage.modified_timestamp DESC', 'Hl7IncomingMessage.incoming_message_id DESC' ), 'limit' => $this->limit )
				));
	}
	
	// FIXME: this is just a convenient place for a hack to display stuff I wanna see in the db
	public function foo() {
		$id = $this->params['named']['id'];
		$this->set( 'msg', $id );
	}
	public function html() {
		$m = ClassRegistry::init( 'EmdeonLabResult' );
		$id = $this->params['named']['id'];
		$this->set( 'msg', 'id: '. $id . "\n\r" . $m->getHTML( $id ));
		$this->render( 'foo' );
	}
	public function hl7() {
		$m = ClassRegistry::init( 'EmdeonLabResult' );
		$id = $this->params['named']['id'];
		$this->set( 'msg', 'id: '. $id . "\n\r" . $m->getHL7( $id ));
		$this->render( 'foo' );
	}
}