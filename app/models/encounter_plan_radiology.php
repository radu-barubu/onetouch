<?php

class EncounterPlanRadiology extends AppModel 
{ 
	public $name = 'EncounterPlanRadiology'; 
	public $primaryKey = 'plan_radiology_id';
	public $useTable = 'encounter_plan_radiology';
	public $actsAs = array('Containable');
	public $belongsTo = array(
			'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	public $createOrder = true;
	
	public function getItemsByPatient($patient_id)
	{
		$options['conditions'] = array('EncounterMaster.patient_id' => $patient_id);
		$options['fields'] = array('EncounterPlanRadiology.*', 'DES_DECRYPT(PatientDemo.first_name) as patient_firstname', 'DES_DECRYPT(PatientDemo.last_name) as patient_lastname','PatientDemo.patient_id');
		$options['order'] = array('EncounterPlanRadiology.date_ordered DESC');
		$options['joins'] = array(					
					array(
						'table' => 'encounter_master'
						, 'type' => 'INNER'
						, 'alias' => 'EncounterMastr'
						, 'conditions' => array(
						'EncounterPlanRadiology.encounter_id = EncounterMastr.encounter_id'
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

	public function getDiagnosis($encounter_id, &$plan = array(), $combine = false)
	{
		$this->belongsTo = array();
		
		$params = array(
			'conditions' => array('EncounterPlanRadiology.encounter_id' => $encounter_id),
		);
		
		if ($combine) {
			$params['group'] = array('EncounterPlanRadiology.procedure_name');
		}
		
		
		$radiology_items = $this->find('all', $params);
		
		foreach($radiology_items as $v) {
			$v = $v['EncounterPlanRadiology'];
			
			$plan[$v['diagnosis']]['radiology']['items'][] = $v['procedure_name'];
			$plan[$v['diagnosis']]['radiology'][$v['procedure_name']] = $v;
			
			if ($combine) {
				$plan['combined']['radiology']['items'][] = $v['procedure_name'];
				$plan['combined']['radiology'][$v['procedure_name']] = $v;
			}
			
		}
		
		if ($combine) {
			foreach ($plan as $diagnosis => $data) {
				if ($diagnosis == 'combined') {
					continue;
				}
				
				if (!isset($plan[$diagnosis]['radiology'])) {
					continue;
				}
				
				$plan[$diagnosis]['radiology'] = $plan['combined']['radiology'];
			}
		}
		
		return $plan;
	}
	
	public function getAllRadiologies($encounter_id, $combine = false)
	{
		
		$params = array(
			'conditions' => array('EncounterPlanRadiology.encounter_id' => $encounter_id),
		);
		
		
		if ($combine) {
			$params['group'] = array('EncounterPlanRadiology.procedure_name');
		}
		
		$search_result = $this->find(
				'all', $params
		);
		
		
		
		$ret = array();
		
		if(count($search_result) > 0)
		{
			foreach($search_result as $item)
			{
				if(strlen($item['EncounterPlanRadiology']['procedure_name']) > 0)
				{
					$ret[] = $item['EncounterPlanRadiology']['procedure_name'];
				}
			}
		}
		
		array_unique($ret);
		return $ret;
	}
	
	
	
	
	public function getPlans($encounter_id, $diagnosis)
	{
		
		if ($diagnosis == 'all') {
			$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			
			$diagnosis = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);
			
			$radiology_items = $this->find('all', array(
				'conditions' => array('EncounterPlanRadiology.encounter_id' => $encounter_id, 'EncounterPlanRadiology.diagnosis' => $diagnosis),
				'group' => array('EncounterPlanRadiology.procedure_name'),
			));

			
		} else {
			$radiology_items = $this->find('all', array('conditions' => array('EncounterPlanRadiology.encounter_id' => $encounter_id, 'EncounterPlanRadiology.diagnosis' => $diagnosis)));
			
		} 
		
		$plan_array = array();
			
		foreach($radiology_items as $radiology_item)
		{
			$plan_array[] = $radiology_item['EncounterPlanRadiology']['procedure_name'];
		}
		return $plan_array;
	}
	
	private function searchItem($encounter_id, $diagnosis, $procedure_name)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterPlanRadiology.encounter_id' => $encounter_id, 'EncounterPlanRadiology.diagnosis' => $diagnosis, 'EncounterPlanRadiology.procedure_name' => $procedure_name)
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
	
	public function setItemValue($field, $value, $encounter_id, $user_id, $diagnosis, $procedure_name)
	{
		
		if ($diagnosis == 'all') {
			$list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			$diagnosis = $list[0]['EncounterAssessment']['diagnosis'];
			$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $list);

			$search_results = $this->find(
					'all', 
					array(
						'conditions' => array(
							'EncounterPlanRadiology.encounter_id' => $encounter_id, 
							'EncounterPlanRadiology.diagnosis' => $diagnosisList, 
							'EncounterPlanRadiology.procedure_name' => $procedure_name)
					)
			);
			
			$searchMap = array();
			foreach ($search_results as $s) {
				$searchMap[$s['EncounterPlanRadiology']['diagnosis']] = $s;
			}
			
			$createOrder = true;
			foreach ($diagnosisList as $d) {
				if (isset($searchMap[$d])) {
					$searchMap[$d]['EncounterPlanRadiology'][$field] = $value;
					$this->createOrder = $createOrder;
					$this->save($searchMap[$d]);
					$createOrder = false;
				} else {

					$data = array();
					$this->create();
					$data['EncounterPlanRadiology']['diagnosis'] = $d;
					$data['EncounterPlanRadiology']['procedure_name'] = $procedure_name;
					$data['EncounterPlanRadiology']['encounter_id'] = $encounter_id;
					$data['EncounterPlanRadiology']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$data['EncounterPlanRadiology']['modified_user_id'] = $user_id;
					$data['EncounterPlanRadiology']['ordered_by_id'] = $user_id;
					$data['EncounterPlanRadiology'][$field] = $value;
					$this->createOrder = $createOrder;
					$this->save($data);
					$createOrder = false;
				}
			}			
			
		} else {
			$search_result = $this->find(
					'first', 
					array(
						'conditions' => array('EncounterPlanRadiology.encounter_id' => $encounter_id, 'EncounterPlanRadiology.diagnosis' => $diagnosis, 'EncounterPlanRadiology.procedure_name' => $procedure_name)
					)
			);

			if(!empty($search_result) )
			{
				$data = array();
				$data['EncounterPlanRadiology']['plan_radiology_id'] = $search_result['EncounterPlanRadiology']['plan_radiology_id'];
				$data['EncounterPlanRadiology']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$data['EncounterPlanRadiology']['modified_user_id'] = $user_id;
					$data['EncounterPlanRadiology'][$field] = $value;
				$this->save($data);
			}			
		}
	}
	
	public function addItem($item_value, $encounter_id, $user_id, $diagnosis, $reason='', $logPatientOrders = true)
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
				
				if ($reason == 'all') {
					$actual_reason = $allDiagnosis;
				}				
				
				$this->addItem($item_value, $encounter_id, $user_id, $diagnosis, $actual_reason, $logPatientOrders);
				$logPatientOrders = false;
			}
			
		} else {
			
			$data = array();
			$plan_arr = array();

			$search_result = $this->searchItem($encounter_id, $diagnosis, $item_value);

			if($search_result)
			{
				$data = $search_result;
			}
			else
			{
				$this->create();
				$data['EncounterPlanRadiology']['encounter_id'] = $encounter_id;
				$data['EncounterPlanRadiology']['diagnosis'] = $diagnosis;
				$data['EncounterPlanRadiology']['procedure_name'] = $item_value;
				$data['EncounterPlanRadiology']['ordered_by_id'] = $user_id;
				$data['EncounterPlanRadiology']['date_ordered'] = __date("Y-m-d H:i:s");
				$data['EncounterPlanRadiology']['status'] = 'Open';
				$data['EncounterPlanRadiology']['reason'] = trim($reason);
				$data['EncounterPlanRadiology']['reminder_notify_json'] = $this->notifyData;
			}

			$data['EncounterPlanRadiology']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['EncounterPlanRadiology']['modified_user_id'] = $user_id;

			$this->createOrder = $logPatientOrders;
			$this->save($data);
			$plan_radiology_id = $this->getLastInsertId();
			$plan_radiology_id = (intval($plan_radiology_id)>0) ? $plan_radiology_id : $data['EncounterPlanRadiology']['plan_radiology_id'];
			$patient_id = $this->EncounterMaster->getPatientID($encounter_id);

			if ($logPatientOrders) {
				App::import('Model','PatientOrders');
				App::import('Helper', 'Html');$html = new HtmlHelper();
				$PatientOrders= new PatientOrders();
				$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_plan' => 2, 'plan_radiology_id' => $plan_radiology_id), array('escape' => false));										
				$PatientOrders->addActivitiesItem($data['EncounterPlanRadiology']['ordered_by_id'], $data['EncounterPlanRadiology']['procedure_name'], "Radiology", "Outside Radiology", $data['EncounterPlanRadiology']['status'], $patient_id, $plan_radiology_id , $editlink);			
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
					'EncounterPlanRadiology.encounter_id' => $encounter_id, 
					'EncounterPlanRadiology.diagnosis' => $diagnosis, 
					'EncounterPlanRadiology.procedure_name' => $itemvalue)
			));
			
			foreach ($search_results as $search_result) {
				$plan_radiology_id = $search_result['EncounterPlanRadiology']['plan_radiology_id'];
				App::import('Model','PatientOrders');
				$PatientOrders= new PatientOrders();
				$PatientOrders->deleteActivitiesItem($plan_radiology_id, "Radiology", "Outside Radiology");
				$this->deleteAll(array(
					'EncounterPlanRadiology.procedure_name' => $search_result['EncounterPlanRadiology']['procedure_name'],
					'EncounterPlanRadiology.encounter_id' => $search_result['EncounterPlanRadiology']['encounter_id'],
				));
				
				App::import('Model', 'Order');
				$order = new Order();

				$order->deleteAll(array(
					'Order.test_name' => $search_result['EncounterPlanRadiology']['procedure_name'],
					'Order.encounter_id' => $search_result['EncounterPlanRadiology']['encounter_id'],
				));				
				
				
			}
			
		} else {
			$search_result = $this->searchItem($encounter_id, $diagnosis, $itemvalue);

			if($search_result)
			{
					$plan_radiology_id = $search_result['EncounterPlanRadiology']['plan_radiology_id'];
				App::import('Model','PatientOrders');
				$PatientOrders= new PatientOrders();
				$PatientOrders->deleteActivitiesItem($plan_radiology_id, "Radiology", "Outside Radiology");
				$this->delete($plan_radiology_id);
			}
		}
	}
	
	public function execute(&$controller, $encounter_id, $diagnosis, $task, $user_id)
	{
		if(isset($controller->data['init_plan_value']))
		{
			$controller->set("init_plan_value", $controller->data['init_plan_value']);
		}
		
		switch ($task)
        {
            case "get_plans":
            {
                echo json_encode($controller->EncounterPlanRadiology->getPlans($encounter_id, $diagnosis));
                exit;
            }
            break;
            case "radiology_search":
            {
                if (!empty($controller->data))
                {
                    $search_keyword = $controller->data['autocomplete']['keyword'];
                    $search_limit = $controller->data['autocomplete']['limit'];
                    $diagnosis = $controller->params['form']['diagnosis'];
                    
                    App::import('Model', 'EncounterFrequentPrescribed');
                    $frequent = new EncounterFrequentPrescribed();

                    $results = $frequent->autocompleteSearch('radiology', $diagnosis, $search_keyword, $search_limit);
                    
                    $data_array = array();
                    
                    foreach ($results as $r)
                    {
                        $data_array[] = $r['EncounterFrequentPrescribed']['value'] . '|' . $r['EncounterFrequentPrescribed']['value'];
                    }
                    
                    echo implode("\n", $data_array);
                }
                exit();
            }
            
            case "addPlan":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $rad_plan_format = ucwords(strtolower($controller->data['item_value']));
					if(isset($controller->data['reason'])) {
						$reason = $controller->data['reason'];
					} else {
						$reason = '';
					}
					$this->notifyData = $controller->PracticeSetting->reminderDefaultJson();
                    $controller->EncounterPlanRadiology->addItem($rad_plan_format, $encounter_id, $user_id, $diagnosis, $reason);
                    echo json_encode($controller->EncounterPlanRadiology->getPlans($encounter_id, $diagnosis));
                }
                exit;
            }
            break;
            
            case "deletePlan":
            {
                if (!empty($controller->data))
                {
                    $controller->EncounterPlanRadiology->deleteItem($controller->data['item_value'], $encounter_id, $user_id, $diagnosis);
                    echo json_encode($controller->EncounterPlanRadiology->getPlans($encounter_id, $diagnosis));
                }
                exit;
            }
            break;
            
            default:
            {
                App::import('Model', 'EncounterFrequentPrescribed');
                $frequent = new EncounterFrequentPrescribed();
                
                $diagnosisList = array($diagnosis);
                if ($diagnosis == 'all') {
									$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
									$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);			
                }
                
                $controller->set('frequentData', $frequent->getFrequent('radiology', $diagnosisList, $user_id));
                
                
                $controller->set('_diagnosis', $diagnosis);
                
            }
        }
	}
	
	public function executeData(&$controller, $encounter_id, $diagnosis, $procedure_name, $task, $user_id)
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
                        $controller->EncounterPlanRadiology->setItemValue('lab_facility_name', $lab_facility_name, $encounter_id, $user_id, $diagnosis, $procedure_name);
                        $controller->EncounterPlanRadiology->setItemValue('lab_address_1', $address_1, $encounter_id, $user_id, $diagnosis, $procedure_name);
                        $controller->EncounterPlanRadiology->setItemValue('lab_address_2', $address_2, $encounter_id, $user_id, $diagnosis, $procedure_name);
                        $controller->EncounterPlanRadiology->setItemValue('lab_city', $city, $encounter_id, $user_id, $diagnosis, $procedure_name);
                        $controller->EncounterPlanRadiology->setItemValue('lab_state', $state, $encounter_id, $user_id, $diagnosis, $procedure_name);
                        $controller->EncounterPlanRadiology->setItemValue('lab_zip_code', $zip_code, $encounter_id, $user_id, $diagnosis, $procedure_name);
                        $controller->EncounterPlanRadiology->setItemValue('lab_country', $country, $encounter_id, $user_id, $diagnosis, $procedure_name);
                    }
                    $controller->EncounterPlanRadiology->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, $diagnosis, $procedure_name);
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
							
                $radiology_items = $controller->EncounterPlanRadiology->find('first', array('conditions' => array('EncounterPlanRadiology.encounter_id' => $encounter_id, 'EncounterPlanRadiology.diagnosis' => $diagnosis, 'EncounterPlanRadiology.procedure_name' => $procedure_name)));
                if ($radiology_items)
                {
                    $controller->set('RadiologyItem', $radiology_items['EncounterPlanRadiology']);
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
            
						$planRadiologyId = ($this->id) ? $this->id : $this->data['EncounterPlanRadiology']['plan_radiology_id'];
						
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
						
						$planRadiology = $this->find('first', array(
							'conditions' => array(
								'EncounterPlanRadiology.plan_radiology_id' => $planRadiologyId,
							),
							'fields' => array(
								'encounter_id', 'plan_radiology_id', 'procedure_name', 'priority', 'status',
								'modified_timestamp', 'date_ordered', 'diagnosis', 'ordered_by_id',
                 'number_of_views', 'cpt', 'body_site1', 'body_site2', 'body_site3', 'body_site4', 
                 'body_site5', 'body_site_count', 'lab_facility_name', 'patient_instruction', 'comment', 'patient_id'
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
						if (isset($planRadiology['EncounterMaster']) && $planRadiology['EncounterMaster']['patient_id']) {
							$data = array('Order' => array(
								'data_id' => $planRadiology['EncounterPlanRadiology']['plan_radiology_id'],
								'encounter_id' => $planRadiology['EncounterPlanRadiology']['encounter_id'],
								'patient_id' => $planRadiology['EncounterMaster']['patient_id'],
								'encounter_status' => $planRadiology['EncounterMaster']['encounter_status'],
								'test_name' => $planRadiology['EncounterPlanRadiology']['procedure_name'],
								'source' => '',
								'patient_firstname' => $planRadiology['EncounterMaster']['PatientDemographic']['first_name'],
								'patient_lastname' => $planRadiology['EncounterMaster']['PatientDemographic']['last_name'],
								'provider_name' => $planRadiology['EncounterMaster']['ScheduleCalendar']['UserAccount']['firstname'] . ' ' . $planRadiology['EncounterMaster']['ScheduleCalendar']['UserAccount']['lastname'],
								'priority' => $planRadiology['EncounterPlanRadiology']['priority'],
								'order_type' => 'Radiology',
								'status' => $planRadiology['EncounterPlanRadiology']['status'],
								'item_type' => 'plan_radiology',
								'date_performed' => $planRadiology['EncounterPlanRadiology']['modified_timestamp'],
								'date_ordered' => $planRadiology['EncounterPlanRadiology']['date_ordered'],
								'modified_timestamp' => $planRadiology['EncounterPlanRadiology']['modified_timestamp'],
							));								
						}						
						
            // If a new record was created
            if ($created) {
                // Add the data saved in to this user's
                // frquency data
                App::import('Model', 'EncounterFrequentPrescribed');
                $frequent = new EncounterFrequentPrescribed();
                $record = $frequent->addRecord(
                        'radiology', 
                        $planRadiology['EncounterPlanRadiology']['diagnosis'], 
                        $planRadiology['EncounterPlanRadiology']['procedure_name'],
                        $planRadiology['EncounterPlanRadiology']['ordered_by_id'],
                        $planRadiology['EncounterPlanRadiology']
                );
                
                $default = array();
                
                if (isset($record['EncounterFrequentPrescribed']['data']) && $record['EncounterFrequentPrescribed']['data']){
                  $default = json_decode($record['EncounterFrequentPrescribed']['data'], true);
                }
                
                if ($default) {
                  $this->updateAll(
                    array(
                        'EncounterPlanRadiology.number_of_views' => '\'' . Sanitize::escape($default['number_of_views']) . '\'',
                        'EncounterPlanRadiology.cpt' => '\'' . Sanitize::escape($default['cpt']) . '\'',
                        'EncounterPlanRadiology.priority' => '\'' . Sanitize::escape($default['priority']) . '\'',
                        'EncounterPlanRadiology.body_site1' => '\'' . Sanitize::escape($default['body_site1']) . '\'',
                        'EncounterPlanRadiology.body_site2' => '\'' . Sanitize::escape($default['body_site2']) . '\'',
                        'EncounterPlanRadiology.body_site3' => '\'' . Sanitize::escape($default['body_site3']) . '\'',
                        'EncounterPlanRadiology.body_site4' => '\'' . Sanitize::escape($default['body_site4']) . '\'',
                        'EncounterPlanRadiology.body_site5' => '\'' . Sanitize::escape($default['body_site5']) . '\'',
                        'EncounterPlanRadiology.body_site_count' => '\'' . Sanitize::escape($default['body_site_count']) . '\'',
                        'EncounterPlanRadiology.lab_facility_name' => '\'' . Sanitize::escape($default['lab_facility_name']) . '\'',
                        'EncounterPlanRadiology.patient_instruction' => '\'' . Sanitize::escape($default['patient_instruction']) . '\'',
                        'EncounterPlanRadiology.comment' => '\'' . Sanitize::escape($default['comment']) . '\'',
                    ), 
                    array(
                        'EncounterPlanRadiology.plan_radiology_id' => $planRadiology['EncounterPlanRadiology']['plan_radiology_id'],
                    ));
                }                
                
								$order->create();
								if ($data && $this->createOrder) {
									$order->save($data);																	
								}								
            } else {
              
                // Add the data saved in to this user's
                // frquency data
                App::import('Model', 'EncounterFrequentPrescribed');
                $frequent = new EncounterFrequentPrescribed();
                $frequent->updateData(
                        'radiology', 
                        $planRadiology['EncounterPlanRadiology']['diagnosis'], 
                        $planRadiology['EncounterPlanRadiology']['procedure_name'],
                        $planRadiology['EncounterPlanRadiology']['ordered_by_id'],
                        $planRadiology['EncounterPlanRadiology']
                );              
              
							$current = $order->find('first', array(
								'conditions' => array(
									'Order.item_type' => 'plan_radiology',
									'Order.data_id' => $planRadiologyId,
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
									'Order.item_type' => 'plan_radiology',
									'Order.data_id' => $this->id,
								),
							));
							
							if ($current) {
								$order->delete($current['Order']['encounter_order_id']);
							}								
				}
				
	public function generateCombined($encounterId) {
		$modelname = $this->name;
		$field = 'procedure_name';
		$idField = 'plan_radiology_id';
		
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
				$copy[$modelname]['reason'] = $diagnosis;
				
				$this->create();
				$this->save($copy);
			}
			
		}
		
	}
}


?>
