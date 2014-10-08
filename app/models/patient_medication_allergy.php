<?php

class PatientMedicationAllergy extends AppModel 
{ 
	public $name = 'PatientMedicationAllergy'; 
	public $primaryKey = 'PatientMedicationAllergyID';
	public $useTable = 'patient_medication_allergies';
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
					'conditions' => array('PatientMedicationAllergy.encounter_id' => $encounter_id, 'PatientMedicationAllergy.patient_id' => $patient_id)
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
	
	
	public function addAllergyItem($item_value, $encounter_id, $patient_id, $user_id)
	{
		$data = array();
		$Allergies_arr = array();
		
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		if($search_result)
		{
			$data['PatientMedicationAllergy']['PatientMedicationAllergyID'] = $search_result['PatientMedicationAllergy']['PatientMedicationAllergyID'];
			$data['PatientMedicationAllergy']['patient_id'] = $search_result['PatientMedicationAllergy']['patient_id'];
			$data['PatientMedicationAllergy']['encounter_id'] = $search_result['PatientMedicationAllergy']['encounter_id'];
			$Allergies_arr = explode($this->data_separator, $search_result['PatientMedicationAllergy']['Allergies']);
		}
		else
		{
			$this->create();
			$data['PatientMedicationAllergy']['patient_id'] = $patient_id;
			$data['PatientMedicationAllergy']['encounter_id'] = $encounter_id;
		}
		
		if(!in_array($item_value, $Allergies_arr))
		{
			$Allergies_arr[] = $item_value;
		}

		$data['PatientMedicationAllergy']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientMedicationAllergy']['UpdateUserID'] = $user_id;
		$data['PatientMedicationAllergy']['Allergies'] = implode($this->data_separator, $Allergies_arr);
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', 'Allergies') );
	}
		
	public function deleteAllergyItem($itemvalue, $encounter_id, $patient_id, $user_id)
	{
		$data = array();
		$Allergies_arr = array();
		
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		if($search_result)
		{
			$data['PatientMedicationAllergy']['PatientMedicationAllergyID'] = $search_result['PatientMedicationAllergy']['PatientMedicationAllergyID'];
			$data['PatientMedicationAllergy']['patient_id'] = $search_result['PatientMedicationAllergy']['patient_id'];
			$data['PatientMedicationAllergy']['encounter_id'] = $search_result['PatientMedicationAllergy']['encounter_id'];
			$Allergies_arr = explode($this->data_separator, $search_result['PatientMedicationAllergy']['Allergies']);
		}
		
		$new_Allergies_arr = array();
		
		foreach($Allergies_arr as $Allergies_item)
		{
			if($Allergies_item != $itemvalue)
			{
				$new_Allergies_arr[] = $Allergies_item;
			}
		}

		$data['PatientMedicationAllergy']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientMedicationAllergy']['UpdateUserID'] = $user_id;
		$data['PatientMedicationAllergy']['Allergies'] = implode($this->data_separator, $new_Allergies_arr);
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', 'Allergies') );
	}
	
	public function addMedicationItem($item_value, $encounter_id, $patient_id, $user_id)
	{
		$data = array();
		$Medications_arr = array();
		
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		if($search_result)
		{
			$data['PatientMedicationAllergy']['PatientMedicationAllergyID'] = $search_result['PatientMedicationAllergy']['PatientMedicationAllergyID'];
			$data['PatientMedicationAllergy']['patient_id'] = $search_result['PatientMedicationAllergy']['patient_id'];
			$data['PatientMedicationAllergy']['encounter_id'] = $search_result['PatientMedicationAllergy']['encounter_id'];
			$Medications_arr = explode($this->data_separator, $search_result['PatientMedicationAllergy']['Medications']);
		}
		else
		{
			$this->create();
			$data['PatientMedicationAllergy']['patient_id'] = $patient_id;
			$data['PatientMedicationAllergy']['encounter_id'] = $encounter_id;
		}
		
		if(!in_array($item_value, $Medications_arr))
		{
			$Medications_arr[] = $item_value;
		}

		$data['PatientMedicationAllergy']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientMedicationAllergy']['UpdateUserID'] = $user_id;
		$data['PatientMedicationAllergy']['Medications'] = implode($this->data_separator, $Medications_arr);
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', 'Medications') );
	}
	
	public function getAllMedicationItems($encounter_id, $patient_id)
	{
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		$ret = array();
		
		if($search_result)
		{
			$ret = explode($this->data_separator, $search_result['PatientMedicationAllergy']['Medications']);
		}
		
		return $ret;
	}
		
	public function deleteMedicationItem($itemvalue, $encounter_id, $patient_id, $user_id)
	{
		$data = array();
		$Medications_arr = array();
		
		$search_result = $this->searchItem($encounter_id, $patient_id);
		
		if($search_result)
		{
			$data['PatientMedicationAllergy']['PatientMedicationAllergyID'] = $search_result['PatientMedicationAllergy']['PatientMedicationAllergyID'];
			$data['PatientMedicationAllergy']['patient_id'] = $search_result['PatientMedicationAllergy']['patient_id'];
			$data['PatientMedicationAllergy']['encounter_id'] = $search_result['PatientMedicationAllergy']['encounter_id'];
			$Medications_arr = explode($this->data_separator, $search_result['PatientMedicationAllergy']['Medications']);
		}
		
		$new_Medications_arr = array();
		
		foreach($Medications_arr as $Medications_item)
		{
			if($Medications_item != $itemvalue)
			{
				$new_Medications_arr[] = $Medications_item;
			}
		}

		$data['PatientMedicationAllergy']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientMedicationAllergy']['UpdateUserID'] = $user_id;
		$data['PatientMedicationAllergy']['Medications'] = implode($this->data_separator, $new_Medications_arr);
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', 'Medications') );
	}
	
	public function setItemValue($field, $value, $encounter_id, $patient_id, $user_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientMedicationAllergy.encounter_id' => $encounter_id, 'PatientMedicationAllergy.patient_id' => $patient_id)
				)
		);
		
		$data = array();
		
		if(!empty($search_result))
		{
			$data['PatientMedicationAllergy']['PatientMedicationAllergyID'] = $search_result['PatientMedicationAllergy']['PatientMedicationAllergyID'];
			$data['PatientMedicationAllergy']['patient_id'] = $search_result['PatientMedicationAllergy']['patient_id'];
			$data['PatientMedicationAllergy']['encounter_id'] = $search_result['PatientMedicationAllergy']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['PatientMedicationAllergy']['patient_id'] = $patient_id;
			$data['PatientMedicationAllergy']['encounter_id'] = $encounter_id;
		}
		
		$data['PatientMedicationAllergy']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['PatientMedicationAllergy']['UpdateUserID'] = $user_id;
		$data['PatientMedicationAllergy'][$field] = $value;
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'encounter_id', $field) );
	}
	
	public function getItemValue($field, $encounter_id, $patient_id, $default_text = '')
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PatientMedicationAllergy.encounter_id' => $encounter_id, 'PatientMedicationAllergy.patient_id' => $patient_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result['PatientMedicationAllergy'][$field];
		}
		else
		{
			return $default_text;
		}
	}
}


?>