<?php

EMR_Controller::run();

class DashboardController extends AppController
{
	public $name = 'Dashboard';
	public $helpers = array('Html', 'Form', 'Javascript', 'QuickAcl');

	public $uses = array('ScheduleCalendar','ScheduleType','ScheduleStatus','ScheduleRoom', 'UserAccount', 'PatientDemographic', 'MessagingMessage', 'FormTemplate', 'FormData','ScheduleAppointmentTypes');
	public $actAs = array('Containable');

	public function patient_portal()
	{
		$user = $this->Session->read('UserAccount');
		$user_id = $user['user_id'];
		$role_id = $user['role_id'];
		$patient_id = $user['patient_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		
		
		if(!$patient_id) {
			$this->loadModel('PatientDemographic');
			$this->loadModel('UserAccount');
			
			$new_patient['first_name'] = $user['firstname'];
			$new_patient['last_name'] = $user['lastname'];
			$new_patient['gender'] = $user['gender'];
			$new_patient['dob'] = $user['dob'];
			
			$this->PatientDemographic->create();
			$this->PatientDemographic->save($new_patient);
			
			$user['patient_id'] = $patient_id = $this->PatientDemographic->getLastInsertId();
			
			if(!$patient_id) {
				die('could not create patient. - '. $this->PatientDemographic->getLastQuery());
			} else {
				
				$this->UserAccount->save($user);
				$this->Session->write('UserAccount', $user);
			}
		}
		
		$this->loadModel('PatientDemographic');
		$patient_id = $user['patient_id'];
		$this->set('patient_id', $patient_id);
		$this->set('user_id',$user_id);
		$this->set('patient_name', $this->PatientDemographic->getPatientName($patient_id));
		
                // Get patent data ...
                $patientData = $this->PatientDemographic->find('first', array('conditions' => array(
                    'PatientDemographic.patient_id' => $patient_id,
                ), 'recursive' => -1));
                // Pass info to view
                $this->set('patientData', $patientData);
                
                
		$this->loadModel('PatientPreference');
		$prefenrences = $this->PatientPreference->find('first', array('conditions' => array('PatientPreference.patient_id' => $patient_id)));
        
        if($prefenrences) 
        {
            $pcp = $prefenrences['PatientPreference']['pcp'];
			$pcp_text = trim($prefenrences['UserAccount']['firstname'] . ' ' . $prefenrences['UserAccount']['lastname']);
			$this->set('pcp',$pcp);
            $this->set('pcp_text',$pcp_text);
        }
        
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		if($task=='add')
		{
		     $this->Session->setFlash(__('Appointment Request Sent.', true));
		}
		
		    $this->loadModel('PatientCheckinNotes');
		//if checkin process is finished, update table
		$checkin_complete = (isset($this->params['named']['checkin_complete'])) ? $this->params['named']['checkin_complete'] : "";
		if($checkin_complete)
		{
                    $this->data['PatientCheckinNotes']['patient_checkin_id'] = $checkin_complete; 
                    $this->data['PatientCheckinNotes']['checkin_complete'] = 1;
                    $this->PatientCheckinNotes->save($this->data);  		   
		}

		$this->ScheduleCalendar->recursive=-1;
		$appointments = $this->ScheduleCalendar->find('all', array('conditions' => array('ScheduleCalendar.patient_id' => $patient_id, 
												'ScheduleCalendar.approved !=' => 'no', 
												'ScheduleCalendar.deleted' => 0, 
												'ScheduleCalendar.date >=' =>  __date('Y-m-d'))));
		if($appointments) 
        	{
			//find checkin information for this patient, if present
        		$calendarIds = Set::extract('n/ScheduleCalendar/calendar_id', $appointments);       	
			$this->PatientCheckinNotes->recursive=-1;
                	$checkin_items = $this->PatientCheckinNotes->find('all', array('conditions' => array('PatientCheckinNotes.calendar_id' => $calendarIds) ));
				
				$appointments_data = array();
				foreach($calendarIds as $cd)
				{	
					$appointments_data[] = $this->ScheduleCalendar->find('first',
					array('joins' => array(
					array(
					'table' => 'schedule_appointment_types',
					'alias' => 'ScheduleType',
					'type' => 'INNER',
					'conditions' => array(
					'ScheduleType.appointment_type_id = ScheduleCalendar.visit_type'
					)
					)),
					'fields' => array('ScheduleType.type', 'ScheduleCalendar.*'),
					'conditions' => array('ScheduleCalendar.calendar_id' => $cd)
					));
				}	

				 $this->set('appointments_data', $appointments_data);
                	$this->set('checkin_items', $checkin_items);
		    	$this->set('appointments', $appointments);
		}
		
		//if not registered with dosespot, fetch patient id now
		$pr=$this->Session->read('PracticeSetting');
		$rx_setup=$pr['PracticeSetting']['rx_setup'];
		if($rx_setup == 'Electronic_Dosespot' )
		{
			$dosespot_patient_id=$patientData['PatientDemographic']['dosespot_patient_id'];
			if(empty($dosespot_patient_id )) { //need to fetch  new ID
				$this->PatientDemographic->updateDosespotPatient($patient_id);
			}
		}

	}
    
	public function general_information()
	{
		$user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		
		$this->loadModel('PatientDemographic');
		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		if($role_id == EMR_Roles::PATIENT_ROLE_ID and $user['patient_id'] != $patient_id)
		{
			$this->redirect("index");
		}
		$this->set('patient_id', $patient_id);
		$start_checkin = (isset($this->params['named']['start_checkin'])) ? $this->params['named']['start_checkin'] : "";
		//$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
		if($start_checkin)
		{
			$this->loadModel('PatientCheckinNotes');
		    //see if this record exists already
		    $items = $this->PatientCheckinNotes->find('first',array('conditions' => array('PatientCheckinNotes.calendar_id' => $start_checkin)));
		    if(!$items)
		    {	
		    	//make new record to start checkin 
                    	$this->data['PatientCheckinNotes']['calendar_id'] = $start_checkin; 
                    	$this->data['PatientCheckinNotes']['patient_id'] = $patient_id;
                    	$this->PatientCheckinNotes->create();
                    	$this->PatientCheckinNotes->save($this->data); 
                    	$this->set('patient_checkin_id', $this->PatientCheckinNotes->getLastInsertId());	
                    }	
                    else
                    {
                       $this->set('patient_checkin_id', $items['PatientCheckinNotes']['patient_checkin_id']);
                    }	
		}	
	}
	
	public function medication_list()
    {        
        $user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		$this->loadModel("UserAccount");
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		$this->loadModel("PatientMedicationList");
        $this->loadModel("PatientDemographic");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $show_all_medications = (isset($this->params['named']['show_all_medications'])) ? $this->params['named']['show_all_medications'] : "";
        $show_surescripts = (isset($this->params['named']['show_surescripts'])) ? $this->params['named']['show_surescripts'] : "yes";
        $show_reported = (isset($this->params['named']['show_reported'])) ? $this->params['named']['show_reported'] : "yes";
        $show_prescribed = (isset($this->params['named']['show_prescribed'])) ? $this->params['named']['show_prescribed'] : "yes";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
		if($role_id == EMR_Roles::PATIENT_ROLE_ID and $user['patient_id'] != $patient_id)
		{
			$this->redirect("index");
		}
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $mrn = $patient['mrn'];
	//$this->set('PatientMedicationList', $this->sanitizeHTML($this->PatientMedicationList->find('all'), array('conditions' => array('PatientMedicationList.patient_id' => $patient_id))));
        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
			$this->data['PatientMedicationList']['patient_id'] = $patient_id;
			$this->data['PatientMedicationList']['encounter_id'] = 0;
			$this->data['PatientMedicationList']['medication_type'] = "Standard";
			$this->data['PatientMedicationList']['provider_id'] = $this->data['PatientMedicationList']['provider_id'];
            $this->data['PatientMedicationList']['medication'] = $this->data['PatientMedicationList']['medication'];
            $this->data['PatientMedicationList']['diagnosis'] = $this->data['PatientMedicationList']['diagnosis'];
            $this->data['PatientMedicationList']['icd_code'] = $this->data['PatientMedicationList']['icd_code'];
            $this->data['PatientMedicationList']['taking'] = $this->data['PatientMedicationList']['taking'];
            $this->data['PatientMedicationList']['start_date'] = $this->data['PatientMedicationList']['start_date']?__date("Y-m-d", strtotime($this->data['PatientMedicationList']['start_date'])):'';
            $this->data['PatientMedicationList']['end_date'] = $this->data['PatientMedicationList']['end_date']?__date("Y-m-d", strtotime($this->data['PatientMedicationList']['end_date'])):'';
            $this->data['PatientMedicationList']['long_term'] = $this->data['PatientMedicationList']['long_term'];
            $this->data['PatientMedicationList']['source'] = $this->data['PatientMedicationList']['source'];
            $this->data['PatientMedicationList']['provider'] = $this->data['PatientMedicationList']['provider'];
            $this->data['PatientMedicationList']['status'] = $this->data['PatientMedicationList']['status'];
            $this->data['PatientMedicationList']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $this->data['PatientMedicationList']['modified_user_id'] = $this->user_id;
		}

        switch($task)
        {
			case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->PatientMedicationList->create();
                    $this->PatientMedicationList->save($this->data);
                    $this->PatientMedicationList->saveAudit('New');
					
					$this->Session->setFlash(__('Item(s) added.', true));
					$_r = array('action' => 'medication_list', 'patient_id'=> $patient_id);
					if($patient_checkin_id)
					   $_r['patient_checkin_id']=$patient_checkin_id;
					   
					$this->redirect($_r);					
                }
            } break;
            case "edit":
            {
                $medication_list_id = (isset($this->params['named']['medication_list_id'])) ? $this->params['named']['medication_list_id'] : "";
                
                if(!empty($this->data))
                {
                    $this->PatientMedicationList->save($this->data);
                    $this->PatientMedicationList->saveAudit('Update');
					
					$this->Session->setFlash(__('Item(s) saved.', true));
					$_r = array('action' => 'medication_list', 'patient_id'=> $patient_id);
					if($patient_checkin_id)
					   $_r['patient_checkin_id']=$patient_checkin_id;
					   
					$this->redirect($_r);					
                }
                else
                { 
                    $items = $this->PatientMedicationList->find(
                            'first',
                            array(
                                'conditions' => array('PatientMedicationList.medication_list_id' => $medication_list_id)
                            )
                    );

                    $this->set('EditItem', $this->sanitizeHTML($items));
                }
            } break;
            default:
            {
                $demographic_items = $this->PatientDemographic->find('first',array('fields' => 'medication_list_none', 'conditions' => array('PatientDemographic.patient_id' => $patient_id),'recursive' => -1));

                $medication_list_none = $demographic_items['PatientDemographic']['medication_list_none'];
                $this->set('medication_list_none', $medication_list_none);

                $source_array = array();
                $source_array[] = '';
                if($show_surescripts == 'yes')
                {
                       $source_array[] = 'e-Prescribing History';
                }
                if($show_reported == 'yes')
                {
                       $source_array[] = 'Patient Reported';
                }
                if($show_prescribed == 'yes')
                {
                       $source_array[] = 'Practice Prescribed';
                }
                if($show_all_medications == 'yes')
                {
                    $this->set('show_all_medications', 'yes');
                    $this->set('PatientMedicationList', $this->sanitizeHTML($this->paginate('PatientMedicationList', array('patient_id' => $patient_id, 'source' => $source_array))));
                }
                elseif($show_all_medications == 'no')
                {
                    $this->set('show_all_medications', 'no');
                    $this->set('PatientMedicationList', $this->sanitizeHTML($this->paginate('PatientMedicationList', array('PatientMedicationList.patient_id' => $patient_id, 'PatientMedicationList.status'=>'Active', 'PatientMedicationList.source' => $source_array))));
                }
                else
                {
                    $this->set('show_all_medications', 'yes');
                    $this->set('PatientMedicationList', $this->sanitizeHTML($this->paginate('PatientMedicationList', array('patient_id' => $patient_id, 'source' => $source_array))));
                }
                $this->set('show_surescripts', $show_surescripts);
                $this->set('show_reported', $show_reported);
                $this->set('show_prescribed', $show_prescribed);

				$this->PatientMedicationList->saveAudit('View');
            }
        }
    }
	
	public function past_visits()
	{		
		$user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		if($role_id == EMR_Roles::PATIENT_ROLE_ID and $user['patient_id'] != $patient_id)
		{
			$this->redirect("index");
		}
		$this->loadModel("EncounterMaster");
		$this->EncounterMaster->hasMany['EncounterAssessment']['order'] = array('EncounterAssessment.diagnosis' => 'ASC');
		$this->paginate['EncounterMaster'] = array(
					'limit' => 10,
					'order' => array('EncounterMaster.encounter_date' => 'DESC'),
					'conditions' =>  array('AND' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterMaster.encounter_status' => 'Closed')),
					'fields' => array('`EncounterMaster`.`encounter_id`', '`EncounterMaster`.`patient_id`', '`EncounterMaster`.`calendar_id`', '`EncounterMaster`.`encounter_date`','`Provider`.`firstname`', '`Provider`.`lastname`','PracticeLocation.location_name',/*'EncounterAssessment.diagnosis' ,*/'`EncounterMaster`.`encounter_status`'),
					'joins' => array(
/*					
								  array(
										   'table' => 'encounter_assessment',
										   'alias' => 'EncounterAssessment',
										   'type' => 'left',
										   'conditions' => array(
												   'EncounterMaster.encounter_id = EncounterAssessment.encounter_id'
												   )
								   ),
*/								   
								   array(
										   'table' => 'schedule_calendars',
										   'alias' => 'ScheduleCalendar',
										   'type' => 'inner',
										   'conditions' => array(
												   'EncounterMaster.calendar_id = ScheduleCalendar.calendar_id'
												   )
								   ),
								   array(
										   'table' => 'practice_locations',
										   'alias' => 'PracticeLocation',
										   'type' => 'inner',
										   'conditions' => array(
												   'PracticeLocation.location_id = ScheduleCalendar.location'
												   )
								   ),
								   array(
										   'table' => 'user_accounts',
										   'alias' => 'Provider',
										   'type' => 'inner',
										   'conditions' => array(
												   'Provider.user_id = ScheduleCalendar.provider_id'
												   )
								   )
							)
				);
		$this->set('pastvisit_items', $this->sanitizeHTML($this->paginate('EncounterMaster')));

		$this->EncounterMaster->saveAudit('View', 'EncounterMaster', 'Attachments - Past Visits');
    }
	
	public function superbill()
	{		
		$user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
		$phone = (isset($this->params['named']['phone'])) ? $this->params['named']['phone'] : "";
		$this->loadModel("EncounterMaster");

		//use Contain to fine-tune results from find query. 
	        // NOTE IF YOU make changes within this function and need $encounter you will need to adjust this here
		$filterit = array('PatientDemographic','UserAccount');
		$encounter = $this->EncounterMaster->find('first', array(
			'conditions' => array(
				'EncounterMaster.encounter_id' => $encounter_id,
			), 'contain' => $filterit
		));		
		// If the current user is a patient ...
		if ($role_id == EMR_Roles::PATIENT_ROLE_ID) {
			
			// ... check if it owns the encounter being viewed ...
			if ($user['patient_id'] != $encounter['EncounterMaster']['patient_id']) {
				
				// ... and prevent them from viewing if
				// it is someone other patient's encounter
				$this->redirect('index');
			}
		}
		
		switch($task)
		{
		    case "get_report_html":
            {
                $this->layout = 'empty';
								
								if ($encounter['EncounterMaster']['encounter_status'] == 'Closed') {
								
									if(empty($encounter['UserAccount']['new_pt_note']) || empty($encounter['UserAccount']['est_pt_note'])) {
									 $defaultFormat = 'soap';
									} else {
									 $defaultFormat = 'full';
									}

									if ($encounter['PatientDemographic']['status'] == 'New') {
										 $defaultFormat = 'soap';

										if ($encounter['UserAccount']['new_pt_note'] == '1') {
										 $defaultFormat = 'full';
										}

									} else {
										 $defaultFormat = 'soap';

										if ($encounter['UserAccount']['est_pt_note'] == '1') {
										 $defaultFormat = 'full';
										}
									}									
									
									$snapShots = Visit_Summary::getSnapShot($encounter_id);
										
									$overrideFormat = isset($_GET['format']) ? $_GET['format'] : $defaultFormat;		
									$user = $this->UserAccount->getUserByID(EMR_Account::getCurretUserId());
									$isPatient = ($user->role_id == EMR_Roles::PATIENT_ROLE_ID);

									if ($isPatient) {
										echo $snapShots['patient'];
									} else if (isset($snapShots[$overrideFormat])) {
										echo $snapShots[$overrideFormat];
									}
									ob_flush();
									flush();
									exit();										
									
								} else {
									
									Visit_Summary::$isPatient = true;
									if($report = Visit_Summary::generateReport($encounter_id, $phone))
									{
											App::import('Helper', 'Html');
											$html = new HtmlHelper();

											$url = UploadSettings::createIfNotExists($this->paths['encounters'] . $encounter_id . DS);
											$url = str_replace('//', '/', $url);

											$pdffile = 'encounter_' . $encounter_id . '_summary.pdf';

											//format report, by removing hide text
											$reportmod = preg_replace('/(<span class="hide_for_print">.+?)+(<\/span>)/i', '', $report);


											//PDF file creation
											site::write(pdfReport::generate($reportmod, $url . $pdffile), $url . $pdffile);

											echo $report;

											ob_flush();
											flush();

											exit();
									}
								}
								
                exit('could not generate report');
            }break;
            case "get_report_ccr":
            {
                CCR::generateCCR($this);
            }
            break;
            case "get_report_pdf":
            {
							
								if ($encounter['EncounterMaster']['encounter_status'] == 'Closed') {
									if(empty($encounter['UserAccount']['new_pt_note']) || empty($encounter['UserAccount']['est_pt_note'])) {
									 $defaultFormat = 'soap';
									} else {
									 $defaultFormat = 'full';
									}

									if ($encounter['PatientDemographic']['status'] == 'New') {
										 $defaultFormat = 'soap';

										if ($encounter['UserAccount']['new_pt_note'] == '1') {
										 $defaultFormat = 'full';
										}

									} else {
										 $defaultFormat = 'soap';

										if ($encounter['UserAccount']['est_pt_note'] == '1') {
										 $defaultFormat = 'full';
										}
									}									
									
									$snapShots = Visit_Summary::getSnapShot($encounter_id, 'pdf');
										
									$overrideFormat = isset($_GET['format']) ? $_GET['format'] : $defaultFormat;		
									$user = $this->UserAccount->getUserByID(EMR_Account::getCurretUserId());
									$isPatient = ($user->role_id == EMR_Roles::PATIENT_ROLE_ID);

									if ($isPatient) {
										$targetFile = $snapShots['patient'];
									} else if (isset($snapShots[$overrideFormat])) {
										$targetFile = $snapShots[$overrideFormat];
									}
									$file = 'encounter_' . $encounter_id . '_summary.pdf';									
								}	else {
									$file = 'encounter_' . $encounter_id . '_summary.pdf';
									
									// Check and use original location if exists
									$targetPath = $this->paths['encounters'];
									$targetFile = str_replace('//', '/', $targetPath) . $file;
									
								
									if (!is_file($targetFile)) {
										// Otherwise, look in new location
										$targetPath = UploadSettings::createIfNotExists($this->paths['encounters'] . $encounter_id . DS);
										$targetFile = str_replace('//', '/', $targetPath) . $file;									
									}
									
									
									
								}						
							
							
                
                if (!is_file($targetFile))
                {
                    die("Invalid File: does not exist");
                }
                header('Content-Type: application/octet-stream; name="' . $file . '"');
                header('Content-Disposition: attachment; filename="' . $file . '"');
                header('Accept-Ranges: bytes');
                header('Pragma: no-cache');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Content-transfer-encoding: binary');
                header('Content-length: ' . @filesize($targetFile));
                @readfile($targetFile);
                exit;
            }
            break;
			default:
			{
			}
		}
	}
	
	public function hx_medical()
    {
        $user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		
        $this->loadModel("PatientMedicalHistory");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $this->loadModel("PatientProblemList");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
	$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
		if($role_id == EMR_Roles::PATIENT_ROLE_ID and $user['patient_id'] != $patient_id)
		{
			$this->redirect("index");
		}
                
        $this->set('__patient', $this->PatientDemographic->getPatient($patient_id));
                
        //$this->set("Icd9", $this->sanitizeHTML($this->Icd9->find('all')));
        //$this->set('PatientMedicalHistory', $this->sanitizeHTML($this->PatientMedicalHistory->find('all')));

        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientMedicalHistory']['patient_id'] = $patient_id;
           /* $this->data['PatientMedicalHistory']['diagnosis'] = $this->data['PatientMedicalHistory']['diagnosis'];
            $this->data['PatientMedicalHistory']['icd_code'] = $this->data['PatientMedicalHistory']['icd_code'];*/
            $this->data['PatientMedicalHistory']['start_month'] = $this->data['PatientMedicalHistory']['start_month'];
            $this->data['PatientMedicalHistory']['start_year'] = $this->data['PatientMedicalHistory']['start_year'];
            $this->data['PatientMedicalHistory']['end_month'] = $this->data['PatientMedicalHistory']['end_month'];
            $this->data['PatientMedicalHistory']['end_year'] = $this->data['PatientMedicalHistory']['end_year'];
            $this->data['PatientMedicalHistory']['occurrence'] = $this->data['PatientMedicalHistory']['occurrence'];
            $this->data['PatientMedicalHistory']['comment'] = $this->data['PatientMedicalHistory']['comment'];
            $this->data['PatientMedicalHistory']['status'] = isset($this->data['PatientMedicalHistory']['status'])?$this->data['PatientMedicalHistory']['status']:'';
            $this->data['PatientMedicalHistory']['action'] = isset($this->data['PatientMedicalHistory']['action'])?'Moved':'';
            $this->data['PatientMedicalHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $this->data['PatientMedicalHistory']['modified_user_id'] = $this->user_id;
        }

        switch($task)
        {
            case "load_patient_medical_autocomplete":
            {
                if (!empty($this->data))
                {
		  $results=array();
		  $keyword=$this->data['autocomplete']['keyword'];
		  if($keyword) {
			$this->loadModel("PatientPortalMedicalFavorite");
			$keyword=$this->data['autocomplete']['keyword'];
			$search_limit= (!empty($this->data['autocomplete']['limit'])) ? $this->data['autocomplete']['limit']:'20';
			$r=$this->PatientPortalMedicalFavorite->find('all',array('conditions' => array('PatientPortalMedicalFavorite.diagnosis LIKE' => $keyword.'%'), 'limit' => $search_limit));

			foreach ($r as $items) {
			  $results[]= $items['PatientPortalMedicalFavorite']['diagnosis'];
			}
		  }	
		  echo implode("\n", $results);
                }
                exit();
            } 
			break;
			case "validate_duplicate":
            {
                if(!empty($this->data))
                {
                    $all_diagnosis = explode(',', $this->data['diagnosis']);
					$patient_id = $this->data['patient_id'];
					$return = array('result' => 'true');
                    foreach($all_diagnosis as $diagnosis)
					{	
						$diagnosis = trim($diagnosis);
						if(empty($diagnosis))	
							continue; 
						$count = $this->PatientMedicalHistory->find('count', array(
							'conditions' => array('diagnosis' => $diagnosis, 'patient_id' => $patient_id)
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
                if(!empty($this->data))
                {
                    if($this->data['PatientMedicalHistory']['action'] == 'Moved')  // Move to Problem List
                    {
                        $this->data['PatientProblemList']['patient_id'] = $patient_id;
                        /*$this->data['PatientProblemList']['diagnosis'] = $this->data['PatientMedicalHistory']['diagnosis'];
                        $this->data['PatientProblemList']['icd_code'] = $this->data['PatientMedicalHistory']['icd_code'];*/
                        $this->data['PatientProblemList']['start_date'] = $this->data['PatientMedicalHistory']['start_year'].'-'.$this->data['PatientMedicalHistory']['start_month'].'-'.'00';
                        $this->data['PatientProblemList']['end_date'] = $this->data['PatientMedicalHistory']['end_year'].'-'.$this->data['PatientMedicalHistory']['end_month'].'-'.'00';
                        $this->data['PatientProblemList']['occurrence'] = $this->data['PatientMedicalHistory']['occurrence'];
                        $this->data['PatientProblemList']['comment'] = $this->data['PatientMedicalHistory']['comment'];
                        $this->data['PatientProblemList']['status'] = 'Active';
                        $this->data['PatientProblemList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['PatientProblemList']['modified_user_id'] = $this->user_id;
                    }
					
					$all_diagnosis = explode(',', $this->data['PatientMedicalHistory']['diagnosis']);
                    foreach($all_diagnosis as $diagnosis)
					{	
						$diagnosis = trim($diagnosis);
						if(empty($diagnosis))	
							continue; 
						$icd9 = '';					
						// Check if matches with an ICD9 code format in the name...
						if (preg_match('/\[(?P<icd9>[\w\.]+)]\s*$/i', $diagnosis, $match)) {
							// Get the matching code
							$icd9 = $match['icd9'];
						}
						$this->data['PatientMedicalHistory']['diagnosis'] = $diagnosis;
						$this->data['PatientMedicalHistory']['icd_code'] = $icd9;
						$this->PatientMedicalHistory->create();
						$this->PatientMedicalHistory->save($this->data);
						$this->PatientMedicalHistory->saveAudit('New');
						unset($this->PatientMedicalHistory->id);
						if ($this->data['PatientMedicalHistory']['action'] == 'Moved')
                    	{
							// Check if not yet in problem list for this encounter
							$prob = $this->PatientProblemList->find('count', array(
								'conditions' => array(
									'PatientProblemList.patient_id' => $patient_id,
									'PatientProblemList.diagnosis' => $diagnosis,
									'PatientProblemList.icd_code' => $icd9,
								),
							));
							// Not yet in problem list
							if (!$prob) 
							{
								$this->data['PatientProblemList']['diagnosis'] = $diagnosis;
								$this->data['PatientProblemList']['icd_code']  = $icd9;
								$this->PatientProblemList->create();
								$this->PatientProblemList->save($this->data);
								$this->PatientProblemList->saveAudit('New');
								unset($this->PatientProblemList->id);
							}
						}
					}

                    $this->Session->setFlash(__('Item(s) added.', true));
					$_r = array('action' => 'hx_medical', 'patient_id'=> $patient_id);
					if($patient_checkin_id)
					   $_r['patient_checkin_id']=$patient_checkin_id;
					   
					$this->redirect($_r);

                } else {
                	$this->loadModel("PatientPortalMedicalFavorite");
                	$favitems = $this->PatientPortalMedicalFavorite->find('all');
                	$this->set('favitems', $this->sanitizeHTML($favitems));
		}
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    if($this->data['PatientMedicalHistory']['action'] == 'Moved')  // Move to Problem List
                    {
                        $this->data['PatientProblemList']['patient_id'] = $patient_id;
                        $this->data['PatientProblemList']['diagnosis'] = $this->data['PatientMedicalHistory']['diagnosis'];
                        $this->data['PatientProblemList']['icd_code'] = $this->data['PatientMedicalHistory']['icd_code'];
                        $this->data['PatientProblemList']['start_date'] = $this->data['PatientMedicalHistory']['start_year'].'-'.$this->data['PatientMedicalHistory']['start_month'].'-'.'00';
                        $this->data['PatientProblemList']['end_date'] = $this->data['PatientMedicalHistory']['end_year'].'-'.$this->data['PatientMedicalHistory']['end_month'].'-'.'00';
                        $this->data['PatientProblemList']['occurrence'] = $this->data['PatientMedicalHistory']['occurrence'];
                        $this->data['PatientProblemList']['comment'] = $this->data['PatientMedicalHistory']['comment'];
                        $this->data['PatientProblemList']['status'] = 'Active';
                        $this->data['PatientProblemList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['PatientProblemList']['modified_user_id'] = $this->user_id;
                        $this->PatientProblemList->create();
                        $this->PatientProblemList->save($this->data);

                        //Delete from Medical History
                        //$medical_history_id = (isset($this->params['named']['medical_history_id'])) ? $this->params['named']['medical_history_id'] : "";
                        //$this->PatientMedicalHistory->delete($medical_history_id, false);

						$this->PatientProblemList->saveAudit('New');

                    }
                    //else
                    //{
                    $this->PatientMedicalHistory->save($this->data);

					$this->PatientMedicalHistory->saveAudit('Update');
                    //}

                    $this->Session->setFlash(__('Item(s) saved.', true));
                                        $_r = array('action' => 'hx_medical', 'patient_id'=> $patient_id);
                                        if($patient_checkin_id)
                                           $_r['patient_checkin_id']=$patient_checkin_id;

                                        $this->redirect($_r);
                }
                else
                {
                    $medical_history_id = (isset($this->params['named']['medical_history_id'])) ? $this->params['named']['medical_history_id'] : "";
                    $items = $this->PatientMedicalHistory->find(
                            'first',
                            array(
                                'conditions' => array('PatientMedicalHistory.medical_history_id' => $medical_history_id)
                            )
                    );
                    $this->set('EditItem', $this->sanitizeHTML($items));
                 	$this->loadModel("PatientPortalMedicalFavorite");
                	$favitems = $this->PatientPortalMedicalFavorite->find('all');
                	$this->set('favitems', $this->sanitizeHTML($favitems));

                }
            } break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientMedicalHistory']['medical_history_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientMedicalHistory->delete($id, false);
                       $ret['delete_count']++;
                    }

					if($ret['delete_count'] > 0)
					{
						$this->PatientMedicalHistory->saveAudit('Delete');
						$this->Session->setFlash($ret["delete_count"] . __('Item(s) deleted.', true));
                                        $_r = array('action' => 'hx_medical', 'patient_id'=> $patient_id);
                                        if($patient_checkin_id)
                                           $_r['patient_checkin_id']=$patient_checkin_id;

                                        $this->redirect($_r);
					}
                }

               // echo json_encode($ret);
                //exit;
            }break;
            default:
            {
                $this->set('PatientMedicalHistory', $this->sanitizeHTML($this->paginate('PatientMedicalHistory', array('patient_id' => $patient_id))));

				$this->PatientMedicalHistory->saveAudit('View');
            }
        }
    }

    public function hx_surgical()
    {
        $user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		
        $this->loadModel("PatientSurgicalHistory");
       // $this->loadModel("Icd9");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
	$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
		if($role_id == EMR_Roles::PATIENT_ROLE_ID and $user['patient_id'] != $patient_id)
		{
			$this->redirect("index");
		}
                
        $this->set('__patient', $this->PatientDemographic->getPatient($patient_id));
                
       // $this->set("Icd9", $this->sanitizeHTML($this->Icd9->find('all')));
        $this->set('PatientSurgicalHistory', $this->sanitizeHTML($this->PatientSurgicalHistory->find('all')));

        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientSurgicalHistory']['patient_id'] = $patient_id;
            $this->data['PatientSurgicalHistory']['hospitalization'] = isset($this->data['PatientSurgicalHistory']['hospitalization'])?$this->data['PatientSurgicalHistory']['hospitalization']:'';
            $this->data['PatientSurgicalHistory']['date_from'] = $this->data['PatientSurgicalHistory']['date_from']? intval($this->data['PatientSurgicalHistory']['date_from']):"";
            $this->data['PatientSurgicalHistory']['date_to'] = $this->data['PatientSurgicalHistory']['date_to']? intval($this->data['PatientSurgicalHistory']['date_to']):"";
            $this->data['PatientSurgicalHistory']['reason'] = $this->data['PatientSurgicalHistory']['reason'];
            $this->data['PatientSurgicalHistory']['outcome'] = $this->data['PatientSurgicalHistory']['outcome'];
            $this->data['PatientSurgicalHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $this->data['PatientSurgicalHistory']['modified_user_id'] = $this->user_id;
        }

        switch($task)
        {
            case "load_patient_surgical_autocomplete":
            {
                if (!empty($this->data))
                {
                  $results=array();
                  $keyword=$this->data['autocomplete']['keyword'];
                  if($keyword) {
                        $this->loadModel("PatientPortalSurgicalFavorite");
                        $keyword=$this->data['autocomplete']['keyword'];
                        $search_limit= (!empty($this->data['autocomplete']['limit'])) ? $this->data['autocomplete']['limit']:'20';
                        $r=$this->PatientPortalSurgicalFavorite->find('all',array('conditions' => array('PatientPortalSurgicalFavorite.surgeries LIKE' => $keyword.'%'), 'limit' => $search_limit));

                        foreach ($r as $items) {
                          $results[]= $items['PatientPortalSurgicalFavorite']['surgeries'];
                        }
                  }
                  echo implode("\n", $results);
                }
		exit();
            } break;
            case "addnew":
            {
                if(!empty($this->data))
                {
                    $all_surgeries = explode(',', $this->data['PatientSurgicalHistory']['surgery']);
					foreach($all_surgeries as $surgeries)
					{	
						$surgeries = trim($surgeries);
						if(empty($surgeries))	
							continue; 
						$this->data['PatientSurgicalHistory']['surgery'] = $surgeries;
						$this->PatientSurgicalHistory->create();
						$this->PatientSurgicalHistory->save($this->data);
						unset($this->PatientSurgicalHistory->id);
                    }

                    $this->PatientSurgicalHistory->saveAudit('New');

                    $this->Session->setFlash(__('Item(s) added.', true));
					$_r = array('action' => 'hx_surgical', 'patient_id'=> $patient_id);
					if($patient_checkin_id)
					   $_r['patient_checkin_id']=$patient_checkin_id;
					   
					$this->redirect($_r);	
                } else {
                        $this->loadModel("PatientPortalSurgicalFavorite");
                        $favitems = $this->PatientPortalSurgicalFavorite->find('all');
                        $this->set('favitems', $this->sanitizeHTML($favitems));

		}
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->PatientSurgicalHistory->save($this->data);

					$this->PatientSurgicalHistory->saveAudit('Update');

                    $this->Session->setFlash(__('Item(s) saved.', true));
                                        $_r = array('action' => 'hx_surgical', 'patient_id'=> $patient_id);
                                        if($patient_checkin_id)
                                           $_r['patient_checkin_id']=$patient_checkin_id;

                                        $this->redirect($_r);
                }
                else
                {
                    $surgical_history_id = (isset($this->params['named']['surgical_history_id'])) ? $this->params['named']['surgical_history_id'] : "";
                    $items = $this->PatientSurgicalHistory->find(
                            'first',
                            array(
                                'conditions' => array('PatientSurgicalHistory.surgical_history_id' => $surgical_history_id)
                            )
                    );

                    $this->set('EditItem', $this->sanitizeHTML($items));

                        $this->loadModel("PatientPortalSurgicalFavorite");
                        $favitems = $this->PatientPortalSurgicalFavorite->find('all');
                        $this->set('favitems', $this->sanitizeHTML($favitems)); 
               }
            } break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientSurgicalHistory']['surgical_history_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientSurgicalHistory->delete($id, false);
                       $ret['delete_count']++;
                    }

					if($ret['delete_count'] > 0)
					{
						$this->PatientSurgicalHistory->saveAudit('Delete');
						$this->Session->setFlash($ret["delete_count"] . __('Item(s) deleted.', true));
                                        	$_r = array('action' => 'hx_surgical', 'patient_id'=> $patient_id);
                                        	if($patient_checkin_id)
                                           	   $_r['patient_checkin_id']=$patient_checkin_id;

                                        	$this->redirect($_r);
					}
                }

                //echo json_encode($ret);
               // exit;
            }
            default:
            {
                $this->set('PatientSurgicalHistory', $this->sanitizeHTML($this->paginate('PatientSurgicalHistory', array('patient_id' => $patient_id))));

				$this->PatientSurgicalHistory->saveAudit('View');
            }
        }
    }

    public function hx_social()
    {
        $user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		
        $this->loadModel("PatientSocialHistory");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
	$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
		if($role_id == EMR_Roles::PATIENT_ROLE_ID and $user['patient_id'] != $patient_id)
		{
			$this->redirect("index");
		}
		$this->loadModel("PatientPortalSocialFavorites");
        $this->set('__patient', $this->PatientDemographic->getPatient($patient_id));
                
        $this->loadModel("MaritalStatus");
        $this->set("MaritalStatus", $this->sanitizeHTML($this->MaritalStatus->find('all')));

        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientSocialHistory']['pets'] = ((isset($this->params['form']['pets_option_1'])) ? $this->params['form']['pets_option_1'] : "") . "|" . ((isset($this->params['form']['pets_option_2'])) ? $this->params['form']['pets_option_2'] : "") . "|" . ((isset($this->params['form']['pets_option_3'])) ? $this->params['form']['pets_option_3'] : "") . "|" . ((isset($this->params['form']['pets_option_4'])) ? $this->params['form']['pets_option_4'] : "") . "|" . ((isset($this->params['form']['pets_option_5'])) ? $this->params['form']['pets_option_5'] : "");
            $this->data['PatientSocialHistory']['patient_id'] = $patient_id;
            $this->data['PatientSocialHistory']['smoking_recodes'] = (trim($this->data['PatientSocialHistory']['smoking_recodes']) !="")?$this->data['PatientSocialHistory']['smoking_recodes']:0;
            $this->data['PatientSocialHistory']['modified_user_id'] =  $this->user_id;
            $this->data['PatientSocialHistory']['modified_timestamp'] =  __date("Y-m-d H:i:s");
        }
        switch($task)
        {
            case "load_Icd9_autocomplete":
            {

            } break;
            case "addnew":
            {
                if(!empty($this->data))
                {
		    // re ticket #3146 - to allow a Q&A option. print question with the answer
		    if($this->data['PatientSocialHistory']['type']== 'Other' && !empty($this->data['PatientSocialHistory']['question'])) {
			$reformat= $this->data['PatientSocialHistory']['question']. "\n".$this->data['PatientSocialHistory']['comment']; 
			$this->data['PatientSocialHistory']['comment']=$reformat;
		    }
                    $this->PatientSocialHistory->create();
                    $this->PatientSocialHistory->save($this->data);

					$this->PatientSocialHistory->saveAudit('New');

                    $this->Session->setFlash(__('Item(s) added.', true));
					$_r = array('action' => 'hx_social', 'patient_id'=> $patient_id);
					if($patient_checkin_id)
					   $_r['patient_checkin_id']=$patient_checkin_id;
					   
					$this->redirect($_r);	
                }else{ 
						$type = (isset($this->params['named']['type'])) ? $this->params['named']['type'] : "";
						$history_id = (isset($this->params['named']['history_id'])) ? $this->params['named']['history_id'] : "";
						if(!empty($history_id)){					
								$fav = $this->PatientPortalSocialFavorites->find('first',array(
								'conditions' => array('PatientPortalSocialFavorites.social_favorite_id' => $history_id)
								)
							);
					$this->set('favourites_data',$fav);

						}

				}
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->PatientSocialHistory->save($this->data);

					$this->PatientSocialHistory->saveAudit('Update');

                    $this->Session->setFlash(__('Item(s) saved.', true));
                                        $_r = array('action' => 'hx_social', 'patient_id'=> $patient_id);
                                        if($patient_checkin_id)
                                           $_r['patient_checkin_id']=$patient_checkin_id;

                                        $this->redirect($_r);
                }
                else
                {
					
					$favourites = $this->PatientPortalSocialFavorites->find('all');
                    $social_history_id = (isset($this->params['named']['social_history_id'])) ? $this->params['named']['social_history_id'] : "";
                    //echo $social_history_id;
                    $items = $this->PatientSocialHistory->find(
                            'first',
                            array(
                                'conditions' => array('PatientSocialHistory.social_history_id' => $social_history_id)
                            )
                    );
					$this->set('favourites_data',$favourites);
                    $this->set('EditItem', $this->sanitizeHTML($items));
                }
            } break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientSocialHistory']['social_history_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientSocialHistory->delete($id, false);
                       $ret['delete_count']++;
                    }

					if($ret['delete_count'] > 0)
					{
						$this->PatientSocialHistory->saveAudit('Delete');
						$this->Session->setFlash($ret["delete_count"] . __('Item(s) deleted.', true));
                                        	$_r = array('action' => 'hx_social', 'patient_id'=> $patient_id);
                                        	if($patient_checkin_id)
                                           	    $_r['patient_checkin_id']=$patient_checkin_id;

                                        	$this->redirect($_r);
					}
                }

                //echo json_encode($ret);
                //exit;
            }
            default:
            {
				$this->set('SocialFavouriteHistory',$this->PatientPortalSocialFavorites->find('all'));
                $this->set('PatientSocialHistory', $this->sanitizeHTML($this->paginate('PatientSocialHistory', array('patient_id' => $patient_id))));

				$this->PatientSocialHistory->saveAudit('View');
            }
        }
    }

    public function hx_family()
    {
		
        $user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
	$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
		if($role_id == EMR_Roles::PATIENT_ROLE_ID and $user['patient_id'] != $patient_id)
		{
			$this->redirect("index");
		}
        //if checkin, see if online forms are present in order to forward to next step; otherwise they are finished checkin
        if($patient_checkin_id)
        {
                $this->loadModel("FormTemplate");
                $online_templates = $this->FormTemplate->find('count');
                if(empty($online_templates)) //if no online forms, check for downloadable forms
                {
                        $this->loadModel("AdministrationForm");
                        $online_templates= $this->AdministrationForm->find('count');
                }

                $this->set('online_templates', $online_templates);
        }
                
        $this->set('__patient', $this->PatientDemographic->getPatient($patient_id));
                
        $this->loadModel("PatientFamilyHistory");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $this->set('PatientFamilyHistory', $this->sanitizeHTML($this->PatientFamilyHistory->find('all')));
        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientFamilyHistory']['patient_id'] = $patient_id;
            $this->data['PatientFamilyHistory']['modified_user_id'] =  $this->user_id;
            $this->data['PatientFamilyHistory']['modified_timestamp'] =  __date("Y-m-d H:i:s");
        }
        
        switch($task)
        {
            case "load_relationship":
            { 
		$showall = (isset($this->params['named']['showall'])) ? $this->params['named']['showall'] :"";

	        $data_array = array('Mother', 'Father', 'Maternal Grandmother', 'Maternal Grandfather', 'Paternal Grandmother', 'Paternal Grandfather', 'Sister', 'Brother', 'Aunt', 'Uncle', 'Cousin');
		if(empty($showall))
		{
                   echo implode("\n", $data_array);
		}
		else
		{
		  echo json_encode($data_array);
		}
		 exit();
            }
            break;
			
			case "load_problem":
            { 
			     $data_array = array('Asthma', 'Back Problems', 'Cancer', 'Child Birth', 'Diabetes Type II', 'Heart Disease', 'Hypertension', 'Mental Disorders', 'Osteoarthritis', 'Trauma Disorders');
                 echo implode("\n", $data_array);
				 exit();
            }
            break;
			
            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->PatientFamilyHistory->create();
		    //if comma on end, strip off
		    $strip=trim($this->data['PatientFamilyHistory']['relationship']);
		    $this->data['PatientFamilyHistory']['relationship']=rtrim($strip, ',');
                    $this->PatientFamilyHistory->save($this->data);

					$this->PatientFamilyHistory->saveAudit('New');

                    $this->Session->setFlash(__('Item(s) added.', true));
                                                $_r = array('action' => 'hx_family', 'patient_id'=> $patient_id);
                                                if($patient_checkin_id)
                                                    $_r['patient_checkin_id']=$patient_checkin_id;

                                                $this->redirect($_r);
                } else {
					$this->loadModel("PatientPortalFamilyFavorites");
					$history_id = (isset($this->params['named']['history_id'])) ? $this->params['named']['history_id'] : "";
						if(!empty($history_id)){					
								$fav = $this->PatientPortalFamilyFavorites->find('first',array(
								'conditions' => array('PatientPortalFamilyFavorites.family_favorite_id' => $history_id)
								)
							);
					$this->set('favourites_data',$fav); 
					}
				}
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    //if comma on end, strip off
                    $strip=trim($this->data['PatientFamilyHistory']['relationship']);
                    $this->data['PatientFamilyHistory']['relationship']=rtrim($strip, ',');
                    $this->PatientFamilyHistory->save($this->data);
		    $this->PatientFamilyHistory->saveAudit('Update');

                    $this->Session->setFlash(__('Item(s) saved.', true));
                                                $_r = array('action' => 'hx_family', 'patient_id'=> $patient_id);
                                                if($patient_checkin_id)
                                                    $_r['patient_checkin_id']=$patient_checkin_id;

                                                $this->redirect($_r);
                }
                else
                {
                    $family_history_id = (isset($this->params['named']['family_history_id'])) ? $this->params['named']['family_history_id'] : "";
                    //echo $family_history_id;
                    $items = $this->PatientFamilyHistory->find(
                            'first',
                            array(
                                'conditions' => array('PatientFamilyHistory.family_history_id' => $family_history_id)
                            )
                    );

                    $this->set('EditItem', $this->sanitizeHTML($items));
                }
            } break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientFamilyHistory']['family_history_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientFamilyHistory->delete($id, false);
                       $ret['delete_count']++;
                    }

					if($ret['delete_count'] > 0)
					{
						$this->PatientFamilyHistory->saveAudit('Delete');
						$this->Session->setFlash($ret["delete_count"] . __('Item(s) deleted.', true));
                                                $_r = array('action' => 'hx_family', 'patient_id'=> $patient_id);
                                                if($patient_checkin_id)
                                                    $_r['patient_checkin_id']=$patient_checkin_id;

                                                $this->redirect($_r);
					}
                }

                //echo json_encode($ret);
                //exit;
            }
            default:
            {
				
                $this->set('PatientFamilyHistory', $this->sanitizeHTML($this->paginate('PatientFamilyHistory', array('patient_id' => $patient_id))));
		$this->loadModel("PracticeProfile");
        	$PracticeProfile = $this->PracticeProfile->find('first');
        	$this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);
		$this->PatientFamilyHistory->saveAudit('View');

		//load patient portal favorites
		$this->loadModel("PatientPortalFamilyFavorites");
		$this->set('famfavs',$this->PatientPortalFamilyFavorites->find('all'));

            }
        }
    }

    public function hx_obgyn() {
        
        $user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
	$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
		if($role_id == EMR_Roles::PATIENT_ROLE_ID and $user['patient_id'] != $patient_id)
		{
			$this->redirect("index");
		}

        //if checkin, see if online forms are present in order to forward to next step; otherwise they are finished checkin
        if($patient_checkin_id)
        {
                $this->loadModel("FormTemplate");
                $online_templates = $this->FormTemplate->find('count');
                if(empty($online_templates)) //if no online forms, check for downloadable forms
                {
                        $this->loadModel("AdministrationForm");
                        $online_templates= $this->AdministrationForm->find('count');
                }

                $this->set('online_templates', $online_templates);
        }
                
        $this->set('__patient', $this->PatientDemographic->getPatient($patient_id));
                
        $this->loadModel("PatientObGynHistory");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $this->set('PatientObGynHistory', $this->sanitizeHTML($this->PatientObGynHistory->find('all')));
        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
			if ($this->data['PatientObGynHistory']['type'] == "Gynecologic History")
			{
				if (!isset($this->data['PatientObGynHistory']['abnormal_pap_smear']) or @$this->data['PatientObGynHistory']['abnormal_pap_smear'] != 'Yes')
				{
					$this->data['PatientObGynHistory']['abnormal_pap_smear_date'] = "";
				}
				if (!isset($this->data['PatientObGynHistory']['abnormal_irregular_bleeding']) or @$this->data['PatientObGynHistory']['abnormal_irregular_bleeding'] != 'Yes')
				{
					$this->data['PatientObGynHistory']['abnormal_irregular_bleeding_date'] = "";
				}
				if (!isset($this->data['PatientObGynHistory']['endometriosis']) or @$this->data['PatientObGynHistory']['endometriosis'] != 'Yes')
				{
					$this->data['PatientObGynHistory']['endometriosis_date'] = "";
					$this->data['PatientObGynHistory']['endometriosis_text'] = "";
				}
				if (!isset($this->data['PatientObGynHistory']['sexually_transmitted_disease']) or @$this->data['PatientObGynHistory']['sexually_transmitted_disease'] != 'Yes')
				{
					$this->data['PatientObGynHistory']['sexually_transmitted_disease_date'] = "";
					$this->data['PatientObGynHistory']['sexually_transmitted_disease_text'] = "";
				}
				if (!isset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease']) or @$this->data['PatientObGynHistory']['pelvic_inflammatory_disease'] != 'Yes')
				{
					$this->data['PatientObGynHistory']['pelvic_inflammatory_disease_date'] = "";
					$this->data['PatientObGynHistory']['pelvic_inflammatory_disease_text'] = "";
				}
				unset($this->data['PatientObGynHistory']['age_started_period']);
				unset($this->data['PatientObGynHistory']['last_menstrual_period']);
				unset($this->data['PatientObGynHistory']['how_often']);
				unset($this->data['PatientObGynHistory']['how_long']);
				unset($this->data['PatientObGynHistory']['birth_control_method']);
				unset($this->data['PatientObGynHistory']['menopause']);
				unset($this->data['PatientObGynHistory']['menopause_text']);
				unset($this->data['PatientObGynHistory']['total_of_pregnancies']);
				unset($this->data['PatientObGynHistory']['number_of_full_term']);
				unset($this->data['PatientObGynHistory']['number_of_premature']);
				unset($this->data['PatientObGynHistory']['number_of_miscarriages']);
				unset($this->data['PatientObGynHistory']['number_of_abortions']);
				for ($i = 1; $i <= 3; ++$i)
				{
					unset($this->data['PatientObGynHistory']['type_of_delivery_'.$i]);
					unset($this->data['PatientObGynHistory']['delivery_weight_'.$i]);
					unset($this->data['PatientObGynHistory']['delivery_date_'.$i]);
				}
				$this->data['PatientObGynHistory']['abnormal_pap_smear_date'] = $this->data['PatientObGynHistory']['abnormal_pap_smear_date']?__date("Y-m-d", strtotime($this->data['PatientObGynHistory']['abnormal_pap_smear_date'])):'';
				$this->data['PatientObGynHistory']['abnormal_irregular_bleeding_date'] = $this->data['PatientObGynHistory']['abnormal_irregular_bleeding_date']?__date("Y-m-d", strtotime($this->data['PatientObGynHistory']['abnormal_irregular_bleeding_date'])):'';
				$this->data['PatientObGynHistory']['endometriosis_date'] = $this->data['PatientObGynHistory']['endometriosis_date']?__date("Y-m-d", strtotime($this->data['PatientObGynHistory']['endometriosis_date'])):'';
				$this->data['PatientObGynHistory']['sexually_transmitted_disease_date'] = $this->data['PatientObGynHistory']['sexually_transmitted_disease_date']?__date("Y-m-d", strtotime($this->data['PatientObGynHistory']['sexually_transmitted_disease_date'])):'';
				$this->data['PatientObGynHistory']['pelvic_inflammatory_disease_date'] = $this->data['PatientObGynHistory']['pelvic_inflammatory_disease_date']?__date("Y-m-d", strtotime($this->data['PatientObGynHistory']['pelvic_inflammatory_disease_date'])):'';
			}
			else if ($this->data['PatientObGynHistory']['type'] == "Menstrual History")
			{
				unset($this->data['PatientObGynHistory']['abnormal_pap_smear']);
				unset($this->data['PatientObGynHistory']['abnormal_pap_smear_date']);
				unset($this->data['PatientObGynHistory']['abnormal_irregular_bleeding']);
				unset($this->data['PatientObGynHistory']['abnormal_irregular_bleeding_date']);
				unset($this->data['PatientObGynHistory']['endometriosis']);
				unset($this->data['PatientObGynHistory']['endometriosis_date']);
				unset($this->data['PatientObGynHistory']['endometriosis_text']);
				unset($this->data['PatientObGynHistory']['sexually_transmitted_disease']);
				unset($this->data['PatientObGynHistory']['sexually_transmitted_disease_date']);
				unset($this->data['PatientObGynHistory']['sexually_transmitted_disease_text']);
				unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease']);
				unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease_date']);
				unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease_text']);
				if (!isset($this->data['PatientObGynHistory']['menopause']) or @$this->data['PatientObGynHistory']['menopause'] != 'Yes')
				{
					$this->data['PatientObGynHistory']['menopause_text'] = "";
				}
				unset($this->data['PatientObGynHistory']['total_of_pregnancies']);
				unset($this->data['PatientObGynHistory']['number_of_full_term']);
				unset($this->data['PatientObGynHistory']['number_of_premature']);
				unset($this->data['PatientObGynHistory']['number_of_miscarriages']);
				unset($this->data['PatientObGynHistory']['number_of_abortions']);
				for ($i = 1; $i <= 3; ++$i)
				{
					unset($this->data['PatientObGynHistory']['type_of_delivery_'.$i]);
					unset($this->data['PatientObGynHistory']['delivery_weight_'.$i]);
					unset($this->data['PatientObGynHistory']['delivery_date_'.$i]);
				}
				$this->data['PatientObGynHistory']['age_started_period'] = $this->data['PatientObGynHistory']['age_started_period']?__date("Y-m-d", strtotime($this->data['PatientObGynHistory']['age_started_period'])):'';
				$this->data['PatientObGynHistory']['last_menstrual_period'] = $this->data['PatientObGynHistory']['last_menstrual_period']?__date("Y-m-d", strtotime($this->data['PatientObGynHistory']['last_menstrual_period'])):'';
			}
			else if ($this->data['PatientObGynHistory']['type'] == "Pregnancy History")
			{
				unset($this->data['PatientObGynHistory']['abnormal_pap_smear']);
				unset($this->data['PatientObGynHistory']['abnormal_pap_smear_date']);
				unset($this->data['PatientObGynHistory']['abnormal_irregular_bleeding']);
				unset($this->data['PatientObGynHistory']['abnormal_irregular_bleeding_date']);
				unset($this->data['PatientObGynHistory']['endometriosis']);
				unset($this->data['PatientObGynHistory']['endometriosis_date']);
				unset($this->data['PatientObGynHistory']['endometriosis_text']);
				unset($this->data['PatientObGynHistory']['sexually_transmitted_disease']);
				unset($this->data['PatientObGynHistory']['sexually_transmitted_disease_date']);
				unset($this->data['PatientObGynHistory']['sexually_transmitted_disease_text']);
				unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease']);
				unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease_date']);
				unset($this->data['PatientObGynHistory']['pelvic_inflammatory_disease_text']);
				unset($this->data['PatientObGynHistory']['age_started_period']);
				unset($this->data['PatientObGynHistory']['last_menstrual_period']);
				unset($this->data['PatientObGynHistory']['how_often']);
				unset($this->data['PatientObGynHistory']['how_long']);
				unset($this->data['PatientObGynHistory']['birth_control_method']);
				unset($this->data['PatientObGynHistory']['menopause']);
				unset($this->data['PatientObGynHistory']['menopause_text']);
				/*
				for ($i = 1; $i <= 3; ++$i)
				{
					$this->data['PatientObGynHistory']['delivery_date_'.$i] = $this->data['PatientObGynHistory']['delivery_date_'.$i]?__date("Y-m-d", strtotime($this->data['PatientObGynHistory']['delivery_date_'.$i])):'';
				}*/
				if(!empty($this->data['PatientObGynHistory']['type_of_delivery'])) {
				 $length = count($this->data['PatientObGynHistory']['type_of_delivery']);
				} else {
				 $length=0;
				}
				$deliveries = array();
				for($ct = 0; $ct < $length; $ct++) {
					
					$type = trim($this->data['PatientObGynHistory']['type_of_delivery'][$ct]);
					$weight = trim($this->data['PatientObGynHistory']['delivery_weight'][$ct]);
					// added ounces
					$ounces = trim($this->data['PatientObGynHistory']['delivery_weight_ounce'][$ct]);
					$date = trim($this->data['PatientObGynHistory']['delivery_date'][$ct]);
					$date = __date('Y-m-d', strtotime($date));
					
					if ($type) {
						$deliveries[] = array(
							'type' => $type,
							'weight' => $weight,
							'ounces' => $ounces,
							'date' => $date,
						);
					}
					
				}
				
				$this->data['PatientObGynHistory']['deliveries'] = json_encode($deliveries);
				
				unset($this->data['PatientObGynHistory']['type_of_delivery']);
				unset($this->data['PatientObGynHistory']['delivery_weight']);
				// unset ounces
				unset($this->data['PatientObGynHistory']['delivery_weight_ounce']);
				unset($this->data['PatientObGynHistory']['delivery_date']);
				
				
				
				
			}
            $this->data['PatientObGynHistory']['patient_id'] = $patient_id;
			$this->data['PatientObGynHistory']['encounter_id'] = 0;
            $this->data['PatientObGynHistory']['modified_user_id'] =  $this->user_id;
            $this->data['PatientObGynHistory']['modified_timestamp'] =  __date("Y-m-d H:i:s");
        }
        switch($task)
        {
            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->PatientObGynHistory->create();
                    $this->PatientObGynHistory->save($this->data);

                    $this->PatientObGynHistory->saveAudit('New');

                    $this->Session->setFlash(__('Item(s) added.', true));
                                                $_r = array('action' => 'hx_obgyn', 'patient_id'=> $patient_id);
                                                if($patient_checkin_id)
                                                    $_r['patient_checkin_id']=$patient_checkin_id;

                                                $this->redirect($_r);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->PatientObGynHistory->save($this->data);

                    $this->PatientObGynHistory->saveAudit('Update');
                    
                    $ret = array();
                    echo json_encode($ret);

                    $this->Session->setFlash(__('Item(s) saved.', true));
                                                $_r = array('action' => 'hx_obgyn', 'patient_id'=> $patient_id);
                                                if($patient_checkin_id)
                                                    $_r['patient_checkin_id']=$patient_checkin_id;

                                                $this->redirect($_r);
                    exit;
                }
                else
                {
                    $ob_gyn_history_id = (isset($this->params['named']['ob_gyn_history_id'])) ? $this->params['named']['ob_gyn_history_id'] : "";
                    //echo $ob_gyn_history_id;
                    $items = $this->PatientObGynHistory->find(
                            'first',
                            array(
                                'conditions' => array('PatientObGynHistory.ob_gyn_history_id' => $ob_gyn_history_id)
                            )
                    );

                    $this->set('EditItem', $this->sanitizeHTML($items));
                    $this->set('rawItem', $items);
                }
            } break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientObGynHistory']['ob_gyn_history_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientObGynHistory->delete($id, false);
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->PatientObGynHistory->saveAudit('Delete');
                    }
                }

                    $this->Session->setFlash(__('Item(s) deleted.', true));
                                                $_r = array('action' => 'hx_obgyn', 'patient_id'=> $patient_id);
                                                if($patient_checkin_id)
                                                    $_r['patient_checkin_id']=$patient_checkin_id;

                                                $this->redirect($_r);
            }
            default:
            {
                $this->set('PatientObGynHistory', $this->sanitizeHTML($this->paginate('PatientObGynHistory', array('patient_id' => $patient_id))));

                $this->PatientObGynHistory->saveAudit('View');
            }
        }        
        
    }
    
	public function in_house_work_labs()
    {
        $user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user = $this->Session->read('UserAccount');
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		if($role_id == EMR_Roles::PATIENT_ROLE_ID and $user['patient_id'] != $patient_id)
		{
			$this->redirect("index");
		}
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

		$view_labs = (isset($this->params['named']['view_labs'])) ? $this->params['named']['view_labs'] : "";

		if($view_labs == 1)
		{
			$this->redirect(array('action' => 'lab_results_electronic', 'patient_id' => $patient_id));
		}

        switch($task)
        {
            default:
            {
                $encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
                //debug($encounter_items);
				
				if ($encounter_items) {
				  $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Labs'))));
				} else {
				  $this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => 0, 'EncounterPointOfCare.order_type' => 'Labs'));
				  $this->set('EncounterPointOfCare', array());
				}

				$this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
			} break;
        }
    }
	
	public function lab_results_electronic()
    {
        $this->loadModel("EmdeonOrder");
        $this->EmdeonOrder->execute($this, false);
	}

    public function lab_results_electronic_view()
    {
        $this->layout = "empty";
        $this->loadModel("EmdeonLabResult");

        $lab_result_id = (isset($this->params['named']['lab_result_id'])) ? $this->params['named']['lab_result_id'] : "";
        $page = (isset($this->params['named']['page'])) ? $this->params['named']['page'] : 1;
				$this->set('report_html', $this->EmdeonLabResult->getHTML($lab_result_id, $page));
		
		$this->render("/patients/lab_results_electronic_view");
    }

	public function allergies()
    {
        $user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
                    	$PracticeSetting = ClassRegistry::init('PracticeSetting')->getSettings();
	    		$rx_setup = $PracticeSetting->rx_setup;
		
		$this->loadModel("PatientDemographic");
        $show_all_allergies = (isset($this->params['named']['show_all_allergies'])) ? $this->params['named']['show_all_allergies'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
        $this->loadModel("PatientAllergy");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		$dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
		$dosespot_xml_api = new Dosespot_XML_API();
		
        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientAllergy']['patient_id'] = $patient_id;
			$this->data['PatientAllergy']['encounter_id'] = 0;
            $this->data['PatientAllergy']['modified_user_id'] =  $this->user_id;
            $this->data['PatientAllergy']['modified_timestamp'] =  __date("Y-m-d H:i:s");
            for($i=$this->data['PatientAllergy']['reaction_count'] + 1; $i<=10; $i++)
            {
               $this->data['PatientAllergy']['reaction'.$i] = "";
               $this->data['PatientAllergy']['severity'.$i] = "";
            }
        }
		
		switch($task)
		{
			case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->PatientAllergy->create();
                    $this->PatientAllergy->save($this->data);
                    $this->PatientAllergy->saveAudit('New');
                    
                    	if($rx_setup == 'Electronic_Dosespot')
                    	{
                    		$this->loadModel("PatientDemographic");
 				$dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
 				//Add Allergy data to Dosespot  					                  
				$this->data['agent'] = ucwords(strtolower($this->data['PatientAllergy']['agent']));
				$this->data['reaction1'] = ucwords(strtolower($this->data['PatientAllergy']['reaction1'])); 
				$this->data['allergy_code'] = 3;
				$this->data['allergy_code_type'] = 'AllergyClass';
				if( !empty($dosespot_patient_id) ){
				  $dosespot_xml_api = new Dosespot_XML_API(); 
            			  $added_allergy_item = $dosespot_xml_api->executeAddAllergy($dosespot_patient_id, $this->data);
            			}
            		}	                   
                    
					
					$this->Session->setFlash(__('Item(s) added.', true));
					$_r = array('action' => 'allergies', 'patient_id'=> $patient_id);
					if($patient_checkin_id)
					   $_r['patient_checkin_id']=$patient_checkin_id;
					   
					$this->redirect($_r);

                }
            } break;
			case "edit":
            {
                if(!empty($this->data))
                {
                    $this->PatientAllergy->save($this->data);
                    $this->PatientAllergy->saveAudit('Update');
					 //Update Allergy data in Dosespot
					if(($this->data['PatientAllergy']['dosespot_allergy_id'] != "") and ($this->data['PatientAllergy']['dosespot_allergy_id'] !=0) and ($rx_setup == 'Electronic_Dosespot') )
					{
		
						$allergy_data['dosespot_allergy_id']=(int)$this->data['PatientAllergy']['dosespot_allergy_id'];
						$allergy_data['reaction1']=ucwords(strtolower($this->data['PatientAllergy']['reaction1']));
						$allergy_data['status']=$this->data['PatientAllergy']['status'];
				   		$dosespot_xml_api = new Dosespot_XML_API(); 
				   		$dosespot_xml_api->executeEditAllergy($dosespot_patient_id, $allergy_data);
						
					}
                    
					
					$this->Session->setFlash(__('Item(s) saved.', true));
					$_r = array('action' => 'allergies', 'patient_id'=> $patient_id);
					if($patient_checkin_id)
					   $_r['patient_checkin_id']=$patient_checkin_id;
					   
					$this->redirect($_r);					
                }
                else
                {
                    $allergy_id = (isset($this->params['named']['allergy_id'])) ? $this->params['named']['allergy_id'] : "";
                    //echo $family_history_id;
                    $items = $this->PatientAllergy->find(
                            'first',
                            array(
                                'conditions' => array('PatientAllergy.allergy_id' => $allergy_id)
                            )
                    );

                    $this->set('EditItem', $this->sanitizeHTML($items));
                }
            } break;
			case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientAllergy']['allergy_id'];

                    foreach($ids as $id)
                    {
                       $id_array = explode('|', $id);
					   $allergy_id = $id_array[0];
					   $dosespot_allergy_id = $id_array[1];
					   $this->PatientAllergy->delete($allergy_id, false);
					   
					   //Delete allergy in Dosespot
					   if( !empty($dosespot_allergy_id) and $rx_setup == 'Electronic_Dosespot' )
					   {
			   
						$allergy_data['dosespot_allergy_id']=(int)$dosespot_allergy_id;
						$allergy_data['status']='Deleted';
				   		$dosespot_xml_api = new Dosespot_XML_API(); 
				   		$dosespot_xml_api->executeEditAllergy($dosespot_patient_id, $allergy_data);
					   }
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->PatientAllergy->saveAudit('Delete');
			$this->Session->setFlash($ret["delete_count"] . __(' Item(s) deleted.', true));
                    }
                    	$_r = array('action' => 'allergies', 'patient_id'=> $patient_id);
			if($patient_checkin_id)
				$_r['patient_checkin_id']=$patient_checkin_id;
					   
				$this->redirect($_r);
                }
            }
			default:
			{
				$demographic_items = $this->PatientDemographic->find('first', array('fields' => 'allergies_none', 'conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
				$allergies_none = $demographic_items['PatientDemographic']['allergies_none'];
				$this->set('allergies_none', $allergies_none);
		
				if($show_all_allergies == 'no')
				{
					$this->set('show_all_allergies', 'no');
					$this->set('PatientAllergy', $this->sanitizeHTML($this->paginate('PatientAllergy', array('PatientAllergy.patient_id' => $patient_id, 'PatientAllergy.status'=>'Active'))));
				}
				else
				{
					$this->set('show_all_allergies', 'yes');
					$this->set('PatientAllergy', $this->sanitizeHTML($this->paginate('PatientAllergy', array('PatientAllergy.patient_id' => $patient_id))));
				}
		
				$this->PatientAllergy->saveAudit('View');
			}
		}
    }

    public function problem_list()
    {
        $user = $this->Session->read('UserAccount');
		$role_id = $user['role_id'];
		
		if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
		{
			$this->redirect("index");
		}
		
		$this->loadModel("PatientProblemList");
        $this->loadModel("PatientDemographic");
        $this->loadModel("PatientMedicalHistory");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $show_all_problems = (isset($this->params['named']['show_all_problems'])) ? $this->params['named']['show_all_problems'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
	$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
	
	//if checkin, see if online forms are present in order to forward to next step; otherwise they are finished checkin
	if($patient_checkin_id)
	{
		$this->loadModel("FormTemplate");
		$online_templates = $this->FormTemplate->find('count');
		if(empty($online_templates)) //if no online forms, check for downloadable forms
		{
			$this->loadModel("AdministrationForm");	
			$online_templates= $this->AdministrationForm->find('count');
		}
		
		$this->set('online_templates', $online_templates);
	}
		if($show_all_problems == 'no')
		{
			$this->set('show_all_problems', 'no');
			$this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList', array('patient_id' => $patient_id, 'status'=>'Active'))));
		}
		else
		{
			$this->set('show_all_problems', 'yes');
			$this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList', array('patient_id' => $patient_id))));
		}

		$this->PatientProblemList->saveAudit('View');
    }
	
    /**
     *  Output json data for use in patient portal calendar
     */
    function json_calendar() {
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $role_id = $user['role_id'];
        $patient_id = $user['patient_id'];

        if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
        {
                $this->redirect("index");
        }
		        
        $this->layout = 'empty' ;
        $this->loadModel("ScheduleCalendar");
        $this->loadModel("PreferencesWorkSchedule");
        $this->loadModel("UserAccount");
				$this->loadModel("PracticeLocation");
        
        // Initialize calendar data
        $data = array();
        
        // Get specified provider
        $provider = isset($this->params['url']['provider']) ? $this->params['url']['provider'] : 'Any Available Nurse';
        
        // No provider given, send empty calendar data
        if (!$provider) {
            echo json_encode($data);
            die();
        }
        
        // Get start date
        $start = isset($this->params['url']['start']) ? intval($this->params['url']['start']) : time() ;
        $start = __date('Y-m-d', $start);
        
        // Days from start date to next 7 days
        $days = array();
        for ($ct = 0; $ct <= 7; $ct++) {
            $days[] = __date('Y-m-d', strtotime( $start . " +$ct days"));
        }
        
        // Business hours
				$operationalHours = $this->PracticeLocation->getOperationalHours();
        $officeOpen = $operationalHours->start;
        $officeClose = $operationalHours->end;

        // Business hours that should be plotted in the calendar
        $scheduleStart = null;
        $scheduleEnd = null;
        
        // Initialize date to build
        $appointmentSlots = array();
        $unavailableSlots = array();
        
        // Find appointments within selected days
        $appointments = $this->ScheduleCalendar->find('all', array(
            'conditions' => array(
                'ScheduleCalendar.patient_id' => $patient_id, 
                'ScheduleCalendar.date BETWEEN ? and ?' =>  array($days[0], $days[6]),
        ), 'recursive' => -1 ));
        // Build appointment schedule data
        foreach ($appointments as $a) {
            $aStartHour = explode(':', $a['ScheduleCalendar']['starttime']);
            $aStart = strtotime($a['ScheduleCalendar']['date'] . ' ' . $aStartHour[0] . ':00:00');
            
            $approved = (strtolower($a['ScheduleCalendar']['approved']) == 'no') ? false : true;
            $tmp = array(
                'id' => $a['ScheduleCalendar']['calendar_id'],
                'start' => __date('D, d M Y H:i:s', $aStart),
                'end' => __date('D, d M Y H:i:s', $aStart + 3600),
                'title' => '',
                'approved' => $approved,
            );
            
            $appointmentSlots[] = $tmp;
        }
        
        // List of users to check for availablity
        $userIdList = array();
        
        // Get user id of physicians if searching for any available doctor
        if (strtolower($provider) == strtolower('Any Available Doctor')) {
            $physicians = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => EMR_Roles::PHYSICIAN_ROLE_ID)));
            $userIdList = Set::extract('/UserAccount/user_id', $physicians);
        }
        
        // Get user id of nurses if searching for any available nurse
        $nurses = array();
        if (strtolower($provider) == strtolower('Any Available Nurse')) {
            $nurses = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => EMR_Roles::NURSE_PRACTITIONER_ROLE_ID)));
            $userIdList = Set::extract('/UserAccount/user_id', $nurses);
        }
        
        // If we have a specific/actual provider id, use it instead
        if (is_numeric($provider)) {
            $userIdList[] = intval($provider);
        }

        // Iterate through days
        foreach ($days as $givenDay) {
            
            $date = __date('Y-m-d H:i:s', strtotime($givenDay));
            
            // Track unavailable hour ranges
            $startUnavailable = null;
            $allDayClosed  = true;
            
            // Iterate through business hours
            for ($givenHour = $officeOpen; $givenHour <= $officeClose; $givenHour++) {
                
                $thisHour = __date("H:i:s", strtotime(sprintf("%02d:00", $givenHour)));                

                
                // Not available unless proven
                $available = false;

                // Iterate through the user list, if any
                if (!empty($userIdList)) {
                    
                    // Check if any user is available during the time slot
                    // @TODO: Optimize this part to minize db queries
                    foreach($userIdList as $userId) {
                        $available = $this->PreferencesWorkSchedule->isAvailable_PatientPortal($date, $thisHour, $userId);	
                        //Check the availability of the Providers
                        if($available) {
                            break;
                        }
                    }
                }

                // Timeslot is available
                if ($available) {
                    
                    // Check if there were is already a starting sched for plotting
                    if ($scheduleStart === null) {
                        // No, schedule starts with this current hour
                        $scheduleStart = $givenHour;
                    }
                    
                    // If the current hour is earlier than any 
                    // the the $scheduleStart currently noted, use it
                    if ($givenHour < $scheduleStart) {
                        $scheduleStart = $givenHour;
                    }
                    
                    
                    // There was a timeslot available
                    // location was open at some point during the day
                    $allDayClosed = false;
                    
                    // If time slots were unavailable
                    if ($startUnavailable !== null) {
                        
                        // Note range of unavailable time
                        // From start to current time
                        $tmp = array(
                            'start' => __date('D, d M Y H:i:s', strtotime($givenDay . ' ' . $startUnavailable)) ,
                            'end' => __date('D, d M Y H:i:s', strtotime($givenDay . ' ' . $thisHour)) ,
                            'free' => false
                        );
                        
                        // Add to unavailable schedules
                        $unavailableSlots[] = $tmp;
                        
                        $startUnavailable = null;
                    }
                    
                // Timeslot not available, note it
                } else {
                    
                    if ($startUnavailable === null) {
                        $startUnavailable = $thisHour;
                    }
                    
                    // Check if there were is already an ending sched for plotting
                    if ($scheduleEnd === null) {
                        // No, schedule ends with this current hour
                        $scheduleEnd = $givenHour;
                    }
                    
                    // If the current hour is later than any 
                    // the the $scheduleEnd currently noted, use it
                    if ($givenHour > $scheduleEnd) {
                        $scheduleEnd = $givenHour;
                    }
                    
                }
            }
            
            // Case when still unavailable until closing
            if ($startUnavailable !== null) {

                // Note range of unavailable time
                // From start to current time

                $tmp = array(
                    'start' => __date('D, d M Y H:i:s', strtotime($givenDay . ' ' . $startUnavailable)) ,
                    'end' => __date('D, d M Y H:i:s', strtotime($givenDay . ' ' . '+1 day')) ,
                    'free' => false
                );

                $unavailableSlots[] = $tmp;
                $startUnavailable = null;
            }            
            
            // Case when the location is closed all day
            if ($allDayClosed) {
                $tmp = array(
                    'start' => __date('D, d M Y H:i:s', strtotime($givenDay . ' ' . __date("H:i:s", strtotime(sprintf("%02d:00", $officeOpen))))) ,
                    'end' => __date('D, d M Y H:i:s', strtotime($givenDay . ' ' . '+1 day')) ,
                    'free' => false
                );
                $unavailableSlots[] = $tmp;
            }
            
        }
        
        $options = array(
            'businessHours' => array(
                'start' => $scheduleStart,
                'end' => $scheduleEnd,
                'limitDisplay' => true,
            )
        );
        
        // Build data ...
        $data['events'] = $appointmentSlots;
        $data['freebusys'] = $unavailableSlots;
        $data['options'] = $options;
        
        // ... and output as json
        echo json_encode($data);
        die();
    }
    
    /**
     * Used by Calendar to receive and process appointment request
     */
    function save_appointment_request(){
        
        // Get user credentials and check permissions
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $role_id = $user['role_id'];
        $patient_id = $user['patient_id'];

        if($this->getAccessType("dashboard", "patient_portal") == 'NA' || $role_id != EMR_Roles::PATIENT_ROLE_ID)
        {
                $this->redirect("index");
        }
        
        // Get relevant data
        $start = isset($this->params['form']['start']) ? trim($this->params['form']['start']): '';
        $provider = isset($this->params['form']['provider']) ? trim($this->params['form']['provider']): '';
        $reason = isset($this->params['form']['reason']) ? trim($this->params['form']['reason']): '';
        
        $start = strtotime($start);
        
        $this->loadModel("ScheduleCalendar");

        // Build schedule data
        $sched = array('ScheduleCalendar' => array(
            'starttime' => __date('H:i', $start),
            'endtime' => __date('H:i', $start + 3600),
            'duration' => 60,
            'date' => __date('Y-m-d', $start),
            'reason_for_visit' => $reason,
            'patient_id' => $patient_id,
            'approved' => 'no',
        ));
        
        // Actual provider id given? Use it
        if (is_numeric($provider)) {
            $sched['ScheduleCalendar']['provider_id'] = $provider;
        }
        
        // Default response for json
        $response = array(
            'success' => true,
            'msg' => '',
            'calendar_id' => '',
        );
        
        // If successfully saved, send notifications to front-desk accounts
        if ($this->ScheduleCalendar->save($sched)) {
            
            $calendarId = $this->ScheduleCalendar->getLastInsertId();
        
            // Specify successful response
            $response['msg'] = 'Appointment request successfully submitted';
            $response['calendar_id'] = $calendarId;
            $response['success'] = true;
            
            $this->loadModel("UserAccount");
            $this->loadModel("MessagingMessage");
            $user = $this->Session->read('UserAccount');
            $this->data['MessagingMessage']['sender_id'] = $user_id;
            $this->data['MessagingMessage']['patient_id'] = $patient_id;
            $this->data['MessagingMessage']['calendar_id'] = $calendarId;
            $this->data['MessagingMessage']['type'] = "Appointment";
            $this->data['MessagingMessage']['subject'] = "Appointment Request";
            $schedule_url = Router::url(array('controller'=>'schedule', 'action' => 'index', 'appointment' => $calendarId));
            $this->data['MessagingMessage']['message'] = "Greetings, I would like to request an appointment for: ".__date('l, F jS Y',strtotime($sched['ScheduleCalendar']['date']))." @ ".__date('g:ia ',strtotime($sched['ScheduleCalendar']['starttime']))." if available. Please review this request and approve it or choose a new time slot:  <br><a href=".$schedule_url.">See schedule</a>";                        
            $this->data['MessagingMessage']['priority'] = "Normal";
            $this->data['MessagingMessage']['status'] = "New";
            $this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
            $this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $this->data['MessagingMessage']['modified_user_id'] = $patient_id;
			$this->data['MessagingMessage']['time'] = time();

            $frontdesk_users = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => EMR_Roles::FRONT_DESK_ROLE_ID)));

            if(count($frontdesk_users)!=0) {
                foreach($frontdesk_users as $frontdesk_user) {
                    $this->MessagingMessage->create();							
                    $this->data['MessagingMessage']['recipient_id'] = $frontdesk_user['UserAccount']['user_id'];
                    $this->MessagingMessage->save($this->data);
                }
            } else {
		// no front desk roles exist, so find another user
		$this->loadModel('UserGroup');
		$nmessaging=$this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_PATIENT_NURSE_MESSAGING,false))));
		if(count($nmessaging) > 0) {
		  foreach($nmessaging as $nm) {
                    $this->MessagingMessage->create();
                    $this->data['MessagingMessage']['recipient_id'] = $nm['UserAccount']['user_id'];
                    $this->MessagingMessage->save($this->data);
		  }
		}
	    }

        // Something went wrong when saving the schedule
        } else {
            
            // Note it in the response message
            $response['success'] = false;
            $response['msg'] = 'Failed to create appointment for selected schedule';
        }
        
        // Output response date in json format
        echo json_encode($response);
        
        die();
    }
    
	function create_calendar()
	{
	    $this->loadModel("ScheduleCalendar");
		$this->loadModel("PreferencesWorkSchedule");
		$ret = array();
		$this->loadModel("UserAccount");

		$day = array();
		
                for ($ct = 0 ; $ct < 7 ; $ct++) {
                    $day[] = __date('Y-m-d', strtotime("+$ct days"));
                }
                
		$unapproved_appointments = $this->ScheduleCalendar->find('all', array('conditions' => array('ScheduleCalendar.patient_id' => $this->data['ScheduleCalendar']['patient_id'], 'ScheduleCalendar.date >=' =>  __date('Y-m-d'))));
		if($unapproved_appointments) 
        	{
		    $this->set('unapproved_appointments', $unapproved_appointments);
		}
		
		$content = '';
		for($j=0; $j<count($day); $j++)
		{			
		   $formatted_day = __date("D (m/d)", strtotime($day[$j]));
		   $content .= '<th>'.$formatted_day.'</th>';	
		}

		$content .= '</tr></thead><tbody>';

		for($i = 7; $i <= 23; $i++)
		{
			$time1 = ($i <= 12)?$i:($i-12);
			$time2 = ($i < 12)?($i+1):(($i-12)+1);
			$ampm1 = ($i >= 12)?'pm':'am';
			$ampm2 = (($i+1) >= 12)?'pm':'am';
            if($i == 23)
			{
			   $ampm2 = 'am';
			}
			$content .= '<tr><th>'.$time1.':00'.$ampm1.' - '.$time2.':00'.$ampm2.'</th>';

			for($j=0; $j<count($day); $j++)
			{
				$addclass = ' ui-unavailable';
				$title='';
				
				if($this->data['ScheduleCalendar']['provider']!='')
				{
				     $date = __date('Y-m-d H:i:s', strtotime($day[$j]));
		           //echo 'str:'.strtotime($i.' '.$ampm1);
					$formatted_time = sprintf("%02d:00", $i);
					
					  $start = __date("H:i:s", strtotime($formatted_time));
                     
					 if($this->data['ScheduleCalendar']['provider']=='Any Available Doctor')
				     {
				         $physician_users = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => EMR_Roles::PHYSICIAN_ROLE_ID)));
						 if(count($physician_users)!=0)
						 {
							 foreach($physician_users as $physician_user)
							 {
								 $availability = $this->PreferencesWorkSchedule->isAvailable_PatientPortal($date, $start, $physician_user['UserAccount']['user_id']);	
								 //Check the availability of the Providers
								 if($availability)
								 {
									 $addclass = '';
									 break;
								 }
							 }
						 }
				     }
				     elseif($this->data['ScheduleCalendar']['provider']=='Any Available Nurse')
				     {
				      
						 $nurse_users = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => EMR_Roles::NURSE_PRACTITIONER_ROLE_ID)));
				         if(count($nurse_users)!=0)
				         {
					         foreach($nurse_users as $nurse_user)
					         {
							      
								  $availability = $this->PreferencesWorkSchedule->isAvailable_PatientPortal($date, $start, $nurse_user['UserAccount']['user_id']);	
								 //Check the availability of the Nurses
								 if($availability)
								 {
									 //echo $nurse_user['UserAccount']['username'];  
									 $addclass = '';
									 break;
								 }
					         }
				         }
				     }
				     else
				     {				
					     $availability = $this->PreferencesWorkSchedule->isAvailable_PatientPortal($date, $start, $this->data['ScheduleCalendar']['provider']);	
						 //Check the availability of the Provider
						 if($availability)
						 {
							 $addclass = '';
						 }
					 }	 
				}
				
				if(isset($unapproved_appointments))
				{
					 foreach($unapproved_appointments as $unapproved_appointment)
					 {
						 $starttime= explode(':',$unapproved_appointment['ScheduleCalendar']['starttime']);
						 if((date('Y-m-d', strtotime($unapproved_appointment['ScheduleCalendar']['date'])) == $day[$j]) and ((int)$starttime[0]==$i) and ($unapproved_appointment['ScheduleCalendar']['approved']!='no'))
						 {
							 $addclass = ' ui-approved';
							 $title='Approved';
							 break;
						 }
						 elseif((date('Y-m-d', strtotime($unapproved_appointment['ScheduleCalendar']['date'])) == $day[$j]) and ((int)$starttime[0]==$i) and ($unapproved_appointment['ScheduleCalendar']['approved']=='no'))
						 {								     
							 $addclass = ' ui-unapproved';
							 $title='Not Approved';
							 break;
						 }
						 else
						 {
							 $title='';
						 }
					 }
				}		
				
				$time =  $time1.'|'.$time2.'|'.$ampm1;	
                                $_day = __date($this->__global_date_format, strtotime($day[$j]));
			    $content .= '<td class="ui-selectee'.$addclass.'"  date="'.$_day.'"  time="'.$time.'" title="'.$title.'" ></td>';
			}
		}
        $ret['content'] = $content;
		echo json_encode($ret);
		exit;
	}
	
	function getCalendar() 
	{
		if(isset($_GET["id"]))
		{
			$id = $_GET["id"];
		}
		else
		{
			$id=false;
		}

		if($id)
		{
			$ret = $this->__updateDetailedCalendar($id,$this->data);
		}else
		{
			$ret = $this->__addDetailedCalendar($this->data);
		}
		echo json_encode($ret);
		exit;
    }
	
	function __addDetailedCalendar($data)
	{
		$this->loadModel("ScheduleCalendar");
		$ret = array();
		try{
			$login = $this->Session->read('login');
			if (!empty($data))
			{	
				if($data['provider']=='Any Available Doctor')
				{
				
				}
				elseif($data['provider']=='Any Available Nurse')
				{
				
				}
				else
				{
				    $data['ScheduleCalendar']['provider_id'] = $data['provider'];
				}
				$data['ScheduleCalendar']['endtime'] = __date("H:i", strtotime($data['ScheduleCalendar']['starttime'] . ' ' . $data['ScheduleCalendar']['ampm'] . ' + 60 minutes'));
				$data['ScheduleCalendar']['starttime'] = __date("H:i", strtotime($data['ScheduleCalendar']['starttime'] . ' ' . $data['ScheduleCalendar']['ampm']));
				$data['ScheduleCalendar']['duration'] = 60;
				$data['ScheduleCalendar']['date'] = $this->ScheduleCalendar->php2MySqlTime($this->ScheduleCalendar->js2PhpTime($data['ScheduleCalendar']['date']));
				$this->ScheduleCalendar->create();
				$result = $this->ScheduleCalendar->save($data);
			}
			if($result===false){
				$ret['IsSuccess'] = false;
				$ret['Msg'] = 'Failed';
			}else{
				$ret['IsSuccess'] = true;
				$ret['Msg'] = 'add success';
				$ret['Data'] = $this->ScheduleCalendar->getLastInsertId();
				
				$this->loadModel("UserAccount");
				$this->loadModel("MessagingMessage");
				$user = $this->Session->read('UserAccount');
				$this->data['MessagingMessage']['sender_id'] = $user['user_id'];
                $this->data['MessagingMessage']['patient_id'] = $data['ScheduleCalendar']['patient_id'];
				$this->data['MessagingMessage']['calendar_id'] = (int)$ret['Data'];
				$this->data['MessagingMessage']['type'] = "Appointment";
                $this->data['MessagingMessage']['subject'] = "Appointment Request";
				$schedule_url = Router::url(array('controller'=>'schedule', 'action' => 'index', 'appointment' => $this->data['MessagingMessage']['calendar_id']));
				$this->data['MessagingMessage']['message'] = "Greetings, I would like to request a doctor's appointment:<br><a href=".$schedule_url.">See schedule</a>";                        
                $this->data['MessagingMessage']['priority'] = "Normal";
                $this->data['MessagingMessage']['status'] = "New";
				$this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
				$this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
				$this->data['MessagingMessage']['modified_user_id'] = $data['ScheduleCalendar']['patient_id'];
				$this->data['MessagingMessage']['time'] = time();
				
				/*if($data['provider']=='Any Available Doctor')
				{
				    $physician_users = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => EMR_Roles::PHYSICIAN_ROLE_ID)));
				    if(count($physician_users)!=0)
				    {
					    foreach($physician_users as $physician_user)
					    {
							$this->MessagingMessage->create();							
							$this->data['MessagingMessage']['recipient_id'] = $physician_user['UserAccount']['user_id'];
							$this->MessagingMessage->save($this->data);
					    }
				    }
				}
				elseif($data['provider']=='Any Available Nurse')
				{
				    $nurse_users = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => EMR_Roles::NURSE_PRACTITIONER_ROLE_ID)));
				    if(count($nurse_users)!=0)
				    {
					    foreach($nurse_users as $nurse_user)
					    {
							$this->MessagingMessage->create();							
							$this->data['MessagingMessage']['recipient_id'] = $nurse_user['UserAccount']['user_id'];
							$this->MessagingMessage->save($this->data);
					    }
				    }
				}
				else
				{
                    $this->MessagingMessage->create();
                    $this->data['MessagingMessage']['recipient_id'] = $data['provider'];    
                    $this->MessagingMessage->save($this->data);						
				}*/
				
				$frontdesk_users = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => EMR_Roles::FRONT_DESK_ROLE_ID)));
				if(count($frontdesk_users)!=0)
				{
					foreach($frontdesk_users as $frontdesk_user)
					{
						$this->MessagingMessage->create();							
						$this->data['MessagingMessage']['recipient_id'] = $frontdesk_user['UserAccount']['user_id'];
						$this->MessagingMessage->save($this->data);
					}
				}
			}
		}catch(Exception $e){
			$ret['IsSuccess'] = false;
			$ret['Msg'] = $e->getMessage();
		}
		return $ret;
	}
	
	public function non_clinical_index()
    {
		//redirect to clinical -- disabled this view file since it was never updated as we made features
		$this->redirect("index");	
		exit;
	/*
	    $tutor_mode_value = $this->UserAccount->find('first', array('conditions' => array('UserAccount.user_id' => $this->user_id)));
		$this->set("disable_tutor_mode", $tutor_mode_value['UserAccount']['tutor_mode']);
	    
		if($this->getAccessType("dashboard", "non_clinical_index") == 'NA')
		{
			$this->redirect("index");
		}
	
		if($this->Session->check('dashboard_location') == false)
		{
			$this->Session->write('dashboard_location', 0);
		}
		// check for provider filter
		if($this->Session->check('dashboard_provider') == false)
		{
			$this->Session->write('dashboard_provider', $this->user_id);
		}
		
		$location_id = $this->Session->read('dashboard_location');
		$provider_id = ($this->Session->read('dashboard_provider'))? $this->Session->read('dashboard_provider') : 0;
		$this->loadModel('UserGroup');
		$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";
		$showdate = (isset($this->params['named']['showdate'])) ? $this->params['named']['showdate'] : "";
		$date = $this->Session->read('DashboardDate');
		$role_ids = $this->UserGroup->getRoles(EMR_Groups::GROUP_NON_PROVIDERS);

		if ($view == "")
		{
			$this->redirect(array('action' => 'non_clinical_index', 'view' => 'today'));
		}

		if ($view == "previous_day")
		{
			if ($date)
			{
				$date = explode("-", $date);
				$date = __date("Y-m-d", mktime(0, 0, 0, $date[1], $date[2] - 1, $date[0]));
			}
			else
			{
				$date = __date("Y-m-d", mktime(0, 0, 0, __date("m"), __date("d") - 1, __date("Y")));
			}
		}
		else if ($view == "next_day")
		{
			if ($date)
			{
				$date = explode("-", $date);
				$date = __date("Y-m-d", mktime(0, 0, 0, $date[1], $date[2] + 1, $date[0]));
			}
			else
			{
				$date = __date("Y-m-d", mktime(0, 0, 0, __date("m"), __date("d") + 1, __date("Y")));
			}
		}
		else if($view == "same_day")
		{
			
		}
		else
		{
			if($showdate == 'true')
			{
				$date = __date("Y-m-d", strtotime($this->data['setdate']));
				$this->set("setdate", $this->data['setdate']);
			}
			else
			{
				$date = __date("Y-m-d");
			}
		}
		
		$this->Session->write('DashboardDate', $date);

		$user = $this->Session->read('UserAccount');
		$user_id = $user['user_id'];
		$role_id = $user['role_id'];
    	$this->ScheduleCalendar->recursive = 0;
    	if(isset($_POST['frm_submit']))
    	{
    		$this->Session->write('showall', isset($_POST['show_all']));
    	}
    	else if($this->Session->read('showall')){
    		$this->Session->write('showall', true);
    	}
		else if($role_id == EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $role_id == EMR_Roles::PRACTICE_ADMIN_ROLE_ID){//else if(in_array($role_id, $role_ids)){
			$this->Session->write('showall', true);
		}
    	else{
    		$this->Session->write('showall', false);
    	}
		
		if(!$this->Session->read('showall'))
		{
			$conditions = array();
			if($provider_id != 0){
				$conditions['ScheduleCalendar.provider_id'] = $user_id;
			}
			$conditions['ScheduleCalendar.date'] = $date;
			$conditions['ScheduleCalendar.approved !='] = 'no';
			if($location_id != 0)
			{
				$conditions['ScheduleCalendar.location'] = $location_id;
			}
			// added in filter array
			if($provider_id != 0)
			{
				$conditions['ScheduleCalendar.provider_id'] = $provider_id;
			}
			
			$this->paginate['ScheduleCalendar'] =
				array(
					'limit' => 50
					, 'conditions' => $conditions
				);
				
		}
		else
		{
			$conditions = array();
			$conditions['ScheduleCalendar.date'] = $date;
			$conditions['ScheduleCalendar.approved !='] = 'no';
			if($location_id != 0)
			{
				$conditions['ScheduleCalendar.location'] = $location_id;
			}
			// added provider filter array
			$filter_role = $this->UserAccount->getUserRole($provider_id);
			
			if($filter_role == EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $filter_role == EMR_Roles::PRACTICE_ADMIN_ROLE_ID)
			{
				$provider_id = 0;
			}
			if($provider_id != 0){
				$conditions['ScheduleCalendar.provider_id'] = $provider_id;
			}
			         
			$this->paginate['ScheduleCalendar'] =
			array(
				'limit' => 10
				, 'conditions' => $conditions
			);
			
		}
                
        $this->set('selectedDate', $date);
		$this->set('schedulecalendar', $this->paginate('ScheduleCalendar'));
		$this->set('show_all', ($this->Session->read('showall'))? "checked":"");
		
		$encounter_access = (int)$this->validateAccess("encounters", "non_clinical_index");
		$patient_access = (int)$this->validateAccess("patients", "non_clinical_index");
		
		$this->set("encounter_access", $encounter_access);
		$this->set("patient_access", $patient_access);
		
		$this->loadModel('PracticeLocation');
		$this->set("location_name", $this->sanitizeHTML($this->PracticeLocation->getLocation($location_id)));
		$this->set("total_location", count($this->sanitizeHTML($this->PracticeLocation->find('all'))));
		
		// assigned values to variables to use in templates
		if($provider_id != 0){
			$this->set("provider_name", $this->sanitizeHTML($this->UserAccount->getUserShortRealName($provider_id)));
		}
		else{
			$this->set("provider_name", "All Providers");
		}
		//$this->set("avail_provider", count($this->sanitizeHTML($this->UserAccount->getProviders())));
		$this->set("avail_provider", count($this->sanitizeHTML($this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,false)), 'fields' => array('UserAccount.user_id'))))));
	*/
    }
	
	public function index(){
    $tutor_mode_value = $this->UserAccount->find('first', array('conditions' => array('UserAccount.user_id' => $this->user_id)));
		$this->set("disable_tutor_mode", $tutor_mode_value['UserAccount']['tutor_mode']);
		$db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
		$this->cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
		
		//check for the search cache 
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        if( $task == 'save_advance_search' ) {
					$adv_search['status_id'] = $this->params['named']['status_enc'];
					$adv_search['room_id'] = $this->params['named']['room_enc'];
					$adv_search['type_enc'] = $this->params['named']['type_enc'];
					$adv_search['location_enc'] = $this->params['named']['location_enc'];
					$adv_search['provider_enc'] = $this->params['named']['provider_enc'];
					
					Cache::set(array('duration' => '+10 years'));
					Cache::write($this->cache_file_prefix.'dashboard_search_'.$this->user_id, $adv_search);
					
					echo "true";
					exit;
		} 
		
		if($task == "delete"){
			Cache::delete($this->cache_file_prefix.'dashboard_search_'.$this->user_id);
			echo 'true';
			exit;
		}
		
		Cache::set(array('duration' => '+10 years'));					
		$saved_search = Cache::read($this->cache_file_prefix.'dashboard_search_'.$this->user_id);
		$status_ids = array();
		$room_ids = array();
		$type_ids = array();
		$location_ids = array();
		$provider_ids = array();
		
		$location_id = array();
		$provider_id = array();
		$room_id=array();
		$status_id=array();
		$type_id = array();
		
		if( !empty( $saved_search ) ){
			$status_ids = ($saved_search['status_id']) ? explode(',',$saved_search['status_id']) : array();
			$room_ids = ($saved_search['room_id']) ?  explode(',',$saved_search['room_id']) : array();
			$type_ids = ($saved_search['type_enc']) ? explode(',',$saved_search['type_enc']) : array();
			$location_ids = ($saved_search['location_enc']) ? explode(',',$saved_search['location_enc']) : array();
			$provider_ids = ($saved_search['provider_enc']) ? explode(',',$saved_search['provider_enc']) : array();
			
			
			$location_id = $location_ids;
			$provider_id = $provider_ids;
			$room_id = $room_ids;
			$status_id = $status_ids;
			$type_id = $type_ids;			
			
			$this->set('provider_ids',$provider_ids);
			$this->set('location_ids',$location_ids);
			$this->set('status_ids',$status_ids);
			$this->set('room_ids',$room_ids);
			$this->set('type_ids',$type_ids);		
			$this->set('custom_field' , 'on');	
		} else {
			$this->set('provider_ids',$provider_ids);
			$this->set('location_ids',$location_ids);
			$this->set('status_ids',$status_ids);
			$this->set('room_ids',$room_ids);
			$this->set('type_ids',$type_ids);	
		}
		
		// Read any existing info on this user, if any
		$info = Cache::read('user_' . $this->user_id);

		if ($info) {
			// If the session id from the user info
			// is different from the current session id
			// this means the user is already logged in
			// another computer
			if (count($info['sid']) > 1 ) {
	//			$this->set('already_logged_in', $info);
			}                                                
		}

		$this->loadModel("EncounterMaster");
		if($this->getAccessType("dashboard", "index") == 'NA' && $this->getAccessType("dashboard", "patient_portal") != 'NA')
		{
			$this->redirect("patient_portal");
		}
		
		if($this->getAccessType("dashboard", "index") == 'NA' && $this->getAccessType("dashboard", "non_clinical_index") != 'NA')
		{
			//disabled
			//$this->redirect("non_clinical_index");
			$this->set('non_clinical_index',1);
		}
		
		if($this->Session->check('dashboard_location') == false)
		{
			$this->Session->write('dashboard_location', 0);
		}
		//to get the providers
		$this->loadModel('UserGroup');

            $items_providers = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,false)), 'order' => array('UserAccount.firstname' => 'ASC', 'UserAccount.lastname' => 'ASC')));

			$data_providers = array();
			//$data_providers[0] = 'All Providers';
			foreach($items_providers as $items_provider)
			{
				$data_providers[$items_provider['UserAccount']['user_id']] = substr($items_provider['UserAccount']['firstname'], 0, 1) . '. ' . $items_provider['UserAccount']['lastname'];
			}
			$this->set('data_providers',$data_providers);
		// check for provider filter
		$role_id_status = 0;
		
		
		$this->set('sys_admin',0);

		if(!empty($_POST)){
			$provider_id = $_POST['provider'];
			$location_id = $_POST['location'];
			$room_id = $_POST['room'];
			$status_id = $_POST['status'];
			$type_id = $_POST['type'];	
		}		
		
		if($tutor_mode_value['UserAccount']['role_id']==EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $tutor_mode_value['UserAccount']['role_id']==EMR_Roles::PRACTICE_ADMIN_ROLE_ID ){
			$this->set('sys_admin',1);
			
			if(!$provider_id) {
				$provider_id = 	array_keys($data_providers);
			}
				
		} else if (!$provider_id && empty( $saved_search )) {
			$provider_id = $this->user_id;
		} 

			
			
		if (!$provider_id) {
			$provider_id = array();
		}
			
		$provider_ids = $provider_id;
		
		$this->loadModel('UserGroup');
		
		$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";
		$modeTo = (isset($this->params['named']['modeTo'])) ? $this->params['named']['modeTo'] : "";
		$showdate = (isset($this->params['named']['showdate'])) ? $this->params['named']['showdate'] : "";
		$date = $this->Session->read('DashboardDate');
		$role_ids = $this->UserGroup->getRoles(EMR_Groups::GROUP_NON_PROVIDERS);

		if ($view == "")
		{
			$this->redirect(array('action' => 'index', 'view' => 'today'));
		}

		if ($view == "previous_day")
		{
			Cache::delete($this->cache_file_prefix.'dash'.$this->user_id);
			if ($date)
			{
				$date = explode("-", $date);
				$date = __date("Y-m-d", mktime(0, 0, 0, $date[1], $date[2] - 1, $date[0]));
			}
			else
			{
				$date = __date("Y-m-d", mktime(0, 0, 0, __date("m"), __date("d") - 1, __date("Y")));
			}

		}
		else if ($view == "next_day")
		{
			Cache::delete($this->cache_file_prefix.'dash'.$this->user_id);
			if ($date)
			{
				$date = explode("-", $date);
				$date = __date("Y-m-d", mktime(0, 0, 0, $date[1], $date[2] + 1, $date[0]));
			}
			else
			{
				$date = __date("Y-m-d", mktime(0, 0, 0, __date("m"), __date("d") + 1, __date("Y")));
			}
		}
		else if($view == "same_day")
		{
			
		}
		else
		{
			if($showdate == 'true')
			{
								Cache::delete($this->cache_file_prefix.'dash'.$this->user_id);
								if(!empty($_POST)){
									if (isset($_POST['setdate'])) {
										$date = __date("Y-m-d", strtotime($_POST['setdate']));
										$this->set("setdate", $_POST['setdate']);
									} 
                                }
			}
			else
			{
				$date = __date('Y-m-d');
			}
		}
		
		if(isset($this->params['named']['page']))
		{			
			$page['page'] = $this->params['named']['page'];
			Cache::set(array('duration' => '+12 hours'));
			Cache::write($this->cache_file_prefix.'dash'.$this->user_id, $page); 				
		}
		
		Cache::set(array('duration' => '+12 hours'));					
		$page = Cache::read($this->cache_file_prefix.'dash'.$this->user_id);
		
		if(empty($page))
		{
			$page['page'] = 1;			
		}
		
        // Force view and showdate params so they get included in pagination links
        $this->params['named']['view'] = $view = 'd';
        $this->params['named']['showdate'] = 'true';
		$this->Session->write('DashboardDate', $date);
		$date = (isset($date)) ? __date("Y-m-d", strtotime($date)) : date('Y-m-d');
		$this->set('date',$date);
		$user = $this->Session->read('UserAccount');
		$user_id = $user['user_id'];
		$role_id = $user['role_id'];
    	$this->ScheduleCalendar->recursive = 0;
    	if(isset($_POST['frm_submit']))
    	{
    		$this->Session->write('showall', isset($_POST['show_all']));
    	}
    	else if($this->Session->read('showall')){
    		$this->Session->write('showall', true);
    	}
		else if($role_id == EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $role_id == EMR_Roles::PRACTICE_ADMIN_ROLE_ID){//else if(in_array($role_id, $role_ids)){
			$this->Session->write('showall', true);
		}
    	else{
    		$this->Session->write('showall', false);
    	}
			
		if(!$this->Session->read('showall'))
		{
			$conditions = array();
			$conditions['ScheduleCalendar.deleted'] = 0; //don't show deleted records
			if($provider_id != 0){
				$conditions['ScheduleCalendar.provider_id'] = $user_id;
			}
			$conditions['ScheduleCalendar.date'] = $date;
			$conditions['ScheduleCalendar.approved !='] = 'no';
		
			if(!empty($location_id))
			{
				$conditions['ScheduleCalendar.location'] = $location_id;
			}
			// added in filter array
			
			if(count($provider_id) != 0)
			{
				$conditions['ScheduleCalendar.provider_id'] = $provider_id;
			}
			if(!empty($room_id)){
				
					$conditions['ScheduleCalendar.room'] = $room_id;
				
			}
			if(!empty($status_id)){
				
					$conditions['ScheduleCalendar.status'] = $status_id;
				
			}
			if(!empty($type_id)){
				$conditions['ScheduleCalendar.visit_type'] = $type_id;
			}
			
			$this->paginate['ScheduleCalendar'] =
				array(
					'limit' => 10
					, 'conditions' => $conditions
				);
			$this->set('providers_selected',$provider_id);
			
		}
		else
		{

			$conditions = array();
                        $conditions['ScheduleCalendar.deleted'] = 0; //don't show deleted records
			$conditions['ScheduleCalendar.date'] = $date;
			$conditions['ScheduleCalendar.approved !='] = 'no';
			if(!empty($location_id))
			{
				$conditions['ScheduleCalendar.location'] = $location_id;
			}
			// added provider filter array
			/*
			$filter_role = $this->UserAccount->getUserRole($provider_id);
			
			if($filter_role == EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $filter_role == EMR_Roles::PRACTICE_ADMIN_ROLE_ID)
			{
				$provider_id = 0;
			}
			*/
			if(count($provider_id) != 0){
				$conditions['ScheduleCalendar.provider_id'] = $provider_id;
			}
			if(!empty($room_id)){
				$conditions['ScheduleCalendar.room'] = $room_id;
			}
			if(!empty($status_id)){
				$conditions['ScheduleCalendar.status'] = $status_id;
			}
			if(!empty($type_id)){
				$conditions['ScheduleCalendar.visit_type'] = $type_id;
			}
			
			
			$this->ScheduleCalendar->virtualFields['patient_sort_name'] = "CONCAT(CONVERT(DES_DECRYPT(PatientDemographic.last_name) USING latin1),' ',CONVERT(SUBSTRING(DES_DECRYPT(PatientDemographic.first_name), 1, 1) USING latin1))";
			$this->ScheduleCalendar->virtualFields['provider_sort_name'] = "CONCAT(CONVERT(DES_DECRYPT(UserAccount.lastname) USING latin1),' ',CONVERT(SUBSTRING(DES_DECRYPT(UserAccount.firstname), 1, 1) USING latin1))";
			$this->ScheduleCalendar->virtualFields['schedule_room'] =  "CASE ScheduleRoom.room WHEN '' THEN 'Click to edit' ELSE ScheduleRoom.room END";
			$this->ScheduleCalendar->virtualFields['schedule_status'] =  "CASE WHEN ScheduleStatus.status <> '' THEN ScheduleStatus.status ELSE 'Click to edit' END";

			$this->paginate['ScheduleCalendar'] =
			array(
				'limit' => 10,
				'page' => $page['page']
				, 'conditions' => $conditions
			);
			$this->set('providers_selected',$provider_id);
		
		}
                
                //see if patient did online checkin through patient portal?
        	$calendarIds = Set::extract('n/ScheduleCalendar/calendar_id', $this->paginate('ScheduleCalendar'));   
        	if($calendarIds)
        	{
        	 	$this->loadModel('PatientCheckinNotes');    	
			$this->PatientCheckinNotes->recursive=-1;
                	$checkin_items = $this->PatientCheckinNotes->find('all', array('conditions' => array('PatientCheckinNotes.calendar_id' => $calendarIds) ));
                	$this->set('checkin_items', $checkin_items);
                }
                                
        	$this->set('selectedDate', $date);

		if($modeTo == 'printable')
		{
			$this->set('schedulecalendar', $this->ScheduleCalendar->find('all', array('conditions' => $conditions)));
			$this->layout = 'empty';
			echo $this->render("/schedule/printable");
			exit;
		}
		else
		{
			$this->set('schedulecalendar',$this->paginate('ScheduleCalendar'));
		}

		$this->set('show_all', ($this->Session->read('showall'))? "checked":"");
		
		$encounter_access = (int)$this->validateAccess("encounters", "index");
		$patient_access = (int)$this->validateAccess("patients", "index");
		
		$this->set("encounter_access", $encounter_access);
		$this->set("patient_access", $patient_access);
		
		//to load all the locations information
		$this->loadModel("PracticeLocation");
		$items = $this->PracticeLocation->find('all');

		$data = array();
		
		foreach($items as $item)
		{
			$data[$item['PracticeLocation']['location_id']] = $item['PracticeLocation']['location_name'];
		}
		$this->set('data',$data);
		$this->set("location_name", $this->sanitizeHTML($this->PracticeLocation->getLocation($location_id)));
		$this->set("total_location", count($this->sanitizeHTML($this->PracticeLocation->find('all'))));
		
		// assigned values to variables to use in templates
		if($provider_id != 0){
			$this->set("provider_name", $this->sanitizeHTML($this->UserAccount->getUserShortRealName($provider_id)));
		}
		else{
			$this->set("provider_name", "All Providers");
		}

		$this->set("avail_provider", $this->UserAccount->find('count', array('conditions' => array('UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,false)))));
		$this->set('schedule_rooms', $this->ScheduleRoom->find('all'));
		$this->set('schedule_status', $this->ScheduleStatus->find('all', null));
		$this->set('schedule_types', $this->ScheduleType->find('all', null));
		
    }

	public function messages()
    {
		$this->layout = "iframe";
		$user = $this->Session->read('UserAccount');
		
		$newMessageCount = $this->MessagingMessage->find('count', array(
                    'conditions' => array(
                            "AND" => array(
                                'MessagingMessage.recipient_id' => $user['user_id'], 
                                'MessagingMessage.status' => array('New'),
																'MessagingMessage.sender_folder' => null,
																'MessagingMessage.inbox' => 1,
                            )
                    )
		));
		
		$MessagingMessages = $this->MessagingMessage->find('all', array(
                    'conditions' => array(
                            "AND" => array(
                                'MessagingMessage.recipient_id' => $user['user_id'], 
                                'MessagingMessage.status' => array('New'),
																'MessagingMessage.sender_folder' => null,
																'MessagingMessage.inbox' => 1,
                            )
                    ),
                    'order' => array(
                        'MessagingMessage.created_timestamp' => 'DESC',
                    ),
                    'limit' => 25
                ));
		$this->set(compact('newMessageCount'));
		$this->set('MessagingMessages', $this->sanitizeHTML($MessagingMessages));
	}
	public function orders()
    {
		$this->layout = "iframe";
		$this->loadModel("Order");
		$options['page'] = 1;
		$options['limit'] = 25;
		$options['order'] = array('date_performed' => 'desc', 'modified_timestamp' => 'desc');
		$orders = $this->Order->find('all', $options);		
		$this->set('orders', $this->sanitizeHTML($orders));
	}
    
    public function rx_refills()
    {
        $this->layout = "iframe";
		$practice_settings = $this->Session->read("PracticeSetting");
		$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];
		
		if($rx_setup == 'Electronic_Dosespot')
		{
		    $this->loadModel("DosespotRefillRequest");		
		    $refills = $this->DosespotRefillRequest->getRefillInformation();
			
		}
		else
		{
		    $this->loadModel('PatientMedicationRefill');
		    $refills = $this->PatientMedicationRefill->getRefillInformation();
		}
		$this->set('refills', $this->sanitizeHTML($refills));
    }
	
	public function new_lab_results()
	{
		$this->layout = "iframe";
		//$user = $this->Session->read('UserAccount');
		$user = $this->getAccessType("dashboard", "index");
		$this->loadModel('EmdeonLabResult');
		
		$this->EmdeonLabResult->quickDashboard = true;
		//if a provider, only show his/her results on dashboard, go back 30 days to not overload text on dashboard
		 $user = $this->Session->read('UserAccount'); 
		if($user['role_id'] == EMR_Roles::PHYSICIAN_ROLE_ID
		  || $user['role_id'] == EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID
		  || $user['role_id'] == EMR_Roles::NURSE_PRACTITIONER_ROLE_ID
		  )
		{
		  $electronic_lab_results = $this->EmdeonLabResult->getAlertPaginate($this, $user,"31");
		}
		else
		{ 
		  $electronic_lab_results = $this->EmdeonLabResult->getAlertPaginate($this);
		}
		  $this->set('electronic_lab_results', $this->sanitizeHTML($electronic_lab_results));		
	}
    	
	public function load_dropdown_data()
	{
		$type = (isset($this->params['named']['type'])) ? $this->params['named']['type'] : "";

		if($type == "type")
		{
			$items = $this->ScheduleType->find('all');

			$data = array();
			$data[0] = 'Select...';
			foreach($items as $item)
			{
				$data[$item['ScheduleType']['appointment_type_id']] = $item['ScheduleType']['type'];
			}

			echo json_encode($data);
		}

		if($type == "provider")
		{
			$this->loadModel('UserGroup');

            $items = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,false)), 'order' => array('UserAccount.firstname' => 'ASC', 'UserAccount.lastname' => 'ASC')));

			$data = array();
			$data[0] = 'Select...';
			foreach($items as $item)
			{
				$data[$item['UserAccount']['user_id']] = substr($item['UserAccount']['firstname'], 0, 1) . '. ' . $item['UserAccount']['lastname'];
			}

			echo json_encode($data);
		}
		// for providers filter on dashboard
		if($type == "provider_filter")
		{
			$this->loadModel('UserGroup');

            $items = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,false)), 'order' => array('UserAccount.firstname' => 'ASC', 'UserAccount.lastname' => 'ASC')));

			$data = array();
			$data[0] = 'All Providers';
			foreach($items as $item)
			{
				$data[$item['UserAccount']['user_id']] = substr($item['UserAccount']['firstname'], 0, 1) . '. ' . $item['UserAccount']['lastname'];
			}

			echo json_encode($data);
		}

		if($type == "room")
		{
			$items = $this->ScheduleRoom->find('all');

			$data = array();
			$data[0] = 'Select...';
			foreach($items as $item)
			{
				$data[$item['ScheduleRoom']['room_id']] = $item['ScheduleRoom']['room'];
			}

			echo json_encode($data);
		}

		if($type == "status")
		{
			$items = $this->ScheduleStatus->find('all');

			$data = array();
			$data[0] = 'Select...';
			foreach($items as $item)
			{
				$data[$item['ScheduleStatus']['status_id']] = $item['ScheduleStatus']['status'];
			}

			echo json_encode($data);
		}
		
		if($type == "location")
		{
			$this->loadModel("PracticeLocation");
			$items = $this->PracticeLocation->find('all');

			$data = array();
			
			$data[0] = 'All Locations';

			foreach($items as $item)
			{
				$data[$item['PracticeLocation']['location_id']] = $item['PracticeLocation']['location_name'];
			}

			echo json_encode($data);
		}

		exit;
	}
	
	public function status_listener()
	{
		$calendar_id = explode("|", $this->data['calendar_id']);
		$results = array();
		for($i = 0; $i < count($calendar_id); $i++)
		{
			$results[$i]['calendar_id'] = $calendar_id[$i];
			$results[$i]['status_text'] = $this->ScheduleCalendar->getStatus($calendar_id[$i]);
		}
		echo json_encode($results);
		exit;
	}

	public function update_single_field()
	{
		$from_encounter = (isset($this->params['named']['from_encounter'])) ? $this->params['named']['from_encounter'] : "";
		
		if($this->data['field'] == 'reason' && !empty($this->data['submitted']['value']))
		{
			$data['ScheduleCalendar']['calendar_id'] = $this->data['itemid'];
			$data['ScheduleCalendar']['reason_for_visit'] = $this->data['submitted']['value'];
			$this->ScheduleCalendar->save($data, false, array('reason_for_visit'));

			echo $this->data['submitted']['value'];
		}
		
		if($this->data['field'] == 'type' && !empty($this->data['submitted']['value']))
		{
			$item = $this->ScheduleType->find('first', array(
					'conditions' => array('ScheduleType.appointment_type_id' => $this->data['submitted']['value'])
				)
			);

			$data['ScheduleCalendar']['calendar_id'] = $this->data['itemid'];
			$data['ScheduleCalendar']['visit_type'] = $this->data['submitted']['value'];
			$this->ScheduleCalendar->save($data, false, array('visit_type'));

			echo $item['ScheduleType']['type'];
		}
		
		if($this->data['field'] == 'provider' && !empty($this->data['submitted']['value']))
		{
			$item = $this->UserAccount->find('first', array(
					'conditions' => array('UserAccount.user_id' => $this->data['submitted']['value'])
				)
			);

			$data['ScheduleCalendar']['calendar_id'] = $this->data['itemid'];
			$data['ScheduleCalendar']['provider_id'] = $this->data['submitted']['value'];
			$this->ScheduleCalendar->save($data, false, array('provider_id'));

			echo substr($item['UserAccount']['firstname'], 0, 1) . '. ' . $item['UserAccount']['lastname'];
		}
		
		if($this->data['field'] == 'room' && !empty($this->data['submitted']['value']))
		{
			$item = $this->ScheduleRoom->find('first', array(
					'conditions' => array('ScheduleRoom.room_id' => $this->data['submitted']['value'])
				)
			);

			$data['ScheduleCalendar']['calendar_id'] = $this->data['itemid'];
			$data['ScheduleCalendar']['room'] = $this->data['submitted']['value'];
			$this->ScheduleCalendar->save($data, false, array('room'));

			echo $item['ScheduleRoom']['room'];
		}

		if($this->data['field'] == 'status' && !empty($this->data['submitted']['value']))
		{			
			$item = $this->ScheduleStatus->find('first', array(
					'conditions' => array('ScheduleStatus.status_id' => $this->data['submitted']['value'])
				)
			);

			$data['ScheduleCalendar']['calendar_id'] = $this->data['itemid'];
			$data['ScheduleCalendar']['status'] = $this->data['submitted']['value'];
			$this->ScheduleCalendar->save($data, false, array('status'));

			echo $item['ScheduleStatus']['status'];
		}

		exit;
	}
        
        function check_login () {
            Configure::write('debug', 0);
            $this->layout = 'empty';
        }

    public function plan_labs()
	{
		$this->loadModel("EncounterPlanLab");
		$this->loadModel("DirectoryLabFacility");
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		$plan_labs_id = (isset($this->params['named']['plan_labs_id'])) ? $this->params['named']['plan_labs_id'] : "";
		
		$lab_facility_items = $this->DirectoryLabFacility->find('all');
        $this->set('LabFacilityCount', count($lab_facility_items));
		
		$encounter_plan_labs = $this->paginate('EncounterPlanLab', array('EncounterMaster.patient_id' => $patient_id));
		$this->set("encounter_plan_labs", $this->sanitizeHTML($encounter_plan_labs));
	}
	
	public function printable_forms() {
		$user = $this->Session->read('UserAccount');
		$user_id = $user['user_id'];
		$role_id = $user['role_id'];
		$patient_id = $user['patient_id'];
		$this->set(compact('patient_id'));
		
		$dashboard_access = 'clinical';

		App::import('Helper', 'QuickAcl');
		$quickacl = new QuickAclHelper();

		if($quickacl->getAccessType("dashboard", "patient_portal", '', array('role_id' => $role_id, 'emergency' => 0)) != 'NA')
		{
			$dashboard_access = 'patient';
		}

		if($quickacl->getAccessType("dashboard", "non_clinical", '', array('role_id' => $role_id, 'emergency' => 0)) != 'NA')
		{
			$dashboard_access = 'non_clinical';
		}				
		
		$this->set(compact('dashboard_access'));
		
		$this->set('hasOnlineForms', $this->FormTemplate->find('count', array(
			'conditions' => array(
				'FormTemplate.template_version' => 0,
				'FormTemplate.access_'.$dashboard_access => '1',
			),
		)));
		
		$this->loadModel("AdministrationForm");
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

		switch ($task) {
			case "download_file": {
				$form_id = (isset($this->params['named']['form_id'])) ? $this->params['named']['form_id'] : "";
				$items = $this->AdministrationForm->find(
					'first', array(
					'conditions' => array('AdministrationForm.form_id' => $form_id)
					)
					);

				$current_item = $items;

				$file = $current_item['AdministrationForm']['attachment'];
				$targetPath = $this->paths['help'];
				$targetFile = str_replace('//', '/', $targetPath) . $file;
				header('Content-Type: application/octet-stream; name="' . $file . '"');
				header('Content-Disposition: attachment; filename="' . $file . '"');
				header('Accept-Ranges: bytes');
				header('Pragma: no-cache');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Content-transfer-encoding: binary');
				header('Content-length: ' . @filesize($targetFile));
				@readfile($targetFile);

				exit;
			} break;
			default: {
				$this->set('AdministrationForm', $this->sanitizeHTML($this->paginate('AdministrationForm', array(
					'AdministrationForm.access_'.$dashboard_access => '1',
				))));
				$this->set('role_id', $role_id = $user['role_id']);
			} break;
		}			
		
	}

	public function online_forms() {
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		$roleId = $user['role_id'];
		$patient_id = $user['patient_id'];		
		$this->set(compact('patient_id'));
	
		$dashboard_access = 'clinical';

		App::import('Helper', 'QuickAcl');
		$quickacl = new QuickAclHelper();

		if($quickacl->getAccessType("dashboard", "patient_portal", '', array('role_id' => $roleId, 'emergency' => 0)) != 'NA')
		{
			$dashboard_access = 'patient';
		}

		if($quickacl->getAccessType("dashboard", "non_clinical", '', array('role_id' => $roleId, 'emergency' => 0)) != 'NA')
		{
			$dashboard_access = 'non_clinical';
		}		
		$this->set(compact('dashboard_access'));
		
		$this->loadModel('AdministrationForm');
		
		$this->set('hasPrintableForms', $this->AdministrationForm->find('count', array(
			'conditions' => array(
				'AdministrationForm.access_'.$dashboard_access => '1',
			)
		)));
		
		if (access::isAjaxRequest()) {
			$this->layout = 'empty';
		}
		
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "index";
		$this->loadModel('FormTemplate');
		$this->loadModel('FormData');

		switch($task) {
			
		case 'get_pdf': {
				$templateId = (isset($this->params['named']['template_id'])) ? $this->params['named']['template_id'] : "";
				
				$template = $this->FormTemplate->find('first', array(
					'conditions' => array(
						'FormTemplate.template_id' => $templateId,
					)
				));

				if (!$template) {
						$this->Session->setFlash(__('Template not found', true));
						$this->redirect(array('controller' => 'administration', 'action' => 'online_forms'));
						exit();
				}
			
				$this->set(compact('template', 'patient_id'));
				$this->render('online_forms', 'empty', 'online_forms-get_pdf');
		}	break;			
			
			case 'fill_up': {
				$id = (isset($this->params['named']['template_id'])) ? $this->params['named']['template_id'] : 0;

				$template = $this->FormTemplate->find('first', array(
					'conditions' => array(
						'FormTemplate.template_id' => $id,
						'FormTemplate.access_'.$dashboard_access => '1',
					)
				));

				if (!$template) {
						$this->Session->setFlash(__('Template not found', true));
						$this->redirect(array('controller' => 'dashboard', 'action' => 'forms', 'patient_id' => $patient_id));
						exit();
				}		

				if (isset($this->params['form']['submit'])) {
					$patient_checkin_id = isset($this->params['form']['patient_checkin_id']) ? trim($this->params['form']['patient_checkin_id']): '';
					App::import('Lib', 'FormBuilder');
					$formBuilder = new FormBuilder();

					$jsonData = $formBuilder->extractData($template['FormTemplate']['template_content'], $this->params['form']);

					$formBuilder->triggerSave($template['FormTemplate']['template_content'], $jsonData);

					// Save submitted form data
					$formData = array(
						'patient_id' => $patient_id,
						'form_template_id' => $template['FormTemplate']['template_id'],
						'form_data' => $jsonData,
						'form_completed_user_id' => $userId,
					);
					$this->FormData->create();
					$this->FormData->save($formData);

					$this->Session->setFlash(__('Form submitted', true));
					$_r=array('controller' => 'dashboard', 'action' => 'online_forms', 'patient_id' => $patient_id);
					if($patient_checkin_id)
					  $_r['patient_checkin_id']=$patient_checkin_id;
					  
					$this->redirect($_r);
					exit();			
				}		

				$this->set(compact('template', 'patient_id'));
				
				
			} break;
		
			case 'view_data': {
				$dataId = (isset($this->params['named']['data_id'])) ? $this->params['named']['data_id'] : 0;
				
				$formData = $this->FormData->find('first', array(
					'conditions' => array(
						'FormData.form_data_id' => $dataId,
						'FormData.patient_id' => $patient_id,
					),
				));

				if (!$formData) {
						$this->Session->setFlash(__('Data not found', true));
						$this->redirect(array('controller' => 'forms', 'action' => 'index'));
						exit();
				}

				$this->set(compact('formData'));				
			} break;
		
			default: {
				$task = 'index';
				$this->paginate['FormTemplate'] = array(
					'limit' => 10,
					'order' => array(
						'FormTemplate.template_name' => 'asc',
					)			
				);

				$templates = $this->paginate('FormTemplate', array(
					'FormTemplate.template_version' => 0,
					'FormTemplate.access_'.$dashboard_access => '1',
				));

				// Find Form data for this patient
				$this->paginate['FormData'] = array(
					'limit' => 10,
					'order' => array(
						'FormData.created' => 'DESC'
					),
				);

				$formData = $this->paginate('FormData', array(
						'FormData.patient_id' => $patient_id,
				));

				$this->set(compact('templates', 'formData'));									
			} break;
		}
		
		$this->set(compact('task'));		
		

	}
	
	public function forms() {
	
	}
	
	public function patient_checkin() {

        	$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        	$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        	$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
        	$comment = (isset($this->params['form']['comment'])) ? $this->params['form']['comment'] : "";
        		
	 	$this->loadModel('PatientCheckinNotes');
	 	
                    $items = $this->PatientCheckinNotes->find(
                            'first',
                            array(
                                'conditions' => array('PatientCheckinNotes.patient_checkin_id' => $patient_checkin_id)
                            )
                    );	 	
	 	
                if(!empty($items))
                {
		   $this->data['PatientCheckinNotes']['patient_checkin_id'] = $items['PatientCheckinNotes']['patient_checkin_id'];
                    $this->data['PatientCheckinNotes'][$task] = $comment; 
                    $this->PatientCheckinNotes->save($this->data);  
               }
							 
		die('Ok');
							 
	}        
}
?>
