<?php

class VaccineList extends AppModel 
{
	public $name = 'VaccineList';
	public $primaryKey = 'id';
	public $useTable = 'vaccine_list';
	
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
                    
                    $vaccine_list_items = $controller->VaccineList->find('all', array('conditions' => array('OR' => array('VaccineList.code LIKE ' => '%' . $search_keyword . '%', 'VaccineList.description LIKE ' => '%' . $search_keyword . '%')), 'limit' => $search_limit));
                    $data_array = array();
                    
                    foreach ($vaccine_list_items as $vaccine_list_item)
                    {
                        $data_array[] = $vaccine_list_item['VaccineList']['description'] . ' [' . $vaccine_list_item['VaccineList']['code'] . ']|' . $vaccine_list_item['VaccineList']['code'];
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