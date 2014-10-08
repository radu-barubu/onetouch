<?php

class PatientAllergy extends AppModel 
{ 
	public $name = 'PatientAllergy'; 
	public $primaryKey = 'allergy_id';
	public $useTable = 'patient_allergies';
	
	public $actsAs = array(
		'Auditable' => 'General Information - Allergies',
		'Unique' => array('patient_id', 'agent')
	);
	
	public $belongsTo = array(
			'PatientDemographic' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		)
	);
	
	public function beforeSave($options)
	{
		$this->data['PatientAllergy']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['PatientAllergy']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function getActiveAllergies($patient_id)
	{

			$patientallergy_items = $this->find('all', array('conditions' => array('AND' => array('PatientAllergy.patient_id' => $patient_id, 'PatientAllergy.status' => 'Active'))));
		
		return $patientallergy_items;
	}
	
	public function getAllergies($patient_id)
	{
		

			$data = $this->find('all',array(
				'conditions' => array('PatientAllergy.patient_id' => $patient_id)
			));
			
		if (!$data) {
			return array();
		}
		return array_chunk($data, 10);
	}
	
	public function getAllAlergies($patient_id, $show_all_allergies = "")
	{
		if($show_all_allergies == "no")
		{
			$items = $this->getActiveAllergies($patient_id);
		}
		else
		{
					$items = $this->find('all', array('conditions' => array('PatientAllergy.patient_id' => $patient_id)));

		}
		

		for ($i = 0; $i < count($items); ++$i)
		{
			$reaction = "";
			for ($j = 1; $j <= $items[$i]['PatientAllergy']['reaction_count']; ++$j)
			{
				if ($items[$i]['PatientAllergy']['reaction'.$j])
				{
					$reaction[] = $items[$i]['PatientAllergy']['reaction'.$j];
				}
			}
			if ($reaction)
			{
				$items[$i]['PatientAllergy']['reaction'] = implode(", ", $reaction);
			}
		}
		return $items;
	}
	
	public function addAllergy($data, $patient_id, $encounter_id)
	{
		$PracticeSetting = ClassRegistry::init('PracticeSetting')->getSettings();
	    $rx_setup = $PracticeSetting->rx_setup;
    			
		//search duplicate
		$item = $this->find('first', array('conditions' => array('PatientAllergy.patient_id' => $patient_id, 'PatientAllergy.agent' => ucwords(strtolower($data['item_value'])), 'PatientAllergy.type' => $data['item_type'])));
		
		if(!$item)
		{
			$data['PatientAllergy']['patient_id'] = $patient_id;
			$data['PatientAllergy']['encounter_id'] = $encounter_id;
			$data['PatientAllergy']['agent'] = ucwords(strtolower($data['item_value']));
			$data['PatientAllergy']['reaction1'] = ucwords(strtolower($data['item_reaction']));
			$data['PatientAllergy']['type'] = ($data['item_type']=='medication')?'Drug':$data['item_type'];
			$data['PatientAllergy']['snowmed'] = '416098002';
			$data['PatientAllergy']['source'] = 'Patient Reported';
			$data['PatientAllergy']['status'] = 'Active';
			$data['PatientAllergy']['reaction_count'] = 1;
			$this->create();
		
	    	if($rx_setup == 'Electronic_Dosespot')
	    	{
	    		$data['PatientAllergy']['dosespot_code'] = $data['allergy_code'];
	    		$data['PatientAllergy']['dosespot_code_type'] = $data['allergy_code_type'];	
	    			    					
				$dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
				//If the patient not exists in Dosespot, add the patient to Dosespot
				if($dosespot_patient_id == 0 or $dosespot_patient_id == '')
				{					
				   $this->PatientDemographic->updateDosespotPatient($patient_id);					
				   $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
				}
				
				$this->data['agent'] = $data['item_value'];
				$this->data['reaction1'] = $data['item_reaction']; 
				$this->data['allergy_code'] = $data['allergy_code'];
				$this->data['allergy_code_type'] = $data['allergy_code_type'];
				
				if(!empty($dosespot_patient_id)) {
					//Add Allergy data to Dosespot  					                  
            		$dosespot_xml_api = new Dosespot_XML_API();				
            		$added_allergy_item = $dosespot_xml_api->executeAddAllergy($dosespot_patient_id, $this->data);
            	}
            	
				$data['PatientAllergy']['dosespot_allergy_id'] = isset($added_allergy_item['PatientAllergyID'])?($added_allergy_item['PatientAllergyID']):0;
			}
			else if($rx_setup == 'Electronic_Emdeon')
			{
				$data['PatientAllergy']['severity1'] = 'Severe';
				
				$emdeon_xml_api = new Emdeon_XML_API();
				$emdeon_data = array();
				$emdeon_data['allergy_name'] = $data['PatientAllergy']['agent'];
				$mrn = $this->PatientDemographic->getPatientMRN($patient_id);
				$emdeon_data['person'] = $emdeon_xml_api->getPersonByMRN($mrn);
				
				$emdeon_data['allergy_id'] = $data['allergy_code'];
				
				//Change type according to Emdeon allergy type. fdbATDrugName, fdbATIngredient, fdbATAllergenGroup
				switch($data['PatientAllergy']['type'])
				{
					case "Insect":
					case "Plant":
					case "Environment":
						$emdeon_data['type'] = 'fdbATAllergenGroup';
						break;
					case "Inhalant":
					case "Food":
						$emdeon_data['type'] = 'fdbATIngredient';
						break;
					default:
						$emdeon_data['type'] = 'fdbATDrugName';
				}
				
				$emdeon_data['severity'] = $data['PatientAllergy']['severity1'];
				
				$personallergy = '';
				
				$result = $emdeon_xml_api->execute("personallergy", "add", $emdeon_data);
				$personallergy = isset($result['xml']->OBJECT)?trim($result['xml']->OBJECT[0]->personallergy):'';
				$data['PatientAllergy']['allergy_id_emdeon'] = $personallergy;
			}
			
			$this->save($data);
		}
		else
		{
			if ($item['PatientAllergy']['reaction_count'] == 10)
			{
				break;
			}
			
			if($data['item_reaction'])
			{
				$item['PatientAllergy']['reaction_count'] += 1;
				$item['PatientAllergy']['reaction'.$item['PatientAllergy']['reaction_count']] = ucwords(strtolower($data['item_reaction']));
				$this->save($item['PatientAllergy']);
	    		
				if($rx_setup == 'Electronic_Dosespot')
	    		{				
					$dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
					$dosespot_xml_api = new Dosespot_XML_API(); 
					$dosespot_xml_api->executeEditAllergy($dosespot_patient_id, $item['PatientAllergy']);
				}
				else if($rx_setup == 'Electronic_Emdeon')
				{
					//No need to update reaction on emdeon
				}
			}
		}
	}
	
	public function setItemValue($field, $value, $allergy_id, $patient_id, $user_id, $reaction_count = "")
	{
		$search_result = $this->find('first', array('conditions' => array('PatientAllergy.patient_id' => $patient_id, 'PatientAllergy.allergy_id' =>$allergy_id)));
		
		if($search_result)
		{
			$data = array();
			$data['PatientAllergy']['allergy_id'] = $search_result['PatientAllergy']['allergy_id'];		
			$data['PatientAllergy']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$data['PatientAllergy']['modified_user_id'] = $user_id;
			$data['PatientAllergy'][$field] = $value;
			if ($reaction_count)
			{
				$data['PatientAllergy']['reaction_count'] = $reaction_count;
			}
			$this->save($data);
		}
	}
	
	public function getDetails(&$controller, $encounter_id, $user_id)
	{
		$encounter_items = $controller->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'recursive' => -1));
		$no_medication = $encounter_items['EncounterMaster']['taking_medication'];
		
		$no_allergy = $encounter_items['EncounterMaster']['allergy_none'];
		
		$controller->set("no_medication", $no_medication);
		$controller->set("no_allergy", $no_allergy);
		
		if ($encounter_items)
		{
			$reconciliated_fields = array();
			if ($encounter_items)
			{
				extract($encounter_items['EncounterMaster']);
				$exist_current_user = '';
				for ($i = 1; $i <= 3; ++$i)
				{
					if (((${"medication_list_reviewed$i"}) != "") and ((${"medication_list_reviewed$i"}) != 0) and ((${"medication_list_reviewed$i"}) != $user_id))
					{
						$user_detail = $controller->UserAccount->getCurrentUser(${"medication_list_reviewed$i"});
						$user_name = $user_detail['firstname'] . ' ' . $user_detail['lastname'];
						$reviewed = '<label for="others_reviewed" class="label_check_box"><input type="checkbox" name="others_reviewed" id="others_reviewed" value="yes" disabled="disabled" />&nbsp;&nbsp;Reconciled by ' . $user_name . ' , Time: ' . __date("m/d/Y H:i:s", strtotime(${"medication_list_timestamp$i"})) . '</label>';
						array_push($reconciliated_fields, $reviewed);
					}
					if ((${"medication_list_reviewed$i"}) == $user_id)
					{
						$exist_current_user = 'yes';
						$current_user_reviewed_timestamp = ${"medication_list_timestamp$i"};
					}
				}
				
				$current_user_detail = $controller->UserAccount->getCurrentUser($user_id);
				$current_user_name = $current_user_detail['firstname'] . ' ' . $current_user_detail['lastname'];
				$checked = ($exist_current_user == 'yes') ? 'checked' : '';
				$time_field = ($exist_current_user == 'yes') ? ', Time: ' . __date('m/d/Y H:i:s', strtotime($current_user_reviewed_timestamp)) : '';
				$current_user_reviewed = '<label for="medication_reconciliated" class="label_check_box"><input type="checkbox" name="medication_reconciliated" id="medication_reconciliated" ' . $checked . ' />&nbsp;&nbsp;Reviewed and Reconciled by ' . $current_user_name . $time_field . '</label>';
				
				array_push($reconciliated_fields, $current_user_reviewed);
			}
			$controller->set("reconciliated_fields", $reconciliated_fields);
		}
	}
	
	public function execute(&$controller, $encounter_id, $patient_id, $task, $user_id, $show_all_allergies, $medication_show_option)
	{
		switch ($task)
        {
            case "refill_medication":
            {
                $ret = array();
                $refill_count = $controller->PatientMedicationList->refill($controller->data['medication_list_id']);
                $ret['medication_list_id'] = $controller->data['medication_list_id'];
                $ret['refill_count'] = $refill_count;
                echo json_encode($ret);
                exit;
            } break;
            case "addAllergy":
            {
                if (!empty($controller->data))
                {
					$controller->PatientAllergy->addAllergy($controller->data, $patient_id, $encounter_id);
                }
                
                $ret['allergy_list'] = $controller->PatientAllergy->getAllAlergies($patient_id, $show_all_allergies);
                echo json_encode($ret);
                exit;
            }
            break;
            
            case "deleteAllergy":
            {
                if (!empty($controller->data))
                {
                    $controller->PatientAllergy->delete($controller->data['item_value']);
					
					$PracticeSetting = ClassRegistry::init('PracticeSetting')->getSettings();
	    			$rx_setup = $PracticeSetting->rx_setup;
					
					if($rx_setup == 'Electronic_Dosespot')
					{
						$dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
						$dosespot_allergy_id = $controller->data['dosespot_allergy_id'];
						//Delete allergy in Dosespot
						if($dosespot_allergy_id!=0 and $dosespot_allergy_id!='')
						{                       
							$dosespot_xml_api = new Dosespot_XML_API();
							$this->data['dosespot_allergy_id'] = $dosespot_allergy_id;
							$this->data['reaction1'] = '';
							$this->data['status'] = 'Deleted';
							$dosespot_xml_api->executeEditAllergy($dosespot_patient_id, $this->data);
						}
					}
					else if($rx_setup == 'Electronic_Emdeon')
					{
						if(strlen($controller->data['allergy_id_emdeon']) > 0)
						{
							$emdeon_xml_api = new Emdeon_XML_API();
							
							$emdeon_data = array();
							$emdeon_data['personallergy'] = $controller->data['allergy_id_emdeon'];
							$this->result = $emdeon_xml_api->execute("personallergy", "delete", $emdeon_data);
						}
					}
					
                    $ret['allergy_list'] = $controller->PatientAllergy->getAllAlergies($patient_id, $show_all_allergies);
                    echo json_encode($ret);
                }
                exit;
            }
            break;
            
            case "get_allergies":
            {
                $ret = array();
                $ret['allergy_list'] = $controller->PatientAllergy->getAllAlergies($patient_id, $show_all_allergies);
                echo json_encode($ret);
                exit;
            }
            break;
            
            case "addMedication":
            {
                if (!empty($controller->data))
                {
					$controller->PatientMedicationList->addMedication($controller->data['item_value'], $controller->data['frequency'], $controller->data['rxnorm'], $controller->data['medication_form'], $controller->data['medication_strength_value'], $controller->data['medication_strength_unit'],  $controller->data['medication_id'], $encounter_id, $patient_id);
                }
                $ret['medication_list'] = $controller->PatientMedicationList->getAllMedications($patient_id, $medication_show_option);
                $ret = __iconv('UTF-8', 'UTF-8//IGNORE', $ret);
                echo json_encode($ret);
                exit;
            }
            break;
            case "deleteMedication":
            {
                $controller->PatientMedicationList->delete($controller->data['item_value']);
                $ret['medication_list'] = $controller->PatientMedicationList->getAllMedications($patient_id, $medication_show_option);
                echo json_encode($ret);
                exit;
            }
            break;
            
            case "get_medications":
            {
                $ret = array();
                $ret['medication_list'] = $controller->PatientMedicationList->getAllMedications($patient_id, $medication_show_option);
                echo json_encode($ret);
                exit;
            }
            break;
            
            case "update_medication_allergy":
            {
                $controller->EncounterMaster->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $encounter_id, $patient_id, $user_id);
                exit;
            }
            
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
				$patientallergy_items = $controller->PatientAllergy->getAllAlergies($patient_id, $show_all_allergies);
				$controller->set('show_all_allergies', $show_all_allergies);
                $controller->set("patientallergy_items", $patientallergy_items);
				
				$patientmedication_items = $controller->PatientMedicationList->getAllMedications($patient_id, $medication_show_option);
		
                $controller->set("patientmedication_items", $patientmedication_items);
				$controller->set("patient_id", $patient_id);
				$this->getDetails(&$controller, $encounter_id, $user_id);
				
				$controller->loadModel("UserGroup");
				$isRefillEnable = $controller->UserGroup->isRxRefillEnable();
				$controller->set("isRefillEnable", $isRefillEnable);
				
				$controller->set("is_physician", (bool)($controller->Session->read("UserAccount.role_id") == EMR_Roles::PHYSICIAN_ROLE_ID));
				$patient_checkin_id = (isset($controller->params['named']['patient_checkin_id'])) ? $controller->params['named']['patient_checkin_id'] : "";
				if($patient_checkin_id)
				{
	  				$controller->loadModel('PatientCheckinNotes');
                			$items = $controller->PatientCheckinNotes->find(
                            					'first',
                            						array(
                            							'fields' => array('medications','allergies'),
                                						'conditions' => array('PatientCheckinNotes.patient_checkin_id' => $patient_checkin_id)
                            		));
                			if(!empty($items))
                			{ 
                  				$controller->set('patient_checkin', $items);
                			}    
				}       
            }
        }
	}
	
	public function executeData(&$controller, $patient_id, $allergy_id, $dosespot_allergy_id, $task, $user_id)
	{
		$PracticeSetting = ClassRegistry::init('PracticeSetting')->getSettings();
	        $rx_setup = $PracticeSetting->rx_setup;
		$controller->loadModel("PatientDemographic");
		
		if($rx_setup == 'Electronic_Dosespot')
		{
			$dosespot_xml_api = new Dosespot_XML_API();
			$dosespot_patient_id = $controller->PatientDemographic->getPatientDoesespotId($patient_id);
			
			//If the patient not exists in Dosespot, add the patient to Dosespot
			if($dosespot_patient_id == 0 or $dosespot_patient_id == '')
			{					
				$controller->PatientDemographic->updateDosespotPatient($patient_id);					
				$dosespot_patient_id = $controller->PatientDemographic->getPatientDoesespotId($patient_id);
			}
		}
		else if($rx_setup == 'Electronic_Emdeon')
		{
			$emdeon_xml_api = new Emdeon_XML_API();
			$controller->PatientDemographic->updateEmdeonPatient($patient_id);
		}
		
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
                    
					if($rx_setup == 'Electronic_Dosespot')
					{
						if($dosespot_allergy_id != '' and $dosespot_allergy_id != '')
						{
							//Update reaction field of allergy in Dosespot
							if($controller->data['submitted']['id'] == 'reaction1')
							{   
								$this->data['dosespot_allergy_id'] = $dosespot_allergy_id;
								$this->data['reaction1'] = $controller->data['submitted']['value'];
								$this->data['status'] = 'Active';
								$dosespot_xml_api->executeEditAllergy($dosespot_patient_id, $this->data);
							}
							
							//Update status field of allergy in Dosespot
							if($controller->data['submitted']['id'] == 'status')
							{
								$this->data['dosespot_allergy_id'] = $dosespot_allergy_id;
								$this->data['reaction1'] = '';
								$this->data['status'] = $controller->data['submitted']['value'];
								$dosespot_xml_api->executeEditAllergy($dosespot_patient_id, $this->data);
							}
						}
					}
					
					if($rx_setup == 'Electronic_Emdeon')
					{
						if($controller->data['PatientAllergy']['allergy_id_emdeon'])
						{
							$emdeon_data = array();
							$emdeon_data['personallergy'] = $controller->data['PatientAllergy']['allergy_id_emdeon'];
							
							//Update severity field of allergy in Emdeon
							if($controller->data['submitted']['id'] == 'severity1')
							{
								$emdeon_data['severity'] = $controller->data['submitted']['value'];
							}
							
							$emdeon_xml_api->execute("personallergy", "update_all", $emdeon_data);
						}
					}
					
					$items = $controller->PatientAllergy->find('first', array('fields' => 'reaction_count', 'conditions' => array('PatientAllergy.allergy_id' => $allergy_id)));
					if ($controller->data['submitted']['value'] < $items['PatientAllergy']['reaction_count'])
					{
						$data['PatientAllergy']['allergy_id'] = $allergy_id;		
						$data['PatientAllergy']['pulse'] = "";
						$data['PatientAllergy']['severity'] = "";
						for ($i = $controller->data['submitted']['value'] + 1; $i <= 10; ++$i)
						{
							$data['PatientAllergy']['reaction'.$i] = "";
							$data['PatientAllergy']['severity'.$i] = "";
						}
						$this->save($data);
					}
					$reaction_count = isset($controller->data['submitted']['reaction_count'])? $controller->data['submitted']['reaction_count']:'';
					$controller->PatientAllergy->setItemValue($controller->data['submitted']['id'], $controller->data['submitted']['value'], $allergy_id, $patient_id, $user_id, $reaction_count);
                }
                exit;
            }
            break;
            default:
            {
                $items = $controller->PatientAllergy->find('first', array('conditions' => array('PatientAllergy.allergy_id' => $allergy_id)));
                $controller->set('EditItem', $controller->sanitizeHTML($items));
            }
        }
	}
	
	public function saveAllergy($savedata)
	{
		$emdeon_xml_api = new Emdeon_XML_API();
		$data = array();
		$data['allergy_name'] = $savedata['PatientAllergy']['agent'];
		$data['person'] = $emdeon_xml_api->getPersonByMRN($savedata['mrn']);
		
		//Change type according to Emdeon allergy type. fdbATDrugName, fdbATIngredient, fdbATAllergenGroup
		switch($savedata['PatientAllergy']['type'])
		{
			case "Insect":
			case "Plant":
			case "Environment":
				$data['type'] = 'fdbATAllergenGroup';
				break;
			case "Inhalant":
			case "Food":
				$data['type'] = 'fdbATIngredient';
				break;
			default:
				$data['type'] = 'fdbATDrugName';
		}
		
		$data['allergy_id'] = $savedata['PatientAllergy']['allergy_code'];
		$data['severity'] = $savedata['PatientAllergy']['severity1'];
	    
		$personallergy = '';
		
		if(isset($savedata['PatientAllergy']['allergy_id_emdeon']))
		{
			$personallergy = $data['personallergy'] = $savedata['PatientAllergy']['allergy_id_emdeon'];
			$this->result = $emdeon_xml_api->execute("personallergy", "update_all", $data);
		}
		else
		{
			$result = $emdeon_xml_api->execute("personallergy", "add", $data);
			$personallergy = isset($result['xml']->OBJECT)?trim($result['xml']->OBJECT[0]->personallergy):'';
	        $savedata['PatientAllergy']['allergy_id_emdeon'] = $personallergy;
		}

		$this->syncSingleallergy($personallergy, $savedata['PatientAllergy']['patient_id']);
		$this->save($savedata);
		
	}
	public function syncSingleallergy($personallergy, $patient_id)
	{
		$emdeon_xml_api = new Emdeon_XML_API();
		
		if($emdeon_xml_api->checkConnection())
		{
			$personallergys = $emdeon_xml_api->getSingleAllergy($personallergy);
			
			foreach($personallergys as $personallergy)
			{
				$item = $this->find('first', array('conditions' => array('PatientAllergy.allergy_id_emdeon' => $personallergy['personallergy'])));
				
				if(!$item)
				{
					$item = array();
					$this->create();
				}
				
				$item['PatientAllergy']['patient_id'] = $patient_id;
				$item['PatientAllergy']['allergy_id_emdeon'] = $personallergy['personallergy'];
				$item['PatientAllergy']['agent'] = $personallergy['allergy_name'];
				$item['PatientAllergy']['modified_timestamp'] = $personallergy['creation_date'];
				$item['PatientAllergy']['severity1'] = $personallergy['severity'];
				$item['PatientAllergy']['type'] = $personallergy['type'];
				
				$this->save($item);
			}
		}
	}
	
	public function deleteAllergy($allergy_id)
	{
		$emdeon_xml_api = new Emdeon_XML_API();
		
		$item = $this->find('first', array('conditions' => array('PatientAllergy.allergy_id' => $allergy_id)));
		
		$data = array();
		$data['personallergy'] = $item['PatientAllergy']['allergy_id_emdeon'];
		
		$this->result = $emdeon_xml_api->execute("personallergy", "delete", $data);
		$this->delete($allergy_id, false);
	}
}

?>
