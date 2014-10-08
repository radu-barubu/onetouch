<?php

class PatientSocialHistory extends AppModel 
{ 
	public $name = 'PatientSocialHistory'; 
	public $primaryKey = 'social_history_id';
	public $useTable = 'patient_social_history';
	
	public $actsAs = array(
		'Auditable' => 'Medical Information - HX - Social History',
		'Unique' => array('patient_id', 'type', 'routine', 'substance')
	);
	
	
	function getSocialHistory($patient_id)
	{
		

			$data = $this->find('all', array(
				'conditions' => array('PatientSocialHistory.patient_id' => $patient_id)
			));

		
		return array_chunk($data, 10);
	}
	
	public function execute(&$controller, $task, $encounter_id, $patient_id)
	{
		$controller->set('encounter_id', $encounter_id);
		$controller->set('patient_id', $patient_id);
		
		$controller->set('PatientSocialHistory', $controller->sanitizeHTML($controller->PatientSocialHistory->find('all', array('conditions' => array('patient_id' => $patient_id)) )));
		
        if (!empty($controller->data) && ($task == "addnew" || $task == "edit"))
        {
	    	$controller->data['PatientSocialHistory']['pets'] = ((isset($controller->params['form']['pets_option_1'])) ? $controller->params['form']['pets_option_1'] : "") . "|" . ((isset($controller->params['form']['pets_option_2'])) ? $controller->params['form']['pets_option_2'] : "") . "|" . ((isset($controller->params['form']['pets_option_3'])) ? $controller->params['form']['pets_option_3'] : "") . "|" . ((isset($controller->params['form']['pets_option_4'])) ? $controller->params['form']['pets_option_4'] : "") . "|" . ((isset($controller->params['form']['pets_option_5'])) ? $controller->params['form']['pets_option_5'] : "");
         
            $controller->data['PatientSocialHistory']['patient_id'] = $patient_id;
			$controller->data['PatientSocialHistory']['encounter_id'] = $encounter_id;
            $controller->data['PatientSocialHistory']['smoking_recodes'] = (trim($controller->data['PatientSocialHistory']['smoking_recodes']) != "") ? $controller->data['PatientSocialHistory']['smoking_recodes'] : 0;
            $controller->data['PatientSocialHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $controller->data['PatientSocialHistory']['modified_user_id'] = $controller->user_id;
        }
        
        switch ($task)
        {
            case "load_Icd9_autocomplete":
            {
            }
            break;
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->PatientSocialHistory->create();
                    $controller->PatientSocialHistory->save($controller->data);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                if (!empty($controller->data))
                {
                    $controller->PatientSocialHistory->save($controller->data);
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $social_history_id = (isset($controller->params['named']['social_history_id'])) ? $controller->params['named']['social_history_id'] : "";
                    $items = $controller->PatientSocialHistory->find('first', array('conditions' => array('PatientSocialHistory.social_history_id' => $social_history_id)));
                    $controller->set('EditItem', $controller->sanitizeHTML($items));

                    $last_user_inf=array();
                    // who was last person to edit this record?
                    $last_user=$controller->UserAccount->getUserByID($items['PatientSocialHistory']['modified_user_id']);
                    if(!empty($last_user)){
                    $last_user_inf['role_id']=$last_user->role_id;
                    $last_user_inf['full_name']=$last_user->full_name;
					}
                    $controller->set('last_user',$last_user_inf);
                }
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;
                
                if (!empty($controller->data))
                {
                    $ids = $controller->data['PatientSocialHistory']['social_history_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->PatientSocialHistory->delete($id, false);
                        $ret['delete_count']++;
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
							

								$controller->paginate['PatientSocialHistory'] = array(
									'conditions' => array('PatientSocialHistory.patient_id' => $patient_id),
									'order' => array('PatientSocialHistory.modified_timestamp' => 'desc')
								);
							
							$controller->set('PatientSocialHistory', $controller->sanitizeHTML($controller->paginate('PatientSocialHistory')));
							
            }
        }
		
	}
}

?>
