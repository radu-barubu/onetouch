<?php

class ImmtrackCountry extends AppModel 
{
	public $name = 'ImmtrackCountry';
	public $primaryKey = 'immtrack_country_id';
	public $useTable = 'immtrack_country';
	
	public function beforeSave($options)
	{
		$this->data['ImmtrackCountry']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['ImmtrackCountry']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	
	function getList()
	{
		$countries = $this->find('list',
			array(
				'fields' => array('code','country')
			)
		);
		
		$countries = AppController::sanitizeHTML( $countries );
		
		return $countries;
	}
}

?>