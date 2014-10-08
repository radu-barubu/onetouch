<?php

class FavoriteLabTest extends AppModel 
{
	public $name = 'FavoriteLabTest';
	public $primaryKey = 'lab_test_id';
	public $useTable = 'favorite_lab_tests';
	
	public function beforeSave($options)
	{
		$this->data['FavoriteLabTest']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['FavoriteLabTest']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>