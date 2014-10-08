<?php
App::import( 'Lib', 'ELabs_API', 				array( 'file' => 'ELabs_API.php' ));
App::import( 'Lib', 'ELabs_API_Emdeon', 		array( 'file' => 'ELabs_API_Emdeon.php' ));
App::import( 'Lib', 'ELabs_API_MacPractice', 	array( 'file' => 'ELabs_API_MacPractice.php' ));
App::import( 'Lib', 'ELabs_API_HL7Files', 		array( 'file' => 'ELabs_API_HL7Files.php' ));
App::import( 'Lib', 'Emdeon_XML_API',			array( 'file' => 'Emdeon_XML_API.php' ));
App::import( 'Lib', 'Emdeon_HL7', 				array( 'file' => 'Emdeon_HL7.php' ));

class EmdeonLabResult extends AppModel
{
    public $name = 'EmdeonLabResult';
    public $primaryKey = 'lab_result_id';
    public $useTable = 'emdeon_lab_results';
    public $belongsTo = array(
        'EmdeonOrder' => array(
            'className' => 'EmdeonOrder',
            'foreignKey' => 'order_id'
        )
    );
	public $specialPaginate = false;
	public $specialResult = null;
	public $quickDashboard = false;
	public $actsAs = array('Containable');
	public $api = null;
	public $virtualFields = array(
    'hl7' => 'UNCOMPRESS(hl7)'
  );
	/**
	 * See what type of e-lab behavior we are using and return appropriate API instance
	 * 
	 * @return	ELabsAPI		API supporting sync(), getClients(), getHTML(), getReportList(), getReport()
	 */
	public function getELabsAPI() {
		if( is_null( $this->api )) {
			$this->api = new ELabs_API_Emdeon();
			if( !$this->api->isOK() ) {
				$this->api = new ELabs_API_MacPractice();
				if( !$this->api->isOK() ) {
					$this->api = new ELabs_API_HL7Files();
					if( !$this->api->isOK() ) {
						$this->api = new ELabs_API();
					}
				}
			}
		}
		return $this->api;
	}
		
    public function beforeSave($options)
    {
        $user_id = (isset($_SESSION['UserAccount']['user_id']) ? $_SESSION['UserAccount']['user_id'] : 0);

        $this->data['EmdeonLabResult']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['EmdeonLabResult']['modified_user_id'] = $user_id;
		
		if(isset($this->data['EmdeonLabResult']['html']))
		{
			$this->data['EmdeonLabResult']['html'] = DboSource::expression("COMPRESS('" . $this->sanitize_data($this->data['EmdeonLabResult']['html']) . "')");
		}
		
		if(isset($this->data['EmdeonLabResult']['hl7']))
		{
			$this->data['EmdeonLabResult']['hl7'] = DboSource::expression("COMPRESS('" . $this->sanitize_data($this->data['EmdeonLabResult']['hl7']) . "')");
		}
		
		return true;
    }

    public function beforeFind($queryData)
    {
        $this->virtualFields['ordering_client'] = sprintf("TRIM(CONCAT(%s.physician_first_name, ' ', %s.physician_last_name))", $this->alias, $this->alias, $this->alias);
        $this->virtualFields['report_patient_name'] = sprintf("TRIM(CONCAT(%s.patient_first_name, ' ', %s.patient_last_name))", $this->alias, $this->alias, $this->alias);
		$this->virtualFields['html'] = sprintf("UNCOMPRESS(%s.html)", $this->alias);
		$this->virtualFields['hl7'] = sprintf("UNCOMPRESS(%s.hl7)", $this->alias);
        return $queryData;
    }

    private function createDBDate($str)
    {
        $year = sprintf("%04d", (int) substr($str, 0, 4));
        $month = sprintf("%02d", (int) substr($str, 4, 2));
        $day = sprintf("%02d", (int) substr($str, 6, 2));
        $hour = sprintf("%02d", (int) substr($str, 8, 2));
        $minute = sprintf("%02d", (int) substr($str, 10, 2));
        $second = sprintf("%02d", (int) substr($str, 12, 2));

        return $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second;
    }

    /**
     * Get all lab result status and returns a result set array.
     *
     * @return array Array of lab result statuses
     */
    public function loadLabResultStatusList()
    {
        $this->EmdeonLabResultStatus = ClassRegistry::init('EmdeonLabResultStatus');
        $status_list = $this->EmdeonLabResultStatus->find('all');
        
        $data = array();
        
        foreach($status_list as $status)
        {
            $data[$status['EmdeonLabResultStatus']['status_code']] = $status['EmdeonLabResultStatus']['description'];    
        }
        
        return $data;
    }

    /**
     * Get specific lab result discreet data and returns a result set array.
     *
     * @param int $lab_result_id lab result identifier
     * @param bool $simplified Whether to returns just main test name or entire result data
     * @return array Array of tests
     */
    public function getLabResultTestInformation($lab_result_id, $simplified = false, $includeEmpty = false)
    {
        $status_list = array('P' => 'Partial', 'F' => 'Final', 'C' => 'Corrected', 'X' => 'Cancel');
        
        $lab_result = $this->find('first', array('conditions' => array('EmdeonLabResult.lab_result_id' => $lab_result_id),'recursive' => -1, 'fields' => array('EmdeonLabResult.hl7')));
        
				return $this->getTestDataFromHL7(json_decode($lab_result['EmdeonLabResult']['hl7'], true), $simplified, $includeEmpty);
    }
		
		function getTestDataFromHL7($hl7, $simplified = false, $includeEmpty = false) {
        $emdeon_hl7 = new Emdeon_HL7($hl7);
        $hl7_data = $emdeon_hl7->getData();
        
        $all_data = array();
        
        foreach ($hl7_data['test_segments'] as $test_segment)
        {
            $data = array();
                
            if(!isset($data['results']))
            {
                $data['results'] = array();
            }
            
            foreach($test_segment as $test_data)
            {
                if($test_data['segment_type'] == 'OBR')
                {
                    if($test_data['obr_order_code'] == '_NOTE')
                    {
                        continue;    
                    }

                    // Phlebotomy/Venipuncture is not a lab test
                    // It is just there to note that there were charges
                    // for doing venipuncture
                    if($test_data['obr_order_code'] == 'PHLEB')
                    {
                        continue 2;    
                    }
                    
                    $data['order_description'] = $test_data['obr_description'];
                    $data['placer_order_number'] = $test_data['obr_placer_order_number'];
                    $data['specimen_source'] = $test_data['obr_specimen_source_code'];
                    $data['specimen_condition'] = $test_data['obr_specimen_source_description'];
                    $data['datetime'] = __date("Y-m-d H:i:s", strtotime($test_data['obr_observation_date_time']));
                    $data['status'] = $test_data['obr_result_status'];
                }
                else if($test_data['segment_type'] == 'OBX')
                {
                    if($test_data['obx_value_type'] == 'NM' || $test_data['obx_value_type'] == 'ST') {
											$result = array();
											$result['order_code'] = $test_data['obx_analyte_code'];
											$result['test_name'] = $test_data['obx_analyte_description'];
											$result['loinc'] = $test_data['obx_LOINC_code'];
											$result['result_value'] = $test_data['obx_result_value'];
											list($result['unit']) = explode('^', $test_data['obx_unit_code']);
											$result['normal_range'] = $test_data['obx_range'];
											$result['abnormal_flag'] = $test_data['obx_abnormal_flags'];
											$result['observe_result_status'] = $test_data['obx_observe_result_status'];
											$result['date_time'] = __date("Y-m-d H:i:s", strtotime($test_data['obx_date_time_of_the_observation']));
											$result['comment'] = '';
											$data['results'][count($data['results'])] = $result;
                    } else if($includeEmpty && $test_data['obx_value_type'] == 'TX') {
											$result = array();
											$result['order_code'] = $test_data['obx_analyte_code'];
											$result['test_name'] = $test_data['obx_analyte_description'];
											$result['loinc'] = $test_data['obx_LOINC_code'];
											$result['result_value'] = $test_data['obx_result_value'];
											list($result['unit']) = explode('^', $test_data['obx_unit_code']);
											$result['normal_range'] = $test_data['obx_range'];
											$result['abnormal_flag'] = $test_data['obx_abnormal_flags'];
											$result['observe_result_status'] = $test_data['obx_observe_result_status'];
											$result['date_time'] = __date("Y-m-d H:i:s", strtotime($test_data['obx_date_time_of_the_observation']));
											$result['comment'] = '';
											
										}
                    
                }
								else if ($includeEmpty && $test_data['segment_type'] == 'NTE') {
									$result['comment'] .= $test_data['comment'];
									$currentCount = count($data['results']);
									
									if ($currentCount && $data['results'][$currentCount-1]['test_name'] == $result['test_name']) {
										$data['results'][$currentCount-1] = $result;
									} else {
										$data['results'][$currentCount] = $result;
									}
									
									
								}			
								
                else
                {
                    continue;    
                }
            }
            
            if(count($data) > 0)
            {
                if($simplified)
                {
                    $final_data = array();
                    $final_data['test_name'] = @$data['order_description'];
                    $final_data['date_performed'] = @$data['datetime'];
                    $final_data['status'] = @$status_list[$data['status']];
                    $all_data[] = $final_data;
                }
                else
                {
                    $all_data[] = $data;
                }
            }
        }
        
        return $all_data;			
		}
		
		function executeGraph(&$controller){
			$lab_result_id = (isset($controller->params['named']['lab_result_id'])) ? $controller->params['named']['lab_result_id'] : "";
			$patient_id = (isset($controller->params['named']['patient_id'])) ? $controller->params['named']['patient_id'] : "";
			$task = (isset($controller->params['named']['task'])) ? $controller->params['named']['task'] : "";

			$encounter_id = (isset($controller->params['named']['from_encounter'])) ? $controller->params['named']['from_encounter'] : '';
			
			
			switch($task) {
				
				case 'graph': {
					$controller->layout = 'lab_result_graph';
					$test_name = (isset($controller->params['url']['test_name'])) ? $controller->params['url']['test_name'] : "";

					$data = $this->getPastTestData($lab_result_id, $test_name);
					
					
					
					$controller->set(compact('data', 'test_name'));
					
				} break;
				
				default: {
					$this->id = $lab_result_id;

					$labResult = $this->read();
					$controller->set(compact('labResult'));

					$hl7 = $labResult['EmdeonLabResult']['hl7'];
					
					$data = $this->getTestDataFromHL7(json_decode($hl7, true));

				} break;
				
			}
			

			
			
			$controller->set(compact('data', 'patient_id', 'lab_result_id', 'encounter_id'));
			
		}
		
		function getPastTestData($labResultId, $testName = 'HDL CHOLESTEROL') {
			
			// Note: We want the latest/final lab result from an order
			// This is because there can be several emdeon_lab_results 
			// associated with an order, in case of updates 
			// for corrections or partial results
			
			$info = array();
			
			$this->virtualFields['emdeon_datetime'] = "STR_TO_DATE(EmdeonOrder.date, '%c/%e/%Y %H:%i' )";
			$this->contain(array(
				'EmdeonOrder' => array(
					'fields' => array(
						'date', 'person_hsi_value',
					),
				),
			));
			
			$conditions = array(
				'EmdeonLabResult.lab_result_id' => $labResultId,
			);
			
			$labResult = $this->find('first', array(
				'conditions' => $conditions,
				'fields' => array(
					'order_id', 'emdeon_datetime',
				),
			));

			
			$orderConditions = array(
        'OR' => array(
            'AND' => array(
              'EmdeonOrder.person_hsi_value' => $labResult['EmdeonOrder']['person_hsi_value'],
              'EmdeonOrder.order_datetime <=' => __date('Y-m-d H:i:s', strtotime($labResult['EmdeonLabResult']['emdeon_datetime'])),
            ),
            'EmdeonOrder.order_id' => $labResult['EmdeonOrder']['order_id']
        )
				
			);

			$this->EmdeonOrder->contain(array(
				'EmdeonLabResult' => array(
					'fields' => array('hl7', 'report_service_date'),
				),
			));
			
			$orders = $this->EmdeonOrder->find('all', array(
				'conditions' => $orderConditions,
				'fields' => array(
					'date', 'order_datetime'
				),
				'order' => array('EmdeonOrder.order_id DESC')
			));
			
			
			$pastResults = array();
			
			
			foreach ($orders as $o) {
				if (!isset($o['EmdeonLabResult'][0]['hl7'])) {
					continue;
				}
				
				$data = $this->getTestDataFromHL7(json_decode($o['EmdeonLabResult'][0]['hl7'], true));
				
				foreach ($data as $d) {
					if (empty($d['results'])) {
						continue;
					}
					
					foreach ($d['results'] as $test) {
						
						if ($test['test_name'] != $testName) {
							continue;
						}

						if (!isset($info['graph_details'])) {
							$info['graph_details'] = array(
								'unit' => $test['unit'],
							);
						}            
            
						$date = __date('Y-m-d', strtotime($o['EmdeonLabResult'][0]['report_service_date']));
						$pastResults[$date] = $test['result_value'];
					}
					
					
				}
			}
			
			ksort($pastResults);
			
			$info['data'] = $pastResults;
			
			return $info;
			
		}
		/*
		  this is called from health_maintenance_flowsheet_data shell for model health_maintenance_flowsheet_data
		  to get latest lab result for a patient. ticket #2924
		*/
		function findRecentTest($patient_id, $testName) {
			$mrn = ClassRegistry::init('PatientDemographic')->getPatientMRN($patient_id);
			//cover all case types
			$test_lower=strtolower($testName);
			$test_upper=strtoupper($testName);
		 	// return the latest record	
			$lab = $this->find('first', array(
				'conditions' => array(
					'EmdeonLabResult.mrn' => $mrn,
					'EmdeonLabResult.hl7 REGEXP ' => "\^$testName\^|\^$test_lower\^|\^$test_upper\^"  //hl7 data is in plain text
				),
				'order' => array(
					'EmdeonLabResult.report_service_date' => 'DESC'
				),
				'recursive'=> -1,
				'fields' => array('EmdeonLabResult.hl7','EmdeonLabResult.report_service_date')
			));

			$unit = '';
			$testFound = false;
			$date = '';
			$result = '';
			
			//foreach ($labs as $l) {		
				//search through HL7 data 
				$data = $this->getTestDataFromHL7(json_decode($lab['EmdeonLabResult']['hl7'], true));
				foreach ($data as $d) {
					if (empty($d['results'])) {
						continue;
					}
					
					foreach ($d['results'] as $test) {
						
						$unit = $test['unit'];
						
						if (strtolower($test['test_name']) != strtolower($testName)) {
							continue;
						}
						$date = __date('Y-m-d', strtotime($lab['EmdeonLabResult']['report_service_date']));
						$result = $test['result_value'];
						
						$testFound = true;
						break;
					}
					
					if ($testFound) {
						break;
					}
				}				
			//}
			
			if (!$testFound) {
				return array();
			}
			
			return array(
				'date' => $date,
				'result_value' => $result . ' ' . $unit,
			);
			
		}
		
		

    /**
     * Get specific lab result by encounter and returns a result set array.
     *
     * @param int $encounter_id encounter identifier
     * @return array Array of lab results
     */
    public function getLabResultsByEncounter($encounter_id)
    {
        $order_ids = $this->EmdeonOrder->getOrderIdsByEncounter($encounter_id);
        $all_lab_results = $this->find('all', array(
            'fields' => array('EmdeonLabResult.lab_result_id', 'EmdeonLabResult.order_id'),
            'conditions' => array('EmdeonLabResult.order_id' => $order_ids),
            'order' => array('EmdeonLabResult.date_time_transaction' => 'ASC')
        ));
        
        $lab_results = array();
        
        foreach($all_lab_results as $current_lab_result)
        {
            $lab_results[$current_lab_result['EmdeonLabResult']['order_id']] = $current_lab_result['EmdeonLabResult']['lab_result_id'];
        }
        
        $lab_results_data = array();
        
        $i = 1;
        
        foreach($lab_results as $order_id => $lab_result_id)
        {
            $data = $this->getLabResultTestInformation($lab_result_id, true);
            
            $lab_results_data = array_merge($lab_results_data, $data);
        }
        
        for($i = 0; $i < count($lab_results_data); $i++)
        {
            $lab_results_data[$i]['datetime_flag'] = $i;
        }
        
        return $lab_results_data;
    }
    
    /**
     * Get specific lab result by patitent and returns a result set array.
     *
     * @param int $encounter_id encounter identifier
     * @return array Array of lab results
     */
    public function getLabResultsByPatient($patient_id)
    {
			
				$patient = ClassRegistry::init('PatientDemographic')->getPatient($patient_id);
				$user = ClassRegistry::init('UserAccount')->getCurrentUser($_SESSION['UserAccount']['user_id']);
        $order_ids = $this->EmdeonOrder->getOrderByPatient($user, $patient['mrn']);
        $all_lab_results = $this->find('all', array(
            'fields' => array('EmdeonLabResult.lab_result_id', 'EmdeonLabResult.order_id'),
            'conditions' => array('EmdeonLabResult.order_id' => $order_ids),
            'order' => array('EmdeonLabResult.date_time_transaction' => 'ASC')
        ));
        
        $lab_results = array();
        
        foreach($all_lab_results as $current_lab_result)
        {
            $lab_results[$current_lab_result['EmdeonLabResult']['order_id']] = $current_lab_result['EmdeonLabResult']['lab_result_id'];
        }
        
        $lab_results_data = array();
        
        $i = 1;
        
        foreach($lab_results as $order_id => $lab_result_id)
        {
            $data = $this->getLabResultTestInformation($lab_result_id, true);
            
            $lab_results_data = array_merge($lab_results_data, $data);
        }
        
        for($i = 0; $i < count($lab_results_data); $i++)
        {
            $lab_results_data[$i]['datetime_flag'] = $i;
        }
        
        return $lab_results_data;
    }		
		
    /**
     * Check if the specific lab results is valid patient
     *
     * @param int $lab_result_id lab result identifier
     * @return bool
     */
    public function hasMatchedPatient($lab_result_id, $data = false)
    {
        $result = false;

				if ($data) {
					$order = $data;
				} else {
					$order = $this->find('first', array('conditions' => array('EmdeonLabResult.lab_result_id' => $lab_result_id), 'recursive' => -1, 'fields' => array('patient_first_name','patient_last_name','patient_dob','mrn')));
				}

        $this->bindModel(array('hasMany' => array('PatientDemographic')));
				
				
				$conditions = array(
					'OR' => array(
						array(
							'AND' => array(
								'PatientDemographic.first_name' => $order['EmdeonLabResult']['patient_first_name'], 
								'PatientDemographic.last_name' => $order['EmdeonLabResult']['patient_last_name'],
								'PatientDemographic.dob' => $order['EmdeonLabResult']['patient_dob'],
							),
						),
						array(
							'AND' => array(
								'PatientDemographic.mrn' => $order['EmdeonLabResult']['mrn'], 
							),
						),
					),
				);
				
				
        $patient = $this->PatientDemographic->find('first', array(
					'conditions' => $conditions));

        if ($patient)
        {
            $result = $patient['PatientDemographic']['patient_id'];
        }

        $this->unbindModel(array('hasMany' => array('PatientDemographic')));

        return $result;
    }

	public function getAlertPaginate(&$controller, $user = '', $days = '', $search = '') {
		$data = array();
		$this->unbindModelAll();
		$conditions = array(
			'EmdeonLabResult.patient_last_name <>' => '',
			'EmdeonLabResult.physician_last_name <>' => '', 
			'EmdeonLabResult.approve' => 0, 
		);

		//if $user is defined, filter results by that provider; match by name since no other way in this table.
		if($user) {
			$conditions['EmdeonLabResult.ordering_client']= $user['full_name'];
		}
		
		if($days)	{
			$conditions['DATEDIFF(CURDATE(), DATE(EmdeonLabResult.report_service_date)) <'] = $days;
		}
		
		// Check for matching mrn in patient demographics. Also watch out for empty mrns!
		// $this->virtualFields['patient_mrn_count'] = sprintf("SELECT COUNT(`patient_demographics`.`mrn`) FROM `patient_demographics` WHERE `patient_demographics`.`mrn` = %s.`mrn` AND `patient_demographics`.`mrn` <> '' LIMIT 1", $this->alias);
					
		$controller->paginate['EmdeonLabResult'] = array(
			'order' => array(
				'EmdeonLabResult.report_service_date' => 'DESC'
			),
		);
		
		if ($this->quickDashboard) {
			$controller->paginate['EmdeonLabResult']['limit'] = 10;
		} else {
			$this->virtualFields['sortable_name'] = sprintf("CONCAT(%s.patient_first_name,' ', %s.patient_last_name)", $this->alias, $this->alias);
		}
		
		if ($search) {
			$search_keyword = str_replace(',', ' ', trim($search));
			$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);

			$keywords = explode(' ', $search_keyword);
			$patient_search_conditions = array();
			foreach($keywords as $word) {
				$patient_search_conditions[] = array('OR' => 
						array(
							'EmdeonLabResult.patient_first_name LIKE ' => $word . '%', 
							'EmdeonLabResult.patient_last_name LIKE ' => $word . '%'
						)
				);
			}			
			$conditions['OR'] = $patient_search_conditions;
		}
		
		$items =  $controller->paginate('EmdeonLabResult', $conditions);
		
		$conditions = array('OR' => array());
		
		$labOrders = array();
		foreach ($items as $item) {
	        $emdeon_hl7 = new Emdeon_HL7(json_decode($item['EmdeonLabResult']['hl7'], true));
    	    $hl7_data = $emdeon_hl7->getData();
			$test_list = array();
      		foreach ($hl7_data['test_segments'] as $test_segment) {
            	for ($i = 0; $i < count($test_segment); $i++) {
                	if ($test_segment[$i]['segment_type'] == 'OBR' && $test_segment[$i]['obr_order_code'] != '_NOTE') {
                    	$test_list[] = $test_segment[$i]['obr_description'];
                	}
            	}
        	}
			$item['EmdeonLabResult']['_test_list'] = $test_list;
				
			$conditions['OR'][] = array(
					'AND' => array(
						'PatientDemographic.first_name' => $item['EmdeonLabResult']['patient_first_name'], 
						'PatientDemographic.last_name' => $item['EmdeonLabResult']['patient_last_name'],
						'PatientDemographic.dob' => $item['EmdeonLabResult']['patient_dob'],
					),					
			);
				
			if ($item['EmdeonLabResult']['mrn']) {
				$conditions['OR'][] = array(
						'AND' => array(
							'PatientDemographic.mrn' => $item['EmdeonLabResult']['mrn'], 
						),					
					);
			}
			$labOrders[] = $item;
		}
		
		if (empty($labOrders)) {
			return $labOrders;
		}
		
		$controller->loadModel('PatientDemographic');
		$controller->PatientDemographic->contain();
		$patients = $controller->PatientDemographic->find('all', array(
			'fields' => array(
				'PatientDemographic.patient_id', 
				'PatientDemographic.mrn', 
				'PatientDemographic.first_name', 
				'PatientDemographic.last_name', 
				'PatientDemographic.dob'
				),
			'conditions' => $conditions,
			));
		
		$fnames = array();
		$lnames = array();
		$dobs = array();
		$mrns = array();
		foreach ($patients as $p) {
			$mrns[$p['PatientDemographic']['patient_id']] = $p['PatientDemographic']['mrn'];
			$fnames[$p['PatientDemographic']['patient_id']] = strtolower($p['PatientDemographic']['first_name']);
			$lnames[$p['PatientDemographic']['patient_id']] = strtolower($p['PatientDemographic']['last_name']);
			$dobs[$p['PatientDemographic']['patient_id']] = strtolower($p['PatientDemographic']['dob']);
		}
		
		$labResults = array();
		
		
		foreach ($labOrders as $l) {
			$patient_id = 0;
			
			$mrn_pid = array_search($l['EmdeonLabResult']['mrn'], $mrns);
			
			if ($mrn_pid !== false) {
				$patient_id = $mrn_pid;
			} else {
				
				$dob_pid = array_search($l['EmdeonLabResult']['patient_dob'], $dobs);
				$fname_pid = array_search(strtolower($l['EmdeonLabResult']['patient_first_name']), $fnames);
				$lname_pid = array_search(strtolower($l['EmdeonLabResult']['patient_last_name']), $lnames);

				if ( $dob_pid == $fname_pid && $fname_pid == $lname_pid && $lname_pid !== false) {
					$patient_id = $dob_pid;
				}
				
			}
			
			$l['EmdeonLabResult']['patient_id'] = $patient_id;
			
			if ($l['EmdeonLabResult']['patient_id'] && $l['EmdeonLabResult']['order_id'] == '0') {
				$l['EmdeonLabResult']['order_id'] = $this->assignLabResult($l['EmdeonLabResult']['lab_result_id'], $patient_id);
			}
			
			$labResults[] = $l;
		}
		
		return $labResults;
	}

	public function getAlert( $user = '', $days = '' ) {
		$client_ids = $this->getELabsAPI()->getClients( true );
		$data = array();
		$conditions = array( 
				'EmdeonLabResult.approve' => 0, 
				'EmdeonLabResult.receiving_client_id' => $client_ids 
		);
		
		// if $user is defined, filter results by that provider; match by name since no other way in this table.
		if( $user ) {
			$conditions['EmdeonLabResult.ordering_client'] = $user['full_name'];
		}
		if( $days ) {
			$conditions['DATEDIFF(CURDATE(), DATE(EmdeonLabResult.report_service_date)) <'] = $days;
		}
		$items = $this->find( 'all', array( 
				'conditions' => $conditions, 
				'order' => array( 
						'EmdeonLabResult.report_service_date' => 'DESC' 
				) 
		));
		foreach( $items as $item ) {
			$patient_id = $this->hasMatchedPatient( $item['EmdeonLabResult']['lab_result_id'] );
			
			if( $patient_id ) {
				$tests = $this->getLabResultTests( $item['EmdeonLabResult']['lab_result_id'] );
				
				foreach( $tests as $test ) {
					$data[] = array( 
							'order_id' => $item['EmdeonOrder']['order_id'], 
							'patient_id' => $patient_id, 
							'placer_order_number' => $item['EmdeonLabResult']['placer_order_number'], 
							'physician_first_name' => $item['EmdeonLabResult']['physician_first_name'], 
							'physician_last_name' => $item['EmdeonLabResult']['physician_last_name'], 
							'report_service_date' => $item['EmdeonLabResult']['report_service_date'], 
							'sponsor_name' => $item['EmdeonLabResult']['sponsor_name'], 
							'test_name' => $test, 
							'sortable_name' => ucwords( strtolower( $item['EmdeonLabResult']['patient_last_name'] ) ) . ', ' . ucwords( strtolower( $item['EmdeonLabResult']['patient_first_name'] ) ) 
					);
				}
			}
		}
		$PatientDemographic = ClassRegistry::init( 'PatientDemographic' );
		for( $i = 0; $i < count( $data ); $i++ ) {
			$data[$i]['patient_name'] = $PatientDemographic->getPatientName( $data[$i]['patient_id'] );
		}
		return $data;
	}

    public function approveResult($lab_result_id)
    {
        $data = array();
        $data['EmdeonLabResult']['lab_result_id'] = $lab_result_id;
        $data['EmdeonLabResult']['approve'] = 1;

        $this->save($data);
    }

    public function isApprove($lab_result_id)
    {
        $ret = false;

        $item = $this->find('first', array('conditions' => array('EmdeonLabResult.lab_result_id' => $lab_result_id),'recursive' => -1, 'fields' => array('approve')));
        if ($item)
        {
            return (($item['EmdeonLabResult']['approve'] == 1) ? true : false);
        }

        return $ret;
    }

    public function getHTML( $lab_result_id, $page = 1 ) {
		$this->recursive = -1;
		$item = $this->find( 'first', array( 
				'fields' => array( 
						'EmdeonLabResult.lab_result_id', 
						'EmdeonLabResult.html' 
				), 
				'conditions' => array( 
						'EmdeonLabResult.lab_result_id' => $lab_result_id 
				) 
		));
		$html = $this->getELabsAPI()->filterHTML( $item['EmdeonLabResult']['html'] );
		
		$paginate = true;
		$doPrint = 'false';
		if( strtolower( $page ) == 'all' || strtolower( $page ) == 'print' ) {
			$paginate = false;
			$doPrint = ( strtolower( $page ) == 'print' ) ? 'true' : 'false';
		}
		$page = intval( $page );
		if( !$page ) {
			$page = 1;
		}
		$paginate = ( $paginate ) ? 'true' : 'false';
        $inject_script = '
        <script language="javascript" type="text/javascript" src="'.$_SESSION['webroot'].'js/jquery/jquery.min.js'.'"></script>
        <script language="javascript" type="text/javascript">
					window.currentPage = '.($page -1).';
					window.pageUrl =  \'{__PAGE_URL__}\';
					window.paginate = '. $paginate . ';
					window.doPrint = ' . $doPrint . ';
			/*
            function getDocHeight() 
            {
                var D = document;
                var ret = Math.max(
                    Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
                    Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
                    Math.max(D.body.clientHeight, D.documentElement.clientHeight)
                );
                
                return ret;
            }
			*/

            $(document).ready(function()
            {
								var page_height = (real_page_count + 1) * 1020;
								
								if (window.paginate) {
									parent.adjustIframeHeight(1100);
								} else {
									parent.adjustIframeHeight(page_height);
								}
                
            });
            
            function printPage()
            {
                window.location.href = window.pageUrl.replace("page:0", "page:print");
            }
        </script>
        <style>
            @media all
            {
              .page-break  { display:none; }
            }
            
            @media print
            {
              .page-break  { float: left; page-break-before:always; }
							
							div.paging {
								display: none;
							}
            }


						/**
						 * Pagination
						 */
						div.paging_area {
							width: 60%; 
							float: right; 
							margin-top: 15px;
						}


						/*div.paging, */div.counter {
							text-align: right;
							margin: 10px;
							margin-right: 0px;
							margin-bottom: 10px;
							width:100%;
						}
						div.paging{
							font-family: "{__BUTTON_FONT_FAMILY__}";
							margin:15px 10px 0 0;
							text-align: right;
							margin-right: 0px;
							margin-bottom: 10px;
							
							width:auto;
							float:right;
						}
						div.paging .dots {
							color: #000;
							padding:0 2px;
							font-weight: bold;
						}
						div.paging .current {
							/*display:block;*/
							color: #D54E21;
							border: 1px solid #bbb;
							padding: 5px 10px;
							margin-right:0;
							text-decoration: none;
							font-weight: bold;
							-moz-border-radius: 4px;
							-webkit-border-radius: 4px;
							border-radius: 4px;
							background: -moz-linear-gradient(center top, #fefefe, #eee) repeat scroll 0 0 transparent;
							background: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#eee));
							filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#fefefe\', endColorstr=\'#eeeeee\');
						}
						div.paging a {
							/*display:block;*/
							color: #464646;
							cursor: pointer;
							padding: 5px 10px;
							margin-right:0;
							text-decoration: none;
							font-weight: bold;
							-moz-border-radius: 4px;
							-webkit-border-radius: 4px;
							border-radius: 4px;
							border: 1px solid #ddd;
							background: -moz-linear-gradient(center top, #fefefe, #eee) repeat scroll 0 0 transparent;
							background: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#eee));
							filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#fefefe\', endColorstr=\'#eeeeee\');
						}
						div.paging a:hover {
							color: #D54E21;
							border: 1px solid #bbb;
						}

        </style>
        <!--[if IE 9]>
        <style>
            @media print
            {
                body {
                    zoom: 158%;    
                }
            }
        </style>
        <![endif]-->
				<script type="text/javascript">
				$(function(){
					if (doPrint) {
							window.print();
					}
				});
				</script>
        ';
        $inject_script .= '</HEAD>';
		$html = str_replace( "</HEAD>", $inject_script, $html );
		$item['EmdeonLabResult']['html'] = $html;	// FIXME: don't believe this is necessary, put this in to exactly replicate old code in case $item is a pass-by-reference or something
		return $html;
    }

    public function getHL7($lab_result_id)
    {
        $item = $this->find('first', array('conditions' => array('EmdeonLabResult.lab_result_id' => $lab_result_id),'recursive' => -1, 'fields' => array('EmdeonLabResult.hl7')));
        return $item['EmdeonLabResult']['hl7'];
    }
    /* this function might not be used any longer. i can't find it called anywhere */
    public function getDisplayResultIds($user, $patient)
    {
        $ids = array();

        $items = $this->find('all', array('conditions' => array('EmdeonLabResult.order_id !=' => 0, 'EmdeonLabResult.mrn' => $patient['mrn'], 'EmdeonLabResult.patient_first_name' => $patient['first_name'], 'EmdeonLabResult.patient_middle_name' => $patient['middle_name'], 'EmdeonLabResult.patient_last_name' => $patient['last_name'])));
        foreach ($items as $item)
        {
            if ($item['EmdeonLabResult']['approve'] == 1)
            {
                $ids[] = $item['EmdeonLabResult']['lab_result_id'];
            }
            else
            {
                if ($user['clinician_reference_id'] == $item['EmdeonOrder']['referringcaregiver'] && strlen($user['clinician_reference_id']) > 0)
                {
                    $ids[] = $item['EmdeonLabResult']['lab_result_id'];
                }
            }
        }

        return $ids;
    }

    public function getLabResultTests($lab_result_id)
    {
        $test_list = array();

        if (is_numeric($lab_result_id)) {
          $lab_result = $this->find('first', array('conditions' => array('EmdeonLabResult.lab_result_id' => $lab_result_id),'recursive' => -1, 'fields' => array('EmdeonLabResult.hl7')));
          $emdeon_hl7 = new Emdeon_HL7(json_decode($lab_result['EmdeonLabResult']['hl7'], true));
        } else {
          $emdeon_hl7 = new Emdeon_HL7(json_decode($lab_result_id, true));
        }
        
        $hl7_data = $emdeon_hl7->getData();

        foreach ($hl7_data['test_segments'] as $test_segment)
        {
            for ($i = 0; $i < count($test_segment); $i++)
            {
                if ($test_segment[$i]['segment_type'] == 'OBR' && $test_segment[$i]['obr_order_code'] != '_NOTE')
                {
                    $test_list[] = $test_segment[$i]['obr_description'];
                }
            }
        }

        return $test_list;
    }
    
    public function extractHL7Details($lab_result_id, $patient_id = 0)
    { 
/*  -- disabled per Izwan,Thomas as not needed any longer - 11/1/12

        $this->PatientLabResult = ClassRegistry::init('PatientLabResult');
        $this->EmdeonOrder = ClassRegistry::init('EmdeonOrder');
        
        $emdeon_xml_api = new Emdeon_XML_API();
        $lab_clients = $emdeon_xml_api->getClients();
        
        $lab_result = $this->find('first', array('conditions' => array('EmdeonLabResult.lab_result_id' => $lab_result_id)));
        
        if(!isset($lab_clients[$lab_result['EmdeonLabResult']['receiving_client_id']]))
        {
            return;
        }
        
        $emdeon_hl7 = new Emdeon_HL7(json_decode($lab_result['EmdeonLabResult']['hl7'], true));
        $hl7_data = $emdeon_hl7->getData();
        
        //delete all existing result
        $this->PatientLabResult->deleteAll(array('PatientLabResult.lab_report_id' => $lab_result['EmdeonLabResult']['order_id']));

        foreach ($hl7_data['test_segments'] as $test_segment)
        {
            $data = array();
                
            if(!isset($data['results']))
            {
                $data['results'] = array();
            }
            
            foreach($test_segment as $test_data)
            {
                if($test_data['segment_type'] == 'OBR')
                {
                    if($test_data['obr_order_code'] == '_NOTE')
                    {
                        continue;    
                    }
                    
                    $data['order_description'] = $test_data['obr_description'];
                    $data['placer_order_number'] = $test_data['obr_placer_order_number'];
                    $data['specimen_source'] = $test_data['obr_specimen_source_code'];
                    $data['specimen_condition'] = $test_data['obr_specimen_source_description'];
                    $data['datetime'] = __date("Y-m-d H:i:s", strtotime($test_data['obr_observation_date_time']));
                    $data['status'] = $test_data['obr_result_status'];
                }
                else if($test_data['segment_type'] == 'OBX')
                {
                    if($test_data['obx_value_type'] != 'NM')
                    {
                        continue;    
                    }
                    
                    $result = array();
                    $result['order_code'] = $test_data['obx_analyte_code'];
                    $result['test_name'] = $test_data['obx_analyte_description'];
                    $result['loinc'] = $test_data['obx_LOINC_code'];
                    $result['result_value'] = $test_data['obx_result_value'];
                    list($result['unit']) = explode('^', $test_data['obx_unit_code']);
                    $result['normal_range'] = $test_data['obx_range'];
                    $result['abnormal_flag'] = $test_data['obx_abnormal_flags'];
                    $result['observe_result_status'] = $test_data['obx_observe_result_status'];
                    $result['date_time'] = __date("Y-m-d H:i:s", strtotime($test_data['obx_date_time_of_the_observation']));
                    
                    $data['results'][count($data['results'])] = $result;
                }
                else
                {
                    continue;    
                }
            }
            
            if(count($data) > 0)
            {
                $order = $this->EmdeonOrder->find('first', array('conditions' => array('EmdeonOrder.order_id' => $lab_result['EmdeonLabResult']['order_id'])));
                
                $patient_lab_result = array();
                
                if($order)
                {
                    $patient_lab_result['PatientLabResult']['diagnosis'] = $order['EmdeonOrder']['single_diagnosis']['description'];
                    $patient_lab_result['PatientLabResult']['icd_code'] = $order['EmdeonOrder']['single_diagnosis']['icd_code'];
                }
                
                
                $patient_lab_result['PatientLabResult']['patient_id'] = $patient_id;
                $patient_lab_result['PatientLabResult']['order_type'] = 'Electronic';
                $patient_lab_result['PatientLabResult']['overall_test_result_status'] = (string)@$data['status'];
                $patient_lab_result['PatientLabResult']['status'] = 'Pending Review';
                $patient_lab_result['PatientLabResult']['lab_report_id'] = $lab_result['EmdeonLabResult']['order_id'];
                $patient_lab_result['PatientLabResult']['ordered_by'] = $lab_result['EmdeonLabResult']['physician_first_name'] . ' ' . $lab_result['EmdeonLabResult']['physician_last_name'];
                $patient_lab_result['PatientLabResult']['test_specimen_source'] = (string)@$data['specimen_source'];
                $patient_lab_result['PatientLabResult']['condition_of_specimen'] = (string)@$data['specimen_condition'];
                $patient_lab_result['PatientLabResult']['date_ordered'] = (string)@$data['datetime'];
                $patient_lab_result['PatientLabResult']['report_date'] = __date("Y-m-d", strtotime($lab_result['EmdeonLabResult']['report_service_date']));
                
                
                $lab_details = $lab_clients[$lab_result['EmdeonLabResult']['receiving_client_id']];
                $patient_lab_result['PatientLabResult']['lab_facility_name'] = $lab_details['lab_name'];
                $patient_lab_result['PatientLabResult']['lab_address_1'] = $lab_details['address_1'];
                $patient_lab_result['PatientLabResult']['lab_address_2'] = $lab_details['address_2'];
                $patient_lab_result['PatientLabResult']['lab_city'] = $lab_details['city'];
                $patient_lab_result['PatientLabResult']['lab_state'] = $lab_details['state'];
                $patient_lab_result['PatientLabResult']['lab_zip_code'] = $lab_details['zip'];
                
                $index = 1;
                
                if(count($data['results']) > 0)
                {
                    foreach($data['results'] as $result)
                    {
                        if($index == 6)
                        {
                            break;    
                        }
                        
                        $patient_lab_result['PatientLabResult']['test_name'.$index] = $result['test_name'];
                        $patient_lab_result['PatientLabResult']['lab_loinc_code'.$index] = $result['loinc'];
                        $patient_lab_result['PatientLabResult']['normal_range'.$index] = $result['normal_range'];
                        $patient_lab_result['PatientLabResult']['result_value'.$index] = $result['result_value'];
                        $patient_lab_result['PatientLabResult']['unit'.$index] = $result['unit'];
                        $patient_lab_result['PatientLabResult']['abnormal'.$index] = (strlen($result['abnormal_flag']) == "" ? "N" : $result['abnormal_flag']);
                        $patient_lab_result['PatientLabResult']['test_result_status'.$index] = $result['observe_result_status'];
                        $patient_lab_result['PatientLabResult']['test_report_date'.$index] = $result['date_time'];
                        
                        $index++;
                    }
                }
                
                $this->PatientLabResult->create();
                $this->PatientLabResult->save($patient_lab_result);
            }
        }
   */
      return true;
    }

    public function extractHL7()
    {
        $lab_results = $this->find('all', array('order' => array('lab_result_id'), 'conditions' => array('EmdeonLabResult.order_id !=' => 0), 'fields' => array('EmdeonLabResult.order_id', 'EmdeonLabResult.lab_result_id'), 'recursive' => -1));

        $this->PatientDemographic = ClassRegistry::init('PatientDemographic');
        
        foreach ($lab_results as $lab_result)
        {
			//read emdeon_orders table for valid mrn - this is always accurate.
			$mrn = $this->EmdeonOrder->getMrnByOrderId($lab_result['EmdeonLabResult']['order_id']);
			
            $patient = $this->PatientDemographic->getPatientByMRN($mrn);
			
			if($patient)
			{
				$patient_id = $patient['PatientDemographic']['patient_id'];
				$this->extractHL7Details($lab_result['EmdeonLabResult']['lab_result_id'], $patient_id);
			}
        }
    }

    /**
     * Get labs from e-source (emdeon or MacPractice)
     * @param string $batchDownload
     * @param string $date_from
     * @param string $date_to
     * @return number
     */
    public function sync( $batchDownload = false, $date_from = '', $date_to = '' ) {
		set_time_limit( 900 );
   		$created 	= 0;
		$data 		= $this->getELabsAPI()->getReportList( $batchDownload, $date_from, $date_to );
		if( count( $data ) > 0 ) {
			$new_lab_result_ids = array();
			foreach( $data as $datum ) {
				$search_item = $this->find( 'count', array(
						'conditions' => array( 'EmdeonLabResult.unique_id' => $datum['unique_id'] ),
						'recursive' => -1
				));
				if( $search_item == 0 ) {
					$this->create();
					$this->save( array( 'EmdeonLabResult' => $datum	));
					$new_lab_result_ids[] = $this->getLastInsertId();
					$created++;
				}
			}
			$this->downloadSource( $batchDownload, $date_from, $date_to );
			if( count( $new_lab_result_ids ) > 0 )
				$this->send_notifications( $new_lab_result_ids );
		}
		return $created;
	}

    public function isValidPatient($first_name, $last_name, $mrn, $gender, $dob)
    {
        /*
        patient validation
        Accept lab results if 3 of 4 of the followings are matched:
        - First Name and Last Name
        - DOB
        - MRN (PID)
        - Gender
        */
        $ret['valid'] = false;
        $ret['mrn'] = '';
        
        $this->PatientDemographic = ClassRegistry::init('PatientDemographic');
            
        /*
        // Old code uses OR of possible mathing predicates
        $conditions = array();
        $subconditions = array();
        $subconditions['PatientDemographic.first_name'] = $first_name;
        $subconditions['PatientDemographic.last_name'] = $last_name;
        $subconditions['PatientDemographic.gender'] = $gender;
        $subconditions['PatientDemographic.mrn'] = $mrn;
        $subconditions['PatientDemographic.dob'] = $dob;
        $conditions['OR'] = $subconditions;
        */
        
        // Code uses MySQL to check for a match (e.g. when 3 or more conditions are met)
        $efirst_name = Sanitize::escape($first_name);
        $elast_name = Sanitize::escape($last_name);
        $egender = Sanitize::escape($gender);
        $emrn = Sanitize::escape($mrn);
        $edob = Sanitize::escape($dob);
        $conditions = array( "(
        	IF(((CONVERT(DES_DECRYPT(`PatientDemographic`.`first_name`) USING latin1) COLLATE latin1_swedish_ci)) = '$efirst_name',1,0)+
        	IF(((CONVERT(DES_DECRYPT(`PatientDemographic`.`last_name`) USING latin1) COLLATE latin1_swedish_ci)),1,0)+
        	IF(((CONVERT(DES_DECRYPT(`PatientDemographic`.`gender`) USING latin1) COLLATE latin1_swedish_ci)) = '$egender',1,0)+
        	IF(PatientDemographic.mrn = '$emrn',1,0)+
        	IF(((CONVERT(DES_DECRYPT(`PatientDemographic`.`dob`) USING latin1) COLLATE latin1_swedish_ci)) = '$edob',1,0)
        	) >= 3");
        
        $this->PatientDemographic->recursive = -1;
        $patients = $this->PatientDemographic->find('all', array(
            'conditions' => $conditions,
            'fields' => array('PatientDemographic.patient_id', 'PatientDemographic.first_name', 'PatientDemographic.last_name', 'PatientDemographic.gender', 'PatientDemographic.mrn', 'PatientDemographic.dob'),
            'callbacks' => false,
        ));
        
				if (!is_array($patients)) {
					return $ret;
				}

        foreach($patients as $patient)
        {
            $valid_count = 0;
            
            if(strcasecmp($patient['PatientDemographic']['first_name'], $first_name) == 0 && strcasecmp($patient['PatientDemographic']['last_name'], $last_name) == 0)
            {
                $valid_count++;
            }
            
            if($patient['PatientDemographic']['mrn'] == $mrn)
            {
                $valid_count++;
            }
            
            if($patient['PatientDemographic']['gender'] == $gender)
            {
                $valid_count++;
            }
            
            if($patient['PatientDemographic']['dob'] == $dob)
            {
                $valid_count++;
            }
            
            if($valid_count >= 3)
            {
                $ret['valid'] = true;
                $ret['patient_id'] = $patient['PatientDemographic']['patient_id'];
                $ret['mrn'] = $patient['PatientDemographic']['mrn'];
            }    
        }
        return $ret;
    }
    
    /**
     * Extract HL7 source.
     *
     *
     * @return null
     */
    public function extractSource()
    {
    		global $APP_TIMER;
        //CakeLog::write('debug', "extractSource start");
        if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('extractSource start');

        $valid_clients = $this->getELabsAPI()->getClients( true );

        $conditions = array();
        $conditions['EmdeonLabResult.extracted'] = 0;        
        $conditions['EmdeonLabResult.receiving_client_id'] = $valid_clients;
        $conditions['EmdeonLabResult.status !='] = 'Patient Not Found';
        $conditions['EmdeonLabResult.downloaded !='] = 0;
        $items = $this->find('all', array(
            'fields' => array('EmdeonLabResult.patient_first_name', 'EmdeonLabResult.patient_last_name', 'EmdeonLabResult.patient_gender', 'EmdeonLabResult.patient_dob', 'EmdeonLabResult.lab_result_id', 'EmdeonLabResult.unique_id', 'EmdeonLabResult.downloaded', 'EmdeonLabResult.hl7'), 
            'conditions' => $conditions
        ));
        
        $result_weight = array('F' => 0, 'P' => 1, 'C' => 2, 'X' => 3);
        $result_status = array('P' => 'Partial', 'F' => 'Final', 'C' => 'Corrected', 'X' => 'Cancel');
        
        foreach ($items as $item){
        		$id = $item['EmdeonLabResult']['lab_result_id'];
        		
        		//CakeLog::write('debug', "process $id");
        		if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label("extractSource process $id");
            
            $data = array();
            $data['EmdeonLabResult']['lab_result_id'] = $item['EmdeonLabResult']['lab_result_id'];
            $emdeon_hl7 = new Emdeon_HL7(json_decode($item['EmdeonLabResult']['hl7'], true));            
        		
        		//CakeLog::write('debug', 'Emdeon_HL7 done');
        		if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('Emdeon_HL7 done');

        	$hl7_data = $emdeon_hl7->getData();
        	$orc = $hl7_data['common_order'];
        	
        	// Find first OBR segment--some vendors only fill in the OBRs and not the ORC
        	$obr = null;
        	foreach( $hl7_data['test_segments'] as $test_segment ) {
        		foreach( $test_segment as $x ) {
        			if( $x['segment_type'] == "OBR" ) {
        				$obr = $x;
        				break;
        			}
        		}
        		break;
        	}

        	//CakeLog::write('debug', 'getData done');
        	if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('getData done');
    
            $data['EmdeonLabResult']['mrn'] = $hl7_data['patient_identification']['patient_id'];
            foreach( array (
            		'filler_order_number',	'placer_order_number', 
            		'physician_identifier', 'physician_last_name', 'physician_first_name', 'physician_middle_name'
            ) as $vn ) {
            	$obrvn = 'obr_' . $vn;
            	$data['EmdeonLabResult'][$vn] = (empty( $orc[$vn] ) ? $obr[$obrvn] : $orc[$vn] );
            }
            $data['EmdeonLabResult']['status'] = 'F';
			$data['EmdeonLabResult']['date_time_transaction'] = $this->createDBDate($orc['date_time_transaction']);
    
      		//CakeLog::write('debug', 'set EmdeonLabResult1 done');
       		if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('set EmdeonLabResult1 done');
            
            foreach($hl7_data['test_segments'] as $test_segment){
				if(isset($result_weight[$test_segment[0]['obr_result_status']])){
					if($result_weight[$test_segment[0]['obr_result_status']] >= $result_weight[$data['EmdeonLabResult']['status']]){
                        $data['EmdeonLabResult']['status'] = $test_segment[0]['obr_result_status'];
						$data['EmdeonLabResult']['date_time_transaction'] = $this->createDBDate($test_segment[0]['obr_results_date_time']);
                    }
                }
            }
        		//CakeLog::write('debug', 'set EmdeonLabResult2 done');
        		if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('set EmdeonLabResult2 done');
        		
            $data['EmdeonLabResult']['status'] = @$result_status[$data['EmdeonLabResult']['status']];
            $data['EmdeonLabResult']['patient_first_name'] = $hl7_data['patient_identification']['first_name'];
            $data['EmdeonLabResult']['patient_middle_name'] = $hl7_data['patient_identification']['middle_name'];
            $data['EmdeonLabResult']['patient_last_name'] = $hl7_data['patient_identification']['last_name'];
            $data['EmdeonLabResult']['patient_gender'] = $hl7_data['patient_identification']['sex'];
            $data['EmdeonLabResult']['patient_dob'] = __date("Y-m-d", strtotime($hl7_data['patient_identification']['date_of_birth']));
            $data['EmdeonLabResult']['extracted'] = 1;

        		//CakeLog::write('debug', 'set EmdeonLabResult3 done');
        		if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('set EmdeonLabResult3 done');
            
            //get order id if available
            $order = $this->EmdeonOrder->find('first', array('conditions' => array('EmdeonOrder.placer_order_number' => $data['EmdeonLabResult']['placer_order_number'])));

        		//CakeLog::write('debug', 'set EmdeonOrder find done');
        		if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('set EmdeonOrder find done');

            if($order){
                $data['EmdeonLabResult']['order_id'] = $order['EmdeonOrder']['order_id'];
                //CakeLog::write('debug', "extractSource order $order");
        				if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label("extractSource order $order");
            } else {
                $valid_patient = $this->isValidPatient($data['EmdeonLabResult']['patient_first_name'], $data['EmdeonLabResult']['patient_last_name'], $data['EmdeonLabResult']['mrn'], $data['EmdeonLabResult']['patient_gender'], $data['EmdeonLabResult']['patient_dob']);

        				//CakeLog::write('debug', 'set isValidPatient done');
        				if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('set isValidPatient done');
                
                if($valid_patient['valid']){
                    $data['EmdeonLabResult']['mrn'] = $valid_patient['mrn'];
                    $this->save($data);	// since the following assignLabResult will depend on this data!
                    $data['EmdeonLabResult']['order_id'] = $this->assignLabResult($data['EmdeonLabResult']['lab_result_id'], $valid_patient['patient_id']);
                		//CakeLog::write('debug', "extractSource valid {$data['EmdeonLabResult']['order_id']}");
                		if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label("extractSource valid {$data['EmdeonLabResult']['order_id']}");
                }
                else
                {
                    $data['EmdeonLabResult']['order_id'] = 0;
                    $data['EmdeonLabResult']['extracted'] = 2;	// Not a valid patient, but don't process it again
                		//CakeLog::write('debug', "extractSource no match");
                		if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('extractSource no match');
                }
            }
            
            $html = $this->api->composeHTML( $emdeon_hl7, $data['EmdeonLabResult'] );
            if( $html )
            	$data['EmdeonLabResult']['html'] = $html;
            
            $this->save($data);

        		//CakeLog::write('debug', 'save done');
        		if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('save done');
        }
        //CakeLog::write('debug', 'extractSource finish');
        if( $APP_TIMER && $APP_TIMER->isActive() ) $APP_TIMER->label('extractSource finish');
    }

    /**
     * Download HL7 data from Emdeon
     *
     * @return null
     */
	public function downloadSource($batchDownload = false, $date_from = '', $date_to = '') {

		$db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
		$cache_file_prefix = $db_config['host'] . '_' . $db_config['database'] . '_';

		$valid_clients = $this->getELabsAPI()->getClients(true);
		$conditions = array();
		$conditions['EmdeonLabResult.downloaded'] = 0;
		$conditions['EmdeonLabResult.receiving_client_id'] = $valid_clients;
		if ( $date_from && $date_to ) {
			$date_from = __date('Y-m-d', strtotime($date_from));
			$date_to = __date('Y-m-d', strtotime($date_to));
			$conditions['EmdeonLabResult.report_service_date BETWEEN ? AND ?'] = array($date_from, $date_to);
		}
		$items = $this->find('all', array('conditions' => $conditions, 'recursive' => -1, 'fields' => array('EmdeonLabResult.unique_id','EmdeonLabResult.lab_result_id')));

		$unique_ids = array();
		foreach ( $items as $item )
			$unique_ids[] = $item['EmdeonLabResult']['unique_id'];
		$reports = $this->api->getReport($unique_ids, $batchDownload, $date_from, $date_to);
		if ( count($reports) == 0 )	// probably connection error, so download later
			return false;

		foreach ( $items as $item ) {
			$data = array();
			$data['EmdeonLabResult']['lab_result_id'] = $item['EmdeonLabResult']['lab_result_id'];
			$report = $reports[$item['EmdeonLabResult']['unique_id']];
			if ( $report['found'] ) {

				$html_report = $report['content_html'];
				$data['EmdeonLabResult']['html'] = $html_report;

				$hl7_report = $report['content_hl7'];
				$hl7_arr = explode("<br />", nl2br($hl7_report));
				foreach ( $hl7_arr as &$item ) {
					$item = trim($item);
				}
				
				// First element is a doctype header
				// Not an HL7 response so skip item
				if ($hl7_arr[0] == '<!DOCTYPE html>') {
					continue;
				}
				
				$data['EmdeonLabResult']['hl7'] = json_encode($hl7_arr);
				$data['EmdeonLabResult']['downloaded'] = 1;
			} else {
				// not found or old version
				$data['EmdeonLabResult']['downloaded'] = 2;
				$data['EmdeonLabResult']['extracted'] = 2;
			}

			if ( !empty( $data['EmdeonLabResult']['mrn'] ) ) {
				$cacheKey = $cache_file_prefix . $data['EmdeonLabResult']['mrn'] . '_' . 'orders_by_patient';
				Cache::delete($cacheKey);

				$cacheKey = $cache_file_prefix . $data['EmdeonLabResult']['mrn'] . '_' . 'orders_by_diagnosis';
				Cache::delete($cacheKey);
			}

			$this->save($data);
		}
		$this->extractSource();
	}
    
    public function execute(&$controller)
    {
        $lab_result_id = (isset($controller->params['named']['lab_result_id'])) ? $controller->params['named']['lab_result_id'] : "";
        $page = (isset($controller->params['named']['page'])) ? $controller->params['named']['page'] : 1;
				$report_html = $this->getHTML($lab_result_id, $page);
        $controller->set('report_html', $report_html);
    }
    
    /**
     * Get specific lab result data
     *
     * @param int $lab_result_id lab result identifier
     * @return array Array of data
     */
    public function getSingleResult($lab_result_id)
    {
        $result = $this->find('first', array('conditions' => array('EmdeonLabResult.lab_result_id' => $lab_result_id), 'recursive' => -1));
        return $result['EmdeonLabResult'];
    }
    
    /**
     * Extract area code from phone number
     *
     * @param string $phone_number Phone Number
     * @return string Area code
     */
    private function getAreaCode($phone_number)
    {
        $result = "";
        
        if(strlen($phone_number) > 0)
        {
            $result = substr($phone_number, 0, 3);    
        }
        
        return $result;
    }
    
    /**
     * Extract area code from phone number - without area code
     *
     * @param string $phone_number Phone Number
     * @return string phone number
     */
    private function getPhoneNumber($phone_number)
    {
        $result = "";
        
        if(strlen($phone_number) > 0)
        {
            $phone_number = str_replace("-", "", $phone_number);
            $phone_number = str_replace($this->getAreaCode($phone_number), "", $phone_number);
            $result = $phone_number;
        }
        
        return $result;    
    }
    
    /**
     * Assign specific lab result to specific patient
     *
     * @param int $lab_result_id Lab result identifier
     * @param int $patient_id Patient identifier
     * @return int Order identifier
     */
    public function assignLabResult($lab_result_id, $patient_id)
    {
        $this->PatientDemographic = ClassRegistry::init('PatientDemographic');
        $this->PracticeSetting = ClassRegistry::init('PracticeSetting');
        $this->EmdeonOrderTest = ClassRegistry::init('EmdeonOrderTest');
        $this->EmdeonOrderable = ClassRegistry::init('EmdeonOrderable');
        
        $info = $this->getELabsAPI()->getInfo();
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $lab_result = $this->getSingleResult($lab_result_id);
        
				// Already assigned to an order, skip
				if ($lab_result['order_id']) {
					return $lab_result['order_id'];
				}
				
        $clients = $this->getELabsAPI()->getClients();
        
        //create matching order data based on results
        $data = array();
        $data['EmdeonOrder']['order_status'] = 'T';
        $data['EmdeonOrder']['orderingorganization'] = $info['facility'];
        $data['EmdeonOrder']['ref_cg_mname'] = $lab_result['physician_middle_name'];
        $data['EmdeonOrder']['person_middle_name'] = $patient['middle_name'];
        $data['EmdeonOrder']['ownerid'] = $info['facility'];
        $data['EmdeonOrder']['person_city'] = $patient['city'];
        $data['EmdeonOrder']['person_home_phone_area_code'] = $this->getAreaCode($patient['home_phone']);
        $data['EmdeonOrder']['person_ssn'] = str_replace("-", "", $patient['ssn']);
        $data['EmdeonOrder']['person_state'] = $patient['state'];
        $data['EmdeonOrder']['person_last_name'] = $patient['last_name'];
        $data['EmdeonOrder']['person_home_phone_number'] = $this->getPhoneNumber($patient['home_phone']);
        $data['EmdeonOrder']['person_zip'] = $patient['zipcode'];
        $data['EmdeonOrder']['person_address_1'] = $patient['address1'];
        $data['EmdeonOrder']['person_address_2'] = $patient['address2'];
        $data['EmdeonOrder']['person_hsi_value'] = $patient['mrn'];
        $data['EmdeonOrder']['person_dob'] = __date("n/j/Y", strtotime($patient['dob']));
        $data['EmdeonOrder']['bill_type'] = 'P';
        $data['EmdeonOrder']['stat_flag'] = 'R';
        $data['EmdeonOrder']['age_type'] = 'YEARS';
        $data['EmdeonOrder']['person_sex'] = $patient['gender'];
        $data['EmdeonOrder']['ref_cg_fname'] = $lab_result['physician_first_name'];
        $data['EmdeonOrder']['referring_cg_id'] = $lab_result['physician_identifier'];
        $data['EmdeonOrder']['ref_cg_lname'] = $lab_result['physician_last_name'];
        $data['EmdeonOrder']['order_type'] = 'Standard';
        $data['EmdeonOrder']['lab'] = @$clients[$lab_result['receiving_client_id']]['lab'];
        $data['EmdeonOrder']['ordering_cg_id'] = $lab_result['receiving_client_id'];
        $data['EmdeonOrder']['date'] = __date("n/j/Y H:i:A", strtotime($lab_result['date_time_transaction']));
        $data['EmdeonOrder']['submission_date'] = __date("n/j/Y H:i:A", strtotime($lab_result['date_time_transaction']));
        $data['EmdeonOrder']['is_split'] = 'n';
        $data['EmdeonOrder']['placer_order_number'] = $lab_result['placer_order_number'];
        $data['EmdeonOrder']['person_first_name'] = $patient['first_name'];
        $data['EmdeonOrder']['include_in_manifest'] = 'n';
        $data['EmdeonOrder']['collection_datetime'] = __date("Y-m-d H:i:s", strtotime($lab_result['report_service_date']));
        $data['EmdeonOrder']['downloaded'] = '1';
        $data['EmdeonOrder']['approve'] = '0';
        $data['EmdeonOrder']['order_mode'] = 'generated';
        $this->EmdeonOrder->create();
        $this->EmdeonOrder->save($data);
        $order_id = $this->EmdeonOrder->getLastInsertId();
        
        $lab_result_details = $this->getLabResultTestInformation($lab_result_id);
        
				
				$modified_user_id = isset($_SESSION['UserAccount']['user_id']) ? $_SESSION['UserAccount']['user_id'] : 0 ;
				
        foreach($lab_result_details as $details)
        {
            $data = array();
            $data['EmdeonOrderTest']['order_id'] = $order_id;
            $data['EmdeonOrderTest']['modified_user_id'] = $modified_user_id;
            $this->EmdeonOrderTest->create();
            $this->EmdeonOrderTest->save($data);
            $order_test_id = $this->EmdeonOrderTest->getLastInsertId();
            
            $data = array();
            $data['EmdeonOrderable']['order_test_id'] = $order_test_id;
            $data['EmdeonOrderable']['description'] = @$details['order_description'];
            $this->EmdeonOrderable->create();
            $this->EmdeonOrderable->save($data);
        }
        
        $data = array();
        $data['EmdeonLabResult']['lab_result_id'] = $lab_result_id;
        $data['EmdeonLabResult']['order_id'] = $order_id;
        $data['EmdeonLabResult']['mrn'] = $patient['mrn'];
        $this->save($data);
		
		$this->extractHL7Details($lab_result_id, $patient_id);
        
        return $order_id;
    }
    
    /**
     * Unmatched lab reports action handler
     *
     * @param pointer controller pointer
     * @return null
     */
    public function unmatched_lab_reports(&$controller, $user = '', $search = '')
    {
        $task = (isset($controller->params['named']['task'])) ? $controller->params['named']['task'] : "";
        
        switch($task)
        {
            case "validate_patient":
            {
                $this->PatientDemographic = ClassRegistry::init('PatientDemographic');
                $ret = array();
                $ret['valid'] = $this->PatientDemographic->validatePatient($controller->data['patient_id'], $controller->data['patient']);    
                echo json_encode($ret);
                exit;
            } break;
            case "patient_not_found":
            {
                $data = array();
                $data['EmdeonLabResult']['lab_result_id'] = $controller->data['lab_result_id'];
                $data['EmdeonLabResult']['status'] = 'Patient Not Found';
                $this->save($data);
                
                $ret = array();
                echo json_encode($ret);
                exit;
            }
            case "view_order":
            {
                $lab_result_id = (isset($controller->params['named']['lab_result_id'])) ? $controller->params['named']['lab_result_id'] : "";
                $lab_result = $this->getSingleResult($lab_result_id);
                $controller->set("current_status", $lab_result['status']);
            } break;
            case "assign_lab_result":
            {
                $lab_result_id = $controller->data['lab_result_id'];
                $patient_id = $controller->data['patient_id'];
                
                $this->assignLabResult($lab_result_id, $patient_id);
                
                $this->PatientDemographic = ClassRegistry::init('PatientDemographic');
                $patient = $this->PatientDemographic->getPatient($patient_id);
                $lab_result = $this->getSingleResult($lab_result_id);
                
                $controller->Session->setFlash(__('Lab result #'.$lab_result['placer_order_number'].' has been assigned to '.$patient['first_name'].' '.$patient['last_name'].'.', true));
                
                $ret = array();
                echo json_encode($ret);
                exit;
            } break;
            default:
            {
            	$this->extractSource();
                $client_ids = $this->getELabsAPI()->getClients( true );
        
								$conditions = array();
				if($user) {
					$conditions['EmdeonLabResult.ordering_client']= $user['full_name'];
				}
								$controller->paginate['EmdeonLabResult'] = array(
									'order' => 'EmdeonLabResult.report_service_date DESC', 
								);
								
				$controller->paginate['EmdeonLabResult']['limit'] = 10;
				
				$this->virtualFields['patient_mrn_count'] = sprintf("SELECT COUNT(`patient_demographics`.`mrn`) FROM `patient_demographics` WHERE `patient_demographics`.`mrn` = %s.`mrn` AND `patient_demographics`.`mrn` <> '' LIMIT 1", $this->alias);
								
				if ($search) {
					$search_keyword = str_replace(',', ' ', trim($search));
					$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);
		
					$keywords = explode(' ', $search_keyword);
					$patient_search_conditions = array();
					foreach($keywords as $word) {
						$patient_search_conditions[] = array('OR' => 
								array(
									'EmdeonLabResult.patient_first_name LIKE ' => $word . '%', 
									'EmdeonLabResult.patient_last_name LIKE ' => $word . '%'
								)
						);
					}			
					$conditions['OR'] = $patient_search_conditions;
				}
				
								$params = array(
									'EmdeonLabResult.ordering_client <>' => '',
									'OR' => array(
											array(
												'AND' => array(
													'EmdeonLabResult.order_id' => 0, 
													'EmdeonLabResult.receiving_client_id' => $client_ids,
													'EmdeonLabResult.patient_first_name <>' => '',
													'EmdeonLabResult.patient_last_name <>' => '',
												),
											),
									),
								);
								
								if ($conditions) {
									$params['AND'] = $conditions;
								}
								
                $lab_results = $controller->paginate('EmdeonLabResult', $params);								
								
                for($i = 0; $i < count($lab_results); $i++)
                {
                    $lab_result = $lab_results[$i];
                    $tests = array();
                    
                    $emdeon_hl7 = new Emdeon_HL7(json_decode($lab_result['EmdeonLabResult']['hl7'], true));
                    $hl7_data = $emdeon_hl7->getData();
                    
                    foreach ($hl7_data['test_segments'] as $test_segment)
                    {
                        foreach($test_segment as $test_data)
                        {
                            if($test_data['segment_type'] == 'OBR')
                            {
                                if($test_data['obr_order_code'] == '_NOTE')
                                {
                                    continue;    
                                }
                                
                                $tests[] = $test_data['obr_description'];
                            }
                        }
                    }
                    
                    $lab_results[$i]['test_list'] = $tests;
                }
                
                
                $controller->set("lab_results", $lab_results);
				//return $lab_results;
            }
        }
    }
	
    /**
     * Send notification to ordering physician when new lab result is arrived.
     *
     * @param Array Lab Result IDs
     * @return null
     */
    public function send_notifications($lab_result_ids)
    {
        App::import('Core', 'View');
        App::import('Core', 'Controller');
        $controller = new Controller();
        $view = new View($controller);
        
        $UserAccount = ClassRegistry::init('UserAccount');
	$pS = ClassRegistry::init('PracticeSetting');
	$practiceSetting = $pS->getSettings();
	$practiceProfile = ClassRegistry::init('PracticeProfile')->find('first');
        $db_config = $pS->getDataSource()->config;
        $cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';

	$cachekey=$cache_file_prefix."emdeon_sent_notifications";
        Cache::set(array('duration' => '+1 hour'));
        Cache::write($cachekey,array());

        foreach($lab_result_ids as $lab_result_id)
        {
            $lab_result = $this->find('first', array('conditions' => array('EmdeonLabResult.lab_result_id' => $lab_result_id),
						     'recursive' => -1,
						     'fields' => array('EmdeonLabResult.physician_identifier','EmdeonLabResult.physician_first_name','EmdeonLabResult.physician_last_name')));
            
            if($lab_result)
            {
                $npi = $lab_result['EmdeonLabResult']['physician_identifier'];
                
                $user_account = $UserAccount->find('first', array('conditions' => array('UserAccount.npi' => $npi)));
                
                if(!$user_account) //retry if no NPI matched up
                {
                    $user_account = $UserAccount->find('first', array('conditions' => array('UCASE(UserAccount.firstname)' => strtoupper($lab_result['EmdeonLabResult']['physician_first_name']), 'UCASE(UserAccount.lastname)' => strtoupper($lab_result['EmdeonLabResult']['physician_last_name']))));
                }                                        

		Cache::set(array('duration' => '+1 hour'));
		$sent_notifications=Cache::read($cachekey);

                if($user_account && !in_array($user_account['UserAccount']['user_id'],$sent_notifications) && $user_account['UserAccount']['new_lab_notify'] )
                {
        		$customer = $practiceSetting->practice_id;       
        		$embed_logo_path='';         
			//see if practice has their own logo, if so use it
			$practice_logo = $practiceProfile['PracticeProfile']['logo_image'];
           		if($practice_logo ) {
           	 	    $embed_logo_path = ROOT. '/app/webroot/CUSTOMER_DATA/'.$practiceSetting->practice_id.'/' . $practiceSetting->uploaddir_administration.'/'.$practice_logo;
           	 	    if(!file_exists($embed_logo_path)) {$embed_logo_path='';   }
           	 	}                

                    $message = array();
                    $message['to_name'] = $user_account['UserAccount']['title']. ' ' . $user_account['UserAccount']['firstname'] . ' ' . $user_account['UserAccount']['lastname'];
                    $message['to_email'] = $user_account['UserAccount']['email'];
                    $message['subject'] = '[' . $practiceProfile['PracticeProfile']['practice_name']. '] New Lab Result Notification';
		    $message['body'] = $view->element('new_lab_result_notification', array('customer' => $customer, 'recipient' => $message['to_name'], 'practice_name' => $practiceProfile['PracticeProfile']['practice_name'], 'partner_id' => $practiceSetting->partner_id));

                    email::send($message['to_name'], $message['to_email'], $message['subject'], $message['body'],'','',true,'','','','',$embed_logo_path);  

		    $notify[] =$user_account['UserAccount']['user_id'];
		    Cache::set(array('duration' => '+1 hour'));
		    Cache::write($cachekey,$notify);
                }
            }
        }
    }
		
  /**
	 * Enable/disable special pagination routine
	 * 
	 * @param type $on Optional. Toggle pagination to on/pff
	 */		
	function specialPaginate($on = null){
		
		if ($on === null) {
			$on  = (!$this->specialPaginate);
		}
		
		$this->specialPaginate = $on;
		
		// If special pagination is on ...
		if ($this->specialPaginate) {
			// Introduce custom sortable fields as virtual fields
			$this->virtualFields['sortable_name'] = "TRIM('')" ;	
			$this->virtualFields['test_name'] = "TRIM('')" ;	
			$this->virtualFields['service_date'] = "TRIM('')" ;	
			
		} else {
			// Remove sortable virtual fields
			unset($this->virtualFields['sortable_name']);
			unset($this->virtualFields['test_name']);
			unset($this->virtualFields['service_date']);
		}
		
		$this->specialResult = null;
		
	}

	/**
	 * paginateCount override to factor in special pagination routine
	 * 
	 * @param type $conditions
	 * @param type $recursive
	 * @param type $extra
	 * @return type 
	 */
	function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		// If special pagination is off,
		// do usual paginate count
		if (!$this->specialPaginate) {
			$parameters = compact('conditions');
			
			if ($recursive != $this->recursive) {
				$parameters['recursive'] = $recursive;
			}
			
			$count = $this->find('count', array_merge($parameters, $extra));
			
			if (isset($extra['group'])) {
				$count = $this->getAffectedRows();
			}			
			
			return $count;
		}
		// Check if user is given
		$user = isset($conditions['user']) ? $conditions['user'] : null;		
		if ($this->specialResult === null) {
			$this->specialResult = $this->getAlert($user);
		}
		$result = $this->specialResult;
		
		// Check if search condition is given
		$search = isset($conditions['search']) ? trim($conditions['search']) : '';

		// Filter results given the condition
		if ($search) {
			$tmp = array();
			
			$search = str_replace(',', ' ', $search);
			$search = preg_replace('/\s\s+/', ' ', $search);
			
			$keywords = explode(' ', $search);			
			foreach ($result as $r) {
				$matchFound = true;
				foreach ($keywords as $k) {
					if (stripos($r['patient_name'], $k) === false ) {
						$matchFound = false;
						break;
					}
				}

				if ($matchFound) {
					$tmp[] = $r;
				}
				
			}
			
			$result = $tmp;
		}		
		
		return count($result);
	}

	/**
	 * paginate override to factor in special pagination routine
	 * 
	 * @param type $conditions
	 * @param type $fields
	 * @param type $order
	 * @param type $limit
	 * @param type $page
	 * @param type $recursive
	 * @param type $extra
	 * @return type 
	 */
	function paginate($conditions, $fields, $order, $limit, $page = 1, $recursive = null, $extra = array()) {

		// If special pagination is off,
		// do usual paginate routine
		if (!$this->specialPaginate) {
			$parameters = compact('conditions', 'fields', 'order', 'limit', 'page');
			if ($recursive != $this->recursive) {
				$parameters['recursive'] = $recursive;
			}
			return $this->find('all', array_merge($parameters, $extra));
		}

		// Check if user is given
		$user = isset($conditions['user']) ? $conditions['user'] : null;
		
		if ($this->specialResult === null) {
			$this->specialResult = $this->getAlert($user);
		}
		
		$result = $this->specialResult;

		if (!$result) {
			return array();
		}

		// Check if search condition is given
		$search = isset($conditions['search']) ? trim($conditions['search']) : '';

		// Filter results given the condition
		if ($search) {
			$tmp = array();
			
			$search = str_replace(',', ' ', $search);
			$search = preg_replace('/\s\s+/', ' ', $search);
			
			$keywords = explode(' ', $search);			
			foreach ($result as $r) {

				$matchFound = true;
				foreach ($keywords as $k) {
					if (stripos($r['patient_name'], $k) === false ) {
						$matchFound = false;
						break;
					}
				}

				if ($matchFound) {
					$tmp[] = $r;
				}
				
			}
			
			if (empty($tmp)) {
				return array();
			}
			
			$result = $tmp;
		}
		
		// If order option is present...
		if ($order) {
			
			// process result sorting 
			$sortable = array(
				'sortable_name' => 'sortable_name',
				'test_name' => 'test_name',
				'service_date' => 'report_service_date',
				'physician_first_name' => 'physician_first_name',
				'physician_last_name' => 'physician_last_name'
			);
			
			$sortField = '';
			$sortDir = '';

			foreach ($order as $key => $val) {
				
				if (array_key_exists($key, $sortable)) {
					$sortField = $sortable[$key];
					$sortDir = (strtolower($val) === 'asc') ? SORT_ASC : SORT_DESC;
					break;
				}
			}
			
			foreach ($result as $key => $row) {
					$sort[$key]  = $row[$sortField];
			}
			
			array_multisort($sort, $sortDir, $result);			
		}
		
		return array_slice($result, ($page-1) * $limit, $limit);
		
	}		
		
		
	public function resetDownloadStatus($date_from = '', $date_to = '', $failedOnly = true) {
		
		$fields = array(
			'EmdeonLabResult.extracted' => 0,
			'EmdeonLabResult.downloaded' => 0,
		);
		
		$conditions = array();
		
		if ($failedOnly === true) {
			$conditions['EmdeonLabResult.extracted'] = 2;
			$conditions['EmdeonLabResult.downloaded'] = 2;
		}
		
		$date_from = strtotime($date_from);
		$date_to = strtotime($date_to);
		if ($date_from && $date_to) {
			$conditions['EmdeonLabResult.report_service_date BETWEEN ? AND ?'] = array(
				__date('Y-m-d 00:00::00', $date_from),
				__date('Y-m-d 23:59:59', $date_to)
			);
		}
		
		$this->updateAll($fields, $conditions);
		
		
		
	}
  
  public function pruneEmdeonOrderTest() {

    echo "\nTrimming empty Emdeon Order Test...";
    // Delete empty EmdeonOrderTests
    
    $emptyRecords = $this->EmdeonOrder->EmdeonOrderTest->find('all', array(
        'conditions' => array(
          'EmdeonOrderTest.ordertest' => '',
          'EmdeonOrderTest.modified_timestamp <' => __date('Y-m-d', strtotime('-2 months')),
        ),
        'fields' => array(
            'EmdeonOrderTest.order_test_id'
        ),
        'limit' => 100,
    ));
    
    $orderTestIds = Set::extract('/EmdeonOrderTest/order_test_id', $emptyRecords);
    
    if ($orderTestIds) {
      $this->EmdeonOrder->EmdeonOrderTest->deleteAll(array(
          'EmdeonOrderTest.order_test_id' => $orderTestIds,
      ), true);    
    }
    
    echo " done.\n";
    
    
    echo "\nTrimming Emdeon Order Test with final lab results...";
    
    $this->unbindModelAll();
    $labs = $this->find('all', array(
        'conditions' => array(
            'EmdeonLabResult.status' => 'Final',
            'EmdeonLabResult.report_service_date <' => __date('Y-m-d', strtotime('-2 months')),
        ),
        'joins' => array(
          array(
            'table' => 'emdeon_order_tests',
            'alias' => 'EmdeonOrderTest',
            'type' => 'INNER',
            'conditions' => array(
              'EmdeonLabResult.order_id = EmdeonOrderTest.order_id',
            )
          ),
        ),
        'fields' => array(
            'EmdeonOrderTest.order_id', 'EmdeonLabResult.status', 'EmdeonOrderTest.order_test_id'
        ),
        'group' => array(
            'EmdeonLabResult.order_id'
        ),
        'limit' => 100,
    ));

    
    $orderIds = array();
    
    foreach ($labs as $l) {
      if (!$l['EmdeonOrderTest']['order_id']) {
        continue;
      }
      
      $orderIds[] = $l['EmdeonOrderTest']['order_id'];
      
    }

    if ($orderIds) {

      $this->EmdeonOrder->EmdeonOrderTest->deleteAll(array(
          'EmdeonOrderTest.order_id' => $orderIds,
      ), true);
    }
    
    echo " done.\n";
    
  }
  
		
}

?>
