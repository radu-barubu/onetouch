<?php

class EncounterPlanRx extends AppModel 
{ 
	public $name = 'EncounterPlanRx'; 
	public $primaryKey = 'plan_rx_id';
	public $useTable = 'encounter_plan_rx';
	
	public $belongsTo = array(
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	public function getItemsByPatient($patient_id)
	{
		$options['conditions'] = array('EncounterMaster.patient_id' => $patient_id);
		$options['fields'] = array('EncounterPlanRx.*', 'DES_DECRYPT(PatientDemo.first_name) as patient_firstname', 'DES_DECRYPT(PatientDemo.last_name) as patient_lastname','PatientDemo.patient_id');
		$options['order'] = array('EncounterPlanRx.date_ordered DESC');
		$options['joins'] = array(					
					array(
						'table' => 'encounter_master'
						, 'type' => 'INNER'
						, 'alias' => 'EncounterMastr'
						, 'conditions' => array(
						'EncounterPlanRx.encounter_id = EncounterMastr.encounter_id'
						)
					),
					array(
						'table' => 'patient_demographics',
						'alias' => 'PatientDemo',
						'type' => 'INNER',
						'conditions' => array(
								"EncounterMastr.patient_id = PatientDemo.patient_id AND PatientDemo.patient_id = $patient_id"
							)
					)
			);
		$search_results = $this->find('all' , $options);		
		return $search_results;
	}
	
	public function getDiagnosis ($encounter_id, &$drug = array(), $combine = false)
	{
		$this->belongsTo = array();
		
		$params = array(
			'conditions' => array('EncounterPlanRx.encounter_id' => $encounter_id)
		);
		
		if ($combine) {
			$params['group'] = array('EncounterPlanRx.drug');
		}
		
		$rx_items = $this->find('all', $params);
		
		foreach($rx_items as $v) {
			$v = $v['EncounterPlanRx'];
			
			$drug[$v['diagnosis']]['rx']['items'][] = $v['drug'];
			$drug[$v['diagnosis']]['rx'][$v['drug']] = $v;
			
			if ($combine) {
				$drug['combined']['rx']['items'][] = $v['drug'];
				$drug['combined']['rx'][$v['drug']] = $v;
			}			
			
		}
		
		if ($combine) {
			foreach ($drug as $diagnosis => $data) {
				if ($diagnosis == 'combined') {
					continue;
				}
				
				if (!isset($plan[$diagnosis]['rx'])) {
					continue;
				}				
				
				$drug[$diagnosis]['rx'] = $drug['combined']['rx'];
			}
			
			
			
		}		
		
		return $drug;
	}

	public function getDrugs($encounter_id, $diagnosis)
	{
		
		if ($diagnosis == 'all') {
			$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			
			$diagnosis = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);
			$rx_items = $this->find('all', array(
				'conditions' => array('EncounterPlanRx.encounter_id' => $encounter_id, 'EncounterPlanRx.diagnosis' => $diagnosis),
				'group' => array('EncounterPlanRx.drug'),
				));
		} else {
			$rx_items = $this->find('all', array('conditions' => array('EncounterPlanRx.encounter_id' => $encounter_id, 'EncounterPlanRx.diagnosis' => $diagnosis)));
			
		}
		
		$drug_array = array();
			
		foreach($rx_items as $rx_item)
		{
			$drug_array[] = $rx_item['EncounterPlanRx']['drug'];
		}
		return $drug_array;
	}
	
	private function searchItem($encounter_id, $diagnosis, $drug)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterPlanRx.encounter_id' => $encounter_id, 'EncounterPlanRx.diagnosis' => $diagnosis, 'EncounterPlanRx.drug' => $drug)
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
	
	public function setItemValue($field, $value, $encounter_id, $user_id, $diagnosis, $drug)
	{
		
		if ($diagnosis == 'all') {
			$list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			$diagnosis = $list[0]['EncounterAssessment']['diagnosis'];
			$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $list);

			$search_results = $this->find(
					'all', 
					array(
						'conditions' => array(
							'EncounterPlanRx.encounter_id' => $encounter_id, 
							'EncounterPlanRx.diagnosis' => $diagnosisList, 
							'EncounterPlanRx.drug' => $drug)
					)
			);			
			
			$searchMap = array();
			foreach ($search_results as $s) {
				$searchMap[$s['EncounterPlanRx']['diagnosis']] = $s;
			}
			
			foreach ($diagnosisList as $d) {
				if (isset($searchMap[$d])) {
					$searchMap[$d]['EncounterPlanRx'][$field] = $value;
					$this->save($searchMap[$d]);
				} else {

					$data = array();
					$this->create();
					$data['EncounterPlanRx']['encounter_id'] = $encounter_id;
					$data['EncounterPlanRx']['diagnosis'] = $d;
					$data['EncounterPlanRx']['drug'] = $drug;
					$data['EncounterPlanRx']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$data['EncounterPlanRx']['modified_user_id'] = $user_id;
					$data['EncounterPlanRx'][$field] = $value;
					$this->save($data);
				}
			}			
			
			
		} else {
			$search_result = $this->find(
					'first', 
					array(
						'conditions' => array('EncounterPlanRx.encounter_id' => $encounter_id, 'EncounterPlanRx.diagnosis' => $diagnosis, 'EncounterPlanRx.drug' => $drug)
					)
			);



			if(!empty($search_result))
			{
				$data = array();
				$data['EncounterPlanRx']['plan_rx_id'] = $search_result['EncounterPlanRx']['plan_rx_id'];
				$data['EncounterPlanRx']['modified_timestamp'] = __date("Y-m-d H:i:s");
				$data['EncounterPlanRx']['modified_user_id'] = $user_id;
				$data['EncounterPlanRx'][$field] = $value;
				$this->save($data);
			}			
		}
		
	}
		
	public function addItem($item_value, $rxnorm, $encounter_id, $user_id, $diagnosis, $dataPlanRx=array())
	{
		
		if ($diagnosis == 'all') {
			$list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			
			foreach ($list as $l) {
				$diagnosis = $l['EncounterAssessment']['diagnosis'];
				
				if ($diagnosis == 'all') {
					continue;
				}
				
				$this->addItem($item_value, $rxnorm, $encounter_id, $user_id, $diagnosis, $dataPlanRx);
			}
			
		} else {
			$data = array();
			$plan_arr = array();

			$search_result = $this->searchItem($encounter_id, $diagnosis, $item_value);

			if($search_result)
			{
				$data['EncounterPlanRx']['plan_rx_id'] = $search_result['EncounterPlanRx']['plan_rx_id'];
			}
			else
			{

				$this->create();
				$data['EncounterPlanRx']['encounter_id'] = $encounter_id;
				$data['EncounterPlanRx']['diagnosis'] = $diagnosis;
				$data['EncounterPlanRx']['drug'] = $item_value;
				$data['EncounterPlanRx']['rxnorm'] = $rxnorm;
				$data['EncounterPlanRx']['date_ordered'] = __date("Y-m-d H:i:s");
				$data['EncounterPlanRx']['status'] = 'active';
				$data['EncounterPlanRx']['taking'] = 'active';
			}
			if(!empty($dataPlanRx)) {
				foreach($dataPlanRx as $key=>$value) {
					$data['EncounterPlanRx'][$key] = $value;
				}			
			}
			$data['EncounterPlanRx']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['EncounterPlanRx']['modified_user_id'] = $user_id;	

			$this->save($data);
		}
	}
	
	public function deleteItem($itemvalue, $encounter_id, $user_id, $diagnosis)
	{
		
		if ($diagnosis == 'all') {
			$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			$diagnosis = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);			
			
			
			$search_results = $this->find('all', array(
				'conditions' => array(
					'EncounterPlanRx.encounter_id' => $encounter_id, 
					'EncounterPlanRx.diagnosis' => $diagnosis, 
					'EncounterPlanRx.drug' => $itemvalue)
			));
			
			foreach ($search_results as $search_result) {
				$this->deleteAll(array(
					'EncounterPlanRx.drug' => $search_result['EncounterPlanRx']['drug'],
					'EncounterPlanRx.encounter_id' => $search_result['EncounterPlanRx']['encounter_id'],
				));
			}			
		} else {
			$search_result = $this->searchItem($encounter_id, $diagnosis, $itemvalue);

			if($search_result)
			{
					$plan_rx_id = $search_result['EncounterPlanRx']['plan_rx_id'];
				$this->delete($plan_rx_id);
			}
		}
		
		
	}
	
	public function execute(&$controller, $encounter_id, $patient_id, $diagnosis, $task, $user_id)
	{
		if(isset($controller->data['init_plan_value']))
		{
			$controller->set("init_plan_value", $controller->data['init_plan_value']);
		}
		
		switch ($task)
        {
            case "get_drugs":
            {
                echo json_encode($controller->EncounterPlanRx->getDrugs($encounter_id, $diagnosis));
                exit;
            }
            break;
            
            case "addDrug":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $rx_format = ucwords(strtolower($controller->data['item_value']));
                    $controller->EncounterPlanRx->addItem($rx_format, $controller->data['rxnorm'], $encounter_id, $user_id, $diagnosis);
                    echo json_encode($controller->EncounterPlanRx->getDrugs($encounter_id, $diagnosis));
                    $last_insert_id = $controller->EncounterPlanRx->getLastInsertId();
                    
                    
                if ($diagnosis == 'all') {
									$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
									$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);		
                  $diagnosis = implode(', ', $diagnosisList);
                }                     
                    
					if($last_insert_id)
					{
						$controller->loadModel("PatientMedicationList");
						//Insert Prescription into Medication List.
						$controller->data['PatientMedicationList']['patient_id'] = $patient_id;
						$controller->data['PatientMedicationList']['encounter_id'] = $encounter_id;
						$controller->data['PatientMedicationList']['plan_rx_id'] = $last_insert_id;
						$controller->data['PatientMedicationList']['medication'] = $rx_format;
						$controller->data['PatientMedicationList']['rxnorm'] = $controller->data['rxnorm'];
						$controller->data['PatientMedicationList']['diagnosis'] = $diagnosis;
						$controller->data['PatientMedicationList']['status'] = 'Active';
						$controller->data['PatientMedicationList']['source'] = 'Practice Prescribed';
						$controller->data['PatientMedicationList']['start_date'] = __date("Y-m-d");
						$controller->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$controller->data['PatientMedicationList']['modified_user_id'] = $controller->user_id;
						$controller->PatientMedicationList->create();
						$controller->PatientMedicationList->save($controller->data);
					}
                }
                exit;
            }
            break;
            case "deleteDrug":
            {
                if (!empty($controller->data))
                {
                    $drug = $controller->data['item_value'];
                    
                    if ($diagnosis == 'all') {
                      $rx_items = $controller->EncounterPlanRx->find('all', array('conditions' => array('EncounterPlanRx.encounter_id' => $encounter_id, 'EncounterPlanRx.drug' => $drug)));
                      
                      $plan_rx_id = Set::extract('/EncounterPlanRx/plan_rx_id', $rx_items);
                    } else {
                      $rx_items = $controller->EncounterPlanRx->find('first', array('conditions' => array('EncounterPlanRx.encounter_id' => $encounter_id, 'EncounterPlanRx.diagnosis' => $diagnosis, 'EncounterPlanRx.drug' => $drug)));
                      if ($rx_items)
                      {
                          $plan_rx_id = $rx_items['EncounterPlanRx']['plan_rx_id'];
                      }
                      
                    }
                    
                    
                    $controller->EncounterPlanRx->deleteItem($controller->data['item_value'], $encounter_id, $user_id, $diagnosis);
                    echo json_encode($controller->EncounterPlanRx->getDrugs($encounter_id, $diagnosis));
                    
                    $controller->loadModel("PatientMedicationList");
                    //Delete the prescription from Medication List
                    if ($plan_rx_id)
                    {
                        $controller->PatientMedicationList->deleteAll(array(
                            'PatientMedicationList.plan_rx_id' => $plan_rx_id
                        ), false, true);
                      
                    }
                }
                exit;
            }
            break;
            
            case "updateReview":
            {
                if ($controller->data['submitted']['value'] == 1)
                {
                    $controller->EncounterMaster->updateReview($controller->data['submitted']['id'], $user_id, $encounter_id, $user_id);
                }
                else
                {
                    $controller->EncounterMaster->updateReview($controller->data['submitted']['id'], '', $encounter_id, $user_id);
                }
                exit;
            }
            break;
            
            default:
            {
                $diagnosisList = array($diagnosis);
                if ($diagnosis == 'all') {
									$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
									$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);			
                }                
                
                App::import('Model', 'EncounterFrequentPrescribed');
                $frequent = new EncounterFrequentPrescribed();
                $controller->set('frequentData', $frequent->getFrequent('rx', $diagnosisList, $user_id));
                $controller->set('_diagnosis', $diagnosis);
                
            }
        }
	}
	
	public function executeData(&$controller, $encounter_id, $patient_id, $diagnosis, $drug, $task, $user_id)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if ($controller->data['submitted']['id'] == 'date_ordered')
                    {
                        $controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
                    }
					
                    $controller->EncounterPlanRx->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, $diagnosis, $drug);
					
										if ($diagnosis == 'all') {
											$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id, 1);
											$diagnosis = $encounterAssessment[0]['EncounterAssessment']['diagnosis'];			
										}
										
										
					$rx_items = $controller->EncounterPlanRx->find('first', array('conditions' => array('EncounterPlanRx.encounter_id' => $encounter_id, 'EncounterPlanRx.diagnosis' => $diagnosis, 'EncounterPlanRx.drug' => $drug)));
					if ($rx_items)
                    {
					    $controller->loadModel("PatientMedicationList");
                        $plan_rx_id = $rx_items['EncounterPlanRx']['plan_rx_id'];
						$controller->PatientMedicationList->setItemValueByPlanRxId($controller->data['submitted']['id'], $controller->data['submitted']['value'], $plan_rx_id, $patient_id, $user_id);
                    }
					
                }
                exit;
            }
            break;
            
            case "pharmacy_load":
            {
                if (!empty($controller->data))
                {
                    $search_keyword = $controller->data['autocomplete']['keyword'];
					$search_limit = $controller->data['autocomplete']['limit'];
                    $lab_items = $controller->DirectoryPharmacy->find('all', array('conditions' => array('DirectoryPharmacy.pharmacy_name LIKE ' => '%' . $search_keyword . '%'),'limit' => $search_limit));
                    $data_array = array();
                    
                    foreach ($lab_items as $lab_item)
                    {
                        $data_array[] = $lab_item['DirectoryPharmacy']['pharmacy_name'] . '|' . $lab_item['DirectoryPharmacy']['address_1'] . '|' . $lab_item['DirectoryPharmacy']['address_2'] . '|' . $lab_item['DirectoryPharmacy']['city'] . '|' . $lab_item['DirectoryPharmacy']['state'] . '|' . $lab_item['DirectoryPharmacy']['zip_code'] . '|' . $lab_item['DirectoryPharmacy']['country'] . '|' . $lab_item['DirectoryPharmacy']['contact_name'] . '|' . $lab_item['DirectoryPharmacy']['phone_number'] . '|' . $lab_item['DirectoryPharmacy']['fax_number'];
                    }
                    
                    echo implode("\n", $data_array);
                }
                exit();
            }
            break;
            default:
            {
								if ($diagnosis == 'all') {
									$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
									$diagnosis = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);			
								}
							
							
                $rx_items = $controller->EncounterPlanRx->find('first', array('conditions' => array('EncounterPlanRx.encounter_id' => $encounter_id, 'EncounterPlanRx.diagnosis' => $diagnosis, 'EncounterPlanRx.drug' => $drug)));
                if ($rx_items)
                {
                    $controller->set('RxItem', $rx_items['EncounterPlanRx']);
                }
            }
        }
		
	}

        /**
         *
         * @param boolean $created True if new record was created with the save
         */
        public function afterSave($created) {
            parent::afterSave($created);
            
            $planRxId = ($this->id) ? $this->id : $this->data['EncounterPlanRx']['plan_rx_id'];

            $planRx = $this->find('first', array(
                'conditions' => array(
                    'EncounterPlanRx.plan_rx_id' => $planRxId,
                ),
                'fields' => array(
                    'diagnosis', 'drug', 'modified_user_id',
                    'rxnorm', 'type', 'quantity', 'unit', 'route', 'plan_rx_id',
                    'frequency', 'dispense', 'refill_allowed', 'pharmacy_instruction', 'pharmacy_name', 
                    'direction',
                ),
            ));
            
            // If a new record was created
            if ($created) {
                // Add the data saved in to this user's
                // frquency data
                App::import('Model', 'EncounterFrequentPrescribed');
                $frequent = new EncounterFrequentPrescribed();
                $record = $frequent->addRecord(
                        'rx', 
                        $planRx['EncounterPlanRx']['diagnosis'], 
                        $planRx['EncounterPlanRx']['drug'],
                        $planRx['EncounterPlanRx']['modified_user_id']
                );
                
                $default = array();
                
                if (isset($record['EncounterFrequentPrescribed']['data']) && $record['EncounterFrequentPrescribed']['data']){
                  $default = json_decode($record['EncounterFrequentPrescribed']['data'], true);
                }
                
                if ($default) {
                  $this->updateAll(
                    array(
                        'EncounterPlanRx.rxnorm' => '\'' . Sanitize::escape($default['rxnorm']) . '\'',
                        'EncounterPlanRx.type' => '\'' . Sanitize::escape($default['type']) . '\'',
                        'EncounterPlanRx.quantity' => '\'' . Sanitize::escape($default['quantity']) . '\'',
                        'EncounterPlanRx.unit' => '\'' . Sanitize::escape($default['unit']) . '\'',
                        'EncounterPlanRx.route' => '\'' . Sanitize::escape($default['route']) . '\'',
                        'EncounterPlanRx.frequency' => '\'' . Sanitize::escape($default['frequency']) . '\'',
                        'EncounterPlanRx.dispense' => '\'' . Sanitize::escape($default['dispense']) . '\'',
                        'EncounterPlanRx.refill_allowed' => '\'' . Sanitize::escape($default['refill_allowed']) . '\'',
                        'EncounterPlanRx.pharmacy_instruction' => '\'' . Sanitize::escape($default['pharmacy_instruction']) . '\'',
                        'EncounterPlanRx.pharmacy_name' => '\'' . Sanitize::escape($default['pharmacy_name']) . '\'',
                        'EncounterPlanRx.direction' => '\'' . Sanitize::escape($default['direction']) . '\'',
                    ), 
                    array(
                        'EncounterPlanRx.plan_rx_id' => $planRx['EncounterPlanRx']['plan_rx_id'],
                    ));
                }                
                

            } else {
                // Update the data saved in to this user's
                // frquency data
                App::import('Model', 'EncounterFrequentPrescribed');
                $frequent = new EncounterFrequentPrescribed();
                $frequent->updateData(
                        'rx', 
                        $planRx['EncounterPlanRx']['diagnosis'], 
                        $planRx['EncounterPlanRx']['drug'],
                        $planRx['EncounterPlanRx']['modified_user_id'],
                        $planRx['EncounterPlanRx']
                );              
            }
        }   
				
	public function generateCombined($encounterId) {
		$modelname = $this->name;
		$field = 'drug';
		$idField = 'plan_rx_id';
		
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