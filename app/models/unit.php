<?php

class Unit extends AppModel 
{
	public $name = 'Unit';
	public $primaryKey = 'unit_id';
	public $useTable = 'units';
	public $order = "description";
	
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
                    
                    $unit_items = $controller->Unit->find('all', array('conditions' => array('OR' => array('Unit.code LIKE ' => '%' . $search_keyword . '%', 'Unit.description LIKE ' => '%' . $search_keyword . '%')), 'limit' => $search_limit));
                    $data_array = array();
                    
                    foreach ($unit_items as $unit_item)
                    {
                        $data_array[] = $unit_item['Unit']['description'] . ' [' . $unit_item['Unit']['code'] . ']|' . $unit_item['Unit']['code'];
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