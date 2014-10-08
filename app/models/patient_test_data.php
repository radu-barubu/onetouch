<?php

class PatientTestData extends AppModel 
{ 
	public $name = 'PatientTestData'; 
	public $primaryKey = 'PatientTestID';
	public $useTable = 'patient_test_data';
	
	public $belongsTo = array(
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		)
	);
	
	public function getTestRecordDetails($id, $patient_id)
	{
		 $search_result = $this->find('all', array('conditions' => array('PatientTestData.id' => $id, 'PatientTestData.patient_id' => $patient_id)));
         return $search_result;
	}
	
	public function getPatientPreviousTestRecords($patient_id)
	{
		 $search_result = $this->find('all', array('conditions' => array('PatientTestData.date <' => 'current_date()', 'PatientTestData.patient_id' => $patient_id)));
         return $search_result;
	}
	
	public function setItemValue($field, $value, $encounter_id, $patient_id, $user_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientTestData.encounter_id' => $encounter_id, 'PatientTestData.patient_id' => $patient_id)
				)
		);
		
		$data = array();
		
		if(!empty($search_result))
		{
			$data['PatientTestData']['PatientTestID'] = $search_result['PatientTestData']['PatientTestID'];
			$data['PatientTestData']['patient_id'] = $search_result['PatientTestData']['patient_id'];
			$data['PatientTestData']['encounter_id'] = $search_result['PatientTestData']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['PatientTestData']['patient_id'] = $patient_id;
			$data['PatientTestData']['encounter_id'] = $encounter_id;
		}
		
		$data['PatientTestData']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientTestData']['UpdateUserID'] = $user_id;
		$data['PatientTestData'][$field] = $value;
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', $field) );
	}
	
	public function getItemValue($field, $encounter_id, $patient_id, $default_text = '')
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientTestData.encounter_id' => $encounter_id, 'PatientTestData.patient_id' => $patient_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result['PatientTestData'][$field];
		}
		else
		{
			return $default_text;
		}
	}
}


?>