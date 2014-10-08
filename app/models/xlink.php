<?php

class Xlink extends AppModel 
{
	public $name = 'Xlink';
	public $useTable = false;
	
	public function Xlink()
	{
		//Configure::write('debug', 0);
	}
	
	public function initialiseExport()
	{
		App::import('Sanitize');
	}
	public function demographic($patient_id='', $encounter_id='')
	{				
		$customer_folder = $this->customerPath();
		$strDest = '/home/'.$customer_folder;	
		$this->initialiseExport();
		$this->PatientDemographic = ClassRegistry::init('PatientDemographic');
		$this->PatientDemographic->unbindModelAll();
		$this->PatientDemographic->bindmodel(array(
			'hasOne' => array(
				/*'UserAccount'=> array(
					'foreignKey' => 'patient_id',
					'conditions' => array('role_id' => 8),
					'fields' => array('user_id','patient_id'),
					'type' => 'LEFT',
				),*/
				'PatientPreference'=> array(
					'foreignKey' => 'patient_id',
					'fields' => array('pcp', 'patient_id'),					
				),
				/*'Provider'=> array(
					'className' => 'UserAccount',
					'foreignKey' => false,
					'type' => 'LEFT',
					'conditions' => array('Provider.user_id = PatientPreference.pcp'),
					'alias' => 'Provider'			
				),*/
				/*'DirectoryReferralList'=> array(
					'foreignKey' => false,
					'conditions' => array('DirectoryReferralList.user_id = UserAccount.user_id'),
				),*/
				'PatientEmployment'=> array(
					'foreignKey' => 'patient_id',
				),
				'PatientSocialHistory'=> array(
					'foreignKey' => 'patient_id',
					'conditions' => array('PatientSocialHistory.type' => 'Marital Status'),
					'fields' => 'marital_status' 
				),
			),
			'hasMany' =>array(
				'PatientGuarantor'=> array(
					'foreignKey' => 'patient_id',
					'limit' => 1,
				),
				'PatientInsurance'=> array(
					'foreignKey' => 'patient_id',					
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
					'fields' => array('Provider.firstname', 'Provider.lastname', 'Provider.xlink_id','Provider.work_phone')
				)			
			),
		));
		if($patient_id) {		
			$users = $this->PatientDemographic->find('all', array('recursive' => 2, 'conditions' => array('PatientDemographic.patient_id' => $patient_id), 'callbacks' => false));
		} else {
			$users = $this->PatientDemographic->find('all', array('recursive' => 2, 'order' => 'PatientDemographic.patient_id', 'callbacks' => false));
		}

		//pr($users);		
		foreach($users as $user) 
		{
			//pr($user);
			$demographic = $user['PatientDemographic'];
			$provider = $user['PatientPreference']['Provider'];
			$guarantor = @$user['PatientGuarantor'][0];
			$employment = $user['PatientEmployment'];
			$content = '
			<Demographic Action="Batch">';
			$content .= '
				<Patient ChartNumber="'.$demographic['xlink_chart_number'].'"
					FirstName="'.$demographic['first_name'].'"
					MiddleName="'.$demographic['middle_name'].'"
					LastName="'.$demographic['last_name'].'"
					Address1="'.$demographic['address1'].'"
					Address2="'.$demographic['address2'].'"
					City="'.$demographic['city'].'"
					State="'.$demographic['state'].'"
					ZipCode="'.$demographic['zipcode'].'"
					WorkPhone="'.$this->formatPhone($demographic['work_phone']).'"
					HomePhone="'.$this->formatPhone($demographic['home_phone']).'"
					SSN="'.$demographic['ssn'].'"
					Sex="'.$demographic['gender'].'"
					BirthDate="'.$this->formatDate($demographic['dob']).'"
					Marital="'.$this->maritalStatus($user['PatientSocialHistory']['marital_status']).'"
					Race="'.$demographic['race'].'" 
					Ethnicity="'.$demographic['ethnicity'].'" 
					Language="'.$demographic['preferred_language'].'" >
					<Provider Code="'.$provider['xlink_id'].'"
						FirstName="'.$provider['firstname'].'"
						MiddleName=""
						LastName="'.$provider['lastname'].'"
						Address1=""
						Address2=""
						City=""
						State=""
						ZipCode=""
						Phone="'.$this->formatPhone($provider['work_phone']).'"
						TaxID="" />
					<Responsible Code="'.$guarantor['guarantor_id'].'"
						FirstName="'.$guarantor['first_name'].'"
						MiddleName="'.$guarantor['middle_name'].'"
						LastName="'.$guarantor['last_name'].'"
						Address1="'.$guarantor['address_1'].'"
						Address2="'.$guarantor['address_2'].'"					
						City="'.$guarantor['city'].'"
						State="'.$guarantor['state'].'"
						ZipCode="'.$guarantor['zip'].'"
						HomePhone="'.$this->formatPhone($guarantor['home_phone']).'"
						WorkPhone="'.$this->formatPhone($guarantor['work_phone']).'"
						Relation="'.$guarantor['relationship'].'"
						Sex="'.$guarantor['guarantor_sex'].'"
						BirthDate="'.$this->formatDate($guarantor['birth_date']).'"
						SSN="'.$guarantor['ssn'].'" />
					<Employer Code="'.$employment['patient_employment_id'].'"
						Company="'.$employment['employer_name'].'"
						Address1="'.$employment['employer_address1'].'"
						Address2="'.$employment['employer_address2'].'"
						City="'.$employment['employer_city'].'"
						State="'.$employment['employer_state'].'"
						ZipCode="'.$employment['employer_zip'].'"
						Phone="" />';

			foreach($user['PatientInsurance'] as $policy)
			{
				$content .= '			
					<Policy Priority="'.$this->InsurPriority($policy['priority']).'"
						ID="'.$policy['insurance_info_id'].'"
						Group="'.$policy['group_name'].'"
						CoPayment="'.$policy['copay_amount'].'"
						Effective="'.$this->formatDate($policy['start_date']).'"
						Termination="'.$this->formatDate($policy['end_date']).'" >
						<Insurance Code="'.$policy['insurance_code'].'"
							Type="'.$policy['type'].'"
							Company="'.$policy['organization_name'].'"
							Address1=""
							City=""
							State=""
							ZipCode=""
							Phone="" />
						<Insured Code="'.$policy['policy_number'].'"
							Relation="'.$policy['EmdeonRelationship']['description'].'"
							FirstName="'.$policy['insured_first_name'].'"
							MiddleName="'.$policy['insured_middle_name'].'"
							LastName="'.$policy['insured_last_name'].'"
							Address1="'.$policy['insured_address_1'].'"
							Address2="'.$policy['insured_address_2'].'"
							City="'.$policy['insured_city'].'"
							State="'.$policy['insured_state'].'"
							ZipCode="'.$policy['insured_zip'].'"
							HomePhone="'.$this->formatPhone($policy['insured_home_phone_number']).'"	
							WorkPhone="'.$this->formatPhone($policy['insured_work_phone_number']).'"				
							Sex="'.$policy['insured_sex'].'"
							BirthDate="'.$this->formatDate($policy['insured_birth_date']).'" >
							<InsuredEmployer Code=""
								Company=""
								Address1=""
								Address2=""
								City=""
								State=""
								ZipCode=""
								Phone="" />
						</Insured>
					</Policy>';
			}
			
			$content .= "\n".'</Patient>';
			$content .= "\n".'</Demographic>';
			$content .= $this->appointment($demographic['patient_id'], $demographic);
			//$content .= $this->bill($demographic['patient_id'], $demographic, $encounter_id);
			//pr(htmlentities($content));exit;
			$this->createXml($content, $strDest.'/out/'.rand(99999, 9999999).'.xml'); 
			//exit;
		}
	}
	
	public function appointment($patient_id, $demographic=array())
	{
		$this->ScheduleCalendar = ClassRegistry::init('ScheduleCalendar');
		if($patient_id) {
			$schedules = $this->ScheduleCalendar->find('all', array('conditions' => array('ScheduleCalendar.patient_id' => $patient_id), 'callbacks' => false, 'recursive' => -1));
		} else {
			//$schedules = $this->ScheduleCalendar->find('all');
		}
		//pr($schedules);exit;
		$content = '';
		foreach($schedules as $schedule)
		{
			$content .=' 
			<Appointment Action="Add">
				<Patient ChartNumber="'.$demographic['xlink_chart_number'].'"
					LastName="'.$demographic['last_name'].'"
					FirstName="'.$demographic['first_name'].'"
					MiddleName="'.$demographic['middle_name'].'"
					BirthDate="'.$this->formatDate($demographic['dob']).'"
					Sex="'.$demographic['gender'].'"
					HomePhone="'.$this->formatPhone($demographic['home_phone']).'"
					WorkPhone="'.$this->formatPhone($demographic['work_phone']).'" />
				<Schedule Date="'.$this->formatDate($schedule['ScheduleCalendar']['date']).'"
					Time="'.$this->formatTime($schedule['ScheduleCalendar']['starttime']).'"
					Identifier="'.$schedule['ScheduleCalendar']['calendar_id'].'"
					Length="'.$schedule['ScheduleCalendar']['duration'].'"
					Provider="'.$schedule['ScheduleCalendar']['provider_id'].'"
					Resource=""
					Description="'.$schedule['ScheduleCalendar']['reason_for_visit'].'"
					Procedure=""/>
			</Appointment>';
		}
		//pr(htmlentities($content));
		return $content;
	}
	
	public function bill($patient_id, $demographic, $encounter_id='')
	{
		//ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED);
		$customer_folder = $this->customerPath();
		$strDest = '/home/'.$customer_folder;
		//Configure::write('debug', 0);
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
					'fields' => array('procedure_name', 'procedure_reason', 'procedure_comment', 'procedure_date_performed', 'fee', 'cpt', 'cpt_code') 
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
					'fields' => array('injection_name', 'injection_reason', 'injection_date_performed', 'fee', 'cpt', 'cpt_code') 
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
					'fields' => array('ScheduleCalendar.calendar_id', 'ScheduleCalendar.provider_id', 'Provider.xlink_id'),
					'joins' =>  array(
						array(
							'table' => 'user_accounts',
							'alias' => 'Provider',
						 	'type' => 'LEFT',
						 	'conditions' => array('Provider.user_id = ScheduleCalendar.provider_id')
						)
					)
				),
				'PatientDemographic' => array(
					'className' => 'PatientDemographic',
					'foreignKey' => 'patient_id',
					'fields' => array('patient_id', 'xlink_chart_number')
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
					
		//pr($bills);
		$content = '';
		foreach($bills as $bill)
		{
			$bill = $this->decodeItem($bill);
			$bill = $this->sanitizeHTML($bill);
			$provider = $bill['EncounterMaster']['ScheduleCalendar']['Provider']['xlink_id'];
			//pr($bill);exit;
			$content .=' 
			<Bill Action="Add"
				ID="'.$bill['EncounterSuperbill']['superbill_id'].'" >
				<Patient ChartNumber="'.$bill['EncounterMaster']['PatientDemographic']['xlink_chart_number'].'"/>
				<Encounter Identifier="'.$bill['EncounterMaster']['encounter_id'].'"
					BillingProvider="'.$provider.'"
					BillingDate="">';
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
				$lineItem['Provider'] = $provider;
				$lineItem['MoreDiagnosis'] = $assessmentDiagnosis;
				$content .= $this->lineItem($lineItem);
			}
			// bill item service_level_advanced
			if(is_array($bill['EncounterSuperbill']['service_level_advanced']))
			{
				foreach($bill['EncounterSuperbill']['service_level_advanced'] as $service_level_advanced) {
					$lineItem = $this->resetLineItem();
					preg_match("/^(.*)\s+\((.*)\)$/", $service_level_advanced, $matches);
					$lineItem['Procedure'] = $matches[1];
					$lineItem['Provider'] = $provider;
					$lineItem['MoreDiagnosis'] = $assessmentDiagnosis;
					$content .= $this->lineItem($lineItem);
				}
			}
			// bill item other_codes
			if(is_array($bill['EncounterSuperbill']['other_codes']))
			{
				foreach($bill['EncounterSuperbill']['other_codes'] as $other_codes) {					
					if(empty($other_codes['code'])) continue;
					$lineItem = $this->resetLineItem();
					$lineItem['Procedure'] = $other_codes['code'];
					$lineItem['Provider'] = $provider;
					$lineItem['MoreDiagnosis'] = $assessmentDiagnosis;
					$content .= $this->lineItem($lineItem);
				}
			}
			foreach	($bill['EncounterMaster']['EncounterImmunization'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['vaccine_name'];
				$lineItem['FromDate'] = $value['vaccine_date_performed'];
				$lineItem['ToDate'] = $value['vaccine_expiration_date'];
				$lineItem['Diagnosis'] = $value['vaccine_reason'];
				$lineItem['Provider'] = $provider;
				if(in_array($value['vaccine_name'], $bill['EncounterSuperbill']['ignored_in_house_immunizations'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$content .= $this->lineItem($lineItem);
			}
			foreach	($bill['EncounterMaster']['EncounterLabs'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['lab_test_name'];
				$lineItem['FromDate'] = $value['lab_date_performed'];
				$lineItem['Diagnosis'] = $value['lab_reason'];
				$lineItem['Provider'] = $provider;
				$lineItem['Units'] = $value['lab_unit'];
				if(in_array($value['lab_test_name'], $bill['EncounterSuperbill']['ignored_in_house_labs'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$content .= $this->lineItem($lineItem);
			}	
			foreach	($bill['EncounterMaster']['EncounterRadiology'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['radiology_procedure_name'];
				$lineItem['FromDate'] = $value['radiology_date_performed'];
				$lineItem['Diagnosis'] = $value['radiology_reason'];
				$lineItem['Provider'] = $provider;
				if(in_array($value['radiology_procedure_name'], $bill['EncounterSuperbill']['ignored_in_house_radiologies'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$content .= $this->lineItem($lineItem);
			}	
			foreach	($bill['EncounterMaster']['EncounterProcedure'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['procedure_name'];
				$lineItem['FromDate'] = $value['procedure_date_performed'];
				$lineItem['Diagnosis'] = $value['procedure_reason'];
				$lineItem['Provider'] = $provider;
				if(in_array($value['procedure_name'], $bill['EncounterSuperbill']['ignored_in_house_procedures'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$content .= $this->lineItem($lineItem);
			}
			foreach	($bill['EncounterMaster']['EncounterMed'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['drug'];
				$lineItem['FromDate'] = $value['drug_date_given'];
				$lineItem['Diagnosis'] = $value['drug_reason'];
				$lineItem['Units'] = $value['quantity'];
				$lineItem['Provider'] = $provider;
				if(in_array($value['drug'], $bill['EncounterSuperbill']['ignored_in_house_meds'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$content .= $this->lineItem($lineItem);
			}
			foreach	($bill['EncounterMaster']['EncounterInjection'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['injection_name'];
				$lineItem['FromDate'] = $value['injection_date_performed'];
				$lineItem['Diagnosis'] = $value['injection_reason'];
				$lineItem['Provider'] = $provider;
				if(in_array($value['injection_name'], $bill['EncounterSuperbill']['ignored_in_house_injections'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$content .= $this->lineItem($lineItem);
			}
			foreach	($bill['EncounterMaster']['EncounterSupply'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['supply_name'];
				$lineItem['FromDate'] = '';
				$lineItem['Diagnosis'] = '';
				$lineItem['Units'] = $value['supply_quantity'];
				$lineItem['Provider'] = $provider;
				if(in_array($value['supply_name'], $bill['EncounterSuperbill']['ignored_in_house_supplies'])) {
					$value['fee'] = '0.00';
				}
				$lineItem['Charge'] = $value['fee'];
				$content .= $this->lineItem($lineItem);
			}			
			foreach	($bill['EncounterMaster']['EncounterPlanLab'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['test_name'];
				$lineItem['Diagnosis'] = $value['diagnosis'];
				$lineItem['Provider'] = $provider;
				$content .= $this->lineItem($lineItem);
			}
			foreach	($bill['EncounterMaster']['EncounterPlanRadiology'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['procedure_name'];
				$lineItem['Diagnosis'] = $value['diagnosis'];
				$lineItem['Provider'] = $provider;
				$content .= $this->lineItem($lineItem);
			}
			foreach	($bill['EncounterMaster']['EncounterPlanProcedure'] as $value) {
				$lineItem = $this->resetLineItem();
				$lineItem['Procedure'] = $this->getCode($value);
				$lineItem['Description'] = $value['test_name'];
				$lineItem['Diagnosis'] = $value['diagnosis'];
				$lineItem['Provider'] = $provider;
				$content .= $this->lineItem($lineItem);
			}
			$content .='</Encounter>
			</Bill>';
			//pr(htmlentities($content));exit;
			$this->createXml($content, $strDest.'/out/'.rand(99999, 9999999).'.xml');
		}
		return $content;
	}
	
	public function lineItem($lineItem)
	{
		preg_match("/^(.*)\s+\[(.*)\]$/", $lineItem['Diagnosis'], $matches);
		if(isset($matches[2]))
			$diagnosis = $matches[2];
		else
			$diagnosis = $lineItem['Diagnosis'];
		$moreDiagnosis = '';
		if(isset($lineItem['MoreDiagnosis'])) {
			foreach($lineItem['MoreDiagnosis'] as $eachDiagnosis)
				$moreDiagnosis .= '<Diagnosis Code="'.$eachDiagnosis.'"/>';
		}
		$lineItemTag = '
					<LineItem Charge="'.$lineItem['Charge'].'"
						Description="'.$lineItem['Description'].'"
						FromDate="'.$lineItem['FromDate'].'"
						ToDate="'.$lineItem['ToDate'].'"
						Identifier="'.$lineItem['Identifier'].'"
						PlaceOfService="'.$lineItem['PlaceOfService'].'"
						Procedure="'.$lineItem['Procedure'].'"
						Provider="'.$lineItem['Provider'].'"
						Units="'.$lineItem['Units'].'" >
						<Diagnosis Code="'.$diagnosis.'"/>'
						.$moreDiagnosis.'
					</LineItem>
		';
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
	
	public function gender($g, $reformat=false)
	{
		$gender = array('F'=>'Female','M'=>'Male');
		if($g && $reformat) {
			$status = array_search($g, $gender);
			$status = ($status)? $status : $g;
			return $status;
		}
		if($g) {
			$status = (isset($gender[$g]))? $gender[$g] : $g;
			return $status;
		}
	}
	
	public function connectXlink()
	{
		$settings = ClassRegistry::init('PracticeSetting')->find('first', array('fields' => array('xlink_status', 'xlink_hostname', 'xlink_username', 'xlink_password', 'practice_id')));
		$this->settings = $settings['PracticeSetting'];
		if($this->settings['xlink_status']) {
			set_include_path(get_include_path() . PATH_SEPARATOR . WWW_ROOT . 'phpseclib');
			include('Net/SFTP.php');
			/*$strHost = 'xlink.onetouchemr.com';
			$strUser = 'xlink';
			$strPassword = 'x@63m1n1a$0';*/
			$strHost = $this->settings['xlink_hostname'];
			$strUser = $this->settings['xlink_username'];
			$strPassword = $this->settings['xlink_password'];
	
			$this->sftp = new Net_SFTP($strHost);
			if ($this->sftp->login($strUser, $strPassword)) {
				return true;
			} else {
				return false;
			}	
		} else {
			return false;
		}
	}
	
	public function createXml($content, $file)
	{
		//echo $content;exit;
		$content = '<?xml version="1.0" encoding="utf-8"?>'. $content;
		$strDest = $file;
		$sftp = $this->sftp;
		$sftp->pwd() . "\r\n";
		$isUploaded = $sftp->put($strDest, $content);
		if($isUploaded) {
			//echo 'Uploaded<br>';
		}	
	}
	
	public function customerPath()
	{
		global $argv;
		$server_name = isset($_SERVER['SERVER_NAME'])? $_SERVER['SERVER_NAME'] : '';
		list($customer,)= explode('.', $server_name);
    $customer = strtolower($customer);
		$practice_settings = ClassRegistry::init('PracticeSetting')->getSettings();
		if($this->settings['practice_id'] && strlen(Inflector::slug($this->settings['practice_id'])) > 0) {
			$practice_folder = Inflector::slug($this->settings['practice_id']);
		}else if($customer) {
			$practice_folder = $customer;
		} else if(isset($argv[4])) {
			$practice_folder = $argv[4];
		}		
		if($practice_folder == 'tw' || $practice_folder == 'onetouch_devel' || $practice_folder == 'localhost') 
			$practice_folder = 'xlink';
		
		return $practice_folder;
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
	
	function getFiles($path)
	{
		return $this->sftp->nlist($path);
		//return $files = glob(WWW_ROOT.'xlink'. DS . "*.txt");
	}
	
	function import()
	{
		//ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED);
		$this->PatientDemographic = ClassRegistry::init('PatientDemographic');
		$this->PatientInsurance = ClassRegistry::init('PatientInsurance');
		$this->ScheduleCalendar = ClassRegistry::init('ScheduleCalendar');
		$this->PracticeLocation = ClassRegistry::init('PracticeLocation');
		$this->UserAccount = ClassRegistry::init('UserAccount');
		$this->PatientSocialHistory = ClassRegistry::init('PatientSocialHistory');
		
		$this->scheduleLocation = $this->PracticeLocation->find('first', array('fields' => array('location_id','default_visit_duration'), 'order' => 'location_id', 'recursive' => -1));
		//EMR_Roles::SYSTEM_ADMIN_ROLE_ID;
		$adminAcc = $this->UserAccount->find('first', array(
			'conditions' => array('role_id' => 10), 'fields' => array('user_id'), 'recursive' => -1
		));
		$this->adminId = $adminAcc['UserAccount']['user_id'];
		App::import('Xml');
		$customer_folder = $this->customerPath();
		$file_path = '/home/'.$customer_folder.'/in/';
		$files = $this->getFiles($file_path);
		//Configure::write('debug', 0);
		//pr($files);exit;	
		foreach($files as $file) {
			if($file=='.' || $file=='..') {
				continue;
			}
			$xml_content = $this->sftp->get($file_path.$file);
			$xml =& new XML($xml_content);
			$xml = Set::reverse($xml);
    		//pr($xml);exit;
			if(isset($xml['Demographic'])) {
				$this->import_demographic($xml['Demographic']);
			} elseif(isset($xml['Appointment'])) {
				$this->import_schedule($xml['Appointment']);
			}
			//$this->sftp->delete('/home/'.$customer_folder.'/in/'.$file);
		}	
	}
	
	function import_demographic($data)
	{		
		$patient = $data['Patient'];		
		$demographicData = array(
			'first_name' => $patient['FirstName'],
			'middle_name' => $patient['MiddleName'],
			'last_name' => $patient['LastName'],
			'gender' => $this->gender($patient['Sex'], true),
			'dob' => $this->formatDate($patient['BirthDate'], true),
			'ssn' => $patient['SSN'],
			'address1' => $patient['Address1'],
			'address2' => $patient['Address2'],
			'city' => $patient['City'],
			'state' => $patient['State'],
			'zipcode' => $patient['ZipCode'],
			'work_phone' => $this->formatPhone($patient['WorkPhone'], true),
			'home_phone' => $this->formatPhone($patient['HomePhone'], true),
			'race' => $patient['Race'],
			'ethnicity' => $patient['Ethnicity'],
			'preferred_language' => $patient['Language'],
			'xlink_chart_number' => $patient['ChartNumber'],
			'modified_timestamp' => __date("Y-m-d H:i:s"),
			'modified_user_id' => $this->adminId
		);
		$checkChartNum = $this->PatientDemographic->find('first', array('fields' => array('patient_id'), 'conditions' => array('xlink_chart_number = DES_ENCRYPT(\''.$patient['ChartNumber'].'\')'), 'callbacks' => false, 'recursive' => -1));
				
		if(isset($checkChartNum['PatientDemographic']['patient_id']) && $checkChartNum['PatientDemographic']['patient_id']) {
			$demographicData['patient_id'] = $checkChartNum['PatientDemographic']['patient_id'];
		} else {
			$mrn = $this->PatientDemographic->find('first', array('fields' => array('max(mrn) as newMRN'), 'callbacks' => false, 'recursive' => -1));
			$demographicData['mrn'] = $mrn[0]['newMRN'] + 1;
			$demographicData['status'] = 'New';			
		}
		//pr($demographicData);exit;
		$_SESSION['UserAccount']['user_id'] = $this->adminId;
		$this->PatientDemographic->save($demographicData);
		$this->patientId = $this->PatientDemographic->id;
		if(isset($data['Patient']['Policy'][0])) {
			foreach($data['Patient']['Policy'] as $policy) {
				$this->import_policy($policy);
			}
		} else {
			$this->import_policy($data['Patient']['Policy']);
		}
		if(isset($patient['Marital']) && $patient['Marital']) {
			$socialHistoryData = array(
				'patient_id' => $this->patientId,
				'type' => 'Marital Status',
				'marital_status' => $this->maritalStatus($patient['Marital'], true),
				'modified_timestamp' => __date("Y-m-d H:i:s"),
				'modified_user_id' => $this->adminId
			);
			$this->PatientSocialHistory->save($socialHistoryData, array('callbacks' => false));
			unset($this->PatientSocialHistory->id);
		}
		unset($this->PatientDemographic->id);
	}
	
	function import_policy($policy)
	{		
		if(is_array($policy)===false) {
			return;
		}
		$relationship = $this->PatientInsurance->EmdeonRelationship->field(
			'code', array('description' => $policy['Insured']['Relation'])
		);
		$insuranceData = array(
			'patient_id' => $this->patientId,  
			'priority' => $this->InsurPriority($policy['Priority'], true),
			'group_name' => $policy['Group'],
			'copay_amount' => $policy['CoPayment'],
			'start_date' => $this->formatDate($policy['Effective'], true),
			'end_date' => $this->formatDate($policy['Termination'], true),
			'insurance_code' => $policy['Insurance']['Code'],
			'type' => $policy['Insurance']['Type'],
			'organization_name' => $policy['Insurance']['Company'],
			'policy_number' => $policy['Insured']['Code'],
			'relationship' => $relationship,
			'insured_first_name' => $policy['Insured']['FirstName'],
			'insured_middle_name' => $policy['Insured']['MiddleName'],
			'insured_last_name' => $policy['Insured']['LastName'],
			'insured_address_1' => $policy['Insured']['Address1'],
			'insured_address_2' => $policy['Insured']['Address2'],
			'insured_city' => $policy['Insured']['City'],
			'insured_state' => $policy['Insured']['State'],
			'insured_zip' => $policy['Insured']['ZipCode'],
			'insured_home_phone_number' => $this->formatPhone($policy['Insured']['HomePhone'], true),
			'insured_work_phone_number' => $this->formatPhone($policy['Insured']['WorkPhone'], true),
			'insured_sex' => $policy['Insured']['Sex'],
			'insured_birth_date' => $this->formatDate($policy['Insured']['BirthDate'], true),
			'modified_timestamp' => __date("Y-m-d H:i:s"),
			'modified_user_id' => $this->adminId
		);
		if(isset($policy['ID']) && $policy['ID']) {
			$insuranceData['insurance_info_id'] = $policy['ID'];
		}		
		$this->PatientInsurance->save($insuranceData, array('callbacks' => false));		
		unset($this->PatientInsurance->id);		
	}
	
	function import_schedule($schedule)
	{		
		//pr($schedule);exit;
		if(isset($schedule['Schedule']['Length'])===false || $schedule['Schedule']['Length']=='') {
			$schedule['Schedule']['Length'] = 0;
		}
		$provider = $this->UserAccount->find('first', array(
		   'conditions' => array('xlink_id' => $schedule['Schedule']['Provider']), 'fields' => array('user_id'), 'recursive' => -1
		));
		$checkChartNum = $this->PatientDemographic->find('first', array('fields' => array('patient_id'), 'conditions' => array('xlink_chart_number = DES_ENCRYPT(\''.$schedule['Patient']['ChartNumber'].'\')'), 'callbacks' => false, 'recursive' => -1));
				
		if(!isset($checkChartNum['PatientDemographic']['patient_id']) || empty($checkChartNum['PatientDemographic']['patient_id']) || !isset($provider['UserAccount']['user_id']) || empty($provider['UserAccount']['user_id'])) {
			return;
		} 
		$scheduleData = array(
			'patient_id' => $checkChartNum['PatientDemographic']['patient_id'],
			'provider_id' => $provider['UserAccount']['user_id'],
			'reason_for_visit' => $schedule['Schedule']['Note'],
			'duration' => $schedule['Schedule']['Length'],
			'starttime' => $schedule['Schedule']['Time'],
			'date' => $this->formatDate($schedule['Schedule']['Date'], true),
			'endtime' => __date('H:i', strtotime($schedule['Schedule']['Time'] . "+{$schedule['Schedule']['Length']} minutes")),
			'location' => $this->scheduleLocation['PracticeLocation']['location_id'],
			'modified_timestamp' => __date("Y-m-d H:i:s"),
			'modified_user_id' => $this->adminId
		);
		//pr($scheduleData );exit;
		if(isset($schedule['Identifier']) && $schedule['Identifier']) {
			$scheduleData['calendar_id'] = $policy['Identifier'];
		}			
		$this->ScheduleCalendar->save($scheduleData, array('callbacks' => false));		
		unset($this->ScheduleCalendar->id);
	}
		
}