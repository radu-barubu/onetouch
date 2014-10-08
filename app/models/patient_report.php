<?php

class PatientReport extends AppModel 
{ 
	var $name = 'PatientReport'; 
	var $primaryKey = 'ID';
	var $useTable = 'patient_reports';
	
	var $belongsTo = array(
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
					'conditions' => array('PatientReport.encounter_id' => $encounter_id, 'PatientReport.patient_id' => $patient_id)
				)
		);
		
		$data = array();
		
		if(!empty($search_result))
		{
			$data['PatientReport']['ID'] = $search_result['PatientReport']['ID'];
			$data['PatientReport']['patient_id'] = $search_result['PatientReport']['patient_id'];
			$data['PatientReport']['encounter_id'] = $search_result['PatientReport']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['PatientReport']['patient_id'] = $patient_id;
			$data['PatientReport']['encounter_id'] = $encounter_id;
		}
		
		$data['PatientReport'][$field] = $value;
		
		$this->save($data, false, array('patient_id', 'encounter_id', $field) );
	}
	
	public function getItemValue($field, $encounter_id, $patient_id, $default_text = '')
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientReport.encounter_id' => $encounter_id, 'PatientReport.patient_id' => $patient_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result['PatientReport'][$field];
		}
		else
		{
			return $default_text;
		}
	}
}


?>