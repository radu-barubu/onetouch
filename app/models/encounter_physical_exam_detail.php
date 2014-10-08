<?php

class EncounterPhysicalExamDetail extends AppModel 
{ 
	public $name = 'EncounterPhysicalExamDetail'; 
	public $primaryKey = 'details_id';
	public $useTable = 'encounter_physical_exam_details';
	
	public $belongsTo = array(
		'EncounterPhysicalExam' => array(
			'className' => 'EncounterPhysicalExam',
			'foreignKey' => 'physical_exam_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['EncounterPhysicalExamDetail']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EncounterPhysicalExamDetail']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>