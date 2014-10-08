<?php

class PracticeProfile extends AppModel 
{ 
	public $name = 'PracticeProfile'; 
	public $primaryKey = 'profile_id';
	public $useTable = 'practice_profile';
	
	public function beforeSave($options)
	{
		$this->data['PracticeProfile']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PracticeProfile']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	/**
    * Get current practice name
    * 
    * @return string practice name
    */
	public function getPracticeName()
	{
		$data = $this->find('first', array('fields' => array('PracticeProfile.practice_name'), 'conditions' => array('PracticeProfile.profile_id' => 1)));
		
		if($data)
		{
			return $data['PracticeProfile']['practice_name'];
		}
		
		return '';	
	}
}


?>