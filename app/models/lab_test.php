<?php

class LabTest extends AppModel 
{ 
    public $name = 'LabTest'; 
	public $primaryKey = 'LabTestID';
	
	public function execute(&$controller, $task)
	{
		if (!empty($controller->data))
        {
            $search_keyword = $controller->data['autocomplete']['keyword'];
            $search_limit = $controller->data['autocomplete']['limit'];
            
            $lab_test_items = $controller->LabTest->find('all', array('conditions' => array('OR' => array('LabTest.TestDescription LIKE ' => '' . $search_keyword . '%')), 'limit' => $search_limit));
            
            
            switch ($task)
            {
                case "load_autocomplete":
                {
                    $data_array = array();
                    foreach ($lab_test_items as $lab_test_item)
                    {
                        $data_array[] = $lab_test_item['LabTest']['TestDescription'];
                    }
                    echo implode("\n", array_unique($data_array));
                }
                break;
            }
        }
        exit();
		
	}
}


?>