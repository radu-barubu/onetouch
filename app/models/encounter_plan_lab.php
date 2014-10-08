<?php

class EncounterPlanLab extends AppModel 
{ 
    public $name = 'EncounterPlanLab'; 
    public $primaryKey = 'plan_labs_id';
    public $useTable = 'encounter_plan_labs';
    public $actsAs = array('Containable');
    public $belongsTo = array(
            'EncounterMaster' => array(
            'className' => 'EncounterMaster',
            'foreignKey' => 'encounter_id'
        )
    );
    
		public $createOrder = true;
		
    /**
    * Queries the order list by patient id and returns a result set array.
    *
    * @param integer $patient_id Patient Identifier
    * @return array Array of records
    */
    public function getOrderList($patient_id)
    {
				if (!$patient_id) {
					return array();
				}
        $results = $this->getItemsByPatient($patient_id);
        $orders = array();
        
        foreach($results as $result)
        {
            $data = array();
            $data['plan_labs_id'] = $result['EncounterPlanLab']['plan_labs_id'];
            $data['encounter_id'] = $result['EncounterPlanLab']['encounter_id'];
            $data['test_name'] = $result['EncounterPlanLab']['test_name'];
            $data['diagnosis'] = $result['EncounterPlanLab']['diagnosis'];
            $data['icd_code'] = $result['EncounterPlanLab']['icd_code'];
            
            $orders[] = $data;
        }
        
        return $orders;
    }
    
    public function getItemsByPatient($patient_id)
    {
				if (!$patient_id) {
					return array();
				}
			
        $options['conditions'] = array('EncounterMaster.patient_id' => $patient_id);
        $options['fields'] = array('EncounterPlanLab.*', 'DES_DECRYPT(PatientDemo.first_name) as patient_firstname', 'DES_DECRYPT(PatientDemo.last_name) as patient_lastname','PatientDemo.patient_id');
        $options['order'] = array('EncounterPlanLab.date_ordered DESC');
        $options['joins'] = array(                    
                    array(
                        'table' => 'encounter_master'
                        , 'type' => 'INNER'
                        , 'alias' => 'EncounterMastr'
                        , 'conditions' => array(
                        'EncounterPlanLab.encounter_id = EncounterMastr.encounter_id'
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
    
    public function getDiagnosis ($encounter_id, &$plan = array(), $combine = false)
    {
        $this->belongsTo = array();
        
				$params = array(
            'conditions' => array('EncounterPlanLab.encounter_id' => $encounter_id),
				);
				
				if ($combine) {
					$params['group'] = array('EncounterPlanLab.test_name');
				}
				
        $lab_items = $this->find('all', $params);
        
        $data = array();
        
        foreach($lab_items as $v) {
            $v = $v['EncounterPlanLab'];
            
            $plan[$v['diagnosis']]['lab']['items'][] = $v['test_name'];
            $plan[$v['diagnosis']]['lab'][$v['test_name']] = $v;
						
						if ($combine) {
							$plan['combined']['lab']['items'][] = $v['test_name'];
							$plan['combined']['lab'][$v['test_name']] = $v;
						}						
						
        }
				
				
				if ($combine) {
					foreach ($plan as $diagnosis => $data) {
						if ($diagnosis == 'combined') {
							continue;
						}
						
						if (!isset($plan[$diagnosis]['lab'])) {
							continue;
						}						

						$plan[$diagnosis]['lab'] = $plan['combined']['lab'];
					}
				}				
				
        
        return $plan;
    }
    
    
    public function getTests($encounter_id, $diagnosis)
    {
				if ($diagnosis == 'all') {
					$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);

					$diagnosis = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);
					$lab_items = $this->find('all', array(
						'conditions' => array('EncounterPlanLab.encounter_id' => $encounter_id, 'EncounterPlanLab.diagnosis' => $diagnosis),
						'group' => array('EncounterPlanLab.test_name'),
						));
					
				} else {
					$lab_items = $this->find('all', array('conditions' => array('EncounterPlanLab.encounter_id' => $encounter_id, 'EncounterPlanLab.diagnosis' => $diagnosis)));
					
				}
			
			
        $test_array = array();
            
        foreach($lab_items as $lab_item)
        {
            $test_array[] = $lab_item['EncounterPlanLab']['test_name'];
        }
        
        return $test_array;
    }
    
    public function getAllLabs($encounter_id, $combine = false)
    {
			$params = array(
				'conditions' => array('EncounterPlanLab.encounter_id' => $encounter_id),
			);


			if ($combine) {
				$params['group'] = array('EncounterPlanLab.test_name');
			}

			$search_result = $this->find(
					'all', $params
			);
        
        $ret = array();
        
        if(count($search_result) > 0)
        {
            foreach($search_result as $item)
            {
                if(strlen($item['EncounterPlanLab']['test_name']) > 0)
                {
                    $ret[] = $item['EncounterPlanLab']['test_name'];
                }
            }
        }
        
        array_unique($ret);
        return $ret;
    }
    
    private function searchItem($encounter_id, $diagnosis, $test_name)
    {
        $search_result = $this->find('first', array('conditions' => array('EncounterPlanLab.encounter_id' => $encounter_id, 'EncounterPlanLab.diagnosis' => $diagnosis,  'EncounterPlanLab.test_name' => $test_name)
));
        
        if(!empty($search_result))
        {
            return $search_result;
        }
        else
        {
            return false;
        }
    }
    
    public function setItemValue($field, $value, $encounter_id, $user_id, $diagnosis, $test_name)
    {
				if ($diagnosis == 'all') {
					$list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
					$diagnosis = $list[0]['EncounterAssessment']['diagnosis'];
					$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $list);

					$search_results = $this->find(
									'all', 
									array(
											'conditions' => array(
												'EncounterPlanLab.encounter_id' => $encounter_id, 
												'EncounterPlanLab.diagnosis' =>$diagnosisList, 
												'EncounterPlanLab.test_name' =>$test_name)
									)
					);

					$searchMap = array();
					foreach ($search_results as $s) {
						$searchMap[$s['EncounterPlanLab']['diagnosis']] = $s;
					}

					$createOrder = true;
					foreach ($diagnosisList as $d) {
						if (isset($searchMap[$d])) {
							$searchMap[$d]['EncounterPlanLab'][$field] = $value;
							$this->createOrder = $createOrder;
							$this->save($searchMap[$d]);
							$createOrder = false;
						} else {

							$data = array();
							$this->create();
							$data['EncounterPlanLab']['test_name'] = $test_name;
							$data['EncounterPlanLab']['diagnosis'] = $d;
							$data['EncounterPlanLab']['encounter_id'] = $encounter_id;
							$data['EncounterPlanLab']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$data['EncounterPlanLab']['modified_user_id'] = $user_id;
							$data['EncounterPlanLab']['ordered_by_id'] = $user_id;
							$data['EncounterPlanLab'][$field] = $value;
							$this->createOrder = $createOrder;
							$this->save($data);
							$createOrder = false;
						}
					}								
				} else {
					$search_result = $this->find(
									'first', 
									array(
											'conditions' => array('EncounterPlanLab.encounter_id' => $encounter_id, 'EncounterPlanLab.diagnosis' =>$diagnosis, 'EncounterPlanLab.test_name' =>$test_name)
									)
					);

					if(!empty($search_result))
					{
							$data = array();
							$data['EncounterPlanLab']['plan_labs_id'] = $search_result['EncounterPlanLab']['plan_labs_id'];        
							$data['EncounterPlanLab']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$data['EncounterPlanLab']['modified_user_id'] = $user_id;
							$data['EncounterPlanLab'][$field] = $value;
							$this->save($data);
					}					
				}
    }
    
    public function addItem($item_value, $encounter_id, $user_id, $diagnosis, $reason = '', $logPatientOrders = true)
    {
				if ($diagnosis == 'all') {
          $list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
          $allDiagnosis = implode(', ', Set::extract('/EncounterAssessment/diagnosis', $list));

					$logPatientOrders = true;
					foreach ($list as $l) {
						$diagnosis = $l['EncounterAssessment']['diagnosis'];

						if ($diagnosis == 'all') {
							continue;
						}
            
            $actual_reason = $reason;

            if ($actual_reason == 'all') {
              $actual_reason = $allDiagnosis;
            }		
            
						$this->addItem($item_value, $encounter_id, $user_id, $diagnosis, $actual_reason, $logPatientOrders);
						$logPatientOrders = false;
					}					
				} else {
					$data = array();

					$search_result = $this->searchItem($encounter_id, $diagnosis, $item_value);

					if($search_result)
					{
							$data = $search_result;
					}
					else
					{
							$this->create();
							$data['EncounterPlanLab']['encounter_id'] = $encounter_id;
							$data['EncounterPlanLab']['diagnosis'] = $diagnosis;
							$data['EncounterPlanLab']['test_name'] = $item_value;
							$data['EncounterPlanLab']['ordered_by_id'] = $user_id;
							$data['EncounterPlanLab']['date_ordered'] = __date("Y-m-d H:i:s");
							$data['EncounterPlanLab']['status'] = 'Open';   
              $data['EncounterPlanLab']['reason'] = trim($reason);
              
				$data['EncounterPlanLab']['reminder_notify_json'] = $this->notifyData;
					}

					$data['EncounterPlanLab']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$data['EncounterPlanLab']['modified_user_id'] = $user_id;        

					$this->createOrder = $logPatientOrders;
					$this->save($data);
					$plan_labs_id = $this->getLastInsertId();
					$plan_labs_id = (intval($plan_labs_id)>0) ? $plan_labs_id : $data['EncounterPlanLab']['plan_labs_id'];
					App::import('Model','EncounterMaster');
					$EncounterMaster= new EncounterMaster();
					$patient_id = $EncounterMaster->getPatientID($encounter_id);
					
					if ($logPatientOrders) {					
						App::import('Model','PatientOrders');
						App::import('Helper', 'Html');$html = new HtmlHelper();
						$PatientOrders= new PatientOrders();
						$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_plan' => 1, 'plan_labs_id' => $plan_labs_id), array('escape' => false));                    
						$PatientOrders->addActivitiesItem($data['EncounterPlanLab']['ordered_by_id'], $data['EncounterPlanLab']['test_name'], "Labs", "Outside Labs", $data['EncounterPlanLab']['status'], $patient_id, $plan_labs_id , $editlink);					
					}
				}
    }

    public function deleteItem($itemvalue, $encounter_id, $user_id, $diagnosis)
    {
			
				if ($diagnosis == 'all') {
					$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
					$diagnosis = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);			
					
					$search_results = $this->find('all', array(
						'conditions' => array(
							'EncounterPlanLab.encounter_id' => $encounter_id, 
							'EncounterPlanLab.diagnosis' => $diagnosis, 
							'EncounterPlanLab.test_name' => $itemvalue)
					));

					foreach ($search_results as $search_result) {
						$plan_labs_id = $search_result['EncounterPlanLab']['plan_labs_id'];
						App::import('Model','PatientOrders');
						$PatientOrders= new PatientOrders();
						$PatientOrders->deleteActivitiesItem($plan_labs_id, "Labs", "Outside Labs");
						
						$this->deleteAll(array(
							'EncounterPlanLab.test_name' => $itemvalue,
							'EncounterPlanLab.encounter_id' => $encounter_id,
						));				
						
						App::import('Model', 'Order');
						$order = new Order();
						
						$order->deleteAll(array(
							'Order.test_name' => $itemvalue,
							'Order.encounter_id' => $encounter_id,
						));				
						
					}
				} else {
					$search_result = $this->searchItem($encounter_id, $diagnosis, $itemvalue);

					if($search_result)
					{
							$plan_labs_id = $search_result['EncounterPlanLab']['plan_labs_id'];
							App::import('Model','PatientOrders');
							$PatientOrders= new PatientOrders();
							$PatientOrders->deleteActivitiesItem($plan_labs_id, "Labs", "Outside Labs");
							$this->delete($plan_labs_id);
					}
				}
    }
    
    public function execute(&$controller, $encounter_id, $diagnosis, $task, $user_id, $patient_id, $patient)
    {
        if(isset($controller->data['init_plan_value']))
        {
            $controller->set("init_plan_value", $controller->data['init_plan_value']);
        }
        
        switch ($task)
        {
            case "get_tests":
            {
                echo json_encode($controller->EncounterPlanLab->getTests($encounter_id, $diagnosis));
                exit;
            }
            break;
            case "addTest":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $plan_format = ucwords(strtolower($controller->data['item_value']));
                    
                    if(isset($controller->data['reason'])) {
                      $reason = $controller->data['reason'];
                    } else {
                      $reason = '';
                    }                    
                    
					$this->notifyData = $controller->PracticeSetting->reminderDefaultJson();
                    $controller->EncounterPlanLab->addItem($plan_format, $encounter_id, $user_id, $diagnosis, $reason);
                    echo json_encode($controller->EncounterPlanLab->getTests($encounter_id, $diagnosis));
                }
                exit;
            }
            break;
            case "deleteTest":
            {
                if (!empty($controller->data))
                {
                    $controller->EncounterPlanLab->deleteItem($controller->data['item_value'], $encounter_id, $user_id, $diagnosis);
                    echo json_encode($controller->EncounterPlanLab->getTests($encounter_id, $diagnosis));
                }
                exit;
            }
            break;
            case "labname_load":
            {
                if (!empty($controller->data))
                {
                    $search_keyword = $controller->data['autocomplete']['keyword'];
                    $search_limit = $controller->data['autocomplete']['limit'];
                    $lab_items = $controller->DirectoryLabFacility->find('all', array('conditions' => array('DirectoryLabFacility.lab_facility_name LIKE ' => '%' . $search_keyword . '%'),'limit' => $search_limit));
                    $data_array = array();
                    
                    foreach ($lab_items as $lab_item)
                    {
                        $data_array[] = $lab_item['DirectoryLabFacility']['lab_facility_name'] . '|' . $lab_item['DirectoryLabFacility']['address_1'] . '|' . $lab_item['DirectoryLabFacility']['address_2'] . '|' . $lab_item['DirectoryLabFacility']['city'] . '|' . $lab_item['DirectoryLabFacility']['state'] . '|' . $lab_item['DirectoryLabFacility']['zip_code'] . '|' . $lab_item['DirectoryLabFacility']['country'];
                    }
                    
                    echo implode("\n", $data_array);
                }
                exit();
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
                $controller->set('frequentData', $frequent->getFrequent('lab', $diagnosisList, $user_id));
                $controller->set('_diagnosis', $diagnosis);                
            }
        }
    }
    
    public function executeData(&$controller, $encounter_id, $diagnosis, $test_name, $user_id, $task)
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
					else if($controller->data['submitted']['id'] == 'reminder_notify_json')
					{						
						$controller->data['submitted']['value'] = $controller->PracticeSetting->reminderNotifyJson($controller->data['submitted']['value']);						
					}
                    //If only one Lab facility is avilable, insert the lab details automatically
                    if ($controller->data['submitted']['lab_facility_count'] == 1)
                    {
                        $lab_facility_items = $controller->DirectoryLabFacility->find('first');
                        extract($lab_facility_items['DirectoryLabFacility']);
                        $controller->EncounterPlanLab->setItemValue('lab_facility_name', $lab_facility_name, $encounter_id, $user_id, $diagnosis, $test_name);
                        $controller->EncounterPlanLab->setItemValue('lab_address_1', $address_1, $encounter_id, $user_id, $diagnosis, $test_name);
                        $controller->EncounterPlanLab->setItemValue('lab_address_2', $address_2, $encounter_id, $user_id, $diagnosis, $test_name);
                        $controller->EncounterPlanLab->setItemValue('lab_city', $city, $encounter_id, $user_id, $diagnosis, $test_name);
                        $controller->EncounterPlanLab->setItemValue('lab_state', $state, $encounter_id, $user_id, $diagnosis, $test_name);
                        $controller->EncounterPlanLab->setItemValue('lab_zip_code', $zip_code, $encounter_id, $user_id, $diagnosis, $test_name);
                        $controller->EncounterPlanLab->setItemValue('lab_country', $country, $encounter_id, $user_id, $diagnosis, $test_name);
                    }
                    
                    $controller->EncounterPlanLab->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, $diagnosis, $test_name);
                }
                exit;
            }
            break;
            
            default:
            {
							
								if ($diagnosis == 'all') {
									$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
									$diagnosis = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);			
								}
								
                $lab_items = $controller->EncounterPlanLab->find('first', array('conditions' => array('EncounterPlanLab.encounter_id' => $encounter_id, 'EncounterPlanLab.diagnosis' => $diagnosis, 'EncounterPlanLab.test_name' => $test_name)));
                if ($lab_items)
                {
                    $controller->set('LabItem', $lab_items['EncounterPlanLab']);
                }
                $lab_facility_items = $controller->DirectoryLabFacility->find('all');
                $controller->set('LabFacilityCount', count($lab_facility_items));
                
                $encounter_items = $controller->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'recursive' => -1));
                
                if ($encounter_items)
                {
                    $controller->set('EncounterItem', $encounter_items['EncounterMaster']);
                }
				$notify = $controller->PracticeSetting->find('first', array('fields' => 'reminder_notify_json', 'recursive' => -1));				
				$controller->Set('notify', $notify['PracticeSetting']['reminder_notify_json']);
            }
        }
    }
    

        /**
         *
         * @param boolean $created True if new record was created with the save
         */
        public function afterSave($created) {
            parent::afterSave($created);
						App::import('Model', 'Order');
						$order = new Order();
            
						$planLabId = ($this->id) ? $this->id : $this->data['EncounterPlanLab']['plan_labs_id'];
						
						// Discovered some quirk in using the containable behavior
						// If the association name starts in lower case,
						// the containable behavior treats as a field name.
						// That is why we have to rebind EncounterMaster
						// to ScheduleCalendar and use uppercased first letter for the association
						$this->EncounterMaster->unbindModelAll();
						$this->EncounterMaster->bindModel(array(
							'belongsTo' => array(
								'PatientDemographic' => array(
									'className' => 'PatientDemographic',
									'foreignKey' => 'patient_id'
								),
								'ScheduleCalendar' => array(
									'className' => 'ScheduleCalendar',
									'foreignKey' => 'calendar_id'
								)				
							),
						));						
						$planLab = $this->find('first', array(
							'conditions' => array(
								'EncounterPlanLab.plan_labs_id' => $planLabId,
							),
							'fields' => array(
								'encounter_id', 'plan_labs_id', 'test_name', 'priority', 'status',
								'modified_timestamp', 'date_ordered', 'diagnosis', 
                'test_type', 'cpt', 'specimen', 'lab_facility_name', 'patient_instruction',
                 'comment', 'ordered_by_id'
                  
							),                
							'contain' => array(
								'EncounterMaster' => array(
									'fields' => array('encounter_status', 'patient_id'),
									'PatientDemographic' => array(
										'fields' => array('first_name', 'last_name'),
									),
									'ScheduleCalendar' => array(
										'UserAccount' => array(
											'fields' => array('firstname', 'lastname'),
										),
									),
								),
							),
						));		
						
						$data = array();
						if (isset($planLab['EncounterMaster']) && $planLab['EncounterMaster']['patient_id']) {
							$data = array('Order' => array(
								'data_id' => $planLab['EncounterPlanLab']['plan_labs_id'],
								'encounter_id' => $planLab['EncounterPlanLab']['encounter_id'],
								'patient_id' => $planLab['EncounterMaster']['patient_id'],
								'encounter_status' => $planLab['EncounterMaster']['encounter_status'],
								'test_name' => $planLab['EncounterPlanLab']['test_name'],
								'source' => '',
								'patient_firstname' => $planLab['EncounterMaster']['PatientDemographic']['first_name'],
								'patient_lastname' => $planLab['EncounterMaster']['PatientDemographic']['last_name'],
								'provider_name' => $planLab['EncounterMaster']['ScheduleCalendar']['UserAccount']['firstname'] . ' ' . $planLab['EncounterMaster']['ScheduleCalendar']['UserAccount']['lastname'],
								'priority' => $planLab['EncounterPlanLab']['priority'],
								'order_type' => 'Labs',
								'status' => $planLab['EncounterPlanLab']['status'],
								'item_type' => 'plan_labs',
								'date_performed' => $planLab['EncounterPlanLab']['modified_timestamp'],
								'date_ordered' => $planLab['EncounterPlanLab']['date_ordered'],
								'modified_timestamp' => $planLab['EncounterPlanLab']['modified_timestamp'],
							));						
						}
						
            // If a new record was created
            if ($created) {
                // Add the data saved in to this user's
                // frquency data
                App::import('Model', 'EncounterFrequentPrescribed');
                $frequent = new EncounterFrequentPrescribed();
                $record = $frequent->addRecord(
                        'lab', 
                        $planLab['EncounterPlanLab']['diagnosis'], 
                        $planLab['EncounterPlanLab']['test_name'],
                        $planLab['EncounterPlanLab']['ordered_by_id'],
                        $planLab['EncounterPlanLab']
                );

                $default = array();
                
                if (isset($record['EncounterFrequentPrescribed']['data']) && $record['EncounterFrequentPrescribed']['data']){
                  $default = json_decode($record['EncounterFrequentPrescribed']['data'], true);
                }
                
                if ($default) {
                  $this->updateAll(
                    array(
                        'EncounterPlanLab.test_type' => '\'' . Sanitize::escape($default['test_type']) . '\'',
                        'EncounterPlanLab.cpt' => '\'' . Sanitize::escape($default['cpt']) . '\'',
                        'EncounterPlanLab.priority' => '\'' . Sanitize::escape($default['priority']) . '\'',
                        'EncounterPlanLab.specimen' => '\'' . Sanitize::escape($default['specimen']) . '\'',
                        'EncounterPlanLab.lab_facility_name' => '\'' . Sanitize::escape($default['lab_facility_name']) . '\'',
                        'EncounterPlanLab.patient_instruction' => '\'' . Sanitize::escape($default['patient_instruction']) . '\'',
                        'EncounterPlanLab.comment' => '\'' . Sanitize::escape($default['comment']) . '\'',
                    ), 
                    array(
                        'EncounterPlanLab.plan_labs_id' => $planLab['EncounterPlanLab']['plan_labs_id'],
                    ));
                }                
                
								$order->create();
								if ($data && $this->createOrder) {
									$order->save($data);
								}
								
            } else {
							
                // Update the data saved in to this user's
                // frquency data
                App::import('Model', 'EncounterFrequentPrescribed');
                $frequent = new EncounterFrequentPrescribed();
                $record = $frequent->updateData(
                        'lab', 
                        $planLab['EncounterPlanLab']['diagnosis'], 
                        $planLab['EncounterPlanLab']['test_name'],
                        $planLab['EncounterPlanLab']['ordered_by_id'],
                        $planLab['EncounterPlanLab']
                );

              
							$current = $order->find('first', array(
								'conditions' => array(
									'Order.item_type' => 'plan_labs',
									'Order.data_id' => $planLabId,
								),
							));
							
							if ($current && $data && $this->createOrder) {
								$data['Order']['encounter_order_id'] = $current['Order']['encounter_order_id'];
								$order->save($data);
							}
							
						}
        }
				
				public function afterDelete(){
					parent::afterDelete();
					App::import('Model', 'Order');
					$order = new Order();
					
							$current = $order->find('first', array(
								'conditions' => array(
									'Order.item_type' => 'plan_labs',
									'Order.data_id' => $this->id,
								),
							));
							
							if ($current) {
								$order->delete($current['Order']['encounter_order_id']);
							}								
				}				
				
	public function generateCombined($encounterId) {
		$modelname = $this->name;
		$field = 'test_name';
		$idField = 'plan_labs_id';
		
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
