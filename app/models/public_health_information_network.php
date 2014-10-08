<?php

class PublicHealthInformationNetwork extends AppModel 
{ 
	public $name = 'PublicHealthInformationNetwork'; 
	public $primaryKey = 'cdc';
	public $useTable = 'public_health_information_network';
	
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
                    
                    $cdc_items = $controller->PublicHealthInformationNetwork->find('all', array('conditions' => array('OR' => array('PublicHealthInformationNetwork.code LIKE ' => '%' . $search_keyword . '%', 'PublicHealthInformationNetwork.event LIKE ' => '%' . $search_keyword . '%'))));
                    $data_array = array();
                    
                    foreach ($cdc_items as $cdc_item)
                    {
                        $data_array[] = $cdc_item['PublicHealthInformationNetwork']['event'] . ' [' . $cdc_item['PublicHealthInformationNetwork']['code'] . ']|' . $cdc_item['PublicHealthInformationNetwork']['cdc'];
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