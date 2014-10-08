<?php

class InjectionList extends AppModel 
{
	public $name = 'InjectionList';
	public $primaryKey = 'id';
	public $useTable = 'injections_list';
	
	public function execute(&$controller, $task)
	{
		if (!empty($controller->data))
        {
            $search_keyword = $controller->data['autocomplete']['keyword'];
            $search_limit = $controller->data['autocomplete']['limit'];
            
            $injection_list_items = $controller->InjectionList->find('all', array('conditions' => array('OR' => array('InjectionList.description LIKE ' => '' . $search_keyword . '%')), 'limit' => $search_limit));
            
            
            switch ($task)
            {
                case "load_autocomplete":
                {
                    $data_array = array();
                    foreach ($injection_list_items as $injection_list_item)
                    {
                        $data_array[] = $injection_list_item['InjectionList']['description'] . ' ' . $injection_list_item['InjectionList']['strength'];
                    }
                    echo implode("\n", array_unique($data_array));
                }
                break;
                case "load_autocomplete2":
                {
                    $data_array2 = array();
                    foreach ($injection_list_items as $injection_list_item2)
                    {
                        $mname = explode(' ', $injection_list_item2['InjectionList']['description']);
                        $data_array2[] = $mname[0];
                    }
                    echo implode("\n", array_unique($data_array2));
                }
                break;
            }
        }
        exit();
	}
}

?>