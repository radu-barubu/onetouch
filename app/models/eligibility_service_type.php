<?php

class EligibilityServiceType extends AppModel 
{
	public $name = 'EligibilityServiceType';
	public $primaryKey = 'service_type_id';
	public $useTable = 'eligibility_service_type';

	public function execute(&$controller, $task)
	{
		switch ($task)
        {
            case "load_autocomplete":
            {
                if (!empty($controller->data))
                {
                    $search_keyword = $controller->data['autocomplete']['keyword'];
                    $search_limit = $controller->data['autocomplete']['limit'];
                    
                    $eligibility_service_type_items = $controller->EligibilityServiceType->find('all', array(
						'conditions' => array('OR' => array('EligibilityServiceType.service_type_code LIKE ' => '%' . $search_keyword . '%', 'EligibilityServiceType.description LIKE ' => '%' . $search_keyword . '%')),
						'limit' => $search_limit
					));
                    $data_array = array();
                    
                    foreach ($eligibility_service_type_items as $eligibility_service_type_item)
                    {
                        $data_array[] = $eligibility_service_type_item['EligibilityServiceType']['description'] . ' [' . $eligibility_service_type_item['EligibilityServiceType']['service_type_code'] . ']|' . $eligibility_service_type_item['EligibilityServiceType']['service_type_code'];
                    }
                    
                    echo implode("\n", $data_array);
                }
                exit();
            }
            break;
        }
	}
}

?>