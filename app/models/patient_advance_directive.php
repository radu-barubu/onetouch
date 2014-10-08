<?php

class PatientAdvanceDirective extends AppModel 
{ 
	public $name = 'PatientAdvanceDirective'; 
	public $primaryKey = 'advance_directive_id';
	public $useTable = 'patient_advance_directives';
	
	public $actsAs = array(
		'Auditable' => 'General Information - Advance Directives',
		'Unique' => array('patient_id', 'directive_name')
	);
	
	public function beforeSave($options)
	{
		$this->data['PatientAdvanceDirective']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientAdvanceDirective']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>