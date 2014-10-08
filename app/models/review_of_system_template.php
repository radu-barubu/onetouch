<?php

class ReviewOfSystemTemplate extends AppModel 
{ 
	public $name = 'ReviewOfSystemTemplate'; 
	public $primaryKey = 'template_id';
	public $useTable = 'review_of_system_templates';
	public $actsAs = array('Containable');
	
	public $hasMany = array(
		'ReviewOfSystemCategory' => array(
			'className' => 'ReviewOfSystemCategory',
			'foreignKey' => 'template_id'
		)
	);
	
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
            'contain' => array(
                'ReviewOfSystemCategory' => array(
                    'fields' => array('category_name', 'order', 'enable'),
                    'order' => array('order'),
                    'ReviewOfSystemSymptom' => array('fields' => array('symptom', 'order', 'enable'), 'order' => array('order'))
                )
            ),
            'conditions' => array('template_id' => $template_ids),
            'fields' => array('template_name', 'type_of_practice', 'show', 'share', 'default_negative')
        ));
        
        $name = '';
        $names = array();
        
        foreach($templates as $template)
        {
            $names[] = $template['ReviewOfSystemTemplate']['template_name'];
        }
        
        $name = implode("_", $names);
		
		for ($i = 0; $i < count($templates); ++$i)
		{
			unset($templates[$i]['ReviewOfSystemTemplate']['template_id']);
			for ($j = 0; $j < count($templates[$i]['ReviewOfSystemCategory']); ++$j)
			{
				unset($templates[$i]['ReviewOfSystemCategory'][$j]['category_id']);
				unset($templates[$i]['ReviewOfSystemCategory'][$j]['template_id']);
				for ($k = 0; $k < count($templates[$i]['ReviewOfSystemCategory'][$j]['ReviewOfSystemSymptom']); ++$k)
				{
					unset($templates[$i]['ReviewOfSystemCategory'][$j]['ReviewOfSystemSymptom'][$k]['symptom_id']);
					unset($templates[$i]['ReviewOfSystemCategory'][$j]['ReviewOfSystemSymptom'][$k]['category_id']);
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
        $template = $this->find('first', array('conditions' => array('ReviewOfSystemTemplate.template_name' => $new_template_name)));
        
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
        $this->ReviewOfSystemCategory = ClassRegistry::init('ReviewOfSystemCategory');
        $this->ReviewOfSystemSymptom = ClassRegistry::init('ReviewOfSystemSymptom');
		
		if (json_decode($json_string, true) == NULL)
		{
			echo json_encode("ERROR");
			exit;
		}

		$templates = json_decode($json_string, true);

        foreach($templates as $template)
        {
        		if( !array_key_exists('ReviewOfSystemTemplate', $template) ){
							echo json_encode("ERROR");
							exit;
        		}
        		$data = array();
            $data['ReviewOfSystemTemplate']['user_id'] = $_SESSION['UserAccount']['user_id'];
            $data['ReviewOfSystemTemplate']['template_name'] = $this->getNewTemplateName($template['ReviewOfSystemTemplate']['template_name'], 0, $copy_mode);
            $data['ReviewOfSystemTemplate']['type_of_practice'] = $template['ReviewOfSystemTemplate']['type_of_practice'];
            $data['ReviewOfSystemTemplate']['show'] = $template['ReviewOfSystemTemplate']['show'];
            $data['ReviewOfSystemTemplate']['share'] = $template['ReviewOfSystemTemplate']['share'];
            $data['ReviewOfSystemTemplate']['default_negative'] = $template['ReviewOfSystemTemplate']['default_negative'];
            $this->create();
            $this->save($data);
            $template_id = $this->getLastInsertId();
            
            foreach($template['ReviewOfSystemCategory'] as $category)
            {
                $data = array();
                $data['ReviewOfSystemCategory']['template_id'] = $template_id;
                $data['ReviewOfSystemCategory']['category_name'] = $category['category_name'];
                $data['ReviewOfSystemCategory']['order'] = $category['order'];
                $data['ReviewOfSystemCategory']['enable'] = $category['enable'];
                $this->ReviewOfSystemCategory->create();
                $this->ReviewOfSystemCategory->save($data);
                $category_id = $this->ReviewOfSystemCategory->getLastInsertId();
                
                foreach($category['ReviewOfSystemSymptom'] as $symptom)
                {
                    $data = array();
                    $data['ReviewOfSystemSymptom']['category_id'] = $category_id;
                    $data['ReviewOfSystemSymptom']['symptom'] = $symptom['symptom'];
                    $data['ReviewOfSystemSymptom']['order'] = $symptom['order'];
                    $data['ReviewOfSystemSymptom']['enable'] = $symptom['enable'];
                    $this->ReviewOfSystemSymptom->create();
                    $this->ReviewOfSystemSymptom->save($data);
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
        $item = $this->find('first', array('conditions' => array('ReviewOfSystemTemplate.template_id' => $template_id)));

        if ($item)
        {
            return $item['ReviewOfSystemTemplate']['template_name'];
        }
        else
        {
            return 'General';
        }
    }

    public function getDefaultNegative($template_id)
    {
        $item = $this->find('first', array('conditions' => array('ReviewOfSystemTemplate.template_id' => $template_id)));

        if ($item)
        {
            return $item['ReviewOfSystemTemplate']['default_negative'];
        }
        else
        {
            return 0;
        }
    }

	public function isDefaultNegative($template_id)
	{
		$template = $this->find('first', array('conditions' => array('template_id' => $template_id)));
		
		$ret = false;
		
		if($template)
		{
			$ret = (bool)$template['ReviewOfSystemTemplate']['default_negative'];
		}
		
		return $ret;
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
			'fields' => array('UserAccount.default_template_ros'),
			'conditions' => array('UserAccount.user_id' => $user_id)
			)
		);

		if (in_array($_SESSION['UserAccount']['role_id'], $providerRoles))
		{
			$item = $this->find('first', array('conditions' => array('AND' => array('OR' => array('AND' => array('ReviewOfSystemTemplate.user_id' => $user_id, 'ReviewOfSystemTemplate.share' => 'No'), 'ReviewOfSystemTemplate.share' => 'Yes'), 'ReviewOfSystemTemplate.template_id' => $item['UserAccount']['default_template_ros'], 'ReviewOfSystemTemplate.type_of_practice' => array('', $PracticeProfile['PracticeProfile']['type_of_practice']),    'ReviewOfSystemTemplate.show' => 'Yes'))));
		}
		else
		{
			$item = $this->find('first', array('conditions' => array('AND' => array('ReviewOfSystemTemplate.user_id' => $user_id, 'ReviewOfSystemTemplate.template_id' => $item['UserAccount']['default_template_ros'], 'ReviewOfSystemTemplate.show' => 'Yes', 'ReviewOfSystemTemplate.type_of_practice' => array('', $PracticeProfile['PracticeProfile']['type_of_practice']), 'ReviewOfSystemTemplate.share' => 'No'))));
		}

        if ($item)
        {
            return $item['ReviewOfSystemTemplate']['template_id'];
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
		$data['UserAccount']['default_template_ros'] = $template_id;
		$data['UserAccount']['user_id'] = $user_id;
		$UserAccount->save($data);
    }

	public function getDisplayTemplate($template_id)
	{
		$body_systems = $this->find('first', array(
			'contain' => array
			(
				'ReviewOfSystemCategory' => array
				(
					'fields' => array('category_name', 'order'),
					'conditions' => array('enable' => 1),
					'order' => array(
						'ReviewOfSystemCategory.order' => 'ASC',
					),
					'ReviewOfSystemSymptom' => array(
						'fields' => array('symptom', 'order'),
						'conditions' => array('enable' => 1),
						'order' => array(
							'ReviewOfSystemSymptom.order' => 'ASC'
						),
					)
				)
			),
			'conditions' => array('template_id' => $template_id),
			'fields' => array('template_name', 'default_negative')
		));
		
		return $body_systems;
	}
	
	public function getDisplayData($template_id, $selected_items, $comments)
	{
		$body_systems = $this->getDisplayTemplate($template_id);

		$ros_data = array();
		foreach($body_systems['ReviewOfSystemCategory'] as $body_system)
		{
		   $data = array();
		   $data['description'] = $body_system['category_name'];
		   $data['comments'] = '';
		   
		   if(isset($comments[$body_system['category_name']]))
		   {
			   $data['comments'] = $comments[$body_system['category_name']];
		   }
		
		   $sub_data = array();
		
		   foreach($body_system['ReviewOfSystemSymptom'] as $ros_symptom)
		   {
			   $init_val = ' ';
		
			   if(isset($selected_items[$data['description']][$ros_symptom['symptom']]))
			   {
					   $init_val = $selected_items[$data['description']][$ros_symptom['symptom']];
			   }
		
			   $sub_data[] = array('data' => $ros_symptom['symptom'], 'init_val' => $init_val);
		   }
		
		   $data['details'] = $sub_data;
		   $ros_data[] = $data;
		}
		
		return $ros_data;
	}
	
	public function GetTemplate($template_id)
	{
		$this->recursive = 0;
		$template = $this->find('first', array(
			'contain' => array
			(
				'ReviewOfSystemCategory' => array
				(
					'fields' => array('category_name', 'order', 'enable'),
					'order' => array(
						'ReviewOfSystemCategory.order' => 'ASC',
					),
					'ReviewOfSystemSymptom' => array(
						'fields' => array(
							'symptom', 'order', 'enable'
						),
						'order' => array(
							'ReviewOfSystemSymptom.order' => 'ASC',
						),
					)
				)
			),
			'conditions' => array('template_id' => $template_id),
			'fields' => array('template_name')
		));
		return $template;
	}

	public function beforeSave($options)
	{
		$this->data['ReviewOfSystemTemplate']['default_negative'] = (int)@$this->data['ReviewOfSystemTemplate']['default_negative'];
		$this->data['ReviewOfSystemTemplate']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['ReviewOfSystemTemplate']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function executeROSTemplateManager(&$controller, $task)
	{
        switch ($task)
        {
					
					case 'inline_edit' : {
						$mode = $controller->params['data']['submitted']['id'];
						$value = trim($controller->params['data']['submitted']['value']);
						
						
						$mode = explode('-', $mode);
						
						switch ($mode[0]) {
							
							case 'category':
								$controller->loadModel('ReviewOfSystemCategory');
								$controller->ReviewOfSystemCategory->id = $mode[1];
								$controller->ReviewOfSystemCategory->saveField('category_name', $value);
								break;

							case 'symptom':
								$controller->loadModel('ReviewOfSystemSymptom');
								$controller->ReviewOfSystemSymptom->id = $mode[1];
								$controller->ReviewOfSystemSymptom->saveField('symptom', $value);
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
                $controller->redirect(array('action' => 'ros_template'));
			} break;
			case "set_show_template":
			{
				$controller->data['ReviewOfSystemTemplate']['template_id'] = $controller->data['template_id'];
				if ($controller->data['set'] == 'true')
				{
					$controller->data['ReviewOfSystemTemplate']['show'] = "Yes";
				}
				else
				{
					$controller->data['ReviewOfSystemTemplate']['show'] = "No";
				}
				$this->save($controller->data);
				exit;
			} break;
			case "set_share_template":
			{
				$controller->data['ReviewOfSystemTemplate']['template_id'] = $controller->data['template_id'];
				if ($controller->data['set'] == 'true')
				{
					$controller->data['ReviewOfSystemTemplate']['share'] = "Yes";
				}
				else
				{
					$controller->data['ReviewOfSystemTemplate']['share'] = "No";
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
						$controller->data['ReviewOfSystemTemplate']['user_id'] = $_SESSION['UserAccount']['user_id'];
						if (!isset($controller->data['ReviewOfSystemTemplate']['show']))
						{
							$controller->data['ReviewOfSystemTemplate']['show'] = "No";
						}
	
						if (!isset($controller->data['ReviewOfSystemTemplate']['share']))
						{
							$controller->data['ReviewOfSystemTemplate']['share'] = "No";
						}
                        $this->create();
                        $this->save($controller->data);
                        $template_id = $this->getLastInsertID();

                        $this->recursive = 0;
                        $template = $this->find('first', array(
                            'contain' => array
                                (
                                'ReviewOfSystemCategory' => array
                                    (
                                    'fields' => array('category_name', 'order'),
                                    'ReviewOfSystemSymptom' => array('fields' => array('symptom', 'order'))
                                )
                            ),
                            'conditions' => array('user_id' => 0),
                            'fields' => array('template_name', 'default_negative')
                            ));

                        //copy category name
                        foreach ($template['ReviewOfSystemCategory'] as $category_name)
                        {
                            $data = array();
                            $data['ReviewOfSystemCategory']['template_id'] = $template_id;
                            $data['ReviewOfSystemCategory']['category_name'] = $category_name['category_name'];
                            $data['ReviewOfSystemCategory']['order'] = $category_name['order'];

                            $controller->ReviewOfSystemCategory->create();
                            $controller->ReviewOfSystemCategory->save($data);
                            $category_id = $controller->ReviewOfSystemCategory->getLastInsertID();

                            //copy symptom
                            foreach ($category_name['ReviewOfSystemSymptom'] as $symptom)
                            {
                                $data = array();
                                $data['ReviewOfSystemSymptom']['category_id'] = $category_id;
                                $data['ReviewOfSystemSymptom']['symptom'] = $symptom['symptom'];
                                $data['ReviewOfSystemSymptom']['order'] = $symptom['order'];

                                $controller->ReviewOfSystemSymptom->create();
                                $controller->ReviewOfSystemSymptom->save($data);
                            }
                        }
                        $controller->Session->setFlash(__('Item(s) added.', true));
                        $controller->redirect(array('action' => 'ros_template', 'task' => 'edit', 'template_id' => $template_id));
                    }
                } break;
            case "add_category_name":
                {
                    $controller->data['ReviewOfSystemCategory']['template_id'] = $controller->data['template_id'];
                    $controller->data['ReviewOfSystemCategory']['category_name'] = $controller->data['category_name'];
                    $controller->data['ReviewOfSystemCategory']['order'] = $controller->data['order'] + 1;
                    $controller->ReviewOfSystemCategory->create();
                    $controller->ReviewOfSystemCategory->save($controller->data);

                    $template = $this->GetTemplate($controller->data['template_id']);
                    echo json_encode($template);
                    exit;
                } break;
            case "delete_category_name":
                {
					$controller->ReviewOfSystemCategory->delete($controller->data['category_id'], false);
					$controller->ReviewOfSystemSymptom->deleteAll(array('ReviewOfSystemSymptom.category_id' => $controller->data['category_id']));

                    $template = $this->GetTemplate($controller->data['template_id']);
                    echo json_encode($template);
                    exit;
                } break;
            case "add_symptom":
                {
                    $controller->data['ReviewOfSystemSymptom']['category_id'] = $controller->data['category_id'];
                    $controller->data['ReviewOfSystemSymptom']['symptom'] = $controller->data['symptom'];
                    $controller->data['ReviewOfSystemSymptom']['order'] = $controller->data['order'] + 1;
                    $controller->ReviewOfSystemSymptom->create();
                    $controller->ReviewOfSystemSymptom->save($controller->data);

                    $template = $this->GetTemplate($controller->data['template_id']);
                    echo json_encode($template);
                    exit;
                } break;
            case "delete_symptom":
                {
					$controller->ReviewOfSystemSymptom->delete($controller->data['symptom_id'], false);

                    $template = $this->GetTemplate($controller->data['template_id']);
                    echo json_encode($template);
                    exit;
                } break;
            case "update_display":
                {
                    switch ($controller->data['level'])
                    {
                        case "1" :
                            {
                                $controller->data['ReviewOfSystemCategory']['category_id'] = $controller->data['id'];
                                $controller->data['ReviewOfSystemCategory']['enable'] = $controller->data['enable'];
                                $controller->ReviewOfSystemCategory->save($controller->data);
                            } break;
                        case "2" :
                            {
                                $controller->data['ReviewOfSystemSymptom']['symptom_id'] = $controller->data['id'];
                                $controller->data['ReviewOfSystemSymptom']['enable'] = $controller->data['enable'];
                                $controller->ReviewOfSystemSymptom->save($controller->data);
                            } break;
                    }
                    exit;
                } break;
            case "edit":
                {
                    if (!empty($controller->data))
                    {
						if (!isset($controller->data['ReviewOfSystemTemplate']['show']))
						{
							$controller->data['ReviewOfSystemTemplate']['show'] = "No";
						}
	
						if (!isset($controller->data['ReviewOfSystemTemplate']['share']))
						{
							$controller->data['ReviewOfSystemTemplate']['share'] = "No";
						}
                        $this->save($controller->data);
												
												if (isset($controller->params['form']['category_order'])) {
													foreach ($controller->params['form']['category_order'] as $order => $id) {
														$this->ReviewOfSystemCategory->id = $id;
														$this->ReviewOfSystemCategory->saveField('order', $order+1);
													}
												}
												
												if (isset($controller->params['form']['symptom_order'])) {
													$controller->loadModel('ReviewOfSystemSymptom');
													foreach ($controller->params['form']['symptom_order'] as $category_id => $symptoms) {
														foreach ($symptoms as $order => $symptom_id) {
															$controller->ReviewOfSystemSymptom->id = $symptom_id;
															$controller->ReviewOfSystemSymptom->saveField('order', $order+1);
														}
													}
												}												
												
                        $controller->Session->setFlash(__('Item(s) saved.', true));
                        $controller->redirect(array('action' => 'ros_template'));
                    }
                    else
                    {
                        $template_id = (isset($controller->params['named']['template_id'])) ? $controller->params['named']['template_id'] : "";
                        $items = $this->find(
                            'first', array(
                            'conditions' => array('ReviewOfSystemTemplate.template_id' => $template_id)
                            )
                        );

                        $controller->set('EditItem', $controller->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
                    if (!empty($controller->data))
                    {
                        $template_id = $controller->data['ReviewOfSystemTemplate']['template_id'];
                        $delete_count = 0;

                        foreach ($template_id as $template_id)
                        {
                            $template = $this->GetTemplate($template_id);
                            $this->delete($template_id, false);
                            foreach ($template['ReviewOfSystemCategory'] as $category_name)
                            {
                                $controller->ReviewOfSystemCategory->delete($category_name['category_id'], false);
                                foreach ($category_name['ReviewOfSystemSymptom'] as $symptom)
                                {
                                    $controller->ReviewOfSystemSymptom->delete($symptom['symptom_id'], false);
                                }
                            }
                            $delete_count++;
                        }

                        if ($delete_count > 0)
                        {
                            $controller->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                        }
                    }
                    $controller->redirect(array('action' => 'ros_template'));
                } break;
            default:
                {
					$controller->set('ReviewOfSystemTemplate', $controller->sanitizeHTML($controller->paginate('ReviewOfSystemTemplate',  array('OR' => array('ReviewOfSystemTemplate.user_id' => $controller->user_id, 'ReviewOfSystemTemplate.share' => 'Yes')))));

					$item = $controller->UserAccount->find(
						'first', array(
						'fields' => array('UserAccount.default_template_ros'),
						'conditions' => array('UserAccount.user_id' => $controller->user_id)
						)
					);
					$controller->set('default_template_ros', $item['UserAccount']['default_template_ros']);
                } break;
        }
	}
}

?>
