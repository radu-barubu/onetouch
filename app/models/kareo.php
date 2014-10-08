<?php

class Kareo extends AppModel 
{ 
    public $name = 'Kareo'; 
    public $useTable = false;
    public $cache_file_prefix;
	public $kareo_debug;
	
	function Kareo()
	{
        	/*Prepare cache file prefix for multiple host*/
        	$db_config = $this->getDataSource()->config;
        	$this->cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
		$this->kareo_debug = Configure::read('debug');
		if(!$this->client()) //can't get Soap started
		{
		 die();
		}
	}
	function client()
	{
		$this->settings = ClassRegistry::init('PracticeSetting')->find('first', array('fields' => array('kareo_status', 'kareo_user', 'kareo_password', 'kareo_customer_key', 'kareo_practice_name', 'kareo_schedule_adjust_time', 'kareo_encounter_lock', 'practice_id', 'emdeon_facility', 'labs_setup', 'mrn_start')));
		$wsdl = 'https://webservice.kareo.com/services/soap/2.1/KareoServices.svc?wsdl';
		if($this->settings['PracticeSetting']['kareo_status'] && $this->connectionCheck($wsdl)) {
			$user = $this->settings['PracticeSetting']['kareo_user'];
			$password = $this->settings['PracticeSetting']['kareo_password'];
			$customerKey = $this->settings['PracticeSetting']['kareo_customer_key'];
			$this->RequestHeader = array('User' => $user, 'Password' => $password, 'CustomerKey' => $customerKey);
			$this->client = new SoapClient($wsdl);
			return $this->client;
		} else {
			return false;
		}		
	}
	private function connectionCheck($url)
        {
                $cacheKey='kareo_connection_check';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_TIMEOUT, 7);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $data = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if($httpcode>=200 && $httpcode<300){
                   Cache::delete($cacheKey);
                   return true;
                } else {
                   //unable to connect, or error
                   Cache::set(array('duration' => '+1 hour'));
                  $kareo_connection_check = Cache::read($cacheKey);
                  if(!$kareo_connection_check)
                  {
                   $err = "cURL Kareo Connection check error returned httpcode of ".$httpcode." \n\n". $data;
                   Cache::set(array('duration' => '+1 hour'));
                   Cache::write($cacheKey, date('n/j/Y h:i:s A'));
                   $this->mailToSupport('Connection Check', $err);
                  }
                   return false;
                }
        }
	function testConnection($user, $password, $customerKey)
	{
		$RequestHeader = array('User' => $user, 'Password' => $password, 'CustomerKey' => $customerKey);
		$params = array('request' => array('RequestHeader' => $RequestHeader), 'Fields' => array('PracticeName' => true));
		$wsdl = 'https://webservice.kareo.com/services/soap/2.1/KareoServices.svc?wsdl';
		$client = new SoapClient($wsdl);
		$reponse = $client->GetPractices($params)->GetPracticesResult;
		$result = $reponse->SecurityResponse->SecurityResult;
		return array('isValid' => $reponse->SecurityResponse->Authenticated, 'message' => $result);
	}
	function getParams($req)
	{
		$req['RequestHeader'] = $this->RequestHeader;
		$req['Practice'] = array('PracticeName' => $this->settings['PracticeSetting']['kareo_practice_name']);
		return array('request' => $req);
	}
	function import()
	{
		global $argv;	
		// Kareo uses Pacific Time zone
		$userTimezone = new DateTimeZone('America/Los_Angeles');
		$PacificDateTime = new DateTime('now', $userTimezone);

                $ToLastModifiedDate = $PacificDateTime->format('n/j/Y h:i:s A');

		//get last cron time and use as from modified date
		Cache::set(array('duration' => '+1 day'));
		$import_patient_time_stamp = Cache::read($this->cache_file_prefix.'kareo_import_patient_time_stamp');
                $PacificDateTime->modify('-1 day'); // go back this far on the FROM date (next line) if no cache
                $FromDate=$PacificDateTime->format('n/j/Y h:i:s A');
                $FromLastModifiedDate = (!empty($import_patient_time_stamp)) ? $import_patient_time_stamp : $FromDate;

		//tack on 1 second and cache it for next CRON as FromLastModifiedDate
		$addseconds=date('n/j/Y h:i:s A',strtotime('+1 seconds',strtotime($ToLastModifiedDate))); 
		Cache::set(array('duration' => '+1 day'));
        	Cache::write($this->cache_file_prefix.'kareo_import_patient_time_stamp', $addseconds );

		$request = array (
		   'Fields' => array('ID' => true),
		   'Filter' => array('FromLastModifiedDate' => $FromLastModifiedDate, 'ToLastModifiedDate' => $ToLastModifiedDate),
		  // 'Filter' => array('FullName' => 'Noah Abbate'),
		); 
		if(isset($argv[5]) && $argv[5]=='import_all') {
			unset($request['Filter']);
		}

		if($this->kareo_debug) {
			echo "req: import -> from: ".$FromLastModifiedDate." to: ".$ToLastModifiedDate." \n" ;
		}
		$params = $this->getParams($request);
		try {
			$Patients = $this->client->GetPatients($params)->GetPatientsResult->Patients;
			if(empty($Patients)) {
				echo '0 Patient\'s record(s) imported',"\n";
				return;
			} 
			$response = $Patients->PatientData;
		} catch (Exception $err) {
           	   $err = $err->getMessage();
		  if($this->kareo_debug) { echo "import error: ".$err. "\n"; }
		   $this->mailToSupport('GetPatients', $err);
		   return;
        }
		$response = Set::reverse($response);
		$patientIds = Set::extract('n/ID', $response);
		if($this->kareo_debug) { pr($patientIds);}
		$this->PatientDemographic = ClassRegistry::init('PatientDemographic');
		$this->PatientInsurance = ClassRegistry::init('PatientInsurance');
		$this->ScheduleCalendar = ClassRegistry::init('ScheduleCalendar');
		$this->PracticeLocation = ClassRegistry::init('PracticeLocation');
		$this->UserAccount = ClassRegistry::init('UserAccount');
		$this->PatientSocialHistory = ClassRegistry::init('PatientSocialHistory');
		$this->PatientPreference = ClassRegistry::init('PatientPreference');
		$this->ImmtrackCountry = ClassRegistry::init('ImmtrackCountry');
		$this->emdeon_xml_api = new Emdeon_XML_API();
		$this->MessagingMessage = ClassRegistry::init('MessagingMessage');
		
		if($this->settings['PracticeSetting']['labs_setup'] == 'Electronic' && $this->emdeon_xml_api->checkConnection()) 
		{
			$search_options = array('type' => 'self', 'name' => '**', 'address' => '', 'city' => '', 'state' => '',	'hsi_value' => '');
			$this->allInsurance = $this->emdeon_xml_api->searchInsurance($search_options);
			$this->insuranceNames = Set::extract('n/name', $this->allInsurance);
			$this->insuranceNames = array_map('strtolower', $this->insuranceNames);
		}		
		//EMR_Roles::SYSTEM_ADMIN_ROLE_ID;
		$adminAcc = $this->UserAccount->find('first', array(
			'conditions' => array('role_id' => 10), 'fields' => array('user_id'), 'recursive' => -1
		));
		$this->adminId = $adminAcc['UserAccount']['user_id'];
		//$patientIds = array(2);
		$this->importPatientCount = 0;
		if(!empty($patientIds)) {
			foreach($patientIds as $id) {
				if(empty($id)) continue;
				$request = array (
				   'Filter' => array('PatientID' => $id),
				);
				$params = $this->getParams($request);
				try {
					$response = $this->client->GetPatient($params)->GetPatientResult;
				} catch (Exception $err) {
				   $err = $err->getMessage();
				   if($this->kareo_debug) {echo "import error:".$err. "\n"; }
				   $this->mailToSupport('GetPatient', $err);
				   continue; // if error on geting patient data continue to next patient
				}
				$response = Set::reverse($response);
				if($this->kareo_debug) { pr($response);}
				$this->importDemographic($response);
			}
		}
		echo "\n".$this->importPatientCount.' Patient\'s record(s) imported',"\n";
		
	}
	
	function importDemographic($data)
	{
		$patient = $data['Patient'];
		$demographicData = array(
			'first_name' => $patient['FirstName'],
			'middle_name' => $patient['MiddleName'],
			'last_name' => $patient['LastName'],
			'gender' => $this->gender($patient['Gender'], true),
			'dob' => $this->formatDate($patient['DOB'], true),
			'ssn' => $patient['SSN'],
			'address1' => $patient['AddressLine1'],
			'address2' => $patient['AddressLine2'],
			'city' => $patient['City'],
			'state' => $patient['State'],
			'zipcode' => $patient['ZipCode'],
			'immtrack_country' => $this->country($patient['Country']),
			'work_phone' => $this->formatPhone($patient['WorkPhone'], true),
			'home_phone' => $this->formatPhone($patient['HomePhone'], true),
			'cell_phone' => $this->formatPhone($patient['MobilePhone'], true),
			'emergency_contact' => $patient['EmergencyName'],
			'emergency_phone' => $this->formatPhone($patient['EmergencyPhone'], true),			
			'kareo_id' => $patient['ID'],
			'modified_timestamp' => __date("Y-m-d H:i:s"),
			'modified_user_id' => $this->adminId,
			'custom_patient_identifier' => $patient['ID'],
			'email' => $patient['EmailAddress'],
		);
		//pr($demographicData);
		$checkChartNum = $this->PatientDemographic->find('first', array('fields' => array('patient_id', 'mrn'), 'conditions' => array('kareo_id' => $patient['ID']), 'callbacks' => false, 'recursive' => -1));
				
		if(isset($checkChartNum['PatientDemographic']['patient_id']) && $checkChartNum['PatientDemographic']['patient_id']) {
			$demographicData['patient_id'] = $checkChartNum['PatientDemographic']['patient_id'];
			$demographicData['mrn'] = $checkChartNum['PatientDemographic']['mrn'];
		} else {
			//$mrn = $this->PatientDemographic->find('first', array('fields' => array('max(mrn) as newMRN'), 'callbacks' => false, 'recursive' => -1));
			$demographicData['mrn'] = 0;
			$demographicData['status'] = 'New';			
		}
		$this->demographicData = $demographicData;
		//pr($this->demographicData );
		$_SESSION['UserAccount']['user_id'] = $this->adminId;
		$this->PatientDemographic->save($demographicData);
		$this->patientId = $this->PatientDemographic->id;
		$this->messageSent = 0;
		if($this->patientId)
			$this->importPatientCount++;
		if(empty($checkChartNum) && $demographicData['mrn'] == 0)
		{
			// update MRN to new patient
			$newMRN = $this->patientId + $this->settings['PracticeSetting']['mrn_start'];
			$this->PatientDemographic->save(array('mrn' => $newMRN));
			// update MRN into Kareo
			$params = array('UpdatePatientReq' => array(
				'Patient' => array(
					'MedicalRecordNumber' => $newMRN, 'PatientID' => $patient['ID'], 'FirstName' => $patient['FirstName'], 
					'Practice' => array('PracticeName' => $this->settings['PracticeSetting']['kareo_practice_name'])
				),
				'RequestHeader' => $this->RequestHeader
			));
			try {
				$response = $this->client->UpdatePatient($params);
				if($this->kareo_debug && $response->UpdatePatientResult->ErrorResponse->IsError)
					echo strip_tags($response->UpdatePatientResult->ErrorResponse->ErrorMessage), "\n";
			} catch (Exception $err) {
			   echo $err = $err->getMessage();
			}
		}
		// patient preference
		if($patient['PrimaryCarePhysicianFullName']) {
			$expPro = explode(',', $patient['PrimaryCarePhysicianFullName']);
			$proName = explode(' ', $expPro[0]);
			$proFname = $proName[1];
			$proLname = $proName[2];
			$provider = $this->UserAccount->find('first', array(
			   'conditions' => array('firstname' => $proFname, 'lastname' => $proLname), 'fields' => array('user_id'),'recursive' => -1
			));
			$preference_id = $this->PatientPreference->field('preference_id', array('PatientPreference.patient_id' =>$this->patientId));
			$prefData = array('patient_id' => $this->patientId, 'pcp' => $provider['UserAccount']['user_id']);
			if($preference_id)
				$prefData['preference_id'] = $preference_id;
			$this->PatientPreference->save($prefData);
			unset($this->PatientPreference->id);
		}
		// import patient's policies
		$PatientCaseData = isset($patient['Cases']['PatientCaseData'])? $patient['Cases']['PatientCaseData']:'';
		$allCase = array();
		if(!empty($PatientCaseData)) {			
			if(isset($PatientCaseData['InsurancePolicies']))
				$allCase[] = $PatientCaseData;
			elseif(isset($PatientCaseData[0]['InsurancePolicies']))
				$allCase = $PatientCaseData;
			//pr($allCase);
			foreach($allCase as $case) {
				if(isset($case['InsurancePolicies']['PatientInsurancePolicyData'])) {
					$PatientInsurancePolicyData = $case['InsurancePolicies']['PatientInsurancePolicyData'];
					if(isset($PatientInsurancePolicyData['CompanyID'])) {
						$this->importPolicy($PatientInsurancePolicyData);
					} else {
						//pr($PatientInsurancePolicyData);exit;
						foreach($PatientInsurancePolicyData as $policyData) {
							$this->importPolicy($policyData);
						}
					}
				}
			}
		}
		unset($this->PatientDemographic->id);
	}
	
	function importPolicy($policy)
	{				
		if(is_array($policy)===false) {
			return;
		}
		//pr($policy);
		$relationship = $this->PatientInsurance->EmdeonRelationship->field(
			'code', array('description' => $this->InsurRelation($policy['PatientRelationshipToInsured']))
		);
		@list($title, $firstName, $middleName, $lastName, $suffix) = explode(' ', $policy['InsuredFullName']);
		$insuranceData = array(
			'patient_id' => $this->patientId,  
			//'priority' => $this->InsurPriority($policy['Priority'], true),
			'group_id' => $policy['GroupNumber'],
			'copay_amount' => $policy['Copay'],
			'start_date' => $this->formatDate($policy['EffectiveStartDate'], true),
			'end_date' => $this->formatDate($policy['EffectiveEndDate'], true),
			'insurance_code' => $policy['InsuredIDNumber'],
			//'type' => $policy['Insurance']['Type'],
			'payer' => $policy['CompanyName'],
			'policy_number' => $policy['Number'],
			'relationship' => $relationship,
			'insured_first_name' => $firstName,
			'insured_middle_name' => $middleName,
			'insured_last_name' => $lastName,
			'insured_name_suffix' => $suffix,
			'insured_address_1' => $policy['InsuredAddressLine1'],
			'insured_address_2' => $policy['InsuredAddressLine2'],
			'insured_city' => $policy['InsuredCity'],
			'insured_state' => $policy['InsuredState'],
			'insured_zip' => $policy['InsuredZipCode'],
			'insured_home_phone_number' => $this->formatPhone($policy['PlanPhoneNumber'], true),
			'insured_work_phone_number' => $this->formatPhone($this->demographicData['work_phone'], true),
			'insured_sex' => $policy['InsuredGender'],
			'insured_birth_date' => $this->formatDate($policy['InsuredDateOfBirth'], true),
			'insured_ssn' => $policy['InsuredSocialSecurityNumber'],
			'plan_name' => $policy['PlanName'],
			'ownerid' => $this->settings['PracticeSetting']['emdeon_facility'],
			'kareo_insurance_id' => $policy['InsurancePolicyID'],
			'modified_timestamp' => __date("Y-m-d H:i:s"),
			'modified_user_id' => $this->adminId
		);
		if($policy['PatientRelationshipToInsured']=='S') {
			$insuranceData['insured_first_name'] = $this->demographicData['first_name'];
			$insuranceData['insured_middle_name'] = $this->demographicData['middle_name'];
			$insuranceData['insured_last_name'] = $this->demographicData['last_name'];
			$insuranceData['insured_address_1'] = $this->demographicData['address1'];
			$insuranceData['insured_address_2'] = $this->demographicData['address2'];
			$insuranceData['insured_city'] = $this->demographicData['city'];
			$insuranceData['insured_state'] = $this->demographicData['state'];
			$insuranceData['insured_zip'] = $this->demographicData['zipcode'];
			$insuranceData['insured_work_phone_number'] = $this->demographicData['work_phone'];
			$insuranceData['insured_sex'] = $this->demographicData['gender'];
			$insuranceData['insured_birth_date'] = $this->demographicData['dob'];
			$insuranceData['insured_ssn'] = $this->demographicData['ssn'];
		}
		$matchedKey = array_search(strtolower($policy['CompanyName']), (isset($this->insuranceNames) && !empty($this->insuranceNames))? $this->insuranceNames: array());
		$person = '';
		if($matchedKey >= 0)
		{
			$emdeon_xml_api = new Emdeon_XML_API();
			$person = $emdeon_xml_api->getPersonByMRN($this->demographicData['mrn']);
			//pr($person);exit;
			if(empty($person) && $this->messageSent == 0) {
				// turned off for now. not working well enough
				//$this->sendMessage($this->demographicData['first_name'].' '.$this->demographicData['last_name'], $this->demographicData['dob'], $this->demographicData['mrn']);
				$this->messageSent = 1;
			}
			$matchedInsurance = $this->allInsurance[$matchedKey];
			$insuranceData['isphsi'] = $matchedInsurance['isphsi'];
			$insuranceData['priority'] = 'Primary';
			$insuranceData['group_name'] = '';
			$insuranceData['employer_name'] = '';
			$insuranceData['insured_employee_id'] = '';
			$insuranceData['insured_employment_status'] = '';
			$insuranceData['isp'] = $matchedInsurance['isp'];
			$insuranceData['person'] = $person;
			$insuranceData['plan_identifier'] = '';
			$insuranceData['insurance_card_front'] = '';
			$insuranceData['insurance_card_back'] = '';
			$insuranceData['type'] = '';
			$insuranceData['payment_type'] = '';
			$insuranceData['copay_percentage'] = '';
			$insuranceData['status'] = '';
			$insuranceData['texas_vfc_status'] = '';
			$insuranceData['notes'] = '';
		}
		
		$existing_insurance_info = $this->PatientInsurance->find('first', array(
			'fields' => array('insurance_info_id', 'insurance'), 'conditions' => array('patient_id' => $this->patientId, 'kareo_insurance_id' => $policy['InsurancePolicyID']), 'recursive' => -1,
		));
		//pr($existing_insurance_info);
		if(!empty($existing_insurance_info)) {
			$insuranceData['insurance_info_id'] = $existing_insurance_info['PatientInsurance']['insurance_info_id'];
			$insuranceData['insurance'] = $existing_insurance_info['PatientInsurance']['insurance'];
		} 
		//pr($insuranceData);	
		
		if($matchedKey >= 0 && $person && $this->settings['PracticeSetting']['labs_setup'] == 'Electronic' && $this->emdeon_xml_api->checkConnection())
		{		
			$savedata['PatientInsurance'] = $insuranceData;
			$this->PatientInsurance->saveInsurance($savedata);
		} 
		else 
		{			
			$insurSaved = $this->PatientInsurance->save($insuranceData, array('callbacks' => false));		
			unset($this->PatientInsurance->id);
		}		
	}
	
	public function bill($patient_id, $demographic, $encounter_id='')
	{
		App::import('Sanitize');
		$this->Superbill = ClassRegistry::init('EncounterSuperbill');
		$this->Superbill->EncounterMaster->unbindModelAll();
		
		$this->Superbill->EncounterMaster->bindmodel(array(			
			'hasMany' =>array(
				'EncounterImmunization' => array(
					'className' => 'EncounterPointOfCare',
					'foreignKey' => 'encounter_id',
					'conditions' => array('EncounterImmunization.order_type' => 'Immunization'),
					'fields' => array('vaccine_name', 'vaccine_reason', 'vaccine_date_performed', 'vaccine_expiration_date', 'fee', 'vaccine_comment', 'cpt', 'cpt_code') 
				),
				'EncounterLabs' => array(
					'className' => 'EncounterPointOfCare',
					'foreignKey' => 'encounter_id',
					'conditions' => array('EncounterLabs.order_type' => 'Labs'),
					'fields' => array('lab_test_name', 'lab_reason', 'lab_date_performed', 'lab_test_result', 'lab_unit', 'fee', 'lab_comment', 'cpt', 'cpt_code') 
				),
				'EncounterRadiology' => array(
					'className' => 'EncounterPointOfCare',
					'foreignKey' => 'encounter_id',
					'conditions' => array('EncounterRadiology.order_type' => 'Radiology'),
					'fields' => array('radiology_procedure_name', 'radiology_reason', 'radiology_date_performed', 'radiology_comment', 'fee', 'cpt', 'cpt_code') 
				),
				'EncounterProcedure' => array(
					'className' => 'EncounterPointOfCare',
					'foreignKey' => 'encounter_id',
					'conditions' => array('EncounterProcedure.order_type' => 'Procedure'),
					'fields' => array('procedure_name', 'procedure_reason', 'procedure_comment', 'procedure_date_performed', 'procedure_unit', 'fee', 'cpt', 'cpt_code','modifier') 
				),
				'EncounterMed' => array(
					'className' => 'EncounterPointOfCare',
					'foreignKey' => 'encounter_id',
					'conditions' => array('EncounterMed.order_type' => 'Meds'),
					'fields' => array('drug', 'rxnorm', 'drug_reason', 'quantity', 'unit', 'drug_date_given', 'drug_given_time', 'drug_comment', 'fee', 'cpt', 'cpt_code') 
				),
				'EncounterInjection' => array(
					'className' => 'EncounterPointOfCare',
					'foreignKey' => 'encounter_id',
					'conditions' => array('EncounterInjection.order_type' => 'Injection'),
					'fields' => array('injection_name', 'injection_reason', 'injection_unit', 'injection_date_performed', 'fee', 'cpt', 'cpt_code') 
				),
				'EncounterSupply' => array(
					'className' => 'EncounterPointOfCare',
					'foreignKey' => 'encounter_id',
					'conditions' => array('EncounterSupply.order_type' => 'Supplies'),
					'fields' => array('supply_name', 'supply_quantity', 'fee', 'cpt', 'cpt_code') 
				),
				'EncounterAssessment' => array(
					'className' => 'EncounterAssessment',
					'foreignKey' => 'encounter_id',
					'fields' => array('diagnosis', 'icd_code', 'occurence')
				),
				'EncounterPlanLab' => array(
					'className' => 'EncounterPlanLab',
					'foreignKey' => 'encounter_id',
					'fields' => array('diagnosis', 'test_name', 'comment', 'cpt', 'cpt_code')
				),
				'EncounterPlanRadiology' => array(
					'className' => 'EncounterPlanRadiology',
					'foreignKey' => 'encounter_id',
					'fields' => array('diagnosis', 'procedure_name', 'comment', 'cpt', 'cpt_code')
				),
				'EncounterPlanProcedure' => array(
					'className' => 'EncounterPlanProcedure',
					'foreignKey' => 'encounter_id',
					'fields' => array('diagnosis', 'test_name', 'comment', 'cpt', 'cpt_code')
				),
			),
			'belongsTo' =>array(
				'ScheduleCalendar'=> array(
					'foreignKey' => 'calendar_id',					
					'fields' => array('ScheduleCalendar.calendar_id', 'ScheduleCalendar.provider_id', 'ScheduleCalendar.location', 'Provider.firstname', 'Provider.lastname', 'PracticeLocation.location_name'),
					'joins' =>  array(
						array(
							'table' => 'user_accounts',
							'alias' => 'Provider',
						 	'type' => 'LEFT',
						 	'conditions' => array('Provider.user_id = ScheduleCalendar.provider_id')
						),
						array(
							'table' => 'practice_locations',
							'alias' => 'PracticeLocation',
						 	'type' => 'LEFT',
						 	'conditions' => array('PracticeLocation.location_id = ScheduleCalendar.location')
						)
					)
				),
				'PatientDemographic' => array(
					'className' => 'PatientDemographic',
					'foreignKey' => 'patient_id',
					'fields' => array('patient_id', 'kareo_id')
				),				
			)		
		));
		$conditions = array('EncounterMaster.patient_id' => $patient_id);
		if($encounter_id) {
			$conditions['EncounterSuperbill.encounter_id'] = $encounter_id;
		}
		$bills = $this->Superbill->find('all', array(
			'conditions' => $conditions,
			'recursive' => 2,
			'fields' => array('EncounterSuperbill.*'),
			'callbacks' => false
		));
		if($this->kareo_debug)
		{			
			Cakelog::write('debug',"SUPERBILLS: --> \n" . print_r($bills,true));
		}		
		foreach($bills as $bill)
		{
			if(empty($bill['EncounterMaster']['PatientDemographic']['kareo_id']))
			{
				$outx= "no Kareo ID present for patient_id: ".$bill['EncounterMaster']['PatientDemographic']['patient_id']." so aborting...";
				if($this->kareo_debug) {
					Cakelog::write('debug',$outx); 
				}
				$this->mailToSupport('CreateEncounter', $outx);
				return;
			}
			$bill = $this->decodeItem($bill);
			$bill = $this->sanitizeHTML($bill);
			$providerFirstName = $bill['EncounterMaster']['ScheduleCalendar']['Provider']['firstname'];
			$providerLastName = $bill['EncounterMaster']['ScheduleCalendar']['Provider']['lastname'];
			$this->EncounterServiceStartDate = __date('Y-m-d', strtotime($bill['EncounterMaster']['encounter_date']));
			//pr($bill);exit;
			$ServiceLines = array();
			// get Assessment Diagnosis
			$assessmentDiagnosis = array();
			foreach	($bill['EncounterMaster']['EncounterAssessment'] as $EncounterAssessment) {
				if($EncounterAssessment['icd_code'])
					$assessmentDiagnosis[] = $EncounterAssessment['icd_code'];
				elseif($EncounterAssessment['occurence'])
					$assessmentDiagnosis[] = $EncounterAssessment['occurence'];
				else 
					$assessmentDiagnosis[] = $EncounterAssessment['diagnosis'];
			}
			// bill item service_level	
			preg_match("/^(.*)\s+\((.*)\)$/", $bill['EncounterSuperbill']['service_level'], $matches);
			if(isset($matches[1])) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $matches[1];
				$lineItem['MoreDiagnosis'] = $assessmentDiagnosis;
				$ServiceLines[] = $this->lineItem($lineItem);
			}
			// bill item service_level_advanced
			if(is_array($bill['EncounterSuperbill']['service_level_advanced']))
			{
				foreach($bill['EncounterSuperbill']['service_level_advanced'] as $service_level_advanced) {
					$lineItem = $this->resetLineItem();
					preg_match("/^(.*)\s+\((.*)\)$/", $service_level_advanced, $matches);
					$lineItem['Procedure'] = $matches[1];
					$lineItem['MoreDiagnosis'] = $assessmentDiagnosis;
					$ServiceLines[] = $this->lineItem($lineItem);
				}
			}
			// bill item other_codes
			if(is_array($bill['EncounterSuperbill']['other_codes']))
			{
				foreach($bill['EncounterSuperbill']['other_codes'] as $other_codes) {					
					if(empty($other_codes['code'])) continue;
					$lineItem = $this->resetLineItem();
					$lineItem['Procedure'] = $other_codes['code'];
					$lineItem['MoreDiagnosis'] = $assessmentDiagnosis;
					$ServiceLines[] = $this->lineItem($lineItem);
				}
			}
			$value=array();
			foreach	($bill['EncounterMaster']['EncounterImmunization'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['vaccine_name'];
				$lineItem['FromDate'] = $value['vaccine_date_performed'];
				$lineItem['ToDate'] = $value['vaccine_expiration_date'];
				$lineItem['Diagnosis'] = $value['vaccine_reason'];
				if(in_array($value['vaccine_name'], $bill['EncounterSuperbill']['ignored_in_house_immunizations'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$ServiceLines[] = $this->lineItem($lineItem);
			}
			$value=array();
			foreach	($bill['EncounterMaster']['EncounterLabs'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['lab_test_name'];
				$lineItem['FromDate'] = $value['lab_date_performed'];
				$lineItem['Diagnosis'] = $value['lab_reason'];$lineItem['Units'] = $value['lab_unit'];
				if(in_array($value['lab_test_name'], $bill['EncounterSuperbill']['ignored_in_house_labs'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$ServiceLines[] = $this->lineItem($lineItem);
			}
			$value=array();	
			foreach	($bill['EncounterMaster']['EncounterRadiology'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['radiology_procedure_name'];
				$lineItem['FromDate'] = $value['radiology_date_performed'];
				$lineItem['Diagnosis'] = $value['radiology_reason'];
				if(in_array($value['radiology_procedure_name'], $bill['EncounterSuperbill']['ignored_in_house_radiologies'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$ServiceLines[] = $this->lineItem($lineItem);
			}
			$value=array();	
			foreach	($bill['EncounterMaster']['EncounterProcedure'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['procedure_name'];
				$lineItem['FromDate'] = $value['procedure_date_performed'];
				$lineItem['Diagnosis'] = $value['procedure_reason'];
				$lineItem['Units'] = $value['procedure_unit'];
				$lineItem['Modifier']= $value['modifier'];
				if(in_array($value['procedure_name'], $bill['EncounterSuperbill']['ignored_in_house_procedures'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$ServiceLines[] = $this->lineItem($lineItem);
			}
			$value=array();
			foreach	($bill['EncounterMaster']['EncounterMed'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['drug'];
				$lineItem['FromDate'] = $value['drug_date_given'];
				$lineItem['Diagnosis'] = $value['drug_reason'];
				$lineItem['Units'] = $value['quantity'];
				if(in_array($value['drug'], $bill['EncounterSuperbill']['ignored_in_house_meds'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$ServiceLines[] = $this->lineItem($lineItem);
			}
			$value=array();
			foreach	($bill['EncounterMaster']['EncounterInjection'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['injection_name'];
				$lineItem['FromDate'] = $value['injection_date_performed'];
				$lineItem['Diagnosis'] = $value['injection_reason'];
				$lineItem['Units'] = $value['injection_unit'];
				if(in_array($value['injection_name'], $bill['EncounterSuperbill']['ignored_in_house_injections'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$ServiceLines[] = $this->lineItem($lineItem);
			}
			$value=array();
			foreach	($bill['EncounterMaster']['EncounterSupply'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['supply_name'];
				$lineItem['FromDate'] = '';
				$lineItem['Diagnosis'] = '';
				$lineItem['Units'] = $value['supply_quantity'];
				if(in_array($value['supply_name'], $bill['EncounterSuperbill']['ignored_in_house_supplies'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$ServiceLines[] = $this->lineItem($lineItem);
			}
			$value=array();			
			foreach	($bill['EncounterMaster']['EncounterPlanLab'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['test_name'];
				$lineItem['Diagnosis'] = $value['diagnosis'];
				if( !empty($lineItem['Procedure']) && !empty($lineItem['Diagnosis']) && !empty($lineItem['Description'])  )
				$ServiceLines[] = $this->lineItem($lineItem);
			}
			$value=array();
			foreach	($bill['EncounterMaster']['EncounterPlanRadiology'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['procedure_name'];
				$lineItem['Diagnosis'] = $value['diagnosis'];
				if( !empty($lineItem['Procedure']) && !empty($lineItem['Diagnosis']) && !empty($lineItem['Description'])  )
				$ServiceLines[] = $this->lineItem($lineItem);
			}
			$value=array();
			foreach	($bill['EncounterMaster']['EncounterPlanProcedure'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['test_name'];
				$lineItem['Diagnosis'] = $value['diagnosis'];
				if( !empty($lineItem['Procedure']) && !empty($lineItem['Diagnosis']) && !empty($lineItem['Description'])  )
				$ServiceLines[] = $this->lineItem($lineItem);
			}

			$encounter = array(
				'ServiceStartDate' => $this->EncounterServiceStartDate,
				//'PostDate' => '3/25/2012 12:00:00',
				'Practice' => array('PracticeName' => $this->settings['PracticeSetting']['kareo_practice_name']),
				'ServiceLocation' => array('LocationName' => $bill['EncounterMaster']['ScheduleCalendar']['PracticeLocation']['location_name']),
				'Patient' => array('PatientID' => $bill['EncounterMaster']['PatientDemographic']['kareo_id']),
				//'Case' => array('CaseName' => "case 1"),
				//'RenderingProvider' => array('FirstName' => 'William', 'LastName' => 'Mayfield'),
				//'RenderingProvider' => array('FirstName' => $providerFirstName, 'LastName' => $providerLastName),
				'ServiceLines' => $ServiceLines,
				'MedicalOfficeNotes' => $bill['EncounterSuperbill']['superbill_comments']
			);

			//add Supervising Provider if defined. but kareo always wants a RenderingProvider that must match what they have. 
			if (!empty($bill['EncounterSuperbill']['supervising_provider_id'])) {
				$userinf=ClassRegistry::init('UserAccount')->getUserByID($bill['EncounterSuperbill']['supervising_provider_id']);
				$encounter['RenderingProvider']=array('FirstName' => $userinf->firstname, 'LastName' => $userinf->lastname);
			} else {
				$encounter['RenderingProvider']= array('FirstName' => $providerFirstName, 'LastName' => $providerLastName);
			}

			//print_r($encounter);
			$params = $this->getParams(array('Encounter' => $encounter));
		        if($this->kareo_debug)
                	{
                          Cakelog::write('debug',"PARAMS : \n".print_r($params,true));
                	}
			try {			
				$response = $this->client->CreateEncounter($params);
			} catch (Exception $err) {
			   $err = $err->getMessage();
			   $this->mailToSupport('CreateEncounter', $err);
                           if($this->kareo_debug)
                          {
                             Cakelog::write('debug',"ERROR : \n".print_r($err,true));
                          }
			   return;
			}

			if($this->kareo_debug )
			{
			  Cakelog::write('debug',"RESPONSE : \n".print_r($response,true) );
			}			
			//if any error from Kareo notify us
			if( $response->CreateEncounterResult->ErrorResponse->IsError)
			{
				$err_xml = $response->CreateEncounterResult->ErrorResponse->ErrorMessage;
				preg_match_all('/>([^>]+|)?<err[^>]+>(.*?)<\/err>/',$err_xml, $errors);
				$err_str = '';
				$tmp = array();
				foreach($errors[1] as $key => $e) {
					if($e && in_array($e, $tmp)) continue;
					$tmp[] = $e;
					$nes = preg_replace('/code(.*)not found/', "code $e not found", $errors[2][$key]);	
					$e = ($e)? $e.' => '.$nes : $nes;
					$err_str .=  $e."\n";	
				}
				$err_mail = array();
				$err_mail[] = 'Encounter #: '. $encounter_id;
				if($this->settings['PracticeSetting']['kareo_encounter_lock']){ //if we want to return errors to screen
				  $url = Router::url(array(
					'controller' => 'encounters', 'action' => 'index', 'task' => 'edit', 'encounter_id' => $encounter_id), true);
				  $err_mail[] = 'Encounter URL: '.$url;
				}
				$err_mail[] = $err_str;
				$err_mail = implode("\n", $err_mail). "\n\n RAW DATA: \n".$response->CreateEncounterResult->ErrorResponse->ErrorMessage;
				$this->mailToSupport('CreateEncounter', $err_mail);
				if($this->settings['PracticeSetting']['kareo_encounter_lock']){ //if we want to return errors to screen
				  $return = nl2br($err_str);
				  return $return;
				}
			}
		}
	}
	
	public function lineItem($lineItem)
	{
		if(empty($lineItem['Units']))
			$lineItem['Units'] = 1;
		$lineItemTag = array(
			'ServiceStartDate' => $this->EncounterServiceStartDate,
			'ProcedureCode' => $lineItem['Procedure'],
			'Units' => $lineItem['Units'],
			'UnitCharge' => $lineItem['Charge'],
		);
		$i = 1;
		if(!empty($lineItem['Diagnosis'])) {
			$i = 2;
			preg_match("/^(.*)\s+\[(.*)\]$/", $lineItem['Diagnosis'], $matches);
			if(isset($matches[2]))
				$lineItemTag['DiagnosisCode1'] = $matches[2];
			else
				$lineItemTag['DiagnosisCode1'] = $lineItem['Diagnosis'];
		}
		if(isset($lineItem['MoreDiagnosis'])) {
			foreach($lineItem['MoreDiagnosis'] as $eachDiagnosis) {
				$lineItemTag['DiagnosisCode'.$i] = $eachDiagnosis;
				$i++;
			}
		}
		// get modifiers
		if(!empty($lineItem['Modifier'])) {
                       if(strstr($lineItem['Modifier'], ',')) {
                          $ms=explode(',',$lineItem['Modifier'] );
                          for($i=0;$i<4;$i++)
                          {
			     if( !empty($ms[$i]) ) {
                               $i2=$i+1;
                               $lineItemTag["ProcedureModifier{$i2}"] = trim($ms[$i]);
			     }
                          }
		       } else {
                             $lineItemTag["ProcedureModifier1"]=$lineItem['Modifier'];
                       }
		}

		return $lineItemTag;
	}
	
	public function getCode($value)
	{
		$return = '';
		if(isset($value['cpt']) && $value['cpt']!='') {
			preg_match("/^(.*)\s+\[(.*)\]$/", $value['cpt'], $matches);
			if(isset($matches[2]))
				$value['cpt'] = $matches[2];
			$return = $value['cpt'];
		}
		else if(isset($value['cpt_code']) && $value['cpt_code']!='')
			$return = $value['cpt_code'];
		else if(isset($value['code']) && $value['code']!='')
			$return = $value['code'];
		return $return;
	}
	
	public function resetLineItem()
	{
		$lineItem = array('Charge' => '','Description' => '','FromDate' => '','ToDate' => '','Identifier' => '','PlaceOfService' => '','Procedure' => '','Provider' => '','Units' => '','Diagnosis' => '');
		return $lineItem;
	}
	
	public function decodeItem($item)
    {
        if($item)
        {
            $item['EncounterSuperbill']['ignored_diagnosis'] = json_decode($item['EncounterSuperbill']['ignored_diagnosis'], true);
            $item['EncounterSuperbill']['ignored_in_house_labs'] = json_decode($item['EncounterSuperbill']['ignored_in_house_labs'], true);
            $item['EncounterSuperbill']['ignored_in_house_radiologies'] = json_decode($item['EncounterSuperbill']['ignored_in_house_radiologies'], true);
            $item['EncounterSuperbill']['ignored_in_house_procedures'] = json_decode($item['EncounterSuperbill']['ignored_in_house_procedures'], true);
			$item['EncounterSuperbill']['ignored_in_house_immunizations'] = json_decode($item['EncounterSuperbill']['ignored_in_house_immunizations'], true);
			$item['EncounterSuperbill']['ignored_in_house_meds'] = json_decode($item['EncounterSuperbill']['ignored_in_house_meds'], true);
			$item['EncounterSuperbill']['ignored_outside_labs'] = json_decode($item['EncounterSuperbill']['ignored_outside_labs'], true);
			$item['EncounterSuperbill']['ignored_radiologies'] = json_decode($item['EncounterSuperbill']['ignored_radiologies'], true);
			$item['EncounterSuperbill']['ignored_procedures'] = json_decode($item['EncounterSuperbill']['ignored_procedures'], true);  
			$item['EncounterSuperbill']['ignored_in_house_injections'] = json_decode($item['EncounterSuperbill']['ignored_in_house_injections'], true);
			$item['EncounterSuperbill']['ignored_in_house_supplies'] = json_decode($item['EncounterSuperbill']['ignored_in_house_supplies'], true);          
        
			$item['EncounterSuperbill']['service_level_advanced'] = json_decode($item['EncounterSuperbill']['service_level_advanced'], true);
			$item['EncounterSuperbill']['other_codes'] = json_decode($item['EncounterSuperbill']['other_codes'], true);          
        }
		return $item;
    }
	
	function importSchedule()
	{
                // Kareo uses Pacific Time zone
                $userTimezone = new DateTimeZone('America/Los_Angeles');
                $PacificDateTime = new DateTime('now', $userTimezone);

		$ToLastModifiedDate = $PacificDateTime->format('n/j/Y h:i:s A');

		Cache::set(array('duration' => '+1 day'));
		//get last cron time and use as from modified date
		$import_schedule_time_stamp = Cache::read($this->cache_file_prefix.'kareo_import_schedule_time_stamp');
                $PacificDateTime->modify('-1 day'); // go back this far on the FROM date (next line) if no cache
                $FromDate=$PacificDateTime->format('n/j/Y h:i:s A');
		$FromLastModifiedDate = (!empty($import_schedule_time_stamp)) ? $import_schedule_time_stamp : $FromDate;

		//set cache that ToLastModifiedDate of this cron to use as starting time of next cron 
		Cache::set(array('duration' => '+1 day'));
		Cache::write($this->cache_file_prefix.'kareo_import_schedule_time_stamp', $ToLastModifiedDate );
						
		$request = array (
		   'Filter' => array('FromLastModifiedDate' => $FromLastModifiedDate, 'ToLastModifiedDate' => $ToLastModifiedDate),
		); 
		$params = $this->getParams($request);
		try {
			$Appointments = $this->client->GetAppointments($params)->GetAppointmentsResult->Appointments;
			if(empty($Appointments)) {
				echo '0 Schedule has been imported',"\n";
				return;
			}
			$response = $Appointments->AppointmentData;
		} catch (Exception $err) {
           	   $err = $err->getMessage();
		   $this->mailToSupport('GetAppointments', $err);
		   return;
        }
		$response = Set::reverse($response);
		$this->importCount = 0;
		if( !empty($response[0]['ID']) || !empty($response['ID']) ) {
		if($this->kareo_debug) { pr($response);}
			$this->PatientDemographic = ClassRegistry::init('PatientDemographic');
			$this->ScheduleCalendar = ClassRegistry::init('ScheduleCalendar');
			$this->PracticeLocation = ClassRegistry::init('PracticeLocation');
			$this->UserAccount = ClassRegistry::init('UserAccount');
			$this->ScheduleRoom = ClassRegistry::init('ScheduleRoom'); // get all Schedule Rooms
			$this->ScheduleStatus = ClassRegistry::init('ScheduleStatus'); // get all Schedule Status
			$this->ScheduleRooms = $this->ScheduleRoom->find('list', array('fields' => array('room', 'room_id')));
			$this->ScheduleStatuses = $this->ScheduleStatus->find('list', array('fields' => array('status', 'status_id')));								
			$adminAcc = $this->UserAccount->find('first', array(
				'conditions' => array('role_id' => 10), 'fields' => array('user_id'), 'recursive' => -1
			));
			$this->adminId = $adminAcc['UserAccount']['user_id'];
			if(isset($response[0]['ID'])) {
				foreach($response as $schedule) {
					$this->addSchedule($schedule);
				}
			} else {
				$this->addSchedule($response);
			}
		}
		echo $this->importCount.' Schedule has been imported',"\n"; 
		
	}
	
	function addSchedule($schedule)
	{		
		//pr($schedule);exit;
		$proName = explode(' ', $schedule['ResourceName1']); // first resource should be provider, split fullname
		$proFname = $proName[0];
		$proLname = '';
		if(isset($proName[2]))
			$proLname = $proName[2];
		elseif(isset($proName[1]))
			$proLname = $proName[1];
		$provider = $this->UserAccount->find('first', array(
		   'conditions' => array('firstname' => $proFname, 'lastname' => $proLname), 'fields' => array('user_id'),'recursive' => -1
		));
		$checkPatient = $this->PatientDemographic->find('first', array('fields' => array('patient_id'), 'conditions' => array('kareo_id' => $schedule['PatientID']), 'callbacks' => false, 'recursive' => -1));
				
		if(!isset($checkPatient['PatientDemographic']['patient_id']) || empty($checkPatient['PatientDemographic']['patient_id']) || !isset($provider['UserAccount']['user_id']) || empty($provider['UserAccount']['user_id'])) {
			return; // if patient or provider id is empty no need to continue the process
		}

		if($schedule['ServiceLocationName']) {
			$scheduleLocation = $this->PracticeLocation->find('first', array('fields' => array('location_id','default_visit_duration'), 'conditions' => array('location_name' => $schedule['ServiceLocationName']), 'recursive' => -1));
		} 
		//location name from Kareo didn't match our system, so grab first one
		if(!$scheduleLocation['PracticeLocation']['location_id']) { 
			$scheduleLocation = $this->PracticeLocation->find('first', array('fields' => array('location_id','default_visit_duration'), 'order' => 'location_id', 'recursive' => -1));
		}

		//calculate the time need to adjust for schedule from practice settings
		$ajustTime = $this->settings['PracticeSetting']['kareo_schedule_adjust_time'] * 3600;
		$EndDate = strtotime($schedule['EndDate']) + $ajustTime;
		$StartDate = strtotime($schedule['StartDate']) + $ajustTime;
		$date = __date('Y-m-d', $StartDate);
		$starttime = date('H:i:s', $StartDate);
		$endtime = date('H:i:s', $EndDate);
		$duration = ($EndDate - $StartDate)? ($EndDate - $StartDate)/60 : 0;
		$room = isset($this->ScheduleRooms[$schedule['ResourceName2']])? $this->ScheduleRooms[$schedule['ResourceName2']] : ''; // match and get room id using second resource from kareo(ResourceName2)
		$status = isset($this->ScheduleStatuses[$schedule['ConfirmationStatus']])? $this->ScheduleStatuses[$schedule['ConfirmationStatus']] : ''; // match and get status id 
		
		$visit_type=$this->getApptType($schedule['AppointmentReason1']);
		$scheduleData = array(
			'patient_id' => $checkPatient['PatientDemographic']['patient_id'],
			'provider_id' => $provider['UserAccount']['user_id'],
			'visit_type' => $visit_type,  
			'reason_for_visit' => $schedule['Notes'],
			'duration' => $duration,
			'starttime' => $starttime,
			'date' => $date,
			'endtime' => $endtime,
			'location' => $scheduleLocation['PracticeLocation']['location_id'],
			'room' => $room,
			'status' => $status,
			'kareo_cal_id' => $schedule['ID'], // save appointment id from kareo for later update
			'modified_timestamp' => __date("Y-m-d H:i:s"),
			'modified_user_id' => $this->adminId
		);
		$existCal = $this->ScheduleCalendar->find('first', array('conditions' => array('kareo_cal_id' => $schedule['ID']), 'fields' => array('calendar_id'), 'recursive' => -1));
		if(!empty($existCal)) {
			$scheduleData['calendar_id'] = $existCal['ScheduleCalendar']['calendar_id']; // if appointment already exist update the old appointment
		}
		//pr($scheduleData);			
		$res = $this->ScheduleCalendar->save($scheduleData, array('callbacks' => false));
		if($res)
			$this->importCount++;		
		unset($this->ScheduleCalendar->id);
	}

	function getApptType($type)
	{
		//grab our system schedule types, and match up
		$st = ClassRegistry::init('ScheduleAppointmentTypes')->find('first', array('conditions' => array('type' => $type)));
		if($st) {
		  return $st['ScheduleAppointmentTypes']['appointment_type_id'];
		} else {
		  return 1; //default Office Visit type if no match
		}
	}
	function deleteSchedule()
	{
/*   DISBLED 9/4/2013 - NOT REALLY NEEDED, doesn't work right either. it needs date range  

		$request = array (
		    'Fields' => array('ID' => 'true')
		); 
		$params = $this->getParams($request);
		try {
			$Appointments = $this->client->GetAppointments($params)->GetAppointmentsResult->Appointments; // get all schedules in kareo
			if(empty($Appointments)) {
				echo '0 Schedule has been deleted',"\n";
				return;
			}
			$response = $Appointments->AppointmentData;
			$response = Set::reverse($response);
			$ids = Set::extract('n/ID', $response);
			if(empty($ids))
				return;
		} catch (Exception $err) {
           		echo $err = $err->getMessage();
        	}
		
		$this->ScheduleCalendar = ClassRegistry::init('ScheduleCalendar');
		$kareo_cal_ids = implode("','", $ids);
		$res = $this->ScheduleCalendar->query('UPDATE schedule_calendars SET deleted = "1" WHERE kareo_cal_id IS NOT NULL AND kareo_cal_id != "" AND kareo_cal_id NOT IN(\''.$kareo_cal_ids.'\')');
		$deleteCount = $this->ScheduleCalendar->getAffectedRows();
		echo $deleteCount.' Schedule has been updated as deleted',"\n";
	*/
	}
	
	function export($patient_id='')
	{
		global $argv;
		if(isset($argv[5]) && $argv[5]) {
			$patient_id = $argv[5];
		}
		App::import('Sanitize');
		$this->PatientDemographic = ClassRegistry::init('PatientDemographic');
		$this->PatientDemographic->unbindModelAll();
		$this->PatientDemographic->bindmodel(array(
			'hasOne' => array(
				'PatientPreference'=> array(
					'foreignKey' => 'patient_id',
					'fields' => array('pcp', 'patient_id', 'email_address'),					
				),				
				/*'PatientEmployment'=> array(
					'foreignKey' => 'patient_id',
				),*/
				'PatientSocialHistory'=> array(
					'foreignKey' => 'patient_id',
					'conditions' => array('PatientSocialHistory.type' => 'Marital Status'),
					'fields' => 'marital_status' 
				),
			),
			'hasMany' =>array(
				'PatientGuarantor'=> array(
					'foreignKey' => 'patient_id',
					'conditions' => 'PatientGuarantor.relationship != 18',
					'order' => 'guarantor_id asc',
					'limit' => 1,
				),
				'PatientInsurance'=> array(
					'foreignKey' => 'patient_id',
					//'conditions' =>	array('ownerid' => $this->settings['PracticeSetting']['emdeon_facility']),			
				)				
			),			
		));
		$this->PatientDemographic->PatientPreference->unbindModel(array('belongsTo' => array('UserAccount')));
		$this->PatientDemographic->PatientGuarantor->unbindModel(array('belongsTo' => array('PatientDemographic')));
		$this->PatientDemographic->PatientInsurance->unbindModel(array('belongsTo' => array('PatientDemographic')));
		$this->PatientDemographic->PatientPreference->bindModel(array(
			'belongsTo'=> array(
				'Provider' => array(	
					'foreignKey' => 'pcp',
					'className' => 'UserAccount',
					'fields' => array('Provider.firstname', 'Provider.lastname', 'Provider.work_phone')
				)			
			),
		));
		if($patient_id) {		
			$users = $this->PatientDemographic->find('all', array('recursive' => 2, 'conditions' => 'PatientDemographic.patient_id = '.$patient_id.' ', 'callbacks' => false));
		} else {
		  //find last time this was run
		  Cache::set(array('duration' => '+1 day'));
		  $kareo_export_time_stamp=Cache::read($this->cache_file_prefix.'kareo_export_time_stamp');
		  $last_run = (!empty($kareo_export_time_stamp)) ? $kareo_export_time_stamp : date('Y-m-d H:i:s');
		  $users = $this->PatientDemographic->find('all', array('conditions' => '(DES_DECRYPT(PatientDemographic.status) = "Active" or DES_DECRYPT(PatientDemographic.status) = "New") and ((kareo_id IS NULL or kareo_id = "") or PatientDemographic.modified_timestamp > "'.$last_run.'")', 'recursive' => 2, 'order' => 'PatientDemographic.patient_id', 'callbacks' => false));
		}
		$this->exportPatientCount = 0;
		if($this->kareo_debug) { echo "req: export -> ";}
		foreach($users as $user) 
		{
			if($this->kareo_debug) {pr($user);}
			$patient = $user['PatientDemographic'];
			if(empty($patient['first_name'])) continue;
			$provider = isset($user['PatientPreference']['Provider'])? $user['PatientPreference']['Provider']:'';
			$guarantor = @$user['PatientGuarantor'][0];
			//$employment = $user['PatientEmployment'];
			$insurences = $user['PatientInsurance'];
			
			$patientData = array(
				'FirstName' => $patient['first_name'],
				'MiddleName' => $patient['middle_name'],
				'LastName' => $patient['last_name'],
				'Gender' => $this->gender($patient['gender']),
				//'DateofBirth' => $this->formatDate($patient['dob'],true),
				'SocialSecurityNumber' => $patient['ssn'],
				'AddressLine1' => $patient['address1'],
				'AddressLine2' => $patient['address2'],
				'City' => $patient['city'],
				'State' => $patient['state'],
				'ZipCode' => $patient['zipcode'],
				'Country' => $patient['immtrack_country'],
				'WorkPhone' => $this->formatPhone($patient['work_phone'],true),
				'HomePhone' => $this->formatPhone($patient['home_phone'],true),
				'MobilePhone' => $this->formatPhone($patient['cell_phone'],true),
				'EmergencyName' => $patient['emergency_contact'],
				'EmergencyPhone' => $this->formatPhone($patient['emergency_phone'],true),
				'EmailAddress' => $user['PatientPreference']['email_address'],
				'MedicalRecordNumber' => $patient['mrn'],
			);
			$patientDOB = $this->formatDate($patient['dob'],true);
			if($patientDOB) {
				$patientData['DateofBirth'] = $patientDOB;
			}
			$patientData['Practice'] = array('PracticeName' => $this->settings['PracticeSetting']['kareo_practice_name']);
			if(!empty($provider))
				$patientData['PrimaryCarePhysician'] = array('FullName' => $provider['firstname']. ' '. $provider['lastname']);
			if(!empty($guarantor)) {
				$guarantor_relation = isset($guarantor['EmdeonRelationship']['description'])? $guarantor['EmdeonRelationship']['description'] : '';
				$patientData['Guarantor'] = array(
					'AddressLine1' => $guarantor['address_1'],
					'AddressLine2' => $guarantor['address_2'],
					'City' => $guarantor['city'],
					'FirstName' => $guarantor['first_name'],
					'LastName' => $guarantor['last_name'],
					'MiddleName' => $guarantor['middle_name'],
					'RelationshiptoGuarantor' => $this->InsurRelation($guarantor_relation, true),
					'State' => $guarantor['state'],
					'ZipCode' => $guarantor['zip'],
					'DifferentThanPatient' => true,
				);
			}
			if(!empty($insurences)) {
				$i = 1;
				foreach($insurences as $policy) {
					$kareoInsurences = array(
						'insurance_info_id' => $policy['insurance_info_id'], 
						'GroupNumber' => $policy['group_id'],
						'Copay' => $policy['copay_amount'],
						//'EffectiveStartDate' => $policy['start_date'],
						//'EffectiveEndDate' => $policy['end_date'],
						'InsuredIDNumber' => $policy['insurance_code'],
						//'type' => $policy['Insurance']['Type'],
						'CompanyName' => $policy['payer'],
						'PolicyNumber' => $policy['policy_number'],
						'PlanName' => $policy['plan_name'],
						'InsurancePolicyID' => $policy['kareo_insurance_id'], // pass kareo insurance id to update into kareo 
						'Insured' => array(
							'FirstName' => $policy['insured_first_name'],
							'MiddleName' => $policy['insured_middle_name'],
							'LastName' => $policy['insured_last_name'],
							'AddressLine1' => $policy['insured_address_1'],
							'AddressLine2' => $policy['insured_address_2'],
							'City' => $policy['insured_city'],
							'State' => $policy['insured_state'],
							'ZipCode' => $policy['insured_zip'],
							'Gender' => $this->gender($policy['insured_sex']),
							//'DateofBirth' => $policy['insured_birth_date'],
							'SocialSecurityNumber' => $policy['insured_ssn'],
							'PatientRelationshipToInsured' => $this->InsurRelation($policy['relationship'],true),
						),
					);
					if($policy['start_date'] && strpos($policy['start_date'],'0000')===false)
						$kareoInsurences['EffectiveStartDate'] = $policy['start_date'];
					if($policy['end_date'] && strpos($policy['end_date'],'0000')===false)
						$kareoInsurences['EffectiveEndDate'] = $policy['end_date'];
					if($policy['insured_birth_date'] && strpos($policy['insured_birth_date'],'0000')===false)
						$kareoInsurences['Insured']['DateofBirth'] = $policy['insured_birth_date'];
						
					$patientData['Cases'][] = array('CaseName' => "Case".$i, 'Policies' => array($kareoInsurences)); 
					$i++;				
				}
			}
			$patientData['Practice'] = array('PracticeName' => $this->settings['PracticeSetting']['kareo_practice_name']);
			//pr($patientData);
			if(empty($patient['kareo_id'])) { // if patient not exist in kareo create as new patient
				$params = $this->getParams(array('Patient' => $patientData));
				try {
					$response = $this->client->CreatePatient($params);
					if($this->kareo_debug && $response->CreatePatientResult->ErrorResponse->IsError)
						echo strip_tags($response->CreatePatientResult->ErrorResponse->ErrorMessage), "\n";
					else
						$this->exportPatientCount++;
				} catch (Exception $err) {
				   echo $err = $err->getMessage();
				   $this->mailToSupport('CreatePatient', $err);
				   continue; // continue to next patient if error on Createing current patient
				}
				$resVal = 'CreatePatientResult';
			} else { // if patient exist in kareo update the patient data
				$patientData['PatientID'] = $patient['kareo_id'];
				$params = array('UpdatePatientReq' => array(
					'Patient' => $patientData,
					'RequestHeader' => $this->RequestHeader
				));
				try {
					$response = $this->client->UpdatePatient($params);
					if($this->kareo_debug && $response->UpdatePatientResult->ErrorResponse->IsError)
						echo strip_tags($response->UpdatePatientResult->ErrorResponse->ErrorMessage), "\n";
					else
						$this->exportPatientCount++;
				} catch (Exception $err) {
				   echo $err = $err->getMessage();
				   continue; // continue to next patient if error on Createing current patient
				}
				$resVal = 'UpdatePatientResult';
			}			
			$response = Set::reverse($response->$resVal); // convert object into array
			//pr($response);
			$kareo_id = isset($response['PatientID'])? $response['PatientID'] : '';
			if($kareo_id)
				$this->PatientDemographic->save(array('patient_id' => $patient['patient_id'], 'kareo_id' => $kareo_id));
			//update kareo id as MRN when new patient export to kareo
			if(empty($patient['kareo_id']) && $kareo_id)
				$this->PatientDemographic->save(array('patient_id' => $patient['patient_id'], 'custom_patient_identifier' => $kareo_id));
			// update insurence id returned from kareo in to patient_insurance_info table's field kareo_insurance_id
			if(isset($patientData['Cases']) && !empty($patientData['Cases']) && isset($response['Cases']['PatientCaseRes']))
			{	
				$caseData = $patientData['Cases'];
				$caseRes = $response['Cases']['PatientCaseRes'];
				if(isset($caseRes['Policies']['InsurancePolicyRes'])) { // check is only one case
					$caseRes = array($caseRes);
				}
				foreach($caseRes as $caseKey => $eachCase) {
					if(isset($eachCase['Policies']['InsurancePolicyRes'])) {
						$policyRes = $eachCase['Policies']['InsurancePolicyRes'];
						if(isset($policyRes['InsurancePolicyID'])) { // check is only one policy
							$policyRes = array($policyRes);
						} 
						foreach($policyRes as $policyKey => $eachPolicy) {
							// get insurence id of the table by maching caseKey and policyKey from the sent request
							if(isset($caseData[$caseKey]['Policies'][$policyKey]['insurance_info_id'])) {
								$insurance_info_id =$caseData[$caseKey]['Policies'][$policyKey]['insurance_info_id'];
								$this->PatientDemographic->PatientInsurance->save(array('insurance_info_id' => $insurance_info_id, 'kareo_insurance_id' => $eachPolicy['InsurancePolicyID']), array('callbacks' => false));
							}
						}
					}
				}
			}
		}
		echo "\n".$this->exportPatientCount.' Patient\'s record(s) exported',"\n";
		
		//finished, so update time stamp. only if no individual patient
		if(!$patient_id) {
		  $kar=date('Y-m-d H:i:s', time() + 5); //add 5 seconds
		  Cache::set(array('duration' => '+1 day'));
                  Cache::write($this->cache_file_prefix.'kareo_export_time_stamp', $kar );
		}
	}
	
	public function sanitizeHTML($data)
	{
		$ret = array();
		if(is_array($data)) {
			foreach($data as $key => $value) {
				if(is_array($value)) {
					$ret[$key] = self::sanitizeHTML($value);
				}
				else {
					$ret[$key] = Sanitize::html($value);
				}
			}
		}
		else {
			$ret = Sanitize::html($data);
		}
		return $ret;
	}
	
	public function formatPhone($phone, $reformat=false)
	{
		if($phone && $reformat) {
			$newphone = str_replace(array('(', ' '), array(''), $phone);
			$newphone = str_replace(')', '-', $newphone);
			return $newphone;
		}
		if($phone) {
			$exp = explode('-', $phone);
			$ccc = "($exp[0]) ";
			unset($exp[0]);
			return $ccc.implode('-', $exp);
		}
	}
	
	public function formatDate($date, $reformat=false)
	{
		if($date && $reformat) {
			$newDate = __date('Y-m-d', strtotime($date));
			return $newDate;
		}
		if($date) {
			$newDate = __date('m/d/Y', strtotime($date));
			return $newDate;
		}
		return '';
	}
	
	public function formatTime($time)
	{
		if($time) {
			$newTime = __date('H:i', strtotime($time));
			return $newTime;
		}
	}
	
	public function maritalStatus($m, $reformat=false)
	{
		$marital_status = array('Single'=>'S','Married'=>'M','Divorced'=>'D','Separated'=>'X','Widowed'=>'W','Unknown'=>'U');
		if($m && $reformat) {			
			$status = array_search($m, $marital_status);
			$status = ($status)? $status : $m;
			return $status;
		}
		if($m) {			
			$status = (isset($marital_status[$m]))? $marital_status[$m] : $m;
			return $status;
		}
	}
	
	public function InsurPriority($p, $reformat=false)
	{
		$priorities = array('Primary'=>'1','Secondary'=>'2','Tertiary'=>'3');
		if($p && $reformat) {
			$status = array_search($p, $priorities);
			$status = ($status)? $status : $p;
			return $status;
		}
		if($p) {
			$status = (isset($priorities[$p]))? $priorities[$p] : $p;
			return $status;
		}
	}
	
	public function InsurRelation($r, $reformat=false)
	{
		$relations = array('C'=>'Child','O'=>'Other','S'=>'Self', 'U' => 'Spouse');
		if($r && $reformat) {
			$status = array_search($r, $relations);
			$status = ($status)? $r : 'Other';
			return $status;
		}
		if($r) {
			$status = (isset($relations[$r]))? $relations[$r] : $r;
			return $status;
		}
	}
	
	public function gender($g, $reformat=false)
	{
		$gender = array('F'=>'Female','M'=>'Male');
		if($g && $reformat) {
			$status = array_search($g, $gender);
			$status = ($status)? $status : $g;
			return $status;
		}
		if($g) {
			$status = (isset($gender[$g]))? $gender[$g] : 'Unknown';
			return $status;
		}
	}
	
	public function country($country)
	{
		$getCode = $this->ImmtrackCountry->find('first', array('fields' =>'code', 'conditions' => array('or' => array('country' => $country, 'code' => $country))));
		if(!empty($getCode)) {
			$code = $getCode['ImmtrackCountry']['code'];
		} else {
			$code = 'UN'; // for unknown
		}
		return $code;
	}
	
	private function mailToSupport($fn, $err)
	{
		global $argv;
		$customer = $argv[4];
		$db_config = $this->getDataSource()->config;
		$message = "Encountered an error while accessing Kareo API: \"$fn\" service \n
			the error message was: \n\n ".htmlentities($err)." \n\n for the customer: $customer ".$db_config['database'];
		$sub = 'Error on Kareo API service';
		email::send('Errors', 'errors@onetouchemr.com', $sub, nl2br($message),'','',false,'','','','','');
	}
	
	public function sendMessage($patient, $dob, $mrn)
	{
		$mail_content = "NOTE: The name of this patient's insurance company does not exactly match what is set up for Emdeon lab ordering in our system. Please make sure this is corrected inside Kareo otherwise this practice cannot order Labs for this patient. \n\n Patient: ".$patient." \n DOB: ".$dob." \n MRN: ".$mrn." \nPractice: {$this->settings['PracticeSetting']['practice_id']} ";	
		$message = array();
		$message['MessagingMessage']['sender_id'] = $_SESSION['UserAccount']['user_id'];
		$message['MessagingMessage']['recipient_id'] = $this->adminId;
		$message['MessagingMessage']['patient_id'] = 0;
		$message['MessagingMessage']['reply_id'] = 0;
		$message['MessagingMessage']['calendar_id'] = 0;
		$message['MessagingMessage']['type'] = 'Other';
		$message['MessagingMessage']['subject'] = 'Kareo Insurance Name';
		$message['MessagingMessage']['message'] = $mail_content;
		$message['MessagingMessage']['priority'] = 'Urgent';
		$message['MessagingMessage']['status'] = 'New';
		$message['MessagingMessage']['archived'] = 0;
		$message['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
		$message['MessagingMessage']['time'] = time();
		$message['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$message['MessagingMessage']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		
		$this->MessagingMessage->create();
		$this->MessagingMessage->save($message);
		//disabled for now until review further
		//email::send('Support', 'support@onetouchemr.com', $message['MessagingMessage']['subject'], nl2br($mail_content),'','',false,'','','','','');
	}
	
	/*
	 this is called from patients controller when adding / editing patient to our system
	*/
	public function exportPatientToKareo($patient_id)
	{
			$db_config = $this->getDataSource()->config;
			$shellcommand = "php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app \"".APP."\" kareo_export ".$db_config['database']." ".$patient_id." >> /dev/null 2>&1 & ";
			exec($shellcommand);
	}

        /*
         this is called from patients controller when adding / editing patient to our system
        */
        public function exportBillToKareo($patient_id,$encounter_id)
        {
                        $db_config = $this->getDataSource()->config;
                        $shellcommand = "php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app \"".APP."\" kareo_bill ".$db_config['database']." ".$patient_id." ".$encounter_id." >> /dev/null 2>&1 & ";
                        exec($shellcommand);
        }
	/*
		adjust the current time with kareo time difference and return as given input format 
	*/
	public function kareo_date($format)
	{
		$ajustTime = $this->settings['PracticeSetting']['kareo_schedule_adjust_time'] * 3600;
		return date($format, time() + $ajustTime);
	}
	
}
?>

