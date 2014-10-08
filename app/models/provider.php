<?php

class Provider extends AppModel 
{ 
	public $name = 'Provider'; 
	public $primaryKey = 'ProviderID';
	public $useTable = 'provider';
	

	public function getPin($user_id)
	{
		$result = $this->find('list', array(
			'conditions' => array('user_accounts.user_id' => $user_id),
			'fields' => 'provider_pin'
			)
		);
		
		$pin = end($result);
		
		return $pin;
	}
	
}


?>