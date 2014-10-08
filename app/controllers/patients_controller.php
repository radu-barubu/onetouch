<?php

class PatientsController extends AppController
{
    public $name = 'Patients';
    public $helpers = array('Html', 'Form', 'Javascript', 'Ajax', 'QuickAcl');
    public $components = array('Image');

    public $uses = null;

    public function webcam()
    {
        $this->layout = 'iframe';
    }

    public function patient_preferences()
    {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		$this->loadModel("UserAccount");
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);
        
        switch($task)
        {
            case 'autocomplete':
            {
                $this->loadModel("UserRole");
                $names = $this->UserRole->getRoleNames(EMR_Roles::PHYSICIAN_ROLE_ID);

                $data = array();
                foreach($names as $id => $value)
                {
                    $data[] = $value . '|' . $id;
                }

                echo implode("\n", $data);
                exit;
            } break;
            case 'edit':
            {
                $this->loadModel("ImmtrackCountry");
                $this->loadModel("StateCode");
                $this->loadModel("PatientPreference");
                $this->loadModel("SmsCarrier");

                if (!empty($this->data))
                {
                    $this->PatientPreference->save($this->data);
                    $ret = array();
                    echo json_encode($ret);

                    $this->PatientPreference->saveAudit('Update');

                    exit;
                }
                else
                {
        
		    $ifsetPCP=$this->PatientPreference->getPrimaryCarePhysician($patient_id);
		    if(empty($ifsetPCP))  // if no PCP is already set
		    {
                      // find number of doctors. if only 1, 'Primary Care Physician' box should be pre-filled.
                      $this->loadModel("UserRole");
                      $names = $this->UserRole->getRoleNames(EMR_Roles::PHYSICIAN_ROLE_ID);
                      $data = array();
                	foreach($names as $value)
                	{
                    	    $data[] = $value ;
                	}
                    
		      if(sizeof($data)==1) {
		        $this->set("singleProvider",$data[0]);
		      }   
		    }   
                    $this->set("patient_preferences", $this->sanitizeHTML($this->PatientPreference->getPreferences($patient_id)));
                    
					/*$referred_by_name = $this->PatientPreference->getPreferences($patient_id);
					$referred_by = $referred_by_name['referred_by'];
					$value = $this->UserAccount->getCurrentUser($referred_by);
					$referred_by_val = $value['firstname'].' '.$value['lastname'];
					$this->set('referred_by_val', $referred_by_val); 
					
					$recommended_by_name = $this->PatientPreference->getPreferences($patient_id);
					$recommended_by = $recommended_by_name['recommended_by'];
					$value = $this->UserAccount->getCurrentUser($recommended_by);
					$recommended_by_val = $value['firstname'].' '.$value['lastname'];
					$this->set('recommended_by_val', $recommended_by_val);*/
					           
                    $countries = $this->ImmtrackCountry->getList();
                    $states = $this->StateCode->getList();
                    $smscarrier = $this->SmsCarrier->getList();
                    $this->set("states", $states);
                    $this->set("SmsCarrier", $smscarrier);
                    $this->set("ImmtrackCountries", $countries);

                    $this->PatientPreference->saveAudit('View');
                }
            }
        }
    }

    public function webcam_save()
    {
        $save_image_path = $this->paths['temp'];

        if(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
        {
            $snaptime = md5(mktime());
            $jpg = $GLOBALS["HTTP_RAW_POST_DATA"];
            $file_real_name = $snaptime . "_webcam.jpg";
            $filename = $save_image_path . $file_real_name;
            file_put_contents($filename, $jpg);

            $this->Image->resize($filename, $filename, 640, 480, 90);

            $converted_file_real_name = FileHash::getHash($filename) . "_webcam.jpg";
            $converted_filename = $save_image_path . $converted_file_real_name;

            rename($filename, $converted_filename);

            echo $this->url_abs_paths['temp'] . $converted_file_real_name;
        }
        else
        {
            echo "Encoded JPEG information not received.";
        }

        exit;
    }

    public function index()
    {
        $from_encounter = (isset($this->params['named']['from_encounter'])) ? $this->params['named']['from_encounter'] : "";
        $search_data =  (isset($this->params['named']['dat'])) ? $this->params['named']['dat'] : "";
        //exit;
        if($from_encounter=='yes')
        {
            $this->layout = "encounter_view";
        }
 
        $this->loadModel("ScheduleCalendar");
        $this->loadModel("PracticeLocation");
        $this->loadModel("EncounterMaster");
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $calendar_id = (isset($this->params['named']['calendar_id'])) ? $this->params['named']['calendar_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";

        if(strlen($calendar_id) > 0)
        {
            $user = $this->Session->read('UserAccount');
            $user_id = $user['user_id'];
            
            $patient_id = $this->ScheduleCalendar->getPatientID($calendar_id);
            $reason_for_visit = $this->ScheduleCalendar->getReason($calendar_id);
            
            $encounter_id = $this->EncounterMaster->getEncounter($calendar_id, $patient_id, $user_id);
            
            $this->loadModel("EncounterChiefComplaint");
            $this->EncounterChiefComplaint->addItem($reason_for_visit, $encounter_id, $this->user_id);
            
            //$this->redirect(array('action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id));
        }
        
		
        switch($task)
        {
			case "save_search_data":
			{
				
				if(!empty($this->data['first_name']))
				{
				$this->Session->write('search_firstname', $this->data['first_name']);
				}
				
				if(!empty($this->data['last_name']))
				{
				$this->Session->write('search_last_name', $this->data['last_name']);
				}

				if(!empty($this->data['ssn']))
				{
				$this->Session->write('search_ssn', $this->data['ssn']);
				}

				if(!empty($this->data['dob']))
				{
				$this->Session->write('search_dob', $this->data['dob']);
				}
				exit;
			}
			case "import_patient":
			{
				$ret = array();
				$ret['success'] = true;
				$ret['error_field'] = $this->data['error_field'];
				
				$ccr_reader = new CCR_Reader($this->paths[$this->data['folder']] . $this->data['filename']);
				
				if($ccr_reader->isValidDocument())
				{
					$import_result = $ccr_reader->importPatient($this->data['validate_mode'], $this->data['patient_id']);
					
					if($import_result['success'])
					{
						$ret['patient_id'] = $import_result['patient_id'];
					}
					else
					{
						$ret['success'] = false;
						$ret['reason'] = $import_result['reason'];
					}
				}
				else
				{
					$ccd_reader = new CCD_Reader($this->paths[$this->data['folder']] . $this->data['filename']);
					
					if($ccd_reader->isValidDocument())
					{
						$import_result = $ccd_reader->importPatient($this->data['validate_mode'], $this->data['patient_id']);
						
						if($import_result['success'])
						{
							$ret['patient_id'] = $import_result['patient_id'];
						}
						else
						{
							$ret['success'] = false;
							$ret['reason'] = $import_result['reason'];
						}
					}
					else
					{
						$ret['success'] = false;
						$ret['reason'] = "Invalid Document";
					}
				}
				
				echo json_encode($ret);
				exit;
			} break;
            case "import_ccr_ccd":
            {
                $this->layout = "empty";
				
				$ccr_reader = new CCR_Reader($this->paths[$this->data['folder']] . $this->data['filename']);
				
				if($ccr_reader->isValidDocument())
				{
					$file_data = $ccr_reader->ccr_file_contents;
					
					header ("Content-Type:text/xml");
					echo '<?xml-stylesheet type="text/xsl" href="'.Router::url(array('task' => 'get_ccr_xsl', 'enable_import' => $this->data['enable_import'], 'folder' => $this->data['folder'], 'validate_mode' => $this->data['validate_mode'], 'patient_id' => $this->data['patient_id'])).'"?>';
					echo $file_data;
				}
				else
				{
					$ccd_reader = new CCD_Reader($this->paths[$this->data['folder']] . $this->data['filename']);
					
					if($ccd_reader->isValidDocument())
					{
						$file_data = $ccd_reader->ccd_file_contents;
					
						header ("Content-Type:text/xml");
						echo '<?xml-stylesheet type="text/xsl" href="'.Router::url(array('task' => 'get_ccd_xsl', 'enable_import' => $this->data['enable_import'], 'folder' => $this->data['folder'], 'validate_mode' => $this->data['validate_mode'], 'patient_id' => $this->data['patient_id'])).'"?>';
						echo $file_data;
					}
					else
					{
						echo "Invalid Document";
					}
				}
				
                exit;
            } break;
            case "get_ccr_xsl":
            {
				$enable_import = (isset($this->params['named']['enable_import'])) ? $this->params['named']['enable_import'] : "";
				$folder = (isset($this->params['named']['folder'])) ? $this->params['named']['folder'] : "";
				$validate_mode = (isset($this->params['named']['validate_mode'])) ? $this->params['named']['validate_mode'] : "";
				$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
				$this->set(compact("enable_import", "folder", "validate_mode", "patient_id"));
				
                $this->layout = "empty";
                header ("Content-Type:text/xml");
                $this->render("ccr_xsl");
            } break;
			case "get_ccd_xsl":
            {
				$enable_import = (isset($this->params['named']['enable_import'])) ? $this->params['named']['enable_import'] : "";
				$folder = (isset($this->params['named']['folder'])) ? $this->params['named']['folder'] : "";
				$validate_mode = (isset($this->params['named']['validate_mode'])) ? $this->params['named']['validate_mode'] : "";
				$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
				$this->set(compact("enable_import", "folder", "validate_mode", "patient_id"));
				
				$this->set("enable_import", $enable_import);
				$this->set("folder", $folder);
				
                $this->layout = "empty";
                header ("Content-Type:text/xml");
                $this->render("ccd_xsl");
            } break;
            case "addnew":
            {
		$this->loadModel("FavoriteMacros");
                $favs=$this->FavoriteMacros->find('all', array('conditions' => array('FavoriteMacros.user_id'=> $this->user_id)));
                $this->set('FavoriteMacros', $favs);
            } break;
            case "edit":
            {
                $this->loadModel("PatientDemographic");
                $item = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
                $this->set("demographic_info", $item['PatientDemographic']);

		$this->loadModel("FavoriteMacros");
                $favs=$this->FavoriteMacros->find('all', array('conditions' => array('FavoriteMacros.user_id'=> $this->user_id)));
                $this->set('FavoriteMacros', $favs);

		$PracticeSetting = $this->Session->read("PracticeSetting");
		if($view == 'medical_information' && $PracticeSetting['PracticeSetting']['rx_setup']== 'Electronic_Dosespot' )
		{
		       //If the patient not exists in Dosespot, add the patient to Dosespot
			if(empty($item['PatientDemographic']['dosespot_patient_id']))
			{					
			   $this->PatientDemographic->updateDosespotPatient($patient_id);					
			}
		}

 
            } break;
            case "checkPatient":
            {
                $this->loadModel("PatientDemographic");
                $patients = $this->PatientDemographic->find(
                        'all',
                        array(
                            'fields' => array('patient_id', 'first_name', 'last_name'),
                            'conditions' => array('AND' => array('PatientDemographic.first_name' => $this->data['check']['first_name'], 'PatientDemographic.last_name' => $this->data['check']['last_name'], 'PatientDemographic.dob' => __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['check']['dob']))))),
                             'recursive' => -1
			    )
                );
                if (count($patients))
                {
                    $patient_array = array();
                    foreach ($patients as $patient):
                        array_push($patient_array, '<a href="'.Router::url(array('action' => 'index', 'task' => 'edit', 'patient_id' => $patient['PatientDemographic']['patient_id'])).'" target="_blank">'.$patient['PatientDemographic']['first_name'].' '.$patient['PatientDemographic']['last_name'].'</a>');
                    endforeach;
                    echo implode(", ", $patient_array);
                }
                exit;
            } break;
            default:
            {
                $this->redirect(array('action' => 'search_charts'));
            }
        }
		
		// for quick visit encounter 
				if($patient_id && strlen($calendar_id) == 0 )
			$this->quick_visit_encounter($patient_id);
		
        $general_information_access = $this->getAccessType("patients", "general_information");
        $medical_information_access = $this->getAccessType("patients", "medical_information");
        $attachments_access = $this->getAccessType("patients", "attachments");

        $this->set("general_information_access", $general_information_access);
        $this->set("medical_information_access", $medical_information_access);
        $this->set("attachments_access", $attachments_access);

        if($view == 'medical_information' && $medical_information_access == 'NA')
        {
            $this->redirect(array('controller' => 'administration', 'action' => 'no_access'));
        }
        else if($view == 'attachments' && $attachments_access == 'NA')
        {
            $this->redirect(array('controller' => 'administration', 'action' => 'no_access'));
        }
        else
        {
            if($general_information_access == 'NA')
            {
                $this->redirect(array('controller' => 'administration', 'action' => 'no_access'));
            }
        }
    }
	
	/*
	* params patient id
	* return quick visit encounter create button 
	*/
	public function quick_visit_encounter($patient_id='')
	{
		$this->loadModel('UserGroup');
        $isProvider = $this->UserGroup->isProvider($this);
		$this->Set('isProvider', $isProvider);
		if($isProvider===false)// if current user is not a provider no need to continue
			return;
	
		if(empty($patient_id)) 
		{
			$patient_id = $this->params['named']['patient_id'];
			if(empty($patient_id))
				exit;
			$this->layout = "empty";
			$this->loadModel("PatientDemographic");
			$item = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id),'recursive' => -1));
            $this->set("demographic_info", $item['PatientDemographic']);
		}	
		
		$this->loadModel('ScheduleCalendar');
        $locations = $this->PracticeLocation->find('list', array('order' => 'PracticeLocation.location_name','fields' => 'PracticeLocation.location_name')); // get list of practice locations
		$patient_last_schedule_location = $this->ScheduleCalendar->find('first', array('conditions' => array('patient_id' => $patient_id), 'fields' => array('location'), 'order' => array('date desc', 'starttime desc'), 'recursive' => -1));
        $this->Set(compact('locations', 'patient_last_schedule_location'));
	}

    public function zipcode()
    {
		$this->loadModel("Zipcode");
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$zipcode = (isset($this->data['zipcode'])) ? $this->data['zipcode'] : "";
		$this->Zipcode->execute($this, $task, $zipcode);
    }

    function demographics_counties($state)
    {
        $this->loadModel('CountyCodes');
        
        
        $counties = $this->CountyCodes->getCounties($state);
        
    
        
        exit(json_encode($counties));
    }
    
    
    public function add_patient()
    {
        $this->redirect(array('action' => 'index', 'task' => 'addnew'));
    }

    public function demographics()
    {
        $this->layout = "blank";
        $this->loadModel("PatientDemographic");
		$this->loadModel("PatientSocialHistory");
        $this->loadModel("StateCode");
        $this->loadModel("MaritalStatus");
        $this->loadModel("Race");
        $this->loadModel("Ethnicity");
        $this->loadModel("PreferredLanguage");
        $this->loadModel("PracticeLocation");
        $this->loadModel("ImmtrackCountry");
        $this->loadModel("ImmtrackCounty");
        $this->loadModel("ImmtrackVfc");
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];

        if( $this->Session->read('search_last_name') ){
			$search_data['last_name'] = $this->Session->read('search_last_name');
		$this->Session->delete('search_last_name');
		}
		if($this->Session->read('search_firstname')){
		$search_data['first_name'] = $this->Session->read('search_firstname');
		$this->Session->delete('search_firstname');
		}
		if($this->Session->read('search_ssn')){
		$search_data['ssn'] = $this->Session->read('search_ssn');
		$this->Session->delete('search_ssn');
		}
		if($this->Session->read('search_dob')){
		$search_data['dob'] = $this->Session->read('search_dob');
		$this->Session->delete('search_dob');
		}
		if(!empty($search_data))
		$this->set("search",$search_data);
		
        if($task == "addnew" || $task == "edit")
        {
            $this->set("StateCode", $this->sanitizeHTML($this->StateCode->find('all')));
            $this->set("MaritalStatus", $this->sanitizeHTML($this->MaritalStatus->find('all')));
            $this->set("Race", $this->sanitizeHTML($this->Race->find('all')));
            $this->set("Ethnicities", $this->sanitizeHTML($this->Ethnicity->find('all')));
            $this->set("PreferredLanguages", $this->sanitizeHTML($this->PreferredLanguage->find('all')));
            $this->set("PracticeLocations", $this->sanitizeHTML($this->PracticeLocation->find('all')));
            $this->set("ImmtrackCountries", $this->sanitizeHTML($this->ImmtrackCountry->find('all')));
            $this->set("ImmtrackCounties", $this->sanitizeHTML($this->ImmtrackCounty->find('all')));
            $this->set("ImmtrackVfcs", $this->sanitizeHTML($this->ImmtrackVfc->find('all')));

			$practice_location = $this->PracticeLocation->find('list', array(
				'order' => 'PracticeLocation.state',
				'fields' => 'PracticeLocation.state'
			));
			$practice_location = array_unique($practice_location);
			if (count($practice_location) == 1)
			{
				$state_codes = $this->StateCode->find('first', array('conditions' => array('StateCode.fullname' => current($practice_location))));
			}
			else
			{
				$state_codes['StateCode']['state'] = "";
			}
			$this->set("StateCodes", $this->sanitizeHTML($state_codes['StateCode']['state']));
        }
		
		$emdeon_xml_api = new Emdeon_XML_API();
		$isEmdeonOk = $emdeon_xml_api->isOK();
		$this->set('isEmdeonOk', $isEmdeonOk);
		
		$dosespot_xml_api = new Dosespot_XML_API();
		$isDosespotOk = $dosespot_xml_api->isOK();
		$this->set('isDosespotOk', $isDosespotOk);

        switch($task)
        {
            case 'approve':
                $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                
                $this->PatientDemographic->id = $patient_id;
                $this->PatientDemographic->saveField('status', 'Active');
                
                $this->Session->setFlash('Patient Approved and set to Active status');
                $this->redirect(array(
                    'controller' => 'patients',
                    'action' => 'index',
                    'task' => 'edit',
                    'patient_id' => $patient_id,
                ));
                exit();
                break;
			case "patient_user":
			{
				if (!isset($this->data['UserAccount']['user_id']))
				{
          
          $date_dob = data::formatDateToStandard($this->__global_date_format, $this->data['UserAccount']['dob']);
          $date_dob = __date('m/d/Y', strtotime($date_dob));
          
          if (!$date_dob) {
            $date_dob = __date('m/d/Y');
          }
          
					$date_ar = explode("/",$date_dob);
					$lastdigits_year = $date_ar[2];
					$username_code = $date_ar[0].$date_ar[1].$lastdigits_year[2].$lastdigits_year[3];
					$user_firstname = $this->data['UserAccount']['firstname'];
					$firstinitial = $user_firstname[0];
					$this->data['UserAccount']['username'] = $firstinitial.$this->data['UserAccount']['lastname'].$username_code;
					//$this->data['UserAccount']['username'] = $this->data['UserAccount']['firstname'].rand(10000, 99999);
					$this->data['UserAccount']['password'] = $this->data['UserAccount']['lastname'].rand(10000, 99999);
					$count = $this->UserAccount->find('count', array('conditions' => array('UserAccount.username' => $this->data['UserAccount']['username'])));
					while ($count > 0)
					{
						$this->data['UserAccount']['username'] = $this->data['UserAccount']['firstname'].rand(10000, 99999);
						$count = $this->UserAccount->find('count', array('conditions' => array('UserAccount.username' => $this->data['UserAccount']['username'])));
					}
					if ($this->data['UserAccount']['patient_id'])
					{
						$this->UserAccount->create();
						$this->UserAccount->save($this->data);
						$array_data[0] = $this->UserAccount->getLastInsertId();
					}
					else
					{
						$array_data[0] = 0;
					}
					$array_data[1] = $this->data['UserAccount']['username'];
					$array_data[2] = $this->data['UserAccount']['password'];
					echo json_encode($array_data);
				}
				else
				{
					$this->UserAccount->save($this->data);
				}
				exit();
			} break;
            case 'delete_photo':
                
                $patient_id = $this->params['form']['patient_id'];
                
                $patientData = $this->PatientDemographic->getPatient($patient_id);

								$this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;
								
                $original = UploadSettings::existing(
									$this->paths['patients'] . $patientData['patient_photo'], 
									$this->paths['patient_id'] . $patientData['patient_photo']
								);
                
                @unlink($original);
                
                $this->PatientDemographic->id = $patient_id;
                $this->PatientDemographic->saveField('patient_photo', '');
                
                exit();
                break;
            case 'delete_license':
                $patient_id = $this->params['form']['patient_id'];
                
                $patientData = $this->PatientDemographic->getPatient($patient_id);

								$this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;
								
                $original = UploadSettings::existing(
									$this->paths['patients'] . $patientData['driver_license'], 
									$this->paths['patient_id'] . $patientData['driver_license']
								);
                
                @unlink($original);
                
                $this->PatientDemographic->id = $patient_id;
                $this->PatientDemographic->saveField('driver_license', '');
                
                exit();
                break;
            
            case "save_photo":
            {
                $patientData = $this->PatientDemographic->getPatient($this->data['PatientDemographic']['patient_id']);
                
								$this->paths['patient_id'] = $this->paths['patients'] . $patientData['patient_id'] . DS;
								UploadSettings::createIfNotExists($this->paths['patient_id']);
								
                if($this->data['photo_type'] == 'photo')
                {
                    $source_file = $this->paths['temp'] . $this->data['PatientDemographic']['patient_photo'];
                    $destination_file = $this->paths['patient_id'] . $this->data['PatientDemographic']['patient_photo'];
                    $original = $this->paths['patient_id'] . $patientData['patient_photo'];
										
										$original = UploadSettings::existing(
											$this->paths['patients'] . $patientData['patient_photo'], 
											$this->paths['patient_id'] . $patientData['patient_photo']
										);										
										
                }
                else
                {
                    $source_file = $this->paths['temp'] . $this->data['PatientDemographic']['driver_license'];
                    $destination_file = $this->paths['patient_id'] . $this->data['PatientDemographic']['driver_license'];
										$original = UploadSettings::existing(
											$this->paths['patients'] . $patientData['driver_license'], 
											$this->paths['patient_id'] . $patientData['driver_license']
										);										
                }

                @unlink($original);
                
                @copy($source_file, $destination_file);
                @unlink($source_file); // remove temp file
                $this->PatientDemographic->save($this->data);
                $this->PatientDemographic->saveAudit('Update');
                echo 'saved';
                exit;
            } break;
            case "check_mrn":
            {
                $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

                if($this->PatientDemographic->checkMRN($this->data['PatientDemographic']['mrn'], $patient_id))
                {
                    echo "false";
                }
                else
                {
                    echo "true";
                }

                exit;
            } break;
            case "addnew":
            {
                if (!empty($this->data))
                {
                    if($this->PatientDemographic->checkMRN($this->data['PatientDemographic']['mrn']))
                    {
                        echo json_encode(array('mrn_error'=>'yes'));
                        exit;
                    } 
                    $this->PatientDemographic->create();

                    $this->data['PatientDemographic']['mrn'] = 0; //Dummy value assigned to MRN initially
                    $this->data['PatientDemographic']['dob'] = data::formatDateToStandard($this->__global_date_format, $this->data['PatientDemographic']['dob']);
                    $this->PatientDemographic->save($this->data);
                    $patient_id = $this->PatientDemographic->getLastInsertID();
					
					if($patient_id)
					{
							$this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;
							UploadSettings::createIfNotExists($this->paths['patient_id']);
							
							if(strlen($this->data['PatientDemographic']['patient_photo']) > 0)
							{
									$source_file = $this->paths['temp'] . $this->data['PatientDemographic']['patient_photo'];
									$destination_file = $this->paths['patient_id'] . $this->data['PatientDemographic']['patient_photo'];

									@rename($source_file, $destination_file);
							}

							if(strlen($this->data['PatientDemographic']['driver_license']) > 0)
							{
									$source_file = $this->paths['temp'] . $this->data['PatientDemographic']['driver_license'];
									$destination_file = $this->paths['patient_id'] . $this->data['PatientDemographic']['driver_license'];

									@rename($source_file, $destination_file);
							}						
						
						// Add PracticeSetting start mrn #
						$PracticeSetting = $this->Session->read("PracticeSetting");
						$this->data['PatientDemographic']['mrn'] = $patient_id + $PracticeSetting['PracticeSetting']['mrn_start'];
						$this->PatientDemographic->save($this->data);
					}
					
					//Patient User
					if ($this->data['UserAccount']['patient_user_username'] and $this->data['UserAccount']['patient_user_password'])
					{
						$this->data['UserAccount']['username'] = $this->data['UserAccount']['patient_user_username'];
						$this->data['UserAccount']['password'] = $this->data['UserAccount']['patient_user_password'];
						$this->data['UserAccount']['role_id'] = EMR_Roles::PATIENT_ROLE_ID;
						$this->data['UserAccount']['patient_id'] = $patient_id;
						$this->data['UserAccount']['firstname'] = $this->data['PatientDemographic']['first_name'];
						$this->data['UserAccount']['lastname'] = $this->data['PatientDemographic']['last_name'];
						$this->data['UserAccount']['email'] = $this->data['PatientDemographic']['email'];
						$this->UserAccount->create();
						$this->UserAccount->save($this->data);
					}

		    $this->PatientDemographic->updateEmdeonPatient($patient_id);
		    // add patient to dosespot, and get a dosespot ID
                    $this->PatientDemographic->updateDosespotPatient($patient_id);

                    $p = $this->Session->read("PracticeSetting");
                    if(!empty($p['PracticeSetting']['kareo_status']))
		    {			
			// export patient data into kareo
			$this->loadModel('kareo');
			$this->kareo->exportPatientToKareo($patient_id);
		    }
                    $ret = array();
                    $ret['task'] = $task;
                    $ret['patient_id'] = $patient_id;
                    echo json_encode($ret);

                    $this->PatientDemographic->saveAudit('New');
                    exit;
                }

				if($this->getAccessType("administration", "users") != 'NA')
				{ 
					$this->set("useraccount_access", 'yes');
				}
				else
				{
					$this->set("useraccount_access", 'no');
				}
            } break;
            case "edit":
            {
                // Flag if logged in user is a patient
                $is_patient = ($user['role_id'] == EMR_Roles::PATIENT_ROLE_ID);
                $this->set('is_patient', $is_patient);
                
                if (!empty($this->data))
                {
                    $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                    if($this->PatientDemographic->checkMRN($this->data['PatientDemographic']['mrn'], $patient_id))
                    {
                        echo json_encode(array('mrn_error'=>'yes'));
                        exit;
                    } 
                    $this->data['PatientDemographic']['dob'] = data::formatDateToStandard($this->__global_date_format, $this->data['PatientDemographic']['dob']) ;

                    $this->PatientDemographic->save($this->data);
										
										
										// Check Marital Status
										$this->loadModel('PatientSocialHistory');
										$maritalHistory = $this->PatientSocialHistory->find('first', array(
											'conditions' => array(
												'PatientSocialHistory.patient_id' => $patient_id,
												'PatientSocialHistory.type' => 'Marital Status',
											),
										));
										
										$this->data['PatientDemographic']['marital_status'] = trim($this->data['PatientDemographic']['marital_status']);
										if ($this->data['PatientDemographic']['marital_status']) {
											
											if ($maritalHistory) {
												$maritalHistory['PatientSocialHistory']['marital_status'] = $this->data['PatientDemographic']['marital_status'];
											} else {
												$maritalHistory = array(
													'PatientSocialHistory' => array(
														'marital_status' => $this->data['PatientDemographic']['marital_status'],
														'patient_id' => $patient_id,
														'type' => 'Marital Status',
													),
												);
											}
											
												$this->PatientSocialHistory->save($maritalHistory);
											
										} else {
											
											if ($maritalHistory) {
												$this->PatientSocialHistory->delete($maritalHistory['PatientSocialHistory']['social_history_id']);
											}
											
										}
										
										

                    $patient_user_id = 0;
                    
                    // Note if there is an exisiting user account id for this user
                    if ($this->data['UserAccount']['patient_user_user_id']) {
                        $patient_user_id = $this->data['UserAccount']['patient_user_user_id'];
                    }
                    
					if ($this->data['UserAccount']['patient_user_username'] and $this->data['UserAccount']['patient_user_password'])
					{
                                            // Check if user id is present
                                            // which means patient currently has a user account
                                            if ($this->data['UserAccount']['patient_user_user_id']) {
						$this->data['UserAccount']['user_id'] = $this->data['UserAccount']['patient_user_user_id'];
                                            } 
                                                
                                                // If user logged in is a patient
                                                // do not allow username modification
                                                if ($is_patient) {
                                                    unset($this->data['UserAccount']['username']);
                                                } else {
                                                    $this->data['UserAccount']['username'] = $this->data['UserAccount']['patient_user_username'];
                                                }
                                                
						$this->data['UserAccount']['password'] = $this->data['UserAccount']['patient_user_password'];
                                                
                                                // If this user already has an existing user account note it
                                                $existingUserAccount = array();
                                                if ($patient_user_id) {
                                                    
                                                    $existingUserAccount = $this->UserAccount->getCurrentUser($patient_user_id);
                                                
                                                    // If we are changing the password, set password_last_update to 0
                                                    // to trigger password change for user
                                                    if ($this->data['UserAccount']['password'] !== $existingUserAccount['password']) {
                                                        $this->data['UserAccount']['password_last_update'] = 0;
                                                    }
                                                }
                                                
                                                // Set User Patient User Role
                                                $this->data['UserAccount']['role_id'] = EMR_Roles::PATIENT_ROLE_ID;
                                                // Relate to patient demographic via patient_id
                                                $this->data['UserAccount']['patient_id'] = $patient_id;
                                                
                                                // Minimize private data we will be synching and exposing
                                                // from the patient demographics
						$this->data['UserAccount']['firstname'] = $this->data['PatientDemographic']['first_name'];
						$this->data['UserAccount']['lastname'] = $this->data['PatientDemographic']['last_name'];
						$this->data['UserAccount']['email'] = $this->data['PatientDemographic']['email'];
                                                
						$this->UserAccount->save($this->data);
                                                
                                                // If account user id was not set or missing
                                                // this means user account for this patient was just created
                                                // Note the newly created user account id
                                                if (!isset($this->data['UserAccount']['user_id'])) {
                                                    $patient_user_id = $this->UserAccount->getLastInsertId();
                                                }
					}

					$this->PatientDemographic->updateEmdeonPatient($patient_id);

 					$p = $this->Session->read("PracticeSetting");
					if(!empty($p['PracticeSetting']['kareo_status']))
					{
					  // export patient data into kareo
					  $this->loadModel('kareo');
					  $this->kareo->exportPatientToKareo($patient_id);
					}
					$ret = array();
                    $ret['task'] = $task;
                    $ret['patient_id'] = $patient_id;
                    
                    // Send patient's user account id along with other info
                    $ret['patient_user_id'] = $patient_user_id;
                    echo json_encode($ret);

                    $this->PatientDemographic->saveAudit('Update');
                    exit;
                }
                else
                {
                    $this->PatientDemographic->recursive = -1;
                    $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                    $item = $this->PatientDemographic->find(
                        'first',
                        array(
                            'conditions' => array('PatientDemographic.patient_id' => $patient_id),
			    'recursive' => -1
                        )
                    );

                    $this->set('EditItem', $item);
					
                    $this->PatientDemographic->saveAudit('View');
                    $patient_exist_in_useraccount = $this->UserAccount->checkPatient($patient_id);
                    $this->set("patient_exist_in_useraccount", $patient_exist_in_useraccount);
                    
					$patient_user = $this->UserAccount->find('first', array('fields' => array('UserAccount.user_id', 'UserAccount.username', 'UserAccount.password'), 'conditions' => array('UserAccount.role_id' => 8, 'UserAccount.patient_id' => $patient_id)));
					$this->set("patient_user", $patient_user);
                    
                    if($this->getAccessType("administration", "users") != 'NA')
                    { 
                        $this->set("useraccount_access", 'yes');
                    }
                    else
                    {
                        $this->set("useraccount_access", 'no');
                    }
                }
            } break;
        }
    }

    public function search_charts()
    {
         $this->loadModel("PatientDemographic");
         $this->loadModel("PracticeLocation");
         $locations = $this->PracticeLocation->find('list', array(
             'order' => 'PracticeLocation.location_name',
            'fields' => 'PracticeLocation.location_name'
         ));
         $this->Set(compact('locations'));
         $state_codes['StateCode']['state'] = "";
		 $this->set("StateCodes", $this->sanitizeHTML($state_codes['StateCode']['state']));
         $this->PatientDemographic->searchCharts($this);
    }
    
    public function search_charts_view()
    {
        $this->layout = "empty";
        $this->loadModel('UserGroup');
        $isProvider = $this->UserGroup->isProvider($this);
        $this->Set(compact('isProvider'));
        $this->loadModel("PatientDemographic");
		//echo "<pre>";
		//print_r($this->data);
		//exit("End here :P ");
        $this->PatientDemographic->searchChartsData($this, $this->data);
    }

    public function advance_directives()
    {
        $this->layout = "blank";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientAdvanceDirective");
		
		if($task == 'addnew' || $task == 'edit')
        {
			$user = $this->Session->read('UserAccount');
            $user_id = $user['user_id'];
			$this->set('role_id', $role_id = $user['role_id']);
		}

        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            if($this->data['PatientAdvanceDirective']['attachment_is_uploaded'] == "true")
            {
							
								$this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;
								UploadSettings::createIfNotExists($this->paths['patient_id']);
								
                $source_file = $this->paths['temp'] . $this->data['PatientAdvanceDirective']['attachment'];
                $destination_file = $this->paths['patient_id'] . $this->data['PatientAdvanceDirective']['attachment'];

                @rename($source_file, $destination_file);
            }
			
			$this->data['PatientAdvanceDirective']['terminally_ill'] = (isset($this->data['PatientAdvanceDirective']['terminally_ill'])? 1:0);

            $this->data['PatientAdvanceDirective']['patient_id'] = $patient_id;
            $this->data['PatientAdvanceDirective']['service_date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['PatientAdvanceDirective']['service_date'])));
        }

        switch($task)
        {
        
            case "download_file":
            {
            
                $advance_directive_id = (isset($this->params['named']['advance_directive_id'])) ? $this->params['named']['advance_directive_id'] : "";
                $items = $this->PatientAdvanceDirective->find(
                        'first', 
                        array(
                            'conditions' => array('PatientAdvanceDirective.advance_directive_id' => $advance_directive_id)
                        )
                );
                
                $current_item = $items;
                
                
                $file = $current_item['PatientAdvanceDirective']['attachment'];
                $file_name = explode('_',$file);
                
                // Since it is possible for the original file name to contain underscore(s)
                // we get the name by discarding the left most part that contains the file hash
                // and combine the remaining items in the split array. 
                // If it did not have any underscores, we only have 1 element to combine
                // If it had underscores, all remaining elements are combined
                $file_attachment = implode('_',array_slice($file_name, 1));
                $this->paths['patient_id'] = $this->paths['patients'] . $current_item['PatientAdvanceDirective']['patient_id'] . DS;
								
								$targetFile = UploadSettings::existing(
									str_replace('//','/',$this->paths['patients']) . $file,
									str_replace('//','/',$this->paths['patient_id']) . $file
								);
								
                header('Content-Type: application/octet-stream; name="'.$file.'"'); 
                header('Content-Disposition: attachment; filename="'.$file_attachment.'"'); 
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
                if(!empty($this->data))
                {
                    $this->PatientAdvanceDirective->create();
                    $this->PatientAdvanceDirective->save($this->data);

                    $ret = array();
                    echo json_encode($ret);

                    $this->PatientAdvanceDirective->saveAudit('New');
                    exit;
                }
            }
            break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->PatientAdvanceDirective->save($this->data);

                    $ret = array();
                    echo json_encode($ret);
                    $this->PatientAdvanceDirective->saveAudit('Update');
                    exit;
                }
                else
                {
                    $advance_directive_id = (isset($this->params['named']['advance_directive_id'])) ? $this->params['named']['advance_directive_id'] : "";
                    $item = $this->PatientAdvanceDirective->find(
                            'first',
                            array(
                                'conditions' => array('PatientAdvanceDirective.advance_directive_id' => $advance_directive_id)
                            )
                    );

                    $this->set('EditItem', $this->sanitizeHTML($item));
                }
            }
            break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientAdvanceDirective']['advance_directive_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientAdvanceDirective->delete($id, false);
                       $ret['delete_count']++;
                    }
                }

                if($ret['delete_count'] > 0)
                {
                    $this->PatientAdvanceDirective->saveAudit('Delete');
                }

                echo json_encode($ret);
                exit;
            }
            break;
            default:
            {
			    $this->paginate['PatientAdvanceDirective'] = array(
            'conditions' => array('PatientAdvanceDirective.patient_id' => $patient_id),
			'order' => array('PatientAdvanceDirective.modified_timestamp' => 'DESC')
        );
                $this->set('advance_directives', $this->sanitizeHTML($this->paginate('PatientAdvanceDirective')));
                $this->PatientAdvanceDirective->saveAudit('View');

            }
        }
    }

    public function guarantor_details($patient_id)
    {
        $this->loadModel('PatientDemographic');
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $patient['ipad_dob'] = $patient['dob'];
        $patient['dob'] = __date($this->__global_date_format, strtotime($patient['dob']));
        die(json_encode($patient));
    }

    public function guarantor_information()
    {   
		$emdeon_xml_api = new Emdeon_XML_API();
		$practice_settings = $this->Session->read("PracticeSetting");
        $labs_setup =  $practice_settings['PracticeSetting']['labs_setup'];
		
        $this->loadModel('PatientGuarantor');
        $this->loadModel('PatientDemographic');
        
        $this->layout = "empty";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $mrn = $patient['mrn'];
		
		$practice_settings = $this->Session->read("PracticeSetting");
        $labs_setup =  $practice_settings['PracticeSetting']['labs_setup'];

        $this->set('patient_data', $this->sanitizeHTML($patient));

        if($task == 'addnew' || $task == 'edit')
        {
            $this->loadModel('EmdeonRelationship');
            $this->loadModel('StateCode');
            $this->set('states', $this->sanitizeHTML($this->StateCode->getList()));
            $this->set('relationships', $this->sanitizeHTML($this->EmdeonRelationship->find('all')));
            if(!empty($this->data))
            {
                $emdeon_xml_api = new Emdeon_XML_API();
								$this->data['PatientGuarantor']['person'] = 0;
				
				if($emdeon_xml_api->checkConnection())
				{
					$person = $emdeon_xml_api->getPersonByMRN($mrn);
					$this->data['PatientGuarantor']['person'] = $person;
				}
            }
        }

        switch($task)
        {
            case "addnew":
            {
                if(!empty($this->data))
                {
					if($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection())
					{
                    	$this->PatientGuarantor->saveGuarantor($this->data);
					}
					else
					{
						$this->PatientGuarantor->save($this->data);
					}

                    $ret = array();
                    echo json_encode($ret);
                    $this->PatientGuarantor->saveAudit('New');
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
					if($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection())
					{
                    	$this->PatientGuarantor->saveGuarantor($this->data);
					}
					else
					{
						$this->PatientGuarantor->save($this->data);
					}

                    $ret = array();
                    echo json_encode($ret);

                    $this->PatientGuarantor->saveAudit('Update');
                    exit;
                }
                else
                {
                    $guarantor_id = (isset($this->params['named']['guarantor_id'])) ? $this->params['named']['guarantor_id'] : "";
                    $items = $this->PatientGuarantor->find(
                            'first',
                            array(
                                'conditions' => array('PatientGuarantor.guarantor_id' => $guarantor_id)
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
                    $ids = $this->data['PatientGuarantor']['guarantor_id'];

                    foreach($ids as $id)
                    {
						if($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection())
						{
                        	$this->PatientGuarantor->deleteGuarantor($id);
						}
						else
						{
							$this->PatientGuarantor->delete($id);
						}
						
                        $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->PatientGuarantor->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            }
			case "get_content":
            {
				 $guarantor_id = (isset($this->data['guarantor_id'])) ? $this->data['guarantor_id'] : "";
				 $guarantor_content = $this->PatientGuarantor->find('first', array('conditions' =>array('PatientGuarantor.patient_id' => $this->data['patient_id'], 'PatientGuarantor.relationship' => $this->data['relationship'], 'PatientGuarantor.guarantor_id' => $guarantor_id)));
				 $ret = array();
				 $ret['content'] = $guarantor_content['PatientGuarantor'];
				 echo json_encode($ret);

				 exit;
		    }break;
            default:
            {
                $page = (isset($this->params['named']['page'])) ? $this->params['named']['page'] : "";

                if($page == "" && $labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection())
                {
					$this->loadModel("PatientDemographic");
					$this->PatientDemographic->updateEmdeonPatient($patient_id, true);
                    $this->PatientGuarantor->sync($patient_id);
                }
				

                $this->paginate['PatientGuarantor'] = array(
                //'conditions' => array('PatientGuarantor.patient_id' => $patient_id, 'PatientGuarantor.relationship !=' => 18),
                'conditions' => array('PatientGuarantor.patient_id' => $patient_id),
			    'order' => array('PatientGuarantor.modified_timestamp' => 'DESC')
                );
                $this->set('guarantors', $this->sanitizeHTML($this->paginate('PatientGuarantor')));



                //$this->set('guarantors', $this->sanitizeHTML($this->paginate('PatientGuarantor', array('PatientGuarantor.patient_id' => $patient_id, 'PatientGuarantor.relationship !=' => 18))));

                $this->PatientGuarantor->saveAudit('View');
            }
        }
    }

    public function insurance_information()
    {
        $this->loadModel("PatientDemographic");
        $this->loadModel("PatientInsurance");

        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $insurance_info_id = (isset($this->params['named']['insurance_info_id'])) ? $this->params['named']['insurance_info_id'] : "0";
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $mrn = $patient['mrn'];
		
		if($patient['dob'] && !strpos($patient['dob'],'0000')) {
			$patient['dob_js'] = __date($this->__global_date_format, strtotime($patient['dob']));
		}
		
		$this->set('patient_state', $patient['state']);
		
		$practice_settings = $this->Session->read("PracticeSetting");
        $labs_setup =  $practice_settings['PracticeSetting']['labs_setup'];
		
		$emdeon_xml_api = new Emdeon_XML_API();
				
        if($task == "save_photo")
        {
						$this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;
						UploadSettings::createIfNotExists($this->paths['patient_id']);
            if($this->data['photo_type'] == 'insurance_card_front')
            {
                $source_file = $this->paths['temp'] . $this->data['PatientInsurance']['insurance_card_front'];
                $destination_file = $this->paths['patient_id'] . $this->data['PatientInsurance']['insurance_card_front'];
            }
            else
            {
                $source_file = $this->paths['temp'] . $this->data['PatientInsurance']['insurance_card_back'];
                $destination_file = $this->paths['patient_id'] . $this->data['PatientInsurance']['insurance_card_back'];
            }

            @copy($source_file, $destination_file);
            @unlink($source_file); // remove temp file
            $this->PatientInsurance->save($this->data);

            $this->PatientInsurance->saveAudit('Update');

            echo 'saved';
            exit;
        }

        if($task == 'addnew' || $task == 'edit')
        {
            $this->loadModel('EmdeonRelationship');
            $this->loadModel('StateCode');
            $this->set('states', $this->sanitizeHTML($this->StateCode->getList()));
            $this->set('relationships', $this->sanitizeHTML($this->EmdeonRelationship->find('all')));
            if(!empty($this->data))
            {
                $emdeon_xml_api = new Emdeon_XML_API();
                $person = $emdeon_xml_api->getPersonByMRN($mrn);
                $this->data['PatientInsurance']['person'] = $person;
            }
            
            $this->set("priority_values", $this->PatientInsurance->getPriorityValues($patient_id, $insurance_info_id));
        }

        switch($task)
        {
			case "get_patient_data":
			{
				$patientData = json_encode($patient);
				echo $patientData;
				exit;
			} break;
            case "search_insurance":
            {
				
		if(isset($this->data['name']))
		{
		  if ($labs_setup == 'Electronic') {
			$this->data['name'] = str_replace('*', '', $this->data['name']);
			$this->data['name'] = '*'.$this->data['name'].'*';
		  } else {
                        $this->data['name'] = str_replace('%', '', $this->data['name']);
		  }
		}
				
                $search_options = array(
                    'type' => isset($this->data['type']) ? $this->data['type'] : '',
                    'name' => isset($this->data['name']) ? $this->data['name'] : '',
					'address' => isset($this->data['address']) ? $this->data['address'] : '',
                    'city' => isset($this->data['city']) ? $this->data['city'] : '',
                    'state' => isset($this->data['state']) ? $this->data['state'] : '',
                    'hsi_value' => isset($this->data['hsi_value']) ? $this->data['hsi_value'] : ''
                );
		 if($labs_setup == 'Electronic')
             	{
                   $emdeon_xml_api = new Emdeon_XML_API();
                   $search_results = $emdeon_xml_api->searchInsurance($search_options);
		}
		else
		{
		   $this->loadModel('DirectoryInsuranceCompany');
		   $search_results = $this->DirectoryInsuranceCompany->searchInsurance($search_options);
		}

                echo json_encode($search_results);
                exit;
            } break;
            case "addnew":
            {
                if(!empty($this->data))
                {
										//$this->PatientInsurance->id = $this->data['PatientInsurance']['insurance_info_id'];
										//$patient_id = $this->PatientInsurance->field('patient_id');
					
					$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : $this->data['PatientInsurance']['patient_id'];
					
					if($patient_id)
					{
						$this->paths['patient_id'] = $this->paths['patients'] . $patient_id . DS;
						UploadSettings::createIfNotExists($this->paths['patient_id']);
							
						if(strlen($this->data['PatientInsurance']['insurance_card_front']) > 0)
						{
							$source_file = $this->paths['temp'] . $this->data['PatientInsurance']['insurance_card_front'];
							$destination_file = $this->paths['patient_id'] . $this->data['PatientInsurance']['insurance_card_front'];

							@rename($source_file, $destination_file);
						}

						if(strlen($this->data['PatientInsurance']['insurance_card_back']) > 0)
						{
							$source_file = $this->paths['temp'] . $this->data['PatientInsurance']['insurance_card_back'];
							$destination_file = $this->paths['patient_id'] . $this->data['PatientInsurance']['insurance_card_back'];

							@rename($source_file, $destination_file);
						}
					}
                    if($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection())
					{					    
						$this->PatientInsurance->saveInsurance($this->data);
					}
					else
					{
					    $this->PatientInsurance->save($this->data);
					}

                    $p = $this->Session->read("PracticeSetting");
                    if(!empty($p['PracticeSetting']['kareo_status']))
                    {					
			// export patient data into kareo
			$this->loadModel('kareo');
			$this->kareo->exportPatientToKareo($patient_id);
		    }

                    $ret = array();
                    echo json_encode($ret);

                    $this->PatientInsurance->saveAudit('New');

                    exit;
                }
            }
            break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    if($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection())
					{
					    $this->PatientInsurance->saveInsurance($this->data);
					}
					else
					{
					    $this->PatientInsurance->save($this->data);
					}

                    $p = $this->Session->read("PracticeSetting");
                    if(!empty($p['PracticeSetting']['kareo_status']))
                    {					
			// export patient data into kareo
			$this->loadModel('kareo');
			$this->kareo->exportPatientToKareo($patient_id);
		    }

                    $ret = array();
                    echo json_encode($ret);

                    $this->PatientInsurance->saveAudit('Update');

                    exit;
                }
                else
                {
                    $items = $this->PatientInsurance->find('first', array('conditions' => array('PatientInsurance.insurance_info_id' => $insurance_info_id)));
                    $this->set('EditItem', $this->sanitizeHTML($items));
                }
            } break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientInsurance']['insurance_info_id'];

                    foreach($ids as $id)
                    {
                        if($labs_setup=='Electronic' && $emdeon_xml_api->checkConnection())
					    {
						    $this->PatientInsurance->deleteInsurance($id, false);
						}
						else
						{
						    $this->PatientInsurance->delete($id, false);
						}
                        $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->PatientInsurance->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            } break;
            default:
            {
                $page = (isset($this->params['named']['page'])) ? $this->params['named']['page'] : "";                

                if($page == "" and $labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection())
                {
					$this->loadModel("PatientDemographic");
					$this->PatientDemographic->updateEmdeonPatient($patient_id, true);
                    $this->PatientInsurance->sync($patient_id);
                }
				/*
				$this->PracticeSetting =& ClassRegistry::init('PracticeSetting');
				$practice_settings = $this->PracticeSetting->getSettings();
				
				if($labs_setup == 'Electronic')
				{
					$this->paginate['PatientInsurance'] = array(
					'conditions' => array('PatientInsurance.patient_id' => $patient_id, 'PatientInsurance.ownerid' => $practice_settings->emdeon_facility),
					'order' => array('PatientInsurance.start_date' => 'DESC')
					);
				}
				else
				{
					$this->paginate['PatientInsurance'] = array(
					'conditions' => array('PatientInsurance.patient_id' => $patient_id, 'PatientInsurance.insurance' => ''),
					'order' => array('PatientInsurance.start_date' => 'DESC')
					);
				}
				*/
				// show all insurances which are entered...emdeon and self-entered ones
                                        $this->paginate['PatientInsurance'] = array(
                                        'conditions' => array('PatientInsurance.patient_id' => $patient_id),
                                        'order' => array('PatientInsurance.start_date' => 'DESC')
                                        );

                $this->set('insurance_datas', $this->sanitizeHTML($this->paginate('PatientInsurance')));

                //$this->set('insurance_datas', $this->sanitizeHTML($this->paginate('PatientInsurance', array('PatientInsurance.patient_id' => $patient_id, 'PatientInsurance.ownerid' => $practice_settings->emdeon_facility))));

                $this->PatientInsurance->saveAudit('View');
            }
        }
    }

    public function check_eligibility()
    {
        $this->layout = "blank";
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        switch($task)
        {
            case "payer_list_autocomplete":
            {
				$this->loadModel("EligibilityPayerList");
				$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
				
				$this->EligibilityPayerList->execute($this, "load_autocomplete");
            } break;
            case "service_type_autocomplete":
            {
				$this->loadModel("EligibilityServiceType");
				$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
				
				$this->EligibilityServiceType->execute($this, "load_autocomplete");
            } break;
            case "provider_list_autocomplete":
            {
				$this->loadModel("UserRole");
                if (!empty($this->data))
                {
					$this->loadModel("UserGroup");
					$this->loadModel("UserRole");
					
					$providerRoles = $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK, true);

                    $search_keyword = $this->data['autocomplete']['keyword'];
                    $search_limit = $this->data['autocomplete']['limit'];
                    
                    $eligibility_provider_list_items = $this->UserRole->find('all', array(
						'conditions' => array('AND' => array('UserRole.role_desc LIKE ' => '%' . $search_keyword . '%', 'UserRole.role_id' => $providerRoles)),
						'limit' => $search_limit
					));
                    $data_array = array();
                    
                    foreach ($eligibility_provider_list_items as $eligibility_provider_list_item)
                    {
                        $data_array[] = $eligibility_provider_list_item['UserRole']['role_desc'];
                    }
                    
                    echo implode("\n", $data_array);
                }
                exit();
            } break;
			case "check_eligibility":
			{
				$x12 = ITS::print_elig($this->data);
				$x12_arr = explode('~', $x12);
				$new_x12 = "";
				for($i = 0; $i < count($x12_arr); $i++)
				{
					$new_x12 .= trim($x12_arr[$i]) . '~';
				}
				$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
				$insurance_info_id = (isset($this->params['named']['insurance_info_id'])) ? $this->params['named']['insurance_info_id'] : "0";
				$this->Session->write('EligibilityRespond_'.$patient_id.'_'.$insurance_info_id, ITS::emdeonITS("X12", array("wsRequest" => $new_x12)));
				exit;
			} break;
			case "eligibility_respond":
			{
				$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
				$insurance_info_id = (isset($this->params['named']['insurance_info_id'])) ? $this->params['named']['insurance_info_id'] : "0";
				$this->set('insurance_info_id', $insurance_info_id);
				$this->set('EligibilityRespond', $this->Session->read('EligibilityRespond_'.$patient_id.'_'.$insurance_info_id));
			} break;
			default:
			{
				$this->loadModel("PatientDemographic");
				$this->loadModel("PatientInsurance");
				$this->loadModel("PatientPreference");
				$this->loadModel("EligibilityPayerList");

				$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
				$insurance_info_id = (isset($this->params['named']['insurance_info_id'])) ? $this->params['named']['insurance_info_id'] : "0";
				
				$this->Session->write('EligibilityRespond_'.$patient_id.'_'.$insurance_info_id, "");

				$patient = $this->PatientDemographic->getPatient($patient_id);
				$mrn = $patient['mrn'];
				
				if($patient['dob'] && !strpos($patient['dob'],'0000')) {
					$patient['dob_js'] = __date($this->__global_date_format, strtotime($patient['dob']));
				}
				
				$this->set('patient_state', $patient['state']);
				
				$practice_settings = $this->Session->read("PracticeSetting");
				$labs_setup =  $practice_settings['PracticeSetting']['labs_setup'];
				
				$emdeon_xml_api = new Emdeon_XML_API();

				$this->loadModel('EmdeonRelationship');
				$this->loadModel('StateCode');
				$this->set('states', $this->sanitizeHTML($this->StateCode->getList()));
				$this->set('relationships', $this->sanitizeHTML($this->EmdeonRelationship->find('all')));
				if(!empty($this->data))
				{
					$emdeon_xml_api = new Emdeon_XML_API();
					$person = $emdeon_xml_api->getPersonByMRN($mrn);
					$this->data['PatientInsurance']['person'] = $person;
				}
				
				$this->set("priority_values", $this->PatientInsurance->getPriorityValues($patient_id, $insurance_info_id));
	
				$patient_preferences = $this->PatientPreference->find('first', array('conditions' => array('PatientPreference.patient_id' => $patient_id)));
				$this->set("provider_npi", $patient_preferences['UserAccount']['npi']);
	
				$items = $this->PatientInsurance->find('first', array('conditions' => array('PatientInsurance.insurance_info_id' => $insurance_info_id)));
				$this->set('EditItem', $this->sanitizeHTML($items));
			} break;
        }
    }

    public function disclosure_records()
    {
        $this->layout = "blank";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $disclosure_id = (isset($this->params['named']['disclosure_id'])) ? $this->params['named']['disclosure_id'] : "";

        $this->loadModel("PatientDisclosure");
        $this->loadModel("PatientDemographic");

        $medical_information_access = $this->getAccessType("patients", "medical_information");
        $this->set("medical_information_access", $medical_information_access);

        $user = $this->Session->read('UserAccount');
        $this->set('role_id', $user['role_id']);
		$user_name = $user['firstname'].' '.$user['lastname'];
        
        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientDisclosure']['patient_id'] = $patient_id;
			$this->data['PatientDisclosure']['created_by'] = $user_name;

            $this->data['PatientDisclosure']['date_requested'] = __date("Y-m-d", strtotime($this->data['PatientDisclosure']['date_requested']));
            $this->data['PatientDisclosure']['service_date'] = __date("Y-m-d");

            $this->data['PatientDisclosure']['demographics'] = (int)@$this->data['PatientDisclosure']['demographics'];
            $this->data['PatientDisclosure']['allergies'] = (int)@$this->data['PatientDisclosure']['allergies'];
            $this->data['PatientDisclosure']['immunizations'] = (int)@$this->data['PatientDisclosure']['immunizations'];
            $this->data['PatientDisclosure']['problem_list'] = (int)@$this->data['PatientDisclosure']['problem_list'];
            $this->data['PatientDisclosure']['medication_list'] = (int)@$this->data['PatientDisclosure']['medication_list'];
            $this->data['PatientDisclosure']['lab_results'] = (int)@$this->data['PatientDisclosure']['lab_results'];
            $this->data['PatientDisclosure']['radiology_results'] = (int)@$this->data['PatientDisclosure']['radiology_results'];
            $this->data['PatientDisclosure']['health_maintenance'] = (int)@$this->data['PatientDisclosure']['health_maintenance'];
            $this->data['PatientDisclosure']['referrals'] = (int)@$this->data['PatientDisclosure']['referrals'];
        }

        $patient = $this->PatientDemographic->getPatient($patient_id);

        switch($task)
        {
            case "get_report_html" :
            case "get_report_pdf" :
            {
                $this->layout = 'empty';
				ob_start();
				
							$patient_disclosure = (isset($_COOKIE['patient_disclosure_'.$patient_id])) ? $_COOKIE['patient_disclosure_'.$patient_id] : "";
							$patient_disclosure = explode("|", $patient_disclosure);
				
                if($report = Disclosure_Records::generateReport( $patient_id, $disclosure_id ))
                {
                    if ($task == "get_report_pdf")
                    {
                        //$this->loadModel("Pdf");
						
                        $url = $this->paths['temp'];
                        $url = str_replace('//','/',$url);

                        $pdffile = $patient['mrn'].'_Medical_Records.pdf';

                        //PDF file creation
                        //site::write(pdfReport::generate($report, $url.$pdffile), $url.$pdffile);
                        site::write(pdfReport::generate($report), $url.$pdffile);
                        $this->loadModel('practiceSetting');
						$settings  = $this->practiceSetting->getSettings();        
						if(!$settings->faxage_username || !$settings->faxage_password || !$settings->faxage_company) {
							$this->Session->setFlash(__('Fax is not enabled. Contact Sales for assistance.', true));
							$this->redirect(array('controller'=> 'encounters', 'action' => 'index'));
							exit();
						}
						if( $view == 'fax' ){
							$this->Session->write('fileName', $url.$pdffile);
							$this->redirect(array('controller'=> 'messaging', 'action' => 'new_fax' ,'fax_doc'));		
							exit;						
						}

                        $file = 'disclosure_'.$patient_id.'_'.$disclosure_id.'.pdf';
                        $targetPath = $this->paths['temp'];
                        $targetFile =  str_replace('//','/',$targetPath) . $file;
                        header('Content-Type: application/octet-stream; name="'.$file.'"');
                        header('Content-Disposition: attachment; filename="'.$file.'"');
                        header('Accept-Ranges: bytes');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Content-transfer-encoding: binary');
                        header('Content-length: ' . @filesize($url.$pdffile));
                        @readfile($url.$pdffile);
                    } else if ($patient_disclosure[13] == 'checked') {
												
                      
                      $timeCount = intval($patient_disclosure[14]);
                      $timeUnit = strtolower(trim($patient_disclosure[15]));
                      $timeUnit = ($timeUnit == 'months') ? $timeUnit : 'years';
                      
                      
                      $conditions = array(
													'EncounterMaster.encounter_status' => 'Closed',
													'EncounterMaster.patient_id' => $patient_id,
                      );
                      if ($timeCount) {
                        $conditions['EncounterMaster.encounter_date >='] = __date('Y-m-d 00:00:0', strtotime('-' . $timeCount . ' ' . $timeUnit));
                      }
                      
											$this->loadModel('EncounterMaster');
											$encounters = $this->EncounterMaster->find('all', array(
												'conditions' => $conditions,
											));
											
											$zipFile = $this->paths['temp'] . uniqid();
											$zip = new ZipArchive();
											if (!$zip->open($zipFile, ZipArchive::CREATE)) {
												echo "\n Failed to create zip archive. \n";
												die();
											}
											
											
											$url = $this->paths['temp'];
											$url = str_replace('//','/',$url);

											$pdffile = $patient['mrn'].'_Medical_Records.pdf';

											//PDF file creation
											//site::write(pdfReport::generate($report, $url.$pdffile), $url.$pdffile);
											site::write(pdfReport::generate($report), $url.$pdffile);

											$zip->addFile($url.$pdffile, $pdffile);
			
											$visitSummaryFiles = array();
											
											foreach ($encounters as $e) {
											
												$encounter_id = $e['EncounterMaster']['encounter_id'];
												if(empty($e['UserAccount']['new_pt_note']) || empty($e['UserAccount']['est_pt_note'])) {
												 $defaultFormat = 'soap';
												} else {
												 $defaultFormat = 'full';
												}

												if ($e['PatientDemographic']['status'] == 'New') {
													 $defaultFormat = 'soap';

													if ($e['UserAccount']['new_pt_note'] == '1') {
													 $defaultFormat = 'full';
													}

												} else {
													 $defaultFormat = 'soap';

													if ($e['UserAccount']['est_pt_note'] == '1') {
													 $defaultFormat = 'full';
													}
												}									

												$snapShots = Visit_Summary::getSnapShot($encounter_id, 'pdf');

												$targetFile = $snapShots[$defaultFormat];
												
												$file = 'encounter_' . $encounter_id . '_summary.pdf';												
												
												$zip->addFile($targetFile, $file);
												
											}
											
											$zip->close();
											
											
											$filename = 'patient_disclosure_' . $patient_id . '.zip';
											header('Content-Type: application/octet-stream; name="'.$filename.'"');
											header('Content-Disposition: attachment; filename="'.$filename.'"');
											header('Accept-Ranges: bytes');
											header('Pragma: no-cache');
											header('Expires: 0');
											header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
											header('Content-transfer-encoding: binary');
											header('Content-length: ' . @filesize($zipFile));
											@readfile($zipFile);											
											
											@unlink($zipFile);
                    } else {
                        echo $report;
										}
                    ob_flush();
                    flush();
                    exit();
                }
                exit('could not generate report');
            } break;
            case "get_report_ccr" :
            {
                function getUuid() {

                   // The field names refer to RFC 4122 section 4.1.2

                   return sprintf('A%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
                       mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
                       mt_rand(0, 65535), // 16 bits for "time_mid"
                       mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
                       bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
                           // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
                           // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
                           // 8 bits for "clk_seq_low"
                       mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
                   );
                }

                function sourceType($ccr, $uuid){

                    $e_Source = $ccr->createElement('Source');

                    $e_Actor = $ccr->createElement('Actor');
                    $e_Source->appendChild($e_Actor);

                    $e_ActorID = $ccr->createElement('ActorID',$uuid);
                    $e_Actor->appendChild($e_ActorID);

                    return $e_Source;
                }

                $authorID = getUuid();
                $patientID = getUuid();
                $sourceID = getUuid();
                $oemrID = getUuid();
                $uuid = '';

                $user = $this->Session->read('UserAccount');
                $this->loadModel("EncounterMaster");
                $this->loadModel("PatientDemographic");
                $this->loadModel("PatientMedicalHistory");
                $this->loadModel("PatientSurgicalHistory");
                $this->loadModel("PatientSocialHistory");
                $this->loadModel("PatientFamilyHistory");
                $this->loadModel("PatientAllergy");
                $this->loadModel("PatientProblemList");
                $this->loadModel("EncounterPointOfCare");
                $this->loadModel("PatientMedicationList");
                $this->loadModel("EncounterPlanReferral");
                $this->loadModel("EncounterPlanHealthMaintenance");

                $patient_encounter_id = $this->EncounterMaster->getEncountersByPatientID($patient_id);
                $location = $this->PracticeLocation->find('first', array('conditions' => array('PracticeLocation.location_id' => $user['work_location'])));
                $patient = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id),'recursive' => -1));
                $medical_histories = $this->sanitizeHTML($this->PatientMedicalHistory->find('all', array('conditions' => array('PatientMedicalHistory.patient_id' => $patient_id))));
                $surgical_histories = $this->sanitizeHTML($this->PatientSurgicalHistory->find('all', array('conditions' => array('PatientSurgicalHistory.patient_id' => $patient_id))));
                $social_histories = $this->sanitizeHTML($this->PatientSocialHistory->find('all', array('conditions' => array('PatientSocialHistory.patient_id' => $patient_id))));
                $family_histories = $this->sanitizeHTML($this->PatientFamilyHistory->find('all', array('conditions' => array('PatientFamilyHistory.patient_id' => $patient_id))));
                $allergies = $this->sanitizeHTML($this->PatientAllergy->find('all', array('conditions' => array('PatientAllergy.patient_id' => $patient_id))));
                $problem_lists = $this->sanitizeHTML($this->PatientProblemList->find('all', array('conditions' => array('PatientProblemList.patient_id' => $patient_id))));
                $lab_results = $this->sanitizeHTML($this->EncounterPointOfCare->find('all', array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Labs')))));
                $radiology_results = $this->sanitizeHTML($this->EncounterPointOfCare->find('all', array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Radiology')))));
                $plan_procedures = $this->sanitizeHTML($this->EncounterPointOfCare->find('all', array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Procedure')))));
                $immunizations = $this->sanitizeHTML($this->EncounterPointOfCare->find('all', array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Immunization')))));
                $injections = $this->sanitizeHTML($this->EncounterPointOfCare->find('all', array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Injection')))));
                $medication_lists = $this->sanitizeHTML($this->PatientMedicationList->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id))));
                $plan_referrals = $this->sanitizeHTML($this->EncounterPlanReferral->find('all', array('conditions' => array('EncounterPlanReferral.encounter_id' => $patient_encounter_id))));
                $plan_health_maintenance = $this->sanitizeHTML($this->EncounterPlanHealthMaintenance->find('all', array('conditions' => array('EncounterPlanHealthMaintenance.encounter_id' => $patient_encounter_id))));
				$ccr_mode = (isset($this->params['named']['ccr_mode'])) ? $this->params['named']['ccr_mode'] : "";

                $patient_disclosure = (isset($_COOKIE['patient_disclosure_'.$patient_id])) ? $_COOKIE['patient_disclosure_'.$patient_id] : "";
                $patient_disclosure = explode("|", $patient_disclosure);
				
				//$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                //$disclosure_id = (isset($this->params['named']['disclosure_id'])) ? $this->params['named']['disclosure_id'] : "";
				
				$patient_disclosure[0] = (isset($patient_disclosure[0])) ? $patient_disclosure[0] : "";
				$patient_disclosure[1] = (isset($patient_disclosure[1])) ? $patient_disclosure[1] : "";
				$patient_disclosure[2] = (isset($patient_disclosure[2])) ? $patient_disclosure[2] : "";
				$patient_disclosure[3] = (isset($patient_disclosure[3])) ? $patient_disclosure[3] : "";
				$patient_disclosure[4] = (isset($patient_disclosure[4])) ? $patient_disclosure[4] : "";
				$patient_disclosure[5] = (isset($patient_disclosure[5])) ? $patient_disclosure[5] : "";
				$patient_disclosure[6] = (isset($patient_disclosure[6])) ? $patient_disclosure[6] : "";
				$patient_disclosure[7] = (isset($patient_disclosure[7])) ? $patient_disclosure[7] : "";
				$patient_disclosure[8] = (isset($patient_disclosure[8])) ? $patient_disclosure[8] : "";
				$patient_disclosure[9] = (isset($patient_disclosure[9])) ? $patient_disclosure[9] : "";
				$patient_disclosure[10] = (isset($patient_disclosure[10])) ? $patient_disclosure[10] : "";
				$patient_disclosure[11] = (isset($patient_disclosure[11])) ? $patient_disclosure[11] : "";

                $ccr = new DOMDocument('1.0','UTF-8');
                $e_styleSheet = $ccr->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'.substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], "//") + 2).$_SERVER['HTTP_HOST'].str_replace("index.php", "", $_SERVER['PHP_SELF']).'ccr/patient_ccr.xsl"');
                $ccr->appendChild($e_styleSheet);

                $e_ccr = $ccr->createElementNS('urn:astm-org:CCR', 'ContinuityOfCareRecord');
                $ccr->appendChild($e_ccr);


                /////////////// Header
                $e_ccrDocObjID = $ccr->createElement('CCRDocumentObjectID', getUuid());
                $e_ccr->appendChild($e_ccrDocObjID);

                $e_Language = $ccr->createElement('Language');
                $e_ccr->appendChild($e_Language);

                $e_Text = $ccr->createElement('Text', 'English');
                $e_Language->appendChild($e_Text);

                $e_Version = $ccr->createElement('Version', 'V1.0');
                $e_ccr->appendChild($e_Version);

                $e_dateTime = $ccr->createElement('DateTime');
                $e_ccr->appendChild($e_dateTime);

                $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date('Y-m-d\TH:i:s\Z'));
                $e_dateTime->appendChild($e_ExactDateTime);

                $e_patient = $ccr->createElement('Patient');
                $e_ccr->appendChild($e_patient);

                //$e_ActorID = $ccr->createElement('ActorID', $patient['PatientDemographic']['patient_id']);
                $e_ActorID = $ccr->createElement('ActorID', 'A1234');
                $e_patient->appendChild($e_ActorID);

                //Header From:
                $e_From = $ccr->createElement('From');
                $e_ccr->appendChild($e_From);

                $e_ActorLink = $ccr->createElement('ActorLink');
                $e_From->appendChild($e_ActorLink);

                $e_ActorID = $ccr->createElement('ActorID', $authorID );
                $e_ActorLink->appendChild($e_ActorID);

                $e_ActorRole = $ccr->createElement('ActorRole');
                $e_ActorLink->appendChild($e_ActorRole);

                $e_Text = $ccr->createElement('Text', 'author');
                $e_ActorRole->appendChild($e_Text);

                //Header To:
                $e_To = $ccr->createElement('To');
                $e_ccr->appendChild($e_To);

                $e_ActorLink = $ccr->createElement('ActorLink');
                $e_To->appendChild($e_ActorLink);

                //$e_ActorID = $ccr->createElement('ActorID', $patient['PatientDemographic']['patient_id']);
                $e_ActorID = $ccr->createElement('ActorID', 'A1234');
                $e_ActorLink->appendChild($e_ActorID);

                $e_ActorRole = $ccr->createElement('ActorRole');
                $e_ActorLink->appendChild($e_ActorRole);

                $e_Text = $ccr->createElement('Text', 'patient');
                $e_ActorRole->appendChild($e_Text);

                //Header Purpose:
                $e_Purpose = $ccr->createElement('Purpose');
                $e_ccr->appendChild($e_Purpose);

                $e_Description = $ccr->createElement('Description');
                $e_Purpose->appendChild($e_Description);

                $e_Text = $ccr->createElement('Text', 'Summary of patient information');
                $e_Description->appendChild($e_Text);

                $e_Body = $ccr->createElement('Body');
                $e_ccr->appendChild($e_Body);

                if ($patient_disclosure[1] == "true")
                {
                    /////////////// Medical Histories

                    $e_MedicalHistories = $ccr->createElement('MedicalHistories');
                    foreach ($medical_histories as $medical_history):

                        $e_MedicalHistory = $ccr->createElement('MedicalHistory');
                        $e_MedicalHistories->appendChild($e_MedicalHistory);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_MedicalHistory->appendChild($e_CCRDataObjectID);

												if (isset($medical_history['PatientMedicalHistory']['start_date'])) {
													$e_DateTime = $ccr->createElement('DateTime');
													$e_MedicalHistory->appendChild($e_DateTime);
													
													$e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($medical_history['PatientMedicalHistory']['start_date'])));
													$e_DateTime->appendChild($e_ExactDateTime);
												}
												
                        $e_IDs = $ccr->createElement('IDs');
                        $e_MedicalHistory->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $medical_history['PatientMedicalHistory']['patient_id']);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_Type = $ccr->createElement('Type');
                        $e_MedicalHistory->appendChild($e_Type);

                        $e_Text = $ccr->createElement('Text', $medical_history['PatientMedicalHistory']['diagnosis']);
                        $e_Type->appendChild($e_Text);

                        $e_Code = $ccr->createElement('Code');
                        $e_Description->appendChild($e_Code);

                        $e_Value = $ccr->createElement('Value', $medical_history['PatientMedicalHistory']['icd_code']);
                        $e_Code->appendChild($e_Value);

                        $e_Value = $ccr->createElement('CodingSystem', 'ICD9-CM');
                        $e_Code->appendChild($e_Value);

                        $e_Description = $ccr->createElement('Description');
                        $e_MedicalHistory->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', $medical_history['PatientMedicalHistory']['comment']);
                        $e_Description->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_MedicalHistory->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', $medical_history['PatientMedicalHistory']['status']);
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_MedicalHistory->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_MedicalHistories);


                    /////////////// Surgical Histories

                    $e_SurgicalHistories = $ccr->createElement('SurgicalHistories');
                    foreach ($surgical_histories as $surgical_history):

                        $e_SurgicalHistory = $ccr->createElement('SurgicalHistory');
                        $e_SurgicalHistories->appendChild($e_SurgicalHistory);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_SurgicalHistory->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_SurgicalHistory->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($surgical_history['PatientSurgicalHistory']['date_from'])));
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_SurgicalHistory->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $surgical_history['PatientSurgicalHistory']['patient_id']);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_Surgery = $ccr->createElement('Surgery');
                        $e_SurgicalHistory->appendChild($e_Surgery);

                        $e_Text = $ccr->createElement('Text', $surgical_history['PatientSurgicalHistory']['surgery']);
                        $e_Surgery->appendChild($e_Text);

                        $e_Type = $ccr->createElement('Type');
                        $e_SurgicalHistory->appendChild($e_Type);

                        $e_Text = $ccr->createElement('Text', $surgical_history['PatientSurgicalHistory']['type']);
                        $e_Type->appendChild($e_Text);

                        $e_Description = $ccr->createElement('Description');
                        $e_SurgicalHistory->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', $surgical_history['PatientSurgicalHistory']['reason']);
                        $e_Description->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_SurgicalHistory->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', '');
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_SurgicalHistory->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_SurgicalHistories);


                    /////////////// Social Histories

                    $e_SocialHistories = $ccr->createElement('SocialHistories');
                    foreach ($social_histories as $social_history):

                        $e_SocialHistory = $ccr->createElement('SocialHistory');
                        $e_SocialHistories->appendChild($e_SocialHistory);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_SocialHistory->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_SocialHistory->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', '');
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_SocialHistory->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $social_history['PatientSocialHistory']['patient_id']);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_Routine = $ccr->createElement('Routine');
                        $e_SocialHistory->appendChild($e_Routine);

                        $e_Text = $ccr->createElement('Text', $social_history['PatientSocialHistory']['routine']);
                        $e_Routine->appendChild($e_Text);

                        $e_Substance = $ccr->createElement('Substance');
                        $e_SocialHistory->appendChild($e_Substance);

                        $e_Text = $ccr->createElement('Text', $social_history['PatientSocialHistory']['substance']);
                        $e_Substance->appendChild($e_Text);

                        $e_Type = $ccr->createElement('Type');
                        $e_SocialHistory->appendChild($e_Type);

                        $e_Text = $ccr->createElement('Text', $social_history['PatientSocialHistory']['type']);
                        $e_Type->appendChild($e_Text);

                        $e_Description = $ccr->createElement('Description');
                        $e_SocialHistory->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', $social_history['PatientSocialHistory']['comment']);
                        $e_Description->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_SocialHistory->appendChild($e_Status);

                        if ($social_history['PatientSocialHistory']['type'] == 'Activities')
                        {
                            $e_Text = $ccr->createElement('Text', $social_history['PatientSocialHistory']['routine_status']);
                        }
                        else
                        {
                            $e_Text = $ccr->createElement('Text', $social_history['PatientSocialHistory']['consumption_status']);
                        }
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_SocialHistory->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_SocialHistories);


                    /////////////// Family Histories

                    $e_FamilyHistories = $ccr->createElement('FamilyHistories');
                    foreach ($family_histories as $family_history):

                        $e_FamilyHistory = $ccr->createElement('FamilyHistory');
                        $e_FamilyHistories->appendChild($e_FamilyHistory);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_FamilyHistory->appendChild($e_CCRDataObjectID);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_FamilyHistory->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $family_history['PatientFamilyHistory']['patient_id']);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_Name = $ccr->createElement('Name');
                        $e_FamilyHistory->appendChild($e_Name);

                        $e_Text = $ccr->createElement('Text', $family_history['PatientFamilyHistory']['name']);
                        $e_Name->appendChild($e_Text);

                        $e_Relationship = $ccr->createElement('Relationship');
                        $e_FamilyHistory->appendChild($e_Relationship);

                        $e_Text = $ccr->createElement('Text', $family_history['PatientFamilyHistory']['relationship']);
                        $e_Relationship->appendChild($e_Text);

                        $e_Problem = $ccr->createElement('Problem');
                        $e_FamilyHistory->appendChild($e_Problem);

                        $e_Text = $ccr->createElement('Text', $family_history['PatientFamilyHistory']['problem']);
                        $e_Problem->appendChild($e_Text);

                        $e_Description = $ccr->createElement('Description');
                        $e_FamilyHistory->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', $family_history['PatientFamilyHistory']['comment']);
                        $e_Description->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_FamilyHistory->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', $family_history['PatientFamilyHistory']['status']);
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_FamilyHistory->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_FamilyHistories);
                }

                if ($patient_disclosure[2] == "true")
                {
                    /////////////// Allergies

                    $e_Allergies = $ccr->createElement('Allergies');
                    foreach ($allergies as $allergy):

                        $e_Allergy = $ccr->createElement('Allergy');
                        $e_Allergies->appendChild($e_Allergy);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_Allergy->appendChild($e_CCRDataObjectID);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_Allergy->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $allergy['PatientAllergy']['patient_id']);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_Agent = $ccr->createElement('Agent');
                        $e_Allergy->appendChild($e_Agent);

                        $e_Text = $ccr->createElement('Text', $allergy['PatientAllergy']['agent']);
                        $e_Agent->appendChild($e_Text);

                        $e_Type = $ccr->createElement('Type');
                        $e_Allergy->appendChild($e_Type);

                        $e_Text = $ccr->createElement('Text', $allergy['PatientAllergy']['type']);
                        $e_Type->appendChild($e_Text);

                        $e_Reactions = $ccr->createElement('Reactions');
                        $e_Allergy->appendChild($e_Reactions);

                        $reactions = "";
                        for ($count = 1; $count <= $allergy['PatientAllergy']['reaction_count']; ++$count)
                        {
                            if ($allergy['PatientAllergy']['reaction'.$count])
                            {
                                if ($reactions)
                                {
                                    $reactions .= ', ';
                                }
                                $reactions .= 'Reaction #'.$count.': '.$allergy['PatientAllergy']['reaction'.$count];
                                if ($allergy['PatientAllergy']['severity'.$count])
                                {
                                    $reactions .= ' Severity: '.$allergy['PatientAllergy']['severity'.$count];
                                }
                            }
                        }

                        $e_Text = $ccr->createElement('Text', $reactions);
                        $e_Reactions->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_Allergy->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', $allergy['PatientAllergy']['status']);
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_Allergy->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_Allergies);
                }

                if ($patient_disclosure[3] == "true")
                {
                    /////////////// Problem Lists

                    $e_Problems = $ccr->createElement('Problems');
                    $pCount = 0;
                    foreach ($problem_lists as $problem_list)
                        : $pCount++;

                        $e_Problem = $ccr->createElement('Problem');
                        $e_Problems->appendChild($e_Problem);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', 'PROB' . $pCount);
                        $e_Problem->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_Problem->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($problem_list['PatientProblemList']['start_date'])));
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_Problem->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $problem_list['PatientProblemList']['patient_id']);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_Type = $ccr->createElement('Type');
                        $e_Problem->appendChild($e_Type);

                        $e_Text = $ccr->createElement('Text', 'Problem');
                        $e_Type->appendChild($e_Text);

                        $e_Description = $ccr->createElement('Description');
                        $e_Problem->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', $problem_list['PatientProblemList']['diagnosis']);
                        $e_Description->appendChild($e_Text);

                        $e_Code = $ccr->createElement('Code');
                        $e_Description->appendChild($e_Code);

                        $e_Value = $ccr->createElement('Value', $problem_list['PatientProblemList']['icd_code']);
                        $e_Code->appendChild($e_Value);

                        $e_Value = $ccr->createElement('CodingSystem', 'ICD9-CM');
                        $e_Code->appendChild($e_Value);

                        $e_Status = $ccr->createElement('Status');
                        $e_Problem->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', $problem_list['PatientProblemList']['status']);
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_Problem->appendChild($e_Source);

                        $e_CommentID = $ccr->createElement('CommentID', $problem_list['PatientProblemList']['comment']);
                        $e_Problem->appendChild($e_CommentID);

                        $e_Episodes = $ccr->createElement('Episodes');
                        $e_Problem->appendChild($e_Episodes);

                        $e_Number = $ccr->createElement('Number');
                        $e_Episodes->appendChild($e_Number);

                        $e_Episode = $ccr->createElement('Episode');
                        $e_Episodes->appendChild($e_Episode);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', 'EP' . $pCount);
                        $e_Episode->appendChild($e_CCRDataObjectID);

                        $e_Episode->appendChild(sourceType($ccr, $sourceID));

                        $e_Episodes->appendChild(sourceType($ccr, $sourceID));

                        $e_HealthStatus = $ccr->createElement('HealthStatus');
                        $e_Problem->appendChild($e_HealthStatus);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_HealthStatus->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime');
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_Description = $ccr->createElement('Description');
                        $e_HealthStatus->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', $problem_list['PatientProblemList']['diagnosis']);
                        $e_Description->appendChild($e_Text);

                        $e_HealthStatus->appendChild(sourceType($ccr, $sourceID));
                    endforeach;
                    $e_Body->appendChild($e_Problems);
                }

                if ($patient_disclosure[4] == "true")
                {
                    /////////////// Labs

                    $e_Labs = $ccr->createElement('Labs');
                    foreach ($lab_results as $lab_result):

                        $e_Lab = $ccr->createElement('Lab');
                        $e_Labs->appendChild($e_Lab);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_Lab->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_Lab->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($lab_result['EncounterPointOfCare']['lab_date_performed'])));
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_Lab->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $patient_id);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_TestName = $ccr->createElement('TestName');
                        $e_Lab->appendChild($e_TestName);

                        $e_Text = $ccr->createElement('Text', $lab_result['EncounterPointOfCare']['lab_test_name']);
                        $e_TestName->appendChild($e_Text);

                        $e_Specimen = $ccr->createElement('Specimen');
                        $e_Lab->appendChild($e_Specimen);

                        $e_Text = $ccr->createElement('Text', $lab_result['EncounterPointOfCare']['lab_specimen']);
                        $e_Specimen->appendChild($e_Text);

                        $e_TestResult = $ccr->createElement('TestResult');
                        $e_Lab->appendChild($e_TestResult);

                        $e_Text = $ccr->createElement('Text', $lab_result['EncounterPointOfCare']['lab_test_result']);
                        $e_TestResult->appendChild($e_Text);

                        $e_NormalRange = $ccr->createElement('NormalRange');
                        $e_Lab->appendChild($e_NormalRange);

                        $e_Text = $ccr->createElement('Text', $lab_result['EncounterPointOfCare']['lab_normal_range']);
                        $e_NormalRange->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_Lab->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', $lab_result['EncounterPointOfCare']['status']);
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_Lab->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_Labs);
                }

                if ($patient_disclosure[5] == "true")
                {
                    /////////////// Radiologies

                    $e_Radiologies = $ccr->createElement('Radiologies');
                    foreach ($radiology_results as $radiology_result):

                        $e_Radiology = $ccr->createElement('Radiology');
                        $e_Radiologies->appendChild($e_Radiology);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_Radiology->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_Radiology->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($radiology_result['EncounterPointOfCare']['radiology_date_performed'])));
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_Radiology->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $patient_id);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_ProcedureName = $ccr->createElement('ProcedureName');
                        $e_Radiology->appendChild($e_ProcedureName);

                        $e_Text = $ccr->createElement('Text', $radiology_result['EncounterPointOfCare']['radiology_procedure_name']);
                        $e_ProcedureName->appendChild($e_Text);

												if (isset($radiology_result['EncounterPointOfCare']['radiology_body_site'])) {
													$e_BodySite = $ccr->createElement('BodySite');
													$e_Radiology->appendChild($e_BodySite);

													$e_Text = $ccr->createElement('Text', $radiology_result['EncounterPointOfCare']['radiology_body_site']);
													$e_BodySite->appendChild($e_Text);
												}

                        $e_Laterality = $ccr->createElement('Laterality');
                        $e_Radiology->appendChild($e_Laterality);

                        $e_Text = $ccr->createElement('Text', $radiology_result['EncounterPointOfCare']['radiology_laterality']);
                        $e_Laterality->appendChild($e_Text);

                        $e_TestResult = $ccr->createElement('TestResult');
                        $e_Radiology->appendChild($e_TestResult);

                        $e_Text = $ccr->createElement('Text', $radiology_result['EncounterPointOfCare']['radiology_test_result']);
                        $e_TestResult->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_Radiology->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', $radiology_result['EncounterPointOfCare']['status']);
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_Radiology->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_Radiologies);
                }

                if ($patient_disclosure[6] == "true")
                {
                    /////////////// Procedures

                    $e_Procedures = $ccr->createElement('Procedures');
                    foreach ($plan_procedures as $plan_procedure):

                        $e_Procedure = $ccr->createElement('Procedure');
                        $e_Procedures->appendChild($e_Procedure);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_Procedure->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_Procedure->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($plan_procedure['EncounterPointOfCare']['procedure_date_performed'])));
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_Procedure->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $patient_id);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_ProcedureName = $ccr->createElement('ProcedureName');
                        $e_Procedure->appendChild($e_ProcedureName);

                        $e_Text = $ccr->createElement('Text', $plan_procedure['EncounterPointOfCare']['procedure_name']);
                        $e_ProcedureName->appendChild($e_Text);

                        $e_BodySite = $ccr->createElement('BodySite');
                        $e_Procedure->appendChild($e_BodySite);

                        $e_Text = $ccr->createElement('Text', $plan_procedure['EncounterPointOfCare']['procedure_body_site']);
                        $e_BodySite->appendChild($e_Text);

                        $e_Details = $ccr->createElement('Details');
                        $e_Procedure->appendChild($e_Details);

                        $e_Text = $ccr->createElement('Text', $plan_procedure['EncounterPointOfCare']['procedure_details']);
                        $e_Details->appendChild($e_Text);

                        $e_Description = $ccr->createElement('Description');
                        $e_Procedure->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', $plan_procedure['EncounterPointOfCare']['procedure_comment']);
                        $e_Description->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_Procedure->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', '');
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_Procedure->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_Procedures);
                }

                if ($patient_disclosure[7] == "true")
                {
                    /////////////// Immunizations

                    $e_Immunizations = $ccr->createElement('Immunizations');
                    foreach ($immunizations as $immunization):

                        $e_Immunization = $ccr->createElement('Immunization');
                        $e_Immunizations->appendChild($e_Immunization);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_Immunization->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_Immunization->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($immunization['EncounterPointOfCare']['vaccine_date_performed'])));
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_Immunization->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $patient_id);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_VaccineName = $ccr->createElement('VaccineName');
                        $e_Immunization->appendChild($e_VaccineName);

                        $e_Text = $ccr->createElement('Text', $immunization['EncounterPointOfCare']['vaccine_name']);
                        $e_VaccineName->appendChild($e_Text);

                        $e_LotNumber = $ccr->createElement('LotNumber');
                        $e_Immunization->appendChild($e_LotNumber);

                        $e_Text = $ccr->createElement('Text', $immunization['EncounterPointOfCare']['vaccine_lot_number']);
                        $e_LotNumber->appendChild($e_Text);

                        $e_Manufacturer = $ccr->createElement('Manufacturer');
                        $e_Immunization->appendChild($e_Manufacturer);

                        $e_Text = $ccr->createElement('Text', $immunization['EncounterPointOfCare']['vaccine_manufacturer']);
                        $e_Manufacturer->appendChild($e_Text);

                        $e_Dose = $ccr->createElement('Dose');
                        $e_Immunization->appendChild($e_Dose);

                        $e_Text = $ccr->createElement('Text', $immunization['EncounterPointOfCare']['vaccine_dose']);
                        $e_Dose->appendChild($e_Text);

                        $e_BodySite = $ccr->createElement('BodySite');
                        $e_Immunization->appendChild($e_BodySite);

                        $e_Text = $ccr->createElement('Text', $immunization['EncounterPointOfCare']['vaccine_body_site']);
                        $e_BodySite->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_Immunization->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', $immunization['EncounterPointOfCare']['status']);
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_Immunization->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_Immunizations);
                }

                if ($patient_disclosure[8] == "true")
                {
                    /////////////// Injections

                    $e_Injections = $ccr->createElement('Injections');
                    foreach ($injections as $injection):

                        $e_Injection = $ccr->createElement('Injection');
                        $e_Injections->appendChild($e_Injection);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_Injection->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_Injection->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($injection['EncounterPointOfCare']['injection_date_performed'])));
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_Injection->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $patient_id);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_InjectionName = $ccr->createElement('InjectionName');
                        $e_Injection->appendChild($e_InjectionName);

                        $e_Text = $ccr->createElement('Text', $injection['EncounterPointOfCare']['injection_name']);
                        $e_InjectionName->appendChild($e_Text);

                        $e_LotNumber = $ccr->createElement('LotNumber');
                        $e_Injection->appendChild($e_LotNumber);

                        $e_Text = $ccr->createElement('Text', $injection['EncounterPointOfCare']['injection_lot_number']);
                        $e_LotNumber->appendChild($e_Text);

                        $e_Manufacturer = $ccr->createElement('Manufacturer');
                        $e_Injection->appendChild($e_Manufacturer);

                        $e_Text = $ccr->createElement('Text', $injection['EncounterPointOfCare']['injection_manufacturer']);
                        $e_Manufacturer->appendChild($e_Text);

                        $e_Dose = $ccr->createElement('Dose');
                        $e_Injection->appendChild($e_Dose);

                        $e_Text = $ccr->createElement('Text', $injection['EncounterPointOfCare']['injection_dose']);
                        $e_Dose->appendChild($e_Text);

                        $e_BodySite = $ccr->createElement('BodySite');
                        $e_Injection->appendChild($e_BodySite);

                        $e_Text = $ccr->createElement('Text', $injection['EncounterPointOfCare']['injection_body_site']);
                        $e_BodySite->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_Injection->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', $injection['EncounterPointOfCare']['status']);
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_Injection->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_Injections);
                }

                if ($patient_disclosure[9] == "true")
                {
                    /////////////// Medical Lists

                    $e_Medicals = $ccr->createElement('Medicals');
                    $pCount = 0;
                    foreach ($medication_lists as $medication_list)
                        : $pCount++;

                        $e_Medical = $ccr->createElement('Medical');
                        $e_Medicals->appendChild($e_Medical);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', 'MED' . $pCount);
                        $e_Medical->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_Medical->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($medication_list['PatientMedicationList']['start_date'])));
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_IDs = $ccr->createElement('IDs');
                        $e_Medical->appendChild($e_IDs);

                        $e_ID = $ccr->createElement('ID', $medication_list['PatientMedicationList']['patient_id']);
                        $e_IDs->appendChild($e_ID);

                        $e_IDs->appendChild(sourceType($ccr, $sourceID));

                        $e_Type = $ccr->createElement('Type');
                        $e_Medical->appendChild($e_Type);

                        $e_Text = $ccr->createElement('Text', $medication_list['PatientMedicationList']['medication']);
                        $e_Type->appendChild($e_Text);

                        $e_Description = $ccr->createElement('Description');
                        $e_Medical->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', $medication_list['PatientMedicationList']['diagnosis']);
                        $e_Description->appendChild($e_Text);

                        $e_Code = $ccr->createElement('Code');
                        $e_Description->appendChild($e_Code);

                        $e_Value = $ccr->createElement('Value', $medication_list['PatientMedicationList']['icd_code']);
                        $e_Code->appendChild($e_Value);

                        $e_Value = $ccr->createElement('CodingSystem', 'ICD9-CM');
                        $e_Code->appendChild($e_Value);

                        $e_Status = $ccr->createElement('Status');
                        $e_Medical->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', $medication_list['PatientMedicationList']['status']);
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_Medical->appendChild($e_Source);

                        $e_CommentID = $ccr->createElement('CommentID', $medication_list['PatientMedicationList']['source']);
                        $e_Medical->appendChild($e_CommentID);

                        $e_Episodes = $ccr->createElement('Episodes');
                        $e_Medical->appendChild($e_Episodes);

                        $e_Number = $ccr->createElement('Number');
                        $e_Episodes->appendChild($e_Number);

                        $e_Episode = $ccr->createElement('Episode');
                        $e_Episodes->appendChild($e_Episode);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', 'EP' . $pCount);
                        $e_Episode->appendChild($e_CCRDataObjectID);

                        $e_Episode->appendChild(sourceType($ccr, $sourceID));

                        $e_Episodes->appendChild(sourceType($ccr, $sourceID));

                        $e_HealthStatus = $ccr->createElement('HealthStatus');
                        $e_Medical->appendChild($e_HealthStatus);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_HealthStatus->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime');
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_Description = $ccr->createElement('Description');
                        $e_HealthStatus->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', $medication_list['PatientMedicationList']['diagnosis']);

                        $e_Description->appendChild($e_Text);

                        $e_HealthStatus->appendChild(sourceType($ccr, $sourceID));
                    endforeach;
                    $e_Body->appendChild($e_Medicals);
                }

                if ($patient_disclosure[10] == "true")
                {
                    /////////////// Referrals

                    $e_Referrals = $ccr->createElement('Referrals');
                    foreach ($plan_referrals as $plan_referral):

                        $e_Referral = $ccr->createElement('Referral');
                        $e_Referrals->appendChild($e_Referral);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_Referral->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_Referral->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($plan_referral['EncounterPlanReferral']['date_ordered'])));
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_Type = $ccr->createElement('Type');
                        $e_Referral->appendChild($e_Type);

                        $e_Text = $ccr->createElement('Text', $plan_referral['EncounterPlanReferral']['referred_to']);
                        $e_Type->appendChild($e_Text);

                        $e_Description = $ccr->createElement('Description');
                        $e_Referral->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', $plan_referral['EncounterPlanReferral']['diagnosis']);
                        $e_Description->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_Referral->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_Referrals);
                }

                if ($patient_disclosure[11] == "true")
                {
                    /////////////// Health Maintenances

                    $e_HealthMaintenances = $ccr->createElement('HealthMaintenances');
                    foreach ($plan_health_maintenance as $plan_health_maintenance):

                        $e_HealthMaintenance = $ccr->createElement('HealthMaintenance');
                        $e_HealthMaintenances->appendChild($e_HealthMaintenance);

                        $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
                        $e_HealthMaintenance->appendChild($e_CCRDataObjectID);

                        $e_DateTime = $ccr->createElement('DateTime');
                        $e_Lab->appendChild($e_DateTime);

                        $e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($plan_health_maintenance['EncounterPlanHealthMaintenance']['action_date'])));
                        $e_DateTime->appendChild($e_ExactDateTime);

                        $e_Type = $ccr->createElement('Type');
                        $e_HealthMaintenance->appendChild($e_Type);

                        $e_Text = $ccr->createElement('Text', $plan_health_maintenance['EncounterPlanHealthMaintenance']['plan_name']);
                        $e_Type->appendChild($e_Text);

                        $e_Description = $ccr->createElement('Description');
                        $e_HealthMaintenance->appendChild($e_Description);

                        $e_Text = $ccr->createElement('Text', '');
                        $e_Description->appendChild($e_Text);

                        $e_Status = $ccr->createElement('Status');
                        $e_HealthMaintenance->appendChild($e_Status);

                        $e_Text = $ccr->createElement('Text', $plan_health_maintenance['EncounterPlanHealthMaintenance']['status']);
                        $e_Status->appendChild($e_Text);

                        $e_Source = $ccr->createElement('Source');

                        $e_Actor = $ccr->createElement('Actor');
                        $e_Source->appendChild($e_Actor);

                        $e_ActorID = $ccr->createElement('ActorID', $uuid);
                        $e_Actor->appendChild($e_ActorID);

                        $e_HealthMaintenance->appendChild($e_Source);
                    endforeach;
                    $e_Body->appendChild($e_HealthMaintenances);
                }


                /////////////// Actors

                $e_Actors = $ccr->createElement('Actors');

                if ($patient_disclosure[0] == "true")
                {
                    $e_Actor = $ccr->createElement('Actor');
                    $e_Actors->appendChild($e_Actor);

                    $e_ActorObjectID = $ccr->createElement('ActorObjectID', 'A1234');
                    $e_Actor->appendChild($e_ActorObjectID);

                    $e_Person = $ccr->createElement('Person');
                    $e_Actor->appendChild($e_Person);

                    $e_Name = $ccr->createElement('Name');
                    $e_Person->appendChild($e_Name);

                    $e_CurrentName = $ccr->createElement('CurrentName');
                    $e_Name->appendChild($e_CurrentName);

                    $e_Given = $ccr->createElement('Given', $patient['PatientDemographic']['first_name']);
                    $e_CurrentName->appendChild($e_Given);

                    $e_Family = $ccr->createElement('Family', $patient['PatientDemographic']['last_name']);
                    $e_CurrentName->appendChild($e_Family);

                    $e_Suffix = $ccr->createElement('Suffix');
                    $e_CurrentName->appendChild($e_Suffix);

                    $e_DateOfBirth = $ccr->createElement('DateOfBirth');
                    $e_Person->appendChild($e_DateOfBirth);

                    $e_ExactDateTime = $ccr->createElement('ExactDateTime', $patient['PatientDemographic']['dob']);
                    $e_DateOfBirth->appendChild($e_ExactDateTime);

                    $e_Gender = $ccr->createElement('Gender');
                    $e_Person->appendChild($e_Gender);

                    $e_Text = $ccr->createElement('Text', $patient['PatientDemographic']['gender']);
                    $e_Gender->appendChild($e_Text);

                    $e_Code = $ccr->createElement('Code');
                    $e_Gender->appendChild($e_Code);

                    $e_Value = $ccr->createElement('Value');
                    $e_Code->appendChild($e_Value);

                    $e_IDs = $ccr->createElement('IDs');
                    $e_Actor->appendChild($e_IDs);

                    $e_Type = $ccr->createElement('Type');
                    $e_IDs->appendChild($e_Type);

                    $e_Text = $ccr->createElement('Text', 'Patient ID');
                    $e_Type->appendChild($e_Text);

                    $e_ID = $ccr->createElement('ID', $patient['PatientDemographic']['patient_id']);
                    $e_IDs->appendChild($e_ID);

                    $e_Source = $ccr->createElement('Source');
                    $e_IDs->appendChild($e_Source);

                    $e_SourceActor = $ccr->createElement('Actor');
                    $e_Source->appendChild($e_SourceActor);

                    $e_ActorID = $ccr->createElement('ActorID', getUuid());
                    $e_SourceActor->appendChild($e_ActorID);

                    // address
                    $e_Address = $ccr->createElement('Address');
                    $e_Actor->appendChild($e_Address);

                    $e_Type = $ccr->createElement('Type');
                    $e_Address->appendChild($e_Type);

                    $e_Text = $ccr->createElement('Text', 'H');
                    $e_Type->appendChild($e_Text);

                    $e_Line1 = $ccr->createElement('Line1', $patient['PatientDemographic']['address1']);
                    $e_Address->appendChild($e_Line1);

                    $e_Line2 = $ccr->createElement('Line2');
                    $e_Address->appendChild($e_Line1);

                    $e_City = $ccr->createElement('City', $patient['PatientDemographic']['city']);
                    $e_Address->appendChild($e_City);

                    $e_State = $ccr->createElement('State', $patient['PatientDemographic']['state']);
                    $e_Address->appendChild($e_State);

                    $e_PostalCode = $ccr->createElement('PostalCode', $patient['PatientDemographic']['zipcode']);
                    $e_Address->appendChild($e_PostalCode);

                    $e_Telephone = $ccr->createElement('Telephone');
                    $e_Actor->appendChild($e_Telephone);

                    $e_Value = $ccr->createElement('Value', $patient['PatientDemographic']['home_phone']);
                    $e_Telephone->appendChild($e_Value);

                    $e_Source = $ccr->createElement('Source');
                    $e_Actor->appendChild($e_Source);

                    $e_Actor = $ccr->createElement('Actor');
                    $e_Source->appendChild($e_Actor);

                    $e_ActorID = $ccr->createElement('ActorID', $authorID);
                    $e_Actor->appendChild($e_ActorID);
                }


                //////// Actor Information Systems
                $e_Actor = $ccr->createElement('Actor');
                $e_Actors->appendChild($e_Actor);

                $e_ActorObjectID = $ccr->createElement('ActorObjectID', $authorID);
                $e_Actor->appendChild($e_ActorObjectID);

                $e_InformationSystem = $ccr->createElement('InformationSystem');
                $e_Actor->appendChild($e_InformationSystem);

                $e_Name = $ccr->createElement('Name', $user['firstname']." ".$user['lastname']);
                $e_InformationSystem->appendChild($e_Name);

                $e_Type = $ccr->createElement('Type', 'Facility');
                $e_InformationSystem->appendChild($e_Type);

                $e_IDs = $ccr->createElement('IDs');
                $e_Actor->appendChild($e_IDs);

                $e_Type = $ccr->createElement('Type');
                $e_IDs->appendChild($e_Type);

                $e_Text = $ccr->createElement('Text', '');
                $e_Type->appendChild($e_Text);

                $e_ID = $ccr->createElement('ID', '');
                $e_IDs->appendChild($e_ID);

                $e_Source = $ccr->createElement('Source');
                $e_IDs->appendChild($e_Source);

                $e_SourceActor = $ccr->createElement('Actor');
                $e_Source->appendChild($e_SourceActor);

                $e_ActorID = $ccr->createElement('ActorID', $authorID);
                $e_SourceActor->appendChild($e_ActorID);

                $e_Address = $ccr->createElement('Address');
                $e_Actor->appendChild($e_Address);

                $e_Type = $ccr->createElement('Type');
                $e_Address->appendChild($e_Type);

                $e_Text = $ccr->createElement('Text', 'WP');
                $e_Type->appendChild($e_Text);

                $location_address_line_1 = (isset($location['PracticeLocation']['address_line_1'])) ? $location['PracticeLocation']['address_line_1'] : '';
                $e_Line1 = $ccr->createElement('Line1', $location_address_line_1);
                $e_Address->appendChild($e_Line1);

                $e_Line2 = $ccr->createElement('Line2');
                $e_Address->appendChild($e_Line1);

                $location_city = (isset($location['PracticeLocation']['city'])) ? $location['PracticeLocation']['city'] : '';
                $e_City = $ccr->createElement('City', $location_city);
                $e_Address->appendChild($e_City);

                $location_state = (isset($location['PracticeLocation']['state'])) ? $location['PracticeLocation']['state'] : '';
                $e_State = $ccr->createElement('State', $location_state.' ');
                $e_Address->appendChild($e_State);

                $location_zip = (isset($location['PracticeLocation']['zip'])) ? $location['PracticeLocation']['zip'] : '';
                $e_PostalCode = $ccr->createElement('PostalCode', $location_zip);
                $e_Address->appendChild($e_PostalCode);

                $e_Telephone = $ccr->createElement('Telephone');
                $e_Actor->appendChild($e_Telephone);

                $e_Phone = $ccr->createElement('Value', $user['work_phone']);
                $e_Telephone->appendChild($e_Phone);

                $e_Source = $ccr->createElement('Source');
                $e_Actor->appendChild($e_Source);

                $e_Actor = $ccr->createElement('Actor');
                $e_Source->appendChild($e_Actor);

                $e_ActorID = $ccr->createElement('ActorID', $authorID);
                $e_Actor->appendChild($e_ActorID);
                $e_ccr->appendChild($e_Actors);


                //////// Actor Information Systems
                $e_Actor = $ccr->createElement('Actor');
                $e_Actors->appendChild($e_Actor);

                $e_ActorObjectID = $ccr->createElement('ActorObjectID', $oemrID);
                $e_Actor->appendChild($e_ActorObjectID);

                $e_InformationSystem = $ccr->createElement('InformationSystem');
                $e_Actor->appendChild($e_InformationSystem);

                $e_Name = $ccr->createElement('Name', 'OTEMR');
                $e_InformationSystem->appendChild($e_Name);

                $e_Type = $ccr->createElement('Type', 'OneTouchEMR');
                $e_InformationSystem->appendChild($e_Type);

                $e_Version = $ccr->createElement('Version', '1.x');
                $e_InformationSystem->appendChild($e_Version);

                $e_IDs = $ccr->createElement('IDs');
                $e_Actor->appendChild($e_IDs);

                $e_Type = $ccr->createElement('Type');
                $e_IDs->appendChild($e_Type);

                $e_Text = $ccr->createElement('Text', 'Certification #');
                $e_Type->appendChild($e_Text);

                $e_ID = $ccr->createElement('ID', 'EHRX-OTEMRXXXXXX-2011');
                $e_IDs->appendChild($e_ID);

                $e_Source = $ccr->createElement('Source');
                $e_IDs->appendChild($e_Source);

                $e_SourceActor = $ccr->createElement('Actor');
                $e_Source->appendChild($e_SourceActor);

                $e_ActorID = $ccr->createElement('ActorID', $authorID);
                $e_SourceActor->appendChild($e_ActorID);

                $e_Address = $ccr->createElement('Address');
                $e_Actor->appendChild($e_Address);

                $e_Type = $ccr->createElement('Type');
                $e_Address->appendChild($e_Type);

                $e_Text = $ccr->createElement('Text', 'WP');
                $e_Type->appendChild($e_Text);

                $e_Line1 = $ccr->createElement('Line1','2365 Springs Rd. NE');
                $e_Address->appendChild($e_Line1);

                $e_Line2 = $ccr->createElement('Line2');
                $e_Address->appendChild($e_Line1);

                $e_City = $ccr->createElement('City','Hickory');
                $e_Address->appendChild($e_City);

                $e_State = $ccr->createElement('State','NC ');
                $e_Address->appendChild($e_State);

                $e_PostalCode = $ccr->createElement('PostalCode','28601');
                $e_Address->appendChild($e_PostalCode);

                $e_Telephone = $ccr->createElement('Telephone');
                $e_Actor->appendChild($e_Telephone);

                $e_Phone = $ccr->createElement('Value','000-000-0000');
                $e_Telephone->appendChild($e_Phone);

                $e_Source = $ccr->createElement('Source');
                $e_Actor->appendChild($e_Source);

                $e_Actor = $ccr->createElement('Actor');
                $e_Source->appendChild($e_Actor);

                $e_ActorID = $ccr->createElement('ActorID', $authorID);
                $e_Actor->appendChild($e_ActorID);

                $ccr->preserveWhiteSpace = false;
                $ccr->formatOutput = true;

                if($ccr_mode != 'yes')
				{
					header("Content-type: application/xml");
					echo $ccr->saveXml();
				}
				else
				{
					$output = $ccr->saveXml();
					
					$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
					$disclosure_id = (isset($this->params['named']['disclosure_id'])) ? $this->params['named']['disclosure_id'] : "";
					$filename = 'disclosure_'.$patient_id.'_'.$disclosure_id. '.xml';
					$output_as_file = "true";
					if($output_as_file == "true")
					{
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename=' . $filename);
						header('Content-Transfer-Encoding: binary');
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
						header('Content-Length: ' . strlen($output));
						
						echo $output;
						exit;
					}
			
			        return $output;
	  	        }
/*
                // save the raw xml
                $main_xml = $ccr->saveXml();

                // save the stylesheet
                $main_stylesheet = file_get_contents(substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], "//") + 2).$_SERVER['HTTP_HOST'].str_replace("index.php", "", $_SERVER['PHP_SELF']).'ccr/patient_ccr.xsl');

                // replace stylesheet link in raw xml file
                $substitute_string = '<?xml-stylesheet type="text/xsl" href="#style1"?>
<!DOCTYPE ContinuityOfCareRecord [
<!ATTLIST xsl:stylesheet id ID #REQUIRED>
]>
';
                $replace_string = '<?xml-stylesheet type="text/xsl" href="stylesheet/ccr.xsl"?>';
                $main_xml = str_replace($replace_string,$substitute_string,$main_xml);

                // remove redundant xml declaration from stylesheet
                $replace_string = '<?xml version="1.0" encoding="UTF-8"?>';
                $main_stylesheet = str_replace($replace_string,'',$main_stylesheet);

                // embed the stylesheet in the raw xml file
                $replace_string ='<ContinuityOfCareRecord xmlns="urn:astm-org:CCR">';
                $main_stylesheet = $replace_string.$main_stylesheet;
                $main_xml = str_replace($replace_string,$main_stylesheet,$main_xml);

                // insert style1 id into the stylesheet parameter
                $substitute_string = 'xsl:stylesheet id="style1" exclude-result-prefixes';
                $replace_string = 'xsl:stylesheet exclude-result-prefixes';
                $main_xml = str_replace($replace_string,$substitute_string,$main_xml);

                // prepare the filename to use
                //   LASTNAME-FIRSTNAME-PID-DATESTAMP-ccr.xml
                $main_filename = $patient['mrn'].'_Medical_Records.xml';

                // send the output as a file to the user
                header("Content-type: text/xml");
                header("Content-Disposition: attachment; filename=" . $main_filename . "");

                echo $main_xml;
*/
                exit();
            } break;
            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->PatientDisclosure->create();
                    $this->PatientDisclosure->save($this->data);

                    $ret = array();
                    echo json_encode($ret);

                    $this->PatientDisclosure->saveAudit('New');

                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->PatientDisclosure->save($this->data);

                    $ret = array();
                    echo json_encode($ret);

                    $this->PatientDisclosure->saveAudit('Update');

                    exit;
                }
                else
                {
                    $items = $this->PatientDisclosure->find(
                            'first',
                            array(
                                'conditions' => array('PatientDisclosure.disclosure_id' => $disclosure_id)
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
                    $ids = $this->data['PatientDisclosure']['disclosure_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientDisclosure->delete($id, false);
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->PatientDisclosure->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
			    $this->paginate['PatientDisclosure'] = array(
                'conditions' => array('PatientDisclosure.patient_id' => $patient_id),
			    'order' => array('PatientDisclosure.service_date' => 'DESC')
                );
                $this->set('disclosure_records', $this->sanitizeHTML($this->paginate('PatientDisclosure')));
                //$this->set('disclosure_records', $this->sanitizeHTML($this->paginate('PatientDisclosure', array('patient_id' => $patient_id))));
                $this->PatientDisclosure->saveAudit('View');
            }
        }
    }


    public function hx_medical()
    {
        $this->layout = "blank";
        $this->loadModel("PatientMedicalHistory");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $this->loadModel("PatientProblemList");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        //$this->set("Icd9", $this->sanitizeHTML($this->Icd9->find('all')));
        //$this->set('PatientMedicalHistory', $this->sanitizeHTML($this->PatientMedicalHistory->find('all')));

				$this->loadModel('PatientDemographic');
				$patient_data = $this->PatientDemographic->getPatient($patient_id);
				$this->set(compact('patient_data'));
				
        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientMedicalHistory']['patient_id'] = $patient_id;
			$this->data['PatientMedicalHistory']['encounter_id'] = 0;
            /*$this->data['PatientMedicalHistory']['diagnosis'] = $this->data['PatientMedicalHistory']['diagnosis'];
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
            case "load_Icd9_autocomplete":
            {
                if (!empty($this->data))
                {
                    $this->Icd->execute($this, $task);
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
                        $this->data['PatientProblemList']['status'] = $this->data['PatientMedicalHistory']['status'];
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
									'PatientProblemList.diagnosis' => $diagnosis,
									'PatientProblemList.icd_code' => $icd9,
									'patient_id' => $patient_id
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

                    $ret = array();
                    echo json_encode($ret);
                    exit;
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
                        $this->data['PatientProblemList']['status'] = $this->data['PatientMedicalHistory']['status'];
                        $this->data['PatientProblemList']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['PatientProblemList']['modified_user_id'] = $this->user_id;
						$prob = $this->PatientProblemList->find('count', array(
							'conditions' => array(
								'PatientProblemList.diagnosis' => $this->data['PatientMedicalHistory']['diagnosis'],
								'PatientProblemList.icd_code' => $this->data['PatientMedicalHistory']['icd_code'],
								'patient_id' => $patient_id
							),
						));
						// Not yet in problem list
						if (!$prob) 
						{
							$this->PatientProblemList->create();
							$this->PatientProblemList->save($this->data);
						}
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

                    $ret = array();
                    echo json_encode($ret);
                    exit;
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
                    }
                }

                echo json_encode($ret);
                exit;
            }break;
            default:
            {
			    $this->paginate['PatientMedicalHistory'] = array(
                'conditions' => array('PatientMedicalHistory.patient_id' => $patient_id),
			    'order' => array('PatientMedicalHistory.modified_timestamp' => 'DESC')
                );
                $this->set('PatientMedicalHistory', $this->sanitizeHTML($this->paginate('PatientMedicalHistory')));
               //$this->set('PatientMedicalHistory', $this->sanitizeHTML($this->paginate('PatientMedicalHistory', array('patient_id' => $patient_id))));

                $this->PatientMedicalHistory->saveAudit('View');
            }
        }
		
		$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);     
				$this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);

		$this->loadModel("PatientDemographic");
        $PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $PatientDemographic['gender']);   
        
                $this->loadModel("FavoriteMedical");
	$favitems = $this->FavoriteMedical->find('all', array(
      'conditions' => array('FavoriteMedical.user_id' => $_SESSION['UserAccount']['user_id']),
      'order' => array('FavoriteMedical.diagnosis ASC'),
      
      ));
        $this->set('favitems', $this->sanitizeHTML($favitems));  
    }

    public function hx_surgical()
    {
        $this->layout = "blank";
        $this->loadModel("PatientSurgicalHistory");
       // $this->loadModel("Icd9");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
       // $this->set("Icd9", $this->sanitizeHTML($this->Icd9->find('all')));
        //$this->set('PatientSurgicalHistory', $this->sanitizeHTML($this->PatientSurgicalHistory->find('all')));

				$this->loadModel('PatientDemographic');
				$patient_data = $this->PatientDemographic->getPatient($patient_id);
				$this->set(compact('patient_data'));
				
        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientSurgicalHistory']['patient_id'] = $patient_id;
			$this->data['PatientSurgicalHistory']['encounter_id'] = 0;
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
            case "load_Icd9_autocomplete":
            {

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
                    $ret = array();
                    echo json_encode($ret);

                    $this->PatientSurgicalHistory->saveAudit('New');

                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->PatientSurgicalHistory->save($this->data);

                    $ret = array();
                    echo json_encode($ret);

                    $this->PatientSurgicalHistory->saveAudit('Update');

                    exit;
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
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
			    $this->paginate['PatientSurgicalHistory'] = array(
                'conditions' => array('PatientSurgicalHistory.patient_id' => $patient_id),
			    'order' => array('PatientSurgicalHistory.modified_timestamp' => 'DESC')
                );

                $this->set('PatientSurgicalHistory', $this->sanitizeHTML($this->paginate('PatientSurgicalHistory')));
                //$this->set('PatientSurgicalHistory', $this->sanitizeHTML($this->paginate('PatientSurgicalHistory', array('patient_id' => $patient_id))));

                $this->PatientSurgicalHistory->saveAudit('View');
            }
        }
		
		$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
				$this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);

		$this->loadModel("PatientDemographic");
        $PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $PatientDemographic['gender']); 
		if($task == 'addnew' || $task == 'edit')
		{
			$this->loadModel("FavoriteSurgeries");
			$favitems = $this->FavoriteSurgeries->find('all', array(
				'fields' => 'distinct(surgeries)',
				'conditions' => array(
					'FavoriteSurgeries.user_id' => $this->user_id,
				),
        'order' => array(
            'FavoriteSurgeries.surgeries ASC'
        ),
			));
			$this->set('favitems', $favitems);  
		}    
    }
	
    public function hx_social()
    {
        $this->layout = "blank";
        $this->loadModel("PatientSocialHistory");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        
        $this->loadModel("MaritalStatus");
        $this->set("MaritalStatus", $this->sanitizeHTML($this->MaritalStatus->find('all')));
        
        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientSocialHistory']['pets'] = ((isset($this->params['form']['pets_option_1'])) ? $this->params['form']['pets_option_1'] : "") . "|" . ((isset($this->params['form']['pets_option_2'])) ? $this->params['form']['pets_option_2'] : "") . "|" . ((isset($this->params['form']['pets_option_3'])) ? $this->params['form']['pets_option_3'] : "") . "|" . ((isset($this->params['form']['pets_option_4'])) ? $this->params['form']['pets_option_4'] : "") . "|" . ((isset($this->params['form']['pets_option_5'])) ? $this->params['form']['pets_option_5'] : "");
            $this->data['PatientSocialHistory']['patient_id'] = $patient_id;
			$this->data['PatientSocialHistory']['encounter_id'] = 0;
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
                    $this->PatientSocialHistory->create();
                    $this->PatientSocialHistory->save($this->data);

                    $this->PatientSocialHistory->saveAudit('New');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->PatientSocialHistory->save($this->data);

                    $this->PatientSocialHistory->saveAudit('Update');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $social_history_id = (isset($this->params['named']['social_history_id'])) ? $this->params['named']['social_history_id'] : "";
                    //echo $social_history_id;
                    $items = $this->PatientSocialHistory->find(
					'first',
						array(
							'conditions' => array('PatientSocialHistory.social_history_id' => $social_history_id)
						)
                    );
		    $last_user_inf = array(
            'role_id' => '',
            'full_name' => '(User no longer in the system)',
            
        );
		    // who was last person to edit this record?			
		    $last_user=$this->UserAccount->getUserByID($items['PatientSocialHistory']['modified_user_id']);
        
        if ($last_user) {
          $last_user_inf['role_id']=$last_user->role_id;
          $last_user_inf['full_name']=$last_user->full_name;          
        }
        
        
		    $this->set('last_user',$last_user_inf);

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
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default: {
                $this->paginate['PatientSocialHistory'] = array(
                	'conditions' => array('PatientSocialHistory.patient_id' => $patient_id),
                	'order' => array('PatientSocialHistory.modified_timestamp' => 'DESC')
                );
                if(isset($this->params['named']['sort']) && $this->params['named']['sort']=='status'){
                	$this->PatientSocialHistory->virtualFields['status']= "(
										CASE 1
										WHEN TYPE = 'Marital Status' THEN marital_status
										WHEN TYPE = 'Occupation' THEN occupation
										WHEN TYPE = 'Living Arrangement' THEN living_arrangement
										WHEN TYPE = 'Activities' THEN routine_status
										WHEN TYPE = 'Pets' THEN replace(replace(pets,'|',', '), ', ','')
										WHEN consumption_status != '' THEN consumption_status
										ELSE smoking_status
										END
                  )";
                }
                $PatientSocialHistory = $this->sanitizeHTML($this->paginate('PatientSocialHistory'));
                $this->set('PatientSocialHistory', $PatientSocialHistory);
                $this->PatientSocialHistory->saveAudit('View');
            }
        }
		
		$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
				$this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);

		$this->loadModel("PatientDemographic");
        $PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $PatientDemographic['gender']);     
    }

    public function hx_family()
    {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientFamilyHistory");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        //$this->set('PatientFamilyHistory', $this->sanitizeHTML($this->PatientFamilyHistory->find('all')));
        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientFamilyHistory']['patient_id'] = $patient_id;
			$this->data['PatientFamilyHistory']['encounter_id'] = 0;
            $this->data['PatientFamilyHistory']['modified_user_id'] =  $this->user_id;
            $this->data['PatientFamilyHistory']['modified_timestamp'] =  __date("Y-m-d H:i:s");
        }
        switch($task)
        {
            case "load_relationship":
            { 
		$showall = (isset($this->params['named']['showall'])) ? $this->params['named']['showall'] :"";

                 $search_keyword = $this->data['autocomplete']['keyword'];
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
                 $search_keyword = $this->data['autocomplete']['keyword'];
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
                if(!empty($this->data))
                {
                    $this->PatientFamilyHistory->create();
                    $this->PatientFamilyHistory->save($this->data);

                    $this->PatientFamilyHistory->saveAudit('New');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
		$this->loadModel("FavoriteMedical");
		$favitems = $this->FavoriteMedical->find('all', array(
        'conditions' => array('FavoriteMedical.user_id' => $_SESSION['UserAccount']['user_id']),
        'order' => array(
            'FavoriteMedical.diagnosis ASC',
        ),
        ));
           	$this->set('favitems', $this->sanitizeHTML($favitems));

            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->PatientFamilyHistory->save($this->data);

                    $this->PatientFamilyHistory->saveAudit('Update');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
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

		    $last_user_inf = array(
            'role_id' => '',
            'full_name' => '(User no longer in the system)',
            
        );
        
// who was last person to edit this record?			
		    $last_user=$this->UserAccount->getUserByID($items['PatientFamilyHistory']['modified_user_id']);
        
        if ($last_user) {
          $last_user_inf['role_id']=$last_user->role_id;
          $last_user_inf['full_name']=$last_user->full_name;
        }
        
		    $this->set('last_user',$last_user_inf);

                    $this->set('EditItem', $this->sanitizeHTML($items));

                   $this->loadModel("FavoriteMedical");
                   $favitems = $this->FavoriteMedical->find('all', array(
                       'conditions' => array('FavoriteMedical.user_id' => $_SESSION['UserAccount']['user_id']),
                        'order' => array(
                            'FavoriteMedical.diagnosis ASC',
                        ),                       
                       ));
                   $this->set('favitems', $this->sanitizeHTML($favitems));

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
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
			    $this->paginate['PatientFamilyHistory'] = array(
                'conditions' => array('PatientFamilyHistory.patient_id' => $patient_id),
			    'order' => array('PatientFamilyHistory.modified_timestamp' => 'DESC')
                );
				
                $this->set('PatientFamilyHistory', $this->sanitizeHTML($this->paginate('PatientFamilyHistory')));
                //$this->set('PatientFamilyHistory', $this->sanitizeHTML($this->paginate('PatientFamilyHistory', array('patient_id' => $patient_id))));

                $this->PatientFamilyHistory->saveAudit('View');
            }
        }
		
		$this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
				$this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);

		$this->loadModel("PatientDemographic");
        $PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
        $this->set('gender', $PatientDemographic['gender']);     
    }

    public function hx_obgyn()
    {
				$this->loadModel("PracticeProfile");
				$PracticeProfile = $this->PracticeProfile->find('first');
				$this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
				$this->set('obgyn_feature_include_flag', $PracticeProfile['PracticeProfile']['obgyn_feature_include_flag']);
			
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientObGynHistory");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        //$this->set('PatientObGynHistory', $this->sanitizeHTML($this->PatientObGynHistory->find('all')));

				$this->loadModel('PatientDemographic');
				$patient_data = $this->PatientDemographic->getPatient($patient_id);
				$this->set(compact('patient_data'));
				
		if($task == "addnew" || $task == "edit")
		{
		        $this->loadModel("PracticeProfile");
				$PracticeProfile = $this->PracticeProfile->find('first');
				$this->set('type_of_practice', $PracticeProfile['PracticeProfile']['type_of_practice']);
		
				$this->loadModel("PatientDemographic");
				$PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
				$this->set('gender', $PatientDemographic['gender']);
		}
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
				unset($this->data['PatientObGynHistory']['type_of_delivery']);
				unset($this->data['PatientObGynHistory']['delivery_weight']);
				unset($this->data['PatientObGynHistory']['delivery_date']);
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
				unset($this->data['PatientObGynHistory']['type_of_delivery']);
				unset($this->data['PatientObGynHistory']['delivery_weight']);
				unset($this->data['PatientObGynHistory']['delivery_date']);
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
				
				
				if(isset($this->data['PatientObGynHistory']['type_of_delivery'])){
					$length = count($this->data['PatientObGynHistory']['type_of_delivery']);
				
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

                    $ret = array();
                    echo json_encode($ret);
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

                echo json_encode($ret);
                exit;
            }
            default:
            {
			    $this->paginate['PatientObGynHistory'] = array(
                'conditions' => array('PatientObGynHistory.patient_id' => $patient_id),
			    'order' => array('PatientObGynHistory.modified_timestamp' => 'DESC')
                );
				
                $this->set('PatientObGynHistory', $this->sanitizeHTML($this->paginate('PatientObGynHistory')));
                //$this->set('PatientObGynHistory', $this->sanitizeHTML($this->paginate('PatientObGynHistory', array('patient_id' => $patient_id))));

                $this->PatientObGynHistory->saveAudit('View');
				
		
				$this->loadModel("PatientDemographic");
				$PatientDemographic = $this->PatientDemographic->getPatient($patient_id);
				$this->set('gender', $PatientDemographic['gender']);
            }
        }
	}

    public function allergies()
    {
        $this->loadModel("PatientDemographic");
        $this->layout = "blank";
        $practice_settings = $this->Session->read("PracticeSetting");
        $labs_setup =  $practice_settings['PracticeSetting']['labs_setup'];
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $this->loadModel("PatientAllergy");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        //$this->set('PatientAllergy', $this->sanitizeHTML($this->PatientAllergy->find('all')));
		
		$dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
		$dosespot_xml_api = new Dosespot_XML_API();
		$emdeon_xml_api = new Emdeon_XML_API();
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
		if($task == "addnew" || $task == "edit")
		{
			$patient_mrn = $this->PatientDemographic->getPatient($patient_id);
			$mrn = $patient_mrn['mrn'];
			$this->set('mrn', $this->sanitizeHTML($mrn));
		}
        switch($task)
        {
            case "addnew":
            {
                if(!empty($this->data))
                {
					if( $practice_settings['PracticeSetting']['rx_setup'] == 'Electronic_Emdeon' )
					{ // Emdeon patient allergies 
                    				$this->PatientAllergy->saveAllergy($this->data);
					}
				if(!empty($dosespot_patient_id))
				{
					//Add Allergy data to Dosespot 
					if (!isset($this->data['PatientAllergy']['allergy_code'])) {
						$this->data['PatientAllergy']['allergy_code'] = '3';
					}
					
					if (!isset($this->data['PatientAllergy']['allergy_code_type'])) {
						$this->data['PatientAllergy']['allergy_code_type'] = 'AllergyClass';
					}
				  $added_allergy_item = $dosespot_xml_api->executeAddAllergy($dosespot_patient_id, $this->data['PatientAllergy']);
				}

                    $this->data['PatientAllergy']['dosespot_allergy_id'] = isset($added_allergy_item['PatientAllergyID'])?($added_allergy_item['PatientAllergyID']):0;
                    $this->PatientAllergy->save($this->data);               

                    $ret = array();
                    echo json_encode($ret);
                    $this->PatientAllergy->saveAudit('New');
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    if($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection())
			{
                    		$this->PatientAllergy->saveAllergy($this->data);
			}
			else
			{
                        	$this->PatientAllergy->save($this->data);
                    	}
			$PracticeSetting = $this->Session->read("PracticeSetting");
			if(!empty($this->data['PatientAllergy']['dosespot_allergy_id']) and $PracticeSetting['PracticeSetting']['rx_setup']== 'Electronic_Dosespot'  )
			{
				$dosespot_xml_api = new Dosespot_XML_API();
				$dosespot_xml_api->executeEditAllergy($dosespot_patient_id, $this->data['PatientAllergy']);
			}
					
                    $this->PatientAllergy->saveAudit('Update');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
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

            case "import_allery_from_surescripts":
            {                
		$PracticeSetting = $this->Session->read("PracticeSetting");
		if( $PracticeSetting['PracticeSetting']['rx_setup']== 'Electronic_Dosespot' )
		{
                //If the patient not exists in Dosespot, add the patient to Dosespot
				if($dosespot_patient_id == 0 or $dosespot_patient_id == '')
				{					
					$this->PatientDemographic->updateDosespotPatient($patient_id);					
					$dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
				}
				
				$allergy_items = $dosespot_xml_api->getAllergyList($dosespot_patient_id);

                foreach ($allergy_items as $allergy_item)
                {
                    $dosespot_allergy_id = $allergy_item['PatientAllergyId'];
                    $items = $this->PatientAllergy->find('first', array('conditions' => array('PatientAllergy.dosespot_allergy_id' => $dosespot_allergy_id)));

                    if(empty($items))
                    {
                        $this->data = array();
                        $this->data['PatientAllergy']['patient_id'] = $patient_id;
                        $this->data['PatientAllergy']['dosespot_allergy_id'] = $dosespot_allergy_id;
                        $this->data['PatientAllergy']['agent'] = $allergy_item['agent'];
                        $this->data['PatientAllergy']['reaction_count'] = 1;
                        $this->data['PatientAllergy']['reaction1'] = $allergy_item['reaction1']?$allergy_item['reaction1']:'';
                        $this->data['PatientAllergy']['status'] = $allergy_item['status'];
                        $this->data['PatientAllergy']['modified_user_id'] =  $this->user_id;
                        $this->data['PatientAllergy']['modified_timestamp'] =  __date("Y-m-d H:i:s");
                        $this->PatientAllergy->create();
                        $this->PatientAllergy->save($this->data);
                        $this->PatientAllergy->saveAudit('New');
                    }
                    else
                    {
                        $this->data['PatientAllergy']['allergy_id'] = $items['PatientAllergy']['allergy_id'];
                        $this->data['PatientAllergy']['patient_id'] = $patient_id;
                        $this->data['PatientAllergy']['dosespot_allergy_id'] = $dosespot_allergy_id;
                        $this->data['PatientAllergy']['agent'] = $allergy_item['agent'];
                        $this->data['PatientAllergy']['reaction_count'] = 1;
                        $this->data['PatientAllergy']['reaction1'] = $allergy_item['reaction1']?$allergy_item['reaction1']:'';
                        $this->data['PatientAllergy']['status'] = $allergy_item['status'];
                        $this->data['PatientAllergy']['modified_user_id'] =  $this->user_id;
                        $this->data['PatientAllergy']['modified_timestamp'] =  __date("Y-m-d H:i:s");
                        $this->PatientAllergy->save($this->data);
                        $this->PatientAllergy->saveAudit('Update');
                    }

                }
	     } //close if dosespot enabled
                $ret = array();
                echo json_encode($ret);
                exit;
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
					
					   if($labs_setup == 'Electronic' && $emdeon_xml_api->checkConnection())
						{
							$this->PatientAllergy->deleteAllergy($id);
						}
						else
						{
							$id_array = explode('|', $id);
						    $allergy_id = $id_array[0];
						    $dosespot_allergy_id = $id_array[1];
						    $this->PatientAllergy->delete($allergy_id, false);
						 }
                        				$PracticeSetting = $this->Session->read("PracticeSetting");
						    //Delete allergy in Dosespot
						    if(!empty($dosespot_allergy_id) and $PracticeSetting['PracticeSetting']['rx_setup']== 'Electronic_Dosespot')
						    {                       
							    $this->data['PatientAllergy']['dosespot_allergy_id'] = $dosespot_allergy_id;
							    $this->data['PatientAllergy']['reaction1'] = '';
							    $this->data['PatientAllergy']['status'] = 'Deleted';
							    $dosespot_xml_api = new Dosespot_XML_API();
							    $dosespot_xml_api->executeEditAllergy($dosespot_patient_id, $this->data['PatientAllergy']);
						    }
		
                       
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->PatientAllergy->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            case "markNone":
            {
                if(!empty($this->data))
                {
                    $this->data['PatientDemographic']['patient_id'] = $patient_id;
                    $this->data['PatientDemographic']['allergies_none'] = $this->data['submitted']['value'];
                    $this->PatientDemographic->save($this->data);
                }
                exit;
            } break;
            case "update_status":
            {
                $this->PatientAllergy->setItemValue("status", $this->data['submitted']['value'], $this->data['allergy_id'], $patient_id, $this->user_id);
                echo $this->data['submitted']['value'];
                exit;
            }break;
            default:
            {
                $demographic_items = $this->PatientDemographic->find('first',array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
        		$practice_settings = $this->Session->read("PracticeSetting");
	 		$rx_setup = $practice_settings['PracticeSetting']['rx_setup'];
	 		if($rx_setup == 'Electronic_Dosespot')
	 		{
	    			$dosespot_patient_id = $demographic_items['PatientDemographic']['dosespot_patient_id'];
	   			//If the patient not exists in Dosespot, add the patient to Dosespot
  	   			if(empty($dosespot_patient_id))
	   			{					
	     				$this->PatientDemographic->updateDosespotPatient($patient_id);					
	   			}
			}

                $allergies_none = $demographic_items['PatientDemographic']['allergies_none'];
                $this->set('allergies_none', $allergies_none);
				
				$show_all_allergies = (isset($this->params['named']['show_all_allergies'])) ? $this->params['named']['show_all_allergies'] : "yes";
				$show_history = (isset($this->params['named']['show_history'])) ? $this->params['named']['show_history'] : "yes";
				$show_patient_reported = (isset($this->params['named']['show_patient_reported'])) ? $this->params['named']['show_patient_reported'] : "yes";
				$show_practice_reported = (isset($this->params['named']['show_practice_reported'])) ? $this->params['named']['show_practice_reported'] : "yes";
				$this->set(compact('show_all_allergies', 'show_history', 'show_patient_reported', 'show_practice_reported'));
				
				$conditions = array();
				$conditions['PatientAllergy.patient_id'] = $patient_id;
				
				if($show_all_allergies == 'no')
				{
					$conditions['PatientAllergy.status'] = 'Active';
				}
				
				$sources_array = array();
				
				if($show_history == 'no')
				{
					$sources_array[] = 'Allergy History';
				}
				
				if($show_patient_reported == 'no')
				{
					$sources_array[] = 'Patient Reported';
				}
				
				if($show_practice_reported == 'no')
				{
					$sources_array[] = 'Practice Reported';
				}
				
				if(count($sources_array) > 0)
				{
					$sources_array[] = '';
					
					$conditions['PatientAllergy.source NOT '] = $sources_array;
				}
				
				$this->paginate['PatientAllergy'] = array(
                'conditions' => $conditions,
			    'order' => array('PatientAllergy.modified_timestamp' => 'DESC')
                );
				
                $this->set('PatientAllergy', $this->sanitizeHTML($this->paginate('PatientAllergy')));
				
				//$this->set('PatientAllergy', $this->sanitizeHTML($this->paginate('PatientAllergy', $conditions)));

                $this->PatientAllergy->saveAudit('View');
            }
        }
    }
	
    public function problem_list()
    {
        $this->layout = "blank";
        $this->loadModel("PatientProblemList");
        $this->loadModel("PatientDemographic");
        $this->loadModel("PatientMedicalHistory");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $show_all_problems = (isset($this->params['named']['show_all_problems'])) ? $this->params['named']['show_all_problems'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        //$this->set('PatientProblemList', $this->sanitizeHTML($this->PatientProblemList->find('all')));
        if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
        {
            $this->data['PatientProblemList']['patient_id'] = $patient_id;
			$this->data['PatientProblemList']['encounter_id'] = 0;
            $this->data['PatientProblemList']['diagnosis'] = $this->data['PatientProblemList']['diagnosis'];
            $this->data['PatientProblemList']['start_date'] = $this->data['PatientProblemList']['start_date']?__date("Y-m-d", strtotime($this->data['PatientProblemList']['start_date'])):'';
            $this->data['PatientProblemList']['end_date'] = $this->data['PatientProblemList']['end_date']?__date("Y-m-d", strtotime($this->data['PatientProblemList']['end_date'])):'';
            $this->data['PatientProblemList']['occurrence'] = $this->data['PatientProblemList']['occurrence'];
            $this->data['PatientProblemList']['comment'] = $this->data['PatientProblemList']['comment'];
            $this->data['PatientProblemList']['status'] = isset($this->data['PatientProblemList']['status'])?$this->data['PatientProblemList']['status']:'';
            $this->data['PatientProblemList']['action'] = isset($this->data['PatientProblemList']['action'])?'Moved':'';
            $this->data['PatientProblemList']['modified_timestamp'] = __date("Y-m-d H:i:s");
            $this->data['PatientProblemList']['modified_user_id'] = $this->user_id;
        }

        switch($task)
        {
            case "load_Icd9_autocomplete":
            {
                if (!empty($this->data))
                {
                    $this->Icd->execute($this, $task);
                }
                exit();
            } break;
            case "addnew":
            {
                if(!empty($this->data))
                {
                    if($this->data['PatientProblemList']['action'] == 'Moved')
                    {
                        $this->data['PatientMedicalHistory']['patient_id'] = $patient_id;
                        $this->data['PatientMedicalHistory']['diagnosis'] = $this->data['PatientProblemList']['diagnosis'];

                        if($this->data['PatientProblemList']['start_date']!='')
                        {
                            $splitted_start_date= explode('-',$this->data['PatientProblemList']['start_date']);
                            if($this->__global_date_format == 'Y-m-d')
                            {
                                list($start_day, $start_month, $start_year) = $splitted_start_date;
                            }
                            elseif($this->__global_date_format == 'd-m-Y')
                            {
                                list($start_year, $start_month, $start_day) = $splitted_start_date;
                            }
                            else
                            {
                                list($start_year, $start_day, $start_month) = $splitted_start_date;
                            }
                            $this->data['PatientMedicalHistory']['start_month'] = $start_month?$start_month:'';
                            $this->data['PatientMedicalHistory']['start_year'] = $start_year?$start_year:'';
                        }
                        if($this->data['PatientProblemList']['end_date']!='')
                        {
                            $splitted_end_date= explode('-',$this->data['PatientProblemList']['end_date']);
                            if($this->__global_date_format == 'Y-m-d')
                            {
                                list($end_day, $end_month, $end_year) = $splitted_end_date;
                            }
                            elseif($this->__global_date_format == 'd-m-Y')
                            {
                                list($end_year, $end_month, $end_day) = $splitted_end_date;
                            }
                            else
                            {
                                list($end_year, $end_day, $end_month) = $splitted_end_date;
                            }
                            $this->data['PatientMedicalHistory']['end_month'] = $end_month?$end_month:'';
                            $this->data['PatientMedicalHistory']['end_year'] = $end_year?$end_year:'';
                        }
                        $this->data['PatientMedicalHistory']['occurrence'] = $this->data['PatientProblemList']['occurrence'];
                        $this->data['PatientMedicalHistory']['comment'] = $this->data['PatientProblemList']['comment'];
                        $this->data['PatientMedicalHistory']['status'] = isset($this->data['PatientProblemList']['status'])?$this->data['PatientProblemList']['status']:'';
                        $this->data['PatientMedicalHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['PatientMedicalHistory']['modified_user_id'] = $this->user_id;
                        $this->PatientMedicalHistory->create();
                        $this->PatientMedicalHistory->save($this->data);

                        $this->PatientMedicalHistory->saveAudit('New');
                    }
                    else
                    {
                        $this->PatientProblemList->create();
                        $this->PatientProblemList->save($this->data);

                        $this->PatientProblemList->saveAudit('New');
                    }
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    if($this->data['PatientProblemList']['action'] == 'Moved')
                    {
                        $this->data['PatientMedicalHistory']['patient_id'] = $patient_id;
                        $this->data['PatientMedicalHistory']['diagnosis'] = $this->data['PatientProblemList']['diagnosis'];
                        $this->data['PatientMedicalHistory']['icd_code'] = $this->data['PatientProblemList']['icd_code'];
                        /*$this->data['PatientMedicalHistory']['start_date'] = $this->data['PatientProblemList']['start_date']?__date("Y-m-d", strtotime($this->data['PatientProblemList']['start_date'])):'';
                        $this->data['PatientMedicalHistory']['end_date'] = $this->data['PatientProblemList']['end_date']?__date("Y-m-d", strtotime($this->data['PatientProblemList']['end_date'])):'';*/
                        if($this->data['PatientProblemList']['start_date']!='')
                        {
                            $splitted_start_date= explode('-',$this->data['PatientProblemList']['start_date']);
                            if($this->__global_date_format == 'Y-m-d')
                            {
                                list($start_day, $start_month, $start_year) = $splitted_start_date;
                            }
                            elseif($this->__global_date_format == 'd-m-Y')
                            {
                                list($start_year, $start_month, $start_day) = $splitted_start_date;
                            }
                            else
                            {
                                list($start_year, $start_day, $start_month) = $splitted_start_date;
                            }
                            $this->data['PatientMedicalHistory']['start_month'] = $start_month?$start_month:'';
                            $this->data['PatientMedicalHistory']['start_year'] = $start_year?$start_year:'';
                        }
                        if($this->data['PatientProblemList']['end_date']!='')
                        {
                            $splitted_end_date= explode('-',$this->data['PatientProblemList']['end_date']);
                            if($this->__global_date_format == 'Y-m-d')
                            {
                                list($end_day, $end_month, $end_year) = $splitted_end_date;
                            }
                            elseif($this->__global_date_format == 'd-m-Y')
                            {
                                list($end_year, $end_month, $end_day) = $splitted_end_date;
                            }
                            else
                            {
                                list($end_year, $end_day, $end_month) = $splitted_end_date;
                            }
                            $this->data['PatientMedicalHistory']['end_month'] = $end_month?$end_month:'';
                            $this->data['PatientMedicalHistory']['end_year'] = $end_year?$end_year:'';
                        }
                        $this->data['PatientMedicalHistory']['occurrence'] = $this->data['PatientProblemList']['occurrence'];
                        $this->data['PatientMedicalHistory']['comment'] = $this->data['PatientProblemList']['comment'];
                        $this->data['PatientMedicalHistory']['status'] = isset($this->data['PatientProblemList']['status'])?$this->data['PatientProblemList']['status']:'';
                        $this->data['PatientMedicalHistory']['modified_timestamp'] = __date("Y-m-d H:i:s");
                        $this->data['PatientMedicalHistory']['modified_user_id'] = $this->user_id;
                        $this->PatientMedicalHistory->create();
                        $this->PatientMedicalHistory->save($this->data);

                        $this->PatientMedicalHistory->saveAudit('New');

                        //Delete from Problem List
                        $problem_list_id = (isset($this->params['named']['problem_list_id'])) ? $this->params['named']['problem_list_id'] : "";
                        $this->PatientProblemList->delete($problem_list_id, false);

                        $this->PatientProblemList->saveAudit('Delete');
                    }
                    else
                    {
                        $this->PatientProblemList->save($this->data);

                        $this->PatientProblemList->saveAudit('Update');
                    }
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $problem_list_id = (isset($this->params['named']['problem_list_id'])) ? $this->params['named']['problem_list_id'] : "";
                    $items = $this->PatientProblemList->find(
                            'first',
                            array(
                                'conditions' => array('PatientProblemList.problem_list_id' => $problem_list_id)
                            )
                    );

                    $this->set('EditItem', $this->sanitizeHTML($items));
                }
            } break;

            case "markNone":
            {
                if(!empty($this->data))
                {
                    $this->data['PatientDemographic']['patient_id'] = $patient_id;
                    $this->data['PatientDemographic']['problem_list_none'] = $this->data['submitted']['value'];
                    $this->PatientDemographic->save($this->data);
                }
                exit;
            } break;

            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientProblemList']['problem_list_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientProblemList->delete($id, false);
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->PatientProblemList->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            }break;
            default:
            {
                $demographic_items = $this->PatientDemographic->find('first',array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));

                $problem_list_none = $demographic_items['PatientDemographic']['problem_list_none'];
                $this->set('problem_list_none', $problem_list_none);

                if($show_all_problems == 'yes')
                {
                    $this->set('show_all_problems', 'yes');
					
					$this->paginate['PatientProblemList'] = array(
                    'conditions' => array('PatientProblemList.patient_id' => $patient_id),
			        'order' => array('PatientProblemList.modified_timestamp' => 'DESC')
                    );
				
                    $this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList')));
                    //$this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList', array('patient_id' => $patient_id))));
                }
                elseif($show_all_problems == 'no')
                {
                    $this->set('show_all_problems', 'no');
					
					$this->paginate['PatientProblemList'] = array(
                    'conditions' => array('PatientProblemList.patient_id' => $patient_id, 'PatientProblemList.status'=>'Active'),
			        'order' => array('PatientProblemList.modified_timestamp' => 'DESC')
                    );
				
                    $this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList')));
					
                    //$this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList', array('patient_id' => $patient_id, 'status'=>'Active'))));
                }
                else
                {
                    $this->set('show_all_problems', 'yes');
					
					$this->paginate['PatientProblemList'] = array(
                    'conditions' => array('PatientProblemList.patient_id' => $patient_id),
			        'order' => array('PatientProblemList.modified_timestamp' => 'DESC')
                    );
				
                    $this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList')));
					
                    //$this->set('PatientProblemList', $this->sanitizeHTML($this->paginate('PatientProblemList', array('patient_id' => $patient_id))));
                }

                $this->PatientProblemList->saveAudit('View');
            }
        }
    }

    public function medication_list_refill()
    {
        $this->layout = "blank";
        $this->loadModel("PatientMedicationRefill");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        
        $role_id = $_SESSION['UserAccount']['role_id'];
        $this->set("role_id", $role_id);
		
		$this->loadModel("UserAccount");
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);
        
        switch($task)
        {
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->PatientMedicationRefill->save($this->data);
                    $this->PatientMedicationRefill->saveAudit('Update');
                    $this->PatientMedicationRefill->PatientMedicationList->approveRefill($this->data['PatientMedicationRefill']['refill_id']);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                { 
                    $refill_id  = (isset($this->params['named']['refill_id'])) ? $this->params['named']['refill_id'] : "";
                    $item = $this->PatientMedicationRefill->find('first', array('conditions' => array('PatientMedicationRefill.refill_id' => $refill_id)));
    
                    $this->set('EditItem', $this->sanitizeHTML($item));
                    $this->PatientMedicationRefill->saveAudit('View');
                }
            }
            break;
            case "show_dosespot_refill":
            {
                $dosespot_xml_api = new Dosespot_XML_API();                
                $this->set("dosespot_info", $dosespot_xml_api->getInfo());                
            }break;
            default:
            {
                $this->paginate['PatientMedicationRefill'] = array(
                    'order' => array('PatientMedicationRefill.refill_request_date' => 'desc', 'PatientMedicationRefill.refill_id' => 'desc')
                );
        
                $this->set('refills', $this->sanitizeHTML($this->paginate('PatientMedicationRefill', array('PatientMedicationRefill.patient_id' => $patient_id))));
                $this->PatientMedicationRefill->saveAudit('View');
            }
        }
    }
    
    public function refill_summary()
    {
        $practice_settings = $this->Session->read("PracticeSetting");
        $rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];
        if($rx_setup=='Electronic_Dosespot')
        {
           $this->redirect(array('action' => 'dosespot_refill_summary'));
        }
    }
    
    public function refill_summary_grid()
    {
        $this->layout = "empty";
        $this->loadModel("PatientMedicationRefill");
        $this->loadModel("PatientDemographic");
		
		$this->PatientMedicationRefill->find('all');
				  
        $this->PatientMedicationRefill->inheritVirtualFields('PatientDemographic', 'patientName');

        $patient_ids = array();
        $conditions = array();
        
        if(isset($this->data['patient_name']))
        {
            if(strlen($this->data['patient_name']) > 0)
            {
                $this->PatientDemographic->recursive = -1;
                $patients = $this->PatientDemographic->find('all', array(
                    'conditions' => array('OR' => array('PatientDemographic.patient_search_name LIKE ' => ''.$this->data['patient_name'] . '%', 'PatientDemographic.first_name LIKE ' => ''.$this->data['patient_name'] . '%', 'PatientDemographic.last_name LIKE ' => ''.$this->data['patient_name'] . '%'))
                ));

                if (count($patients) > 0)
                {
                    foreach($patients as $patient)
                    {
                        $patient_ids[] = $patient['PatientDemographic']['patient_id'];
                    }
                }
                else
                {
                    $patient_ids[] = '0';
                }

                if(count($patient_ids) > 0) 
                {
                    $conditions['PatientMedicationRefill.patient_id'] = $patient_ids;
                }
            }
        }
       // $this->PatientMedicationRefill->find('all');
				  
        //$this->PatientMedicationRefill->inheritVirtualFields('PatientDemographic', 'patientName');
	   
        $this->paginate['PatientMedicationRefill'] = array(
            'conditions' => $conditions, 
            'limit' => 20, 
            'page' => 1, 
            'order' => array('PatientMedicationRefill.refill_request_date' => 'desc', 'PatientMedicationRefill.refill_id' => 'desc')
        );
        
        $this->set('refills', $this->sanitizeHTML($this->paginate('PatientMedicationRefill')));
		//var_dump($this->paginate('PatientMedicationRefill'));
    }
    
    public function dosespot_refill_summary()
    {
        
    }
    
    public function dosespot_refill_summary_grid()
    {
        $this->layout = "empty";             
        
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		$this->loadModel("DosespotRefillRequest");
		
		$this->DosespotRefillRequest->execute($this, $task);
		
    }
		
	public function lab_result_summary()
	{
    
        	$user = $this->Session->read('UserAccount');	
		//find out how many providers are in system
		$conditions = array('UserAccount.role_id  ' => array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID));
		$providers=$this->UserAccount->find('all', array('conditions' => $conditions));	
		$this->set(compact('providers'));			
	}
		
	public function lab_result_summary_grid()
	{
        	$this->layout = "empty";             
        	$user = $this->Session->read('UserAccount');
		$this->loadModel('EmdeonLabResult');
				
				$usr = (isset($this->params['named']['usr'])) ? $this->params['named']['usr'] : "";
				$search = (isset($this->params['named']['search'])) ? $this->params['named']['search'] : "";			
				//find out how many providers are in system
				$conditions = array('UserAccount.role_id  ' => array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID));
				$providers=$this->UserAccount->find('all', array('conditions' => $conditions));	
			
			if($usr == 'all')
			{
			  $user2="";
			}	
			//is a provider logged in?
			else if($user['role_id'] == EMR_Roles::PHYSICIAN_ROLE_ID
		  		|| $user['role_id'] == EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID
		  		|| $user['role_id'] == EMR_Roles::NURSE_PRACTITIONER_ROLE_ID
		  	)
			{
			  $user2 = $user;
			}
			else
			{
			  $user2="";
			}
								
				
				if ($search) {
					$electronic_lab_results = $this->EmdeonLabResult->getAlertPaginate($this, $user2, '', $search);
				} else {
					$electronic_lab_results = $this->EmdeonLabResult->getAlertPaginate($this, $user2, '');
				}
				

		$this->set(compact('electronic_lab_results', 'providers'));		
	}
    
    public function medication_list()
    {
        $this->layout = "blank";
        $this->loadModel("PatientMedicationList");
        $this->loadModel("PatientDemographic");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $this->loadModel("UserGroup");
		$this->loadModel('EmdeonPrescription');
        
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $prescriber = (isset($this->params['named']['prescriber'])) ? $this->params['named']['prescriber'] : "";
				
        $view_medications = (isset($this->params['named']['view_medications'])) ? $this->params['named']['view_medications'] : "";
        
        $dosespot = (isset($this->params['named']['dosespot'])) ? $this->params['named']['dosespot'] : "";
        if($view_medications == 1 && $dosespot == 1)
        {
            $this->redirect(array('action' => 'medication_list', 'task' => 'dosespot', 'patient_id' => $patient_id, 'prescriber' => $prescriber));
        }
        if($view_medications == 1 && $dosespot == 'show_dosespot_refill')
        {
            $this->redirect(array('action' => 'medication_list_refill', 'task' => 'show_dosespot_refill', 'patient_id' => $patient_id));
        }
        
        $refill_id = (isset($this->params['named']['refill_id'])) ? $this->params['named']['refill_id'] : "";
        if($view_medications == 1 && strlen($refill_id) > 0)
        {
            $this->redirect(array('action' => 'medication_list_refill', 'task' => 'edit', 'patient_id' => $patient_id, 'refill_id' => $refill_id));
        }
        
        $medication_list_id = (isset($this->params['named']['medication_list_id'])) ? $this->params['named']['medication_list_id'] : "";
        if($view_medications == 1 && strlen($medication_list_id) > 0)
        {
            $this->redirect(array('action' => 'medication_list', 'task' => 'edit', 'refill' => 1, 'patient_id' => $patient_id, 'medication_list_id' => $medication_list_id));
        }
        
		$this->loadModel("UserAccount");
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);
        
        $show_all_medications = (isset($this->params['named']['show_all_medications'])) ? $this->params['named']['show_all_medications'] : "";
        $show_surescripts = (isset($this->params['named']['show_surescripts'])) ? $this->params['named']['show_surescripts'] : "yes";
        $show_reported = (isset($this->params['named']['show_reported'])) ? $this->params['named']['show_reported'] : "yes";
        $show_prescribed = (isset($this->params['named']['show_prescribed'])) ? $this->params['named']['show_prescribed'] : "yes";
		$show_surescripts_history=(isset($this->params['named']['show_surescripts_history'])) ? $this->params['named']['show_surescripts_history'] : "no";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $patient = $this->PatientDemographic->getPatient($patient_id);
        $mrn = $patient['mrn'];
        //$this->set('PatientMedicationList', $this->sanitizeHTML($this->PatientMedicationList->find('all')));
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
        
        $this->set("is_physician", (bool)($this->Session->read("UserAccount.role_id") == EMR_Roles::PHYSICIAN_ROLE_ID));
		$this->set("dosepot_singlesignon_userid", $this->Session->read("UserAccount.dosepot_singlesignon_userid"));
        switch($task)
        {
			case "emdeon_rx":
			{
				$this->set("mrn", $patient['mrn']);
				
			} break;
            case "load_Icd9_autocomplete":
            {
                if (!empty($this->data))
                {
                   $this->Icd->execute($this, $task);
                }
                exit();
            } break;

            case "load_provider_autocomplete":
            {
                if (!empty($this->data))
                {
                    $search_keyword = ''.$this->data['autocomplete']['keyword'];
                    $search_limit = $this->data['autocomplete']['limit'];
                    $referred_by_items = $this->UserAccount->find('all',
                    array('conditions' => array('OR' => array('UserAccount.firstname LIKE ' => $search_keyword.'%', 'UserAccount.lastname LIKE ' => $search_keyword.'%'), array('AND' =>array('UserAccount.role_id' => 3))),
                    'limit' => $search_limit
                    ));

                    $data_array = array();

                    foreach($referred_by_items as $referred_by_item)
                    {
                        $data_array[] = $referred_by_item['UserAccount']['firstname'].' '.$referred_by_item['UserAccount']['lastname'].'|'.$referred_by_item['UserAccount']['user_id'];
                    }

                    echo implode("\n", $data_array);
                }
                exit();
            } break;

            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->PatientMedicationList->create();
                    $this->PatientMedicationList->save($this->data);
                    $this->PatientMedicationList->saveAudit('New');
					
                    $ret = array();

                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                $medication_list_id = (isset($this->params['named']['medication_list_id'])) ? $this->params['named']['medication_list_id'] : "";
				$practice_settings = $this->Session->read("PracticeSetting");
				$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];
                
                if(!empty($this->data))
                {
                    $medication_status = $this->data['PatientMedicationList']['status'];
                    $this->data['PatientMedicationList']['medication_list_id'] = $medication_list_id;
                    if( ( $this->data['PatientMedicationList']['end_date'] == '' ||
                    			$this->data['PatientMedicationList']['end_date'] == '0000-00-00' ) &&
                    		( $medication_status == 'Inactive' ||
                    	  	$medication_status == 'Cancelled' ||
                    	  	$medication_status == 'Discontinued' ||
                    	  	$medication_status == 'Completed' ) ){
                        $this->data['PatientMedicationList']['end_date'] = __date("Y-m-d");
                    }
                    $this->PatientMedicationList->save($this->data);
                    $this->PatientMedicationList->saveAudit('Update');
                    
                    if($this->data['refill'] == 1)
                    {
                        $this->PatientMedicationList->refill($medication_list_id);
                    }

			//we have to also update the emdeon e-Rx table if status was changed
			if($rx_setup == 'Electronic_Emdeon' && !empty($this->data['PatientMedicationList']['emdeon_medication_id']))
			{
                		$this->loadModel('EmdeonPrescription');
				$data2['rx_status']= $medication_status;
				$data2['prescription_id']= $this->data['PatientMedicationList']['emdeon_medication_id'];   
				$this->EmdeonPrescription->save($data2);

			}
                    $ret = array();
                    echo json_encode($ret);
                    exit;
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

            case "markNone":
            {
                if(!empty($this->data))
                {
                    $this->data['PatientDemographic']['patient_id'] = $patient_id;
                    $this->data['PatientDemographic']['medication_list_none'] = $this->data['submitted']['value'];
                    $this->PatientDemographic->save($this->data);
                }
                exit;
            } break;

            case "import_medications_from_surescripts":
            {
                $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);

	      // you can't proceed any further if no dosespot patient ID is defined, so abort. 
	      if($dosespot_patient_id && is_numeric($dosespot_patient_id) )				
              {
			$dosespot_xml_api = new Dosespot_XML_API();
                	$medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id,true);
   
                foreach ($medication_items as $medication_item)
                {
                    $dosespot_medication_id = $medication_item['MedicationId'];
                    $items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.dosespot_medication_id' => $dosespot_medication_id)));

			$this->data['PatientMedicationList']['medication_type'] = "surescripts_history";//if this name is ever modified, update PatientMedicationList->removeDosespotDeletedData() function to match
			$this->data['PatientMedicationList']['source'] = "Surescripts History";
                    if(empty($items))
                    {
                        $this->PatientMedicationList->create();
			$this->data['PatientMedicationList']['status'] = $medication_item['status'];
                        $this->PatientMedicationList->saveAudit('New');
                    }
                    else
                    {
			$this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
			$this->data['PatientMedicationList']['status'] = $items['PatientMedicationList']['status'];
                        $this->PatientMedicationList->saveAudit('Update');
                    }
			$start_date = __date('Y-m-d', strtotime($medication_item['date_written'].'+'.$medication_item['days_supply'].'days'));
			$inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
			$this->data['PatientMedicationList']['patient_id'] = $patient_id;
			$this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
			$this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
			$this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
			$this->data['PatientMedicationList']['status'] = $medication_item['status'];
			$this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
			$this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
			$this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
			$this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
			//only set inactive_date IF days_supply was provided
			if (!empty ($medication_item['days_supply'])) {
 				$this->data['PatientMedicationList']['end_date'] = $inactive_date;
			}
			$this->data['PatientMedicationList']['modified_user_id'] =  $this->user_id;
			$this->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");

		   	$this->PatientMedicationList->save($this->data);
                }
				
		//Remove the dosespot data from the database.If removed in dosespot.	
		$this->PatientMedicationList->removeDosespotDeletedData($patient_id, true, $medication_items,true);	

	      } //if dosespot patient ID loop close		

                $ret = array();
                echo json_encode($ret);
                exit;
            } break;
			
			case "import_medications_from_surescripts_emdeon":
            {
			    $this->loadModel('EmdeonPrescription');
				$emdeon_xml_api = new Emdeon_XML_API();
				$person = $emdeon_xml_api->getPersonByMRN($mrn);
				
				$medication_items = $this->EmdeonPrescription->find('all', array(
					'conditions' => array('EmdeonPrescription.patient_id' => $patient_id, 'EmdeonPrescription.rx_status !=' => 'Pending'),
					'group' => array('EmdeonPrescription.rx_unique_id')
				));
                foreach ($medication_items as $medication_item)
                {
                    $emdeon_medication_id = $medication_item['EmdeonPrescription']['prescription_id'];
                    $items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.emdeon_medication_id' => $emdeon_medication_id)));

                    if(empty($items))
                    {
					    $start_date_split = explode(" ", $medication_item['EmdeonPrescription']['created_date']);
					    $start_date = __date('Y-m-d', strtotime($start_date_split[0].'+'.$medication_item['EmdeonPrescription']['days_supply'].'days'));
					    $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                        $this->data = array();
                        $this->data['PatientMedicationList']['patient_id'] = $patient_id;
						$this->data['PatientMedicationList']['encounter_id'] = $medication_item['EmdeonPrescription']['encounter_id'];
						$this->data['PatientMedicationList']['medication_type'] = "Electronic_Emdeon";
                        $this->data['PatientMedicationList']['emdeon_medication_id'] = $emdeon_medication_id;
						$this->data['PatientMedicationList']['emdeon_drug_id'] = $medication_item['EmdeonPrescription']['drug_id'];
                        $this->data['PatientMedicationList']['medication'] = $medication_item['EmdeonPrescription']['drug_name'];
						$this->data['PatientMedicationList']['rxnorm'] = $medication_item['EmdeonPrescription']['rxnorm'];
                        $this->data['PatientMedicationList']['source'] = "e-Prescribing History";
                        $this->data['PatientMedicationList']['status'] = "Active";
						$this->data['PatientMedicationList']['direction'] = $medication_item['EmdeonPrescription']['sig'];
						$this->data['PatientMedicationList']['quantity_value'] = $medication_item['EmdeonPrescription']['quantity'];
						$this->data['PatientMedicationList']['refill_allowed'] = $medication_item['EmdeonPrescription']['refills'];
						$this->data['PatientMedicationList']['start_date'] = __date("Y-m-d", strtotime($start_date_split[0]));
                        $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                        $this->data['PatientMedicationList']['modified_user_id'] =  $this->user_id;
                        $this->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
                        $this->PatientMedicationList->create();
                        $this->PatientMedicationList->save($this->data);
                        $this->PatientMedicationList->saveAudit('New');

                    }
                    else
                    {
/* I DON'T THINK THIS IS NEEDED - Robert @ 6/16/2014
   this should mean the med was already in the table, and it's possible the user had modified it. if this runs,
it will overwrite the changes the user made in our table

					    $start_date_split = explode(" ", $medication_item['EmdeonPrescription']['created_date']);
					    $start_date = __date('Y-m-d', strtotime($start_date_split[0].'+'.$medication_item['EmdeonPrescription']['days_supply'].'days'));
					    $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                        $this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
                        $this->data['PatientMedicationList']['patient_id'] = $patient_id;
						$this->data['PatientMedicationList']['medication_type'] = "Electronic_Emdeon";
                        $this->data['PatientMedicationList']['emdeon_medication_id'] = $emdeon_medication_id;
						$this->data['PatientMedicationList']['emdeon_drug_id'] = $medication_item['EmdeonPrescription']['drug_id'];
                        $this->data['PatientMedicationList']['medication'] = $medication_item['EmdeonPrescription']['drug_name'];
						$this->data['PatientMedicationList']['rxnorm'] = @$medication_item['EmdeonPrescription']['rxnorm'];
                        $this->data['PatientMedicationList']['source'] = "e-Prescribing History";
                        $this->data['PatientMedicationList']['status'] = "Active";
						$this->data['PatientMedicationList']['direction'] = "";
						$this->data['PatientMedicationList']['quantity_value'] = $medication_item['EmdeonPrescription']['quantity'];
						$this->data['PatientMedicationList']['refill_allowed'] = $medication_item['EmdeonPrescription']['refills'];
						$this->data['PatientMedicationList']['start_date'] = __date("Y-m-d", strtotime($start_date_split[0]));
                        $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                        $this->data['PatientMedicationList']['modified_user_id'] =  $this->user_id;
                        $this->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
                        $this->PatientMedicationList->save($this->data);
                        $this->PatientMedicationList->saveAudit('Update');
*/
                    }

                }
				
				//Remove the dosespot data from the database.If removed in dosespot.	
				//$this->PatientMedicationList->removeDosespotDeletedData($patient_id, true, $medication_items);	
				
                $ret = array();
                echo json_encode($ret);
                exit;
            } break;
			
            case "update_status":
            {
								$medication_status = $this->data['submitted']['value'];
								if( $medication_status == 'Inactive' ||
                		$medication_status == 'Cancelled' ||
                		$medication_status == 'Discontinued' ||
                    $medication_status == 'Completed' )
								{
									// Check to autofill the end date
									$existing = $this->PatientMedicationList->find('first',
										array('conditions' => array('PatientMedicationList.medication_list_id' => $this->data['medication_list_id'])));
									if( isset($existing) &&
											( !isset($existing['PatientMedicationList']['end_date']) ||
												$existing['PatientMedicationList']['end_date'] == '0000-00-00' ) ){
										$this->data['PatientMedicationList']['medication_list_id'] = $this->data['medication_list_id'];
										$this->data['PatientMedicationList']['end_date'] = __date("Y-m-d");
										$this->PatientMedicationList->save($this->data);
										$this->PatientMedicationList->saveAudit('UpdateStatusAutofill');
									}
								}
								$this->PatientMedicationList->setItemValue("status", $this->data['submitted']['value'], $this->data['medication_list_id'], $patient_id, $this->user_id);
								echo $this->data['submitted']['value'];
								exit;
            }
            break;
            
            case 'track_changes': {
              $encounter_id = $this->params['form']['encounter_id'];
              $medication_list_id = $this->params['form']['medication_list_id'];
              $status = $this->params['form']['status'];
              
              $existing = $this->PatientMedicationList->find('first',
                array('conditions' => array('PatientMedicationList.medication_list_id' => $medication_list_id)));
              
              
              if (!$existing) {
                die();
              }
              

              $frequency = '';
              $unit = '';
              $route = '';
              $quantity = '';
              $direction = '';

              $frequency_value = $existing['PatientMedicationList']['frequency'];
              $unit_value = $existing['PatientMedicationList']['unit'];
              $route_value = $existing['PatientMedicationList']['route'];
              $quantity_value = $existing['PatientMedicationList']['quantity'];
              $direction_value = $existing['PatientMedicationList']['direction'];
              if($frequency_value != "")
              {
              $frequency = ', '.$frequency_value;
              }
              if($unit_value != "")
              {
              $unit = ', ' .$unit_value;
              }
              if($route_value != "")
              {
              $route = ', ' .$route_value;
              }
              if($quantity_value != "0")
              {
              $quantity = ', ' .$quantity_value;
              }
              if($direction_value != "")
              {
              $direction = ', ' .$direction_value;
              }              
              
              
              
              
              $data = array(
                  'encounter_id' => $encounter_id,
                  'medication_list_id' => $medication_list_id,
                  'medication_details' =>  $existing['PatientMedicationList']['medication'].$quantity.$unit.$route.$frequency.$direction,
		  'medication_status' => $status,
                  'modified_user_id' => $_SESSION['UserAccount']['user_id']
              );

              $this->loadModel('EncounterPlanRxChanges');
              $this->EncounterPlanRxChanges->save($data);
              
              
              
              exit;
            } break;
            
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientMedicationList']['medication_list_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientMedicationList->delete($id, false);
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->PatientMedicationList->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            }break;
			case "delete_medication":
            {
			   $medication_list_id = (isset($this->data['medication_list_id'])) ? $this->data['medication_list_id'] : "";
               $this->PatientMedicationList->delete($medication_list_id, false);
			   $ret = array();
               echo json_encode($ret);
                exit;
			
            }break;
            case "get_report_html":
            {
                $controller->layout = 'empty';
                
                if ($report = Medication_List::generateReport($patient_id, $show_all_medications, true))
                {
                    App::import('Helper', 'Html');
                    $html = new HtmlHelper();
    
                    echo $report;
                  
                    ob_flush();
                    flush();
                    
                    exit();
                }
                exit('could not generate report');
            }
            case "get_report_pdf" :
            {	
				$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : '';
                $this->layout = 'empty';
                if($report = Medication_List::generateReport($patient_id, $show_all_medications, true))
                {
                    if ($task == "get_report_pdf")
                    {
                        //$this->loadModel("Pdf");

                        $url = $this->paths['temp'];
                        $url = str_replace('//','/',$url);

                        $pdffile = $mrn.'_MedicationList.pdf';

                        //PDF file creation
                        //site::write(pdfReport::generate($report, $url.$pdffile), $url.$pdffile);
                        site::write(pdfReport::generate($report), $url.$pdffile);
                        $file = $mrn.'_MedicationList.pdf';
                        $targetPath = $this->paths['temp'];
                        $targetFile =  str_replace('//','/',$targetPath) . $file;
						if( $view == 'fax' ){
							$this->loadModel('practiceSetting');
							$settings  = $this->practiceSetting->getSettings();        
							if(!$settings->faxage_username || !$settings->faxage_password || !$settings->faxage_company) {
								$this->Session->setFlash(__('Fax is not enabled. Contact Sales for assistance.', true));
								$this->redirect(array('controller'=> 'encounters', 'action' => 'index'));
								exit();
							}
							if($view == 'fax')
							{
								$this->Session->write('fileName', $targetFile);
								$this->redirect(array('controller'=> 'messaging', 'action' => 'new_fax' ,'fax_doc'));		
								exit;						
							}
						}
	


                        header('Content-Type: application/octet-stream; name="'.$file.'"');
                        header('Content-Disposition: attachment; filename="'.$file.'"');
                        header('Accept-Ranges: bytes');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Content-transfer-encoding: binary');
                        header('Content-length: ' . @filesize($targetFile));
                        @readfile($targetFile);
                    }
                    else
                    {
                        echo $report;
                    }
                    ob_flush();
                    flush();
                    exit();
                }
                exit('could not generate report');
            } break;
            case "get_report_ccr" :
            {
                MedicationCCR::generateCCR($this, $show_all_medications);
            } break;
            case "dosespot":
            {
								$this->loadModel('AdministrationPrescriptionAuth');
								
								$allowed = $this->AdministrationPrescriptionAuth->getAuthorizingUsers($_SESSION['UserAccount']['user_id']);
								$allowedIds = Set::extract('/UserAccount/user_id', $allowed);
								
								
								$prescriber = (isset($this->params['named']['prescriber'])) ? intval($this->params['named']['prescriber']) : 0;
								
								if ($allowedIds && in_array($prescriber, $allowedIds)) {
									foreach ($allowed as $userAccount) {
										
										if (intval($userAccount['UserAccount']['user_id']) == $prescriber) {
											$dosespot_xml_api = new Dosespot_XML_API($userAccount, 'write'); 
											break;
										}
									}
								} else {
									$dosespot_xml_api = new Dosespot_XML_API(false, 'write');                
								}
								
                $this->set("dosespot_info", $dosespot_xml_api->getInfo());
                $demographic_item = $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
                $this->set('demographic_item', $demographic_item['PatientDemographic']);
								
								$this->loadModel('PracticeSetting');
								$settings = $this->Session->read("PracticeSetting");
								$db_config = $this->PracticeSetting->getDataSource()->config;
								$cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';								
								
								$dosespot_accessed = Cache::read($cache_file_prefix . 'dosespot_accessed');
								
								if (!$dosespot_accessed) {
									$dosespot_accessed = array();
								}
								
								$dosespot_accessed[] = $patient_id;
								$dosespot_accessed = array_unique($dosespot_accessed);
								
								Cache::write($cache_file_prefix . 'dosespot_accessed', $dosespot_accessed);
								
            } break;
					
					  case 'encounter_dosespot':
							// Fetch newly added dosespot medication for patient
							// and associate with given encounter
							
							$encounterId = (isset($this->params['named']['encounter_id'])) ? intval($this->params['named']['encounter_id']) : 0;
							
							$practice_settings = $this->Session->read("PracticeSetting");
							$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];
							if($rx_setup=='Electronic_Dosespot')
							{
							   $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
							    //if dosespot patient ID is defined. you can't proceed!
							    if($dosespot_patient_id && is_numeric($dosespot_patient_id) )
							    {
								$begin_date=__date('Y-m-d', strtotime('-1 day'));
								$dosespot_xml_api = new Dosespot_XML_API();
								$medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id,false,$begin_date);
								foreach ($medication_items as $medication_item)
								{
									$dosespot_medication_id = $medication_item['MedicationId'];
									$items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.dosespot_medication_id' => $dosespot_medication_id)));

									if(empty($items))
									{
											$start_date = __date('Y-m-d', strtotime($medication_item['date_written'].'+'.$medication_item['days_supply'].'days'));
												$inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
										$this->data = array();
										$this->data['PatientMedicationList']['patient_id'] = $patient_id;
										$this->data['PatientMedicationList']['encounter_id'] = $encounterId;
										$this->data['PatientMedicationList']['medication_type'] = "Electronic";
										$this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
										$this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
										$this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
										$this->data['PatientMedicationList']['source'] = "e-Prescribing History";
										$this->data['PatientMedicationList']['status'] = $medication_item['status'];
										$this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
										$this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
										$this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
										$this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
										//only set inactive_date IF days_supply was provided 
										if (!empty ($medication_item['days_supply'])) {
																				 $this->data['PatientMedicationList']['end_date'] = $inactive_date;
																			}
										$this->data['PatientMedicationList']['modified_user_id'] =  $this->user_id;
										$this->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
										$this->PatientMedicationList->create();
										$this->PatientMedicationList->save($this->data);
										$this->PatientMedicationList->saveAudit('New');
										echo 'Added ' . $medication_item['medication'] . " <br />\n";
									}
								}

							    } //close loop if $dosespot_patient_id	

							}							
							
							
							
							
							exit();
							break;
            default:
            {
                //Import medications from Dosespot when loading the display table in medication list page.
        			$db_config = $this->PatientDemographic->getDataSource()->config;
        			$cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
				
				$practice_settings = $this->Session->read("PracticeSetting");
				$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];
				
					$this->loadModel('AdministrationPrescriptionAuth');
					
					$allowed = $this->AdministrationPrescriptionAuth->getAuthorizingUsers($_SESSION['UserAccount']['user_id']);
					$this->set('prescriptionAuth', $allowed);
				if($rx_setup=='Electronic_Dosespot')
				{
					
				    $dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
				
					//If the patient not exists in Dosespot, add the patient to Dosespot
					if($dosespot_patient_id == 0 or $dosespot_patient_id == '')
					{					
						$this->PatientDemographic->updateDosespotPatient($patient_id);					
						$dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
					}	
				// must have $dosespot_patient_id to proceed, otherwise skip				
				if($dosespot_patient_id && is_numeric($dosespot_patient_id) )
				{
					$dosespot_xml_api = new Dosespot_XML_API();
					$dosespot_cache_key=$cache_file_prefix.$dosespot_patient_id.'_dosespot_import_time_stamp';
					Cache::set(array('duration' => '+2 months'));
					$import_time_stamp = Cache::read($dosespot_cache_key);
					if(!empty($import_time_stamp)) {
						$medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id,false,$import_time_stamp);
					} else {
						$medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id);
					}
					foreach ($medication_items as $medication_item)
					{
						$dosespot_medication_id = $medication_item['MedicationId'];
						$items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.dosespot_medication_id' => $dosespot_medication_id)));
	
						if(empty($items))
						{
						    $start_date = __date('Y-m-d', strtotime($medication_item['date_written'].'+'.$medication_item['days_supply'].'days'));
					        $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
							$this->data = array();
							$this->data['PatientMedicationList']['patient_id'] = $patient_id;
							$this->data['PatientMedicationList']['medication_type'] = "Electronic";
						$this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
							$this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
							$this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
							$this->data['PatientMedicationList']['source'] = "e-Prescribing History";
							$this->data['PatientMedicationList']['status'] = $medication_item['status'];
							$this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
							$this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
						$this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
							$this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
							//only set inactive_date IF days_supply was provided 
							if (!empty ($medication_item['days_supply'])) {
                         				   $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                        				}
							$this->data['PatientMedicationList']['modified_user_id'] =  $this->user_id;
							$this->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
							$this->PatientMedicationList->create();
							$this->PatientMedicationList->save($this->data);
							$this->PatientMedicationList->saveAudit('New');
	
						}
						else
						{
						    $start_date = __date('Y-m-d', strtotime($medication_item['date_written'].'+'.$medication_item['days_supply'].'days'));
					        $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
							$this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
							$this->data['PatientMedicationList']['patient_id'] = $patient_id;
							$this->data['PatientMedicationList']['medication_type'] = "Electronic";
							$this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
							$this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
							$this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
							$this->data['PatientMedicationList']['source'] = "e-Prescribing History";
							$this->data['PatientMedicationList']['status'] = $items['PatientMedicationList']['status'];
							$this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
							$this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
						$this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
							$this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
							//only set inactive_date IF days_supply was provided 
							if (!empty ($medication_item['days_supply'])) {
                         				   $this->data['PatientMedicationList']['end_date'] = $inactive_date;
                        				}
							$this->data['PatientMedicationList']['modified_user_id'] =  $this->user_id;
							$this->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
							//only set inactive_date IF days_supply was provided 
							if (strtotime(date('Y-m-d')) >= strtotime($inactive_date) && !empty ($medication_item['days_supply'])) {
								$this->data['PatientMedicationList']['status'] = 'Completed';
							}
							
							$this->PatientMedicationList->save($this->data);
							$this->PatientMedicationList->saveAudit('Update');
						}
							
					}	
					
					//Remove the dosespot data from the database.If removed in dosespot.	
					$this->PatientMedicationList->removeDosespotDeletedData($patient_id, true, $medication_items);				
					Cache::set(array('duration' => '+2 months'));
					Cache::write($dosespot_cache_key, __date('Y-m-d',strtotime('-1 week')));
				    } //close loop if $dosespot_patient_id
				}
				
				$isRefillEnable = $this->UserGroup->isRxRefillEnable();
                $this->set("isRefillEnable", $isRefillEnable);
                
                $demographic_items = $this->PatientDemographic->find('first',array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'recursive' => -1));
                $medication_list_none = $demographic_items['PatientDemographic']['medication_list_none'];
                $this->set('medication_list_none', $medication_list_none);
				
		if($rx_setup == 'Electronic_Emdeon')
		{				
				$emdeon_xml_api = new Emdeon_XML_API();
				$person = $emdeon_xml_api->getPersonByMRN($mrn);

					$medication_items = $this->EmdeonPrescription->find('all', array(
						'conditions' => array('EmdeonPrescription.patient_id' => $patient_id, 'EmdeonPrescription.rx_status !=' => 'Pending'),
						'group' => array('EmdeonPrescription.rx_unique_id')
					));
					foreach ($medication_items as $medication_item)
                	{
                    		$emdeon_medication_id = $medication_item['EmdeonPrescription']['prescription_id'];
                    		$items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.emdeon_medication_id' => $emdeon_medication_id)));

                    	    if(empty($items))
                    	    {
						$this->PatientMedicationList->create();
						$this->PatientMedicationList->saveAudit('New');
                    	    }
                    	   else
                    	    {
                        			$this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
						$this->PatientMedicationList->saveAudit('Update');
			    }
					    $start_date_split = explode(" ", $medication_item['EmdeonPrescription']['created_date']);
					    $start_date = __date('Y-m-d', strtotime($start_date_split[0].'+'.$medication_item['EmdeonPrescription']['days_supply'].'days'));
					    $inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
                        			$this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
                        			$this->data['PatientMedicationList']['patient_id'] = $patient_id;
						$this->data['PatientMedicationList']['medication_type'] = "Electronic_Emdeon";
                        			$this->data['PatientMedicationList']['emdeon_medication_id'] = $emdeon_medication_id;
						$this->data['PatientMedicationList']['emdeon_drug_id'] = $medication_item['EmdeonPrescription']['drug_id'];
                        			$this->data['PatientMedicationList']['medication'] = $medication_item['EmdeonPrescription']['drug_name'];
                        			$this->data['PatientMedicationList']['source'] = "e-Prescribing History";
						$this->data['PatientMedicationList']['encounter_id'] =$medication_item['EmdeonPrescription']['encounter_id'];
                        			$this->data['PatientMedicationList']['status'] = ($medication_item['EmdeonPrescription']['rx_status']=='Authorized')?'Active':$medication_item['EmdeonPrescription']['rx_status'];
						$this->data['PatientMedicationList']['direction'] = $medication_item['EmdeonPrescription']['sig'];
						$this->data['PatientMedicationList']['quantity_value'] = $medication_item['EmdeonPrescription']['quantity'];
						$this->data['PatientMedicationList']['refill_allowed'] = $medication_item['EmdeonPrescription']['refills'];
						$this->data['PatientMedicationList']['start_date'] = __date("Y-m-d", strtotime($start_date_split[0]));
                        			$this->data['PatientMedicationList']['end_date'] = $inactive_date;
                        			$this->data['PatientMedicationList']['modified_user_id'] =  $this->user_id;
                        			$this->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
                        			$this->PatientMedicationList->save($this->data);
                    

                	}
		   }

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
			 if($show_surescripts_history == 'yes') {
                                $source_array[] ="Surescripts History";
                        }

                    $this->set('show_all_medications', 'yes');
					
			$this->paginate['PatientMedicationList'] = array(
                    		'conditions' => array('PatientMedicationList.patient_id' => $patient_id, 
						     'PatientMedicationList.source' => $source_array),
			        'order' => array('PatientMedicationList.modified_timestamp' => 'DESC')
                    );
                }
                else
                {
                    $this->set('show_all_medications', 'no');

			$conditions['PatientMedicationList.patient_id'] = $patient_id;
                        //this is outside/old surescripts hx from other providers
                        if($show_surescripts_history == 'yes') {
				$conditions['OR'] = array(
                                                array("PatientMedicationList.source" => $source_array,"PatientMedicationList.status"=>"Active"),
                                                array("PatientMedicationList.source" => "Surescripts History"));
			} else {
				$conditions['PatientMedicationList.source'] = $source_array;
				$conditions['PatientMedicationList.status'] = 'Active';
			}

			$this->paginate['PatientMedicationList'] = array(
                    		'conditions' => $conditions, 
			        'order' => array('PatientMedicationList.modified_timestamp' => 'DESC'),
				'recursive' => -1
                    	);
                }
		$PatientMedicationList=$this->sanitizeHTML($this->paginate('PatientMedicationList'));
		$this->set('PatientMedicationList', $PatientMedicationList);

		if($rx_setup=='Electronic_Dosespot') {
			$dosespot_xml_api = new Dosespot_XML_API();
			$this->set('verifydosespotinfo',$dosespot_xml_api->verifyPatientDemographics($demographic_items['PatientDemographic']));
		}

                $this->set('show_surescripts', $show_surescripts);
                $this->set('show_reported', $show_reported);
                $this->set('show_prescribed', $show_prescribed);
		$this->set('show_surescripts_history',$show_surescripts_history);
                $this->PatientMedicationList->saveAudit('View');
            }
        }
    }

    public function imm_injections()
    {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
    }

   public function in_house_work_labs()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user = $this->Session->read('UserAccount');
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        
        $view_labs = (isset($this->params['named']['view_labs'])) ? $this->params['named']['view_labs'] : "";    
        $order_id = (isset($this->params['named']['order_id'])) ? $this->params['named']['order_id'] : "";
        
        if($view_labs == 1 && strlen($order_id) > 0)
        {
            $this->redirect(array('action' => 'lab_results_electronic', 'task' => 'edit_order', 'patient_id' => $patient_id, 'order_id' => $order_id));
        }
        
        $this->loadModel('Unit');
        $this->set("units", $this->Unit->find('all'));
        
        $this->loadModel('SpecimenSource');
        $this->set("specimen_sources", $this->SpecimenSource->find('all'));
        
        switch($task)
        {
            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                    $this->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                    $this->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                    $this->data['EncounterPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $this->data['EncounterPointOfCare']['modified_user_id'] = $this->user_id;
                    $this->EncounterPointOfCare->create();
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
                    
                    $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPointOfCare']['lab_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['lab_date_performed']));

                    if (isset($this->params['form']['lab_panels'])) {
                        $posted_panels = $this->params['form']['lab_panels'];
                        $panels = array();
                        
                        foreach ($posted_panels as $field => $value) {
                            $panels[$field] = $value;
                        }
                        
                        $this->data['EncounterPointOfCare']['lab_panels'] = json_encode($panels);
                    }
                    
                    
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->data['EncounterPointOfCare']['point_of_care_id'];
                    
                    $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                    $items = $this->EncounterPointOfCare->find(
                            'first',
                            array(
                                'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
                            )
                    );

                    $this->set('EditItem', $this->sanitizeHTML($items));
                    $this->set('rawData', $items);
                }
            } break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPointOfCare->delete($id, false);

                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
                    }
                }

                echo json_encode($ret);
                exit;
            }break;
            default:
            {
                $encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
                //debug($encounter_items);
				
								if ($encounter_items) {
									$this->paginate['EncounterPointOfCare'] = array(
											'conditions' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Labs'),
											'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
									);

								} else {
									$this->paginate['EncounterPointOfCare'] = array(
										'conditions' => array('EncounterPointOfCare.encounter_id' => null),
									);
								}
								$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));
				
                //$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Labs'))));

                $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Labs - Point of Care');
            } break;
        }
    }

    public function lab_results_electronic()
    {
        $availableProviders = $this->UserAccount->getProviders();
        $this->set('availableProviders', $availableProviders);
			
			
        $this->loadModel("EmdeonOrder");
        $this->EmdeonOrder->execute($this);
    }

    public function lab_results_electronic_view()
    {
        if(isset($this->Toolbar))
        {
            $this->Toolbar->enabled = false; 
        }
        
        $this->layout = "empty";
        $this->loadModel("EmdeonLabResult");

        $lab_result_id = (isset($this->params['named']['lab_result_id'])) ? $this->params['named']['lab_result_id'] : "";
        $page = (isset($this->params['named']['page'])) ? $this->params['named']['page'] : 1;
        
        if (isset($this->params['named']['auto_print'])) {
          $page = 'print';
        }
        
        $this->set('report_html', $this->EmdeonLabResult->getHTML($lab_result_id, $page));
    }

		
		public function lab_result_graph() {
			$this->layout = "blank";
			$this->loadModel('EmdeonLabResult');
			$this->EmdeonLabResult->executeGraph($this);
		}
		
		
    public function lab_results()
    {
        $this->layout = "blank";
        $this->loadModel("PatientLabResult");
		$this->loadModel("EncounterPlanLab");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $this->loadModel("DirectoryLabFacility");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        
        $this->loadModel('Unit');
        $this->set("units", $this->Unit->find('all'));         
        $this->loadModel('SpecimenSource');
        $this->set("specimen_sources", $this->SpecimenSource->find('all'));
		
		$this->loadModel("StateCode");
		$this->set("StateCode", $this->sanitizeHTML($this->StateCode->find('all')));
		
		$labs_setup = $this->Session->read("PracticeSetting.PracticeSetting.labs_setup");
		$this->set("labs_setup", $labs_setup);
		
		$standard_order_list = $this->EncounterPlanLab->getOrderList($patient_id);
		$this->set("standard_order_list", $standard_order_list);

        switch($task)
        {
            case "load_Icd9_autocomplete":
            {
                if (!empty($this->data))
                {
                    $this->Icd->execute($this, $task);
                }
                exit();
            } break;

            case "labname_load":
            {
                if (!empty($this->data))
                {
                    $search_keyword = $this->data['autocomplete']['keyword'];
                    $search_limit = $this->data['autocomplete']['limit'];
                    $lab_items = $this->DirectoryLabFacility->find('all',
                                array(
                                    'conditions' => array('DirectoryLabFacility.lab_facility_name LIKE ' => '%'.$search_keyword.'%'), 'limit' => $search_limit)

                    );
                    $data_array = array();

                    foreach($lab_items as $lab_item)
                    {
                        $data_array[] = $lab_item['DirectoryLabFacility']['lab_facility_name'].'|'.$lab_item['DirectoryLabFacility']['address_1'] . '|' . $lab_item['DirectoryLabFacility']['address_2'] . '|' . $lab_item['DirectoryLabFacility']['city'] . '|' . $lab_item['DirectoryLabFacility']['state'].'|'.$lab_item['DirectoryLabFacility']['zip_code'] .'|'.$lab_item['DirectoryLabFacility']['country'];
                    }

                    echo implode("\n", $data_array);
                }
                exit();
            } break;

            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->data['PatientLabResult']['patient_id'] = $patient_id;
                    $this->data['PatientLabResult']['date_ordered'] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['date_ordered']));
                    $this->data['PatientLabResult']['report_date'] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['report_date']));
                    $this->data['PatientLabResult']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $this->data['PatientLabResult']['modified_user_id'] = $this->user_id;
					
					for($i = 1; $i <= 5; $i++)
					{
						$this->data['PatientLabResult']['test_report_date'.$i] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['test_report_date'.$i]));
					}
					
                    $this->PatientLabResult->create();
                    $this->PatientLabResult->save($this->data);
                    $lab_result_id = $this->PatientLabResult->getLastInsertId();

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->data['PatientLabResult']['patient_id'] = $patient_id;
                    $this->data['PatientLabResult']['date_ordered'] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['date_ordered']));
                    $this->data['PatientLabResult']['report_date'] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['report_date']));
                    $this->data['PatientLabResult']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $this->data['PatientLabResult']['modified_user_id'] = $this->user_id;
					
					for($i = 1; $i <= 5; $i++)
					{
						$this->data['PatientLabResult']['test_report_date'.$i] = __date("Y-m-d", strtotime($this->data['PatientLabResult']['test_report_date'.$i]));
					}
                    
                    $this->PatientLabResult->save($this->data);
                    
                    //App::import('Model','PatientActivities');
                    //App::import('Helper', 'Html');$html = new HtmlHelper();
                    $lab_result_id = $this->data['PatientLabResult']['lab_result_id'];
                    
                    //$PatientActivities= new PatientActivities();
                    //$PatientActivities->addActivitiesItem($this->data['PatientLabResult']['ordered_by_id'], $this->data['PatientLabResult']['test_name'], "Labs", "Outside Labs", $this->data['PatientLabResult']['status'], $patient_id, $lab_result_id , $editlink);                    
                    $this->PatientLabResult->saveAudit('Update');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $lab_result_id = (isset($this->params['named']['lab_result_id'])) ? $this->params['named']['lab_result_id'] : "";
                    $items = $this->PatientLabResult->find(
                            'first',
                            array(
                                'conditions' => array('PatientLabResult.lab_result_id' => $lab_result_id)
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
                    $ids = $this->data['PatientLabResult']['lab_result_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientLabResult->delete($id, false);
                        //App::import('Model','PatientActivities');                        
                        //$PatientActivities= new PatientActivities();
                        //$PatientActivities->deleteActivitiesItem( $id , "Labs", "Outside Labs");

                        $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->PatientLabResult->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
                $this->set('PatientLabResult', $this->sanitizeHTML($this->paginate('PatientLabResult', array('PatientLabResult.patient_id' => $patient_id, 'PatientLabResult.order_type' => $labs_setup))));
                $this->PatientLabResult->saveAudit('View');
            }
        }
    }
	
	public function in_house_administered_by()
	{
		if (!empty($this->data))
		{
			$search_keyword = ''.$this->data['autocomplete']['keyword'];
			$search_limit = $this->data['autocomplete']['limit'];
			$referred_by_items = $this->UserAccount->find('all',
			array('conditions' => array('OR' => array('UserAccount.firstname LIKE ' => $search_keyword.'%', 'UserAccount.lastname LIKE ' => $search_keyword.'%'), array('AND' =>array('UserAccount.role_id' => array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID, EMR_Roles::MEDICAL_ASSISTANT_ROLE_ID)))),
				'order' => array('UserAccount.firstname' => 'asc', 'UserAccount.lastname' => 'asc'),
				'limit' => $search_limit
			));

			$data_array = array();

			foreach($referred_by_items as $referred_by_item)
			{
				$data_array[] = $referred_by_item['UserAccount']['firstname'].' '.$referred_by_item['UserAccount']['lastname'].'|'.$referred_by_item['UserAccount']['user_id'];
			}

			echo implode("\n", $data_array);
		}
		exit();
	}


    public function in_house_work_radiology()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("PatientRadiologyResult");
        $this->loadModel("EncounterMaster");

        $user = $this->Session->read('UserAccount');
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch($task)
        {
					
						case 'save_file':
							$point_of_care_id = $this->params['named']['point_of_care_id'];
							
							if (isset($this->params['form']['name'])) {
									$this->EncounterPointOfCare->id = $point_of_care_id;
									$this->EncounterPointOfCare->saveField('file_upload', $this->params['form']['name']);
							}
							
							exit();
							break;						
					
						case 'remove_file':
							$point_of_care_id = $this->params['named']['point_of_care_id'];
							
							if (isset($this->params['form']['delete'])) {
									$this->EncounterPointOfCare->id = $point_of_care_id;
									$file = $this->EncounterPointOfCare->field('file_upload');
									if ($file) {
										@unlink(WWW_ROOT . ltrim($file, DIRECTORY_SEPARATOR));
									}
									
									$this->EncounterPointOfCare->saveField('file_upload', null);
									
							}
							
							exit();
							break;					
					
						case 'download_file':
									$point_of_care_id = $this->params['named']['point_of_care_id'];
							
									$this->EncounterPointOfCare->id = $point_of_care_id;
									$file = $this->EncounterPointOfCare->field('file_upload');
									if ($file) {
										
										$filename = explode(DIRECTORY_SEPARATOR, $file);
										$filename = array_pop($filename);
										$tmp = explode('_', $filename);
										unset($tmp[0]);
										$filename = implode('_', $tmp);										
										
										header("Content-Type: application/force-download");
										header("Content-Type: application/octet-stream");
										header("Content-Type: application/download");										
										header('Content-Disposition: attachment; filename="'.$filename.'"');										
										header("Cache-Control: no-cache, must-revalidate");
										header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
										readfile(WWW_ROOT . ltrim($file, DIRECTORY_SEPARATOR));
									}
									
									
							
							exit();
							break;					
					
            case "upload_file":
            {
                if (!empty($_FILES))
                {
                    $tempFile = $_FILES['file_upload']['tmp_name'];
                    $targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
                    $targetFile =  str_replace('//','/',$targetPath) . $_FILES['file_upload']['name'];

                    move_uploaded_file($tempFile,$targetFile);
                    echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
                }

                exit;
            } break;
            case "addnew":
            {
                if(!empty($this->data))
                {

                    $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                    $this->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                    $this->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                    $this->EncounterPointOfCare->create();
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
                    
                    $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPointOfCare']['radiology_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['radiology_date_performed']));
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
                    
                    $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                    $items = $this->EncounterPointOfCare->find(
                            'first',
                            array(
                                'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
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
                    $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPointOfCare->delete($id, false);
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
                $encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
                /*$result = array();
                if($encounter_items)
                {
                    foreach($encounter_items as $encounter_item)
                    {
                       $result[] = $encounter_item['encounter_id'];
                    }
                }*/

								if ($encounter_items) {
									$this->paginate['EncounterPointOfCare'] = array(
										'conditions' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Radiology'),
										'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
									);

								} else {
									$this->paginate['EncounterPointOfCare'] = array(
										'conditions' => array('EncounterPointOfCare.encounter_id' => null),
									);
								}
								$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));
								
			
                //$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Radiology'))));

                $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Radiology - Point of Care');
            } break;
        }
    }

    public function radiology_results()
    {
        $this->layout = "blank";
        $this->loadModel("PatientRadiologyResult");
        $this->loadModel("DirectoryLabFacility");
        $this->loadModel("Icd");
        $this->Icd->setVersion();
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        switch($task)
        {
            case "load_Icd9_autocomplete":
            {
                if (!empty($this->data))
                {
                    $this->Icd->execute($this, $task);
                }
                exit();
            } break;

            case "labname_load":
            {
                if (!empty($this->data))
                {
                    $search_keyword = $this->data['autocomplete']['keyword'];
                    $lab_items = $this->DirectoryLabFacility->find('all',
                                array(
                                    'conditions' => array('DirectoryLabFacility.lab_facility_name LIKE ' => '%'.$search_keyword.'%'))

                    );
                    $data_array = array();

                    foreach($lab_items as $lab_item)
                    {
                        $data_array[] = $lab_item['DirectoryLabFacility']['lab_facility_name'].'|'.$lab_item['DirectoryLabFacility']['address_1'] . '|' . $lab_item['DirectoryLabFacility']['address_2'] . '|' . $lab_item['DirectoryLabFacility']['city'] . '|' . $lab_item['DirectoryLabFacility']['state'].'|'.$lab_item['DirectoryLabFacility']['zip_code'] .'|'.$lab_item['DirectoryLabFacility']['country'];
                    }

                    echo implode("\n", $data_array);
                }
                exit();
            } break;

            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->data['PatientRadiologyResult']['patient_id'] = $patient_id;
                    //$this->data['PatientRadiologyResult']['plan_radiology_id'] = 0;
										
										if (isset($this->data['PatientRadiologyResult']['plan_radiology_id'])) {
											unset($this->data['PatientRadiologyResult']['plan_radiology_id']);
										}										
										
                    $this->data['PatientRadiologyResult']['diagnosis'] = $this->data['PatientRadiologyResult']['diagnosis'];
                    $this->data['PatientRadiologyResult']['icd_code'] = $this->data['PatientRadiologyResult']['icd_code'];
										
										$date_ordered = explode('/', trim($this->data['PatientRadiologyResult']['date_ordered']));
										if (count($date_ordered) == 3) {
											$date_ordered = __date('Y-m-d', strtotime($date_ordered[2].'-'.$date_ordered[0].'-'.$date_ordered[1] ));
										}
										
										$report_date = explode('/', trim($this->data['PatientRadiologyResult']['report_date']));
										if (count($report_date) == 3) {
											$report_date = __date('Y-m-d', strtotime($report_date[2].'-'.$report_date[0].'-'.$report_date[1] ));
										}
										
										
                    $this->data['PatientRadiologyResult']['date_ordered'] = $date_ordered;
                    $this->data['PatientRadiologyResult']['report_date'] = $report_date;
                    $this->data['PatientRadiologyResult']['modified_timestamp'] = __date("Y-m-d H:i:s");
                    $this->data['PatientRadiologyResult']['modified_user_id'] = $this->user_id;
                    $this->PatientRadiologyResult->create();
                    $this->PatientRadiologyResult->save($this->data);
                    //$radiology_result_id = $this->PatientRadiologyResult->getLastInsertId();
                    /*App::import('Model','PatientActivities');
                    $PatientActivities= new PatientActivities();
                    $editlink = Router::url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_radiology' => 2, 'task' => 'edit', 'patient_id' => $patient_id, 'radiology_result_id' => $radiology_result_id), array('escape' => false));
                    $PatientActivities->addActivitiesItem($this->data['PatientRadiologyResult']['ordered_by_id'], $this->data['PatientRadiologyResult']['test_name'], "Radiology", "Outside Radiology", $this->data['PatientRadiologyResult']['status'], $patient_id, $radiology_result_id , $editlink);*/

                    $this->PatientRadiologyResult->saveAudit('New');
										
										$attachment = trim($this->data['PatientRadiologyResult']['attachment']);
										if ($attachment) {
											
											if (file_exists($this->paths['temp'] . $attachment)) {
												
												$this->paths['patient_encounter_radiology'] = 
													$this->paths['patients'] . $patient_id . DS . 'radiology' . DS . '0' . DS;
												
												UploadSettings::createIfNotExists($this->paths['patient_encounter_radiology']);
												
												rename($this->paths['temp'] . $attachment, $this->paths['patient_encounter_radiology'] . $attachment);
											}
										}
										
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
										
										$date_ordered = explode('/', trim($this->data['PatientRadiologyResult']['date_ordered']));
										if (count($date_ordered) == 3) {
											$date_ordered = __date('Y-m-d', strtotime($date_ordered[2].'-'.$date_ordered[0].'-'.$date_ordered[1] ));
										}
										
										$report_date = explode('/', trim($this->data['PatientRadiologyResult']['report_date']));
										if (count($report_date) == 3) {
											$report_date = __date('Y-m-d', strtotime($report_date[2].'-'.$report_date[0].'-'.$report_date[1] ));
										}
										
										
                    $this->data['PatientRadiologyResult']['date_ordered'] = $date_ordered;
                    $this->data['PatientRadiologyResult']['report_date'] = $report_date;										
										
									
                    $this->PatientRadiologyResult->save($this->data);
                   /* App::import('Model','PatientActivities');
                    $PatientActivities= new PatientActivities();
                    $radiology_result_id = $this->data['PatientRadiologyResult']['radiology_result_id'] = $radiology_result_id;
                    $editlink = Router::url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_radiology' => 2, 'task' => 'edit', 'patient_id' => $patient_id, 'radiology_result_id' => $radiology_result_id), array('escape' => false));
                    $PatientActivities->addActivitiesItem($this->data['PatientRadiologyResult']['ordered_by_id'], $this->data['PatientRadiologyResult']['test_name'], "Radiology", "Outside Radiology", $this->data['PatientRadiologyResult']['status'], $patient_id, $radiology_result_id , $editlink);*/
    
                    $this->PatientRadiologyResult->saveAudit('Update');
										$attachment = trim($this->data['PatientRadiologyResult']['attachment']);
										if ($attachment) {
											
											if (file_exists($this->paths['temp'] . $attachment)) {
												$this->paths['patient_id'] = $this->paths['patients'] . intval($patient_id) . DS;
												UploadSettings::createIfNotExists($this->paths['patient_id']);
												
												copy($this->paths['temp'] . $attachment, $this->paths['patient_id'] . $attachment);
											}
										}
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $radiology_result_id= (isset($this->params['named']['radiology_result_id'])) ? $this->params['named']['radiology_result_id'] : "";
                    //echo $family_history_id;
                    $items = $this->PatientRadiologyResult->find(
                            'first',
                            array(
                                'conditions' => array('PatientRadiologyResult.radiology_result_id' => $radiology_result_id)
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
                    $ids = $this->data['PatientRadiologyResult']['radiology_result_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientRadiologyResult->delete($id, false);
                       /* App::import('Model','PatientActivities');
                        $PatientActivities= new PatientActivities();
                        $PatientActivities->deleteActivitiesItem($id, "Radiology", "Outside Radiology");
*/
                        $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->PatientRadiologyResult->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
			    $this->paginate['PatientRadiologyResult'] = array(
                    'conditions' => array('PatientRadiologyResult.patient_id' => $patient_id),
			        'order' => array('PatientRadiologyResult.modified_timestamp' => 'DESC')
                    );
				
                $this->set('PatientRadiologyResult', $this->sanitizeHTML($this->paginate('PatientRadiologyResult')));
				
                //$this->set('PatientRadiologyResult', $this->sanitizeHTML($this->paginate('PatientRadiologyResult', array('PatientRadiologyResult.patient_id' => $patient_id))));
                $this->PatientRadiologyResult->saveAudit('View');
             }
        }
    }

    public function in_house_work_procedures()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        switch($task)
        {
            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                    $this->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                    $this->EncounterPointOfCare->create();
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
                    
                    
                    $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPointOfCare']['procedure_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['procedure_date_performed']));
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
                    
                    $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                    $items = $this->EncounterPointOfCare->find(
                            'first',
                            array(
                                'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
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
                    $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPointOfCare->delete($id, false);
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
                $encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
												
								if ($encounter_items) {
									$this->paginate['EncounterPointOfCare'] = array(
										'conditions' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Procedure'),
										'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
									);

								} else {
									$this->paginate['EncounterPointOfCare'] = array(
										'conditions' => array('EncounterPointOfCare.encounter_id' => null),
									);
								}
								$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));
				
                //$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Procedure'))));

                $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Procedures - Point of Care');
            } break;
        }
    }

    public function procedures()
    {
        $this->loadModel("EncounterPlanProcedure");
        $this->loadModel("EncounterMaster");
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        switch($task)
        {
            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPlanProcedure']['date_ordered'] = __date("Y-m-d");
                    $this->data['EncounterPlanProcedure']['patient_id'] = $patient_id;
                    $this->data['EncounterPlanProcedure']['ordered_by_id'] = 0;
                    
                    $this->EncounterPlanProcedure->create();
                    $this->EncounterPlanProcedure->save($this->data);
                    $plan_procedures_id = $this->EncounterPlanProcedure->getLastInsertId();  
                    
                    if(isset($this->data['EncounterPlanProcedure']['print_save_add']) && $this->data['EncounterPlanProcedure']['print_save_add'] == 1)
                    {
						$this->Session->write('last_saved_id', $plan_procedures_id);
					}
                    $editlink = Router::url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_procedure' => 2, 'task' => 'edit', 'patient_id' => $patient_id, 'plan_procedures_id' => $plan_procedures_id), array('escape' => false));

                    $this->EncounterPlanProcedure->saveAudit('New');
					
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPlanProcedure']['date_ordered'] = __date("Y-m-d");
                    $this->EncounterPlanProcedure->save($this->data);
                    //App::import('Model','PatientActivities');
                    //$PatientActivities= new PatientActivities();
                    $plan_procedures_id = $this->data['EncounterPlanProcedure']['plan_procedures_id'];
                    
                    $editlink = Router::url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information','view_procedure' => 2, 'task' => 'edit', 'patient_id' => $patient_id, 'plan_procedures_id' => $plan_procedures_id), array('escape' => false));
                    //$PatientActivities->addActivitiesItem($this->data['EncounterPlanProcedure']['ordered_by_id'], $this->data['EncounterPlanProcedure']['test_name'], "Procedure", "Outside Procedure", $this->data['EncounterPlanProcedure']['status'], $patient_id, $plan_procedures_id , $editlink);

                    $this->EncounterPlanProcedure->saveAudit('Update');

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $plan_procedures_id = (isset($this->params['named']['plan_procedures_id'])) ? $this->params['named']['plan_procedures_id'] : "";
                    //echo $family_history_id;
                    $items = $this->EncounterPlanProcedure->find(
                            'first',
                            array(
                                'conditions' => array('EncounterPlanProcedure.plan_procedures_id' => $plan_procedures_id)
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
                    $ids = $this->data['EncounterPlanProcedure']['plan_procedures_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPlanProcedure->delete($id, false);
                        //App::import('Model','PatientActivities');
                        //$PatientActivities= new PatientActivities();
                        //$PatientActivities->deleteActivitiesItem($id, "Procedure", "Outside Procedure");

                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->EncounterPlanProcedure->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
                $this->paginate['EncounterPlanProcedure'] = array(
                    'conditions' => array('EncounterPlanProcedure.patient_id' => $patient_id),
			        'order' => array('EncounterPlanProcedure.modified_timestamp' => 'DESC')
                    );
                
              $combine = $this->Session->read('UserAccount.assessment_plan') ? true : false;

              if ($combine) {
                $this->paginate['EncounterPlanProcedure']['group'] = array(
                    'EncounterPlanProcedure.encounter_id', 'EncounterPlanProcedure.test_name'
                );

                $this->paginate['EncounterPlanProcedure']['contain'] = array(
                    'EncounterMaster' => array(
                        'fields' => array('encounter_id', 'patient_id'),
                        'EncounterAssessment' => array(
                            'fields' => array('diagnosis'),
                        )), 
                );
              } else {
                $this->paginate['EncounterPlanProcedure']['contain'] = array(
                    'EncounterMaster' => array(
                        'fields' => array('encounter_id', 'patient_id'),
                    ), 
                );          
              }
              $this->set('combine', $combine);                
                
                
                $this->set('EncounterPlanProcedure', $this->sanitizeHTML($this->paginate('EncounterPlanProcedure')));
				
                //$this->set('EncounterPlanProcedure', $this->sanitizeHTML($this->paginate('EncounterPlanProcedure', array('EncounterMaster.encounter_id' => $result))));
                $this->EncounterPlanProcedure->saveAudit('View');
            } break;
        }
    }
	
	public function plan_labs()
	{
		$this->layout = "blank";
		$this->loadModel("EncounterPlanLab");
		$this->loadModel("DirectoryLabFacility");
		$practice_settings = $this->Session->read("PracticeSetting");
		
        $labs_setup =  $practice_settings['PracticeSetting']['labs_setup'];
        $this->set('labs_setup',$labs_setup);
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		$plan_labs_id = (isset($this->params['named']['plan_labs_id'])) ? $this->params['named']['plan_labs_id'] : "";
		
		$lab_facility_items = $this->DirectoryLabFacility->find('all');
        $this->set('LabFacilityCount', count($lab_facility_items));
		
		switch($task)
		{
			case "addnew":
                {
                    if(!empty($this->data))
                    {
					    $this->data['EncounterPlanLab']['patient_id'] = $patient_id;
						$this->data['EncounterPlanLab']['encounter_id'] = 0;
						$this->data['EncounterPlanLab']['diagnosis'] = $this->data['EncounterPlanLab']['reason'];						
						$this->data['EncounterPlanLab']['date_ordered'] = __date("Y-m-d");
						$this->data['EncounterPlanLab']['modified_user_id'] = $this->user_id;
						$this->data['EncounterPlanLab']['ordered_by_id'] = $this->user_id;
						$this->data['EncounterPlanLab']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$this->EncounterPlanLab->create();
						$this->EncounterPlanLab->save($this->data);
						$plan_labs_id = $this->EncounterPlanLab->getLastInsertId();  
						
						
						if(isset($this->data['EncounterPlanLab']['print_save_add']) && $this->data['EncounterPlanLab']['print_save_add'] == 1)
						{
							$this->Session->write('last_saved__lab_id', $plan_labs_id);
						}
						
                        $ret = array();
					
                        echo json_encode($ret);
                        exit;
						
                    }
            }break;
			case "edit":
			{
				if(!empty($this->data))
                {
					$this->data['EncounterPlanLab']['diagnosis'] = $this->data['EncounterPlanLab']['reason'];
                    $this->EncounterPlanLab->save($this->data);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    
                    $items = $this->EncounterPlanLab->find('first',array('conditions' => array('EncounterPlanLab.plan_labs_id' => $plan_labs_id)));
                    $this->set('EditItem', $this->sanitizeHTML($items));
                }
			} break;
			case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['EncounterPlanLab']['plan_labs_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPlanLab->delete($id, false);

                       $ret['delete_count']++;
                    }
                }

                echo json_encode($ret);
                exit;
            }
			default:
			{

		$this->paginate['EncounterPlanLab'] = array(
                    'conditions' => array('OR'=>array('EncounterPlanLab.patient_id' => $patient_id, 'EncounterMaster.patient_id' => $patient_id)),
			        'order' => array('EncounterPlanLab.modified_timestamp' => 'DESC')
                    );
        $combine = $this->Session->read('UserAccount.assessment_plan') ? true : false;
        
        if ($combine) {
          $this->paginate['EncounterPlanLab']['group'] = array(
              'EncounterPlanLab.encounter_id', 'EncounterPlanLab.test_name'
          );
          
          $this->paginate['EncounterPlanLab']['contain'] = array(
              'EncounterMaster' => array(
                  'fields' => array('encounter_id', 'patient_id'),
                  'EncounterAssessment' => array(
                      'fields' => array('diagnosis'),
                  )), 
          );
        } else {
			
          $this->paginate['EncounterPlanLab']['contain'] = array(
              'EncounterMaster' => array(
                  'fields' => array('encounter_id', 'patient_id'),
              ), 
          );          
        }
        $this->set('combine', $combine);
        
        
				$encounter_plan_labs = $this->paginate('EncounterPlanLab');
				$this->set("encounter_plan_labs", $this->sanitizeHTML($encounter_plan_labs));
			}
		}
	}
	
	public function plan_labs_electronic()
	{
		$this->layout = "blank";
		$this->loadModel('PatientDemographic');
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		
		$mrn = $this->PatientDemographic->getPatientMRN($patient_id);
		$this->set("mrn", $mrn);
	}
	
	public function plan_radiology()
	{
		$this->layout = "blank";
		$this->loadModel("EncounterPlanRadiology");
		$this->loadModel("DirectoryLabFacility");
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		$plan_radiology_id = (isset($this->params['named']['plan_radiology_id'])) ? $this->params['named']['plan_radiology_id'] : "";
		
		$lab_facility_items = $this->DirectoryLabFacility->find('all');
        $this->set('LabFacilityCount', count($lab_facility_items));
		
		switch($task)
		{
		    case "addnew":
                {
                    if(!empty($this->data))
                    {
					    $this->data['EncounterPlanRadiology']['patient_id'] = $patient_id;
						$this->data['EncounterPlanRadiology']['encounter_id'] = 0;
						$this->data['EncounterPlanRadiology']['diagnosis'] = $this->data['EncounterPlanRadiology']['reason'];
						$this->data['EncounterPlanRadiology']['date_ordered'] = __date("Y-m-d");
						$this->data['EncounterPlanRadiology']['modified_user_id'] = $this->user_id;
						$this->data['EncounterPlanRadiology']['ordered_by_id'] = $this->user_id;
						$this->data['EncounterPlanRadiology']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$this->EncounterPlanRadiology->create();
						$this->EncounterPlanRadiology->save($this->data);
						$plan_radiology_id = $this->EncounterPlanRadiology->getLastInsertId();  
						
						if(isset($this->data['EncounterPlanRadiology']['print_save_add']) && $this->data['EncounterPlanRadiology']['print_save_add'] == 1)
						{
							$this->Session->write('last_saved_id_radiology', $plan_radiology_id);
						}
                        $ret = array();
					
                        echo json_encode($ret);
                        exit;
						
                    }
                }break;
			case "edit":
			{
				if(!empty($this->data))
                {
				    $plan_radiology_id = (isset($this->params['named']['plan_radiology_id'])) ? $this->params['named']['plan_radiology_id'] : "";
				    $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
				    $this->data['EncounterPlanRadiology']['patient_id'] = $patient_id;
					$this->data['EncounterPlanRadiology']['plan_radiology_id'] = $plan_radiology_id;
				    $this->data['EncounterPlanRadiology']['diagnosis'] = $this->data['EncounterPlanRadiology']['reason'];
                    $this->EncounterPlanRadiology->save($this->data);
                    
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $items = $this->EncounterPlanRadiology->find('first',array('conditions' => array('EncounterPlanRadiology.plan_radiology_id' => $plan_radiology_id)));
                    $this->set('EditItem', $this->sanitizeHTML($items));
                }
			} break;
			case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['EncounterPlanRadiology']['plan_radiology_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPlanRadiology->delete($id, false);

                       $ret['delete_count']++;
                    }
                }

                echo json_encode($ret);
                exit;
            }
			default:
			{
        
		 $this->paginate['EncounterPlanRadiology'] = array(
			'conditions' => array('OR'=>array('EncounterPlanRadiology.patient_id' => $patient_id, 'EncounterMaster.patient_id' => $patient_id)),
			'order' => array('EncounterPlanRadiology.modified_timestamp' => 'DESC')
			);
   
        $combine = $this->Session->read('UserAccount.assessment_plan') ? true : false;
        
        if ($combine) {
          $this->paginate['EncounterPlanRadiology']['group'] = array(
              'EncounterPlanRadiology.encounter_id', 'EncounterPlanRadiology.procedure_name'
          );
          
          $this->paginate['EncounterPlanRadiology']['contain'] = array(
              'EncounterMaster' => array(
                  'fields' => array('encounter_id', 'patient_id'),
                  'EncounterAssessment' => array(
                      'fields' => array('diagnosis'),
                  )), 
          );
        } else {
          $this->paginate['EncounterPlanRadiology']['contain'] = array(
              'EncounterMaster' => array(
                  'fields' => array('encounter_id', 'patient_id'),
              ), 
          );          
        }
        $this->set('combine', $combine);
        
        
        
        

				
                $this->set('encounter_plan_radiology', $this->sanitizeHTML($this->paginate('EncounterPlanRadiology')));

                 /*$this->set('encounter_plan_radiology', $this->sanitizeHTML($this->paginate('EncounterPlanRadiology', 
                        array('OR'=>array( 
                         
                            'EncounterPlanRadiology.patient_id' => $patient_id, 'EncounterMaster.patient_id' => $patient_id)
                        ))));*/
			}
		}
	}


    public function in_house_work_immunizations()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        switch($task)
        {
            case "addnew":
            {
                if(!empty($this->data))    
                {
                    $this->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                    $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                    $this->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                    $this->data['EncounterPointOfCare']['vaccine_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['vaccine_date_performed']));
                    $this->data['EncounterPointOfCare']['vaccine_expiration_date'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['vaccine_expiration_date']));
                   
                    $this->data['EncounterPointOfCare']['administered_units'] = ($this->data['EncounterPointOfCare']['administered_units']!="")?$this->data['EncounterPointOfCare']['administered_units']:1;
                    $this->EncounterPointOfCare->create();
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();                    
                    
                    $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Immunization');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPointOfCare']['vaccine_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['vaccine_date_performed']));
                    $this->data['EncounterPointOfCare']['vaccine_expiration_date'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['vaccine_expiration_date']));
                    $this->data['EncounterPointOfCare']['administered_units'] = ($this->data['EncounterPointOfCare']['administered_units']!="")?$this->data['EncounterPointOfCare']['administered_units']:1;
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
                    
                    $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Immunization');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                    $items = $this->EncounterPointOfCare->find(
                            'first',
                            array(
                                'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
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
                    $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPointOfCare->delete($id, false);

                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Immunization');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            case "mark_none":
            {
                if ($this->data['EncounterPointOfCare']['mark_none'] == "true")
                {
                    $this->data['EncounterPointOfCare']['encounter_id'] = $this->params['named']['encounter_id'];
                    $this->data['EncounterPointOfCare']['order_type'] = "Marked as None";
                    $this->data['EncounterPointOfCare']['immunization_none'] = $user['user_id'];
                    $this->EncounterPointOfCare->create();
                    $this->EncounterPointOfCare->save($this->data);
                }
                else
                {
                    $this->EncounterPointOfCare->deleteAll(array("AND" => array("EncounterPointOfCare.encounter_id" => $this->params['named']['encounter_id'], "order_type" => "Marked as None", "immunization_none" => $user['user_id'])), false);
                }
            } break;
            case "review_by":
            {
                if ($this->data['EncounterPointOfCare']['review_by'] == "true")
                {
                    $this->data['EncounterPointOfCare']['encounter_id'] = $this->params['named']['encounter_id'];
                    $this->data['EncounterPointOfCare']['order_type'] = "Reviewed by";
                    $this->data['EncounterPointOfCare']['immunization_reviewed'] = $user['user_id'];
                    $this->data['EncounterPointOfCare']['immunization_reviewed_by'] = $user['firstname'].' '.$user['lastname'];
                    $this->data['EncounterPointOfCare']['immunization_reviewed_time'] = __date("Y-m-d, H:i:s");
                    $this->EncounterPointOfCare->create();
                    $this->EncounterPointOfCare->save($this->data);
                }
                else
                {
                    $this->EncounterPointOfCare->deleteAll(array("AND" => array("EncounterPointOfCare.encounter_id" => $this->params['named']['encounter_id'], "order_type" => "Reviewed by", "immunization_reviewed" => $user['user_id'])), false);
                }
            } break;
            default:
            {
			
			    $this->paginate['EncounterPointOfCare'] = array(
                    'conditions' => array('EncounterPointOfCare.order_type' => 'Immunization','EncounterPointOfCare.patient_id' => $patient_id),
			        'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
                    );
				
                $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));
			
                /*$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', 
                        array( 
                            'EncounterPointOfCare.order_type' => 'Immunization',
                            'EncounterPointOfCare.patient_id' => $patient_id
                        ))));*/
                /*$encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
                var_dump($encounter_items);
                $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('OR' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Immunization'),'AND' => array('EncounterPointOfCare.encounter_id' =>$encounter_id
                                        )))));
                var_dump(EncounterPointOfCare);
                  // var_dump($this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Immunization'))));
                $mark_none = $this->EncounterPointOfCare->find(
                        'first',
                        array(
                            'conditions' => array('AND' => array('EncounterMaster.encounter_id' =>$encounter_items, 'EncounterPointOfCare.order_type' => 'Marked as None', 'immunization_none' => $user['user_id']))
                        )
                );
                if (!empty($mark_none))
                {
                    $this->set('MarkedNone', $this->sanitizeHTML($mark_none));
                }

                $review_by = $this->EncounterPointOfCare->find(
                        'first',
                        array(
                            'conditions' => array('AND' => array('EncounterMaster.encounter_id' => $encounter_items, 'order_type' => 'Reviewed by', 'immunization_reviewed' => $user['user_id']))
                        )
                );
                if (!empty($review_by))
                {
                    $this->set('ReviewedBy', $this->sanitizeHTML($review_by));
                }*/

                $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Immunization');
            } break;
        }
    }
	
	 /**
	 * To show the immunizations chart
	 * for a patient
	 * 
	 */
	
	public function immunizations_chart()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		$this->loadModel('PatientDemographic');
		$patient_info = $this->PatientDemographic->find('first', array(
			'fields' => 'dob', 'conditions' => array('patient_id' => $patient_id, 'dob !=' => ''), 'recursive' => -1
		));
		if(empty($patient_info)) 
			exit('Patient\'s DOB should not be Empty');
		$dob_timestamp = __date("Y-m-d",strtotime($patient_info['PatientDemographic']['dob']));
		$month_intervals = array(
			1 => array('month' => 'birth', 'label' => 'Birth'),
			2 => array('month' => array(1, 2), 'label' => '1 month'),
			3 => array('month' => array(2, 3), 'label' => '2 months'),
			4 => array('month' => array(4, 5), 'label' => '4 months'),
			5 => array('month' => array(6, 7), 'label' => '6 months'),
			6 => array('month' => array(12, 13), 'label' => '12 months'),
			7 => array('month' => array(15, 16), 'label' => '15 months'),
			8 => array('month' => array(18, 19), 'label' => '18 months'),
			9 => array('month' => array(19, 24), 'label' => '19-23 months'),
			10 => array('month' => array(24, 48), 'label' => '2-3 years'),
			11 => array('month' => array(48, 84), 'label' => '4-6 years'),
			12 => array('month' => array(84, 132), 'label' => '7-10 years'),
			13 => array('month' => array(132, 156), 'label' => '11-12 years'),
			14 => array('month' => array(156, 228), 'label' => '13-18 years'),
		);
		foreach($month_intervals as $key=>$month_interval)
		{	
			if($month_interval['month']=='birth') {
				$month_intervals[$key]['schedule_time'] = __date("Y-m-d", strtotime($dob_timestamp . "+1 week"));
			}
			else if(is_array($month_interval['month'])) {
				$month_intervals[$key]['schedule_time'][0] = __date("Y-m-d", strtotime($dob_timestamp . "+{$month_interval['month'][0]} month"));
				$month_intervals[$key]['schedule_time'][1] = __date("Y-m-d", strtotime($dob_timestamp . "+{$month_interval['month'][1]} month"));
			}
		}
		//pr($month_intervals);
		$immunizations = array(
		array('label' => 'Hepatitis B', 'cvx_code' => array('08', 42, 43, 44, 45, 51, 104, 110), 'columns' => array(1, 2, 3, 5, 6, 7, 8, 12, 13, 14), 'highlight1' => array(2, 3, 5, 6, 7, 8,13), 'highlight2' => array(), 'highlight3' => array(14)),
		array('label' => 'Rotavirus', 'cvx_code' => array(116, 119), 'columns' => array(3, 4, 5), 'highlight1' => array(), 'highlight2' => array(), 'highlight3' => array()),
		array('label' => 'Diphtheria, Tetanus, Pertussis', 'cvx_code' => array(20, 50, 106, 110, 120, 130, 115), 'columns' => array(3, 4, 5, 7, 8, 11, 13, 14), 'highlight1' => array(7, 8, 11, 13), 'highlight2' => array(), 'highlight3' => array(14)),
		array('label' => 'Haemophilus influenzae type b', 'cvx_code' => array(17, 46, 47, 48, 49), 'columns' => array(3, 4, 5, 6, 7), 'highlight1' => array(6, 7), 'highlight2' => array(), 'highlight3' => array()),
		array('label' => 'Pneumococcal', 'cvx_code' => array(100, 133), 'columns' => array(3, 4, 5, 6, 7, 10, 11, 12, 13, 14), 'highlight1' => array(6, 7), 'highlight2' => array(10, 11), 'highlight3' => array()),
		array('label' => 'Inactivated Poliovirus', 'cvx_code' => array(10, 110, 120, 130), 'columns' => array(3, 4, 5, 6, 7, 8, 11, 12, 13, 14), 'highlight1' => array(5,6,7,8,11), 'highlight2' => array(), 'highlight3' => array()),
		array('label' => 'Influenza', 'cvx_code' => array(144, 140, 141, 16, 111, 135, 88), 'columns' => array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14), 'highlight1' => array(5,6,7,8,9,10,11,12,13,14), 'highlight2' => array(), 'highlight3' => array()),
		array('label' => 'Measles, Mumps, Rubella', 'cvx_code' => array('03', '05', '06', '07'), 'columns' => array(6, 7, 11, 12, 13, 14), 'highlight1' => array(6,7,11), 'highlight2' => array(), 'highlight3' => array(12,13,14)),
		array('label' => 'Varicella', 'cvx_code' => array(21), 'columns' => array(6, 7, 11, 12, 13, 14), 'highlight1' => array(6,7,11), 'highlight2' => array(), 'highlight3' => array(12,13,14)),
		array('label' => 'Hepatitis A', 'cvx_code' => array(52, 83, 84, 85, 104), 'columns' => array(6, 7, 8, 9, 10, 11, 12, 13, 14), 'highlight1' => array(6,7,8,9), 'highlight2' => array(10, 11, 12, 13,14), 'highlight3' => array()),
		array('label' => 'Meningococcal', 'cvx_code' => array(32, 114, 136), 'columns' => array(10, 11, 12, 13, 14), 'highlight1' => array(13), 'highlight2' => array(10, 11, 12), 'highlight3' => array(14)),
		array('label' => 'Human Papillomavirus', 'cvx_code' => array(62, 118), 'columns' => array(13, 14), 'highlight1' => array(13), 'highlight2' => array(), 'highlight3' => array(14)),
		);
		$chart	= array();
		foreach($immunizations as $immunization) {	 
			$data = $this->EncounterPointOfCare->getPatientImmu($patient_id, $immunization['cvx_code']);
			$dates = Set::extract('n/EncounterPointOfCare/vaccine_date_performed', $data); //pr($dates);
			$tmpChart = array();
			foreach($month_intervals as $key=>$month_interval) { 
				if(in_array($key, $immunization['columns'])) {
					foreach($dates as $date) {
						$date1 = new DateTime($date);
						if($month_interval['month']=='birth') { 
							$date2 = new DateTime($month_interval['schedule_time']);
							$interval = $date1->diff($date2);
							if($interval->y==0 && $interval->m==0 && $interval->d<=7) {
								$tmpChart[$key] = 'Valid';//pr($interval);	
								break;
							} 
						}
						else if(is_array($month_interval['month'])) {							
							$firstDate1 = strtotime($month_interval['schedule_time'][0]);
							$firstDate2 = strtotime($month_interval['schedule_time'][1]);
							$checkDate = strtotime($date);
							if($checkDate >= $firstDate1 && $checkDate < $firstDate2) {
								$tmpChart[$key] = 'Valid';	
								break;
							} 
						} 
					}
					if(!isset($tmpChart[$key])) {
						$tmpChart[$key] = 'Missing';
					}
				} else {
					$tmpChart[$key] = 'blank';
				}
			}
			$chart[] = array('label' => $immunization['label'], 'data' => $tmpChart, 'highlight1' => $immunization['highlight1'], 'highlight2' => $immunization['highlight2'], 'highlight3' => $immunization['highlight3']);
		}
		//pr($chart);
		$this->Set(compact('month_intervals', 'chart'));
	}
	
	public function immunizations_record()
    {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        $this->loadModel("PatientDemographic");
		$demographics = $this->PatientDemographic->getPatient($patient_id);
		$this->set("demographics", (object) $demographics);

		$this->set('admin_path',$this->url_abs_paths['administration']);

        $this->loadModel("PracticeProfile");
        $PracticeProfile = $this->PracticeProfile->find('first');
        $provider = $PracticeProfile['PracticeProfile'];
		$this->set("provider", (object) $provider);

		$this->loadModel('ScheduleCalendar');
		$schedule = $this->ScheduleCalendar->find('first', array(
			'fields' => array('PracticeLocation.*'),
			'conditions' => array('ScheduleCalendar.patient_id' => $patient_id),
			'order' => array('ScheduleCalendar.date' => 'DESC', 'ScheduleCalendar.starttime' => 'DESC')
			));

		$this->set('location',$schedule['PracticeLocation']);
		
        $this->loadModel("EncounterPointOfCare");
		$patient_immunizations_items = $this->EncounterPointOfCare->find('all', array('conditions' => array('order_type' => 'Immunization', 'EncounterPointOfCare.patient_id' => $patient_id), 'order' => array('vaccine_name' => 'ASC')));
		$this->set("patient_immunizations_items", $patient_immunizations_items);

		$report = $this->render(null, null, 'immunizations_record');

		App::import('Helper', 'Html');
		$html = new HtmlHelper();
		
		$url = $this->paths['temp'];
		$url = str_replace('//', '/', $url);
		
		$pdffile = 'patient_' . $patient_id . '_immunizations_record.pdf';
		
		//format report, by removing hide text
		$reportmod = preg_replace('/(<span class="hide_for_print">.+?)+(<\/span>)/i', '', $report);

		//PDF file creation
		//site::write(pdfReport::generate($reportmod, $url . $pdffile), $url . $pdffile);
		
		// Instead of writing a pdf file, just right the html output for later retrieval;
		$tmp_file = 'patient_' . $patient_id . '_immunizations_record.tmp';
		site::write($reportmod, $url . $tmp_file);

		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		if ($task == "get_report_pdf")
		{
			// Get path were files are being saved/read
			$targetPath = str_replace('//', '/', $this->paths['temp']);
			
			// Html version file
			$tmp_file = 'patient_' . $patient_id . '_immunizations_record.tmp';
			
			// PDF file
			$file = 'patient_' . $patient_id . '_immunizations_record.pdf';

			$targetFile = $targetPath . $file;

			// Read contents of report
			$report = file_get_contents($targetPath . $tmp_file);
			
			// Write pdf
			site::write(pdfReport::generate($report, "landscape"), $targetFile);
			
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
	}

    public function in_house_work_injections()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        switch($task)
        {
            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                    $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                    $this->data['EncounterPointOfCare']['injection_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['injection_date_performed']));
                    $this->data['EncounterPointOfCare']['injection_expiration_date'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['injection_expiration_date']));
                   
                    $this->data['EncounterPointOfCare']['injection_unit'] = ($this->data['EncounterPointOfCare']['injection_unit']!="")?$this->data['EncounterPointOfCare']['injection_unit']:1;
                    $this->EncounterPointOfCare->create();
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
                    
                    $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Injection');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPointOfCare']['injection_date_performed'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['injection_date_performed']));
                    $this->data['EncounterPointOfCare']['injection_expiration_date'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['injection_expiration_date']));
                    $this->data['EncounterPointOfCare']['injection_unit'] = ($this->data['EncounterPointOfCare']['injection_unit']!="")?$this->data['EncounterPointOfCare']['injection_unit']:1;
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();

                    $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Injection');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                    $items = $this->EncounterPointOfCare->find(
                            'first',
                            array(
                                'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
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
                    $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPointOfCare->delete($id, false);

                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Injection');
                    }
                }

                echo json_encode($ret);
                exit;
            } break;
            /*case "mark_none":
            {
                if ($this->data['EncounterPointOfCare']['mark_none'] == "true")
                {
                    $this->data['EncounterPointOfCare']['encounter_id'] = $this->params['named']['encounter_id'];
                    $this->data['EncounterPointOfCare']['order_type'] = "Marked as None";
                    $this->data['EncounterPointOfCare']['injection_none'] = $user['user_id'];
                    $this->EncounterPointOfCare->create();
                    $this->EncounterPointOfCare->save($this->data);
                }
                else
                {
                    $this->EncounterPointOfCare->deleteAll(array("AND" => array("EncounterPointOfCare.encounter_id" => $this->params['named']['encounter_id'], "order_type" => "Marked as None", "injection_none" => $user['user_id'])), false);
                }
            }
            break;*/

            /*case "review_by":
            {
                if ($this->data['EncounterPointOfCare']['review_by'] == "true")
                {
                    $this->data['EncounterPointOfCare']['encounter_id'] = $this->params['named']['encounter_id'];
                    $this->data['EncounterPointOfCare']['order_type'] = "Reviewed by";
                    $this->data['EncounterPointOfCare']['injection_reviewed'] = $user['user_id'];
                    $this->data['EncounterPointOfCare']['injection_reviewed_by'] = $user['firstname'].' '.$user['lastname'];

                    $this->data['EncounterPointOfCare']['injection_reviewed_time'] = __date("Y-m-d, H:i:s");
                    $this->EncounterPointOfCare->create();
                    $this->EncounterPointOfCare->save($this->data);
                }
                else
                {
                    $this->EncounterPointOfCare->deleteAll(array("AND" => array("EncounterPointOfCare.encounter_id" => $this->params['named']['encounter_id'], "order_type" => "Reviewed by", "injection_reviewed" => $user['user_id'])), false);
                }
            } break;*/
            default:
            {
			      $this->paginate['EncounterPointOfCare'] = array(
                    'conditions' => array('EncounterPointOfCare.order_type' => 'Injection','EncounterPointOfCare.patient_id' => $patient_id),
			        'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
                    );
				
                  $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));
				  
			      /*$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', 
                        array( 
                            'EncounterPointOfCare.order_type' => 'Injection',
                            'EncounterPointOfCare.patient_id' => $patient_id
                        ))));*/
			
			    //$encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
               // $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Injection'))));
               /* $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('OR' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Injection'),'AND' => array('EncounterPointOfCare.patient_id' =>$patient_id
                                        )))));*/
				  						
                /*$mark_none = $this->EncounterPointOfCare->find(
                        'first',
                        array(
                            'conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $this->params['named']['encounter_id'], 'order_type' => 'Marked as None', 'injection_none' => $user['user_id']))
                        )
                );
                if (count($mark_none))
                {
                    $this->set('MarkedNone', $this->sanitizeHTML($mark_none));
                }

                $review_by = $this->EncounterPointOfCare->find(
                        'first',
                        array(
                            'conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $this->params['named']['encounter_id'], 'order_type' => 'Reviewed by', 'injection_reviewed' => $user['user_id']))
                        )
                );
                if (count($review_by))
                {
                    $this->set('ReviewedBy', $this->sanitizeHTML($review_by));
                }*/

                $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Imm/Injections - Injection');

            } break;
        }
    }
	
	public function in_house_work_meds()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");
		$this->loadModel("Unit");

        $user = $this->Session->read('UserAccount');
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$this->set("units", $this->Unit->find('all'));
		
        switch($task)
        {
            case "edit":
            {
                if(!empty($this->data))
                {
					$this->data['EncounterPointOfCare']['drug_date_given'] = __date("Y-m-d H:i:s", strtotime($this->data['EncounterPointOfCare']['drug_date_given'] . ' ' . $this->data['EncounterPointOfCare']['drug_given_time'] . ':00'));
					
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
                    
                    $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Medication List - Point of Care');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                    $items = $this->EncounterPointOfCare->find(
                            'first',
                            array(
                                'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
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
                    $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPointOfCare->delete($id, false);
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Medication List - Point of Care');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
                $encounter_items = $this->EncounterMaster->getEncountersByPatientID($patient_id);
				
								if ($encounter_items) {
									$this->paginate['EncounterPointOfCare'] = array(
										'conditions' => array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Meds'),
										'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
									);

							} else {
								$this->paginate['EncounterPointOfCare'] = array(
									'conditions' => array('EncounterPointOfCare.encounter_id' => null),
								);
							}
							$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));
								
				
                //$this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare', array('EncounterMaster.encounter_id' => $encounter_items, 'EncounterPointOfCare.order_type' => 'Meds'))));
                $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Medication List - Point of Care');
            } break;
        }
    }

    public function in_house_work_supplies()
    {
        $this->layout = "blank";
        $this->loadModel("EncounterPointOfCare");
        $this->loadModel("EncounterMaster");

        $user = $this->Session->read('UserAccount');
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch($task)
        {
            case "addnew":
            {
                if(!empty($this->data))
                {

                    $this->data['EncounterPointOfCare']['encounter_id'] = 0;
                    $this->data['EncounterPointOfCare']['patient_id'] = $patient_id;
                    $this->data['EncounterPointOfCare']['ordered_by_id'] = $user['user_id'];
                    $this->EncounterPointOfCare->create();
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
                    
                    $this->EncounterPointOfCare->saveAudit('New', 'EncounterPointOfCare', 'Medical Information - Supplies - Point of Care');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->EncounterPointOfCare->save($this->data);
                    $point_of_care_id = $this->EncounterPointOfCare->getLastInsertId();
                    
                    $this->EncounterPointOfCare->saveAudit('Update', 'EncounterPointOfCare', 'Medical Information - Supplies - Point of Care');
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
                    $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                    $items = $this->EncounterPointOfCare->find(
                            'first',
                            array(
                                'conditions' => array('EncounterPointOfCare.point_of_care_id' => $point_of_care_id)
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
                    $ids = $this->data['EncounterPointOfCare']['point_of_care_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPointOfCare->delete($id, false);
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->EncounterPointOfCare->saveAudit('Delete', 'EncounterPointOfCare', 'Medical Information - Supplies - Point of Care');
                    }
                }

                echo json_encode($ret);
                exit;
            }
            default:
            {
                $this->paginate['EncounterPointOfCare'] = array(
                    'conditions' => array('EncounterPointOfCare.patient_id' => $patient_id, 'EncounterPointOfCare.order_type' => 'Supplies'),
			        'order' => array('EncounterPointOfCare.modified_timestamp' => 'DESC')
                    );
				
                  $this->set('EncounterPointOfCare', $this->sanitizeHTML($this->paginate('EncounterPointOfCare')));
                $this->EncounterPointOfCare->saveAudit('View', 'EncounterPointOfCare', 'Medical Information - Supplies - Point of Care');
            } break;
        }
    }

    public function health_maintenance_plans()
    {
        $this->loadModel("EncounterPlanHealthMaintenanceEnrollment");
        $this->layout = "blank";

        $this->EncounterPlanHealthMaintenanceEnrollment->patientExecute($this);
    }
    
    public function load_vitals() {
      $this->loadModel("EncounterVital");
    	$this->layout = 'empty';
    	$patient_id = isset($this->params['named']['patient_id'])? $this->params['named']['patient_id'] : null;
      $this->EncounterVital->patientData($this, $patient_id);
    }    

    public function patient_reminders()
    {
        $this->loadModel("PatientReminder");
        $this->layout = "blank";

		$this->PatientReminder->patientExecute($this);
	}

    public function referrals()
    {
        $this->loadModel("EncounterPlanReferral");
        $this->loadModel("EncounterMaster");
        $this->loadModel("DirectoryReferralList");
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        switch($task)
        {
			
            case "addnew":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPlanReferral']['date_ordered'] = __date("Y-m-d");
										$this->data['EncounterPlanReferral']['patient_id'] = $patient_id;
					
					if(isset($this->data['EncounterPlanReferral']['encounter_id']) && $this->data['EncounterPlanReferral']['encounter_id']!= 0){
						$this->data['EncounterPlanReferral']['visit_summary'] = 1;
					}				
                    $this->EncounterPlanReferral->create();
                    $this->EncounterPlanReferral->save($this->data);
                    $this->EncounterPlanReferral->saveAudit('New');
                    
                    $plan_referral_id = $this->EncounterPlanReferral->getLastInsertId(); 
                    
                    //visit summary attached is selected then only save related information
                    if(isset($this->data['EncounterPlanReferral']['encounter_id']) && $this->data['EncounterPlanReferral']['encounter_id']!= 0){
                    $this->EncounterPlanReferral->getRelatedInfo($plan_referral_id);
					}
                    
                    
                    
					if(isset($this->data['EncounterPlanReferral']['print_save_add']) && $this->data['EncounterPlanReferral']['print_save_add'] == 1)
					{
						$this->Session->write('last_saved_id_referral', $plan_referral_id);
						//$this->Session->write('last_encounter_id', $encounter_id);
					}
					if(isset($this->data['EncounterPlanReferral']['fax_save_add']) && $this->data['EncounterPlanReferral']['fax_save_add'] == 1){
						$this->Session->write('last_saved_id_referral_fax', $plan_referral_id);
					}
					
                    $ret = array();
                    echo json_encode($ret);
                    exit;
                } else {
					
					 $this->EncounterMaster->virtualFields = array(
				'location_name' => 'location_name',
				'firstname' => 'firstname'
			);
            $this->EncounterMaster->hasMany['EncounterAssessment']['order'] = array('EncounterAssessment.diagnosis' => 'ASC');
            $this->paginate['EncounterMaster'] = array(
                        'limit' => 20,
                        'conditions' => array('EncounterMaster.patient_id' => $patient_id), 
//array('AND' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterMaster.encounter_status' => array('Closed', 'Open'))),
			'order' => array('EncounterMaster.encounter_date' => 'DESC'),
                        'fields' => array('`EncounterMaster`.`encounter_id`', '`EncounterMaster`.`patient_id`', '`EncounterMaster`.`calendar_id`', '`EncounterMaster`.`encounter_date`','`Provider`.`firstname`', '`Provider`.`lastname`','PracticeLocation.location_name',/*'EncounterAssessment.diagnosis' ,*/'`EncounterMaster`.`encounter_status`', 'ScheduleCalendar.visit_type'),
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
					
				}
            } break;
            case "edit":
            {
                if(!empty($this->data))
                {
                    $this->data['EncounterPlanReferral']['date_ordered'] = __date("Y-m-d");
                    
					if(isset($this->data['EncounterPlanReferral']['encounter_id']) && $this->data['EncounterPlanReferral']['encounter_id']!= 0){
						$this->data['EncounterPlanReferral']['visit_summary'] = 1;
					}
                    $this->EncounterPlanReferral->save($this->data);
                    $this->EncounterPlanReferral->saveAudit('Update');
                    
                    $plan_referral_id = $this->data['EncounterPlanReferral']['plan_referrals_id'];  
					//$encounter_id = $this->data['EncounterPlanReferral']['encounter_id'];	
					if(isset($this->data['EncounterPlanReferral']['print_edit_add']))
					{
						$this->Session->write('last_edited_id_referral', $plan_referral_id);
						
					}

                    $ret = array();
                    echo json_encode($ret);
                    exit;
                }
                else
                {
					
                    $plan_referrals_id = (isset($this->params['named']['plan_referrals_id'])) ? $this->params['named']['plan_referrals_id'] : "";
                    //echo $family_history_id;
                    $items = $this->EncounterPlanReferral->find(
                            'first',
                            array(
                                'conditions' => array('EncounterPlanReferral.plan_referrals_id' => $plan_referrals_id)
                            )
                    );

                    $this->set('EditItem', $this->sanitizeHTML($items));
                    $this->EncounterMaster->virtualFields = array(
				'location_name' => 'location_name',
				'firstname' => 'firstname'
			);
            $this->EncounterMaster->hasMany['EncounterAssessment']['order'] = array('EncounterAssessment.diagnosis' => 'ASC');
            $this->paginate['EncounterMaster'] = array(
                        'limit' => 20,
                        'conditions' => array('EncounterMaster.patient_id' => $patient_id), 
//array('AND' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterMaster.encounter_status' => array('Closed', 'Open'))),
			'order' => array('EncounterMaster.encounter_date' => 'DESC'),
                        'fields' => array('`EncounterMaster`.`encounter_id`', '`EncounterMaster`.`patient_id`', '`EncounterMaster`.`calendar_id`', '`EncounterMaster`.`encounter_date`','`Provider`.`firstname`', '`Provider`.`lastname`','PracticeLocation.location_name',/*'EncounterAssessment.diagnosis' ,*/'`EncounterMaster`.`encounter_status`', 'ScheduleCalendar.visit_type'),
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
                    
                    
                    
                }
            } break;
            case "delete":
            {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['EncounterPlanReferral']['plan_referrals_id'];

                    foreach($ids as $id)
                    {
                        $this->EncounterPlanReferral->delete($id, false);
                       $ret['delete_count']++;
                    }

                    if($ret['delete_count'] > 0)
                    {
                        $this->EncounterPlanReferral->saveAudit('Delete');
                    }
                }

                echo json_encode($ret);
                exit;
            }break;
            case "referral_search":
            {
                if (!empty($this->data))
                {
                    $search_keyword = $this->data['autocomplete']['keyword'];

                    $referral_items = $this->DirectoryReferralList->find('all',
                                array(
                                    'conditions' => array('DirectoryReferralList.physician LIKE ' => '%'.$search_keyword.'%'))

                    );
                    $data_array = array();

                    foreach($referral_items as $referral_item)
                    {
                        $data_array[] = $referral_item['DirectoryReferralList']['physician'].'|'.$referral_item['DirectoryReferralList']['specialties'].'|'.$referral_item['DirectoryReferralList']['practice_name'].'|'.$referral_item['DirectoryReferralList']['address_1'].'|'.$referral_item['DirectoryReferralList']['address_2'].'|'.$referral_item['DirectoryReferralList']['city'].'|'.$referral_item['DirectoryReferralList']['state'].'|'.$referral_item['DirectoryReferralList']['zip_code'].'|'.$referral_item['DirectoryReferralList']['country'].'|'.$referral_item['DirectoryReferralList']['phone_number'];
                    }

                    echo implode("\n", $data_array);
                }
                exit();
            } break;

            default:
            {
				
                $this->paginate['EncounterPlanReferral'] = array(
                    'conditions' => array('EncounterPlanReferral.patient_id' => $patient_id),
			        'order' => array('EncounterPlanReferral.modified_timestamp' => 'desc')
                    );
				
                    $this->set('EncounterPlanReferral', $this->sanitizeHTML($this->paginate('EncounterPlanReferral')));
                //$this->set('EncounterPlanReferral', $this->sanitizeHTML($this->paginate('EncounterPlanReferral', array('EncounterMaster.patient_id' => $patient_id))));
                $this->EncounterPlanReferral->saveAudit('View');
            } break;
        }
    }
    
    public function pictures()
    {
		$this->layout = "blank";
		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$this->set(compact('patient_id'));
		
		switch($task) {
			case 'save_image':
				$this->loadModel('EncounterPhysicalExamImage');
				$source_file = $this->paths['temp'] . $this->data['image_file_name'];
				
				$this->paths['patient_encounter_img'] = $this->paths['patients'] . $patient_id . DS . 'images' . DS . '0' . DS;							
				UploadSettings::createIfNotExists($this->paths['patient_encounter_img']);

				
				
				$destination_file = $this->paths['patient_encounter_img'] . $this->data['image_file_name'];
				@copy($source_file, $destination_file);
				@unlink($source_file); // remove temp file
				
				$this->EncounterPhysicalExamImage->save(array(
					'EncounterPhysicalExamImage' => array(
						'image' => $this->data['image_file_name'],
						'patient_id' => $patient_id,
					),
				), false);
				
				exit;				
			break;
			
			// added task to delete image
			case 'delete_image':
				$this->loadModel('EncounterPhysicalExamImage');
					$this->EncounterPhysicalExamImage->id = $this->data['image_file_id'];

					$peImage = $this->EncounterPhysicalExamImage->read();

					if (!$peImage) {
						die('Image not found');
					}
					
					$encounter_id = intval($peImage['EncounterPhysicalExamImage']['encounter_id']);
					$patient_id = ($encounter_id) ? $peImage['EncounterMaster']['patient_id'] : $peImage['EncounterPhysicalExamImage']['patient_id'];

					$this->paths['patient_encounter_img'] = $this->paths['patients'] . $patient_id . DS . 'images' . DS . $encounter_id . DS;							
					$filename = UploadSettings::existing(
						$this->paths['encounters'] . $peImage['EncounterPhysicalExamImage']['image'],
						$this->paths['patient_encounter_img'] . $peImage['EncounterPhysicalExamImage']['image']
						);
					@unlink($filename);
				
				
				$this->EncounterPhysicalExamImage->delete($this->data['image_file_id']);
				exit;
			break;
			
			default:
				break;
		}
			
		
		
    }

    public function picture_search() {

        $this->layout = "blank";
        $this->loadModel('EncounterPhysicalExamImage');

        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

				$conditions['OR'] = array(
					'EncounterPhysicalExamImage.patient_id' => $patient_id,
					'EncounterMaster.patient_id' => $patient_id,
				);
				
        $term = (isset($this->params['named']['term'])) ? trim($this->params['named']['term']) : "";

        if ($term) {
            $conditions['EncounterPhysicalExamImage.comment LIKE '] = '%' . $term .'%';
        }

        $this->paginate['EncounterPhysicalExamImage'] = array(
            'conditions' => $conditions,
            'limit' => 10,
						'order' => array(
							'EncounterPhysicalExamImage.physical_exam_image_id ' => 'DESC'
						),
        );

        $pe_images = $this->paginate('EncounterPhysicalExamImage');
        $this->set(compact('pe_images'));        
    }

	public function orders() {
		$patient_mode = (isset($this->params['named']['patient_mode'])) ? $this->params['named']['patient_mode'] : "";
		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		
		if($patient_mode == 1) {
			$this->layout = "empty";
			$this->set('patient_mode', 1);
			$this->set('patient_id', $patient_id);
		}

		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		if ($task == 'rebuild') {
			$this->loadModel('Order');
			$this->Order->rebuildTable();
			$this->Session->setFlash('Order table successfully rebuilt');
			$this->redirect(array(
				'controller' => 'patients',
				'action' => 'orders',
			));
			exit();					
		}		
		
		if ($task == 'fix_duplicates') {
			$this->loadModel('Order');
			$this->Order->fixDuplicateOrders();
			$this->Session->setFlash('Duplicates removed');
			$this->redirect(array(
				'controller' => 'patients',
				'action' => 'orders',
			));
			exit();					
		}				
		

		$this->loadModel('PracticeSetting');
		$settings = $this->Session->read("PracticeSetting");
		$db_config = $this->PracticeSetting->getDataSource()->config;
		$cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';		
		
		$dosespot_accessed = Cache::read($cache_file_prefix . 'dosespot_accessed');

		if (!$dosespot_accessed) {
			$dosespot_accessed = array();
		}
		
		//Import medications from Dosespot when for each patients 
		// who accessed the dosespot screen
		$practice_settings = $this->Session->read("PracticeSetting");
		$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];
		
		if($rx_setup=='Electronic_Dosespot') {
			$this->loadModel('PatientDemographic');
			$this->loadModel('PatientMedicationList');
			foreach ($dosespot_accessed as $patient_id) {
				$dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);

				//If the patient not exists in Dosespot, add the patient to Dosespot
				if($dosespot_patient_id == 0 or $dosespot_patient_id == '') {					
					$this->PatientDemographic->updateDosespotPatient($patient_id);					
					$dosespot_patient_id = $this->PatientDemographic->getPatientDoesespotId($patient_id);
				}					
			   //must have $dosespot_patient_id to proceed
			   if($dosespot_patient_id && is_numeric($dosespot_patient_id) )
			   {
				$dosespot_xml_api = new Dosespot_XML_API();
				$medication_items = $dosespot_xml_api->getMedicationList($dosespot_patient_id);

				foreach ($medication_items as $medication_item) {
					$dosespot_medication_id = $medication_item['MedicationId'];
					$items = $this->PatientMedicationList->find('first', array('conditions' => array('PatientMedicationList.dosespot_medication_id' => $dosespot_medication_id)));

					if(empty($items)) {
						$start_date = __date('Y-m-d', strtotime($medication_item['date_written'].'+'.$medication_item['days_supply'].'days'));
						$inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
						$this->data = array();
						$this->data['PatientMedicationList']['patient_id'] = $patient_id;
						$this->data['PatientMedicationList']['medication_type'] = "Electronic";
						$this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
						$this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
						$this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
						$this->data['PatientMedicationList']['source'] = "e-Prescribing History";
						$this->data['PatientMedicationList']['status'] = $medication_item['status'];
						$this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
						$this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
						$this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
						$this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
						//only set inactive_date IF days_supply was provided 
						if (!empty ($medication_item['days_supply'])) {
						$this->data['PatientMedicationList']['end_date'] = $inactive_date;
						}
						$this->data['PatientMedicationList']['modified_user_id'] =  $this->user_id;
						$this->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
						$this->PatientMedicationList->create();
						$this->PatientMedicationList->save($this->data);
						$this->PatientMedicationList->saveAudit('New');
					}	else 	{
						$start_date = __date('Y-m-d', strtotime($medication_item['date_written'].'+'.$medication_item['days_supply'].'days'));
						$inactive_date = __date('Y-m-d', strtotime(str_replace('-', '/', $start_date)));
						$this->data['PatientMedicationList']['medication_list_id'] = $items['PatientMedicationList']['medication_list_id'];
						$this->data['PatientMedicationList']['patient_id'] = $patient_id;
						$this->data['PatientMedicationList']['medication_type'] = "Electronic";
						$this->data['PatientMedicationList']['provider_id'] = $this->UserAccount->getProviderId($medication_item['prescriber_user_id']);
						$this->data['PatientMedicationList']['dosespot_medication_id'] = $dosespot_medication_id;
						$this->data['PatientMedicationList']['medication'] = $medication_item['medication'];
						$this->data['PatientMedicationList']['source'] = "e-Prescribing History";
						$this->data['PatientMedicationList']['status'] = $items['PatientMedicationList']['status'];
						$this->data['PatientMedicationList']['direction'] = $medication_item['direction'];
						$this->data['PatientMedicationList']['quantity_value'] = $medication_item['quantity_value'];
						$this->data['PatientMedicationList']['refill_allowed'] = $medication_item['refill_allowed'];
						$this->data['PatientMedicationList']['start_date'] = $medication_item['date_written'];
						
						//only set inactive_date IF days_supply was provided 
						if (!empty ($medication_item['days_supply'])) {
							$this->data['PatientMedicationList']['end_date'] = $inactive_date;
						}
						
						$this->data['PatientMedicationList']['modified_user_id'] =  $this->user_id;
						$this->data['PatientMedicationList']['modified_timestamp'] =  __date("Y-m-d H:i:s");
						
						//only set inactive_date IF days_supply was provided 
						if (strtotime(date('Y-m-d')) >= strtotime($inactive_date) && !empty ($medication_item['days_supply'])) {
							$this->data['PatientMedicationList']['status'] = 'Completed';
						}

						$this->PatientMedicationList->save($this->data);
						$this->PatientMedicationList->saveAudit('Update');
					}
					//Remove the dosespot data from the database.If removed in dosespot.	
					$this->PatientMedicationList->removeDosespotDeletedData($patient_id, true, $medication_items);						
				}	
			     } // close loop $dosespot_patient_id
			}
			
			Cache::write($cache_file_prefix . 'dosespot_accessed', array());
		}				
	}
    
    public function orders_grid()
    {
        $this->layout = "empty";
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
        
				$patient_name = (isset($this->data['patient_name'])) ? $this->data['patient_name'] : "";
				$test_name = (isset($this->data['test_name'])) ? $this->data['test_name'] : "";
				$order_type = (isset($this->data['order_type'])) ? $this->data['order_type'] : "";
				$status = (isset($this->data['status'])) ? $this->data['status'] : "";
				$provider_name = (isset($this->data['provider_name'])) ? $this->data['provider_name'] : "";
				$date_performed = (isset($this->data['date_performed'])) ? $this->data['date_performed'] : "";
				$date_ordered = (isset($this->data['order_date'])) ? $this->data['order_date'] : "";
				
				$isiPadApp = isset($_COOKIE["iPad"]);
				
        $conditions = array();
        $test_array = array();

        if(strlen($patient_id) > 0)
        {
            $conditions['patient_id ='] = $patient_id;
        }
        
        if(strlen($patient_name) > 0)
        {
					
						$search_keyword = str_replace(',', ' ', trim($patient_name));
						$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);

						$keywords = explode(' ', $search_keyword);
						$patient_search_conditions = array();
						foreach($keywords as $word) {
							$patient_search_conditions[] = array('OR' => 
									array(
										'CONVERT(DES_DECRYPT(patient_firstname) USING latin1) LIKE ' => $word . '%', 
										'CONVERT(DES_DECRYPT(patient_lastname) USING latin1) LIKE ' => $word . '%'
									)
							);
						}						
					
						$conditions['AND'] = $patient_search_conditions;
        }
        
				if ($order_type) {
					$conditions['Order.order_type LIKE'] = $order_type.'%';
				}
				
				if ($test_name) {
					$conditions['Order.test_name LIKE'] = $test_name.'%';
				}				
				
				if ($status) {
					$conditions['Order.status LIKE'] = $status.'%';
				}				
				
				if ($provider_name) {
					$conditions['Order.provider_name LIKE'] = $provider_name.'%';
				}				
				
				if ($date_performed) {
					
					if (!$isiPadApp) {
						$tmp = explode('/', $date_performed);

						$date_performed = __date('Y-m-d', strtotime($tmp[2].'-' . $tmp[0] .'-'. $tmp[1]));
					} 
					
					
					$conditions['Order.date_performed'] = $date_performed;
				}							
				
				if ($date_ordered) {
					
					if (!$isiPadApp) {
						$tmp = explode('/', $date_ordered);
						$date_ordered = __date('Y-m-d', strtotime($tmp[2].'-' . $tmp[0] .'-'. $tmp[1]));
					}
					
					$conditions['Order.date_ordered'] = $date_ordered;
				}							
				
        $this->loadModel("Order");		
        $this->paginate['Order'] = array(
            'limit' => 20, 
            'page' => 1, 
            'order' => array('Order.date_performed' => 'desc', 'Order.modified_timestamp' => 'desc'),
            'conditions' => $conditions
        );
        
        $data = $this->paginate("Order");
        $this->set('orders', $this->sanitizeHTML($data));
		
		$this->UserGroup =& ClassRegistry::init('UserGroup');
		$this->UserAccount =& ClassRegistry::init('UserAccount');
		$conditions = array('UserAccount.role_id  ' => $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK,$include_admin=false));
		$users = $this->UserAccount->find('all', array('conditions' => $conditions));
        //all providers
        $this->set('users', $this->sanitizeHTML($users));
    }


        public function notes()
        {
            $this->layout = "blank";
            $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
            $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
            $this->loadModel("PatientNote");
            if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
            {
                 $this->data['PatientNote']['patient_id'] = $patient_id;
                 $this->data['PatientNote']['date'] = __date("Y-m-d", strtotime(str_replace("-", "/", $this->data['PatientNote']['date'])));
            }
            switch($task)
            {
                case "addnew":
                {
                    if(!empty($this->data))
                    {
                        $this->PatientNote->create();
                        $this->PatientNote->save($this->data);
                        $this->PatientNote->saveAudit('New');

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                }
                break;
                case "edit":
                {
                    if(!empty($this->data))
                    {
                        $this->PatientNote->save($this->data);
                        $this->PatientNote->saveAudit('Update');

                        $ret = array();
                        echo json_encode($ret);
                        exit;
                    }
                    else
                    {
                        $note_id = (isset($this->params['named']['note_id'])) ? $this->params['named']['note_id'] : "";
                        $items = $this->PatientNote->find(
                                'first',
                                array(
                                    'conditions' => array('PatientNote.note_id' => $note_id)
                                )
                        );
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                }
                break;
                case "delete":
                {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data))
                    {
                        $ids = $this->data['PatientNote']['note_id'];

                        foreach($ids as $id)
                        {
                            $this->PatientNote->delete($id, false);
                           $ret['delete_count']++;
                        }

                        if($ret['delete_count'] > 0)
                        {
                            $this->PatientNote->saveAudit('Delete');
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
                break;
                default:
                {
				    $this->paginate['PatientNote'] = array(
                    'conditions' => array('PatientNote.patient_id' => $patient_id),
			        'order' => array('PatientNote.date' => 'desc')
                    );
				
                    $this->set('patient_notes', $this->sanitizeHTML($this->paginate('PatientNote')));
                    //$this->set('patient_notes', $this->sanitizeHTML($this->paginate('PatientNote'), array('PatientNote.patient_id' => $patient_id)));
                    $this->PatientNote->saveAudit('View');
                }
            }
        }

        public function documents()
        {
            $this->layout = "blank";
            $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
            $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
            $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
			$this->cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
            
			$this->loadModel("UserAccount");
            $availableProviders = $this->UserAccount->getProviders();
            $this->set('availableProviders', $availableProviders);
            
            $this->loadModel("PatientDocument");
            
            //save the filter variables to the cache for later use .
            if($task=="save_filter"){
					$adv_search['doc_name'] = (isset($_POST['doc_name'])) ? $_POST['doc_name'] : "";
					$adv_search['doc_type'] = (isset($_POST['doc_type'])) ? $_POST['doc_type'] : "";
					$adv_search['doc_status'] = (isset($_POST['doc_status'])) ? $_POST['doc_status'] : "";
					$adv_search['doc_fromdate'] = (isset($_POST['doc_fromdate'])) ? $_POST['doc_fromdate'] : "";
					$adv_search['doc_todate'] = (isset($_POST['doc_todate'])) ? $_POST['doc_todate'] : "";
					
					Cache::set(array('duration' => '+10 years'));
					Cache::write($this->cache_file_prefix.'document_search_'.$this->user_id, $adv_search);
					
					echo "true";
					exit;
			}
            // delete the cache filter here 
            if($task=="delete_filter"){

				Cache::delete($this->cache_file_prefix.'document_search_'.$this->user_id);
				echo 'true';
				exit;
			}
          
			Cache::set(array('duration' => '+10 years'));					
			$saved_search = Cache::read($this->cache_file_prefix.'document_search_'.$this->user_id);
			$saved_search_array = array();
			if( !empty( $saved_search ) ){
				
				foreach($saved_search as $key=>$save_search){
					if($save_search!=""){
						if($key=="doc_type"){
							$saved_search_array[$key] = $save_search;
						} else {
						$saved_search_array[$key] = base64_decode($save_search);
						}
					} else {
						$saved_search_array[$key] = "";
					}
				}
				$this->set(compact('saved_search_array' , $saved_search_array));
			}
            
            $this->PatientDocument->execute($this, $task, $patient_id);
        }
		
	public function patient_documents()
        {
            $this->layout = "blank";
            $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
            $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
            $this->loadModel("PatientDocument");
			$availableProviders = $this->UserAccount->getProviders();
            $this->set('availableProviders', $availableProviders);

            $this->PatientDocument->execute($this, $task, $patient_id);
			/*$this->paginate['PatientDocument'] = array(
                    'conditions' => array('PatientDocument.document_type' => 'Lab','PatientDocument.patient_id' =>$patient_id),
			        'order' => array('PatientDocument.service_date' => 'desc')
                    );*/
					
			$this->paginate['PatientDocument'] = array(
                    'conditions' => array('PatientDocument.patient_id' =>$patient_id, 'PatientDocument.document_type' => 'Lab'),
			        'order' => array('PatientDocument.service_date' => 'desc')
                    );		
				
            //$this->set('PatientDocument', $this->sanitizeHTML($this->paginate('PatientDocument')));
			$this->set('PatientDocument', $this->sanitizeHTML($this->paginate('PatientDocument')));
        }

        public function messages()
        {
            $this->layout = "blank";
            $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
            $this->loadModel("MessagingMessage");
            
            $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
            
            if($task)
            {
			   switch($task)
               {
			    case "delete":
                {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data))
                    {
                        $ids = $this->data['MessagingMessage']['message_id'];

                        foreach($ids as $id)
                        {
                            $this->MessagingMessage->delete($id, false);
                           $ret['delete_count']++;
                        }

                        if($ret['delete_count'] > 0)
                        {
                            $this->MessagingMessage->saveAudit('Delete');
                        }
                    }

                    echo json_encode($ret);
					exit;
				    $this->redirect(array('action' => 'MessagingMessage'));
                   
                }
                break;
                default:
                {
                $this->set('patient_user_id' , $this->UserAccount->getUserbyPatientID($patient_id));
                $message_id = (isset($this->params['named']['message_id'])) ? $this->params['named']['message_id'] : "";

                $items = $this->MessagingMessage->find(
                        'first', 
                        array(
                            'conditions' => array('MessagingMessage.message_id' => $message_id)
                        )
                );
                
                $this->set('EditItem', $this->sanitizeHTML($items));
                }
			   }
			}
            else
            {
			    $this->set('patient_user_id' , $this->UserAccount->getUserbyPatientID($patient_id));
				
			    $this->paginate['MessagingMessage'] = array(
                    'conditions' => array(
						'MessagingMessage.patient_id' => $patient_id,
                        'MessagingMessage.status <>' => 'Draft',
                    ),
			        'order' => array('MessagingMessage.modified_timestamp' => 'desc')
                    );
				
                $this->set('MessagingMessages', $this->sanitizeHTML($this->paginate('MessagingMessage')));
                //$this->set('MessagingMessages', $this->sanitizeHTML($this->paginate('MessagingMessage', array('MessagingMessage.patient_id' => $patient_id))));
            }

            $this->MessagingMessage->saveAudit('View');
        }

        public function phone_calls()
        {
            $this->layout = "blank";
            $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
            $this->loadModel("MessagingPhoneCall");
            
            $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
            
            if($task)
            {
			   switch($task)
               {
			    case "delete":
                {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data))
                    {
                        $ids = $this->data['MessagingPhoneCall']['phone_call_id'];

                        foreach($ids as $id)
                        {
                            $this->MessagingPhoneCall->delete($id, false);
                           $ret['delete_count']++;
                        }

                        if($ret['delete_count'] > 0)
                        {
                            $this->MessagingPhoneCall->saveAudit('Delete');
                        }
                    }

                    echo json_encode($ret);
					exit;
				    $this->redirect(array('action' => 'phone_calls'));
                   
                }
                break;
                default:
                {
                $phone_call_id = (isset($this->params['named']['phone_call_id'])) ? $this->params['named']['phone_call_id'] : "";

                $items = $this->MessagingPhoneCall->find(
                        'first', 
                        array(
                            'conditions' => array('MessagingPhoneCall.phone_call_id' => $phone_call_id)
                        )
                );
                $documented_by_Obj =$this->UserAccount->getUserByID($items['MessagingPhoneCall']['documented_by_user_id']);
                
                if(!is_object($documented_by_Obj))
                {
					$this->redirect(array('action' => 'phone_calls','patient_id' => $patient_id));
				}
				
                $items['MessagingPhoneCall']['documented_by']=$documented_by_Obj->full_name;
                $this->set('EditItem', $this->sanitizeHTML($items));
				}
            }
			}
            else
            {
			    $this->paginate['MessagingPhoneCall'] = array(
                    'conditions' => array('MessagingPhoneCall.patient_id' => $patient_id),
			        'order' => array('MessagingPhoneCall.modified_timestamp' => 'desc')
                    );
				
                $this->set('MessagingPhoneCalls', $this->sanitizeHTML($this->paginate('MessagingPhoneCall')));
				
                //$this->set('MessagingPhoneCalls', $this->sanitizeHTML($this->paginate('MessagingPhoneCall', array('MessagingPhoneCall.patient_id' => $patient_id))));
            }
 
            $this->MessagingPhoneCall->saveAudit('View');
			
		   
        }



        public function letters()
        {
            $this->layout = "blank";
            $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
            $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
            $this->loadModel("PatientLetter");
            $this->loadModel("PatientDemographic");
			$this->loadModel("LetterTemplate");
			$this->loadModel("PatientPreference");
			$this->loadModel("PracticeProfile");
			$template_type = (isset($this->data['template_type'])) ? $this->data['template_type'] : "";
			
			if($task == "addnew" || $task == "edit")
            {
				$types = $this->LetterTemplate->getTemplates();
				$this->set("types", $this->sanitizeHTML($types));
			
				$items = $this->PatientPreference->find(
						'first',
						array(
							'conditions' => array('PatientPreference.patient_id' => $patient_id)
						)
				);
				$this->set('EditItem', $this->sanitizeHTML($items));
				$user = $this->UserAccount->getUserRealName($items['PatientPreference']['pcp']);
				$this->set('user', $this->sanitizeHTML($user));
	        }
			
            if(!empty($this->data) && ($task == "addnew" || $task == "edit"))
            {
                 $this->data['PatientLetter']['patient_id'] = $patient_id;
                 $this->data['PatientLetter']['date_performed'] = __date("Y-m-d", strtotime($this->data['PatientLetter']['date_performed']));
            }
			
            $patient_items = $this->PatientDemographic->find(
                'first',
                array(
                    'conditions' => array('PatientDemographic.patient_id' => $patient_id),
		    'recursive' => -1
                )
            );
			
            $this->set('PatientDemo', $this->sanitizeHTML($patient_items));
			
            switch($task)
            {
                case "addnew":
                {
                    if(!empty($this->data))
                    {
                        $this->PatientLetter->create();
                        $this->PatientLetter->save($this->data);
                        $this->PatientLetter->saveAudit('New');

                        $ret = array();
						
						/*if($this->data['preview_mode'] == "true")
						{
							$ret['redir_url'] = Router::url(array('task' => 'edit', 'patient_id' => $patient_id, 'letter_id' => $this->PatientLetter->getLastInsertId()));	
						}*/
						
                        echo json_encode($ret);
                        exit;
                    }
                }
                break;
                case "edit":
                {
                    if(!empty($this->data))
                    {
                        $this->PatientLetter->save($this->data);
                        $this->PatientLetter->saveAudit('Update');

                        $ret = array();
						
						/*if($this->data['preview_mode'] == "true")
						{
							$ret['redir_url'] = Router::url(array('task' => 'edit', 'patient_id' => $patient_id, 'letter_id' => $this->PatientLetter->id));	
						}*/
						
                        echo json_encode($ret);
                        exit;
                    }
                    else
                    {
                        $letter_id = (isset($this->params['named']['letter_id'])) ? $this->params['named']['letter_id'] : "";
                        $items = $this->PatientLetter->find(
                                'first',
                                array(
                                    'conditions' => array('PatientLetter.letter_id' => $letter_id)
                                )
                        );
                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                }
                break;
                case "delete":
                {
                    $ret = array();
                    $ret['delete_count'] = 0;

                    if (!empty($this->data))
                    {
                        $ids = $this->data['PatientLetter']['letter_id'];

                        foreach($ids as $id)
                        {
                            $this->PatientLetter->delete($id, false);
                           $ret['delete_count']++;
                        }

                        if($ret['delete_count'] > 0)
                        {
                            $this->PatientLetter->saveAudit('Delete');
                        }
                    }

                    echo json_encode($ret);
                    exit;
                }
                break;
				case "get_content":
                {
				     $template_content = $this->LetterTemplate->find('first', array('conditions' =>array('LetterTemplate.template_id' => $this->data['template_id'])));
					 $ret = array();
					 $ret['content'] = $template_content['LetterTemplate']['content'];
					 echo json_encode($ret);
			         exit;
				}break;
				case "letter_content":
                {
				    $template_id = $this->data['PatientLetter']['template_id'];
					$template_data = $this->LetterTemplate->getTemplate($template_id);
					$template_data['content'] = $this->data['PatientLetter']['content'];
					
					$location_id = $template_data['location_id'];
					$location = $this->PracticeLocation->getLocationItem($location_id);
					$this->set('location', $location);
					$this->set('template_data', $template_data);
					$practice_profile = $this->PracticeProfile->find('first');
				    $practice_profile_logo = $practice_profile['PracticeProfile']['logo_image'];
				    $this->set('practice_profile', $practice_profile_logo);
				
					$this->layout = 'empty';
    	            $data =  $this->render('../administration/template/letter_template');
					$file_path = $this->paths['temp'];
					$file_path = str_replace('//', '/', $file_path);
					$file_name = 'lettertemplate' . $template_id . '.pdf';
					site::write(pdfReport::generate($data), $file_path . $file_name);
					$file_path_test = $this->url_rel_paths['temp'];
					$ret = array();
                    $ret['target_file'] = $file_name;
                    echo json_encode($ret);
					exit();
				}break;
                default:
                {
	                $types = $this->LetterTemplate->getTemplates();
				    $this->set("types", $this->sanitizeHTML($types));
	
                    $this->set('patient_letters', $this->sanitizeHTML($this->paginate('PatientLetter',array('PatientLetter.patient_id' => $patient_id))));
                    $this->PatientLetter->saveAudit('View');
                }
            }
        }

        public function past_visits()
        {
            $this->layout = "blank";
            $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
            $this->loadModel("EncounterMaster");
			$this->EncounterMaster->virtualFields = array(
				'location_name' => 'location_name',
				'firstname' => 'firstname'
			);
            $this->EncounterMaster->hasMany['EncounterAssessment']['order'] = array('EncounterAssessment.diagnosis' => 'ASC');
            $this->paginate['EncounterMaster'] = array(
                        'limit' => 20,
                        'conditions' => array('EncounterMaster.patient_id' => $patient_id), 
//array('AND' => array('EncounterMaster.patient_id' => $patient_id, 'EncounterMaster.encounter_status' => array('Closed', 'Open'))),
			'order' => array('EncounterMaster.encounter_date' => 'DESC'),
                        'fields' => array('`EncounterMaster`.`encounter_id`', '`EncounterMaster`.`patient_id`', '`EncounterMaster`.`calendar_id`', '`EncounterMaster`.`encounter_date`','`Provider`.`firstname`', '`Provider`.`lastname`','PracticeLocation.location_name',/*'EncounterAssessment.diagnosis' ,*/'`EncounterMaster`.`encounter_status`', 'ScheduleCalendar.visit_type'),
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
    public function activities()
        {
            $user = $this->Session->read('UserAccount');
            $user_id = $user['user_id'];
            $this->loadModel("PatientOrders");
            $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
                $this->paginate['PatientOrders'] = array(
                            'limit' => 20,
                            'conditions' =>  array('AND' => array('PatientOrders.ordered_by_id' => $user_id))
                        );
                $this->set('patient_orders', $this->sanitizeHTML($this->paginate('PatientOrders')));
    }    
    function activities_documents(){
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("PatientDocument");
        $patient_documents = $this->PatientDocument->getItemsByPatient($user_id);
        $this->set('patient_documents', $patient_documents);
        $this->loadModel("EncounterPlanProcedure");
        $patient_outside_order_items = $this->EncounterPlanProcedure->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }
    function outside_order(){
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanLab");
        $patient_outside_order_items = $this->EncounterPlanLab->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }
    function outside_order_radiology(){
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanRadiology");
        $patient_outside_order_items = $this->EncounterPlanRadiology->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }
    function outside_order_procedure(){
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanProcedure");
        $patient_outside_order_items = $this->EncounterPlanProcedure->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }
    function outside_order_rx(){
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanRx");
        $patient_outside_order_items = $this->EncounterPlanRx->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }
    function outside_order_referral(){
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanReferral");
        $patient_outside_order_items = $this->EncounterPlanReferral->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }
    function outside_order_advice_instruction(){
        $this->layout = "blank";
        $user = $this->Session->read('UserAccount');
        $user_id = $user['user_id'];
        $this->loadModel("EncounterPlanAdviceInstructions");
        $patient_outside_order_items = $this->EncounterPlanAdviceInstructions->getItemsByPatient($user_id);
        $this->set('patient_outside_order_items', $patient_outside_order_items);
    }

    public function audit_log()
    {
        $this->layout = "blank";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";

        if(!empty($this->data))
        {
        $date_from = str_replace("/", "-", $this->data['date_from']);
        $date_to = str_replace("/", "-", $this->data['date_to']);
            $this->redirect(array('controller' => 'patients', 'action' => 'audit_log', 'patient_id' => $patient_id, 'truncate_output' => 1, 'period' => $this->data['period'], 'date_from' => $date_from, 'date_to' => $date_to, 'section' => $this->data['section']));
        }
        $this->loadModel('Audit');
        $this->loadModel('AuditSection');

        $period = (isset($this->params['named']['period'])) ? $this->params['named']['period'] : 'today';
        $date_from = (isset($this->params['named']['date_from'])) ? $this->params['named']['date_from'] : "";
        $date_to = (isset($this->params['named']['date_to'])) ? $this->params['named']['date_to'] : "";
        $section = (isset($this->params['named']['section'])) ? $this->params['named']['section'] : "";
		
        $conditions = $this->Audit->getAuditConditions($patient_id, $period, $date_from, $date_to, $section);
		
		$joins = array(
            array('table' => 'audit_sections', 'alias' => 'AuditSection', 'type' => 'INNER', 'conditions' => array('AuditSection.audit_section_id = Audit.audit_section_id')),
            array('table' => 'user_accounts', 'alias' => 'UserAccount', 'type' => 'INNER', 'conditions' => array('UserAccount.user_id = Audit.user_id'))
        );
		
		$this->Audit->recursive = -1;
		
		$this->Audit->virtualFields['full_name'] = "CONCAT(UserAccount.firstname, ' ', UserAccount.lastname)";
		
		$this->paginate['Audit'] = array(
			'fields' => array('Audit.audit_type', 'Audit.modified_timestamp', 'AuditSection.section_name', 'Audit.full_name', 'Audit.emergency'),
            'conditions' => $conditions,
			'joins' => $joins,
			'limit'    => 20,
            'page'    => 1,
            'order'    => array('Audit.modified_timestamp' => 'desc')
        );
		
        $audit_logs = $this->paginate('Audit');
		
        $this->set('audit_logs', $audit_logs);
        $this->set("audit_sections", $this->AuditSection->getAllSections());
    }

    /**
     * Override Controller::paginate() from cake/libs/controller/controller.php
     * 
     */
    public function paginate($object = null, $scope = array(), $whitelist = array()) {
        
        // For other models proceed normally
        return parent::paginate($object, $scope, $whitelist);
    }
    
    /**
     * Special pagination routine for Order model
     * 
     * This is almost identical to the original pagination method
     * 
     */
    private function __paginateOrder($object = null, $scope = array(), $whitelist = array()) {
        if (is_array($object)) {
                $whitelist = $scope;
                $scope = $object;
                $object = null;
        }
        $assoc = null;

        if (is_string($object)) {
                $assoc = null;
                if (strpos($object, '.')  !== false) {
                        list($object, $assoc) = pluginSplit($object);
                }

                if ($assoc && isset($this->{$object}->{$assoc})) {
                        $object =& $this->{$object}->{$assoc};
                } elseif (
                        $assoc && isset($this->{$this->modelClass}) &&
                        isset($this->{$this->modelClass}->{$assoc}
                )) {
                        $object =& $this->{$this->modelClass}->{$assoc};
                } elseif (isset($this->{$object})) {
                        $object =& $this->{$object};
                } elseif (
                        isset($this->{$this->modelClass}) && isset($this->{$this->modelClass}->{$object}
                )) {
                        $object =& $this->{$this->modelClass}->{$object};
                }
        } elseif (empty($object) || $object === null) {
                if (isset($this->{$this->modelClass})) {
                        $object =& $this->{$this->modelClass};
                } else {
                        $className = null;
                        $name = $this->uses[0];
                        if (strpos($this->uses[0], '.') !== false) {
                                list($name, $className) = explode('.', $this->uses[0]);
                        }
                        if ($className) {
                                $object =& $this->{$className};
                        } else {
                                $object =& $this->{$name};
                        }
                }
        }

        if (!is_object($object)) {
                trigger_error(sprintf(
                        __('Controller::paginate() - can\'t find model %1$s in controller %2$sController',
                                true
                        ), $object, $this->name
                ), E_USER_WARNING);
                return array();
        }
        $options = array_merge($this->params, $this->params['url'], $this->passedArgs);

        if (isset($this->paginate[$object->alias])) {
                $defaults = $this->paginate[$object->alias];
        } else {
                $defaults = $this->paginate;
        }

        if (isset($options['show'])) {
                $options['limit'] = $options['show'];
        }

        if (isset($options['sort'])) {
                $direction = null;
                if (isset($options['direction'])) {
                        $direction = strtolower($options['direction']);
                }
                if ($direction != 'asc' && $direction != 'desc') {
                        $direction = 'asc';
                }
                $options['order'] = array($options['sort'] => $direction);
        }

        
        // Since the order model does not map to an actual order,
        // skip checking/verification of fields to be sorted
        
        /*
        if (!empty($options['order']) && is_array($options['order'])) {
                $alias = $object->alias ;
                $key = $field = key($options['order']);

                if (strpos($key, '.') !== false) {
                        list($alias, $field) = explode('.', $key);
                }
                $value = $options['order'][$key];
                unset($options['order'][$key]);

                if ($object->hasField($field)) {
                        $options['order'][$alias . '.' . $field] = $value;
                } elseif ($object->hasField($field, true)) {
                        $options['order'][$field] = $value;
                } elseif (isset($object->{$alias}) && $object->{$alias}->hasField($field)) {
                        $options['order'][$alias . '.' . $field] = $value;
                }
        }
        */
        
        
        $vars = array('fields', 'order', 'limit', 'page', 'recursive');
        $keys = array_keys($options);
        $count = count($keys);

        for ($i = 0; $i < $count; $i++) {
                if (!in_array($keys[$i], $vars, true)) {
                        unset($options[$keys[$i]]);
                }
                if (empty($whitelist) && ($keys[$i] === 'fields' || $keys[$i] === 'recursive')) {
                        unset($options[$keys[$i]]);
                } elseif (!empty($whitelist) && !in_array($keys[$i], $whitelist)) {
                        unset($options[$keys[$i]]);
                }
        }
        $conditions = $fields = $order = $limit = $page = $recursive = null;

        if (!isset($defaults['conditions'])) {
                $defaults['conditions'] = array();
        }

        $type = 'all';

        if (isset($defaults[0])) {
                $type = $defaults[0];
                unset($defaults[0]);
        }

        $options = array_merge(array('page' => 1, 'limit' => 20), $defaults, $options);
        $options['limit'] = (int) $options['limit'];
        if (empty($options['limit']) || $options['limit'] < 1) {
                $options['limit'] = 1;
        }

        extract($options);

        if (is_array($scope) && !empty($scope)) {
                $conditions = array_merge($conditions, $scope);
        } elseif (is_string($scope)) {
                $conditions = array($conditions, $scope);
        }
        if ($recursive === null) {
                $recursive = $object->recursive;
        }

        $extra = array_diff_key($defaults, compact(
                'conditions', 'fields', 'order', 'limit', 'page', 'recursive'
        ));
        if ($type !== 'all') {
                $extra['type'] = $type;
        }

        if (method_exists($object, 'paginateCount')) {
                $count = $object->paginateCount($conditions, $recursive, $extra);
        } else {
                $parameters = compact('conditions');
                if ($recursive != $object->recursive) {
                        $parameters['recursive'] = $recursive;
                }
                $count = $object->find('count', array_merge($parameters, $extra));
        }
        $pageCount = intval(ceil($count / $limit));

        if ($page === 'last' || $page >= $pageCount) {
                $options['page'] = $page = $pageCount;
        } elseif (intval($page) < 1) {
                $options['page'] = $page = 1;
        }
        $page = $options['page'] = (integer)$page;

        if (method_exists($object, 'paginate')) {
                $results = $object->paginate(
                        $conditions, $fields, $order, $limit, $page, $recursive, $extra
                );
        } else {
                $parameters = compact('conditions', 'fields', 'order', 'limit', 'page');
                if ($recursive != $object->recursive) {
                        $parameters['recursive'] = $recursive;
                }
                $results = $object->find($type, array_merge($parameters, $extra));
        }
        $paging = array(
                'page'		=> $page,
                'current'	=> count($results),
                'count'		=> $count,
                'prevPage'	=> ($page > 1),
                'nextPage'	=> ($count > ($page * $limit)),
                'pageCount'	=> $pageCount,
                'defaults'	=> array_merge(array('limit' => 20, 'step' => 1), $defaults),
                'options'	=> $options
        );
        $this->params['paging'][$object->alias] = $paging;

        if (!in_array('Paginator', $this->helpers) && !array_key_exists('Paginator', $this->helpers)) {
                $this->helpers[] = 'Paginator';
        }
        return $results;        
    }
    
    
    public function summary() {
      $this->layout = "empty";
      $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
      
      $content = $this->requestAction('/encounters/summary/', array('return', 'patient_id' => $patient_id));
      
      echo $content;
      exit();
    }
    
}

?>
