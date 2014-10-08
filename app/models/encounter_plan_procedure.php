<?php

class EncounterPlanProcedure extends AppModel 
{ 
	public $name = 'EncounterPlanProcedure'; 
	public $primaryKey = 'plan_procedures_id';
	public $useTable = 'encounter_plan_procedures';
	
	public $actsAs = array('Auditable' => 'Medical Information - Procedures - Outside Procedure', 'Containable');
	
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
		$options['fields'] = array('EncounterPlanProcedure.*', 'DES_DECRYPT(PatientDemo.first_name) as patient_firstname', 'DES_DECRYPT(PatientDemo.last_name) as patient_lastname','PatientDemo.patient_id');
		$options['order'] = array('EncounterPlanProcedure.date_ordered DESC');
		$options['joins'] = array(					
					array(
						'table' => 'encounter_master'
						, 'type' => 'INNER'
						, 'alias' => 'EncounterMastr'
						, 'conditions' => array(
						'EncounterPlanProcedure.encounter_id = EncounterMastr.encounter_id'
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
			'conditions' => array('EncounterPlanProcedure.encounter_id' => $encounter_id),
		);
		
		if ($combine) {
			$params['group'] = array('EncounterPlanProcedure.test_name');
		}
		
		$procedure_items = $this->find('all', $params);
		
		foreach($procedure_items as $v) {
			
			$v = $v['EncounterPlanProcedure'];
			
			$plan[$v['diagnosis']]['procedure']['items'][] = $v['test_name'];
			$plan[$v['diagnosis']]['procedure'][$v['test_name']] = $v;
			
			if ($combine) {
				$plan['combined']['procedure']['items'][] = $v['test_name'];
				$plan['combined']['procedure'][$v['test_name']] = $v;
			}			
		}
		
		if ($combine) {
			foreach ($plan as $diagnosis => $data) {
				if ($diagnosis == 'combined') {
					continue;
				}
				
				if (!isset($plan[$diagnosis]['procedure'])) {
					continue;
				}				
				
				$plan[$diagnosis]['procedure'] = $plan['combined']['procedure'];
			}
		}		
		
		return $plan;
	}
	
	public function getAllProcedures($encounter_id, $combine = false)
	{
		$params = array(
			'conditions' => array('EncounterPlanProcedure.encounter_id' => $encounter_id),
		);
		
		
		if ($combine) {
			$params['group'] = array('EncounterPlanProcedure.test_name');
		}
		
		$search_result = $this->find(
				'all', $params
		);
		
		$ret = array();
		
		if(count($search_result) > 0)
		{
			foreach($search_result as $item)
			{
				if(strlen($item['EncounterPlanProcedure']['test_name']) > 0)
				{
					$ret[] = $item['EncounterPlanProcedure']['test_name'];
				}
			}
		}
		
		array_unique($ret);
		return $ret;
	}
	
	
	private function searchItem($encounter_id, $diagnosis, $test_name)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterPlanProcedure.encounter_id' => $encounter_id, 'EncounterPlanProcedure.diagnosis' =>$diagnosis, 'EncounterPlanProcedure.test_name' => $test_name)
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
							'EncounterPlanProcedure.encounter_id' => $encounter_id, 
							'EncounterPlanProcedure.diagnosis' => $diagnosisList, 
							'EncounterPlanProcedure.test_name' => $test_name)
					)
			);
			
			$searchMap = array();
			foreach ($search_results as $s) {
				$searchMap[$s['EncounterPlanProcedure']['diagnosis']] = $s;
			}
			$createOrder = true;
			foreach ($diagnosisList as $d) {
				if (isset($searchMap[$d])) {
					$searchMap[$d]['EncounterPlanProcedure'][$field] = $value;
					$this->save($searchMap[$d]);
				} else {

					$data = array();
					$this->create();
					$data['EncounterPlanProcedure']['diagnosis'] = $d;			
					$data['EncounterPlanProcedure']['test_name'] = $test_name;			
					$data['EncounterPlanProcedure']['encounter_id'] = $encounter_id;			
					$data['EncounterPlanProcedure']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$data['EncounterPlanProcedure']['modified_user_id'] = $user_id;
					$data['EncounterPlanProcedure']['ordered_by_id'] = $user_id;
					$data['EncounterPlanProcedure'][$field] = $value;
					$this->createOrder = $createOrder;
					$this->save($data);
					$createOrder = false;
				}
			}			
			
		} else {
			$search_result = $this->find(
					'first', 
					array(
						'conditions' => array('EncounterPlanProcedure.encounter_id' => $encounter_id, 'EncounterPlanProcedure.diagnosis' =>$diagnosis, 'EncounterPlanProcedure.test_name' => $test_name)
					)
			);

			if(!empty($search_result) )
			{
				$data = array();
				$data['EncounterPlanProcedure']['plan_procedures_id'] = $search_result['EncounterPlanProcedure']['plan_procedures_id'];			
				$data['EncounterPlanProcedure']['modified_timestamp'] = __date("Y-m-d H:i:s");
				$data['EncounterPlanProcedure']['modified_user_id'] = $user_id;
				$data['EncounterPlanProcedure'][$field] = $value;
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

			$search_result = $this->searchItem($encounter_id, $diagnosis, $item_value);

			if($search_result)
			{
				$data = $search_result;
			}
			else
			{
				$this->create();
				$data['EncounterPlanProcedure']['encounter_id'] = $encounter_id;
				$data['EncounterPlanProcedure']['diagnosis'] = $diagnosis;
				$data['EncounterPlanProcedure']['test_name'] = $item_value;
				$data['EncounterPlanProcedure']['ordered_by_id'] = $user_id;
				$data['EncounterPlanProcedure']['date_ordered'] = __date("Y-m-d H:i:s");
				$data['EncounterPlanProcedure']['status'] = 'Open';
				$data['EncounterPlanProcedure']['reason'] = trim($reason);
				$data['EncounterPlanProcedure']['reminder_notify_json'] = $this->notifyData;
			}
			$patient_id = $this->EncounterMaster->getPatientID($encounter_id);
			$data['EncounterPlanProcedure']['patient_id'] = $patient_id;
			$data['EncounterPlanProcedure']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['EncounterPlanProcedure']['modified_user_id'] = $user_id;		

			$this->createOrder = $logPatientOrders;
			$this->save($data);
			$plan_procedures_id = $this->getLastInsertId();

			$plan_procedures_id = (intval($plan_procedures_id)>0) ? $plan_procedures_id : $data['EncounterPlanProcedure']['plan_procedures_id'];

			if ($logPatientOrders) {
				App::import('Model','PatientOrders');
				App::import('Helper', 'Html');$html = new HtmlHelper();
				$PatientOrders= new PatientOrders();
				$editlink = $html->url(array('controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id, 'view_plan' => 3, 'plan_procedures_id' => $plan_procedures_id), array('escape' => false));					
				$PatientOrders->addActivitiesItem($data['EncounterPlanProcedure']['ordered_by_id'], $data['EncounterPlanProcedure']['test_name'], "Procedure", "Outside Procedure", $data['EncounterPlanProcedure']['status'], $patient_id, $plan_procedures_id , $editlink);
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
					'EncounterPlanProcedure.encounter_id' => $encounter_id, 
					'EncounterPlanProcedure.diagnosis' => $diagnosis, 
					'EncounterPlanProcedure.test_name' => $itemvalue)
			));
			
			foreach ($search_results as $search_result) {
				$plan_procedures_id = $search_result['EncounterPlanProcedure']['plan_procedures_id'];
				App::import('Model','PatientOrders');
				$PatientOrders= new PatientOrders();
				$PatientOrders->deleteActivitiesItem($plan_procedures_id, "Procedure", "Outside Procedure");
				
				$this->deleteAll(array(
					'EncounterPlanProcedure.test_name' => $search_result['EncounterPlanProcedure']['test_name'],
					'EncounterPlanProcedure.encounter_id' => $search_result['EncounterPlanProcedure']['encounter_id'],
					
				));
				
				App::import('Model', 'Order');
				$order = new Order();

				$order->deleteAll(array(
					'Order.test_name' => $search_result['EncounterPlanProcedure']['test_name'],
					'Order.encounter_id' => $search_result['EncounterPlanProcedure']['encounter_id'],
				));				
				
				
			}			
			
			
		} else {
			$search_result = $this->searchItem($encounter_id, $diagnosis, $itemvalue);

			if($search_result)
			{
				$plan_procedures_id = $search_result['EncounterPlanProcedure']['plan_procedures_id'];
				App::import('Model','PatientOrders');
				$PatientOrders= new PatientOrders();
				$PatientOrders->deleteActivitiesItem($plan_procedures_id, "Procedure", "Outside Procedure");
				$this->delete($plan_procedures_id);
			}
		}
		
		
	}
	
	public function getPlans($encounter_id, $diagnosis)
	{
		if ($diagnosis == 'all') {
			$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			
			$diagnosis = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);

			$procedure_items = $this->find('all', array(
				'conditions' => array('EncounterPlanProcedure.encounter_id' => $encounter_id, 'EncounterPlanProcedure.diagnosis' => $diagnosis),
				'group' => array(
					'EncounterPlanProcedure.test_name'
				),
			));
			
		} else {
			$procedure_items = $this->find('all', array('conditions' => array('EncounterPlanProcedure.encounter_id' => $encounter_id, 'EncounterPlanProcedure.diagnosis' => $diagnosis)));
			
		} 		
		
		
		$plan_array = array();
			
		foreach($procedure_items as $procedure_item)
		{
			$plan_array[] = $procedure_item['EncounterPlanProcedure']['test_name'];
		}
		return $plan_array;
	}
	
	public function execute(&$controller, $encounter_id, $patient_id, $task)
	{
		$controller->set('patient_id', $patient_id);
		
        switch ($task)
        {
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPlanProcedure']['date_ordered'] = __date("Y-m-d");
					$controller->data['EncounterPlanProcedure']['encounter_id'] = $encounter_id;
                    $controller->EncounterPlanProcedure->create();
                    $controller->EncounterPlanProcedure->save($controller->data);
                    
                    $controller->EncounterPlanProcedure->saveAudit('New');
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->data['EncounterPlanProcedure']['date_ordered'] = __date("Y-m-d");
                    $controller->EncounterPlanProcedure->save($controller->data);
                    $controller->EncounterPlanProcedure->saveAudit('Update');
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $plan_procedures_id = (isset($controller->params['named']['plan_procedures_id'])) ? $controller->params['named']['plan_procedures_id'] : "";
                    //echo $family_history_id;
                    $items = $controller->EncounterPlanProcedure->find('first', array('conditions' => array('EncounterPlanProcedure.plan_procedures_id' => $plan_procedures_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['EncounterPlanProcedure']['plan_procedures_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->EncounterPlanProcedure->delete($id, false);
                        $ret['delete_count']++;
                    }
                    
                    if ($ret['delete_count'] > 0)
                    {
                        $controller->EncounterPlanProcedure->saveAudit('Delete');
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
                $encounter_items = $controller->EncounterMaster->getEncountersByPatientID($patient_id);
                
                $result = array();
                if ($encounter_items)
                {
                    foreach ($encounter_items as $encounter_item)
                    {
                        $result[] = $encounter_item['encounter_id'];
                    }
                }
                
                $controller->set('EncounterPlanProcedure', $controller->sanitizeHTML($controller->paginate('EncounterPlanProcedure', array('EncounterMaster.encounter_id' => $result))));
                
                $controller->EncounterPlanProcedure->saveAudit('View');
            }
            break;
        }
	}
	
	public function executePlanProcedures(&$controller, $encounter_id, $diagnosis, $task, $user_id)
	{
		if(isset($controller->data['init_plan_value']))
		{
			$controller->set("init_plan_value", $controller->data['init_plan_value']);
		}
		
		switch ($task)
        {
            case "get_plans":
            {
                echo json_encode($controller->EncounterPlanProcedure->getPlans($encounter_id, $diagnosis));
                exit;
            }
            break;
            case "procedure_search":
            {
                if (!empty($controller->data))
                {
                    $search_keyword = $controller->data['autocomplete']['keyword'];
                    $search_limit = $controller->data['autocomplete']['limit'];
                    $diagnosis = $controller->params['form']['diagnosis'];
                    
                    App::import('Model', 'EncounterFrequentPrescribed');
                    $frequent = new EncounterFrequentPrescribed();

                    $results = $frequent->autocompleteSearch('procedure', $diagnosis, $search_keyword, $search_limit);
                    
                    $data_array = array();
                    
                    foreach ($results as $r)
                    {
                        $data_array[] = $r['EncounterFrequentPrescribed']['value'] . '|' . $r['EncounterFrequentPrescribed']['value'];
                    }
                    
                    echo implode("\n", $data_array);
                }
                exit();
            }
            break;            
            case "addPlan":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $proc_format = ucwords(strtolower($controller->data['item_value']));
					if(isset($controller->data['reason'])) {
						$reason = $controller->data['reason'];
					} else {
						$reason = '';
					}
					$this->notifyData = $controller->PracticeSetting->reminderDefaultJson();
                    $controller->EncounterPlanProcedure->addItem($proc_format, $encounter_id, $user_id, $diagnosis, $reason);
                    echo json_encode($controller->EncounterPlanProcedure->getPlans($encounter_id, $diagnosis));
                }
                exit;
            }
            break;
            
            case "deletePlan":
            {
                if (!empty($controller->data))
                {
                    $controller->EncounterPlanProcedure->deleteItem($controller->data['item_value'], $encounter_id, $user_id, $diagnosis);
                    echo json_encode($controller->EncounterPlanProcedure->getPlans($encounter_id, $diagnosis));
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
                $controller->set('frequentData', $frequent->getFrequent('procedure', $diagnosisList, $user_id));
                $controller->set('_diagnosis', $diagnosis);
                
            }
        }
	}
	
	public function executePlanProceduresData(&$controller, $encounter_id, $diagnosis, $test_name, $task, $user_id)
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
                    $controller->EncounterPlanProcedure->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, $diagnosis, $test_name);
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
							
							
                $procedure_items = $controller->EncounterPlanProcedure->find('first', array('conditions' => array('EncounterPlanProcedure.encounter_id' => $encounter_id, 'EncounterPlanProcedure.diagnosis' => $diagnosis, 'EncounterPlanProcedure.test_name' => $test_name)));
                if ($procedure_items)
                {
                    $controller->set('ProcedureItem', $procedure_items['EncounterPlanProcedure']);
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

						$planProcedureId = ($this->id) ? $this->id : $this->data['EncounterPlanProcedure']['plan_procedures_id'];
						
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
						
						$planProcedure = $this->find('first', array(
							'conditions' => array(
								'EncounterPlanProcedure.plan_procedures_id' => $planProcedureId,
							),
							'fields' => array(
								'encounter_id', 'plan_procedures_id', 'test_name', 'status',
								'modified_timestamp', 'date_ordered', 'diagnosis', 'ordered_by_id',
                'cpt', 'body_site', 'laterality', 'comment',
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
						if (isset($planProcedure['EncounterMaster']) && $planProcedure['EncounterMaster']['patient_id']) {
							$data = array('Order' => array(
								'data_id' => $planProcedure['EncounterPlanProcedure']['plan_procedures_id'],
								'encounter_id' => $planProcedure['EncounterPlanProcedure']['encounter_id'],
								'patient_id' => $planProcedure['EncounterMaster']['patient_id'],
								'encounter_status' => $planProcedure['EncounterMaster']['encounter_status'],
								'test_name' => $planProcedure['EncounterPlanProcedure']['test_name'],
								'source' => '',
								'patient_firstname' => $planProcedure['EncounterMaster']['PatientDemographic']['first_name'],
								'patient_lastname' => $planProcedure['EncounterMaster']['PatientDemographic']['last_name'],
								'provider_name' => $planProcedure['EncounterMaster']['ScheduleCalendar']['UserAccount']['firstname'] . ' ' . $planProcedure['EncounterMaster']['ScheduleCalendar']['UserAccount']['lastname'],
								'priority' => '',
								'order_type' => 'Procedure',
								'status' => $planProcedure['EncounterPlanProcedure']['status'],
								'item_type' => 'plan_procedure',
								'date_performed' => $planProcedure['EncounterPlanProcedure']['modified_timestamp'],
								'date_ordered' => $planProcedure['EncounterPlanProcedure']['date_ordered'],
								'modified_timestamp' => $planProcedure['EncounterPlanProcedure']['modified_timestamp'],
							));									
						}
						
            // If a new record was created
            if ($created) {
                // Add the data saved in to this user's
                // frquency data
                App::import('Model', 'EncounterFrequentPrescribed');
                $frequent = new EncounterFrequentPrescribed();
                
                // If ordered by id was not given, set it to 0
                // this means this was entered in the Patient Charts
                // and might not be actually prescribed by the current provider
                if (!isset($this->data['EncounterPlanProcedure']['ordered_by_id'])) {
                    $this->data['EncounterPlanProcedure']['ordered_by_id'] = 0;
                }
                
                $record = $frequent->addRecord(
                        'procedure', 
                        $planProcedure['EncounterPlanProcedure']['diagnosis'], 
                        $planProcedure['EncounterPlanProcedure']['test_name'],
                        $planProcedure['EncounterPlanProcedure']['ordered_by_id']
                );
                
                $default = array();
                
                if (isset($record['EncounterFrequentPrescribed']['data']) && $record['EncounterFrequentPrescribed']['data']){
                  $default = json_decode($record['EncounterFrequentPrescribed']['data'], true);
                }
                
                if ($default) {
                  $this->updateAll(
                    array(
                        'EncounterPlanProcedure.cpt' => '\'' . Sanitize::escape($default['cpt']) . '\'',
                        'EncounterPlanProcedure.body_site' => '\'' . Sanitize::escape($default['body_site']) . '\'',
                        'EncounterPlanProcedure.laterality' => '\'' . Sanitize::escape($default['laterality']) . '\'',
                        'EncounterPlanProcedure.comment' => '\'' . Sanitize::escape($default['comment']) . '\'',
                    ), 
                    array(
                        'EncounterPlanProcedure.plan_procedures_id' => $planProcedure['EncounterPlanProcedure']['plan_procedures_id'],
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
                        'procedure', 
                        $planProcedure['EncounterPlanProcedure']['diagnosis'], 
                        $planProcedure['EncounterPlanProcedure']['test_name'],
                        $planProcedure['EncounterPlanProcedure']['ordered_by_id'],
                        $planProcedure['EncounterPlanProcedure']
                        
                );
                
							$current = $order->find('first', array(
								'conditions' => array(
									'Order.item_type' => 'plan_procedure',
									'Order.data_id' => $planProcedureId,
								),
							));
							
							if ($current && $data) {
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
									'Order.item_type' => 'plan_procedure',
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
		$idField = 'plan_procedures_id';
		
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
