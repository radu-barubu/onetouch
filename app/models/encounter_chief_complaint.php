<?php

class EncounterChiefComplaint extends AppModel 
{ 
	public $name = 'EncounterChiefComplaint'; 
	public $primaryKey = 'chief_complaint_id';
	public $useTable = 'encounter_chief_complaint';
	
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
	
	private function searchItem($encounter_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterChiefComplaint.encounter_id' => $encounter_id)
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
	
	public function updateNomoreComplaints($value, $encounter_id, $user_id)
	{
		$search_result = $this->searchItem($encounter_id);
		
		if($search_result)
		{
			$data['EncounterChiefComplaint']['chief_complaint_id'] = $search_result['EncounterChiefComplaint']['chief_complaint_id'];
			$data['EncounterChiefComplaint']['encounter_id'] = $search_result['EncounterChiefComplaint']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['EncounterChiefComplaint']['encounter_id'] = $encounter_id;
		}

		$data['EncounterChiefComplaint']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterChiefComplaint']['modified_user_id'] = $user_id;
		$data['EncounterChiefComplaint']['no_more_complaint'] = $value;
		
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', 'no_more_complaint') );
	}

	public function addItem($item_value, $encounter_id, $user_id)
	{
		$data = array();
		$CC_arr = array();
		
		$search_result = $this->searchItem($encounter_id);
		
		if($search_result)
		{
			$data['EncounterChiefComplaint']['chief_complaint_id'] = $search_result['EncounterChiefComplaint']['chief_complaint_id'];
			$data['EncounterChiefComplaint']['encounter_id'] = $search_result['EncounterChiefComplaint']['encounter_id'];
			$CC_arr = json_decode($search_result['EncounterChiefComplaint']['chief_complaint'], true);
			if(!is_array( $CC_arr ))
				$CC_arr = array();
		}
		else
		{
			$this->create();
			$data['EncounterChiefComplaint']['encounter_id'] = $encounter_id;
		}
		
		if(!in_array($item_value, $CC_arr))
		{
			$CC_arr[] = $item_value;
		}
		else
		{
			return false;
		}

		$data['EncounterChiefComplaint']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterChiefComplaint']['modified_user_id'] = $user_id;
		$data['EncounterChiefComplaint']['chief_complaint'] = json_encode($CC_arr);
		
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', 'chief_complaint') );
		
		return true;
	}
	
	public function getAllItems($encounter_id)
	{
		$search_result = $this->searchItem($encounter_id);
		
		$ret = array();
		
		if($search_result)
		{
			$ret = json_decode($search_result['EncounterChiefComplaint']['chief_complaint'], true);
		}
		
		return $ret;
	}
	
	public function getNomoreComplaints($encounter_id)
	{
		$search_result = $this->searchItem($encounter_id);
		
		$ret = '0';
		
		if($search_result)
		{
			$ret = $search_result['EncounterChiefComplaint']['no_more_complaint'];
		}
		
		return $ret;
	}

	public function getHxSource($encounter_id)
	{
		$search_result = $this->searchItem($encounter_id);
		
		$ret = '';
		
		if($search_result)
		{
			$ret = $search_result['EncounterChiefComplaint']['hx_source'];
		}
		
		return $ret;
	}
		
	public function deleteItem($itemvalue, $encounter_id, $user_id)
	{
		$data = array();
		$CC_arr = array();
		
		$search_result = $this->searchItem($encounter_id);
		
		if($search_result)
		{
			$data['EncounterChiefComplaint']['chief_complaint_id'] = $search_result['EncounterChiefComplaint']['chief_complaint_id'];
			$data['EncounterChiefComplaint']['encounter_id'] = $search_result['EncounterChiefComplaint']['encounter_id'];
			$CC_arr = json_decode($search_result['EncounterChiefComplaint']['chief_complaint'], true);
		}
		
		$new_CC_arr = array();
		
		foreach($CC_arr as $CC_item)
		{
			if($CC_item != $itemvalue)
			{
				$new_CC_arr[] = $CC_item;
			}
		}

		$data['EncounterChiefComplaint']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterChiefComplaint']['modified_user_id'] = $user_id;
		$data['EncounterChiefComplaint']['chief_complaint'] = json_encode($new_CC_arr);
		
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', 'chief_complaint') );
	}
	
	public function setItemValue($field, $value, $encounter_id, $user_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterChiefComplaint.encounter_id' => $encounter_id)
				)
		);
		$data = array();
		
		if(!empty($search_result))
		{
			$data['EncounterChiefComplaint']['chief_complaint_id'] = $search_result['EncounterChiefComplaint']['chief_complaint_id'];
			$data['EncounterChiefComplaint']['encounter_id'] = $search_result['EncounterChiefComplaint']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['EncounterChiefComplaint']['encounter_id'] = $encounter_id;
		}
		
		$data['EncounterChiefComplaint']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$data['EncounterChiefComplaint']['modified_user_id'] = $user_id;
		$data['EncounterChiefComplaint'][$field] = $value;
		
		$this->save($data, false, array('modified_timestamp','modified_user_id', 'encounter_id', $field) );
	}
	
	public function getItemValue($field, $encounter_id, $default_text = '')
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('EncounterChiefComplaint.encounter_id' => $encounter_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result['EncounterChiefComplaint'][$field];
		}
		else
		{
			return $default_text;
		}
	}
	
	public function execute(&$controller, $encounter_id, $patient_id, $task, $user_id)
	{
		switch ($task)
        {
            case "get_list":
            {
				echo json_encode($controller->EncounterChiefComplaint->getAllItems($encounter_id));
                exit;
            }
            break;
            case "get_past_list":
            {
				$controller->loadModel('EncounterPlanAdviceInstructions');
				$tooBig=0;
				$search_encounter = array();

				$search_encounter = $this->EncounterMaster->find(
						'all', 
						array(
							'conditions' => array(
								'AND' => array('EncounterMaster.encounter_id <' => $encounter_id, 'EncounterMaster.patient_id' => $patient_id),
								'NOT' => array('EncounterMaster.encounter_status' => 'Voided'),
								
							),
							'order'	=> array('EncounterMaster.encounter_id'	=> 'desc'),
							'fields' => 'encounter_id',
							'recursive'  => -1 
						)
				);

				$search_chief_complaint = array();
				
				$ret = array();
				
				if(count($search_encounter) > 0)
				{
					foreach ($search_encounter as $search_encounter):

						$search_chief_complaint = $this->find(
								'first', 
								array(
									'conditions' => array('EncounterChiefComplaint.encounter_id' => $search_encounter['EncounterMaster']['encounter_id']),
									'order'	=> array('EncounterChiefComplaint.chief_complaint_id'	=> 'desc') 
								)
						);
						
						if (!empty($search_chief_complaint))
						{
							if ($search_chief_complaint['EncounterChiefComplaint']['chief_complaint'] and $search_chief_complaint['EncounterChiefComplaint']['chief_complaint'] != "[]")
							{
								$chief_complaint = json_decode($search_chief_complaint['EncounterChiefComplaint']['chief_complaint'], true);
								for ($i = 0; $i < count($chief_complaint); ++$i)
								{
										$cc = trim($chief_complaint[$i]);
								    if (empty($tooBig) && $cc && !stristr('on f/u', $cc)) //remove redundant phrases - ticket #758
								    {
										$search_plan_advice_instructions = $controller->EncounterPlanAdviceInstructions->find(
											'count', 
											array(
												'conditions' => array('AND' => array('EncounterPlanAdviceInstructions.encounter_id' => $search_encounter['EncounterMaster']['encounter_id'], 'EncounterPlanAdviceInstructions.diagnosis' => $chief_complaint[$i]))
											)
										);
									//remove redundant phrases - ticket #758
                                                                        $chief_complaint[$i]=str_ireplace('F/u On','',$chief_complaint[$i]);
                                                                        
									if ($search_plan_advice_instructions > 0)
									{
										$chief_complaint[$i] .= " Follow Up";
									}
									
									// i don't want more than 10 records sent. 
									if(sizeof($ret) > 10) {
									   $tooBig=1;
									} else {
									    array_push($ret, $chief_complaint[$i]);
									}
								    } 
								}
							}
						}

					endforeach;
				}

                echo json_encode($ret);

                exit;
            }
            break;
            case "add":
            {
                if (!empty($controller->data))
                {
                    //if they screw up and use ALL CAPS
                    $cc_formated = ucwords(strtolower($controller->data['item_value']));
                    $result = $controller->EncounterChiefComplaint->addItem($cc_formated, $encounter_id, $user_id);

                    echo json_encode($controller->EncounterChiefComplaint->getAllItems($encounter_id));
                    
                    //only update 1 of these, not both. preferences come first
                    if(!ClassRegistry::init('AutocompleteCache')->updateCitationCount($controller->data['item_value'])) {
                     ClassRegistry::init('RosSymptom')->updateCitationCount($controller->data['item_value']);
                    } 
                     
                }
                
                exit;
            }
            break;
            case "UpdateHxSource":
            {
                if (!empty($controller->data))
                {
                    $field = 'hx_source';
                    $controller->EncounterChiefComplaint->setItemValue($field, $controller->data['item_value'], $encounter_id, $user_id);
                    //echo json_encode($controller->EncounterChiefComplaint->getHxSource($encounter_id));
                }
                
                exit;
            }
            break;            
            case "delete":
            {
                if (!empty($controller->data))
                {
                    $controller->EncounterChiefComplaint->deleteItem($controller->data['item_value'], $encounter_id, $user_id);
					$controller->loadModel("EncounterHpi");
					$controller->EncounterHpi->deleteItem($controller->data['item_value'], $encounter_id);
                    echo json_encode($controller->EncounterChiefComplaint->getAllItems($encounter_id));
                }
                
                exit;
            }
            break;
            case "no_more":
            {
                if (!empty($controller->data))
                {
                    $controller->EncounterChiefComplaint->updateNomoreComplaints($controller->data['item_value'], $encounter_id, $user_id);
                }
                
                exit;
            }
            break;
            default:
            {
                $controller->loadModel('ReviewOfSystemTemplate');
                $body_systems = $controller->ReviewOfSystemTemplate->getDisplayTemplate(1);
                $controller->set("body_systems", $controller->sanitizeHTML($body_systems));
                $no_more_complains = $controller->EncounterChiefComplaint->getNomoreComplaints($encounter_id);
                $controller->set("no_more_complains", $controller->sanitizeHTML($no_more_complains));
                $controller->set("hx_source",$controller->EncounterChiefComplaint->getHxSource($encounter_id));
            }
        }
	}
}


?>
