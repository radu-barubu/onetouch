<?php

class Encounter extends AppModel 
{ 
    public $name = 'Encounter'; 
	public $primaryKey = 'encounter_id';
	
	public $belongsTo = array(
		'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		),
		'UserAccount' => array(
			'className' => 'UserAccount',
			'foreignKey' => 'modified_user_id'
		)
	);
	
	public function getEncounter($calendar_id, $patient_id, $user_id)
	{
		$items = $this->find(
				'first', 
				array(
					'conditions' => array('Encounter.calendar_id' => $calendar_id)
				)
		);
		
		if(!empty($items))
		{
			return $items['Encounter']['encounter_id'];
			
		}
		else
		{
			$data = array();
			$data['Encounter']['patient_id'] = $patient_id;
			$data['Encounter']['calendar_id'] = $calendar_id;
			$data['Encounter']['encounter_date'] = __date("Y-m-d H:i:s");
			$data['Encounter']['created'] = time();
			$data['Encounter']['encounter_status'] = 'Open';
			$data['Encounter']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['Encounter']['modified_user_id'] = $user_id;
			
			$this->create();
			$this->save($data);
			
			return $this->getLastInsertID();
		}
	}
	
	public function getPatientID($encounter_id)
	{
		$items = $this->find(
				'first', 
				array(
					'conditions' => array('Encounter.encounter_id' => $encounter_id)
				)
		);
		
		if(!empty($items))
		{
			return $items['Encounter']['patient_id'];
		}
		else
		{
			return false;
		}
	}
	
	function getLastQuery()
	{
		$dbo = $this->getDatasource();
		$logs = $dbo->_queriesLog;
	
		return end($logs);
	}
	
	public function getPatientEncounterCount($patient_id)
	{
		$items = $this->find(
				'count', 
				array(
					'conditions' => array('Encounter.patient_id' => $patient_id)
				)
		);
		
		if($items > 0)
		{
			return $items;
		}
		else
		{
			return false;
		}
	}
	
	public function setItemValue($field, $value, $encounter_id, $patient_id, $user_id)
	{ 
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('Encounter.encounter_id' => $encounter_id, 'Encounter.patient_id' => $patient_id)
				)
		);
		
		$data = array();
		
		if(!empty($search_result))
		{
			$data['Encounter']['encounter_id'] = $search_result['Encounter']['encounter_id'];
			$data['Encounter']['patient_id'] = $search_result['Encounter']['patient_id'];
		}
		else
		{
			$this->create();
			$data['Encounter']['patient_id'] = $patient_id;
			$data['Encounter']['encounter_id'] = $encounter_id;
		}
		
		$data['Encounter']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['Encounter']['modified_user_id'] = $user_id;
		$data['Encounter'][$field] = $value;
		
		$this->save($data, false, array('modified','user_id', 'patient_id', 'encounter_id', $field) );
	}
}


?>