<?php


App::import('Model', 'ConnectionManager', false);

$db = ConnectionManager::getDataSource('default');
$tables = $db->listSources();

if (empty($tables))
{
    exit(header("location: ../installation"));
}

class AdministrationController extends AppController
{

    public $name = 'Administration';
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Javascript', 'QuickAcl', 'RoleGenerator');
    public $components    = array('Cookie');
    public $uses = array('UserAccount', 'UserRole', 'UserGroup', 'UserLocation', 'Provider', 'PracticeSetting', 'PracticeLocation', 'PracticeProfile', 'ScheduleType', 'DirectoryLabFacility', 'DirectoryPharmacy', 'DirectoryReferralList', 'DirectoryInsuranceCompany', 'HealthMaintenancePlan', 'ClinicalAlert', 'PatientReminder', 'SetupDetail', 'StateCode', 'LetterTemplate');
    public $paginate = array(
        'UserRole' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array(
                'UserRole.role_desc' => 'asc')
        ),
        'UserGroup' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array(
                'UserGroup.group_desc' => 'asc')
        ),
        'UserAccount' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array(
                "CONCAT(UserAccount.lastname, ' ', UserAccount.firstname)" => 'asc')
        ),
        'PracticeLocation' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array(
                'PracticeLocation.location_name' => 'asc')
        ),
        'HealthMaintenancePlan' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array('HealthMaintenancePlan.plan_name' => 'ASC')
        ),
        'ClinicalAlert' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array('ClinicalAlert.alert_name' => 'ASC')
        ),
        'PatientReminder' => array(
            'limit' => 10,
            'page' => 1,
            'order' => array('PatientReminder.subject' => 'ASC')
        )
    );
	
	public function upload_directories()
	{
		$this->PracticeSetting->executeUploadSettings($this);
	}
    
    function saveServices()
    {
        $this->loadModel('PracticeSetting');
        $settings = $this->Session->read("PracticeSetting");
        $db_config = $this->PracticeSetting->getDataSource()->config;
        $cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
        Cache::delete($cache_file_prefix."emdeon_drug_search");
        Cache::delete($cache_file_prefix."emdeon_icd_search");
        Cache::delete($cache_file_prefix."emdeon_lab_client_list");
        Cache::delete($cache_file_prefix."emdeon_lab_list");
        Cache::delete($cache_file_prefix."emdeon_pharmacy_search");
        Cache::delete($cache_file_prefix."emdeon_test_code_search");
        Cache::delete($cache_file_prefix."dosespot_connection_status");
        Cache::delete($cache_file_prefix."emdeon_connection_status");
		
				//clear sync information
				$this->loadModel("EmdeonSyncStatus");
				$this->EmdeonSyncStatus->query('TRUNCATE TABLE emdeon_sync_status;');
				
				// Fixup emdeon settings: all emdeon settings must use the same emdeon_host and emdeon_facility,
				// But there are two places to input them: e-Labs and e-Prescribing
				if( isset($this->data['emdeon_facility_rx'] ) ){
					if( !isset($this->data['emdeon_facility']) || $this->data['emdeon_facility_rx'] != $settings['PracticeSetting']['emdeon_facility'] ){
						// No emdeon_facility setting, or the emdeon_facility_rx setting is different from the existing emdeon_facility
						$this->data['emdeon_facility'] = $this->data['emdeon_facility_rx'];
					}
					unset($this->data['emdeon_facility_rx']);
				}
				if( isset($this->data['emdeon_host_rx'] ) ){
					if( !isset($this->data['emdeon_host']) || $this->data['emdeon_host_rx'] != $settings['PracticeSetting']['emdeon_host'] ){
						// No emdeon_host setting, or the emdeon_host_rx setting is different from the existing emdeon_host
						$this->data['emdeon_host'] = $this->data['emdeon_host_rx'];
					}
					unset($this->data['emdeon_host_rx']);
				}
				if(isset($this->data['kareo_import_all_patients']) && $this->data['kareo_import_all_patients'] && $this->data['kareo_status'] && $this->data['kareo_user'] && $this->data['kareo_password'] && $this->data['kareo_customer_key']) {
					$kareo_import_shellcommand = "php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app \"".APP."\" kareo_import ".$db_config['database']." import_all >> /dev/null 2>&1 &";
					exec($kareo_import_shellcommand);
				}
				if(isset($this->data['kareo_export_all_patients']) && $this->data['kareo_export_all_patients'] && $this->data['kareo_status'] && $this->data['kareo_user'] && $this->data['kareo_password'] && $this->data['kareo_customer_key']) {
                                        $kareo_export_shellcommand = "php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app \"".APP."\" kareo_export ".$db_config['database']."  >> /dev/null 2>&1 &";
                                        exec($kareo_export_shellcommand);
                                }
				if (!empty($this->data['elab_flush_cache'])) {
				   //flush out all records from the cache lab test table
				   $this->loadModel("EmdeonTestCache");
				   $this->EmdeonTestCache->deleteAll(array('1 = 1'));
				}

                if( $this->data['hl7_engine'] ) {
                        // check to see if hl7_engine has changed, and if so run hl7_ignore for encounters
                        if( $settings['PracticeSetting']['hl7_engine'] != $this->data['hl7_engine'] ) {
                                if( empty( $this->data['hl7_receiver'] ) )
                                        $receiver = $this->data['hl7_engine'];
                                else
                                        $receiver = $this->data['hl7_receiver'];

				App::import( 'Lib', 'HL7Message', array('file'=>'HL7Message.php'));
                                HL7Message::runHL7Ignore( $db_config, $receiver, false, true, false );
                        }
                        // for macpractice only, check to see if hl7_sftp_produce_adts has just been set and if so run hl7_ignore for patients
                        if( $this->data['hl7_sftp_produce_adts'] && empty($settings['PracticeSetting']['hl7_sftp_produce_adts']) ) {
				App::import( 'Lib', 'HL7Message', array('file'=>'HL7Message.php'));
                                HL7Message::runHL7Ignore( $db_config, $this->data['hl7_engine'], true, false, false );
                        }
                }
        
        $this->PracticeSetting->save($this->data);
        exit();
    }
    
    function services()
    {
    	$emergency_access_type = (($this->getAccessType("preferences", "emergency_access") == 'NA') ? false : true);
    	$user_options_type = (($this->getAccessType("preferences", "user_options") == 'NA') ? false : true);
    	
    	
    	$this->loadModel('practiceSetting');
	$this->loadModel('PracticePlan');
        
        // Fetch all types of available plans
        $availablePlans = $this->PracticePlan->getPlans();
        
    	$settings  = $this->practiceSetting->getSettings();

        $this->set(compact('settings', 'emergency_access_type', 'user_options_type', 'availablePlans'));
    }
	
	public function service_connection_test()
	{
		$type = (isset($this->data['type'])) ? $this->data['type'] : "";
		$this->autoRender = false;
		if($type=='kareo')
		{
			$user = $this->data['kareo_user'];
			$password = $this->data['kareo_password'];
			$customer_key = $this->data['kareo_customer_key'];
			$practice_name = $this->data['kareo_practice_name'];
			$this->loadModel('Kareo');
			$response = $this->Kareo->testConnection($user, $password, $customer_key);
			echo json_encode($response);
			exit;
		}
	}
	
	public function manage_demo_database()
  {
    if(isset($api_user) && count($api_user) > 0) {
        $user = $api_user;
    } else {
        $user = $this->Session->read('UserAccount');
    } 
    $role_id = $user['role_id'];
		if($role_id != EMR_Roles::SYSTEM_ADMIN_ROLE_ID)
			die("Must be logged in as admin");
		if( !DemoDatabase::is_db_allowed() )
			die("Only allowed for demo databases");
		
    $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
    switch($task){
    case 'reset_demo_database': {
			$executed = DemoDatabase::reset_demo_database();
			$this->Session->setFlash(__($executed .' queries executed.', true));
			$this->redirect(array('action' => 'services'));
			} break;
    case 'populate_appointments': {
			$created = DemoDatabase::populate_appointments();
			$this->Session->setFlash(__($created .' appointments created.', true));
			$this->redirect(array('action' => 'services'));
		} break;
		default: {}    
    }
  }

    public function practice_settings()
    {
	    $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');
		$this->loadModel('PatientDemographic');
		
		switch ($task)
        {
            case "save":
			{
			    
				if($this->getAccessType() == "W")
				{
					if (!empty($this->data))
					{
						if (!isset($this->data['PracticeSetting']['setting_id']))
						{
							$this->PracticeSetting->create();
						}
						$this->data['PracticeSetting']['patient_status'] = ($this->data['PracticeSetting']['patient_status'] == 'on') ? 'yes' : 'no';
						$this->data['PracticeSetting']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$this->data['PracticeSetting']['modified_user_id'] = $user['user_id'];
	
						if ($this->data['usedefault'] == 'true')
						{
							$this->data['PracticeSetting']['instant_notification'] = 'Yes';
							$this->data['PracticeSetting']['test_patient_data'] = 'Yes';
							$this->data['PracticeSetting']['notification_time'] = '30';
							$this->data['PracticeSetting']['mrn_start'] = '100001';
							$this->data['PracticeSetting']['encounter_start'] = '1001';
							$this->data['PracticeSetting']['scale'] = 'English';
							$this->data['PracticeSetting']['autologoff'] = '20';
							$this->data['PracticeSetting']['labs_setup'] = 'Electronic';
							$this->data['PracticeSetting']['rx_setup'] = 'Standard';
							$this->data['PracticeSetting']['general_dateformat'] = 'm/d/Y';
							$this->data['PracticeSetting']['general_timeformat'] = '12';
	
							$default_email_info = $this->UserAccount->getDefaultSenderEmailInfo();
							$this->data['PracticeSetting']['sender_name'] = $default_email_info['sender_name'];
							$this->data['PracticeSetting']['sender_email'] = $default_email_info['sender_email'];
							$this->data['PracticeSetting']['patient_status'] = 'yes';
							
						}
						
						$this->data['PracticeSetting']['reminder_notify_json'] = $this->PracticeSetting->reminderNotifyJson($this->data['PracticeSetting']['notify_frequency'].'-'.$this->data['PracticeSetting']['notify_frequency_type']);
						
						if (isset($this->data['PracticeSetting']['encounter_start'])) {
							if($autoincrement = (int) $this->data['PracticeSetting']['encounter_start']) {
								$this->loadModel('EncounterMaster');

								$this->EncounterMaster->Query("ALTER TABLE  `encounter_master` AUTO_INCREMENT =$autoincrement");
							}
							
						}
						
						if (isset($this->data['PracticeSetting']['test_patient_data'])){
							//Change the patient status to Active/Inactive
							$newStatus = $this->data['PracticeSetting']['test_patient_data'] == 'No' ? 'Deleted' : 'Active';
							$test_datas = $this->PatientDemographic->find('all', array('conditions' => array('PatientDemographic.type_indicator' => 'E'),  'recursive' => -1));
							foreach ($test_datas as $test_data){   
								$this->data['PatientDemographic']['patient_id'] = $test_data['PatientDemographic']['patient_id'];
								$this->data['PatientDemographic']['status'] = $newStatus;
								$this->PatientDemographic->save($this->data);
							}
						}
	
						if ($this->PracticeSetting->save($this->data))
						{
							$this->Session->setFlash(__('Item(s) saved.', true));
							$this->redirect(array('action' => 'practice_settings'));
						}
						else
						{
							$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
						}
					}
				}
			} break;
            default:
			{
                                // Count current practice location settings
                                $this->set('locationCount',  count($this->PracticeLocation->getAllLocations()));
                                
                
				$user = $this->Session->read('UserAccount');
				$user_id = $user['user_id'];
				$items = $this->PracticeSetting->find('first');

				if (!empty($items))
				{
					if($this->getAccessType() == "W")
					{
						$default_email_info = $this->UserAccount->getDefaultSenderEmailInfo();
						$items['PracticeSetting']['sender_name'] = (strlen($items['PracticeSetting']['sender_name']) > 0) ? $items['PracticeSetting']['sender_name'] : $default_email_info['sender_name'];
						$items['PracticeSetting']['sender_email'] = (strlen($items['PracticeSetting']['sender_email']) > 0) ? $items['PracticeSetting']['sender_email'] : $default_email_info['sender_email'];
						$this->PracticeSetting->save($items);
					}

					$this->set('PracticeSetting', $this->sanitizeHTML($items));
				}
			} break;
        }
	}

    public function index()
    {
        $this->redirect(array('action' => 'practice_settings'));
    }

    public function general()
    {
        $this->redirect(array('action' => 'practice_settings'));
    }

    public function directories()
    {
        $this->redirect(array('action' => 'lab_facilities'));
    }

    public function point_of_care()
    {
        $this->redirect(array('action' => 'in_house_work_labs'));
    }

    public function no_access()
    {
        
    }

    function setup()
    {
        
    }

    public function check_username()
    {
        if (!empty($this->data))
        {
            $conditions = array(
                'UserAccount.username' => $this->data['UserAccount']['username']
            );

            if ($this->data['task'] == 'edit')
            {
                $conditions['UserAccount.user_id !='] = $this->data['user_id'];
            }

            $items = $this->UserAccount->find(
                'count', array(
                'conditions' => $conditions
                )
            );

            if ($items > 0)
            {
                echo "false";
            }
            else
            {
                echo "true";
            }
        }
        else
        {
            echo "false";
        }

        exit;
    }

    public function users()
    {
    	$this->loadModel("StateCode");
        $states = $this->StateCode->getList();
        $this->set("StateCode", $states);
            
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
				$user_id = (isset($this->params['named']['user_id'])) ? $this->params['named']['user_id'] : "";
				
        if ($task == 'addnew' || $task == 'edit')
        {
					
					
						$this->loadModel('AdministrationPrescriptionAuth');

						
						$authorized = array();
						if ($task == 'edit') {
							$authorized = $this->AdministrationPrescriptionAuth->getAuthorizedUsers($user_id);
						}
						$this->UserAccount->unbindModelAll();
						$authorizable = $this->UserAccount->find('all', array(
							'conditions' => array(
								'NOT' => array('UserAccount.status' => '0',
									'UserAccount.role_id' => array(
										EMR_Roles::PHYSICIAN_ROLE_ID,
										//EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID,
										//EMR_Roles::NURSE_PRACTITIONER_ROLE_ID,
										EMR_Roles::PATIENT_ROLE_ID,
										//EMR_Roles::PRACTICE_ADMIN_ROLE_ID,
										EMR_Roles::SYSTEM_ADMIN_ROLE_ID,
									),
								),
							),
						));

						$this->set(compact('authorized', 'authorizable'));						

					
					
            $this->set('roles', $this->sanitizeHTML($this->UserRole->getUserRoles()));
            $this->set('groups', $this->sanitizeHTML($this->UserGroup->getUserGroups()));

            if (!empty($this->data))
            {
                $this->data['UserAccount']['emergency'] = (int)@$this->data['UserAccount']['emergency'];
				
				if($this->data['UserAccount']['emergency'] == 1)
				{
					$this->data['UserAccount']['emergency_date'] = __date("Y-m-d H:i:s");
				}
            } 
	    else 
	    {
             	$ttl_docs=$this->UserAccount->find('count', array('conditions' => array('UserAccount.role_id' => EMR_Roles::PHYSICIAN_ROLE_ID), 'recursive' => -1) );
            	$this->set('current_total_doctors', $ttl_docs);
                $ttl_mids=$this->UserAccount->find('count', array('conditions' => array('UserAccount.role_id' => array(EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID)), 'recursive' => -1));
            	$this->set('current_total_midlevels', $ttl_mids);

	    }		
            $provider_roles = $this->UserGroup->getRoles(EMR_Groups::GROUP_ENCOUNTER_LOCK);
            $this->set('provider_roles', $provider_roles);
        }
        if ($task == 'addnew' and $patient_id != '')
        {
            $this->loadModel("PatientDemographic");
            $patient_names = $this->PatientDemographic->getPatientNamebyID($patient_id);
            if ($patient_names)
            {
                $this->set('patient_firstname', $patient_names['first_name']);
                $this->set('patient_lastname', $patient_names['last_name']);
            }
        }
        switch ($task)
        {
            case "addnew":
			{
				if($this->getAccessType() == "W")
				{
					if (!empty($this->data))
					{
						/*    -- This feature needs to be overhauled. this only works if doctor already has an account with Dosepot? what about before that?
						//Add Physician information into Dosespot
						if($this->data['UserAccount']['role_id']=='3')
						{
							$practicelocation_items = $this->PracticeLocation->find('first', array('conditions' => array('PracticeLocation.head_office' => 'Yes')));
							if($practicelocation_items==false)
							{
								$practicelocation_items = $this->PracticeLocation->find('first');
							}
							
							$formatted_phone = vsprintf("(%3s)%3s-%4s", explode('-',$practicelocation_items['PracticeLocation']['phone']));
							$formatted_fax = vsprintf("(%3s)%3s-%4s", explode('-',$practicelocation_items['PracticeLocation']['fax']));
							
							$soap_request = "<Clinician>\n";
							$soap_request .= "<FirstName>".$this->data['UserAccount']['firstname']."</FirstName>\n";
							$soap_request .= "<LastName>".$this->data['UserAccount']['lastname']."</LastName>\n";
							$soap_request .= "<DateOfBirth>".date('Y-m-d',strtotime($this->data['UserAccount']['dob']))."T00:00:00</DateOfBirth>\n";
							$soap_request .= "<Gender>".$this->data['UserAccount']['gender']."</Gender>\n";
							$soap_request .= "<Email>".$this->data['UserAccount']['email']."</Email>\n";
							$soap_request .= "<Address1>".$practicelocation_items['PracticeLocation']['address_line_1']."</Address1>\n";
							$soap_request .= "<Address2>".$practicelocation_items['PracticeLocation']['address_line_2']."</Address2>\n";
							$soap_request .= "<City>".$practicelocation_items['PracticeLocation']['city']."</City>\n";
							$soap_request .= "<State>".$practicelocation_items['PracticeLocation']['state']."</State>\n";
							$soap_request .= "<ZipCode>".$practicelocation_items['PracticeLocation']['zip']."</ZipCode>\n";
							$soap_request .= "<PrimaryPhone>".$formatted_phone."</PrimaryPhone>\n";
							$soap_request .= "<PrimaryPhoneType>Work</PrimaryPhoneType>\n";
							$soap_request .= "<PrimaryFax>".$formatted_fax."</PrimaryFax>\n";
							$soap_request .= "<DEANumber>".$this->data['UserAccount']['dea']."</DEANumber>\n";
							$soap_request .= "<NPINumber>".$this->data['UserAccount']['npi']."</NPINumber>\n";
							$soap_request .= "</Clinician>\n";
					
							$dosespot_xml_api = new Dosespot_XML_API();
							$result_xml = $dosespot_xml_api->addClinician($soap_request);
							if($result_xml!=false)
							{
								$this->data['UserAccount']['dosespot_clinician_id'] = $result_xml;
							}
						}
						*/
						
						site::setting('password_expires', access::PASSWORD_EXPIRES_TIME_DEFAULT);
						
                                                // Set last password update to zero
                                                // so this user sees the expire password change
						$this->data['UserAccount']['password_last_update'] =  0;
						$this->data['UserAccount']['firstname'] = trim($this->data['UserAccount']['firstname']);
						$this->data['UserAccount']['lastname'] = trim($this->data['UserAccount']['lastname']);
						
						if ($this->UserAccount->save($this->data))
						{
							
							if (trim($this->data['UserAccount']['email'])) {
								
								
								
								$practiceProfile = ClassRegistry::init('PracticeProfile')->find('first');
								$practiceSetting = ClassRegistry::init('PracticeSetting')->getSettings();
								$customer = $practiceSetting->practice_id;
								$partner_id = $practiceSetting->partner_id;
								
								$practice_logo = $practiceProfile['PracticeProfile']['logo_image'];
								$embed_logo_path = '';
								if($practice_logo ) {
										$embed_logo_path = ROOT . '/app/webroot/CUSTOMER_DATA/'.$practiceSetting->practice_id.'/' . $practiceSetting->uploaddir_administration.'/'.$practice_logo;
										if(!file_exists($embed_logo_path)) {$embed_logo_path='';   }
								}								
								
								
								
								$password = $this->data['UserAccount']['password2'];
								$name = $this->data['UserAccount']['firstname'] . ' ' . $this->data['UserAccount']['lastname'];
								$email = $this->data['UserAccount']['email'];
								$username = $this->data['UserAccount']['username'];


								$htmlHelper = new HtmlHelper();

								$this->layout = 'empty';
								$this->set(compact('name', 'email', 'password', 'username', 'customer', 'partner_id'));

								$content = $this->render('../elements/email/html/user_added');

								email::send($name, $email, 'Your Account Has Been Added', $content,'','',true,'','','','',$embed_logo_path);								
							}
							
							if ($this->data['UserAccount']['role_id'] == EMR_Roles::PHYSICIAN_ROLE_ID || $this->data['UserAccount']['role_id'] == EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID || $this->data['UserAccount']['role_id'] == EMR_Roles::NURSE_PRACTITIONER_ROLE_ID) {
								$user_id = $this->UserAccount->getLastInsertID();
								$assignees = isset($this->data['AdministrationPrescriptionAuth']) ? $this->data['AdministrationPrescriptionAuth'] : array();
								$authorizableIds = Set::extract('/UserAccount/user_id', $authorizable);
								$assignees = array_intersect($assignees, $authorizableIds);
								$this->AdministrationPrescriptionAuth->setAuthorizedUsers($user_id, $assignees);
							}
							
							
							$this->Session->setFlash(__(' Item(s) added.', true));
							$this->redirect(array('action' => 'users'));
						}
						else
						{
							$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
						}
					}
				}
			} break;
            case "edit":
			{
				
				if($this->UserAccount->getUserRole($user_id) == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)
				{
					$this->redirect(array('action' => 'users'));
				}
				
				if (!empty($this->data))
				{
					if($this->getAccessType() == "W")
					{
						$this->data['UserAccount']['clear_status_flag'] = 0;
						$this->data['UserAccount']['firstname'] = trim($this->data['UserAccount']['firstname']);
						$this->data['UserAccount']['lastname'] = trim($this->data['UserAccount']['lastname']);
						
						$user = $this->UserAccount->getUserByID($user_id);
						
						if ($user_id && $this->data['UserAccount']['status'])
						{
	
							if (!$user->status)
							{
								$this->data['UserAccount']['clear_status_flag'] = 1;
								EMR_Security::reEnableAccountProcess($user_id);
							}
						}
						
						if ($user->role_id == EMR_Roles::PHYSICIAN_ROLE_ID || $user->role_id == EMR_Roles::NURSE_PRACTITIONER_ROLE_ID || $user->role_id == EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID) {
							$assignees = isset($this->data['AdministrationPrescriptionAuth']) ? $this->data['AdministrationPrescriptionAuth'] : array();
							$authorizableIds = Set::extract('/UserAccount/user_id', $authorizable);
							$assignees = array_intersect($assignees, $authorizableIds);
							$this->AdministrationPrescriptionAuth->setAuthorizedUsers($user_id, $assignees);
						}

						//    -- This feature needs to be overhauled. this only works if doctor already has an account with Dosepot? what about before that?
					   
						//Update Physician information in Dosespot 
						/*
						if($this->data['UserAccount']['role_id']==EMR_Roles::PHYSICIAN_ROLE_ID and $this->data['UserAccount']['dosepot_singlesignon_userid']!='')
						{
							$practicelocation_items = $this->PracticeLocation->find('first', array('conditions' => array('PracticeLocation.head_office' => 'Yes')));
							if($practicelocation_items==false)
							{
								$practicelocation_items = $this->PracticeLocation->find('first');
							}
	
							$formatted_phone = "(000)-000-0000";
							if(strlen($practicelocation_items['PracticeLocation']['phone']) > 0)
							{
								$raw_phone_array = explode('-',$practicelocation_items['PracticeLocation']['phone']);
								
								if(count($raw_phone_array) == 3)
								{
									$formatted_phone = vsprintf("(%3s)%3s-%4s", $raw_phone_array);
								}
							}
							
							$formatted_fax = "(000)-000-0000";
							if(strlen($practicelocation_items['PracticeLocation']['fax']) > 0)
							{
								$raw_fax_array = explode('-',$practicelocation_items['PracticeLocation']['fax']);
								
								if(count($raw_fax_array) == 3)
								{
									$formatted_fax = vsprintf("(%3s)%3s-%4s", $raw_fax_array);
								}
							}
	
							$soap_request = "<Clinician>\n";
							if($this->data['UserAccount']['dosespot_clinician_id'] != '' or $this->data['UserAccount']['dosespot_clinician_id'] != NULL)
							{
							   $soap_request .= "<ClinicianId>".$this->data['UserAccount']['dosespot_clinician_id']."</ClinicianId>\n";
							}
							$soap_request .= "<FirstName>".$this->data['UserAccount']['firstname']."</FirstName>\n";
							$soap_request .= "<LastName>".$this->data['UserAccount']['lastname']."</LastName>\n";
							$soap_request .= "<DateOfBirth>".date('Y-m-d',strtotime($this->data['UserAccount']['dob']))."T00:00:00</DateOfBirth>\n";
							$soap_request .= "<Gender>".$this->data['UserAccount']['gender']."</Gender>\n";
							$soap_request .= "<Email>".$this->data['UserAccount']['email']."</Email>\n";
							$soap_request .= "<Address1>".$practicelocation_items['PracticeLocation']['address_line_1']."</Address1>\n";
							$soap_request .= "<Address2>".$practicelocation_items['PracticeLocation']['address_line_2']."</Address2>\n";
							$soap_request .= "<City>".$practicelocation_items['PracticeLocation']['city']."</City>\n";
							$soap_request .= "<State>".$practicelocation_items['PracticeLocation']['state']."</State>\n";
							$soap_request .= "<ZipCode>".$practicelocation_items['PracticeLocation']['zip']."</ZipCode>\n";
							$soap_request .= "<PrimaryPhone>".$formatted_phone."</PrimaryPhone>\n";
							$soap_request .= "<PrimaryPhoneType>Work</PrimaryPhoneType>\n";
							$soap_request .= "<PrimaryFax>".$formatted_fax."</PrimaryFax>\n";
							$soap_request .= "<DEANumber>".$this->data['UserAccount']['dea']."</DEANumber>\n";
							$soap_request .= "<NPINumber>".$this->data['UserAccount']['npi']."</NPINumber>\n";
							$soap_request .= "</Clinician>\n";
							$dosespot_xml_api = new Dosespot_XML_API();
							$result_xml = $dosespot_xml_api->addClinician($soap_request, $this->data['UserAccount']['dosepot_singlesignon_userid']);
	
							if($result_xml!=false)
							{
								$this->data['UserAccount']['dosespot_clinician_id'] = $result_xml;
							} else {
                                                                //  clinician_reference_id is same as dosespot_clinician_id per Dosespot
                                                                $this->data['UserAccount']['dosespot_clinician_id'] = $this->data['UserAccount']['dosepot_singlesignon_userid'];

                                                        }
							
						}
						*/
                                                
                                                $existingUserAccount = $this->UserAccount->getCurrentUser($user_id);

                                                // If we are changing the password, set password_last_update to 0
                                                // to trigger password change for user
                                                if ($this->data['UserAccount']['password'] !== $existingUserAccount['password']) {
                                                    $this->data['UserAccount']['password_last_update'] = 0;
                                                }
						
						if($this->data['UserAccount']['emergency'] == 1)
						{
							//check if its already activated previously, then no need to put date
							$previous_emergency_value = $this->UserAccount->getEmergencyAccess($user_id);
							
							if($previous_emergency_value == 1)
							{
								unset($this->data['UserAccount']['emergency_date']);
							}
						}
						
						
						if ($this->UserAccount->save($this->data))
						{
							$this->Session->setFlash(__('Item(s) saved.', true));
							$this->redirect(array('action' => 'users'));
						}
						else
						{
							$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
						}
					}
				}
				else
				{
					$items = $this->UserAccount->find('first', array('conditions' => array('UserAccount.user_id' => $user_id)));
					$this->set('EditItem', $this->sanitizeHTML($items));
				}
			} break;
            case "delete":
			{
				if($this->getAccessType() == "W")
				{
					if (!empty($this->data))
					{
						$user_ids = $this->data['UserAccount']['user_id'];
						$delete_count = 0;
	
						$this->loadModel('AdministrationPrescriptionAuth');

						$this->AdministrationPrescriptionAuth->deleteAll(array(
							'AdministrationPrescriptionAuth.prescribing_user_id' => $user_ids,
						));
						
						
						foreach ($user_ids as $user_id)
						{
							$this->UserAccount->delete($user_id, false);
							$delete_count++;
						}
							
						if ($delete_count > 0)
						{
							$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
						}
					}
				}

				$this->redirect(array('action' => 'users'));
			} break;
			case 'download_eprescribing_signup_form':
			{
				$targetPath =  UploadSettings::getPath('help');
				
				$file = 'eprescribing_verification_request_form.pdf';
				$targetFile = $targetPath.$file;
			
				header('Content-Type: application/octet-stream; name="'.$file.'"'); 
				header('Content-Disposition: attachment; filename="'.$file.'"'); 
				header('Accept-Ranges: bytes'); 
				header('Pragma: no-cache'); 
				header('Expires: 0'); 
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
				header('Content-transfer-encoding: binary'); 
				header('Content-length: ' . @filesize($targetFile)); 
				@readfile($targetFile);
			
			exit;
			
			}break;
      default:
				{
					$user = $this->Session->read('UserAccount');
					$this->set('users', $this->sanitizeHTML($this->paginate('UserAccount', 
							array('UserAccount.role_id !=' .EMR_Roles::SYSTEM_ADMIN_ROLE_ID.
										' AND UserAccount.role_id !=' =>EMR_Roles::PATIENT_ROLE_ID)))); // Hide admin users
				}
			}
    }

    public function user_roles()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $this->loadModel("Acl");

        switch ($task)
        {
            case "addnew":
            {
                if (!empty($this->data))
                {
                    $this->UserRole->create();
            
                    if($this->UserRole->save($this->data))
                    {
                        $role_id = $this->UserRole->getLastInsertId();
                        $opts = $this->data['opt'];
            
                        foreach($opts as $menu_id => $access)
                        {
                            $acl_data = array();
                            $acl_data['Acl']['menu_id'] = $menu_id;
                            $acl_data['Acl']['role_id'] = $role_id;
                            $acl_data['Acl']['acl_read'] = '0';
                            $acl_data['Acl']['acl_write'] = '0';
            
                            if($access == 'R')
                            {
                                $acl_data['Acl']['acl_read'] = '1';
                            }
            
                            if($access == 'W')
                            {
                                $acl_data['Acl']['acl_write'] = '1';
                            }
            
                            $this->Acl->create();
                            $this->Acl->save($acl_data);
                        }
            
                        $this->Session->setFlash(__('Item(s) added.', true));
                        $this->redirect(array('action' => 'user_roles'));
                    }
                    else
                    {
                        $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                    }
                }
            } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
                        if($this->getAccessType() == "W")
                        {
                            if ($this->UserRole->save($this->data))
                            {
                                $role_id = $this->data['UserRole']['role_id'];
                                $opts = $this->data['opt'];
								$use_default = $this->data['use_default'];
								
								if($use_default == '1')
								{
									$opts = $this->UserRole->getDefaultAcls($role_id);
								}
    
                                foreach ($opts as $menu_id => $access)
                                {
                                    $acl_data = array();
    
                                    $acls = $this->Acl->find
                                        (
                                        'first', array
                                        (
                                        'conditions' => array('Acl.role_id' => $role_id, 'Acl.menu_id' => $menu_id)
                                        )
                                    );
    
                                    if (!empty($acls))
                                    {
                                        $acl_data = $acls;
                                        $acl_data['Acl']['acl_read'] = '0';
                                        $acl_data['Acl']['acl_write'] = '0';
                                    }
                                    else
                                    {
                                        $acl_data['Acl']['menu_id'] = $menu_id;
                                        $acl_data['Acl']['role_id'] = $role_id;
                                        $acl_data['Acl']['acl_read'] = '0';
                                        $acl_data['Acl']['acl_write'] = '0';
    
                                        $this->Acl->create();
                                    }
    
                                    if ($access == 'R')
                                    {
                                        $acl_data['Acl']['acl_read'] = '1';
                                    }
    
                                    if ($access == 'W')
                                    {
                                        $acl_data['Acl']['acl_write'] = '1';
                                    }
    
                                    $this->Acl->save($acl_data);
                                }
    
																$db_config = $this->Acl->getDataSource()->config;
																$cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';								
																
																Cache::set(array('duration' => '+30 days'));
																Cache::delete($cache_file_prefix.'loadMenu'.$role_id);
                                $this->Session->setFlash(__('Item(s) saved.', true));
                                $this->redirect(array('action' => 'user_roles'));
                            }
                            else
                            {
                                $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
                            }
                        }
                    }
                    else
                    {
                        $role_id = (isset($this->params['named']['role_id'])) ? $this->params['named']['role_id'] : "";
                        $items = $this->UserRole->find(
                            'first', array(
                            'conditions' => array('UserRole.role_id' => $role_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));

                        $acls = $this->Acl->find
                            (
                            'all', array
                            (
                            'conditions' => array('Acl.role_id' => $role_id)
                            )
                        );
                        $opt = array();

                        foreach ($acls as $acl)
                        {
                            $acl_data = $acl['Acl'];

                            if ($acl_data['acl_read'] == '1')
                            {
                                $opt[$acl_data['menu_id']] = 'R';
                            }
                            else if ($acl_data['acl_write'] == '1')
                            {
                                $opt[$acl_data['menu_id']] = 'W';
                            }
                            else
                            {
                                $opt[$acl_data['menu_id']] = 'NA';
                            }
                        }

                        $this->set('opt', $this->sanitizeHTML($opt));
                    }
                } break;
            case "delete":
                {
                    if($this->getAccessType() == "W")
                    {
                        if (!empty($this->data))
                        {
                            $role_ids = $this->data['UserRole']['role_id'];
                            $delete_count = 0;
    
                            foreach ($role_ids as $role_id)
                            {
                                $this->Acl->deleteAll(array("Acl.role_id" => $role_id), false);
                                $this->UserRole->delete($role_id, false);
                                $delete_count++;
                            }
    
                            if ($delete_count > 0)
                            {
                                $this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                            }
                        }
                    }

                    $this->redirect(array('action' => 'user_roles'));
                } break;
            default:
                {
                    $this->UserRole->recursive = -1;
                    $roles = $this->paginate('UserRole', array('UserRole.role_id !=' => '10'));
                    $this->set('UserRole', $this->sanitizeHTML($roles));
                }
        }
    }

    public function user_groups()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');
        $group_id = (isset($this->params['named']['group_id'])) ? $this->params['named']['group_id'] : "0";

        $this->set("group_functions", $this->UserGroup->getFunctions($group_id));

        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$this->UserGroup->create();
	
							if (isset($this->params['form']['user_roles']))
							{
								$this->data['UserGroup']['group_roles'] = implode("-", $this->params['form']['user_roles']);
							}
	
							if ($this->UserGroup->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) added.', true));
								$this->redirect(array('action' => 'user_groups'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
							}
						}
	
						$this->UserRole->recursive = -1;
						
						$UserRoles = $this->UserRole->find('all', array(
							'conditions' => array(
								'UserRole.role_id !=' => '10'
							),
						));
						
						$this->set('UserRoles', $this->sanitizeHTML($UserRoles));
					}
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							if (isset($this->params['form']['user_roles']))
							{
								$this->data['UserGroup']['group_roles'] = implode("-", $this->params['form']['user_roles']);
							}
	
							$this->data['UserGroup']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['UserGroup']['modified_user_id'] = $user['user_id'];
	
							if ($this->UserGroup->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) saved.', true));
								$this->redirect(array('action' => 'user_groups'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
							}
						}
                    }
                    else
                    {

                        $items = $this->UserGroup->find(
                            'all', array(
                            'conditions' => array('UserGroup.group_id' => $group_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items[0]));

                        $this->UserRole->recursive = -1;
												$UserRoles = $this->UserRole->find('all', array(
													'conditions' => array(
														'UserRole.role_id !=' => '10'
													),
												));

												$this->set('UserRoles', $this->sanitizeHTML($UserRoles));                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$group_id = $this->data['UserGroup']['group_id'];
							$delete_count = 0;
	
							foreach ($group_id as $group_id)
							{
								$this->UserGroup->delete($group_id, false);
								$delete_count++;
							}
	
							if ($delete_count > 0)
							{
								$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
							}
						}
					}
					
                    $this->redirect(array('action' => 'user_groups'));
                } break;
            default:
                {
                    $this->set('UserGroups', $this->sanitizeHTML($this->paginate('UserGroup')));

                    foreach ($this->viewVars['UserGroups'] as $UserGroup):

                        $UserRoles = $this->UserRole->find(
                            'all', array(
                            'conditions' => array('UserRole.role_id' => explode("-", $UserGroup['UserGroup']['group_roles'])),
                            'order' => array('UserRole.role_desc' => 'asc')
                            )
                        );

                        $group_roles = "";

                        foreach ($UserRoles as $UserRole):
                            $group_roles .= $UserRole['UserRole']['role_desc'] . ", ";
                        endforeach;

                        if ($group_roles)
                        {
                            $group_roles = substr($group_roles, 0, -2);
                        }
                        else
                        {
                            $group_roles = "None";
                        }

                        $this->set('GroupRoles_' . $UserGroup['UserGroup']['group_id'], $group_roles);

                    endforeach;
                } break;
        }
    }

    public function user_locations()
    {
        $user = $this->Session->read('UserAccount');
        $this->paginate['UserLocation']['order'] = array('UserLocation.login_timestamp' => 'desc');
        $this->set('UserLocations', $this->sanitizeHTML($this->paginate('UserLocation')));
    }
	
    public function practice_profile()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');
        switch ($task)
        {
            case "save":
			{
				if($this->getAccessType() == "W")
				{
					if (!empty($this->data))
					{
						$this->data['PracticeProfile']['payment_option'] = ((isset($this->params['form']['payment_option_1'])) ? $this->params['form']['payment_option_1'] : "") . "|" . ((isset($this->params['form']['payment_option_2'])) ? $this->params['form']['payment_option_2'] : "") . "|" . ((isset($this->params['form']['payment_option_3'])) ? $this->params['form']['payment_option_3'] : "") . "|" . ((isset($this->params['form']['payment_option_4'])) ? $this->params['form']['payment_option_4'] : "") . "|" . ((isset($this->params['form']['payment_option_5'])) ? $this->params['form']['payment_option_5'] : "");
						
						if ($this->data['PracticeProfile']['logo_is_uploaded'] == "true")
						{
							$source_file = $this->paths['temp'] . $this->data['PracticeProfile']['logo_image'];
							$shrinkLength=substr($this->data['PracticeProfile']['logo_image'], -45); //only last 45 char
							$this->data['PracticeProfile']['logo_image'] = $shrinkLength; //rename it so it will save properly in the db
							$destination_file = $this->paths['administration'] . $shrinkLength;
							@rename($source_file, $destination_file);

						}                        
						$this->PracticeProfile->save($this->data, false);
						$this->Session->setFlash(__('Item(s) saved.', true));
						$this->redirect(array('action' => 'practice_profile'));
					}
				}
			} break;
            default:
			{
				$items = $this->PracticeProfile->find(
					'first', array(
					'conditions' => array('PracticeProfile.profile_id' => '1')
					)
				);

				if (empty($items))
				{
					// No practice settings yet, generate an empty array
					$fields = array_keys($this->PracticeProfile->schema());
					$items = array('PracticeProfile' => array());
					
					foreach ($fields as $f) {
						$items['PracticeProfile'][$f] = '';
					}
				}
                                
				$this->set('PracticeProfile', $this->sanitizeHTML($items));
			} break;
        }
    }

    function practice_locations()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');

        $this->loadModel("PatientDemographic");
        $this->loadModel("ScheduleCalendar");
        $this->loadModel("StateCode");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');
        $states = $this->StateCode->getList();
        $this->set("StateCode", $states);

        switch ($task)
        {
            case "update_patient_status":
			{
				$this->PatientDemographic->recursive= -1;
				$demographic_items = $this->PatientDemographic->find('all');
				foreach ($demographic_items as $demographic_item)
				{
					$last_shedule_of_patient = $this->ScheduleCalendar->find('first', array('conditions' => array('ScheduleCalendar.patient_id' => $demographic_item['PatientDemographic']['patient_id']), 'order' => array('ScheduleCalendar.calendar_id DESC'), 'limit' => '1', 'recursive' => -1));
					//var_dump($last_shedule_of_patient);
					if (!empty($last_shedule_of_patient))
					{
						$start_ts = strtotime($last_shedule_of_patient['ScheduleCalendar']['date']);
						// echo 'start: '.$start_ts;
						$end_ts = strtotime(date("m/d/y"));
						$diff = $end_ts - $start_ts;
						$daysdiff = round($diff / 86400);
						if ($daysdiff > 1095)
						{
							$this->data['PatientDemographic']['status'] = 'Inactive';
							$this->PatientDemographic->save($this->data);
						}
					}
					else
					{

						$d = $this->data['PatientDemographic']['modified_timestamp'];
						list($year) = split(' ', $d);
						//echo "Year: $year";
						$start_ts = strtotime($year);
						$end_ts = strtotime(date("m/d/y"));
						$diff = $end_ts - $start_ts;
						$daysdiff = round($diff / 86400);
						if ($daysdiff > 1095)
						{
							$this->data['PatientDemographic']['status'] = 'Inactive';
							$this->PatientDemographic->save($this->data);
						}
					}
				}
			}
            case "addnew":
			{
				if($this->getAccessType() == "W")
				{
					if (!empty($this->data))
					{
						extract($this->params['form']);
	
						if ($this->__global_time_format == "12")
						{
							if ($start_ampm == "PM" and $_start_hour != 12)
							{
								$start_hour = $_start_hour + 12;
								$_start_hour = $_start_hour + 12;
								//echo 'Start hour'.$start_hour;
							}
							else if ($start_ampm == "AM" and $_start_hour == 12)
							{
								$start_hour = 0;
								$_start_hour = 0;
							}
	
							if ($end_ampm == "PM" and $_end_hour != 12)
							{
								$end_hour = $_end_hour + 12;
								$_end_hour = $_end_hour + 12;
							}
							else if ($end_ampm == "AM" and $_end_hour == 12)
							{
								$end_hour = 0;
								$_end_hour = 0;
							}
	
							if ($lunch_start_ampm == "PM" and $_lunch_start_hour != 12)
							{
								$lunch_start_hour = $_lunch_start_hour + 12;
								$_lunch_start_hour = $_lunch_start_hour + 12;
							}
							else if ($lunch_start_ampm == "AM" and $_lunch_start_hour == 12)
							{
								$lunch_start_hour = 0;
								$_lunch_start_hour = 0;
							}
	
							if ($lunch_end_ampm == "PM" and $_lunch_end_hour != 12)
							{
								$lunch_end_hour = $_lunch_end_hour + 12;
								$_lunch_end_hour = $_lunch_end_hour + 12;
							}
							else if ($lunch_end_ampm == "AM" and $_lunch_end_hour == 12)
							{
								$lunch_end_hour = 0;
								$_lunch_end_hour = 0;
							}
	
							if ($dinner_start_ampm == "PM" and $_dinner_start_hour != 12)
							{
								$dinner_start_hour = $_dinner_start_hour + 12;
								$_dinner_start_hour = $_dinner_start_hour + 12;
							}
							else if ($dinner_start_ampm == "AM" and $_dinner_start_hour == 12)
							{
								$dinner_start_hour = 0;
								$_dinner_start_hour = 0;
							}
	
							if ($dinner_end_ampm == "PM" and $_dinner_end_hour != 12)
							{
								$dinner_end_hour = $_dinner_end_hour + 12;
								$_dinner_end_hour = $_dinner_end_hour + 12;
							}
							else if ($dinner_end_ampm == "AM" and $_dinner_end_hour == 12)
							{
								$dinner_end_hour = 0;
								$_dinner_end_hour = 0;
							}
	
							$start_minute = $_start_minute;
							$end_minute = $_end_minute;
							$lunch_start_minute = $_lunch_start_minute;
							$lunch_end_minute = $_lunch_end_minute;
							$dinner_start_minute = $_dinner_start_minute;
							$dinner_end_minute = $_dinner_end_minute;
						}
	
						$this->data['PracticeLocation']['operation_days'] = @implode("|", $operation_days);
						$this->data['PracticeLocation']['operation_start'] = ($this->__global_time_format == "12") ? ($_start_hour . ':' . $_start_minute) : ($start_hour . ':' . $start_minute);
						$this->data['PracticeLocation']['operation_end'] = ($this->__global_time_format == "12") ? ($_end_hour . ':' . $_end_minute) : ($end_hour . ':' . $end_minute);
						$this->data['PracticeLocation']['lunch_starthour'] = ($this->__global_time_format == "12") ? ($_lunch_start_hour . ':' . $_lunch_start_minute) : ($lunch_start_hour . ':' . $lunch_start_minute);
						$this->data['PracticeLocation']['lunch_endhour'] = ($this->__global_time_format == "12") ? ($_lunch_end_hour . ':' . $_lunch_end_minute) : ($lunch_end_hour . ':' . $lunch_end_minute);
						$this->data['PracticeLocation']['dinner_starthour'] = ($this->__global_time_format == "12") ? ($_dinner_start_hour . ':' . $_dinner_start_minute) : ($dinner_start_hour . ':' . $dinner_start_minute);
						$this->data['PracticeLocation']['dinner_endhour'] = ($this->__global_time_format == "12") ? ($_dinner_end_hour . ':' . $_dinner_end_minute) : ($dinner_end_hour . ':' . $dinner_end_minute);
						$this->data['PracticeLocation']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$this->data['PracticeLocation']['modified_user_id'] = $user['user_id'];
	
						$this->data['PracticeLocation']['general_localtime_auto_adjust'] = (int) @$this->data['PracticeLocation']['general_localtime_auto_adjust'];
	
						$this->PracticeLocation->create();
	
						if ($this->PracticeLocation->save($this->data))
						{
							$this->Session->setFlash(__('Item(s) added.', true));
							$this->redirect(array('action' => 'practice_locations'));
						}
						else
						{
							$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
						}
					}
				}
			} break;
            case "edit":
			{
				if (!empty($this->data))
				{
					if($this->getAccessType() == "W")
					{
						extract($this->params['form']);
						if ($this->__global_time_format == "12")
						{
							if ($start_ampm == "PM" and $_start_hour != 12)
							{
								$start_hour = $_start_hour + 12;
								$_start_hour = $_start_hour + 12;
							}
							else if ($start_ampm == "AM" and $_start_hour == 12)
							{
								$start_hour = 0;
								$_start_hour = 0;
							}
	
							if ($end_ampm == "PM" and $_end_hour != 12)
							{
								$end_hour = $_end_hour + 12;
								$_end_hour = $_end_hour + 12;
							}
							else if ($end_ampm == "AM" and $_end_hour == 12)
							{
								$end_hour = 0;
								$_end_hour = 0;
							}
	
							if ($lunch_start_ampm == "PM" and $_lunch_start_hour != 12)
							{
								$lunch_start_hour = $_lunch_start_hour + 12;
								$_lunch_start_hour = $_lunch_start_hour + 12;
							}
							else if ($lunch_start_ampm == "AM" and $_lunch_start_hour == 12)
							{
								$lunch_start_hour = 0;
								$_lunch_start_hour = 0;
							}
	
							if ($lunch_end_ampm == "PM" and $_lunch_end_hour != 12)
							{
								$lunch_end_hour = $_lunch_end_hour + 12;
								$_lunch_end_hour = $_lunch_end_hour + 12;
							}
							else if ($lunch_end_ampm == "AM" and $_lunch_end_hour == 12)
							{
								$lunch_end_hour = 0;
								$_lunch_end_hour = 0;
							}
	
							if ($dinner_start_ampm == "PM" and $_dinner_start_hour != 12)
							{
								$dinner_start_hour = $_dinner_start_hour + 12;
								$_dinner_start_hour = $_dinner_start_hour + 12;
							}
							else if ($dinner_start_ampm == "AM" and $_dinner_start_hour == 12)
							{
								$dinner_start_hour = 0;
								$_dinner_start_hour = 0;
							}
	
							if ($dinner_end_ampm == "PM" and $_dinner_end_hour != 12)
							{
								$dinner_end_hour = $_dinner_end_hour + 12;
								$_dinner_end_hour = $_dinner_end_hour + 12;
							}
							else if ($dinner_end_ampm == "AM" and $_dinner_end_hour == 12)
							{
								$dinner_end_hour = 0;
								$dinner_end_hour = 0;
							}
	
							$start_minute = $_start_minute;
							$end_minute = $_end_minute;
							$lunch_start_minute = $_lunch_start_minute;
							$lunch_end_minute = $_lunch_end_minute;
							$dinner_start_minute = $_dinner_start_minute;
							$dinner_end_minute = $_dinner_end_minute;
						}
	
						$this->data['PracticeLocation']['operation_days'] = @implode("|", $operation_days);
						$this->data['PracticeLocation']['operation_start'] = ($this->__global_time_format == "12") ? ($_start_hour . ':' . $_start_minute) : ($start_hour . ':' . $start_minute);
						$this->data['PracticeLocation']['operation_end'] = ($this->__global_time_format == "12") ? ($_end_hour . ':' . $_end_minute) : ($end_hour . ':' . $end_minute);
						$this->data['PracticeLocation']['lunch_starthour'] = ($this->__global_time_format == "12") ? ($_lunch_start_hour . ':' . $_lunch_start_minute) : ($lunch_start_hour . ':' . $lunch_start_minute);
						$this->data['PracticeLocation']['lunch_endhour'] = ($this->__global_time_format == "12") ? ($_lunch_end_hour . ':' . $_lunch_end_minute) : ($lunch_end_hour . ':' . $lunch_end_minute);
						$this->data['PracticeLocation']['dinner_starthour'] = ($this->__global_time_format == "12") ? ($_dinner_start_hour . ':' . $_dinner_start_minute) : ($dinner_start_hour . ':' . $dinner_start_minute);
						$this->data['PracticeLocation']['dinner_endhour'] = ($this->__global_time_format == "12") ? ($_dinner_end_hour . ':' . $_dinner_end_minute) : ($dinner_end_hour . ':' . $dinner_end_minute);
						$this->data['PracticeLocation']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$this->data['PracticeLocation']['modified_user_id'] = $user['user_id'];
	
						$this->data['PracticeLocation']['general_localtime_auto_adjust'] = (int) @$this->data['PracticeLocation']['general_localtime_auto_adjust'];
	
						if ($this->PracticeLocation->save($this->data))
						{
							$this->Session->setFlash(__('Item(s) saved.', true));
							$this->redirect(array('action' => 'practice_locations'));
						}
						else
						{
							$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
						}
					}
				}
				else
				{
					$id = (isset($this->params['named']['location_id'])) ? $this->params['named']['location_id'] : "";
					$items = $this->PracticeLocation->find(
						'first', array(
						'conditions' => array('PracticeLocation.location_id' => $id)
						)
					);

					$this->set('EditItem', $this->sanitizeHTML($items));
				}
			} break;
            case "delete":
			{
				if($this->getAccessType() == "W")
				{
					if (!empty($this->data))
					{
						$id = $this->data['PracticeLocation']['location_id'];
						$delete_count = 0;
	
						foreach ($id as $id)
						{
							$this->PracticeLocation->delete($id, false);
							$delete_count++;
						}
	
						if ($delete_count > 0)
						{
							$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
						}
					}
				}
				
				$this->redirect(array('action' => 'practice_locations'));
			} break;
            default:
			{
				$this->set('PracticeLocations', $this->sanitizeHTML($this->paginate('PracticeLocation')));
			} break;
        }
    }

	public function encounter_tabs() {
		$this->loadModel('PracticeEncounterType');
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$encounterTypeId = (isset($this->params['named']['encounter_type_id'])) ? intval($this->params['named']['encounter_type_id']) : 1;

		$encounterTypes = $this->PracticeEncounterType->find('all');
		$this->set(compact('encounterTypes', 'encounterTypeId'));
		
		if ( $task == "blank" ) {
			$this->layout = "blank";
		} else {
			$this->loadModel("PracticeEncounterTab");

			switch ( $task ) {
				case "save" : {
						if ( $this->getAccessType() == "W" ) {
							if ( !empty($this->data) ) {
								$tabs = isset($this->params['form']['tab']) ? $this->params['form']['tab'] : array();

								foreach ( $tabs as $tabId => $hide ) {
									$this->PracticeEncounterTab->id = $tabId;
									$this->PracticeEncounterTab->saveField('hide', $hide);
								}

								if ( $this->data['usedefault'] == "false" ) {
									$this->data['TabOrdering'] = explode("&", $this->data['NewTabOrdering']);
								}
								for ( $i = 0; $i < count($this->data['TabOrdering']); ++$i ) {
									if ( $this->data['usedefault'] == "false" ) {
										$this->data['PracticeEncounterTab']['tab_id'] = substr($this->data['TabOrdering'][$i], 6);
										$this->data['PracticeEncounterTab']['order'] = $i;
									} else {
										$this->data['PracticeEncounterTab']['tab_id'] = $this->data['TabOrdering']['tab_id' . $i];
										$this->data['PracticeEncounterTab']['order'] = $this->data['TabOrdering']['tab_id' . $i];
									}
									if ( $this->PracticeEncounterTab->save($this->data) ) {
										$this->Session->setFlash(__('Item(s) saved.', true));
										if ( $i == count($this->data['TabOrdering']) - 1 ) {
											if ($encounterTypeId == 1) {
												$this->redirect(array('action' => 'encounter_tabs'));
											} else {
												$this->redirect(array('action' => 'encounter_tabs', 'encounter_type_id' => $encounterTypeId));
											}
										}
									} else {
										$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
									}
								}
							}
						}
					} break;
			}

			$PracticeEncounterTab = $this->PracticeEncounterTab->getEncounterTypeTabs($encounterTypeId);
			
			if (!$PracticeEncounterTab) {
				$this->redirect(array('action' => 'encounter_tabs'));
			}
			
			$this->set("PracticeEncounterTab", $PracticeEncounterTab);
		}
	}

    function lab_facilities()
    {
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$this->DirectoryLabFacility->create();
	
							$this->data['DirectoryLabFacility']['practice_id'] = $user['user_id'];
							$this->data['DirectoryLabFacility']['user_id'] = $user['user_id'];
							$this->data['DirectoryLabFacility']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['DirectoryLabFacility']['modified_user_id'] = $user['user_id'];
	
							if ($this->DirectoryLabFacility->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) added.', true));
								$this->redirect(array('action' => 'lab_facilities'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
							}
						}
					}
					
                    $this->set('StateCodes', $this->sanitizeHTML($this->StateCode->getList()));
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							$this->data['DirectoryLabFacility']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['DirectoryLabFacility']['modified_user_id'] = $user['user_id'];
	
							if ($this->DirectoryLabFacility->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) saved.', true));
								$this->redirect(array('action' => 'lab_facilities'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
							}
						}
                    }
                    else
                    {
                        $lab_facilities_id = (isset($this->params['named']['lab_facilities_id'])) ? $this->params['named']['lab_facilities_id'] : "";
                        $items = $this->DirectoryLabFacility->find(
                            'first', array(
                            'conditions' => array('DirectoryLabFacility.lab_facilities_id' => $lab_facilities_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                        $this->set('StateCodes', $this->sanitizeHTML($this->StateCode->getList()));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$lab_facilities_id = $this->data['DirectoryLabFacility']['lab_facilities_id'];
							$delete_count = 0;
	
							foreach ($lab_facilities_id as $lab_facilities_id)
							{
								$this->DirectoryLabFacility->delete($lab_facilities_id, false);
								$delete_count++;
							}
	
							if ($delete_count > 0)
							{
								$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
							}
						}
					}
					
                    $this->redirect(array('action' => 'lab_facilities'));
                } break;
            default:
                {
                    $this->set('DirectoryLabFacilities', $this->sanitizeHTML($this->paginate('DirectoryLabFacility')));
                } break;
        }
    }

    public function pharmacies()
    {
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$this->DirectoryPharmacy->create();
	
							$this->data['DirectoryPharmacy']['practice_id'] = $user['user_id'];
							$this->data['DirectoryPharmacy']['user_id'] = $user['user_id'];
							$this->data['DirectoryPharmacy']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['DirectoryPharmacy']['modified_user_id'] = $user['user_id'];
	
							if ($this->DirectoryPharmacy->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) added.', true));
								$this->redirect(array('action' => 'pharmacies'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
							}
						}
					}
					
                    $this->set('StateCodes', $this->sanitizeHTML($this->StateCode->getList()));
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							$this->data['DirectoryPharmacy']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['DirectoryPharmacy']['modified_user_id'] = $user['user_id'];
	
							if ($this->DirectoryPharmacy->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) saved.', true));
								$this->redirect(array('action' => 'pharmacies'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
							}
						}
                    }
                    else
                    {
                        $pharmacies_id = (isset($this->params['named']['pharmacies_id'])) ? $this->params['named']['pharmacies_id'] : "";
                        $items = $this->DirectoryPharmacy->find(
                            'first', array(
                            'conditions' => array('DirectoryPharmacy.pharmacies_id' => $pharmacies_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                        $this->set('StateCodes', $this->sanitizeHTML($this->StateCode->getList()));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$pharmacies_id = $this->data['DirectoryPharmacy']['pharmacies_id'];
							$delete_count = 0;
	
							foreach ($pharmacies_id as $pharmacies_id)
							{
								$this->DirectoryPharmacy->delete($pharmacies_id, false);
								$delete_count++;
							}
	
							if ($delete_count > 0)
							{
								$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
							}
						}
					}
					
                    $this->redirect(array('action' => 'pharmacies'));
                } break;
            default:
                {
                    $this->set('DirectoryPharmacies', $this->sanitizeHTML($this->paginate('DirectoryPharmacy')));
                } break;
        }
    }

    public function referral_list()
    {
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$this->DirectoryReferralList->create();
	
							$this->data['DirectoryReferralList']['practice_id'] = $user['user_id'];
							$this->data['DirectoryReferralList']['user_id'] = $user['user_id'];
							$this->data['DirectoryReferralList']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['DirectoryReferralList']['modified_user_id'] = $user['user_id'];
	
							if ($this->DirectoryReferralList->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) added.', true));
								$this->redirect(array('action' => 'referral_list'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
							}
						}
					}
					
                    $this->set('StateCodes', $this->sanitizeHTML($this->StateCode->getList()));
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							$this->data['DirectoryReferralList']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['DirectoryReferralList']['modified_user_id'] = $user['user_id'];
	
							if ($this->DirectoryReferralList->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) saved.', true));
								$this->redirect(array('action' => 'referral_list'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
							}
						}
                    }
                    else
                    {
                        $referral_list_id = (isset($this->params['named']['referral_list_id'])) ? $this->params['named']['referral_list_id'] : "";
                        $items = $this->DirectoryReferralList->find(
                            'first', array(
                            'conditions' => array('DirectoryReferralList.referral_list_id' => $referral_list_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                        $this->set('StateCodes', $this->sanitizeHTML($this->StateCode->getList()));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$referral_list_id = $this->data['DirectoryReferralList']['referral_list_id'];
							$delete_count = 0;
	
							foreach ($referral_list_id as $referral_list_id)
							{
								$this->DirectoryReferralList->delete($referral_list_id, false);
								$delete_count++;
							}
	
							if ($delete_count > 0)
							{
								$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
							}
						}
					}
					
                    $this->redirect(array('action' => 'referral_list'));
                } break;
            default:
                {
                    $this->set('DirectoryReferralLists', $this->sanitizeHTML($this->paginate('DirectoryReferralList')));
                } break;
        }
    }

    public function insurance_companies()
    {
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$this->DirectoryInsuranceCompany->create();
							$this->data['DirectoryInsuranceCompany']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['DirectoryInsuranceCompany']['modified_user_id'] = $user['user_id'];
	
							if ($this->DirectoryInsuranceCompany->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) added.', true));
								$this->redirect(array('action' => 'insurance_companies'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
							}
						}
					}
					
                    $this->set('StateCodes', $this->sanitizeHTML($this->StateCode->getList()));
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							$this->data['DirectoryInsuranceCompany']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['DirectoryInsuranceCompany']['modified_user_id'] = $user['user_id'];
	
							if ($this->DirectoryInsuranceCompany->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) saved.', true));
								$this->redirect(array('action' => 'insurance_companies'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
							}
						}
                    }
                    else
                    {
                        $insurance_company_id = (isset($this->params['named']['insurance_company_id'])) ? $this->params['named']['insurance_company_id'] : "";
                        $items = $this->DirectoryInsuranceCompany->find(
                            'first', array(
                            'conditions' => array('DirectoryInsuranceCompany.insurance_company_id' => $insurance_company_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                        $this->set('StateCodes', $this->sanitizeHTML($this->StateCode->getList()));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$insurance_company_id = $this->data['DirectoryInsuranceCompany']['insurance_company_id'];
							$delete_count = 0;
	
							foreach ($insurance_company_id as $insurance_company_id)
							{
								$this->DirectoryInsuranceCompany->delete($insurance_company_id, false);
								$delete_count++;
							}
	
							if ($delete_count > 0)
							{
								$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
							}
						}
					}
					
                    $this->redirect(array('action' => 'insurance_companies'));
                } break;
            default:
                {
                    $this->set('DirectoryInsuranceCompanies', $this->sanitizeHTML($this->paginate('DirectoryInsuranceCompany')));
                } break;
        }
    }

    public function save_new_password()
    {
        $form = data::object($this->params['form']);
        $password = null;

        if ($form->_generate)
        {
            $password = $this->Session->read('password');
        }
        elseif (!$form->new_password)
        {
            $response['error'] = "Please enter  a password";
        }
        elseif ($form->new_password != $form->new_password_repeat)
        {
            $response['error'] = "Password does not match.";
        }
        else
        {
            $password = $form->new_password;
        }


        if ($password)
        {
            $password = $form->new_password;
            $key = $this->Session->read('key');
            $new_password = data::rc4Decrypt($key, data::hexDecode($password));

            if (strlen($new_password) < 6)
            {
                $response['error'] = "Password length must be at least 6 characters.";
            }
            else
            {
                $user = $this->Session->read('UserAccount');
                $this->UserAccount->changePassword($user['user_id'], NULL, $new_password, $this);

				$url = Router::url(array('controller' => 'dashboard', 'action' => 'index'), true);

				//$response['success'] = "Password Changed";
				$response['url'] = $url;

				// Remove cached other session info
				Cache::delete('user_' . $user['user_id']);
				
				//$this->Session->destroy();
				$this->Session->setFlash('You\'ve successfully updated your password.');
				//$this->redirect(array('controller' => 'dashboard', 'action' => 'index'));
            }
        }

        exit(json_encode($response));
    }

    public function generate_password()
    {
        App::import('Lib', 'data');

        $password = data::generatePassword(10);

        $this->Session->write('password', $password);

        $response['new_password'] = $password;
        exit(json_encode($response));
    }

    public function expired_password()
    {
        $this->layout = "default_empty";

        App::import('Lib', 'site');
        App::import('Lib', 'data');

        if (!site::setting('password_expires'))
        {
            //The expiration time is not set,
            //set expiration time
            access::setExpirePasswordTime(); //90 days

            $this->redirect(array('controller' => 'dashboard', 'action' => 'index'));
            exit();
        }
        $key = data::generatePassword(5);

        $this->Session->write('key', $key);

        $this->set('key', $key);
    }

    public function login()
    {
        $this->layout = "login";
        $this->set('title_for_layout', __('Log in', true));
        $ua = $this->browser_check();

        $cookie_enabled = true;
        setcookie('cookie_test', '1', strtotime('+1 hour'));
        if (!count($_COOKIE)){
            $cookie_enabled = false;
        }
        
        $this->set('cookie_enabled', $cookie_enabled);
        
        if (!empty($ua['notsupported']))
        {
        	$this->Session->setFlash('WARNING: Your Browser Version [' . $ua['version'] . '] is not supported by our system. Please upgrade ' . $ua['name'] . ' ' . $ua['extramessage'], 'default', array('class' => 'error'));
        }
        $this->loadModel('PracticeProfile');
	$practice_profile = $this->PracticeProfile->find('first');
	$this->set('practice_name', $practice_profile['PracticeProfile']['practice_name']);
	$this->set('practice_logo', $practice_profile['PracticeProfile']['logo_image']);
			
        if (!empty($this->data))
        {
            $data = (isset($this->data['UserAccount']) ? $this->data['UserAccount'] : array());

            $tries = $this->Session->read('tries');

            $user = $this->UserAccount->getUserByUsername($data['username']);

            //clear_status_flag - clears previous tries so that the user may login, after admin has reactivated the account.
            if ($user && $user->clear_status_flag)
            {
                $data = array();
                $data['user_id'] = $user->user_id;
                $data['clear_status_flag'] = 0;
                $data['account_disabled_reason'] = null;

                $this->UserAccount->save($data);

                $tries = 0;
                $this->Session->write('tries', 1);
            }
            else
            {

                if ($user && !$user->status)
                {
                    $this->Session->setFlash('We\'re sorry. This account has been disabled for security reasons.', 'default', array('class' => 'error'));
                    $this->redirect('login');
                    exit();
                }

                if ($tries && $tries > 10)
                {
                    $this->set('url', $this->Session->host);

                    EMR_Security::tooManyTries($data);

                    $this->Session->setFlash('Sorry, we could not log you in, too many tries.', 'default', array('class' => 'error'));
                    $this->redirect('login');
                    exit();
                }
            }
            
            $user = $this->UserAccount->validateLogin($this->data['UserAccount']);
/*
			if ($user['role_id'] != 10)
			{
				$PracticeSetting = $this->Session->read("PracticeSetting");
				$this->loadModel("User");
				$user_data = $this->User->find(
					'count', 
					array(
						'conditions' => array('AND' => array('User.customer' => $PracticeSetting['PracticeSetting']['practice_id'], 'User.status' => 'ACTIVE'))
					)
				);
				if ($user_data == 0)
				{
					unset($user);
				}
			}
*/
            if ($user)
            {
                $this->Session->write('tries', 0);
                $this->Session->write('UserAccount', $user);
                $this->current_user = $user;
                if ($user['last_login'] && !$user['password_last_update'])
                {
                    $this->redirect(array('controller' => 'administration', 'action' => 'expired_password'));
                }
                else
                {
                    if ($user['password_last_update'] && (time() > $user['password_last_update']) && $user['last_login'])
                    {
                        $this->redirect(array('controller' => 'administration', 'action' => 'expired_password'));
                    }
                    else
                    {
                        $data = array();

                        if (!$user['last_login'])
                        {
                            //if this is  the first login, then make the password expire in 3 months.
                            $data['password_last_update'] = time() + (3600 * 24 * 90);
                        }

                        $data['user_id'] = $user['user_id'];
                        $data['last_login'] = time();

                        $this->UserAccount->save($data);
                        
						$user_location_data = array();
						$user_location_data['user_id'] = $user['user_id'];
						$user_location_data['ip_address'] = $_SERVER['REMOTE_ADDR'];
						$user_location_data['login_timestamp'] = __date("Y-m-d H:i:s");
						$this->UserLocation->create();
						$this->UserLocation->save($user_location_data);

						// Notes session id for current user
						$sid = $this->Session->id();
	
						// Read any existing info on this user, if any
						$info = Cache::read('user_' . $user['user_id']);
	
						// No info, create one for this user
						if (!$info) {
							$info = array(
								'sid' => array(),
								'kick' => array(),
							);
	
						} 
	
						// Note the current sid
						$info['sid'][$sid] = true;
	
						// Save info
						Cache::write('user_' . $user['user_id'], $info);
						
			$autoLogout=(isset($_COOKIE['autoLogout']))? $_COOKIE['autoLogout']:'';	
			//make sure matches user that logged in last time
			@list($last_user_id,$last_url)=explode('|',$autoLogout);							
			//check to see if auto-logout cookie exists (were logged out due to being Idle, redirect them back to last screen)
			if ($autoLogout && ($this->Session->read('UserAccount.user_id') == $last_user_id))
			{
				setcookie("autoLogout","-",time()-100, "/", $_SERVER['SERVER_NAME']);
			        $this->redirect($last_url);
				
			}
			else
			{
                           	$this->redirect(array('controller' => 'dashboard', 'action' => 'index'));
                        }  	
                    }
                }
                
                exit();
            }
            else
            {
                $this->Session->write('tries', $tries + 1);

                $this->Session->setFlash('Sorry, the information you\'ve entered is incorrect.', 'default', array('class' => 'error'));

                $this->redirect('login');
            }
        }
    }

    public function in_house_work_labs()
    {
        $this->loadModel("AdministrationPointOfCare");
				$this->loadModel("AdministrationPointOfCareCategory");
				$this->set('categories', $this->AdministrationPointOfCareCategory->getCategories('Labs'));
				
		$this->AdministrationPointOfCare->process_csv_upload($this);
		
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task)
        {
            case "addnew":
			{
				if($this->getAccessType() == "W")
				{
					if (!empty($this->data))
					{
						$this->data['AdministrationPointOfCare']['ordered_by_id'] = $user['user_id'];
						$this->data['AdministrationPointOfCare']['lab_panels'] = '';
						
						if (isset($this->data['AdministrationPointOfCare']['category'])) {
							$this->AdministrationPointOfCareCategory->saveCategory('Labs', $this->data['AdministrationPointOfCare']['category']);
						}
						
						if ($this->data['AdministrationPointOfCare']['lab_test_type'] === 'Panel') {
							
							$post_lab_panel = $this->params['form']['lab_panel'];
							$fields = $post_lab_panel['field'];
							$values = $post_lab_panel['value'];
							
							$total = count($fields);
							
							$lab_panels = array();
							
							for ($ct = 0; $ct < $total; $ct++) {
								$field = trim($fields[$ct]);
								$value = trim($values[$ct]);
								
								if ($field === '') {
									continue;
								}
								
								$lab_panels[] = array(
									'field' => $field,
									'value' => $value,
								);
							}
							
							if (!empty($lab_panels)) {
								$this->data['AdministrationPointOfCare']['lab_panels'] = json_encode($lab_panels);
							}
						} 
											
						// only run once if match
						if(!ClassRegistry::init('Cpt4')->updateCitationCount('cpt_code',$this->data['AdministrationPointOfCare']['cpt_code']))
						{
						   ClassRegistry::init('Cpt4')->updateCitationCount('cpt',$this->data['AdministrationPointOfCare']['cpt']);
						}
							
											
						$this->AdministrationPointOfCare->create();
						$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
						$this->AdministrationPointOfCare->save($this->data);
						$this->Session->setFlash(__('Item(s) added.', true));
						$this->redirect(array('action' => 'in_house_work_labs'));
	
	
						$ret = array();
						echo json_encode($ret);
						
						exit;
						
					}
				}
			} break;
            case "edit":
			{
				if (!empty($this->data))
				{
					if($this->getAccessType() == "W")
					{
						$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
						$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
						if (isset($this->data['AdministrationPointOfCare']['category'])) {
							$this->AdministrationPointOfCareCategory->saveCategory('Labs', $this->data['AdministrationPointOfCare']['category']);
						}
											
						$this->data['AdministrationPointOfCare']['lab_panels'] = '';
						
						if ($this->data['AdministrationPointOfCare']['lab_test_type'] === 'Panel') {
							
							$post_lab_panel = $this->params['form']['lab_panel'];
							$fields = $post_lab_panel['field'];
							$values = $post_lab_panel['value'];
							
							$total = count($fields);
							
							$lab_panels = array();
							
							for ($ct = 0; $ct < $total; $ct++) {
								$field = trim($fields[$ct]);
								$value = trim($values[$ct]);
								
								if ($field === '') {
									continue;
								}
								
								$lab_panels[] = array(
									'field' => $field,
									'value' => $value,
								);
							}
							
							if (!empty($lab_panels)) {
								$this->data['AdministrationPointOfCare']['lab_panels'] = json_encode($lab_panels);
							}
						} 
						// only run once if match
						if(!ClassRegistry::init('Cpt4')->updateCitationCount('cpt_code',$this->data['AdministrationPointOfCare']['cpt_code']))
						{
						   ClassRegistry::init('Cpt4')->updateCitationCount('cpt',$this->data['AdministrationPointOfCare']['cpt']);
						}
											
						$this->AdministrationPointOfCare->save($this->data);
						$this->Session->setFlash(__('Item(s) saved.', true));
						$this->redirect(array('action' => 'in_house_work_labs'));
						$ret = array();
						echo json_encode($ret);
						exit;
					}
				}
				else
				{
					$point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
					$items = $this->AdministrationPointOfCare->find(
						'first', array(
						'conditions' => array('AdministrationPointOfCare.point_of_care_id' => $point_of_care_id)
						)
					);

					$this->set('EditItem', $this->sanitizeHTML($items));
                                        
                                        // We also want raw, unsanitized since wwe have json data
                                        $this->set('rawData', $items);
				}
			} break;
            case "delete":
			{
				$ret = array();
				$ret['delete_count'] = 0;

				if (!empty($this->data))
				{
					$ids = $this->data['AdministrationPointOfCare']['point_of_care_id'];

					foreach ($ids as $id)
					{
						$this->AdministrationPointOfCare->delete($id, false);
						$ret['delete_count']++;
					}
				}

				$this->redirect(array('action' => 'in_house_work_labs'));
			}break;
            default:
			{
				$this->set('AdministrationPointOfCare', $this->sanitizeHTML($this->paginate('AdministrationPointOfCare', array('order_type' => 'Labs'))));
			}
        }
    }

    public function in_house_work_radiology()
    {
        //$this->layout = "blank";
        $this->loadModel("AdministrationPointOfCare");
				$this->loadModel("AdministrationPointOfCareCategory");
				$this->set('categories', $this->AdministrationPointOfCareCategory->getCategories('Radiology'));
				
		$this->AdministrationPointOfCare->process_csv_upload($this);
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task)
        {
            case "upload_file":
                {
                    if (!empty($_FILES))
                    {
                        $tempFile = $_FILES['file_upload']['tmp_name'];
                        $targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
                        $targetFile = str_replace('//', '/', $targetPath) . $_FILES['file_upload']['name'];

                        move_uploaded_file($tempFile, $targetFile);
                        echo str_replace($_SERVER['DOCUMENT_ROOT'], '', $targetFile);
                    }

                    exit;
                } break;
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
								if (isset($this->data['AdministrationPointOfCare']['category'])) {
									$this->AdministrationPointOfCareCategory->saveCategory('Radiology', $this->data['AdministrationPointOfCare']['category']);
								}
							
							$this->AdministrationPointOfCare->create();
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) added.', true));
							$this->redirect(array('action' => 'in_house_work_radiology'));
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
					}
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
								if (isset($this->data['AdministrationPointOfCare']['category'])) {
									$this->AdministrationPointOfCareCategory->saveCategory('Radiology', $this->data['AdministrationPointOfCare']['category']);
								}
							
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) saved.', true));
							$this->redirect(array('action' => 'in_house_work_radiology'));
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
                    }
                    else
                    {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->AdministrationPointOfCare->find(
                            'first', array(
                            'conditions' => array('AdministrationPointOfCare.point_of_care_id' => $point_of_care_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						$ret = array();
						$ret['delete_count'] = 0;
	
						if (!empty($this->data))
						{
							$ids = $this->data['AdministrationPointOfCare']['point_of_care_id'];
	
							foreach ($ids as $id)
							{
								$this->AdministrationPointOfCare->delete($id, false);
								$ret['delete_count']++;
							}
						}
					}

                    $this->redirect(array('action' => 'in_house_work_radiology'));
                }break;
            default:
                {
                    $this->set('AdministrationPointOfCare', $this->sanitizeHTML($this->paginate('AdministrationPointOfCare', array('order_type' => 'Radiology'))));
                } break;
        }
    }

    public function in_house_work_procedures()
    {
        // $this->layout = "blank";
        $this->loadModel("AdministrationPointOfCare");
				$this->loadModel("AdministrationPointOfCareCategory");
				$this->set('categories', $this->AdministrationPointOfCareCategory->getCategories('Procedure'));
				
		$this->AdministrationPointOfCare->process_csv_upload($this);
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task)
        {
            case "addnew":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							
								if (isset($this->data['AdministrationPointOfCare']['category'])) {
									$this->AdministrationPointOfCareCategory->saveCategory('Procedure', $this->data['AdministrationPointOfCare']['category']);
								}
							
							
							if (empty($this->data['AdministrationPointOfCare']['procedure_unit']))
							{
								$this->data['AdministrationPointOfCare']['procedure_unit'] = 1;//must be at least 1
							}
							$this->data['AdministrationPointOfCare']['encounter_id'] = 0;
							$this->AdministrationPointOfCare->create();
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$success = $this->AdministrationPointOfCare->save($this->data);
              
              if ($success) {
                $this->Session->setFlash(__('Item(s) added.', true));
              } else {
                $this->Session->setFlash(__('Error saving procedure.', true));
              }
              
							$this->redirect(array('action' => 'in_house_work_procedures'));
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
                    }
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
								if (isset($this->data['AdministrationPointOfCare']['category'])) {
									$this->AdministrationPointOfCareCategory->saveCategory('Procedure', $this->data['AdministrationPointOfCare']['category']);
								}
							
							
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) saved.', true));
							$this->redirect(array('action' => 'in_house_work_procedures'));
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
                    }
                    else
                    {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $this->AdministrationPointOfCare->virtualFields['poc_form'] = sprintf("UNCOMPRESS(%s.poc_form)", $this->AdministrationPointOfCare->alias);
                        $items = $this->AdministrationPointOfCare->find(
                            'first', array(
                            'conditions' => array('AdministrationPointOfCare.point_of_care_id' => $point_of_care_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						$ret = array();
						$ret['delete_count'] = 0;
	
						if (!empty($this->data))
						{
							$ids = $this->data['AdministrationPointOfCare']['point_of_care_id'];
	
							foreach ($ids as $id)
							{
								$this->AdministrationPointOfCare->delete($id, false);
								$ret['delete_count']++;
							}
						}
					}

                    $this->redirect(array('action' => 'in_house_work_procedures'));
                }break;
            default:
                {
                    $this->set('AdministrationPointOfCare', $this->sanitizeHTML($this->paginate('AdministrationPointOfCare', array('order_type' => 'Procedure'))));
                } break;
        }
    }

    public function in_house_work_immunizations()
    {
        //$this->layout = "blank";
        $this->loadModel("AdministrationPointOfCare");
				$this->loadModel("AdministrationPointOfCareCategory");
				$this->set('categories', $this->AdministrationPointOfCareCategory->getCategories('Immunization'));
				
		$this->AdministrationPointOfCare->process_csv_upload($this);
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							
							if (isset($this->data['AdministrationPointOfCare']['category'])) {
								$this->AdministrationPointOfCareCategory->saveCategory('Immunization', $this->data['AdministrationPointOfCare']['category']);
							}
							
							
							$this->AdministrationPointOfCare->create();
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) added.', true));
							$this->redirect(array('action' => 'in_house_work_immunizations'));
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
					}
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							
							if (isset($this->data['AdministrationPointOfCare']['category'])) {
								$this->AdministrationPointOfCareCategory->saveCategory('Immunization', $this->data['AdministrationPointOfCare']['category']);
							}
							
							
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) saved.', true));
							$this->redirect(array('action' => 'in_house_work_immunizations'));
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
                    }
                    else
                    {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->AdministrationPointOfCare->find(
                            'first', array(
                            'conditions' => array('AdministrationPointOfCare.point_of_care_id' => $point_of_care_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						$ret = array();
						$ret['delete_count'] = 0;
	
						if (!empty($this->data))
						{
							$ids = $this->data['AdministrationPointOfCare']['point_of_care_id'];
	
							foreach ($ids as $id)
							{
								$this->AdministrationPointOfCare->delete($id, false);
								$ret['delete_count']++;
							}
						}
					}

                    $this->redirect(array('action' => 'in_house_work_immunizations'));
                }break;
            case "mark_none":
                {
                    if ($this->data['AdministrationPointOfCare']['mark_none'] == "true")
                    {
                        $this->data['AdministrationPointOfCare']['encounter_id'] = $this->params['named']['encounter_id'];
                        $this->data['AdministrationPointOfCare']['order_type'] = "Marked as None";
                        $this->data['AdministrationPointOfCare']['immunization_none'] = $user['user_id'];
                        $this->AdministrationPointOfCare->create();
                        $this->AdministrationPointOfCare->save($this->data);
                    }
                    else
                    {
                        $this->AdministrationPointOfCare->deleteAll(array("AND" => array("order_type" => "Marked as None", "immunization_none" => $user['user_id'])), false);
                    }
                } break;
            /* case "review_by":
              {
              if ($this->data['AdministrationPointOfCare']['review_by'] == "true")
              {
              $this->data['AdministrationPointOfCare']['encounter_id'] = $this->params['named']['encounter_id'];
              $this->data['AdministrationPointOfCare']['order_type'] = "Reviewed by";
              $this->data['AdministrationPointOfCare']['immunization_reviewed'] = $user['user_id'];
              $this->data['AdministrationPointOfCare']['immunization_reviewed_by'] = $user['firstname'].' '.$user['lastname'];
              $this->data['AdministrationPointOfCare']['immunization_reviewed_time'] = __date("Y-m-d, H:i:s");
              $this->AdministrationPointOfCare->create();
              $this->AdministrationPointOfCare->save($this->data);
              }
              else
              {
              $this->AdministrationPointOfCare->deleteAll(array("AND" => array("AdministrationPointOfCare.encounter_id" => $this->params['named']['encounter_id'], "order_type" => "Reviewed by", "immunization_reviewed" => $user['user_id'])), false);
              }
              } break; */
            default:
                {
                    $this->set('AdministrationPointOfCare', $this->sanitizeHTML($this->paginate('AdministrationPointOfCare', array('order_type' => 'Immunization'))));

                    /* $mark_none = $this->AdministrationPointOfCare->find(
                      'first',
                      array(
                      'conditions' => array('AND' => array('AdministrationPointOfCare.encounter_id' => $this->data['AdministrationPointOfCare']['encounter_id'], 'order_type' => 'Marked as None', 'immunization_none' => $user['user_id']))
                      )
                      );
                      if (count($mark_none))
                      {
                      $this->set('MarkedNone', $this->sanitizeHTML($mark_none));
                      }

                      $review_by = $this->AdministrationPointOfCare->find(
                      'first',
                      array(
                      'conditions' => array('AND' => array('AdministrationPointOfCare.encounter_id' => $this->data['AdministrationPointOfCare']['encounter_id'], 'order_type' => 'Reviewed by', 'immunization_reviewed' => $user['user_id']))
                      )
                      );
                      if (count($review_by))
                      {
                      $this->set('ReviewedBy', $this->sanitizeHTML($review_by));
                      } */
                } break;
        }
    }

    public function in_house_work_injections()
    {
        //$this->layout = "blank";
        $this->loadModel("AdministrationPointOfCare");
				$this->loadModel("AdministrationPointOfCareCategory");
				$this->set('categories', $this->AdministrationPointOfCareCategory->getCategories('Injection'));
				
		$this->AdministrationPointOfCare->process_csv_upload($this);
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							if (isset($this->data['AdministrationPointOfCare']['category'])) {
								$this->AdministrationPointOfCareCategory->saveCategory('Injection', $this->data['AdministrationPointOfCare']['category']);
							}
							
							//$this->data['AdministrationPointOfCare']['encounter_id'] = 0;
							$this->data['AdministrationPointOfCare']['ordered_by_id'] = $user['user_id'];
							//$this->data['AdministrationPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['date_ordered']));
							$this->AdministrationPointOfCare->create();
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) added.', true));
							$this->redirect(array('action' => 'in_house_work_injections'));
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
					}
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							
							if (isset($this->data['AdministrationPointOfCare']['category'])) {
								$this->AdministrationPointOfCareCategory->saveCategory('Injection', $this->data['AdministrationPointOfCare']['category']);
							}
							
							
							//$this->data['AdministrationPointOfCare']['injection_date_performed'] = __date("Y-m-d", strtotime($this->data['AdministrationPointOfCare']['injection_date_performed']));
							//$this->data['AdministrationPointOfCare']['injection_expiration_date'] = __date("Y-m-d", strtotime($this->data['AdministrationPointOfCare']['injection_expiration_date']));
							//$this->data['AdministrationPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($this->data['AdministrationPointOfCare']['date_ordered']));
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) saved.', true));
							$this->redirect(array('action' => 'in_house_work_injections'));
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
                    }
                    else
                    {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->AdministrationPointOfCare->find(
                            'first', array(
                            'conditions' => array('AdministrationPointOfCare.point_of_care_id' => $point_of_care_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						$ret = array();
						$ret['delete_count'] = 0;
	
						if (!empty($this->data))
						{
							$ids = $this->data['AdministrationPointOfCare']['point_of_care_id'];
	
							foreach ($ids as $id)
							{
								$this->AdministrationPointOfCare->delete($id, false);
								$ret['delete_count']++;
							}
						}
					}

                    $this->redirect(array('action' => 'in_house_work_injections'));
                } break;
            /* case "mark_none":
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
              break; */

            /* case "review_by":
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
              } break; */
            default:
                {
                    $this->set('AdministrationPointOfCare', $this->sanitizeHTML($this->paginate('AdministrationPointOfCare', array('order_type' => 'Injection'))));

                    /* $mark_none = $this->EncounterPointOfCare->find(
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
                      } */
                } break;
        }
    }

    public function in_house_work_meds()
    {
        //$this->layout = "blank";
        $this->loadModel("AdministrationPointOfCare");
				$this->loadModel("AdministrationPointOfCareCategory");
				$this->set('categories', $this->AdministrationPointOfCareCategory->getCategories('Meds'));
				
		$this->AdministrationPointOfCare->process_csv_upload($this);
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
				$this->loadModel('Unit');
				$this->set("units", $this->Unit->find('all'));
				
        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							
							if (isset($this->data['AdministrationPointOfCare']['category'])) {
								$this->AdministrationPointOfCareCategory->saveCategory('Meds', $this->data['AdministrationPointOfCare']['category']);
							}
							
							//$this->data['AdministrationPointOfCare']['drug_date_given'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['drug_date_given']));
							//$this->data['AdministrationPointOfCare']['injection_expiration_date'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['injection_expiration_date']));
							$this->data['AdministrationPointOfCare']['ordered_by_id'] = $user['user_id'];
						   //$this->data['AdministrationPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['date_ordered']));
							$this->AdministrationPointOfCare->create();
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) added.', true));
							$this->redirect(array('action' => 'in_house_work_meds'));
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
					}
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							if (isset($this->data['AdministrationPointOfCare']['category'])) {
								$this->AdministrationPointOfCareCategory->saveCategory('Meds', $this->data['AdministrationPointOfCare']['category']);
							}
							
							
							//$this->data['AdministrationPointOfCare']['drug_date_given'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['drug_date_given']));
							//$this->data['AdministrationPointOfCare']['injection_expiration_date'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['injection_expiration_date']));
							//$this->data['AdministrationPointOfCare']['date_ordered'] = __date("Y-m-d", strtotime($this->data['EncounterPointOfCare']['date_ordered']));
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) saved.', true));
							$this->redirect(array('action' => 'in_house_work_meds'));
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
                    }
                    else
                    {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->AdministrationPointOfCare->find(
                            'first', array(
                            'conditions' => array('AdministrationPointOfCare.point_of_care_id' => $point_of_care_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						$ret = array();
						$ret['delete_count'] = 0;
	
						if (!empty($this->data))
						{
							$ids = $this->data['AdministrationPointOfCare']['point_of_care_id'];
	
							foreach ($ids as $id)
							{
								$this->AdministrationPointOfCare->delete($id, false);
								$ret['delete_count']++;
							}
						}
					}

                    $this->redirect(array('action' => 'in_house_work_meds'));
                }break;
            default:
                {
                    $this->set('AdministrationPointOfCare', $this->sanitizeHTML($this->paginate('AdministrationPointOfCare', array('order_type' => 'Meds'))));
                } break;
        }
    }

    public function in_house_work_supplies()
    {
        //$this->layout = "blank";
        $this->loadModel("AdministrationPointOfCare");
				$this->loadModel("AdministrationPointOfCareCategory");
				$this->set('categories', $this->AdministrationPointOfCareCategory->getCategories('Supplies'));
				
		$this->AdministrationPointOfCare->process_csv_upload($this);
        $user = $this->Session->read('UserAccount');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
								if (isset($this->data['AdministrationPointOfCare']['category'])) {
									$this->AdministrationPointOfCareCategory->saveCategory('Supplies', $this->data['AdministrationPointOfCare']['category']);
								}
							
							$this->data['AdministrationPointOfCare']['ordered_by_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->create();
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) added.', true));
							$this->redirect(array('action' => 'in_house_work_supplies'));
	
	
							$ret = array();
							echo json_encode($ret);
							exit;
						}
					}
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
								if (isset($this->data['AdministrationPointOfCare']['category'])) {
									$this->AdministrationPointOfCareCategory->saveCategory('Supplies', $this->data['AdministrationPointOfCare']['category']);
								}
							
							$this->data['AdministrationPointOfCare']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['AdministrationPointOfCare']['modified_user_id'] = $user['user_id'];
							$this->AdministrationPointOfCare->save($this->data);
							$this->Session->setFlash(__('Item(s) saved.', true));
							$this->redirect(array('action' => 'in_house_work_supplies'));
							$ret = array();
							echo json_encode($ret);
							exit;
						}
                    }
                    else
                    {
                        $point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
                        $items = $this->AdministrationPointOfCare->find(
                            'first', array(
                            'conditions' => array('AdministrationPointOfCare.point_of_care_id' => $point_of_care_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));

                        $this->loadModel("EncounterPointOfCare");

                        $count = $this->EncounterPointOfCare->find(
                            'first', array(
                            'fields' => array('sum(EncounterPointOfCare.supply_quantity) as order_quantity'),
                            'conditions' => array('AND' => array('EncounterPointOfCare.supply_name' => $items['AdministrationPointOfCare']['supply_name'], 'EncounterPointOfCare.order_type' => 'Supplies'))
                            )
                        );

                        $this->set('order_quantity', $this->sanitizeHTML($count[0]['order_quantity']));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						$ret = array();
						$ret['delete_count'] = 0;
	
						if (!empty($this->data))
						{
							$ids = $this->data['AdministrationPointOfCare']['point_of_care_id'];
	
							foreach ($ids as $id)
							{
								$this->AdministrationPointOfCare->delete($id, false);
								$ret['delete_count']++;
							}
						}
					}

                    $this->redirect(array('action' => 'in_house_work_supplies'));
                }break;

            default:
                {
                    $this->set('AdministrationPointOfCare', $this->sanitizeHTML($this->paginate('AdministrationPointOfCare', array('order_type' => 'Supplies'))));
                } break;
        }
    }

    public function logout()
    {
        $session = isset($this->params['named']['session']) ? true : false;

        // Get current session id
        $sid = $this->Session->id();

        // Read current cached user info
        $info = Cache::read('user_' . $this->user_id);

        if ($info) {
            // If we are to retain current session
            if ($session) {
                $kick = array();

                foreach ($info['sid'] as $key => $value) {
                    if ($key != $sid) {
                        $kick[$key] = true;
                    }
                }

                // Note the sessions the should be kicked
                $info['kick'] = $kick;

                // Set info so that the only recognized session
                // is the current session
                $info['sid'] = array($sid => true);

                // Save the info
                Cache::write('user_' . $this->user_id, $info);

                $this->redirect(array('controller' => 'dashboard', 'action' => 'index'));
                exit();
            }

            // Unset current session id from list
            if (isset($info['sid'][$sid])) {
                unset($info['sid'][$sid]);
            }

            // If entire session id is list, 
            // this means no other sessions for this user exists,
            // so we remove the info cache
            if (empty($info['sid'])) {
                Cache::delete('user_' . $this->user_id);
            } else {
                // There are still other sessions for this user
                // so let's keep the cache
                Cache::write('user_' . $this->user_id, $info);
            }            
        } 
          
              
	if ($this -> Session -> valid())
	{
    	 $this -> Session -> destroy();
    	 $this->Session->destroy('user');
	}
	//were they logged out due to inactivity?
        $inactivity=(!empty($_COOKIE['autoLogout']))? 'You\'ve been logged out due to inactivity.':'You\'ve been successfully logged out.';
        $this->Session->setFlash($inactivity);
        $this->redirect('login');
    }

    function appointment_types()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $user = $this->Session->read('UserAccount');
				$this->loadModel('PracticeEncounterType');
				$encounterTypes = $this->PracticeEncounterType->find('all');
				$this->set(compact('encounterTypes'));
				
				
				$phoneCall = $this->ScheduleType->find('first', array(
					'conditions' => array(
						'ScheduleType.type' => 'Phone Call',
					),
				));
				
				
        switch ($task)
        {
            case "addnew":
			{
				if($this->getAccessType() == "W")
				{
					if (!empty($this->data))
					{
						$this->ScheduleType->create();
						
						$continueSave = true;
						if ($phoneCall && trim($this->data['ScheduleType']['type']) == 'Phone Call') {
							$continueSave = false;
						}
						
						if($this->data['ScheduleType']['appointment_type_duration']=="")
						{
							$this->data['ScheduleType']['appointment_type_duration'] = 15;
						}
						
						if ($continueSave && $this->ScheduleType->save($this->data))
						{
							$this->Session->setFlash(__('Item(s) added.', true));
							$this->redirect(array('action' => 'appointment_types'));
						}
						else
						{
							$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
						}
					}
				}
			} break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							extract($this->params['form']);
							
							$continueSave = true;
							if (	$phoneCall && 
										trim($this->data['ScheduleType']['type']) == 'Phone Call' && 
										intval($this->data['ScheduleType']['appointment_type_id']) !== intval($phoneCall['ScheduleType']['appointment_type_id']) ) {
								$continueSave = false;
							}							
							
							if (	$phoneCall && 
										trim($this->data['ScheduleType']['type']) !== 'Phone Call' && 
										intval($this->data['ScheduleType']['appointment_type_id']) == intval($phoneCall['ScheduleType']['appointment_type_id']) ) {
								$continueSave = false;
							}							
							
							if ($continueSave && $this->ScheduleType->save($this->data))
							{
								$this->Session->setFlash(__('Item(s) saved.', true));
								$this->redirect(array('action' => 'appointment_types'));
							}
							else
							{
								$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
								$this->redirect(array('action' => 'appointment_types', 'task' => 'edit', 'appointment_type_id' => $this->data['ScheduleType']['appointment_type_id']));
							}
						}
                    }
                    else
                    {
                        $id = (isset($this->params['named']['appointment_type_id'])) ? $this->params['named']['appointment_type_id'] : "";
                        $items = $this->ScheduleType->find(
                            'first', array(
                            'conditions' => array('ScheduleType.appointment_type_id' => $id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$ids = $this->data['ScheduleType']['appointment_type_id'];
							$delete_count = 0;
	
							foreach ($ids as $id)
							{
								
								if ($phoneCall && intval($phoneCall['ScheduleType']['appointment_type_id']) == intval($id)) {
									continue;
								}
								
								$this->ScheduleType->delete($id, false);
								$delete_count++;
							}
	
							if ($delete_count > 0)
							{
								$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
							}
						}
					}
					
                    $this->redirect(array('action' => 'appointment_types'));
                } break;
            default:
			{
				$this->set('ScheduleTypes', $this->sanitizeHTML($this->paginate('ScheduleType')));
			} break;
        }
    }
	
	function schedule_rooms()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$this->loadModel('ScheduleRoom');
        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$checkRoom = $this->ScheduleRoom->find('count', array(
								'conditions' => array('ScheduleRoom.room' => $this->data['ScheduleRoom']['room'])
							));
							if(empty($checkRoom)) {
								$this->ScheduleRoom->create();
								if ($this->ScheduleRoom->save($this->data)) {
									$this->Session->setFlash(__('Item(s) added.', true));
									$this->redirect(array('action' => 'schedule_rooms'));
								} else {
									$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
								}
							} else {
								$this->Session->setFlash('Schedule Room already exist', 'default', array('class' => 'error'));
							}
						}
					}
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							$checkRoom = $this->ScheduleRoom->find('count', array(
								'conditions' => array('ScheduleRoom.room' => $this->data['ScheduleRoom']['room'], 'ScheduleRoom.room_id !=' => $this->data['ScheduleRoom']['room_id'])
							));
							if(empty($checkRoom)) {
								if ($this->ScheduleRoom->save($this->data)) {
									$this->Session->setFlash(__('Item(s) saved.', true));
									$this->redirect(array('action' => 'schedule_rooms'));
								} else {
									$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
								}
							} else {
								$this->Session->setFlash('Schedule Room already exist', 'default', array('class' => 'error'));
							}
						}
                    }
                    else
                    {
                        $id = (isset($this->params['named']['room_id'])) ? $this->params['named']['room_id'] : "";
                        $this->data = $this->ScheduleRoom->find(
                            'first', array(
                            'conditions' => array('ScheduleRoom.room_id' => $id)
                            )
                        );
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$id = $this->data['ScheduleRoom']['room_id'];
							$delete_count = 0;
	
							foreach ($id as $id)
							{
								$this->ScheduleRoom->delete($id, false);
								$delete_count++;
							}
	
							if ($delete_count > 0)
							{
								$this->Session->setFlash($delete_count . __(' Item(s) deleted.', true));
							}
						}
					}
					
                    $this->redirect(array('action' => 'schedule_rooms'));
                } break;
            default:
                {
                    $this->set('ScheduleRooms', $this->sanitizeHTML($this->paginate('ScheduleRoom')));
                } break;
        }
    }
	
	function schedule_statuses()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$this->loadModel('ScheduleStatus');
        switch ($task)
        {
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$checkRoom = $this->ScheduleStatus->find('count', array(
								'conditions' => array('ScheduleStatus.status' => $this->data['ScheduleStatus']['status'])
							));
							if(empty($checkRoom)) {
								$this->ScheduleStatus->create();
								if ($this->ScheduleStatus->save($this->data)) {
									$this->Session->setFlash(__('Item(s) added.', true));
									$this->redirect(array('action' => 'schedule_statuses'));
								} else {
									$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
								}
							} else {
								$this->Session->setFlash('Schedule Status already exist', 'default', array('class' => 'error'));
							}
						}
					}
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
						if($this->getAccessType() == "W")
						{
							$checkStatus = $this->ScheduleStatus->find('count', array(
								'conditions' => array('ScheduleStatus.status' => $this->data['ScheduleStatus']['status'], 'ScheduleStatus.status_id !=' => $this->data['ScheduleStatus']['status_id'])
							));
							if(empty($checkStatus)) {
								if ($this->ScheduleStatus->save($this->data)) {
									$this->Session->setFlash(__('Item(s) saved.', true));
									$this->redirect(array('action' => 'schedule_statuses'));
								} else {
									$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
								}
							} else {
								$this->Session->setFlash('Schedule Status already exist', 'default', array('class' => 'error'));
							}
						}
                    }
                    else
                    {
                        $id = (isset($this->params['named']['status_id'])) ? $this->params['named']['status_id'] : "";
                        $this->data = $this->ScheduleStatus->find(
                            'first', array(
                            'conditions' => array('ScheduleStatus.status_id' => $id)
                            )
                        );
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						if (!empty($this->data))
						{
							$id = $this->data['ScheduleStatus']['status_id'];
							$delete_count = 0;
	
							foreach ($id as $id)
							{
								$this->ScheduleStatus->delete($id, false);
								$delete_count++;
							}
	
							if ($delete_count > 0)
							{
								$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
							}
						}
					}
					
                    $this->redirect(array('action' => 'schedule_statuses'));
                } break;
            default:
                {
                    $this->set('ScheduleStatuss', $this->sanitizeHTML($this->paginate('ScheduleStatus')));
                } break;
        }
    }

    function health_maintenance()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

        if ($task != "patient_load" and $task != "allergy_load" and $task != "history_load" and $task != "result_load")
        {
            $this->redirect(array('action' => 'health_maintenance_plans'));
        }

        $this->loadModel("PatientDemographic");

				
				$search_limit = '100';
				
				if (isset($this->data['autocomplete']['limit'])) {
					$search_limit = $this->data['autocomplete']['limit'];
				}
				
				if (!$search_limit) {
					$search_limit = '100';
				}
				
        switch ($task)
        {
            case "patient_load":
                {
                    if (!empty($this->data))
                    {
											$search_keyword = str_replace(',', ' ', trim($this->data['autocomplete']['keyword']));
											$search_keyword = preg_replace('/\s\s+/', ' ', $search_keyword);


											$search_limit = $this->data['autocomplete']['limit'];
											if (empty($search_limit))
												$search_limit = '100';

											$keywords = explode(' ', $search_keyword);
											$conditions = array();
											foreach($keywords as $word) {
												$conditions[] = array('OR' => 
														array(
															'PatientDemographic.first_name LIKE ' => $word . '%', 
															'PatientDemographic.last_name LIKE ' => $word . '%'
														)
												);
											}
                        $patient_items = $this->PatientDemographic->find('all', array(
													'conditions' => array(
														'AND' => $conditions,
														'CONVERT(DES_DECRYPT(PatientDemographic.status) USING latin1)' => array('active', 'new')
													),
							'limit' => $search_limit,
							'recursive' => -1
                            )
                        );
                        $data_array = array();

                        foreach ($patient_items as $patient_item)
                        {
                            $data_array[] = $patient_item['PatientDemographic']['first_name'] . ' ' . $patient_item['PatientDemographic']['last_name'] . '|' . $patient_item['PatientDemographic']['patient_id'] . '|' . __date($this->__global_date_format , strtotime($patient_item['PatientDemographic']['dob']));
                        }

                        echo implode("\n", $data_array);
                    }
                    exit();
                } break;
			case "allergy_load":
			{
				if (!empty($this->data))
				{
					$this->loadModel("PatientAllergy");

					$search_keyword = $this->data['autocomplete']['keyword'];

					$allegy_items = $this->PatientAllergy->find('all', array(
						'fields' => array('DISTINCT PatientAllergy.agent', 'PatientAllergy.allergy_id'),
						'conditions' => array('PatientAllergy.agent LIKE ' => '%' . $search_keyword . '%'),
						'order' => array('PatientAllergy.agent' => 'ASC'),
						'limit' => $search_limit
						)
					);
					$data_array = array();

					foreach ($allegy_items as $allegy_item)
					{
						$data_array[] = $allegy_item['PatientAllergy']['agent'] . '|' . $allegy_item['PatientAllergy']['allergy_id'];
					}

					echo implode("\n", $data_array);
				}
				exit();
			} break;
			case "history_load":
			{
				if (!empty($this->data))
				{
					$this->loadModel("PatientMedicalHistory");

					$search_keyword = $this->data['autocomplete']['keyword'];

					$history_items = $this->PatientMedicalHistory->find('all', array(
						'fields' => array('DISTINCT PatientMedicalHistory.diagnosis', 'PatientMedicalHistory.medical_history_id'),
						'conditions' => array('PatientMedicalHistory.diagnosis LIKE ' => '%' . $search_keyword . '%'),
						'order' => array('PatientMedicalHistory.diagnosis' => 'ASC'),
						'limit' => $search_limit
						)
					);
					$data_array = array();

					foreach ($history_items as $history_item)
					{
						$data_array[] = $history_item['PatientMedicalHistory']['diagnosis'] . '|' . $history_item['PatientMedicalHistory']['medical_history_id'];
					}

					echo implode("\n", $data_array);
				}
				exit();
			} break;
			case "result_load":
			{
				if (!empty($this->data))
				{
					$this->loadModel("EncounterPointOfCare");

					$search_keyword = $this->data['autocomplete']['keyword'];

					$result_items = $this->EncounterPointOfCare->find('all', array(
						'fields' => array('DISTINCT EncounterPointOfCare.lab_test_name', 'EncounterPointOfCare.point_of_care_id'),
						'conditions' => array('AND' => array('EncounterPointOfCare.lab_test_name LIKE ' => '%' . $search_keyword . '%', 'EncounterPointOfCare.order_type' => 'Labs')),
						'order' => array('EncounterPointOfCare.lab_test_name' => 'ASC'),
						'limit' => $search_limit
						)
					);
					$data_array = array();

					foreach ($result_items as $result_item)
					{
						$data_array[] = $result_item['EncounterPointOfCare']['lab_test_name'] . '|' . $result_item['EncounterPointOfCare']['point_of_care_id'];
					}

					echo implode("\n", $data_array);
				}
				exit();
			} break;
        }
    }

	function patient_portal_family()
        {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $family_favorite_id = (isset($this->params['named']['family_favorite_id'])) ? $this->params['named']['family_favorite_id'] : "";
	$this->loadModel("PatientPortalFamilyFavorite");
	if(!empty($this->data) && ($task == 'edit' || $task == 'addnew') ) {
	     if(isset($this->data['PatientPortalFamilyFavorite']['family_favorite_id']))
	     {
		$data['family_favorite_id']=$this->data['PatientPortalFamilyFavorite']['family_favorite_id'];
	     }
	     else
	     {
		 $this->PatientPortalFamilyFavorite->create();
	     }
		$data['family_favorite_question']=$this->data['PatientPortalFamilyFavorite']['family_favorite_question'];
		$data['family_favorite_problem']=$this->data['PatientPortalFamilyFavorite']['family_favorite_problem'];
                $this->PatientPortalFamilyFavorite->save($data);

		$this->redirect(array('controller' => 'administration', 'action' => 'patient_portal_family'));
	}
	  if($task == 'edit') {
		$item=$this->PatientPortalFamilyFavorite->find('first', array('conditions' => array('family_favorite_id' => $family_favorite_id)));
	  	$this->set('EditItem',$item);
	  } else if ($task == 'delete') {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientPortalFamilyFavorite']['family_favorite_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientPortalFamilyFavorite->delete($id, false);
                       $ret['delete_count']++;
                    }

                }
		$this->redirect(array('controller' => 'administration', 'action' => 'patient_portal_family'));

	  } else {
      
      $this->paginate['PatientPortalFamilyFavorite'] = array(
          'limit' => 10,
          'page' => 1,
      );
	    $this->set('FamilyFavorites',$this->paginate('PatientPortalFamilyFavorite'));
	  }

    }

    function patient_portal_social()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $social_favorite_id = (isset($this->params['named']['social_favorite_id'])) ? $this->params['named']['social_favorite_id'] : "";
	$this->loadModel("PatientPortalSocialFavorites");
	if(!empty($this->data) && ($task == 'edit' || $task == 'addnew') ) {
	     if(isset($this->data['PatientPortalSocialFavorites']['social_favorite_id']))
	     {
		$data['social_favorite_id']=$this->data['PatientPortalSocialFavorites']['social_favorite_id'];
	     }
	     else
	     {
		 $this->PatientPortalSocialFavorites->create();
	     }
		$subtype=($this->data['PatientPortalSocialFavorites']['substance'])?$this->data['PatientPortalSocialFavorites']['substance']:$this->data['PatientPortalSocialFavorites']['routine'];
		$data['social_favorite_type']=$this->data['PatientPortalSocialFavorites']['type'];
                $data['social_favorite_subtype']=$subtype;
                $data['social_favorite_question']=$this->data['PatientPortalSocialFavorites']['social_favorite_question'];
                $this->PatientPortalSocialFavorites->save($data);

		$this->redirect(array('controller' => 'administration', 'action' => 'patient_portal_social'));
	}
	  if($task == 'edit') {
		$item=$this->PatientPortalSocialFavorites->find('first', array('conditions' => array('social_favorite_id' => $social_favorite_id)));
	  	$this->set('EditItem',$item);
	  } else if ($task == 'delete') {
                $ret = array();
                $ret['delete_count'] = 0;

                if (!empty($this->data))
                {
                    $ids = $this->data['PatientPortalSocialFavorites']['social_favorite_id'];

                    foreach($ids as $id)
                    {
                        $this->PatientPortalSocialFavorites->delete($id, false);
                       $ret['delete_count']++;
                    }

                }
		$this->redirect(array('controller' => 'administration', 'action' => 'patient_portal_social'));

	  } else {
                $this->paginate['PatientPortalSocialFavorites'] = array(
                'limit' => 10,
                'page' => 1,
                );
                $this->set('SocialFavorites',$this->paginate('PatientPortalSocialFavorites'));

	  }

	$this->loadModel("MaritalStatus");
	$this->set("MaritalStatus", $this->sanitizeHTML($this->MaritalStatus->find('all')));
    }

    function health_maintenance_plans()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		if(!empty($this->data) && ($task == "addnew" || $task == "edit" || $task == "delete") && $this->getAccessType() != "W")
		{
			$task = "";
		}
		
		$this->HealthMaintenancePlan->execute($this, $task);
    }

    function clinical_alerts()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		if(!empty($this->data) && ($task == "addnew" || $task == "edit" || $task == "delete") && $this->getAccessType() != "W")
		{
			$task = "";
		}
		
        $this->ClinicalAlert->execute($this, $task);
    }

    function patient_portal()
    {
	if(!empty($this->data)) {
		$this->data['PracticeSetting']['setting_id']=1;
	 	if ($this->PracticeSetting->save($this->data))
		{
			$this->Session->setFlash(__('Item(s) saved.', true));
			$this->redirect(array('action' => 'patient_portal'));
		} else 	{
			$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
		}	
	}

	$this->set('settings',$this->PracticeSetting->getSettings());


    }

    function patient_portal_surgical()
    {
        $this->loadModel("PatientPortalSurgicalFavorite");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
				$userId = $this->Session->read('UserAccount.user_id');
				
        switch ($task)
        {
            case "addnew":
                {
                    if (!empty($this->data))
                    {
                        $this->PatientPortalSurgicalFavorite->create();
												$this->data['PatientPortalSurgicalFavorite']['user_id'] = $userId;
                        if ($this->PatientPortalSurgicalFavorite->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) added.', true));
                            $this->redirect(array('action' => 'patient_portal_surgical'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                        }
                    }
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
												$this->data['PatientPortalSurgicalFavorite']['user_id'] = $userId;
                        if ($this->PatientPortalSurgicalFavorite->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) saved.', true));
                            $this->redirect(array('action' => 'patient_portal_surgical'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $surgeries_id = (isset($this->params['named']['surgeries_id'])) ? $this->params['named']['surgeries_id'] : "";
                        $items = $this->PatientPortalSurgicalFavorite->find(
                            'first', array(
                            'conditions' => array(
															'PatientPortalSurgicalFavorite.surgeries_id' => $surgeries_id,
														//	'PatientPortalSurgicalFavorite.user_id' => $userId,
															)
                            )
                        );
												
												if (empty($items)) {
														$this->Session->setFlash('Favorite Surgery not found', 'default', array('class' => 'error'));
														$this->redirect(array('action' => 'patient_portal_surgical'));
														exit();
												}

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
                    if (!empty($this->data))
                    {
                        $surgeries_id = $this->data['PatientPortalSurgicalFavorite']['surgeries_id'];
                        $delete_count = 0;

												
												$diagnoses = $this->PatientPortalSurgicalFavorite->find('all', array(
													'conditions' => array(
														'PatientPortalSurgicalFavorite.surgeries_id' => $surgeries_id,
														//'PatientPortalSurgicalFavorite.user_id' => $userId,
													),
												));
												
                        foreach ($diagnoses as $d)
                        {
                            $this->PatientPortalSurgicalFavorite->delete($d['PatientPortalSurgicalFavorite']['surgeries_id'], false);
                            $delete_count++;
                        }

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                        }
                    }
                    $this->redirect(array('action' => 'patient_portal_surgical'));
                } break;
            default:
                {
                    $this->set('PatientPortalSurgicalFavorite', $this->sanitizeHTML($this->paginate('PatientPortalSurgicalFavorite')));
//, array(
//											'PatientPortalSurgicalFavorite.user_id' => $userId,
//										))));
                } break;
        }
    }

    function patient_portal_medical()
    {
        $this->loadModel("PatientPortalMedicalFavorite");
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
				$userId = $this->Session->read('UserAccount.user_id');
				
        switch ($task)
        {
            case "addnew":
                {
                    if (!empty($this->data))
                    {
                        $this->PatientPortalMedicalFavorite->create();
												$this->data['PatientPortalMedicalFavorite']['user_id'] = $userId;
                        if ($this->PatientPortalMedicalFavorite->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) added.', true));
                            $this->redirect(array('action' => 'patient_portal_medical'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
                        }
                    }
                } break;
            case "edit":
                {
                    if (!empty($this->data))
                    {
												$this->data['PatientPortalMedicalFavorite']['user_id'] = $userId;
                        if ($this->PatientPortalMedicalFavorite->save($this->data))
                        {
                            $this->Session->setFlash(__('Item(s) saved.', true));
                            $this->redirect(array('action' => 'patient_portal_medical'));
                        }
                        else
                        {
                            $this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
                        }
                    }
                    else
                    {
                        $diagnosis_id = (isset($this->params['named']['diagnosis_id'])) ? $this->params['named']['diagnosis_id'] : "";
                        $items = $this->PatientPortalMedicalFavorite->find(
                            'first', array(
                            'conditions' => array(
															'PatientPortalMedicalFavorite.diagnosis_id' => $diagnosis_id,
															//'PatientPortalMedicalFavorite.user_id' => $userId,
															)
                            )
                        );
												
												if (empty($items)) {
														$this->Session->setFlash('Favorite diagnosis not found', 'default', array('class' => 'error'));
														$this->redirect(array('action' => 'patient_portal_medical'));
														exit();
												}

                        $this->set('EditItem', $this->sanitizeHTML($items));
                    }
                } break;
            case "delete":
                {
                    if (!empty($this->data))
                    {
                        $diagnosis_id = $this->data['PatientPortalMedicalFavorite']['diagnosis_id'];
                        $delete_count = 0;

												
												$diagnoses = $this->PatientPortalMedicalFavorite->find('all', array(
													'conditions' => array(
														'PatientPortalMedicalFavorite.diagnosis_id' => $diagnosis_id,
														//'PatientPortalMedicalFavorite.user_id' => $userId,
													),
												));
												
                        foreach ($diagnoses as $d)
                        {
                            $this->PatientPortalMedicalFavorite->delete($d['PatientPortalMedicalFavorite']['diagnosis_id'], false);
                            $delete_count++;
                        }

                        if ($delete_count > 0)
                        {
                            $this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
                        }
                    }
                    $this->redirect(array('action' => 'patient_portal_medical'));
                } break;
            default:
                {
                    $this->set('PatientPortalMedicalFavorite', $this->sanitizeHTML($this->paginate('PatientPortalMedicalFavorite')));
//, array(
//											'PatientPortalMedicalFavorite.user_id' => $userId,
//										))));
                } break;
        }
    }



    function patient_reminders()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		if(!empty($this->data) && ($task == "addnew" || $task == "edit" || $task == "delete") && $this->getAccessType() != "W")
		{
			$task = "";
		}
		
        $this->PatientReminder->execute($this, $task);
    }

    function print_postcards()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$this->loadModel("SetupDetail");
		$this->loadModel("PatientReminder");
		$items = $this->SetupDetail->find('first');

		$this->layout = "blank";

		$this->set('SetupDetail', $this->sanitizeHTML($items));

		$this->set('PatientReminders', $PatientReminders = $this->sanitizeHTML($this->paginate('PatientReminder')));

		foreach ($PatientReminders as $PatientReminder):
			$this->data = array('PatientReminder' => array('reminder_id' => $PatientReminder['PatientReminder']['reminder_id'], 'postcard' => 'Printed'));
			$this->PatientReminder->save($this->data);
		endforeach;
    }

    function setup_details()
    {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		
		if(!empty($this->data) && $this->getAccessType() != "W")
		{
			$task = "";
		}
		
        $this->SetupDetail->execute($this, $task);
    }
	
	function reminders()
    {
		$this->loadModel('AppointmentSetupDetail');
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
				
		if(!empty($this->data) && $this->getAccessType('administration', 'appointment_types') != "W")
		{
			$task = "";
		}
		
        $this->AppointmentSetupDetail->execute($this, $task);
    }

    function browser_check()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $ub = NULL;
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";
        $notsupported = 0;
        $extramessage = "";

        //First get the platform?
        if (preg_match('/mobile|ipad|android/i', $u_agent))
        {
            $platform = 'mobile';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent))
        {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent))
        {
            $platform = 'windows';
        }
        elseif (preg_match('/linux/i', $u_agent))
        {
            $platform = 'linux';
        }
        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        elseif (preg_match('/Firefox/i', $u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        }
        elseif (preg_match('/Chrome/i', $u_agent))
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        }
        elseif (preg_match('/Safari/i', $u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        }
        elseif (preg_match('/Opera/i', $u_agent))
        {
            $bname = 'Opera';
            $ub = "Opera";
            $notsupported = 1;
        }
        elseif (preg_match('/Netscape/i', $u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
            $notsupported = 1;
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches))
        {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1)
        {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub))
            {
                $version = $matches['version'][0];
            }
            else
            {
                $version = $matches['version'][1];
            }
        }
        else
        {
            $version = $matches['version'][0];
        }


        // check if we have a number
        if ($version == null || $version == "")
        {
            $version = "?";
        }
        else
        {
            list($version2, ) = explode('.', $version);
            //see if depreciated, and alert
            if ($ub == 'Firefox')
            {
                if ($version2 < 8)
                {
                    $notsupported = 1;
                    $extramessage = " <a href='http://www.mozilla.org'>upgrade link</a>";
                }
            }
            else if ($ub == 'MSIE')
            {
                if ($version2 < 8)
                {
                    $notsupported = 1;
                    $extramessage = '
					(or try turning off \'Compatibility View\')<br />
						<div style=" margin:5px auto 0 auto;">
						<span style="float:left; margin:0 8px 0 0; font-weight:700;">We recommend using  one of these supported browser:</span><br />
						<a style="float:left; margin:15px 14px 0 14px;" href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home" target="_blank">
						<img style="float:left; margin:-10px 4px 0 0" src="/img/ie_download.png" />
						Internet Explorer</a>
					 	<a style="float:left; margin:15px 12px 0 7px;" href="http://www.mozilla.org/en-US/firefox/new/" target="_blank">
						<img style="float:left; margin:-10px 4px 0 0" src="/img/firefox_download.png" />
						Firefox</a>
						<a style="float:left; margin:15px 0 0 14px;" href="https://www.google.com/chrome" target="_blank">
						<img style="float:left; margin:-10px 4px 0 0" src="/img/chrome_download.png" />
						Google Chrome</a>
						<div style="clear:both;"></div>
						</div>
					';
                }
            }
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'notsupported' => $notsupported,
            'extramessage' => $extramessage
        );
    }
	
	function letter_templates()
    {
	    $this->loadModel("StateCode");
		$this->loadModel("ImmtrackCountry");
		$this->loadModel("PracticeProfile");
		$this->loadModel("PracticeLocation");
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$template_id = (isset($this->params['named']['template_id'])) ? $this->params['named']['template_id'] : "";
		$address_location = (isset($this->data['address_location'])) ? $this->data['address_location'] : "";
		$no_redirect = (isset($this->data['no_redirect'])?$this->data['no_redirect']:'');
		
		//$html = (isset($this->data['html'])) ? $this->data['html'] : "";
		$user_id = $this->user_id;
		
		$locations = $this->PracticeLocation->getAllLocations();
		$this->set("locations", $locations);
		
		if($task == 'addnew' || $task == 'edit' || $task == 'letter_content')
		{
			if (!empty($this->data))
			{
				$this->data['LetterTemplate']['use_practice_logo'] = (isset($this->data['LetterTemplate']['use_practice_logo'])) ? 1 : 0;
				$this->data['LetterTemplate']['use_practice_address'] = (isset($this->data['LetterTemplate']['use_practice_address'])) ? 1 : 0;
			}
		}
		
        switch ($task)
        {
		   case "letter_content":
			{				
				$this->layout = 'empty';
				$location_id = (isset($this->data['LetterTemplate']['location_id'])) ? $this->data['LetterTemplate']['location_id'] : "";
				$template_id = (isset($this->data['LetterTemplate']['template_id'])) ? $this->data['LetterTemplate']['template_id'] : "";
				$location = $this->PracticeLocation->getLocationItem($location_id);
				$this->set('location', $location);
				$practice_profile = $this->PracticeProfile->find('first');
				$practice_profile_logo = $practice_profile['PracticeProfile']['logo_image'];
				$this->set('practice_profile', $practice_profile_logo);
				$this->set('template_data', $this->data['LetterTemplate']);
				$data =  $this->render('template/letter_template');
				$file_path = $this->paths['temp'];
				$file_path = str_replace('//', '/', $file_path);
				$file_name = 'letter_template' . $template_id . '.pdf';
				site::write(pdfReport::generate($data), $file_path . $file_name);
				$file_path_test = $this->url_rel_paths['temp'];
				$ret = array();
				$ret['target_file'] = $file_name;
				echo json_encode($ret);	 
				exit();
			}break;
            case "addnew":
                {
					if($this->getAccessType() == "W")
					{
						$this->set("StateCode", $this->sanitizeHTML($this->StateCode->find('all')));
						$this->set("Countries", $this->sanitizeHTML($this->ImmtrackCountry->find('all')));
						
						if (!empty($this->data))
						{
							
							$this->data['LetterTemplate']['logo_image'] = (isset($this->data['LetterTemplate']['logo_image']) == 'on') ? 'Yes' : 'No';
							$this->data['LetterTemplate']['practice_address'] = (isset($this->data['LetterTemplate']['practice_address']) == 'on') ? 'Yes' : 'No';
							
							$this->LetterTemplate->create();
							$this->LetterTemplate->save($this->data);
							/*$template_id = $this->LetterTemplate->getLastInsertId();
							
							if($no_redirect == 'true')
							{
								$ret = array();
								$ret['template_id'] = $template_id;
								$ret['new_post_url'] = Router::url(array('task' => 'edit', 'template_id' => $template_id));
								echo json_encode($ret);
								exit;
							}
							else
							{*/
								$this->Session->setFlash(__('Item(s) added.', true));
								$this->redirect(array('action' => 'letter_templates'));
							//}
						}
					}
                } break;
            case "edit":
                {
				
				    $this->set("StateCode", $this->sanitizeHTML($this->StateCode->find('all')));
					$this->set("Countries", $this->sanitizeHTML($this->ImmtrackCountry->find('all')));
					
                    if (!empty($this->data))
                    {
                        if($this->getAccessType() == "W")
						{
							$this->data['LetterTemplate']['logo_image'] = (isset($this->data['LetterTemplate']['logo_image'])== 'on') ? 'Yes' : 'No';
							$this->data['LetterTemplate']['practice_address'] = (isset($this->data['LetterTemplate']['practice_address'])== 'on') ? 'Yes' : 'No';
							$this->data['LetterTemplate']['modified_timestamp'] = __date("Y-m-d H:i:s");
							$this->data['LetterTemplate']['modified_user_id'] = $this->user_id;
							$this->LetterTemplate->save($this->data);
							
							/*if($no_redirect == 'true')
							{
								$ret = array();
								echo json_encode($ret);
								exit;
							}
							else
							{*/
								$this->Session->setFlash(__('Item(s) saved.', true));
								$this->redirect(array('action' => 'letter_templates'));
							//}
						}
                    }
                    else
                    {
                        $template_id = (isset($this->params['named']['template_id'])) ? $this->params['named']['template_id'] : "";
                        $items = $this->LetterTemplate->find(
                            'first', array(
                            'conditions' => array('LetterTemplate.template_id' => $template_id)
                            )
                        );

                        $this->set('EditItem', $this->sanitizeHTML($items));
						
                    }
                } break;
            case "delete":
                {
					if($this->getAccessType() == "W")
					{
						$ret = array();
						$ret['delete_count'] = 0;
	
						if (!empty($this->data))
						{
							$ids = $this->data['LetterTemplate']['template_id'];
	
							foreach ($ids as $id)
							{
								$this->LetterTemplate->delete($id, false);
								$ret['delete_count']++;
							}
						}
					}

                    $this->redirect(array('action' => 'letter_templates'));
                }break;
            default:
                {
                    $this->set('LetterTemplate', $this->sanitizeHTML($this->paginate('LetterTemplate')));
			
                } break;
        }
	}
	
	public function forms() {
		if ($this->Session->read("UserAccount.role_id") == EMR_Roles::PATIENT_ROLE_ID) {
			$this->redirect(array('controller' => 'dashboard', 'action' => 'printable_forms'));
		}
		
		$this->redirect(array('action' => 'printable_forms'));
	}
		
	/**
	 * Action for printable forms administration
	 */
	public function printable_forms() {
		$this->loadModel("AdministrationForm");
		$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";

		$role_id = $this->Session->read('UserAccount.role_id');		
		$isAdmin = ($role_id == EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $role_id == EMR_Roles::PRACTICE_ADMIN_ROLE_ID ) ;
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
		
		if($task == "addnew" || $task == "edit")	{
			$user = $this->Session->read('UserAccount');
			$user_id = $user['user_id'];
			$role_id = $user['role_id'];
			$this->set('role_id', $role_id = $user['role_id']);

			if (!empty($this->data)) {
				$this->data["AdministrationForm"]['access_clinical'] = (int)@$this->data["AdministrationForm"]['access_clinical'];
				$this->data["AdministrationForm"]['access_non_clinical'] = (int)@$this->data["AdministrationForm"]['access_non_clinical'];
				$this->data["AdministrationForm"]['access_patient'] = (int)@$this->data["AdministrationForm"]['access_patient'];
			}
		}

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
			case "addnew": {
				if (!empty($this->data)) {
					$this->AdministrationForm->create();

					if ($this->data['AdministrationForm']['attachment_is_uploaded'] == "true") {
						$source_file = $this->paths['temp'] . $this->data['AdministrationForm']['attachment'];
						$destination_file = $this->paths['help'] . $this->data['AdministrationForm']['attachment'];

						@rename($source_file, $destination_file);
					}

					if ($this->AdministrationForm->save($this->data)) {
						$this->Session->setFlash(__('Item(s) added.', true));
						$this->redirect(array('action' => $this->params['action']));
					} else {
						$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
					}
				}
			} break;
			case "edit": {
				if (!empty($this->data)) {
					if ($this->data['AdministrationForm']['attachment_is_uploaded'] == "true") {
						$source_file = $this->paths['temp'] . $this->data['AdministrationForm']['attachment'];
						$destination_file = $this->paths['help'] . $this->data['AdministrationForm']['attachment'];

						@rename($source_file, $destination_file);
					}

					if ($this->AdministrationForm->save($this->data)) {
						$this->Session->setFlash(__('Item(s) saved.', true));
						$this->redirect(array('action' => $this->params['action']));
					} else {
						$this->Session->setFlash('Sorry, data can\'t be updated.', 'default', array('class' => 'error'));
					}
				} else {
					$form_id = (isset($this->params['named']['form_id'])) ? $this->params['named']['form_id'] : "";
					$items = $this->AdministrationForm->find(
					'first', array(
					'conditions' => array('AdministrationForm.form_id' => $form_id)
					)
					);

					$this->set('EditItem', $this->sanitizeHTML($items));
				}
			} break;
			case "delete": {
				if (!empty($this->data)) {
					$form_id = $this->data['AdministrationForm']['form_id'];
					$delete_count = 0;

					foreach ($form_id as $form_id) {
						$this->AdministrationForm->delete($form_id, false);
						$delete_count++;
					}

					if ($delete_count > 0) {
						$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
					}
				}
				$this->redirect(array('action' => $this->params['action']));
			} break;
			default: {
				
				if ($isAdmin) {
					$this->set('AdministrationForm', $this->sanitizeHTML($this->paginate('AdministrationForm')));
				} else {
					$this->set('AdministrationForm', $this->sanitizeHTML($this->paginate('AdministrationForm', array(
						'AdministrationForm.access_'.$dashboard_access => '1',
					))));
				}
				
				$user = $this->Session->read('UserAccount');
				$user_id = $user['user_id'];
				$role_id = $user['role_id'];
				$this->set('role_id', $role_id = $user['role_id']);
			} break;
		}			
	}
		
	/**
	 * Action for online forms
	 */
	public function online_forms(){
		$user = $this->Session->read('UserAccount');
		$userId = $user['user_id'];
		$role_id = $user['role_id'];		
		$isAdmin = ($role_id == EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $role_id == EMR_Roles::PRACTICE_ADMIN_ROLE_ID ) ;
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
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "index";
		$this->loadModel('FormTemplate');
    $this->FormTemplate->publishedOnly = false;
		$this->loadModel('FormData');
		
		switch($task) {
			
			case 'add_sample': {
				
				App::import('Lib', 'FormBuilder');
				$formBuilder = new FormBuilder();

				$forms = $formBuilder->getSampleForms();

				foreach ($forms as $name => $body) {

					$data = array(
						'FormTemplate' => array(
							'template_name' => $name,
							'template_content' => $body,
						),
					);

					$this->FormTemplate->create();

					$this->FormTemplate->save($data);
				}

					$this->Session->setFlash(__('Sample form added', true));
					$this->redirect(array('controller' => 'administration', 'action' => 'online_forms'));
					exit();			
				
				
			} break;
			
			case 'add': {
				if (isset($this->data['FormTemplate'])) {
					$this->FormTemplate->create();

					if (isset($this->params['form']['access'])) {
						$formAccessBits = '';
						$formAccessBits .= isset($this->params['form']['access']['clinical']) ? '1' : '0';
						$formAccessBits .= isset($this->params['form']['access']['non_clinical']) ? '1' : '0';
						$formAccessBits .= isset($this->params['form']['access']['patient']) ? '1' : '0';
						$this->data['FormTemplate']['access_control_bits'] = $formAccessBits;					
					}
					
					if ($this->FormTemplate->save($this->data)) {
						$this->Session->setFlash(__('Template added', true));
						$this->redirect(array('controller' => 'administration', 'action' => 'online_forms', 'task'=> 'view','template_id' => $this->FormTemplate->id));
					}
				}				
			} break;
		
		
			case 'edit': {
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

				$this->set(compact('template'));
				if (isset($this->data['FormTemplate'])) {
					
          if (isset($this->data['FormTemplate']['published'])) {
            $this->data['FormTemplate']['published'] = 1;
          }          
          
					if (isset($this->params['form']['access'])) {
						$formAccessBits = '';
						$formAccessBits .= isset($this->params['form']['access']['clinical']) ? '1' : '0';
						$formAccessBits .= isset($this->params['form']['access']['non_clinical']) ? '1' : '0';
						$formAccessBits .= isset($this->params['form']['access']['patient']) ? '1' : '0';
						$this->data['FormTemplate']['access_control_bits'] = $formAccessBits;					
					}
					
					$this->data['FormTemplate']['template_id'] = $template['FormTemplate']['template_id'];
					if ($this->FormTemplate->save($this->data)) {
						$this->Session->setFlash(__('Template saved', true));
						$this->redirect(array('controller' => 'administration', 'action' => 'online_forms'));
					}
				} else {
					$this->data = $template;
				}				
			} break;
		
			case 'view': {
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

				$jsonData = false;
				if (isset($this->params['form']['json_data'])) {
					App::import('Lib', 'FormBuilder');
					$formBuilder = new FormBuilder();

					$jsonData = $formBuilder->extractData($template['FormTemplate']['template_content'], $this->params['form']);

					$formBuilder->triggerSave($template['FormTemplate']['template_content'], $jsonData);

				}

				if (isset($this->params['form']['current_json_data'])) {
					$jsonData = $this->params['form']['current_json_data'];
				}

				if (isset($this->data['FormTemplate'])) {
					
					if (isset($this->params['form']['access'])) {
						$formAccessBits = '';
						$formAccessBits .= isset($this->params['form']['access']['clinical']) ? '1' : '0';
						$formAccessBits .= isset($this->params['form']['access']['non_clinical']) ? '1' : '0';
						$formAccessBits .= isset($this->params['form']['access']['patient']) ? '1' : '0';
						$this->data['FormTemplate']['access_control_bits'] = $formAccessBits;					
					}
					
					$this->data['FormTemplate']['template_id'] = $template['FormTemplate']['template_id'];
					if ($this->FormTemplate->save($this->data)) {
						$this->Session->setFlash(__('Template Saved', true));
						$this->redirect(array('controller' => 'administration', 'action' => 'online_forms'));//, 'task' => 'view', 'template_id' => $template['FormTemplate']['template_id']));
					}
				} else {
					$this->data = $template;
				}		


				$this->set(compact('template', 'jsonData'));				
			} break;
		
			case 'fill_up': {
				$templateId = (isset($this->params['named']['template_id'])) ? $this->params['named']['template_id'] : "";
				$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
				
				$patientName = '';
				
				if ($patient_id) {
					$this->loadModel('PatientDemographic');
					$patientName = $this->PatientDemographic->getPatientName($patient_id);
				}
				$this->set(compact('patient_id', 'patientName'));
				
				
				
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
				
				if (isset($this->params['form']['submit']) || isset($this->params['form']['submit_to_chart'])) {
					App::import('Lib', 'FormBuilder');
					$formBuilder = new FormBuilder();

					$jsonData = $formBuilder->extractData($template['FormTemplate']['template_content'], $this->params['form']);

					$formBuilder->triggerSave($template['FormTemplate']['template_content'], $jsonData);

					$patient_id = $this->params['form']['patient_id'];
					
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
					if (isset($this->params['form']['submit'])) {
						$this->redirect(array('controller' => 'administration', 'action' => 'online_forms', ));
					} else {
						$this->redirect(array(
							'controller' => 'patients', 
							'action' => 'index',
							'task' => 'edit',
							'patient_id' => $patient_id,
							'view' => 'attachments',
							'view_tab' => 2,
							'view_actions' => 'documents'
						));
					}
					
					
					exit();			
				}		

				$this->set(compact('template', 'patient_id'));				
				
				
				
			} break;
			
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
			
		case 'mass_delete': {

			$templateIds = (isset($this->params['form']['template_id'])) ? $this->params['form']['template_id'] : array();
			
			if ($templateIds) {
				foreach ($templateIds as $tId) {
					$this->FormTemplate->delete($tId);
				}
			}
			
			$this->Session->setFlash(__('Templates deleted', true));
			$this->redirect(array('controller' => 'administration', 'action' => 'online_forms'));
			exit();
			
		}	break;
		case 'delete': {
			
			if (!isset($this->params['form']['delete_template'])) {
					$this->redirect(array('controller' => 'administration', 'action' => 'online_forms', ));
					exit();
			}
			
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
				
				$this->FormTemplate->delete($templateId);
				$this->Session->setFlash(__('Template deleted', true));
				$this->redirect(array('controller' => 'administration', 'action' => 'online_forms'));
				exit();
				
				
			
		} break;
			default: {
				$task = 'index';
		
				$this->paginate['FormTemplate'] = array(
					'limit' => 10,
					'order' => array(
						'FormTemplate.template_name' => 'asc',
					),
				);
				
				if ($isAdmin) {
					$templates = $this->paginate('FormTemplate', array(
						'FormTemplate.template_version' => 0
					));
				} else {
					$templates = $this->paginate('FormTemplate', array(
						'FormTemplate.template_version' => 0,
						'FormTemplate.access_'.$dashboard_access => '1',
					));
				}
				
				$this->set(compact('templates'));		
		
		
			} break;
			
		}

		
		$this->set(compact('task'));
	}
		
	public function online_form_builder() {
		$task = isset($this->params['named']['task']) ? $this->params['named']['task'] : '';
		
		
		switch ($task) {
			case 'generate_component':
				App::import('Lib', 'FormBuilder');
				$formBuilder = new FormBuilder();
				$formBuilder->withJSON = true;
				$data = $this->params['data'];
				
				if ($data['component']['type'] == 'snippet') {
					unset($data['component']['type']);
					echo trim($formBuilder->renderSnippet($data));
				} else {
					
					if (in_array($data['component']['type'], array('select', 'radio', 'checkbox'))) {
						$data['component']['elementOptions'] = json_decode($data['component']['elementOptions'], true);
						$data['component']['default'] = json_decode($data['component']['default'], true);
					}
					
					echo trim($formBuilder->renderElement($data));
				}
				
				exit();
				break;
			default:
				break;
				
		}
		
	}
	
	
		
		public function vendor_settings() {
			$emergency_access_type = (($this->getAccessType("preferences", "emergency_access") == 'NA') ? false : true);
			$user_options_type = (($this->getAccessType("preferences", "user_options") == 'NA') ? false : true);


			$this->loadModel('practiceSetting');
			$this->loadModel('PracticePlan');

			// Fetch all types of available plans
			$availablePlans = $this->PracticePlan->getPlans();

			$settings  = $this->practiceSetting->getSettings();
			
			$this->set(compact('settings', 'emergency_access_type', 'user_options_type', 'availablePlans'));			
		}
		
		public function patient_import() {
			
			$this->loadModel('PatientDemographic');
			
			$this->PatientDemographic->process_csv_upload($this);
			
		}

		public function patient_export() {
			//$this->layout = "empty";
			$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
			$file_id = (isset($this->params['named']['file_id'])) ? $this->params['named']['file_id'] : "";
			$this->loadModel('PatientDemographic');
			
			$this->PatientDemographic->download_csv_dump($this,$file_id,$task);
			
		}	
		
		public function patient_export_ccr() {
			$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
			$file_id = (isset($this->params['named']['file_id'])) ? $this->params['named']['file_id'] : "";
			$this->loadModel('PatientDemographic');
			
			$this->PatientDemographic->download_ccr_dump($this,$file_id,$task);
		}		
		
	function encounter_types() {
		$this->loadModel('PracticeEncounterType');
		$this->loadModel('PracticeEncounterTab');
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$user = $this->Session->read('UserAccount');
		$this->set('task', $task);
		
		$defaults = array(
			'Summary', 'CC', 'HPI', 'HX', 'Meds & Allergy',
			'ROS', 'Vitals', 'PE', 'POC', 'Results', 
			'Assessment', 'Plan', 'Superbill',
		);				

		$subHeadingDefaults = array(
			'HX' => array(
				'Medical History' => 
						array(
							'name' => 'Medical History',
							'hide' => '0',
						),
				'Surgical History' => 
						array(
							'name' => 'Surgical History',
							'hide' => '0',
						),

				'Social History' => 
						array(
							'name' => 'Social History',
							'hide' => '0',
						),

				'Family History' => 
						array(
							'name' => 'Family History',
							'hide' => '0',
						),
				'Ob/Gyn History' => 
						array(
							'name' => 'Ob/Gyn History',
							'hide' => '0',
						),
			),
		);
		
		switch ( $task ) {
			case "addnew": {
				
					if ( $this->getAccessType('administration', 'encounter_tabs') == "W"  && !empty($this->data)) {
						if (isset($this->data['PracticeEncounterType']['encounter_type_id'])) {
							unset($this->data['PracticeEncounterType']['encounter_type_id']);
						}

						$this->data['PracticeEncounterType']['modified_user_id'] = $user['user_id'];
						$dateTime = __date('Y-m-d H:i:s');
						$this->data['PracticeEncounterType']['created_timestamp'] = $dateTime;
						$this->data['PracticeEncounterType']['modified_timestamp'] = $dateTime;
						
						$this->PracticeEncounterType->create();
						if ( $this->PracticeEncounterType->save($this->data) ) {
							
							$encounterTypeId = $this->PracticeEncounterType->getLastInsertID();
							
							$userId = EMR_Account::getCurretUserId();
							$currentTime = __date('Y-m-d H:i:s');
							
							$this->data['TabOrdering'] = explode("&", $this->data['NewTabOrdering']);
							$tabs = isset($this->params['form']['tab']) ? $this->params['form']['tab'] : array();
							$subHeadings = isset($this->params['form']['subHeadings']) ? $this->params['form']['subHeadings'] : array();
							
							for ( $i = 0; $i < count($this->data['TabOrdering']); ++$i ) {
									$index = substr($this->data['TabOrdering'][$i], 6);

									$current = array(
										'PracticeEncounterTab' => array(
											'tab' => $defaults[$index],
											'name' => $defaults[$index],
											'order' => $i,
											'modified_user_id' => $userId,
											'modified_timestamp' => $currentTime,
											'encounter_type_id' => $encounterTypeId,
											'hide' => isset($tabs[$index]) ? $tabs[$index] : 0,
										),
									);							
									
									$this->PracticeEncounterTab->create();
									
									if (!$this->PracticeEncounterTab->save($current) ) {
										$error = true;
									} else {
										$cTabId = $this->PracticeEncounterTab->getLastInsertID();
										
										if (isset($subHeadings[$index])) {
											$this->PracticeEncounterTab->id = $cTabId;
											$this->PracticeEncounterTab->saveField('sub_headings', json_encode($subHeadings[$index]));
										}
										
									}
							}									
							

							
							$this->Session->setFlash(__('Item(s) added.', true));
							$this->redirect(array('action' => 'encounter_types'));
						} else {
							$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
						}
					} else {
						$ct = 0;
						$PracticeEncounterTab = array();
						foreach ($defaults as $order => $name) {

							$current = array(
								'PracticeEncounterTab' => array(
									'tab' => $name,
									'name' => $name,
									'order' => $order,
									'modified_user_id' => '',
									'modified_timestamp' => '',
									'encounter_type_id' => 0,
									'hide' => 0,
								),
							);
							
							if (isset($subHeadingDefaults[$name])) {
								$current['PracticeEncounterTab']['sub_headings'] = json_encode($subHeadingDefaults[$name]);
							}							
							
								$current['PracticeEncounterTab']['tab_id'] = $ct++;
								$PracticeEncounterTab[] = $current;
						}
						$this->set("PracticeEncounterTab", $PracticeEncounterTab);							
					}
				} break;
			case "edit": {
					$encounterTypeId = (isset($this->params['named']['encounter_type_id'])) ? intval($this->params['named']['encounter_type_id']) : 0;

					$encounterType = $this->PracticeEncounterType->find('first', array(
						'conditions' => array(
							'PracticeEncounterType.encounter_type_id' => $encounterTypeId,
						),
					));

					if (!$encounterType) {
						$this->Session->setFlash('Encounter type not found.', 'default', array('class' => 'error'));
						$this->redirect(array('action' => 'encounter_types'));
					}
					
					$this->set(compact('encounterType'));
					
					$PracticeEncounterTab = $this->PracticeEncounterTab->getEncounterTypeTabs($encounterTypeId);

					if (!$PracticeEncounterTab) {
						$this->redirect(array('action' => 'encounter_tabs'));
					}

					$this->set("PracticeEncounterTab", $PracticeEncounterTab);					
					
					
					if ( $this->getAccessType('administration', 'encounter_tabs') == "W"  && !empty($this->data)) {
						
						$this->data['PracticeEncounterType']['encounter_type_id'] = $encounterTypeId;
						$this->data['PracticeEncounterType']['modified_user_id'] = $user['user_id'];
						$dateTime = __date('Y-m-d H:i:s');
						$this->data['PracticeEncounterType']['modified_timestamp'] = $dateTime;
						
						$error = false;
						if (!$this->PracticeEncounterType->save($this->data) ) {
							$error = true;
						} 
						
						$tabs = isset($this->params['form']['tab']) ? $this->params['form']['tab'] : array();
						$tabName = isset($this->params['form']['tabName']) ? $this->params['form']['tabName'] : array();
						foreach ( $tabs as $tabId => $hide ) {
							$this->PracticeEncounterTab->id = $tabId;
							$this->PracticeEncounterTab->saveField('hide', $hide);
						}
						$subHeadings = isset($this->params['form']['subHeadings']) ? $this->params['form']['subHeadings'] : array();
						

						if ( $this->data['usedefault'] == "false" ) {
							$this->data['TabOrdering'] = explode("&", $this->data['NewTabOrdering']);
						}
						for ( $i = 0; $i < count($this->data['TabOrdering']); ++$i ) {
							if ( $this->data['usedefault'] == "false" ) {
								$this->data['PracticeEncounterTab']['tab_id'] = substr($this->data['TabOrdering'][$i], 6);
								if (isset($tabName[$this->data['PracticeEncounterTab']['tab_id']])) {
									$this->data['PracticeEncounterTab']['name'] = $tabName[$this->data['PracticeEncounterTab']['tab_id']];
								}
								
								if (isset($subHeadings[$this->data['PracticeEncounterTab']['tab_id']])) {
									$this->data['PracticeEncounterTab']['sub_headings'] = json_encode($subHeadings[$this->data['PracticeEncounterTab']['tab_id']]);
								} else {
									unset($this->data['PracticeEncounterTab']['sub_headings']);
								}
								
								$this->data['PracticeEncounterTab']['order'] = $i;
							} else {
								$this->PracticeEncounterTab->id = $this->data['TabOrdering']['tab_id' . $i];
								$current = $this->PracticeEncounterTab->read();
								$current['PracticeEncounterTab']['order'] = array_search($current['PracticeEncounterTab']['tab'], $defaults);
								$current['PracticeEncounterTab']['hide'] = 0;
								$current['PracticeEncounterTab']['name'] = $current['PracticeEncounterTab']['tab'];
								
								if (isset($subHeadingDefaults[$current['PracticeEncounterTab']['tab']])) {
									$current['PracticeEncounterTab']['sub_headings'] = json_encode($subHeadingDefaults[$current['PracticeEncounterTab']['tab']]);
								}												
								
								$this->data['PracticeEncounterTab'] = $current['PracticeEncounterTab'];
							}
							
							if (isset($this->data['PracticeEncounterTab']['tab'])) {
								unset($this->data['PracticeEncounterTab']['tab']);
							}
							if (!$this->PracticeEncounterTab->save($this->data) ) {
								$error = true;
							} 
						}						
						
						if (!$error) {
							$this->Session->setFlash(__('Changes saved.', true));
							$this->redirect(array('action' => 'encounter_types'));
						} else {
							$this->Session->setFlash('Sorry, data can\'t be saved.', 'default', array('class' => 'error'));
						}
					}
				} break;
			case "delete": {
					if ( $this->getAccessType('administration', 'encounter_tabs') == "W" && !empty($this->data)) {
						$encounterTypeIds = $this->data['PracticeEncounterType']['encounter_type_id'];
						
						if ($encounterTypeIds) {
							if (!is_array($encounterTypeIds)) {
								$encounterTypeIds = array($encounterTypeIds);
							}
							
							$encounterTypes = $this->PracticeEncounterType->find('all', array(
								'conditions' => array(
									'PracticeEncounterType.encounter_type_id' => $encounterTypeIds,
								),
							));
							
						}
						
						
						
						$delete_count = 0;

						foreach ($encounterTypes as $e ) {
							if (intval($e['PracticeEncounterType']['readonly'])) {
								continue;
							}
							
							$this->PracticeEncounterType->delete($e['PracticeEncounterType']['encounter_type_id'], true);
							$delete_count++;
						}

						if ( $delete_count > 0 ) {
							$this->Session->setFlash($delete_count . __('Item(s) deleted.', true));
						}
					}

					$this->redirect(array('action' => 'encounter_types'));
				} break;
			default: {
				
					$encounterTypes = $this->paginate('PracticeEncounterType'); 
					$this->set(compact('encounterTypes'));
				
				} break;
		}
	}

	function superbill_service_level() {
		$this->loadModel('AdministrationSuperbillServiceLevel');
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$service_level_id = (isset($this->params['named']['service_level_id'])) ? intval($this->params['named']['service_level_id']) : '';
		  switch ( $task ) {
			case "addnew_service_level": {
					if($this->data)
					{			
						$this->AdministrationSuperbillServiceLevel->create();
						$this->AdministrationSuperbillServiceLevel->save($this->data);
						$this->Session->setFlash(__('Item(s) added.', true));
						$this->redirect(array('action' => 'superbill_service_level'));
					}	
				}
			case "edit_service_level": {
					if($this->data)
					{
						$this->AdministrationSuperbillServiceLevel->save($this->data);
						$this->Session->setFlash(__('Item(s) updated.', true));
						$this->redirect(array('action' => 'superbill_service_level'));						
					}
					else
					{
					    $this->data = $this->AdministrationSuperbillServiceLevel->find('first', array(
						'conditions' => array(
							'AdministrationSuperbillServiceLevel.service_level_id' => $service_level_id,
						),
					    ));				
					}	
				} break;
            		case "delete":   	{
					if($this->getAccessType('administration', 'encounter_tabs') == "W")
					{
						if (!empty($this->data))
						{
							$delete_count = 0;
							$id = $this->data['AdministrationSuperbillServiceLevel']['service_level_id'];
							foreach ($id as $ids)
							{
								$this->AdministrationSuperbillServiceLevel->delete($ids, false);
								$delete_count++;
							}
	
							if ($delete_count > 0)
							{
								$this->Session->setFlash($delete_count . __(' Item(s) deleted.', true));
							}
						}
					}
                    		$this->redirect(array('action' => 'superbill_service_level'));
                } break;				
			default: {
					$this->paginate['AdministrationSuperbillServiceLevel']['order'] = array('AdministrationSuperbillServiceLevel.service_level_id' => 'asc');
					$service_levels = $this->paginate('AdministrationSuperbillServiceLevel'); 

					$this->set(compact('service_levels'));
									
				} break;
		  }
	}
	
	function superbill_advanced() {
		$this->loadModel('AdministrationSuperbillAdvanced');
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$advanced_level_id = (isset($this->params['named']['advanced_level_id'])) ? intval($this->params['named']['advanced_level_id']) : '';
		  switch ( $task ) {
			case "addnew": {
					if($this->data)
					{			
						$this->AdministrationSuperbillAdvanced->create();
						$this->AdministrationSuperbillAdvanced->save($this->data);
						$this->Session->setFlash(__('Item(s) added.', true));
						$this->redirect(array('action' => 'superbill_advanced'));
					}	
				}
			case "edit": {
					if($this->data)
					{
						$this->AdministrationSuperbillAdvanced->save($this->data);
						$this->Session->setFlash(__('Item(s) updated.', true));
						$this->redirect(array('action' => 'superbill_advanced'));						
					}
					else
					{
					    $this->data = $this->AdministrationSuperbillAdvanced->find('first', array(
						'conditions' => array(
							'AdministrationSuperbillAdvanced.advanced_level_id' => $advanced_level_id,
						),
					    ));				
					}	
				} break;
            		case "delete":   	{
					if($this->getAccessType('administration', 'encounter_tabs') == "W")
					{
						if (!empty($this->data))
						{
							$delete_count = 0;
							$id = $this->data['AdministrationSuperbillAdvanced']['advanced_level_id'];
							foreach ($id as $ids)
							{
								$this->AdministrationSuperbillAdvanced->delete($ids, false);
								$delete_count++;
							}
	
							if ($delete_count > 0)
							{
								$this->Session->setFlash($delete_count . __(' Item(s) deleted.', true));
							}
						}
					}
                    		$this->redirect(array('action' => 'superbill_advanced'));
                } break;				
			default: {

					$this->paginate['AdministrationSuperbillAdvanced']['order'] = array('AdministrationSuperbillAdvanced.advanced_level_id' => 'asc');
					$advanced_levels = $this->paginate('AdministrationSuperbillAdvanced'); 
					
					$this->set(compact('advanced_levels'));
									
				} break;
		  }
	}
	
}

?>
