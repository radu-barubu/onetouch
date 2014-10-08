<?php

class CCR
{
	public static function getUuid()
	{
		// The field names refer to RFC 4122 section 4.1.2
		
		return sprintf('A%04x%04x-%04x-%03x4-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
		mt_rand(0, 65535), // 16 bits for "time_mid"
		mt_rand(0, 4095), // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
		bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)), // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
		// (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
		// 8 bits for "clk_seq_low"
		mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));// 48 bits for "node");
	}
	
	public static function sourceType($ccr, $uuid)
	{
		$e_Source = $ccr->createElement('Source');
		
		$e_Actor = $ccr->createElement('Actor');
		$e_Source->appendChild($e_Actor);
		
		$e_ActorID = $ccr->createElement('ActorID', $uuid);
		$e_Actor->appendChild($e_ActorID);
		
		return $e_Source;
	}
				
	public static function generateCCR(&$controller, $patient_id = 0, $output_as_file = true, $append_xsl = false)
	{
		$authorID = self::getUuid();
		$patientID = self::getUuid();
		$sourceID = self::getUuid();
		$oemrID = self::getUuid();
		$uuid = self::getUuid();
		
		$controller->loadModel("PracticeLocation");
		$user = $controller->Session->read('UserAccount');
		$controller->loadModel("EncounterMaster");
		$controller->loadModel("PatientAdvanceDirective");
		$controller->loadModel("ScheduleCalendar");
		$controller->loadModel("UserRole");
		$controller->loadModel("PatientProblemList");
		$controller->loadModel("EncounterPointOfCare");
		$controller->loadModel("PatientMedicationList");
		$controller->loadModel("PatientAllergy");
		$controller->loadModel("PatientLabResult");
		
		$encounter_id = (isset($controller->params['named']['encounter_id']) ? $controller->params['named']['encounter_id'] : "");
		
		if($encounter_id != "") //encounter id defined, patient id not defined
		{
			$patient_id = $controller->EncounterMaster->getPatientID($encounter_id);
		}
		else //patient id defined, encounter_id not defined
		{
			$encounter_id = $controller->EncounterMaster->getEncountersByPatientID($patient_id);
		}
		
		$location = $controller->PracticeLocation->find('all', array('conditions' => array('PracticeLocation.location_id' => $user['work_location'])));
		$encounter = $controller->EncounterMaster->find('all', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id)));
		$advancedirectives = $controller->PatientAdvanceDirective->find('all', array('conditions' => array('PatientAdvanceDirective.patient_id' => $patient_id)));
		$schedulecalendarlist = $controller->ScheduleCalendar->find('all', array('conditions' => array('ScheduleCalendar.calendar_id' => $encounter[0]['EncounterMaster']['calendar_id'])));
		$userrolelist = $controller->UserRole->find('all', array('conditions' => array('UserRole.role_id' => $schedulecalendarlist[0]['UserAccount']['role_id'])));
		$problemlist = $controller->PatientProblemList->find('all', array('conditions' => array('PatientProblemList.patient_id' => $patient_id)));
		$medicationlist = $controller->PatientMedicationList->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $patient_id)));
		$allergies = $controller->PatientAllergy->find('all', array('conditions' => array('PatientAllergy.patient_id' => $patient_id)));
		$lab_results = $controller->PatientLabResult->find('all', array('conditions' => array('PatientLabResult.patient_id' => $patient_id)));
		$procedurelist = $controller->EncounterPointOfCare->find('all', array('conditions' => array("AND" => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'order_type' => 'Procedure'))));
		$immunizationlist = $controller->EncounterPointOfCare->find('all', array('conditions' => array("AND" => array('EncounterPointOfCare.encounter_id' => $encounter_id, 'order_type' => 'Immunization'))));
		
		$ccr = new DOMDocument('1.0', 'UTF-8');
		
		if($append_xsl)
		{
			$http_str = 'http://';
			if($_SERVER['SERVER_PORT'] == 443)
			{
				$http_str = 'https://';
			}
			
			$e_styleSheet = $ccr->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'.$controller->webroot.'reports/information_exchange_results/task:get_ccr_xsl/'.'"');
			$ccr->appendChild($e_styleSheet);
		}
		
		$e_ccr = $ccr->createElementNS('urn:astm-org:CCR', 'ContinuityOfCareRecord');
		$ccr->appendChild($e_ccr);
		
		/////////////// Header
		$e_ccrDocObjID = $ccr->createElement('CCRDocumentObjectID', self::getUuid());
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
		
		//$e_ActorID = $ccr->createElement('ActorID', $encounter[0]['PatientDemographic']['patient_id']);
		$e_ActorID = $ccr->createElement('ActorID', $uuid);
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
		
		//$e_ActorID = $ccr->createElement('ActorID', $encounter[0]['PatientDemographic']['patient_id']);
		$e_ActorID = $ccr->createElement('ActorID', $uuid);
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
		
		
		/////////////// Advance Directives
		if(count($advancedirectives) > 0):
		$e_AdvanceDirectives = $ccr->createElement('AdvanceDirectives');
		foreach ($advancedirectives as $advancedirective) : 
		
		$e_AdvanceDirective = $ccr->createElement('AdvanceDirective');
		$e_AdvanceDirectives->appendChild($e_AdvanceDirective);
		
		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', self::getUuid());
		$e_AdvanceDirective->appendChild($e_CCRDataObjectID);
		
		$e_DateTime = $ccr->createElement('DateTime');
		$e_AdvanceDirective->appendChild($e_DateTime);
		
		$e_Type = $ccr->createElement('Type');
		$e_DateTime->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', 'Recorded Date');
		$e_Type->appendChild($e_Text);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($advancedirective['PatientAdvanceDirective']['service_date'])));
		$e_DateTime->appendChild($e_ExactDateTime);
		
		$e_IDs = $ccr->createElement('IDs');
		$e_AdvanceDirective->appendChild($e_IDs);
		
		$e_ID = $ccr->createElement('ID', $advancedirective['PatientAdvanceDirective']['patient_id']);
		$e_IDs->appendChild($e_ID);
		
		$e_IDs->appendChild(self::sourceType($ccr, $sourceID));
		
		$e_Type = $ccr->createElement('Type');
		$e_AdvanceDirective->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', $advancedirective['PatientAdvanceDirective']['directive_name']);
		$e_Type->appendChild($e_Text);
		
		$e_Description = $ccr->createElement('Description');
		$e_AdvanceDirective->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', $advancedirective['PatientAdvanceDirective']['description']);
		$e_Description->appendChild($e_Text);
		
		$e_Status = $ccr->createElement('Status');
		$e_AdvanceDirective->appendChild($e_Status);
		
		$e_Text = $ccr->createElement('Text', $advancedirective['PatientAdvanceDirective']['status']);
		$e_Status->appendChild($e_Text);
		
		$e_Source = $ccr->createElement('Source');
		
		$e_Actor = $ccr->createElement('Actor');
		$e_Source->appendChild($e_Actor);
		
		$e_ActorID = $ccr->createElement('ActorID', $uuid);
		$e_Actor->appendChild($e_ActorID);
		
		$e_AdvanceDirective->appendChild($e_Source);
		endforeach;
		$e_Body->appendChild($e_AdvanceDirectives);
		endif;
		
		/////////////// Support Providers
		
		$e_Support = $ccr->createElement('Support');
		$e_Body->appendChild($e_Support);
		
		$e_SupportProvider = $ccr->createElement('SupportProvider');
		$e_Support->appendChild($e_SupportProvider);
		
		$e_ActorID = $ccr->createElement('ActorID', $uuid);
		$e_SupportProvider->appendChild($e_ActorID);
		
		$e_ActorRole = $ccr->createElement('ActorRole');
		$e_SupportProvider->appendChild($e_ActorRole);
		
		$e_Text = $ccr->createElement('Text', $userrolelist[0]['UserRole']['role_desc']);
		$e_ActorRole->appendChild($e_Text);
		
		
		/////////////// Problems
		if(count($problemlist) > 0):
		$e_Problems = $ccr->createElement('Problems');
		$pCount = 0;
		foreach ($problemlist as $problemlist)
			: $pCount++;
		
		$e_Problem = $ccr->createElement('Problem');
		$e_Problems->appendChild($e_Problem);
		
		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', 'PROB' . $pCount);
		$e_Problem->appendChild($e_CCRDataObjectID);
		
		$e_DateTime = $ccr->createElement('DateTime');
		$e_Problem->appendChild($e_DateTime);
		
		$e_Type = $ccr->createElement('Type');
		$e_DateTime->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', 'Start Date');
		$e_Type->appendChild($e_Text);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($problemlist['PatientProblemList']['start_date'])));
		$e_DateTime->appendChild($e_ExactDateTime);
		
		$e_IDs = $ccr->createElement('IDs');
		$e_Problem->appendChild($e_IDs);
		
		$e_ID = $ccr->createElement('ID', $problemlist['PatientProblemList']['patient_id']);
		$e_IDs->appendChild($e_ID);
		
		$e_IDs->appendChild(self::sourceType($ccr, $sourceID));
		
		$e_Type = $ccr->createElement('Type');
		$e_Problem->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', 'Diagnosis');
		$e_Type->appendChild($e_Text);
		
		$e_Description = $ccr->createElement('Description');
		$e_Problem->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', $problemlist['PatientProblemList']['diagnosis']);
		$e_Description->appendChild($e_Text);
		
		$e_Code = $ccr->createElement('Code');
		$e_Description->appendChild($e_Code);
		
		$e_Value = $ccr->createElement('Value', $problemlist['PatientProblemList']['icd_code']);
		$e_Code->appendChild($e_Value);
		
		$e_Value = $ccr->createElement('CodingSystem', 'ICD9-CM');
		$e_Code->appendChild($e_Value);
		
		$e_Version = $ccr->createElement('Version', '2005');
		$e_Code->appendChild($e_Version);
		
		$e_Status = $ccr->createElement('Status');
		$e_Problem->appendChild($e_Status);
		
		$e_Text = $ccr->createElement('Text', $problemlist['PatientProblemList']['status']);
		$e_Status->appendChild($e_Text);
		
		$e_Source = $ccr->createElement('Source');
		
		$e_Actor = $ccr->createElement('Actor');
		$e_Source->appendChild($e_Actor);
		
		$e_ActorID = $ccr->createElement('ActorID', $uuid);
		$e_Actor->appendChild($e_ActorID);
		
		$e_Problem->appendChild($e_Source);
		
		$e_CommentID = $ccr->createElement('CommentID', $problemlist['PatientProblemList']['comment']);
		$e_Problem->appendChild($e_CommentID);
		
		$e_Episodes = $ccr->createElement('Episodes');
		$e_Problem->appendChild($e_Episodes);
		
		$e_Number = $ccr->createElement('Number');
		$e_Episodes->appendChild($e_Number);
		
		$e_Episode = $ccr->createElement('Episode');
		$e_Episodes->appendChild($e_Episode);
		
		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', 'EP' . $pCount);
		$e_Episode->appendChild($e_CCRDataObjectID);
		
		$e_Episode->appendChild(self::sourceType($ccr, $sourceID));
		
		$e_Episodes->appendChild(self::sourceType($ccr, $sourceID));
		
		$e_HealthStatus = $ccr->createElement('HealthStatus');
		$e_Problem->appendChild($e_HealthStatus);
		
		$e_DateTime = $ccr->createElement('DateTime');
		$e_HealthStatus->appendChild($e_DateTime);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime');
		$e_DateTime->appendChild($e_ExactDateTime);
		
		$e_Description = $ccr->createElement('Description');
		$e_HealthStatus->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', $problemlist['PatientProblemList']['diagnosis']);
		$e_Description->appendChild($e_Text);
		
		$e_HealthStatus->appendChild(self::sourceType($ccr, $sourceID));
		endforeach;
		$e_Body->appendChild($e_Problems);
		endif;
		
		////allergy
		if(count($allergies) > 0):
		$e_Allergies = $ccr->createElement('Alerts');
		
		foreach($allergies as $allergy):
		$e_allergy = $ccr->createElement('Alert');
		$e_Allergies->appendChild($e_allergy);
		
		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', self::getUuid());
		$e_allergy->appendChild($e_CCRDataObjectID);
		
		$e_DateTime = $ccr->createElement('DateTime');
		$e_allergy->appendChild($e_DateTime);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($allergy['PatientAllergy']['modified_timestamp'])));
		$e_DateTime->appendChild($e_ExactDateTime);
		
		$e_Type = $ccr->createElement('Type');
		$e_allergy->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', 'Allergy');
		$e_Type->appendChild($e_Text);
		
		$e_Description = $ccr->createElement('Description');
		$e_allergy->appendChild($e_Description);

		$e_Text = $ccr->createElement('Text', $allergy['PatientAllergy']['agent']);
		$e_Description->appendChild($e_Text);
		
		$e_Code = $ccr->createElement('Code');
		$e_Description->appendChild($e_Code);
		
		$e_Value = $ccr->createElement('Value', $allergy['PatientAllergy']['snowmed']);
		$e_Code->appendChild($e_Value);
		
		$e_CodingSystem = $ccr->createElement('CodingSystem', 'SNOMED');
		$e_Code->appendChild($e_CodingSystem);
		
		$e_Status = $ccr->createElement('Status');
		$e_allergy->appendChild($e_Status);
		
		$e_Text = $ccr->createElement('Text', $allergy['PatientAllergy']['status']);
		$e_Status->appendChild($e_Text);
		
		$e_allergy->appendChild(self::sourceType($ccr, $sourceID));
		
		$e_Agent = $ccr->createElement('Agent');
		$e_allergy->appendChild($e_Agent);
		
		$e_Products = $ccr->createElement('Products');
		$e_Agent->appendChild($e_Products);
		
		$e_Product = $ccr->createElement('Product');
		$e_Products->appendChild($e_Product);
		
		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', self::getUuid());
		$e_Product->appendChild($e_CCRDataObjectID);
		
		$e_Description = $ccr->createElement('Description');
		$e_Product->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', $allergy['PatientAllergy']['agent']);
		$e_Description->appendChild($e_Text);
		
		$e_Product->appendChild(self::sourceType($ccr, $sourceID));
		
		$e_Product2 = $ccr->createElement('Product');
		$e_Product->appendChild($e_Product2);
		
		$e_ProductName = $ccr->createElement('ProductName');
		$e_Product2->appendChild($e_ProductName);
		
		$e_Text = $ccr->createElement('Text', $allergy['PatientAllergy']['agent']);
		$e_ProductName->appendChild($e_Text);
		
		$e_Reaction = $ccr->createElement('Reaction');
		$e_allergy->appendChild($e_Reaction);
		
		$e_Description = $ccr->createElement('Description');
		$e_Reaction->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', $allergy['PatientAllergy']['reaction1']);
		$e_Description->appendChild($e_Text);
		
		$e_Severity = $ccr->createElement('Severity');
		$e_Reaction->appendChild($e_Severity);
		
		$e_Text = $ccr->createElement('Text', $allergy['PatientAllergy']['severity1']);
		$e_Severity->appendChild($e_Text);
		
		endforeach;
		
		$e_Body->appendChild($e_Allergies);
		endif;
		
		////////////////// Medication
		if(count($medicationlist) > 0):
		$e_Medications = $ccr->createElement('Medications');
		$pCount = 0;
		foreach ($medicationlist as $medicationlist)
			: $e_Medication = $ccr->createElement('Medication');
		$e_Medications->appendChild($e_Medication);
		
		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', self::getUuid());
		$e_Medication->appendChild($e_CCRDataObjectID);
		
		$e_DateTime = $ccr->createElement('DateTime');
		$e_Medication->appendChild($e_DateTime);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($medicationlist['PatientMedicationList']['start_date'])));
		$e_DateTime->appendChild($e_ExactDateTime);
		
		$e_Type = $ccr->createElement('Type');
		$e_Medication->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', 'Medication');
		$e_Type->appendChild($e_Text);
		
		$e_Status = $ccr->createElement('Status');
		$e_Medication->appendChild($e_Status);
		
		$e_Text = $ccr->createElement('Text', $medicationlist['PatientMedicationList']['status']);
		$e_Status->appendChild($e_Text);
		
		$e_Medication->appendChild(self::sourceType($ccr, $sourceID));
		
		$e_Product = $ccr->createElement('Product');
		$e_Medication->appendChild($e_Product);
		
		$e_ProductName = $ccr->createElement('ProductName');
		$e_Product->appendChild($e_ProductName);
		
		$e_Text = $ccr->createElement('Text', $medicationlist['PatientMedicationList']['medication']);
		$e_ProductName->appendChild(clone $e_Text);
		
		$e_Code = $ccr->createElement('Code');
		$e_ProductName->appendChild($e_Code);
		
		$e_Value = $ccr->createElement('Value', $medicationlist['PatientMedicationList']['rxnorm']);
		$e_Code->appendChild($e_Value);
		
		$e_Value = $ccr->createElement('CodingSystem', 'RxNorm');
		$e_Code->appendChild($e_Value);
		
		$e_Strength = $ccr->createElement('Strength');
		$e_Product->appendChild($e_Strength);
		
		$e_Value = $ccr->createElement('Value', $medicationlist['PatientMedicationList']['medication_strength_value']);
		$e_Strength->appendChild($e_Value);
		
		$e_Units = $ccr->createElement('Units');
		$e_Strength->appendChild($e_Units);
		
		$e_Unit = $ccr->createElement('Unit', $medicationlist['PatientMedicationList']['medication_strength_unit']);
		$e_Units->appendChild($e_Unit);
		
		$e_Form = $ccr->createElement('Form');
		$e_Product->appendChild($e_Form);
		
		$e_Text = $ccr->createElement('Text', $medicationlist['PatientMedicationList']['medication_form']);
		$e_Form->appendChild($e_Text);
		
		$e_Quantity = $ccr->createElement('Quantity');
		$e_Medication->appendChild($e_Quantity);
		
		$e_Value = $ccr->createElement('Value', $medicationlist['PatientMedicationList']['quantity_value']);
		$e_Quantity->appendChild($e_Value);
		
		$e_Units = $ccr->createElement('Units');
		$e_Quantity->appendChild($e_Units);
		
		$e_Unit = $ccr->createElement('Unit', $medicationlist['PatientMedicationList']['quantity_unit']);
		$e_Units->appendChild($e_Unit);
		
		/* directions */
		
		$e_Directions = $ccr->createElement('Directions');
		$e_Medication->appendChild($e_Directions);
		
		$e_Direction = $ccr->createElement('Direction');
		$e_Directions->appendChild($e_Direction);
			
		if(strlen($medicationlist['PatientMedicationList']['direction']) > 0)
		{
			$e_Description = $ccr->createElement('Description');
			$e_Direction->appendChild($e_Description);
			
			$e_Text = $ccr->createElement('Text', $medicationlist['PatientMedicationList']['direction']);
			$e_Description->appendChild($e_Text);
			
			$e_DoseIndicator = $ccr->createElement('DoseIndicator');
			$e_Direction->appendChild($e_DoseIndicator);
			
			$e_Text = $ccr->createElement('Text', '');
			$e_DoseIndicator->appendChild($e_Text);
		}
		else
		{
			$e_Dose = $ccr->createElement('Dose');
			$e_Direction->appendChild($e_Dose);
			
			$e_Value = $ccr->createElement('Value', $medicationlist['PatientMedicationList']['quantity']);
			$e_Dose->appendChild($e_Value);
			
			$e_Units = $ccr->createElement('Units');
			$e_Dose->appendChild($e_Value);
			
			$e_Unit = $ccr->createElement('Unit', $medicationlist['PatientMedicationList']['unit']);
			$e_Units->appendChild($e_Unit);
			
			$e_Route = $ccr->createElement('Route');
			$e_Direction->appendChild($e_Route);
			
			$e_Text = $ccr->createElement('Text', $medicationlist['PatientMedicationList']['route']);
			$e_Route->appendChild($e_Text);
			
			$e_Frequency = $ccr->createElement('Frequency');
			$e_Direction->appendChild($e_Frequency);
			
			$e_Value = $ccr->createElement('Value', $medicationlist['PatientMedicationList']['frequency']);
			$e_Frequency->appendChild($e_Value);
		}
		
		/* patient instructions */
		$e_PatientInstructions = $ccr->createElement('PatientInstructions');
		$e_Medication->appendChild($e_PatientInstructions);
		
		$e_Instruction = $ccr->createElement('Instruction');
		$e_PatientInstructions->appendChild($e_Instruction);
		
		$e_Text = $ccr->createElement('Text', '');
		$e_Instruction->appendChild($e_Text);
		
		/* refills */
		$e_Refills = $ccr->createElement('Refills');
		$e_Medication->appendChild($e_Refills);
		
		$e_Refill = $ccr->createElement('Refill');
		$e_Refills->appendChild($e_Refill);
		
		$e_Number = $ccr->createElement('Number', 0);
		$e_Refill->appendChild($e_Number);
		endforeach;
		$e_Body->appendChild($e_Medications);
		endif;
		
		///////////////// Immunization
		if(count($immunizationlist) > 0):
		$e_Immunizations = $ccr->createElement('Immunizations');
		foreach ($immunizationlist as $immunizationlist)
			: $e_Immunization = $ccr->createElement('Immunization');
		$e_Immunizations->appendChild($e_Immunization);
		
		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', self::getUuid());
		$e_Immunization->appendChild($e_CCRDataObjectID);
		
		$e_DateTime = $ccr->createElement('DateTime');
		$e_Immunization->appendChild($e_DateTime);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($immunizationlist['EncounterPointOfCare']['vaccine_date_performed'])));
		$e_DateTime->appendChild($e_ExactDateTime);
		
		$e_Type = $ccr->createElement('Type');
		$e_Immunization->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', 'Immunization');
		$e_Type->appendChild($e_Text);
		
		$e_Status = $ccr->createElement('Status');
		$e_Immunization->appendChild($e_Status);
		
		$e_Text = $ccr->createElement('Text', $immunizationlist['EncounterPointOfCare']['status']);
		$e_Status->appendChild($e_Text);
		
		$e_Immunization->appendChild(self::sourceType($ccr, $sourceID));
		
		$e_Product = $ccr->createElement('Product');
		$e_Immunization->appendChild($e_Product);
		
		$e_ProductName = $ccr->createElement('ProductName');
		$e_Product->appendChild($e_ProductName);
		
		$e_Text = $ccr->createElement('Text', $immunizationlist['EncounterPointOfCare']['vaccine_name']);
		$e_ProductName->appendChild($e_Text);
		
		$e_Code = $ccr->createElement('Code');
		$e_ProductName->appendChild($e_Code);
		
		$e_Value = $ccr->createElement('Value', $immunizationlist['EncounterPointOfCare']['cvx_code']);
		$e_Code->appendChild($e_Value);
		
		$e_CodingSystem = $ccr->createElement('CodingSystem', 'CVX');
		$e_Code->appendChild($e_CodingSystem);
		
		$e_Version = $ccr->createElement('Version', '2005');
		$e_Code->appendChild($e_Version);
		
		$e_Directions = $ccr->createElement('Directions');
		$e_Immunization->appendChild($e_Directions);
		
		$e_Direction = $ccr->createElement('Direction');
		$e_Directions->appendChild($e_Direction);
		
		$e_Description = $ccr->createElement('Description');
		$e_Direction->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', $immunizationlist['EncounterPointOfCare']['comment']);
		$e_Description->appendChild($e_Text);
		
		$e_Code = $ccr->createElement('Code');
		$e_Description->appendChild($e_Code);
		
		$e_Value = $ccr->createElement('Value', 'None');
		$e_Code->appendChild($e_Value);
		endforeach;
		$e_Body->appendChild($e_Immunizations);
		endif;
		
		//////////////////// Vital Signs
		
		// $e_VitalSigns = $ccr->createElement('VitalSigns');
		// $e_Body->appendChild($e_VitalSigns);
		
		
		////results
		if(count($lab_results) > 0):
		$e_Results = $ccr->createElement('Results');
		
		foreach($lab_results as $lab_result):
		
		$e_Result = $ccr->createElement('Result');
		$e_Results->appendChild($e_Result);
		
		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', self::getUuid());
		$e_Result->appendChild($e_CCRDataObjectID);
		
		$e_DateTime = $ccr->createElement('DateTime');
		$e_Result->appendChild($e_DateTime);
		
		$e_Type = $ccr->createElement('Type');
		$e_DateTime->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', 'Observation Date Time');
		$e_Type->appendChild($e_Text);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($lab_result['PatientLabResult']['date_ordered'])));
		$e_DateTime->appendChild($e_ExactDateTime);
		
		
		$e_DateTime = $ccr->createElement('DateTime');
		$e_Result->appendChild($e_DateTime);
		
		$e_Type = $ccr->createElement('Type');
		$e_DateTime->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', 'Report Date');
		$e_Type->appendChild($e_Text);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($lab_result['PatientLabResult']['report_date'])));
		$e_DateTime->appendChild($e_ExactDateTime);
		
		$e_Type = $ccr->createElement('Type');
		$e_Result->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', 'Chemistry');
		$e_Type->appendChild($e_Text);
		
		$e_Description = $ccr->createElement('Description');
		$e_Result->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', $lab_result['PatientLabResult']['test_name1']);
		$e_Description->appendChild($e_Text);
		
		$e_Code = $ccr->createElement('Code');
		$e_Description->appendChild($e_Code);
		
		$e_Value = $ccr->createElement('Value', $lab_result['PatientLabResult']['lab_loinc_code1']);
		$e_Code->appendChild($e_Value);
		
		$e_CodingSystem = $ccr->createElement('CodingSystem', 'LOINC');
		$e_Code->appendChild($e_CodingSystem);
		
		$e_Result->appendChild(self::sourceType($ccr, $sourceID));
		
		for($a = 1; $a <= 5; $a++)
		{
			if(strlen($lab_result['PatientLabResult']['test_name'.$a]) == 0)
			{
				break;
			}
			
			$e_Test = $ccr->createElement('Test');
			$e_Result->appendChild($e_Test);
			
			$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', self::getUuid());
			$e_Test->appendChild($e_CCRDataObjectID);
			
			$e_Type = $ccr->createElement('Type');
			$e_Test->appendChild($e_Type);
			
			$e_Text = $ccr->createElement('Text', 'Result');
			$e_Type->appendChild($e_Text);
			
			$e_Description = $ccr->createElement('Description');
			$e_Test->appendChild($e_Description);
			
			$e_Text = $ccr->createElement('Text', $lab_result['PatientLabResult']['test_name'.$a]);
			$e_Description->appendChild($e_Text);
			
			$e_Code = $ccr->createElement('Code');
			$e_Description->appendChild($e_Code);
			
			$e_Value = $ccr->createElement('Value', $lab_result['PatientLabResult']['lab_loinc_code'.$a]);
			$e_Code->appendChild($e_Value);
			
			$e_CodingSystem = $ccr->createElement('CodingSystem', 'LOINC');
			$e_Code->appendChild($e_CodingSystem);
			
			$e_Test->appendChild(self::sourceType($ccr, $sourceID));
			
			$e_TestResult = $ccr->createElement('TestResult');
			$e_Test->appendChild($e_TestResult);
			
			$e_Value = $ccr->createElement('Value', $lab_result['PatientLabResult']['result_value'.$a]);
			$e_TestResult->appendChild($e_Value);
			
			$e_Units = $ccr->createElement('Units');
			$e_TestResult->appendChild($e_Units);
			
			$e_Unit = $ccr->createElement('Unit', $lab_result['PatientLabResult']['unit'.$a]);
			$e_Units->appendChild($e_Unit);
			
			$e_NormalResult = $ccr->createElement('NormalResult');
			$e_Test->appendChild($e_NormalResult);
			
			$e_Normal = $ccr->createElement('Normal');
			$e_NormalResult->appendChild($e_Normal);
			
			$e_Value = $ccr->createElement('Value', $lab_result['PatientLabResult']['normal_range'.$a]);
			$e_Normal->appendChild($e_Value);
			
			$e_Units = $ccr->createElement('Units');
			$e_Normal->appendChild($e_Units);
			
			$e_Unit = $ccr->createElement('Unit', $lab_result['PatientLabResult']['unit'.$a]);
			$e_Units->appendChild($e_Unit);
			
			$e_Normal->appendChild(self::sourceType($ccr, $sourceID));
			
			$e_Flag = $ccr->createElement('Flag');
			$e_Test->appendChild($e_Flag);
			
			$e_Text = $ccr->createElement('Text', '');
			$e_Flag->appendChild($e_Text);
		}
		
		
		
		endforeach;
		
		
		$e_Body->appendChild($e_Results);
		endif;
		
		/////////////////// Procedures
		
		if(count($procedurelist) > 0):
		$e_Procedures = $ccr->createElement('Procedures');
		foreach ($procedurelist as $procedurelist)
			: $e_Procedure = $ccr->createElement('Procedure');
		$e_Procedures->appendChild($e_Procedure);
		
		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', self::getUuid());
		$e_Procedure->appendChild($e_CCRDataObjectID);
		
		$e_DateTime = $ccr->createElement('DateTime');
		$e_Procedure->appendChild($e_DateTime);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', __date("Y-m-d\TH:i:s\Z", strtotime($procedurelist['EncounterPointOfCare']['procedure_date_performed'])));
		$e_DateTime->appendChild($e_ExactDateTime);
		
		$e_Type = $ccr->createElement('Type');
		$e_Procedure->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', $procedurelist['EncounterPointOfCare']['procedure_name']);
		$e_Type->appendChild($e_Text);
		
		$e_Description = $ccr->createElement('Description');
		$e_Procedure->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', $procedurelist['EncounterPointOfCare']['cpt']);
		$e_Description->appendChild($e_Text);
		
		$e_Code = $ccr->createElement('Code');
		$e_Description->appendChild($e_Code);
		
		$e_Value = $ccr->createElement('Value', $procedurelist['EncounterPointOfCare']['cpt_code']);
		$e_Code->appendChild($e_Value);
		
		$e_Value = $ccr->createElement('CodingSystem', 'CPT-4');
		$e_Code->appendChild($e_Value);
		
		$e_Status = $ccr->createElement('Status');
		$e_Procedure->appendChild($e_Status);
		
		$e_Text = $ccr->createElement('Text', $procedurelist['EncounterPointOfCare']['status']);
		$e_Status->appendChild($e_Text);
		
		$e_Procedure->appendChild(self::sourceType($ccr, $sourceID));
		
		$e_Locations = $ccr->createElement('Locations');
		$e_Procedure->appendChild($e_Locations);
		
		$e_Location = $ccr->createElement('Location');
		$e_Locations->appendChild($e_Location);
		
		$e_Description = $ccr->createElement('Description');
		$e_Location->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', $procedurelist['EncounterPointOfCare']['procedure_details']);
		$e_Description->appendChild($e_Text);
		
		$e_Practitioners = $ccr->createElement('Practitioners');
		$e_Procedure->appendChild($e_Practitioners);
		
		$e_Practitioner = $ccr->createElement('Practitioner');
		$e_Practitioners->appendChild($e_Practitioner);
		
		$e_ActorRole = $ccr->createElement('ActorRole');
		$e_Practitioner->appendChild($e_ActorRole);
		
		$e_Text = $ccr->createElement('Text', 'None');
		$e_ActorRole->appendChild($e_Text);
		
		$e_Duration = $ccr->createElement('Duration');
		$e_Procedure->appendChild($e_Duration);
		
		$e_Description = $ccr->createElement('Description');
		$e_Duration->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', $procedurelist['EncounterPointOfCare']['procedure_comment']);
		$e_Description->appendChild($e_Text);
		
		$e_Substance = $ccr->createElement('Substance');
		$e_Procedure->appendChild($e_Substance);
		
		$e_Text = $ccr->createElement('Text', '');
		$e_Substance->appendChild($e_Text);
		
		$e_Method = $ccr->createElement('Method');
		$e_Procedure->appendChild($e_Method);
		
		$e_Text = $ccr->createElement('Text', '');
		$e_Method->appendChild($e_Text);
		
		$e_Position = $ccr->createElement('Position');
		$e_Procedure->appendChild($e_Position);
		
		$e_Text = $ccr->createElement('Text', '');
		$e_Position->appendChild($e_Text);
		
		$e_Site = $ccr->createElement('Site');
		$e_Procedure->appendChild($e_Site);
		
		$e_Text = $ccr->createElement('Text', $procedurelist['EncounterPointOfCare']['procedure_body_site']);
		$e_Site->appendChild($e_Text);
		endforeach;
		$e_Body->appendChild($e_Procedures);
		endif;
		/////////////// Actors
		
		$e_Actors = $ccr->createElement('Actors');
		$e_Actor = $ccr->createElement('Actor');
		$e_Actors->appendChild($e_Actor);
		
		$e_ActorObjectID = $ccr->createElement('ActorObjectID', $uuid);
		$e_Actor->appendChild($e_ActorObjectID);
		
		$e_Person = $ccr->createElement('Person');
		$e_Actor->appendChild($e_Person);
		
		$e_Name = $ccr->createElement('Name');
		$e_Person->appendChild($e_Name);
		
		$e_CurrentName = $ccr->createElement('CurrentName');
		$e_Name->appendChild($e_CurrentName);
		
		$e_Given = $ccr->createElement('Given', $encounter[0]['PatientDemographic']['first_name']);
		$e_CurrentName->appendChild($e_Given);
		
		$e_Family = $ccr->createElement('Family', $encounter[0]['PatientDemographic']['last_name']);
		$e_CurrentName->appendChild($e_Family);
		
		$e_Suffix = $ccr->createElement('Suffix');
		$e_CurrentName->appendChild($e_Suffix);
		
		$e_DateOfBirth = $ccr->createElement('DateOfBirth');
		$e_Person->appendChild($e_DateOfBirth);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', $encounter[0]['PatientDemographic']['dob']);
		$e_DateOfBirth->appendChild($e_ExactDateTime);
		
		$e_Gender = $ccr->createElement('Gender');
		$e_Person->appendChild($e_Gender);
		
		$e_Text = $ccr->createElement('Text', $encounter[0]['PatientDemographic']['gender']);
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
		
		$e_ID = $ccr->createElement('ID', $encounter[0]['PatientDemographic']['patient_id']);
		$e_IDs->appendChild($e_ID);
		
		$e_Source = $ccr->createElement('Source');
		$e_IDs->appendChild($e_Source);
		
		$e_SourceActor = $ccr->createElement('Actor');
		$e_Source->appendChild($e_SourceActor);
		
		$e_ActorID = $ccr->createElement('ActorID', self::getUuid());
		$e_SourceActor->appendChild($e_ActorID);
		
		// address
		$e_Address = $ccr->createElement('Address');
		$e_Actor->appendChild($e_Address);
		
		$e_Type = $ccr->createElement('Type');
		$e_Address->appendChild($e_Type);
		
		$e_Text = $ccr->createElement('Text', 'H');
		$e_Type->appendChild($e_Text);
		
		$e_Line1 = $ccr->createElement('Line1', $encounter[0]['PatientDemographic']['address1']);
		$e_Address->appendChild($e_Line1);
		
		$e_Line2 = $ccr->createElement('Line2');
		$e_Address->appendChild($e_Line1);
		
		$e_City = $ccr->createElement('City', $encounter[0]['PatientDemographic']['city']);
		$e_Address->appendChild($e_City);
		
		$e_State = $ccr->createElement('State', $encounter[0]['PatientDemographic']['state']);
		$e_Address->appendChild($e_State);
		
		$e_PostalCode = $ccr->createElement('PostalCode', $encounter[0]['PatientDemographic']['zipcode']);
		$e_Address->appendChild($e_PostalCode);
		
		$e_Telephone = $ccr->createElement('Telephone');
		$e_Actor->appendChild($e_Telephone);
		
		$e_Value = $ccr->createElement('Value', $encounter[0]['PatientDemographic']['home_phone']);
		$e_Telephone->appendChild($e_Value);
		
		$e_Source = $ccr->createElement('Source');
		$e_Actor->appendChild($e_Source);
		
		$e_Actor = $ccr->createElement('Actor');
		$e_Source->appendChild($e_Actor);
		
		$e_ActorID = $ccr->createElement('ActorID', $authorID);
		$e_Actor->appendChild($e_ActorID);
		
		
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
		
		$location_address_line_1 = (isset($location[0]['PracticeLocation']['address_line_1'])) ? $location[0]['PracticeLocation']['address_line_1'] : '';
		$e_Line1 = $ccr->createElement('Line1', $location_address_line_1);
		$e_Address->appendChild($e_Line1);
		
		$e_Line2 = $ccr->createElement('Line2');
		$e_Address->appendChild($e_Line1);
		
		$location_city = (isset($location[0]['PracticeLocation']['city'])) ? $location[0]['PracticeLocation']['city'] : '';
		$e_City = $ccr->createElement('City', $location_city);
		$e_Address->appendChild($e_City);
		
		$location_state = (isset($location[0]['PracticeLocation']['state'])) ? $location[0]['PracticeLocation']['state'] : '';
		$e_State = $ccr->createElement('State', $location_state . ' ');
		$e_Address->appendChild($e_State);
		
		$location_zip = (isset($location[0]['PracticeLocation']['zip'])) ? $location[0]['PracticeLocation']['zip'] : '';
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
		
		$filename = $encounter[0]['PatientDemographic']['first_name'] . '_' . $encounter[0]['PatientDemographic']['last_name'] . '_' . __date("m-d-Y") . '.xml';
		
		if($output_as_file)
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
		
		/*
		// save the raw xml
		$main_xml = $ccr->saveXml();
		
		// save the stylesheet
		$main_stylesheet = file_get_contents(substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], "//") + 2) . $_SERVER['HTTP_HOST'] . str_replace("index.php", "", $_SERVER['PHP_SELF']) . 'ccr/encounter_ccr.xsl');
		
		// replace stylesheet link in raw xml file
		$substitute_string = '<?xml-stylesheet type="text/xsl" href="#style1"?><!DOCTYPE ContinuityOfCareRecord [<!ATTLIST xsl:stylesheet id ID #REQUIRED>]>';
		$replace_string = '<?xml-stylesheet type="text/xsl" href="stylesheet/ccr.xsl"?>';
		$main_xml = str_replace($replace_string, $substitute_string, $main_xml);
		
		// remove redundant xml declaration from stylesheet
		$replace_string = '<?xml version="1.0" encoding="UTF-8"?>';
		$main_stylesheet = str_replace($replace_string, '', $main_stylesheet);
		
		// embed the stylesheet in the raw xml file
		$replace_string = '<ContinuityOfCareRecord xmlns="urn:astm-org:CCR">';
		$main_stylesheet = $replace_string . $main_stylesheet;
		$main_xml = str_replace($replace_string, $main_stylesheet, $main_xml);
		
		// insert style1 id into the stylesheet parameter
		$substitute_string = 'xsl:stylesheet id="style1" exclude-result-prefixes';
		$replace_string = 'xsl:stylesheet exclude-result-prefixes';
		$main_xml = str_replace($replace_string, $substitute_string, $main_xml);
		
		// prepare the filename to use
		//   LASTNAME-FIRSTNAME-PID-DATESTAMP-ccr.xml
		$main_filename = "encounter-" . $controller->params['named']['encounter_id'] . "-ccr.xml";
		
		// send the output as a file to the user
		header("Content-type: text/xml");
		header("Content-Disposition: attachment; filename=" . $main_filename . "");
		
		echo $main_xml;
		*/
	}
}

?>