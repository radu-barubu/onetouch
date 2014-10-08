<?php

class EmdeonFavoritePrescription extends AppModel 
{
	public $name = 'EmdeonFavoritePrescription';
	public $primaryKey = 'rx_preference_id';
	public $useTable = 'emdeon_favorite_prescriptions';


	public function beforeSave($options)
	{
		$this->data['EmdeonFavoritePrescription']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EmdeonFavoritePrescription']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function deleteFavoritePrescription($rx_preference_id)
    {
        $this->delete($rx_preference_id, true);
        return true;
    }

	public function execute(&$controller, $task)
	{
		$emdeon_xml_api = new Emdeon_XML_API();
		
		$labs = $emdeon_xml_api->getLabs();
		$controller->set("labs", $labs);
 
        
        if($task == "addnew" || $task == "edit")
        {
            $caregivers = $emdeon_xml_api->getCaregivers();
            $controller->set("caregivers", $caregivers);            
       
            $unit_of_measures = $emdeon_xml_api->getSystemCode('RXUOM');
            $controller->set("unit_of_measures", $unit_of_measures); 

			$sigverb_list = $emdeon_xml_api->getSystemCode('SIGVERB');
			$controller->set("sigverb_list", $sigverb_list);
			
			$sigform_list = $emdeon_xml_api->getSystemCode('SIGFORM');
			$controller->set("sigform_list", $sigform_list);
			
			$sigroute_list = $emdeon_xml_api->getSystemCode('SIGROUTE');
			$controller->set("sigroute_list", $sigroute_list);
			
			$sigfreq_list = $emdeon_xml_api->getSystemCode('SIGFREQ');
			$controller->set("sigfreq_list", $sigfreq_list);  
			
			$sigmod_list = $emdeon_xml_api->getSystemCode('SIGMODIF');
			$controller->set("sigmod_list", $sigmod_list);
		}
		switch($task)
		{
			case "addnew":
			{				
				if (!empty($controller->data))
				{
					$controller->data['user_id'] = $controller->user_id;
					$controller->data['icd_description'] = $controller->data['diagnosis'];
					$controller->data['daw'] = isset($controller->data['daw'])?'y':'n';
					$rx_details = $controller->data;
					
					if($this->save($controller->data))
					{
						$controller->Session->setFlash(__('Item(s) added.', true));
						$controller->redirect(array('action' => 'favorite_prescriptions'));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
					}

					/*
                    $rx_preference_unique_id = $emdeon_xml_api->executeRxPreference('add', $rx_details, '');
					
					
   				    if($rx_preference_unique_id != '')
				    {
					    $controller->data['rx_preference_unique_id'] = $rx_preference_unique_id;
					    if($this->save($controller->data))
						{
							$controller->Session->setFlash(__('Item(s) added.', true));
							$controller->redirect(array('action' => 'favorite_prescriptions'));
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
					*/
				}
			} break;
			case "edit":
			{				
				if (!empty($controller->data))
				{
					$controller->data['user_id'] = $controller->user_id;
					$controller->data['icd_description'] = $controller->data['diagnosis'];
					$controller->data['daw'] = isset($controller->data['daw'])?'y':'n';
					$rx_details = $controller->data;
					
					if($this->save($controller->data))
					{
						$rx_preference_id = (isset($controller->params['named']['rx_preference_id'])) ? $controller->params['named']['rx_preference_id'] : "";
						$controller->Session->setFlash(__('Item(s) saved.', true));
						$controller->redirect(array('action' => 'favorite_prescriptions'));
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
					}

					/*
                    $rx_preference_unique_id = $emdeon_xml_api->executeRxPreference('update', $rx_details, $controller->data['rx_preference_unique_id']);
					$rx_preference_id = (isset($controller->params['named']['rx_preference_id'])) ? $controller->params['named']['rx_preference_id'] : "";
					if($rx_preference_id != '')
				    {
						if($this->save($controller->data))
						{
							$rx_preference_id = (isset($controller->params['named']['rx_preference_id'])) ? $controller->params['named']['rx_preference_id'] : "";
							
							$controller->Session->setFlash(__('Item(s) saved.', true));
							$controller->redirect(array('action' => 'favorite_prescriptions'));
						}
						else
						{
							$controller->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
						}
					}
					else
					{
						$controller->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
					}
					*/
				}
				else
				{
					$rx_preference_id = (isset($controller->params['named']['rx_preference_id'])) ? $controller->params['named']['rx_preference_id'] : "";
					$item = $this->find(
							'first',
							array(
								'conditions' => array('EmdeonFavoritePrescription.rx_preference_id' => $rx_preference_id)
							)
					);

					$controller->set('EditItem', $controller->sanitizeHTML($item));
				}
			} break;
			case "delete":
			{
				if (!empty($controller->data))
				{
					$rx_preference_id = $controller->data['EmdeonFavoritePrescription']['rx_preference_id'];
					$delete_count = 0;

					foreach($rx_preference_id as $delete_id)
					{
						$rx_ids = explode('|',$delete_id);
						$this->delete($rx_ids[0], true);
						$delete_count++;
						
						$object_param = array();
                        $object_param['rxpreference'] = $rx_ids[1];
                        $result = $emdeon_xml_api->execute("rxpreference", "delete", $object_param);
					}

					if($delete_count > 0)
					{
						$controller->Session->setFlash($delete_count . __('Item(s) deleted.', true));
					}
				}
				$controller->redirect(array('action' => 'favorite_prescriptions'));
			} break;
			default:
			{
				$controller->set('EmdeonFavoritePrescription', $controller->sanitizeHTML($controller->paginate('EmdeonFavoritePrescription', array('EmdeonFavoritePrescription.user_id' => $controller->user_id))));
				
				$controller->loadModel("PracticeSetting");
		        $practice_data = $controller->PracticeSetting->find('first');
			    $controller->set('PracticeData', $controller->sanitizeHTML($practice_data));
			} break;
		}
	}
}

?>