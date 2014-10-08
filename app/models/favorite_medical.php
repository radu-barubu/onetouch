<?php

class FavoriteMedical extends AppModel 
{
	public $name = 'FavoriteMedical';
	public $primaryKey = 'diagnosis_id';
	public $useTable = 'favorite_medical';

	public function beforeSave($options)
	{
		$this->data['FavoriteMedical']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['FavoriteMedical']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>
