<?php

class MessagingFax extends AppModel 
{ 
	public $name = 'MessagingFax'; 
	public $primaryKey = 'fax_id';
	public $useTable = 'messaging_fax';
	
	function getItemByFaxId($fax_id)
	{
		$fax = $this->find('first', array(
			'conditions' => array('fax_id' => $fax_id)
		));
		
		return isset($fax['MessagingFax'])?  $fax['MessagingFax']: false ;
	}	
	
	function getItemByRecvid($recvid)
	{
		$fax = $this->find('first', array(
			'conditions' => array('recvid' => $recvid)
		));
		
		return isset($fax['MessagingFax'])?  $fax['MessagingFax']: false ;
	}
	
	
	
}