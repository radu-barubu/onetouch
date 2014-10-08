<?php

class EncounterPlanHealthMaintenance extends AppModel 
{ 
	public $name = 'EncounterPlanHealthMaintenance'; 
	public $primaryKey = 'health_maintenance_id';
	public $useTable = 'encounter_plan_health_maintenance';
	
	public $actsAs = array('Auditable' => 'Medical Information - Health Maintenance');
	
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
	
	public function getDiagnosis($encounter_id, &$plan)
	{
		$this->belongsTo = array();
		
		$data = $this->find('all',  array(
			'conditions' => array('EncounterPlanHealthMaintenance.encounter_id' => $encounter_id))
		);
		
		
		foreach( $data as $v ) {
			$v = $v['EncounterPlanHealthMaintenance'];
			
			if(isset($plan[$v['diagnosis']]) && isset($plan[$v['diagnosis']]['advice'])) {
				$v = array_merge( $v, $plan[$v['diagnosis']]['advice']);
			}
			$plan[$v['diagnosis']]['advice'] = $v;
		}
		
		return $plan;
	}
	
	private function searchItem($encounter_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterPlanHealthMaintenance.encounter_id' => $encounter_id)
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
	
	public function setItemValue($field, $value, $encounter_id, $user_id, $diagnosis)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterPlanHealthMaintenance.encounter_id' => $encounter_id, 'EncounterPlanHealthMaintenance.diagnosis' => $diagnosis)
				)
		);
		
		$data = array();
		
		if(!empty($search_result))
		{
			$data['EncounterPlanHealthMaintenance']['health_maintenance_id'] = $search_result['EncounterPlanHealthMaintenance']['health_maintenance_id'];
			$data['EncounterPlanHealthMaintenance']['encounter_id'] = $search_result['EncounterPlanHealthMaintenance']['encounter_id'];
			$data['EncounterPlanHealthMaintenance']['diagnosis'] = $search_result['EncounterPlanHealthMaintenance']['diagnosis'];
		}
		else
		{
			$this->create();
			$data['EncounterPlanHealthMaintenance']['encounter_id'] = $encounter_id;
			$data['EncounterPlanHealthMaintenance']['diagnosis'] = $diagnosis;
		}
		
		$data['EncounterPlanHealthMaintenance']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterPlanHealthMaintenance']['modified_user_id'] = $user_id;
		$data['EncounterPlanHealthMaintenance'][$field] = $value;
		
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', 'diagnosis', $field) );
	}
	
	public function getItemValue($field, $encounter_id, $default_text = '')
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterPlanHealthMaintenance.encounter_id' => $encounter_id)
				)
		);
		
		if(!empty($search_result) )
		{
			return $search_result['EncounterPlanHealthMaintenance'][$field];
		}
		else
		{
			return $default_text;
		}
	}
	
	public function execute(&$controller, $encounter_id, $diagnosis, $task, $user_id)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if ($controller->data['submitted']['id'] == 'action_date' or $controller->data['submitted']['id'] == 'signup_date')
                    {
                        $controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
                    }
                    $controller->EncounterPlanHealthMaintenance->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, $controller->data['submitted']['diagnosis']);
                }
                exit;
            }
            break;
            default:
            {
                $health_maintenance_items = $controller->EncounterPlanHealthMaintenance->find('first', array('conditions' => array('EncounterPlanHealthMaintenance.encounter_id' => $encounter_id, 'EncounterPlanHealthMaintenance.diagnosis' => $diagnosis)));
                if ($health_maintenance_items)
                {
                    $controller->set('HealthMaintenanceItem', $health_maintenance_items['EncounterPlanHealthMaintenance']);
                }
            }
        }
		
	}
}


?>