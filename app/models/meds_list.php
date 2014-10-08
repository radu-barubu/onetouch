<?php

class MedsList extends AppModel 
{
	public $name = 'MedsList';
	public $primaryKey = 'id';
	public $useTable = 'meds_list';
	
	public function execute(&$controller, $task)
	{
		if (!empty($controller->data))
        {
            $search_keyword = $controller->data['autocomplete']['keyword'];
            $search_limit = $controller->data['autocomplete']['limit'];
            
			switch ($task)
            {
                case "load_autocomplete":
                {
            	    $meds_list_items = $controller->MedsList->find('all', array('conditions' => array('OR' => array("CONCAT(MedsList.description, ' ', MedsList.strength) LIKE " => '' . $search_keyword . '%', 'MedsList.rxnorm LIKE ' => '%' . $search_keyword . '%')), 'limit' => $search_limit, 'order' => array('MedsList.citation_count' => 'DESC', 'MedsList.description' => 'ASC', 'MedsList.strength' => 'ASC')));
                
                    $data_array = array();
					
                    foreach ($meds_list_items as $meds_list_item)
                    {
						$strengths = explode(" / ", $meds_list_item['MedsList']['strength']);
						$medications = explode(" / ", $meds_list_item['MedsList']['description']);
						
						$strength = $strengths[0];
						list($strength_val, $unit) = explode(" ", $strength);
							
                        $data_array[] = trim($meds_list_item['MedsList']['description'] . ' ' . $meds_list_item['MedsList']['strength'] . '|' . $meds_list_item['MedsList']['rxnorm'] . '|' . $meds_list_item['MedsList']['type'] . '|' . $meds_list_item['MedsList']['strength'] . '|' . $unit . '|'. $meds_list_item['MedsList']['id'] );
                    }
					
					$data_array = array_unique($data_array);
					
                    echo implode("\n", $data_array);
                }
                break;
                case "load_autocomplete2":
                {	
            		$PracticeSettings = $controller->Session->read('PracticeSetting');
					$rx_setup = $PracticeSettings['PracticeSetting']['rx_setup'];
					if($rx_setup=='Electronic_Dosespot')
					{
						// use Dosespot's Allergy search instead so we can grab medication code ID
						$dosespot_xml_api = new Dosespot_XML_API();    
						$meds_list_items=$dosespot_xml_api->searchAllergy($search_keyword);
					}
					else if($rx_setup == 'Electronic_Emdeon')
					{
						$search_type = $controller->data['type'];
						
						switch($search_type)
						{
							case "Insect":
							case "Plant":
							case "Environment":
								$search_type = 'fdbATAllergenGroup';
								break;
							case "Inhalant":
							case "Food":
								$search_type = 'fdbATIngredient';
								break;
							default:
								$search_type = 'fdbATDrugName';
						}
						
						// use Emdeon's Allergy search instead so we can grab medication allergy_id
						$emdeon_xml_api = new Emdeon_XML_API();    
						$meds_list_items = $emdeon_xml_api->searchAllergy($search_keyword, $search_type);
					}
					else
					{
						$meds_list_items = $controller->MedsList->find('all', array('conditions' => array('OR' => array("CONCAT(MedsList.description, ' ', MedsList.strength) LIKE " => '' . $search_keyword . '%', 'MedsList.rxnorm LIKE ' => '%' . $search_keyword . '%')), 'limit' => $search_limit, 'order' => array('MedsList.description', 'MedsList.strength')));  		
					}  
					          
					$data_array2 = array();
					foreach ($meds_list_items as $meds_list_item2)
					{
						if($rx_setup=='Electronic_Dosespot')
						{
							if (isset($meds_list_item2['Name']) && !stristr($meds_list_item2['Name'], 'Obsolete'))
								$data_array2[] = trim($meds_list_item2['Name']  . '|' . $meds_list_item2['Code'] . '|' . $meds_list_item2['CodeType']);
						}
						else if($rx_setup == 'Electronic_Emdeon')
						{
							$data_array2[] = trim($meds_list_item2['name']  . '|' . $meds_list_item2['id'] . '|' . $meds_list_item2['type']);
						}
						else
						{
							$mname = explode(' ', $meds_list_item2['MedsList']['description']);
							$data_array2[] = $mname[0];
						}	
					}
					
					echo implode("\n", array_unique($data_array2));
				}
                break;
            }
        }
        exit();
	}
	
	/*
	* increment the amount of times used for ranking purposes
	*/
	public function updateCitationCount($medication_id)
	{
		$this->recursive = -1;
		$items=$this->find('first',array('conditions' => array('MedsList.id' => $medication_id)));
		if($items['MedsList']['id'])
		{
		  $data['MedsList']['id']=$medication_id;
		  $data['MedsList']['citation_count']=$items['MedsList']['citation_count'] + 1;
		  $this->save($data);
		}
	}	
}

?>