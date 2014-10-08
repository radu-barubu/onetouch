<?php

class Education extends AppModel 
{
	public $name = 'Education';
	public $primaryKey = 'education';
	public $useTable = 'educations';
	
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
                    
                    $education_items = $controller->Education->find('all', array(
						'conditions' => array('OR' => array('Education.code LIKE ' => '%' . $search_keyword . '%', 'Education.description LIKE ' => '%' . $search_keyword . '%')),
						'limit' => $search_limit
					));
                    $data_array = array();
                    
                    foreach ($education_items as $education_item)
                    {
                        $data_array[] = $education_item['Education']['code'] . ' [' . $education_item['Education']['description'] . ']|' . $education_item['Education']['code'];
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