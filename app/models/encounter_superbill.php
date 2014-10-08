<?php


class EncounterSuperbill extends AppModel 
{
    public $name = 'EncounterSuperbill';
    public $primaryKey = 'superbill_id';
    public $useTable = 'encounter_superbill';
	
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
    
    public function beforeSave($options)
    {
        $this->data['EncounterSuperbill']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['EncounterSuperbill']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
        return true;
    }
    
    public function searchItem($encounter_id)
    {
        return $search_result = $this->find('first', array('conditions' => array('EncounterSuperbill.encounter_id' => $encounter_id)));
    }
    
    public function getItem($encounter_id)
    {
        $item = $this->searchItem($encounter_id);
        
        if($item)
        {
            $item['EncounterSuperbill']['ignored_diagnosis'] = json_decode($item['EncounterSuperbill']['ignored_diagnosis'], true);
            $item['EncounterSuperbill']['ignored_in_house_labs'] = json_decode($item['EncounterSuperbill']['ignored_in_house_labs'], true);
            $item['EncounterSuperbill']['ignored_in_house_radiologies'] = json_decode($item['EncounterSuperbill']['ignored_in_house_radiologies'], true);
            $item['EncounterSuperbill']['ignored_in_house_procedures'] = json_decode($item['EncounterSuperbill']['ignored_in_house_procedures'], true);
			$item['EncounterSuperbill']['ignored_in_house_immunizations'] = json_decode($item['EncounterSuperbill']['ignored_in_house_immunizations'], true);
			$item['EncounterSuperbill']['ignored_in_house_meds'] = json_decode($item['EncounterSuperbill']['ignored_in_house_meds'], true);
			$item['EncounterSuperbill']['ignored_in_house_injections'] = json_decode($item['EncounterSuperbill']['ignored_in_house_injections'], true);
			$item['EncounterSuperbill']['ignored_in_house_supplies'] = json_decode($item['EncounterSuperbill']['ignored_in_house_supplies'], true);
			$item['EncounterSuperbill']['ignored_outside_labs'] = json_decode($item['EncounterSuperbill']['ignored_outside_labs'], true);
			$item['EncounterSuperbill']['ignored_radiologies'] = json_decode($item['EncounterSuperbill']['ignored_radiologies'], true);
			$item['EncounterSuperbill']['ignored_procedures'] = json_decode($item['EncounterSuperbill']['ignored_procedures'], true);
			$item['EncounterSuperbill']['service_level_advanced'] = json_decode($item['EncounterSuperbill']['service_level_advanced'], true);
			$item['EncounterSuperbill']['other_codes'] = json_decode($item['EncounterSuperbill']['other_codes'], true);


            return $item;
        }
        else
        {
            $data = array();
            $data['EncounterSuperbill']['ignored_diagnosis'] = json_encode(array());
            $data['EncounterSuperbill']['ignored_in_house_labs'] = json_encode(array());
            $data['EncounterSuperbill']['ignored_in_house_radiologies'] = json_encode(array());
            $data['EncounterSuperbill']['ignored_in_house_procedures'] = json_encode(array());
			$data['EncounterSuperbill']['ignored_in_house_immunizations'] = json_encode(array());
			$data['EncounterSuperbill']['ignored_in_house_meds'] = json_encode(array());
			$data['EncounterSuperbill']['ignored_in_house_supplies'] = json_encode(array());
			$data['EncounterSuperbill']['ignored_in_house_injections'] = json_encode(array());	 
			$data['EncounterSuperbill']['ignored_outside_labs'] = json_encode(array());
			$data['EncounterSuperbill']['ignored_radiologies'] = json_encode(array());
			$data['EncounterSuperbill']['ignored_procedures'] = json_encode(array());
			$data['EncounterSuperbill']['service_level_advanced'] = json_encode(array());

			$data['EncounterSuperbill']['other_codes'] = json_encode(array());
            $data['EncounterSuperbill']['encounter_id'] = $encounter_id;
            $data['EncounterSuperbill']['time_created'] = __date("Y-m-d");
            $this->save($data);
            
            return $this->getItem($encounter_id);
        }
    }
    
    public function getSingleItem($encounter_id, $field)
    {
        $item = $this->getItem($encounter_id);
        return $item['EncounterSuperbill'][$field];
    }
    
    public function setSingleItem($encounter_id, $field, $value)
    {
        $item = $this->getItem($encounter_id);
        
        $data = array();
        $data['EncounterSuperbill']['superbill_id'] = $item['EncounterSuperbill']['superbill_id'];
        $data['EncounterSuperbill'][$field] = $value;
        $this->save($data);
    }

    public function addIgnoredItem($encounter_id, $ignored_field, $ignored_value)
    {
        $value = $this->getSingleItem($encounter_id, $ignored_field);
        $value[] = $ignored_value;
        array_unique($value);
        
        $this->setSingleItem($encounter_id, $ignored_field, json_encode($value));
    }
    
    public function deleteIgnoredItem($encounter_id, $ignored_field, $ignored_value)
    {
        $value = $this->getSingleItem($encounter_id, $ignored_field);
        $value = array_diff($value, array($ignored_value));
        $value = array_values($value);
        
        $this->setSingleItem($encounter_id, $ignored_field, json_encode($value));
    }

	public function billingTasks(&$controller,$PracticeSetting,$encounter_id,$patient_id) 
	{
		$this->kareoErr="";
		if($PracticeSetting['PracticeSetting']['kareo_status']) // if kareo
		{
			  $controller->loadModel('kareo');
			  //if we are to wait for kareo's response
			  if($PracticeSetting['PracticeSetting']['kareo_encounter_lock']) { 
				$response = $controller->kareo->bill($patient_id, '', $encounter_id);
				if($response) {
					$this->kareoErr= json_encode(array('kareo_error' => $response));
				}
			  } else {
			   //push into background
			   $controller->kareo->exportBillToKareo($patient_id, $encounter_id);
			  }
                }
		if($PracticeSetting['PracticeSetting']['xlink_status']) //if xlink enabled
		{
			$controller->loadModel('xlink');
			$xlinkCon = $controller->xlink->connectXlink();
			if($xlinkCon)
			    $controller->xlink->bill($patient_id, '', $encounter_id);
		}					
	
	}
	
	private function postHL7($PracticeSetting,$encounter_id)
	{
		if($PracticeSetting['PracticeSetting']['hl7_engine'] && $PracticeSetting['PracticeSetting']['hl7_engine'] != 'MacPractice' ) // if HL7
               	{
			$db_config = $this->getDataSource()->config;
			$shellcommand = "php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app \"".APP."\" hl7_produce ".$db_config['database']." -receiver \"".$PracticeSetting['PracticeSetting']['hl7_receiver']."\" -files \"/CUSTOMER_DATA/HL7_CUSTOMERS/".$PracticeSetting['PracticeSetting']['hl7_customer_name']."/outgoing\" -count 1 -encounters ".$encounter_id." >> /dev/null 2>&1 & ";
			exec($shellcommand);
		}
	}
	
	public function execute(&$controller, $encounter_id, $patient_id, $task, $user_id, $phone ,$view)
	{
		switch ($task)
        {
			
						case 'void': {
							$user = $controller->Session->read('UserAccount');

							if (isset($controller->params['form']['void'])) {
								$controller->EncounterMaster->setItemValue('encounter_status', 'Voided', $encounter_id, $patient_id, $user_id);
                $controller->loadModel('Order');
                $controller->Order->updateOrderEncounterStatus($encounter_id, 'Voided');
							}
							
						} break;
			
            case 'unlock': {
                $user = $controller->Session->read('UserAccount');

                $controller->loadModel("UserGroup");
				$unlock_roles_ids = $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_UNLOCK);

                if(in_array(intval($user['role_id']), $unlock_roles_ids) || $controller->name == 'Api') 
								{
                    $controller->EncounterMaster->setItemValue('encounter_status', 'Open', $encounter_id, $patient_id, $user_id);
                    $controller->loadModel('Order');
                    $controller->Order->updateOrderEncounterStatus($encounter_id, 'Open');
                    
                    
                    $controller->EncounterMaster->setItemValue('encounter_elapsed_time', '00:00:00', $encounter_id, $patient_id, $user_id);
                    $controller->EncounterMaster->setItemValue( 'hl7_status', '0', $encounter_id, $patient_id, $user_id );
                }
                
                if ($controller->name == 'Api') {
                  return true;
                }
                
                if($phone != 'yes')
				{
					$controller->redirect(array(
						'controller' => 'encounters',
						'action' => 'index',
						'task' => 'edit',
						'encounter_id' => $encounter_id,
					));
				}
				else
				{
					$controller->redirect(array(
						'controller' => 'encounters',
						'action' => 'index',
						'task' => 'edit',
						'encounter_id' => $encounter_id,
						'phone' => $phone,
					));
				
				}
                
                die();
            }
            break;
			case 'save_other_codes': {
        
				$item = $controller->EncounterSuperbill->getItem($encounter_id);
				$value = json_encode($controller->data['other_codes']);
				$data['EncounterSuperbill']['superbill_id'] = $item['EncounterSuperbill']['superbill_id'];
				$data['EncounterSuperbill']['other_codes'] = $value;
				$controller->EncounterSuperbill->save($data);
                exit;
            }
            break;

		case 'lockit':
          	{
            	}
	   	case "lock_only":
		{
	    	} 
            case "close_record":
            {
               // $controller->loadModel("Superbill");  not sure what this is used for any longer? 
                $controller->loadModel("UserAccount");

                $db_pin = $controller->UserAccount->getPin($user_id);
                
                $pin = $controller->data['pin'];
				
                if ($controller->name == 'Api') {
                  $pin = $db_pin = true;
                }
                
                
                if (!$pin)
                {
                    $response['error'] = "Enter your Pin Number";
                }
                elseif ($pin != $db_pin)
                {
                    $response['error'] = "Invalid Pin Number";
                }
                else
                {
                	$patient_status = $controller->PatientDemographic->getPatientStatus($patient_id);
                	if (($patient_status == 'New') or ($patient_status == 'Inactive'))
                	{
                    		$controller->data['PatientDemographic']['patient_id'] = $patient_id;
                    		$controller->data['PatientDemographic']['status'] = 'Active';
                    		$controller->PatientDemographic->save($controller->data);
                	}
                	$controller->layout = 'empty';

					//set Supervising Provider if present
                                        if(isset($controller->data['EncounterSuperbill']['supervising_provider_id']))
                                            $controller->EncounterSuperbill->setSingleItem($encounter_id, 'supervising_provider_id', $controller->data['EncounterSuperbill']['supervising_provider_id']);

					if($task != 'lock_only')
					{
						$PracticeSetting=$controller->Session->read('PracticeSetting');
		    			$this->billingTasks(&$controller,$PracticeSetting,$encounter_id,$patient_id);
	                	if($this->kareoErr) {
	                		echo $this->kareoErr;
	                		exit;
	                	}
					}
          if (!isset($_SESSION['api'])) {
            Visit_Summary::createSnapShot($encounter_id);
          }
					
					if ($encounter_id) {
						$controller->EncounterMaster->unbindModelAll ();
						// $controller->EncounterMaster->id = $encounter_id;
						// $encounter = $controller->EncounterMaster->read();
						$encounter = $controller->EncounterMaster->find ( 'first', array (
								'conditions' => array (	'EncounterMaster.encounter_id' => $encounter_id ),
								'recursive' => - 1 
						));
						if ($encounter ['EncounterMaster'] ['encounter_begin_timestamp']) {
							$encounter_time_end = time ();
							$encounter_time_start = strtotime ( $encounter ['EncounterMaster'] ['encounter_begin_timestamp'] );
							$elapsed = $encounter_time_end - $encounter_time_start;
							$hours = floor ( $elapsed / 3600 );
							$minutes = floor ( ($elapsed - ($hours * 3600)) / 60 );
							$seconds = $elapsed - ($hours * 3600) - ($minutes * 60);
							$ts = str_pad ( $hours, 2, '0', STR_PAD_LEFT ) . ':' . str_pad ( $minutes, 2, '0', STR_PAD_LEFT ) . ':' . str_pad ( $seconds, 2, '0', STR_PAD_LEFT );
							$controller->EncounterMaster->saveField ( 'encounter_elapsed_time', $ts );
						}
					}
					
					// if lock_only, set hl7_status to 'NotBillable', a.k.a. '2' -- this will cause HL7 lib to ignore this encounter and not send a DFT message
					if( $task == 'lock_only' )
						$controller->EncounterMaster->setItemValue( 'hl7_status', '2', $encounter_id, $patient_id, $user_id );
					else
						$controller->EncounterMaster->setItemValue( 'hl7_status', '0', $encounter_id, $patient_id, $user_id );
						
					// Close the encounter
					$controller->EncounterMaster->setItemValue ( 'encounter_status', 'Closed', $encounter_id, $patient_id, $user_id );

					//add ICD version
					$PracticeSetting=$controller->Session->read('PracticeSetting');
					$controller->EncounterMaster->setItemValue( 'icd_version', $PracticeSetting['PracticeSetting']['icd_version'], $encounter_id, $patient_id, $user_id );

					// update Order table
					$controller->loadModel('Order');
					$controller->Order->updateOrderEncounterStatus($encounter_id, 'Closed');
					
					//define response to end-user
					$response ['msg'] = "Encounter has been locked.";
							
					// see if PCP is defined. if not, update field with this provider
					$controller->loadModel ( "PatientPreference" );
					$controller->PatientPreference->recursive = - 1;
					$items = $controller->PatientPreference->find ( 'first', array (
							'conditions' => array( 'PatientPreference.patient_id' => $patient_id ) 
					));
					if (empty ( $items ['PatientPreference'] ['pcp'] )) {
						$isprovider = ($_SESSION ['UserAccount'] ['role_id'] == EMR_Roles::PHYSICIAN_ROLE_ID) ? true : false;
						if ($isprovider) {
							$controller->data2 ['PatientPreference'] ['patient_id'] = $patient_id;
							$controller->data2 ['PatientPreference'] ['preference_id'] = $items ['PatientPreference'] ['preference_id'];
							$controller->data2 ['PatientPreference'] ['pcp'] = $_SESSION ['UserAccount'] ['user_id'];
							$controller->PatientPreference->save ( $controller->data2 );
						}
					}
					//post HL7 DFT if necessary
					$this->postHL7($PracticeSetting,$encounter_id);
				} 
        
                if ($controller->name == 'Api') {
                  return true;
                }
        
                echo json_encode($response);
                exit;
            }
            break;
	    	
	    	case 'postcharges': {
                $controller->loadModel("UserAccount");
                $db_pin = $controller->UserAccount->getPin($user_id);
	        	$pin = $controller->data['pin'];
				
                if (!$pin)
                {
                    $response['error'] = "Enter your Pin Number";
                }
                elseif ($pin != $db_pin)
                {
                    $response['error'] = "Invalid Pin Number";
                }
                else
                {
                	// set hl7_status to 'Billable' -- this will cause HL7 lib to pick this up and send a DFT message
                	$controller->EncounterMaster->setItemValue( 'hl7_status', '1', $encounter_id, $patient_id, $user_id );

                                        //set Supervising Provider if present
                                        if(isset($controller->data['EncounterSuperbill']['supervising_provider_id']))
					     $controller->EncounterSuperbill->setSingleItem($encounter_id, 'supervising_provider_id', $controller->data['EncounterSuperbill']['supervising_provider_id']);

                	$PracticeSetting=$controller->Session->read('PracticeSetting');
		    		$this->billingTasks(&$controller,$PracticeSetting,$encounter_id,$patient_id);
                	if($this->kareoErr) {
                		echo $this->kareoErr;
                		exit;
                	}
                	$response['msg2'] = "Charges Posted!";	                
		    
                                        //post HL7 DFT if necessary
                                        $this->postHL7($PracticeSetting,$encounter_id);

				}
                echo json_encode($response);
                exit;
	    	} 
	    	break;					
						
			case 'forward': {
							if (isset($_POST['routing']) and $_POST['routing']!='')
							{
								App::import('Helper', 'Html');
								$html = new HtmlHelper();
								$controller->loadModel("MessagingMessage");
								$controller->MessagingMessage->create();
								$controller->data['MessagingMessage']['sender_id'] = $user_id;
								$controller->data['MessagingMessage']['recipient_id'] = $_POST['routing'];
								$controller->data['MessagingMessage']['patient_id'] = $patient_id;
								$controller->data['MessagingMessage']['type'] = "Patient";
								$controller->data['MessagingMessage']['subject'] = "ATTN: Encounter for Review";

								$http_str = 'http://';

								if($_SERVER['SERVER_PORT'] == 443)
								{
									$http_str = 'https://';
								}

								$url = Router::url(array(
										'controller'=>'encounters', 
										'action' =>'superbill', 
										'encounter_id' => $encounter_id,
										'task' => 'get_report_html',
								));

								$summaryLink = "<a href=\"".$url."\" target=\"_blank\">Click here to just see the Visit Summary Report</a>";
																										
								$controller->EncounterMaster->id = $encounter_id;
								$status = $controller->EncounterMaster->field('encounter_status');

								
								if ($status == 'Closed') {
									$url = Router::url(array(
											'controller'=>'encounters', 
											'action' =>'index', 
											'task' => 'edit',
											'encounter_id' => $encounter_id,
											'view_addendum' => 1,
									));
									
									
									
									$encounterLink = "<a href=\"".$url."\" target=\"_blank\">Click Here to go to the Encounter (to add an Addendum)</a>";
								} else {
									$url = Router::url(array(
											'controller'=>'encounters', 
											'action' =>'index', 
											'task' => 'edit',
											'encounter_id' => $encounter_id,
									));
									
									$encounterLink = "<a href=\"".$url."\" target=\"_blank\">Click Here to go to the Encounter</a>";
								}
								
																										
								$controller->data['MessagingMessage']['message'] = 
									"Please review this patient's Encounter.\n
									$encounterLink\n
									$summaryLink";
								$controller->data['MessagingMessage']['priority'] = "Normal";
								$controller->data['MessagingMessage']['status'] = "New";
								$controller->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
								$controller->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
								$controller->data['MessagingMessage']['modified_user_id'] = $user_id;
								$controller->MessagingMessage->save($controller->data);
								
								die('Done');
							}							
						} break;
						
						
            case "get_report_html":
            {
                $controller->layout = 'empty';
                
				//If we click the visit summary link, Import medication details from the Dosespot 
				$controller->PracticeSetting = ClassRegistry::init('PracticeSetting');
				$controller->PatientDemographic = ClassRegistry::init('PatientDemographic');
				$controller->PatientMedicationList = ClassRegistry::init('PatientMedicationList');
				$controller->UserGroup = ClassRegistry::init('UserGroup');
				$controller->UserAccount = ClassRegistry::init('UserAccount');
				/*  ------- no longer needed (at least it shouldn't be!) in ticket #2130 we have it listen in plan tab		
				                $PracticeSettings =  $controller->PracticeSetting->find('first');
		        $rx_setup = $PracticeSettings['PracticeSetting']['rx_setup'];
				if($rx_setup=='Electronic_Dosespot')
				{
				    $dosespot_patient_id = $controller->PatientDemographic->getPatientDoesespotId($patient_id);
				
					//If the patient not exists in Dosespot, add the patient to Dosespot
					if($dosespot_patient_id == 0 or $dosespot_patient_id == '')
					{					
						$controller->PatientDemographic->updateDosespotPatient($patient_id);					
						$dosespot_patient_id = $controller->PatientDemographic->getPatientDoesespotId($patient_id);
					}					
					
					$dosespot_xml_api = new Dosespot_XML_API();
	
					$medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id);
	   
					foreach ($medication_items as $medication_item)
					{
						$dosespot_medication_id = $medication_item['MedicationId'];
						$items = $controller->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.dosespot_medication_id' => $dosespot_medication_id)));
	
						if(empty($items))
						{
						    $start_date = __date('Y-m-d', strtotime($medication_item['date_written'].'+'.$medication_item['days_supply'].'days'));
					        $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
							$controller->data = array();
							$controller->data['PatientMedicationList']['patient_id'] = $patient_id;
							$controller->data['PatientMedicationList']['medication_type'] = "Electronic";
							$controller->data['PatientMedicationList']['provider_id'] = $controller->UserAccount->getProviderId($medication_item['prescriber_user_id']);
							$controller->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
							$controller->data['PatientMedicationList']['medication'] = $medication_item['medication'];
							$controller->data['PatientMedicationList']['source'] = "e-Prescribing History";
							$controller->data['PatientMedicationList']['status'] = $medication_item['status'];
							$controller->data['PatientMedicationList']['direction'] = $medication_item['direction'];
							$controller->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
						    $controller->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
							$controller->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
							//only set inactive_date IF days_supply was provided 
							if (!empty ($medication_item['days_supply'])) {
                         					$controller->data['PatientMedicationList']['end_date'] = $inactive_date;
                        				}
							$controller->data['PatientMedicationList']['modified_user_id'] =  $controller->user_id;
							$controller->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
							$controller->PatientMedicationList->create();
							$controller->PatientMedicationList->save($controller->data);
							$controller->PatientMedicationList->saveAudit('New');
	
						}
						else
						{
						    $start_date = __date('Y-m-d', strtotime($medication_item['date_written'].'+'.$medication_item['days_supply'].'days'));
					        $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
							$controller->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
							$controller->data['PatientMedicationList']['patient_id'] = $patient_id;
							$controller->data['PatientMedicationList']['medication_type'] = "Electronic";
							$controller->data['PatientMedicationList']['provider_id'] = $controller->UserAccount->getProviderId($medication_item['prescriber_user_id']);
							$controller->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
							$controller->data['PatientMedicationList']['medication'] = $medication_item['medication'];
							$controller->data['PatientMedicationList']['source'] = "e-Prescribing History";
							$controller->data['PatientMedicationList']['status'] = $items['PatientMedicationList']['status'];
							$controller->data['PatientMedicationList']['direction'] = $medication_item['direction'];
							$controller->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
						    	$controller->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
							$controller->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
							//only set inactive_date IF days_supply was provided 
							if (!empty ($medication_item['days_supply'])) {
                         					$controller->data['PatientMedicationList']['end_date'] = $inactive_date;
                        				}
							$controller->data['PatientMedicationList']['modified_user_id'] =  $controller->user_id;
							$controller->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
							$controller->PatientMedicationList->save($controller->data);
							$controller->PatientMedicationList->saveAudit('Update');
						}	
					}				
				}
				*/
				$isRefillEnable = $controller->UserGroup->isRxRefillEnable();
                $controller->set("isRefillEnable", $isRefillEnable);
                
                $demographic_items = $controller->PatientDemographic->find('first',array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1, 'fields' => 'medication_list_none'));
                $medication_list_none = $demographic_items['PatientDemographic']['medication_list_none'];
                $controller->set('medication_list_none', $medication_list_none);
				
										$controller->EncounterMaster->recursive=0;
								$encounter = $controller->EncounterMaster->find('first', array(
									'conditions' => array(
										'EncounterMaster.encounter_id' => $encounter_id,
									),
								));
								
								if ($encounter['EncounterMaster']['encounter_status'] == 'Closed') {
								  //this should be defined, so bring it in as 1st priority	
								  if($encounter['EncounterMaster']['visit_summary_view_format'])
								  {
								  	$defaultFormat =$encounter['EncounterMaster']['visit_summary_view_format'];
								  }
								  else
								  {	
									if(empty($encounter['UserAccount']['new_pt_note']) || empty($encounter['UserAccount']['est_pt_note'])) {
									 $defaultFormat = 'soap';
									} else {
									 $defaultFormat = 'full';
									}

									if ($encounter['PatientDemographic']['status'] == 'New') {
										 $defaultFormat = 'soap';

										if ($encounter['UserAccount']['new_pt_note'] == '1') {
										 $defaultFormat = 'full';
										}

									} else {
										 $defaultFormat = 'soap';

										if ($encounter['UserAccount']['est_pt_note'] == '1') {
										 $defaultFormat = 'full';
										}
									}									
								   }
									$snapShots = Visit_Summary::getSnapShot($encounter_id);
							
									$overrideFormat = isset($_GET['format']) ? $_GET['format'] : $defaultFormat;
										
									//update lastviewed format into table
									if(isset($_GET['format']))
									{
					  				   $data['visit_summary_view_format'] = $_GET['format'];
					  				   $data['encounter_id']=$encounter_id;
					  				   $controller->EncounterMaster->create();
					  				   $controller->EncounterMaster->save($data);
									}

							
									$user = $controller->UserAccount->getUserByID(EMR_Account::getCurretUserId());
									$isPatient = ($user->role_id == EMR_Roles::PATIENT_ROLE_ID);

									if ($isPatient) {
										echo $snapShots['patient'];
									} else if (isset($snapShots[$overrideFormat])) {
										echo $snapShots[$overrideFormat];
									}
									ob_flush();
									flush();
									exit();										
								} else {
									if ($report = Visit_Summary::generateReport($encounter_id, $phone))
									{
											App::import('Helper', 'Html');
											$html = new HtmlHelper();

											$url = UploadSettings::createIfNotExists($controller->paths['encounters'] . $encounter_id . DS);
											$url = str_replace('//', '/', $url);

											$pdffile = 'encounter_' . $encounter_id . '_summary.pdf';

											//format report, by removing hide text
											$reportmod = preg_replace('/(<span class="hide_for_print">.+?)+(<\/span>)/i', '', $report);


											//PDF file creation
											//site::write(pdfReport::generate($reportmod, $url . $pdffile), $url . $pdffile);

											// Instead of writing a pdf file, just right the html output for later retrieval;
											$tmp_file = 'encounter_' . $encounter_id . '_summary.tmp';
											site::write($reportmod, $url . $tmp_file);

											echo $report;


											//remove server copy of report
											//unlink($url.$pdffile);

											ob_flush();
											flush();

											exit();
									}									
								}
								
								

                exit('could not generate report');
            }
            case "get_report_ccr":
            {
                CCR::generateCCR(&$controller);
            }
            break;
            case "get_report_pdf":
            {
							
								$encounter = $controller->EncounterMaster->find('first', array(
									'conditions' => array(
										'EncounterMaster.encounter_id' => $encounter_id,
									),'recursive' => 0,
								));
								
								
								if ($encounter['EncounterMaster']['encounter_status'] == 'Closed' && $controller->name != 'Api') {
									
									if(empty($encounter['UserAccount']['new_pt_note']) || empty($encounter['UserAccount']['est_pt_note'])) {
									 $defaultFormat = 'soap';
									} else {
									 $defaultFormat = 'full';
									}

									if ($encounter['PatientDemographic']['status'] == 'New') {
										 $defaultFormat = 'soap';

										if ($encounter['UserAccount']['new_pt_note'] == '1') {
										 $defaultFormat = 'full';
										}

									} else {
										 $defaultFormat = 'soap';

										if ($encounter['UserAccount']['est_pt_note'] == '1') {
										 $defaultFormat = 'full';
										}
									}									
									
									$snapShots = Visit_Summary::getSnapShot($encounter_id, 'pdf');
										
									$overrideFormat = isset($_GET['format']) ? $_GET['format'] : $defaultFormat;		
									$user = $controller->UserAccount->getUserByID(EMR_Account::getCurretUserId());
									$isPatient = ($user->role_id == EMR_Roles::PATIENT_ROLE_ID);

									if ($isPatient) {
										$targetFile = $snapShots['patient'];
									} else if (isset($snapShots[$overrideFormat])) {
										$targetFile = $snapShots[$overrideFormat];
									}
									$file = 'encounter_' . $encounter_id . '_summary.pdf';

								} else {

									// Get path were files are being saved/read
									$targetPath = str_replace('//', '/', $controller->paths['encounters']);

									// Html version file
									$tmp_file = 'encounter_' . $encounter_id . '_summary.tmp';

									// PDF file
									$file = 'encounter_' . $encounter_id . '_summary.pdf';


									$targetFile = $targetPath . $file;

									// Check and use original location if exists
									if (!is_file($targetPath . $tmp_file)) {
										// Otherwise, look in new location
										$targetPath = str_replace('//', '/', UploadSettings::createIfNotExists($controller->paths['encounters'] . $encounter_id . DS));
										$targetFile = $targetPath . $file;
									}
									
                  // File still not there, create
                  // If being accessed through API force create
                  if (!is_file($targetPath . $tmp_file) || $controller->name == 'Api' ) {
                    $report = Visit_Summary::generateReport($encounter_id, $phone);
                    $reportmod = preg_replace('/(<span class="hide_for_print">.+?)+(<\/span>)/i', '', $report);

                    $tmp_file = 'encounter_' . $encounter_id . '_summary.tmp';
                    site::write($reportmod, $targetPath . $tmp_file);                    
                  }
                  
									// Read contents of report
									$report = file_get_contents($targetPath . $tmp_file);
									if($report) {
                    
                    if ($controller->name == 'Api') {
                      $report = preg_replace('/(<!--\[BEGIN_SIGNED\]-->.+?)+(<!--\[END_SIGNED\]-->)/is', '', $report);
                    }
                    
									 // Write pdf
									 site::write(pdfReport::generate($report), $targetFile);
									}
							
								}
								if($view == 'fax'){
										$controller->loadModel('practiceSetting');
										$settings  = $controller->practiceSetting->getSettings();        
										if(!$settings->faxage_username || !$settings->faxage_password || !$settings->faxage_company) {
											$controller->Session->setFlash(__('Fax is not enabled. Contact Sales for assistance.', true));
											$controller->redirect(array('controller'=> 'encounters', 'action' => 'index'));
											exit();
										}

										$controller->Session->write('fileName', $targetFile);
										$controller->redirect(array('controller'=> 'messaging', 'action' => 'new_fax' ,'fax_doc'));		
										exit;						
									}

								if (!is_file($targetFile))
								{
										die("WARNING: File ".$file." does not exist. Try unlocking and relocking the Encounter and try again.");
								}

								header('Content-Type: application/octet-stream; name="' . $file . '"');
								header('Content-Disposition: attachment; filename="' . $file . '"');
								header('Accept-Ranges: bytes');
								header('Pragma: no-cache');
								header('Expires: 0');
								header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
								header('Content-transfer-encoding: binary');
								header('Content-length: ' . @filesize($targetFile));
								@readfile($targetFile);									

                if ($controller->name == 'Api') {
                  $controller->__cleanUp();
                }
                
                exit;
            }
            break;
            case "edit":
            {
                switch ($controller->data['field'])
                {
                    case "ignored_diagnosis":
                    case "ignored_in_house_labs":
                    case "ignored_in_house_radiologies":
                    case "ignored_in_house_procedures":
                    case "ignored_in_house_immunizations":
		    case "ignored_in_house_injections":                    
                    case "ignored_in_house_meds":
                    case "ignored_in_house_supplies":                    
                    case "ignored_outside_labs":
                    case "ignored_radiologies":
                    case "ignored_procedures":
					case "service_level_advanced":
                    {
                        if ($controller->data['remove'] == 'true')
                        {
                            $controller->EncounterSuperbill->deleteIgnoredItem($encounter_id, $controller->data['field'], $controller->data['value']);
                        }
                        else
                        {
                            $controller->EncounterSuperbill->addIgnoredItem($encounter_id, $controller->data['field'], $controller->data['value']);
                        }
                    }
                    break;
                    default:
                    {
                        $controller->EncounterSuperbill->setSingleItem($encounter_id, $controller->data['field'], $controller->data['value']);
                    }
                }
                
                echo json_encode(array());
                exit;
            }
            break;
			case "import_past_data":
			{
        
        $import_encounter_id = $controller->params['named']['import_encounter_id'];
				$controller->loadModel("EncounterPhysicalExam");
				$controller->loadModel("EncounterPhysicalExamDetail");
				$controller->loadModel("EncounterPhysicalExamText");
				$controller->loadModel("EncounterPhysicalExamImage");
				$controller->loadModel("EncounterRos");
				$controller->loadModel("EncounterAssessment");
				$controller->loadModel("EncounterChiefComplaint");
				$controller->loadModel("EncounterHpi");
				$controller->loadModel("EncounterVital");
				$controller->loadModel("EncounterPointOfCare");
                                
        $planModels = array(
            'EncounterPlanAdviceInstructions', 'EncounterPlanFreeText', 'EncounterPlanHealthMaintenance',
            'EncounterPlanHealthMaintenanceEnrollment', 'EncounterPlanLab', 'EncounterPlanProcedure',
            'EncounterPlanRadiology', 'EncounterPlanReferral',
            'EncounterPlanRx', 'EncounterPlanStatus', 
        );
        
        foreach ($planModels as $m) 
        {
            $controller->loadModel($m);

            if ($m != 'EncounterPlanHealthMaintenanceEnrollment') 
            {
                unset($controller->{$m}->belongsTo['EncounterMaster']);
            }
        }
                                
				unset($controller->EncounterPhysicalExam->belongsTo['PhysicalExamTemplate']);
				unset($controller->EncounterPhysicalExam->belongsTo['EncounterMaster']);
				unset($controller->EncounterRos->belongsTo['EncounterMaster']);
				unset($controller->EncounterRos->belongsTo['ReviewOfSystemTemplate']);
				unset($controller->EncounterAssessment->belongsTo['EncounterMaster']);
				unset($controller->EncounterChiefComplaint->belongsTo['EncounterMaster']);
				unset($controller->EncounterHpi->belongsTo['EncounterMaster']);        
				unset($controller->EncounterVital->belongsTo['EncounterMaster']);        
        
        $importData = $controller->params['form']['import'];
        
        $db_config = $this->getDataSource()->config;
        $cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';								

        Cache::set(array('duration' => '+30 days'));
        Cache::write($cache_file_prefix .'importPastData' . $_SESSION['UserAccount']['user_id'], $importData);
        
        $controller->loadModel('EncounterMaster');
        $existingDataCount = $controller->EncounterMaster->checkExistingData($controller, $encounter_id);
        
        
        if (isset($importData['cc']) && $existingDataCount['cc'] == 0) {
          /*
           * [BEGIN] Import Chief Complaint ----------------------------------- 
           */
          // Note encounter id to import
          $import_encounter_id = $controller->params['named']['import_encounter_id'];

          // Get current chief complaint
          $current_cc = $controller->EncounterChiefComplaint->find('first', array(
              'conditions' => array(
                  'EncounterChiefComplaint.encounter_id' => $encounter_id,
              ),
          ));

          if ($current_cc) 
          {
              $current_cc = $current_cc['EncounterChiefComplaint'];
          }

          // Get import chief complaint
          $import_cc = $controller->EncounterChiefComplaint->find('first', array(
              'conditions' => array(
                  'EncounterChiefComplaint.encounter_id' => $import_encounter_id,
              ),
          ));

          if ($import_cc) 
          {
              $import_cc = $import_cc['EncounterChiefComplaint'];
          }

          if ($import_cc && $current_cc) 
          {
              // Import cc into current cc
              $current_cc['chief_complaint'] = $import_cc['chief_complaint'];
              $current_cc['modified_timestamp'] = __date("Y-m-d H:i:s");
              $current_cc['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
              $current_cc['no_more_complaint'] = $import_cc['no_more_complaint'];
              $current_cc['hx_source'] = $import_cc['hx_source'];

              $current_cc = array(
                  'EncounterChiefComplaint' => $current_cc
              );
              // Save
              $controller->EncounterChiefComplaint->save($current_cc);
          }
          /*
           * [END] Import Chief Complaint ----------------------------------- 
           */
        }
        
        
        if (isset($importData['hpi']) && $existingDataCount['hpi'] == 0) {
          /*
           * [BEGIN] Import HPI -------------------------------------------- 
           */
          // Get hpis to import
          $hpis = $controller->EncounterHpi->find('all', array('conditions' => array('EncounterHpi.encounter_id' => $import_encounter_id)));

          foreach ($hpis as $h) {
              $new = $h;
              unset($new['EncounterHpi']['history_of_present_illness_id']);
              $new['EncounterHpi']['encounter_id'] = $encounter_id; 
              $new['EncounterHpi']['modified_timestamp'] = __date("Y-m-d H:i:s");
              $new['EncounterHpi']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
              $controller->EncounterHpi->create();
              $controller->EncounterHpi->save($new);
          }
          /*
           * [END] Import HPI ---------------------------------------------- 
           */          
        }        
        
        if (isset($importData['ros']) && $existingDataCount['ros'] == 0) {
					$ros = $controller->EncounterRos->find('first', array('conditions' => array('EncounterRos.encounter_id' => $controller->params['named']['import_encounter_id'])));
					if ($ros)	{
						$controller->data = $ros;
						unset($controller->data['EncounterRos']['review_of_system_id']);
						$controller->data['EncounterRos']['encounter_id'] = $encounter_id;
						$controller->data['EncounterRos']['system_negative'] = $ros['EncounterRos']['system_negative'];
						$controller->data['EncounterRos']['ros'] = $ros['EncounterRos']['ros'];
						$controller->data['EncounterRos']['template'] = $ros['EncounterRos']['template'];
						$controller->data['EncounterRos']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$controller->data['EncounterRos']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
						$controller->EncounterRos->create();
						$controller->EncounterRos->save($controller->data);
					}          
        }        
        
        if (isset($importData['vitals']) && $existingDataCount['vitals'] == 0) {
          $vitals = $controller->EncounterVital->find('first', array('conditions' => array(
            'EncounterVital.encounter_id' => $controller->params['named']['import_encounter_id'],
          )));
          
          if ($vitals) {
            unset($vitals['EncounterVital']['vital_id']);
            $vitals['EncounterVital']['encounter_id'] = $encounter_id;
            $vitals['EncounterVital']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $vitals['EncounterVital']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
            $controller->EncounterVital->create();
            $controller->EncounterVital->save($vitals);
          }
        }        
        
        
        if (isset($importData['pe']) && $existingDataCount['pe'] == 0) {
					$physical_exam = $controller->EncounterPhysicalExam->find('first', array('conditions' => array('EncounterPhysicalExam.encounter_id' => $controller->params['named']['import_encounter_id'])));
					if ($physical_exam)
					{
						$controller->data = $physical_exam;
						unset($controller->data['EncounterPhysicalExam']['physical_exam_id']);
						$controller->data['EncounterPhysicalExam']['encounter_id'] = $encounter_id;
						$controller->data['EncounterPhysicalExam']['template_id'] = $physical_exam['EncounterPhysicalExam']['template_id'];
						$controller->data['EncounterPhysicalExam']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$controller->data['EncounterPhysicalExam']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
						$controller->EncounterPhysicalExam->create();
						$controller->EncounterPhysicalExam->save($controller->data);
						$physical_exam_id = $controller->EncounterPhysicalExam->getLastInsertID();

						foreach ($physical_exam['EncounterPhysicalExamDetail'] as $physical_exam['EncounterPhysicalExamDetail']):
							$controller->data['EncounterPhysicalExamDetail'] = $physical_exam['EncounterPhysicalExamDetail'];
							unset($controller->data['EncounterPhysicalExamDetail']['details_id']);
							$controller->data['EncounterPhysicalExamDetail']['physical_exam_id'] = $physical_exam_id;
							$controller->data['EncounterPhysicalExamDetail']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$controller->data['EncounterPhysicalExamDetail']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
							$controller->EncounterPhysicalExamDetail->create();
							$controller->EncounterPhysicalExamDetail->save($controller->data);
						endforeach;

						foreach ($physical_exam['EncounterPhysicalExamText'] as $physical_exam['EncounterPhysicalExamText']):
							$controller->data['EncounterPhysicalExamText'] = $physical_exam['EncounterPhysicalExamText'];
							unset($controller->data['EncounterPhysicalExamText']['text_id']);
							$controller->data['EncounterPhysicalExamText']['physical_exam_id'] = $physical_exam_id;
							$controller->data['EncounterPhysicalExamText']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$controller->data['EncounterPhysicalExamText']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
							$controller->EncounterPhysicalExamText->create();
							$controller->EncounterPhysicalExamText->save($controller->data);
						endforeach;
					}

					$physical_exam_image = $controller->EncounterPhysicalExamImage->find('first', array('conditions' => array('EncounterPhysicalExamImage.encounter_id' => $controller->params['named']['import_encounter_id'])));
					if ($physical_exam_image)
					{
						$controller->data = $physical_exam_image;
						unset($controller->data['EncounterPhysicalExamImage']['physical_exam_image_id']);
						$controller->data['EncounterPhysicalExamImage']['encounter_id'] = $encounter_id;
						$controller->data['EncounterPhysicalExamImage']['image'] = $physical_exam_image['EncounterPhysicalExamImage']['image'];
						$controller->data['EncounterPhysicalExamImage']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$controller->data['EncounterPhysicalExamImage']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
						$controller->EncounterPhysicalExamImage->create();
						$controller->EncounterPhysicalExamImage->save($controller->data);
					}          
        }        
        
        if (isset($importData['poc']) && $existingDataCount['poc'] == 0) {
          $pocList =  $controller->EncounterPointOfCare->find('all', array('conditions' => array(
            'EncounterPointOfCare.encounter_id' => $controller->params['named']['import_encounter_id'],
          )));
          
          foreach ($pocList as $p) {
            unset($p['EncounterPointOfCare']['point_of_care_id']);
            $p['EncounterPointOfCare']['encounter_id'] = $encounter_id;
            $p['EncounterPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $p['EncounterPointOfCare']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
            $controller->EncounterPointOfCare->create();
            $controller->EncounterPointOfCare->save($p);
          }
        }        
        
        if (isset($importData['assessment']) && $existingDataCount['assessment'] == 0) {
					$assessment = $controller->EncounterAssessment->find('all', array('conditions' => array('EncounterAssessment.encounter_id' => $controller->params['named']['import_encounter_id'])));
					foreach ($assessment as $assessment):
						$controller->data = $assessment;
						unset($controller->data['EncounterAssessment']['assessment_id']);
						$controller->data['EncounterAssessment']['encounter_id'] = $encounter_id;
						$controller->data['EncounterAssessment']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$controller->data['EncounterAssessment']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
						$controller->EncounterAssessment->create();
						$controller->EncounterAssessment->save($controller->data);
					endforeach;
        }        
        
        if (isset($importData['plan']) && $existingDataCount['plan'] == 0) {
          /*
           * [BEGIN] Import Plans -------------------------------------------- 
           */

          $modified_timestamp = __date("Y-m-d H:i:s");
          $modified_user_id = $_SESSION['UserAccount']['user_id'];
          foreach ($planModels as $m)
          {
              if ( $m == 'EncounterPlanHealthMaintenanceEnrollment' || $m == 'EncounterPlanHealthMaintenance') 
              {
                  continue;
              }

              $imports = $controller->{$m}->find('all', array(
                  'conditions' => array(
                      $m . '.encounter_id' => $import_encounter_id, 
                  ),
              ));

              foreach ($imports as $i) 
              {
                  $new = $i;

                  unset($new[$m][$controller->{$m}->primaryKey]);
                  $new[$m]['encounter_id'] = $encounter_id;
                  $new[$m]['modified_timestamp'] = $modified_timestamp;
                  $new[$m]['modified_user_id'] = $modified_user_id;

                  $controller->{$m}->create();
                  $controller->{$m}->save($new);

              }

          }

          // Special case for Health Maintenance Plan and Enrollment

          $hmps = $controller->EncounterPlanHealthMaintenance->find('all', array('conditions' => array(
              'EncounterPlanHealthMaintenance.encounter_id' => $import_encounter_id,
          )));

          foreach ($hmps as $hmp) 
          {
              $new_hmp = $hmp;

              unset($new_hmp['EncounterPlanHealthMaintenance']['health_maintenance_id']);
              $new_hmp['EncounterPlanHealthMaintenance']['encounter_id'] = $encounter_id;
              $new_hmp['EncounterPlanHealthMaintenance']['modified_timestamp'] = $modified_timestamp;
              $new_hmp['EncounterPlanHealthMaintenance']['modified_user_id'] = $modified_user_id;

              $controller->EncounterPlanHealthMaintenance->create();
              $controller->EncounterPlanHealthMaintenance->save($new_hmp);

              $new_hmp_id = $controller->EncounterPlanHealthMaintenance->getLastInsertID();

              $enrollments = $controller->EncounterPlanHealthMaintenanceEnrollment->find('all', array(
                  'conditions' => array(
                      'EncounterPlanHealthMaintenanceEnrollment.encounter_id' => $import_encounter_id,
                      'EncounterPlanHealthMaintenanceEnrollment.plan_id' => $hmp['EncounterPlanHealthMaintenance']['health_maintenance_id'],
                  ),
              ));

              foreach ($enrollments as $e) 
              {
                  $new_e = $e;

                  unset($new_e['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id']);
                  $new_e['EncounterPlanHealthMaintenanceEnrollment']['encounter_id'] = $encounter_id;
                  $new_e['EncounterPlanHealthMaintenanceEnrollment']['plan_id'] = $new_hmp_id;
                  $new_e['EncounterPlanHealthMaintenanceEnrollment']['modified_timestamp'] = $modified_timestamp;
                  $new_e['EncounterPlanHealthMaintenanceEnrollment']['modified_user_id'] = $modified_user_id;

                  $controller->EncounterPlanHealthMaintenanceEnrollment->create();
                  $controller->EncounterPlanHealthMaintenanceEnrollment->save($new_e);
              }

          }
          /*
           * [END] Import Plans ---------------------------------------------- 
           */          
        }        
        
        $controller->Session->setFlash(__('Data import successful.', true));
				$controller->redirect(array('action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id));
			} break;
			case 'edit_date':
				
				$encounter_date =  isset($controller->params['form']['encounter_date']) ? $controller->params['form']['encounter_date'] : '';
				
				if (!$encounter_date) {
					exit();
				}
				
				$encounter_date = __date('Y-m-d H:i:s', strtotime($encounter_date));
				
				$controller->loadModel('EncounterMaster');
				
				$controller->EncounterMaster->id = $encounter_id;
				$controller->EncounterMaster->saveField('encounter_date', $encounter_date);
				
				exit();
				break;			
            default:
            {
			
							$combine = false;
							if (isset($controller->provider_info)) {
								$combine = intval($controller->provider_info['UserAccount']['assessment_plan']) ? true : false;
							}
							
			    $controller->loadModel("EncounterPlanAdviceInstructions"); 
                $controller->set("encounter_id", $encounter_id);
                
                $controller->loadModel("PatientDemographic");
                $patient_status = $controller->PatientDemographic->getPatientStatus($patient_id);
                $controller->set("patient_status", $patient_status);
                
                //Encounter Superbill
                $controller->loadModel("EncounterSuperbill");
		$superbill_items=$controller->EncounterSuperbill->getItem($encounter_id);
                $controller->set("superbill_item", $controller->sanitizeHTML($superbill_items));
                
                //Patient Name
                $controller->loadModel("PatientDemographic");
                $controller->set("patient_name", $controller->sanitizeHTML($controller->PatientDemographic->getPatientName($patient_id)));
                
                //Symptoms/Diagnosis

                $controller->loadModel("EncounterAssessment");
                $controller->set("assessments", $controller->sanitizeHTML($controller->EncounterAssessment->getAllAssessments($encounter_id)));
                
                $controller->loadModel("EncounterPointOfCare");
                
                //in house labs
                $controller->set("in_house_labs", $controller->sanitizeHTML($controller->EncounterPointOfCare->getAllLab($encounter_id)));
                
                //in house radiology
                $controller->set("in_house_radiologies", $controller->sanitizeHTML($controller->EncounterPointOfCare->getAllRadiology($encounter_id)));
                
                //in house procedure
                $controller->set("in_house_procedures", $controller->sanitizeHTML($controller->EncounterPointOfCare->getAllProcedure($encounter_id)));
                
                //in house immunization
                $controller->set("in_house_immunizations", $controller->sanitizeHTML($controller->EncounterPointOfCare->getAllImmunization($encounter_id)));
                
                //in house med
                $controller->set("in_house_meds", $controller->sanitizeHTML($controller->EncounterPointOfCare->getAllMed($encounter_id)));

		//in house supplies
                 $controller->set("in_house_supplies", $controller->sanitizeHTML($controller->EncounterPointOfCare->getAllSupplies($encounter_id)));

                 //in house injections
                $controller->set("in_house_injections", $controller->sanitizeHTML($controller->EncounterPointOfCare->getAllInjections($encounter_id)));
              
				//outside labs
				$this->PracticeSetting = ClassRegistry::init('PracticeSetting');
				$practice_settings = $this->PracticeSetting->getSettings();
				
				if($practice_settings->labs_setup == 'Electronic')
				{
					$controller->loadModel("EmdeonOrder");
					$test_list = $controller->EmdeonOrder->getTestListByEncounter($encounter_id);
					$controller->set("labs", $test_list);
				}
				else
				{
					$controller->loadModel("EncounterPlanLab");
					$controller->set("labs", $controller->sanitizeHTML($controller->EncounterPlanLab->getAllLabs($encounter_id, $combine)));
				}
                
                //radiology
                $controller->loadModel("EncounterPlanRadiology");

                $controller->set("radiologies", $controller->sanitizeHTML($controller->EncounterPlanRadiology->getAllRadiologies($encounter_id, $combine)));
                
                //procedures
                $controller->loadModel("EncounterPlanProcedure");
                $controller->set("procedures", $controller->sanitizeHTML($controller->EncounterPlanProcedure->getAllProcedures($encounter_id, $combine)));
				
				$encounter_items = $controller->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id)));
				
				if ($encounter_items)
                {
										$controller->set('encounter_date', $encounter_items['EncounterMaster']['encounter_date']);
                    $controller->set('followup', $encounter_items['EncounterMaster']['followup']);
                    $controller->set('encounter_status', $encounter_items['EncounterMaster']['encounter_status']);
                    $controller->set('return_time', $encounter_items['EncounterMaster']['return_time']);
                    $controller->set('return_period', $encounter_items['EncounterMaster']['return_period']);
                    $controller->set('visit_summary_given', $encounter_items['EncounterMaster']['visit_summary_given']);
					for ($i = 1; $i <= 3; ++$i)
					{
						if (($encounter_items['EncounterMaster']['medication_list_reviewed'.$i]) == $user_id)
						{
							$controller->set('meds_reviewed', 'yes');
							break;
						}
						else
						{
							$controller->set('meds_reviewed', 'no');
						}
					}
				}
				
				$controller->loadModel("UserGroup");
                
                $controller->set("encounter_group_defined", $controller->UserGroup->isGroupFunctionDefined(EMR_Groups::GROUP_ENCOUNTER_LOCK));
                
                $provider_roles = $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK);
                $controller->set('provider_roles', $provider_roles);
				
				//plan education check
				$plan_education_item = $controller->EncounterPlanAdviceInstructions->find('first',array('conditions' => array('EncounterPlanAdviceInstructions.encounter_id' => $encounter_id, 'EncounterPlanAdviceInstructions.patient_education_comment !=' => '')));
		        $controller->set('PlanEducationItem', $controller->sanitizeHTML($plan_education_item));
				
		$controller->set('supervising_provider', $controller->UserAccount->getUserByID($superbill_items['EncounterSuperbill']['supervising_provider_id']));

            }
        }
	}
	
}

?>
