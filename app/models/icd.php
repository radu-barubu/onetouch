<?php

class Icd extends AppModel {
	public $name = 'Icd';
	public $primaryKey = null;
	public $useTable = false;  
  
  public function setVersion($icd = false) {
    
    
    if ($icd === false) {
      $settings = ClassRegistry::init('PracticeSetting')->getSettings();
      $icd = intval($settings->icd_version);
    }
        
    
    $icd = intval($icd);
    switch ($icd) {
      
      case 10:
        $this->setSource('icd10');
        $this->primaryKey = 'icd10';
        break;
      
      default:
        $this->setSource('icd9');
        $this->primaryKey = 'icd9';
        break;
    }
    
    
  }
  
  
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
						$icd_items = $controller->Icd->find('all', array(
							'conditions' => array(
								'AND' => array(
									'Icd.code NOT LIKE' => '%.%', 
									array(
										'OR' => array(
											array('Icd.code LIKE ' => $search_keyword . '%'), 
											array('Icd.description LIKE ' => $search_keyword . '%'), 
											array('Icd.description LIKE ' => '% ' . $search_keyword . '%'), 
											array('Icd.description LIKE ' => '%[' . $search_keyword . '%')
										)
									)
								)
							),
							'limit' => $search_limit,
							'order' => array('Icd.citation_count' => 'DESC')
						));
					} else {
						$icd_items = $controller->Icd->find('all', array(
							'conditions' => array(
								'OR' => array(
									array('Icd.code LIKE ' => $search_keyword . '%'), 
									array('Icd.description LIKE ' => $search_keyword . '%'), 
									array('Icd.description LIKE ' => '% ' . $search_keyword . '%'), 
									array('Icd.description LIKE ' => '%[' . $search_keyword . '%')
								)
							), 
							'limit' => $search_limit,
							'order' => array('Icd.citation_count' => 'DESC')
						));
					}
					$data_array = array();
					foreach ($icd_items as $icd_item){
						$data_array[] = $icd_item['Icd']['description'] . ' [' . $icd_item['Icd']['code'] . ']|' . $icd_item['Icd']['code'];
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
	public function updateCitationCount($icd)
	{
		$this->recursive = -1;
		$items = $this->find('first',array('conditions' => array('Icd.code' => "$icd")));
		if($items && $items['Icd'][$this->primaryKey])
		{
		  $data['Icd'][$this->primaryKey]=$items['Icd'][$this->primaryKey];
		  $data['Icd']['citation_count']=$items['Icd']['citation_count'] + 1;
		  $this->save($data);
		}
	}  
  
  
  
  
}
