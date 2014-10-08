<?php
class PatientNote extends AppModel 
{ 
	public $name = 'PatientNote'; 
	public $primaryKey = 'note_id';
	public $useTable = 'patient_notes';
	
	public $actsAs = array(
		'Auditable' => 'Attachments - Notes',
		'Unique' => array('patient_id', 'subject')
	);
	
	public function beforeSave($options)
	{
		$this->data['PatientNote']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientNote']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}	
}

?>