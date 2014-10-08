<?php

class EncounterRos extends AppModel 
{ 
	public $name = 'EncounterRos'; 
	public $primaryKey = 'review_of_system_id';
	public $useTable = 'encounter_review_of_system';
	
	public $belongsTo = array(
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		),
		'ReviewOfSystemTemplate' => array(
			'className' => 'ReviewOfSystemTemplate',
			'foreignKey' => 'template'
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
	
	public function getCookedItems($encounter_id)
	{
		$data = $this->find('first', array(
				'conditions' => array('EncounterRos.encounter_id' => $encounter_id),
				'recursive' => -1,
				)
		);
		if(! $data ) {
			return;
		}
		$ros = $data['EncounterRos'];
		//$ros = json_decode($ros['ros'], true);
		return $ros;
	}

	/* this shouldn't be used or needed any longer */
	public function getCookedComments($encounter_id)
	{
		$data = $this->find('first', array(
				'recursive' => -1,
				'fields' => 'EncounterRos.comments',
				'conditions' => array('EncounterRos.encounter_id' => $encounter_id)
			)
		);
		if(! $data ) {
			return;
		}
		$ros = $data['EncounterRos'];
		$ros = json_decode($ros['comments'], true);
		
		return $ros;
	}	
	
	public function beforeSave($options)
	{
		$this->data['EncounterRos']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EncounterRos']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	private function searchItem($encounter_id,$fields=false)
	{	//can't see any need for this
		$this->unbindModel(array('belongsTo' => array('EncounterMaster')));

		$options['conditions']=array('EncounterRos.encounter_id' => $encounter_id);
	
		if($fields)
		  $options['fields']=$fields;

		$search_result = $this->find('first', $options);

		if(!empty($search_result))
		{
			return $search_result;
		}
		else
		{
			return false;
		}
	}
	
	public function isSystemNegative($encounter_id)
	{
		$search_result = $this->searchItem($encounter_id);
		
		$ret = array();
		
		if($search_result)
		{
			if($search_result['EncounterRos']['system_negative'] == '1')
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	public function getAllItems($encounter_id)
	{
		$search_result = $this->searchItem($encounter_id);
		
		$ret = array();
		
		if($search_result)
		{
			$ret = json_decode($search_result['EncounterRos']['ros'], true);
		}
		
		return $ret;
	}
	
	public function getTemplate($encounter_id)
	{
		$search_result = $this->searchItem($encounter_id);

        $ret['template_id'] = $this->ReviewOfSystemTemplate->getDefaultTemplate();
        $ret['template_name'] = $this->ReviewOfSystemTemplate->getTemplateName($ret['template_id']);
        $ret['default_negative'] = $this->ReviewOfSystemTemplate->getDefaultNegative($ret['template_id']);

				
		if (!$search_result) {
			$this->setTemplate($ret['template_id'], $encounter_id);
			$search_result = $this->searchItem($encounter_id);
		}
				
		if($search_result)
		{
			$ret['template_id'] = $search_result['EncounterRos']['template'];
			$ret['template_name'] = $search_result['ReviewOfSystemTemplate']['template_name'];
            $ret['default_negative'] = $search_result['ReviewOfSystemTemplate']['default_negative'];
		}
		
		return $ret;
	}
	
	public function setTemplate($template_id, $encounter_id)
	{
		$data = array();
		$ros_arr = array();
		
		$search_result = $this->searchItem($encounter_id);
		
		if($search_result)
		{
			$data['EncounterRos']['review_of_system_id'] = $search_result['EncounterRos']['review_of_system_id'];
			$data['EncounterRos']['encounter_id'] = $search_result['EncounterRos']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['EncounterRos']['encounter_id'] = $encounter_id;
		}
		
		$data['EncounterRos']['template'] = $template_id;
		$data['EncounterRos']['ros'] = '[]';
		$data['EncounterRos']['comments'] = '[]';
		
		$this->save($data);
		
		//post save operations
		$body_systems = $this->ReviewOfSystemTemplate->getDisplayTemplate($template_id);
		$default_negative = $this->ReviewOfSystemTemplate->isDefaultNegative($template_id);
		$system_nagative = $this->isSystemNegative($encounter_id);
		
		if($default_negative)
		{
			foreach($body_systems['ReviewOfSystemCategory'] as $body_system)
			{
				foreach($body_system['ReviewOfSystemSymptom'] as $ros_symptom)
				{
					$this->updateItem($body_system['category_name'], $ros_symptom['symptom'], '-', $encounter_id);
				}
			}
		}
		
		if($system_nagative)
		{
			$this->bindModel(array('hasMany' => array('EncounterChiefComplaint')));
			$cc_list = $this->EncounterChiefComplaint->getAllItems($encounter_id);
			$this->unbindModel(array('hasMany' => array('EncounterChiefComplaint')));
			
			foreach($body_systems['ReviewOfSystemCategory'] as $body_system)
			{
				foreach($body_system['ReviewOfSystemSymptom'] as $ros_symptom)
				{
					$init_val = '-';
			
					if(in_array($ros_symptom['symptom'], $cc_list))
					{
						$init_val = '+';
					}
			
					$this->updateItem($body_system['category_name'], $ros_symptom['symptom'], $init_val, $encounter_id);
				}
			}
		}
	}
	
	public function updateItem($section, $item_value, $sign, $encounter_id)
	{
		$data = array();
		$ros_arr = array();
		
		$search_result = $this->searchItem($encounter_id,$fields=array('ros','encounter_id','review_of_system_id'));
		
		if($search_result)
		{
			$data['EncounterRos']['review_of_system_id'] = $search_result['EncounterRos']['review_of_system_id'];
			$data['EncounterRos']['encounter_id'] = $search_result['EncounterRos']['encounter_id'];
			$ros_arr = json_decode($search_result['EncounterRos']['ros'], true);
		}
		else
		{
			$this->create();
			$data['EncounterRos']['encounter_id'] = $encounter_id;
			$data['EncounterRos']['template'] = 1;
			$data['EncounterRos']['comments'] = '[]';
		}
		
		if(!isset($ros_arr[$section]))
		{
			$ros_arr[$section] = array();
		}
		
		if($sign == ' ' && isset($ros_arr[$section][$item_value]))
		{
			unset($ros_arr[$section][$item_value]);
		}
		else
		{
			$ros_arr[$section][$item_value] = $sign;
		}
		
		if(count($ros_arr[$section]) == 0)
		{
			$ros_arr = Set::remove($ros_arr, $section);
		}
		
		$data['EncounterRos']['ros'] = json_encode($ros_arr);
		$this->save($data);
	}
	
	public function setItemValue($encounter_id, $field, $value)
	{
		$search_result = $this->searchItem($encounter_id);
		
		$data = array();
		
		if($search_result)
		{
			$data['EncounterRos']['review_of_system_id'] = $search_result['EncounterRos']['review_of_system_id'];
			$data['EncounterRos']['encounter_id'] = $search_result['EncounterRos']['encounter_id'];
		}
		else
		{
			$this->create();
			$data['EncounterRos']['encounter_id'] = $encounter_id;
			$data['EncounterRos']['template'] = 1;
			$data['EncounterRos']['ros'] = '[]';
			$data['EncounterRos']['comments'] = '[]';
		}
		
		$data['EncounterRos'][$field] = $value;
		
		$this->save($data);
	}
	
	public function getItemValue($field, $encounter_id)
	{
		$search_result = $this->searchItem($encounter_id);
		
		if($search_result)
		{
			return @$search_result['EncounterRos'][$field];
		}
		else
		{
			return '';
		}
	}
	
	public function getComments($encounter_id)
	{
		$comments = $this->getItemValue('comments', $encounter_id);
		
		if($comments == '')
		{
			$comments = '[]';
		}
		
		return json_decode($comments, true);
	}
	
	public function addComment($encounter_id, $body_system, $comment)
	{
		$data = array();
		$comments_arr = array();
		
		$search_result = $this->searchItem($encounter_id);
		
		if($search_result)
		{
			$data['EncounterRos']['review_of_system_id'] = $search_result['EncounterRos']['review_of_system_id'];
			$data['EncounterRos']['encounter_id'] = $search_result['EncounterRos']['encounter_id'];
			
			if($search_result['EncounterRos']['comments'] == '')
			{
				$search_result['EncounterRos']['comments'] = '[]';
			}
			
			$comments_arr = json_decode($search_result['EncounterRos']['comments'], true);
		}
		else
		{
			$this->create();
			$data['EncounterRos']['encounter_id'] = $encounter_id;
			$data['EncounterRos']['template'] = 1;
			$data['EncounterRos']['ros'] = '[]';
		}
		
		$comments_arr[$body_system] = $comment;
		
		$data['EncounterRos']['comments'] = json_encode($comments_arr);
		$this->save($data);
	}
	
	public function execute(&$controller, $encounter_id, $patient_id, $task, $user_id)
	{
		switch ($task)
        {
			case "add_comments":
			{
				$comment = trim(__strip_tags($controller->data['submitted']['value']));
				$this->addComment($encounter_id, $controller->data['body_system'], $comment);
				echo nl2br(trim(htmlentities($comment)));
				exit;
			} break;
            case "set_template":
            {
                if (!empty($controller->data))
                {
                    $this->setTemplate($controller->data['template_id'], $encounter_id, $patient_id, $user_id);
                }
            }
            case "updateSystemNegative":
            {
                if (!empty($controller->data))
                {
                    if (!empty($controller->data['submitted']['id']))
                    {
                        if ($controller->data['submitted']['id'] == 'system_negative')
                        {
                            $system_nagative_val = (int)@$controller->data['submitted']['value'];
                            $this->setItemValue($encounter_id, $controller->data['submitted']['id'], $system_nagative_val);
                            // disabled
                           // $template_to_use = $this->getTemplate($encounter_id);
                          //  $this->setTemplate($template_to_use['template_id'], $encounter_id);
                        }
                    }
                }
            }
            case "get_list":
            {
                $template_to_use = $this->getTemplate($encounter_id);
                $selected_items = $this->getAllItems($encounter_id);
                $default_negative = $controller->ReviewOfSystemTemplate->isDefaultNegative($template_to_use['template_id']);
                $system_nagative = $this->isSystemNegative($encounter_id);
				$comments = $this->getComments($encounter_id);
                
                if ($system_nagative)
                {
                    $controller->loadModel("EncounterChiefComplaint");
                    $cc_list = $controller->EncounterChiefComplaint->getAllItems($encounter_id);
                }
		  		
				$ros_data = $controller->ReviewOfSystemTemplate->getDisplayData($template_to_use['template_id'], $selected_items, $comments);
                // filter ros depends on patient gender for Gu
				if(!empty($ros_data)) {
					$controller->loadModel('PatientDemographic');
					$gender = $controller->PatientDemographic->field('gender', array('patient_id' =>$patient_id));
					$male_gu_ROS   = array('impotence');
					$female_gu_ROS = array('painful menstruation','painful sexual intercourse','vaginal bleeding','vaginal discharge','vaginal dryness','vaginal odor','vaginal pain','vaginal itching');
					if($gender=='M')
						$filterElem = $female_gu_ROS;
					else 
						$filterElem = $male_gu_ROS;
					$tmp = array();
					foreach($ros_data as $key1 => $ros_each_data) {
						if(strtolower($ros_each_data['description'])=='gu') {
							foreach($ros_each_data['details'] as $key2 => $element) {
								if(!in_array(strtolower($element['data']), $filterElem)) 
									$tmp[] = $ros_data[$key1]['details'][$key2];
							}
							$ros_data[$key1]['details'] = $tmp;
							break;
						}
					}
				}
				
				$ret = array();
                $ret['ros_data'] = $ros_data;
                
                echo json_encode($ret);
                
                exit;
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $this->updateItem($controller->data['section'], $controller->data['item_value'], $controller->data['sign'], $encounter_id);
                    
                    $data = array();
                    echo json_encode($data);
                }
                
                exit;
            }
            break;
            default:
            {
				$controller->loadModel("UserGroup");
				$providerRoles = $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK);
		
				$controller->loadModel("PracticeProfile");
				$PracticeProfile = $controller->PracticeProfile->find('first');
		
				$controller->ReviewOfSystemTemplate->recursive = 0;
		
				if (in_array($controller->Session->read('UserAccount.role_id'), $providerRoles))
				{
					$templates = $controller->ReviewOfSystemTemplate->find('all', array(
						'conditions' => array(
							'AND' => array(
								'OR' => array(
									'AND' => array(
										'OR' => array(
											'AND' => array(
												'ReviewOfSystemTemplate.user_id' => 0,
												'ReviewOfSystemTemplate.template_id' => 1,
											),
											'ReviewOfSystemTemplate.user_id' => array($controller->user_id)
										),
										'ReviewOfSystemTemplate.share' => 'No'
									), 
									'ReviewOfSystemTemplate.share' => 'Yes'
								), 
								'ReviewOfSystemTemplate.type_of_practice' => array(
									'', 
									$PracticeProfile['PracticeProfile']['type_of_practice']
								), 
								'ReviewOfSystemTemplate.show' => 'Yes'
							)
						)
					));
					$controller->set("templates", $controller->sanitizeHTML($templates));
				}
				else
				{
					$templates = $controller->ReviewOfSystemTemplate->find('all', array(
						'conditions' => array(
							'AND' => array(
								'OR' => array(
									'AND' => array(
										'ReviewOfSystemTemplate.user_id' => 0,
										'ReviewOfSystemTemplate.template_id' => 1,
									),
									'ReviewOfSystemTemplate.user_id' => array($controller->user_id)
								),
								'ReviewOfSystemTemplate.show' => 'Yes', 
								'ReviewOfSystemTemplate.type_of_practice' => array('', $PracticeProfile['PracticeProfile']['type_of_practice']), 
								'ReviewOfSystemTemplate.share' => 'No'
							)
						)
					));
					$controller->set("templates", $controller->sanitizeHTML($templates));
				}

                $controller->ReviewOfSystemTemplate->recursive = 0;
                //$controller->set("templates", $controller->sanitizeHTML($controller->ReviewOfSystemTemplate->find('all')));
                
                $template_to_use = $this->getTemplate($encounter_id, $patient_id);
                $controller->set("template_to_use", $controller->sanitizeHTML($template_to_use));
                
                $system_negative = $this->getItemValue('system_negative', $encounter_id);
                $controller->set("system_negative", $system_negative);
            }
        }
	}
}

?>
