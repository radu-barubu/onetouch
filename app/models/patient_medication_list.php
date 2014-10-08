<?php

class PatientMedicationList extends AppModel 
{
	public $name = 'PatientMedicationList'; 
	public $primaryKey = 'medication_list_id';
	public $useTable = 'patient_medication_list';
	
	public $actsAs = array(
		'Auditable' => 'Medical Information - Medication List',
		'Unique' => array('patient_id', 'medication'),
		'Containable'
	);
    
    public $hasMany = array(
		'PatientMedicationRefill' => array(
			'className' => 'PatientMedicationRefill',
			'foreignKey' => 'medication_list_id'
		)
	);
		
	public $paginate = array(
        'PatientMedicationList' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array('PatientMedicationList.modified_timestamp' => 'DESC')
        )
    );
    
    
	public function beforeSave($options)
	{
		$this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
		@$this->data['PatientMedicationList']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function afterSave($created)
	{
		
		App::import('Model', 'Order');
		$order = new Order();
		$this->bindModel(array(
			'belongsTo' => array(
					'PatientDemographic' => array(
						'className' => 'PatientDemographic',
						'foreignKey' => 'patient_id'
					),
					'UserAccount' => array(
						'className' => 'UserAccount',
						'foreignKey' => 'modified_user_id'
					),				
				),
		));
		
		$medicationListId = ($this->id) ? $this->id : $this->data['PatientMedicationList']['medication_list_id'];
		$medicationList = $this->find('first', array(
			'conditions' => array(
				'PatientMedicationList.medication_list_id' => $medicationListId,
			),
			// Do not specify fields!
			// Took me quite a few hours to realize that, for some reason,
			// encrypted fields from the related PatientDemographic table
			// do not get included when you limit fields 
			/*
			'fields' => array(
				'medication_list_id', 'patient_id', 'modified_user_id', 'medication',
				'source', 'status', 'created_timestamp', 'modified_timestamp'
			),
			 */			
			'contain' => array(
					'PatientDemographic' => array(
						'fields' => array('first_name', 'last_name'),
					),
					'UserAccount' => array(
						'fields' => array('firstname', 'lastname'),
					),				
			),
	
		));		
		
		// If patient reported, do not add to encounter_orders table
		if ($medicationList['PatientMedicationList']['source'] == 'Patient Reported') {
			return true;
		}
		
		// If imported from surescripts, do not add to encounter_orders table
		if ($medicationList['PatientMedicationList']['source'] == 'Surescripts History') {
			return true;
		}
    
		$data = array('Order' => array(
			'data_id' => $medicationList['PatientMedicationList']['medication_list_id'],
			'encounter_id' => 0,
			'patient_id' => $medicationList['PatientMedicationList']['patient_id'],
			'encounter_status' => '',
			'test_name' => $medicationList['PatientMedicationList']['medication'],
			'source' => 'e-Prescribing',
			'patient_firstname' => $medicationList['PatientDemographic']['first_name'],
			'patient_lastname' => $medicationList['PatientDemographic']['last_name'],
			'provider_name' => $medicationList['UserAccount']['firstname'] . ' ' . $medicationList['UserAccount']['lastname'],
			'priority' => '',
			'order_type' => ($medicationList['PatientMedicationList']['source'] == 'e-Prescribing History') ? 'e-Rx' : 'Rx',
			'status' => $medicationList['PatientMedicationList']['status'],
			'item_type' => 'plan_rx_electronic',
			'date_performed' => $medicationList['PatientMedicationList']['start_date'],
			'date_ordered' => $medicationList['PatientMedicationList']['start_date'],
			'modified_timestamp' => $medicationList['PatientMedicationList']['modified_timestamp'],
		));					
		
		if($created) {
			$order->create();
			$order->save($data);			
			
			$data = array();
			$data['PatientMedicationList']['medication_list_id'] = $this->id;
			$data['PatientMedicationList']['created_timestamp'] = __date("Y-m-d H:i:s");
			$this->save($data);
			
		} else {
			$current = $order->find('first', array(
				'conditions' => array(
					'Order.item_type' => 'plan_rx_electronic',
					'Order.data_id' => $medicationListId,
				),
			));

			if ($current) {
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
									'Order.item_type' => 'plan_rx_electronic',
									'Order.data_id' => $this->id,
								),
							));
							
							if ($current) {
								$order->delete($current['Order']['encounter_order_id']);
							}								
				}	
	
	public function getCookedMeds( $patient_id )
	{


			$data = $this->find('all', array(
					'conditions' => array('PatientMedicationList.patient_id' => $patient_id)
				)
			);

		
		
		if(!$data) {
			return ;
		}
		
		$new_data = array();
		
		$i = 0;
		foreach( $data as $k => $v) {
			
			$v =$v['PatientMedicationList'];
			
			if(($i < 10)) {
				
				$new_data['10'][] = $v;
				
			} else if ($i < 20) {
				
				$new_data['20'][] = $v;
			} else if ($i < 30) {
				
				$new_data['30'][] = $v;
			} else if ($i < 40) {
				
				$new_data['40'][] = $v;
			} else if ($i < 50) {
				
				$new_data['50'][] = $v;
			}
			
			$i++;
		}
		return $new_data;
	}
	
	/**
    * Retrieve list of Doespot Medications by patient and encounter
    * @param int $patient_id Patient ID
	* @param int $encounter_date Encounter Date
    * @return array Array of Medications
    */
	
	public function GetDosespotMedication($encounter_date, $patient_id,$encounter_id="")
	{
		$this->belongsTo = array();
		$search_result = array();
		/* no one will void an encounter that actually ordered medicine		
		App::import('Model', 'EncounterMaster');

		$em = new EncounterMaster();

		$em->recursive = -1;

		$voided = $em->find('all', array(
			'conditions' => array(
				'EncounterMaster.patient_id' => $patient_id,
				'EncounterMaster.encounter_status' => 'Voided',
			),
		));

		$voidedIds = array();

		if ($voided) {
			$voidedIds = Set::extract('/EncounterMaster/encounter_id', $voided);
		}		
		
		if (empty($voidedIds)) {
			$search_result = $this->find( 'all', array(
						'conditions' => array('PatientMedicationList.patient_id' => $patient_id, 
							'PatientMedicationList.start_date' => $encounter_date, 'PatientMedicationList.dosespot_medication_id !=' => 0)
					)
			);
		} else {
			$search_result = $this->find( 'all', array(
						'conditions' => array(
							'PatientMedicationList.patient_id' => $patient_id, 
							'PatientMedicationList.start_date' => $encounter_date, 
							'PatientMedicationList.dosespot_medication_id !=' => 0,
							'NOT' => array(
								'PatientMedicationList.encounter_id' => $voidedIds, 
							)
						)
					)
			);
			
		}
		*/

                   //now we can associate encounter ID with dosespot, so see if we have records
                   if($encounter_id)
                   {
                           $search_result = $this->find( 'all', array(
                                       'conditions' => array('PatientMedicationList.patient_id' => $patient_id,
                                                                 'PatientMedicationList.encounter_id' => $encounter_id,
                                                                  'PatientMedicationList.dosespot_medication_id !=' => 0)
                                        ));
                   }

                        if(!$search_result) //if no results found above
                        {       //less desirable search, but at least something

  					$search_result = $this->find( 'all', array(
                                                'conditions' => array('PatientMedicationList.patient_id' => $patient_id,
                                                        'PatientMedicationList.start_date' => $encounter_date, 'PatientMedicationList.dosespot_medication_id !=' => 0)
                                        ) );
			}


		
		
		/*foreach($search_result as $results)
		{
		    if($results['PatientMedicationList']['encounter_id'] == 0)
			{
		
			$results['PatientMedicationList']['patient_id'] = $patient_id;
			$results['PatientMedicationList']['encounter_id'] = $encounter_id;
			$this->save($results);
			}
		}*/
		
		$new = array();
		
		if (!$search_result) {
			return $new;
		}
		
		foreach($search_result as  $k => $v) {
			$v = $v['PatientMedicationList'];
			
			$new[$v['medication']] = $v;
		}
		
		return $new;
	}
	
        
        public function getPreviousMedications($patient_id, $encounter_id, $active_only = false) {
            
				
					
            $encounter_id = intval($encounter_id);
            
            // Get meds for this patient
            // from previous encounters INCLUDING the given encounter
            $conditions = array(
                'PatientMedicationList.patient_id' => $patient_id,
                'PatientMedicationList.encounter_id <=' => $encounter_id,
            );
            
						
						
            $data = $this->find('all', array(
                            'conditions' => $conditions
                    )
            );

            if(!$data) {
                    return array();
            }

            
            // Filter results
            $filtered = array();

            foreach ($data as $d) {
				
				if($active_only && $d['PatientMedicationList']['status'] != 'Active')
				{
					continue;
				}
                
                // "patient reported" meds should be printed in visit summary if < OR = encounter ID
                if ($d['PatientMedicationList']['source'] == 'Patient Reported') {
                    $filtered[] = $d;
                    continue;
                }
                
                //"practice prescribed" or "e-prescribed" meds query should be if < encounter ID
                if (intval($d['PatientMedicationList']['encounter_id']) < intval($encounter_id)) {
                    $filtered[] = $d;
                }
            }
            
            // One liner to split medication lists
            // into 10 sets each ^__^ - rolan
            return array_chunk($filtered, 10);
                
        }
        
	
	public function getActiveMedications($patient_id)
	{


			$patientmedication_items = $this->find('all', array('conditions' => array('AND' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.status' => 'Active'))));
		
		
		return $patientmedication_items;
	}
	
	public function addMedication($medication, $frequency, $rxnorm, $medication_form, $medication_strength_value, $medication_strength_unit,  $medication_id, $encounter_id, $patient_id)
	{
		//search duplicate
		$item = $this->find('first', array(
			'conditions' => array(
				'PatientMedicationList.patient_id' => $patient_id, 
				'PatientMedicationList.medication' => ucwords(strtolower($medication)),
				'PatientMedicationList.status' => 'Active'
			)
		));
		
		if(!$item)
		{
			$data = array();
			
			$data['PatientMedicationList']['patient_id'] = $patient_id;
			$data['PatientMedicationList']['encounter_id'] = $encounter_id;
			$data['PatientMedicationList']['medication'] = ucwords(strtolower($medication));
			$data['PatientMedicationList']['frequency'] = $frequency;
			$data['PatientMedicationList']['rxnorm'] = $rxnorm;
			$data['PatientMedicationList']['medication_type'] = 'Standard';
			$data['PatientMedicationList']['medication_form'] = $medication_form;
			$data['PatientMedicationList']['medication_strength_value'] = $medication_strength_value;
			$data['PatientMedicationList']['medication_strength_unit'] = $medication_strength_unit;
			$data['PatientMedicationList']['source'] = 'Patient Reported';
			$data['PatientMedicationList']['start_date'] = '';
			$data['PatientMedicationList']['end_date'] = '';
			$data['PatientMedicationList']['status'] = 'Active';
			$this->create();
			$this->save($data);
			
			ClassRegistry::init('MedsList')->updateCitationCount($medication_id);
		}
	}
	
	public function MedicationStatusCheck()
	{
	    $this->PracticeSetting = ClassRegistry::init('PracticeSetting');
		
		$PracticeSetting = $this->PracticeSetting->find('first');

		$items = $this->find('all');

	    foreach($items as $item)
		{			
			$data = array();
			
			$currentdate = strtotime(date('Y-m-d'));			
			
			$data['PatientMedicationList']['medication_list_id'] = $item['PatientMedicationList']['medication_list_id'];
			$item_end_date = __date('Y-m-d', strtotime(str_replace("-", "/", $item['PatientMedicationList']['end_date'])));
			
			if($item_end_date != '0000-00-00' && $item_end_date != '' && $currentdate >= strtotime($item_end_date) && $item['PatientMedicationList']['status'] != 'Completed')
			{
			  $data['PatientMedicationList']['status'] = 'Completed';
				
				$this->id = $data['PatientMedicationList']['medication_list_id'];
				$this->saveField('status', 'Completed');
				
				$this->id = $data['PatientMedicationList']['medication_list_id'];
				
				
			}		
		}
	}
	
	
	public function getAllMedications($patient_id, $medication_show_option)
	{
	    $source_array = array();
		$source_array[] = '';
		if($medication_show_option[1] == 'yes')
		{
			   $source_array[] = 'e-Prescribing History';
		}
		if($medication_show_option[2] == 'yes')
		{
			   $source_array[] = 'Patient Reported';
		}
		if($medication_show_option[3] == 'yes')
		{
			   $source_array[] = 'Practice Prescribed';
		}
		
		$medications = array();

	
		
		if ($medication_show_option[0] == 'no')
		{

				$medications = $this->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.status' => 'Active', 'PatientMedicationList.source' => $source_array, 'PatientMedicationList.source !=' => '')));

			
	
		}
		else
		{

				$medications = $this->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.source' => $source_array, 'PatientMedicationList.source !=' => '')));

			

		}
		
		$all_status = array("Active" => 1, "Inactive" => 2, "Cancelled" => 3, "Discontinued" => 4, "Completed" => 5, "" => 6);
		
		for($i = 0; $i < count($medications); $i++)
		{
		   $medications[$i]['PatientMedicationList']['status_int'] = $all_status[ucwords(strtolower($medications[$i]['PatientMedicationList']['status']))];
	
		}

		$medications = Set::sort($medications, "{n}.PatientMedicationList.status_int", "asc");
		
		return $medications;
	}
	
	public function approveRefill($refill_id)
	{
		$user_id = $_SESSION['UserAccount']['user_id'];
		$refill = $this->PatientMedicationRefill->find('first', array('conditions' => array('PatientMedicationRefill.refill_id' => $refill_id)));
		
		if($refill)
		{
			if($refill['PatientMedicationRefill']['refill_status'] == 'Requested')
			{
				$medication_list_id = $refill['PatientMedicationRefill']['medication_list_id'];
				$medication = $this->find('first', array('conditions' => array('PatientMedicationList.medication_list_id' => $medication_list_id)));
				
				if($medication)
				{
					$refill_count = (int)$medication['PatientMedicationList']['refill_allowed'];
					
					if($refill_count > 0)
					{
						$refill_count--;
					}
					
					$medication['PatientMedicationList']['refill_allowed'] = $refill_count;
					$this->saveAudit('Update');
            		$this->save($medication);
				}
				
				$refill['PatientMedicationRefill']['refill_status'] = 'Approved';
				$data['PatientMedicationRefill']['refill_request_date'] = __date("Y-m-d");
				$refill['PatientMedicationRefill']['refilled_by'] = $user_id;
				$this->PatientMedicationRefill->save($refill);
				$this->PatientMedicationRefill->saveAudit('Update');
			}
		}
	}
    
    public function refill($medication_list_id)
    {
		$user_id = $_SESSION['UserAccount']['user_id'];
		$role_id = $_SESSION['UserAccount']['role_id'];
		
        $this->recursive = -1;
        $medication = $this->find('first', array('conditions' => array('PatientMedicationList.medication_list_id' => $medication_list_id)));
        
        if($medication)
        {
            $refill_count = (int)$medication['PatientMedicationList']['refill_allowed'];
            
            if($refill_count > 0)
            {
				if($role_id == EMR_Roles::PHYSICIAN_ROLE_ID)
				{
                	$refill_count--;
				}
            }
            
            $medication['PatientMedicationList']['refill_allowed'] = $refill_count;
            $this->save($medication);
            
            $data = array();
            $data['PatientMedicationRefill'] = $medication['PatientMedicationList'];
            
			if($role_id == EMR_Roles::PHYSICIAN_ROLE_ID)
			{
				$data['PatientMedicationRefill']['refill_status'] = 'Approved';
				$data['PatientMedicationRefill']['refilled_by'] = $user_id;
				$data['PatientMedicationRefill']['provider_id'] = $user_id;
			}
			else
			{
				$data['PatientMedicationRefill']['refill_status'] = 'Requested';
				$data['PatientMedicationRefill']['requested_by'] = $user_id;
				$data['PatientMedicationRefill']['provider_id'] = 0;
			}
			
			$data['PatientMedicationRefill']['refill_request_date'] = __date("Y-m-d");
            
            $this->PatientMedicationRefill->create();
            $this->PatientMedicationRefill->save($data);
            $this->PatientMedicationRefill->saveAudit('New');
            
            return $refill_count;
        }
        else
        {
            return 0;
        }
    }
	
	public function setItemValue($field, $value, $medication_list_id, $patient_id, $user_id)
	{
		$search_result = $this->find('first', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.medication_list_id' =>$medication_list_id)));
		
		if(!empty($search_result))
		{
			$data = array();
			$data['PatientMedicationList']['medication_list_id'] = $search_result['PatientMedicationList']['medication_list_id'];		
			$data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['PatientMedicationList']['modified_user_id'] = $user_id;
			$data['PatientMedicationList'][$field] = $value;
			$this->save($data);
		}
	}
	
	public function setItemValueByPlanRxId($field, $value, $plan_rx_id, $patient_id, $user_id)
	{
		$search_result = $this->find('first', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.plan_rx_id' =>$plan_rx_id)));
		
		if(!empty($search_result))
		{
			$data = array();
			$data['PatientMedicationList']['medication_list_id'] = $search_result['PatientMedicationList']['medication_list_id'];		
			$data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['PatientMedicationList']['modified_user_id'] = $user_id;
			$data['PatientMedicationList'][$field] = $value;
			$this->save($data);
		}
	}
	
	public function executeData(&$controller, $patient_id, $medication_list_id, $task, $user_id)
	{
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if (($controller->data['submitted']['id'] == 'start_date') or ($controller->data['submitted']['id'] == 'end_date'))
                    {
                        $controller->data['submitted']['value'] = __date("Y-m-d", strtotime($controller->data['submitted']['value']));
                    }
                    $this->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $medication_list_id, $patient_id, $user_id);
                    
                    // Check to autofill the end date
                    if( $controller->data['submitted']['id'] == "status" &&
                    		( $controller->data['submitted']['value'] == 'Inactive' ||
                    	  	$controller->data['submitted']['value'] == 'Cancelled' ||
                    	  	$controller->data['submitted']['value'] == 'Discontinued' ||
                    	  	$controller->data['submitted']['value'] == 'Completed' ) ){
                    	// Autofill if not already set
											$existing = $this->find('first',
												array('conditions' => array('PatientMedicationList.medication_list_id' => $medication_list_id)));
											if( isset($existing) &&
													( !isset($existing['PatientMedicationList']['end_date']) ||
														$existing['PatientMedicationList']['end_date'] == '0000-00-00' ) ){
												$endDate = __date("Y-m-d");
												$info = array("PatientMedicationList::executeData - set end_date", $endDate);
                    	}
                    }
                }
                exit;
            }
            break;
            default:
            {
                $items = $this->find('first', array('conditions' => array('PatientMedicationList.medication_list_id' => $medication_list_id)));
                
                $controller->set('EditItem', $controller->sanitizeHTML($items));
            }
        }
	}
	
    /**
    * Remove the dosespot data from the database.If removed in dosespot.
    * @param int $patient_id Patient ID
    * @param bool $send_previous Determine whether use previous previous fetched dosespot data or not
    * @param array $dosespot_meds dosespot previous data (if $send_previous = true)
    * @return null
    */
    public function removeDosespotDeletedData($patient_id, $send_previous = false, $dosespot_meds = array(),$surescripts_hx=false)
    {
        if($send_previous)
        {
            $dosespot_medication_items = $dosespot_meds;
        }
        else
        {
            $this->PatientDemographic = ClassRegistry::init('PatientDemographic');
            $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
                    
           if($dosespot_patient_id && is_numeric($dosespot_patient_id) ) { 
            $dosespot_xml_api = new Dosespot_XML_API();
            $dosespot_medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id,$surescripts_hx);
	   }
        }
        
        $dosespot_medication_ids = array();
        $dosespot_medication_ids[] = 0;
        if(count($dosespot_medication_items) > 0)
	{
          foreach($dosespot_medication_items as $medication_item)
          {
            $dosespot_medication_ids[] = $medication_item['MedicationId'];
          }
        
          $conditions = array();
          $conditions['PatientMedicationList.patient_id'] = $patient_id;
        
          if(count($dosespot_medication_ids) == 1)
          {
            $conditions['PatientMedicationList.dosespot_medication_id != '] = $dosespot_medication_ids[0];
          }
          else
          {
            $conditions['PatientMedicationList.dosespot_medication_id NOT '] = $dosespot_medication_ids;
          }

	  if($surescripts_hx)
	  {
		$conditions['PatientMedicationList.medication_type'] = "surescripts_history";
	  }
	  else
	  {
		$conditions['PatientMedicationList.medication_type !='] = "surescripts_history";
	  }

          $this->deleteAll($conditions, true, true);
	}
    }
	
	public function removeEmdeonDeletedData($patient_id, $send_previous = false, $emdeon_meds = array())
    {
        if($send_previous)
        {
            $emdeon_medication_items = $emdeon_meds;
        }
        else
        {
		    $this->PatientDemographic = ClassRegistry::init('PatientDemographic');
            $patient = $this->PatientDemographic->getPatient($patient_id);
            $mrn = $patient['mrn'];
            $emdeon_xml_api = new Emdeon_XML_API();
			$person = $emdeon_xml_api->getPersonByMRN($mrn);
            $emdeon_medication_items = $this->sanitizeHTML($this->paginate('EmdeonPrescription', array('EmdeonPrescription.person' => $person)));
        }
        
        $emdeon_medication_ids = array();
        $emdeon_medication_ids[] = 0;
        
        foreach($emdeon_medication_items as $medication_item)
        {
            $emdeon_medication_ids[] = $medication_item['EmdeonPrescription']['prescription_id'];
        }
        
        $conditions = array();
        $conditions['PatientMedicationList.patient_id'] = $patient_id;
        
        if(count($emdeon_medication_ids) == 1)
        {
            $conditions['PatientMedicationList.emdeon_medication_id != '] = $emdeon_medication_ids[0];
        }
        else
        {
            $conditions['PatientMedicationList.emdeon_medication_id NOT '] = $emdeon_medication_ids;
        }
        
        $this->deleteAll($conditions, true, true);
    }
}

?>
