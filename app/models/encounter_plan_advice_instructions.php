<?php

class EncounterPlanAdviceInstructions extends AppModel 
{ 
	public $name = 'EncounterPlanAdviceInstructions'; 
	public $primaryKey = 'plan_advice_instructions_id';
	public $useTable = 'encounter_plan_advice_instructions';
	public $actAs = array('Containable');
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
	
	
	public function getAdvice( $encounter_id , &$plan, $combine = false)
	{
		$this->belongsTo = array();
		$data = $this->find( 'all', array(
				'conditions' => array('EncounterPlanAdviceInstructions.encounter_id' => $encounter_id)
			)
		);
		
		foreach($data as $v) {
			$v = $v['EncounterPlanAdviceInstructions'];
			
			$plan[$v['diagnosis']]['advice'] = $v;
			
			if ($combine) {
				$plan['combined']['advice'] = $v;
			}
			
		}
		
		if ($combine) {
			foreach ($plan as $diagnosis => $data) {
				if ($diagnosis == 'combined') {
					continue;
				}

				if (!isset($plan[$diagnosis]['advice'])) {
					continue;
				}						

				$plan[$diagnosis]['advice'] = $plan['combined']['advice'];
			}
		}			
			
		return $plan;
	}
	
	private function searchItem($encounter_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterPlanAdviceInstructions.encounter_id' => $encounter_id)
				)
		);
		
		if(!empty($search_result) )
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

		if ($diagnosis == 'all') {
			$list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			$diagnosis = $list[0]['EncounterAssessment']['diagnosis'];
			$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $list);
			$allDiagnosis = implode(', ', $diagnosisList);
			
		
			$search_results = $this->find(
					'all',
					array(
						'conditions' => array(
							'EncounterPlanAdviceInstructions.encounter_id' => $encounter_id, 
							'EncounterPlanAdviceInstructions.diagnosis' => $diagnosisList, 
			)));
			
			$searchMap = array();
			foreach ($search_results as $s) {
				$searchMap[$s['EncounterPlanAdviceInstructions']['diagnosis']] = $s;
			}
			
			foreach ($diagnosisList as $d) {
					$data = array();
				if (isset($searchMap[$d])) {
					$data = $searchMap[$d];
				} else {
					$this->create();
					$data['EncounterPlanAdviceInstructions']['encounter_id'] = $encounter_id;
					$data['EncounterPlanAdviceInstructions']['diagnosis'] = $d;
				}
				
				$data['EncounterPlanAdviceInstructions']['modified_timestamp'] = __date("Y-m-d H:i:s");
				$data['EncounterPlanAdviceInstructions']['modified_user_id'] = $user_id;
				$data['EncounterPlanAdviceInstructions'][$field] = $value;

				$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', 'diagnosis', $field) );			
			}			
		} else {
			$search_result = $this->find(
					'first', 
					array(
						'conditions' => array('EncounterPlanAdviceInstructions.encounter_id' => $encounter_id, 'EncounterPlanAdviceInstructions.diagnosis' => $diagnosis)
					)
			);

			$data = array();

			if(!empty($search_result))
			{
				$data['EncounterPlanAdviceInstructions']['plan_advice_instructions_id'] = $search_result['EncounterPlanAdviceInstructions']['plan_advice_instructions_id'];
				$data['EncounterPlanAdviceInstructions']['encounter_id'] = $search_result['EncounterPlanAdviceInstructions']['encounter_id'];
				$data['EncounterPlanAdviceInstructions']['diagnosis'] = $search_result['EncounterPlanAdviceInstructions']['diagnosis'];
			}
			else
			{
				$this->create();
				$data['EncounterPlanAdviceInstructions']['encounter_id'] = $encounter_id;
				$data['EncounterPlanAdviceInstructions']['diagnosis'] = $diagnosis;
			}

			$data['EncounterPlanAdviceInstructions']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['EncounterPlanAdviceInstructions']['modified_user_id'] = $user_id;
			$data['EncounterPlanAdviceInstructions'][$field] = $value;

			$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', 'diagnosis', $field) );			
		}
	}
	
	public function getItemValue($field, $encounter_id, $default_text = '')
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterPlanAdviceInstructions.encounter_id' => $encounter_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result['EncounterPlanAdviceInstructions'][$field];
		}
		else
		{
			return $default_text;
		}
	}
	
	public function execute(&$controller, $encounter_id, $patient_id, $diagnosis, $task, $user_id)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if ($controller->data['submitted']['id'] == 'action_date' or $controller->data['submitted']['id'] == 'date')
                    {
                        $controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
                    }
                    $controller->EncounterPlanAdviceInstructions->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, $controller->data['submitted']['diagnosis']);
                }
                exit;
            }
            break;
            default:
            {
                $demographic_items = $controller->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'contain' => array('PatientDemographic.gender','PatientDemographic.dob')));
                $gender = $demographic_items['PatientDemographic']['gender'];
                $controller->set('gender', $gender);
                
                $birthdate = $demographic_items['PatientDemographic']['dob'];
                $age_in_months = Charts::getAgeInMonth($birthdate);
                
                $age_in_years = ceil($age_in_months / 12);
                $controller->set('patient_age', isset($age_in_years) ? $age_in_years : '');
                
                // check to see if the patient is 13 years or older.  If yes, check smoking_status in patient_social_history table to see if it's empty.
                if ($age_in_years >= 13)
                {
                    $controller->loadModel("PatientSocialHistory");
                    $social_history_items = $controller->PatientSocialHistory->find('all', array('conditions' => array('PatientSocialHistory.patient_id' => $patient_id)));
                    $smoking_status_filled = 'no';
                    if (count($social_history_items) > 0)
                    {
                        foreach ($social_history_items as $social_history_item)
                        {
                            if ($social_history_item['PatientSocialHistory']['smoking_status'] != "")
                            {
                                $smoking_status_filled = 'yes';
                            }
                        }
                    }
                    
                    if ($smoking_status_filled == 'no')
                    {
                        $controller->set('smoking_status', 'empty');
                    }
                    else
                    {
                        $controller->set('smoking_status', 'not_empty');
                    }
                }
                
								if ($diagnosis == 'all') {
									$list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
									$diagnosis = $list[0]['EncounterAssessment']['diagnosis'];
								}
								
                $plan_instruction_items = $controller->EncounterPlanAdviceInstructions->find('first', array('conditions' => array('EncounterPlanAdviceInstructions.encounter_id' => $encounter_id, 'EncounterPlanAdviceInstructions.diagnosis' => $diagnosis)));
                if ($plan_instruction_items)
                {
                    $controller->set('PlanAdviceItem', $plan_instruction_items['EncounterPlanAdviceInstructions']);
                }
                $controller->loadModel("EncounterPlanHealthMaintenance");
                $health_maintenance_items = $controller->EncounterPlanHealthMaintenance->find('first', array('conditions' => array('EncounterPlanHealthMaintenance.encounter_id' => $encounter_id, 'EncounterPlanHealthMaintenance.diagnosis' => $diagnosis)));
                if ($health_maintenance_items)
                {
                    $controller->set('HealthMaintenanceItem', $health_maintenance_items['EncounterPlanHealthMaintenance']);
                }
            }
        }
		
	}
	
	public function generateCombined($encounterId) {
		$combined = array();
		
		$instructions = $this->find('all', array(
			'conditions' => array(
				'EncounterPlanAdviceInstructions.encounter_id' => $encounterId
			),
		));
		
		if (!$instructions) {
			return $combined;
		}
		
		foreach ($instructions as $f) {
			
			if (in_array($f['EncounterPlanAdviceInstructions']['patient_education_comment'], $combined)) {
				continue;
			}
			
			$combined[] = $f['EncounterPlanAdviceInstructions']['patient_education_comment'];
			
		}
		
		if (count($combined) > 1) {
			$combined = implode("\n", $combined);

			$this->updateAll(
				array(
					'EncounterPlanAdviceInstructions.patient_education_comment' => '\''.Sanitize::escape($combined) .'\''
				), 
				array(
					'EncounterPlanAdviceInstructions.encounter_id' => $encounterId,
				));
		} 
		
		$modelname = $this->name;
		$field = 'patient_education_comment';
		$idField = 'plan_advice_instructions_id';
		
		$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounterId);		
		$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);
		
		$plans = $this->find('all', array(
			'conditions' => array(
				$modelname . '.encounter_id' => $encounterId,
			),
		));
		
		
		$planMap = array();
		$uniqueData = array();
		
		
		foreach ($plans as $p) {
			$diagnosis = $p[$modelname]['diagnosis'];
			$data = $p[$modelname][$field];
			
			if (!isset($planMap[$diagnosis])) {
				$planMap[$diagnosis] = array();
			}
			
			if (!isset($planMap[$diagnosis][$data])) {
				$planMap[$diagnosis][$data] = $p;
			}
			
			if (!in_array($data, $uniqueData)) {
				$uniqueData[$data] = $p;
			}
		}

		// Fill up assessement diagnosis without plans
		foreach ($diagnosisList as $diagnosis) {
			if (!isset($planMap[$diagnosis])) {
				$planMap[$diagnosis] = array();
			}
		}
		
		foreach ($planMap as $diagnosis => $dataList) {
			$missing = array_diff(array_keys($uniqueData), array_keys($dataList));
			
			if (!$missing) {
				continue;
			}
			
			foreach ($missing as $m) {
				$copy = $uniqueData[$m];
				
				unset($copy[$modelname][$idField]);
				
				$copy[$modelname]['diagnosis'] = $diagnosis;
				
				$this->create();
				$this->save($copy);
			}
			
		}		
		
		
	}	
	
}


?>
