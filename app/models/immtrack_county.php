<?php

class ImmtrackCounty extends AppModel 
{
	public $name = 'ImmtrackCounty';
	public $primaryKey = 'immtrack_county_id';
	public $useTable = 'immtrack_county';
	
	public function beforeSave($options)
	{
		$this->data['ImmtrackCounty']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['ImmtrackCounty']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>