<?php

class MedicationCCR
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
				
	public static function generateCCR(&$controller)
	{
		$authorID = self::getUuid();
		$patientID = self::getUuid();
		$sourceID = self::getUuid();
		$oemrID = self::getUuid();
		$uuid = '';
		
		$user = $controller->Session->read('UserAccount');
		$controller->loadModel("PatientAdvanceDirective");
		$controller->loadModel("ScheduleCalendar");
		$controller->loadModel("UserRole");
		$controller->loadModel("PatientProblemList");
		$controller->loadModel("EncounterPointOfCare");
		$controller->loadModel("PatientMedicationList");
		
		$location = $controller->PracticeLocation->find('all', array('conditions' => array('PracticeLocation.location_id' => $user['work_location'])));
		$demographic_item = $controller->PatientDemographic->find('all', array('conditions' => array('PatientDemographic.patient_id' => $controller->params['named']['patient_id'])));
		$medicationlist = $controller->PatientMedicationList->find('all', array('conditions' => array('PatientMedicationList.patient_id' => $demographic_item[0]['PatientDemographic']['patient_id'])));
				
		$ccr = new DOMDocument('1.0', 'UTF-8');
		$e_styleSheet = $ccr->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], "//") + 2) . $_SERVER['HTTP_HOST'] . str_replace("index.php", "", $_SERVER['PHP_SELF']) . 'ccr/encounter_ccr.xsl"');
		$ccr->appendChild($e_styleSheet);
		
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
		
		//$e_ActorID = $ccr->createElement('ActorID', $demographic_item[0]['PatientDemographic']['patient_id']);
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
		
		//$e_ActorID = $ccr->createElement('ActorID', $demographic_item[0]['PatientDemographic']['patient_id']);
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
		
		$e_Text = $ccr->createElement('Text', 'Medication List');
		$e_Description->appendChild($e_Text);
		
		$e_Body = $ccr->createElement('Body');
		$e_ccr->appendChild($e_Body);
		
		
		////////////////// Medication
		
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
		
		$e_Value = $ccr->createElement('Value', '');
		$e_Strength->appendChild($e_Value);
		
		$e_Units = $ccr->createElement('Units');
		$e_Strength->appendChild($e_Units);
		
		$e_Unit = $ccr->createElement('Unit', '');
		$e_Units->appendChild($e_Unit);
		
		$e_Form = $ccr->createElement('Form');
		$e_Product->appendChild($e_Form);
		
		$e_Text = $ccr->createElement('Text', '');
		$e_Form->appendChild($e_Text);
		
		$e_Quantity = $ccr->createElement('Quantity');
		$e_Medication->appendChild($e_Quantity);
		
		$e_Value = $ccr->createElement('Value', '');
		$e_Quantity->appendChild($e_Value);
		
		$e_Units = $ccr->createElement('Units');
		$e_Quantity->appendChild($e_Units);
		
		$e_Unit = $ccr->createElement('Unit', '');
		$e_Units->appendChild($e_Unit);
		
		$e_Directions = $ccr->createElement('Directions');
		$e_Medication->appendChild($e_Directions);
		
		$e_Direction = $ccr->createElement('Direction');
		$e_Directions->appendChild($e_Direction);
		
		$e_Description = $ccr->createElement('Description');
		$e_Direction->appendChild($e_Description);
		
		$e_Text = $ccr->createElement('Text', '');
		$e_Description->appendChild(clone $e_Text);
		
		$e_Route = $ccr->createElement('Route');
		$e_Direction->appendChild($e_Route);
		
		$e_Text = $ccr->createElement('Text', 'Tablet');
		$e_Route->appendChild($e_Text);
		
		$e_Site = $ccr->createElement('Site');
		$e_Direction->appendChild($e_Site);
		
		$e_Text = $ccr->createElement('Text', 'Oral');
		$e_Site->appendChild($e_Text);
		
		$e_PatientInstructions = $ccr->createElement('PatientInstructions');
		$e_Medication->appendChild($e_PatientInstructions);
		
		$e_Instruction = $ccr->createElement('Instruction');
		$e_PatientInstructions->appendChild($e_Instruction);
		
		$e_Text = $ccr->createElement('Text', '');
		$e_Instruction->appendChild($e_Text);
		
		$e_Refills = $ccr->createElement('Refills');
		$e_Medication->appendChild($e_Refills);
		
		$e_Refill = $ccr->createElement('Refill');
		$e_Refills->appendChild($e_Refill);
		
		$e_Number = $ccr->createElement('Number', '');
		$e_Refill->appendChild($e_Number);
		endforeach;
		$e_Body->appendChild($e_Medications);
		
		
		
		
		//////////////////// Vital Signs
		
		// $e_VitalSigns = $ccr->createElement('VitalSigns');
		// $e_Body->appendChild($e_VitalSigns);
		
		
		/////////////// Actors
		
		$e_Actors = $ccr->createElement('Actors');
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
		
		$e_Given = $ccr->createElement('Given', $demographic_item[0]['PatientDemographic']['first_name']);
		$e_CurrentName->appendChild($e_Given);
		
		$e_Family = $ccr->createElement('Family', $demographic_item[0]['PatientDemographic']['last_name']);
		$e_CurrentName->appendChild($e_Family);
		
		$e_Suffix = $ccr->createElement('Suffix');
		$e_CurrentName->appendChild($e_Suffix);
		
		$e_DateOfBirth = $ccr->createElement('DateOfBirth');
		$e_Person->appendChild($e_DateOfBirth);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', $demographic_item[0]['PatientDemographic']['dob']);
		$e_DateOfBirth->appendChild($e_ExactDateTime);
		
		$e_Gender = $ccr->createElement('Gender');
		$e_Person->appendChild($e_Gender);
		
		$e_Text = $ccr->createElement('Text', $demographic_item[0]['PatientDemographic']['gender']);
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
		
		$e_ID = $ccr->createElement('ID', $demographic_item[0]['PatientDemographic']['patient_id']);
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
		
		$e_Line1 = $ccr->createElement('Line1', $demographic_item[0]['PatientDemographic']['address1']);
		$e_Address->appendChild($e_Line1);
		
		$e_Line2 = $ccr->createElement('Line2');
		$e_Address->appendChild($e_Line1);
		
		$e_City = $ccr->createElement('City', $demographic_item[0]['PatientDemographic']['city']);
		$e_Address->appendChild($e_City);
		
		$e_State = $ccr->createElement('State', $demographic_item[0]['PatientDemographic']['state']);
		$e_Address->appendChild($e_State);
		
		$e_PostalCode = $ccr->createElement('PostalCode', $demographic_item[0]['PatientDemographic']['zipcode']);
		$e_Address->appendChild($e_PostalCode);
		
		$e_Telephone = $ccr->createElement('Telephone');
		$e_Actor->appendChild($e_Telephone);
		
		$e_Value = $ccr->createElement('Value', $demographic_item[0]['PatientDemographic']['home_phone']);
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

		header("Content-type: application/xml");
		echo $ccr->saveXml();
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
		exit();	
	}
}

?>