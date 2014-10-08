<?php
class  Public_Health_Surveillance_Hl7_Writer
{
    
    public $field_separator;
    public $encoding_characters;
    public $message_code;
    public $event_type;
    public $message_structure;
    public $processing_id;
    public $version_id;
	
	
	public $NameSpaceIdSendApplication;
    public $UniversalIdSendApplication;
    public $UniversalIdTypeSendApplication;
	
    public $NameSpaceIdSendFacility;
    public $UniversalIdSendFacility;
    public $UniversalIdTypeSendFacility;
	
    public $NamespaceIdReceiveApplication;
    public $UniversalIdReceiveApplication;
    public $UniversalIdTypeReceiveApplication;
	
	public $NamespaceIdReceiveFacility;
    public $UniversalIdReceiveFacility;
    public $UniversalIdTypeReceiveFacility;
	
	public $MessageDate;
    public $MessageControlId;
	
	public $DateStartAdministration;
	public $DateEndAdministration;
	public $AdministeredCodeIdentifier;
	public $AdministeredCodeText;
	public $AdministeredCodingSystem;


	public $AdministeredCodeAmount;
	public $AdministeredUnitsIdentifier;
	public $AdministeredUnitsText;
	public $AdministeredUnitsName;
	public $AdministeredUnitsLotNumbers;
	
	public $ManufacturerIdentifier;
	public $ManufacturerText;
	
	public $IdNumber;
    public $NamespaceId;
    public $UniversalId;
    public $EventType;
    public $UniversalIdType;
 
	
	public $IdNumberType;
    public $Surname;
    public $GivenName;
    public $Dob;
    public $AdministrativeSex;

	
	public $IdentifierRace;
    public $TextRace;
    public $NameOfCodingSystemRace;
    public $StreetAddress;
    public $StreetOrMailingAddress;

	
	public $City;
    public $State;
    public $Zipcode;
    public $AddressType;
    public $TelecommunicationUseCode;
   
	
	public $AreaCode;
    public $LocalNumber;
    public $IdentifierEthnic;
    public $TextEthnic;
    public $NameOfCodingSystemEthnic;
	
	public $OrderControl;

    public function Public_Health_Surveillance_Hl7_Writer()
    {
		
        $this->field_separator = '|';
     
    }

	public function create_MSH() 
	{
	
		$MSH	 =	array();
	
		$MSH[1] = "MSH";							
		
		$MSH[2] = "^~\&";									  
		
		$MSH[3] = "ONETOUCHEMR";	
		
		$MSH[4] = "";
		
		$MSH[5] = "";							
													
		$MSH[6] = "";	
		
		$MSH[7] = __date("Ymd");
		
		$MSH[8] = "";	
		
		$MSH[9] = "ADT^A08";		
		
		$MSH[10] = __date("Ymd");
		
		$MSH[11] = "P^T";		
		
		$MSH[12] = "2.5.1";	
		
		$MSH[13] = "";
		
		$MSH[14] = "";	
		
		$MSH[15] = "";						
		
		$MSH[16] = "";	
		
		$MSH[17] = "";
		
		$MSH[18] = "";
		
		$MSH[19] = "";		
		
		$MSH[20] = "";	
		
		$MSH[21] = "";							
		
		$MSH['Created'] = implode($this->field_separator, $MSH);		
	
		return trim($MSH['Created']);
	
	}
	
	public function create_EVN() 
	{
	
		$EVN	 =	array();
	
		$EVN[1] = "EVN";						
		
		$EVN[2] = "A08";		 // 1.B Event Type Code								  
		
		$EVN[3] = __date("Ymdhi");	 // 2.R Recorded Date/Time
		
		$EVN[4] = "";            // 3. Date/Time Planned Event
		
		$EVN[5] = "";			 // 4. Event Reason Cod				
													
		$EVN[6] = "";	         // 5. Operator ID
		
		$EVN[7] = "";            // 6. Event Occurred
		
		$EVN[8] = "";							
		
		$EVN['Created'] = implode($this->field_separator, $EVN);		
	
		return trim($EVN['Created']);
	
	}

    public function create_PID($patient_id) 
	{
		$created = ClassRegistry::init('PatientDemographic')->getPatient($patient_id);
		//debug($created);
		$this->Patient_id=$created['patient_id'];

		$this->PatientName=$created['first_name']."^".$created['middle_name']."^".$created['last_name'];
		
		$this->MotherName = $created['mother'];
		$this->Dob = str_replace('-', '', $created['dob']);
		$this->PatientGender = $created['gender'];
		$this->PatientRace=$created['race'];
		$this->City = $created['city'];
		$this->State = $created['state'];
		$this->Zipcode = $created['zipcode'];
		$this->HomePhone = $created['home_phone'];
		$this->WorkPhone = $created['work_phone'];	
		$this->MaritalStatus = $created['marital_status'];
		$this->Ssn = $created['ssn'];
		$this->Lincense_Id = $created['driver_license_id'];
		
		$this->Preferred_language = $created['preferred_language'];
		$this->PatientAddress = str_replace(' ', '^', $created['address1'])."^^".str_replace(' ', '^', $created['city'])."^".$created['state'];
		$this->PatientEthnic= str_replace(' ', '^', $created['ethnicity']);
		

	
		$PID	 =	array();
	
		$PID[1] = "PID";							
	
		$PID[2] = "";									  
		
		$PID[3] = "";	
		
		$PID[4] = $this->Patient_id;
		
		$PID[5] = "";							
													
		$PID[6] = $this->PatientName;			
		
		$PID[7] = $this->MotherName;
			
		$PID[8] = $this->Dob;	
		
		$PID[9] = $this->PatientGender;
		
		$PID[10] = $this->PatientEthnic;	
		
		$PID[11] = $this->PatientRace;
		
		$PID[12] = $this->PatientAddress;	
		
		$PID[13] = ''; //Country	
		
		$PID[14] = $this->Zipcode;								
		
		$PID[15] = $this->HomePhone;		
		
		$PID[16] = $this->WorkPhone;	
		
		$PID[17] = $this->Preferred_language;            //Primary Language
		
		$PID[18] = $this->MaritalStatus;
		
		$PID[19] = "";                                   //Religion
		
		$PID[20] = "";                                   //Account Number
		
		$PID[21] = $this->Ssn;
		
		$PID[22] = $this->Lincense_Id;
		
		$PID[23] = "";                                  //Mother Identifier
		
		$PID[24] = ""; //Ethnic Group
		
		$PID[25] = ""; //Birth Place
		
		$PID[26] = ""; //Multiple birth indicator
		
		$PID[27] = ""; //Birth order
		
		$PID[28] = ""; //Citizenship
		
		$PID[29] = ""; //Veteran military status
		         
		$PID[30] = "";	 //B Nationality
		
		$PID[31] = ""; //Patient Death Date and Time
		
		$PID[32] = ""; //Patient Death Indicator
		
		$PID[33] = "";//Identity Unknown Indicator
		
		$PID[34] = "";//Identity Reliability Code
		
		$PID[35] = "";//Last Update Date/Time
		
		$PID[36] = "";//Last Update Facility
		
		$PID[37] = "";//Species Code
		
		$PID[38] = "";//Breed Code
		
		$PID[39] = "";//Breed Code
		
		$PID[40] = "";//Production Class Code
		
		$PID[41] = "";//Tribal Citizenship
		
		
		$PID['Created'] = implode($this->field_separator, $PID);				
			
		return trim($PID['Created']);
	
	}

    function create_PVl() {

	$PV1 =	array();

	$PVl[0] = "PVl";							
	
	$PVl[1] = "";	// 1. Set ID
	
	$PVl[2] = "U";	// 2.R Patient Class (U - unknown)
	
	$PVl[3] = "";// 3. ... 52.
	
	
	$PVl['Created'] = implode($this->field_separator, $PVl);						
	
	//$ORC['Created'] = "|";		

	return trim($PVl['Created']);
	
}

function create_DGl($encounter_id, $assessment_id) {

        $created = ClassRegistry::init('EncounterAssessment')->getAllAssessmentsForHealth($assessment_id);
		$this->IcdCode = $created["icd_code"];
		$diagnosis_value = explode(" [",$created["diagnosis"]);
		$diagnosis = $diagnosis_value[0];
		$this->Diagnosis=str_replace(' ', '^', $diagnosis);

		$this->DateReported=str_replace('-', '', $created["date_reported"]);
	

	$DGl	 =	array();

	$DGl[0] = "DGl";			// [[ 6.24 ]]				
	
	$DGl[1] = "1";		// 1. Set ID					
	
    $DGl[2] = "ICD9:".$this->IcdCode;	// 2.B.R Diagnosis Coding Method
	
	$DGl[3] = $this->IcdCode;	  // 3. Diagnosis Code - DG1
	
	$DGl[4] = $this->Diagnosis;// 4.B Diagnosis Description
	
	$DGl[5] = $this->DateReported;	// 5. Diagnosis Date/Time							

	$DGl[6] = "W";// 6.R Diagnosis Type  // A - Admiting, W - working		
	
	$DGl[7] = "";	// 7.B Major Diagnostic Category
	
	$DGl[8] = "";// 8.B Diagnostic Related Group
	
	$DGl[9] = "";	// 9.B DRG Approval Indicator		
	
	$DGl[10] = "";	// 10.B DRG Grouper Review Code	
	
	$DGl[11] = "";	// 11.B Outlier Type 
	
	$DGl[12] = "";// 12.B Outlier Days
	
	$DGl[13] = "";	// 13.B Outlier Cost		
	
	$DGl[14] = "";	// 14.B Grouper Version And Type 	
	
	$DGl[15] = "";	// 15. Diagnosis Priority	
	
	$DGl[16] = "";	// 16. Diagnosing Clinician
	
	$DGl[17] = "";	// 17. Diagnosis Classification		
	
	$DGl[18] = "";// 18. Confidential Indicator
	
	$DGl[19] = "";// 19. Attestation Date/Time
	
	$DGl[20] = "";// 20.C Diagnosis Identifier
	
	$DGl[21] = "";// 21.C Diagnosis Action Code
	
	$DGl['Created'] = implode($this->field_separator, $DGl);

	//$RXA['Created'] = "|";		

	return trim($DGl['Created']);
	
}



}