<?php

App::import('Core', 'Model');
App::import('Core', 'Controller');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'Dosespot_XML_API', array( 'file' => 'Dosespot_XML_API.php' ));
App::import('Lib', 'EMR_Roles', array( 'file' => 'EMR_Roles.php' ));
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Lib', 'email');

class DosespotRefillsShell extends Shell
{

	var $uses = array('PatientDemographic','DosespotRefillRequest','PracticeSetting'); 
	function main() 
	{
		$pr=$this->PracticeSetting->getSettings();
		if($pr->rx_setup == 'Electronic_Dosespot')
		{
				//flush out table and start from scratch
				 $this->DosespotRefillRequest->deleteAll(array('1 = 1'));

				 // add Cache feature #3498
				$db_config = $this->PracticeSetting->getDataSource()->config;
				$cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';		
				$cache_key=$cache_file_prefix . 'dosespot_rx_accessed';
				Cache::set(array('duration' => '+1 year'));
				$dosespot_rx_accessed = Cache::read($cache_key);

				if (!empty($this->args[1]) && is_numeric($this->args[1])) 
				   $years_back = '-'.$this->args[1]. ' years ';
				else if(!empty($dosespot_rx_accessed))
				   $years_back =$dosespot_rx_accessed;
				else
				   $years_back ='-1 year';
		
				$from_time_stamp= __date('Y-m-d\Th:i:s', strtotime($years_back));
                                $refills_from_dosespot = ClassRegistry::init('Dosespot_XML_API')->getRefillRequestDetails($from_time_stamp);
                                foreach($refills_from_dosespot as $refill)
                                {
						 $this->DosespotRefillRequest->create();
						 $patient_exist = $this->PatientDemographic->find('count',array('conditions' => array('PatientDemographic.dosespot_patient_id' => $refill['PatientID']), 'recursive' => -1, 'callbacks' => false)	);
						 $data['DosespotRefillRequest']['patient_exist'] = (empty($patient_exist))?0:1;
                                                 $data['DosespotRefillRequest']['patient_id'] = $refill['PatientID'];
                                                 $data['DosespotRefillRequest']['patient_name'] = $refill['FirstName']." ".$refill['LastName'];
                                                 $data['DosespotRefillRequest']['prescriber_id'] = $refill['ClinicianId'];
                                                 $data['DosespotRefillRequest']['prescriber_name'] = $refill['ClinicianFirstName']." ".$refill['ClinicianLastName'];
                                                 $data['DosespotRefillRequest']['medication_id'] = $refill['MedicationId'];
                                                 $data['DosespotRefillRequest']['medication_name'] = $refill['DisplayName'];
                                                 $data['DosespotRefillRequest']['medication_status'] = $refill['MedicationStatus'];
						  if(!is_array ($refill['Refills']))
	                                                 $data['DosespotRefillRequest']['refills'] = $refill['Refills'];

                                                 $data['DosespotRefillRequest']['quantity'] = $refill['Quantity'];
                                                 $data['DosespotRefillRequest']['requested_date'] = $refill['DateRequested'];
                                                 $data['DosespotRefillRequest']['request_status'] = $refill['RequestStatus'];

                                                 $data['DosespotRefillRequest']['approve'] = 0;

                                                 $this->DosespotRefillRequest->save($data);
                                }
			
				//set cache to the time we just ran script
                                Cache::set(array('duration' => '+1 year'));
                                Cache::write($cache_key, date('Y-m-d\Th:i:s', strtotime('-1 hour')));				
		}
	}
}

?>
