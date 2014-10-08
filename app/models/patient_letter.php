<?php
class PatientLetter extends AppModel 
{ 
	public $name = 'PatientLetter'; 
	public $primaryKey = 'letter_id';
	public $useTable = 'patient_letters';
	
	public $actsAs = array(
		'Auditable' => 'Attachments - Letters',
		'Unique' => array('patient_id', 'patient_name', 'subject')
	);
	
	public function beforeSave($options)
	{
		$this->data['PatientLetter']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientLetter']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}	
}

?>