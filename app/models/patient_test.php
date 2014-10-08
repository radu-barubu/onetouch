<?php

class PatientTest extends AppModel 
{ 
	public $name = 'PatientTest'; 
	public $primaryKey = 'PatientTestID';
	public $useTable = 'patient_tests';
	
	public $belongsTo = array(
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		),
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	public function setItemValue($field, $value, $encounter_id, $patient_id, $user_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientTest.encounter_id' => $encounter_id, 'PatientTest.patient_id' => $patient_id)
				)
		);
		
		$data = array();
		
		if(!empty($search_result))
		{
			$data['PatientTest']['PatientTestID'] = $search_result['PatientTest']['PatientTestID'];
			$data['PatientTest']['patient_id'] = $search_result['PatientTest']['patient_id'];
			$data['PatientTest']['encounter_id'] = $search_result['PatientTest']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['PatientTest']['patient_id'] = $patient_id;
			$data['PatientTest']['encounter_id'] = $encounter_id;
		}
		
		$data['PatientTest']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientTest']['UpdateUserID'] = $user_id;
		$data['PatientTest'][$field] = $value;
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', $field) );
	}
	
	public function getItemValue($field, $encounter_id, $patient_id, $default_text = '')
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientTest.encounter_id' => $encounter_id, 'PatientTest.patient_id' => $patient_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result['PatientTest'][$field];
		}
		else
		{
			return $default_text;
		}
	}
}


?>