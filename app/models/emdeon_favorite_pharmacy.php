<?php

class EmdeonFavoritePharmacy extends AppModel 
{
	public $name = 'EmdeonFavoritePharmacy';
	public $primaryKey = 'favorite_pharmacy_id';
	public $useTable = 'emdeon_favorite_pharmacy';


	public function beforeSave($options)
	{
		$this->data['EmdeonFavoritePharmacy']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EmdeonFavoritePharmacy']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	

	public function execute(&$controller, $task)
	{
		$emdeon_xml_api = new Emdeon_XML_API();
        
        if($task == "addnew" || $task == "edit")
        {
            $caregivers = $emdeon_xml_api->getCaregivers();
            $controller->set("caregivers", $caregivers);            
      
		}
		switch($task)
		{
			case "addnew":
			{				
				if (!empty($controller->data))
				{
					$pharmacy_details = $controller->data;
					$orgpreference_id = $emdeon_xml_api->executeFavoritePharmacy('add', $pharmacy_details, '');
		
					if($orgpreference_id != '')
				    {
					    $controller->data['pharmacy_orgpreference'] = $orgpreference_id;
					    if($this->save($controller->data))
						{
							$controller->Session->setFlash(__('Item(s) added.', true));
							$controller->redirect(array('action' => 'favorite_pharmacy'));
						}
						else
						{
							$controller->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
						}
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
				    $pharmacy_details = $controller->data;
				    $result = $emdeon_xml_api->executeFavoritePharmacy('update', $pharmacy_details, $controller->data['pharmacy_orgpreference']);

					if($this->save($controller->data))
					{
						$favorite_pharmacy_id = (isset($controller->params['named']['favorite_pharmacy_id'])) ? $controller->params['named']['favorite_pharmacy_id'] : "";
						
						$controller->Session->setFlash(__('Item(s) saved.', true));
						$controller->redirect(array('action' => 'favorite_pharmacy'));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
					}
					
			
				}
				else
				{
					$favorite_pharmacy_id = (isset($controller->params['named']['favorite_pharmacy_id'])) ? $controller->params['named']['favorite_pharmacy_id'] : "";
					$item = $this->find(
							'first',
							array(
								'conditions' => array('EmdeonFavoritePharmacy.favorite_pharmacy_id' => $favorite_pharmacy_id)
							)
					);

					$controller->set('EditItem', $controller->sanitizeHTML($item));
				}
			} break;
			case "delete":
			{
				if (!empty($controller->data))
				{
					$pharmacy_id = $controller->data['EmdeonFavoritePharmacy']['favorite_pharmacy_id'];
					$delete_count = 0;

					foreach($pharmacy_id as $delete_id)
					{
						$pharmacy_ids = explode('|',$delete_id);
						$this->delete($pharmacy_ids[0], true);
						$delete_count++;
						
						$object_param = array();
                        $object_param['orgpreference'] = $pharmacy_ids[1];
                        $result = $emdeon_xml_api->execute("orgpreference", "delete", $object_param);
					}

					if($delete_count > 0)
					{
						$controller->Session->setFlash($delete_count . __('Item(s) deleted.', true));
					}
				}
				$controller->redirect(array('action' => 'favorite_pharmacy'));
			} break;
			default:
			{
				$controller->set('EmdeonFavoritePharmacy', $controller->sanitizeHTML($controller->paginate('EmdeonFavoritePharmacy', array('EmdeonFavoritePharmacy.modified_user_id' => $controller->user_id))));
				
				$controller->loadModel("PracticeSetting");
		        $practice_data = $controller->PracticeSetting->find('first');
			    $controller->set('PracticeData', $controller->sanitizeHTML($practice_data));
			} break;
		}
	}

}

?>