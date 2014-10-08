<?php

class EmdeonFavoriteTestCode extends AppModel 
{
	public $name = 'EmdeonFavoriteTestCode';
	public $primaryKey = 'test_code_id';
	public $useTable = 'emdeon_favorite_test_codes';

	public function beforeSave($options)
	{
		$this->data['EmdeonFavoriteTestCode']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EmdeonFavoriteTestCode']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function afterFind($results, $primary)
	{
		for($i = 0; $i < count($results); $i++)
		{
			if(isset($results[$i][$this->alias]['test_code_id']))
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
	
	public function getTestCodes($lab)
	{
		$items = $this->find('all', array('conditions' => array('EmdeonFavoriteTestCode.lab' => $lab, 'EmdeonFavoriteTestCode.modified_user_id' => $_SESSION['UserAccount']['user_id'])));
		
		$data = array();
		
		foreach($items as $item)
		{
			$data[] = $item['EmdeonFavoriteTestCode'];
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
			case "get_test_code_by_lab":
			{
				echo json_encode($this->getTestCodes($controller->data['lab']));
				exit;
			} break;
		
			case "paginate_test_code_by_lab":
			{
				
				$controller->paginate['EmdeonFavoriteTestCode'] = array(
					'limit' => 10
				);
				
				$items = $controller->paginate('EmdeonFavoriteTestCode', array(
					'EmdeonFavoriteTestCode.lab' => $controller->params['named']['lab'], 
					'EmdeonFavoriteTestCode.modified_user_id' => $_SESSION['UserAccount']['user_id']
				));
				
				$controller->set('items', $items);
				
				//echo json_encode($this->getTestCodes($controller->data['lab']));
				$controller->layout = 'empty';
				echo $controller->render('/preferences/favorite_test_code_paginate');
				exit;
			} break;
		
			case "addnew":
			{
				if (!empty($controller->data))
				{
					if($this->saveAll($controller->data))
					{
						$controller->Session->setFlash(__('Item(s) added.', true));
						$controller->redirect(array('action' => 'favorite_test_codes'));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
					}
				}
			} break;
			case "edit":
			{
				$ajaxmode = (isset($controller->params['named']['ajaxmode'])) ? $controller->params['named']['ajaxmode'] : "";
				
				if($ajaxmode == '1')
				{
					$controller->layout = 'empty';
				}
				
				$lab = (isset($controller->params['named']['lab'])) ? $controller->params['named']['lab'] : "";
				$orderable = (isset($controller->params['named']['orderable'])) ? $controller->params['named']['orderable'] : "";
				$document = (isset($controller->params['named']['document'])) ? $controller->params['named']['document'] : "";
				
				$aoe_list = $emdeon_xml_api->getTestAoe($lab, $orderable);
				$controller->set("aoe_list", $aoe_list);
				
				if(strlen($document) > 0)
				{
					//$document_content = $emdeon_xml_api->getTestDocument($document);
					//$controller->set("document_content", $document_content['body_text']);
				}
				else
				{
					//$controller->set("document_content", 'No Document Attached.');
				}
			} break;
			case "delete":
			{
				if (!empty($controller->data))
				{
					$test_code_id = $controller->data['EmdeonFavoriteTestCode']['test_code_id'];
					$delete_count = 0;

					foreach($test_code_id as $test_code_id)
					{
						$this->delete($test_code_id, false);
						$delete_count++;
					}

					if($delete_count > 0)
					{
						$controller->Session->setFlash($delete_count . __('Item(s) deleted.', true));
					}
				}
				$controller->redirect(array('action' => 'favorite_test_codes'));
			} break;
			default:
			{
				$valid_labs = $emdeon_xml_api->getValidLabs();
				$controller->set('EmdeonFavoriteTestCode', $controller->sanitizeHTML($controller->paginate('EmdeonFavoriteTestCode', array('EmdeonFavoriteTestCode.lab' => $valid_labs, 'EmdeonFavoriteTestCode.modified_user_id' => $controller->user_id))));
				
				$controller->loadModel("PracticeSetting");
		        $practice_data = $controller->PracticeSetting->find('first');
			    $controller->set('PracticeData', $controller->sanitizeHTML($practice_data));
			} break;
		}
	}
}

?>