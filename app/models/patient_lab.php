<?php

class PatientLab extends AppModel 
{
	public $name = 'PatientLab'; 
	public $primaryKey = 'PatientLabResultsID';
	public $useTable = 'patient_labs';
	private $data_separator = '.:|:.';
	
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
	private function searchItem($encounter_id, $patient_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientLab.encounter_id' => $encounter_id, 'PatientLab.patient_id' => $patient_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result;
		}
		else
		{
			return false;
		}
	}
	
	public function getAllItems($encounter_id, $patient_id)
	{
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		$ret = array();
		
		if($search_result)
		{
			
			/*$ret =$search_result['PatientLab']['LabDescription'];
			$ret =$search_result['PatientLab']['PatientLabResultsID'];*/
			array_push($ret, $search_result['PatientLab']['PatientLabResultsID']);
            array_push($ret, $search_result['PatientLab']['LabDescription']);
			
		}
		
		return $ret;
		//die($ret));
	}
	
	public function setItemValue($field, $value, $encounter_id, $patient_id, $user_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientLab.encounter_id' => $encounter_id, 'PatientLab.patient_id' => $patient_id)
				)
		);
		
		$data = array();
		
		if(!empty($search_result))
		{
			$data['PatientLab']['PatientLabResultsID'] = $search_result['PatientLab']['PatientLabResultsID'];
			$data['PatientLab']['patient_id'] = $search_result['PatientLab']['patient_id'];
			$data['PatientLab']['encounter_id'] = $search_result['PatientLab']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['PatientLab']['patient_id'] = $patient_id;
			$data['PatientLab']['encounter_id'] = $encounter_id;
		}
		
		$data['PatientLab']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientLab']['UpdateUserID'] = $user_id;
		$data['PatientLab'][$field] = $value;
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', $field) );
	}
	
	public function getItemValue($field, $encounter_id, $patient_id, $default_text = '')
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientLab.encounter_id' => $encounter_id, 'PatientLab.patient_id' => $patient_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result['PatientLab'][$field];
		}
		else
		{
			return $default_text;
		}
	}
}


?>