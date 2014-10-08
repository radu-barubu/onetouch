<?php

class PreferencesAccount extends AppModel 
{ 
	public $name = 'PreferencesAccount';
	public $primaryKey = 'preferences_account_id';
	public $useTable = 'preferences_account';
	
	public function beforeSave($options)
	{
		$this->data['PreferencesAccount']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PreferencesAccount']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	private function searchData($user_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PreferencesAccount.user_id' => $user_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result;
		}
		else
		{
			return false;
		}
	}
	
	public function saveData($data)
	{
		$this->save($data);
	}
	
	public function getData($user_id)
	{
		$search_result = $this->searchData($user_id);
		
		if($search_result)
		{
			return $search_result;
		}
		else
		{
			$this->create();
			$data = array();
			$data['PreferencesAccount']['user_id'] = $user_id;
			$this->save($data);
			
			return $this->getData($user_id);
		}
	}
}

?>