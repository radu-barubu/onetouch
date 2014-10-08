<?php

class MessageHeaderSegment extends AppModel 
{
	public $name = 'MessageHeaderSegment';
	public $useTable = 'message_header_segment';
	public $primaryKey = 'id';
	
	
	function getSettings()
	{
		$message_header_segment  = $this->find('first');
		$message_header_segment = (object) $message_header_segment['MessageHeaderSegment'];
		
		if(!$message_header_segment ) {
			$message_header_segment  = new stdObject();
		}
		return $message_header_segment;
	}
	
}	
?>	