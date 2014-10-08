<?php
  
class EmdeonOrder extends AppModel 
{
    public $name = 'EmdeonOrder';
    public $primaryKey = 'order_id';
    public $useTable = 'emdeon_orders';
    
    public $actsAs = array('Auditable' => 'Medical Information - Labs - Outside Labs', 'Containable');
    
    public $hasMany = array(
        'EmdeonOrderTest' => array(
            'className' => 'EmdeonOrderTest',
            'foreignKey' => 'order_id',
            'dependent' => true,
        ),
        'EmdeonLabResult' => array(
            'className' => 'EmdeonLabResult',
            'foreignKey' => 'order_id',
						'order' => 'EmdeonLabResult.lab_result_id DESC',
        ),
        'EmdeonOrderinsurance' => array(
            'className' => 'EmdeonOrderinsurance',
            'foreignKey' => 'order_id',
            'dependent' => true,
        )
    );
    
    public $belongsTo = array(
        'EmdeonOrderStatus' => array(
            'className' => 'EmdeonOrderStatus',
            'foreignKey' => 'order_status'
        ),
        'EmdeonRelationship' => array(
            'className' => 'EmdeonRelationship',
            'foreignKey' => 'guarantor_relationship'
        )
    );

    public $virtualFields = array(
      'order_datetime' => "STR_TO_DATE(date, '%c/%e/%Y %H:%i' )"
    );
    
    public static $orderStatus = array(
        'XC' => 'Cancelled by Client',
        'XL' => 'Cancelled by Lab',
        'C' => 'Corrected',
        'D' => 'Draft',
        'E' => 'Entered',
        'X' => 'Error',
        'F' => 'Final Reported',
        'I' => 'Entered',
        'P' => 'Partial Reported',
        'R' => 'Ready to Transmit',
        'NA' => 'Results Received',
        'TX' => 'Transmission Error',
        'T' => 'Transmitted',    
    );
    
    public $api = null;
    
    /**
     * See what type of e-lab behavior we are using and return appropriate API instance
     *
     * @return	ELabsAPI		API supporting sync(), getClients(), getHTML(), getReportList(), getReport()
     */
    public function getELabsAPI() {
    	if( is_null( $this->api )) {
    		$this->api = ClassRegistry::init( 'EmdeonLabResult' )->getELabsAPI();
    	}
    	return $this->api;
    }
    
    public function beforeSave($options)
    {
        if(isset($this->data['EmdeonOrder']['collection_datetime']))
        {
            $this->data['EmdeonOrder']['collection_datetime'] = __date("Y-m-d H:i:s", strtotime($this->data['EmdeonOrder']['collection_datetime']));
        }
        
        $this->data['EmdeonOrder']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['EmdeonOrder']['modified_user_id'] = (int)@$_SESSION['UserAccount']['user_id'];
        return true;
    }
    
	public function afterDelete(){
		parent::afterDelete();
		App::import('Model', 'Order');
		$order = new Order();

		$order->deleteAll(array(
			'Order.data_id' => $this->id,
		));
							
	}			
	
    public function beforeFind($queryData)
    {
        $this->virtualFields['ordered_by'] = sprintf("TRIM(CONCAT(%s.ref_cg_fname, ' ', %s.ref_cg_lname))", $this->alias, $this->alias);
		
		$this->virtualFields['actual_collection_date'] = "
			CAST(
				CASE order_type 
				WHEN 'PSC' THEN 
					CONCAT(
						LPAD(SUBSTRING_INDEX(expected_coll_datetime, '/', -1), 4, '0'), '-', 
						LPAD(SUBSTRING_INDEX(expected_coll_datetime, '/', 1), 2, '0'), '-', 
						LPAD(REPLACE(SUBSTRING_INDEX(expected_coll_datetime, '/', 2), CONCAT(SUBSTRING_INDEX(expected_coll_datetime, '/', 1), '/') , ''), 2, '0')
					)
				ELSE 
					DATE_FORMAT(collection_datetime, '%Y-%m-%d') 
				END
				AS DATE
			)
		";
		
		$this->virtualFields['actual_order_date'] = "
			CAST(
				CONCAT(
					LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(date, ' ', 1), '/', -1), 4, '0'), '-', 
					LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(date, ' ', 1), '/', 1), 2, '0'), '-', 
					LPAD(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(date, ' ', 1), '/', 2), CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(date, ' ', 1), '/', 1), '/') , ''), 2, '0')
				)
				
				AS DATE
			)
		";
    
        return $queryData;
    }
    
    public function afterFind($results, $primary)
    {
        for($i = 0; $i < count($results); $i++)
        {
            if(isset($results[$i][$this->alias]['order_id']))
            {
                $results[$i][$this->alias]['diagnosis'] = $this->getDiagnosisString($results[$i]);
                $results[$i][$this->alias]['single_diagnosis'] = $this->getSingleDiagnosis($results[$i]);
            }
            
            if(isset($results[$i][$this->alias]['lab']))
            {
                $labs = $this->getELabsAPI()->getLabs();
                
                $lab_name = "";
                
                foreach($labs as $lab)
                {
                    if($lab['lab'] == $results[$i][$this->alias]['lab'])
                    {
                        $lab_name = $lab['lab_name'];
                    }
                }
                
                $results[$i][$this->alias]['lab_name'] = $lab_name;
            }
        }
        
        return $results;
    }
    
    /**
    * Retrieve list of order_id by encounter
    * 
    * @param int $encounter_id Encounter ID
    * @return array Array of order_id
    */
    public function getOrderIdsByEncounter($encounter_id)
    {
		$order_ids = $this->find('list', array('conditions' => array('EmdeonOrder.encounter_id' => $encounter_id)));
		/*
        $this->EncounterMaster = ClassRegistry::init('EncounterMaster');
        $this->EncounterAssessment = ClassRegistry::init('EncounterAssessment');
        $this->PatientDemographic = ClassRegistry::init('PatientDemographic');
        
        $emdeon_xml_api = new Emdeon_XML_API();
        $valid_labs = $emdeon_xml_api->getValidLabs();
        
        $patient_id = $this->EncounterMaster->getPatientID($encounter_id);
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $mrn = $patient['mrn'];
        
        $icd9s = $this->EncounterAssessment->find('list', array(
            'group' => array('EncounterAssessment.icd_code'),
            'fields' => array('EncounterAssessment.icd_code'),
            'conditions' => array('EncounterAssessment.encounter_id' => $encounter_id, 'EncounterAssessment.icd_code !=' => '')
        ));
        
        $order_ids = array();
        
        foreach($icd9s as $icd9)
        {
            $current_order_ids = $this->getOrderByDiagnosis($mrn, $icd9, $valid_labs);
            $order_ids = array_merge($order_ids, $current_order_ids);
        }
        
        $order_ids = array_unique($order_ids);
		*/
		
        return $order_ids;
    }
    
    public function getLabResultId($order_id)
    {
        $item = $this->EmdeonLabResult->find('first', array('order' => array('EmdeonLabResult.lab_result_id' => 'DESC'), 'conditions' => array('EmdeonLabResult.order_id' => $order_id), 'fields' => 'EmdeonLabResult.lab_result_id' , 'recursive' => -1));
        if($item)
        {
            return $item['EmdeonLabResult']['lab_result_id'];
        }
        else
        {
            return '0';
        }
    }
    
    public function getOrderStatus($order_id)
    {
        $item = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id), 'fields' => 'EmdeonOrder.order_status', 'recursive' => -1));
        return $item['EmdeonOrder']['order_status'];
    }
    
    public function getOrderEmdeonId($order_id)
    {
        $item = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id), 'fields' => 'EmdeonOrder.order', 'recursive' => -1));
        return $item['EmdeonOrder']['order'];
    }
    
    /**
    * Retrieve patient mrn by order id
    * 
    * @param int $order_id Order ID
    * @return string patient mrn
    */
    public function getMrnByOrderId($order_id)
    {
        $this->recursive = -1;
        $item = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id), 'fields' => array('EmdeonOrder.person_hsi_value')));
        return $item['EmdeonOrder']['person_hsi_value'];
    }
    
    public function hasMatchedPatient($order_id)
    {
        $result = false;
        
        $order = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id), 'fields' => array('EmdeonOrder.person_first_name', 'EmdeonOrder.person_last_name'), 'recursive' => -1));

        $this->bindModel(array('hasMany' => array('PatientDemographic')));
        $patient = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.mrn' => $order['EmdeonOrder']['person_hsi_value'], 'PatientDemographic.first_name' => $order['EmdeonOrder']['person_first_name'], 'PatientDemographic.last_name' => $order['EmdeonOrder']['person_last_name'])));
        
        if($patient)
        {
            $result = $patient['PatientDemographic']['patient_id'];    
        }
        
        $this->unbindModel(array('hasMany' => array('PatientDemographic')));
        
        return $result;
    }
    
    public function getOrderTestString($order_id, $implode = true, $data = array())
    {
        $ret = array();
        
        $items = $this->getOrderTestDetails($order_id, $data);
        
        foreach($items as $item)
        {
            $ret[] = $item['description'];
        }
        
        if($implode)
        {
            return implode(", ", $ret);
        }
        else
        {
            return $ret;
        }
    }
    
    public function getOrderTestDetails($order_id, $data = array())
    {
        $result = array();
        
				// Skip query if we already have data available
				if ($data && isset($order['EmdeonOrderTest'])) {
					$order = $data;
				} else {
					$this->recursive = 3;
					$order = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id),
					 'fields' => array('EmdeonOrder.placer_order_number'), 
					 'contain' => array(
						'EmdeonOrderTest' => array(
							'fields' => array('order_id'),
							'EmdeonOrderable' => array(
								'fields' => array('description','effective_date')
										)
									)
							)
						   )
						);
				}
	 /* NOTE If you make any changes below, you may need to tweak the 'contain' filter query above */        
        foreach($order['EmdeonOrderTest'] as $ordertest)
        {
            foreach($ordertest['EmdeonOrderable'] as $orderable)
            {
                $data = array();
                $data['placer_order_number'] = $order['EmdeonOrder']['placer_order_number'];
                $data['description'] = $orderable['description'];
                $data['date'] = __date("Y-m-d H:i:s", strtotime($orderable['effective_date']));
                
                $is_duplicate = false;
                
                //check if data already exist
                foreach($result as $existing_data)
                {
                    if($existing_data['placer_order_number'] == $order['EmdeonOrder']['placer_order_number'] && $existing_data['description'] == $orderable['description'])
                    {
                        $is_duplicate = true;
                    }
                }
                
                if(!$is_duplicate)
                {
                    $result[] = $data;
                }
            }
        }
        
        return $result;
    }
		
    public function getAlert($user)
    {
        $data = array();
        
        $items = $this->find('all', array('conditions' => array('EmdeonOrder.approve' => 0), 
					  'recursive' => -1,
					  'fields' => array('EmdeonOrder.order_id', 'EmdeonOrder.referringcaregiver')
					));

        if(count($items) > 0)
        {
            foreach($items as $item)
            {
                $patient_id = $this->hasMatchedPatient($item['EmdeonOrder']['order_id']);
                
                if($patient_id)
                {
                    if($user['clinician_reference_id'] == $item['EmdeonOrder']['referringcaregiver'] && strlen($user['clinician_reference_id']) > 0)
                    {
                        $ret_arr = $this->getOrderTestDetails($item['EmdeonOrder']['order_id']);
                        $new_data = array();
                        
                        foreach($ret_arr as $ret_item)
                        {
                            $ret_item['patient_id'] = $patient_id;
                            $new_data[] = $ret_item;
                        }
                        if(count($data)<25)
                        {
                            $data = array_merge($data, $new_data);
                        }
                    }
                }
            }
        }
        
        $PatientDemographic = ClassRegistry::init('PatientDemographic');
        
        for($i = 0; $i < count($data); $i++)
        {
            $data[$i]['patient_name'] = $PatientDemographic->getPatientName($data[$i]['patient_id']);
        }
        
        return $data;
    }
    
    public function approveResult($order_id)
    {
        $data = array();
        $data['EmdeonOrder']['order_id'] = $order_id;
        $data['EmdeonOrder']['approve'] = 1;
        
        $this->save($data);
        
        //also approve in lab result table
        $lab_result_id = $this->getLabResultId($order_id);
        ClassRegistry::init('EmdeonLabResult')->approveResult($lab_result_id);
        
    }
    
    public function isApprove($order_id)
    {
        $ret = false;
        
        $item = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id),
					   'fields'=> array('EmdeonOrder.approve'),
					   'recursive' => -1
						));
        if($item)
        {
            return (($item['EmdeonOrder']['approve'] == 1) ? true: false);
        }
        
        return $ret;
    }
    
    public function getSingleDiagnosis($item)
    {
        $icd9 = array();
        
        if($item)
        {
            if(isset($item['EmdeonOrderTest']))
            {
                foreach($item['EmdeonOrderTest'] as $order_test)
                {
                    if(isset($order_test['EmdeonOrderDiagnosis']))
                    {
                        foreach($order_test['EmdeonOrderDiagnosis'] as $diagnosis)
                        {
                            $icd9['icd_code'] = $diagnosis['icd_9_cm_code'];
                            $icd9['description'] = (isset($diagnosis['description'])? $diagnosis['description'] : '' ) . ' [' . $diagnosis['icd_9_cm_code'] . ']';
                            
                            return $icd9;
                        }
                    }
                }
            }
        }
        
        $icd9['icd_code'] = '';
        $icd9['description'] = '';
        
        return $icd9;
    }
    
    public function getDiagnosisString($item)
    {
        $icd9 = array();
        
        if($item)
        {
            if(isset($item['EmdeonOrderTest']))
            {
                foreach($item['EmdeonOrderTest'] as $order_test)
                {
                    if(isset($order_test['EmdeonOrderDiagnosis']))
                    {
                        foreach($order_test['EmdeonOrderDiagnosis'] as $diagnosis)
                        {
                            $icd9[] = $diagnosis['icd_9_cm_code'];
                        }
                    }
                }
            }
        }
        
        $icd9 = array_unique($icd9);
        
        return implode(', ', $icd9);
    }
    
    public function getOrderReference($order_id)
    {
        $order = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id),
					    'fields' => 'EmdeonOrder.order',
					    'recursive' => -1));
        return $order['EmdeonOrder']['order'];
    }
    
    public function getOrderByPatient($user, $mrn, $includeTests = false)
    {
				if (!$mrn) {
					return array();
				}
				
        $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
        $cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
	
				$cacheConfig = array('duration' => '+4 hours');
				$cacheKey = $cache_file_prefix. $mrn .'_' .'orders_by_patient';
        Cache::set($cacheConfig);
        $orders = Cache::read($cacheKey);				
				
				// Check if patient. Do not use cache is logged in user is a patient
				$isPatient = (intval($user['patient_id']) !== 0);
				
				
				if ($orders && !$isPatient) {
					return ($includeTests) ? $orders : array_keys($orders);
				}
				
        $lab = $this->getELabsAPI()->getValidLabs();
        $emdeon_info = $this->getELabsAPI()->getInfo();
                
        $orders = array();
        //$this->unbindModelAll();
        $items = $this->find('all', array(
					'fields' => array(
						'EmdeonOrder.order_id', 'EmdeonOrder.placer_order_number', 
						'EmdeonOrder.approve', 'EmdeonOrder.referringcaregiver', 
						'EmdeonOrder.person_hsi_value',
					),
					'conditions' => array(
						//'EmdeonOrder.submission_date <>' => '', 
						'EmdeonOrder.person_hsi_value' => $mrn, 
						'EmdeonOrder.downloaded' => 1, 
// FIXME: the following conditions effectively hide any lab results from other (older) facilities so I commented them out (klundeen):
//						'EmdeonOrder.lab' => $lab, 
//						'EmdeonOrder.orderingorganization' => $emdeon_info['facility']
					),
					'contain' => array(
							'EmdeonLabResult' => array('fields' => 'hl7'),
							'EmdeonOrderTest' => array(
								'fields' => array('order_id'),
								'EmdeonOrderable' => array(
								'fields' => array('description','effective_date')
										)
									)						
							),
					'group' => 'EmdeonOrder.placer_order_number',
				));    
				
        foreach($items as $item)
        {
					// Placed outside of the system
					// automatically display
					if ($isPatient) {
						if ($item['EmdeonOrder']['approve'] == 1)  {

							$tests = $this->getOrderTestList($item);
							
							if (!$tests) {
								continue;
							}
							
							$orders[$item['EmdeonOrder']['order_id']] = $tests;
						}						
					} else {

							$tests = $this->getOrderTestList($item);
							
							if (!$tests) {
								continue;
							}
							
							$orders[$item['EmdeonOrder']['order_id']] = $tests;
											
					}
        }
				
				// Cache order_ids if not patient
				if (!$isPatient) {
					Cache::set($cacheConfig);
					Cache::write($cacheKey, $orders);							
				}
				
        return ($includeTests) ? $orders : array_keys($orders);
    }
		
		public function getOrderTestList($data) {

			// Use test info from db is available
			if (count($data['EmdeonOrderTest'])) {
				$test_list = array();
				
				foreach ($data['EmdeonOrderTest'] as $ot) {
					foreach($ot['EmdeonOrderable'] as $o) {
						$test_list[] = $o['description'];
					}
				}
				
				return implode(', ', $test_list);	
				
			// Use test info from HL7 is not available from db
			}	else if (isset($data['EmdeonLabResult'][0]['hl7'])) {
				
				$emdeon_hl7 = new Emdeon_HL7(json_decode($data['EmdeonLabResult'][0]['hl7'], true));
				$hl7_data = $emdeon_hl7->getData();
				$test_list = array();
				foreach ($hl7_data['test_segments'] as $test_segment) {
						for ($j = 0; $j < count($test_segment); $j++) {
								if ($test_segment[$j]['segment_type'] == 'OBR' && $test_segment[$j]['obr_order_code'] != '_NOTE') {
										$test_list[] = $test_segment[$j]['obr_description'];
								}
						}
				}

				return implode(', ', $test_list);					
				
			}		
			
			return '';
		}
    
    public function get_patient_lab_result_id($order_id)
    {
        $this->PatientLabResult = ClassRegistry::init('PatientLabResult');
        return $this->PatientLabResult->get_patient_lab_result_id($order_id);
    }
	
	
	/**
    * Retrieve Emdeon translated ICD9 from OTEMR icd9
    * 
    * @param string $icd_9_cm_code OTEMR ICD9
    * @return string Emdeon ICD9
    */
	public function getTranslatedICD9($icd_9_cm_code)
	{
		$EmdeonLiveIcd9 = ClassRegistry::init('EmdeonLiveIcd9');
		$items = $EmdeonLiveIcd9->find('first', array('conditions' => array('icd_9_cm_code' => $icd_9_cm_code . '*', 'description' => '')));
		if(!empty($items))
		{
			return $items['EmdeonLiveIcd9']['icd_9_cm_code'];
		}	
		
		return false;
	}
    
    public function getOrderByDiagnosis($mrn, $diagnosis_data, $lab, $ordering_cg_id = 0, $encounter_id = 0)
    {
        $order_ids = array();
        
        $emdeon_info = $this->getELabsAPI()->getInfo();
        $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
        $cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
	
				$cacheConfig = array('duration' => '+4 hours');
				$cacheKey = $cache_file_prefix. $mrn .'_' .'orders_by_diagnosis';
        Cache::set($cacheConfig);
        $orderIdCollection = Cache::read($cacheKey);	        
				$collectionKey = md5($diagnosis_data . $lab . $ordering_cg_id . $encounter_id);
				
				if ($orderIdCollection && isset($orderIdCollection[$collectionKey])) {
					return $orderIdCollection[$collectionKey];
				}
				
				if (!is_array($orderIdCollection)) {
					$orderIdCollection = array();
				}
				
        if(strlen($diagnosis_data) > 0)
        {
            $conditions = array();
            $conditions['EmdeonOrder.person_hsi_value'] = $mrn;
            $conditions['EmdeonOrder.downloaded'] = 1;
            $conditions['EmdeonOrder.orderingorganization'] = $emdeon_info['facility'];

						
						if ($lab != 'all') {
							$conditions['EmdeonOrder.lab'] = $lab;
						}
						
						
            if($ordering_cg_id != 0 || $ordering_cg_id != 'all')
            {
                $conditions['EmdeonOrder.ordering_cg_id'] = $ordering_cg_id;
            }
						
						if (intval($encounter_id)) {
							$conditions['EmdeonOrder.encounter_id'] = $encounter_id;
						}
            
            $items = $this->find('all', array(
							'conditions' => $conditions,
							'fields' => array(
								'order_id', 'person_hsi_value', 'downloaded', 
								'lab', 'orderingorganization', 'ordering_cg_id'
							),
							'contain' => array(
								'EmdeonOrderTest' => array(
									'fields' => array(
										'order_test_id',
									),
									'EmdeonOrderDiagnosis' => array(
										'fields' => array(
											'icd_9_cm_code'
										),
									),
								),
							)));
            $this->resetBindings();
						
            foreach($items as $item)
            {
                $icd9 = array();
                
                foreach($item['EmdeonOrderTest'] as $order_test)
                {
                    foreach($order_test['EmdeonOrderDiagnosis'] as $diagnosis)
                    {
                        $icd9[] = (float)$diagnosis['icd_9_cm_code'];
                    }
                }
                
                $icd9 = array_unique($icd9);
                
                foreach($icd9 as $icd9_item)
                {
                    if($diagnosis_data == 'all')
                    {
                        $order_ids[] = $item['EmdeonOrder']['order_id'];
                    }
                    else
                    {
						$translated_icd9 = $this->getTranslatedICD9($diagnosis_data);
						
						if($translated_icd9)
						{
							if((float)$icd9_item == (float)$this->getTranslatedICD9($diagnosis_data))
							{
								$order_ids[] = $item['EmdeonOrder']['order_id'];
							}
						}
                    }
                }
            }
        }
        else
        {
            $order_ids[] = 0;
        }
        
				
				$orderIdCollection[$collectionKey] = $order_ids;
        Cache::set($cacheConfig);
        Cache::write($cacheKey, $orderIdCollection);
				
        return $order_ids;
    }
    
    public function sync_single($order)
    {
        $emdeon_xml_api = new Emdeon_XML_API();
        $order_item = $emdeon_xml_api->getOrder($order);
		
        $data = array();
		
        foreach($order_item as $key => $value)
        {
            $data['EmdeonOrder'][$key] = $value;
        }
        
        if($data['EmdeonOrder']['order_status'] == 'R')
        {
            $data['EmdeonOrder']['order_status'] = 'T';
        }
        
        $is_exist = $this->find('first', array('conditions' => array('EmdeonOrder.order' => $order_item['order']),
						'fields' => 'EmdeonOrder.order_id', 
						'recursive' => -1));
        
        if(!$is_exist)
        {
            $this->create();
            $this->save($data);
            $order_id = $this->getLastInsertId();
        }
        else
        {
            $data['EmdeonOrder']['order_id'] = $is_exist['EmdeonOrder']['order_id'];
            $this->save($data);
            $order_id = $is_exist['EmdeonOrder']['order_id'];
        }
        
        $this->sync_single_child($order_id);
        
        return $order_id;
    }
    
    public function sync_single_child($order_id)
    {
        $result = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id),
						'fields' => array('EmdeonOrder.order_id','EmdeonOrder.order'),
						'recursive' => -1));
        
        if($result)
        {
            $order_tests = $this->EmdeonOrderTest->sync($result['EmdeonOrder']['order_id'], $result['EmdeonOrder']['order']);
            $insurances = $this->EmdeonOrderinsurance->sync($result['EmdeonOrder']['order_id'], $result['EmdeonOrder']['order']);
            
            $result['EmdeonOrder']['downloaded'] = 1;
            $this->save($result);
        }
    }
		
		public function sync_insurance($order_id) {
			$result = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id),
							'fields' => array('EmdeonOrder.order_id','EmdeonOrder.order'),
                                                'recursive' => -1));
			if($result)	{
					$this->EmdeonOrderinsurance->sync($result['EmdeonOrder']['order_id'], $result['EmdeonOrder']['order']);
			}
		}
    
    public function deleteOrder($order_id)
    {
	$order=$this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id),
                                                  'fields' => array('EmdeonOrder.order_status','EmdeonOrder.order'),
                                                  'recursive' => -1)); 
       
        if($order['EmdeonOrder']['order_status'] == 'E' || $order['EmdeonOrder']['order_status'] == 'I' || $order['EmdeonOrder']['order_status'] == 'X')
        {
            $emdeon_xml_api = new Emdeon_XML_API();
            $emdeon_xml_api->deleteOrder($order['EmdeonOrder']['order']);
            $this->delete($order_id);
        }
    }
    
    public function deleteChild($order_id)
    {
        $emdeon_xml_api = new Emdeon_XML_API();
        
        $this->recursive = 3;
        $order=$this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id),
                                                  'fields' => array('EmdeonOrder.order_id'),
                                                  'contain' => array('EmdeonOrderTest' => array(
									'fields' => array(
										'order_test_id','ordertest'
											)
												),
								     'EmdeonOrderinsurance' => array(
									'fields'=> array('orderinsurance_id','orderinsurance')
												)
								   )  
					)
			);
        
        foreach($order['EmdeonOrderTest'] as $EmdeonOrderTest)
        {
            $this->EmdeonOrderTest->delete($EmdeonOrderTest['order_test_id']);
            $emdeon_xml_api->deleteOrderTest($EmdeonOrderTest['ordertest']);
            
        }
        
        foreach($order['EmdeonOrderinsurance'] as $EmdeonOrderinsurance)
        {
            $this->EmdeonOrderinsurance->delete($EmdeonOrderinsurance['orderinsurance_id']);
            $emdeon_xml_api->deleteOrderInsurance($EmdeonOrderinsurance['orderinsurance']);
        }
    }
    
    public function sync($mrn)
    {
        $emdeon_xml_api = new Emdeon_XML_API();
        $orders = $emdeon_xml_api->getOrderList($mrn);
        
        foreach($orders as $order)
        {
            $is_exist = $this->find('first', array('conditions' => array('EmdeonOrder.order' => $order['order']),
							'fields' => array('EmdeonOrder.order_id'),
							'recursive' => -1));
            
            $data = array();
            
            if(!$is_exist)
            {
                $this->create();
            }
            else
            {
                $data['EmdeonOrder']['order_id'] = $is_exist['EmdeonOrder']['order_id'];
            }
            
            foreach($order as $key => $value)
            {
                $data['EmdeonOrder'][$key] = $value;
            }
            
            $this->save($data);
        }
        
        $this->sync_child();
    }
    
    public function sync_child()
    {
        $results = $this->find('all', array('conditions' => array('OR' => array('EmdeonOrder.downloaded' => 0/*, 'EmdeonOrder.order_status' => 'E'*/)), 'recursive' => -1));
        
        foreach($results as $result)
        {
            $order_tests = $this->EmdeonOrderTest->sync($result['EmdeonOrder']['order_id'], $result['EmdeonOrder']['order']);
            $insurances = $this->EmdeonOrderinsurance->sync($result['EmdeonOrder']['order_id'], $result['EmdeonOrder']['order']);
            
            $result['EmdeonOrder']['downloaded'] = 1;
            $this->save($result);
        }
    }
    
    public function execute(&$controller, $tab_view = true)
    {
    		//CakeLog::write('debug', 'emdeon execute');

       	$task = (isset($controller->params['named']['task'])) ? $controller->params['named']['task'] : "";
        $patient_id = (isset($controller->params['named']['patient_id'])) ? $controller->params['named']['patient_id'] : "";
        $encounter_id = (isset($controller->params['named']['encounter_id'])) ? $controller->params['named']['encounter_id'] : "";

        if($tab_view)
        {
            $controller->layout = "blank";
        }
        
        $controller->loadModel("UserAccount");
    		//CakeLog::write('debug', 'emdeon UserAccount loaded');

        $controller->loadModel("EmdeonOrder");
        $controller->EmdeonOrder->recursive = 2;
    		//CakeLog::write('debug', 'emdeon EmdeonOrder loaded');
        
				$controller->loadModel("PatientDemographic");
    		//CakeLog::write('debug', 'emdeon PatientDemographic loaded');
		
        $controller->loadModel("EmdeonLabResult");
    		//CakeLog::write('debug', 'emdeon EmdeonLabResult loaded');
				
				
				// Skip extract source. Much better is we move it as a cron job
				//$this->EmdeonLabResult->extractSource();
    		
				//CakeLog::write('debug', 'emdeon EmdeonLabResult extracted');
        
        $patient_mode = true;    
        if($encounter_id != "")
        {
						// Check first if we already have the patient_id provided
						// so we don't have to load EncounterMaster and query the table
						if (!$patient_id) {
							$controller->loadModel("EncounterMaster");
							$patient_id = $controller->EncounterMaster->getPatientID($encounter_id);
						}
					
            $patient_mode = false;
    				//CakeLog::write('debug', 'emdeon EncounterMaster loaded');
        }

        $controller->set("emdeon_info", $this->getELabsAPI()->getInfo());
        //CakeLog::write('debug', 'emdeon getInfo');
        
        //form init
        $labs = $this->getELabsAPI()->getLabs();
        $controller->set("labs", $labs);
        
        $view_lab = (isset($controller->params['named']['view_lab'])) ? $controller->params['named']['view_lab'] : "";
        $view_ordering_cg_id = (isset($controller->params['named']['view_ordering_cg_id'])) ? $controller->params['named']['view_ordering_cg_id'] : "";
        
				if ($view_lab == '' || $view_lab == 'all') {
					$view_lab = $view_ordering_cg_id = 'all';
				} elseif ($view_ordering_cg_id == '' || $view_ordering_cg_id == 'all') {
            $clients = $this->getELabsAPI()->getLabClients($view_lab);
            $view_ordering_cg_id = $clients[0]['id_value'];					
				}
				
				
				/*
        if($view_lab == "")
        {
            $view_lab = $labs[0]['lab'];
        }
        
        if($view_ordering_cg_id == "")
        {
            $clients = $emdeon_xml_api->getLabClients($view_lab);
            $view_ordering_cg_id = $clients[0]['id_value'];
        }
				 */
				
        $controller->set("view_lab", $view_lab);
        $controller->set("view_ordering_cg_id", $view_ordering_cg_id);
        //end form init
        //CakeLog::write('debug', 'emdeon form inited');
        
        $order_id = (isset($controller->params['named']['order_id'])) ? $controller->params['named']['order_id'] : "";
        
        if($task == 'view_requisition' || $task == 'edit_order' || $task == 'add_order' || $task == 'view_manifest')
        {
            /* sync everything */
            $controller->loadModel("PatientGuarantor");
            $controller->loadModel("PatientInsurance");
       			//CakeLog::write('debug', 'emdeon PatientGuarantor/PatientInsurance loaded');
            
            //sync patient, guarantor, insurance
            $controller->PatientDemographic->updateEmdeonPatient($patient_id, true);
            $controller->PatientGuarantor->sync($patient_id);
            $controller->PatientInsurance->sync($patient_id);
            
            $controller->loadModel('EncounterAssessment');
            $icd9s = $controller->EncounterAssessment->getIcdByPatient($patient_id);
            $controller->set(compact('icd9s'));
       			//CakeLog::write('debug', 'emdeon EncounterAssessment loaded');
            
            $mrn = $controller->PatientDemographic->getPatientMRN($patient_id);
            $controller->set(compact('mrn', 'order_id'));
       			//CakeLog::write('debug', 'emdeon getPatientMRN done');
        }
        
        if($order_id != "")
        {
						$orderData = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id)));
						$current_order_status = $orderData['EmdeonOrder']['order_status'];
						$lab_result_id = (isset($controller->params['named']['lab_result_id'])) ? $controller->params['named']['lab_result_id'] : "";

						if (!$lab_result_id) {
							$lab_result_id = isset($orderData['EmdeonLabResult'][0]['lab_result_id']) ? $orderData['EmdeonLabResult'][0]['lab_result_id'] : 0 ;
						}						
        }

       	//CakeLog::write('debug', "emdeon switch to $task");
        switch($task)
        {
			case "send_selected":
			{
				$ret = array();
				
				$this->transmitOrders($controller->data['order_ids']);
				
				echo json_encode($ret);
				exit;
			} break;
            case "view_requisition":
            {
                /*
                if($lab_result_id != 0)
                {
                    $controller->redirect(array('task' => 'view_order', 'patient_id' => $patient_id, 'order_id' => $order_id));
                }*/
            } break;
            case "edit_order":
            {
                if($current_order_status == 'T')
                {
                    $controller->redirect(array('task' => 'view_requisition', 'patient_id' => $patient_id, 'order_id' => $order_id));
                }
            } break;
            case "add_order":
			case "view_manifest":
            {
                
            } break;
            case "sync_lab_result":
            {
            	// disabled per ticket #2538. done by cron job -- it's very slow process
            	// DO NOT RE-ENABLE THIS
            	// $controller->EmdeonLabResult->sync();  // FIXME: kevin turns this on for testing and should always remember to turn it off again!!
                
                echo json_encode(array());
                exit;
            } break;
            case "view_order":
            {
            			$order_id = (isset($controller->params['named']['order_id'])) ? $controller->params['named']['order_id'] : "";
				$lab_result_id = (isset($controller->params['named']['lab_result_id'])) ? $controller->params['named']['lab_result_id'] : "";
				if($lab_result_id && empty($order_id))
					$order_id = $this->EmdeonLabResult->field('order_id', array('lab_result_id' => $lab_result_id));
                $controller->set("order_id", $order_id);
		//refine results with containable						
		$orderData = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id), 'contain' => array('EmdeonLabResult' => array('fields' => 'lab_result_id'))));

    if (!$lab_result_id) {
      $lab_result_id = $orderData['EmdeonLabResult'][0]['lab_result_id'];
    }
    
		$approved = intval($orderData['EmdeonOrder']['approve']) ? true: false;
                $controller->set("is_approve", $approved);
                $controller->set("order_status", $orderData['EmdeonOrder']['order_status']);
                $controller->set("order_emdeon_id", $orderData['EmdeonOrder']['order']);
                
								
                $controller->set("lab_result_id", $lab_result_id);
              	//looks like $orderData['EmdeonOrder'] array data is used in the view below. no other hasmany relationships
		$controller->set(compact('orderData'));
								
                $data_lab_result_id = $this->get_patient_lab_result_id($order_id);
                $controller->set("data_lab_result_id", $data_lab_result_id);
                
            } break;
					case 'save_comment': 
						$order_id = (isset($controller->params['named']['order_id'])) ? $controller->params['named']['order_id'] : "";
						$comment = isset($controller->params['form']['comment']) ? trim($controller->params['form']['comment']) : '';
						$commentsForPatient = isset($controller->params['form']['comments_for_patient']) ? trim($controller->params['form']['comments_for_patient']) : '';
						
						// $notify either contains a non-zero integer corresponding to a user id OR
						// zero which means no notification will be sent
						$notify = isset($controller->params['form']['notify']) ? $controller->params['form']['notify'] : '';
						
						$this->id = $order_id;
						$order = $this->read();

						//add timestamp for comment
						$user=$controller->UserAccount->getCurrentUser($_SESSION['UserAccount']['user_id']);
						$comment = $comment . ' &#8212; ' .$user['firstname'][0]. '. ' .$user['lastname']. ' ' .date($controller->__global_date_format). ' @ '.date('g:ia');
						
						$this->saveField('comment', $comment);
						$this->saveField('patient_comment', $commentsForPatient);
						
						if ($notify) {
							$s_url = Router::url(array(
									'controller'=>'patients', 
									'action' =>'index', 
									'task' => 'edit',
									'patient_id' => $patient_id,
									'view' => 'medical_information',
									'view_tab' => 3,
									'view_actions' => 'lab_results_electronic',
									'view_task' => 'view_order',
									'target_id_name' => 'order_id',
									'target_id' => $order_id,
							));
							
							$data = array('MessagingMessage' => array());
							
							$data['MessagingMessage']['sender_id'] = $_SESSION['UserAccount']['user_id'];
							$data['MessagingMessage']['patient_id'] = $patient_id;


							$data['MessagingMessage']['subject'] = "Lab Result Comment";
							$data['MessagingMessage']['message'] = "A comment was recently made to a patient lab result <br /><a href=".$s_url."> View Lab Result </a>";                        


							$data['MessagingMessage']['type'] = "Lab Result Comment";
							$data['MessagingMessage']['priority'] = "Normal";
							$data['MessagingMessage']['status'] = "New";
							$data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
							$data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$data['MessagingMessage']['modified_user_id'] = $patient_id ? $patient_id : 0;
							Classregistry::init('MessagingMessage');
							$message = new MessagingMessage();
							$staff_names = explode(',', $notify);
							foreach($staff_names as $staff_name)
							{
								$staff_name = trim($staff_name);
								if(empty($staff_name)) continue;
								$staff_info = $controller->UserAccount->find('first', array('conditions' => array('full_name' => $staff_name), 'fields' => 'user_id', 'recursive' => -1));
								$message->create();
								$data['MessagingMessage']['recipient_id'] = $staff_info['UserAccount']['user_id'];
								$message->save($data);
								unset($message->id);
							}							
						}
						
						if ($commentsForPatient) {
							$this->id = $order_id;
							
							$patientAccountId = $controller->UserAccount->getUserbyPatientID($patient_id);
							if (($commentsForPatient != $order['EmdeonOrder']['patient_comment']) && $patientAccountId) {

								$s_url = Router::url(array(
										'controller'=>'dashboard', 
										'action' =>'lab_results_electronic', 
										'task' => 'view_order',
										'patient_id' => $patient_id,
										'order_id' => $order_id,
								));

								$data = array('MessagingMessage' => array());

								$data['MessagingMessage']['sender_id'] = $_SESSION['UserAccount']['user_id'];
								$data['MessagingMessage']['patient_id'] = $patient_id;


								$data['MessagingMessage']['subject'] = "Lab Result Comment";
								$data['MessagingMessage']['message'] = "Comments on your Lab Results are now available. <br /><a href=".$s_url."> Click here to view </a>";                        


								$data['MessagingMessage']['type'] = "Lab Result Comment";
								$data['MessagingMessage']['priority'] = "Normal";
								$data['MessagingMessage']['status'] = "New";
								$data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
								$data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
								$data['MessagingMessage']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
								Classregistry::init('MessagingMessage');
								$message = new MessagingMessage();
								$message->create();
								$data['MessagingMessage']['recipient_id'] = $patientAccountId;
								$message->save($data);												
								
							}
						}
						
						die('Ok');
					break;
            case "approve_order":
            {
                $order_id = (isset($controller->params['named']['order_id'])) ? $controller->params['named']['order_id'] : "";
                $controller->EmdeonOrder->approveResult($order_id);

                $controller->redirect(array('action' => 'lab_results_electronic', 'patient_id' => $patient_id));
            } break;
					
						case 'set_reviewed': {
							
							$orderId = isset($controller->params['form']['order_id']) ? intval($controller->params['form']['order_id']) : 0;
							$reviewed = isset($controller->params['form']['reviewed']) ? intval($controller->params['form']['reviewed']) : 0;
							
							if ($orderId) {
								$this->id = $orderId;
								$this->saveField('reviewed', $reviewed);
							}
							
							die('Ok');
						} break;
					
            default:
            {
                $patient = $controller->PatientDemographic->getPatient($patient_id);
                $user = $controller->UserAccount->getCurrentUser($controller->user_id);
                $orderTestMap = $this->getOrderByPatient($user, $patient['mrn'], true);
                $order_ids = array_keys($orderTestMap);
								
								
								
                $controller->paginate['EmdeonOrder'] = array(
									'order' => array(
										'EmdeonOrder.actual_order_date' => 'desc',
									),
                  'contain' => array('EmdeonLabResult'),
									'group' => 'EmdeonOrder.placer_order_number'
								);
                
                $conditions = array();
                $conditions['EmdeonOrder.order_id'] = $order_ids;
								
                if($controller->name == 'Patients' && $view_lab != 'all')
                {
                    $conditions['EmdeonOrder.lab'] = $view_lab;
                    $conditions['EmdeonOrder.ordering_cg_id'] = $view_ordering_cg_id;
                }
                
								
								
                $data = $controller->paginate('EmdeonOrder', $conditions);
                
                for($i = 0; $i < count($data); $i++)
                {
                    $data[$i]['EmdeonOrder']['test_ordered'] = $orderTestMap[$data[$i]['EmdeonOrder']['order_id']];
										
                }

                $controller->set('emdeon_orders', $data);
                
                

                $controller->EmdeonOrder->saveAudit('View');
            }
        }
       	//CakeLog::write('debug', "emdeon finished");
    }
	
	public function getReviewedOrders($patient_id) {
		$PatientDemographic = new PatientDemographic();
		$UserAccount = new UserAccount();
		
		$patient = $PatientDemographic->getPatient($patient_id);
		$user = $UserAccount->getCurrentUser($_SESSION['UserAccount']['user_id']);
		$order_ids = $this->getOrderByPatient($user, $patient['mrn']);

		$conditions = array();
		$conditions['EmdeonOrder.order_id'] = $order_ids;
		$conditions['EmdeonOrder.reviewed'] = 1;
		

		$data = $this->find('all', array(
			'conditions' => $conditions,
			'order' => array('EmdeonOrder.actual_order_date' => 'desc'),
		));
		
		for($i = 0; $i < count($data); $i++)
		{
				$data[$i]['EmdeonOrder']['test_ordered'] = $this->getOrderTestString($data[$i]['EmdeonOrder']['order_id']);
		}

		return $data;
	}	
		
		
	public function transmitOrders($order_ids)
	{     
		$emdeon_xml_api = new Emdeon_XML_API();
		for($i = 0; $i < count($order_ids); $i++)
		{
			$item = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_ids[$i]), 
							'fields' => 'EmdeonOrder.order', 'recursive' => -1 ));
			
			if($item)
			{
				$emdeon_xml_api->transmitOrder($item['EmdeonOrder']['order']);
				$this->sync_single($item['EmdeonOrder']['order']);
        $this->queuePatientOrder($order_ids[$i]);
			}
		}
	}
    
    public function executeEncounterPlan(&$controller, $task, $mrn, $icd)
    {
			
				$isAllDiagnosis = false;
				if ($icd == 'all') {
					$isAllDiagnosis = true;
				}
				
				$controller->set('isAllDiagnosis', $isAllDiagnosis);
			
			
        $encounter_id = (isset($controller->params['named']['encounter_id'])) ? $controller->params['named']['encounter_id'] : 0;
		
        $controller->loadModel("PatientDemographic");
        
        $current_patient = $controller->PatientDemographic->getPatientByMRN($mrn);
        $patient_id = $current_patient['PatientDemographic']['patient_id'];
        
        $emdeon_xml_api = new Emdeon_XML_API();
        $labs = $this->getELabsAPI()->getLabs();
        $controller->set("labs", $labs);
        
        $from_patient = (isset($controller->params['named']['from_patient'])) ? $controller->params['named']['from_patient'] : "";
        $view_lab = (isset($controller->params['named']['view_lab'])) ? $controller->params['named']['view_lab'] : "";
        $view_ordering_cg_id = (isset($controller->params['named']['view_ordering_cg_id'])) ? $controller->params['named']['view_ordering_cg_id'] : "";
        
				if ($view_lab == '' || $view_lab == 'all') {
					$view_lab = $view_ordering_cg_id = 'all';
				} elseif ($view_ordering_cg_id == '' || $view_ordering_cg_id == 'all') {
            $clients = $this->getELabsAPI()->getLabClients($view_lab);
            $view_ordering_cg_id = $clients[0]['id_value'];					
				}
        
        $controller->set("view_lab", $view_lab);
        $controller->set("view_ordering_cg_id", $view_ordering_cg_id);
        
        if($task == "addnew" || $task == "edit")
        {
            $caregivers = $emdeon_xml_api->getCaregivers();
            $controller->set("caregivers", $caregivers);
            
            $controller->loadModel('EncounterMaster');
            $provider_id = $controller->EncounterMaster->getProviderId($encounter_id);
            
            $controller->loadModel('UserAccount');
            $clinician_reference_id = $controller->UserAccount->getClinicianReferenceId($provider_id);
            if(isset($controller->current_session_user['clinician_reference_id']) && $controller->current_session_user['clinician_reference_id'])
				$clinician_reference_id = $controller->current_session_user['clinician_reference_id'];
			else
				$controller->set("provider_fullname", $controller->current_session_user['firstname'].' '.$controller->current_session_user['lastname']);
            $controller->set("clinician_reference_id", $clinician_reference_id);
        }
        
        switch ($task)
        {
            case "getPastDiagnosis":
            {
                $controller->loadModel('EncounterAssessment');
                $ret = array();
                $conditions = array();
                $conditions['EncounterMaster.patient_id'] = $patient_id;
                
                $encounter_ids = $controller->EncounterAssessment->EncounterMaster->find('list', array('fields' => 'encounter_id', 'conditions' => $conditions, 'order' =>'EncounterMaster.encounter_date desc', 'recursive' => -1));
                $assessments = $controller->EncounterAssessment->find('all', array('fields' => array('EncounterAssessment.icd_code', 'EncounterAssessment.diagnosis'), 'conditions' => array('EncounterAssessment.encounter_id' => $encounter_ids, 'EncounterAssessment.icd_code !=' => ''), 'recursive' => -1, 'group' => array('icd_code')));
                
                foreach($assessments as $assessment)
                {
                    $data = array();
                    $data['icd9'] = $assessment['EncounterAssessment']['icd_code'];
                    $data['diagnosis'] = $assessment['EncounterAssessment']['diagnosis'];    
                    $ret[] = $data;
                }
                
                echo json_encode($ret);
                exit;
            }
            break;
            case "getFavoriteDiagnosis":
            {
                $controller->loadModel('FavoriteDiagnosis');
                $ret = array();
                
                $favorites = $controller->FavoriteDiagnosis->find('all', array(
                    'fields' => array('FavoriteDiagnosis.diagnosis')
                ));
                
                foreach($favorites as $favorite)
                {
                    $data = array();
                    $data['icd9'] = substr($favorite['FavoriteDiagnosis']['diagnosis'], strpos($favorite['FavoriteDiagnosis']['diagnosis'], '[') + 1, strrpos($favorite['FavoriteDiagnosis']['diagnosis'], ']') - strpos($favorite['FavoriteDiagnosis']['diagnosis'], '[') - 1);
                    $data['diagnosis'] = $favorite['FavoriteDiagnosis']['diagnosis'];    
                    
                    if(is_numeric($data['icd9']))
                    {
                        $ret[] = $data;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            break;
            case "getProblemList":
            {
                $controller->loadModel('PatientProblemList');
                $show_all_problem = $controller->data['show_all_problem'];
                $ret = array();
                
                if($show_all_problem == '0')
                {
                    $problems = $controller->PatientProblemList->find('all', array(
                        'fields' => array('PatientProblemList.icd_code', 'PatientProblemList.diagnosis'),
                        'conditions' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.icd_code !=' => '', 'PatientProblemList.status' => 'Active')
                    ));
                }
                else
                {
                    $problems = $controller->PatientProblemList->find('all', array(
                        'fields' => array('PatientProblemList.icd_code', 'PatientProblemList.diagnosis'),
                        'conditions' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.icd_code !=' => '')
                    ));
                }
                
                foreach($problems as $problem)
                {
                    $data = array();
                    $data['icd9'] = $problem['PatientProblemList']['icd_code'];
                    $data['diagnosis'] = $problem['PatientProblemList']['diagnosis'];    
                    $ret[] = $data;
                }
                
                echo json_encode($ret);
                exit;
            }
            break;
            case "sync_everything":
            {
                $mrn = (isset($controller->params['named']['mrn'])) ? $controller->params['named']['mrn'] : "";
                $controller->loadModel("PatientGuarantor");
                $controller->loadModel("PatientInsurance");
                $data = $controller->PatientDemographic->getPatientByMRN($mrn);
                
                //sync patient, guarantor, insurance
                $controller->PatientDemographic->updateEmdeonPatient($data['PatientDemographic']['patient_id'], true);
                $controller->PatientGuarantor->sync($data['PatientDemographic']['patient_id']);
                $controller->PatientInsurance->sync($data['PatientDemographic']['patient_id']);
                
                exit;
            } break;
            case "get_lab_clients":
            {
                $selected_lab = $controller->data['selected_lab'];
                $clients = $this->getELabsAPI()->getLabClients($selected_lab);
                echo json_encode($clients);
                exit;
            } break;
            case "check_abn":
            {
                $controller->loadModel("PatientDemographic");
                
                $ret = array();
                $mrn = $controller->data['mrn'];
                
                $order = $controller->data;
                
                if($order['bill_type'] == 'T')
                {
                    $data = $controller->PatientDemographic->getPatientByMRN($mrn);
                    
                    $patient = $data['PatientDemographic'];
                    
                    //sync patient before check abn
                    $controller->PatientDemographic->updateEmdeonPatient($patient['patient_id'], true);
                    
                    $guarantor = (isset($data['PatientGuarantor'][0])?$data['PatientGuarantor'][0]:false);
                    $insurances = (isset($data['PatientInsurance'])?$data['PatientInsurance']:false);
                    
                    if($insurances)
                    {
                        echo json_encode($emdeon_xml_api->getABNInfo($order, $insurances));
                    }
                    else
                    {
                        $ret = array();
                        $ret['failed'] = false;
                        echo json_encode($ret);
                    }
                }
                else
                {
                    $ret = array();
                    $ret['failed'] = false;
                    echo json_encode($ret);
                }
                
                exit;
            } break;
            case "transmit_single_order":
            {
                $ret = array();
                
                $item = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $controller->data['order_id']),
							'fields' => 'EmdeonOrder.order', 'recursive' => -1));
                
                if($item)
                {
                    $emdeon_xml_api->transmitOrder($item['EmdeonOrder']['order']);
                    $this->sync_single($item['EmdeonOrder']['order']);
                    $this->queuePatientOrder($controller->data['order_id']);                    
                }
                
                echo json_encode($ret);
                exit;
            }
            break;
            case "transmit_multiple_order":
            {
                $ret = array();
                
                for($i = 0; $i < count($controller->data['order']); $i++)
                {
                    $item = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $controller->data['order'][$i]),
							'fields' => 'EmdeonOrder.order', 'recursive' => -1));
                    
                    if($item)
                    {
                        $emdeon_xml_api->transmitOrder($item['EmdeonOrder']['order']);
                        $this->queuePatientOrder($controller->data['order'][$i]);
                        $this->sync_single($item['EmdeonOrder']['order']);
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            break;
            case "check_order_data":
            {
                $mrn = $controller->data['mrn'];
                $ret = array();
                $ret['guarantor'] = false;
                $ret['insurance'] = false;
                
                $controller->loadModel("PatientDemographic");
                $data = $controller->PatientDemographic->getPatientByMRN($mrn);
                
                if(count($data['PatientGuarantor']))
                {
                    $ret['guarantor'] = true;
                }
                
                if(count($data['PatientInsurance']) > 0)
                {
                    $ret['insurance'] = true;
                }
                
                echo json_encode($ret);
                exit;
            } break;
            case "get_multiple_icd9":
            {
                $controller->loadModel('EmdeonLiveIcd9');
                
                $ret = array();
                $ret['has_data'] = false;
                $diagnoses = array();
                
                $icd9s = explode("|", $controller->data['icd9s']);
                
                foreach($icd9s as $icd9)
                {
                    $items = $controller->EmdeonLiveIcd9->find('first', array('conditions' => array('icd_9_cm_code' => $icd9 . '*', 'description' => '')));
                    
                    if(!empty($items))
                    {
                        $items['EmdeonLiveIcd9']['required'] = false;
                        $diagnoses[] = $items['EmdeonLiveIcd9'];
                    }
                }
                
                if(count($diagnoses) > 0)
                {
                    $ret['has_data'] = true;
                    $ret['diagnoses'] = $diagnoses;
                }
                
                echo json_encode($ret);
                exit;
            } break;
            case "get_single_icd9":
            {
                $ret = array();
                $ret['has_data'] = false;
                
                $controller->loadModel('EmdeonLiveIcd9');
								
								$items = array();
								// Check if icd_9_cm_code is present
								if (isset($controller->data['icd_9_cm_code'])) {
									$items = $controller->EmdeonLiveIcd9->find('first', array('conditions' => array('icd_9_cm_code' => $controller->data['icd_9_cm_code'] . '*', 'description' => '')));
								}
                
                if(!empty($items) )
                {
                    $items['EmdeonLiveIcd9']['required'] = ((@$controller->data['required'] == 'yes') ? true : false);
                    $ret['diagnosis'] = $items['EmdeonLiveIcd9'];
                    $ret['has_data'] = true;
                }
                
                echo json_encode($ret);
                exit;
            } break;
            case "activate_order":
            {
                $order_id = $controller->data['order_id'];
                $order = $this->getOrderReference($order_id);
                $emdeon_xml_api->activateOrder($order);
                
                $ret = array();
                $ret['order_id'] = $this->sync_single($order);
                $ret['redir_link'] = Router::url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'from_patient' => $from_patient, 'task' => 'print_requisition', 'order_id' => $ret['order_id']));
                
                echo json_encode($ret);
                exit;
                
            } break;
            case "save_order":
            {
                $controller->loadModel("PatientDemographic");
                
                $ret = array();
                $mrn = $controller->data['mrn'];
                
                $order = $controller->data;
                $data = $controller->PatientDemographic->getPatientByMRN($mrn);
                
                $patient = $data['PatientDemographic'];
                $guarantor = (isset($data['PatientGuarantor'][0])?$data['PatientGuarantor'][0]:false);
                $insurances = (isset($data['PatientInsurance'])?$data['PatientInsurance']:false);
				
                
                $order_ref = $emdeon_xml_api->saveOrder($order, $patient, $guarantor, $insurances);
				
				if($order_ref)
				{
					$ret['error'] = false;
					$ret['order_id'] = $this->sync_single($order_ref);
					
					//attach to encounter_id
					if($encounter_id != 0)
					{
						$data = array();
						$data['EmdeonOrder']['order_id'] = $ret['order_id'];
						$data['EmdeonOrder']['encounter_id'] = $encounter_id;
						$this->save($data);	
					}
					
					$ret['redir_link'] = Router::url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'from_patient' => $from_patient, 'task' => 'print_requisition', 'order_id' => $ret['order_id']));
				}
				else
				{
					$ret['error'] = true;
				}
					
                echo json_encode($ret);
                exit;
            }
            break;
            case "update_order":
            {
                $controller->loadModel("PatientDemographic");
                
                $ret = array();
                $mrn = $controller->data['mrn'];
                
                $order = $controller->data;
                $data = $controller->PatientDemographic->getPatientByMRN($mrn);
                
                $patient = $data['PatientDemographic'];
                $guarantor = (isset($data['PatientGuarantor'][0])?$data['PatientGuarantor'][0]:false);
                $insurances = (isset($data['PatientInsurance'])?$data['PatientInsurance']:false);
                
                $this->deleteChild($order['order_id']);
				
                
                $order_ref = $emdeon_xml_api->updateOrder($order, $patient, $guarantor, $insurances);
				
				if($order_ref)
				{
					$ret['error'] = false;
                	$ret['order_id'] = $this->sync_single($order_ref);
                
                	$ret['redir_link'] = Router::url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'mrn' => $mrn, 'encounter_id' => $encounter_id, 'view_lab' => $view_lab, 'from_patient' => $from_patient, 'task' => 'print_requisition', 'order_id' => $ret['order_id']));
				}
				else
				{
					$ret['error'] = true;
				}
                
                echo json_encode($ret);
                exit;
            }
            break;
            case "delete_order":
            {
                $ret = array();
                
                for($i = 0; $i < count($controller->data['order']); $i++)
                {
                    $this->deleteOrder($controller->data['order'][$i]);
                }
                
                echo json_encode($ret);
                exit;
            }
            break;
            case "get_test_aoe":
            {
                $ret = array();
                for($i = 0; $i < count($controller->data); $i++)
                {
                    $data = array();
                    $data['lab'] = $controller->data[$i]['lab'];
                    $data['order_code'] = $controller->data[$i]['order_code'];
                    $data['orderable'] = $controller->data[$i]['orderable'];
                    $data['description'] = $controller->data[$i]['description'];
                    $data['questions'] = $emdeon_xml_api->getTestAoe($controller->data[$i]['lab'], $controller->data[$i]['orderable']);
                    $ret[] = $data;
                }
                echo json_encode($ret);
                exit;
            }
            break;
            case "addnew":
            {
                
                
            } break;
            case "edit":
            {
                $order_id = (isset($controller->params['named']['order_id'])) ? $controller->params['named']['order_id'] : "";
                $this->recursive = 3;
                $item = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id)));
                $controller->set("order", $item);
            } break;
            case "print_requisition":
            {
                $order_id = (isset($controller->params['named']['order_id'])) ? $controller->params['named']['order_id'] : "";
                $item = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id),'recursive' => -1));
                
                $controller->set("order_status", $item['EmdeonOrder']['order_status']);
                $controller->set("order_type", $item['EmdeonOrder']['order_type']);
                $controller->set("order_id", $order_id);
                
                $order = $item;
                
                $label_print_info = array();
                $label_print_info['placer_order_number'] = $order['EmdeonOrder']['placer_order_number'];
                $label_print_info['account_number'] = $order['EmdeonOrder']['ordering_cg_id'];
                $label_print_info['patient_name'] = $order['EmdeonOrder']['person_last_name'] . ', ' . $order['EmdeonOrder']['person_first_name'];
                $label_print_info['print_patient_name'] = $order['EmdeonOrder']['person_first_name'] . ' ' . $order['EmdeonOrder']['person_last_name'];
                $label_print_info['patient_dob'] = $order['EmdeonOrder']['person_dob'];
                $label_print_info['patient_id'] = $order['EmdeonOrder']['person_hsi_value'];
                $label_print_info['collection_date'] = __date("m/d/Y H:i e", strtotime($order['EmdeonOrder']['date']));
                $controller->set("label_print_info", $label_print_info);
        
            } break;
            case "manifest":
            {
                $view_lab = (isset($controller->params['named']['view_lab'])) ? $controller->params['named']['view_lab'] : "";
                $order_ids = (isset($controller->params['named']['order_ids'])) ? $controller->params['named']['order_ids'] : "";
                
                $controller->set("lab", $view_lab);
                $controller->set("order_ids", $order_ids);
            } break;
            default:
            {
                $controller->set("emdeon_info", $this->getELabsAPI()->getInfo());
                $this->recursive = 2;
                
				$controller->paginate['EmdeonOrder'] = array(
					'limit' => 10, 
					'page' => 1, 
					'order' => array('EmdeonOrder.collection_datetime' => 'desc', 'EmdeonOrder.placer_order_number' => 'desc')
				);
				
                $order_ids = $this->getOrderByDiagnosis($mrn, $icd, $view_lab, $view_ordering_cg_id);
				
				$conditions = array();
				$conditions['EmdeonOrder.order_id'] = $order_ids;
				
				if($encounter_id != 0)
				{
					$conditions['EmdeonOrder.encounter_id'] = $encounter_id;
				}
                
                $orders = $controller->paginate('EmdeonOrder', $conditions);
                
                for($i = 0; $i < count($orders); $i++)
                {
                    $test_strings = $this->getOrderTestString($orders[$i]['EmdeonOrder']['order_id'], false);
                    
                    for($a = 0; $a < count($test_strings); $a++)
                    {
                        $test_strings[$a] = '<li>'.$test_strings[$a].'</li>';
                    }
                    
                    $orders[$i]['EmdeonOrder']['test_details'] = implode(" ", $test_strings);
                }
                
                $controller->set('emdeon_orders', $controller->sanitizeHTML($orders));
                
                $icd9_defined = true;
								
								$controller->loadModel('EncounterMaster');
								$controller->loadModel('UserAccount');
								$provider_id = $controller->EncounterMaster->getProviderId($encounter_id);								
								
								$controller->UserAccount->id = $provider_id;
								$assessment_plan = $controller->UserAccount->field('assessment_plan');
                $combine = intval($assessment_plan) ? true : false;
								
								
                if(@$controller->params['named']['disable_add'] != '1' && !$combine)
                {
                    $controller->loadModel('EmdeonLiveIcd9');
                    $check_icd = $controller->EmdeonLiveIcd9->find('first', array('conditions' => array('icd_9_cm_code' => $icd . '*', 'description' => '')));
                    
                    if(empty($check_icd))
                    {
                        $icd9_defined = false;
                    }
                }
                
                $controller->set('icd9_defined', $icd9_defined);
            }
        }
    }
    
    public function executePrintOrder(&$controller)
    {
        $emdeon_xml_api = new Emdeon_XML_API();
        $order_id = (isset($controller->params['named']['order_id'])) ? $controller->params['named']['order_id'] : "";
        
				
				$this->sync_insurance($order_id);
        $this->recursive = 3;
        $item = $this->find('first', array('conditions' => array('EmdeonOrder.order_id' => $order_id)));
        $order = $item;
        
        $api_configs = $emdeon_xml_api->getInfo();
        $lab_details = $emdeon_xml_api->getLabDetails($order['EmdeonOrder']['lab']);
        $lab_configuration = $emdeon_xml_api->getLabConfiguration($order['EmdeonOrder']['lab']);
        $organization_details = $emdeon_xml_api->getOrganizationDetails();
        $caregiver_details = $emdeon_xml_api->getCaregiverDetails($order['EmdeonOrder']['referringcaregiver']);
        
        $order['guarantor_information'] = $emdeon_xml_api->getGuarantorDetails($order['EmdeonOrder']['guarantor']);
        $organization_details['contact_phone'] = $emdeon_xml_api->formatPhone($organization_details['contact_phone']);
        $order['EmdeonOrder']['person_home_phone_full'] = $emdeon_xml_api->formatPhone($order['EmdeonOrder']['person_home_phone_area_code'].$order['EmdeonOrder']['person_home_phone_number']);
        $order['EmdeonOrder']['guarantor_home_phone'] = $emdeon_xml_api->formatPhone($order['EmdeonOrder']['guarantor_home_phone']);
        
        $controller->set("api_configs", $api_configs);
        $controller->set("order", $order);
        $controller->set("lab_details", $lab_details);
        $controller->set("lab_configuration", $lab_configuration);
        $controller->set("organization_details", $organization_details);
        $controller->set("caregiver_details", $caregiver_details);
    }
    
    public function executePrintManifest(&$controller)
    {
        $lab = (isset($controller->params['named']['lab'])) ? $controller->params['named']['lab'] : "";
        $order_ids = (isset($controller->params['named']['order_ids'])) ? $controller->params['named']['order_ids'] : "";
        $order_ids = explode("_", $order_ids);
        
        $emdeon_xml_api = new Emdeon_XML_API();
        $lab_details = $emdeon_xml_api->getLabDetails($lab);
        $lab_configuration = $emdeon_xml_api->getLabConfiguration($lab);
        $api_configs = $this->getELabsAPI()->getInfo();
        
        $this->recursive = -1;
        $caregivers = $this->find('all', array(
            'fields' => array('EmdeonOrder.referringcaregiver', 'EmdeonOrder.ref_cg_fname', 'EmdeonOrder.ref_cg_lname', 'EmdeonOrder.ordering_cg_id'),
            'order' => 'EmdeonOrder.referringcaregiver ASC',
            'conditions'=> array('EmdeonOrder.lab' => $lab, 'EmdeonOrder.order_id' => $order_ids),
            'group' => array('EmdeonOrder.referringcaregiver')
        ));
        
        for($i = 0; $i < count($caregivers); $i++)
        {
            $orders = $this->find('all', array(
                'conditions' => array('EmdeonOrder.lab' => $lab, 'EmdeonOrder.referringcaregiver' => $caregivers[$i]['EmdeonOrder']['referringcaregiver'], 'EmdeonOrder.order_id' => $order_ids),
                'order' => 'EmdeonOrder.placer_order_number ASC',
                'contain' => array('EmdeonOrderTest' => 'EmdeonOrderable')
            ));
            
            $caregivers[$i]['orders'] = $orders;
        }
        
        $controller->set("api_configs", $api_configs);
        $controller->set("caregivers", $caregivers);
        $controller->set("lab_details", $lab_details);
        $controller->set("lab_configuration", $lab_configuration);
    }
	
    /**
    * Retrieve list of test by encounter - used in superbill
    * @param int $encounter_id Encounter ID
    * @return array Array of tests
    */
    public function getTestListByEncounter($encounter_id)
    {
		$order_ids = $this->getOrderIdsByEncounter($encounter_id);
		
        $test_list = array();
        
        foreach($order_ids as $order_id)
        {
            $current_order_tests = $this->getOrderTestString($order_id, false);
            $test_list = array_merge($test_list, $current_order_tests);
        }
        
        return $test_list;
    }
    
    /**
    * Retrieve list of Lab orders by encounter
    * @param string $mrn MRN
    * @param int $encounter_id Encounter ID
    * @param array &$plan Array of plan details
    * @param array $diagnosis_data Array of Diagnosis
    * @return array Array of test_details
    */
    
    public function getValues($mrn, $encounter_id, &$plan, $diagnosis_data, $combine = false)
    {    
		//this function call only happens for electronic setting
		$this->PracticeSetting = ClassRegistry::init('PracticeSetting');
		$practice_settings = $this->PracticeSetting->getSettings();
		
		if($practice_settings->labs_setup == 'Standard')
		{
			return;
		}
        $labs = $this->getELabsAPI()->getValidLabs();
		
		if(count($labs) == 0)
		{
			return;	
		}
		
		$first_diagnosis = '';
		if(count($diagnosis_data) > 0 && isset($diagnosis_data[0]) && isset($diagnosis_data[0]['diagnosis']))
		{
			$first_diagnosis = $diagnosis_data[0]['diagnosis'];
		}
		
		
		$combinedList = array();
		
		$all_test_string = array();
            
        foreach($diagnosis_data as $diagnosis)
        {
            $this->recursive = 2;
			
            $order_ids = $this->getOrderByDiagnosis($mrn, $diagnosis['icd_code'], $labs, 0, $encounter_id);

            $orders = $this->find('all', array('conditions' => array('EmdeonOrder.order_id' => $order_ids, 'EmdeonOrder.encounter_id' => $encounter_id), 'fields' => 'EmdeonOrder.order_id', 'recursive' => -1));
						$all_test_string = array();
						for($i = 0; $i < count($orders); $i++)
            {
                 $test_strings = $this->getOrderTestString($orders[$i]['EmdeonOrder']['order_id'], false);
				 
							 if(is_array($test_strings))
							 {
								$all_test_string = array_merge($all_test_string, $test_strings);    
							 }
            }
						
						foreach($all_test_string as &$item)
						{
							$item = ucfirst(strtolower($item));
						}						
						
						if ($all_test_string) {
							$plan[$diagnosis['diagnosis']]['emdeon_lab_orders']['items'] = $all_test_string;
							$combinedList = array_merge($combinedList, $all_test_string);
						}
						
						
        }
		
				$combinedList = array_unique($combinedList);
				
				if ($combine) {
					foreach ($diagnosis_data as $diagnosis) {
						if ($diagnosis['diagnosis'] == $first_diagnosis) {
							$plan[$diagnosis['diagnosis']]['emdeon_lab_orders']['items'] = $combinedList;
							continue;
						}
						
						unset($plan[$diagnosis['diagnosis']]['emdeon_lab_orders']);
					}
				}
				
			if ($combine && isset($plan[$first_diagnosis]['emdeon_lab_orders'])) {
				$plan['combined']['emdeon_lab_orders']['items'] = $plan[$first_diagnosis]['emdeon_lab_orders']['items'];
			}
		
        return $plan;
    }
    
	function paginateCount($conditions = null, $recursive = 0, $extra = array()) 
	{
		$parameters = compact('conditions');
		$this->recursive = $recursive;
		$count = $this->find('count', array_merge($parameters, $extra));
		if (isset($extra['group'])) 
		{
			$count = $this->getAffectedRows();
		}
		return $count;
	}		
  
  function queuePatientOrder($emdeonOrderId) {
    
    $db_config = $this->getDataSource()->config;
    $shellcommand = "php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app \"".APP."\" emdeon_orders_queue ".$db_config['database']." ".$emdeonOrderId." >> /dev/null 2>&1 & ";
    exec($shellcommand);    
    
  }
  
  function processPatientOrderQueue($emdeonOrderId) {

    App::import('Model', 'Order');
    $order = new Order();
    $emdeon_xml_api = new Emdeon_XML_API();
    $valid_labs = $emdeon_xml_api->getValidLabs();

    
    if (!( isset($valid_labs) && count($valid_labs) > 0 )) {
      return true;
    }
    
    $existing = $order->find('count', array(
        'conditions' => array(
            'Order.data_id' => $emdeonOrderId,
            'Order.order_type' => 'Labs',
            'Order.item_type' => 'plan_labs_electronic',
        ),
    ));
    
    if ($existing) {
      return true;
    }
    
    $valid_labs_str = '('.implode(",", $valid_labs).')';

    $sql = "
      SELECT 
        '' AS encounter_status,
        '0' AS encounter_id,
        emdeon_orders.order_id AS data_id,
        patient_demographics.patient_id AS patient_id,
        emdeon_orderables.description AS test_name,
        '' AS source,
        CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) AS patient_firstname,
        CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1) AS patient_lastname,
        CONCAT(emdeon_orders.ref_cg_fname, ' ',emdeon_orders.ref_cg_lname) as provider_name,
        '' AS priority, 
        'Labs' AS order_type,
        emdeon_orders.order_status AS status,
        emdeon_orders.modified_timestamp AS date_performed,
        emdeon_orders.modified_timestamp AS date_ordered,
        emdeon_orders.modified_timestamp AS modified_timestamp,
        'plan_labs_electronic' AS item_type

      FROM emdeon_orders
      INNER JOIN patient_demographics 
        ON patient_demographics.mrn = emdeon_orders.person_hsi_value 
        AND emdeon_orders.person_first_name = CONVERT(DES_DECRYPT(patient_demographics.first_name) USING latin1) 
        AND emdeon_orders.person_last_name = CONVERT(DES_DECRYPT(patient_demographics.last_name) USING latin1)
      INNER JOIN emdeon_order_tests ON emdeon_order_tests.order_id = emdeon_orders.order_id
      INNER JOIN emdeon_orderables ON emdeon_orderables.order_test_id = emdeon_order_tests.order_test_id
      WHERE  emdeon_orders.order_id = '".$emdeonOrderId."' AND 
        emdeon_orders.order_mode = 'electronic' AND emdeon_orders.lab IN $valid_labs_str GROUP BY patient_id, test_name, priority, order_type, date_performed, emdeon_orders.modified_timestamp	
    ";

    $result = $this->query($sql);

    $orderData = array();
    foreach ($result as $r) {
      
      if (!$orderData) {
        $orderData = array(
          'data_id' => $r['emdeon_orders']['data_id'],
          'encounter_id' => 0,
          'patient_id' => $r['patient_demographics']['patient_id'],
          'encounter_status' => '',
          'test_name' => $r['emdeon_orderables']['test_name'],
          'source' => '',
          'patient_firstname' => $r[0]['patient_firstname'],
          'patient_lastname' => $r[0]['patient_lastname'],
          'provider_name' => $r[0]['provider_name'],
          'priority' => '',
          'order_type' => 'Labs',
          'status' => $r['emdeon_orders']['status'],
          'item_type' => 'plan_labs_electronic',
          'date_performed' => $r['emdeon_orders']['modified_timestamp'],
          'date_ordered' => $r['emdeon_orders']['modified_timestamp'],
          'modified_timestamp' => $r['emdeon_orders']['modified_timestamp'],
        );							        
      } else {
        $orderData['test_name'] .= ', ' . $r['emdeon_orderables']['test_name'];
      }

    }    
    
    if ($orderData) {
      $order->create();
      $order->save($orderData);
    }
    
    
  }  
		
}

?>
