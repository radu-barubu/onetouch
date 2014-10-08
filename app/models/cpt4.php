<?php

class Cpt4 extends AppModel 
{
	public $name = 'Cpt4';
	public $primaryKey = 'cpt4';
	public $useTable = 'cpt4';
	
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
                    
                    $cpt4_items = $controller->Cpt4->find('all', array(
						'conditions' => array('OR' => array('Cpt4.code LIKE ' => '%' . $search_keyword . '%', 'Cpt4.description LIKE ' => '%' . $search_keyword . '%')),
						'order' => array('Cpt4.citation_count' => 'DESC'),
						'limit' => $search_limit
					));
                    $data_array = array();
                    
                    foreach ($cpt4_items as $cpt4_item)
                    {
                        $data_array[] = $cpt4_item['Cpt4']['description'] . ' [' . $cpt4_item['Cpt4']['code'] . ']|' . $cpt4_item['Cpt4']['code'];
                    }
                    
                    echo implode("\n", $data_array);
                }
                exit();
            }
            break;
        }
	}
	
	/*
	* increment the amount of times used for ranking purposes
	*/
	public function updateCitationCount($field,$value)
	{ 
		$this->recursive = -1;
	  if($field == 'cpt_code' || is_numeric($value))
	  {		
		$items=$this->find('first',array('conditions' => array('Cpt4.code' => "$value")));
	  }
	  else
	  {
		if(strpos($value, '[') !== false) {
	  	  list($value,)=explode('[',$value);
	  	  $value=trim($value);
	  	}
		$items=$this->find('first',array('conditions' => array('Cpt4.description' => "$value")));
	  }	
		if($items['Cpt4']['cpt4'])
		{
		  $data['Cpt4']['cpt4']=$items['Cpt4']['cpt4'];
		  $data['Cpt4']['citation_count']=$items['Cpt4']['citation_count'] + 1;
		  $this->save($data);
		  return true;
		}
		else
		{
		  return false;
		}
      }	
}

?>