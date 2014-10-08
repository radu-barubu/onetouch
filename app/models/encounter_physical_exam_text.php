<?php

class EncounterPhysicalExamText extends AppModel 
{ 
	public $name = 'EncounterPhysicalExamText'; 
	public $primaryKey = 'text_id';
	public $useTable = 'encounter_physical_exam_texts';
	
	public $belongsTo = array(
		'EncounterPhysicalExam' => array(
			'className' => 'EncounterPhysicalExam',
			'foreignKey' => 'physical_exam_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['EncounterPhysicalExamText']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EncounterPhysicalExamText']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
}

?>