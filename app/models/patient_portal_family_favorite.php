<?php

class PatientPortalFamilyFavorite extends AppModel 
{
	public $name = 'PatientPortalFamilyFavorite';
	public $primaryKey = 'family_favorite_id';
	//public $useTable = ''; use cakephp convention

	public function beforeSave($options)
	{
		$this->data['PatientPortalFamilyFavorite']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientPortalFamilyFavorite']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}


}

?>
