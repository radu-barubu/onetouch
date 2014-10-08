<?php
  
class PatientDemographic extends AppModel 
{
	public $name = 'PatientDemographic';
	public $primaryKey = 'patient_id';
	public $useTable = 'patient_demographics';
	public $actsAs = array('Containable', 'Des' => array(
			'first_name', 'middle_name', 'last_name', 'gender', 
			'preferred_language', 'race', 'ethnicity', 'dob', 'ssn', 
			'driver_license_id', 'driver_license_state', 'address1', 'address2', 
			'city', 'state', 'zipcode', 'immtrack_county', 'immtrack_country',
			'work_phone', 'work_phone_extension', 'home_phone', 'cell_phone', 'fax_number','email',
			'guardian', 'relationship', 'emergency_contact', 'custom_patient_identifier',
			'emergency_phone', 'patient_photo', 'driver_license', 'status', 'source_system_id',
			'segment_code', 'dosespot_patient_id', 'xlink_chart_number'
		),
		'Auditable' => 'General Information - Demographics'
    );
	public $order = "patientName";

	public $hasMany = array(
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'patient_id'
		),
		'PatientProblemList' => array(
			'className' => 'PatientProblemList',
			'foreignKey' => 'patient_id'
		),
		'PatientMedicationList' => array(
			'className' => 'PatientMedicationList',
			'foreignKey' => 'patient_id'
		),
		'PatientGuarantor' => array(
			'className' => 'PatientGuarantor',
			'foreignKey' => 'patient_id'
		),
		'PatientInsurance' => array(
			'className' => 'PatientInsurance',
			'foreignKey' => 'patient_id'
		)
	);

	public $customPaginate = false;
	public $customPaginateResult = array();
	
	public $virtualFields = array();

	public function __construct($id = false, $table = null, $ds = null) 
	{
		parent::__construct($id, $table, $ds);
		
		$this->virtualFields['patientName'] = sprintf("CONCAT(DES_DECRYPT(%s.first_name), ' ', DES_DECRYPT(%s.last_name))", $this->alias, $this->alias);
		$this->virtualFields['age'] = sprintf("(TIMESTAMPDIFF(YEAR,DES_DECRYPT(%s.dob),NOW()))", $this->alias, $this->alias);
		$this->virtualFields['gender_str'] = sprintf("CASE DES_DECRYPT(%s.gender) WHEN 'M' THEN 'Male' ELSE 'Female' END", $this->alias);
		$this->virtualFields['patient_search_name'] = sprintf("CONCAT(CONVERT(DES_DECRYPT(%s.first_name) USING latin1),' ',CONVERT(DES_DECRYPT(%s.last_name) USING latin1))", $this->alias, $this->alias);
	}
	
	public function afterFind($results, $primary)
	{
		$emdeon_xml_api = new Emdeon_XML_API();
		
		for($i = 0; $i < count($results); $i++)
		{
			if(isset($results[$i][$this->alias]['home_phone']))
			{
				$phone_details = $emdeon_xml_api->extractPhone($results[$i][$this->alias]['home_phone']);
				$results[$i][$this->alias]['home_phone_area_code'] = $phone_details['area_code'];
				$results[$i][$this->alias]['home_phone_number'] = $phone_details['phone'];
			}
			
			if(isset($results[$i][$this->alias]['patient_id']))
			{
				$this->PatientSocialHistory = ClassRegistry::init('PatientSocialHistory');
				
				$psh_marital_status = $this->PatientSocialHistory->find('first', array('conditions' => array('PatientSocialHistory.type' => 'Marital Status', 'PatientSocialHistory.patient_id' => $results[$i][$this->alias]['patient_id'])));
				$psh_occupation = $this->PatientSocialHistory->find('first', array('conditions' => array('PatientSocialHistory.type' => 'Occupation', 'PatientSocialHistory.patient_id' => $results[$i][$this->alias]['patient_id'])));
				
				$results[$i][$this->alias]['marital_status'] = $results[$i][$this->alias]['occupation'] = "";
					
				if($psh_marital_status)
				{
					$results[$i][$this->alias]['marital_status'] = $psh_marital_status['PatientSocialHistory']['marital_status'];
				}
				
				if($psh_occupation)
				{
					$results[$i][$this->alias]['occupation'] = $psh_occupation['PatientSocialHistory']['occupation'];
				}	
			}
		}
		//doesnt look like this is used, so commented out. Robert 
		//$this->PracticeSetting = ClassRegistry::init('PracticeSetting');
		//$practice_settings = $this->PracticeSetting->getSettings();
		
		return $results;
	}
	
	public function getPatientAgeInMonth($patient_id)
	{
		$this->recursive = -1;
		$conditions['PatientDemographic.patient_id'] = $patient_id;

		$search_result = $this->find(
			'first', 
			array(
				'conditions' => $conditions
			)
		);
		
		if($search_result)
		{
			if(strlen($search_result['PatientDemographic']['dob']) > 0)
			{
				return (date_diff(date_create($search_result['PatientDemographic']['dob']), date_create('now'))->y * 12) + date_diff(date_create($search_result['PatientDemographic']['dob']), date_create('now'))->m;
			}
			
			return 1;
		}
		else
		{
			return 1;
		}
	}
	
	public function beforeSave($options)
	{
		$this->data['PatientDemographic']['modified_timestamp'] = __date("Y-m-d H:i:s");
                
                // Check is user account session is present
                // It is possible for the patient account demographic to be saved
                // without being logged in for the case of patient account signups
                if (isset($_SESSION['UserAccount']['user_id'])) {
                    $this->data['PatientDemographic']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
                } 
                
		return true;
	}
	
	public function afterSave($created) {
		parent::afterSave($created);
		
		if ($created  && class_exists('AppController') ) {
			$controller = new AppController();
			UploadSettings::initUploadPath($controller);
			$patient_id = $this->field('patient_id');
			
			$controller->paths['patient_id'] = $controller->paths['patients'] . $this->id . DS;
			$controller->paths['patient_encounter_radiology'] = 
				$controller->paths['patients'] . $this->id . DS . 'radiology' . DS . '0' . DS;
			$controller->paths['patient_encounter_img'] = 
				$controller->paths['patients'] . $this->id . DS . 'images' . DS . '0' . DS;
			
			
			UploadSettings::createIfNotExists($controller->paths['patient_id']);
			UploadSettings::createIfNotExists($controller->paths['patient_encounter_radiology']);
			UploadSettings::createIfNotExists($controller->paths['patient_encounter_img']);
			
		}
		
	}
	
	function paginateCount($conditions = null, $recursive = 0, $extra = array()) 
	{
		
		$parameters = compact('conditions');
		$this->recursive = $recursive;
		if (isset($extra['group'])) 
		{
			$extra['callbacks'] = false;
		}
		
			$count = $this->find('count', array_merge($parameters, $extra));
		if (isset($extra['group'])) 
		{
			$count = $this->getAffectedRows();
		}
		return $count;
	}
	
	public function validatePatient($patient_id, $patient_name)
	{
		$patient = $this->getPatient($patient_id);
		
		if($patient)
		{
			if(strtoupper(trim($patient_name)) == strtoupper(trim($patient['patientName'])))
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function getPatient($patient_id)
	{
		$conditions['PatientDemographic.patient_id'] = $patient_id;

		$search_result = $this->find(
			'first', 
			array(
				'conditions' => $conditions,
				'recursive' => -1
			)
		);
		if($search_result)
		{
			return $search_result['PatientDemographic'];
		}
		else
		{
			return false;
		}
	}
	
	public function getPatientByMRN($mrn)
	{
		$conditions['PatientDemographic.mrn'] = $mrn;
		
		$this->PracticeSetting = ClassRegistry::init('PracticeSetting');
		$practice_settings = $this->PracticeSetting->getSettings();

		$search_result = $this->find(
			'first', 
			array(
				'conditions' => $conditions,
				'contain' => array(
					'PatientGuarantor' => array(
					),
					'PatientInsurance' => array(
						'conditions' => array('PatientInsurance.ownerid' => $practice_settings->emdeon_facility)
					)
				)
			)
		);
		
		if($search_result)
		{
			return $search_result;
		}
		else
		{
			return false;
		}
	}
	
	public function getPatientNamebyID($patient_id)
	{	
		$conditions['PatientDemographic.patient_id'] = $patient_id;
		$search_result = $this->find('first', array('conditions' => $conditions,
							    'recursive' => -1,
							    'fields' => array('first_name','middle_name','last_name')));
		
		if($search_result)
		{
			return $search_result['PatientDemographic'];
		}
		else
		{
			return false;
		}
	}
	
	public function getPatientStatus($patient_id)
	{
		$search_result = $this->getPatient($patient_id);
		
		if($search_result)
		{
			return $search_result['status'];
		}
		else
		{
			return '';
		}
	}
	
	public function getPatientDoesespotId($patient_id)
	{
		$conditions['PatientDemographic.patient_id'] = $patient_id;

		$search_result = $this->find(
			'first', 
			array(
				'conditions' => $conditions,
				'recursive' => -1,
				'fields' => array('dosespot_patient_id')
			)
		);
		
		if(!empty($search_result))
		{
			//it must be numeric only
			$val=$search_result['PatientDemographic']['dosespot_patient_id'];
			return (is_numeric($val))?$val:'';
			
		}
		else
		{
			return '';
		}
	}
	
	public function getPatientIdbyDosespotId($dosespot_patient_id)
	{
		$conditions['PatientDemographic.dosespot_patient_id'] = $dosespot_patient_id;

		$search_result = $this->find(
			'first', 
			array(
				'conditions' => $conditions,
				'recursive' => -1,
				'fields' => array('patient_id')
			)
		);
		
		if(!empty($search_result))
		{
			return $search_result['PatientDemographic']['patient_id'];
		}
		else
		{
			return '';
		}
	}
	
	public function getPatientName($patient_id)
	{
		$conditions['PatientDemographic.patient_id'] = $patient_id;
		$search_result = $this->find(
			'first', 
			array(
				'conditions' => $conditions,
				'recursive' => -1,
				'fields' => array('first_name','last_name')
			)
		);
		
		if($search_result)
		{
			return $search_result['PatientDemographic']['first_name'] . ' ' . $search_result['PatientDemographic']['last_name'];
		}
		else
		{
			return '';
		}
	}
	
	public function getPatientMRN($patient_id)
	{
		$patient = $this->getPatient($patient_id);
		
		return $patient['mrn'];
	}
	
	public function checkMRN($mrn, $patient_id = "")
	{
		$conditions = array();
		$conditions['mrn'] = $mrn;
		
		if(strlen($patient_id) > 0)
		{
			$conditions['PatientDemographic.patient_id != '] = $patient_id;
		
		}
		
		$search_result = $this->find(
			'count', 
			array(
				'conditions' => $conditions,
				'recursive' => -1
			)
		);
		
		if($search_result > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function getNewMRN()
	{
		$LastMrn = $this->find('first', array('order' => array('PatientDemographic.mrn DESC'), 'recursive' => -1));

		if (!empty($LastMrn['PatientDemographic']['mrn'])) // if records already exist, increment by 1
		{
			return ($LastMrn['PatientDemographic']['mrn'] + 1);
		} 
		else
		{
			$this->PracticeSetting = ClassRegistry::init('PracticeSetting');
			$practice_settings = $this->PracticeSetting->getSettings();
			
			return $practice_settings->mrn_start;
		}
	}

	/**
	*	this is for patient export of demographics. found in Administration -> Services -> Import/Export
	* 	and runs from a command line shell (patient_demographic_csv_export.php) so it doesn't bog down the system as it runs
	*/
	public function exportPatientDemographics($file_id) //called from shell
	{
				$patients= ClassRegistry::init('PatientDemographic')->find('all', array('contain' => false));
				$settings = ClassRegistry::init('PracticeSetting')->find('first');
				$this->settings = $settings['PracticeSetting'];
				$fields = self::getImportableFields();
				// get location of last schedule
				$locations = ClassRegistry::init('PatientDemographic')->query('
					SELECT location_name, patient_id FROM (  
						SELECT `calendar_id` , `location` , `patient_id`
						FROM `schedule_calendars`
						ORDER BY `date` DESC , `starttime` DESC
					) AS ScheduleCalendar INNER JOIN practice_locations AS PracticeLocation 
					ON ( ScheduleCalendar.location = PracticeLocation.location_id ) 
					GROUP BY `patient_id`
				');
				$tmpLocations = array();
				if ( !empty($locations) ) {
					foreach ( $locations as $key => $location ) {
						$tmpLocations[$location['ScheduleCalendar']['patient_id']] = $location['PracticeLocation']['location_name'];
					}
				}
				foreach($patients as $value)
				{
				   //compare and only use the fields we want
				   foreach($fields as $field)
				   {
				      $data[$field]=$value['PatientDemographic'][$field];
				   }
				   $data['location'] = isset($tmpLocations[$value['PatientDemographic']['patient_id']])? $tmpLocations[$value['PatientDemographic']['patient_id']] : '';
				   //push into a new array
				   $output[]=$data;
				}
				$fields[] = 'location';
				$practice_id =$this->settings['practice_id'];
				$tmp=$this->settings['uploaddir_temp'];
				$tmpfile=APP.'webroot/CUSTOMER_DATA/'.$practice_id.'/'.$tmp. '/'.rand().'.tmp'; //write to temp file first
				$tofile=APP.'webroot/CUSTOMER_DATA/'.$practice_id.'/'.$tmp. '/'.$file_id;
				$csv_file = fopen($tmpfile, 'w');
				// the CSV headers
				fputcsv($csv_file, $fields, ',', '"');
				//push in the output
				foreach ($output as $output2) {
				  fputcsv($csv_file, $output2, ',', '"');
				}
				fclose($csv_file);
				rename($tmpfile,$tofile); //now that file is finished, make it visible to end user for downloading
	}			
				
	/**
	*	this is for patient export of CCR in bulk. found in Administration -> Services -> Import/Export
	* 	and runs from a command line shell (patient_demographic_ccr_export.php) so it doesn't bog down the system as it runs
	*/
	public function exportPatientCcr($file_id) //called from shell
	{
				$patients= ClassRegistry::init('PatientDemographic')->find('all', array('contain' => false, 'fields' => 'patient_id'));
				$settings = ClassRegistry::init('PracticeSetting')->find('first');
				$this->settings = $settings['PracticeSetting'];
			
				$practice_id =$this->settings['practice_id'];
				$tmp=$this->settings['uploaddir_temp'];
				$tmp_folder=APP.'webroot/CUSTOMER_DATA/'.$practice_id.'/'.$tmp. '/'.rand();
				//make folder to put all files into
				mkdir( $tmp_folder );
				foreach($patients as $patient)
				{
					$url ='/patients/disclosure_records/patient_id:'.$patient['PatientDemographic']['patient_id'].'/task:addnew/disclosure_id:0/task:get_report_ccr/ccr_mode:yes//export_dump:1';
				     $output = file_get_contents( $url );
//cakelog::write('debug',$output);				    
//exit;
				}				
				$tmpfile=APP.'webroot/CUSTOMER_DATA/'.$practice_id.'/'.$tmp. '/'.rand().'.tmp'; //write to temp file first
				$tofile=APP.'webroot/CUSTOMER_DATA/'.$practice_id.'/'.$tmp. '/'.$file_id;

				rename($tmpfile,$tofile); //now that file is finished, make it visible to end user for downloading
	}

	public function generatePatientCcr($patient_id, $user_id, $url) {
		
		if (!function_exists('getUuid')) {
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
		}
		
		if (!function_exists('sourceType')) {
			function sourceType($ccr, $uuid){

				$e_Source = $ccr->createElement('Source');

				$e_Actor = $ccr->createElement('Actor');
				$e_Source->appendChild($e_Actor);

				$e_ActorID = $ccr->createElement('ActorID',$uuid);
				$e_Actor->appendChild($e_ActorID);

				return $e_Source;
			}			
		}


		$authorID = getUuid();
		$patientID = getUuid();
		$sourceID = getUuid();
		$oemrID = getUuid();
		$uuid = '';

		$this->UserAccount = ClassRegistry::init('UserAccount');
		$this->EncounterMaster = ClassRegistry::init('EncounterMaster');
		$this->PatientMedicalHistory = ClassRegistry::init('PatientMedicalHistory');
		$this->PatientSurgicalHistory = ClassRegistry::init('PatientSurgicalHistory');
		$this->PatientSocialHistory = ClassRegistry::init('PatientSocialHistory');
		$this->PatientFamilyHistory = ClassRegistry::init('PatientFamilyHistory');
		$this->PatientAllergy = ClassRegistry::init('PatientAllergy');
		$this->PatientProblemList = ClassRegistry::init('PatientProblemList');
		$this->EncounterPointOfCare = ClassRegistry::init('EncounterPointOfCare');
		$this->PatientMedicationList = ClassRegistry::init('PatientMedicationList');
		$this->EncounterPlanReferral = ClassRegistry::init('EncounterPlanReferral');
		$this->EncounterPlanHealthMaintenance = ClassRegistry::init('EncounterPlanHealthMaintenance');
		$this->PracticeLocation = ClassRegistry::init('PracticeLocation');
		
		
		$this->UserAccount->id = $user_id;
		$user = $this->UserAccount->read();
		$user = $user['UserAccount'];
		
		$patient_encounter_id = $this->EncounterMaster->getEncountersByPatientID($patient_id);
		$location = $this->PracticeLocation->find('first', array('conditions' => array('PracticeLocation.location_id' => $user['work_location'])));
		$patient = $this->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id)));
		$medical_histories = ($this->PatientMedicalHistory->find('all', array('conditions' => array('PatientMedicalHistory.patient_id' => $patient_id))));
		$surgical_histories = ($this->PatientSurgicalHistory->find('all', array('conditions' => array('PatientSurgicalHistory.patient_id' => $patient_id))));
		$social_histories = ($this->PatientSocialHistory->find('all', array('conditions' => array('PatientSocialHistory.patient_id' => $patient_id))));
		$family_histories = ($this->PatientFamilyHistory->find('all', array('conditions' => array('PatientFamilyHistory.patient_id' => $patient_id))));
		$allergies = ($this->PatientAllergy->find('all', array('conditions' => array('PatientAllergy.patient_id' => $patient_id))));
		$problem_lists = ($this->PatientProblemList->find('all', array('conditions' => array('PatientProblemList.patient_id' => $patient_id))));
		$lab_results = ($this->EncounterPointOfCare->find('all', array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Labs')))));
		$radiology_results = ($this->EncounterPointOfCare->find('all', array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Radiology')))));
		$plan_procedures = ($this->EncounterPointOfCare->find('all', array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Procedure')))));
		$immunizations = ($this->EncounterPointOfCare->find('all', array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Immunization')))));
		$injections = ($this->EncounterPointOfCare->find('all', array('conditions' => array('AND' => array('EncounterPointOfCare.encounter_id' => $patient_encounter_id, 'EncounterPointOfCare.order_type' => 'Injection')))));
		$medication_lists = ($this->PatientMedicationList->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id))));
		$plan_referrals = ($this->EncounterPlanReferral->find('all', array('conditions' => array('EncounterPlanReferral.encounter_id' => $patient_encounter_id))));
		$plan_health_maintenance = ($this->EncounterPlanHealthMaintenance->find('all', array('conditions' => array('EncounterPlanHealthMaintenance.encounter_id' => $patient_encounter_id))));
		
		$patient_disclosure = array();
		for($ct = 0; $ct < 12; $ct++) {
			$patient_disclosure[$ct] = 'true';
		}
		
		
		
		if ( isset($this->params['named']['export_dump']) ) {
			$patient_disclosure[0] = 'true';
		}



		$ccr = new DOMDocument('1.0', 'UTF-8');
		$e_styleSheet = $ccr->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . $url . '"');
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

		$e_ActorID = $ccr->createElement('ActorID', $authorID);
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

		if ( $patient_disclosure[1] == "true" ) {
			/////////////// Medical Histories

			$e_MedicalHistories = $ccr->createElement('MedicalHistories');
			foreach ( $medical_histories as $medical_history ):

				$e_MedicalHistory = $ccr->createElement('MedicalHistory');
				$e_MedicalHistories->appendChild($e_MedicalHistory);

				$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
				$e_MedicalHistory->appendChild($e_CCRDataObjectID);

				if ( isset($medical_history['PatientMedicalHistory']['start_date']) ) {
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
			foreach ( $surgical_histories as $surgical_history ):

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
			foreach ( $social_histories as $social_history ):

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

				if ( $social_history['PatientSocialHistory']['type'] == 'Activities' ) {
					$e_Text = $ccr->createElement('Text', $social_history['PatientSocialHistory']['routine_status']);
				} else {
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
			foreach ( $family_histories as $family_history ):

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

		if ( $patient_disclosure[2] == "true" ) {
			/////////////// Allergies

			$e_Allergies = $ccr->createElement('Allergies');
			foreach ( $allergies as $allergy ):

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
				for ( $count = 1; $count <= $allergy['PatientAllergy']['reaction_count']; ++$count ) {
					if ( $allergy['PatientAllergy']['reaction' . $count] ) {
						if ( $reactions ) {
							$reactions .= ', ';
						}
						$reactions .= 'Reaction #' . $count . ': ' . $allergy['PatientAllergy']['reaction' . $count];
						if ( $allergy['PatientAllergy']['severity' . $count] ) {
							$reactions .= ' Severity: ' . $allergy['PatientAllergy']['severity' . $count];
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

		if ( $patient_disclosure[3] == "true" ) {
			/////////////// Problem Lists

			$e_Problems = $ccr->createElement('Problems');
			$pCount = 0;
			foreach ( $problem_lists as $problem_list )
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

		if ( $patient_disclosure[4] == "true" ) {
			/////////////// Labs

			$e_Labs = $ccr->createElement('Labs');
			foreach ( $lab_results as $lab_result ):

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

		if ( $patient_disclosure[5] == "true" ) {
			/////////////// Radiologies

			$e_Radiologies = $ccr->createElement('Radiologies');
			foreach ( $radiology_results as $radiology_result ):

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

				if ( isset($radiology_result['EncounterPointOfCare']['radiology_body_site']) ) {
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

		if ( $patient_disclosure[6] == "true" ) {
			/////////////// Procedures

			$e_Procedures = $ccr->createElement('Procedures');
			foreach ( $plan_procedures as $plan_procedure ):

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

		if ( $patient_disclosure[7] == "true" ) {
			/////////////// Immunizations

			$e_Immunizations = $ccr->createElement('Immunizations');
			foreach ( $immunizations as $immunization ):

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

		if ( $patient_disclosure[8] == "true" ) {
			/////////////// Injections

			$e_Injections = $ccr->createElement('Injections');
			foreach ( $injections as $injection ):

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

		if ( $patient_disclosure[9] == "true" ) {
			/////////////// Medical Lists

			$e_Medicals = $ccr->createElement('Medicals');
			$pCount = 0;
			foreach ( $medication_lists as $medication_list )
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

		if ( $patient_disclosure[10] == "true" ) {
			/////////////// Referrals

			$e_Referrals = $ccr->createElement('Referrals');
			foreach ( $plan_referrals as $plan_referral ):

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

		if ( $patient_disclosure[11] == "true" ) {
			/////////////// Health Maintenances

			$e_HealthMaintenances = $ccr->createElement('HealthMaintenances');
			foreach ( $plan_health_maintenance as $plan_health_maintenance ):

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

		if ( $patient_disclosure[0] == "true" ) {
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

		$e_Name = $ccr->createElement('Name', $user['firstname'] . " " . $user['lastname']);
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
		$e_State = $ccr->createElement('State', $location_state . ' ');
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

		$e_Line1 = $ccr->createElement('Line1', '2365 Springs Rd. NE');
		$e_Address->appendChild($e_Line1);

		$e_Line2 = $ccr->createElement('Line2');
		$e_Address->appendChild($e_Line1);

		$e_City = $ccr->createElement('City', 'Hickory');
		$e_Address->appendChild($e_City);

		$e_State = $ccr->createElement('State', 'NC ');
		$e_Address->appendChild($e_State);

		$e_PostalCode = $ccr->createElement('PostalCode', '28601');
		$e_Address->appendChild($e_PostalCode);

		$e_Telephone = $ccr->createElement('Telephone');
		$e_Actor->appendChild($e_Telephone);

		$e_Phone = $ccr->createElement('Value', '000-000-0000');
		$e_Telephone->appendChild($e_Phone);

		$e_Source = $ccr->createElement('Source');
		$e_Actor->appendChild($e_Source);

		$e_Actor = $ccr->createElement('Actor');
		$e_Source->appendChild($e_Actor);

		$e_ActorID = $ccr->createElement('ActorID', $authorID);
		$e_Actor->appendChild($e_ActorID);

		$ccr->preserveWhiteSpace = false;
		$ccr->formatOutput = true;
		$output = $ccr->saveXml();
		
		return $output;
	}
	
	public function searchCharts(&$controller)
	{
		$controller->loadModel("StateCode");
        	$controller->loadModel("PracticeLocation");
		$controller->set("StateCode", $controller->sanitizeHTML($controller->StateCode->find('all')));
        	$controller->set("OfficeLocations", $controller->sanitizeHTML($controller->PracticeLocation->find('all')));
	}
	
	public function searchChartsData(&$controller, $data) {
		$controller->PatientDemographic->unbindModelAll(false);

		$conditions = array();
		if ( isset($data['mrn']) && strlen($data['mrn']) > 0 ) {
			$conditions['PatientDemographic.mrn LIKE '] = '' . $data['mrn'] . '%';
		} else if ( isset($controller->params['named']['auto']) ) {
			if ( strlen($data['ssn']) > 0 ) {
				$conditions['PatientDemographic.ssn LIKE '] = '%' . $data['ssn'] . '%';
			}
		} else if (strlen($data['custom_patient_identifier']) > 0 ) {
			$conditions['PatientDemographic.custom_patient_identifier LIKE '] = '' . $data['custom_patient_identifier'] . '%';
		} else {
			if ( strlen($data['last_name']) > 0 ) {
				$conditions['PatientDemographic.last_name LIKE '] = '' . $data['last_name'] . '%';
			}

			if ( strlen($data['first_name']) > 0 ) {
				$conditions['PatientDemographic.first_name LIKE '] = '' . $data['first_name'] . '%';
			}

			if ( strlen($data['dob']) > 0 ) {
				$data['dob'] = __date("Y-m-d", strtotime($data['dob']));
				$conditions['PatientDemographic.dob'] = $data['dob'];
			}
			
			if ( strlen($data['gender']) > 0 && $data['gender']!='all') {
				$conditions['PatientDemographic.gender'] = $data['gender'];
			}
			
			if ( strlen($data['home_phone']) > 0 ) {
				$conditions['ceil(replace(PatientDemographic.home_phone, "-", ""))'] = str_replace("-","",$data['home_phone']);
			}
			
			if ( strlen($data['city']) > 0) {
				$conditions['PatientDemographic.city LIKE'] = '%'.$data['city'].'%';
			}
			
			if ( strlen($data['state']) > 0 && $data['state']!='all') {
				$conditions['PatientDemographic.state'] = $data['state'];
			}
			
			if ( strlen($data['zipcode']) > 0) {
				$conditions['PatientDemographic.zipcode'] = $data['zipcode'];
			}
			
			if ( strlen($data['address1']) > 0) {
				$conditions['PatientDemographic.address1 LIKE'] = '%'.$data['address1'].'%';
			}
			
			

		}

		if ( isset($data['location_id']) ) {
			$location_cond = '1';
			$tmpLocations = array();

			if ( strlen($data['location_id']) > 0 ) {
				$location_cond = "ScheduleCalendar.location = $data[location_id]";
			}

			
			$locations = $controller->PatientDemographic->query('
				SELECT ScheduleCalendar.* , PracticeLocation.location_name
				FROM (
					SELECT * FROM (  
						SELECT `calendar_id` , `location` , `patient_id`
						FROM `schedule_calendars`
						ORDER BY `date` DESC , `starttime` DESC
					) AS sc_ordered GROUP BY `patient_id`
				) AS ScheduleCalendar
				INNER JOIN practice_locations AS PracticeLocation ON ( ScheduleCalendar.location = PracticeLocation.location_id )
				WHERE ' . $location_cond . ' 					
			');
			
			//pr($locations);
			if ( !empty($locations) ) {
				foreach ( $locations as $location ) {
					$tmpLocations[$location['ScheduleCalendar']['patient_id']] = $location;
				}
			}
			if ( strlen($data['location_id']) > 0 ) {
				$patient_ids = Set::extract('/ScheduleCalendar/patient_id', $locations);
				$conditions['PatientDemographic.patient_id'] = $patient_ids;
			}
			$controller->Set('locations', $tmpLocations);
		}		
		

		// Let's do a rewrite on how patient status are handled
		// Make it more a little flexible and less verbose

		$status_array = array();
		// Get valid statuses
		$statusList = self::getStatusList();

		// Iterate throught the valid statuses
		foreach ( $statusList as $status ) {

			// Build index being used in data array
			// Ex: status_new, status_pending, and so on...
			$statusTmp = 'status_' . strtolower($status);

			// Check existence of index in data array
			// and store result in a variable with
			// the same name as the index used
			// $$ => is a variable variable (http://php.net/manual/en/language.variables.variable.php)
			$$statusTmp = isset($data[$statusTmp]) ? $data[$statusTmp] : '';
			if ( $$statusTmp == $status ) {
				array_push($status_array, $status);
			}
		}

		if ( count($status_array) > 0 ) {
			$conditions['CONVERT(DES_DECRYPT(PatientDemographic.status) USING latin1)'] = $status_array;
		}

		//see if using custom patient ID, if so, then define that column 
		if (!$data['custom_patient_identifier'])
		{
			$custom_pt=$controller->PatientDemographic->find('first', array('fields' => 'custom_patient_identifier', 'conditions' => array('PatientDemographic.custom_patient_identifier !=' => '')));
			$controller->set('custom_pt',$custom_pt);

		}
		else
		{
			$custom_pt = true;
		}
		$controller->set('custom_pt',$custom_pt);


	$controller->PatientDemographic->virtualFields['location_name'] = "CONVERT(PracticeLocation.location_name USING latin1)";


        $joins = array(
         array('table' => 'schedule_calendars', 'alias' => 'ScheduleCalendar', 'type' => 'LEFT', 'conditions' => array('ScheduleCalendar.patient_id = PatientDemographic.patient_id')),            
            array('table' => 'practice_locations','alias' => 'PracticeLocation', 'type' => 'LEFT', 'conditions' => array("PracticeLocation.location_id = ScheduleCalendar.location"))
        );



				$controller->paginate['PatientDemographic'] = array(
            'fields' => array(
                'PatientDemographic.patient_id', 
                'PatientDemographic.first_name',
                'PatientDemographic.last_name',
                'PatientDemographic.gender',
                'PatientDemographic.mrn',
                'PatientDemographic.dob',
                'PatientDemographic.home_phone',
                'PatientDemographic.cell_phone',
                'PatientDemographic.status',
                'PatientDemographic.location_name',
                'PatientDemographic.custom_patient_identifier',
            ),
					
			'conditions' => $conditions,
			'joins' => $joins,
			'order' => array('PatientDemographic.last_name' => 'asc', 'EncounterMaster.first_name' => 'asc'),
			'group' => array('PatientDemographic.patient_id'),
		);

		$result = $controller->paginate('PatientDemographic');
		$controller->set('patient_demographics', $controller->sanitizeHTML($result));
	}
	
    public function updateEmdeonPatient($patient_id, $check_only = false)
    {
        $emdeon_xml_api = new Emdeon_XML_API();
		$this->EmdeonSyncStatus = ClassRegistry::init('EmdeonSyncStatus');
        
        if($emdeon_xml_api->checkConnection())
        {
			$do_update = true;
			
			if($check_only)
			{
				if($this->EmdeonSyncStatus->isPatientSynced($patient_id))
				{
					$do_update = false;	
				}
			}
			
			if($do_update)
			{
				$patient = $this->getPatient($patient_id);
				
				$mrn = $patient['mrn'];
				$first_name = $patient['first_name'];
				$middle_name = $patient['middle_name'];
				$last_name = $patient['last_name'];
				$address1 = $patient['address1'];
				$address2 = $patient['address2'];
				$zip = $patient['zipcode'];
				$city = $patient['city'];
				$state = $patient['state'];
				$home_phone = $patient['home_phone'];
				$gender = $patient['gender'];
				$ssn = str_replace('-', '', $patient['ssn']);
				$date_of_birth = __date("m/d/Y", strtotime($patient['dob']));
				
				if(strlen($home_phone) > 0)
				{
					$home_phone_arr = explode("-", $home_phone);
					$home_phone_area_code = @$home_phone_arr[0];
					$home_phone_number = @$home_phone_arr[1] . @$home_phone_arr[2];
				}
				else
				{
					$home_phone_area_code = '';
					$home_phone_number = '';
				}
				
				$personhsi_result = $emdeon_xml_api->execute("personhsi", "search", array("hsi_value" => $mrn));
				
				if(isset($personhsi_result['xml']->OBJECT) && count($personhsi_result['xml']->OBJECT) > 0)
				{
					$person = trim((string)$personhsi_result['xml']->OBJECT[0]->person);
			
					//update
					$data = array();
					$data['address_1'] = $address1;
					$data['address_2'] = $address2;
					$data['birth_date'] = $date_of_birth;
					$data['city'] = $city;
					$data['first_name'] = $first_name;
					$data['home_phone_area_code'] = $home_phone_area_code;
					$data['home_phone_number'] = $home_phone_number;
					$data['last_name'] = $last_name;
					$data['middle_name'] = $middle_name;
					$data['person'] = $person;
					$data['sex'] = $gender;
					$data['ssn'] = $ssn;
					$data['state'] = $state;
					$data['zip'] = $zip;
			
					$update_result = $emdeon_xml_api->execute("person", "update_all", $data);
				}
				else
				{
					//add
					$data = array();
					$data['address_1'] = $address1;
					$data['address_2'] = $address2;
					$data['birth_date'] = $date_of_birth;
					$data['city'] = $city;
					$data['first_name'] = $first_name;
					$data['home_phone_area_code'] = $home_phone_area_code;
					$data['home_phone_number'] = $home_phone_number;
					$data['last_name'] = $last_name;
					$data['middle_name'] = $middle_name;
					$data['sex'] = $gender;
					$data['ssn'] = $ssn;
					$data['state'] = $state;
					$data['zip'] = $zip;
					
					$add_result = $emdeon_xml_api->execute("person", "add", $data);
								
					$person = '';
					if (isset($add_result['xml']->OBJECT) && count($add_result['xml']->OBJECT) > 0) {
						$person = trim((string)$add_result['xml']->OBJECT[0]->person);
					}
					
					//get hsilabel
					$hsilabel_result = $emdeon_xml_api->execute("hsilabel", "search", array("is_hsi_for" => "Patient", "label_name" => "PAN", "organization" => $emdeon_xml_api->facility));
					$hsilabel = '';
					if (isset($hsilabel_result['xml']->OBJECT) && count($hsilabel_result['xml']->OBJECT) > 0) {
						$hsilabel = trim((string)$hsilabel_result['xml']->OBJECT[0]->hsilabel);
					}
								
					//add personhsi
					$data = array();
					$data['active'] = "y";
					$data['hsi_value'] = $mrn;
					$data['hsilabel'] = $hsilabel;
					$data['person'] = $person;
					$add_personhsi_result = $emdeon_xml_api->execute("personhsi", "add", $data);
					
					//add personprovinfo
					$data = array();
					$data['bill_type'] = "T";
					$data['organization'] = $emdeon_xml_api->facility;
					$data['person'] = $person;
					$add_personprovinfo_result = $emdeon_xml_api->execute("personprovinfo", "add", $data);
				}
				
				$this->EmdeonSyncStatus->addSync('patient', $patient_id);
			}
        }
    }
	
	public function updateDosespotPatient($patient_id)
	{
		  $this->PracticeSetting = ClassRegistry::init('PracticeSetting');
                  $PracticeSetting= $this->PracticeSetting->getSettings();
                  $cl=$PracticeSetting->practice_id;
            //make sure Dosespot is enabled
            if( $PracticeSetting->rx_setup == 'Electronic_Dosespot' )
            {      
		 //cakelog::write('dosespot','CLIENT='.$cl.' exec updateDosespotPatient for patient_id='.$patient_id);

		$dosespot_xml_api = new Dosespot_XML_API();
		
		if($dosespot_xml_api->checkConnection())
		{
			$patient = $this->getPatient($patient_id);
			//cakelog::write('dosespot','CLIENT='.$cl.' our database says dosespot_patient_id='.$patient['dosespot_patient_id']);	
			$data = array();
			$data['dosespot_patient_id'] = $patient['dosespot_patient_id'];
			$data['mrn'] = $patient['mrn'];		
			$data['address_1'] = $patient['address1'];
			$data['address_2'] = $patient['address2'];
			$data['dob'] = __date("Y-m-d", strtotime($patient['dob']));
			$data['city'] = $patient['city'];
			$data['first_name'] = $patient['first_name'];
			$data['cell_phone_number'] = $patient['cell_phone'];
			$data['home_phone_number'] = $patient['home_phone'];
			$data['work_phone_number'] = $patient['work_phone'];
			$data['last_name'] = $patient['last_name'];
			$data['middle_name'] = $patient['middle_name'];
			$data['gender'] = ($patient['gender']=='M')?'Male':'Female';
			$data['state'] = $patient['state'];
			$data['zip'] = $patient['zipcode'];
			
			//if($patient['dosespot_patient_id']=="")
			//{
				$this->db_config = ClassRegistry::init('DATABASE_CONFIG');	
				$shellcommand="php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app '".APP."' dosespot_patientID ".$this->db_config->default['database']." ".$patient_id." ".escapeshellarg(serialize($data)) ."  >> /dev/null 2>&1 & ";
				exec($shellcommand);
				// moved this command below into the above shell script to avoid hanging and waiting.
				//$dosespot_patient_id = $dosespot_xml_api->getDosespotPatientID($patient_id, $data);
			
				/* if($dosespot_patient_id)
				{
					$data = array();
					$data['PatientDemographic']['patient_id'] = $patient_id;
					$data['PatientDemographic']['modified_timestamp'] = __date("Y-m-d H:i:s");
					$data['PatientDemographic']['dosespot_patient_id'] = $dosespot_patient_id;
					$this->save($data);
				} */
			//}
			//return 'id: '.$dosespot_patient_id;
			return true;
		}
	   }	
	}
	
	public function generateSQL()
	{
		$search_result = $this->find('all', array('recursive' => -1) );
		
		$ret = array();
		
		$start = 'INSERT INTO `patient_demographics` (`patient_id`, `patient_photo`, `driver_license`, `mrn`, `first_name`, `middle_name`, `last_name`, `gender`, `preferred_language`, `race`, `ethnicity`, `dob`, `ssn`, `driver_license_id`, `driver_license_state`, `occupation`, `marital_status`, `address1`, `address2`, `city`, `state`, `zipcode`, `immtrack_county`, `immtrack_country`, `work_phone`, `work_phone_extension`, `home_phone`, `cell_phone`, `email`, `guardian`, `relationship`, `immtrack_vfc`, `emergency_contact`, `emergency_phone`, `custom_patient_identifier`, `status`, `source_system_id`, `segment_code`, `allergies_none`, `problem_list_none`, `medication_list_none`, `modified_timestamp`, `modified_user_id`) VALUES';
		
		foreach($search_result as $item)
		{
			$ret[] = "({$item['PatientDemographic']['patient_id']}, DES_ENCRYPT('{$item['PatientDemographic']['patient_photo']}'), DES_ENCRYPT('{$item['PatientDemographic']['driver_license']}'), {$item['PatientDemographic']['mrn']}, DES_ENCRYPT('{$item['PatientDemographic']['first_name']}'), DES_ENCRYPT('{$item['PatientDemographic']['middle_name']}'), DES_ENCRYPT('{$item['PatientDemographic']['last_name']}'), DES_ENCRYPT('{$item['PatientDemographic']['gender']}'), DES_ENCRYPT('{$item['PatientDemographic']['preferred_language']}'), DES_ENCRYPT('{$item['PatientDemographic']['race']}'), DES_ENCRYPT('{$item['PatientDemographic']['ethnicity']}'), DES_ENCRYPT('{$item['PatientDemographic']['dob']}'), DES_ENCRYPT('{$item['PatientDemographic']['ssn']}'), DES_ENCRYPT('{$item['PatientDemographic']['driver_license_id']}'), DES_ENCRYPT('{$item['PatientDemographic']['driver_license_state']}'), DES_ENCRYPT('{$item['PatientDemographic']['occupation']}'), DES_ENCRYPT('{$item['PatientDemographic']['marital_status']}'), DES_ENCRYPT('{$item['PatientDemographic']['address1']}'), DES_ENCRYPT('{$item['PatientDemographic']['address2']}'), DES_ENCRYPT('{$item['PatientDemographic']['city']}'), DES_ENCRYPT('{$item['PatientDemographic']['state']}'), DES_ENCRYPT('{$item['PatientDemographic']['zipcode']}'), DES_ENCRYPT('{$item['PatientDemographic']['immtrack_county']}'), DES_ENCRYPT('{$item['PatientDemographic']['immtrack_country']}'), DES_ENCRYPT('{$item['PatientDemographic']['work_phone']}'), DES_ENCRYPT('{$item['PatientDemographic']['work_phone_extension']}'), DES_ENCRYPT('{$item['PatientDemographic']['home_phone']}'), DES_ENCRYPT('{$item['PatientDemographic']['cell_phone']}'), DES_ENCRYPT('{$item['PatientDemographic']['email']}'),  DES_ENCRYPT('{$item['PatientDemographic']['guardian']}'), DES_ENCRYPT('{$item['PatientDemographic']['relationship']}'), DES_ENCRYPT('{$item['PatientDemographic']['immtrack_vfc']}'), DES_ENCRYPT('{$item['PatientDemographic']['emergency_contact']}'), DES_ENCRYPT('{$item['PatientDemographic']['emergency_phone']}'), DES_ENCRYPT('{$item['PatientDemographic']['custom_patient_identifier']}'),  DES_ENCRYPT('{$item['PatientDemographic']['status']}'), DES_ENCRYPT('{$item['PatientDemographic']['source_system_id']}'), DES_ENCRYPT('{$item['PatientDemographic']['segment_code']}'), DES_ENCRYPT('{$item['PatientDemographic']['allergies_none']}'), DES_ENCRYPT('{$item['PatientDemographic']['problem_list_none']}'), DES_ENCRYPT('{$item['PatientDemographic']['medication_list_none']}'), '{$item['PatientDemographic']['modified_timestamp']}', {$item['PatientDemographic']['modified_user_id']})";
		}
		
		$str_query = implode(",<br>", $ret);
		
		$str_query = $start . "<br>" . $str_query;
		$str_query .= ';';
		
		return $str_query;
	}
	
	public function executePlanSummary(&$controller, $encounter_id, $patient_id, $task, $user_id)
	{
		switch ($task)
        {
            case "get_report_html":
            {
                $controller->layout = 'empty';
                
                if ($report = Visit_Summary::generatePlan($encounter_id))
                {
                    App::import('Helper', 'Html');
                    $html = new HtmlHelper();
                    
                    $url = $_SERVER['DOCUMENT_ROOT'] . $controller->webroot . 'app/webroot/patient_files' . '/';
                    $url = str_replace('//', '/', $url);
                    
                    $pdffile = 'encounter_' . $encounter_id . '_plansummary.pdf';
                    
                    //format report, by removing hide text
                    $reportmod = preg_replace('/(<span class="hide_for_print">.+?)+(<\/span>)/i', '', $report);
                    
                    
                    //PDF file creation
                    site::write(pdfReport::generate($reportmod), $url . $pdffile);
                    
                    echo $report;
                    
                    ob_flush();
                    flush();
                    
                    exit();
                }
                exit('could not generate report');
            }
            case "get_report_pdf":
            {
                $file = 'encounter_' . $encounter_id . '_plansummary.pdf';
                $folder = $controller->webroot . 'app/webroot/patient_files';
                $targetPath = $_SERVER['DOCUMENT_ROOT'] . $folder . '/';
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
            }
            break;
        }
	}
	
	public function executePocSummary(&$controller, $encounter_id, $patient_id, $task, $user_id)
	{
		switch ($task)
        {
            case "get_report_html":
            {
                $controller->layout = 'empty';
                
                if ($report = Visit_Summary::generatePOC($encounter_id))
                {
                    App::import('Helper', 'Html');
                    $html = new HtmlHelper();
                    
                    $url = $_SERVER['DOCUMENT_ROOT'] . $controller->webroot . 'app/webroot/patient_files' . '/';
                    $url = str_replace('//', '/', $url);
                    
                    $pdffile = 'encounter_' . $encounter_id . '_pocsummary.pdf';
                    
                    //format report, by removing hide text
                    $reportmod = preg_replace('/(<span class="hide_for_print">.+?)+(<\/span>)/i', '', $report);
                    
                    
                    //PDF file creation
                    site::write(pdfReport::generate($reportmod), $url . $pdffile);
                    
                    echo $report;
                    
                    ob_flush();
                    flush();
                    
                    exit();
                }
                exit('could not generate report');
            }
            case "get_report_pdf":
            {
                $file = 'encounter_' . $encounter_id . '_pocsummary.pdf';
                $targetPath = $controller->paths['temp'];
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
            }
            break;
        }
	}
        
        /**
         * Get types of status currently being used by the patient demographic
         * 
         * @return array List of valid status strings
         */
        public static function getStatusList() {
            return array("Pending", "New", "Active", "Inactive", "Deceased", "Suspended", "Deleted");
        }
				
	/**
	 * Get the list of valid fields that can be imported
	 * @return array Array of field names 
	 */
	public static  function getImportableFields(){
		return array(
			'first_name', 'middle_name', 'last_name', 'gender', 
			'preferred_language', 'race', 'ethnicity', 'dob', 'ssn', 
			'driver_license_id', 'driver_license_state', 'address1', 'address2', 
			'city', 'state', 'zipcode', 'immtrack_county', 'immtrack_country',
			'work_phone', 'work_phone_extension', 'home_phone', 'cell_phone', 'fax_number','email',
			'guardian', 'relationship', 'emergency_contact', 'custom_patient_identifier',
			'emergency_phone', 'driver_license', 'status'
		);
	}
	
	/**
	 * Handle Patient CSV import features
	 * 
	 * @param AppController $controller Controller handling the current model
	 */
	public function process_csv_upload(&$controller) {
		$task = (isset($controller->params['named']['task'])) ? $controller->params['named']['task'] : "";
		
		switch($task) {
			
			// Output CSV template for importing patient demographic data
			case "download_template":	{
				$fields = self::getImportableFields();
				
				$filename = "patient_template.csv";
				$csv_file = fopen('php://output', 'w');
			
				header('Content-type: application/csv');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				
				
				fputcsv($csv_file, $fields, ',', '"');
	
				fclose($csv_file);
				exit();
				
			} break;
		
		
			// Load import data from uploaded CSV file
			// and render it for viewing/moderation
			case 'load_import_data': {

				$importErrors = array();
				
				// Read file
				$filename = $controller->params['form']['csv'];
				$targetPath = $controller->paths['temp'];
				$filePath = $targetPath . $filename;
				$fhandle = fopen($filePath, 'r');
				
				// Get fields from csv
				$fields = fgetcsv($fhandle);
				
				if ($fields) {
					// Note valid fields
					$allowedFields = self::getImportableFields();


					foreach ($fields as $f) {
						if (!in_array($f, $allowedFields)) {
							$importErrors[] = 'Invalid field found ('. $f . ')';
							break;
						}
					}
					
				} else {
					$importErrors[] = 'Failed to parse CSV file';
				}
				
				
				if ($importErrors) {
					$controller->set(compact('importErrors'));
					echo $controller->render('patient_demographic_import', 'empty');
					exit();
				}
				
				$patientData = array();
				
				// Read patient data from csv file
				$ct = 1;
				while($pData = fgetcsv($fhandle)) {
					
					$pData = $this->__processImportPatientData($pData, $fields);
					
					if ($pData) {
						$pData['data_id'] = $ct++;
						$patientData[] = $pData;
					}
					
				}
				
				$cache = new Cache();
				
				Cache::write($filename, $patientData);
				
				
				$controller->loadModel('PatientImport');
				$controller->paginate['PatientImport'] = array(
					'limit' => 10 
				);
				$patientData = $controller->paginate('PatientImport', array(
					'filename' => $filename
				));
				
				$controller->set(compact('filename', 'patientData', 'importErrors'));
				echo $controller->render('patient_demographic_import', 'empty');
				
				exit();
				
			} break;
		
			
			case 'browse_import': {

				$filename = isset($controller->params['named']['file']) ? $controller->params['named']['file']: '';
				
				$controller->loadModel('PatientImport');
				$controller->paginate['PatientImport'] = array(
					'limit' => 10 
				);
				
				$imports = $controller->paginate('PatientImport', array(
					'filename' => $filename
				));
				
				
				$patientData = array();
				foreach ($imports as $i) {
						$fields = array_keys($i['details']);

						$pData = $this->__processImportPatientData($i['details'], $fields, true);
					
						$pData['data_id'] = $i['data_id'];
						$patientData[] = $pData;
					
					
				}
				
				$importErrors = array();
				
				$controller->set(compact('filename', 'patientData', 'importErrors'));
				
				
				echo $controller->render('patient_demographic_import', 'empty');
				
				exit();
				
			} break;			
			
			
			// 
			case 'import_patient_data' : {
				
				$pData = isset($controller->params['form']['pData']) ? $controller->params['form']['pData'] : array();
				
				$filename = isset($controller->params['form']['filename']) ? $controller->params['form']['filename'] : '';
				
				
				if (!$pData) {
					die('<div class="error">Data list is empty</div>');
				}
				
				if (!$filename) {
					die('<div class="error">Patient import file not specified</div>');
				}
				
				$patientData = Cache::read($filename);
				
				if (!$patientData) {
					die('<div class="error">Patient import file empty</div>');
				}
				
				$remainingData = array();
				foreach ($patientData as $p) {
					if (!in_array($p['data_id'], $pData)) {
						$remainingData[] = $p;
						continue;
					}
					
					if (!$p['importable']) {
						$remainingData[] = $p;
						continue;
					}
					
					$data = array(
						'PatientDemographic' => $p['details']
					);
					
					$data['PatientDemographic']['mrn'] = 0; //Dummy value assigned to MRN initially
					
					$this->create();
					$this->save($data);
					$patient_id = $this->getLastInsertID();
					
					if($patient_id)	{
						// Add PracticeSetting start mrn #
						$PracticeSetting = $controller->Session->read("PracticeSetting");
						$data['PatientDemographic']['mrn'] = $patient_id + $PracticeSetting['PracticeSetting']['mrn_start'];
						$this->save($data);
						
						$this->updateEmdeonPatient($patient_id);
						$this->updateDosespotPatient($patient_id);
						$this->saveAudit('New');
					}
				}
				
				Cache::write($filename, $remainingData);
				
				die('<div class="success">Successfully imported selected patients</div><br /><br />');
				
				
				exit();
				
			} break;
			
			case 'mass_import' : {
				
				$filename = isset($controller->params['form']['filename']) ? $controller->params['form']['filename'] : '';
				$bulk = isset($controller->params['form']['bulk']) ? $controller->params['form']['bulk'] : '';
				
				if (!$filename) {
					die('<div class="error">Patient import file not specified</div>');
				}

				if (!in_array($bulk, array('all', 'unique'))) {
					die('<div class="error">Mass action not specified</div>');
				}
				
				
				$patientData = Cache::read($filename);
				
				if (!$patientData) {
					die('<div class="error">Patient import file empty</div>');
				}

				ini_set('max_execution_time', 0);
				
				
				// Filter duplicates first
				if ($bulk === 'unique') {
					
					$forImport = array();
					
					foreach ($patientData as $p) {

						if (!$p['importable']) {
							continue;
						}

						// Check if duplicate exists
						$patient = $this->find('first',
								array(
										'fields' => array('patient_id', 'first_name', 'last_name'),
										'recursive' => -1,
										'conditions' => array(
										'AND' => array(
												'PatientDemographic.first_name' => $p['details']['first_name'], 
												'PatientDemographic.last_name' => $p['details']['last_name'], 
												'PatientDemographic.dob' => 
													__date("Y-m-d", strtotime(str_replace("-", "/", $p['details']['dob'])))
									))
								));

						// Skip saving if there is duplicate
						if($patient) {
							continue;
						}

						$forImport[] = $p;
					}					
					$patientData = $forImport;
				}
				
				// Start insertion
				foreach ($patientData as $p) {
					
					$data = array(
						'PatientDemographic' => $p['details']
					);
					
					$data['PatientDemographic']['mrn'] = 0; //Dummy value assigned to MRN initially
					
					$this->create();
					$this->save($data);
					$patient_id = $this->getLastInsertID();
					
					if($patient_id)	{
						// Add PracticeSetting start mrn #
						$PracticeSetting = $controller->Session->read("PracticeSetting");
						$data['PatientDemographic']['mrn'] = $patient_id + $PracticeSetting['PracticeSetting']['mrn_start'];
						$this->save($data);
						
						$this->updateEmdeonPatient($patient_id);
						$this->updateDosespotPatient($patient_id);
						$this->saveAudit('New');
					}
				}
				
				Cache::delete($filename);
				
				die('<div class="success">Successfully imported patients</div><br /><br />');
				
				
				exit();
				
			} break;
			
			default:
				break;
		}
	}
	
	/**
	 * Format and check patient data for import
	 * 
	 * @param array $patientData Patient data
	 * @param array $fields Fields to be used
	 * @return array formatted/processed patient data
	 */
	private function __processImportPatientData ($patientData, $fields, $checkDuplicates = false) {
		$patient = array();
	
		foreach ($fields as $key => $val) {
			if (isset($patientData[$key])) {
				
				$dataVal = trim($patientData[$key]);
				
				if ($dataVal) {
					$patient[$val] = $dataVal;
				}
			} else if (isset($patientData[$val])) {
				$dataVal = trim($patientData[$val]);
				
				if ($dataVal) {
					$patient[$val] = $dataVal;
				}
				
			}
			
			
		}
		
		if (empty($patient)) {
			return $patient;
		}
		
		if (isset($patient['dob'])) {
			$patient['dob'] = __date("Y-m-d", strtotime(str_replace("-", "/", $patient['dob'])));
		}
		
		$info = array(
			'details' => $patient,
			'errors' => array(),
			'importable' => true,
		);

		// Check if first name, last name and dob exists
		if (!(isset($patient['first_name']) && isset($patient['last_name']) && isset($patient['dob']))) {
			$info['errors'][] = 'Missing required field. Cannot be imported.';
			$info['importable'] = false;
			return $info;
		}

		if ($checkDuplicates) {
			$patient = $this->find('first',
							array(
									'fields' => array('patient_id', 'first_name', 'last_name'),
									'recursive' => -1, 
									'conditions' => array(
									'AND' => array(
											'PatientDemographic.first_name' => $patient['first_name'], 
											'PatientDemographic.last_name' => $patient['last_name'], 
											'PatientDemographic.dob' => 
												__date("Y-m-d", strtotime(str_replace("-", "/", $patient['dob'])))
								))
							));
			
		} else {
			$patient = array();
		}
		
		if ($patient) {
			
			$html = new HtmlHelper();
			
			$url = $html->link('Duplicate patient found', array(
				'controller' => 'patients', 
				'action' => 'index',
				'task' => 'edit',
				'patient_id' => $patient['PatientDemographic']['patient_id'],
				), 
				array(
					'target' => '__blank'
				)
			);
			
			$info['errors'][] = $url;
		}
		
		
		
		
		return $info;
	}

	public function download_csv_dump(&$controller,$file_id,$task) {
                        $file = $file_id.'.csv';
                        
                	$targetPath = $controller->paths['temp'];
                	$targetFile = str_replace('//', '/', $targetPath) . $file; 
           	                       
                        if($task == 'process_csv_dump')
                        {
                        	//move to background and let shell take care of this task
				$this->db_config = ClassRegistry::init('DATABASE_CONFIG');	
				$shellcommand="php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app '".APP."' patient_demographic_csv_export ".$this->db_config->default['database']." ".$file."   >> /dev/null 2>&1 & ";
				exec($shellcommand); 
				exit;	
                	} 
                        else if($task == 'wait_for_result')
                        {
                          	if(is_file( $targetFile )) { return "file is found";}
                          	exit;
                        }
                        else if ($task == 'download_dump')
                        {
                		if(is_file( $targetFile ))
                		{
                			header('Content-Type: application/csv; name="' . $file . '"');
                			header('Content-Disposition: attachment; filename="' . $file . '"');
                			header('Accept-Ranges: bytes');
                			header('Pragma: no-cache');
                			header('Expires: 0');
                			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                			header('Content-transfer-encoding: binary');
                			header('Content-length: ' . @filesize($targetFile));
                			@readfile($targetFile);
                					
                		} 
                        	exit;	
                        }
	}

	public function download_ccr_dump(&$controller,$file_id,$task) {
                        $file = $file_id.'.zip';
                        
                	$targetPath = $controller->paths['temp'];
                	$targetFile = str_replace('//', '/', $targetPath) . $file; 
           	                       
                        if($task == 'process_ccr_dump')
                        {
                        	//move to background and let shell take care of this task
				$this->db_config = ClassRegistry::init('DATABASE_CONFIG');	
				
				$protocol = 'http://';
				if ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
					|| $_SERVER['SERVER_PORT'] == 443 ) {

					$protocol = 'https://';
				}

				$ccr = new DOMDocument('1.0', 'UTF-8');
				$url = $protocol . $_SERVER['HTTP_HOST'] . str_replace("index.php", "", $_SERVER['PHP_SELF']) . 'ccr/patient_ccr.xsl';
				
				$shellcommand="php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app '".APP."' patient_demographic_ccr_export ".$this->db_config->default['database']." ".$file. " " . $controller->Session->read('UserAccount.user_id') . ' "' . $url . '" '   . "   >> /dev/null 2>&1 & ";
				exec($shellcommand); 
				exit;	
                	} 
                        else if($task == 'wait_for_result')
                        {
                          	if(is_file( $targetFile )) { echo "file is found";}
                          	exit;
                        }
                        else if ($task == 'download_dump')
                        {
                		if(is_file( $targetFile ))
                		{
                			header('Content-Type: application/octet-stream ; name="' . $file . '"');
                			header('Content-Disposition: attachment; filename="' . $file . '"');
                			header('Accept-Ranges: bytes');
                			header('Pragma: no-cache');
                			header('Expires: 0');
                			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                			header('Content-transfer-encoding: binary');
                			header('Content-length: ' . @filesize($targetFile));
                			@readfile($targetFile);
                					
                		} 
                        	exit;	
                        }
	}
	
	/*
	* increment the amount of times used for ranking purposes
	*/
	public function updateCitationCount($patient_id)
	{
		$this->recursive = -1;
		$items=$this->find('first',array('conditions' => array('PatientDemographic.patient_id' => "$patient_id"), 'recursive' => -1));
		if($items['PatientDemographic']['patient_id'])
		{
		  $data['PatientDemographic']['patient_id']=$items['PatientDemographic']['patient_id'];
		  $data['PatientDemographic']['citation_count']=$items['PatientDemographic']['citation_count'] + 1;
		  $this->save($data);
		}
	}	
}

?>
