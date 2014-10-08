<?php

class EncounterHpi extends AppModel 
{ 
	public $name = 'EncounterHpi'; 
	public $primaryKey = 'history_of_present_illness_id';
	public $useTable = 'encounter_history_of_present_illness';
	
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

	public function getCooked( $encounter_id )
	{
		$this->belongsTo = array();
		$hpi = $this->find('all', array(
			'conditions' => array('EncounterHpi.encounter_id' => $encounter_id)
		));
		
		$new_hpi = array();
		if($hpi) {
			foreach($hpi as $k => $v) {
				$v = $v['EncounterHpi'];
				
				$new_hpi[$v['history_of_present_illness_id']] = $v;
			}
		}
		return $new_hpi;
	}
	
	
	public function setItemValue($field, $value, $encounter_id, $user_id, $chief_complaint)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterHpi.encounter_id' => $encounter_id, 'EncounterHpi.chief_complaint' => $chief_complaint)
				)
		);
		$data = array();
		
		if(!empty($search_result))
		{
			$data['EncounterHpi']['history_of_present_illness_id'] = $search_result['EncounterHpi']['history_of_present_illness_id'];
			$data['EncounterHpi']['encounter_id'] = $search_result['EncounterHpi']['encounter_id'];
			$data['EncounterHpi']['chief_complaint'] = $search_result['EncounterHpi']['chief_complaint'];
		}
		else
		{
			$this->create();
			$data['EncounterHpi']['encounter_id'] = $encounter_id;
			$data['EncounterHpi']['chief_complaint'] = $chief_complaint;
		}
		
		$data['EncounterHpi']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterHpi']['modified_user_id'] = $user_id;
		$data['EncounterHpi'][$field] = $value;
		
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', 'chief_complaint', $field) );
	}
	
	public function getItemValue($field, $encounter_id, $chief_complaint)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterHpi.encounter_id' => $encounter_id, 'EncounterHpi.chief_complaint' => $chief_complaint)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result['EncounterHpi'][$field];
		}
		else
		{
			return '';
		}
	}
	public function deleteItem($chief_complaint, $encounter_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterHpi.encounter_id' => $encounter_id, 'EncounterHpi.chief_complaint' => $chief_complaint)
				)
		);
		
		if(!empty($search_result))
		{
			$this->delete($search_result['EncounterHpi']['history_of_present_illness_id']);
		}
		
	}
	public function executeMain(&$controller, $encounter_id, $patient_id, $chronic_problem, $task, $user_id)
	{
		switch ($task)
        {
            case "add_chronic_problem":
            {
                if (!empty($controller->data))
                {
                    $free_txt_value= trim(__strip_tags(trim($controller->data['submitted']['value']))); //make sure not empty data
                    $controller->EncounterMaster->setItemValue($controller->data['submitted']['id'], $free_txt_value, $encounter_id, $patient_id, $user_id);
                    echo nl2br(trim(htmlentities($free_txt_value)));
                }
                
                exit;
            }
            break;
						case 'hpi_per_complaint': {
							
							if (!isset($controller->params['form']['hpi_per_complaint'])) {
								exit;
							}
							
							$hpi_per_complaint = 
								(strtolower(trim($controller->params['form']['hpi_per_complaint'])) == 'yes') ? 1: 0;
							
							$chief_complaint = trim($controller->params['form']['cc']);
							
							$controller->loadModel('EncounterChiefComplaint');
							
							$cc = $controller->EncounterChiefComplaint->find('first', array(
								'conditions' => array(
									'EncounterChiefComplaint.encounter_id' => $encounter_id,
								),
							));
							
							if ($cc) {
								$cc['EncounterChiefComplaint']['multiple_hpi_flag'] = $hpi_per_complaint;
								$controller->EncounterChiefComplaint->save($cc);
							}
					
							die('Ok');
						} break;
            default:
            {
                $encounter_items = $controller->EncounterMaster->find('first', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id), 'recursive' => -1));
                if ($encounter_items)
                {
										
										$controller->loadModel('EncounterChiefComplaint');
										
										$cc = $controller->EncounterChiefComplaint->find('first', array(
											'conditions' => array(
												'EncounterChiefComplaint.encounter_id' => $encounter_id,
											),
										));
										
										$hpi_per_complaint = 1;
										
										if ($cc) {
											$hpi_per_complaint = $cc['EncounterChiefComplaint']['multiple_hpi_flag'];
										}
										$controller->set(compact('hpi_per_complaint'));
										
										
                    $chronic_problem_1 = $encounter_items['EncounterMaster']['chronic_problem_1'];
                    $chronic_problem_2 = $encounter_items['EncounterMaster']['chronic_problem_2'];
                    $chronic_problem_3 = $encounter_items['EncounterMaster']['chronic_problem_3'];
                    $controller->set("chronic_problem_1", $controller->sanitizeHTML($chronic_problem_1));
                    $controller->set("chronic_problem_2", $controller->sanitizeHTML($chronic_problem_2));
                    $controller->set("chronic_problem_3", $controller->sanitizeHTML($chronic_problem_3));
                    
                    $controller->loadModel('EncounterChiefComplaint');
                    $complaints = $controller->EncounterChiefComplaint->getAllItems($encounter_id);
                    
                    $controller->loadModel('CommonHpiData');
                    $common_data = $controller->CommonHpiData->getData($complaints);
                    $controller->set('common_data', $common_data);
                }
            }
        }
	}
	
	public function executeData(&$controller, $encounter_id, $patient_id, $chief_complaint, $hpi_per_complaint, $task, $user_id)
	{
            
            // BEGIN HPI COMMON DATA -------------------------------------------
            App::import('Model', 'CommonHpiData');
            $commonHpiData = new CommonHpiData();
            $commonHpiData->prePopulate();
            $common_data = $commonHpiData->getData($chief_complaint);
            
            // Pass the chief complaint and common data to the view
            $controller->set(compact('chief_complaint', 'common_data'));
            
            // END HPI COMMON DATA ---------------------------------------------
            
            
            
		switch ($task)
        {
            case "edit":
            {
                if (!empty($controller->data))
                {
                    if ($controller->data['submitted']['id'] == 'duration_date')
                    {
											$controller->data['submitted']['value'] = trim($controller->data['submitted']['value']);
											
											if ($controller->data['submitted']['value'] == '') {
												$controller->data['submitted']['value'] = null;
											} else {
                        $controller->data['submitted']['value'] = __date("Y-m-d", str_replace("-", "/", strtotime($controller->data['submitted']['value'])));
											}

											$free_txt_value = $controller->data['submitted']['value'];
                    } else {
											$free_txt_value=trim($controller->data['submitted']['value']); //make sure not empty data
										}
										
										$free_txt_value = trim(__strip_tags($free_txt_value));
										
										$controller->loadModel('EncounterChiefComplaint');
										
										$cc = $controller->EncounterChiefComplaint->find('first', array(
											'conditions' => array(
												'EncounterChiefComplaint.encounter_id' => $encounter_id,
											),
										));
										
										if ($cc['EncounterChiefComplaint']['multiple_hpi_flag'] == 0) {
											
											// HPI per complaint is off, update the hpi for all ccs
											$allsChiefComplaints = json_decode($cc['EncounterChiefComplaint']['chief_complaint']);
											
											foreach ($allsChiefComplaints as $_cc) {
												$controller->EncounterHpi->setItemValue($controller->data['submitted']['id'], $free_txt_value, $encounter_id, $user_id, $_cc);
											}
											
										} else {
											$controller->EncounterHpi->setItemValue($controller->data['submitted']['id'], $free_txt_value, $encounter_id, $user_id, $chief_complaint);
										}
										
                    
										echo nl2br(trim(htmlentities($free_txt_value)));
                }
                exit;
            }
            break;
						
						case 'load_data': {
							
							$chief_complaint = $controller->params['form']['chief_complaint'];
							
							$hpi = $this->find('first', array('conditions' => array(
								'EncounterHpi.chief_complaint' => $chief_complaint,
								'EncounterHpi.encounter_id' => $encounter_id,
							)));
							
							if (!$hpi) {
								echo '';
							} else {
								echo $hpi['EncounterHpi'][$controller->params['form']['id']];
							}
							
							exit();
						} break;
						
						case 'import_form_data': {
							$dataId = isset($controller->params['named']['form_data_id']) ? $controller->params['named']['form_data_id'] : '';
							
							if ($dataId && isset($controller->params['form']['post'])) {
								$this->importFormData($dataId, $encounter_id);
							}
							exit();
						} break;
						
            default:
            {
                if ($hpi_per_complaint == 'no')
                {
                    $first_item = $controller->EncounterHpi->find('first', array('order' => array('EncounterHpi.history_of_present_illness_id ASC'), 'conditions' => array('EncounterHpi.encounter_id' => $encounter_id)));
                    
                    if ($first_item)
                    {
                        $controller->set('HpiItem', $first_item['EncounterHpi']);
                    }
                }
                else
                {
                    $hpi_items = $controller->EncounterHpi->find('first', array('conditions' => array('EncounterHpi.encounter_id' => $encounter_id, 'EncounterHpi.chief_complaint' => $chief_complaint)));

										if (!$hpi_items) {
											$controller->EncounterHpi->setItemValue('free_text', '', $encounter_id, $user_id, $chief_complaint);
										}
                    $hpi_items = $controller->EncounterHpi->find('first', array('conditions' => array('EncounterHpi.encounter_id' => $encounter_id, 'EncounterHpi.chief_complaint' => $chief_complaint)));
										
										$controller->set('HpiItem', $hpi_items['EncounterHpi']);
                }
            }
        }
	}
	
	
	public function importFormData($dataId, $encounterId) {
		App::import('Lib', 'FormBuilder');
		App::import('Model', 'FormData');
		
		$formBuilder = new FormBuilder();		
		$fdModel = new FormData();
		
		$formData = $fdModel->find('first', array('conditions' => array(
			'FormData.form_data_id' => $dataId,
		)));
		
		if (!$formData) {
			return false;
		}
		
		$data = $formData['FormData']['form_data'];
		$map = $formBuilder->getDataMap($formData['FormTemplate']['template_content'], $data, array('preserve_columns' => true));
		$freeText = "\n";
		foreach ($map as $m) {
			
			if (isset($m['question'])) {
				if (trim($m['question'])) {
                                                if (is_array($m['answer'])) {
                                                  $answer = implode(' and ' , $m['answer']);
                                                } else  {
                                                  $answer=$m['answer'];
                                                }																		
					$freeText .= $m['question'] . ' ' . $answer . '' .$m['suffix'] ."\n";
				}
			} else {
				$tmp = array();
				foreach ($m as $row) {
					if (trim($row['question'])) {
						if (is_array($row['answer'])) {
						  $answer = implode(' and ' , $row['answer']);
						} else  {
						  $answer=$row['answer'];
						}
						$tmp[]= $row['question'] . ' ' . $answer .'' . $row['suffix'];
					}
				}
				$freeText .= implode('    ', $tmp);
				$freeText .= "\n";
			}
			
			
		}
		
		$hpi = $this->find('first', array('conditions' => array(
			'EncounterHpi.encounter_id' => $encounterId,
		)));
		
		unset($hpi['EncounterHpi']['modified_timestamp']);
		$new_line = ($hpi['EncounterHpi']['free_text'])? "\n" : '';
		$hpi['EncounterHpi']['free_text'] = $hpi['EncounterHpi']['free_text'].$new_line.$freeText;
		$hpi['EncounterHpi']['modified_user_id'] =	$_SESSION['UserAccount']['user_id'];
		
		$this->save($hpi);
		
		return true;
	}
	
}


?>
