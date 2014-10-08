<?php

class PatientSurgicalHistory extends AppModel 
{ 
	public $name = 'PatientSurgicalHistory'; 
	public $primaryKey = 'surgical_history_id';
	public $useTable = 'patient_surgical_history';
	
	public $actsAs = array(
		'Auditable' => 'Medical Information - HX - Surgical History',
		'Unique' => array('patient_id', 'surgery')
	);
	
	function getSurgicalHistory($patient_id)
	{
		

			$data = $this->find('all', array(
				'conditions' => array('PatientSurgicalHistory.patient_id' => $patient_id)
			));
		
		
		return array_chunk($data, 10);
	}
	
	public function execute(&$controller, $task, $encounter_id, $patient_id)
	{
		$controller->set('encounter_id', $encounter_id);
		$controller->set('patient_id', $patient_id);
		
		$controller->set('PatientSurgicalHistory', $controller->sanitizeHTML($controller->PatientSurgicalHistory->find('all')));
        if (!empty($controller->data) && ($task == "addnew" || $task == "edit"))
        {
            $controller->data['PatientSurgicalHistory']['patient_id'] = $patient_id;
			$controller->data['PatientSurgicalHistory']['encounter_id'] = $encounter_id;
            $controller->data['PatientSurgicalHistory']['hospitalization'] = isset($controller->data['PatientSurgicalHistory']['hospitalization']) ? $controller->data['PatientSurgicalHistory']['hospitalization'] : '';
            $controller->data['PatientSurgicalHistory']['date_from'] = $controller->data['PatientSurgicalHistory']['date_from'] ? intval($controller->data['PatientSurgicalHistory']['date_from']) : "";
            $controller->data['PatientSurgicalHistory']['date_to'] = $controller->data['PatientSurgicalHistory']['date_to'] ? intval($controller->data['PatientSurgicalHistory']['date_to']) : "";
            $controller->data['PatientSurgicalHistory']['reason'] = $controller->data['PatientSurgicalHistory']['reason'];
            $controller->data['PatientSurgicalHistory']['outcome'] = $controller->data['PatientSurgicalHistory']['outcome'];
            $controller->data['PatientSurgicalHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $controller->data['PatientSurgicalHistory']['modified_user_id'] = $controller->user_id;
        }
        
        switch ($task)
        {
            case "load_Icd9_autocomplete":
            {
                if (!empty($controller->data))
                {
                    $controller->Icd->execute($controller, $task);
                }
                exit();
            }
            break;
			case "validate_duplicate":
            {
                if(!empty($controller->data))
                {
                    $all_surgeries = explode(',', $controller->data['surgery']);
					$patient_id = $controller->data['patient_id'];
					$return = array('result' => 'true');
                    foreach($all_surgeries as $surgeries)
					{	
						$surgeries = trim($surgeries);
						if(empty($surgeries))	
							continue; 
						$count = $controller->PatientSurgicalHistory->find('count', array(
							'conditions' => array('surgery' => $surgeries, 'patient_id' => $patient_id)
						));
						if($count) {
							$return = array('result' => 'false');
							break;
						}
					}					
                }
				echo json_encode($return);
                exit();
            }
            break;
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $all_surgeries = explode(',', $controller->data['PatientSurgicalHistory']['surgery']);
					foreach($all_surgeries as $surgeries)
					{	
						$surgeries = trim($surgeries);
						if(empty($surgeries))	
							continue; 
						$controller->data['PatientSurgicalHistory']['surgery'] = $surgeries;
						$controller->PatientSurgicalHistory->create();
						$controller->PatientSurgicalHistory->save($controller->data);
						unset($controller->PatientSurgicalHistory->id);
                    }
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                $controller->layout = "blank";
                if (!empty($controller->data))
                {
                    $controller->PatientSurgicalHistory->save($controller->data);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $surgical_history_id = (isset($controller->params['named']['surgical_history_id'])) ? $controller->params['named']['surgical_history_id'] : "";
                    $items = $controller->PatientSurgicalHistory->row(array('conditions' => array('PatientSurgicalHistory.surgical_history_id' => $surgical_history_id)));
                    
                    $controller->set('EditItem', $controller->sanitizeHTML($items));
                }
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['PatientSurgicalHistory']['surgical_history_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->PatientSurgicalHistory->delete($id, false);
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
							
							$controller->layout = "blank";
							

								$controller->paginate['PatientSurgicalHistory'] = array(
									'conditions' => array('PatientSurgicalHistory.patient_id' => $patient_id),
						'			order' => array('PatientSurgicalHistory.modified_timestamp' => 'desc')
								);

							
							$controller->set('PatientSurgicalHistory', $controller->sanitizeHTML($controller->paginate('PatientSurgicalHistory')));
            }
        }
	}
}

?>