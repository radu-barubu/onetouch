<?php
class  Immunization_Hl7_Writer
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

    public function Immunization_Hl7_Writer()
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
		
		$MSH[7] = date("Ymd");
		
		$MSH[8] = "";	
		
		$MSH[9] = "VXU^V04^VXU_V04";		
		
		$MSH[10] = "ONETOUCHEMR-110316102457117";
		
		$MSH[11] = "P";		
		
		$MSH[12] = "2.5.1";			
		
		$MSH['Created'] = implode($this->field_separator, $MSH);				
		
		//$MSH['Created'] = "|";		
	
		return trim($MSH['Created']);
	
	}

    public function create_PID($patient_id) 
	{
		$created = ClassRegistry::init('PatientDemographic')->getPatient($patient_id);
		
		$this->Surname=$created['last_name'];
		$this->Patient_id=$created['patient_id'];

		$this->PatientName=$created['first_name']."^"/*.$created['middle_name']."^"*/.$created['last_name'];
		$this->MotherName = $created['mother'];
		$this->Dob = str_replace('-', '', $created['dob']);
		$this->PatientGender = $created['gender'];
		$this->PatientRace=$created['race'];
		$this->MaritalStatus = $created['marital_status'];
		$this->Ssn = $created['ssn'];
	
	    $HPhone = explode('-',$created['home_phone']);
		$this->HomePhone = $HPhone[0]."^".$HPhone[1].$HPhone[2];
		$this->WorkPhone = $created['work_phone'];
		$this->NameOfCodingSystemRace = '';
		$this->City = $created['city'];
		$this->State = $created['state'];
		$this->Zipcode = $created['zipcode'];
		$this->Preferred_language = $created['preferred_language'];
		$this->Lincense_Id = $created['driver_license_id'];
		
		$this->PatientAddress = str_replace('  ', '^', $created['address1'])."^^"./*str_replace(' ', '^',*/ $created['city']/*)*/."^".$created['state']."^".$created['zipcode']."^^".'M';
		$this->TelecommunicationUseCode = '';
		$this->AreaCode = '';
		$this->LocalNumber= '';
		if($created['ethnicity'] == 'Not Hispanic or Latino')
		{
		$this->PatientEthnic = "NH"."^".$created['ethnicity']."^".'HL70189';
		}
		else
		{
		$this->PatientEthnic = "H"."^".$created['ethnicity']."^".'HL70189';
		}
		$this->TextEthnic='';
		$this->NameOfCodingSystemEthnic= '';

	
		$PID	 =	array();
	
		$PID[1] = "PID";							
	
		$PID[2] = "";// 1. Set id									  
		
		$PID[3] = "";// 2. (B)Patient id	
		
		$PID[4] = $this->Patient_id."^^^MPI&2.16.840.1.113883.19.3.2.1&ISO^MR";// 3. (R) Patient indentifier list. TODO: Hard-coded the OID from NIST test. 
		
		$PID[5] = "";	// 4. (B) Alternate PID						
													
		$PID[6] = $this->PatientName;				
		
		$PID[7] = $this->MotherName;// 6. Mather Maiden Name
				
		$PID[8] = $this->Dob;	
		
		$PID[9] = $this->PatientGender;
		
		$PID[10] = "";	// 9.B Patient Alias
		
		$PID[11] = "2106-3^".$this->PatientRace."^HL70005";
		
		$PID[12] = $this->PatientAddress;		
		
		$PID[13] = "";								
		
		$PID[14] = "^PRN^^^^" . $this->HomePhone;		
		
		$PID[15] = "";//"^WPN^^^^" . $this->WorkPhone;	
		
		$PID[16] = "";//$this->Preferred_language;
		
		$PID[17] = "";//$this->MaritalStatus;
		
		$PID[18] = "";// 17. Religion
		
		$PID[19] = "";// 18. patient Account Number
		
		$PID[20] = "";//$this->Ssn; 19.B SSN Number
		
		$PID[21] = $this->Lincense_Id;// 20.B Driver license number
		
		$PID[22] = "";// 21. Mathers Identifier
		
		$PID[23] = $this->PatientEthnic;// 22. Ethnic Group
		
		/*$PID[24] = "";// 23. Birth Plase
		
		$PID[25] = "";// 24. Multiple birth indicator
		
		$PID[26] = "";// 25. Birth order
		
		$PID[27] = "";// 26. Citizenship
		
		$PID[28] = "";// 27. Veteran military status
		
		$PID[29] = "";// 28.B Nationality
		
		$PID[30] = "";// 29. Patient Death Date and Time
		
		$PID[31] = "";// 30. Patient Death Indicator
		
		$PID[32] = "";// 31. Identity Unknown Indicator
		
		$PID[33] = "";// 32. Identity Reliability Code
		
		$PID[34] = "";// 33. Last Update Date/Time
		
		$PID[35] = "";// 34. Last Update Facility
		
		$PID[36] = "";// 35. Species Code
		
		$PID[37] = "";// 36. Breed Code
		
		$PID[38] = "";// 37. Breed Code
		
		$PID[39] = "";// 38. Production Class Code
		
		$PID[40] = "";// 39. Tribal Citizenship*/
		
		$PID['Created'] = implode($this->field_separator, $PID);				
			
		return trim($PID['Created']);
	
	}

    function create_ORC() {

	$ORC =	array();

	$ORC[0] = "ORC";							
	
	$ORC[1] = "RE";		
	
	$ORC['Created'] = implode($this->field_separator, $ORC);						
	
	//$ORC['Created'] = "|";		

	return trim($ORC['Created']);
	
}

function create_RXA($encounter_id, $order_type, $point_of_care_id) {

        $created = ClassRegistry::init('EncounterPointOfCare')->get_Point_Of_Care($encounter_id, $order_type, $point_of_care_id);
		
		$vaccine_name = explode('[',$created["vaccine_name"]);
		$this->VaccineName= $vaccine_name[0];
		$Date_Start = explode(' ',$created["vaccine_date_performed"]);
		$this->DateStart=str_replace('-', '', $Date_Start[0]);
		
		$Date_End = explode(' ',$created["vaccine_expiration_date"]);
		$this->DateEnd=str_replace('-', '', $Date_End[0]);
		
		
		$this->CvxCode=$created["cvx_code"];
		$this->Dose = $created["vaccine_dose"];
		
		$this->AdministeredUnitsText='';
		$this->AdministeredUnitsName=$created["administered_units"];;
		$this->LotNumbers = $created["vaccine_lot_number"];
		
		$this->VaccineManufacturer = $created["vaccine_manufacturer"];
		$this->VaccineManufacturerCode = $created["manufacturer_code"];


	$RXA	 =	array();

	$RXA[0] = "RXA";							
	
	$RXA[1] = '0';							
	
    $RXA[2] = '1';	
	
	$RXA[3] = $this->DateStart;	  
	
	$RXA[4] = $this->DateEnd;
	
	$RXA[5] = $this->CvxCode.'^'.$this->VaccineName./*'^'."[".$this->CvxCode."]".*/'^'.'CVX';								

	$RXA[6] = $this->Dose;//6. Administered Amount. TODO: Immunization amt currently not captured in database, default to 999(not recorded)		
	
	$RXA[7] = "ml".'^'.$this->AdministeredUnitsName.'^'."ISO+";	// 7. Administered Units
	
	$RXA[8] = "";// 8. Administered Dosage Form
	
	$RXA[9] = "";// 9. Administration Notes			
	
	$RXA[10] = "";// 10. Administering Provider		
	
	$RXA[11] = "";// 11. Administered-at Location	
	
	$RXA[12] = "";// 12. Administered Per (Time Unit)
	
	$RXA[13] = "";// 13. Administered Strength			
	
	$RXA[14] = "";// 14. Administered Strength Units		
	
	$RXA[15] = $this->LotNumbers;// 15. Substance Lot Number		
	
	$RXA[16] = "";// 16. Substance Expiration Date	
	
	$RXA[17] =$this->VaccineManufacturerCode."^".$this->VaccineManufacturer."^". "HL70227";// 17. Substance Manufacturer Name			
	
	$RXA[18] = "";// 18. Substance/Treatment Refusal Reason
	
	$RXA[19] = ""; // 19.Indication
	
	$RXA[20] = "";// 20.Completion Status
	
	$RXA[21] = "A";// 21.Action Code - RXA
	
	
	$RXA['Created'] = implode($this->field_separator, $RXA);

	//$RXA['Created'] = "|";		

	return trim($RXA['Created']);
	
}


}