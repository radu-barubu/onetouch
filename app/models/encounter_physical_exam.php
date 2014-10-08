<?php

class EncounterPhysicalExam extends AppModel
{

    public $name = 'EncounterPhysicalExam';
    public $primaryKey = 'physical_exam_id';
    public $useTable = 'encounter_physical_exam';
    public $actsAs = array('Containable');
    public $belongsTo = array(
        'PhysicalExamTemplate' => array(
            'className' => 'PhysicalExamTemplate',
            'foreignKey' => 'template_id'
        ),
        'EncounterMaster' => array(
            'className' => 'EncounterMaster',
            'foreignKey' => 'encounter_id'
        )
    );
    public $hasMany = array(
        'EncounterPhysicalExamDetail' => array(
            'className' => 'EncounterPhysicalExamDetail',
            'foreignKey' => 'physical_exam_id'
        ),
        'EncounterPhysicalExamText' => array(
            'className' => 'EncounterPhysicalExamText',
            'foreignKey' => 'physical_exam_id'
        )
    );

    public function beforeSave($options)
    {
        $this->data['EncounterPhysicalExam']['modified_timestamp'] = __date("Y-m-d H:i:s");
        $this->data['EncounterPhysicalExam']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
        return true;
    }

    public function getItemsByPatient($patient_id)
    {
        $search_results = $this->find('all', array(
            'conditions' => array('EncounterMaster.patient_id' => $patient_id)
            )
        );

        return $search_results;
    }

    private function getText($text_type, $item_id, $item_value, $physical_exam_id = null)
    {
        if ($item_value == '[text]')
        {
					 $conditions = array(
						 'EncounterPhysicalExamText.text_type' => $text_type, 
						 'EncounterPhysicalExamText.item_id' => $item_id);

					 if ($physical_exam_id) {
						 $conditions['EncounterPhysicalExamText.physical_exam_id'] = $physical_exam_id;
					 }
					 
            $item = $this->EncounterPhysicalExamText->find('first', array(
				'order' => array('EncounterPhysicalExamText.text_id DESC'),
				'conditions' => $conditions,
			));

            if ($item)
            {
                $item_value = $item['EncounterPhysicalExamText']['item_value'];
            } else {
							$item_value = 'Other';
						}
        }
				
        return $item_value;
    }
		
    private function getCookedText($text_type, $item_id, $item_value, $pool)
    {
        if ($item_value == '[text]')
        {
					
					foreach ($pool as $p) {
						if ($p['text_type'] == $text_type && $p['item_id'] == $item_id) {
							return $p['item_value'];
						}
					}
					
					return 'Other';
        }
				
        return $item_value;
    }

    /* @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

      NOTICE: be careful about making changes below, you can break the visit summary view file (summary.ctp)

      @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ */

    public function getCookedItems($encounter_id)
    {
	$bodySystem = array();
 	$r=$this->searchItem($encounter_id, array('PhysicalExamTemplate', 'EncounterPhysicalExamDetail', 'EncounterPhysicalExamText'));
        if (!$r)
        {
            return;
        }

				$commentData = json_decode($r['EncounterPhysicalExam']['comments'], true);
				
        $EncounterPhysicalExamDetail = $r['EncounterPhysicalExamDetail'];
        $texts = $r['EncounterPhysicalExamText'];
				
				App::import('Model', 'PhysicalExamElement');
				App::import('Model', 'PhysicalExamSubElement');
				App::import('Model', 'PhysicalExamObservation');
				$this->PhysicalExamElement = new PhysicalExamElement();
				$this->PhysicalExamSubElement = new PhysicalExamSubElement();
				$this->PhysicalExamObservation = new PhysicalExamObservation();

				$others = array();
				$skipElement = array();
				$skipSubElement = array();
				$otherElements = array();
				$otherSubElements = array();
				$otherObservations = array();
				
				if (!empty($texts)) {
					foreach ($texts as $index => $t) {
						
						if ($t['text_type'] == 'element') {
							$this->PhysicalExamElement->id = $t['item_id'];
							$skipElement[] = $t['item_id'];
							$element = $this->PhysicalExamElement->read();

							if (!$element) {
								continue;
							}
							
							$element['item_value'] = $t['item_value'];
							$otherElements[$element['PhysicalExamElement']['element_id']] = $element;
							
							$bodySystem[$element['PhysicalExamBodySystem']['body_system_id']] = $element['PhysicalExamBodySystem']['body_system'];
							
							if (!isset($others[$element['PhysicalExamElement']['body_system_id']])) {
								$others[$element['PhysicalExamElement']['body_system_id']] = array();
							}
							$t['element'] = $element['PhysicalExamElement']['element'];
							$others[$element['PhysicalExamElement']['body_system_id']][] = $t;							
							
						} else if ($t['text_type'] == 'subelement') {
							$this->PhysicalExamSubElement->id = $t['item_id'];
							$skipSubElement[] = $t['item_id'];
							$subelement = $this->PhysicalExamSubElement->read();							
							
							if (!$subelement) {
								continue;
							}
							
							$subelement['item_value'] = $t['item_value'];
							$otherSubElements[$subelement['PhysicalExamSubElement']['sub_element_id']] = $subelement;
							
							
							if (!isset($bodySystem[$subelement['PhysicalExamElement']['body_system_id']])) {
								$this->PhysicalExamElement->id = $subelement['PhysicalExamElement']['element_id'];
								$element = $this->PhysicalExamElement->read();
								$bodySystem[$element['PhysicalExamBodySystem']['body_system_id']] = $element['PhysicalExamBodySystem']['body_system'];							
							}
							
							
							if (!isset($others[$subelement['PhysicalExamElement']['body_system_id']])) {
								$others[$subelement['PhysicalExamElement']['body_system_id']] = array();
							}
							$t['element'] = $subelement['PhysicalExamElement']['element'];
							$others[$subelement['PhysicalExamElement']['body_system_id']][] = $t;							
							
						} else if ($t['text_type'] == 'observation') {
							
							$this->PhysicalExamObservation->id = $t['item_id'];
							$skipObservation[] = $t['item_id'];
							$observation = $this->PhysicalExamObservation->read();							

							if (!$observation) {
								continue;
							}

							$observation['item_value'] = $t['item_value'];
							$otherObservations[$observation['PhysicalExamObservation']['observation_id']] = $observation;
							
							
							if (!isset($bodySystem[$observation['PhysicalExamElement']['body_system_id']])) {
								$this->PhysicalExamElement->id = $observation['PhysicalExamElement']['element_id'];
								$element = $this->PhysicalExamElement->read();
								$bodySystem[$element['PhysicalExamBodySystem']['body_system_id']] = $element['PhysicalExamBodySystem']['body_system'];							
							}
							
							
							if (!isset($others[$observation['PhysicalExamElement']['body_system_id']])) {
								$others[$observation['PhysicalExamElement']['body_system_id']] = array();
							}
							$t['element'] = $observation['PhysicalExamElement']['element'];
							$others[$observation['PhysicalExamElement']['body_system_id']][] = $t;									
						}
					}
				}

        $pe = array();
				$ignore = array();
				$freeTextElements = array();
				$includeElements = array();
				
				
        foreach ($EncounterPhysicalExamDetail as $v)
        {

            //if($v['observation_value']=='NC') {
            //	continue;
            //}

						if (in_array($v['element_id'], $skipElement)) {
							
							if (!$v['observation']) {
								continue;
							}
							
							$ignore[] = $v['element_id'];
							
							$freeTextElements[$v['element_id']] = $v;
							
						}
						
						$includeElements[] = $v['element_id'];
						
						$element = '';
						//if (!in_array($v['sub_element_id'], $skipSubElement)) {
							$element = $this->getCookedText('subelement', $v['sub_element_id'], $v['sub_element'], $texts);
						//}
						

            //if ($v['observation_value']) 

            $element .= '�' . $this->getCookedText('observation', $v['observation_id'], $v['observation'], $texts) . ' ' . $v['observation_value'];

            //if($v['specifier']) 

            $element .= '�' . $this->getCookedText('specifier', $v['specifier_id'], $v['specifier'], $texts);
            $pe[$v['body_system']][$this->getCookedText('element', $v['element_id'], $v['element'], $texts)][] = $element;
						
						$bodySystem[$v['body_system_id']] = $v['body_system'];

						
        }
				
				foreach($otherElements as $el) {
					
					$includeElements[] = $el['PhysicalExamElement']['element_id'];
					
					if (!isset($pe[$bodySystem[$el['PhysicalExamElement']['body_system_id']]])) {
						$pe[$bodySystem[$el['PhysicalExamElement']['body_system_id']]] = array();
					}
					
					if (!isset($pe[$bodySystem[$el['PhysicalExamElement']['body_system_id']]][$el['item_value']])) {
						$pe[$bodySystem[$el['PhysicalExamElement']['body_system_id']]][$el['item_value']] = array();
					}
				}
				
				foreach($otherSubElements as $sub) {
					
					if ($sub['PhysicalExamElement']['element'] == '[text]' && isset($otherElements[$sub['PhysicalExamElement']['element_id']])) {
						$sub['PhysicalExamElement']['element']	= $otherElements[$sub['PhysicalExamElement']['element_id']]['item_value'];
					} else if ($sub['PhysicalExamElement']['element'] == '[text]') {
						$includeElements[] = $sub['PhysicalExamElement']['element_id'];
						$sub['PhysicalExamElement']['element'] = 'Other';
					}
					
					
					if (!isset($pe[$bodySystem[$sub['PhysicalExamElement']['body_system_id']]][$sub['PhysicalExamElement']['element']])) {
						$pe[$bodySystem[$sub['PhysicalExamElement']['body_system_id']]][$sub['PhysicalExamElement']['element']] = array();
					}
					
					$found = false;
					foreach($pe[$bodySystem[$sub['PhysicalExamElement']['body_system_id']]][$sub['PhysicalExamElement']['element']] as $t) {
						
						if (stripos($t, $sub['item_value']) === 0) {
							$found = true;
						}
						
					}
					
					if (!$found) {
						$pe[$bodySystem[$sub['PhysicalExamElement']['body_system_id']]][$sub['PhysicalExamElement']['element']][] = $sub['item_value'] . '� ' . '�'  ;
					}
				}
				
				$includeElements = array_unique($includeElements);
				
				$template = $this->PhysicalExamTemplate->getTemplateData($r['PhysicalExamTemplate']['template_id']);

				$sorted = array();
				
				$commentMap = array();
        
        if (!$template) {
          return array(
            'pe_data' => $sorted,
            'comment_data' => $commentMap,
          );          
        }
        
				foreach ($template['PhysicalExamBodySystem'] as $body) {
					
					if (!isset($pe[$body['body_system']])) {
						if (!isset($commentData[$body['body_system_id']])) {
							continue;
						} 
					} 

					if (isset($commentData[$body['body_system_id']])) {
						$commentMap[$body['body_system']] = $commentData[$body['body_system_id']];
					}
					
					
					if (!isset($sorted[$body['body_system']])) {
						$sorted[$body['body_system']] = array();
					}
					
					foreach ($body['PhysicalExamElement'] as $elem) {
						
						if (!isset($pe[$body['body_system']][$elem['element']]) && $elem['element'] != '[text]') {
							continue;
						}
						
						// For free text element
						if ($elem['element'] == '[text]') {
							
							if (isset($freeTextElements[$elem['element_id']])) {
								$elem['element'] = $this->getCookedText('element', 
									$freeTextElements[$elem['element_id']]['element_id'], 
									$freeTextElements[$elem['element_id']]['element'], 
									$texts);

								if (!isset($pe[$body['body_system']][$elem['element']])) {
									continue;
								}
							
								
							} else if (in_array($elem['element_id'], $includeElements)) {
								
								$elem['element'] = 'Other';
								
								if (isset($otherElements[$elem['element_id']])) {
									$elem['element'] = $otherElements[$elem['element_id']]['item_value'];
								}

								if (!isset($pe[$body['body_system']][$elem['element']])) {
									continue;
								}
							} else {
								continue;
							}
						}
						
						if (empty($elem['PhysicalExamSubElement'])) {
							$sorted[$body['body_system']][$elem['element']] = $pe[$body['body_system']][$elem['element']];
							continue;
						}
						
						$sorted[$body['body_system']][$elem['element']] = array();

						foreach ($elem['PhysicalExamSubElement'] as $sub) {
							foreach($pe[$body['body_system']][$elem['element']] as $d) {
								if (stripos($d, $sub['sub_element']) === 0 || stripos($d, 'other') === 0) {
									$sorted[$body['body_system']][$elem['element']][] = $d;
									continue;
								}
								if (isset($otherSubElements[$sub['sub_element_id']])) {
									
									if (!in_array($d, $sorted[$body['body_system']][$elem['element']])) {
										$sorted[$body['body_system']][$elem['element']][] = $d;
									}
									continue;
								}
								
							}
						}
						
						foreach ($elem['PhysicalExamObservation'] as $obs) {
							foreach($pe[$body['body_system']][$elem['element']] as $d) {
								if (stripos($d, $obs['observation']) === 3 || stripos($d, 'other') === 3) {
									$sorted[$body['body_system']][$elem['element']][] = $d;
									continue;
								}
								
								if (isset($otherObservations[$obs['observation_id']])) {
									if (!in_array($d, $sorted[$body['body_system']][$elem['element']])) {
										$sorted[$body['body_system']][$elem['element']][] = $d;
									}
									continue;
								}								
							}
						}						
					}
					
					if (isset($pe[$body['body_system']]['other'])) {
						$sorted[$body['body_system']]['other'] = $pe[$body['body_system']]['other'];
					}
				}
				
				
				$data = array(
					'pe_data' => $sorted,
					'comment_data' => $commentMap,
				);
				
        return $data;
    }

    public function searchItem($encounter_id,$containable=array(),$recursive=false)
    {
	$ext=array('conditions' => array('EncounterPhysicalExam.encounter_id' => $encounter_id));

	if(sizeof($containable)> 0) {
	  $ext['contain'] = $containable;
	}

	if($recursive) {
	  $ext['recursive'] = -1;
	}

        $search_result = $this->find('first', $ext);

        if (!empty($search_result))
        {
            return $search_result;
        }
        else
        {
            return false;
        }
    }

    public function getTemplate($encounter_id)
    {
        $search_result = $this->searchItem($encounter_id, array('PhysicalExamTemplate'));
        if ($search_result)
        {
            $ret['template_id'] = $search_result['EncounterPhysicalExam']['template_id'];
            $ret['template_name'] = $search_result['PhysicalExamTemplate']['template_name'];
            $ret['default_negative'] = $search_result['PhysicalExamTemplate']['default_negative'];
        }
	else
	{
		$ret['template_id'] = $this->PhysicalExamTemplate->getDefaultTemplate();
        	$ret['template_name'] = $this->PhysicalExamTemplate->getTemplateName($ret['template_id']);
        	$ret['default_negative'] = $this->PhysicalExamTemplate->getDefaultNegative($ret['template_id']);
			
			if($ret['default_negative'] == '1')
			{
				$this->setAllNegative($ret['template_id'], $encounter_id);
			}
	}

        return $ret;
    }

    public function setAllNegative($template_id, $encounter_id)
    {
        $physical_exam_template = $this->PhysicalExamTemplate->getTemplateData($template_id);
				$physical_exam_id = $this->getPhysicalExamID($encounter_id);

        $observation_value = '';
        $specifier = "";
        $specifier_id = 0;

				
				$data = array();
				
        foreach ($physical_exam_template['PhysicalExamBodySystem'] as $PhysicalExamBodySystem)
        {
            $body_system_id = $PhysicalExamBodySystem['body_system_id'];
            $body_system = $PhysicalExamBodySystem['body_system'];

            foreach ($PhysicalExamBodySystem['PhysicalExamElement'] as $PhysicalExamElement)
            {
                $element_id = $PhysicalExamElement['element_id'];
                $element = $PhysicalExamElement['element'];

                if ($element == '[text]')
                {
                    continue;
                }

                foreach ($PhysicalExamElement['PhysicalExamObservation'] as $PhysicalExamObservation)
                {
                    $observation_id = $PhysicalExamObservation['observation_id'];
                    $observation = $PhysicalExamObservation['observation'];
                    $observation_value = $PhysicalExamObservation['normal'];
					$normal_selected = $PhysicalExamObservation['normal_selected'];
					
					if($normal_selected == 0)
					{
						continue;	
					}
                    
                    if($observation_value == '')
                    {
                        continue;
                    }

                    if ($observation == '[text]')
                    {
                        continue;
                    }

                    $sub_element = "";
                    $sub_element_id = 0;
					
					//get specifier normal if any
					$specifier_id = 0;
					$specifier = "";
					
					if(count($PhysicalExamObservation['PhysicalExamSpecifier']) > 0)
					{
						foreach($PhysicalExamObservation['PhysicalExamSpecifier'] as $PhysicalExamSpecifier)
						{
							if($PhysicalExamSpecifier['preselect_flag'] == 1)
							{
								$specifier_id = $PhysicalExamSpecifier['specifier_id'];
								$specifier = $PhysicalExamSpecifier['specifier'];
							}
						}
					}

										$data[] = array(
											'physical_exam_id' => $physical_exam_id,
											'body_system' => $body_system,
											'element' => $element,
											'sub_element' => $sub_element,
											'observation' => $observation,
											'observation_value' => $observation_value,
											'specifier' => $specifier,
											'body_system_id' => $body_system_id,
											'element_id' => $element_id,
											'sub_element_id' => $sub_element_id,
											'observation_id' => $observation_id,
											'specifier_id' => $specifier_id,
										);
										
                }

                foreach ($PhysicalExamElement['PhysicalExamSubElement'] as $PhysicalExamSubElement)
                {
                    $sub_element_id = $PhysicalExamSubElement['sub_element_id'];
                    $sub_element = $PhysicalExamSubElement['sub_element'];

                    if ($sub_element == '[text]')
                    {
                        continue;
                    }

                    foreach ($PhysicalExamSubElement['PhysicalExamObservation'] as $PhysicalExamObservation)
                    {
                        $observation_id = $PhysicalExamObservation['observation_id'];
                        $observation = $PhysicalExamObservation['observation'];
                        $observation_value = $PhysicalExamObservation['normal'];
						$normal_selected = $PhysicalExamObservation['normal_selected'];
					
						if($normal_selected == 0)
						{
							continue;	
						}
                        
                        if($observation_value == '')
                        {
                            continue;
                        }

                        if ($observation == '[text]')
                        {
                            continue;
                        }
						
						//get specifier normal if any
						$specifier_id = 0;
						$specifier = "";
						
						if(count($PhysicalExamObservation['PhysicalExamSpecifier']) > 0)
						{
							foreach($PhysicalExamObservation['PhysicalExamSpecifier'] as $PhysicalExamSpecifier)
							{
								if($PhysicalExamSpecifier['preselect_flag'] == 1)
								{
									$specifier_id = $PhysicalExamSpecifier['specifier_id'];
									$specifier = $PhysicalExamSpecifier['specifier'];
								}
							}
						}

												$data[] = array(
													'physical_exam_id' => $physical_exam_id,
													'body_system' => $body_system,
													'element' => $element,
													'sub_element' => $sub_element,
													'observation' => $observation,
													'observation_value' => $observation_value,
													'specifier' => $specifier,
													'body_system_id' => $body_system_id,
													'element_id' => $element_id,
													'sub_element_id' => $sub_element_id,
													'observation_id' => $observation_id,
													'specifier_id' => $specifier_id,
												);
												
												
												
                    }
                }
            }
        }

				$this->EncounterPhysicalExamDetail->saveAll($data);
				
    }
    
    public function deleteByEncounterId($encounter_id)
    {
        $search_result = $this->searchItem($encounter_id, array(), true);
        if ($search_result)
        {
            $physical_exam_id = $search_result['EncounterPhysicalExam']['physical_exam_id'];
            $this->deleteAll(array('EncounterPhysicalExam.physical_exam_id' => $physical_exam_id), true);
            $this->EncounterPhysicalExamDetail->deleteAll(array('EncounterPhysicalExamDetail.physical_exam_id' => $physical_exam_id), true);
            $this->EncounterPhysicalExamText->deleteAll(array('EncounterPhysicalExamText.physical_exam_id' => $physical_exam_id), true);
        }
    }

    public function setTemplate($template_id, $encounter_id)
    {
        $data = array();
        $ros_arr = array();
        
        //reset data on template change
        $this->deleteByEncounterId($encounter_id);

        $this->create();
        $data['EncounterPhysicalExam']['encounter_id'] = $encounter_id;

        $data['EncounterPhysicalExam']['template_id'] = $template_id;
        $this->save($data);
    }

    public function getPhysicalExamID($encounter_id)
    {
        $search_result = $this->searchItem($encounter_id, array(), true);
        if ($search_result)
        {
            return $search_result['EncounterPhysicalExam']['physical_exam_id'];
        }
        else
        {
            $this->create();
            $data['EncounterPhysicalExam']['encounter_id'] = $encounter_id;
            $data['EncounterPhysicalExam']['template_id'] = $this->PhysicalExamTemplate->getDefaultTemplate();
            $this->save($data);
            return $this->getLastInsertID();
        }
    }

    public function searchDetails($physical_exam_id, $body_system_id, $element_id, $sub_element_id, $observation_id)
    {
        $search_result = $this->EncounterPhysicalExamDetail->find(
            'first', array(
            'conditions' => array
                (
                'EncounterPhysicalExamDetail.physical_exam_id' => $physical_exam_id,
                'EncounterPhysicalExamDetail.body_system_id' => $body_system_id,
                'EncounterPhysicalExamDetail.element_id' => $element_id,
                'EncounterPhysicalExamDetail.sub_element_id' => $sub_element_id,
                'EncounterPhysicalExamDetail.observation_id' => $observation_id
            )
            )
        );

        if (!empty($search_result) )
        {
            return $search_result;
        }
        else
        {
            return false;
        }
    }

    public function searchTexts($physical_exam_id, $text_type, $item_id)
    {
        $search_result = $this->EncounterPhysicalExamText->find(
            'first', array(
            'conditions' => array
                (
                'EncounterPhysicalExamText.physical_exam_id' => $physical_exam_id,
                'EncounterPhysicalExamText.text_type' => $text_type,
                'EncounterPhysicalExamText.item_id' => $item_id
            )
            )
        );

        if (!empty($search_result))
        {
            return $search_result;
        }
        else
        {
            return false;
        }
    }

    public function saveItem($physical_exam_id, $encounter_id, $body_system, $element, $sub_element, $observation, $observation_value, $specifier, $body_system_id, $element_id, $sub_element_id, $observation_id, $specifier_id, $ignore_available = false)
    {
        $search_result = false;
        
		if (!$ignore_available)
		{
			$search_result = $this->searchDetails($physical_exam_id, (int)$body_system_id, (int)$element_id, (int)$sub_element_id, (int)$observation_id);
		}

		if ($search_result)
		{
			$data['EncounterPhysicalExamDetail'] = $search_result['EncounterPhysicalExamDetail'];
		}
		else
		{
			$this->EncounterPhysicalExamDetail->create();
			$data['EncounterPhysicalExamDetail']['physical_exam_id'] = $physical_exam_id;
		}

        $data['EncounterPhysicalExamDetail']['body_system'] = $body_system;
        $data['EncounterPhysicalExamDetail']['element'] = $element;
        $data['EncounterPhysicalExamDetail']['sub_element'] = $sub_element;
        $data['EncounterPhysicalExamDetail']['observation'] = $observation;
        $data['EncounterPhysicalExamDetail']['observation_value'] = $observation_value;
        $data['EncounterPhysicalExamDetail']['specifier'] = $specifier;
        $data['EncounterPhysicalExamDetail']['body_system_id'] = (int) $body_system_id;
        $data['EncounterPhysicalExamDetail']['element_id'] = (int) $element_id;
        $data['EncounterPhysicalExamDetail']['sub_element_id'] = (int) $sub_element_id;
        $data['EncounterPhysicalExamDetail']['observation_id'] = (int) $observation_id;
        $data['EncounterPhysicalExamDetail']['specifier_id'] = (int) $specifier_id;
        $this->EncounterPhysicalExamDetail->save($data);
		
		//if user changed their mind by adding observation or subelement to element as template editor
		if((int)$observation_id != 0) //remove any $observation_id = 0 and element = '' of the same body_system_id and element_id
		{
			$this->EncounterPhysicalExamDetail->deleteAll(array(
				'observation_id' => 0,
				'element' => '',
				'body_system_id' => (int)$body_system_id,
				'element_id' => (int)$element_id
			));
		}
    }
    
    public function deleteItem($physical_exam_id, $encounter_id, $body_system, $element, $sub_element, $observation, $observation_value, $specifier, $body_system_id, $element_id, $sub_element_id, $observation_id, $specifier_id)
    {
        
        $search_result = $this->searchDetails($physical_exam_id, (int)$body_system_id, (int)$element_id, (int)$sub_element_id, (int)$observation_id);

        if (!$search_result) {
            return false;
        }
        
        $this->EncounterPhysicalExamDetail->delete($search_result['EncounterPhysicalExamDetail']['details_id']);
    }    

    public function saveText($encounter_id, $text_type, $item_id, $item_value)
    {
        $physical_exam_id = $this->getPhysicalExamID($encounter_id);
        $search_result = $this->searchTexts($physical_exam_id, $text_type, $item_id);


        if ($search_result)
        {
            $data['EncounterPhysicalExamText']['text_id'] = $search_result['EncounterPhysicalExamText']['text_id'];
        }
        else
        {
            $this->EncounterPhysicalExamText->create();
            $data['EncounterPhysicalExamText']['physical_exam_id'] = $physical_exam_id;
        }

        $data['EncounterPhysicalExamText']['text_type'] = $text_type;
        $data['EncounterPhysicalExamText']['item_id'] = $item_id;
        $data['EncounterPhysicalExamText']['item_value'] = $item_value;

        $this->EncounterPhysicalExamText->save($data);
    }
		
    public function deleteText($encounter_id, $text_type, $item_id)
    {
        $physical_exam_id = $this->getPhysicalExamID($encounter_id);
        $search_result = $this->searchTexts($physical_exam_id, $text_type, $item_id);


        if ($search_result)
        {
						$this->EncounterPhysicalExamText->delete($search_result['EncounterPhysicalExamText']['text_id']);
        }
        
    }		
		

    public function getItem($encounter_id)
    {
        //$physical_exam_id = $this->getPhysicalExamID($encounter_id);
        return $this->find('first', array('conditions' => array('EncounterPhysicalExam.encounter_id' => $encounter_id)));
    }

    public function execute(&$controller, $encounter_id, $patient_id, $task)
    {
				$controller->set('patient_id', $patient_id);
        switch ($task)
        {
            case 'set_image_comment':
                $controller->EncounterPhysicalExamImage->id = $controller->params['form']['id'];
                $controller->EncounterPhysicalExamImage->saveField('comment', $controller->params['form']['comment']);
                exit;
                break;
            case 'set_image_in_summary':
                $controller->EncounterPhysicalExamImage->id = $controller->params['form']['id'];
                $controller->EncounterPhysicalExamImage->saveField('display_flag_visit_summary', $controller->params['form']['display_flag_visit_summary']);
                exit;
                break;
            case "load_image":
                {
                    $data = $controller->EncounterPhysicalExamImage->getAllItems($encounter_id);

										if (isset($controller->params['named']['with_path'])) {
											$controller->paths['patient_encounter_img'] = $controller->paths['patients'] . $patient_id . DS . 'images' . DS . $encounter_id . DS;
											foreach ($data as &$d) {
												$d['image'] = UploadSettings::toUrl(UploadSettings::existing(
													$controller->paths['encounters'] . $d['image'],
													$controller->paths['patient_encounter_img'] . $d['image']
												));
											}
										} 
										
                    echo json_encode($data);
                    exit;
                }
                break;
            case "save_image":
                {
										$controller->paths['patient_encounter_img'] = $controller->paths['patients'] . $patient_id . DS . 'images' . DS . $encounter_id . DS;							
										UploadSettings::createIfNotExists($controller->paths['patient_encounter_img']);
										
                    $source_file = $controller->paths['temp'] . $controller->data['image_file_name'];
                    $destination_file = $controller->paths['patient_encounter_img'] . $controller->data['image_file_name'];
                    @copy($source_file, $destination_file);
		    @unlink($source_file); // remove temp file
                    $controller->EncounterPhysicalExamImage->addItem($encounter_id, $controller->data['image_file_name']);
                    exit;
                }
                break;
            case "delete_image":
                {
							
										$controller->EncounterPhysicalExamImage->id = $controller->data['pe_image_id'];
										
										$peImage = $controller->EncounterPhysicalExamImage->read();
										
										if (!$peImage) {
											die('Image not found');
										}
										
										$controller->paths['patient_encounter_img'] = $controller->paths['patients'] . $patient_id . DS . 'images' . DS . $encounter_id . DS;							
										$filename = UploadSettings::existing(
											$controller->paths['encounters'] . $peImage['EncounterPhysicalExamImage']['image'],
											$controller->paths['patient_encounter_img'] . $peImage['EncounterPhysicalExamImage']['image']
											);
                    @unlink($filename);

                    $controller->EncounterPhysicalExamImage->deleteItem($peImage['EncounterPhysicalExamImage']['physical_exam_image_id']);
                    exit;
                }
                break;
            case "add_text":
                {
                    //if they screw up and use ALL CAPS
                    $pe_entered_text = ucwords(strtolower($controller->data['item_value']));
                    $this->saveText($encounter_id, $controller->data['text_type'], $controller->data['item_id'], $pe_entered_text);

                    if (isset($controller->data['template_id']))
                    {
                        $this->setTemplate($controller->data['template_id'], $encounter_id);
                    }

                    $template_to_use = $this->getTemplate($encounter_id);

                    if ($template_to_use['default_negative'] == '1' and isset($controller->data['template_id']))
                    {
                        $this->setAllNegative($template_to_use['template_id'], $encounter_id);
                    }

                    $all_data = array();
                    $all_data['pe_saved_data'] = $this->getItem($encounter_id);

                    $all_data = $controller->PhysicalExamTemplate->getEncounterPEData($controller, $template_to_use, $all_data);
                    echo json_encode($all_data);
                    exit;
                }
                break;
						case 'delete_text': {
							$this->deleteText($encounter_id, $controller->data['text_type'], $controller->data['item_id']);
							if (isset($controller->data['template_id']))
							{
									$this->setTemplate($controller->data['template_id'], $encounter_id);
							}

							$template_to_use = $this->getTemplate($encounter_id);

							if ($template_to_use['default_negative'] == '1' and isset($controller->data['template_id']))
							{
									$this->setAllNegative($template_to_use['template_id'], $encounter_id);
							}

							$all_data = array();
							$all_data['pe_saved_data'] = $this->getItem($encounter_id);

							$all_data = $controller->PhysicalExamTemplate->getEncounterPEData($controller, $template_to_use, $all_data);
							echo json_encode($all_data);
							exit;							
						} break;
								
								
            case "add":
                {
					$physical_exam_id = $this->getPhysicalExamID($encounter_id);
                    $this->saveItem($physical_exam_id, $encounter_id, $controller->data['body_system'], $controller->data['element'], $controller->data['sub_element'], $controller->data['observation'], $controller->data['observation_value'], $controller->data['specifier'], $controller->data['body_system_id'], $controller->data['element_id'], $controller->data['sub_element_id'], $controller->data['observation_id'], $controller->data['specifier_id'], false);

                    $all_data = array();
                    $all_data['pe_saved_data'] = $this->getItem($encounter_id);
                    echo json_encode($all_data);
                    exit;
                }
                break;
            case 'delete': {
                    $physical_exam_id = $this->getPhysicalExamID($encounter_id);
                    $this->deleteItem($physical_exam_id, $encounter_id, $controller->data['body_system'], $controller->data['element'], $controller->data['sub_element'], $controller->data['observation'], $controller->data['observation_value'], $controller->data['specifier'], $controller->data['body_system_id'], $controller->data['element_id'], $controller->data['sub_element_id'], $controller->data['observation_id'], $controller->data['specifier_id'], false);
                    $all_data = array();
                    $all_data['pe_saved_data'] = $this->getItem($encounter_id);
                    echo json_encode($all_data);
                    exit;
                break;
            }
						case 'save_comment': {
							$bodySystemId = array_pop(explode('-', $controller->data['submitted']['id']));
							$val = trim(__strip_tags($controller->data['submitted']['value']));
							$physical_exam_id = $this->getPhysicalExamID($encounter_id);
							$pe = $this->find('first', array(
								'conditions' => array(
									'EncounterPhysicalExam.encounter_id' => $encounter_id,
								),
							));
							
							$commentData = $pe['EncounterPhysicalExam']['comments'];
							
							
							if (!$commentData) {
								$commentData = array();
							} else {
								$commentData = json_decode($commentData, true);
							}
							
							$commentData[$bodySystemId] = $val;
							
							$commentData = json_encode($commentData);
							
							$this->id = $pe['EncounterPhysicalExam']['physical_exam_id'];
							$this->saveField('comments', $commentData);
							echo nl2br(trim(htmlentities($val)));
							exit;
							break;
						
						}
            case "get_list":
                {
                    if (isset($controller->data['template_id']))
                    {
                        $this->setTemplate($controller->data['template_id'], $encounter_id);
                    }

                    $template_to_use = $this->getTemplate($encounter_id);

                    if ($template_to_use['default_negative'] == '1' and isset($controller->data['template_id']))
                    {
                        $this->setAllNegative($template_to_use['template_id'], $encounter_id);
                    }

                    $all_data = array();
                    $all_data['pe_saved_data'] = $this->getItem($encounter_id);

                    $all_data = $controller->PhysicalExamTemplate->getEncounterPEData($controller, $template_to_use, $all_data);
					
					// filter body elements depends on patient gender for Gu
					$controller->loadModel('PatientDemographic');
					$gender = $controller->PatientDemographic->field('gender', array('patient_id' =>$patient_id));
					$maleBodyElem = array('scrotum','penis','epididymis','testes','seminal vesicles','prostate');
					$femaleBodyElem = array('vagina','cervix & os','uterus','adnexa');
					if($gender=='M')
						$filterElem = $femaleBodyElem;
					else 
						$filterElem = $maleBodyElem;
					if(isset($all_data['pe_data']['PhysicalExamBodySystem']) && is_array($all_data['pe_data']['PhysicalExamBodySystem'])) {
							foreach($all_data['pe_data']['PhysicalExamBodySystem'] as $key1 => $bodySystem) {
								if(strtolower($bodySystem['body_system'])=='gu') {
									foreach($bodySystem['PhysicalExamElement'] as $key2 => $element) {
										if(in_array(strtolower($element['element']), $filterElem)) 
											unset($all_data['pe_data']['PhysicalExamBodySystem'][$key1]['PhysicalExamElement'][$key2]);
									}
									break;
								}
							}
						}
					
                    echo json_encode($all_data);
                    exit;
                }
                break;
        }

		$controller->loadModel("UserGroup");
		$providerRoles = $controller->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK);

		$controller->loadModel("PracticeProfile");
		$PracticeProfile = $controller->PracticeProfile->find('first');

        $controller->PhysicalExamTemplate->recursive = 0;

		if (in_array($controller->Session->read('UserAccount.role_id'), $providerRoles))
		{
			$controller->set("templates", $controller->sanitizeHTML(
			$controller->PhysicalExamTemplate->find('all', array('conditions' => array('AND' => array('OR' => array('AND' => array('PhysicalExamTemplate.user_id' => array(0, $controller->user_id), 'PhysicalExamTemplate.share' => 'No'), 'PhysicalExamTemplate.share' => 'Yes'), 'PhysicalExamTemplate.type_of_practice' => array('', $PracticeProfile['PracticeProfile']['type_of_practice']), 'PhysicalExamTemplate.show' => 'Yes'))))));
		}
		else
		{
			$controller->set("templates", $controller->sanitizeHTML($controller->PhysicalExamTemplate->find('all', array('conditions' => array('AND' => array('PhysicalExamTemplate.user_id' => array(0, $controller->user_id), 'PhysicalExamTemplate.type_of_practice' => array('', $PracticeProfile['PracticeProfile']['type_of_practice']), 'PhysicalExamTemplate.show' => 'Yes', 'PhysicalExamTemplate.share' => 'No'))))));
		}

        $template_to_use = $this->getTemplate($encounter_id);
        $controller->set("template_to_use", $controller->sanitizeHTML($template_to_use));
    }

}

?>
