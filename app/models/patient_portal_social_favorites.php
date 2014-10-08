<?php

class PatientPortalSocialFavorites extends AppModel 
{
	public $name = 'PatientPortalSocialFavorites';
	public $primaryKey = 'social_favorite_id';
	public $useTable = 'patient_portal_social_favorites';

	public function beforeSave($options)
	{
		$this->data['PatientPortalSocialFavorites']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientPortalSocialFavorites']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}


}

?>
