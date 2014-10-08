<?php

class Superbill extends AppModel 
{ 
	public $name = 'Superbill'; 
	public $primaryKey = 'SuperBillID';
	public $useTable = 'superbill';
    
    var $belongsTo = array(
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		),
		/*'EncounterMaster' => array(
			'className' => 'Encounter',
			'foreignKey' => 'EncounterID'
		)*/
	);
	
    private function searchItem($encounter_id, $patient_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('Superbill.EncounterID' => $encounter_id, 'Superbill.patient_id' => $patient_id)
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
			
			array_push($ret, $search_result['Superbill']['SuperbillResultsID']);
            array_push($ret, $search_result['Superbill']['LabDescription']);
			
		}
		
		return $ret;
		//die($ret));
	}
	
	public function setItemValue($field, $value, $encounter_id, $patient_id, $user_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('Superbill.EncounterID' => $encounter_id, 'Superbill.patient_id' => $patient_id)
				)
		);
		
		$data = array();
		
		if(!empty($search_result))
		{
			$data['Superbill']['SuperBillID'] = $search_result['Superbill']['SuperBillID'];
			$data['Superbill']['patient_id'] = $search_result['Superbill']['patient_id'];
			$data['Superbill']['EncounterID'] = $search_result['Superbill']['EncounterID'];
		}
		else
		{
			$this->create();
			$data['Superbill']['patient_id'] = $patient_id;
			$data['Superbill']['EncounterID'] = $encounter_id;
		}
		
		$data['Superbill']['UpdateDate'] = __date("Y-m-d H:i:s");
		$data['Superbill']['UpdateUserID'] = $user_id;
		$data['Superbill'][$field] = $value;
		
		$this->save($data, false, array('UpdateDate','UpdateUserID', 'patient_id', 'EncounterID', $field) );
	}
	
	public function getItemValue($field, $encounter_id, $patient_id, $default_text = '')
	{
		$search_result = $this->find(
				'all', 
				array(
					'conditions' => array('Superbill.EncounterID' => $encounter_id, 'Superbill.patient_id' => $patient_id)
				)
		);
		
		if(count($search_result) > 0)
		{
			return $search_result[0]['Superbill'][$field];
		}
		else
		{
			return $default_text;
		}
	}
}


?>