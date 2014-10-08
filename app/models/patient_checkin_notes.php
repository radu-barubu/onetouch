<?php

class PatientCheckinNotes extends AppModel 
{
	public $name = 'PatientCheckinNotes';
	public $primaryKey = 'patient_checkin_id';
	public $useTable = 'patient_checkin_notes';

	var $belongsTo = array(
		'UserAccount' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'modified_user_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['PatientCheckinNotes']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientCheckinNotes']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	

}

?>