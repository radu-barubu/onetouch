<?php

class EmdeonFavoriteTestGroup extends AppModel 
{
	public $name = 'EmdeonFavoriteTestGroup';
	public $primaryKey = 'test_group_id';
	public $useTable = 'emdeon_favorite_test_groups';
	
	public $hasMany = array(
		'EmdeonFavoriteTestGroupDetail' => array(
			'className' => 'EmdeonFavoriteTestGroupDetail',
			'foreignKey' => 'test_group_id'
		)
	);

	public function beforeSave($options)
	{
		$this->data['EmdeonFavoriteTestGroup']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EmdeonFavoriteTestGroup']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function afterFind($results, $primary)
	{
		for($i = 0; $i < count($results); $i++)
		{
			if(isset($results[$i][$this->alias]['test_group_id']))
			{
				$results[$i][$this->alias]['lab_string'] = $this->getLabString($results[$i][$this->alias]['lab']);
			}
		}
		
		return $results;
	}
	
	public function getLabString($lab)
	{
		$emdeon_xml_api = new Emdeon_XML_API();
		$labs = $emdeon_xml_api->getLabs();
		
		foreach($labs as $lab_item)
		{
			if($lab_item['lab'] == $lab)
			{
				return $lab_item['lab_name'];	
			}
		}
	}
	
	public function getTestGroups($lab)
	{
		$items = $this->find('all', array('conditions' => array('EmdeonFavoriteTestGroup.lab' => $lab, 'EmdeonFavoriteTestGroup.modified_user_id' => $_SESSION['UserAccount']['user_id'])));
		
		return $items;
	}
	
	public function getTestCodes($test_group_id)
	{
		$items = $this->EmdeonFavoriteTestGroupDetail->find('all', array('conditions' => array('EmdeonFavoriteTestGroupDetail.test_group_id' => $test_group_id)));
		
		$data = array();
		
		foreach($items as $item)
		{
			$data[] = $item['EmdeonFavoriteTestGroupDetail'];
		}
		
		return $data;
	}
	
	public function execute(&$controller, $task)
	{
		$emdeon_xml_api = new Emdeon_XML_API();
		
		$labs = $emdeon_xml_api->getLabs();
		$controller->set("labs", $labs);
		
		switch($task)
		{
			case "get_test_codes":
			{
				echo json_encode($this->getTestCodes($controller->data['test_group_id']));
				exit;
			} break;
			case "save_test_group_ajax":
			{
				$this->save($controller->data);
				$test_group_id = $this->getLastInsertId();
				
				$test_code_data = array();
				
				foreach($controller->data['testcodes'] as $testcodes)
				{
					$testcodes['test_group_id'] = $test_group_id;
					$test_code_data[] = $testcodes;
				}
				
				$this->EmdeonFavoriteTestGroupDetail->saveAll($test_code_data);
					
				echo json_encode(array());
				exit;
			} break;
			case "get_test_group_by_lab":
			{
				echo json_encode($this->getTestGroups($controller->data['lab']));
				exit;
			} break;
			case "addnew":
			{
				if (!empty($controller->data))
				{
					if($this->save($controller->data))
					{
						$test_group_id = $this->getLastInsertId();
						
						$test_code_data = array();
						
						foreach($controller->data['testcodes'] as $testcodes)
						{
							$testcodes['test_group_id'] = $test_group_id;
							$test_code_data[] = $testcodes;
						}
						
						$this->EmdeonFavoriteTestGroupDetail->saveAll($test_code_data);
						
						$controller->Session->setFlash(__('Item(s) added.', true));
						$controller->redirect(array('action' => 'favorite_test_groups'));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
					}
				}
			} break;
			case "edit":
			{
				if (!empty($controller->data))
				{
					if($this->save($controller->data))
					{
						$test_group_id = (isset($controller->params['named']['test_group_id'])) ? $controller->params['named']['test_group_id'] : "";
						
						$test_code_data = array();
						
						foreach($controller->data['testcodes'] as $testcodes)
						{
							$testcodes['test_group_id'] = $test_group_id;
							$test_code_data[] = $testcodes;
						}
						
						$this->EmdeonFavoriteTestGroupDetail->deleteByForeignKey($test_group_id);
						$this->EmdeonFavoriteTestGroupDetail->saveAll($test_code_data);
						
						$controller->Session->setFlash(__('Item(s) saved.', true));
						$controller->redirect(array('action' => 'favorite_test_groups'));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
					}
				}
				else
				{
					$test_group_id = (isset($controller->params['named']['test_group_id'])) ? $controller->params['named']['test_group_id'] : "";
					$item = $this->find(
							'first',
							array(
								'conditions' => array('EmdeonFavoriteTestGroup.test_group_id' => $test_group_id)
							)
					);

					$controller->set('EditItem', $controller->sanitizeHTML($item));
				}
			} break;
			case "delete":
			{
				if (!empty($controller->data))
				{
					$test_group_id = $controller->data['EmdeonFavoriteTestGroup']['test_group_id'];
					$delete_count = 0;

					foreach($test_group_id as $delete_id)
					{
						$this->delete($delete_id, true);
						$delete_count++;
					}

					if($delete_count > 0)
					{
						$controller->Session->setFlash($delete_count . __('Item(s) deleted.', true));
					}
				}
				$controller->redirect(array('action' => 'favorite_test_groups'));
			} break;
			default:
			{
				$valid_labs = $emdeon_xml_api->getValidLabs();
				$controller->set('EmdeonFavoriteTestGroup', $controller->sanitizeHTML($controller->paginate('EmdeonFavoriteTestGroup', array('EmdeonFavoriteTestGroup.lab' => $valid_labs, 'EmdeonFavoriteTestGroup.modified_user_id' => $controller->user_id))));
				
				$controller->loadModel("PracticeSetting");
		        $practice_data = $controller->PracticeSetting->find('first');
			    $controller->set('PracticeData', $controller->sanitizeHTML($practice_data));
			} break;
		}
	}
}

?>