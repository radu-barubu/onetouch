<?php

class PatientPortalMedicalFavorite extends AppModel 
{
	public $name = 'PatientPortalMedicalFavorite';
	public $primaryKey = 'diagnosis_id';

	public function beforeSave($options)
	{
		$this->data['PatientPortalMedicalFavorite']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientPortalMedicalFavorite']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>
