<?php

class Icd9 extends AppModel 
{
	public $name = 'Icd9';
	public $primaryKey = 'icd9';
	public $useTable = 'icd9';
	
	public function execute(&$controller, $task){
		switch ($task){
		case 'load_Icd9_autocomplete':
		case "load_autocomplete":
			{
				if (!empty($controller->data)){
					$search_keyword = $controller->data['autocomplete']['keyword'];
					$search_limit = $controller->data['autocomplete']['limit'];
					$series = (isset($controller->params['named']['series'])) ? $controller->params['named']['series'] : "false";
					if ($series == 'true'){
						$icd9_items = $controller->Icd9->find('all', array(
							'conditions' => array(
								'AND' => array(
									'Icd9.code NOT LIKE' => '%.%', 
									array(
										'OR' => array(
											array('Icd9.code LIKE ' => $search_keyword . '%'), 
											array('Icd9.description LIKE ' => $search_keyword . '%'), 
											array('Icd9.description LIKE ' => '% ' . $search_keyword . '%'), 
											array('Icd9.description LIKE ' => '%[' . $search_keyword . '%')
										)
									)
								)
							),
							'limit' => $search_limit,
							'order' => array('Icd9.citation_count' => 'DESC')
						));
					} else {
						$icd9_items = $controller->Icd9->find('all', array(
							'conditions' => array(
								'OR' => array(
									array('Icd9.code LIKE ' => $search_keyword . '%'), 
									array('Icd9.description LIKE ' => $search_keyword . '%'), 
									array('Icd9.description LIKE ' => '% ' . $search_keyword . '%'), 
									array('Icd9.description LIKE ' => '%[' . $search_keyword . '%')
								)
							), 
							'limit' => $search_limit,
							'order' => array('Icd9.citation_count' => 'DESC')
						));
					}
					$data_array = array();
					foreach ($icd9_items as $icd9_item){
						$data_array[] = $icd9_item['Icd9']['description'] . ' [' . $icd9_item['Icd9']['code'] . ']|' . $icd9_item['Icd9']['code'];
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
	public function updateCitationCount($icd9)
	{
		$this->recursive = -1;
		$items=$this->find('first',array('conditions' => array('Icd9.code' => "$icd9")));
		if($items['Icd9']['icd9'])
		{
		  $data['Icd9']['icd9']=$items['Icd9']['icd9'];
		  $data['Icd9']['citation_count']=$items['Icd9']['citation_count'] + 1;
		  $this->save($data);
		}
	}
  
  public function getSpecifics($icd9){
    
    $search = $icd9;
    if (stristr($icd9, '.') === false) {
      $search .= '.'; 
    } 
    
    $search .= '%';
    
    $specifics = $this->find('all', array(
        'conditions' => array(
            'Icd9.code LIKE' => $search,
        ),
    ));
    
    return $specifics;
  }
  
  
  
}

?>