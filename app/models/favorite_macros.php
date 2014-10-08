<?php

class FavoriteMacros extends AppModel 
{
	public $name = 'FavoriteMacros';
	public $primaryKey = 'macro_id';
	public $useTable = 'favorite_macros';

	public function beforeSave($options)
	{
		$this->data['FavoriteMacros']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['FavoriteMacros']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>