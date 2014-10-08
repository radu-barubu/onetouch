<?php

class FavoriteSurgeries extends AppModel 
{
	public $name = 'FavoriteSurgeries';
	public $primaryKey = 'surgeries_id';
	public $useTable = 'favorite_surgeries';
	public $actsAs = array(
		'Unique' => array('user_id', 'surgeries')
	);

	public function beforeSave($options)
	{
		$this->data['FavoriteSurgeries']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['FavoriteSurgeries']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>