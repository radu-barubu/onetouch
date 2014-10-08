<?php

class Hl7IncomingMessage extends AppModel
{
	public $name			= 'Hl7IncomingMessage';
	public $primaryKey		= 'incoming_message_id';
	public $actsAs 			= array( 'Des' => array( 'message_text', 'log' ));
	public $virtualFields	= array();

	public function beforeSave($options)
	{
		$this->data[$this->name]['modified_timestamp'] = __date("Y-m-d H:i:s");
		return true;
	}
}