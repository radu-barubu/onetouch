<?php

class PhysicalExamTemplate extends AppModel
{

    public $name = 'PhysicalExamTemplate';
    public $primaryKey = 'template_id';
    public $useTable = 'physical_exam_templates';
    public $actsAs = array('Containable');
    public $hasMany = array(
        'PhysicalExamBodySystem' => array(
            'className' => 'PhysicalExamBodySystem',
            'foreignKey' => 'template_id'
        )
    );

    public function beforeSave($options)
    {
        $this->data['PhysicalExamTemplate']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['PhysicalExamTemplate']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
        return true;
    }
	
	/**
    * Export template(s) to json data
    * 
    * @param array $template_ids List of template_id to be exported
	* @param bool $return_json Set to true if want function to return json encoded data
    * @return null
    */
    public function export_templates($template_ids = array(), $return_json = false)
    {
        $this->recursive = -1;
        $templates = $this->find('all', array(
            'contain' => array
                (
                'PhysicalExamBodySystem' => array
                    (
                    'fields' => array('body_system', 'order', 'enable'),
					'order' => array('body_system_id'),
                    'PhysicalExamElement' => array
                        (
                        'fields' => array('element', 'order', 'enable'),
						'order' => array('element_id'),
                        'PhysicalExamSubElement' => array
                            (
                            'fields' => array('sub_element', 'order', 'enable'),
							'order' => array('sub_element_id'),
                            'PhysicalExamObservation' => array
                            (
                                'fields' => array('observation', 'normal', 'normal_selected', 'order', 'enable'),
								'order' => array('observation_id'),
                                'PhysicalExamSpecifier' => array('fields' => array('specifier', 'preselect_flag', 'order', 'enable'))
                            )
                        ),
                        'PhysicalExamObservation' => array
                            (
                            'fields' => array('observation', 'normal', 'normal_selected', 'order', 'enable'),
							'order' => array('observation_id'),
                            'PhysicalExamSpecifier' => array('fields' => array('specifier', 'preselect_flag', 'order', 'enable'))
                        )
                    )
                )
            ),
            'conditions' => array('template_id' => $template_ids),
            'fields' => array('template_name', 'type_of_practice', 'show', 'share', 'default_negative')
        ));
		
        $name = '';
        $names = array();
        
        foreach($templates as $template)
        {
            $names[] = $template['PhysicalExamTemplate']['template_name'];
        }
        
        $name = implode("_", $names);
		
		for ($i = 0; $i < count($templates); ++$i)
		{
			unset($templates[$i]['PhysicalExamTemplate']['template_id']);
			for ($j = 0; $j < count($templates[$i]['PhysicalExamBodySystem']); ++$j)
			{
				unset($templates[$i]['PhysicalExamBodySystem'][$j]['body_system_id']);
				unset($templates[$i]['PhysicalExamBodySystem'][$j]['template_id']);
				for ($k = 0; $k < count($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement']); ++$k)
				{
					unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['element_id']);
					unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['body_system_id']);
					for ($l = 0; $l < count($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamSubElement']); ++$l)
					{
						unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamSubElement'][$l]['sub_element_id']);
						unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamSubElement'][$l]['element_id']);
						for ($m = 0; $m < count($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamSubElement'][$l]['PhysicalExamObservation']); ++$m)
						{
							unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamSubElement'][$l]['PhysicalExamObservation'][$m]['observation_id']);
							unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamSubElement'][$l]['PhysicalExamObservation'][$m]['sub_element_id']);
							for ($n = 0; $n < count($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamSubElement'][$l]['PhysicalExamObservation'][$m]['PhysicalExamSpecifier']); ++$n)
							{
								unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamSubElement'][$l]['PhysicalExamObservation'][$m]['PhysicalExamSpecifier'][$n]['specifier_id']);
								unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamSubElement'][$l]['PhysicalExamObservation'][$m]['PhysicalExamSpecifier'][$n]['observation_id']);
							}
						}
					}
					for ($l = 0; $l < count($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamObservation']); ++$l)
					{
						unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamObservation'][$l]['observation_id']);
						unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamObservation'][$l]['element_id']);
						for ($m = 0; $m < count($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamObservation'][$l]['PhysicalExamSpecifier']); ++$m)
						{
							unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamObservation'][$l]['PhysicalExamSpecifier'][$m]['specifier_id']);
							unset($templates[$i]['PhysicalExamBodySystem'][$j]['PhysicalExamElement'][$k]['PhysicalExamObservation'][$l]['PhysicalExamSpecifier'][$m]['observation_id']);
						}
					}
				}
			}
		}

        $templates_json = json_encode($templates);
        
		if($return_json)
		{
			return $templates_json;
		}
		else
		{
			$filename = $name.".ottf";
			$fp = fopen('php://output', 'w');
			header('Content-type: text/plain');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			fwrite($fp, $templates_json);
			fclose($fp);
			exit;
		}
    }
	
	/**
    * Avoid duplicate template name for imported template(s)
    * 
    * @param string $name template name
    * @param int $start start number
	* @param bool $copy_mode specify whether copy mode is on
    * @return null
    */
    private function getNewTemplateName($name, $start = 0, $copy_mode = false)
    {
        $append = $start;
		
		if($copy_mode)
		{
			$name .= ' - Copy';	
		}
        
        if($append == 0)
        {
            $append = '';
        }
        else
        {
            $append = ' #'.$append;    
        }
        
        $new_template_name = $name . $append;
        
        $this->recursive = -1;
        $template = $this->find('first', array('conditions' => array('PhysicalExamTemplate.template_name' => $new_template_name)));
        
        if($template)
        {
            return $this->getNewTemplateName($name, ++$start);
        }
        else
        {
            return $new_template_name;    
        }
    }
	
	/**
    * Import template(s) from json text
    * 
    * @param string $json_string json source
	* @param bool $copy_mode specify whether copy mode is on
    * @return null
    */
    public function import_templates($json_string, $copy_mode = false)
    {

        $this->PhysicalExamBodySystem = ClassRegistry::init('PhysicalExamBodySystem');
        $this->PhysicalExamElement = ClassRegistry::init('PhysicalExamElement');
		$this->PhysicalExamSubElement = ClassRegistry::init('PhysicalExamSubElement');
		$this->PhysicalExamObservation = ClassRegistry::init('PhysicalExamObservation');
		$this->PhysicalExamSpecifier = ClassRegistry::init('PhysicalExamSpecifier');
		
		if (json_decode($json_string, true) == NULL)
		{
			echo json_encode("ERROR");
			exit;
		}

		$templates = json_decode($json_string, true);

        foreach($templates as $template)
        {
        		if( !array_key_exists('PhysicalExamTemplate', $template) ){
							echo json_encode("ERROR");
							exit;
        		}
            $data = array();
            $data['PhysicalExamTemplate']['user_id'] = $_SESSION['UserAccount']['user_id'];
            $data['PhysicalExamTemplate']['template_name'] = $this->getNewTemplateName($template['PhysicalExamTemplate']['template_name'], 0, $copy_mode);
            $data['PhysicalExamTemplate']['type_of_practice'] = $template['PhysicalExamTemplate']['type_of_practice'];
            $data['PhysicalExamTemplate']['show'] = $template['PhysicalExamTemplate']['show'];
            $data['PhysicalExamTemplate']['share'] = $template['PhysicalExamTemplate']['share'];
            $data['PhysicalExamTemplate']['default_negative'] = $template['PhysicalExamTemplate']['default_negative'];
            $this->create();
            $this->save($data);
            $template_id = $this->getLastInsertId();
            
            //copy body systems
			foreach ($template['PhysicalExamBodySystem'] as $body_system)
			{
				$data = array();
				$data['PhysicalExamBodySystem']['template_id'] = $template_id;
				$data['PhysicalExamBodySystem']['body_system'] = $body_system['body_system'];
				$data['PhysicalExamBodySystem']['order'] = $body_system['order'];
				$data['PhysicalExamBodySystem']['enable'] = $body_system['enable'];
	
				$this->PhysicalExamBodySystem->create();
				$this->PhysicalExamBodySystem->save($data);
				$body_system_id = $this->PhysicalExamBodySystem->getLastInsertId();
	
				//copy elements
				foreach ($body_system['PhysicalExamElement'] as $element)
				{
					$data = array();
					$data['PhysicalExamElement']['body_system_id'] = $body_system_id;
					$data['PhysicalExamElement']['element'] = $element['element'];
					$data['PhysicalExamElement']['order'] = $element['order'];
					$data['PhysicalExamElement']['enable'] = $element['enable'];
	
					$this->PhysicalExamElement->create();
					$this->PhysicalExamElement->save($data);
					$element_id = $this->PhysicalExamElement->getLastInsertId();
	
					//copy sub elements
					foreach ($element['PhysicalExamSubElement'] as $sub_element)
					{
						$data = array();
						$data['PhysicalExamSubElement']['element_id'] = $element_id;
						$data['PhysicalExamSubElement']['sub_element'] = $sub_element['sub_element'];
						$data['PhysicalExamSubElement']['order'] = $sub_element['order'];
						$data['PhysicalExamSubElement']['enable'] = $sub_element['enable'];
	
						$this->PhysicalExamSubElement->create();
						$this->PhysicalExamSubElement->save($data);
						$sub_element_id = $this->PhysicalExamSubElement->getLastInsertId();
	
						//copy observations
						$observation_index = 0;
						foreach ($sub_element['PhysicalExamObservation'] as $observation)
						{
							$observation_index++;
							$data = array();
							$data['PhysicalExamObservation']['element_id'] = 0;
							$data['PhysicalExamObservation']['sub_element_id'] = $sub_element_id;
							$data['PhysicalExamObservation']['observation'] = $observation['observation'];
							$data['PhysicalExamObservation']['normal'] = $observation['normal'];
							$data['PhysicalExamObservation']['normal_selected'] = $observation['normal_selected'];
							$data['PhysicalExamObservation']['order'] = $observation['order'];
							$data['PhysicalExamObservation']['enable'] = $observation['enable'];
							
							$this->PhysicalExamObservation->create();
							$this->PhysicalExamObservation->save($data);
							$observation_id = $this->PhysicalExamObservation->getLastInsertId();
	
							//copy specifiers
							foreach ($observation['PhysicalExamSpecifier'] as $specifier)
							{
								$data = array();
								$data['PhysicalExamSpecifier']['observation_id'] = $observation_id;
								$data['PhysicalExamSpecifier']['specifier'] = $specifier['specifier'];
								$data['PhysicalExamSpecifier']['preselect_flag'] = $specifier['preselect_flag'];
								$data['PhysicalExamSpecifier']['order'] = $specifier['order'];
								$data['PhysicalExamSpecifier']['enable'] = $specifier['enable'];
	
								$this->PhysicalExamSpecifier->create();
								$this->PhysicalExamSpecifier->save($data);
							}
						}
					}
	
					//copy observations
					$observation_index = 0;
					foreach ($element['PhysicalExamObservation'] as $observation)
					{
						$observation_index++;
						$data = array();
						$data['PhysicalExamObservation']['element_id'] = $element_id;
						$data['PhysicalExamObservation']['sub_element_id'] = 0;
						$data['PhysicalExamObservation']['observation'] = $observation['observation'];
						$data['PhysicalExamObservation']['normal'] = $observation['normal'];
						$data['PhysicalExamObservation']['order'] = $observation['order'];
						$data['PhysicalExamObservation']['normal_selected'] = $observation['normal_selected'];
						$data['PhysicalExamObservation']['enable'] = $observation['enable'];
	
						$this->PhysicalExamObservation->create();
						$this->PhysicalExamObservation->save($data);
						$observation_id = $this->PhysicalExamObservation->getLastInsertID();
	
						//copy specifiers
						foreach ($observation['PhysicalExamSpecifier'] as $specifier)
						{
							$data = array();
							$data['PhysicalExamSpecifier']['observation_id'] = $observation_id;
							$data['PhysicalExamSpecifier']['specifier'] = $specifier['specifier'];
							$data['PhysicalExamSpecifier']['preselect_flag'] = $specifier['preselect_flag'];
							$data['PhysicalExamSpecifier']['order'] = $specifier['order'];
							$data['PhysicalExamSpecifier']['enable'] = $specifier['enable'];
	
							$this->PhysicalExamSpecifier->create();
							$this->PhysicalExamSpecifier->save($data);
						}
					}
				}
			}
        }
    }
	
	/**
    * Duplicate template
    * 
    * @param int $template_id template identifier (template to copy)
    * @return null
    */
    public function duplicateTemplate($template_ids)
    {
        $this->import_templates($this->export_templates($template_ids, true), true);
    }

    public function getTemplateName($template_id)
    {
        $item = $this->find('first', array('conditions' => array('PhysicalExamTemplate.template_id' => $template_id)));

        if ($item)
        {
            return $item['PhysicalExamTemplate']['template_name'];
        }
        else
        {
            return 'General';
        }
    }

    public function getDefaultNegative($template_id)
    {
        $item = $this->find('first', array('conditions' => array('PhysicalExamTemplate.template_id' => $template_id)));

        if ($item)
        {
            return $item['PhysicalExamTemplate']['default_negative'];
        }
        else
        {
            return 0;
        }
    }

    public function getDefaultTemplate()
    {
		App::import('Model','UserGroup');
		$UserGroup= new UserGroup();
		$providerRoles = $UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK);

		App::import('Model','PracticeProfile');
		$PracticeProfile= new PracticeProfile();
		$PracticeProfile = $PracticeProfile->find('first');

        $user_id = $_SESSION['UserAccount']['user_id'];

        App::import('Model','UserAccount');
		$UserAccount= new UserAccount();
		$item = $UserAccount->find(
			'first', array(
			'fields' => array('UserAccount.default_template_pe'),
			'conditions' => array('UserAccount.user_id' => $user_id)
			)
		);

		if (in_array($_SESSION['UserAccount']['role_id'], $providerRoles))
		{
			$item = $this->find('first', array('conditions' => array('AND' => array('OR' => array('AND' => array('PhysicalExamTemplate.user_id' => $user_id, 'PhysicalExamTemplate.share' => 'No'), 'PhysicalExamTemplate.share' => 'Yes'), 'PhysicalExamTemplate.template_id' => $item['UserAccount']['default_template_pe'], 'PhysicalExamTemplate.type_of_practice' => array('', $PracticeProfile['PracticeProfile']['type_of_practice']), 'PhysicalExamTemplate.show' => 'Yes'))));
		}
		else
		{
			$item = $this->find('first', array('conditions' => array('AND' => array('PhysicalExamTemplate.user_id' => $user_id, 'PhysicalExamTemplate.template_id' => $item['UserAccount']['default_template_pe'], 'PhysicalExamTemplate.type_of_practice' => array('', $PracticeProfile['PracticeProfile']['type_of_practice']), 'PhysicalExamTemplate.show' => 'Yes', 'PhysicalExamTemplate.share' => 'No'))));
		}

        if ($item)
        {
            return $item['PhysicalExamTemplate']['template_id'];
        }
        else
        {
            return 1;
        }
    }

	public function setDefaultTemplate($template_id, $user_id)
    {
        App::import('Model','UserAccount');
		$UserAccount= new UserAccount();
		$data['UserAccount']['default_template_pe'] = $template_id;
		$data['UserAccount']['user_id'] = $user_id;
		$UserAccount->save($data);
    }

    public function GetTemplate($template_id)
    {
        $this->recursive = 0;
        $template = $this->find('first', array(
            'contain' => array
                (
                'PhysicalExamBodySystem' => array
                    (
                    'fields' => array('body_system', 'order', 'enable'),
					'order' => array('order'),
                    'PhysicalExamElement' => array
                        (
                        'fields' => array('element', 'order', 'enable'),
						'order' => array('order'),
                        'PhysicalExamSubElement' => array
                            (
                            'fields' => array('sub_element', 'order', 'enable'),
							'order' => array('order'),
                            'PhysicalExamObservation' => array
                            (
                                'fields' => array('observation', 'normal', 'normal_selected', 'order', 'enable'),
								'order' => array('order'),
                                'PhysicalExamSpecifier' => array('fields' => array('specifier', 'preselect_flag', 'order', 'enable'), 'order' => array('order'))
                            )
                        ),
                        'PhysicalExamObservation' => array
                            (
                            'fields' => array('observation', 'normal', 'normal_selected', 'order', 'enable'),
							'order' => array('order'),
                            'PhysicalExamSpecifier' => array('fields' => array('specifier', 'preselect_flag', 'order', 'enable'), 'order' => array('order'))
                        )
                    )
                )
            ),
            'conditions' => array('template_id' => $template_id),
            'fields' => array('template_name')
            ));
        return $template;
    }

    public function getTemplateData($template_id)
    {
			
        $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
        $cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
	
				Cache::set(array('duration' => '+1 year'));
        $templateData = Cache::read($cache_file_prefix. $template_id . '_' .'template_data');				
			
				if ($templateData) {
					return $templateData;
				}
			
        $templateData =  $this->find('first', array
                (
                'contain' => array
                    (
                    'PhysicalExamBodySystem' => array
                        (
                        'conditions' => array
                        (
                            'PhysicalExamBodySystem.enable' => 1
                        ),
						'order' => array("order"),
                        'fields' => array
                        (
                            'body_system'
                        ),
                        'PhysicalExamElement' => array
                            (
                            'conditions' => array
                            (
                                'PhysicalExamElement.enable' => 1
                            ),
							'order' => array("order"),
                            'fields' => array
                            (
                                'element'
                            ),
                            'PhysicalExamSubElement' => array
                                (
                                'conditions' => array
                                (
                                    'PhysicalExamSubElement.enable' => 1
                                ),
								'order' => array("order"),
                                'fields' => array
                                    (
                                    'sub_element'
                                ),
                                'PhysicalExamObservation' => array
                                    (
                                    'conditions' => array
                                        (
                                        'PhysicalExamObservation.enable' => 1
                                    ),
									'order' => array("order"),
                                    'fields' => array
                                        (
                                        'observation', 'normal', 'normal_selected'
                                    ),
                                    'PhysicalExamSpecifier' => array
                                        (
                                        'conditions' => array
                                            (
                                            'PhysicalExamSpecifier.enable' => 1
                                        ),
										'order' => array("order"),
                                        'fields' => array
                                            (
                                            'specifier', 'preselect_flag'
                                        )
                                    )
                                )
                            ),
                            'PhysicalExamObservation' => array
                                (
                                'conditions' => array
                                    (
                                    'PhysicalExamObservation.enable' => 1
                                ),
								'order' => array("order"),
                                'fields' => array
                                    (
                                    'observation', 'normal', 'normal_selected'
                                ),
                                'PhysicalExamSpecifier' => array
                                    (
                                    'conditions' => array
                                        (
                                        'PhysicalExamSpecifier.enable' => 1
                                    ),
									'order' => array("order"),
                                    'fields' => array
                                        (
                                        'specifier', 'preselect_flag'
                                    )
                                )
                            )
                        )
                    )
                ),
                'conditions' => array
                    (
                    'template_id' => $template_id
                ),
                'fields' => array
                    (
                    'template_name', 'default_negative'
                )
                )
        );
				
				
        Cache::set(array('duration' => '+1 year'));
        Cache::write($cache_file_prefix. $template_id . '_' .'template_data', $templateData);							
				
				return $templateData;
    }

    public function getEncounterPEData(&$controller, $template_to_use, $all_data)
    {
		$controller->loadModel("PhysicalExamSubElement");
		$controller->loadModel("PhysicalExamObservation");
		$controller->loadModel("PhysicalExamSpecifier");

        $this->recursive = 0;
        $all_data['pe_data'] = $this->getTemplateData($template_to_use['template_id']);

        if (is_array($all_data['pe_saved_data']['EncounterPhysicalExamText']))
        {
            $physical_exam_texts = $all_data['pe_saved_data']['EncounterPhysicalExamText'];

            for ($i = 0; $i < count($physical_exam_texts); $i++)
            {
                switch ($physical_exam_texts[$i]['text_type'])
                {
                    case "subelement":
                        {
                            $parent_ids = $controller->PhysicalExamSubElement->getParent($physical_exam_texts[$i]['item_id']);

                            $physical_exam_texts[$i]['element_id'] = $parent_ids['element_id'];
                            $physical_exam_texts[$i]['sub_element_id'] = $parent_ids['sub_element_id'];
                        }
                        break;
                    case "observation":
                        {
                            $parent_ids = $controller->PhysicalExamObservation->getParent($physical_exam_texts[$i]['item_id']);

                            $physical_exam_texts[$i]['element_id'] = $parent_ids['element_id'];
                            $physical_exam_texts[$i]['sub_element_id'] = $parent_ids['sub_element_id'];
                        }
                        break;
                    case "specifier":
                        {
                            $parent_ids = $controller->PhysicalExamSpecifier->getParent($physical_exam_texts[$i]['item_id']);

                            $physical_exam_texts[$i]['element_id'] = $parent_ids['element_id'];
                            $physical_exam_texts[$i]['sub_element_id'] = $parent_ids['sub_element_id'];
                        }
                }

                $all_data['pe_saved_data']['EncounterPhysicalExamText'][$i] = $physical_exam_texts[$i];
            }
        }

        return $all_data;
    }
    
    public function resetSubTemplateData($controller, $template_id)
    {
        if($template_id == 1)
        {
            return;
        }
        
        $this->recursive = 0;
        
        //delete data
        $template = $this->find('first', array(
        'contain' => array
            (
            'PhysicalExamBodySystem' => array
                (
                'fields' => array('body_system_id'),
                'PhysicalExamElement' => array
                    (
                    'fields' => array('element_id'),
                    'PhysicalExamSubElement' => array
                        (
                        'fields' => array('sub_element_id'),
                        'PhysicalExamObservation' => array
                            (
                            'fields' => array('observation_id'),
                            'PhysicalExamSpecifier' => array('fields' => array('specifier_id'))
                        )
                    ),
                    'PhysicalExamObservation' => array
                        (
                        'fields' => array('observation_id'),
                        'PhysicalExamSpecifier' => array('fields' => array('specifier_id'))
                    )
                )
            )
        ),
        'conditions' => array('PhysicalExamTemplate.template_id' => $template_id),
        'fields' => array('template_id')
        ));
        
        //delete body system
        foreach ($template['PhysicalExamBodySystem'] as $body_system)
        {
            //delete elements
            foreach ($body_system['PhysicalExamElement'] as $element)
            {
                //delete sub elements
                foreach ($element['PhysicalExamSubElement'] as $sub_element)
                {
                    //delete observations
                    foreach ($sub_element['PhysicalExamObservation'] as $observation)
                    {
                        //delete specifiers
                        foreach ($observation['PhysicalExamSpecifier'] as $specifier)
                        {
                            $specifier_id = $specifier['specifier_id'];
                            $controller->PhysicalExamSpecifier->delete($specifier_id);
                        }
                        
                        $observation_id = $observation['observation_id'];
                        $controller->PhysicalExamObservation->delete($observation_id);
                    }
                    
                    $sub_element_id = $sub_element['sub_element_id'];
                    $controller->PhysicalExamSubElement->delete($sub_element_id);
                }

                //delete observations
                foreach ($element['PhysicalExamObservation'] as $observation)
                {
                    //delete specifiers
                    foreach ($observation['PhysicalExamSpecifier'] as $specifier)
                    {
                        $specifier_id = $specifier['specifier_id'];
                        $controller->PhysicalExamSpecifier->delete($specifier_id);
                    }
                    
                    $observation_id = $observation['observation_id'];
                    $controller->PhysicalExamObservation->delete($observation_id);
                }
                
                $element_id = $element['element_id'];
                $controller->PhysicalExamElement->delete($element_id);
            }
            
            $body_system_id = $body_system['body_system_id'];
            $controller->PhysicalExamBodySystem->delete($body_system_id);
        }
        
        //save data
        $template = $this->find('first', array(
        'contain' => array
            (
            'PhysicalExamBodySystem' => array
                (
                'fields' => array('body_system', 'order'),
				'order' => array('body_system_id'),
                'PhysicalExamElement' => array
                    (
                    'fields' => array('element', 'order'),
					'order' => array('element_id'),
                    'PhysicalExamSubElement' => array
                        (
                        'fields' => array('sub_element', 'order'),
						'order' => array('sub_element_id'),
                        'PhysicalExamObservation' => array
                            (
                            'fields' => array('observation', 'normal', 'order'),
							'order' => array('observation_id'),
                            'PhysicalExamSpecifier' => array('fields' => array('specifier', 'order'))
                        )
                    ),
                    'PhysicalExamObservation' => array
                        (
                        'fields' => array('observation', 'normal', 'order'),
						'order' => array('observation_id'),
                        'PhysicalExamSpecifier' => array('fields' => array('specifier', 'order'))
                    )
                )
            )
        ),
        'conditions' => array('user_id' => 0),
        'fields' => array('template_name')
        ));


        //copy body systems
        foreach ($template['PhysicalExamBodySystem'] as $body_system)
        {
            $data = array();
            $data['PhysicalExamBodySystem']['template_id'] = $template_id;
            $data['PhysicalExamBodySystem']['body_system'] = $body_system['body_system'];
            $data['PhysicalExamBodySystem']['order'] = $body_system['order'];

            $controller->PhysicalExamBodySystem->create();
            $controller->PhysicalExamBodySystem->save($data);
            $body_system_id = $controller->PhysicalExamBodySystem->getLastInsertID();

            //copy elements
            foreach ($body_system['PhysicalExamElement'] as $element)
            {
                $data = array();
                $data['PhysicalExamElement']['body_system_id'] = $body_system_id;
                $data['PhysicalExamElement']['element'] = $element['element'];
                $data['PhysicalExamElement']['order'] = $element['order'];

                $controller->PhysicalExamElement->create();
                $controller->PhysicalExamElement->save($data);
                $element_id = $controller->PhysicalExamElement->getLastInsertID();

                //copy sub elements
                foreach ($element['PhysicalExamSubElement'] as $sub_element)
                {
                    $data = array();
                    $data['PhysicalExamSubElement']['element_id'] = $element_id;
                    $data['PhysicalExamSubElement']['sub_element'] = $sub_element['sub_element'];
                    $data['PhysicalExamSubElement']['order'] = $sub_element['order'];

                    $controller->PhysicalExamSubElement->create();
                    $controller->PhysicalExamSubElement->save($data);
                    $sub_element_id = $controller->PhysicalExamSubElement->getLastInsertID();

                    //copy observations
					$observation_index = 0;
                    foreach ($sub_element['PhysicalExamObservation'] as $observation)
                    {
						$observation_index++;
                        $data = array();
                        $data['PhysicalExamObservation']['element_id'] = 0;
                        $data['PhysicalExamObservation']['sub_element_id'] = $sub_element_id;
                        $data['PhysicalExamObservation']['observation'] = $observation['observation'];
                        $data['PhysicalExamObservation']['normal'] = $observation['normal'];
                        $data['PhysicalExamObservation']['order'] = $observation['order'];
						$data['PhysicalExamObservation']['normal_selected'] = 0;
						
						if($observation_index == 1)
						{
							$data['PhysicalExamObservation']['normal_selected'] = 1;
						}

                        $controller->PhysicalExamObservation->create();
                        $controller->PhysicalExamObservation->save($data);
                        $observation_id = $controller->PhysicalExamObservation->getLastInsertID();

                        //copy specifiers
                        foreach ($observation['PhysicalExamSpecifier'] as $specifier)
                        {
                            $data = array();
                            $data['PhysicalExamSpecifier']['observation_id'] = $observation_id;
                            $data['PhysicalExamSpecifier']['specifier'] = $specifier['specifier'];
                            $data['PhysicalExamSpecifier']['order'] = $specifier['order'];
							$data['PhysicalExamSpecifier']['preselect_flag'] = 0;

                            $controller->PhysicalExamSpecifier->create();
                            $controller->PhysicalExamSpecifier->save($data);
                        }
                    }
                }

                //copy observations
				$observation_index = 0;
                foreach ($element['PhysicalExamObservation'] as $observation)
                {
					$observation_index++;
                    $data = array();
                    $data['PhysicalExamObservation']['element_id'] = $element_id;
                    $data['PhysicalExamObservation']['sub_element_id'] = 0;
                    $data['PhysicalExamObservation']['observation'] = $observation['observation'];
                    $data['PhysicalExamObservation']['normal'] = $observation['normal'];
                    $data['PhysicalExamObservation']['order'] = $observation['order'];
					$data['PhysicalExamObservation']['normal_selected'] = 0;
						
					if($observation_index == 1)
					{
						$data['PhysicalExamObservation']['normal_selected'] = 1;
					}

                    $controller->PhysicalExamObservation->create();
                    $controller->PhysicalExamObservation->save($data);
                    $observation_id = $controller->PhysicalExamObservation->getLastInsertID();

                    //copy specifiers
                    foreach ($observation['PhysicalExamSpecifier'] as $specifier)
                    {
                        $data = array();
                        $data['PhysicalExamSpecifier']['observation_id'] = $observation_id;
                        $data['PhysicalExamSpecifier']['specifier'] = $specifier['specifier'];
                        $data['PhysicalExamSpecifier']['order'] = $specifier['order'];
						$data['PhysicalExamSpecifier']['preselect_flag'] = 0;

                        $controller->PhysicalExamSpecifier->create();
                        $controller->PhysicalExamSpecifier->save($data);
                    }
                }
            }
        }
    }

    public function executePETemplateManager(&$controller, $task)
    {
        $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
        $cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
	
				Cache::set(array('duration' => '+1 year'));
				
				// Any changes in the template, clears cache for that template
				
				$template_id = isset($controller->params['named']['template_id']) ? intval($controller->params['named']['template_id']) : 0 ;

				if ($template_id) {
					Cache::delete($cache_file_prefix. $template_id . '_' .'template_data');	
				} else {
					if (isset($controller->data['template_id']) || isset($controller->data['PhysicalExamTemplate']['template_id'])) {
						$template_id = isset($controller->data['template_id']) ? $controller->data['template_id'] : $controller->data['PhysicalExamTemplate']['template_id'];
						Cache::delete($cache_file_prefix. $template_id . '_' .'template_data');	
					}
				}				
				
        switch ($task)
        {
					
					case 'inline_edit' : {
						$mode = $controller->params['data']['submitted']['id'];
						$value = trim($controller->params['data']['submitted']['value']);
						
						
						$mode = explode('-', $mode);
						
						switch ($mode[0]) {
							
							case 'body_system':
								$controller->loadModel('PhysicalExamBodySystem');
								$controller->PhysicalExamBodySystem->id = $mode[1];
								$controller->PhysicalExamBodySystem->saveField('body_system', $value);
								break;

							case 'element':
								$controller->loadModel('PhysicalExamElement');
								$controller->PhysicalExamElement->id = $mode[1];
								$controller->PhysicalExamElement->saveField('element', $value);
								break;
							
							case 'subelement':
								$controller->loadModel('PhysicalExamSubElement');
								$controller->PhysicalExamSubElement->id = $mode[1];
								$controller->PhysicalExamSubElement->saveField('sub_element', $value);
								break;
							
							case 'observation':
								$controller->loadModel('PhysicalExamObservation');
								$controller->PhysicalExamObservation->id = $mode[1];
								$controller->PhysicalExamObservation->saveField('observation', $value);
								break;
							
							case 'specifier':
								$controller->loadModel('PhysicalExamSpecifier');
								$controller->PhysicalExamSpecifier->id = $mode[1];
								$controller->PhysicalExamSpecifier->saveField('specifier', $value);
								break;
							
							default: 
								break;
						}
						
						
						echo htmlentities($value);
						die();
					}
					break;
					
			case "export_templates":
            {
                $this->export_templates($controller->data['template_ids']);
                exit;
            } break;
            case "import_templates":
            {
                $ret = array();
                
                $file = $controller->paths['temp'] . $controller->data['filename'];
                $json_string = file_get_contents($file);
                $this->import_templates($json_string);
                echo json_encode($ret);
                exit;
            } break;
			case "duplicate_templates":
			{
				$this->duplicateTemplate($controller->data['template_ids']);
				$controller->Session->setFlash(__('Item(s) saved.', true));
                $controller->redirect(array('action' => 'pe_template'));
			} break;
			case "set_show_template":
			{
				$controller->data['PhysicalExamTemplate']['template_id'] = $controller->data['template_id'];
				if ($controller->data['set'] == 'true')
				{
					$controller->data['PhysicalExamTemplate']['show'] = "Yes";
				}
				else
				{
					$controller->data['PhysicalExamTemplate']['show'] = "No";
				}
				$this->save($controller->data);
				exit;
			} break;
			case "set_share_template":
			{
				$controller->data['PhysicalExamTemplate']['template_id'] = $controller->data['template_id'];
				if ($controller->data['set'] == 'true')
				{
					$controller->data['PhysicalExamTemplate']['share'] = "Yes";
				}
				else
				{
					$controller->data['PhysicalExamTemplate']['share'] = "No";
				}
				$this->save($controller->data);
				exit;
			} break;
            case "set_default_template":
			{
				if ($controller->data['set'] == 'false')
				{
					$controller->data['template_id'] = 0;
				}

				$this->setDefaultTemplate($controller->data['template_id'], $controller->user_id);
				exit;
			} break;
            case "get_template":
			{
				$template = $this->GetTemplate($controller->params['named']['template_id']);
				echo json_encode($template);
				exit;
			} break;
            case "addnew":
			{
				if (!empty($controller->data))
				{
					$this->create();
					$controller->data['PhysicalExamTemplate']['user_id'] = $controller->user_id;
					$controller->data['PhysicalExamTemplate']['default_negative'] = (int) @$controller->data['PhysicalExamTemplate']['default_negative'];

					if (!isset($controller->data['PhysicalExamTemplate']['show']))
					{
						$controller->data['PhysicalExamTemplate']['show'] = "No";
					}

					if (!isset($controller->data['PhysicalExamTemplate']['share']))
					{
						$controller->data['PhysicalExamTemplate']['share'] = "No";
					}

					$this->save($controller->data);
					$template_id = $this->getLastInsertID();
					
					$this->resetSubTemplateData(&$controller, $template_id);
					
					$controller->Session->setFlash(__('Item(s) added.', true));
					$controller->redirect(array('action' => 'pe_template', 'task' => 'edit', 'template_id' => $template_id));
				}
			} break;
            case "add_body_system":
			{
				$controller->data['PhysicalExamBodySystem']['template_id'] = $controller->data['template_id'];
				$controller->data['PhysicalExamBodySystem']['body_system'] = $controller->data['body_system'];
				$controller->data['PhysicalExamBodySystem']['order'] = $controller->data['order'] + 1;
				$controller->PhysicalExamBodySystem->create();
				$controller->PhysicalExamBodySystem->save($controller->data);

				$template = $this->GetTemplate($controller->data['template_id']);
				echo json_encode($template);
				exit;
			} break;
            case "delete_body_system":
			{
				$element_id = $controller->PhysicalExamElement->find(
					'list', array(
					'fields' => array('element_id'),
					'conditions' => array('PhysicalExamElement.body_system_id' => $controller->data['body_system_id'])
					)
				);
				$sub_element_id = $controller->PhysicalExamSubElement->find(
					'list', array(
					'fields' => array('sub_element_id'),
					'conditions' => array('PhysicalExamSubElement.element_id' => $element_id)
					)
				);
				$observation_id = $controller->PhysicalExamObservation->find(
					'list', array(
					'fields' => array('observation_id'),
					'conditions' => array('OR' => array('PhysicalExamObservation.element_id' => $element_id, 'PhysicalExamObservation.sub_element_id' => $sub_element_id))
					)
				);

				$controller->PhysicalExamBodySystem->delete($controller->data['body_system_id'], false);
				$controller->PhysicalExamElement->deleteAll(array('PhysicalExamElement.body_system_id' => $controller->data['body_system_id']));
				$controller->PhysicalExamSubElement->deleteAll(array('PhysicalExamSubElement.element_id' => $element_id));
				$controller->PhysicalExamObservation->deleteAll(array('OR' => array('PhysicalExamObservation.element_id' => $element_id, 'PhysicalExamObservation.sub_element_id' => $sub_element_id)));
				$controller->PhysicalExamSpecifier->deleteAll(array('PhysicalExamSpecifier.observation_id' => $observation_id));

				$template = $this->GetTemplate($controller->data['template_id']);
				echo json_encode($template);
				exit;
			} break;
            case "add_element":
			{
				$controller->data['PhysicalExamElement']['body_system_id'] = $controller->data['body_system_id'];
				$controller->data['PhysicalExamElement']['element'] = $controller->data['element'];
				$controller->data['PhysicalExamElement']['order'] = $controller->data['order'] + 1;
				$controller->PhysicalExamElement->create();
				$controller->PhysicalExamElement->save($controller->data);

				$template = $this->GetTemplate($controller->data['template_id']);
				echo json_encode($template);
				exit;
			} break;
            case "delete_element":
			{
				$sub_element_id = $controller->PhysicalExamSubElement->find(
					'list', array(
					'fields' => array('sub_element_id'),
					'conditions' => array('PhysicalExamSubElement.element_id' => $controller->data['element_id'])
					)
				);
				$observation_id = $controller->PhysicalExamObservation->find(
					'list', array(
					'fields' => array('observation_id'),
					'conditions' => array('OR' => array('PhysicalExamObservation.element_id' => $controller->data['element_id'], 'PhysicalExamObservation.sub_element_id' => $sub_element_id))
					)
				);

				$controller->PhysicalExamElement->delete($controller->data['element_id'], false);
				$controller->PhysicalExamSubElement->deleteAll(array('PhysicalExamSubElement.element_id' => $controller->data['element_id']));
				$controller->PhysicalExamObservation->deleteAll(array('OR' => array('PhysicalExamObservation.element_id' => $controller->data['element_id'], 'PhysicalExamObservation.sub_element_id' => $sub_element_id)));
				$controller->PhysicalExamSpecifier->deleteAll(array('PhysicalExamSpecifier.observation_id' => $observation_id));

				$template = $this->GetTemplate($controller->data['template_id']);
				echo json_encode($template);
				exit;
			} break;
            case "add_sub_element":
			{
				$controller->data['PhysicalExamSubElement']['element_id'] = $controller->data['element_id'];
				$controller->data['PhysicalExamSubElement']['sub_element'] = $controller->data['sub_element'];
				$controller->data['PhysicalExamSubElement']['order'] = $controller->data['order'] + 1;
				$controller->PhysicalExamSubElement->create();
				$controller->PhysicalExamSubElement->save($controller->data);

				$template = $this->GetTemplate($controller->data['template_id']);
				echo json_encode($template);
				exit;
			} break;
            case "delete_sub_element":
			{
				$observation_id = $controller->PhysicalExamObservation->find(
					'list', array(
					'fields' => array('observation_id'),
					'conditions' => array('PhysicalExamObservation.sub_element_id' => $controller->data['sub_element_id'])
					)
				);

				$controller->PhysicalExamSubElement->delete($controller->data['sub_element_id'], false);
				$controller->PhysicalExamObservation->deleteAll(array('PhysicalExamObservation.sub_element_id' => $controller->data['sub_element_id']));
				$controller->PhysicalExamSpecifier->deleteAll(array('PhysicalExamSpecifier.observation_id' => $observation_id));

				$template = $this->GetTemplate($controller->data['template_id']);
				echo json_encode($template);
				exit;
			} break;
            case "add_observation":
			{
				
				// If a non-zero subelement_id was passed,
				// do not include element_id
				if (intval($controller->data['sub_element_id'])) {
					$controller->data['PhysicalExamObservation']['sub_element_id'] = $controller->data['sub_element_id'];
				} else {
					$controller->data['PhysicalExamObservation']['element_id'] = $controller->data['element_id'];
				} 				
				
				$controller->data['PhysicalExamObservation']['observation'] = $controller->data['observation'];
				$controller->data['PhysicalExamObservation']['normal'] = $controller->data['normal'];
				$controller->data['PhysicalExamObservation']['normal_selected'] = $controller->data['normal_selected'];
				$controller->data['PhysicalExamObservation']['order'] = $controller->data['order'] + 1;
				$controller->PhysicalExamObservation->create();
				$controller->PhysicalExamObservation->save($controller->data);

				$template = $this->GetTemplate($controller->data['template_id']);
				echo json_encode($template);
				exit;
			} break;
            case "delete_observation":
			{
				$controller->PhysicalExamObservation->delete($controller->data['observation_id'], false);
				$controller->PhysicalExamSpecifier->deleteAll(array('PhysicalExamSpecifier.observation_id' => $controller->data['observation_id']));

				$template = $this->GetTemplate($controller->data['template_id']);
				echo json_encode($template);
				exit;
			} break;
            case "add_specifier":
			{
				$controller->data['PhysicalExamSpecifier']['observation_id'] = $controller->data['observation_id'];
				$controller->data['PhysicalExamSpecifier']['specifier'] = $controller->data['specifier'];
				$controller->data['PhysicalExamSpecifier']['order'] = $controller->data['order'] + 1;
				$controller->PhysicalExamSpecifier->create();
				$controller->PhysicalExamSpecifier->save($controller->data);

				$template = $this->GetTemplate($controller->data['template_id']);
				echo json_encode($template);
				exit;
			} break;
            case "delete_specifier":
			{
				$controller->PhysicalExamSpecifier->delete($controller->data['specifier_id'], false);

				$template = $this->GetTemplate($controller->data['template_id']);
				echo json_encode($template);
				exit;
			} break;
			case "change_normal_selected":
			{
				$controller->PhysicalExamObservation->setNormalSelected($controller->data['observation_id'], $controller->data['normal_selected']);
				echo json_encode(array());
				exit;
			} break;
			case "change_specifier_normal_selected":
			{
				$controller->PhysicalExamSpecifier->setNormalSelected($controller->data['observation_id'], $controller->data['specifier_id'], $controller->data['preselect_flag']);
				echo json_encode(array());
				exit;
			} break;
            case "update_display":
			{
				switch ($controller->data['level'])
				{
					case "1" :
						{
							$controller->data['PhysicalExamBodySystem']['body_system_id'] = $controller->data['id'];
							$controller->data['PhysicalExamBodySystem']['enable'] = $controller->data['enable'];
							$controller->PhysicalExamBodySystem->save($controller->data);
						} break;
					case "2" :
						{
							$controller->data['PhysicalExamElement']['element_id'] = $controller->data['id'];
							$controller->data['PhysicalExamElement']['enable'] = $controller->data['enable'];
							$controller->PhysicalExamElement->save($controller->data);
						} break;
					case "3" :
						{
							$controller->data['PhysicalExamSubElement']['sub_element_id'] = $controller->data['id'];
							$controller->data['PhysicalExamSubElement']['enable'] = $controller->data['enable'];
							$controller->PhysicalExamSubElement->save($controller->data);
						} break;
					case "4" :
						{
							$controller->data['PhysicalExamObservation']['observation_id'] = $controller->data['id'];
							$controller->data['PhysicalExamObservation']['enable'] = $controller->data['enable'];
							$controller->PhysicalExamObservation->save($controller->data);
						} break;
					case "5" :
						{
							$controller->data['PhysicalExamSpecifier']['specifier_id'] = $controller->data['id'];
							$controller->data['PhysicalExamSpecifier']['enable'] = $controller->data['enable'];
							$controller->PhysicalExamSpecifier->save($controller->data);
						} break;
				}
				exit;
			} break;
            case "edit":
			{
				if (!empty($controller->data))
				{
					$template_id = $controller->data['PhysicalExamTemplate']['template_id'];
					
					if($controller->data['use_default'] == '1')
					{
						$this->resetSubTemplateData(&$controller, $template_id);
					}

					$controller->loadModel("UserAccount");
					$user = $controller->UserAccount->getCurrentUser($controller->user_id);
					if ($template_id == 1 && $user['role_id'] != EMR_Roles::SYSTEM_ADMIN_ROLE_ID)
					{
						$controller->redirect(array('action' => 'pe_template'));
					}

					$controller->data['PhysicalExamTemplate']['default_negative'] = (int) @$controller->data['PhysicalExamTemplate']['default_negative'];

					if (!isset($controller->data['PhysicalExamTemplate']['show']))
					{
						$controller->data['PhysicalExamTemplate']['show'] = "No";
					}

					if (!isset($controller->data['PhysicalExamTemplate']['share']))
					{
						$controller->data['PhysicalExamTemplate']['share'] = "No";
					}

					$this->save($controller->data);
					$controller->Session->setFlash(__('Item(s) saved.', true));
					$controller->redirect(array('action' => 'pe_template'));
				}
				else
				{
					$template_id = (isset($controller->params['named']['template_id'])) ? $controller->params['named']['template_id'] : "";

					$controller->loadModel("UserAccount");
					$user = $controller->UserAccount->getCurrentUser($controller->user_id);
					if ($template_id == 1 && $user['role_id'] != EMR_Roles::SYSTEM_ADMIN_ROLE_ID)
					{
						$controller->redirect(array('action' => 'pe_template'));
					}

					$items = $this->find(
						'first', array(
						'conditions' => array('PhysicalExamTemplate.template_id' => $template_id)
						)
					);

					$controller->set('EditItem', $controller->sanitizeHTML($items));
				}
			} break;
            case "delete":
			{
				if (!empty($controller->data))
				{
					$template_id = $controller->data['PhysicalExamTemplate']['template_id'];
					$delete_count = 0;

					foreach ($template_id as $template_id)
					{
						$template = $this->GetTemplate($template_id);
						$this->delete($template_id, false);
						foreach ($template['PhysicalExamBodySystem'] as $body_system)
						{
							$controller->PhysicalExamBodySystem->delete($body_system['body_system_id'], false);
							foreach ($body_system['PhysicalExamElement'] as $element)
							{
								$controller->PhysicalExamElement->delete($element['element_id'], false);
								foreach ($element['PhysicalExamSubElement'] as $sub_element)
								{
									$controller->PhysicalExamSubElement->delete($sub_element['sub_element_id'], false);
									foreach ($sub_element['PhysicalExamObservation'] as $observation)
									{
										$controller->PhysicalExamObservation->delete($observation['observation_id'], false);
										foreach ($observation['PhysicalExamSpecifier'] as $specifier)
										{
											$controller->PhysicalExamSpecifier->delete($specifier['specifier_id'], false);
										}
									}
								}
								foreach ($element['PhysicalExamObservation'] as $observation)
								{
									$controller->PhysicalExamObservation->delete($observation['observation_id'], false);
									foreach ($observation['PhysicalExamSpecifier'] as $specifier)
									{
										$controller->PhysicalExamSpecifier->delete($specifier['specifier_id'], false);
									}
								}
							}
						}
						$delete_count++;
					}

					if ($delete_count > 0)
					{
						$controller->Session->setFlash($delete_count . __('Item(s) deleted.', true));
					}
				}
				$controller->redirect(array('action' => 'pe_template'));
			} break;
			case 'change_order': {
				
				$id = $controller->params['form']['id'];
				$type = $controller->params['form']['type'];
				$direction = strtolower($controller->params['named']['move']);
				
				switch ($type) {
				
					case 'bodysystem': {
						$controller->loadModel('PhysicalExamBodySystem');
						if ($direction == 'up') {
							$controller->PhysicalExamBodySystem->moveUp($id);
						} else {
							$controller->PhysicalExamBodySystem->moveDown($id);
						}
						
					} break;

					case 'element': {
						$controller->loadModel('PhysicalExamElement');
						if ($direction == 'up') {
							$controller->PhysicalExamElement->moveUp($id);
						} else {
							$controller->PhysicalExamElement->moveDown($id);
						}
						
					} break;
					
					case 'subelement': {
						$controller->loadModel('PhysicalExamSubElement');
						if ($direction == 'up') {
							$controller->PhysicalExamSubElement->moveUp($id);
						} else {
							$controller->PhysicalExamSubElement->moveDown($id);
						}
						
					} break;

					case 'observation': {
						$controller->loadModel('PhysicalExamObservation');
						if ($direction == 'up') {
							$controller->PhysicalExamObservation->moveUp($id);
						} else {
							$controller->PhysicalExamObservation->moveDown($id);
						}
						
					} break;
					
					case 'specifier': {
						$controller->loadModel('PhysicalExamSpecifier');
						if ($direction == 'up') {
							$controller->PhysicalExamSpecifier->moveUp($id);
						} else {
							$controller->PhysicalExamSpecifier->moveDown($id);
						}
						
					} break;					
										
					default: 
						break;
				}
				
				
				die('Ok');
			} break;
            default:
			{
				$controller->set('PhysicalExamTemplate', $controller->sanitizeHTML($controller->paginate('PhysicalExamTemplate',  array('OR' => array('PhysicalExamTemplate.user_id' => $controller->user_id, 'PhysicalExamTemplate.share' => 'Yes')))));

				$item = $controller->UserAccount->find(
					'first', array(
					'fields' => array('UserAccount.default_template_pe'),
					'conditions' => array('UserAccount.user_id' => $controller->user_id)
					)
				);
				$controller->set('default_template_pe', $item['UserAccount']['default_template_pe']);
			} break;
        }
    }

}

?>
