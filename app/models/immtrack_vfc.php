<?php

class ImmtrackVfc extends AppModel 
{
	public $name = 'ImmtrackVfc';
	public $primaryKey = 'immtrack_vfc_id';
	public $useTable = 'immtrack_vfc';
	
	public function beforeSave($options)
	{
		$this->data['ImmtrackVfc']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['ImmtrackVfc']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>