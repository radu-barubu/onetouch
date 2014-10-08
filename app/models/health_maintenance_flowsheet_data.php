<?php

class HealthMaintenanceFlowsheetData extends AppModel 
{
	public $name = 'HealthMaintenanceFlowsheetData';
	public $primaryKey = 'flowsheet_data_id';
	public $useTable = 'health_maintenance_flowsheet_data';

	/*
	* find providers that have HM flowsheet information
	*/	
	private function findHMUsers() {
		$hmusers=ClassRegistry::init('HealthMaintenanceFlowsheet')->find('all',array('fields' => 'user_id', 'group' => 'user_id'));		
		$hmusers=Set::extract('/HealthMaintenanceFlowsheet/user_id',$hmusers);
		 return $hmusers;
	}
	/*
	* grab all patients  seen by a provider
	*/
	private function findHMPatients($user_id) {
			$this->EncounterMaster=ClassRegistry::init('EncounterMaster');
			$this->EncounterMaster->unbindModelAll();
			$this->EncounterMaster->bindModel(array(
					'belongsTo' => array(
						'scheduler' => array(
							'className' => 'ScheduleCalendar',
							'foreignKey' => 'calendar_id'
						)									
					),
			));
			// Patients who have office visit appointments and closed encounter with the provider.
			if ($this->start_date and $this->end_date) {
			$encounters = $this->EncounterMaster->find('all', array(
			'fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 
			'group' => array('EncounterMaster.patient_id'),
			'conditions' => array( 
			'EncounterMaster.encounter_status' => 'Closed', 
			'scheduler.provider_id' => $user_id,  'EncounterMaster.encounter_date BETWEEN ? and ?' => array($start_date, $end_date)
			)));
			} else {
			// GO BACK 1 YEAR BY DEFAULT
			$encounters = $this->EncounterMaster->find('all', array(
			'fields' => array('EncounterMaster.encounter_id', 'EncounterMaster.patient_id'), 
			'group' => array('EncounterMaster.patient_id'),
			'conditions' => array(
			'DATE_SUB(EncounterMaster.encounter_date, INTERVAL 1 YEAR) <=' => date("Y-m-d"),
			'EncounterMaster.encounter_status' => 'Closed', 
			'scheduler.provider_id' => $user_id, 
			)));
			}
			$patientIds = Set::extract('/EncounterMaster/patient_id', $encounters);
		return $patientIds;
	}
	/*
	*	this is the daily function to find the last result for the applicable test type
	*/
	public function beginHMReport() {
		echo "beginning....\n";
	   	// get all provider users who want reports
		$patient_ids=array();
		foreach ($this->findHMUsers() as $hmusers) {
		  echo "seeking for user: ".$hmusers. "\n";
		  //find all patients providers have seen
		   $patient_ids=$this->findHMPatients($hmusers);
                   //remove duplicates if present
                   $patient_ids=array_unique($patient_ids);
		   //start finding records
		   $this->findHMResults($patient_ids,$hmusers);
		}
		echo "done...\n";
	}
	
	private function findHMResults($patient_ids,$user_id) {
	    //grab all HM flow sheet info
	    foreach(ClassRegistry::init('HealthMaintenanceFlowsheet')->findAllByUserId($user_id) as $row) {
		// seek results for each patient
		foreach($patient_ids as $patient_id) {	
		$type=$row['HealthMaintenanceFlowsheet']['test_type'];
		switch($type)
		{
		  case "POC - Lab":
			{
				$row['fieldname']='lab_test_name';
				$row['date_performed']='lab_date_performed';
				$row['test_result']='lab_test_result';
				$this->seekPointofCare($row,$patient_id,"Labs");
			} break;
		  case "POC - Radiology":
			{
				$row['fieldname']='radiology_procedure_name';
				$row['date_performed']='radiology_date_performed';
				$row['test_result']='radiology_test_result';
				$this->seekPointofCare($row,$patient_id,"Radiology");
			} break;
                  case "POC - Immunization":
                        {
				$row['fieldname']='vaccine_name';
				$row['date_performed']='vaccine_date_performed';
				$row['test_result']='vaccine_dose';
                                $this->seekPointofCare($row,$patient_id,"Immunization");
                        } break;
                  case "POC - Procedures":
                        {
				$row['fieldname']='procedure_name';
				$row['date_performed']='procedure_date_performed';
				$row['test_result']='procedure_body_site';
                                $this->seekPointofCare($row,$patient_id,"Procedure");
                        } break;
                  case "POC - Injection":
                        {
				$row['fieldname']='injection_name';
				$row['date_performed']='injection_date_performed';
				$row['test_result']='injection_dose';
                                $this->seekPointofCare($row,$patient_id,"Injection");
                        } break;
                  case "POC - Meds":
                        {
				$row['fieldname']='drug';
				$row['date_performed']='drug_date_given';
				$row['test_result']='unit';
                                $this->seekPointofCare($row,$patient_id,"Meds");
                        } break;
                  case "POC - Supplies":
                        {
				$row['fieldname']='supply_name';
				$row['date_performed']='supply_date';
				$row['test_result']='supply_description';
                                $this->seekPointofCare($row,$patient_id,"Supplies");
                        } break;
                  case "Documents":
                        {
				$this->seekDocuments($row,$patient_id);
                        } break;
                  case "Outside Labs":
                        {
				$this->seekEmdeonLabResult($row, $patient_id);
                        } break;
		  default: 
			{
	
			}  break;
		}
		
		}
	    }
	}

	private function seekPointofCare($row,$patient_id,$orderType) {
				$f1=$row['fieldname']; $f2=$row['date_performed']; $f3=$row['test_result'];
                                $results=ClassRegistry::init('EncounterPointOfCare')->find('first',
                                                                array('conditions' => array('EncounterPointOfCare.order_type' => $orderType, "EncounterPointOfCare.$f1" => $row['HealthMaintenanceFlowsheet']['test_name'], 'EncounterPointOfCare.patient_id' => $patient_id),
                                                                        'order' => array('EncounterPointOfCare.point_of_care_id' => 'DESC'),
                                                                        'fields' => array($f2,$f3,'modified_timestamp'),
									'recursive' => -1
                                                                        ));
                                if($results) {
				  $rdate=(strstr($results['EncounterPointOfCare'][$f2],'0000-00-00')) ? $results['EncounterPointOfCare']['modified_timestamp'] : $results['EncounterPointOfCare'][$f2];
				  $inf=json_encode(array(
						'test_data' => $results['EncounterPointOfCare'][$f3],
						'date' => $rdate,
					));
				  $this->processResults($inf,$row,$patient_id);
                                }

	}

	private function seekDocuments($row,$patient_id) {
		$results=ClassRegistry::init('PatientDocument')->find('first', array('conditions' => array('PatientDocument.document_name' => $row['HealthMaintenanceFlowsheet']['test_name'], 'PatientDocument.patient_id' => $patient_id), 'order' => array('PatientDocument.document_id' => 'DESC')));
		if($results) {
		   $inf=json_encode(array(
				 'test_data' => $results['PatientDocument']['description'],
				 'date' =>  $results['PatientDocument']['service_date'],
			 ));
		   $this->processResults($inf,$row,$patient_id);
		}
	}
	
	private function seekEmdeonLabResult($row, $patient_id) {
		$result = ClassRegistry::init('EmdeonLabResult')->findRecentTest($patient_id, $row['HealthMaintenanceFlowsheet']['test_name']);
		if ($result) {
			$inf = json_encode(array(
				'test_data' => $result['result_value'],
				'date' => $result['date'],
			));
			$this->processResults($inf,$row,$patient_id);
		}	
	}

	private function processResults($results,$row,$patient_id) {
		$exists=$this->find('first', array('conditions' => array('HealthMaintenanceFlowsheetData.patient_id' => $patient_id, 'HealthMaintenanceFlowsheetData.flowsheet_id' => $row['HealthMaintenanceFlowsheet']['flowsheet_id'])));

                	$data['flowsheet_id']= $row['HealthMaintenanceFlowsheet']['flowsheet_id'];
                	$data['test_result_info']=$results;
                	$data['patient_id']=$patient_id;

			if($exists) { //do an updatea
		  		$data['flowsheet_data_id']=$exists['HealthMaintenanceFlowsheetData']['flowsheet_data_id'];
				//first see if new data doesn't match old data. if same, skip saving it
				if ($exists['HealthMaintenanceFlowsheetData']['test_result_info'] != $results){
					$this->save($data);
				} 

			} else {
		  		$this->create();
				$this->save($data);
			}	
	}

	public function getFlowSheetResults($hmData, $patient_id) {
		
	   foreach($hmData as $data) {
		$flowsheet_id=$data['HealthMaintenanceFlowsheet']['flowsheet_id'];
		$result=$this->find('first', array('conditions'=> array('HealthMaintenanceFlowsheetData.flowsheet_id' => $flowsheet_id, 'HealthMaintenanceFlowsheetData.patient_id' => $patient_id)));
	    	$res[$flowsheet_id]=$result;
	   }
		//$res=array_values(array_filter($res));
		return $res;
	}
}

?>
