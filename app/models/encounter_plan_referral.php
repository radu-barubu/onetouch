<?php

class EncounterPlanReferral extends AppModel
{
	public $name = 'EncounterPlanReferral';
	public $primaryKey = 'plan_referrals_id';
	public $useTable = 'encounter_plan_referrals';
	
	public $actsAs = array('Auditable' => 'Attachments - Referrals', 'Containable');

	public $belongsTo = array(
			'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);
	
	public $createOrder = true;
	

	public function afterSave($created) {
		parent::afterSave($created);

		App::import('Model', 'Order');
		$order = new Order();

		$planReferralId = ($this->id) ? $this->id : $this->data['EncounterPlanReferral']['plan_referrals_id'];

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
		
		$planReferral = $this->find('first', array(
			'conditions' => array(
				'EncounterPlanReferral.plan_referrals_id' => $planReferralId,
			),
			'fields' => array(
				'encounter_id', 'plan_referrals_id', 'referred_to', 'status',
				'modified_timestamp', 'date_ordered'
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
		if (isset($planReferral['EncounterMaster']) && $planReferral['EncounterMaster']['patient_id']) { 
			$data = array('Order' => array(
				'data_id' => $planReferral['EncounterPlanReferral']['plan_referrals_id'],
				'encounter_id' => $planReferral['EncounterPlanReferral']['encounter_id'],
				'patient_id' => $planReferral['EncounterMaster']['patient_id'],
				'encounter_status' => $planReferral['EncounterMaster']['encounter_status'],
				'test_name' => $planReferral['EncounterPlanReferral']['referred_to'],
				'source' => '',
				'patient_firstname' => $planReferral['EncounterMaster']['PatientDemographic']['first_name'],
				'patient_lastname' => $planReferral['EncounterMaster']['PatientDemographic']['last_name'],
				'provider_name' => $planReferral['EncounterMaster']['ScheduleCalendar']['UserAccount']['firstname'] . ' ' . $planReferral['EncounterMaster']['ScheduleCalendar']['UserAccount']['lastname'],
				'priority' => '',
				'order_type' => 'Referral',
				'status' => $planReferral['EncounterPlanReferral']['status'],
				'item_type' => 'plan_referral',
				'date_performed' => $planReferral['EncounterPlanReferral']['modified_timestamp'],
				'date_ordered' => $planReferral['EncounterPlanReferral']['date_ordered'],
				'modified_timestamp' => $planReferral['EncounterPlanReferral']['modified_timestamp'],
			));									
		}

		// If a new record was created
		if ($created && $this->createOrder) {
				$order->create();
				$order->save($data);

		} else {

			$current = $order->find('first', array(
				'conditions' => array(
					'Order.item_type' => 'plan_referral',
					'Order.data_id' => $planReferralId,
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
						'Order.item_type' => 'plan_referral',
						'Order.data_id' => $this->id,
					),
				));

				if ($current) {
					$order->delete($current['Order']['encounter_order_id']);
				}								
	}	
	
	public function getPatientReferralItems($patient_id)
	{
		$encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
		$result = array();
		if($encounter_items)
		{
			foreach($encounter_items as $encounter_item)
			{
			   $result[] = $encounter_item['encounter_id'];
			}
		}

		$patient_referral_items = $this->find('all', array('conditions' => array('EncounterMaster.encounter_id' => $result)));

		return $patient_referral_items;
	}

	public function getItemsByPatient($patient_id)
	{
		$search_results = $this->find('all',
			array(
				'conditions' => array('EncounterMaster.patient_id' => $patient_id)
			)
		);

		return $search_results;
	}

	public function getValues($encounter_id, &$plan = array(), $combine = false)
	{
		$this->belongsTo = array();
		
		$params = array(
					'conditions' => array('EncounterPlanReferral.encounter_id' => $encounter_id),
		);
		
		if ($combine) {
			$params['group'] = array('EncounterPlanReferral.referred_to');
		}
		
		$search_result = $this->find(
				'all', $params);


		foreach($search_result as $v) {

			$v = $v['EncounterPlanReferral'];

			$data = array();

		    $data['referred_to'] = $v['referred_to'];
		    $data['specialties'] = $v['specialties'];
		    $data['practice_name'] = $v['practice_name'];
		    $data['address1'] = $v['address1'];
		    $data['address2'] = $v['address2'];
		    $data['city'] = $v['city'];
		    $data['state'] = $v['state'];
		    $data['zipcode'] = $v['zipcode'];
		    $data['country'] = $v['country'];
		    $data['office_phone'] = $v['office_phone'];
		    $data['reason'] = $v['reason'];
		    //$data['diagnosis'] = $v['diagnosis'];
		    $data['icd_code'] = $v['icd_code'];
		    $data['referred_by'] = $v['referred_by'];
		    $data['date_ordered'] = $v['date_ordered'];
		    $data['visit_summary'] = $v['visit_summary']? 'yes':'no';
		    $data['status'] = $v['status'];
		    $data['diagnosis'] = $v['diagnosis'];
		    $data['assessment_diagnosis'] = $v['assessment_diagnosis'];

			$plan[$v['assessment_diagnosis']]['referral'][] = $data;
			
			if ($combine) {
				$plan['combined']['referral'][] = $data;
			}
			
		if ($combine) {
			foreach ($plan as $diagnosis => $data) {
				if ($diagnosis == 'combined') {
					continue;
				}
				
				if (!isset($plan[$diagnosis]['referral'])) {
					continue;
				}				
				
				$plan[$diagnosis]['referral'] = $plan['combined']['referral'];
			}
		}			
			
		}


		return $plan;
	}


	private function searchItem($encounter_id, $diagnosis, $plan)
	{
		$search_result = $this->find(
				'first',
				array(
					'conditions' => array('EncounterPlanReferral.encounter_id' => $encounter_id, 
						'EncounterPlanReferral.assessment_diagnosis' =>$diagnosis, 
						'EncounterPlanReferral.referred_to' => $plan)
				)
		);

		if(count($search_result) > 0)
		{
			return $search_result;
		}
		else
		{
			return false;
		}
	}

	public function setItemValue($field, $value, $encounter_id, $user_id, $diagnosis, $referred_to)
	{
		$assessmentDiagnosis = $diagnosis;
		if ($diagnosis == 'all') {
			$list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			$diagnosis = $list[0]['EncounterAssessment']['diagnosis'];
			$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $list);
			$allDiagnosis = implode(', ', $diagnosisList);
			
		
			$search_results = $this->find(
					'all',
					array(
						'conditions' => array(
							'EncounterPlanReferral.encounter_id' => $encounter_id, 
							'EncounterPlanReferral.assessment_diagnosis' => $diagnosisList, 
							'EncounterPlanReferral.referred_to' => $referred_to)
					)
			);
			
			$searchMap = array();
			foreach ($search_results as $s) {
				$searchMap[$s['EncounterPlanReferral']['assessment_diagnosis']] = $s;
			}
			
			$createOrder = true;
			foreach ($diagnosisList as $d) {
				if (isset($searchMap[$d])) {
					$searchMap[$d]['EncounterPlanReferral'][$field] = $value;
					$this->createOrder = $createOrder;
					$this->save($searchMap[$d]);
					$createOrder = false;
				}
				
			}
		} else {
			
			$search_result = $this->find(
					'first',
					array(
						'conditions' => array(
							'EncounterPlanReferral.encounter_id' => $encounter_id, 
							'EncounterPlanReferral.assessment_diagnosis' => $diagnosis, 
							'EncounterPlanReferral.referred_to' => $referred_to)
						,'recursive' => -1
					)
			);
			$data = array();

			if(!empty($search_result))
			{
				$data['EncounterPlanReferral']['plan_referrals_id'] = $search_result['EncounterPlanReferral']['plan_referrals_id'];
				$data['EncounterPlanReferral']['encounter_id'] = $search_result['EncounterPlanReferral']['encounter_id'];
				$data['EncounterPlanReferral']['assessment_diagnosis'] = $search_result['EncounterPlanReferral']['assessment_diagnosis'];
			}
			else
			{
				$this->create();
				$data['EncounterPlanReferral']['encounter_id'] = $encounter_id;
				$data['EncounterPlanReferral']['assessment_diagnosis'] = $diagnosis;
				$data['EncounterPlanReferral']['diagnosis'] = $diagnosis;
				$data['EncounterPlanReferral']['referred_to'] = $referred_to;
				$data['EncounterPlanReferral']['date_ordered'] = __date("Y-m-d H:i:s");
				$data['EncounterPlanReferral']['status'] = "Open";
			}

			$data['EncounterPlanReferral']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['EncounterPlanReferral']['modified_user_id'] = $user_id;
			$data['EncounterPlanReferral'][$field] = $value;

			$this->save($data);			
		}
	}

	public function getItemValue($field, $encounter_id, $default_text = '')
	{
		$search_result = $this->find(
				'first',
				array(
					'conditions' => array('EncounterPlanReferral.encounter_id' => $encounter_id)
				)
		);

		if(!empty($search_result))
		{
			return $search_result['EncounterPlanReferral'][$field];
		}
		else
		{
			return $default_text;
		}
	}

	public function addItem($item_value, $encounter_id, $user_id, $diagnosis, $patient_id, $refer_type = 'referred_to')
	{
		if ($diagnosis == 'all') {
			
			$list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $list);
			$allDiagnosis = implode(', ', $diagnosisList);
			$createOrder = true;
			foreach ($diagnosisList as $diagnosis) {
				
				$this->create();
				$data = array();
				$data['EncounterPlanReferral']['encounter_id'] = $encounter_id;
				$data['EncounterPlanReferral']['patient_id'] = $patient_id;
				$data['EncounterPlanReferral']['diagnosis'] = $allDiagnosis;
			//	$data['EncounterPlanReferral']['reason'] = $allDiagnosis;
				$data['EncounterPlanReferral']['assessment_diagnosis'] = $diagnosis;
				$data['EncounterPlanReferral']['referred_to'] = $item_value;
				$data['EncounterPlanReferral']['ordered_by_id'] = $user_id;
				$data['EncounterPlanReferral']['refer_type'] = $refer_type;
				$data['EncounterPlanReferral']['date_ordered'] = __date("Y-m-d H:i:s");
				$data['EncounterPlanReferral']['status'] = 'Open';
                        if ($refer_type == 'referred_by') {
                                $data['EncounterPlanReferral']['status'] = 'Done';
                        }
				$data['EncounterPlanReferral']['reminder_notify_json'] = $this->notifyData;
				$data['EncounterPlanReferral']['modified_timestamp'] = __date("Y-m-d H:i:s");
				$data['EncounterPlanReferral']['modified_user_id'] = $user_id;
				$this->createOrder = $createOrder;
				$this->save($data);			
				$createOrder = false;
			}
		} else {
			$data = array();

			//$search_result = $this->searchItem($encounter_id, $diagnosis, $item_value);

		/*if($search_result)
		{
			$data['EncounterPlanReferral']['plan_referrals_id'] = $search_result['EncounterPlanReferral']['plan_referrals_id'];
		}
		else*/
		{
			$this->create();
			$data['EncounterPlanReferral']['encounter_id'] = $encounter_id;
			$data['EncounterPlanReferral']['patient_id'] = $patient_id;
			$data['EncounterPlanReferral']['diagnosis'] = $diagnosis;
			$data['EncounterPlanReferral']['assessment_diagnosis'] = $diagnosis;
			$data['EncounterPlanReferral']['referred_to'] = $item_value;
			$data['EncounterPlanReferral']['ordered_by_id'] = $user_id;
			$data['EncounterPlanReferral']['refer_type'] = $refer_type;
			$data['EncounterPlanReferral']['date_ordered'] = __date("Y-m-d H:i:s");
			$data['EncounterPlanReferral']['status'] = 'Open';
			
			if ($refer_type == 'referred_by') {
				$data['EncounterPlanReferral']['status'] = 'Done';
			}
			
			$data['EncounterPlanReferral']['reminder_notify_json'] = $this->notifyData;
		}

			$data['EncounterPlanReferral']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['EncounterPlanReferral']['modified_user_id'] = $user_id;

			$this->save($data);			
		}
	}

	public function deleteItem($itemvalue, $encounter_id, $user_id, $diagnosis)
	{
		//$search_result = $this->searchItem($encounter_id, $diagnosis, $itemvalue);
		
		if ($diagnosis == 'all') {
			$item = $this->find('first', array(
				'conditions' => array(
					'EncounterPlanReferral.plan_referrals_id' => $itemvalue,
				),
			));

			if ($item) {
				$referrals = $this->find('all', array(
					'conditions' => array(
						'EncounterPlanReferral.'. $item['EncounterPlanReferral']['refer_type']  => $item['EncounterPlanReferral'][$item['EncounterPlanReferral']['refer_type']],
						'EncounterPlanReferral.encounter_id' => $item['EncounterPlanReferral']['encounter_id'],
					),
				));
				
				foreach ($referrals as $r) {
					$this->delete($r['EncounterPlanReferral']['plan_referrals_id']);
				}
				
				
			}
			
		} else {
			$plan_referrals_id = $itemvalue;
			$this->delete($plan_referrals_id);
			
		}
		
		
	}

	public function getPlans($encounter_id, $diagnosis)
	{
		if ($diagnosis == 'all') {
			$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			
			$diagnosis = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);
			$procedure_items = $this->find('all', array(
				'conditions' => array('EncounterPlanReferral.encounter_id' => $encounter_id, 'EncounterPlanReferral.assessment_diagnosis' => $diagnosis),
				'group' => array('EncounterPlanReferral.referred_to'),
			));
		} else {
			$procedure_items = $this->find('all', array('conditions' => array('EncounterPlanReferral.encounter_id' => $encounter_id, 'EncounterPlanReferral.assessment_diagnosis' => $diagnosis)));
		} 			
		
		
		$plan_array = array();
		$i=0;
		foreach($procedure_items as $procedure_item)
		{
			$plan_array[$procedure_item['EncounterPlanReferral']['plan_referrals_id']]["attached"] = $procedure_item['EncounterPlanReferral']['visit_summary'];
			$plan_array[$procedure_item['EncounterPlanReferral']['plan_referrals_id']]["name"] = $procedure_item['EncounterPlanReferral']['referred_to'];
			$plan_array[$procedure_item['EncounterPlanReferral']['plan_referrals_id']]["id"] = $procedure_item['EncounterPlanReferral']['plan_referrals_id'];
			$plan_array[$procedure_item['EncounterPlanReferral']['plan_referrals_id']]["refer_type"] = $procedure_item['EncounterPlanReferral']['refer_type'];
			$i++;
		}
		return $plan_array;
	}
	
	
	public function getReferral($plan_referral_id)
	{
		$referral = $this->row(array('conditions' => 
			array('EncounterPlanReferral.plan_referrals_id' => $plan_referral_id))
		);
		
		return $referral;
	}
	
	public function execute(&$controller, $encounter_id, $diagnosis, $task, $user_id, $role_id, $patient_id)
	{
		if(isset($controller->data['init_plan_value']))
		{
			$controller->set("init_plan_value", $controller->data['init_plan_value']);
		}
		
		
		switch ($task)
        {
            case "get_plans":
            {
            	$controller->layout = 'empty';
                //echo json_encode($controller->EncounterPlanReferral->getPlans($encounter_id, $diagnosis));
            	$items = $controller->EncounterPlanReferral->getPlans($encounter_id, $diagnosis);
            	
            	$controller->set('items', $items);
                echo  $controller->render('sections/plan_referral_items_ordered_list');
                
                exit;
            }
            break;
            
            case "addPlan":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $plan_referral_format = ucwords(strtolower($controller->data['item_value']));
										$refer_type = isset($controller->params['form']['refer_type']) ? $controller->params['form']['refer_type'] : 'referred_to';
										$refer_type = ($refer_type == 'referred_by') ? $refer_type : 'referred_to'; 
										
					$this->notifyData = $controller->PracticeSetting->reminderDefaultJson();
                    $controller->EncounterPlanReferral->addItem($plan_referral_format, $encounter_id, $user_id, $diagnosis, $patient_id, $refer_type);
                    //echo json_encode($controller->EncounterPlanReferral->getPlans($encounter_id, $diagnosis));
                    
                    $controller->layout = 'empty';
                    $items = $controller->EncounterPlanReferral->getPlans($encounter_id, $diagnosis);
                    $controller->set('items', $items);
                    echo  $controller->render('sections/plan_referral_items_ordered_list');
                    
                    
                    
                    /*$last_insert_id = $controller->EncounterPlanRx->getLastInsertId();*/
                    
                    $referral_items = $controller->DirectoryReferralList->find('first', array('conditions' => array('DirectoryReferralList.referral_list_id' => $controller->data['item_id'])));
                    
                    $tmp = $user_id; 
                    
                    if ($referral_items)
                    {
                        extract($referral_items['DirectoryReferralList']);
                        
                        $user_id = $tmp;
                        //Insert the details of the Referral
                        //if they screw up and use ALL CAPS
                        $controller->EncounterPlanReferral->setItemValue('specialties', $specialties, $encounter_id, $user_id, $diagnosis, ucwords(strtolower($controller->data['item_value'])));
                        $controller->EncounterPlanReferral->setItemValue('practice_name', $practice_name, $encounter_id, $user_id, $diagnosis, ucwords(strtolower($controller->data['item_value'])));
                        $controller->EncounterPlanReferral->setItemValue('address1', $address_1, $encounter_id, $user_id, $diagnosis, ucwords(strtolower($controller->data['item_value'])));
                        $controller->EncounterPlanReferral->setItemValue('address2', $address_2, $encounter_id, $user_id, $diagnosis, ucwords(strtolower($controller->data['item_value'])));
                        $controller->EncounterPlanReferral->setItemValue('city', $city, $encounter_id, $user_id, $diagnosis, ucwords(strtolower($controller->data['item_value'])));
                        $controller->EncounterPlanReferral->setItemValue('state', $state, $encounter_id, $user_id, $diagnosis, ucwords(strtolower($controller->data['item_value'])));
                        $controller->EncounterPlanReferral->setItemValue('zipcode', $zip_code, $encounter_id, $user_id, $diagnosis, ucwords(strtolower($controller->data['item_value'])));
                        $controller->EncounterPlanReferral->setItemValue('country', $country, $encounter_id, $user_id, $diagnosis, ucwords(strtolower($controller->data['item_value'])));
                        $controller->EncounterPlanReferral->setItemValue('office_phone', $phone_number, $encounter_id, $user_id, $diagnosis, $controller->data['item_value']);
                        
                        
                        $diagnosisList = array($diagnosis);
                        if ($diagnosis == 'all') {
                          $encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
                          $diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);			
                        } 
                        
                        foreach ($diagnosisList as $d) {
                          // Add the data saved in to this user's
                          // frquency data
                          App::import('Model', 'EncounterFrequentPrescribed');
                          $frequent = new EncounterFrequentPrescribed();
                          $frequent->addRecord(
                                  'referral', 
                                  $d, 
                                  $controller->data['item_id'],
                                  $user_id
                          );                               
                        }
                        
                    }
                }
                exit;
            }
            break;
            
            case "deletePlan":
            {
                if (!empty($controller->data))
                {
                    $controller->EncounterPlanReferral->deleteItem($controller->data['item_value'], $encounter_id, $user_id, $diagnosis);
                    
                    $controller->layout = 'empty';
	                //echo json_encode($controller->EncounterPlanReferral->getPlans($encounter_id, $diagnosis));
	            	$items = $controller->EncounterPlanReferral->getPlans($encounter_id, $diagnosis);
	            	
	            	$controller->set('items', $items);
	                echo  $controller->render('sections/plan_referral_items_ordered_list');
	                
	                exit;
                    
                    
                    //echo json_encode($controller->EncounterPlanReferral->getPlans($encounter_id, $diagnosis));
                }
                exit;
            }
            break;
            
            case "referral_search":
            {
                if (!empty($controller->data))
                {
                    $search_keyword = $controller->data['autocomplete']['keyword'];
                    $search_limit = $controller->data['autocomplete']['limit'];
                    $referral_items = $controller->DirectoryReferralList->find('all', array('conditions' => array('DirectoryReferralList.physician LIKE ' => '%' . $search_keyword . '%'),'limit' => $search_limit));
                    $data_array = array();
                    
                    foreach ($referral_items as $referral_item)
                    {
                        $data_array[] = $referral_item['DirectoryReferralList']['physician'] . '|' . $referral_item['DirectoryReferralList']['referral_list_id'];
                    }
                    
                    echo implode("\n", $data_array);
                }
                exit();
            }
            break;
            
            case "referrals_load":
            {
                $controller->loadModel("DirectoryReferralList");
                $ret = array();
                $ret['referral_list'] = $controller->DirectoryReferralList->find('all');
                echo json_encode($ret);
                exit();
            }
            break;
            
            case "referred_by_load":
            {
                if (!empty($controller->data))
                {
                    $search_keyword = ''.$controller->data['autocomplete']['keyword'];
					$search_limit = $controller->data['autocomplete']['limit'];
                    $referred_by_items = $controller->UserAccount->find('all', array('conditions' => array('OR' => array('UserAccount.firstname LIKE ' => $search_keyword . '%', 'UserAccount.lastname LIKE ' => $search_keyword . '%'), array('AND' => array('UserAccount.role_id' => 3))), 'limit' => $search_limit));
                    
                    $data_array = array();
                    
                    foreach ($referred_by_items as $referred_by_item)
                    {
                        $data_array[] = $referred_by_item['UserAccount']['firstname'] . ' ' . $referred_by_item['UserAccount']['lastname'];
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
                $controller->set('frequentData', $frequent->getFrequent('referral', $diagnosisList, $user_id));
                $controller->set('_diagnosis', $diagnosis);
                
            }
        }
	}
	
	public function executeData(&$controller, $encounter_id, $diagnosis, $referred_to, $task, $user_id, $role_id)
	{
		switch ($task)
        {
            case 'referral_preview':
                $planReferralId = $controller->params['named']['plan_referrals_id'];
                echo referral::generateReferralHtml($planReferralId, $controller);
                
                exit();
                break;
                    
            case 'related_information':
                $planReferralId = $controller->params['named']['plan_referrals_id'];
                $info = array();

                if (isset($controller->params['form']['related_information'])) {
                    $info = $controller->params['form']['related_information'];
                }

                $this->saveRelatedInfo($info, $planReferralId);
                
                
                exit();
                break;
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
                    $controller->EncounterPlanReferral->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $user_id, $controller->data['submitted']['diagnosis'], $referred_to);
					if ($controller->data['submitted']['id'] == 'visit_summary')
					{
						echo json_encode($controller->EncounterPlanReferral->getPlans($encounter_id, $controller->data['submitted']['diagnosis']));
					}
                }
                exit;
            }
            break;
            
            default:
            {
		

                                $controller->loadModel('PracticeEncounterTab');
                                $controller->loadModel('EncounterMaster');
                                $controller->loadModel('ScheduleCalendar');
                                $controller->EncounterMaster->id = $encounter_id;

                                $calendarId = $controller->EncounterMaster->field('calendar_id');
                                $controller->ScheduleCalendar->id = $calendarId;
                                $schedule = $controller->ScheduleCalendar->read();

                                $tabs = $controller->PracticeEncounterTab->getAccessibleTabs($schedule['ScheduleType']['encounter_type_id'], EMR_Account::getCurretUserId());
                                $tabList = Set::extract('/PracticeEncounterTab/tab', $tabs);

                                $controller->set(compact('tabs', 'tabList'));
					
								if ($diagnosis == 'all') {

									// Try to generate copies of referrals for each diagnosis
									// This is to prevent the bug where a race condition
									// is generated when all auto-save fields trigger
									// submission for a diagnosis that doesn't have a copy
									// of the referral data
									$this->generateAll($encounter_id, $referred_to);
									
									$encounterAssessment = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
									$diagnosis = Set::extract('/EncounterAssessment/diagnosis', $encounterAssessment);			
									
									
									$referral = $controller->EncounterPlanReferral->row( array(
										'conditions' => array(
											'EncounterPlanReferral.encounter_id' => $encounter_id, 
											'EncounterPlanReferral.assessment_diagnosis' => $diagnosis, 
											'EncounterPlanReferral.referred_to' => $referred_to)));
									
								} else {
									$referral = $controller->EncounterPlanReferral->row( array(
										'conditions' => array(
											'EncounterPlanReferral.encounter_id' => $encounter_id, 
											'EncounterPlanReferral.assessment_diagnosis' => $diagnosis, 
											'EncounterPlanReferral.referred_to' => $referred_to)));
								}
							
                
                $controller->set('referral', $referral);

                // Get info on what to include in visit summary
                $relatedInformation = $controller->EncounterPlanReferral->getRelatedInfo($referral['plan_referrals_id']);
                $controller->set('relatedInformation', $relatedInformation);
                
                //Check for Physician
                if ($role_id == 3)
                {
                    $current_user_detail = $controller->UserAccount->getCurrentUser($user_id);
                    $current_user_name = $current_user_detail['firstname'] . ' ' . $current_user_detail['lastname'];
                    $controller->set('physician_name', $current_user_name);
                }
				$notify = $controller->PracticeSetting->find('first', array('fields' => 'reminder_notify_json', 'recursive' => -1));				
				$controller->Set('notify', $notify['PracticeSetting']['reminder_notify_json']);
            }
        }
		
	}
        
    /**
     * Gets the info/setting on what should be included
     * in the summary that is to be sent along with the referral
     * 
     * @param integer $planReferralId Plan referral id
     * @return array Array containing info on what summary info to include
     */
    public function getRelatedInfo($planReferralId){
        $info = array();
        
        $referral = $this->find('first', array(
            'conditions' => array(
                'EncounterPlanReferral.plan_referrals_id' => $planReferralId
            ),
        ));
        
        $info = '';
        
        if ($referral && isset($referral['EncounterPlanReferral']['related_information'])) {
            $info = trim($referral['EncounterPlanReferral']['related_information']);
        }
        
        // If there are no settings yet, 
        // create a default setting where all info are included
        if (!$info) {
            $info = array(
                'cc' => 1,
								'hpi' => 1,
                'medical_history' => 1,
                'meds_allergies' => 1,
                'ros' => 1,
                'pe' => 1,
                'labs_procedures' => 1,
                'poc' => 1,
                'assessment' => 1,
                'plan' => 1,
								'vitals' => 1,
            );
            
            // Update the record with the default setting
            $this->saveRelatedInfo($info, $referral);
        } else {
            // Convert info data into array form
            $info = json_decode($info, true);
            
        }
        
        return $info;
    }
    
    /**
     *
     * @param array Array containing info on what summary info to include
     * @param mixed $planReferralId Optional. Can be the Plan referral id,
     *  or the associative array representing the model. If omitted, the id
     *  of the current record is used (if available)
     * @return boolean True if successful. Otherwise, false
     */
    public function saveRelatedInfo($info, $planReferralId = false){

        // If passed referralId is not an actual model data...
        if (!is_array($planReferralId)) {

            // ... checks if it is actually set
            // (if not set, it default to false)
            if ($planReferralId === false) {
                // Not set, use the current model's id
                $planReferralId = $this->id;
            }
            
            //  Get the referral info
            $referral = $this->find('first', array(
                'conditions' => array(
                    'EncounterPlanReferral.plan_referrals_id' => $planReferralId
                ),
            ));
            
        } else {
            // Referral id was actuallu an array representing the model data
            // use it and save us some overhead with querying the database
            $referral = $planReferralId;
            
        }
        
        // Referral not found, abort
        if (!$referral) {
            return false;
        }
        
        // Convert to json format
        $info = json_encode($info);

        // Update record and save
        $referral['EncounterPlanReferral']['related_information'] = $info;
        
        return $this->save($referral, false);
    }
        
		
	public function generateAll($encounter_id, $referred_to) {
			$list = ClassRegistry::init('EncounterAssessment')->getAllAssessments($encounter_id);
			$diagnosis = $list[0]['EncounterAssessment']['diagnosis'];
			$diagnosisList = Set::extract('/EncounterAssessment/diagnosis', $list);
			$allDiagnosis = implode(', ', $diagnosisList);
			
		
			$search_results = $this->find(
					'all',
					array(
						'conditions' => array(
							'EncounterPlanReferral.encounter_id' => $encounter_id, 
							'EncounterPlanReferral.assessment_diagnosis' => $diagnosisList, 
							'EncounterPlanReferral.referred_to' => $referred_to)
					)
			);
			
			$searchMap = array();
			$baseDiagnosis = '';
			foreach ($search_results as $s) {
				$searchMap[$s['EncounterPlanReferral']['assessment_diagnosis']] = $s;
				
				
				if (in_array($s['EncounterPlanReferral']['assessment_diagnosis'], $diagnosisList)) {
					$baseDiagnosis = $s['EncounterPlanReferral']['assessment_diagnosis'];
				}
			}
			
			foreach ($diagnosisList as $d) {
				if (isset($searchMap[$d])) {
					continue;
				}
				
				if ($baseDiagnosis) {
					$data = $searchMap[$baseDiagnosis];
					unset($data['EncounterPlanReferral']['plan_referrals_id']);
				}

				$this->create();
				$data['EncounterPlanReferral']['encounter_id'] = $encounter_id;
				$data['EncounterPlanReferral']['assessment_diagnosis'] = $d;
				$data['EncounterPlanReferral']['diagnosis'] = $allDiagnosis;
				$data['EncounterPlanReferral']['referred_to'] = $referred_to;
				$data['EncounterPlanReferral']['date_ordered'] = __date("Y-m-d H:i:s");
				$data['EncounterPlanReferral']['status'] = "Open";
				$this->save($data);
			}			
			
		
	}
	
	public function generateCombined($encounterId) {
		$modelname = $this->name;
		$field = 'referred_to';
		$idField = 'plan_referrals_id';
		
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
			$diagnosis = $p[$modelname]['assessment_diagnosis'];
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
		
		
		$allDiagnosis = implode(', ', $diagnosisList);
		foreach ($planMap as $diagnosis => $dataList) {
			$missing = array_diff(array_keys($uniqueData), array_keys($dataList));
			
			if (!$missing) {
				continue;
			}
			
			foreach ($missing as $m) {
				$copy = $uniqueData[$m];
				
				unset($copy[$modelname][$idField]);
				
				$copy[$modelname]['assessment_diagnosis'] = $diagnosis;
				$copy[$modelname]['diagnosis'] = $allDiagnosis;
				
				$this->create();
				$this->save($copy);
			}
			
		}
		
	}	
		
}

?>
