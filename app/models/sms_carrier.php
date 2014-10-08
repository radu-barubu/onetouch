<?php

class SmsCarrier extends AppModel 
{ 
	public $name = 'SmsCarrier'; 
	public $primaryKey = 'carrier_id';
	public $useTable = 'sms_carriers';
	public $order = "SmsCarrier.carrier_name ASC"; 
	
	function getList()
	{
		$smscarrier = $this->find('list',
			array(
				'fields' => array('carrier_id','carrier_name')
			)
		);
		
		$smscarrier = AppController::sanitizeHTML( $smscarrier );
		
		return $smscarrier;
	}
}

?>