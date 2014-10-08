<?php

class EncounterAssessment extends AppModel 
{ 
	public $name = 'EncounterAssessment'; 
	public $primaryKey = 'assessment_id';
	public $useTable = 'encounter_assessment';
	
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
	
	/**
    * Retrieve list of ICD codes by patient
    * @param int $patient_id Patient ID
    * @return array Array of icd_codes
    */
	public function getIcdByPatient($patient_id)
	{
		$results = $this->find('all', array('fields' => array('EncounterAssessment.icd_code'), 'conditions' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterAssessment.icd_code !=' => '')));
		
		$ret = array();
		
		foreach($results as $result)
		{
			$ret[] = $result['EncounterAssessment']['icd_code'];	
		}
		
		$ret = array_unique($ret);
		
		return $ret;
	}
	
	public function getCookedItems($encounter_id)
	{
		$this->belongsTo = array();
		
		$search_result = $this->find( 'all', array(
					'conditions' => array( 'EncounterAssessment.encounter_id' => $encounter_id),
					'order' => array('EncounterAssessment.order ASC'),
				)
		);
		
		$new = array();
		
		foreach($search_result as  $k => $v) {
			$v = $v['EncounterAssessment'];
			
			$new[] = $v;
		}
		
		return $new;
	}
	
	/**
    * Retrieve list of ICD codes by encounter
    * @param int $encounter_id Encounter ID
    * @return array Array of icd_codes
    */
	
	public function getIcdCodes($encounter_id)
    {
        $icd_codes = array();
        
		$search_result = $this->find('all', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_id)));
		
        foreach($search_result as $item)
        {
            $data = array();
		    $data['icd_code'] = $item['EncounterAssessment']['icd_code'];
			$data['diagnosis'] = $item['EncounterAssessment']['diagnosis'];
			$icd_codes[] = $data;
        }
        
        return $icd_codes;
    }
	
	public function getAllAssessments($encounter_id, $limit = false)
	{
		
		$params = array(
				'conditions' => array('EncounterAssessment.encounter_id' => $encounter_id),
				'order' => array('EncounterAssessment.order ASC'),
		);

		if ($limit !== false) {
			$params['limit'] = $limit;
		}
		
		$search_result = $this->find(
				'all', $params
		);
		
		$ret = array();
		
		if(count($search_result) > 0)
		{
			$x=0;
			foreach($search_result as $item)
			{
				if(strlen($item['EncounterAssessment']['diagnosis']) > 0)
				{
					if($item['EncounterAssessment']['diagnosis'] == 'No Match')
					{
					   $ret[$x]['EncounterAssessment']['diagnosis']  = trim($item['EncounterAssessment']['occurence']);
					}
					else
					{
					   $ret[$x]['EncounterAssessment']['diagnosis'] = trim($item['EncounterAssessment']['diagnosis']);
					}
					$ret[$x]['EncounterAssessment']['comment'] = trim($item['EncounterAssessment']['comment']);
				}
				
			  $x++;
			}
		}
		
		//$ret = array_unique($ret);
		return $ret;
	}
	
	public function getAllAssessmentsForHealth($assessment_id)
	{
		$conditions['EncounterAssessment.assessment_id'] = $assessment_id;

		$search_result = $this->find(
			'first', 
			array(
				'conditions' => $conditions
			)
		);
		
		if($search_result)
		{
			return $search_result['EncounterAssessment'];
		}
		else
		{
			return false;
		}
	}
	
	
	public function beforeSave($options)
	{
		$this->data['EncounterAssessment']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EncounterAssessment']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function checkDuplicate($diagnosis, $occurence, $encounter_id)
	{
		$item = $this->find('count', array('conditions' => array('EncounterAssessment.diagnosis' => $diagnosis, 'EncounterAssessment.occurence' => $occurence, 'EncounterAssessment.encounter_id' => $encounter_id)));
		
		if($item)
		{
			return true;
		}
		
		return false;
	}
	
	public function execute(&$controller, $encounter_id, $patient_id, $task, $user_id)
	{
		switch ($task)
        {
                        case 'update_assessment_summary':
                            $controller->loadModel("EncounterAssessmentSummary");   
														$summary = trim(__strip_tags($controller->data['submitted']['value']));
                            $controller->EncounterAssessmentSummary->changeSummary($summary, $encounter_id);
                            echo nl2br(trim(htmlentities($summary)));
                            exit();
                            break;
			case "update_problem_list_status":
			{
				$controller->loadModel('PatientProblemList');
				$data = array();
				$data['PatientProblemList']['problem_list_id'] = $controller->data['problem_list_id'];
				$data['PatientProblemList']['status'] = $controller->data['submitted']['value'];
				$controller->PatientProblemList->save($data);
				echo $controller->data['submitted']['value'];
			    exit;
			}break;
            case "get_list":
            {
                $ret = array();
                $ret['assessment_options'] = $controller->AssessmentOption->find('all');
                $ret['assessment_list'] = $controller->EncounterAssessment->find('all', array(
                    'conditions' => array('EncounterAssessment.encounter_id' => $encounter_id),
                    'order' => array(
                        'EncounterAssessment.order ASC',
                    ),
                ));
                $ret['event_options'] = $controller->PublicHealthInformationNetwork->find('all');
                echo json_encode($ret);
                exit;
            }
            break;
            case "save_order":
            {
                $assessments = isset($controller->params['form']['assessments']) ? $controller->params['form']['assessments'] : array();
                
                if (!$assessments) {
                  exit;
                }
                
                foreach ($assessments as $order => $id) {
                  $controller->EncounterAssessment->id = $id;
                  $controller->EncounterAssessment->saveField('order', $order);
                }
                exit;
            }
            break;          
          
          
          
            case "getPastDiagnosis":
            {
                $ret = array();
                //$ret['pastDiagnosis'] = $controller->PatientMedicalHistory->find('all', array('conditions' => array('PatientMedicalHistory.patient_id' => $patient_id)));			
				$encounter_ids = $controller->EncounterAssessment->EncounterMaster->find('list', array('fields' => 'encounter_id', 'conditions' => array(
					'EncounterMaster.patient_id' => $patient_id, 
					'EncounterMaster.encounter_id <' => $encounter_id,
					'NOT' => array(
						'EncounterMaster.encounter_status' => 'Voided',
					),
					), 'order' =>'EncounterMaster.encounter_date desc', 'recursive' => -1));
				$controller->EncounterAssessment->alias = 'PatientMedicalHistory';
				$ret['pastDiagnosis'] = $controller->EncounterAssessment->find('all', array('fields' => 'diagnosis', 'conditions' => array('encounter_id' => $encounter_ids), 'recursive' => -1, 'group' => array('diagnosis')));
                echo json_encode($ret);
                exit;
            }
            break;
			case "getFavoriteDiagnosis":
            {
                $controller->loadModel('FavoriteDiagnosis');
				$ret = array();
                
                $ret['favoriteDiagnosis'] = $controller->FavoriteDiagnosis->find('all', array(
									'conditions' => array(
										'FavoriteDiagnosis.user_id' => $user_id,
									),
									'order' => array('FavoriteDiagnosis.diagnosis ASC')
								));
                
                echo json_encode($ret);
                exit;
            }
            break;
            case "getProblemList":
            {
                $show_all_problems = (isset($controller->params['named']['show_all_problems'])) ? $controller->params['named']['show_all_problems'] : "";
                $ret = array();
                
								
								
                if ($show_all_problems == 'no')
                {

											$ret['problemList'] = $controller->PatientProblemList->find('all', array('conditions' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.status' => 'Active')));

									
                }
                else
                {

											$ret['problemList'] = $controller->PatientProblemList->find('all', array('conditions' => array('PatientProblemList.patient_id' => $patient_id)));

                }
                echo json_encode($ret);
                exit;
            }
            break;
            case "add":
            {
                if (!empty($controller->data))
                {
                								$controller->loadModel("Icd");
                                $controller->Icd->setVersion();
										$ret = array();
					
                    								$icd9 = "No Match";
										$controller->data['EncounterAssessment']['diagnosis'] = '';
										$controller->data['EncounterAssessment']['occurence'] = '';

										
										// Check if matches with an ICD9 code format in the name...
										if (preg_match('/\[(?P<icd9>[\w\.]+)]\s*$/i', $controller->data['item'], $match)) {
												// Get the matching code
												$controller->data['EncounterAssessment']['icd_code'] = $match['icd9'];
												$controller->data['EncounterAssessment']['diagnosis'] = substr($controller->data['item'], 3);

                    
                    // Try to match if a valid ICD10 code format was given
                    } else if (preg_match('/\[[A-TV-Z][0-9][A-Z0-9](\.[A-TV-Z0-9]{1,4})?\]/i', $controller->data['item'], $match)) {
												$controller->data['EncounterAssessment']['icd_code'] = str_replace(array('[', ']'), '',$match[0]);
												$controller->data['EncounterAssessment']['diagnosis'] = substr($controller->data['item'], 3);
                        
										// Try to match description
                    }	else {
											
											
											$search_keyword = substr($controller->data['item'], 3);
											
											$icd9_items = $controller->Icd->find('all', array(
												'conditions' => array(
													'OR' => array(
														array('Icd.code LIKE ' => $search_keyword . '%'), 
														array('Icd.description LIKE ' => $search_keyword . '%'), 
														array('Icd.description LIKE ' => '% ' . $search_keyword . '%'), 
														array('Icd.description LIKE ' => '%[' . $search_keyword . '%'),
												)), 
												'order' => array('Icd.code' => 'asc')));

											if (!empty($icd9_items))
											{
												$term = trim(strtolower(substr($controller->data['item'], 3)));
												$list = $icd9_items;
												$icd9_items = array();
												// So we have a list of Icd9s
												// Iterate through the list ...
												foreach ($list as $i) {
													// ... and try to find an EXACT match
													if ($term == trim(strtolower($i['Icd']['description']))){
														// Found one! Use it
														$icd9_items = $i;
														break;
													}
												}

												// No exact match was found,
												// get the first item in the list
												if (empty ($icd9_items)) {
													$icd9_items = $list[0];
												}

												$icd9 = $icd9_items['Icd']['description'] . ' [' . $icd9_items['Icd']['code'] . ']';
												$controller->data['EncounterAssessment']['icd_code'] = $icd9_items['Icd']['code'];
											}
											$controller->data['EncounterAssessment']['occurence'] = substr($controller->data['item'], 3);
											$controller->data['EncounterAssessment']['diagnosis'] = $icd9;											
											
										}
                    
                    $controller->data['EncounterAssessment']['comment'] = $controller->data['value'];
                    $controller->data['EncounterAssessment']['encounter_id'] = $encounter_id;
					
										$ret['duplicate'] = $this->checkDuplicate($controller->data['EncounterAssessment']['diagnosis'], $controller->data['EncounterAssessment']['occurence'], $encounter_id);

										if(!$ret['duplicate'])
										{
											$controller->EncounterAssessment->create();
											$controller->EncounterAssessment->save($controller->data);
										}
										//add to icd9 table to track how frequent this code is used
										if (isset($controller->data['EncounterAssessment']['icd_code'])) {
											$controller->Icd->updateCitationCount($controller->data['EncounterAssessment']['icd_code']);
										}
                    								
                    
                    $ret['assessment_options'] = $controller->AssessmentOption->find('all');
                    $ret['assessment_list'] = $controller->EncounterAssessment->find('all', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_id)));
                    $ret['event_options'] = $controller->PublicHealthInformationNetwork->find('all');
                    echo json_encode($ret);
                    
                }
                
                exit;
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
										$assessment = $controller->EncounterAssessment->find('first', array(
											'conditions' => array(
												'EncounterAssessment.assessment_id' => $controller->data['item'],
											),
										));
									
                    if ($controller->data['problem'] == 'true')
                    {
                        // Add to Problem List
                        $controller->data['PatientProblemList']['patient_id'] = $patient_id;
						$controller->data['PatientProblemList']['encounter_id'] = $encounter_id;
												$controller->data['PatientProblemList']['status'] = 'Active';
                        $controller->data['PatientProblemList']['diagnosis'] = $controller->data['icd9'];
						
												if ($controller->data['icd9'] == 'No Match') {
													$controller->data['PatientProblemList']['diagnosis'] = $assessment['EncounterAssessment']['occurence'];
												}
						
                        if (strstr($controller->data['icd9'], "["))
                        {
                            $controller->data['PatientProblemList']['icd_code'] = substr($controller->data['icd9'], strrpos($controller->data['icd9'], "[") + 1, -1);
                        }
                        $controller->data['PatientProblemList']['start_date'] = '';
                        $controller->data['PatientProblemList']['end_date'] = '';
                        $controller->data['PatientProblemList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $controller->data['PatientProblemList']['modified_user_id'] = $controller->user_id;
						$problemlist_item = $controller->PatientProblemList->find('all', array('conditions' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.diagnosis' => $controller->data['PatientProblemList']['diagnosis'])));
						if(count($problemlist_item)==0)
						{
                            $controller->PatientProblemList->create();
                            $controller->PatientProblemList->save($controller->data);
						}
                    }
                    else
					{
					    //Delete from Problem List
						$controller->data['PatientProblemList']['patient_id'] = $patient_id;
                        $controller->data['PatientProblemList']['diagnosis'] = $controller->data['icd9'];
												
						if ($controller->data['icd9'] == 'No Match') {
							$controller->data['PatientProblemList']['diagnosis'] = $assessment['EncounterAssessment']['occurence'];
						}												
						$problemlist_item = $controller->PatientProblemList->find('first', array('conditions' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.diagnosis' => $controller->data['PatientProblemList']['diagnosis'])));
						if(!empty($problemlist_item))
						{
						    $controller->PatientProblemList->delete($problemlist_item['PatientProblemList']['problem_list_id'], false);
						}
					}
                    $controller->data['EncounterAssessment']['assessment_id'] = $controller->data['item'];
                    $controller->data['EncounterAssessment']['comment'] = $controller->data['value'];
                    $controller->data['EncounterAssessment']['diagnosis'] = $controller->data['icd9'];
                    if (strstr($controller->data['icd9'], "["))
                    {
                        $controller->data['EncounterAssessment']['icd_code'] = substr($controller->data['icd9'], strrpos($controller->data['icd9'], "[") + 1, -1);
                    }
                    $controller->data['EncounterAssessment']['reportable'] = $controller->data['reportable'];
                    $controller->data['EncounterAssessment']['event'] = $controller->data['cdc'];
                    if (strstr($controller->data['cdc'], "["))
                    {
                        $controller->data['EncounterAssessment']['code'] = substr($controller->data['cdc'], strrpos($controller->data['cdc'], "[") + 1, -1);
                    }
                    $controller->data['EncounterAssessment']['action'] = $controller->data['problem'];
                    $controller->EncounterAssessment->save($controller->data);
                    
                    $ret = array();
                    $ret['assessment_options'] = $controller->AssessmentOption->find('all');
                    $ret['assessment_list'] = $controller->EncounterAssessment->find('all', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_id),
                    'order' => array('EncounterAssessment.order ASC',)));
                    $ret['event_options'] = $controller->PublicHealthInformationNetwork->find('all');
                    
                    echo json_encode($ret);
                }
                
                exit;
            }
            break;
			
			case "markNone":
            {
                if(!empty($controller->data))
                {
				    $controller->data['PatientDemographic']['patient_id'] = $patient_id;
                    $controller->data['PatientDemographic']['problem_list_none'] = $controller->data['submitted']['value'];
                    $controller->PatientDemographic->save($controller->data);
                } 
				
            } break;
			
            case "delete":
            {
                if (!empty($controller->data))
                {
                    $assessment_iems = $controller->EncounterAssessment->find('first', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_id, 'EncounterAssessment.assessment_id' => $controller->data['item'])));
                    
                    if ($assessment_iems)
                    {
                        $diagnosis = $assessment_iems['EncounterAssessment']['diagnosis'];
                        
												if ($diagnosis == 'No Match') {
													$diagnosis = $assessment_iems['EncounterAssessment']['occurence'];
												}
												
                        //Delete the plan items for the diagnosis
												
						$controller->loadModel("PatientProblemList");
						$controller->PatientProblemList->deleteAll(array(
							'PatientProblemList.patient_id' => $patient_id, 
							'PatientProblemList.diagnosis' => $diagnosis,
							'PatientProblemList.encounter_id' => $encounter_id,
							
						));
						
						$controller->loadModel("EncounterPlanAdviceInstructions");
                        $controller->EncounterPlanAdviceInstructions->deleteAll(array(
													'EncounterPlanAdviceInstructions.diagnosis' => $diagnosis,
													'EncounterPlanAdviceInstructions.encounter_id' => $encounter_id,
													));
                        
                        $controller->loadModel("EncounterPlanFreeText");
                        $controller->EncounterPlanFreeText->deleteAll(array(
													'EncounterPlanFreeText.diagnosis' => $diagnosis,
													'EncounterPlanFreeText.encounter_id' => $encounter_id,
													));
                        
                        $controller->loadModel("EncounterPlanStatus");
                        $controller->EncounterPlanStatus->deleteAll(array(
													'EncounterPlanStatus.diagnosis' => $diagnosis,
													'EncounterPlanStatus.encounter_id' => $encounter_id,
												));
                        
                        $controller->loadModel("EncounterPlanHealthMaintenance");
                        $controller->EncounterPlanHealthMaintenance->deleteAll(array(
													'EncounterPlanHealthMaintenance.diagnosis' => $diagnosis,
													'EncounterPlanHealthMaintenance.encounter_id' => $encounter_id,
													));
                        $controller->loadModel("EncounterPlanLab");
                        $controller->EncounterPlanLab->deleteAll(array(
													'EncounterPlanLab.diagnosis' => $diagnosis,
													'EncounterPlanLab.encounter_id' => $encounter_id,
												));
                        
                        $controller->loadModel("EncounterPlanRadiology");
                        $controller->EncounterPlanRadiology->deleteAll(array(
													'EncounterPlanRadiology.diagnosis' => $diagnosis,
													'EncounterPlanRadiology.encounter_id' => $encounter_id,
												));
                        
                        $controller->loadModel("EncounterPlanProcedure");
                        $controller->EncounterPlanProcedure->deleteAll(array(
													'EncounterPlanProcedure.diagnosis' => $diagnosis,
													'EncounterPlanProcedure.encounter_id' => $encounter_id,
												));
                        
                        $controller->loadModel("EncounterPlanReferral");
                        $controller->EncounterPlanReferral->deleteAll(array(
													'EncounterPlanReferral.assessment_diagnosis' => $diagnosis,
													'EncounterPlanReferral.encounter_id' => $encounter_id,
													
												));
                        
                        $controller->loadModel("EncounterPlanRx");
                        $controller->EncounterPlanRx->deleteAll(array(
													'EncounterPlanRx.diagnosis' => $diagnosis,
													'EncounterPlanRx.encounter_id' => $encounter_id,
													));
                    }
                    $controller->EncounterAssessment->delete($controller->data['item'], false);
                    $ret = array();
                    $ret['assessment_options'] = $controller->AssessmentOption->find('all');
                    $ret['assessment_list'] = $controller->EncounterAssessment->find('all', array('conditions' => array('EncounterAssessment.encounter_id' => $encounter_id)));
                    $ret['event_options'] = $controller->PublicHealthInformationNetwork->find('all');
                    echo json_encode($ret);
                }
                
                exit;
            }
            break;
            default:
            {
	
							$controller->set('global_date_format', $controller->__global_date_format);
							$this->PatientProblemList = ClassRegistry::init('PatientProblemList');
				

								$controller->set('PatientProblemList', $controller->sanitizeHTML($controller->PatientProblemList->find('all', array('conditions' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.status' => 'Active')))));

				
							$this->PatientDemographic = ClassRegistry::init('PatientDemographic');
							$demographic_items = $controller->PatientDemographic->find('first',array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));

							$controller->loadModel("EncounterAssessmentSummary");                                

							// Fetch assessment summary
							//$assessment_summary = $controller->EncounterAssessmentSummary->getSummary($encounter_id, $demographic_items);
							$controller->set('assessment_summary', $controller->EncounterAssessmentSummary->getSummary($encounter_id, $demographic_items));
                
                
                $problem_list_none = $demographic_items['PatientDemographic']['problem_list_none'];
                $controller->set('problem_list_none', $problem_list_none);
            }
        }
	}
}

?>
