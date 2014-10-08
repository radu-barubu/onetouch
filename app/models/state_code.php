<?php

class StateCode extends AppModel 
{ 
	public $name = 'StateCode'; 
	public $primaryKey = 'state_code_id';
	public $useTable = 'state_codes';
	
	function getList()
	{
		$states = $this->find('list',
			array(
				'fields' => array('state','fullname')
			)
		);
		
		$states = AppController::sanitizeHTML( $states );
		
		return $states;
	}

	function getStateCode($name)
	{
		return $this->find('first', array('conditions' => array('fullname' => $name)));
	}
	function getStateNameFromCode($code)
	{
		$o= $this->find('first', array('conditions' => array('state' => $code)));
		return $o;
	}
}

?>
