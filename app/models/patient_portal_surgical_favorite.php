<?php

class PatientPortalSurgicalFavorite extends AppModel 
{
	public $name = 'PatientPortalSurgicalFavorite';
	public $primaryKey = 'surgeries_id';

	public function beforeSave($options)
	{
		$this->data['PatientPortalSurgicalFavorite']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientPortalSurgicalFavorite']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>
