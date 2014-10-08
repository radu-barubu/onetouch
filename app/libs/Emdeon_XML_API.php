<?php

class Emdeon_XML_API
{
    public $facility;
    public $username;
    public $password;
    public $clinician_url;
    
    public $xmlapi_username;
    public $xmlapi_password;
    public $request_xmlapi;
    public $host;
    public $cache_file_prefix;
    public $batchDownload = false;
		
    public function isOK()
    {
    	return $this->checkConnection();
    }
 
    /*
    * make sure Emdeon RX is turned on for e-Rx functions
    *
    */
    public function isEmdeonRX()
    {
	if(isset($_SESSION['PracticeSetting'])) {
 	  $practice_settings = (object) $_SESSION['PracticeSetting']['PracticeSetting'];
	} else {
	  $practice_settings = ClassRegistry::init('PracticeSetting')->getSettings();
	}
        if($practice_settings->rx_setup == 'Electronic_Emdeon')
        {
            return true;
        } 
	else
	{
	   return false;
	} 

    }   
	/**
    * Verify Emdeon connectivity
    * 
    *
    * @return boolean - true if connection succeeded
    */
    public function checkConnection()
    {
        $practice_settings = ClassRegistry::init('PracticeSetting')->getSettings();
        
        if(isset($practice_settings->labs_setup) && $practice_settings->labs_setup != 'Electronic')
        {
            return false;
        }
        
        if(!($practice_settings->emdeon_host && $practice_settings->emdeon_facility && $practice_settings->emdeon_username && $practice_settings->emdeon_password))
        {
            return false;
        }
        return true;
    }
    
    public function Emdeon_XML_API()
    {
        $this->PracticeSetting =& ClassRegistry::init('PracticeSetting');
        $practice_settings = (array)$this->PracticeSetting->getSettings();

        // if electronic labs are enabled
        if($practice_settings['labs_setup'] != 'Electronic')
        {
            return false;
        }

        $default = array(
            'emdeon_host' => 'cli-cert.emdeon.com',
            'emdeon_facility' => '2504927231',
            'emdeon_username' => 'p_otemr_clc',
            'emdeon_password' => 'practice00'
        );
        
        
        $this->host = (trim($practice_settings['emdeon_host']) !='') ? $practice_settings['emdeon_host'] : $default['emdeon_host']  ;//"cli-cert.emdeon.com";
        $this->facility = (trim($practice_settings['emdeon_facility']) !='') ? $practice_settings['emdeon_facility'] : $default['emdeon_facility']  ;//"2504927231";
        $this->username = (trim($practice_settings['emdeon_username']) !='') ? $practice_settings['emdeon_username'] : $default['emdeon_username'] ; //'p_otemr_clc or p_otemr1';
        $this->password = (trim($practice_settings['emdeon_password']) !='') ? $practice_settings['emdeon_password'] : $default['emdeon_password'] ; //'practice00';
        
        if(isset($this->host) AND isset($this->facility) AND isset($this->username) AND isset($this->password))
        {
            $this->clinician_url = "https://".$this->host;
        }
        else
        {
            $this->clinician_url = "";
        }
          
        $this->xmlapi_username = $this->username;
        $this->xmlapi_password = $this->password;
        
        if(isset($this->host) AND isset($this->xmlapi_username) AND isset($this->xmlapi_password))
        {
            $this->request_xmlapi = "https://".$this->host."/servlet/XMLServlet";
        }
        else
        {
            $this->request_xmlapi = "";
        }
        
        /*Prepare cache file prefix for multiple host*/
        $db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
        $this->cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
    }
    
    public function formatPhone($phone)
    {
        $phone = str_replace("(", "", $phone);
        $phone = str_replace(")", "", $phone);
        $phone = str_replace("-", "", $phone);
        
        if(strlen($phone) == 10)
        {
            $phone = '(' . substr($phone, 0, 3) . ')' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
        }
        
        return $phone;
    }
    
    public function getTextBetween($strlist, $start, $end, $add_pre, $add_post)
    {
        $i = 0;
        
        $item_count = 0;
        
        $item_arr = array();
        
        while($i < strlen($strlist))
        {
            $ch = substr($strlist, $i, 1);
            
            $search_str = $start;
            if(substr($strlist, $i, strlen($search_str)) == $search_str)
            {
                $i += strlen($search_str);
                
                $item = "";
                while(1)
                {
                    $ch = substr($strlist, $i, 1);
                    
                    $current_item_str = substr($strlist, $i, strlen($end));
                    
                    if($current_item_str == $end)
                    {
                        break;
                    }
                    
                    $item .= substr($strlist, $i, 1);
                    
                    $i++;
                    
                    if($i > strlen($strlist))
                    {
                        break;
                    }
                }
    
                $item_arr[$item_count++] =  $add_pre . $item . $add_post;
            }
            
            
            $i++;
        }
        
        return $item_arr;
    }
    
    public function cleanData($data)
    {
        return trim((string)$data);
    }
    
    public function getInfo()
    {
        $ret = array();
        $ret['host'] = $this->host;
        $ret['facility'] = $this->facility;
        $ret['username'] = $this->username;
        $ret['password'] = $this->password;
        $ret['request_xmlapi'] = $this->request_xmlapi;
        
        return $ret;
    }
    
    public function execute($object_name, $object_op, $object_param, &$error_message = NULL)
    {
	if (!$this->isOK())
		return array();
    
        $result = array();
      if(!empty($this->xmlapi_username) && !empty($this->xmlapi_password) && !empty($object_op) && !empty($this->facility) )
      {        
        $request = '<?xml version="1.0"?>
        <REQUEST userid="'.$this->xmlapi_username.'" password="'.$this->xmlapi_password.'" name="'.$object_op.'" facility="'.$this->facility.'">
        <OBJECT name="'.$object_name.'" op="'.$object_op.'">';
        
        foreach($object_param as $key => $value)
        {
            $request .= '<'.$key.'>'.htmlentities($value, ENT_QUOTES).'</'.$key.'>';
        }
        
        $request .= '</OBJECT></REQUEST>';
        $log_req = $request;
        $log_data = "Request: " . $request . "\r\n\r\n";
        
        $post_url = $this->request_xmlapi;
        $post_data = "request=".urlencode($request);
        
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $post_url);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.13) Gecko/2009073022 Firefox/3.0.13 GTB5");
        curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt ($ch, CURLOPT_POST, 1);
        $content = curl_exec ($ch);
    	$cresponse=curl_getinfo( $ch );
        $log_data .= "Response: " . $content . "\r\n\r\n";
        
        if($content === false || curl_errno($ch) || strstr($content, '<ERROR>')) //if not successful
        {
			if(strstr($content, '<ERROR>'))
			{
				$error_xml = new SimpleXMLElement($content);
				@$error_message = (string)$error_xml->description;
			}
			
			
                $practice_settings = ClassRegistry::init('PracticeSetting')->getSettings();
                $Body="Emdeon cURL error! Client: ".$practice_settings->practice_id." \n\n server: ".gethostname()." \n\n cURL error: ".curl_error($ch). " \n\n Request: ".htmlentities($log_req)." \n\n post_data: ".htmlentities(urldecode ($post_data))." \n\n output: ".htmlentities($log_data). "\n\n Curl Info : ".print_r($cresponse,true) ;
		if (!stristr($post_data,'admin') && !strstr($content,'Too many rows') )
                	email::send('Errors', 'errors@onetouchemr.com', 'ATTN: Emdeon cURL error'.ROOT, nl2br($Body),'','',false,'','','','','');
        }
        //CakeLog::write('debug', $log_data);
        
        $result['debug'] = $content;
        $result['debug_output'] = htmlentities($content, ENT_QUOTES);
        
        $xml = simplexml_load_string($content);
        $result['xml'] = $xml;
		
		if(strpos($content, "<ERROR>") !== false)
		{
			$dummy_content = "<?xml version='1.0'?><RESULT sessionid=\"this_is_dummy_content\"></RESULT>";
			$xml = simplexml_load_string($dummy_content);
        	$result['xml'] = $xml;
		}
		
      }    
        return $result;
    }
	
	public function executeDUR($object_name, $object_op, $object_param, $PatientMedicationList_items)
    {
        if (!$this->isOK())
                return array();
    
        $result = array();
        
        $request = '<?xml version="1.0"?>
        <REQUEST userid="'.$this->xmlapi_username.'" password="'.$this->xmlapi_password.'" name="'.$object_op.'" facility="'.$this->facility.'">
        <OBJECT name="'.$object_name.'" op="'.$object_op.'">';
        
        foreach($object_param as $key => $value)
        {
            $request .= '<'.$key.'>'.htmlentities($value, ENT_QUOTES).'</'.$key.'>';
        }

		foreach($PatientMedicationList_items as $patientMedicationList_item)
        {
		    $existing_drug_id = $patientMedicationList_item['PatientMedicationList']['emdeon_drug_id'];
			$existing_drug_name = $patientMedicationList_item['PatientMedicationList']['medication'];
			$icd_code = $patientMedicationList_item['PatientMedicationList']['icd_code'];
            $request .= '<ExistingMeds><drug id="'.$existing_drug_id.'" name="'.$existing_drug_name.'"/></ExistingMeds>';
			$request .= '<ExistingICD9s><icd9 code="'.$icd_code.'"/></ExistingICD9s>';
			
        }
        
        $request .= '</OBJECT></REQUEST>';
        
        $log_data = "Request: " . $request . "\r\n\r\n";
        
        $post_url = $this->request_xmlapi;
        $post_data = "request=".urlencode($request);
        
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $post_url);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.13) Gecko/2009073022 Firefox/3.0.13 GTB5");
        curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt ($ch, CURLOPT_POST, 1);
        $content = curl_exec ($ch);
        
        $log_data .= "Response: " . $content . "\r\n\r\n";
        
        //CakeLog::write('xml_api', $log_data);
        
        $result['debug'] = $content;
        $result['debug_output'] = htmlentities($content, ENT_QUOTES);
        
        $xml = simplexml_load_string($content);
        $result['xml'] = $xml;
		
		if(strpos($content, "<ERROR>") !== false)
		{
			$dummy_content = "<?xml version='1.0'?><RESULT sessionid=\"this_is_dummy_content\"></RESULT>";
			$xml = simplexml_load_string($dummy_content);
        	$result['xml'] = $xml;
		}
		
        
        return $result;
    }
	
	public function executeDURAllergies($object_name, $object_op, $object_param, $PatientAllergy_items)
    {
         if (!$this->isOK())
                return array();
    
        $result = array();
        
        $request = '<?xml version="1.0"?>
        <REQUEST userid="'.$this->xmlapi_username.'" password="'.$this->xmlapi_password.'" name="'.$object_op.'" facility="'.$this->facility.'">
        <OBJECT name="'.$object_name.'" op="'.$object_op.'">';
        
        foreach($object_param as $key => $value)
        {
            $request .= '<'.$key.'>'.htmlentities($value, ENT_QUOTES).'</'.$key.'>';
        }
		foreach($PatientAllergy_items as $PatientAllergy_item)
        {
		    $existing_drug_name = $PatientAllergy_item['allergy_name'];
		    $existing_drug_id = $PatientAllergy_item['allergy_id'];
            $request .= '<Allergies><allergy id="'.$existing_drug_id.'" name="'.$existing_drug_name.'" type="fdbATDrugName" /></Allergies>';
		}	
			
        $request .= '</OBJECT></REQUEST>';
		
        $log_data = "Request: " . $request . "\r\n\r\n";
        
        $post_url = $this->request_xmlapi;
        $post_data = "request=".urlencode($request);
        
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $post_url);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.13) Gecko/2009073022 Firefox/3.0.13 GTB5");
        curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt ($ch, CURLOPT_POST, 1);
        $content = curl_exec ($ch);
        
        $log_data .= "Response: " . $content . "\r\n\r\n";
        
        //CakeLog::write('xml_api', $log_data);
        
        $result['debug'] = $content;
        $result['debug_output'] = htmlentities($content, ENT_QUOTES);
        
        $xml = simplexml_load_string($content);
        $result['xml'] = $xml;
		
		if(strpos($content, "<ERROR>") !== false)
		{
			$dummy_content = "<?xml version='1.0'?><RESULT sessionid=\"this_is_dummy_content\"></RESULT>";
			$xml = simplexml_load_string($dummy_content);
        	$result['xml'] = $xml;
		}
		
        return $result;
    }
    
    public function extractPhone($phone_number)
    {
        $phone_number = str_replace("-", "", $phone_number);
        
        $ret['area_code'] = substr($phone_number, 0, 3);
        $ret['phone'] = substr($phone_number, 3);
        
        return $ret;
    }
    
    public function getReportList($report_types, $date_from = "", $date_to = "")
    {
        if (!$this->isOK())
                return array();

        // If any required login details is missing
        // immedialtey return
        if (! ($this->clinician_url && $this->username && $this->password) ) {
            return array();
        }
        if($this->username == 'p_otemr1') { //this is staging username, do not use it for downloading reports
		return array();	
	}	
		//batchDownload – optional field used to download all matching reports from the search and not just undownloaded reports. Leave this field off to only get new reports.
		$batchDownload = ($this->batchDownload) ? 'true' : 'false';
        $cookie = tempnam ("/tmp", "CURLCOOKIE");
        $err_log=ROOT.'/'.APP_DIR.'/tmp/logs/emdeon_labs.log';
        $timeout = 30;
        $debugval=Configure::read('debug');
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.13) Gecko/2009073022 Firefox/3.0.13 GTB5");
        curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookie);
        if($debugval == 2){
        $logfh = fopen($err_log, 'a+');
        curl_setopt ($ch, CURLOPT_VERBOSE, TRUE);
        curl_setopt ($ch, CURLOPT_STDERR, $logfh);
        }
        //login
        $url = $this->clinician_url . "/servlet/DxLogin?userid=".$this->username."&PW=".$this->password."&apiLogin=true"."&target=html/LoginSuccess.html";
        curl_setopt ($ch, CURLOPT_URL, $url);
        $content = curl_exec ($ch);
        
        if(curl_errno($ch))
        {
            return array();
        }
        
        //get report types
        $url = $this->clinician_url . "/servlet/lab.results.fdcServlet?actionCommand=GetReportTypes&fdcuserid=".$this->username;
        curl_setopt ($ch, CURLOPT_URL, $url);
        $content = curl_exec ($ch);
        
        if(curl_errno($ch))
        {
            return array();
        }

        //start download
				
				$date_from = strtotime($date_from);
				$date_to = strtotime($date_to);
				
				$date_from = $date_from ? __date('m/d/Y', $date_from) : __date('01/01/Y');
				$date_to = $date_to ? __date('m/d/Y', $date_to) : __date('12/31/Y');
				
        $url = $this->clinician_url . "/servlet/DxLogin?userid=".$this->username."&target=jsp/lab/results/FDC.jsp&actionCommand=startDownload&autoPrint=true&batchDownload=".$batchDownload."&EMR=n&reportTypes=".$report_types.'&CreationDateFrom='.$date_from.'&CreationDateTo='.$date_to;
		//CakeLog::write('debug',"Labs download URL: ".$url);
        curl_setopt ($ch, CURLOPT_URL, $url);
        $content = curl_exec ($ch);
        
        if(curl_errno($ch))
        {
            return array();
        }
        
        $report_list = array();
        $report_count = 0;
        
        while(true)
        {
            $url = $this->clinician_url . "/servlet/lab.results.fdcServlet?actionCommand=NextFile&batchDownload=".$batchDownload."&fdcuserid=".$this->username."";
            curl_setopt ($ch, CURLOPT_URL, $url);
            $content = curl_exec ($ch);
            
            if(curl_errno($ch))
            {
                return array();
            }
            
            $content = str_replace('<--BEGIN NUMBER OF DOCS>', '', $content);
            $content = str_replace('<--END NUMBER OF DOCS>', '', $content);
            $content_arr = explode(";", $content);
			
            if((int)$content_arr[0] < 0)
            {
                break;
            }
            
            $report_list[$report_count]['id'] = trim($content_arr[2]);
    
            $url = $this->clinician_url . "/servlet/lab.results.fdcServlet?actionCommand=GetFileInfo&fdcuserid=".$this->username."";
            curl_setopt ($ch, CURLOPT_URL, $url);
            $content = curl_exec ($ch);
            
            if(curl_errno($ch))
            {
                return array();
            }
        
            $content = str_replace('<--BEGIN FILE INFO>', '', $content);
            $content = str_replace('<--END FILE INFO>', '', $content);
            $content_arr = explode(";", $content);
            
            $report_list[$report_count]['sponsor_name'] = trim(@$content_arr[0]);
            $report_list[$report_count]['report_type'] = trim(@$content_arr[1]);
            $report_list[$report_count]['receiving_client_id'] = trim(@$content_arr[2]);
            
            $content_arr2 = explode('_', trim(@$content_arr[3]));
            $report_list[$report_count]['account_number'] = trim(@$content_arr2[0]);
            $report_list[$report_count]['last_name'] = trim(@$content_arr2[1]);
            $report_list[$report_count]['first_name'] = trim(@$content_arr2[2]);
            
            $report_list[$report_count]['unique_id'] = trim(@$content_arr[4]);
            $report_list[$report_count]['mime_type'] = trim(@$content_arr[5]);
            $report_list[$report_count]['special'] = trim(@$content_arr[6]);
            
            $content_arr3 = explode('__', trim(@$content_arr[7]));
            $content_arr4 = explode('_', trim(@$content_arr3[0]));
            $content_arr5 = explode('_', trim(@$content_arr3[1]));
            $report_list[$report_count]['request_or_service_date'] = @$content_arr4[0] . '/' . @$content_arr4[1] . '/' . @$content_arr4[2] . ' ' . @$content_arr5[0] . ':' . @$content_arr5[1] . ' ' . @$content_arr5[2];
            
            $report_list[$report_count]['sponsor_code'] = trim(@$content_arr[8]);
            $report_list[$report_count]['caregiver_id'] = trim(@$content_arr[9]);
            $report_list[$report_count]['report_service_date'] = str_replace('  ', ' ', trim(@$content_arr[13]));
            
            $report_count++;
        }
	curl_close ( $ch );
	if($debugval == 2){fclose($logfh);}	
        return $report_list;
    }
    
    public function getReport($unique_ids, $report_types = "LABRES", $date_from = '', $date_to = '')
    {
        if (!$this->isOK())
                return array();

		//batchDownload – optional field used to download all matching reports from the search and not just undownloaded reports. Leave this field off to only get new reports.
		$batchDownload = ($this->batchDownload) ? 'true' : 'false';
		
        // If any required login details is missing
        // immedialtey return
        if (! ($this->clinician_url && $this->username && $this->password) ) {
            return array();
        } 
		
		if(count($unique_ids) == 0)
		{
			return array();
		}
        
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.13) Gecko/2009073022 Firefox/3.0.13 GTB5");
        curl_setopt ($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt ($ch, CURLOPT_COOKIEJAR, "cookie.txt");
        
        //login
        $url = $this->clinician_url . "/servlet/DxLogin?userid=".$this->username."&PW=".$this->password."&apiLogin=true" . "&target=html/LoginSuccess.html";
        curl_setopt ($ch, CURLOPT_URL, $url);
        $content = curl_exec ($ch);
        
        if(curl_errno($ch))
        {
            return array();
        }
        
        //start download
				$date_from = strtotime($date_from);
				$date_to = strtotime($date_to);
				
				$date_from = $date_from ? __date('m/d/Y', $date_from) : __date('01/01/Y');
				$date_to = $date_to ? __date('m/d/Y', $date_to) : __date('12/31/Y');
				
        $url = $this->clinician_url . "/servlet/DxLogin?userid=".$this->username."&target=jsp/lab/results/FDC.jsp&actionCommand=startDownload&autoPrint=true&batchDownload=".$batchDownload."&reportTypes=".$report_types.'&CreationDateFrom='.$date_from.'&CreationDateTo='.$date_to;
        curl_setopt ($ch, CURLOPT_URL, $url);
        $content = curl_exec ($ch);
        
        if(curl_errno($ch))
        {
            return array();
        }
        
        $results = array();
		
		foreach($unique_ids as $unique_id)
		{
			$results[$unique_id] = array('found' => false, 'content_html' => '', 'content_hl7' => '');
		}
        
        while(true)
        {
            $url = $this->clinician_url . "/servlet/lab.results.fdcServlet?actionCommand=NextFile&batchDownload=".$batchDownload."&fdcuserid=".$this->username."";
            curl_setopt ($ch, CURLOPT_URL, $url);
            $content = curl_exec ($ch);
            
            if(curl_errno($ch))
            {
                return array();
            }
            
            $content = str_replace('<--BEGIN NUMBER OF DOCS>', '', $content);
            $content = str_replace('<--END NUMBER OF DOCS>', '', $content);
            $content_arr = explode(";", $content);
            
            if((int)$content_arr[0] < 0)
            {
                break;
            }
            
            if(in_array(trim($content_arr[2]), $unique_ids))
            {
				$results[trim($content_arr[2])]['found'] = true;
                
                $url = $this->clinician_url . "/servlet/lab.results.fdcServlet?actionCommand=DownloadFile&mmi=true&fdcuserid=".$this->username."";
                curl_setopt ($ch, CURLOPT_URL, $url);
                
				$content = curl_exec ($ch);
                $results[trim($content_arr[2])]['content_html'] = $content;
				
				$content = curl_exec ($ch);
				$results[trim($content_arr[2])]['content_hl7'] = $content;
				
				//mark report as downloaded - so the script will not download it again - to improve speed
				$url = $this->clinician_url . "/servlet/lab.results.fdcServlet?actionCommand=MarkAsDownloaded&fdcuserid=".$this->username."";
                curl_setopt ($ch, CURLOPT_URL, $url);
				curl_exec ($ch);
				
				//if everything found, break
				$all_found = true;
				foreach($results as $result)
				{
					if(!$result['found'])
					{
						$all_found = false;	
					}
				}
				
				if($all_found)
				{
					break;
				}
            } 
        }
        
        return $results;
    }
    
    public function getGuarantorDetails($guarantor)
    {
        if (!$this->isOK())
                return array();

				if (!intval($guarantor)) {
					return array();
				}			
			
        $result = $this->execute("guarantor", "get", array("guarantor" => $guarantor));
        $data = array();
        
        foreach((array)$result['xml']->OBJECT as $key => $value)
        {
            $data[$key] = $this->cleanData($value);
        }
        
        return $data;
    }
	
	public function getPrescriberDetails($prescriber)
	{
		if (!$this->isOK())
        	return array();
		
		$result = $this->execute("caregiver", "get", array("caregiver" => $prescriber));
		
		foreach((array)$result['xml']->OBJECT as $key => $value)
        {
            $data[$key] = $this->cleanData($value);
        }
        
        return $data;
	}
    
    public function getCaregiverDetails($referringcaregiver)
    {
        if (!$this->isOK())
                return array();

        $result = $this->execute("providercaregiver", "search_gui", array("caregiver" => $referringcaregiver, "provider" => $this->facility));
        $data = array();
        
        foreach((array)$result['xml']->OBJECT as $key => $value)
        {
            $data[$key] = $this->cleanData($value);
        }
        
        return $data;
    }
    
    public function getOrganizationDetails()
    {
        if (!$this->isOK())
                return array();

        Cache::set(array('duration' => '+10 years'));
        $organization_details = Cache::read($this->cache_file_prefix.'emdeon_lab_organization_details');
        $data = array();
				
				if ($organization_details && isset($organization_details[$this->facility])) {
					$data = $organization_details[$this->facility];
				} else {
					$result = $this->execute("organization", "get", array("organization" => $this->facility));

					foreach((array)$result['xml']->OBJECT as $key => $value)
					{
							$data[$key] = $this->cleanData($value);
					}
					
					$organization_details[$this->facility] = $data;
					Cache::set(array('duration' => '+10 years'));
					Cache::write($this->cache_file_prefix.'emdeon_lab_organization_details', $organization_details);
				}
        
        return $data;
    }
    
    public function getLabConfiguration($lab)
    {
        if (!$this->isOK())
                return array();

        $lab_settings = array();
				
        Cache::set(array('duration' => '+10 years'));
        $lab_configuration = Cache::read($this->cache_file_prefix.'emdeon_lab_configuration');
				if ($lab_configuration && isset($lab_configuration[$lab])) {
					$lab_settings = $lab_configuration[$lab];
				} else {
					$labcfg_result = $this->execute("labcfg", "search", array("lab" => $lab));

					for($i = 0; $i < count($labcfg_result['xml']->OBJECT); $i++)
					{
							$lab_settings[$this->cleanData($labcfg_result['xml']->OBJECT[$i]->description)] = $this->cleanData($labcfg_result['xml']->OBJECT[$i]->value);
					}
					
					$lab_configuration[$lab] = $lab_settings;
					Cache::set(array('duration' => '+10 years'));
					Cache::write($this->cache_file_prefix.'emdeon_lab_configuration', $lab_configuration);
				}
        
        return $lab_settings;
    }
    
    public function getLabDetails($lab)
    {
        if (!$this->isOK())
                return array();

        $lab_details = $this->execute("lab", "get", array("lab" => $lab));
        $ret = array();
        $ret['lab'] = $this->cleanData((int)$lab_details['xml']->OBJECT[0]->lab);
        $ret['clia_number'] = $this->cleanData($lab_details['xml']->OBJECT[0]->clia_number);
        $ret['discrete_result'] = $this->cleanData($lab_details['xml']->OBJECT[0]->discrete_result);
        $ret['transmission_mode'] = $this->cleanData($lab_details['xml']->OBJECT[0]->transmission_mode);
        $ret['parentlab'] = $this->cleanData((int)$lab_details['xml']->OBJECT[0]->parentlab);
        $ret['can_order_thru'] = $this->cleanData($lab_details['xml']->OBJECT[0]->can_order_thru);
        $ret['zip'] = $this->cleanData($lab_details['xml']->OBJECT[0]->zip);
        $ret['state'] = $this->cleanData($lab_details['xml']->OBJECT[0]->state);
        $ret['phone_number'] = $this->cleanData($lab_details['xml']->OBJECT[0]->phone_number);
        $ret['phone_area_code'] = $this->cleanData($lab_details['xml']->OBJECT[0]->phone_area_code);
        $ret['lab_name'] = $this->cleanData($lab_details['xml']->OBJECT[0]->lab_name);
        $ret['lab_code'] = $this->cleanData($lab_details['xml']->OBJECT[0]->lab_code);
        $ret['fed_tax_id'] = $this->cleanData($lab_details['xml']->OBJECT[0]->fed_tax_id);
        $ret['director_name_2'] = $this->cleanData($lab_details['xml']->OBJECT[0]->director_name_2);
        $ret['director_name_1'] = $this->cleanData($lab_details['xml']->OBJECT[0]->director_name_1);
        $ret['city'] = $this->cleanData($lab_details['xml']->OBJECT[0]->city);
        $ret['alternate_name'] = $this->cleanData($lab_details['xml']->OBJECT[0]->alternate_name);
        $ret['address_2'] = $this->cleanData($lab_details['xml']->OBJECT[0]->address_2);
        $ret['address_1'] = $this->cleanData($lab_details['xml']->OBJECT[0]->address_1);
        
        return $ret;
    }
    
    public function getCaregivers()
    {
        if (!$this->isOK())
                return array();

        $result = $this->execute("providercaregiver", "search_provcg_and_cgpref", array("organization" => $this->facility));
        $caregivers = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $current_caregiver = $result['xml']->OBJECT[$i];
            foreach((array)$current_caregiver as $key => $value)
            {
                $data[$key] = $this->cleanData($value);
            }
            
            $caregivers[] = $data;
        }
        
        return $caregivers;
    }
    
    public function getClients($compact = false)
    {
        if (!$this->isOK())
                return array();

        $clients = array();
        
        $labs = $this->getLabs();
        
        foreach($labs as $lab)
        {
            $current_clients = $this->getLabClients($lab['lab']);
            
            foreach($current_clients as $client)
            {
                if($compact)
                {
                    $clients[] = $client['id_value'];
                }
                else
                {
                    $clients[$client['id_value']] = $lab;
                }
            }
        }
        
        return $clients;
    }
    
    public function getLabClients($lab)
    {
        if (!$this->isOK())
                return array();

        Cache::set(array('duration' => '+10 years'));
        $cached_lab_clients = Cache::read($this->cache_file_prefix.'emdeon_lab_client_list');
        
        $clients = array();
        
        if(!isset($cached_lab_clients[$lab]))
        {
            $result = $this->execute("clientid", "search_orglab", array("provider" => $this->facility, 'lab' => $lab));
            $clients = array();
            
            for($i = 0; $i < count($result['xml']->OBJECT); $i++)
            {
                $data = array();
                $current_client = $result['xml']->OBJECT[$i];
                foreach((array)$current_client as $key => $value)
                {
                    $data[$key] = $this->cleanData($value);
                }
                
                $clients[] = $data;
            }
            
            $cached_lab_clients[$lab] = $clients;
            
            Cache::set(array('duration' => '+10 years'));
            Cache::write($this->cache_file_prefix.'emdeon_lab_client_list', $cached_lab_clients);
        }
        else
        {
            $clients = $cached_lab_clients[$lab];
        }
        
        return $clients;
    }
    
    public function getLabs()
    {
        if (!$this->isOK())
                return array();

        Cache::set(array('duration' => '+10 years'));
        $cached_labs = Cache::read($this->cache_file_prefix.'emdeon_lab_list');
        
        $valid_lab = array();
        
        if(!$cached_labs)
        {
            $lab_list = $this->execute("organizationlab", "search", array("organization" => $this->facility));
            
            for($i = 0; $i < count(@$lab_list['xml']->OBJECT); $i++)
            {
                $current_lab = $this->getLabDetails((int)$lab_list['xml']->OBJECT[$i]->lab);
                
                if($current_lab['can_order_thru'] == 'y' || $current_lab['can_order_thru'] == 'Y')
                {
                    $valid_lab[] = $current_lab;
                }
            }
            
            Cache::set(array('duration' => '+10 years'));
            Cache::write($this->cache_file_prefix.'emdeon_lab_list', $valid_lab);
        }
        else
        {
            $valid_lab = $cached_labs;
        }
        
        return $valid_lab;
    }
    
    public function getValidLabs()
    {
        $labs = $this->getLabs();
        
        $valid_labs = array();
        
        foreach($labs as $lab)
        {
            $valid_labs[] = $lab['lab'];
        }
        
        return $valid_labs;
    }
    
    public function getTestCodePreferenceList($lab)
    {
        if (!$this->isOK())
                return array();

        $result = $this->execute("orgpreference", "search_orderable", array("lab" => $lab, "organization" => $this->facility));
        $test_codes = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['split_code'] = $this->cleanData($result['xml']->OBJECT[$i]->split_code);
            $data['has_aoe'] = $this->cleanData($result['xml']->OBJECT[$i]->has_aoe);
            $data['sequence'] = $this->cleanData($result['xml']->OBJECT[$i]->sequence);
            $data['specimen'] = $this->cleanData($result['xml']->OBJECT[$i]->specimen);
            $data['special_test_flag'] = $this->cleanData($result['xml']->OBJECT[$i]->special_test_flag);
            $data['exclusive_flag'] = $this->cleanData($result['xml']->OBJECT[$i]->exclusive_flag);
            $data['estimated_cost'] = $this->cleanData($result['xml']->OBJECT[$i]->estimated_cost);
            $data['category'] = $this->cleanData($result['xml']->OBJECT[$i]->category);
            $data['ownerid'] = $this->cleanData($result['xml']->OBJECT[$i]->ownerid);
            $data['clearance'] = $this->cleanData($result['xml']->OBJECT[$i]->clearance);
            $data['orderable_type'] = $this->cleanData($result['xml']->OBJECT[$i]->orderable_type);
            $data['orderable'] = $this->cleanData((int)$result['xml']->OBJECT[$i]->orderable);
            $data['ord_expiration_date'] = $this->cleanData($result['xml']->OBJECT[$i]->ord_expiration_date);
            $data['ord_effective_date'] = $this->cleanData($result['xml']->OBJECT[$i]->ord_effective_date);
            $data['description'] = $this->cleanData($result['xml']->OBJECT[$i]->description);
            $data['preferred_object'] = $this->cleanData($result['xml']->OBJECT[$i]->preferred_object);
            $data['preferred_code'] = $this->cleanData($result['xml']->OBJECT[$i]->preferred_code);
            $data['preference_type'] = $this->cleanData($result['xml']->OBJECT[$i]->preference_type);
            $data['orgpreference'] = $this->cleanData($result['xml']->OBJECT[$i]->orgpreference);
            $data['organization'] = $this->cleanData($result['xml']->OBJECT[$i]->organization);
            $data['descriptive_note'] = $this->cleanData($result['xml']->OBJECT[$i]->descriptive_note);
            
            $test_codes[] = $data;
            
        }
        
        return $test_codes;
    }
    
    public function getPersonByMRN($mrn)
    {
        if (!$this->isOK())
                return array();

	    $object_param = array();
        $object_param['hsi_value'] = $mrn;
        $object_param['organization'] = $this->facility;

        $personhsi_result = $this->execute("personhsi", "search_hsi", $object_param);

        if(isset($personhsi_result['xml']->OBJECT) && count($personhsi_result['xml']->OBJECT) > 0)
        {
            return $this->cleanData($personhsi_result['xml']->OBJECT[0]->person);
        }
        else
        {
            false;
        }
    }
    
    public function getPersonHsi($hsi_value)
    {
        if (!$this->isOK())
                return '';

        $personhsi_result = $this->execute("personhsi", "search", array("hsi_value" => $hsi_value));
        
        if(count($personhsi_result['xml']->OBJECT) > 0)
        {
            return $this->cleanData($personhsi_result['xml']->OBJECT[0]->personhsi);
        }
        else
        {
            return '';
        }
    }
	
	/**
    * Retrieve specific guarantor
    * 
    * @param int $guarantor Guarantor Identifier
    * @return array Array guarantor
    */
    public function getSingleGuarantor($guarantor)
    {
        if (!$this->isOK())
                return array();

        $data = array();
				
				if (!intval($guarantor)) {
					return array();
				}
				
				
        $data['guarantor'] = $guarantor;
        $result = $this->execute("guarantor", "get", $data);
        $guarantors = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['suffix'] = $this->cleanData($result['xml']->OBJECT[$i]->suffix);
            $data['date'] = $this->cleanData($result['xml']->OBJECT[$i]->date);
            $data['middle_name'] = $this->cleanData($result['xml']->OBJECT[$i]->middle_name);
            $data['last_name'] = $this->cleanData($result['xml']->OBJECT[$i]->last_name);
            $data['first_name'] = $this->cleanData($result['xml']->OBJECT[$i]->first_name);
            $data['zip'] = $this->cleanData($result['xml']->OBJECT[$i]->zip);
            $data['work_phone_ext'] = $this->cleanData($result['xml']->OBJECT[$i]->work_phone_ext);
            $data['work_phone'] = $this->cleanData($result['xml']->OBJECT[$i]->work_phone);
            $data['state'] = $this->cleanData($result['xml']->OBJECT[$i]->state);
            $data['ssn'] = $this->cleanData($result['xml']->OBJECT[$i]->ssn);
            $data['spouse_name'] = $this->cleanData($result['xml']->OBJECT[$i]->spouse_name);
            $data['relationship'] = $this->cleanData($result['xml']->OBJECT[$i]->relationship);
            $data['person'] = $this->cleanData($result['xml']->OBJECT[$i]->person);
            $data['home_phone'] = $this->cleanData($result['xml']->OBJECT[$i]->home_phone);
            $data['guarantor_sex'] = $this->cleanData($result['xml']->OBJECT[$i]->guarantor_sex);
            $data['guarantor_type'] = $this->cleanData($result['xml']->OBJECT[$i]->guarantor_type);
            $data['guarantor'] = $this->cleanData($result['xml']->OBJECT[$i]->guarantor);
            $data['employment_status'] = $this->cleanData($result['xml']->OBJECT[$i]->employment_status);
            $data['employer_zip'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_zip);
            $data['employer_state'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_state);
            $data['employer_phone'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_phone);
            $data['employer_name'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_name);
            $data['employer_city'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_city);
            $data['employer_address2'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_address2);
            $data['employer_address1'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_address1);
            $data['employee_id'] = $this->cleanData($result['xml']->OBJECT[$i]->employee_id);
            $data['city'] = $this->cleanData($result['xml']->OBJECT[$i]->city);
            $data['birth_date'] = $this->cleanData($result['xml']->OBJECT[$i]->birth_date);
            $data['alt_work_phone_ext'] = $this->cleanData($result['xml']->OBJECT[$i]->alt_work_phone_ext);
            $data['alt_work_phone'] = $this->cleanData($result['xml']->OBJECT[$i]->alt_work_phone);
            $data['alt_home_phone'] = $this->cleanData($result['xml']->OBJECT[$i]->alt_home_phone);
            $data['address_2'] = $this->cleanData($result['xml']->OBJECT[$i]->address_2);
            $data['address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->address_1);
            
            $guarantors[] = $data;
        }
        
        return $guarantors;
    }
	
	public function getPersonAllergy($person)
	{
		if (!$this->isOK())
        	return array();
			
		$data = array();
		$data['person'] = $person;
		$result = $this->execute("personallergy", "search_gui", $data);
		
		$personallergys = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['adverse_reaction'] = $this->cleanData($result['xml']->OBJECT[$i]->adverse_reaction);
            $data['allergy_id'] = $this->cleanData($result['xml']->OBJECT[$i]->allergy_id);
            $data['allergy_name'] = $this->cleanData($result['xml']->OBJECT[$i]->allergy_name);
            $data['creation_date'] = $this->cleanData($result['xml']->OBJECT[$i]->creation_date);
            $data['description'] = $this->cleanData($result['xml']->OBJECT[$i]->description);
            $data['expiration_date'] = $this->cleanData($result['xml']->OBJECT[$i]->expiration_date);
			$data['type'] = $this->cleanData($result['xml']->OBJECT[$i]->type);
			$data['personallergy'] = $this->cleanData($result['xml']->OBJECT[$i]->personallergy);
			$data['severity'] = $this->cleanData($result['xml']->OBJECT[$i]->severity);
            
            $personallergys[] = $data;
        }
        
        return $personallergys;
	}
	
	public function searchAllergy($name, $type)
	{
		if (!$this->isOK())
        	return array();
		
		$data = array();
		$data['name'] = $name;
		$data['type'] = $type;
        $result = $this->execute("allergy", "search", $data);
		
		$allergies = array();
		
		for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['id'] = $this->cleanData($result['xml']->OBJECT[$i]->id);
            $data['name'] = $this->cleanData($result['xml']->OBJECT[$i]->name);
			$data['type'] = $type;
            
            $allergies[] = $data;
        }
		
		return $allergies;
	}
	
    public function getSingleAllergy($personallergy)
    {
        if (!$this->isOK())
                return array();

        $data = array();
        $data['personallergy'] = $personallergy;
        $result = $this->execute("personallergy", "get", $data);
        $personallergys = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['allergy_name'] = $this->cleanData($result['xml']->OBJECT[$i]->allergy_name);
            $data['creation_date'] = $this->cleanData($result['xml']->OBJECT[$i]->creation_date);
            $data['person'] = $this->cleanData($result['xml']->OBJECT[$i]->person);
            $data['severity'] = $this->cleanData($result['xml']->OBJECT[$i]->severity);
            $data['type'] = $this->cleanData($result['xml']->OBJECT[$i]->type);
            $data['personallergy'] = $this->cleanData($result['xml']->OBJECT[$i]->personallergy);
            
            $personallergys[] = $data;
        }
        
        return $personallergys;
    }
	
    public function getGuarantors($mrn)
    {
        if (!$this->isOK())
                return array();

        $data = array();
        $data['person'] = $this->getPersonByMRN($mrn);
        $result = $this->execute("guarantor", "search", $data);
        $guarantors = array();
        
		if(isset($result['xml']->OBJECT))
		{
			for($i = 0; $i < count($result['xml']->OBJECT); $i++)
			{
				$data = array();
				$data['suffix'] = $this->cleanData($result['xml']->OBJECT[$i]->suffix);
				$data['date'] = $this->cleanData($result['xml']->OBJECT[$i]->date);
				$data['middle_name'] = $this->cleanData($result['xml']->OBJECT[$i]->middle_name);
				$data['last_name'] = $this->cleanData($result['xml']->OBJECT[$i]->last_name);
				$data['first_name'] = $this->cleanData($result['xml']->OBJECT[$i]->first_name);
				$data['zip'] = $this->cleanData($result['xml']->OBJECT[$i]->zip);
				$data['work_phone_ext'] = $this->cleanData($result['xml']->OBJECT[$i]->work_phone_ext);
				$data['work_phone'] = $this->cleanData($result['xml']->OBJECT[$i]->work_phone);
				$data['state'] = $this->cleanData($result['xml']->OBJECT[$i]->state);
				$data['ssn'] = $this->cleanData($result['xml']->OBJECT[$i]->ssn);
				$data['spouse_name'] = $this->cleanData($result['xml']->OBJECT[$i]->spouse_name);
				$data['relationship'] = $this->cleanData($result['xml']->OBJECT[$i]->relationship);
				$data['person'] = $this->cleanData($result['xml']->OBJECT[$i]->person);
				$data['home_phone'] = $this->cleanData($result['xml']->OBJECT[$i]->home_phone);
				$data['guarantor_sex'] = $this->cleanData($result['xml']->OBJECT[$i]->guarantor_sex);
				$data['guarantor_type'] = $this->cleanData($result['xml']->OBJECT[$i]->guarantor_type);
				$data['guarantor'] = $this->cleanData($result['xml']->OBJECT[$i]->guarantor);
				$data['employment_status'] = $this->cleanData($result['xml']->OBJECT[$i]->employment_status);
				$data['employer_zip'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_zip);
				$data['employer_state'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_state);
				$data['employer_phone'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_phone);
				$data['employer_name'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_name);
				$data['employer_city'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_city);
				$data['employer_address2'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_address2);
				$data['employer_address1'] = $this->cleanData($result['xml']->OBJECT[$i]->employer_address1);
				$data['employee_id'] = $this->cleanData($result['xml']->OBJECT[$i]->employee_id);
				$data['city'] = $this->cleanData($result['xml']->OBJECT[$i]->city);
				$data['birth_date'] = $this->cleanData($result['xml']->OBJECT[$i]->birth_date);
				$data['alt_work_phone_ext'] = $this->cleanData($result['xml']->OBJECT[$i]->alt_work_phone_ext);
				$data['alt_work_phone'] = $this->cleanData($result['xml']->OBJECT[$i]->alt_work_phone);
				$data['alt_home_phone'] = $this->cleanData($result['xml']->OBJECT[$i]->alt_home_phone);
				$data['address_2'] = $this->cleanData($result['xml']->OBJECT[$i]->address_2);
				$data['address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->address_1);
				
				$guarantors[] = $data;
			}
		}
        
        return $guarantors;
    }
    
    public function searchInsurance($search_options)
    {
        if (!$this->isOK())
                return array();

        $ret = array();
        
        switch($search_options['type'])
        {
            case "self":
            {
                $object_param = array();
                $object_param['organization'] = $this->facility;
                $object_param['name'] = $search_options['name'];
                $object_param['address_1'] = $search_options['address'];
                $object_param['city'] = $search_options['city'];
                $object_param['state'] = $search_options['state'];
                $result = $this->execute("isp", "search_gui", $object_param);
            } break;
            case "hsi":
            {
                $object_param = array();
                $object_param['organization'] = $this->facility;
                $object_param['hsi_value'] = $search_options['hsi_value'];
                $result = $this->execute("isp", "search_by_hsi", $object_param);
            } break;
            default:
            {
                $object_param = array();
                $object_param['organization'] = $this->facility;
                $object_param['name'] = $search_options['name'];
                $object_param['address_1'] = $search_options['address'];
                $object_param['city'] = $search_options['city'];
                $object_param['state'] = $search_options['state'];
                $result = $this->execute("isp", "search_gui", $object_param);
                
                $temp_data = array();
                
                for($i = 0; $i < count($result['xml']->OBJECT); $i++)
                {
                    $temp_data[$i]['hsi_value'] = $this->cleanData($result['xml']->OBJECT[$i]->hsi_value);
                    $temp_data[$i]['isp'] = $this->cleanData($result['xml']->OBJECT[$i]->isp);
                    $temp_data[$i]['isphsi'] = $this->cleanData($result['xml']->OBJECT[$i]->isphsi);
                }
        
                $object_param = array();
                $object_param['name'] = $search_options['name'];
                $object_param['address_1'] = $search_options['address'];
                $object_param['city'] = $search_options['city'];
                $object_param['state'] = $search_options['state'];
                $result = $this->execute("isp", "search", $object_param);
            }
        }
        
        if(count($result['xml']->OBJECT) > 0)
        {
            for($i = 0; $i < count($result['xml']->OBJECT); $i++)
            {
                $data = array();
                
                $data['address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->address_1);
                $data['address_2'] = $this->cleanData($result['xml']->OBJECT[$i]->address_2);
                $data['city'] = $this->cleanData($result['xml']->OBJECT[$i]->city);
                $data['country'] = $this->cleanData($result['xml']->OBJECT[$i]->country);
                $data['fax'] = $this->cleanData($result['xml']->OBJECT[$i]->fax);
                $data['fax_ext'] = $this->cleanData($result['xml']->OBJECT[$i]->fax_ext);
                $data['fed_tax_id'] = $this->cleanData($result['xml']->OBJECT[$i]->fed_tax_id);
                $data['hsi_value'] = $this->cleanData($result['xml']->OBJECT[$i]->hsi_value);
                $data['hsilabel'] = $this->cleanData($result['xml']->OBJECT[$i]->hsilabel);
                $data['isp'] = $this->cleanData($result['xml']->OBJECT[$i]->isp);
                $data['isphsi'] = $this->cleanData($result['xml']->OBJECT[$i]->isphsi);
                $data['label_name'] = $this->cleanData($result['xml']->OBJECT[$i]->label_name);
                $data['name'] = $this->cleanData($result['xml']->OBJECT[$i]->name);
                $data['phone'] = $this->cleanData($result['xml']->OBJECT[$i]->phone);
                $data['phone_ext'] = $this->cleanData($result['xml']->OBJECT[$i]->phone_ext);
                $data['state'] = $this->cleanData($result['xml']->OBJECT[$i]->state);
                $data['zip'] = $this->cleanData($result['xml']->OBJECT[$i]->zip);
                
                if($search_options['type'] != 'self' && $search_options['type'] != 'hsi')
                {
                    for($a = 0; $a < count($temp_data); $a++)
                    {
                        if($temp_data[$a]['isp'] == $data['isp'])
                        {
                            $data['hsi_value'] = $temp_data[$a]['hsi_value'];
                            $data['isphsi'] = $temp_data[$a]['isphsi'];
                            break;
                        }
                    }
                }
                
                $ret[] = $data;
            }    
        }
        
        return $ret;
    }
	
	/**
    * Retrieve specific insurance
    * 
    * @param int $insurance Insurance Identifier
    * @return array Array insurance
    */
    public function getSingleInsurance($insurance)
    {
        if (!$this->isOK())
                return array();

        $data = array();
        $data['insurance'] = $insurance;
        $result = $this->execute("insurance", "get", $data);
        
        $insurances = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['cob_priority'] = $this->cleanData($result['xml']->OBJECT[$i]->cob_priority);
            $data['date'] = $this->cleanData($result['xml']->OBJECT[$i]->date);
            $data['ownerid'] = $this->cleanData($result['xml']->OBJECT[$i]->ownerid);
            $data['clearance'] = $this->cleanData($result['xml']->OBJECT[$i]->clearance);
            $data['isphsi'] = $this->cleanData($result['xml']->OBJECT[$i]->isphsi);
            $data['hsi_value'] = $this->cleanData($result['xml']->OBJECT[$i]->hsi_value);
            $data['policy_number'] = $this->cleanData($result['xml']->OBJECT[$i]->policy_number);
            $data['plan_identifier'] = $this->cleanData($result['xml']->OBJECT[$i]->plan_identifier);
            $data['person'] = $this->cleanData($result['xml']->OBJECT[$i]->person);
            $data['patient_rel_to_insured'] = $this->cleanData($result['xml']->OBJECT[$i]->patient_rel_to_insured);
            $data['organization_name'] = $this->cleanData($result['xml']->OBJECT[$i]->organization_name);
            $data['organization'] = $this->cleanData($result['xml']->OBJECT[$i]->organization);
            $data['last_used_date'] = $this->cleanData($result['xml']->OBJECT[$i]->last_used_date);
            $data['isp_name'] = $this->cleanData($result['xml']->OBJECT[$i]->isp_name);
            $data['isp'] = $this->cleanData($result['xml']->OBJECT[$i]->isp);
            $data['insured_zip'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_zip);
            $data['insured_work_phone_number'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_work_phone_number);
            $data['insured_work_phone_ext'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_work_phone_ext);
            $data['insured_work_phone_area_code'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_work_phone_area_code);
            $data['insured_state'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_state);
            $data['insured_ssn'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_ssn);
            $data['insured_sex'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_sex);
            $data['insured_name_suffix'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_name_suffix);
            $data['insured_middle_name'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_middle_name);
            $data['insured_last_name'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_last_name);
            $data['insured_home_phone_number'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_home_phone_number);
            $data['insured_home_phone_area_code'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_home_phone_area_code);
            $data['insured_first_name'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_first_name);
            $data['insured_employment_status'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_employment_status);
            $data['insured_employee_id'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_employee_id);
            $data['insured_empl_zip'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_zip);
            $data['insured_empl_state'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_state);
            $data['insured_empl_name'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_name);
            $data['insured_empl_city'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_city);
            $data['insured_empl_address_2'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_address_2);
            $data['insured_empl_address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_address_1);
            $data['insured_city'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_city);
            $data['insured_birth_date'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_birth_date);
            $data['insured_address_2'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_address_2);
            $data['insured_address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_address_1);
            $data['insurance'] = $this->cleanData($result['xml']->OBJECT[$i]->insurance);
            $data['group_number'] = $this->cleanData($result['xml']->OBJECT[$i]->group_number);
            $data['group_name'] = $this->cleanData($result['xml']->OBJECT[$i]->group_name);
            $data['expiration_date'] = $this->cleanData($result['xml']->OBJECT[$i]->expiration_date);
            $data['effective_date'] = $this->cleanData($result['xml']->OBJECT[$i]->effective_date);
            
            $insurances[] = $data;
        }
        
        return $insurances;
    }
    
    public function getInsurance($mrn)
    {
        if (!$this->isOK())
                return array();

        $data = array();
        $data['organization'] = $this->facility;
        $data['person'] = $this->getPersonByMRN($mrn);
        $result = $this->execute("insurance", "search_gui", $data);
        
        $insurances = array();
		
		if(isset($result['xml']->OBJECT))
		{
			for($i = 0; $i < count($result['xml']->OBJECT); $i++)
			{
				$data = array();
				$data['cob_priority'] = $this->cleanData($result['xml']->OBJECT[$i]->cob_priority);
				$data['date'] = $this->cleanData($result['xml']->OBJECT[$i]->date);
				$data['ownerid'] = $this->cleanData($result['xml']->OBJECT[$i]->ownerid);
				$data['clearance'] = $this->cleanData($result['xml']->OBJECT[$i]->clearance);
				$data['isphsi'] = $this->cleanData($result['xml']->OBJECT[$i]->isphsi);
				$data['hsi_value'] = $this->cleanData($result['xml']->OBJECT[$i]->hsi_value);
				$data['policy_number'] = $this->cleanData($result['xml']->OBJECT[$i]->policy_number);
				$data['plan_identifier'] = $this->cleanData($result['xml']->OBJECT[$i]->plan_identifier);
				$data['person'] = $this->cleanData($result['xml']->OBJECT[$i]->person);
				$data['patient_rel_to_insured'] = $this->cleanData($result['xml']->OBJECT[$i]->patient_rel_to_insured);
				$data['organization_name'] = $this->cleanData($result['xml']->OBJECT[$i]->organization_name);
				$data['organization'] = $this->cleanData($result['xml']->OBJECT[$i]->organization);
				$data['last_used_date'] = $this->cleanData($result['xml']->OBJECT[$i]->last_used_date);
				$data['isp_name'] = $this->cleanData($result['xml']->OBJECT[$i]->isp_name);
				$data['isp'] = $this->cleanData($result['xml']->OBJECT[$i]->isp);
				$data['insured_zip'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_zip);
				$data['insured_work_phone_number'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_work_phone_number);
				$data['insured_work_phone_ext'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_work_phone_ext);
				$data['insured_work_phone_area_code'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_work_phone_area_code);
				$data['insured_state'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_state);
				$data['insured_ssn'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_ssn);
				$data['insured_sex'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_sex);
				$data['insured_name_suffix'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_name_suffix);
				$data['insured_middle_name'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_middle_name);
				$data['insured_last_name'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_last_name);
				$data['insured_home_phone_number'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_home_phone_number);
				$data['insured_home_phone_area_code'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_home_phone_area_code);
				$data['insured_first_name'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_first_name);
				$data['insured_employment_status'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_employment_status);
				$data['insured_employee_id'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_employee_id);
				$data['insured_empl_zip'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_zip);
				$data['insured_empl_state'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_state);
				$data['insured_empl_name'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_name);
				$data['insured_empl_city'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_city);
				$data['insured_empl_address_2'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_address_2);
				$data['insured_empl_address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_empl_address_1);
				$data['insured_city'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_city);
				$data['insured_birth_date'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_birth_date);
				$data['insured_address_2'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_address_2);
				$data['insured_address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->insured_address_1);
				$data['insurance'] = $this->cleanData($result['xml']->OBJECT[$i]->insurance);
				$data['group_number'] = $this->cleanData($result['xml']->OBJECT[$i]->group_number);
				$data['group_name'] = $this->cleanData($result['xml']->OBJECT[$i]->group_name);
				$data['expiration_date'] = $this->cleanData($result['xml']->OBJECT[$i]->expiration_date);
				$data['effective_date'] = $this->cleanData($result['xml']->OBJECT[$i]->effective_date);
				
				$insurances[] = $data;
			}
		}
        
        return $insurances;
    }
    
    public function getOrderDiagnosis($ordertest)
    {
        if (!$this->isOK())
                return array();

        $result = $this->execute("orderdiagnosis", "search_default", array("ordertest" => $ordertest));
        $orderdiagnosis = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['sequence'] = $this->cleanData($result['xml']->OBJECT[$i]->sequence);
            $data['date'] = $this->cleanData($result['xml']->OBJECT[$i]->date);
            $data['ownerid'] = $this->cleanData($result['xml']->OBJECT[$i]->ownerid);
            $data['clearance'] = $this->cleanData($result['xml']->OBJECT[$i]->clearance);
            $data['ordertest'] = $this->cleanData($result['xml']->OBJECT[$i]->ordertest);
            $data['orderdiagnosis'] = $this->cleanData($result['xml']->OBJECT[$i]->orderdiagnosis);
            $data['icd_9_cm_code'] = $this->cleanData($result['xml']->OBJECT[$i]->icd_9_cm_code);
            $data['description'] = $this->cleanData($result['xml']->OBJECT[$i]->description);
            
            $orderdiagnosis[] = $data;
        }
        
        return $orderdiagnosis;
    }
    
    public function getOrderTestAnswer($ordertest)
    {
        if (!$this->isOK())
                return array();

        $result = $this->execute("ordertestanswer", "search_gui", array("ordertest" => $ordertest));
        $ordertestanswer = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $current_ordertestanswer = (array)$result['xml']->OBJECT[$i];
            
            $data = array();
            
            foreach($current_ordertestanswer as $key => $value)
            {
                if($key == 'question_text')
                {
                    if(strlen(trim($value)) == 0)
                    {
                        $value = 'Specimen Source';
                    }
                }
                
                $data[$key] = $value;
            }
            
            $ordertestanswer[] = $data;
        }
        
        return $ordertestanswer;
    }
    
    public function getOrderable($orderable)
    {
        if (!$this->isOK())
                return array();

        $result = $this->execute("orderable", "get", array("orderable" => $orderable));
        $orderable = array();
        
				if (!isset($result['xml']->OBJECT)) {
					return $orderable;
				}
				
        $orderable['lab'] = $this->cleanData($result['xml']->OBJECT->lab);
        $orderable['expiration_date'] = $this->cleanData($result['xml']->OBJECT->expiration_date);
        $orderable['effective_date'] = $this->cleanData($result['xml']->OBJECT->effective_date);
        $orderable['description'] = $this->cleanData($result['xml']->OBJECT->description);
        $orderable['cpp_count'] = $this->cleanData($result['xml']->OBJECT->cpp_count);
        $orderable['split_code'] = $this->cleanData($result['xml']->OBJECT->split_code);
        $orderable['has_aoe'] = $this->cleanData($result['xml']->OBJECT->has_aoe);
        $orderable['estimated_cost'] = $this->cleanData($result['xml']->OBJECT->estimated_cost);
        $orderable['non_fda_flag'] = $this->cleanData($result['xml']->OBJECT->non_fda_flag);
        $orderable['category'] = $this->cleanData($result['xml']->OBJECT->category);
        $orderable['exclusive_flag'] = $this->cleanData($result['xml']->OBJECT->exclusive_flag);
        $orderable['freq_abn'] = $this->cleanData($result['xml']->OBJECT->freq_abn);
        $orderable['clientid'] = $this->cleanData($result['xml']->OBJECT->clientid);
        $orderable['specimen'] = $this->cleanData($result['xml']->OBJECT->specimen);
        $orderable['special_test_flag'] = $this->cleanData($result['xml']->OBJECT->special_test_flag);
        $orderable['organization'] = $this->cleanData($result['xml']->OBJECT->organization);
        $orderable['orderable_type'] = $this->cleanData($result['xml']->OBJECT->orderable_type);
        $orderable['orderable'] = $this->cleanData($result['xml']->OBJECT->orderable);
        $orderable['order_code'] = $this->cleanData($result['xml']->OBJECT->order_code);
        
        return $orderable;
    }
    
    public function getOrderTest($order)
    {
        if (!$this->isOK())
                return array();

        $result = $this->execute("ordertest", "search", array("order" => $order));
        $ordertest = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['placer_order_number'] = $this->cleanData($result['xml']->OBJECT[$i]->placer_order_number);
            $data['date'] = $this->cleanData($result['xml']->OBJECT[$i]->date);
            $data['ownerid'] = $this->cleanData($result['xml']->OBJECT[$i]->ownerid);
            $data['clearance'] = $this->cleanData($result['xml']->OBJECT[$i]->clearance);
            $data['ordertest'] = $this->cleanData($result['xml']->OBJECT[$i]->ordertest);
            $data['orderable'] = $this->cleanData($result['xml']->OBJECT[$i]->orderable);
            $data['order'] = $this->cleanData($result['xml']->OBJECT[$i]->order);
            $data['lcp_fda_flag'] = $this->cleanData($result['xml']->OBJECT[$i]->lcp_fda_flag);
            
            $data['orderables'] = $this->getOrderable($data['orderable']);
            $data['orderdiagnosis'] = $this->getOrderDiagnosis($data['ordertest']);
            $data['ordertestanswer'] = $this->getOrderTestAnswer($data['ordertest']);
            
            $ordertest[] = $data;
        }
        
        return $ordertest;
    }
    
    public function getIsp($isp, &$data)
    {
        if (!$this->isOK())
                return array();

        $result = $this->execute("isp", "get", array("isp" => $isp));
        
        $isp_data = (array)$result['xml']->OBJECT[0];
        
        foreach($isp_data as $key => $value)
        {
            $data['isp_'.$key] = $this->cleanData($value);
        }
    }
    
    public function getOrderInsurance($order)
    {
        if (!$this->isOK())
                return array();

        $result = $this->execute("orderinsurance", "search_gui", array("order" => $order));
        $orderinsurance = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $insurances = (array)$result['xml']->OBJECT[$i];
            
            $data = array();
            
            foreach($insurances as $key => $value)
            {
                $data[$key] = $this->cleanData($value);
            }
            
            $this->getIsp($data['isp'], $data);
            
            $orderinsurance[] = $data;
        }
        
        return $orderinsurance;
    }
    
    public function getOrderList($mrn, $full = false)
    {
        if (!$this->isOK())
                return array();

        $result = $this->execute("order", "search_by_patient_info", array("person" => $this->getPersonByMRN($mrn), "orderingorganization" => $this->facility));
        $orders = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data = $this->getOrder($this->cleanData($result['xml']->OBJECT[$i]->order));
            
            if($full)
            {
                $data['ordertest'] = $this->getOrderTest($data['order']);
            }
            
            $orders[] = $data;
        }
        
        return $orders;
    }
    
    public function getOrder($order)
    {
        if (!$this->isOK())
                return array();

        $object_param = array();
        $object_param['order'] = $order;
        $data = array();
        
        $result = $this->execute("order", "get", $object_param);
        
        foreach((array)$result['xml']->OBJECT as $key => $value)
        {
            $data[$key] = $this->cleanData($value);
        }
        
        return $data;
    }
    
    public function deleteOrderTest($ordertest)
    {
        $object_param['ordertest'] = $ordertest;
        $this->execute("ordertest", "delete", $object_param);
    }
    
    public function deleteOrderInsurance($orderinsurance)
    {
        $object_param['orderinsurance'] = $orderinsurance;
        $this->execute("orderinsurance", "delete", $object_param);
    }
    
    public function deleteOrder($order)
    {
        $object_param['order'] = $order;
        $this->execute("order", "delete", $object_param);
    }
    
    public function transmitOrder($order)
    {
        $object_param = array();
        $object_param['order_status'] = 'R';
        $object_param['order'] = $order;
        $this->execute("order", "update", $object_param);
    }
    
    public function getGroupABN($order, $insurances, $ordertest)
    {
        if (!$this->isOK())
                return array();

        $ret = "N";
        
        $fda_failed = false;
        $freq_failed = false;
        $lcp_failed = false;
        
        if($insurances && in_array('orderdiagnosis', $ordertest) && is_array($ordertest['orderdiagnosis']) )
        {
            foreach($ordertest['orderdiagnosis'] as $orderdiagnosis)
            {
                $current_abn = $this->getABN($order['lab'], $insurances[0]['isp'], $ordertest['orderable'], $orderdiagnosis['icd_9_cm_code']);
                
                if($current_abn['fda_failed'])
                {
                    $fda_failed = true;
                }
                
                if($current_abn['lcp_failed'])
                {
                    $lcp_failed = true;
                }
                
                if($current_abn['freq_failed'])
                {
                    $freq_failed = true;
                }
            }
        }
        
        if($lcp_failed)
        {
            $ret = "L";
        }
        
        if($fda_failed)
        {
            $ret = "F";
        }
        
        if($freq_failed)
        {
            $ret = "Q";
        }
        
        if($lcp_failed && $freq_failed)
        {
            $ret = "A";
        }
        
        if($fda_failed && $freq_failed)
        {
            $ret = "B";
        }
        
        return $ret;
    }
    
    public function getABNDescription($abn)
    {
        $ret = "";
        
        switch($abn)
        {
            case "L": $ret = "Failed LCP"; break;
            case "F": $ret = "Failed FDA"; break;
            case "Q": $ret = "Failed Freq"; break;
            case "A": $ret = "Failed LCP and Freq"; break;
            case "B": $ret = "Failed FDA and Freq"; break;
            default: $ret = "Passed All Tests";
        }
        
        return $ret;
    }
    
    public function getABNInfo($order, $insurances)
    {
        if (!$this->isOK())
                return array();

        $ret = array();
        $ret['failed'] = false;
        $abn = array();
        
        foreach($order['ordertest'] as $ordertest)
        {
            $data = array();
            $data['payer'] = $insurances[0]['payer'];
            $data['order_code'] = $ordertest['order_code'];
            $data['description'] = $ordertest['description'];
            $data['abn'] = $this->getGroupABN($order, $insurances, $ordertest);
            $data['abn_description'] = $this->getABNDescription($data['abn']);
            
            $abn[] = $data;
        }
        
        $ret['abn'] = $abn;
        
        foreach($abn as $abn_item)
        {
            if($abn_item['abn'] != 'N')
            {
                $ret['failed'] = true;
            }
        }
        
        return $ret;
    }
    
    public function getABN($lab, $isp, $orderable, $icd_9_cm_code)
    {
        if (!$this->isOK())
                return array();

        $fda_failed = false;
        $freq_failed = false;
        $lcp_failed = false;
        
        $data = array();
        
        $result = $this->execute("orderable", "search_isp", array(
            "icd_9_cm_code" => $icd_9_cm_code,
            "isp" => $isp,
            "lab" => $lab,
            "orderable" => $orderable,
            "organization" => $this->facility
        ));
        
        foreach((array)$result['xml']->OBJECT as $key => $value)
        {
            $data[$key] = $this->cleanData($value);
        }
        
        if($data['fda_failed'] == 'Y')
        {
            $fda_failed = true;
        }
        
        if($data['lcp_failed'] == 'Y')
        {
            $lcp_failed = true;
        }
        
        if($data['freq_failed'] == 'Y')
        {
            $freq_failed = true;
        }
        
        $ret['fda_failed'] = $fda_failed;
        $ret['lcp_failed'] = $lcp_failed;
        $ret['freq_failed'] = $freq_failed;
        
        return $ret;
    }
    
    public function updateOrder($order, $patient, $guarantor, $insurances)
    {
        if (!$this->isOK())
                return array();

        $object_param = array();
        $object_param['order'] = $order['order_ref']; 
        $object_param['age'] = $patient['age']; 
        $object_param['age_type'] = 'YEARS';
        $object_param['anonymous_flag'] = 'n'; //required
        $object_param['bill_type'] = $order['bill_type']; //'T';  //required
        
           $object_param['collection_datetime'] = '';
        $object_param['expected_coll_datetime'] = '';
        
        if($order['order_type'] == 'Standard')
        {
            $object_param['collection_datetime'] = __date("n/j/Y", strtotime($order['collection_date'])) . ' ' . __date("g:i A", strtotime($order['collection_time'])); //'9/2/2011 7:51 PM';
        }
        else
        {
            $object_param['expected_coll_datetime'] = __date("n/j/Y", strtotime($order['expected_coll_datetime']));
        }
       
        $object_param['fasting_hours'] = $order['fasting_hours']; //'3';
        
        if($order['bill_type'] != 'C')
        {
            if($guarantor)
            {
                $object_param['guarantor'] = $guarantor['guarantor']; //'2504968791';
                $object_param['guarantor_address_1'] = $guarantor['address_1']; //'8990 Donec Road';
                $object_param['guarantor_address_2'] = $guarantor['address_2']; //'';
                $object_param['guarantor_city'] = $guarantor['city']; //'Moreno Valley';
                $object_param['guarantor_first_name'] = $guarantor['first_name']; //'Amelia';
                
                $guarantor_phone = $this->extractPhone($guarantor['home_phone']);
                $object_param['guarantor_home_phone'] = $guarantor_phone['area_code'] . $guarantor_phone['phone']; //'2464734321';
                
                $object_param['guarantor_last_name'] = $guarantor['last_name']; //'Test';
                $object_param['guarantor_middle_name'] = $guarantor['middle_name']; //'C';
                $object_param['guarantor_relationship'] = $guarantor['relationship']; //'18';
                $object_param['guarantor_sex'] = $guarantor['guarantor_sex']; //'F';
                $object_param['guarantor_state'] = $guarantor['state']; //'AZ';
                $object_param['guarantor_suffix'] = '';
                $object_param['guarantor_zip'] = $guarantor['zip']; //'58474';
            }
        }
        
        $object_param['include_in_manifest'] = 'n';  //required
        $object_param['lab'] = $order['lab']; //'1502191';  //required
        $object_param['lab_instruction'] = $order['lab_instruction']; //'Test Instructions';
        $object_param['order_status'] = 'X';  //required
        $object_param['order_type'] = $order['order_type'];
        $object_param['ordering_cg_id'] = $order['ordering_cg_id'];  //required
        $object_param['orderingorganization'] = $this->facility;  //required
        
        $object_param['person'] = $this->getPersonByMRN($patient['mrn']); //'2504968791';
        $object_param['person_address_1'] = $patient['address1']; //'8990 Donec Road';
        $object_param['person_address_2'] = $patient['address2']; //'';
        $object_param['person_city'] = $patient['city']; //'Moreno Valley';
        $object_param['person_dob'] = __date("n/j/Y", strtotime($patient['dob'])); //'6/20/1985';
        $object_param['person_first_name'] = $patient['first_name']; //'Amelia';
        $object_param['person_home_phone_area_code'] = $patient['home_phone_area_code']; //'246';
        $object_param['person_home_phone_number'] = $patient['home_phone_number']; //'4734321';
        $object_param['person_hsi_value'] = $patient['mrn']; //'100001';
        $object_param['person_last_name'] = $patient['last_name']; //'Test';
        $object_param['person_middle_name'] = $patient['middle_name']; //'C';
        $object_param['person_sex'] = $patient['gender']; //'F';
        $object_param['person_ssn'] = str_replace("-", "", $patient['ssn']); //'585745845';
        $object_param['person_state'] = $patient['state']; //'AZ';
        $object_param['person_suffix'] = '';
        $object_param['person_zip'] = $patient['zipcode']; //'58474';
        $object_param['personhsi'] = $this->getPersonHsi($patient['mrn']); //'2504968792';
        
        $object_param['prepaid_amount'] = $order['prepaid_amount']; //'100.00';
        
        $caregivers = $this->getCaregivers();
        
        foreach($caregivers as $caregiver)
        {
            if($order['referringcaregiver'] == $caregiver['caregiver'])
            {
                $object_param['primaryorderingcaregiver'] = '';
                $object_param['ref_cg_fname'] = $caregiver['cg_first_name'];
                $object_param['ref_cg_lname'] = $caregiver['cg_last_name'];
                $object_param['ref_cg_mname'] = $caregiver['cg_middle_name'];
                $object_param['ref_cg_suffix'] = $caregiver['cg_suffix'];
                $object_param['ref_cg_upin'] = $caregiver['cg_upin'];
                $object_param['referring_cg_id'] = '';
                $object_param['referringcaregiver'] = $caregiver['caregiver'];
            }
        }
        
        $object_param['request_date'] = __date("n/j/Y g:i A"); //'9/2/2011  6:49 AM';  //required
        $object_param['stat_flag'] = (isset($order['stat_flag'])?'S':'R');
        $object_param['username'] = $this->username;
        
        $result = $this->execute("order", "update_all", $object_param);
        
        $emdeon_order_ref = $order['order_ref'];
        
        if($order['bill_type'] == 'T')
        {
            if($insurances)
            {
                
                for($i = 0; $i < count($insurances); $i++)
                {
                    switch($insurances[$i]['priority'])
                    {
                        case "Primary":
                        {
                            $insurances[$i]['cob_priority'] = '1';
                        } break;
                        case "Secondary":
                        {
                            $insurances[$i]['cob_priority'] = '2';
                        } break;
                        case "Tertiary":
                        {
                            $insurances[$i]['cob_priority'] = '3';
                        } break;
                        default:
                        {
                            $insurances[$i]['cob_priority'] = '';
                        }
                    }
                }
                    
                foreach($insurances as $insurance)
                {
                    $object_param = array();
                    $object_param['authorization_number'] = '';
                    $object_param['cob_priority'] = $insurance['cob_priority'];
                    $object_param['effective_date'] = __date("n/j/Y", strtotime($insurance['start_date'])); //required
                    $object_param['expiration_date'] = __date("n/j/Y", strtotime($insurance['end_date']));
                    $object_param['group_name'] = $insurance['group_name'];
                    $object_param['group_number'] = $insurance['group_id'];
                    $object_param['insured_address_1'] = $insurance['insured_address_1'];
                    $object_param['insured_address_2'] = $insurance['insured_address_2'];
                    $object_param['insured_birth_date'] = __date("n/j/Y", strtotime($insurance['insured_birth_date']));
                    $object_param['insured_city'] = $insurance['insured_city'];
                    $object_param['insured_empl_address_1'] = '';
                    $object_param['insured_empl_address_2'] = '';
                    $object_param['insured_empl_city'] = '';
                    $object_param['insured_empl_name'] = $insurance['employer_name'];
                    $object_param['insured_empl_state'] = '';
                    $object_param['insured_empl_zip'] = '';
                    $object_param['insured_employee_id'] = $insurance['insured_employee_id'];
                    $object_param['insured_employment_status'] = $insurance['insured_employment_status'];
                    $object_param['insured_first_name'] = $insurance['insured_first_name'];
                    
                    $insured_home_phone = $this->extractPhone($insurance['insured_home_phone_number']);
                    $object_param['insured_home_phone_area_code'] = $insured_home_phone['area_code'];
                    $object_param['insured_home_phone_number'] = $insured_home_phone['phone'];
                    
                    $object_param['insured_last_name'] = $insurance['insured_last_name']; //required
                    $object_param['insured_middle_name'] = $insurance['insured_middle_name'];
                    $object_param['insured_name_suffix'] = $insurance['insured_name_suffix'];
                    $object_param['insured_sex'] = $insurance['insured_sex'];
                    $object_param['insured_ssn'] = str_replace("-", "", $insurance['insured_ssn']);
                    $object_param['insured_state'] = $insurance['insured_state'];
                    
                    $insured_work_phone = $this->extractPhone($insurance['insured_work_phone_number']);
                    $object_param['insured_work_phone_area_code'] = $insured_work_phone['area_code'];
                    $object_param['insured_work_phone_ext'] = '';
                    $object_param['insured_work_phone_number'] = $insured_work_phone['phone'];
                    
                    $object_param['insured_zip'] = $insurance['insured_zip'];
                    $object_param['isp'] = $insurance['isp']; //required
                    $object_param['isphsi'] = $insurance['isphsi'];
                    $object_param['order'] = $emdeon_order_ref; //required
                    $object_param['originalinsurance'] = $insurance['insurance']; //required
                    $object_param['patient_rel_to_insured'] = $insurance['relationship'];
                    $object_param['person'] = $this->getPersonByMRN($patient['mrn']);; //required
                    $object_param['plan_identifier'] = $insurance['plan_identifier'];
                    $object_param['policy_number'] = $insurance['policy_number'];
                    //$object_param['verification_comment'] = $_POST['verification_comment'][$a];
                    //$object_param['verification_date'] = $_POST['verification_date'][$a];
                    //$object_param['verified_by_method'] = $_POST['verified_by_method'][$a];
                    //$object_param['verifiedbyorganization'] = $_POST['verifiedbyorganization'][$a];
                    //$object_param['verifiedbyuser'] = $_POST['verifiedbyuser'][$a];
                    //$object_param['workman_comp_reqd'] = $_POST['workman_comp_reqd'][$a];
                    $insurance_result = $this->execute("orderinsurance", "add", $object_param);
                }
            }
        }
        
        foreach($order['ordertest'] as $ordertest)
        {
            //ortertest
            $object_param = array();
            $object_param['lcp_fda_flag'] = $this->getGroupABN($order, $insurances, $ordertest);
            $object_param['order'] = $emdeon_order_ref;
            $object_param['orderable'] = $ordertest['orderable'];
            $ordertest_result = $this->execute("ordertest", "add", $object_param);
            
            if(count($ordertest_result['xml']->OBJECT) > 0)
            {
                $ordertest_ref = $this->cleanData($ordertest_result['xml']->OBJECT[0]->ordertest);
               
			   	//NOTE: Don't ignore diagnosis submition - this is required by emdeon, otherwise there will be error at other part of Emdeon codes. 
				//Diagnosis is required by Emdeon. Ignoring it will just lead to another error.
			    //if(isset($ordertest['orderdiagnosis']) && is_array($ordertest['orderdiagnosis'])) {
					//orderdiagnosis
					foreach($ordertest['orderdiagnosis'] as $orderdiagnosis)
					{
						$object_param = array();
						$object_param['description'] = $orderdiagnosis['description'];
						$object_param['icd_9_cm_code'] = $orderdiagnosis['icd_9_cm_code'];
						$object_param['ordertest'] = $ordertest_ref;
						$orderdiagnosis_result = $this->execute("orderdiagnosis", "add", $object_param);
					}
            	//}
                //ordertestanswer
                $sequence = 0;
                if(isset($ordertest['ordertestanswer']))
                {
                    foreach($ordertest['ordertestanswer'] as $ordertestanswer)
                    {
                        $sequence++;
                        $object_param = array();
                        $object_param['sequence'] = $sequence;
                        $object_param['answer_text'] = $ordertestanswer['answer_text'];
                        
                        if(strlen($ordertestanswer['orderabletestques']) > 0)
                        {
                            $object_param['orderabletestques'] = $ordertestanswer['orderabletestques'];
                        }
                        
                        $object_param['ordertest'] = $ordertest_ref;
                        $ordertestanswer_result = $this->execute("ordertestanswer", "add", $object_param);
                    }
                }
            }
        }
        
        $object_param = array();
        
        if($order['order_type'] == 'Standard')
        {
            $object_param['order_status'] = 'E';
        }
        else
        {
            $object_param['order_status'] = 'I';
        }
            
        $object_param['order'] = $emdeon_order_ref;
        $update_result = $this->execute("order", "update", $object_param);
        
        return $emdeon_order_ref;
    }
    
    public function saveOrder($order, $patient, $guarantor, $insurances)
    {
        if (!$this->isOK())
                return array();

        $object_param = array();
        $object_param['age'] = $patient['age']; //'26';
        $object_param['age_type'] = 'YEARS';
        $object_param['anonymous_flag'] = 'n'; //required
        $object_param['bed'] = '';
        $object_param['bill_type'] = $order['bill_type']; //'T';  //required
        $object_param['callback_phone_area_code'] = '';
        $object_param['callback_phone_number'] = '';
        //$object_param['document'] = '2505716077';
        
        $object_param['collection_datetime'] = '';
        $object_param['expected_coll_datetime'] = '';
        
        if($order['order_type'] == 'Standard')
        {
            $object_param['collection_datetime'] = __date("n/j/Y", strtotime($order['collection_date'])) . ' ' . __date("g:i A", strtotime($order['collection_time'])); //'9/2/2011 7:51 PM';
        }
        else
        {
            $object_param['expected_coll_datetime'] = __date("n/j/Y", strtotime($order['expected_coll_datetime']));
        }
        
        $object_param['fasting_hours'] = $order['fasting_hours']; //'3';
        $object_param['faxback_phone_area_code'] = '';
        $object_param['faxback_phone_number'] = '';
        
        if($order['bill_type'] != 'C')
        {
            if($guarantor)
            {
                $object_param['guarantor'] = $guarantor['guarantor']; //'2504968791';
                $object_param['guarantor_address_1'] = $guarantor['address_1']; //'8990 Donec Road';
                $object_param['guarantor_address_2'] = $guarantor['address_2']; //'';
                $object_param['guarantor_city'] = $guarantor['city']; //'Moreno Valley';
                $object_param['guarantor_first_name'] = $guarantor['first_name']; //'Amelia';
                
                $guarantor_phone = $this->extractPhone($guarantor['home_phone']);
                $object_param['guarantor_home_phone'] = $guarantor_phone['area_code'] . $guarantor_phone['phone']; //'2464734321';
                
                $object_param['guarantor_last_name'] = $guarantor['last_name']; //'Test';
                $object_param['guarantor_middle_name'] = $guarantor['middle_name']; //'C';
                $object_param['guarantor_relationship'] = $guarantor['relationship']; //'18';
                $object_param['guarantor_sex'] = $guarantor['guarantor_sex']; //'F';
                $object_param['guarantor_state'] = $guarantor['state']; //'AZ';
                $object_param['guarantor_suffix'] = '';
                $object_param['guarantor_zip'] = $guarantor['zip']; //'58474';
            }
        }
        
        //$object_param['hospital_id'] = '';
        $object_param['include_in_manifest'] = 'n';  //required
        //$object_param['is_split'] = 'n';
        $object_param['lab'] = $order['lab']; //'1502191';  //required
        $object_param['lab_instruction'] = $order['lab_instruction']; //'Test Instructions';
        //$object_param['lab_reference'] = '';
        //$object_param['nurse_unit'] = '';
        //$object_param['order'] = '2505716070';
        //$object_param['order_comment'] = '';
        $object_param['order_status'] = 'X';  //required
        $object_param['order_type'] = $order['order_type'];
        $object_param['ordering_cg_id'] = $order['ordering_cg_id'];  //required
        $object_param['orderingorganization'] = $this->facility;  //required
        //$object_param['pan_indicator'] = '';
        //$object_param['parentorder'] = '';
        
        $object_param['person'] = $this->getPersonByMRN($patient['mrn']); //'2504968791';
        $object_param['person_address_1'] = $patient['address1']; //'8990 Donec Road';
        $object_param['person_address_2'] = $patient['address2']; //'';
        $object_param['person_city'] = $patient['city']; //'Moreno Valley';
        $object_param['person_dob'] = __date("n/j/Y", strtotime($patient['dob'])); //'6/20/1985';
        $object_param['person_first_name'] = $patient['first_name']; //'Amelia';
        $object_param['person_home_phone_area_code'] = $patient['home_phone_area_code']; //'246';
        $object_param['person_home_phone_number'] = $patient['home_phone_number']; //'4734321';
        $object_param['person_hsi_value'] = $patient['mrn']; //'100001';
        $object_param['person_last_name'] = $patient['last_name']; //'Test';
        $object_param['person_middle_name'] = $patient['middle_name']; //'C';
        $object_param['person_sex'] = $patient['gender']; //'F';
        $object_param['person_ssn'] = str_replace("-", "", $patient['ssn']); //'585745845';
        $object_param['person_state'] = $patient['state']; //'AZ';
        $object_param['person_suffix'] = '';
        $object_param['person_zip'] = $patient['zipcode']; //'58474';
        $object_param['personhsi'] = $this->getPersonHsi($patient['mrn']); //'2504968792';
        
        //$object_param['phone_result_flag'] = '';
        //$object_param['placer_order_number'] = '16720';
        $object_param['prepaid_amount'] = $order['prepaid_amount']; //'100.00';
        
        $caregivers = $this->getCaregivers();
        
        foreach($caregivers as $caregiver)
        {
            if($order['referringcaregiver'] == $caregiver['caregiver'])
            {
                $object_param['primaryorderingcaregiver'] = '';
                $object_param['ref_cg_fname'] = $caregiver['cg_first_name'];
                $object_param['ref_cg_lname'] = $caregiver['cg_last_name'];
                $object_param['ref_cg_mname'] = $caregiver['cg_middle_name'];
                $object_param['ref_cg_suffix'] = $caregiver['cg_suffix'];
                $object_param['ref_cg_upin'] = $caregiver['cg_upin'];
                $object_param['referring_cg_id'] = '';
                $object_param['referringcaregiver'] = $caregiver['caregiver'];
            }
        }
        
        $object_param['request_date'] = __date("n/j/Y g:i A"); //'9/2/2011  6:49 AM';  //required
        $object_param['room'] = '';
        $object_param['stat_flag'] = (isset($order['stat_flag'])?'S':'R');
        $object_param['transmission_date'] = '';
        $object_param['username'] = $this->username;
        
        $result = $this->execute("order", "add", $object_param);
        
        if(count($result['xml']->OBJECT) > 0)
        {
					
						// New Order was added. Clear Cached order info
						$db_config = ClassRegistry::init('PracticeSetting')->getDataSource()->config;
						$cache_file_prefix = $db_config['host'] . '_' . $db_config['database'] . '_';					
						$cacheKey = $cache_file_prefix . $patient['mrn'] . '_' . 'orders_by_patient';
						Cache::delete($cacheKey);
						$cacheKey = $cache_file_prefix . $patient['mrn'] . '_' . 'orders_by_diagnosis';
						Cache::delete($cacheKey);					
					
            $emdeon_order_ref = $this->cleanData($result['xml']->OBJECT[0]->order);
            
            if($order['bill_type'] == 'T')
            {
                if($insurances)
                {
                    for($i = 0; $i < count($insurances); $i++)
                    {
                        switch($insurances[$i]['priority'])
                        {
                            case "Primary":
                            {
                                $insurances[$i]['cob_priority'] = '1';
                            } break;
                            case "Secondary":
                            {
                                $insurances[$i]['cob_priority'] = '2';
                            } break;
                            case "Tertiary":
                            {
                                $insurances[$i]['cob_priority'] = '3';
                            } break;
                            default:
                            {
                                $insurances[$i]['cob_priority'] = '';
                            }
                        }
                    }
                    
                    foreach($insurances as $insurance)
                    {
                        $object_param = array();
                        $object_param['authorization_number'] = '';
                        $object_param['cob_priority'] = $insurance['cob_priority'];
                        $object_param['effective_date'] = __date("n/j/Y", strtotime($insurance['start_date'])); //required
                        $object_param['expiration_date'] = __date("n/j/Y", strtotime($insurance['end_date']));
                        $object_param['group_name'] = $insurance['group_name'];
                        $object_param['group_number'] = $insurance['group_id'];
                        $object_param['insured_address_1'] = $insurance['insured_address_1'];
                        $object_param['insured_address_2'] = $insurance['insured_address_2'];
                        $object_param['insured_birth_date'] = __date("n/j/Y", strtotime($insurance['insured_birth_date']));
                        $object_param['insured_city'] = $insurance['insured_city'];
                        $object_param['insured_empl_address_1'] = '';
                        $object_param['insured_empl_address_2'] = '';
                        $object_param['insured_empl_city'] = '';
                        $object_param['insured_empl_name'] = $insurance['employer_name'];
                        $object_param['insured_empl_state'] = '';
                        $object_param['insured_empl_zip'] = '';
                        $object_param['insured_employee_id'] = $insurance['insured_employee_id'];
                        $object_param['insured_employment_status'] = $insurance['insured_employment_status'];
                        $object_param['insured_first_name'] = $insurance['insured_first_name'];
                        
                        $insured_home_phone = $this->extractPhone($insurance['insured_home_phone_number']);
                        $object_param['insured_home_phone_area_code'] = $insured_home_phone['area_code'];
                        $object_param['insured_home_phone_number'] = $insured_home_phone['phone'];
                        
                        $object_param['insured_last_name'] = $insurance['insured_last_name']; //required
                        $object_param['insured_middle_name'] = $insurance['insured_middle_name'];
                        $object_param['insured_name_suffix'] = $insurance['insured_name_suffix'];
                        $object_param['insured_sex'] = $insurance['insured_sex'];
                        $object_param['insured_ssn'] = str_replace("-", "", $insurance['insured_ssn']);
                        $object_param['insured_state'] = $insurance['insured_state'];
                        
                        $insured_work_phone = $this->extractPhone($insurance['insured_work_phone_number']);
                        $object_param['insured_work_phone_area_code'] = $insured_work_phone['area_code'];
                        $object_param['insured_work_phone_ext'] = '';
                        $object_param['insured_work_phone_number'] = $insured_work_phone['phone'];
                        
                        $object_param['insured_zip'] = $insurance['insured_zip'];
                        $object_param['isp'] = $insurance['isp']; //required
                        $object_param['isphsi'] = $insurance['isphsi'];
                        $object_param['order'] = $emdeon_order_ref; //required
                        $object_param['originalinsurance'] = $insurance['insurance']; //required
                        $object_param['patient_rel_to_insured'] = $insurance['relationship'];
                        $object_param['person'] = $this->getPersonByMRN($patient['mrn']);; //required
                        $object_param['plan_identifier'] = $insurance['plan_identifier'];
                        $object_param['policy_number'] = $insurance['policy_number'];
                        //$object_param['verification_comment'] = $_POST['verification_comment'][$a];
                        //$object_param['verification_date'] = $_POST['verification_date'][$a];
                        //$object_param['verified_by_method'] = $_POST['verified_by_method'][$a];
                        //$object_param['verifiedbyorganization'] = $_POST['verifiedbyorganization'][$a];
                        //$object_param['verifiedbyuser'] = $_POST['verifiedbyuser'][$a];
                        //$object_param['workman_comp_reqd'] = $_POST['workman_comp_reqd'][$a];
                        $insurance_result = $this->execute("orderinsurance", "add", $object_param);
                    }
                }
            }
            
            foreach($order['ordertest'] as $ordertest)
            {
                //ortertest
                $object_param = array();
                $object_param['lcp_fda_flag'] = $this->getGroupABN($order, $insurances, $ordertest);
                $object_param['order'] = $emdeon_order_ref;
                $object_param['orderable'] = $ordertest['orderable'];
                $ordertest_result = $this->execute("ordertest", "add", $object_param);
                
                if(count($ordertest_result['xml']->OBJECT) > 0)
                {
                    $ordertest_ref = $this->cleanData($ordertest_result['xml']->OBJECT[0]->ordertest);
                    
					//NOTE: Don't ignore diagnosis submition - this is required by emdeon, otherwise there will be error at other part of Emdeon codes. 
					//Diagnosis is required by Emdeon. Ignoring it will just lead to another error.
                    //if(isset($ordertest['orderdiagnosis']) && is_array($ordertest['orderdiagnosis'])) {
						//orderdiagnosis
						foreach($ordertest['orderdiagnosis'] as $orderdiagnosis)
						{
							$object_param = array();
							$object_param['description'] = $orderdiagnosis['description'];
							$object_param['icd_9_cm_code'] = $orderdiagnosis['icd_9_cm_code'];
							$object_param['ordertest'] = $ordertest_ref;
							$orderdiagnosis_result = $this->execute("orderdiagnosis", "add", $object_param);
						}
                    //}
                    //ordertestanswer
                    $sequence = 0;
                    if(isset($ordertest['ordertestanswer']))
                    {
                        foreach($ordertest['ordertestanswer'] as $ordertestanswer)
                        {
                            $sequence++;
                            $object_param = array();
                            $object_param['sequence'] = $sequence;
                            $object_param['answer_text'] = $ordertestanswer['answer_text'];
                            
                            if(strlen($ordertestanswer['orderabletestques']) > 0)
                            {
                                $object_param['orderabletestques'] = $ordertestanswer['orderabletestques'];
                            }
                            
                            $object_param['ordertest'] = $ordertest_ref;
                            $ordertestanswer_result = $this->execute("ordertestanswer", "add", $object_param);
                        }
                    }
                }
            }
            
            $object_param = array();
            
            if($order['order_type'] == 'Standard')
            {
                $object_param['order_status'] = 'E';
            }
            else
            {
                $object_param['order_status'] = 'I';
            }
            
            $object_param['order'] = $emdeon_order_ref;
            $update_result = $this->execute("order", "update", $object_param);
            
            return $emdeon_order_ref;
        }
        else
        {
            return false;
        }
    }
    
    public function activateOrder($order)
    {
        $object_param = array();
        $object_param['order_type'] = 'Standard';
        $object_param['collection_datetime'] = __date("n/j/Y") . ' ' . __date("g:i A");
        $object_param['order_status'] = 'E';
        $object_param['order'] = $order;
        $update_result = $this->execute("order", "update", $object_param);
        return true;
    }
    
    public function getTestQuestion()
    {
        $object_param = array();
        $object_param['lab'] = '1502191';
        //$object_param['orderable'] = $orderable;
        $object_param['control_type'] = 'MultiSelectCheckBox';
        $object_param['sequence'] = '2';
        $result = $this->execute("orderabletestques", "search_gui", $object_param);
        
        debug($result);
        
    }
    
    public function getTestAoe($lab, $orderable)
    {
        if (!$this->isOK())
                return array();

        $object_param = array();
        $object_param['lab'] = $lab;
        $object_param['orderable'] = $orderable;
        $result = $this->execute("orderabletestques", "search_gui", $object_param);
        
        $aoe = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['sequence'] = $this->cleanData($result['xml']->OBJECT[$i]->sequence);
            $data['list_code_desc'] = $this->cleanData($result['xml']->OBJECT[$i]->list_code_desc);
            $data['control_width'] = $this->cleanData($result['xml']->OBJECT[$i]->control_width);
            $data['list_codes'] = $this->cleanData($result['xml']->OBJECT[$i]->list_codes);
            $data['question_text'] = $this->cleanData($result['xml']->OBJECT[$i]->question_text);
            $data['question_code'] = $this->cleanData($result['xml']->OBJECT[$i]->question_code);
            $data['ask_once'] = $this->cleanData($result['xml']->OBJECT[$i]->ask_once);
            $data['orderable'] = $this->cleanData($result['xml']->OBJECT[$i]->orderable);
            $data['orderabletestques'] = $this->cleanData($result['xml']->OBJECT[$i]->orderabletestques);
            $data['test_description'] = $this->cleanData($result['xml']->OBJECT[$i]->test_description);
            $data['question_help_text'] = $this->cleanData($result['xml']->OBJECT[$i]->question_help_text);
            $data['validation_flag'] = $this->cleanData($result['xml']->OBJECT[$i]->validation_flag);
            $data['default_value'] = $this->cleanData($result['xml']->OBJECT[$i]->default_value);
            $data['question_type'] = $this->cleanData($result['xml']->OBJECT[$i]->question_type);
            $data['num_decimals'] = $this->cleanData($result['xml']->OBJECT[$i]->num_decimals);
            $data['control_type'] = $this->cleanData($result['xml']->OBJECT[$i]->control_type);
            $data['range'] = $this->cleanData($result['xml']->OBJECT[$i]->range);
            
            $aoe[] = $data;
        }
        
        return $aoe;
    }
    
    public function getTestDocument($document)
    {
        if (!$this->isOK())
                return array();

        $object_param = array();
        $object_param['document'] = $document;
        $result = $this->execute("document", "get", $object_param);
        
        $data = array();
        
        $data['ownerid'] = $this->cleanData($result['xml']->OBJECT[0]->ownerid);
        $data['clearance'] = $this->cleanData($result['xml']->OBJECT[0]->clearance);
        $data['mime_type'] = $this->cleanData($result['xml']->OBJECT[0]->mime_type);
        $data['storage_date'] = $this->cleanData($result['xml']->OBJECT[0]->storage_date);
        $data['report_date'] = $this->cleanData($result['xml']->OBJECT[0]->report_date);
        $data['body_text'] = nl2br($this->cleanData($result['xml']->OBJECT[0]->body_text));
        
        return $data;
    }
    
    public function searchICD($icd_9_cm_code, $description, $type = "diagnosis")
    {
        if (!$this->isOK())
                return array();

        $description =  "*".str_replace('*', '',$description)."*";
		
		$icd9s = array();
        
        Cache::set(array('duration' => '+10 years'));
        $cache_search_result = Cache::read($this->cache_file_prefix.'emdeon_icd_search');
        
        $cache_found = false;
        $cached_data = array();
        
        if(!$cache_search_result)
        {
            $cache_search_result = array();
        }
        else
        {
            foreach($cache_search_result as $cache_item)
            {
                if($cache_item['icd_9_cm_code'] == $icd_9_cm_code && $cache_item['description'] == $description)
                {
                    $cached_data = $cache_item['data'];
                    $cache_found = true;
                }
            }
        }
        
        if($cache_found && !empty($cached_data))
        {
            $icd9s = $cached_data;
        }
        else
        {
            $result = $this->execute("icd", "search_gui", array("icd_9_cm_code" => $icd_9_cm_code, "description" => $description, "type" => $type));
            
            for($i = 0; $i < count($result['xml']->OBJECT); $i++)
            {
                $data = array();
                $data['description'] = $this->cleanData($result['xml']->OBJECT[$i]->description);
                $data['effective_date'] = $this->cleanData($result['xml']->OBJECT[$i]->effective_date);
                $data['expiration_date'] = strtoupper($this->cleanData($result['xml']->OBJECT[$i]->expiration_date));
                $data['icd_9_cm_code'] = $this->cleanData($result['xml']->OBJECT[$i]->icd_9_cm_code);
                $data['icd_9_cm_prefix'] = $this->cleanData($result['xml']->OBJECT[$i]->icd_9_cm_prefix);
                $data['is_active'] = $this->cleanData($result['xml']->OBJECT[$i]->is_active);
                $data['sub_ind'] = $this->cleanData($result['xml']->OBJECT[$i]->sub_ind);
                $data['type'] = $this->cleanData($result['xml']->OBJECT[$i]->type);
                
                $full_var_arr = array();
                
                foreach($data as $key => $value)
                {
                    $full_var_arr[] = $key.'="'.htmlentities($value, ENT_QUOTES).'"';
                }
                
                $data['all_var'] = implode(" ", $full_var_arr);
                
                $icd9s[] = $data;
            }
            
            $cache_item = array();
            $cache_item['icd_9_cm_code'] = $icd_9_cm_code;
            $cache_item['description'] = $description;
            $cache_item['data'] = $icd9s;
            $cache_search_result[] = $cache_item;
            
            Cache::set(array('duration' => '+10 years'));
            Cache::write($this->cache_file_prefix.'emdeon_icd_search', $cache_search_result);
        }
        
        return $icd9s;
    }
    
    public function searchTest($lab, $order_code, $description)
    {
        if (!$this->isOK())
                return array();

        $test_codes = array();
        
        Cache::set(array('duration' => '+10 years'));
        $cache_search_result = Cache::read($this->cache_file_prefix.'emdeon_test_code_search');
        
        $cache_found = false;
        $cached_data = array();
        
        if(!$cache_search_result)
        {
            $cache_search_result = array();
        }
        else
        {
            foreach($cache_search_result as $cache_item)
            {
                if($cache_item['lab'] == $lab && $cache_item['order_code'] == $order_code && $cache_item['description'] == $description)
                {
                    $cached_data = $cache_item['data'];
                    $cache_found = true;
                }
            }
        }
        
        if($cache_found)
        {
            $test_codes = $cached_data;
        }
        else
        {
            $result = $this->execute("orderable", "search_gui", array("organization" => $this->facility, "lab" => $lab, "order_code" => $order_code, "description" => $description ));
            
            for($i = 0; $i < count($result['xml']->OBJECT); $i++)
            {
                $data = array();
                $data['effective_date'] = $this->cleanData($result['xml']->OBJECT[$i]->effective_date);
                $data['specimen'] = $this->cleanData($result['xml']->OBJECT[$i]->specimen);
                $data['lab'] = $lab;
                $data['has_aoe'] = strtoupper($this->cleanData($result['xml']->OBJECT[$i]->has_aoe));
                $data['fda_approved'] = $this->cleanData($result['xml']->OBJECT[$i]->fda_approved);
                $data['fda_failed'] = $this->cleanData($result['xml']->OBJECT[$i]->fda_failed);
                $data['document'] = $this->cleanData($result['xml']->OBJECT[$i]->document);
                $data['lcp_failed'] = $this->cleanData($result['xml']->OBJECT[$i]->lcp_failed);
                $data['freq_failed'] = $this->cleanData($result['xml']->OBJECT[$i]->freq_failed);
                $data['mime_type'] = $this->cleanData($result['xml']->OBJECT[$i]->mime_type);
                $data['clientid'] = $this->cleanData($result['xml']->OBJECT[$i]->clientid);
                $data['freq_abn'] = $this->cleanData($result['xml']->OBJECT[$i]->freq_abn);
                $data['icd_9_cm_code'] = $this->cleanData($result['xml']->OBJECT[$i]->icd_9_cm_code);
                $data['orderable'] = $this->cleanData($result['xml']->OBJECT[$i]->orderable);
                $data['body_text'] = $this->cleanData($result['xml']->OBJECT[$i]->body_text);
                $data['exclusive_flag'] = $this->cleanData($result['xml']->OBJECT[$i]->exclusive_flag);
                $data['estimated_cost'] = $this->cleanData($result['xml']->OBJECT[$i]->estimated_cost);
                $data['orderable_type'] = $this->cleanData($result['xml']->OBJECT[$i]->orderable_type);
                $data['split_code'] = $this->cleanData($result['xml']->OBJECT[$i]->split_code);
                $data['special_flag'] = $this->cleanData($result['xml']->OBJECT[$i]->special_flag);
                $data['selec_test_flag'] = $this->cleanData($result['xml']->OBJECT[$i]->selec_test_flag);
                $data['special_test_flag'] = $this->cleanData($result['xml']->OBJECT[$i]->special_test_flag);
                $data['organization'] = $this->cleanData($result['xml']->OBJECT[$i]->organization);
                $data['cpp_count'] = $this->cleanData($result['xml']->OBJECT[$i]->cpp_count);
                $data['aoe'] = $this->cleanData($result['xml']->OBJECT[$i]->aoe);
                $data['non_fda_flag'] = $this->cleanData($result['xml']->OBJECT[$i]->non_fda_flag);
                $data['description'] = $this->cleanData($result['xml']->OBJECT[$i]->description);
                $data['order_code'] = $this->cleanData($result['xml']->OBJECT[$i]->order_code);
                $data['category'] = $this->cleanData($result['xml']->OBJECT[$i]->category);
                $data['expiration_date'] = $this->cleanData($result['xml']->OBJECT[$i]->expiration_date);
                
                if($data['has_aoe'] != 'Y')
                {
                    $data['has_aoe'] = 'N';
                }
                
                $full_var_arr = array();
                
                foreach($data as $key => $value)
                {
                    $full_var_arr[] = $key.'="'.htmlentities($value, ENT_QUOTES).'"';
                }
                
                $data['all_var'] = implode(" ", $full_var_arr);
                
                $test_codes[] = $data;
            }
            
            $cache_item = array();
            $cache_item['lab'] = $lab;
            $cache_item['order_code'] = $order_code;
            $cache_item['description'] = $description;
            $cache_item['data'] = $test_codes;
            $cache_search_result[] = $cache_item;
            
            Cache::set(array('duration' => '+10 years'));
            Cache::write($this->cache_file_prefix.'emdeon_test_code_search', $cache_search_result);
        }
        
        return $test_codes;
    }
    
    //Function used for e-Prescribing
    public function get_rx_norm($drug_id)
	{
		$rxnorm = '';
		
		$result = $this->execute("rxnorm", "get_rxcui_for_medid", array("id" => $drug_id));
		
		if(count($result['xml']->OBJECT) > 0)
		{
			$rxnorm = $this->cleanData($result['xml']->OBJECT[0]->rxcui);
		}
		
		return $rxnorm;
	}
	
    public function searchDrug($name)
    {
        if (!$this->isOK())
                return array();

        $name =  "*".str_replace('*', '',$name)."*";
        
        $drugs = array();
        
        Cache::set(array('duration' => '+10 years'));
        $cache_search_result = Cache::read($this->cache_file_prefix.'emdeon_drug_search');
        
        $cache_found = false;
        $cached_data = array();
        
        if(!$cache_search_result)
        {
            $cache_search_result = array();
        }
        else
        {
            foreach($cache_search_result as $cache_item)
            {
                if($cache_item['description'] == $name)
                {
                    $cached_data = $cache_item['data'];
                    $cache_found = true;
                }
            }
        }
        
        if($cache_found)
        {
            $drugs = $cached_data;
        }
        else
        {
            $result = $this->execute("drug", "search", array("name" => $name));

            for($i = 0; $i < count($result['xml']->OBJECT); $i++)
            {
				$data = array();
				
				/*
				$test = array();
				foreach($result['xml']->OBJECT[$i]->children() as $children_name => $child)
				{
					$test[$children_name] = $this->cleanData($result['xml']->OBJECT[$i]->{$children_name});;
				}
				pr($test);
				*/
				
                $data['deacode'] = $this->cleanData($result['xml']->OBJECT[$i]->deacode);
				$data['dose_form'] = $this->cleanData($result['xml']->OBJECT[$i]->dose_form);
				$data['fedlegendcode'] = $this->cleanData($result['xml']->OBJECT[$i]->fedlegendcode);
				$data['generic_code'] = $this->cleanData($result['xml']->OBJECT[$i]->generic_code);
				$data['generic_name'] = $this->cleanData($result['xml']->OBJECT[$i]->generic_name);
				$data['id'] = $this->cleanData($result['xml']->OBJECT[$i]->id);
				$data['medicaldeviceind'] = $this->cleanData($result['xml']->OBJECT[$i]->medicaldeviceind);
                $data['name'] = $this->cleanData($result['xml']->OBJECT[$i]->name);
				$data['nametypecode'] = $this->cleanData($result['xml']->OBJECT[$i]->nametypecode);
                $data['route'] = $this->cleanData($result['xml']->OBJECT[$i]->route);
				$data['uom'] = $this->cleanData($result['xml']->OBJECT[$i]->uom);
                
				
                /*$data['is_active'] = $this->cleanData($result['xml']->OBJECT[$i]->is_active);
                $data['sub_ind'] = $this->cleanData($result['xml']->OBJECT[$i]->sub_ind);
                $data['type'] = $this->cleanData($result['xml']->OBJECT[$i]->type);*/
                
                $full_var_arr = array();
                
                foreach($data as $key => $value)
                {
                    $full_var_arr[] = $key.'="'.htmlentities($value, ENT_QUOTES).'"';
                }
                
                $data['all_var'] = implode(" ", $full_var_arr);
                
                $drugs[] = $data;
            }
            
            $cache_item = array();
            $cache_item['description'] = $name;
            $cache_item['data'] = $drugs;
            $cache_search_result[] = $cache_item;
            
            Cache::set(array('duration' => '+10 years'));
            Cache::write($this->cache_file_prefix.'emdeon_drug_search', $cache_search_result);
        }
        
        return $drugs;
    }
    
    public function searchPharmacy($name, $pharmacy_id, $address_1, $zip, $state, $city, $phone)
    {
        if (!$this->isOK())
                return array();
		
		$original_text = array('walmart');
		$replacement_text = array('wal-mart');
		
		$name = strtolower($name);
		
		$name = str_replace($original_text, $replacement_text, $name);
		
		if(strlen($name) > 0)
		{
			$name =  "*".str_replace('*', '',$name)."*";
		}

                if(strlen($address_1) > 0)
                {
                        $address_1 =  "*".str_replace('*', '',$address_1)."*";
                }

                if(strlen($city) > 0)
                {
                        $city =  "*".str_replace('*', '',$city)."*";
                }
		
		if(strlen($phone) > 0)
		{
			$phone = str_replace(array('(', ')', '-'), '', $phone);
			$phone = "*".str_replace('*', '',$phone)."*";
		}
        
        $pharmacies = array();
        
        Cache::set(array('duration' => '+1 year'));
        $cache_search_result = Cache::read($this->cache_file_prefix.'emdeon_pharmacy_search');
        
        $cache_found = false;
        $cached_data = array();
        
        if(!$cache_search_result)
        {
            $cache_search_result = array();
        }
        else
        {
            foreach($cache_search_result as $cache_item)
            {
                if($cache_item['description'] == $name 
					&& $cache_item['address_1'] == $address_1
					&& $cache_item['zip'] == $zip
					&& $cache_item['state'] == $state
					&& $cache_item['city'] == $city
					&& $cache_item['phone'] == $phone
				)
                {
                    $cached_data = $cache_item['data'];
		    if (sizeof($cache_item['data']) > 0)
                        $cache_found = true;
                }
            }
        }
        
        if($cache_found)
        {
            $pharmacies = $cached_data;
        }
        else
        {
            $result = $this->execute("pharmacy", "search", array("name" => $name, "pharmacy_id" => $pharmacy_id, "address_1" => $address_1, "zip" => $zip, "state" => $state, "city" => $city, "phone" => $phone));
           for($i = 0; $i < count($result['xml']->OBJECT); $i++)
            {
                $data = array();
                $data['name'] = $this->cleanData($result['xml']->OBJECT[$i]->name);
                $data['pharmacy_id'] = $this->cleanData($result['xml']->OBJECT[$i]->pharmacy_id);
                $data['address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->address_1);
                $data['address_2'] = $this->cleanData($result['xml']->OBJECT[$i]->address_2);                    
                $data['zip'] = $this->cleanData($result['xml']->OBJECT[$i]->zip);
                $data['state'] = $this->cleanData($result['xml']->OBJECT[$i]->state);
                $data['city'] = $this->cleanData($result['xml']->OBJECT[$i]->city);
                $data['phone'] = $this->cleanData($result['xml']->OBJECT[$i]->phone);
                $data['is_electronic'] = $this->cleanData($result['xml']->OBJECT[$i]->is_electronic);
                
                $full_var_arr = array();
                
                foreach($data as $key => $value)
                {
                    $full_var_arr[] = $key.'="'.htmlentities($value, ENT_QUOTES).'"';
                }
                
                $data['all_var'] = implode(" ", $full_var_arr);
                
                $pharmacies[] = $data;
            }
            
            $cache_item = array();
            $cache_item['description'] = $name;
			$cache_item['address_1'] = $address_1;
			$cache_item['zip'] = $zip;
			$cache_item['state'] = $state;
			$cache_item['city'] = $city;
			$cache_item['phone'] = $phone;
            $cache_item['data'] = $pharmacies;
            $cache_search_result[] = $cache_item;
            
            Cache::set(array('duration' => '+1 year'));
            Cache::write($this->cache_file_prefix.'emdeon_pharmacy_search', $cache_search_result);
        }
        
        return $pharmacies;
    }
    
    public function getPlan($person)
    {
        if (!$this->isOK())
                return array();

        $object_param = array();
        $object_param['person'] = $person;
        $result = $this->execute("pharminsurance", "find_rx_plan", $object_param);

        $plan_detail = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['alternative_id'] = $this->cleanData($result['xml']->OBJECT[$i]->alternative_id);
            $data['copay_id'] = $this->cleanData($result['xml']->OBJECT[$i]->copay_id);
            $data['coverage_id'] = $this->cleanData($result['xml']->OBJECT[$i]->coverage_id);
            $data['formuid'] = $this->cleanData($result['xml']->OBJECT[$i]->formuid);
            $data['plan_number'] = $this->cleanData($result['xml']->OBJECT[$i]->plan_number);
            $data['senderid'] = $this->cleanData($result['xml']->OBJECT[$i]->senderid);
            $data['plan_name'] = $this->cleanData($result['xml']->OBJECT[$i]->plan_name);
            $data['pharmacy_benefit'] = $this->cleanData($result['xml']->OBJECT[$i]->pharmacy_benefit);
            $data['mail_order_benefit'] = $this->cleanData($result['xml']->OBJECT[$i]->mail_order_benefit);
            
            $plan_detail[] = $data;
        }
        
        return $plan_detail;
    }
    
    public function getPersonDetails($person)
    {
        if (!$this->isOK())
                return array();

        $object_param = array();
        $object_param['organization'] = $this->facility;
        $object_param['person'] = $person;
        $result = $this->execute("person", "get_rx_person", $object_param);

        $person_detail = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
        
            $data['home_phone_number'] = $this->cleanData($result['xml']->OBJECT[$i]->home_phone_number); 
            $data['home_phone_area_code'] = $this->cleanData($result['xml']->OBJECT[$i]->home_phone_area_code); 
            $data['patient_address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->address_1);
            $data['patient_address_2'] = $this->cleanData($result['xml']->OBJECT[$i]->address_2);                                 
            $data['patient_city'] = $this->cleanData($result['xml']->OBJECT[$i]->city); 
            $data['patient_dob'] = $this->cleanData($result['xml']->OBJECT[$i]->birth_date);
            $data['patient_fname'] = $this->cleanData($result['xml']->OBJECT[$i]->first_name); 
            $data['patient_height'] = $this->cleanData($result['xml']->OBJECT[$i]->height);
            $data['patient_hsi_value'] = $this->cleanData($result['xml']->OBJECT[$i]->hsi_value); 
            $data['patient_lname'] = $this->cleanData($result['xml']->OBJECT[$i]->last_name); 
            $data['patient_mname'] = $this->cleanData($result['xml']->OBJECT[$i]->middle_name); 
            $data['patient_sex'] = $this->cleanData($result['xml']->OBJECT[$i]->sex);
            $data['patient_state'] = $this->cleanData($result['xml']->OBJECT[$i]->state); 
            $data['patient_suffix'] = $this->cleanData($result['xml']->OBJECT[$i]->suffix);
            $data['patient_weight'] = $this->cleanData($result['xml']->OBJECT[$i]->weight);
            $data['patient_zip'] = $this->cleanData($result['xml']->OBJECT[$i]->zip);  
        
            $data['allergy_status'] = $this->cleanData($result['xml']->OBJECT[$i]->allergy_status);
            $data['country'] = $this->cleanData($result['xml']->OBJECT[$i]->country);                 
            $data['clearance'] = $this->cleanData($result['xml']->OBJECT[$i]->clearance); 
            $data['favorite_pharmacy'] = $this->cleanData($result['xml']->OBJECT[$i]->favorite_pharmacy); 
            $data['fav_pharm_name'] = $this->cleanData($result['xml']->OBJECT[$i]->fav_pharm_name);             
            $data['name_prefix'] = $this->cleanData($result['xml']->OBJECT[$i]->name_prefix);             
            $data['ssn'] = $this->cleanData($result['xml']->OBJECT[$i]->ssn);                 
            $data['title'] = $this->cleanData($result['xml']->OBJECT[$i]->title);
            $person_detail[] = $data;
        }
        
        return $person_detail;
    }
    
    public function getRxHistory($person)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['organization'] = $this->facility;
        $object_param['person'] = $person;
        $result = $this->execute("rx", "patient_history_report", $object_param);

        $rx_history = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            
            $data['person'] = $this->cleanData($result['xml']->OBJECT[$i]->person); 
            
            $data['daw'] = $this->cleanData($result['xml']->OBJECT[$i]->daw); 
            
            $data['creation_date'] = $this->cleanData($result['xml']->OBJECT[$i]->creation_date); 
            
            $data['days_supply'] = $this->cleanData($result['xml']->OBJECT[$i]->days_supply); 
            $data['quantity'] = $this->cleanData($result['xml']->OBJECT[$i]->quantity);
            $data['refills'] = $this->cleanData($result['xml']->OBJECT[$i]->refills);        
            $data['units_of_measure'] = $this->cleanData($result['xml']->OBJECT[$i]->units_of_measure);
                
            $data['drug_id'] = $this->cleanData($result['xml']->OBJECT[$i]->drug_id); 
            $data['drug_name'] = $this->cleanData($result['xml']->OBJECT[$i]->drug_name); 
            $data['sig'] = $this->cleanData($result['xml']->OBJECT[$i]->sig);        
            $data['icd_9_cm_code'] = $this->cleanData($result['xml']->OBJECT[$i]->icd_9_cm_code);     
            $data['auth_denied_date'] = $this->cleanData($result['xml']->OBJECT[$i]->auth_denied_date);                                 
            
            $data['pharmacy_id'] = $this->cleanData($result['xml']->OBJECT[$i]->pharmacy_id);
            $data['ph_name'] = $this->cleanData($result['xml']->OBJECT[$i]->ph_name);
            $data['ph_address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->ph_address_1);
            $data['ph_city'] = $this->cleanData($result['xml']->OBJECT[$i]->ph_city);
            
            $data['rx'] = $this->cleanData($result['xml']->OBJECT[$i]->rx);
            $data['rx_issue_type'] = $this->cleanData($result['xml']->OBJECT[$i]->rx_issue_type);
            $data['rx_status'] = $this->cleanData($result['xml']->OBJECT[$i]->rx_status);
            
            $data['prescriber_id'] = $this->cleanData($result['xml']->OBJECT[$i]->prescriber);
            $data['prescriber'] = $this->cleanData($result['xml']->OBJECT[$i]->cg_lname).', '.$this->cleanData($result['xml']->OBJECT[$i]->cg_fname);            
             
            $rx_history[] = $data;
        }
        
        return $rx_history;
    }
    
    public function getSingleRx($person, $id)
    {
        if (!$this->isEmdeonRX())
                return array();

        $id = (int)$id;
        $object_param = array();
        $object_param['organization'] = $this->facility;
        $object_param['person'] = $person;
        $result = $this->execute("rx", "patient_history_report", $object_param);

        $rx_history = array();
        
        if(count($result['xml']->OBJECT) > 0)
        {
            $data = array();
            
            $data['denial_reason'] = $this->cleanData($result['xml']->OBJECT[$id]->denial_reason);
            $data['daw'] = $this->cleanData($result['xml']->OBJECT[$id]->daw);
            $data['creation_date'] = $this->cleanData($result['xml']->OBJECT[$id]->creation_date);  
            $data['days_supply'] = $this->cleanData($result['xml']->OBJECT[$id]->days_supply); 
            $data['quantity'] = $this->cleanData($result['xml']->OBJECT[$id]->quantity);
            $data['refills'] = $this->cleanData($result['xml']->OBJECT[$id]->refills);        
            $data['units_of_measure'] = $this->cleanData($result['xml']->OBJECT[$id]->units_of_measure);
                
            $data['drug_id'] = $this->cleanData($result['xml']->OBJECT[$id]->drug_id); 
            $data['drug_name'] = $this->cleanData($result['xml']->OBJECT[$id]->drug_name); 
            $data['sig'] = $this->cleanData($result['xml']->OBJECT[$id]->sig);        
            $data['icd_9_cm_code'] = $this->cleanData($result['xml']->OBJECT[$id]->icd_9_cm_code);     
            $data['auth_denied_date'] = $this->cleanData($result['xml']->OBJECT[$id]->auth_denied_date);                                 
            
            $data['pharmacy_id'] = $this->cleanData($result['xml']->OBJECT[$id]->pharmacy_id);
            $data['ph_name'] = $this->cleanData($result['xml']->OBJECT[$id]->ph_name);
            $data['ph_address_1'] = $this->cleanData($result['xml']->OBJECT[$id]->ph_address_1);
            $data['ph_city'] = $this->cleanData($result['xml']->OBJECT[$id]->ph_city);
            
            $data['rx'] = $this->cleanData($result['xml']->OBJECT[$id]->rx);
            $data['rx_issue_type'] = $this->cleanData($result['xml']->OBJECT[$id]->rx_issue_type);
            $data['rx_status'] = $this->cleanData($result['xml']->OBJECT[$id]->rx_status);
            
            $data['prescriber_id'] = $this->cleanData($result['xml']->OBJECT[$id]->prescriber);
            $data['prescriber'] = $this->cleanData($result['xml']->OBJECT[$id]->cg_lname).', '.$this->cleanData($result['xml']->OBJECT[$id]->cg_fname);            
             
            $rx_history[] = $data;
        }
        
        return $rx_history[0];
    }
    
    public function getSystemCode($code_type)
    {
        if (!$this->isOK())
			return array();
		
		Cache::set(array('duration' => '+10 years'));
        $sytemcodes = Cache::read($this->cache_file_prefix.'sytemcodes');
		
		$sytemcode = array();
		
		if($sytemcodes && isset($sytemcodes[$code_type]))
		{
			$sytemcode = $sytemcodes[$code_type];
		}
		else
		{
			$object_param = array();
			$object_param['code_type'] = $code_type;   
			$result = $this->execute("systemcode", "search", $object_param);   
			
			for($i = 0; $i < count($result['xml']->OBJECT); $i++)
			{
				$data = array();
				$data['code'] = $this->cleanData($result['xml']->OBJECT[$i]->code);
				$data['description'] = $this->cleanData($result['xml']->OBJECT[$i]->description);    
				$sytemcode[] = $data;
			}
			
			$sytemcodes[$code_type] = $sytemcode;
			Cache::set(array('duration' => '+10 years'));
			Cache::write($this->cache_file_prefix.'sytemcodes', $sytemcodes);
		}
        
        return $sytemcode;
    }
    
    public function getPersonProperty($person)
    {
        if (!$this->isOK())
                return array();

        $object_param = array();
        $object_param['person'] = $person;
        $object_param['property'] = 'Weight';
        $object_param['is_latest'] = 'y';
        $result = $this->execute("personproperties", "get_prop_for_person", $object_param);
        
        $person_property = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['value'] = $this->cleanData($result['xml']->OBJECT[$i]->value);
            $data['unit_of_measure'] = $this->cleanData($result['xml']->OBJECT[$i]->unit_of_measure);    
            $person_property[] = $data;
        }
        
        return $person_property;
    }
    
    public function getRx($rx)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['rx'] = $rx;
        $result = $this->execute("rx", "get", $object_param);
        
        $rx= array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['denial_reason'] = $this->cleanData($result['xml']->OBJECT[$i]->denial_reason);
            $data['comments'] = $this->cleanData($result['xml']->OBJECT[$i]->comments);    
            $data['modified_date'] = $this->cleanData($result['xml']->OBJECT[$i]->modified_date);  
            $data['created_by_name'] = $this->cleanData($result['xml']->OBJECT[$i]->created_by_name);   
            $rx[] = $data;
        }
        
        return $rx[0];
    }
    
    public function getRxDrug($rx)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['rx'] = $rx;
        $result = $this->execute("rxdrug", "search", $object_param);
        
        $rxdrug = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['rxdrug'] = $this->cleanData($result['xml']->OBJECT[$i]->rxdrug);
            $rxdrug[] = $data;
        }
        
        return $rxdrug;
    }
    
    public function getPharmacy($pharmacy_id)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['pharmacy_id'] = $pharmacy_id;
        $result = $this->execute("pharmacy", "get", $object_param);
        
        $pharmacy = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['address_1'] = $this->cleanData($result['xml']->OBJECT[$i]->address_1);
            $data['city'] = $this->cleanData($result['xml']->OBJECT[$i]->city);
            $pharmacy[] = $data;
        }
        
        return $pharmacy;
    }
    
    public function getPendingRx()
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['organization'] = $this->facility;
        $object_param['rx_status'] = 'Pending';
        $result = $this->execute("rx", "search_org", $object_param);
        
        $rx = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['rx_status'] = $this->cleanData($result['xml']->OBJECT[$i]->rx_status);
            $data['creation_date'] = $this->cleanData($result['xml']->OBJECT[$i]->creation_date);
            $data['ph_name'] = $this->cleanData($result['xml']->OBJECT[$i]->ph_name);
            $data['prescriber'] = $this->cleanData($result['xml']->OBJECT[$i]->cg_lname.", ".$result['xml']->OBJECT[$i]->cg_fname);
            $data['patient_name'] = $this->cleanData($result['xml']->OBJECT[$i]->patient_lname.", ".$result['xml']->OBJECT[$i]->patient_fname);
            
            $rx[] = $data;
        }
        
        return $rx;
    }
    
    public function executeRx($operation, $patient, $rx_details, &$error_message = NULL)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['auth_denied_date'] = __date("m/d/Y");
        $object_param['comments'] = $rx_details['comments'];
        $object_param['days_supply'] = (float)$rx_details['days_supply'];
        $object_param['organization'] = $this->facility;
        $object_param['patient_address_1'] = $patient['address1'];
        $object_param['patient_address_2'] = $patient['address2'];
        $object_param['patient_city'] = $patient['city'];
        $object_param['patient_dob'] = __date("m/d/Y", strtotime($patient['dob']));
        $object_param['patient_fname'] = $patient['first_name'];
        //$object_param['patient_height'] = $patient['patient_height'];
        $object_param['patient_hsi_value'] = $this->getPersonHsi($patient['mrn']);
        $object_param['patient_lname'] = $patient['last_name'];
        $object_param['patient_mname'] = $patient['middle_name'];
        $object_param['patient_phone'] = $patient['home_phone'];
        $object_param['patient_sex'] = $patient['gender'];
        $object_param['patient_state'] = $patient['state'];
        //$object_param['patient_suffix'] = $patient['patient_suffix'];
        $object_param['patient_weight'] = isset($rx_details['weight'])?$rx_details['weight']:'';
        $object_param['patient_zip'] = $patient['zipcode'];
        $object_param['person'] = $this->getPersonByMRN($patient['mrn']);
        $object_param['pharmacy_id'] = isset($rx_details['pharmacy_id'])?$rx_details['pharmacy_id']:'';
		$rx_details['prescriber'] = isset($rx_details['prescriber'])?$rx_details['prescriber']:'';
        if($rx_details['prescriber'] != '')
        {
            $prescriber_info = explode('|', $rx_details['prescriber']);
            $object_param['prescriber'] = $prescriber_info[0];
        }
		$object_param['prescriber_name'] = isset($rx_details['prescriber_name'])?$rx_details['prescriber_name']:'';
        $object_param['refills'] = (int)$rx_details['refills'];        
        
        $object_param['rx_issue_type'] = isset($rx_details['rx_issue_type'])?$rx_details['rx_issue_type']:'';
        
		$rx_details['supervising_prescriber'] = isset($rx_details['supervising_prescriber'])?$rx_details['supervising_prescriber']:'';
        if($rx_details['supervising_prescriber'] != '')
        {
            $supervising_prescriber_info = explode('|', $rx_details['supervising_prescriber']);
            $object_param['supervising_prescriber'] = $supervising_prescriber_info[0];
        }
        switch($operation)
        {
            case 'issue':
            {
                $object_param['rx_status'] = "Authorized";
                $object_param['rx_type'] = 'New';
                $result = $this->execute("rx", "add", $object_param);
                
                $rx = '';
                
                if(count($result['xml']->OBJECT) > 0)
                {
                    $rx = $this->cleanData($result['xml']->OBJECT[0]->rx);
                    $this->executeRxDrug('add', $rx_details, $rx);
					$this->issueRx($rx, $rx_details['rx_issue_type']);
                }
                return $rx;
            }break;
            
            case 'hold':
            {
                $object_param['rx_status'] = "Pending";
                $object_param['rx_type'] = 'New';
                $result = $this->execute("rx", "add", $object_param);
                
                $rx = '';
                
                if(count($result['xml']->OBJECT) > 0)
                {
                    $rx = $this->cleanData($result['xml']->OBJECT[0]->rx);
                    $this->executeRxDrug('add', $rx_details, $rx);
					$this->issueHoldRx($rx);
                }
                return $rx;
            }break;
            
            case 'authorize':
            {
                $object_param['rx'] = $rx_details['rx'];
                $object_param['rx_status'] = "Active";
                $result = $this->execute("rx", "update", $object_param);                
                $this->executeRxDrug('update', $rx_details, $rx_details['rx']);
            }break;    
            
			case 'issue_freeformrx':
            {
                $object_param['rx_status'] = "Authorized";
                $object_param['rx_type'] = 'FreeForm';
                $result = $this->execute("rx", "add", $object_param);
                
                $rx = '';
                
                if(count($result['xml']->OBJECT) > 0)
                {
                    $rx = $this->cleanData($result['xml']->OBJECT[0]->rx);
                    $this->executeRxDrug('add', $rx_details, $rx);
                    $this->issueRx($rx, 'Print');
                }
                return $rx;
            }break;
			
			case 'issue_reportedrx':
            {
                $object_param['rx_status'] = "Authorized";
                $object_param['rx_type'] = 'Reported';
                $result = $this->execute("rx", "add", $object_param);
                
                $rx = '';
                
                if(count($result['xml']->OBJECT) > 0)
                {
                    $rx = $this->cleanData($result['xml']->OBJECT[0]->rx);
                    $this->executeRxDrug('add', $rx_details, $rx);
                    $this->issueRx($rx, 'Reported');
                }
                return $rx;
            }break;
        }
    }
    
    public function executeRxDrug($operation, $input, $rx)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['rx'] = $rx;
        $object_param['daw'] = isset($input["daw"])?'y':'n';
        $object_param['days_supply'] = (float)$input['days_supply'];
        $object_param['drug_id'] = isset($input['drug_id'])?$input['drug_id']:'';
        $object_param['drug_name'] = $input['drug_name'];
        $object_param['icd_9_cm_code'] = isset($input['icd_9_cm_code'])?$input['icd_9_cm_code']:'';
        $object_param['quantity'] = $input['quantity'];
        $object_param['refills'] = (int)$input['refills'];
        $object_param['sig'] = $input['sig'];
        $object_param['units_of_measure'] = $input['unit_of_measure'];
        switch($operation)
        {
            case 'add':
            {
                $result = $this->execute("rxdrug", "add", $object_param);
            }break;
            case 'update':
            {
                $result = $this->execute("rxdrug", "update", $object_param);
            }break;
        }
    }
    
    public function issueRx($rx, $rx_issue_type)
    {
        if (!$this->isEmdeonRX())
                return array();
		
		$error_message = '';

        $object_param = array();
        $object_param['rx'] = $rx;
        $object_param['rx_issue_type'] = $rx_issue_type;
        $result = $this->execute("rx", "issue", $object_param, $error_message);
		
		return $error_message;
    }    
    
    public function issueHoldRx($rx)
    {
        if (!$this->isEmdeonRX())
                return array();
		
		$error_message = '';

        $object_param = array();
        $object_param['rx'] = $rx;
        $result = $this->execute("rx", "issue_hold", $object_param, $error_message);
		
		return $error_message;
    }
    
    public function checkDosage($drug_data)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['age'] = $drug_data['age'];
        $object_param['dose_type'] = $drug_data['dose_type'];
        $object_param['dose_unit'] = $drug_data['dose_unit'];
        $object_param['frequency'] = $drug_data['frequency'];
        $object_param['single_dose_amount'] = $drug_data['single_dose_amount'];
        $object_param['new_drug_id'] = $drug_data['drug_id'];
        $object_param['new_drug_name'] = $drug_data['drug_name'];
        $result = $this->execute("dur", "dosage_check", $object_param);
        
        $dosage = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['creation_date'] = $this->cleanData($result['xml']->OBJECT[$i]->creation_date);
            $data['daily_dose_message'] = $this->cleanData($result['xml']->OBJECT[$i]->daily_dose_message);
            $data['frequency_message'] = $this->cleanData($result['xml']->OBJECT[$i]->frequency_message);
            $data['max_daily_dose_message'] = $this->cleanData($result['xml']->OBJECT[$i]->max_daily_dose_message);
            $data['duration_message'] = $this->cleanData($result['xml']->OBJECT[$i]->duration_message);
            $dosage[] = $data;
        }
        
        return $dosage;
    }
    
    public function getRxPreference($rxpreference)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['rxpreference'] = $rxpreference;
        $result = $this->execute("rxpreference", "get", $object_param);
        
        $rxpreference= array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['caregiver'] = $this->cleanData($result['xml']->OBJECT[$i]->caregiver);
            $data['dose_type'] = $this->cleanData($result['xml']->OBJECT[$i]->dose_type);
            $data['dose_unit'] = $this->cleanData($result['xml']->OBJECT[$i]->dose_unit);             
            $data['drug_id'] = $this->cleanData($result['xml']->OBJECT[$i]->drug_id);
            $data['drug_name'] = $this->cleanData($result['xml']->OBJECT[$i]->drug_name);    
            $data['frequency'] = $this->cleanData($result['xml']->OBJECT[$i]->frequency);    
            $data['icd_9_cm_code'] = $this->cleanData($result['xml']->OBJECT[$i]->icd_9_cm_code);        
            $data['icd_description'] = $this->cleanData($result['xml']->OBJECT[$i]->icd_description);        
            $data['refills'] = $this->cleanData($result['xml']->OBJECT[$i]->refills);
            $data['rxpreference'] = $this->cleanData($result['xml']->OBJECT[$i]->rxpreference);
            $data['sig'] = $this->cleanData($result['xml']->OBJECT[$i]->sig);
            $data['single_dose_amount'] = $this->cleanData($result['xml']->OBJECT[$i]->single_dose_amount);
            $data['quantity'] = $this->cleanData($result['xml']->OBJECT[$i]->quantity);
            $data['units_of_measure'] = $this->cleanData($result['xml']->OBJECT[$i]->uom);
            $rxpreference[] = $data;
        }
        
        return $rxpreference[0];
    }
    
    public function executeRxPreference($operation, $input, $rxpreference)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['rxpreference'] = $rxpreference;
        
        $object_param['caregiver'] = $input['prescriber_id'];
        $object_param['daw'] = $input["daw"];
        $object_param['drug_id'] = $input['drug_id'];
        $object_param['drug_name'] = $input['drug_name'];
        $object_param['dose_type'] = $input['dose_type'];
        $object_param['dose_unit'] = $input['dose_unit'];
        $object_param['frequency'] = $input['frequency'];
        $object_param['icd_9_cm_code'] = $input['icd_9_cm_code'];
        $object_param['organization'] =  $this->facility;
        $object_param['quantity'] = $input['quantity'];
        $object_param['refills'] = (int)$input['refills'];
        $object_param['sig'] = $input['sig'];
        $object_param['single_dose_amount'] = $input['single_dose_amount'];
        $object_param['uom'] = $input['unit_of_measure'];
        
        switch($operation)
        {
            case 'add':
            {
                $result = $this->execute("rxpreference", "add", $object_param);
                $rxpreference_unique_id = '';
                
                if(count($result['xml']->OBJECT) > 0)
                {
                    $rxpreference_unique_id = $this->cleanData($result['xml']->OBJECT[0]->rxpreference);
                }
                return $rxpreference_unique_id;
                
            }break;
            case 'update':
            {
                $result = $this->execute("rxpreference", "update", $object_param);
                $rxpreference_unique_id = '';
                
                if(count($result['xml']->OBJECT) > 0)
                {
                    $rxpreference_unique_id = $this->cleanData($result['xml']->OBJECT[0]->rxpreference);
                }
                return $rxpreference_unique_id;
            }break;
        }
    }
	
	public function executeFavoritePharmacy($operation, $input, $pharmacy_orgpreference)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
		
		$object_param['orgpreference'] = $pharmacy_orgpreference;
		
        $object_param['organization'] = $this->facility;
        $object_param['preference_type'] = "Pharmacy";
        $object_param['preferred_code'] = $input["pharmacy_id"];
        
        switch($operation)
        {
            case 'add':
            {
                $result = $this->execute("orgpreference", "put", $object_param);
                $orgpreference = '';
                
                if(count($result['xml']->OBJECT) > 0)
                {
                    $orgpreference = $this->cleanData($result['xml']->OBJECT[0]->orgpreference);
                }
                return $orgpreference;
                
            }break;
            case 'update':
            {
                $result = $this->execute("orgpreference", "update", $object_param);
            }break;
        }
    }
	
	public function personallergysearchgui($drug_data)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['person'] = $this->getPersonByMRN($drug_data['mrn']);
   
        $result = $this->execute("personallergy", "search_gui", $object_param);
 
        $dosage = array();
        
        for($i = 0; $i < count($result['xml']->OBJECT); $i++)
        {
            $data = array();
            $data['allergy_id'] = $this->cleanData($result['xml']->OBJECT[$i]->allergy_id);
            $data['allergy_name'] = $this->cleanData($result['xml']->OBJECT[$i]->allergy_name);
            $data['severity'] = $this->cleanData($result['xml']->OBJECT[$i]->severity);
            $data['personallergy'] = $this->cleanData($result['xml']->OBJECT[$i]->personallergy);
            $dosage[] = $data;
        }
        
        return $dosage;
    }
	
	public function DurScreenDrugs($drug_data, $PatientMedicationList_items)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
		$object_param['new_drug_name'] = $drug_data['drug_name'];
		$object_param['new_drug_id'] = $drug_data['drug_id'];
		$object_param['icd9'] = $drug_data['icd_9_cm_code'];
        $object_param['age'] = $drug_data['age'];
		
		$result = $this->executeDUR("dur", "screenDrugs", $object_param, $PatientMedicationList_items);
		if(isset($result))
		{
			$dosage = array();
	
			for($i = 0; $i < count($result['xml']->OBJECT); $i++)
			{
				$data = array();
				$data['drug_index'] = $this->cleanData($result['xml']->OBJECT[$i]->drug_index);
				$data['drug_name'] = $this->cleanData($result['xml']->OBJECT[$i]->drug_name);
				$data['monograph_id'] = $this->cleanData($result['xml']->OBJECT[$i]->monograph_id);
				$data['monograph_type'] = $this->cleanData($result['xml']->OBJECT[$i]->monograph_type);
				$data['reaction'] = $this->cleanData($result['xml']->OBJECT[$i]->reaction);
				$data['severity'] = $this->cleanData($result['xml']->OBJECT[$i]->severity);
	
				$dosage[] = $data;
			}
			return $dosage;
		}
    }

    public function DurScreenAllergies($drug_data, $PatientAllergy_items)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['new_drug_name'] = $drug_data['drug_name'];
        $object_param['new_drug_id'] = $drug_data['drug_id'];
        
		$result = $this->executeDURAllergies("dur", "screenAllergies", $object_param, $PatientAllergy_items);
		
		if(isset($result))
		{
			$dosage = array();
			for($i = 0; $i < count($result['xml']->OBJECT); $i++)
			{
				$data = array();
				$data['allergen'] = $this->cleanData($result['xml']->OBJECT[$i]->allergen);
				$data['monograph_id'] = $this->cleanData($result['xml']->OBJECT[$i]->monograph_id);
				$data['monograph_type'] = $this->cleanData($result['xml']->OBJECT[$i]->monograph_type);
				$data['reaction'] = $this->cleanData($result['xml']->OBJECT[$i]->reaction);
	
				$dosage[] = $data;
			}
			 return $dosage;
		}
    }
	
	public function FormularyCheck($drug_id, $plan_number, $formuid, $coverage_id, $senderid)
    {
        if (!$this->isEmdeonRX())
                return array();

        $object_param = array();
        $object_param['id'] = $drug_id;
        $object_param['plan_number'] = $plan_number;
		$object_param['formuid'] = $formuid;
		$object_param['coverage_id'] = $coverage_id;
		$object_param['senderid'] = $senderid;
 
		$result = $this->execute('drug', 'search_equiv_formulary', $object_param);
		if(isset($result))
		{
			$formulary = array();
			for($i = 0; $i < count($result['xml']->OBJECT); $i++)
			{
				$data = array();
				$data['deacode'] = $this->cleanData($result['xml']->OBJECT[$i]->deacode);
				$data['dose_form'] = $this->cleanData($result['xml']->OBJECT[$i]->dose_form);
				$data['fedlegendcode'] = $this->cleanData($result['xml']->OBJECT[$i]->fedlegendcode);
				$data['id'] = $this->cleanData($result['xml']->OBJECT[$i]->id);
				$data['name'] = $this->cleanData($result['xml']->OBJECT[$i]->name);
				$data['route'] = $this->cleanData($result['xml']->OBJECT[$i]->route);
				$data['generic_name'] = $this->cleanData($result['xml']->OBJECT[$i]->generic_name);
	
				$formulary[] = $data;
			}
			 return $formulary;
		}
       
    }
}

?>
