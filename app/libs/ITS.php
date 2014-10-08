<?php
class ITS
{
	public function emdeonITS($message_type, $data = array())
	{
		App::import('Core', 'Router');		
		$controller = new Controller;
		$controller->loadModel('practiceSetting');
	
		$settings  = $controller->practiceSetting->getSettings();

		$result = array();
		
		$post_data = "wsUserID=".urlencode($settings->eligiblity_username);
		$post_data .= "&wsPassword=".urlencode($settings->eligiblity_password);
		$post_data .= "&wsMessageType=".urlencode($message_type);
		
		foreach($data as $key => $value)
		{
			$post_data .= "&".$key."=".urlencode($value);
		}
		
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, "https://".$settings->eligiblity_host."/ITS/post.aspx");
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.13) Gecko/2009073022 Firefox/3.0.13 GTB5");
		curl_setopt ($ch, CURLOPT_TIMEOUT, 3000);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_COOKIESESSION, TRUE);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt ($ch, CURLOPT_POST, 1);
		$content = curl_exec ($ch);
		
		return $content;
	}
	
	// ISA Segment  - EDI-270 format 
	public function createISA()
	{
		App::import('Core', 'Router');		
		$controller = new Controller;
		$controller->loadModel('practiceSetting');
		$settings  = $controller->practiceSetting->getSettings();

		$ISA	 =	array();
		$ISA[0] = "ISA";							// Interchange Control Header Segment ID 
		$ISA[1] = "00";								// Author Info Qualifier 
		$ISA[2] = str_pad(" ",10," ");		// Author Information 
		$ISA[3] = "00";								//   Security Information Qualifier
													//   MEDI-CAL NOTE: For Leased-Line & Dial-Up use '01', 
													//   for BATCH use '00'.
													//   '00' No Security Information Present 
													//   (No Meaningful Information in I04)
		$ISA[4] = str_pad(" ",10," ");		// Security Information 
		$ISA[5] = str_pad("ZZ",2," ");				// Interchange ID Qualifier
		$ISA[6] = str_pad($settings->eligiblity_sender_id,15," ");			// INTERCHANGE SENDER ID 
		$ISA[7] = str_pad("ZZ",2," ");				// Interchange ID Qualifier 
		$ISA[8] = str_pad($settings->eligiblity_receiver_id,15," ");		// INTERCHANGE RECEIVER ID  
		$ISA[9] = str_pad(date('ymd'),6," ");		// Interchange Date (YYMMDD) 
		$ISA[10] = str_pad(date('Hi'),4," ");		// Interchange Time (HHMM) 
		$ISA[11] = "^";								// Interchange Control Standards Identifier 
		$ISA[12] = str_pad("00501",5," ");			// Interchange Control Version Number 
		$ISA[13] = str_pad("000000001",9," ");		// INTERCHANGE CONTROL NUMBER   
		$ISA[14] = str_pad("0",1," ");				// Acknowledgment Request [0= not requested, 1= requested]
		if (substr($settings->eligiblity_host, 0, 4) == "cert")
		{
			$ISA[15] =  str_pad("T",1," ");				// Usage Indicator [ P = Production Data, T = Test Data ]
		}
		else
		{
			$ISA[15] =  str_pad("P",1," ");				// Usage Indicator [ P = Production Data, T = Test Data ]
		}
		$ISA['Created'] = implode('*', $ISA);		// Data Element Separator 
		$ISA['Created'] = $ISA['Created'] ."*";
		$ISA['Created'] = $ISA ['Created'] . ":" . "~"; 
		return trim($ISA['Created']);
	}
	
	// GS Segment  - EDI-270 format 
	public function createGS()
	{
		App::import('Core', 'Router');		
		$controller = new Controller;
		$controller->loadModel('practiceSetting');
		$settings  = $controller->practiceSetting->getSettings();

		$GS	   = array();
		$GS[0] = "GS";						// public functional Group Header Segment ID 
		$GS[1] = "HS";						// public functional ID Code [ HS = Eligibility, Coverage or Benefit Inquiry (270) ] 
		$GS[2] =  $settings->eligiblity_sender_id;					// Application Sender's ID 
		$GS[3] =  $settings->eligiblity_receiver_id;				// Application Receiver's ID 
		$GS[4] = date('Ymd');				// Date [CCYYMMDD] 
		$GS[5] = date('His');				// Time [HHMM] 每 Group Creation Time  
		$GS[6] = "000000002";				// Group Control Number 
		$GS[7] = "X";					// Responsible Agency Code Accredited Standards Committee X12 ] 
		$GS[8] = "005010X279A1";			// Version 每Release / Industry[ Identifier Code Query 
		$GS['Created'] = implode('*', $GS);		// Data Element Separator 
		$GS['Created'] = $GS ['Created'] . "~"; 
		return trim($GS['Created']);
	}
	
	// ST Segment  - EDI-270 format 
	public function createST()
	{
		$ST	   =	array();
		$ST[0] = "ST";								// Transaction Set Header Segment ID 
		$ST[1] = "270";								// Transaction Set Identifier Code (Inquiry Request) 
		$ST[2] = "000000003";
		$ST[3] = "005010X279A1";					// Transaction Set Control Number - Must match SE's 
		$ST['Created'] = implode('*', $ST);			// Data Element Separator 
		$ST['Created'] = $ST ['Created'] . "~"; 
		return trim($ST['Created']);
	}
	
	// BHT Segment  - EDI-270 format 
	public function createBHT()
	{
		$BHT	=	array();
		$BHT[0] = "BHT";						// Beginning of Hierarchical Transaction Segment ID 
		$BHT[1] = "0022";						// Subscriber Structure Code   
		$BHT[2] = "13";							// Purpose Code - This is a Request   
		$BHT[3] = "TRANSA";						//  Submitter Transaction Identifier  
												//This information is required by the information Receiver 
												//when using Real Time transactions. 
												//For BATCH this can be used for optional information.
	
		$BHT[4] = date('Ymd');					// Date Transaction Set Created 
		$BHT[5] = date('His');					// Time Transaction Set Created 
		$BHT['Created'] = implode('*', $BHT);			// Data Element Separator 
		$BHT['Created'] = $BHT ['Created'] . "~"; 
		return trim($BHT['Created']);
	}
	
	// HL Segment  - EDI-270 format 
	public function createHL($nHlCounter)
	{
		$HL		= array();
		$HL[0]		= "HL";			// Hierarchical Level Segment ID 
		$HL[1] = $nHlCounter;		// Hierarchical ID No. 
		if($nHlCounter == 1)
		{ 
			$HL[2] = ""; 
			$HL[3] = 20;			// Description: Identifies the payor, maintainer, or source of the information.
			$HL[4] = 1;				// 1 Additional Subordinate HL Data Segment in This Hierarchical Structure. 
		}
		else if($nHlCounter == 2)
		{
			$HL[2] = 1;				// Hierarchical Parent ID Number 
			$HL[3] = 21;			// Hierarchical Level Code. '21' Information Receiver
			$HL[4] = 1;				// 1 Additional Subordinate HL Data Segment in This Hierarchical Structure. 
		}
		else if($nHlCounter == 3)
		{
			$HL[2] = 2;
			$HL[3] = 22;			// Hierarchical Level Code.'22' Subscriber 
			$HL[4] = 0;				// 0 no Additional Subordinate in the Hierarchical Structure. 
		}
		else
		{
			$HL[2] = 3;
			$HL[3] = 23;			// Hierarchical Level Code.'23' Dependent 
			$HL[4] = 0;				// 0 no Additional Subordinate in the Hierarchical Structure. 
		}
		$HL['Created'] = implode('*', $HL);		// Data Element Separator 
		$HL['Created'] = $HL ['Created'] . "~"; 
		return trim($HL['Created']);
	}
	
	// NM1 Segment  - EDI-270 format 
	public function createNM1($nm1Cast, $data)
	{
		$NM1		= array();
		$NM1[0]		= "NM1";					// Subscriber Name Segment ID 
		if($nm1Cast == 'PR')
		{
			$NM1[1] = "PR";						// Entity ID Code - Payer [PR Payer] 
			$NM1[2] = "2";						// Entity Type - Non-Person 
			$NM1[3] = $data['payer_list'];		// Organizational Name 
			$NM1[4] = "";						// Data Element not required.
			$NM1[5] = "";						// Data Element not required.
			$NM1[6] = "";						// Data Element not required.
			$NM1[7] = "";						// Data Element not required.
			$NM1[8] = "PI";						// 46 - Electronic Transmitter Identification Number (ETIN) 
			$NM1[9] = "C5010";					// Application Sender's ID 
		}
		else if($nm1Cast == '1P')
		{
			$NM1[1] = "1P";						// Entity ID Code - Provider [1P Provider]
			$NM1[2] = "2";						// Entity Type - Person 
			$NM1[3] = $data['provider_list'];	// Organizational Name 
			$NM1[4] = "";						// Data Element not required.
			$NM1[5] = "";						// Data Element not required.
			$NM1[6] = "";						// Data Element not required.
			$NM1[7] = "";						// Data Element not required.
			$NM1[8] = "XX";						
			$NM1[9] = $data['provider_npi'];		
		}
		else if($nm1Cast == 'IL')
		{
			$NM1[1] = "IL";						// Insured or Subscriber 
			$NM1[2] = "1";						// Entity Type - Person 
			$NM1[3] = $data['subscriber_lname'];				// last Name	
			$NM1[4] = $data['subscriber_fname'];				// first Name	
			$NM1[5] = $data['subscriber_mname'];				// middle Name	
			$NM1[6] = "";						// data element 
			$NM1[7] = "";						// data element 
			$NM1[8] = "MI";						// Identification Code Qualifier 
			$NM1[9] = $data['subscriber_id'];			// Identification Code 
		}
		else if($nm1Cast == 'PL')
		{
			$NM1[1] = "IL";						// Insured or Provider 
			$NM1[2] = "2";						// Entity Type - Person 
			$NM1[3] = "";						// data element 
			$NM1[4] = "";						// data element 
			$NM1[5] = "";						// data element 
			$NM1[6] = "";						// data element 
			$NM1[7] = "";						// data element 
			$NM1[8] = $data['provider_qualifier'];			// Identification Code Qualifier 
			$NM1[9] = $data['provider_identification'];		// Identification Code 
		}
		else if($nm1Cast == 'DL')
		{
			$NM1[1] = "03";						// Insured or Dependent 
			$NM1[2] = "1";						// Entity Type - Person 
			$NM1[3] = $data['dependent_lname'];				// last Name	
			$NM1[4] = $data['dependent_fname'];				// first Name	
			$NM1[5] = $data['dependent_mname'];				// middle Name	
			$NM1[6] = "";						// data element 
			$NM1[7] = "";						// data element 
			$NM1[8] = "";						// data element 
			$NM1[9] = "";						// data element 
		}
		$NM1['Created'] = implode('*', $NM1);				// Data Element Separator 
		$NM1['Created'] = $NM1['Created'] . "~"; 
		return trim($NM1['Created']);
	
	}

	// PRV Segment  - EDI-270 format 
	public function createPRV($data)
	{
		$PRV	=	array();
		$PRV[0] = "PRV";							// Provider Segment ID 
		$PRV[1] = $data['provider_code'];			// Provider Code
		$PRV[2] = $data['provider_qualifier'];		// Provider Reference Identification Qualifier
		$PRV[3] = $data['provider_identification'];	// Reference Identification
		$PRV[4] = "";								// data element 
		$PRV[5] = "";								// data element 
		$PRV[6] = "";								// data element 
		$PRV['Created'] = implode('*', $PRV);			// Data Element Separator 
		$PRV['Created'] = $PRV ['Created'] . "~"; 
		return trim($PRV['Created']);
	}

	// REF Segment  - EDI-270 format 
	public function createREF($ref, $data)
	{
		$REF	=	array();
		$REF[0] = "REF";						// Subscriber Additional Identification 
		if($ref == '1P')
		{
			$REF[1] = "4A";						// Reference Identification Qualifier 
			$REF[2] = $data['provider_pin'];				// Provider Pin. 
		}
		else
		{
			$REF[1] = "EJ";						// 'EJ' for Patient Account Number 
			$REF[2] = $data['patient_id'];					// Patient Account No. 
		}
		$REF['Created'] = implode('*', $REF);				// Data Element Separator 
		$REF['Created'] = $REF['Created'] . "~"; 
		return trim($REF['Created']);
	}
	
	// TRN Segment - EDI-270 format 
	
	public function createTRN($tracno,$refiden) {
	
		$TRN	=	array();
	
		$TRN[0] = "TRN";						// Subscriber Trace Number Segment ID 
	
		$TRN[1] = "1";							// Trace Type Code 每 Current Transaction Trace Numbers 
	
		$TRN[2] = $tracno;						// Trace Number 
	
		//$TRN[3] = "9000000000";						// Originating Company ID 每 must be 10 positions in length 
	
		$TRN[4] = $refiden;						// Additional Entity Identifier (i.e. Subdivision) 
	
		$TRN['Created'] = implode('*', $TRN);				// Data Element Separator 
	
		$TRN['Created'] = $TRN['Created'] . "~"; 
		 
		return trim($TRN['Created']);
	  
	}
	
	// DMG Segment - EDI-270 format 
	
	public function createDMG($data) {
	
		$DMG	=	array();
		
		$DMG[0] = "DMG";							// Date or Time or Period Segment ID 
	
		$DMG[1] = "D8";								// Date Format Qualifier - (D8 means CCYYMMDD) 
	
		$DMG[2] = __date("Ymd", strtotime($data['dob']));						// Subscriber's/Dependent's Birth date 
	
		$DMG['Created'] = implode('*', $DMG);		// Data Element Separator 
	
		$DMG['Created'] = $DMG['Created'] .  "~"; 
		 
		return trim($DMG['Created']);			
	}
	
	// DTP Segment - EDI-270 format 
	
	public function createDTP($qual, $data) {
	
		$DTP	=	array();
		
		$DTP[0] = "DTP";						// Date or Time or Period Segment ID 
		
		$DTP[1] = $qual;						// Qualifier - Date of Service 
		
		$DTP[2] = "D8";							// Date Format Qualifier - (D8 means CCYYMMDD) 
		
		if($qual == '102'){
			//$DTP[3] = $data['date'];				// Date 
		}else{
			$DTP[3] = __date("Ymd", strtotime($data['service_date']));		// Date of Service 
		}
		$DTP['Created'] = implode('*', $DTP);	// Data Element Separator 
	
		$DTP['Created'] = $DTP['Created'] .  "~"; 
		 
		return trim($DTP['Created']);
	}
	
	// EQ Segment - EDI-270 format 
	
	public function createEQ($data) {
	
		$EQ		=	array();
		
		$EQ[0]	= "EQ";									// Subscriber Eligibility or Benefit Inquiry Information 
		
		$EQ[1]	= $data['service_type_code'];				// Service Type Code 
		
		$EQ['Created'] = implode('*', $EQ);				// Data Element Separator 
	
		$EQ['Created'] = $EQ['Created'] . "~"; 
		 
		return trim($EQ['Created']);
	}
	
	// SE Segment - EDI-270 format 
	
	public function createSE($segmentcount) {
	
		$SE	=	array();
		
		$SE[0] = "SE";								// Transaction Set Trailer Segment ID 
	
		$SE[1] = $segmentcount;						// Segment Count 
	
		$SE[2] = "000000003";						// Transaction Set Control Number - Must match ST's 
	
		$SE['Created'] = implode('*', $SE);			// Data Element Separator 
	
		$SE['Created'] = $SE['Created'] . "~"; 
		 
		return trim($SE['Created']);
	}
	
	// GE Segment - EDI-270 format 
	
	public function createGE() {
	
		$GE	=	array();
		
		$GE[0]	= "GE";							// public functional Group Trailer Segment ID 
	
		$GE[1]	= "1";							// Number of included Transaction Sets 
	
		$GE[2]	= "000000002";						// Group Control Number 
	
		$GE['Created'] = implode('*', $GE);				// Data Element Separator 
	
		$GE['Created'] = $GE['Created'] . "~"; 
		 
		return trim($GE['Created']);
	}
	
	// IEA Segment - EDI-270 format 
	
	public function createIEA() {
	
		$IEA	=	array();
		
		$IEA[0] = "IEA";						// Interchange Control Trailer Segment ID 
	
		$IEA[1] = "1";							// Number of included public functional Groups 
	
		$IEA[2] = "000000001";						// Interchange Control Number 
	
		$IEA['Created'] = implode('*', $IEA);
	
		$IEA['Created'] = $IEA['Created'] .  "~"; 
		 
		return trim($IEA['Created']);
	}

	public function print_elig($data){

		$PATEDI	   = "";
	
		// For Header Segment 
		$trcNo		= 1234501;
		$refiden	= 5432101;
		
		$data['payer_list'] = (isset($data['payer_list'])) ? $data['payer_list'] : "EMDEON X12 5010 CERTIFICATION PAYER";
		$data['provider_list'] = (isset($data['provider_list'])) ? $data['provider_list'] : "EMDEON X12 5010 CERTIFICATION PROVIDER";
		$data['provider_npi'] = (isset($data['provider_npi'])) ? $data['provider_npi'] : "";
		$data['provider_code'] = (isset($data['provider_code'])) ? $data['provider_code'] : "";
		$data['provider_qualifier'] = (isset($data['provider_qualifier'])) ? $data['provider_qualifier'] : "";
		$data['provider_identification'] = (isset($data['provider_identification'])) ? $data['provider_identification'] : "";
		$data['subscriber_lname'] = (isset($data['subscriber_lname'])) ? $data['subscriber_lname'] : "";
		$data['subscriber_fname'] = (isset($data['subscriber_fname'])) ? $data['subscriber_fname'] : "";
		$data['subscriber_mname'] = (isset($data['subscriber_mname'])) ? $data['subscriber_mname'] : "";
		$data['subscriber_id'] = (isset($data['subscriber_id'])) ? $data['subscriber_id'] : "";
		$data['provider_pin'] = (isset($data['provider_pin'])) ? $data['provider_pin'] : "";
		$data['patient_id'] = (isset($data['patient_id'])) ? $data['patient_id'] : "";
		$data['dependent_lname'] = (isset($data['dependent_lname'])) ? $data['dependent_lname'] : "";
		$data['dependent_fname'] = (isset($data['dependent_fname'])) ? $data['dependent_fname'] : "";
		$data['dependent_mname'] = (isset($data['dependent_mname'])) ? $data['dependent_mname'] : "";
		$data['dob'] = (isset($data['dob'])) ? $data['dob'] : "";
		$data['service_date'] = (isset($data['service_date'])) ? $data['service_date'] : "";
		$data['service_type_code'] = (isset($data['service_type_code'])) ? $data['service_type_code'] : "";

		// create ISA 
		$PATEDI	   = ITS::createISA();

		// create GS 
		$PATEDI	  .= ITS::createGS();

		// create ST 
		$PATEDI	  .= ITS::createST();
		$segmentcount = 1;

		// create BHT 
		$PATEDI	  .= ITS::createBHT();
		++$segmentcount;

		// For Payer Segment 
		$PATEDI  .= ITS::createHL(1);
		++$segmentcount;

		if ($data['payer_list'])
		{
			$PATEDI  .= ITS::createNM1('PR', $data);
			++$segmentcount;
		}

		// For Provider Segment 				
		$PATEDI  .= ITS::createHL(2);
		++$segmentcount;

		if ($data['provider_list'] && $data['provider_npi'])
		{
			$PATEDI  .= ITS::createNM1('1P', $data);
			++$segmentcount;
		}

		if ($data['provider_code'] && $data['provider_qualifier'] && $data['provider_identification'])
		{
			$PATEDI  .= ITS::createPRV($data);
			++$segmentcount;
		}

		if ($data['provider_pin'])
		{
			$PATEDI  .= ITS::createREF('1P', $data);
			++$segmentcount;
		}

		// For Subscriber Segment 				
		$PATEDI  .= ITS::createHL(3);
		++$segmentcount;

		$PATEDI  .= ITS::createTRN($trcNo, $refiden);
		++$segmentcount;

		$PATEDI  .= ITS::createNM1('IL', $data);
		++$segmentcount;

		if (!$data['provider_code'] && $data['provider_qualifier'] && $data['provider_identification'])
		{
			$PATEDI  .= ITS::createNM1('PL', $data);
			++$segmentcount;
		}

		if ($data['patient_id'])
		{
			$PATEDI  .= ITS::createREF('IL', $data);
			++$segmentcount;
		}

		if ($data['dependent_lname'] || $data['dependent_fname'] || $data['dependent_mname'])
		{
			// For Dependent Segment 				
			$PATEDI  .= ITS::createHL(4);
			++$segmentcount;

			$PATEDI  .= ITS::createNM1('DL', $data);
			++$segmentcount;
		}

		if ($data['dob'])
		{
			$PATEDI  .= ITS::createDMG($data);
			++$segmentcount;
		}

		//$PATEDI  .= createDTP('102');
		//++$segmentcount;

		if ($data['service_date'])
		{
			$PATEDI  .= ITS::createDTP('291', $data);
			++$segmentcount;
		}

		if ($data['service_type_code'])
		{
			$PATEDI  .= ITS::createEQ($data);
			++$segmentcount;
		}

		$trcNo		= $trcNo + 1;
		$refiden	= $refiden + 1;

		$PATEDI	  .= ITS::createSE($segmentcount + 1);

		$PATEDI	  .= ITS::createGE();

		$PATEDI	  .= ITS::createIEA();
	
		//$PATEDI = str_replace("~", "~<br>", $PATEDI);
		//debug($PATEDI);
		return $PATEDI;
	}
}
