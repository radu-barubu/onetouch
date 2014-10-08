<?php

class Dosespot_XML_API
{

    public $dosespot_api_url;
    public $SingleSignOnClinicId;
    public $SingleSignOnUserId;
    public $SingleSignOnPhraseLength;
    public $SingleSignOnCode;
    public $SingleSignOnUserIdVerify;
    public $SingleSignOnKey;
    private $DoseSpotGenerateInfo;
    public $cache_file_prefix;
    
    public function isOK()
    {
        $practice_settings = ClassRegistry::init('PracticeSetting')->getSettings();

        if ($practice_settings->rx_setup == 'Electronic_Dosespot')
        {
        	return true;
        } 
        else 
        {
        	return false;
        }
    }

    /**
     * Verify Dosespot connectivity
     * 
     *
     * @return boolean - true if connection succeeded
     */
    public function checkConnection()
    {
 	return true;
    }

     private static function area_code_array()
     {
     	return array('000', '201', '202', '203', '204', '205', '206', '207', '208', '209', '210', '212', '213', '214', '215', '216', '217', '218', '219', '224', '225', '226', '228', '229', '231', '234', '239', '240', '242', '246', '248', '250', '251', '252', '253', '254', '256', '260', '262', '264', '267', '268', '269', '270', '276', '281', '284', '289', '301', '302', '303', '304', '305', '306', '307', '308', '309', '310', '312', '313', '314', '315', '316', '317', '318', '319', '320', '321', '323', '325', '330', '331', '334', '336', '337', '339', '340', '343', '345', '347', '351', '352', '360', '361', '385', '386', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '412', '413', '414', '415', '416', '417', '418', '419', '423', '424', '425', '430', '432', '434', '435', '438', '440', '441', '442', '443', '450', '456', '458', '469', '470', '473', '475', '478', '479', '480', '484', '500', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '512', '513', '514', '515', '516', '517', '518', '519', '520', '530', '533', '534', '540', '541', '551', '559', '561', '562', '563', '567', '570', '571', '573', '574', '575', '579', '580', '581', '585', '586', '587', '600', '601', '602', '603', '604', '605', '606', '607', '608', '609', '610', '612', '613', '614', '615', '616', '617', '618', '619', '620', '623', '626', '630', '631', '636', '641', '646', '647', '649', '650', '651', '657', '660', '661', '662', '664', '670', '671', '678', '681', '682', '684', '700', '701', '702', '703', '704', '705', '706', '707', '708', '709', '710', '712', '713', '714', '715', '716', '717', '718', '719', '720', '724', '727', '731', '732', '734', '740', '747', '754', '757', '758', '760', '762', '763', '765', '767', '769', '770', '772', '773', '774', '775', '778', '779', '780', '781', '784', '785', '786', '787', '800', '801', '802', '803', '804', '805', '806', '807', '808', '809', '810', '812', '813', '814', '815', '816', '817', '818', '819', '828', '829', '830', '831', '832', '843', '845', '847', '848', '849', '850', '855', '856', '857', '858', '859', '860', '862', '863', '864', '865', '866', '867', '868', '869', '870', '872', '876', '877', '878', '888', '900', '901', '902', '903', '904', '905', '906', '907', '908', '909', '910', '912', '913', '914', '915', '916', '917', '918', '919', '920', '925', '928', '931', '936', '937', '938', '939', '940', '941', '947', '949', '951', '952', '954', '956', '970', '971', '972', '973', '978', '979', '980', '985', '989');
     }
	public function Dosespot_XML_API($userId = false, $type = 'read') {
		//$userAccount = $_SESSION['UserAccount'];
		
		// Non-boolean false $userId
		// means an argument was passed to the constructor
		if ( $userId !== false ) {
			// Check if passed argument is an id
			// or an array representing the UserAccount model
			$userAccount = $userId;

			if ( is_numeric($userAccount) ) {
				$userAccount = ClassRegistry::init('UserAccount')->find('first', array(
					'UserAccount.user_id' => $userAccount,
					));
			}

			if ( isset($userAccount['UserAccount']) ) {
				$userAccount = $userAccount['UserAccount'];
			} /*else {
				// In case all checks fail
				// default to Session UserAccount
				$userAccount = $_SESSION['UserAccount'];
			}*/
		}
		else
		{
			if(isset($_SESSION) && isset($_SESSION['UserAccount']))
			{
				$userAccount = $_SESSION['UserAccount'];
			}
			else
			{
				$userAccount = array();
			}
		}

		$this->PracticeSetting = & ClassRegistry::init('PracticeSetting');

		/* Prepare cache file prefix for multiple host */
		$db_config = $this->PracticeSetting->getDataSource()->config;
		$practice_value = $this->PracticeSetting->getSettings();
		$this->cache_file_prefix = $db_config['host'] . '_' . $db_config['database'] . '_';

		//url prefix check

		$created = ClassRegistry::init('PracticeSetting')->getSettings();
		$this->SingleSignOnPhraseLength = 32;
		//use Staging vs. production environment? 
		if ( empty($created->dosespot_test_flag) ) { //Prodution info
			$this->dosespot_api_url = 'https://my.dosespot.com/';
			$this->SingleSignOnClinicId = $created->dosepot_singlesignon_clinicid;
			$this->SingleSignOnKey = $created->dosepot_singlesignon_clinickey;

			if ( isset($userAccount['user_id']) ) {
				if ( $userAccount['role_id'] == EMR_Roles::PHYSICIAN_ROLE_ID
					|| $userAccount['role_id'] == EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID
					|| $userAccount['role_id'] == EMR_Roles::NURSE_PRACTITIONER_ROLE_ID
				) {
					$user_id = $userAccount['user_id'];
					$this_doc_dosespot_id = ClassRegistry::init('UserAccount')->getPhysicianDosepotUserId($user_id);
					if ( $this_doc_dosespot_id )
						$dosepot_singlesignon_userid = $this_doc_dosespot_id;
					else
						$dosepot_singlesignon_userid = ''; //none found.                 

						
//this below will be if a different user is allowed to e-Rx     
				} else if ( $userAccount['dosepot_singlesignon_userid'] ) {
					$user_id = $userAccount['user_id'];
					$dosepot_singlesignon_userid = $userAccount['dosepot_singlesignon_userid'];
				} else {
					
					if ($type != 'read') {
						die('You are not allowed to prescribe');
					} 
					
					//Select ClinicKey of any Physician in the practice
					$dosepot_singlesignon_userid = ClassRegistry::init('UserAccount')->getAnyPhysicianDosepotUserId();
				}
			} else {
				//this path is executed when the shell script runs: app/vendors/shells/dosespot_patientID.php
				//Select ClinicKey of any Physician in the practice just so we can grab new Patient ID
				$dosepot_singlesignon_userid = ClassRegistry::init('UserAccount')->getAnyPhysicianDosepotUserId();
			}
		} else { //staging info is hard-coded
			$this->dosespot_api_url = 'https://my.staging.dosespot.com/';
			$this->SingleSignOnClinicId = '63';
			$this->SingleSignOnKey = 'aumUqgW3NRTue5uf6LvUww3dSyDyYTRy';
			$dosepot_singlesignon_userid = '31';
		}



		$this->SingleSignOnUserId = $dosepot_singlesignon_userid;
		$this->DoseSpotGenerateInfo = $this->SSO($this->SingleSignOnKey, $this->SingleSignOnUserId);
		$this->SingleSignOnCode = $this->DoseSpotGenerateInfo[0];
		$this->SingleSignOnUserIdVerify = $this->DoseSpotGenerateInfo[1];
	}

    public function getInfo()
    {
        $ret = array();
        $ret['dosespot_api_url'] = $this->dosespot_api_url;
        $ret['SingleSignOnClinicId'] = $this->SingleSignOnClinicId;
        $ret['SingleSignOnUserId'] = $this->SingleSignOnUserId;
        $ret['SingleSignOnPhraseLength'] = $this->SingleSignOnPhraseLength;
        $ret['SingleSignOnCode'] = urlencode($this->SingleSignOnCode);
        $ret['SingleSignOnUserIdVerify'] = urlencode($this->SingleSignOnUserIdVerify);
        return $ret;
    }

    /*
    * before we send a request to dosespot, we must first verify patient demographics are meeting minimum requirements
    * return error if not
    */
    public function verifyPatientDemographics($param)
    {
    	$err=array();
   
	//remove numbers if exist in any of these 
	$param['city']=preg_replace("/[0-9]/", "", $param['city']); 
	$param['state']=preg_replace("/[0-9]/", "", $param['state']);
	$param['gender']=preg_replace("/[0-9]/", "", $param['gender']);
	
        if(strlen(trim($param['first_name'])) < 1)
           $err[]='Patient First Name Required';  
        if(strlen(trim($param['last_name'])) < 1)
            $err[]='Patient Last Name Required';        
        if(strlen(trim($param['dob'])) < 1)
            $err[]='Patient Date of Birth Required';        
        if(strlen(trim($param['gender'])) < 1)
             $err[]='Patient Gender Required';       
    	if(strlen(trim($param['address1'])) < 1)
            $err[]='Patient Address Required';    	
    	if (strlen(trim($param['city'])) < 1)
            $err[]='Patient City Required';   	
    	//state can be full name or 2 digit code
    	if (strlen(trim($param['state'])) < 1)
           $err[]='Patient State Required';     	
    	if (strlen(trim($param['zipcode'])) < 5)
           $err[]='Valid Patient Zip Code Required';  

	$areacode_array = $this->area_code_array();
            if ($param['home_phone'] != '')
            {
                $home_phone = $param['home_phone'];
                $home_phone_areacode = substr($home_phone, 0, 3);
                if (!in_array($home_phone_areacode, $areacode_array))
                {
           		$err[]='Invalid Area Code Provided for Home Phone'; 
                }
            }
            	
            if ($param['work_phone'] != '')
            {
                $work_phone = $param['work_phone'];
                $work_phone_areacode = substr($work_phone, 0, 3);
		
                if (!in_array($work_phone_areacode, $areacode_array))
                {
           		$err[]='Invalid Area Code Provided for Work Phone'; 
                }
            }

             if ($param['cell_phone'] != '')
            {
                $cell_phone = $param['cell_phone'];
                $cell_phone_areacode = substr($cell_phone, 0, 3);

                if (!in_array($cell_phone_areacode, $areacode_array))
                {
		    $err[]='Invalid Area Code Provided for Cell Phone'; 
                }
            }      
              
       return $err;  
    }

    public function follow_redirect($url)
    {
        $redirect_url = null;
    	$timeout=10;
    	$cookie = tempnam ("/tmp", "CURLCOOKIE");
    	$ch = curl_init();
    	curl_setopt( $ch, CURLOPT_URL, $url );
    	curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
    	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    	curl_setopt( $ch, CURLOPT_ENCODING, "" );
    	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    	curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
    	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );    # required for https urls
    	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
    	curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
    	curl_setopt( $ch, CURLOPT_MAXREDIRS, 3 );
    	$content = curl_exec( $ch );
    	$response = curl_getinfo( $ch );
    	curl_close ( $ch );
    
	//cakelog::write('dosespot',' follow_redirect -> '.$response['http_code'].' '.$response['url']);
        if($response['http_code'] == '200' && $response['url'])
        {
        	//cakelog::write('dosespot','--> SUCCESS!!');
		return $response['url'];
        }
        else
        {
	    $pr = ClassRegistry::init('PracticeSetting')->getSettings();
	     $Body="--> ALERT: FALSE!!! on Client=".$pr->practice_id." DETAILS TO FOLLOW \n response: ".print_r($response,true). "\n content: ".print_r($content,true);
	     cakelog::write('dosespot',$Body);
	   email::send('Errors', 'errors@onetouchemr.com', 'ATTN: Dosespot error on '.$pr->practice_id, nl2br($Body),'','',false,'','','','','');
            return false;
        }
        
    }

    public function getDosespotPatientID($patient_id, $param)
    {
        $areacode_array = $this->area_code_array();

						$param['first_name'] = substr($param['first_name'], 0, 35);
						$param['middle_name'] = substr($param['middle_name'], 0, 35);
						$param['last_name'] = substr($param['last_name'], 0, 35);
						$param['address_1'] = substr($param['address_1'], 0, 35);
						$param['address_2'] = substr($param['address_2'], 0, 35);
						$param['city'] = substr($param['city'], 0, 35);
						$param['state'] = substr($param['state'], 0, 20);
						$param['zip'] = substr($param['zip'], 0, 10);

        	
            $thisurl = $this->dosespot_api_url . "LoginSingleSignOn.aspx?b=2&SingleSignOnClinicId=" . $this->SingleSignOnClinicId . "&SingleSignOnUserId=" . $this->SingleSignOnUserId . "&SingleSignOnPhraseLength=" . $this->SingleSignOnPhraseLength . "&SingleSignOnCode=" . urlencode($this->SingleSignOnCode) . "&SingleSignOnUserIdVerify=" . urlencode($this->SingleSignOnUserIdVerify);
            
           //if patientID is NOT empty submit it. if it is empty, Dosespot will define one
           if (!empty($param['dosespot_patient_id']))
           {
              $thisurl .= "&PatientID=" . $param['dosespot_patient_id'] ;
           }   
         
          	$thisurl .=   "&FirstName=" . urlencode($param['first_name']) . "&MiddleName=" . urlencode($param['middle_name']) . "&LastName=" . urlencode($param['last_name']) . "&DateOfBirth=" . $param['dob'] . "&Gender=" . $param['gender'] . "&MRN=" . $param['mrn'] . "&Address1=" . urlencode($param['address_1']) . "&Address2=" . urlencode($param['address_2']) . "&City=" . urlencode($param['city']) . "&State=" . urlencode($param['state']) . "&ZipCode=" . urlencode($param['zip']);  
          	
          	
          if($param['home_phone_number'] == '000-000-0000' || empty($param['home_phone_number'])) 
          {
		  $param['home_phone_number'] = '214-555-1212';
	  }	
		
						$param['home_phone_number'] = substr($param['home_phone_number'], 0, 25);
		
          	$thisurl .= "&PrimaryPhone=" . __numeric($param['home_phone_number']) . "&PrimaryPhoneType=Home&";

            if ($param['work_phone_number'] != '')
            {
                $work_phone = $param['work_phone_number'];
                $work_phone_areacode = substr($work_phone, 0, 3);
		$param['work_phone_number'] = substr($param['work_phone_number'], 0, 25);

								
                if (in_array($work_phone_areacode, $areacode_array))
                {
                    $thisurl .= "PhoneAdditional1=" . __numeric($param['work_phone_number']) . "&PhoneAdditionalType1=Work&";
                }
            }
            if ($param['cell_phone_number'] != '')
            {
                $cell_phone = $param['cell_phone_number'];
                $cell_phone_areacode = substr($cell_phone, 0, 3);
								$param['cell_phone_number'] = substr($param['cell_phone_number'], 0, 25);

                if (in_array($cell_phone_areacode, $areacode_array))
                {
                    $thisurl .= "PhoneAdditional2=" . __numeric($param['cell_phone_number']) . "&PhoneAdditionalType2=Cell&";
                }
            }
		//cakelog::write('dosespot','URL: '.$thisurl);

            $endurl = $this->follow_redirect($thisurl);
            if ($endurl != false)
            {
                $dosespot_patientId = $this->extractPatientID($endurl);
		//cakelog::write('dosespot','OTEMR patient_id='.$patient_id.' dosespot_patient_id='.$dosespot_patientId);
                return $dosespot_patientId;
            }
    


        return '';
    }

    private function extractPatientID($query)
    {
	$query=urldecode($query);
	if (substr_count($query, '?') === 1){
  		$str=parse_url($query, PHP_URL_QUERY );
  		$str2=$str;
	} else {
  	//sometimes they have 2 ?'s in their string, so grab last one
  		list(,,$query2)=explode('?',$query);
  		$str=parse_url($query2 );
  		$str2=$str['path'];
	}
	parse_str($str2,$output);
	//only integers
	return (!empty($output['PatientID'])) ? preg_replace('/\D/', '',$output['PatientID']) : '';
    }

    public function execute($operation, $request, $soapAction)
    {
        $singleSignOn  = "<SingleSignOn>\n";
        $singleSignOn .= "<SingleSignOnClinicId>" . $this->SingleSignOnClinicId . "</SingleSignOnClinicId>\n";
        $singleSignOn .= "<SingleSignOnCode>" . $this->SingleSignOnCode . "</SingleSignOnCode>\n";
        $singleSignOn .= "<SingleSignOnUserId>" . $this->SingleSignOnUserId . "</SingleSignOnUserId>\n";
        $singleSignOn .= "<SingleSignOnUserIdVerify>" . $this->SingleSignOnUserIdVerify . "</SingleSignOnUserIdVerify>\n";
        $singleSignOn .= "<SingleSignOnPhraseLength>" . $this->SingleSignOnPhraseLength . "</SingleSignOnPhraseLength>\n";
        $singleSignOn .= "</SingleSignOn>\n";

        $soap_request = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $soap_request .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
        $soap_request .= "<soap:Body>\n";
        $soap_request .= "<" . $operation . " xmlns=\"http://www.dosespot.com/API/11/\">\n";
        $soap_request .= $singleSignOn;
        $soap_request .= $request;
        $soap_request .= "</" . $operation . ">\n";
        $soap_request .= "</soap:Body>\n";
        $soap_request .= "</soap:Envelope>";

        $header = array("Content-type: text/xml;charset=\"utf-8\"",
            "SOAPAction: \"http://www.dosespot.com/API/11/" . $soapAction . "\"",
            "Content-length: " . strlen($soap_request),
        );
	//make sure we have all required fields
	if( !empty($this->SingleSignOnClinicId)
		 && !empty($this->SingleSignOnCode)
		&& !empty($this->SingleSignOnUserId)
		&& !empty($this->SingleSignOnUserIdVerify)
		&& !empty($this->SingleSignOnPhraseLength)
		&& !empty($request)
		&& !empty($operation) 
		&& $this->isOK() 
	 	)
	{	

        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $this->dosespot_api_url . "api/11/api.asmx");
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $soap_request);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($soap_do);
	$response = curl_getinfo( $soap_do );
	
        if ($result === false || $response['http_code'] != '200' ) //200 means success
        {
            $pr = ClassRegistry::init('PracticeSetting')->getSettings();
            $err = 'Curl error: ' . curl_error($soap_do). "\n\n".print_r($response,true)."\n\n".print_r($header,true). "\n SOAP: ".htmlentities($soap_request);
            email::send('Errors', 'errors@onetouchemr.com', 'ATTN: Dosespot execute() error on '.$pr->practice_id, nl2br($err),'','',false,'','','','','');
            curl_close($soap_do);
            return $err;
        }
        else
        {
//cakelog::write('debug','EXEC '.print_r($response,true)."\n\n".print_r($header,true). "\n SOAP: ".$soap_request);
            curl_close($soap_do);
            App::import('Xml');
            /* $xmlstring = <<<XML
              $result
              XML; */
						
						// Result is empty string
						// return empty data
						if ($result == '') {
							return array();
						}
						
            $xml = new Xml($result);
            //$data = array();
            $data = Set::reverse($xml);
//cakelog::write('debug'," RESULT: ". print_r($data,true));
            return $data;
            //return $xml;
        }
      } //close loop if not all required data exists
      else
      {
	    $pr = ClassRegistry::init('PracticeSetting')->getSettings();
            $err2 = "HEADER: ".print_r($header,true). "\n\n SOAP REQUEST: ".htmlentities($soap_request);
            email::send('Errors', 'errors@onetouchemr.com', 'ATTN: Dosespot execute() error on '.$pr->practice_id, nl2br($err2),'','',false,'','','','','');
	return array();
      }
    }

    //This function is used when a new physician is added to system. 
    public function executeUser($operation, $request, $soapAction, $singlesignon_userid)
    {
        $singleSignOn = "<SingleSignOn>\n";
        $singleSignOn .= "<SingleSignOnClinicId>" . $this->SingleSignOnClinicId . "</SingleSignOnClinicId>\n";
        $singleSignOn .= "<SingleSignOnCode>" . $this->SingleSignOnCode . "</SingleSignOnCode>\n";
        $singleSignOn .= "<SingleSignOnUserId>" . $singlesignon_userid . "</SingleSignOnUserId>\n";
        $singleSignOn .= "<SingleSignOnUserIdVerify>" . $this->SingleSignOnUserIdVerify . "</SingleSignOnUserIdVerify>\n";
        $singleSignOn .= "<SingleSignOnPhraseLength>" . $this->SingleSignOnPhraseLength . "</SingleSignOnPhraseLength>\n";
        $singleSignOn .= "</SingleSignOn>\n";

        $soap_request = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $soap_request .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
        $soap_request .= "<soap:Body>\n";
        $soap_request .= "<" . $operation . " xmlns=\"http://www.dosespot.com/API/11/\">\n";
        $soap_request .= $singleSignOn;
        $soap_request .= $request;
        $soap_request .= "</" . $operation . ">\n";
        $soap_request .= "</soap:Body>\n";
        $soap_request .= "</soap:Envelope>";

        $header = array("Content-type: text/xml;charset=\"utf-8\"",
            "SOAPAction: \"http://www.dosespot.com/API/11/" . $soapAction . "\"",
            "Content-length: " . strlen($soap_request),
        );

        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $this->dosespot_api_url . "api/11/api.asmx");
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $soap_request);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($soap_do);

        if ($result === false)
        {
            $err = 'Curl error: ' . curl_error($soap_do);
            curl_close($soap_do);
            return $err;
        }
        else
        {
		//cakelog::write('debug', APP. ' xml dosespot "executeUser" parameters: header='.$header.' operation='.$operation.' request='. $request . ' soapAction='. $soapAction . ' singlesignon_userid='.$singlesignon_userid);
		//cakelog::write('debug', ' xml dosespot "executeUser" request: ' .$soap_request);
		//cakelog::write('debug',' xml dosespot "executeUser" result: ' .$result);
            curl_close($soap_do);
            App::import('Xml');
            /* $xmlstring = <<<XML
              $result
              XML; */
						
						// Result is empty string
						// return empty data
						if ($result == '') {
							return array();
						}
						
            $xml = new Xml($result);
            //$data = array();
            $data = Set::reverse($xml);

            return $data;
            //return $xml;
        }
    }

    //Search for valid Allergy Med code ID from Dosepot 
    public function searchAllergy($search)
    {
        $allergy_list_array = array();
	$search=trim($search);
	if(strlen($search) > 2) 
	{
          $soap_request = "<SearchTerm>" . strtolower($search) . "</SearchTerm>\n";
          $result_xml = $this->execute('AllergySearchRequest', $soap_request, 'AllergySearch');
				if(	isset($result_xml) &&
						isset($result_xml['Envelope']) &&
						isset($result_xml['Envelope']['Body']) && 
						isset($result_xml['Envelope']['Body']['AllergySearchResult']) &&
						isset($result_xml['Envelope']['Body']['AllergySearchResult']['SearchResults']) &&
						isset($result_xml['Envelope']['Body']['AllergySearchResult']['SearchResults']['AllergySearchResult']) &&
						$result_xml['Envelope']['Body']['AllergySearchResult']['SearchResults']['AllergySearchResult']
						)
           {
            foreach ($result_xml['Envelope']['Body']['AllergySearchResult']['SearchResults']['AllergySearchResult'] as $allergy_item)
            {
                $allergy_list_array[] = $allergy_item;
            }
           }
  	}
        return $allergy_list_array;
    }
    
    //Pass Allergy Data from EMR to Dosepot
    public function addAllergy($soap_request)
    {
        $allergy_list = array();

        $result_xml = $this->execute('AddAllergyRequest', $soap_request, 'AddAllergy');
		
				if(	isset($result_xml) &&
						isset($result_xml['Envelope']) &&
						isset($result_xml['Envelope']['Body']) && 
						isset($result_xml['Envelope']['Body']['AddAllergyResult']) &&
						isset($result_xml['Envelope']['Body']['AddAllergyResult']['PatientAllergyID']) &&
						$result_xml['Envelope']['Body']['AddAllergyResult']['PatientAllergyID'])
        {
            //foreach ($result_xml['Envelope']['Body']['AddAllergyResult']['Result'] as $allergy_item)
            //{
                //$data = array();
                $allergy_list['PatientAllergyID'] = (int)$result_xml['Envelope']['Body']['AddAllergyResult']['PatientAllergyID'];
                //$allergy_list[] = $data;
            //}
        }

        return $allergy_list;
    }

    //Edit Allergy Data in Dosepot
    public function editAllergy($soap_request)
    {
        $allergy_list = array();

        $result_xml = $this->execute('EditAllergyRequest', $soap_request, 'EditAllergy');

        if( isset($result_xml) &&
						isset($result_xml['Envelope']) &&
						isset($result_xml['Envelope']['Body']) && 
						isset($result_xml['Envelope']['Body']['EditAllergyResult']) &&
						isset($result_xml['Envelope']['Body']['EditAllergyResult']['Result']) &&
						$result_xml['Envelope']['Body']['EditAllergyResult']['Result'])
        {
            foreach ($result_xml['Envelope']['Body']['EditAllergyResult']['Result'] as $allergy_item)
            {
                $data = array();
                $data['ResultCode'] = $allergy_item['ResultCode'];
                $allergy_list[] = $data;
            }
        }

        return $allergy_list;
    }

    /**
     * Create API request that is used for adding an allergy to Dosespot.
     * 
     * @param int $patieny_id Patient ID
     * @param array $allergy_data  Allargy data
     */
    public function executeAddAllergy($patient_id, $allergy_data)
    {
        $soap_request = "<PatientId>" . $patient_id . "</PatientId>\n";
        $soap_request .= "<Allergy>\n";
        $soap_request .= "<Name>" . ucwords(strtolower($allergy_data['agent'])) . "</Name>\n";
        $soap_request .= "<Code>".$allergy_data['allergy_code']."</Code>\n";
        $soap_request .= "<CodeType>".$allergy_data['allergy_code_type']."</CodeType>\n";
        $soap_request .= "<Reaction>" . ucwords(strtolower($allergy_data['reaction1'])) . "</Reaction>\n";
        $soap_request .= "<ReactionType>Allergy</ReactionType>\n";
        $soap_request .= "<StatusType>Active</StatusType>\n";
        $soap_request .= "<OnsetDate>" . __date('Y-m-d\Th:i:s') . "</OnsetDate>\n";
        $soap_request .= "</Allergy>\n";

        return $this->addAllergy($soap_request);
    }

    /**
     * Create API request that is used for updating/deleting an allergy in Dosespot.
     * 
     * @param int $patieny_id Patient ID
     * @param array $allergy_data  Allargy data
     */
    public function executeEditAllergy($patient_id, $allergy_data)
    {
        $soap_request = "<PatientId>" . $patient_id . "</PatientId>\n";
        $soap_request .= "<Allergy>\n";
        $soap_request .= "<PatientAllergyId>" . (int) $allergy_data['dosespot_allergy_id'] . "</PatientAllergyId>\n";
        $soap_request .= "<Reaction>" . ucwords(strtolower($allergy_data['reaction1'])) . "</Reaction>\n";
        $soap_request .= "<ReactionType>Allergy</ReactionType>\n";
        $soap_request .= "<StatusType>" . $allergy_data['status'] . "</StatusType>\n";
        $soap_request .= "<OnsetDate>" . __date('Y-m-d\Th:i:s') . "</OnsetDate>\n";
        $soap_request .= "</Allergy>\n";

        $this->editAllergy($soap_request);
    }

    public function getAllergyList($patient_id)
    {
        $allergy_list = array();

        $soap_request = "<PatientId>" . $patient_id . "</PatientId>\n";

        $result_xml = $this->execute('AllergyListRequest', $soap_request, 'AllergyList');

				if(	isset($result_xml) &&
						isset($result_xml['Envelope']) &&
						isset($result_xml['Envelope']['Body']) && 
						isset($result_xml['Envelope']['Body']['AllergyListResult']) &&
						isset($result_xml['Envelope']['Body']['AllergyListResult']['Allergies']) &&
        		$result_xml['Envelope']['Body']['AllergyListResult']['Allergies'])
        {
            $allergy_array = $result_xml['Envelope']['Body']['AllergyListResult']['Allergies']['Allergy'];
            if (!isset($allergy_array[0]))
            {
                $data = array();
                $data['PatientAllergyId'] = (int) $allergy_array['PatientAllergyId'];
                $data['agent'] = $allergy_array['Name'];
                $data['reaction1'] = $allergy_array['Reaction'];
                $data['status'] = $allergy_array['StatusType'];

                $allergy_list[] = $data;
            }
            else
            {
                foreach ($allergy_array as $allergy_item)
                {
                    $data = array();
                    $data['PatientAllergyId'] = (int) $allergy_item['PatientAllergyId'];
                    $data['agent'] = $allergy_item['Name'];
                    $data['reaction1'] = $allergy_item['Reaction'];
                    $data['status'] = $allergy_item['StatusType'];

                    $allergy_list[] = $data;
                }
            }
        }

        return $allergy_list;
    }

    public function getMedicationList($dosespot_patient_id,$surescripts_hx='',$start_date='',$end_date='')
    {
        	$soap_request = "<PatientId>".$dosespot_patient_id."</PatientId>\n";
		$soap_request .= "<Sources>\n";
	   if($surescripts_hx) {
		$soap_request .= "<MedicationSourceType>SurescriptsHistory</MedicationSourceType>\n";
	   } else {
		$soap_request .= "<MedicationSourceType>Unknown</MedicationSourceType>\n";
		$soap_request .= "<MedicationSourceType>Prescription</MedicationSourceType>\n";
		$soap_request .= "<MedicationSourceType>SelfReported</MedicationSourceType>\n";
		$soap_request .= "<MedicationSourceType>Imported</MedicationSourceType>\n";                
	   }
		$soap_request .= "</Sources>\n";
		$soap_request .= "<Status>\n";
		$soap_request .= "<MedicationStatusType>Unknown</MedicationStatusType>\n";
		$soap_request .= "<MedicationStatusType>Active</MedicationStatusType>\n";
		$soap_request .= "<MedicationStatusType>Inactive</MedicationStatusType>\n";
		$soap_request .= "<MedicationStatusType>Completed</MedicationStatusType>\n";
		$soap_request .= "<MedicationStatusType>Discontinued</MedicationStatusType>\n";
		$soap_request .= "</Status>\n";

		if($start_date) {
		  $st_date=__date('Y-m-d\Th:i:s', strtotime($start_date));
		} else {
		  $st_date=__date('Y-m-d\Th:i:s', strtotime("-1 year")); //mktime(0, 0, 0, __date('m'), __date('d'), __date('Y') - 1));
		}
	        $soap_request .= "<StartDate>". $st_date ."</StartDate>\n";

		if($end_date) {
		  $e_date= __date('Y-m-d\Th:i:s', strtotime($end_date));
		} else {
		  $e_date= __date('Y-m-d\Th:i:s', strtotime("+1 day")); //mktime(0, 0, 0, __date('m'), __date('d')+1, __date('Y')));
		}
      		$soap_request .= "<EndDate>". $e_date."</EndDate>\n";
		$medication_list = array();

        $result_xml = $this->execute('GetMedicationListByDateRangeRequest', $soap_request, 'GetMedicationListByDateRange');
        if( isset($result_xml) &&
        		isset($result_xml['Envelope']) &&
        		isset($result_xml['Envelope']['Body']) &&
        		isset($result_xml['Envelope']['Body']['GetMedicationListByDateRangeResult']) && 
        		isset($result_xml['Envelope']['Body']['GetMedicationListByDateRangeResult']['Medications']) && 
        		isset($result_xml['Envelope']['Body']['GetMedicationListByDateRangeResult']['Medications']['MedicationDetailedListItem']))
        {
            $medication_array = $result_xml['Envelope']['Body']['GetMedicationListByDateRangeResult']['Medications']['MedicationDetailedListItem'];

            if (!isset($medication_array[0]))
            {
            	$medication_list[]=$this->parseMedicationList($medication_array,$surescripts_hx,$soap_request);
            }
            else
            {
                foreach ($medication_array as $medication_item)
                {
			$medication_list[]=$this->parseMedicationList($medication_item,$surescripts_hx,$soap_request);		
                }
                
            }

        }
	$medication_list=array_filter($medication_list); 
        return $medication_list;
    }


    function parseMedicationList($medication_item,$surescripts_hx,$soap_request)
    {
    	$data=array();
	if($surescripts_hx && $medication_item['Source'] == 'SurescriptsHistory')
	{
		$data['MedicationId'] = (int) $medication_item['MedicationId'];
		$data['medication'] = $medication_item['DisplayName'];
		$data['prescriber_user_id'] = $medication_item['PrescriberUserId'];
                if (is_array($medication_item['DaysSupply'])) // this means no days supply was provided, so make empty
                {
                        $data['days_supply'] = 0;
                } else {
                        $data['days_supply'] = $medication_item['DaysSupply'];
                }
		$data['quantity_value'] = $medication_item['Quantity'];
                if ((!is_array($medication_item['DateWritten'])) and ($medication_item['DateWritten'] != ''))
                {
                        $data['date_written'] = __date('Y-m-d', strtotime($medication_item['DateWritten']));
                }
                if ((!is_array($medication_item['DateLastFilled'])) and ($medication_item['DateLastFilled'] != ''))
                {
                        $data['date_written'] = __date('Y-m-d', strtotime($medication_item['DateLastFilled']));
                }
		$data['direction'] = '';
		$data['refill_allowed'] = 0;
		$data['status'] = 'Completed';
	}
	else if(in_array($medication_item['PrescriptionStatus'], array('Sending', 'eRxSent', 'FaxSent', 'Printed')))
	{
		$data['MedicationId'] = (int) $medication_item['MedicationId'];
		$data['medication'] = $medication_item['DisplayName'];
		if (is_array($medication_item['MedicationStatus']))
		{
			$data['status'] = 'Completed';
		} else {
			$data['status'] = $medication_item['MedicationStatus'];
		}
		$data['prescription_status'] = $medication_item['PrescriptionStatus'];
		$data['direction'] = $medication_item['Notes'];
		$data['prescriber_user_id'] = $medication_item['PrescriberUserId'];

		if (is_array($medication_item['Refills'])) // no refills
		{
		  	$data['refill_allowed'] = '';
		} else {
	  		$data['refill_allowed'] = $medication_item['Refills'];	
		}						
    
    
		if (is_array($medication_item['DaysSupply'])) // this means no days supply was provided, so make empty
		{
		  	$data['days_supply'] = 0;
		} else {
	  		$data['days_supply'] = $medication_item['DaysSupply'];	
		}						
		//$data['dispense'] = $medication_item['Quantity'].' '.$medication_item['DispenseUnits'];
		$data['quantity_value'] = $medication_item['Quantity'];//.' '.$medication_array['DispenseUnits'];
		$data['date_written'] = '';
		$data['date_inactive'] = '';
		if ((!is_array($medication_item['DateWritten'])) and ($medication_item['DateWritten'] != ''))
		{
			$data['date_written'] = __date('Y-m-d', strtotime($medication_item['DateWritten']));
		}
		if ((!is_array($medication_item['DateInactive'])) and ($medication_item['DateInactive'] != ''))
		{
			$data['date_inactive'] = __date('Y-m-d', strtotime($medication_item['DateInactive']));
		}
	} else if ($medication_item['PrescriptionStatus'] == 'Entered' || $medication_item['PrescriptionStatus'] == 'Edited') {
		// this means they entered into the e-Rx screen, but haven't submitted the order to pharmacy yet .... so do nothing
	} else {
              // shouldn't end up here... if so notify
              //cakelog::write('dosespot',"getMedicationList() error:: \n".print_r($medication_item,true). "\n\nSOAP --: ".$soap_request);
        }	   
    	return $data;
    }

    /* i can't find this being used anywhere. getRefillRequestDetails() seems like exact same function 
    public function getRefillRequest()
    {
        $soap_request = "<StartDate>" . __date('Y-m-d\Th:i:s', mktime(0, 0, 0, __date('m'), __date('d'), __date('Y') - 1)) . "</StartDate>\n";
        $soap_request .= "<EndDate>" . __date('Y-m-d\Th:i:s') . "</EndDate>\n";
        $soap_request .= "<Status>All</Status>\n";

        $refill_list = array();

        $result_xml = $this->execute('GetRefillRequestsDetailsRequest', $soap_request, 'GetRefillRequestsDetails');

				if(	isset($result_xml) &&
						isset($result_xml['Envelope']) &&
						isset($result_xml['Envelope']['Body']) && 
						isset($result_xml['Envelope']['Body']['GetRefillRequestsDetailsResult']) &&
						isset($result_xml['Envelope']['Body']['GetRefillRequestsDetailsResult']['RefillRequestsDetails']) &&
        		$result_xml['Envelope']['Body']['GetRefillRequestsDetailsResult']['RefillRequestsDetails'])
        {

            $refill_array = $result_xml['Envelope']['Body']['GetRefillRequestsDetailsResult']['RefillRequestsDetails']['RefillRequestDetails'];
            if (!isset($refill_array[0]))
            {
                $data = array();
                $data['PatientID'] = ClassRegistry::init('PatientDemographic')->getPatientIdbyDosespotId((int) $refill_array['PatientId']);
                $data['FirstName'] = $refill_array['FirstName'];
                $data['LastName'] = $refill_array['LastName'];
                $data['DisplayName'] = $refill_array['Medication']['DisplayName'];
                $data['DateRequested'] = $refill_array['DateRequested'];
                $data['MedicationStatus'] = $refill_array['Medication']['MedicationStatus'];
                $data['Refills'] = $refill_array['Medication']['Refills'];
                $data['Source'] = $refill_array['Medication']['Source'];

                $refill_list[] = $data;
            }
            else
            {
                foreach ($refill_array as $refill_item)
                {
                    $data = array();
                    $data['PatientID'] = ClassRegistry::init('PatientDemographic')->getPatientIdbyDosespotId($refill_item['PatientId']);
                    $data['FirstName'] = $refill_item['FirstName'];
                    $data['LastName'] = $refill_item['LastName'];
                    $data['DisplayName'] = $refill_item['Medication']['DisplayName'];
                    $data['DateRequested'] = $refill_item['DateRequested'];
                    $data['MedicationStatus'] = $refill_item['Medication']['MedicationStatus'];
                    $data['Refills'] = $refill_item['Medication']['Refills'];
                    $data['Source'] = $refill_item['Medication']['Source'];

                    $refill_list[] = $data;
                }
            }
        }
        return $refill_list;
    }
	*/

    /**
     * Retrieve the complete details of Dosespot Refill Requests
     * 
     * @return array Array data representing Refill Requests details
     */
    public function getRefillRequestDetails($from_date)
    {
	
        $soap_request = "<ModifiedStartDate>" . $from_date . "</ModifiedStartDate>\n";
        $soap_request .= "<ModifiedEndDate>" . __date('Y-m-d\Th:i:s') . "</ModifiedEndDate>\n";
        $soap_request .= "<Status>Queued</Status>\n"; //former valuel: All

        $refill_list = array();

        $result_xml = $this->execute('GetRefillRequestsDetailsRequest', $soap_request, 'GetRefillRequestsDetails');
				if(	isset($result_xml) &&
						isset($result_xml['Envelope']) &&
						isset($result_xml['Envelope']['Body']) && 
						isset($result_xml['Envelope']['Body']['GetRefillRequestsDetailsResult']) &&
						isset($result_xml['Envelope']['Body']['GetRefillRequestsDetailsResult']['RefillRequestsDetails']) &&
        		$result_xml['Envelope']['Body']['GetRefillRequestsDetailsResult']['RefillRequestsDetails'])
        {
            $refill_array = $result_xml['Envelope']['Body']['GetRefillRequestsDetailsResult']['RefillRequestsDetails']['RefillRequestDetails'];
            if (!isset($refill_array[0]))
            {
                $data = array();
                $patient_id = ClassRegistry::init('PatientDemographic')->getPatientIdbyDosespotId((int) $refill_array['PatientId']);
                $data['PatientID'] = ($patient_id != '') ? $patient_id : ((int) $refill_array['PatientId']);
                $data['FirstName'] = $refill_array['FirstName'];
                $data['LastName'] = $refill_array['LastName'];
                $data['ClinicianId'] = $refill_array['ClinicianId'];
                $data['ClinicianFirstName'] = $refill_array['ClinicianFirstName'];
                $data['ClinicianLastName'] = $refill_array['ClinicianLastName'];
                $data['DateRequested'] = $refill_array['DateRequested'];
                $data['MedicationId'] = $refill_array['Medication']['MedicationId'];
                $data['DisplayName'] = $refill_array['Medication']['DisplayName'];
                $data['MedicationStatus'] = $refill_array['Medication']['MedicationStatus'];
                $data['Quantity'] = $refill_array['Medication']['Quantity'];
                $data['Refills'] = $refill_array['Medication']['Refills'];
                $data['Source'] = $refill_array['Medication']['Source'];
                $data['RequestStatus'] = $refill_array['RequestStatus'];
                $refill_list[] = $data;
            }
            else
            {
                foreach ($refill_array as $refill_item)
                {
                    $data = array();
                    $patient_id = ClassRegistry::init('PatientDemographic')->getPatientIdbyDosespotId($refill_item['PatientId']);
                    $data['PatientID'] = ($patient_id != '') ? $patient_id : ((int) $refill_item['PatientId']);
                    $data['FirstName'] = $refill_item['FirstName'];
                    $data['LastName'] = $refill_item['LastName'];
                    $data['ClinicianId'] = $refill_item['ClinicianId'];
                    $data['ClinicianFirstName'] = $refill_item['ClinicianFirstName'];
                    $data['ClinicianLastName'] = $refill_item['ClinicianLastName'];
                    $data['DateRequested'] = $refill_item['DateRequested'];
                    $data['MedicationId'] = $refill_item['Medication']['MedicationId'];
                    $data['DisplayName'] = $refill_item['Medication']['DisplayName'];
                    $data['MedicationStatus'] = $refill_item['Medication']['MedicationStatus'];
                    $data['Quantity'] = $refill_item['Medication']['Quantity'];
                    $data['Refills'] = $refill_item['Medication']['Refills'];
                    $data['Source'] = $refill_item['Medication']['Source'];
                    $data['RequestStatus'] = $refill_item['RequestStatus'];
                    $refill_list[] = $data;
                }
            }
        }
        return $refill_list;
    }

    //Pass Clinician Data from EMR to Dosepot
    public function addClinician($soap_request, $singlesignon_userid)
    {
        $result_xml = $this->executeUser('ClinicianAddMessage', $soap_request, 'ClinicianAddMessage', $singlesignon_userid);

				if(	isset($result_xml) &&
						isset($result_xml['Envelope']) &&
						isset($result_xml['Envelope']['Body']) && 
						isset($result_xml['Envelope']['Body']['ClinicianAddMessageResult']) &&
						isset($result_xml['Envelope']['Body']['ClinicianAddMessageResult']['Result']) &&
						isset($result_xml['Envelope']['Body']['ClinicianAddMessageResult']['Result']['ResultCode']) &&
        		$result_xml['Envelope']['Body']['ClinicianAddMessageResult']['Result']['ResultCode'] == 'OK')
        {
            return $result_xml['Envelope']['Body']['ClinicianAddMessageResult']['Clinician']['ClinicianId'];
        }
        else
        {
            return false;
        }
    }

    protected function SSO($key, $uid)
    {
        $length = 32;
        $aZ09 = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        $randphrase = '';
        // Generate random phrase
        for ($c = 0; $c < $length; $c++)
        {
            $randphrase .= $aZ09[mt_rand(0, count($aZ09) - 1)];
        }
        //echo "Key: ".$key."<br/>";
        //echo "Phrase: ".$randphrase."<br/>";
        //Append key onto phrase end
        $randkey = $randphrase . $key;
        // SHA512 Hash
        $toencode = utf8_encode($randkey);
        // Pass 3rd, optional parameter as TRUE to output raw binary data
        $output = hash("sha512", $toencode, true);
        //base 64 encode the hash binary data
        $sso = base64_encode($output);
        $length = mb_strlen($sso);
        $characters = 2;
        $start = $length - $characters;
        $last2 = substr($sso, $start, $characters);
        // Yes, Strip the extra ==
        if ($last2 == "==")
        {
            $ssocode = substr($sso, 0, -2);
        }
        // No, just pass the value to the next step
        else
        {
            $ssocode = $sso;
        }
        // Prepend the random phrase to the encrypted code.
        $ssocode = $randphrase . $ssocode;
        //echo "SSO: ".$ssocode."<br/>";
        //Use first 22 characters of random.
        $shortphrase = substr($randphrase, 0, 22);
        //Append uid & key onto shortened phrase end
        $uidv = $uid . $shortphrase . $key;
        // SHA512 Hash
        $idencode = utf8_encode($uidv);
        // Pass 3rd, optional parameter as TRUE to output raw binary data
        $idoutput = hash("sha512", $idencode, true);
        // Base64 Encode of hash binary data
        $idssoe = base64_encode($idoutput);
        //Determine if we need to strip the zeros
        $idlength = mb_strlen($idssoe);
        $idcharacters = 2;
        $idstart = $idlength - $idcharacters;
        $idlast2 = substr($idssoe, $idstart, $idcharacters);
        if ($idlast2 == "==")
        {
            $ssouidv = substr($idssoe, 0, -2);
        }
        // No, just pass the value to the next step
        else
        {
            $ssouidv = $idssoe;
        }
        //echo "SSOID: ".$ssouidv."<br/>";

        return array($ssocode, $ssouidv);
    }

}

?>
