<?php

class PatientMedicalHistory extends AppModel 
{
	public $name = 'PatientMedicalHistory'; 
	public $primaryKey = 'medical_history_id';
	public $useTable = 'patient_medical_history';
	
	public $actsAs = array(
		'Auditable' => 'Medical Information - HX - Medical History',
		'Unique' => array('patient_id', 'diagnosis')
	);
	
	function getMedicalHistory($patient_id)
	{
		
		
			$medical_history = $this->find('all', array(
				'conditions' => array('PatientMedicalHistory.patient_id' => $patient_id)
			));

		
		return array_chunk($medical_history, 10);
	}
	
	public function execute(&$controller, $encounter_id, $patient_id, $task)
	{
		$controller->set('encounter_id', $encounter_id);
		$controller->set('patient_id', $patient_id);
        //$controller->set('PatientMedicalHistory', $controller->sanitizeHTML($controller->PatientMedicalHistory->find('all')));
        
        if (!empty($controller->data) && ($task == "addnew" || $task == "edit"))
        {
            $controller->data['PatientMedicalHistory']['patient_id'] = $patient_id;
			$controller->data['PatientMedicalHistory']['encounter_id'] = $encounter_id;           
            $controller->data['PatientMedicalHistory']['start_month'] = $controller->data['PatientMedicalHistory']['start_month'];
            $controller->data['PatientMedicalHistory']['start_year'] = $controller->data['PatientMedicalHistory']['start_year'];
            $controller->data['PatientMedicalHistory']['end_month'] = $controller->data['PatientMedicalHistory']['end_month'];
            $controller->data['PatientMedicalHistory']['end_year'] = $controller->data['PatientMedicalHistory']['end_year'];
            $controller->data['PatientMedicalHistory']['occurrence'] = $controller->data['PatientMedicalHistory']['occurrence'];
            $controller->data['PatientMedicalHistory']['comment'] = $controller->data['PatientMedicalHistory']['comment'];
            $controller->data['PatientMedicalHistory']['status'] = isset($controller->data['PatientMedicalHistory']['status']) ? $controller->data['PatientMedicalHistory']['status'] : '';
            $controller->data['PatientMedicalHistory']['action'] = isset($controller->data['PatientMedicalHistory']['action']) ? 'Moved' : '';
            $controller->data['PatientMedicalHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $controller->data['PatientMedicalHistory']['modified_user_id'] = $controller->user_id;
        }
        
        switch ($task)
        {
            case "load_Icd9_autocomplete":
            {
                if (!empty($controller->data))
                {
                    $controller->Icd->execute($controller, $task);
                }
                exit();
            }
            break;
			case "validate_duplicate":
            {
                if(!empty($controller->data))
                {
                    $all_diagnosis = explode(',', $controller->data['diagnosis']);
					$patient_id = $controller->data['patient_id'];
					$return = array('result' => 'true');
                    foreach($all_diagnosis as $diagnosis)
					{	
						$diagnosis = trim($diagnosis);
						if(empty($diagnosis))	
							continue; 
						$count = $controller->PatientMedicalHistory->find('count', array(
							'conditions' => array('diagnosis' => $diagnosis, 'patient_id' => $patient_id)
						));
						if($count) {
							$return = array('result' => 'false');
							break;
						}
					}					
                }
				echo json_encode($return);
                exit();
            }
            break;
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    if ($controller->data['PatientMedicalHistory']['action'] == 'Moved')
                    {											
						// Move to Problem List
						if ($controller->data['PatientMedicalHistory']['start_month'])
								$strt_mo = $controller->data['PatientMedicalHistory']['start_month'];
						else
								$strt_mo = '01';
						if ($controller->data['PatientMedicalHistory']['start_year'])
								$strt_yr = $controller->data['PatientMedicalHistory']['start_year'];
						else
								$strt_yr = __date("Y");
						if ($controller->data['PatientMedicalHistory']['end_month'])
								$end_mo = $controller->data['PatientMedicalHistory']['end_month'];
						else
								$end_mo = '01';
						if ($controller->data['PatientMedicalHistory']['end_year'])
								$end_yr = $controller->data['PatientMedicalHistory']['end_year'];
						else
								$end_yr = __date("Y");

						$controller->data['PatientProblemList']['patient_id'] = $patient_id;
						$controller->data['PatientProblemList']['encounter_id'] = $encounter_id;
						$controller->data['PatientProblemList']['start_date'] = $strt_yr . '-' . $strt_mo . '-01';
						if ($controller->data['PatientMedicalHistory']['status'] != 'Active')
								$controller->data['PatientProblemList']['end_date'] = $end_yr . '-' . $end_mo . '-01';
						//$controller->data['PatientProblemList']['end_date'] = $controller->data['PatientMedicalHistory']['end_date']?date("Y-m-d", strtotime($controller->data['PatientMedicalHistory']['end_date'])):'';
						//$controller->data['PatientProblemList']['occurrence'] = $controller->data['PatientMedicalHistory']['occurrence'];
						$controller->data['PatientProblemList']['comment'] = $controller->data['PatientMedicalHistory']['comment'];
						$controller->data['PatientProblemList']['occurrence'] = $controller->data['PatientMedicalHistory']['occurrence'];
						$controller->data['PatientProblemList']['status'] = isset($controller->data['PatientMedicalHistory']['status']) ? $controller->data['PatientMedicalHistory']['status'] : '';
						$controller->data['PatientProblemList']['action'] = 'Active';
						$controller->data['PatientProblemList']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$controller->data['PatientProblemList']['modified_user_id'] = $controller->user_id;
					}
					
					$all_diagnosis = explode(',', $controller->data['PatientMedicalHistory']['diagnosis']);
																					
                    foreach($all_diagnosis as $diagnosis)
					{	
						$diagnosis = trim($diagnosis);
						if(empty($diagnosis))	
							continue; 
						$icd9 = '';					
						// Check if matches with an ICD9 code format in the name...
						if (preg_match('/\[(?P<icd9>[\w\.]+)]\s*$/i', $diagnosis, $match)) {
							// Get the matching code
							$icd9 = $match['icd9'];
						}
						$controller->data['PatientMedicalHistory']['diagnosis'] = $diagnosis;
						$controller->data['PatientMedicalHistory']['icd_code'] = $icd9;
						$controller->PatientMedicalHistory->create();
						$controller->PatientMedicalHistory->save($controller->data);
						unset($controller->PatientMedicalHistory->id);
						if ($controller->data['PatientMedicalHistory']['action'] == 'Moved')
                    	{
							// Check if not yet in problem list for this encounter
							$prob = $controller->PatientProblemList->find('count', array(
								'conditions' => array(
									'PatientProblemList.encounter_id' => $encounter_id,
									'PatientProblemList.diagnosis' => $diagnosis,
									'PatientProblemList.icd_code' => $icd9,
								),
							));
							// Not yet in problem list
							if (!$prob) 
							{
								$controller->data['PatientProblemList']['diagnosis'] = $diagnosis;
								$controller->data['PatientProblemList']['icd_code']  = $icd9;
								$controller->PatientProblemList->create();
								$controller->PatientProblemList->save($controller->data);
								unset($controller->PatientProblemList->id);
							}
						}
                	}
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
                    if ($controller->data['PatientMedicalHistory']['action'] == 'Moved')
                    {											
						// Check if not yet in problem list for this encounter
						$prob = $controller->PatientProblemList->find('count', array(
							'conditions' => array(
								'PatientProblemList.encounter_id' => $encounter_id,
								'PatientProblemList.diagnosis' => $controller->data['PatientMedicalHistory']['diagnosis'],
								'PatientProblemList.icd_code' => $controller->data['PatientMedicalHistory']['icd_code'],
								
							),
						));

						// Not yet in problem list
						if (!$prob) {
							// Move to Problem List
							$controller->data['PatientProblemList']['patient_id'] = $patient_id;
							$controller->data['PatientProblemList']['encounter_id'] = $encounter_id;
							$controller->data['PatientProblemList']['diagnosis'] = $controller->data['PatientMedicalHistory']['diagnosis'];
							$controller->data['PatientProblemList']['icd_code'] = $controller->data['PatientMedicalHistory']['icd_code'];
							$controller->data['PatientProblemList']['start_date'] = __date("Y-m-d");
							//$controller->data['PatientProblemList']['end_date'] = $controller->data['PatientMedicalHistory']['end_date']?date("Y-m-d", strtotime($controller->data['PatientMedicalHistory']['end_date'])):'';
							//$controller->data['PatientProblemList']['occurrence'] = $controller->data['PatientMedicalHistory']['occurrence'];
							$controller->data['PatientProblemList']['comment'] = $controller->data['PatientMedicalHistory']['comment'];
							$controller->data['PatientProblemList']['status'] = isset($controller->data['PatientMedicalHistory']['status']) ? $controller->data['PatientMedicalHistory']['status'] : '';
							$controller->data['PatientProblemList']['action'] = 'Active';
							$controller->data['PatientProblemList']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$controller->data['PatientProblemList']['modified_user_id'] = $controller->user_id;
							$controller->PatientProblemList->create();
							$controller->PatientProblemList->save($controller->data);

							//Delete from Medical History
							//$medical_history_id = (isset($controller->params['named']['medical_history_id'])) ? $controller->params['named']['medical_history_id'] : "";
							//$controller->PatientMedicalHistory->delete($medical_history_id, false);
						}
											
                    }
                    //else
                    //{
                    $controller->PatientMedicalHistory->save($controller->data);
                    //}
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $medical_history_id = (isset($controller->params['named']['medical_history_id'])) ? $controller->params['named']['medical_history_id'] : "";
                    $items = $controller->PatientMedicalHistory->find('first', array('conditions' => array('PatientMedicalHistory.medical_history_id' => $medical_history_id)));
                    
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
                    $ids = $controller->data['PatientMedicalHistory']['medical_history_id'];
                    
                    
                    foreach ($ids as $id)
                    {
                        $controller->PatientMedicalHistory->delete($id, false);
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            break;
            default:
            {
							

								$controller->paginate['PatientMedicalHistory'] = array(
									'conditions' => array('PatientMedicalHistory.patient_id' => $patient_id),
									'order' => array('PatientMedicalHistory.modified_timestamp' => 'desc')
								);

							
							
              $controller->set('PatientMedicalHistory', $controller->sanitizeHTML($controller->paginate('PatientMedicalHistory')));
              //$controller->set('PatientMedicalHistory', $controller->sanitizeHTML($controller->paginate('PatientMedicalHistory', array('patient_id' => $patient_id))));
            }
        }
	}
}

?>
