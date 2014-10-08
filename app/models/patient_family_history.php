<?php

class PatientFamilyHistory extends AppModel 
{ 
	public $name = 'PatientFamilyHistory'; 
	public $primaryKey = 'family_history_id';
	public $useTable = 'patient_family_history';
	
	public $actsAs = array(
		'Auditable' => 'Medical Information - HX - Family History',
		'Unique' => array('patient_id', 'name', 'relationship', 'problem')
	);
	
	function getFamilyHistory( $patient_id )
	{
		


			$data = $this->find('all', array(
				'conditions' => array('PatientFamilyHistory.patient_id' => $patient_id)
			));
		
		
		
		
		return array_chunk($data, 10);
	}
	
	public function execute(&$controller, $patient_id, $task, $encounter_id)
	{
		$controller->set('encounter_id', $encounter_id);
		$controller->set('patient_id', $patient_id);
		
		$controller->set('PatientFamilyHistory', $controller->sanitizeHTML($controller->PatientFamilyHistory->find('all')));
		if (!empty($controller->data) && ($task == "addnew" || $task == "edit"))
        {
            $controller->data['PatientFamilyHistory']['encounter_id'] = $encounter_id;
            $controller->data['PatientFamilyHistory']['patient_id'] = $patient_id;
            $controller->data['PatientFamilyHistory']['modified_user_id'] = $controller->user_id;
            $controller->data['PatientFamilyHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
        }
        switch ($task)
        {
            case "load_relationship":
            { 
		$showall = (isset($controller->params['named']['showall'])) ? $controller->params['named']['showall'] : "";
			     $search_keyword = $controller->data['autocomplete']['keyword'];
                 $data_array = array('Mother', 'Father', 'Maternal Grandmother', 'Maternal Grandfather', 'Paternal Grandmother', 'Paternal Grandfather', 'Sister', 'Brother', 'Aunt', 'Uncle', 'Cousin');
		if(empty($showall))
		{
				$matches = array();
				foreach($data_array as $data_array){
					if(stripos($data_array, $search_keyword) !== false){
						$matches[] = $data_array;
					}
				}
 				$matches = array_slice($matches, 0, 5);
		   echo implode("\n", $matches);
		} else {
		   echo json_encode($data_array);
		}

                exit();
            }
            break;
			
			case "load_problem":
            { 
			     $search_keyword = $controller->data['autocomplete']['keyword'];
                 $data_array = array('Asthma', 'Back Problems', 'Cancer', 'Child Birth', 'Diabetes Type II', 'Heart Disease', 'Hypertension', 'Mental Disorders', 'Osteoarthritis', 'Trauma Disorders');
				 
                 $matches = array();
				foreach($data_array as $data_array){
					if(stripos($data_array, $search_keyword) !== false){
						$matches[] = $data_array;
					}
				}
 
				$matches = array_slice($matches, 0, 5);
                echo implode("\n", $matches);
                exit();
            }
            break;
			
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->PatientFamilyHistory->create();
                    $controller->PatientFamilyHistory->save($controller->data);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }

                $controller->loadModel("FavoriteMedical");
		$favitems = $controller->FavoriteMedical->find('all', array(
        'conditions' => array('FavoriteMedical.user_id' => $_SESSION['UserAccount']['user_id']),
        'order' => array(
            'FavoriteMedical.diagnosis ASC',
        ),        
        ));
        	$controller->set('favitems', $controller->sanitizeHTML($favitems));
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->PatientFamilyHistory->save($controller->data);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $family_history_id = (isset($controller->params['named']['family_history_id'])) ? $controller->params['named']['family_history_id'] : "";
                    //echo $family_history_id;
                    $items = $controller->PatientFamilyHistory->find('first', array('conditions' => array('PatientFamilyHistory.family_history_id' => $family_history_id)));
                    
		    $last_user_inf=array();
		    // who was last person to edit this record?			
		    $last_user=$controller->UserAccount->getUserByID($items['PatientFamilyHistory']['modified_user_id']);
		    $last_user_inf['role_id']=$last_user->role_id;
		    $last_user_inf['full_name']=$last_user->full_name;
		    $controller->set('last_user',$last_user_inf);

                    $controller->set('EditItem', $controller->sanitizeHTML($items));

                $controller->loadModel("FavoriteMedical");
		$favitems = $controller->FavoriteMedical->find('all', array(
        'conditions' => array('FavoriteMedical.user_id' => $_SESSION['UserAccount']['user_id']),
        'order' => array(
            'FavoriteMedical.diagnosis ASC',
        ),        
        ));
        	$controller->set('favitems', $controller->sanitizeHTML($favitems));

                }
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['PatientFamilyHistory']['family_history_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->PatientFamilyHistory->delete($id, false);
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {

								$controller->paginate['PatientFamilyHistory'] = array(
									'conditions' => array('PatientFamilyHistory.patient_id' => $patient_id),
									'order' => array('PatientFamilyHistory.modified_timestamp' => 'desc')
								);
								
						
							$controller->set('PatientFamilyHistory', $controller->sanitizeHTML($controller->paginate('PatientFamilyHistory')));
            }
        }
	}
}

?>
