<?php

class PatientRadiologyResult extends AppModel 
{
	public $name = 'PatientRadiologyResult'; 
	public $primaryKey = 'radiology_result_id';
	public $useTable = 'patient_radiology_results';	
	
	public $actsAs = array(
		'Auditable' => 'Medical Information - Radiology - Outside Radiology',
	);
	
	public function execute(&$controller, $task, $encounter_id, $patient_id)
	{
		$controller->set('patient_id', $patient_id);
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
            
            case "labname_load":
            {
                if (!empty($controller->data))
                {
                    $search_keyword = $controller->data['autocomplete']['keyword'];
					 $search_limit = $controller->data['autocomplete']['limit'];
                    $lab_items = $controller->DirectoryLabFacility->find('all', array('conditions' => array('DirectoryLabFacility.lab_facility_name LIKE ' => '%' . $search_keyword . '%'), 'limit' => $search_limit));
                    $data_array = array();
                    
                    foreach ($lab_items as $lab_item)
                    {
                        $data_array[] = $lab_item['DirectoryLabFacility']['lab_facility_name'] . '|' . $lab_item['DirectoryLabFacility']['address_1'] . '|' . $lab_item['DirectoryLabFacility']['address_2'] . '|' . $lab_item['DirectoryLabFacility']['city'] . '|' . $lab_item['DirectoryLabFacility']['state'] . '|' . $lab_item['DirectoryLabFacility']['zip_code'] . '|' . $lab_item['DirectoryLabFacility']['country'];
                    }
                    
                    echo implode("\n", $data_array);
                }
                exit();
            }
            break;
            
            case "addnew":
            {
                if (!empty($controller->data))
                {
                    $controller->data['PatientRadiologyResult']['patient_id'] = $patient_id;
                    $controller->data['PatientRadiologyResult']['plan_radiology_id'] = 0;
                    $controller->data['PatientRadiologyResult']['diagnosis'] = $controller->data['PatientRadiologyResult']['diagnosis'];
                    $controller->data['PatientRadiologyResult']['icd_code'] = $controller->data['PatientRadiologyResult']['icd_code'];
                    $controller->data['PatientRadiologyResult']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['PatientRadiologyResult']['date_ordered']));
                    $controller->data['PatientRadiologyResult']['report_date'] = __date("Y-m-d", strtotime($controller->data['PatientRadiologyResult']['report_date']));
                    $controller->data['PatientRadiologyResult']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $controller->data['PatientRadiologyResult']['modified_user_id'] = $controller->user_id;
                    $controller->PatientRadiologyResult->create();
                    $controller->PatientRadiologyResult->save($controller->data);
                    $controller->PatientRadiologyResult->saveAudit('New');
										
										
										$attachment = trim($controller->data['PatientRadiologyResult']['attachment']);
										if ($attachment) {
											
											if (file_exists($controller->paths['temp'] . $attachment)) {
												
												$controller->paths['patient_encounter_radiology'] = 
													$controller->paths['patients'] . $patient_id . DS . 'radiology' . DS . $encounter_id . DS;
												
												UploadSettings::createIfNotExists($controller->paths['patient_encounter_radiology']);
												
												copy($controller->paths['temp'] . $attachment, $controller->paths['patient_encounter_radiology'] . $attachment);
											}
										}												
										
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
                    $controller->PatientRadiologyResult->save($controller->data);
                    
                    $controller->PatientRadiologyResult->saveAudit('Update');
                    

										$attachment = isset($controller->data['PatientRadiologyResult']['attachment']) ? trim($controller->data['PatientRadiologyResult']['attachment']) : '';
										if ($attachment) {
											
											if (file_exists($controller->paths['temp'] . $attachment)) {
												
												$controller->paths['patient_encounter_radiology'] = 
													$controller->paths['patients'] . $patient_id . DS . 'radiology' . DS . $encounter_id . DS;
												
												UploadSettings::createIfNotExists($controller->paths['patient_encounter_radiology']);
												
												copy($controller->paths['temp'] . $attachment, $controller->paths['patient_encounter_radiology'] . $attachment);
											}
										}										
										
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $radiology_result_id = (isset($controller->params['named']['radiology_result_id'])) ? $controller->params['named']['radiology_result_id'] : "";
                    //echo $family_history_id;
                    $items = $controller->PatientRadiologyResult->find('first', array('conditions' => array('PatientRadiologyResult.radiology_result_id' => $radiology_result_id)));
                    
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
                    $ids = $controller->data['PatientRadiologyResult']['radiology_result_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->PatientRadiologyResult->delete($id, false);
                        $ret['delete_count']++;
                    }
                    
                    if ($ret['delete_count'] > 0)
                    {
                        $controller->PatientRadiologyResult->saveAudit('Delete');
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
			    $controller->paginate['PatientRadiologyResult'] = array(
                'conditions' => array('PatientRadiologyResult.patient_id' => $patient_id),
			    'order' => array('PatientRadiologyResult.modified_timestamp' => 'desc')
                );
                $controller->set('PatientRadiologyResult', $controller->sanitizeHTML($controller->paginate('PatientRadiologyResult')));
                //$controller->set('PatientRadiologyResult', $controller->sanitizeHTML($controller->paginate('PatientRadiologyResult', array('PatientRadiologyResult.patient_id' => $patient_id))));
                $controller->PatientRadiologyResult->saveAudit('View');
            }
        }
	}
}


?>