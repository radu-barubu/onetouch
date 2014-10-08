<?php

class FavoriteDiagnosis extends AppModel 
{
	public $name = 'FavoriteDiagnosis';
	public $primaryKey = 'diagnosis_id';
	public $useTable = 'favorite_diagnosis';

	public function beforeSave($options)
	{
		$this->data['FavoriteDiagnosis']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['FavoriteDiagnosis']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>