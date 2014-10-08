<?php

class FileHandler {
	
	public static function getRemoteFaxDocument($receive_id)
	{
		$recive_id = filter::integer($receive_id);
		
		if(!$recive_id) {
			return;
		}
	
		$fax = new fax;
		$fax_doc = $fax->getFaxDocument($receive_id);
		
		$controller = new Controller();
		
		$controller->loadModel('MessagingFax');
		$MessagingFax = $controller->MessagingFax->row(array(
			'conditions' => array('recvid' => $receive_id)
		));
		
		$file = UploadSettings::getPath('received_fax') . $MessagingFax['filename'];
		
		if(site::write($fax_doc, $file)) {
			
			$MessagingFax['recvdate'] = time();
			$controller->MessagingFax->save($MessagingFax);
		}
		
		return $file;
	}
}