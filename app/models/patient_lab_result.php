<?php

class PatientLabResult extends AppModel 
{
	public $name = 'PatientLabResult'; 
	public $primaryKey = 'lab_result_id';
	public $useTable = 'patient_lab_results';	
	
	public $actsAs = array('Auditable' => 'Medical Information - Labs - Outside Labs');
	
	/**
    * Queries the standard lab results by encounter id and returns a result set array.
    *
    * @param integer $encounter_id Encounter Identifier
    * @return array Array of records
    */
	public function getLabResultList($encounter_id)
	{
		$this->EmdeonLabResult = ClassRegistry::init('EmdeonLabResult');
		$status_list = $this->EmdeonLabResult->loadLabResultStatusList();
		
		$results = $this->find('all', array('conditions' => array('PatientLabResult.encounter_id' => $encounter_id, 'PatientLabResult.order_type' => 'Standard')));
		
		$all_data = array();
		
		foreach($results as $result)
		{
			for($i = 1; $i <= 5; $i++)
			{
				if(strlen($result['PatientLabResult']['test_name'.$i]) == 0)
				{
					continue;
				}
				
				$final_data = array();
				$final_data['test_name'] = $result['PatientLabResult']['test_name'.$i];
				$final_data['date_performed'] = $result['PatientLabResult']['test_report_date'.$i];
				$final_data['status'] = @$status_list[$result['PatientLabResult']['test_result_status'.$i]];
				
				$current_dt = strtotime($result['PatientLabResult']['test_report_date'.$i]);
				if($current_dt === false)
				{
					$current_dt = 0;
				}
				
				$final_data['datetime_flag'] = $current_dt;
				
				
				$all_data[] = $final_data;
			}
		}
		
		return $all_data;
	}
	
	public function checkDuplicate($patient_id, $lab_facility_name, $test_name, $lab_loinc_code, $normal_range, $result_value, $unit)
	{
		$conditions = array();
		$conditions['PatientLabResult.patient_id'] = $patient_id;
		$conditions['PatientLabResult.lab_facility_name'] = $lab_facility_name;
		$conditions['PatientLabResult.test_name1'] = $test_name;
		$conditions['PatientLabResult.lab_loinc_code1'] = $lab_loinc_code;
		$conditions['PatientLabResult.normal_range1'] = $normal_range;
		$conditions['PatientLabResult.result_value1'] = $result_value;
		$conditions['PatientLabResult.unit1'] = strtolower($unit);
		
		$result = $this->find('first', array('conditions' => $conditions));
		
		if($result)
		{
			return true;
		}
		
		return false;
	}
	
	public function get_patient_lab_result_id($order_id)
	{
		$result = $this->find('first', array(
			'order' => array(
					'PatientLabResult.lab_result_id' => 'ASC'
			), 
			'conditions' => array(
				'PatientLabResult.lab_report_id' => $order_id, 
				'PatientLabResult.order_type' => 'Electronic'
			),
			'fields' => array(
				'PatientLabResult.lab_result_id',
			),
		));
		
		if($result)
		{
			return $result['PatientLabResult']['lab_result_id'];
		}
		
		return false;
	}
	
	public function insertLabResult($patient_id, $lab_facility_name, $test_name, $lab_loinc_code, $normal_range, $result_value, $unit, $date_ordered, $report_date)
	{
		if(!$this->checkDuplicate($patient_id, $lab_facility_name, $test_name, $lab_loinc_code, $normal_range, $result_value, $unit))
		{
			$data = array();
			$data['PatientLabResult']['patient_id'] = $patient_id;
			$data['PatientLabResult']['lab_facility_name'] = $lab_facility_name;
			$data['PatientLabResult']['date_ordered'] = $date_ordered;
			$data['PatientLabResult']['report_date'] = $report_date;
			$data['PatientLabResult']['test_name1'] = $test_name;
			$data['PatientLabResult']['lab_loinc_code1'] = $lab_loinc_code;
			$data['PatientLabResult']['normal_range1'] = $normal_range;
			$data['PatientLabResult']['result_value1'] = $result_value;
			$data['PatientLabResult']['unit1'] = strtolower($unit);
			
			$this->create();
			$this->save($data);
		}
	}
	
	public function execute(&$controller, $task, $encounter_id, $patient_id)
	{
		$controller->set('patient_id', $patient_id);
		
		$controller->loadModel("EncounterPlanLab");
		
		$labs_setup = $controller->Session->read("PracticeSetting.PracticeSetting.labs_setup");
		$controller->set("labs_setup", $labs_setup);
		
		$standard_order_list = $controller->EncounterPlanLab->getOrderList($patient_id);
		$controller->set("standard_order_list", $standard_order_list);
		
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
            
			case "download_file":
			{
			    $this->PatientDocument = ClassRegistry::init('PatientDocument');
				$document_id = (isset($controller->params['named']['document_id'])) ? $controller->params['named']['document_id'] : "";
				$items = $this->PatientDocument->find(
						'first', 
						array(
							'conditions' => array('PatientDocument.document_id' => $document_id)
						)
				);
				
				$current_item = $items;
				
				switch($current_item['PatientDocument']['document_type']) {
					case 'fax-received':
						$folder = $controller->paths['received_fax'];
						
						$file = $current_item['PatientDocument']['document_name'];
						$targetPath =  $folder;
						$targetFile =  $targetPath . $file;
						
					break;
					case 'fax-sent':
						$folder = $controller->paths['sent_fax'];
						
						$file = $current_item['PatientDocument']['document_name'];
						$targetPath =  $folder;
						$targetFile =  $targetPath . $file;
						
					break;
					default:
						$file = $current_item['PatientDocument']['attachment'];
						
						$controller->paths['patient_id'] = $controller->paths['patients'] . $current_item['PatientDocument']['patient_id'] . DS;
						$targetFile = UploadSettings::existing($controller->paths['patients'] . $file, $controller->paths['patient_id'] . $file);
						
				}
				
				$file_name_expolde = explode('_',$file);
                $file_name = $file_name_expolde[1];
				
				header('Content-Type: application/octet-stream; name="'.$file.'"'); 
				header('Content-Disposition: attachment; filename="'.$file_name.'"'); 
				header('Accept-Ranges: bytes'); 
				header('Pragma: no-cache'); 
				header('Expires: 0'); 
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
				header('Content-transfer-encoding: binary'); 
				header('Content-length: ' . @filesize($targetFile)); 
				@readfile($targetFile);
				
				exit;
			} break;
			
            case "addnew":
            {
                $controller->loadModel('Unit');
		        $controller->set("units", $controller->Unit->find('all'));
				
				$controller->loadModel('SpecimenSource');
		        $controller->set("specimen_sources", $controller->SpecimenSource->find('all'));
				
			    if (!empty($controller->data))
                {
                    $controller->data['PatientLabResult']['patient_id'] = $patient_id;
					$controller->data['PatientLabResult']['encounter_id'] = $encounter_id;
                    $controller->data['PatientLabResult']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['PatientLabResult']['date_ordered']));
                    $controller->data['PatientLabResult']['report_date'] = __date("Y-m-d", strtotime($controller->data['PatientLabResult']['report_date']));
                    $controller->data['PatientLabResult']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $controller->data['PatientLabResult']['modified_user_id'] = $controller->user_id;
					
					for($i = 1; $i <= 5; $i++)
					{
						$controller->data['PatientLabResult']['test_report_date'.$i] = __date("Y-m-d", strtotime($controller->data['PatientLabResult']['test_report_date'.$i]));
					}
					
					
                    $controller->PatientLabResult->create();
                    $controller->PatientLabResult->save($controller->data);
                    
                    $controller->PatientLabResult->saveAudit('New');
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            }
            break;
            case "edit":
            {
                $controller->loadModel('Unit');
		        $controller->set("units", $controller->Unit->find('all'));
				
				$controller->loadModel('SpecimenSource');
		        $controller->set("specimen_sources", $controller->SpecimenSource->find('all'));
				
			    if (!empty($controller->data))
                {
					$controller->data['PatientLabResult']['date_ordered'] = __date("Y-m-d", strtotime($controller->data['PatientLabResult']['date_ordered']));
                    $controller->data['PatientLabResult']['report_date'] = __date("Y-m-d", strtotime($controller->data['PatientLabResult']['report_date']));
                    $controller->data['PatientLabResult']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $controller->data['PatientLabResult']['modified_user_id'] = $controller->user_id;
					
					for($i = 1; $i <= 5; $i++)
					{
						$controller->data['PatientLabResult']['test_report_date'.$i] = __date("Y-m-d", strtotime($controller->data['PatientLabResult']['test_report_date'.$i]));
					}
					
                    $controller->PatientLabResult->save($controller->data);
                    
                    $controller->PatientLabResult->saveAudit('Update');
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $lab_result_id = (isset($controller->params['named']['lab_result_id'])) ? $controller->params['named']['lab_result_id'] : "";
                    $items = $controller->PatientLabResult->find('first', array('conditions' => array('PatientLabResult.lab_result_id' => $lab_result_id)));
                    
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
                    $ids = $controller->data['PatientLabResult']['lab_result_id'];
                    
                    foreach ($ids as $id)
                    {
                        $controller->PatientLabResult->delete($id, false);
                        $ret['delete_count']++;
                    }
                    
                    if ($ret['delete_count'] > 0)
                    {
                        $controller->PatientLabResult->saveAudit('Delete');
                    }
                }
                
                echo json_encode($ret);
                exit;
            }
            default:
            {
                $controller->set('PatientLabResult', $controller->sanitizeHTML($controller->paginate('PatientLabResult', array('PatientLabResult.patient_id' => $patient_id, 'PatientLabResult.encounter_id' => $encounter_id))));
                $controller->PatientLabResult->saveAudit('View');
            }
        }
	}
}


?>