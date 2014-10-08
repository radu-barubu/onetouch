<?php

class EncounterPlanStatus extends AppModel 
{ 
	public $name = 'EncounterPlanStatus'; 
	public $primaryKey = 'plan_status_id';
	public $useTable = 'encounter_plan_status';
	
	public $belongsTo = array(
			'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	public function getItemsByPatient($patient_id)
	{
		$search_results = $this->find('all', 
			array(
				'conditions' => array('EncounterMaster.patient_id' => $patient_id)
			)
		);
		
		return $search_results;
	}
	
	public function beforeSave($options)
	{
		$this->data['EncounterPlanStatus']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EncounterPlanStatus']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	private function getItem($encounter_id, $diagnosis)
	{
		$item = $this->find('first', array('conditions' => array('EncounterPlanStatus.encounter_id' => $encounter_id, 'EncounterPlanStatus.diagnosis' => $diagnosis)));
		return $item;
	}
	
	public function saveStatus($encounter_id, $diagnosis, $status)
	{
		$item = $this->getItem($encounter_id, $diagnosis);
		
		$data = array();
		
		if($item)
		{
			$data['EncounterPlanStatus']['plan_status_id'] = $item['EncounterPlanStatus']['plan_status_id'];
		}
		else
		{
			$data['EncounterPlanStatus']['encounter_id'] = $encounter_id;
			$data['EncounterPlanStatus']['diagnosis'] = $diagnosis;
		}
		
		$data['EncounterPlanStatus']['status'] = $status;
		
		$this->save($data);
	}
	
	public function getStatus($encounter_id, $diagnosis)
	{
		$item = $this->getItem($encounter_id, $diagnosis);
		
		if($item)
		{
			return $item['EncounterPlanStatus']['status'];
		}
		else
		{
			return 'New';
		}
	}
}

?>