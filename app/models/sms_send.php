<?php

class SmsSend extends AppModel 
{
	public $name = 'SmsSend';
	public $useTable = false;
	
	public function sms_send($patient_id, $message)
	{
		$patient_preferences = ClassRegistry::init('PatientPreference')->getPreferences($patient_id);
		if( $patient_preferences['preferred_contact_method'] == 'sms' )
		{
			$patient = ClassRegistry::init('PatientDemographic')->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'fields' => 'cell_phone', 'recursive' => -1));			
			$this->sms_ship($patient_id, $patient_preferences['carrier_id'], $patient['PatientDemographic']['cell_phone'], $message);
			return true;
		}
		else 
		{
			return false;
		}
	}
		
	private function sms_ship($patient_id, $carrier_id, $cell, $message)
	{
		$cell = preg_replace("/[^0-9]/","",$cell); //remove dashes, and parenthesis
		if($carrier_id)
		{
			$carrier=ClassRegistry::init('SmsCarrier')->find('first', array('conditions' => array('carrier_id' => $carrier_id)));	
			$to2 = $cell . '@'. $carrier['SmsCarrier']['carrier_postfix'];
		}
		else
		{
			//no carrier was defined, try to guess major providers
			$carrier=ClassRegistry::init('SmsCarrier')->find('all');
			foreach($carrier as $carrier2)
			{
				if ( $carrier2['SmsCarrier']['carrier_id'] == 1 
					|| $carrier2['SmsCarrier']['carrier_id'] == 4
					|| $carrier2['SmsCarrier']['carrier_id'] == 5
					|| $carrier2['SmsCarrier']['carrier_id'] == 10)
					$to[] = $cell.'@'.$carrier2['SmsCarrier']['carrier_postfix'];
			}
			$to2 = implode(', ', $to);
		}		
			$practiceSetting = ClassRegistry::init('PracticeSetting')->getSettings();
			//private label customer?
                        $domain=(!empty($practiceSetting->partner_id))? $practiceSetting->partner_id : 'ote.bz';
			//$sender=(!empty($practiceSetting->sender_email))? $practiceSetting->sender_email : 'text@'.$domain;
			/* we use MMS so no more limits 
			// if sms size is greater than 150 chars, split it up 
			if (strlen($message) > 160) 
			{
				$chunks = explode("||||",wordwrap($message,160,"||||"));
				$total = count($chunks);
				foreach($chunks as $page => $chunk)
				{
    					$body = sprintf("(%d of %d) ",$page+1,$total);
    					$body .= $chunk  . "\n";
    					mail($to2,'',  $body, "From: Your_Doctor@".$domain);
				}					
			}
			else
			{ */
				mail($to2,'', $message, "From: text@".$domain);//.$sender);
			//}
	
	}	
}

?>
