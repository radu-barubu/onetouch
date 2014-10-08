<?php

class PatientDisclosure extends AppModel 
{ 
	public $name = 'PatientDisclosure'; 
	public $primaryKey = 'disclosure_id';
	public $useTable = 'patient_disclosure';
	
	public $actsAs = array(
		'Auditable' => 'General Information - Disclosure Records',
	);
	
	public function beforeSave($options)
	{
		// Following should really be fixed in the sql table definition
		if( empty( $this->data[$this->alias]['visit_time_count'] ))
			$this->data[$this->alias]['visit_time_count'] = 0;
		
		$this->data['PatientDisclosure']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientDisclosure']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>