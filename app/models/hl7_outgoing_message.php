<?php

class Hl7OutgoingMessage extends AppModel
{
	public $name			= 'Hl7OutgoingMessage';
	public $primaryKey		= 'outgoing_message_id';
	public $actsAs 			= array( 'Des' => array( 'message_text' ));
	public $virtualFields	= array();

	public function beforeSave($options)
	{
		$this->data[$this->name]['modified_timestamp'] = __date("Y-m-d H:i:s");
		return true;
	}
}