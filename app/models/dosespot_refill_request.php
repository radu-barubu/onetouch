<?php

class DosespotRefillRequest extends AppModel 
{
	public $name = 'DosespotRefillRequest'; 
	public $primaryKey = 'refill_request_id';
	public $useTable = 'dosespot_refill_requests';  
	
	public function execute(&$controller, $task)
	{
		switch($task)
		{
			case "validate_patient":
            {
                $this->PatientDemographic = ClassRegistry::init('PatientDemographic');
                $ret = array();
                $ret['valid'] = $this->PatientDemographic->validatePatient($controller->data['patient_id'], $controller->data['patient']);    
                echo json_encode($ret);
                exit;
            } break;
			
            case "patient_not_found":
            {
               /* $data = array();
                $data['EmdeonLabResult']['lab_result_id'] = $controller->data['lab_result_id'];
                $data['EmdeonLabResult']['status'] = 'Patient Not Found';
                $this->save($data);*/
                
                $ret = array();
                echo json_encode($ret);
                exit;
            }break;
			
			case "assign_refill_request":
            {
                //var_dump($controller->data);
				
				$refill_request_id = (int)$controller->data['refill_request_id'];
                $patient_id = (int)$controller->data['patient_id'];
                
               // $this->assignLabResult($lab_result_id, $patient_id);
                $data['DosespotRefillRequest']['refill_request_id'] = $refill_request_id;
                $data['DosespotRefillRequest']['approve'] = 1;
                $controller->DosespotRefillRequest->save($data);
						
						
			    $controller->loadModel("PatientMedicationRefill");
				$controller->PatientMedicationRefill->create();
				$refill_data['PatientMedicationRefill']['patient_id'] = (int)$patient_id;
				$refill_data['PatientMedicationRefill']['medication'] = $controller->data['medication_name'];
				$refill_data['PatientMedicationRefill']['quantity'] = (int)$controller->data['quantity'];
				$refill_data['PatientMedicationRefill']['refill_request_date'] = __date("Y-m-d");
                $refill_data['PatientMedicationRefill']['refill_status'] = 'Requested';
                $controller->PatientMedicationRefill->save($refill_data);
				
                $this->PatientDemographic = ClassRegistry::init('PatientDemographic');
                $patient = $this->PatientDemographic->getPatient($patient_id);
                
                $controller->Session->setFlash(__('Refill Request has been assigned to '.$patient['first_name'].' '.$patient['last_name'].'.', true));
                
                $ret = array();
                echo json_encode($ret);
                exit;
            } break;
			
			case "view_refill_request":
			{	
				$refill_request_id = (isset($controller->params['named']['refill_request_id'])) ? $controller->params['named']['refill_request_id'] : "";
				$items = $this->find( 'first', array('conditions' => array('DosespotRefillRequest.refill_request_id' => $refill_request_id) ) );
				$controller->set('ViewItem', $controller->sanitizeHTML($items));
				
				$dosespot_xml_api = new Dosespot_XML_API();                
                $controller->set("dosespot_info", $dosespot_xml_api->getInfo());
			}
			break;
			
			case "reject":
			{
				if (!empty($controller->data))
				{
					$refill_request_ids = $controller->data['DosespotRefillRequest']['refill_request_id'];
					$reject_count = 0;
                     //var_dump($refill_request_ids);
					foreach ($refill_request_ids as $refill_request_id)
					{
						$data = array();
						$data['DosespotRefillRequest']['refill_request_id'] = (int)$refill_request_id;
                        $data['DosespotRefillRequest']['request_status'] = 'Denied';
                        $controller->save($data);
						//echo $refill_request_id;
						$reject_count++;
					}

					if ($reject_count > 0)
					{
						$controller->Session->setFlash($reject_count . __('Request(s) rejected.', true));
					}
				}

				$controller->redirect(array('action' => 'unmatched_rxrefill_requests'));
			} break;
				
			default:
			{	
				$controller->paginate['DosespotRefillRequest'] = array(
					'conditions' => array('DosespotRefillRequest.approve' => 0, 'DosespotRefillRequest.request_status' => 'Queued'), 
					'order' => array('DosespotRefillRequest.requested_date' => 'desc')
			    	);
				
				$controller->set('refills', $controller->sanitizeHTML($controller->paginate('DosespotRefillRequest')));
			}
		}
    }  
	
	/**
    * Verify the existence of medication
    * 
    *
    * @return boolean - true if the medication exists
    */
	
	private function searchItem($medication_id)
    {
	    $count = $this->find('count', array('conditions' => array('DosespotRefillRequest.medication_id' => $medication_id)));
		
		return $count;
		
		if($count > 0)
		{
		    return true;
		}
		else
		{
		    return false;
		}
    }
	
	public function getRefillInformation()
    {
        $refills = $this->find('all', array('conditions' => array('DosespotRefillRequest.approve' => 0, 'DosespotRefillRequest.request_status != ' => 'Denied'), 'order' => array('DosespotRefillRequest.requested_date DESC')));
        
		$data = array();
		
		foreach($refills as $refill)
		{		
			$data[] = array(
					'refill_request_id' => $refill['DosespotRefillRequest']['refill_request_id'],
					'patient_name' => $refill['DosespotRefillRequest']['patient_name'],
					'medication_name' => $refill['DosespotRefillRequest']['medication_name'], 
					'requested_date' => $refill['DosespotRefillRequest']['requested_date'],
					'patient_exist' => $refill['DosespotRefillRequest']['patient_exist']
			);
		}
		
		return $data;
    }
}

?>
