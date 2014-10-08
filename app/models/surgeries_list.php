<?php

class SurgeriesList extends AppModel 
{
	public $name = 'SurgeriesList';
	public $primaryKey = 'id';
	public $useTable = 'surgeries_list';
	
	public function execute(&$controller, $task)
	{
		if (!empty($controller->data))
        {
            $search_keyword = $controller->data['autocomplete']['keyword'];
            $search_limit = $controller->data['autocomplete']['limit'];
            
            $surgeries_list_items = $controller->SurgeriesList->find('all', array('conditions' => array('OR' => array('SurgeriesList.description LIKE ' => '' . $search_keyword . '%')), 'limit' => $search_limit));
            
            
            switch ($task)
            {
                case "load_autocomplete":
                {
                    $data_array = array();
                    foreach ($surgeries_list_items as $surgeries_list_item)
                    {
                        $data_array[] = $surgeries_list_item['SurgeriesList']['description'];
                    }
                    echo implode("\n", array_unique($data_array));
                }
                break;
                case "load_autocomplete2":
                {
                    $data_array2 = array();
                    foreach ($surgeries_list_items as $surgeries_list_item2)
                    {
                        $mname = explode(' ', $surgeries_list_item2['SurgeriesList']['description']);
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