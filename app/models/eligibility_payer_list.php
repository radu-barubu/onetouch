<?php

class EligibilityPayerList extends AppModel 
{
	public $name = 'EligibilityPayerList';
	public $primaryKey = 'payer_list_id';
	public $useTable = 'eligibility_payer_list';

	public function importPayerList()
	{
		$init_url = 'https://access.emdeon.com/PayerLists/';
		$dl_url  = 'https://access.emdeon.com/PayerLists/getRawData.jspx?id=npd&appName=realtime&npi=False&supports5010=False&event_name_id=Eligibility%20and%20Benefits';
		$path = $this->paths['temp']."eligibility_payer_list_".md5(uniqid(rand(), true)).".csv";
	 
		$fp = fopen($path, 'w');
	 
		$ch = curl_init($init_url);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.13) Gecko/2009073022 Firefox/3.0.13 GTB5");
		curl_setopt ($ch, CURLOPT_TIMEOUT, 3000);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_COOKIESESSION, TRUE);
		curl_setopt ($ch, CURLOPT_COOKIEJAR, "cookie.txt");
		$data = curl_exec($ch);

		curl_setopt($ch, CURLOPT_URL, $dl_url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		$data = curl_exec($ch);
	 
		curl_close($ch);
		fclose($fp);
		
		$fieldseparator = ",";
		$lineseparator = "\n";
		$csvfile = $path;
	
		if(!file_exists($csvfile))
		{
			echo "File not found. Make sure you specified the correct path.\n";
			exit;
		}
		
		$file = fopen($csvfile,"r");
		if(!$file)
		{
			echo "Error opening data file.\n";
			exit;
		}
		
		$size = filesize($csvfile);
		if(!$size)
		{
			echo "File is empty.\n";
			exit;
		}
		
		$csvcontent = fread($file,$size);
		fclose($file);
	
		$lines = 0;
		$queries = "";
		$linearray = array();
		
		$this->query('TRUNCATE TABLE eligibility_payer_list;');
	
		foreach(split($lineseparator, $csvcontent) as $line)
		{
			$line = trim($line, " \t");
			
			$line = str_replace("\r", "", $line);
			
			/************************************
			This line escapes the special character. remove it if entries are already escaped in the csv file
			************************************/
			$line = str_replace("'", "\'", $line);
			/*************************************/
			
			$linearray = explode($fieldseparator, $line);
			
			if(trim($linearray[0]) == "" || trim($linearray[0]) == "#" || trim($linearray[6]) != "Eligibility Inquiry and Response")
			{
				continue;
			}
			
			$this->create();
			$this->data['EligibilityPayerList']['change'] = trim($linearray[1]);
			$this->data['EligibilityPayerList']['payer_names'] = trim($linearray[2]);
			$this->data['EligibilityPayerList']['payer_ids'] = trim($linearray[3]);
			$this->data['EligibilityPayerList']['model'] = trim($linearray[4]);
			$this->data['EligibilityPayerList']['lob'] = trim($linearray[5]);
			$this->data['EligibilityPayerList']['trans_type'] = trim($linearray[6]);
			$this->data['EligibilityPayerList']['enroll'] = trim($linearray[7]);
			$this->data['EligibilityPayerList']['npi'] = trim($linearray[8]);
			$this->data['EligibilityPayerList']['link'] = trim($linearray[9]);
			$this->data['EligibilityPayerList']['5010'] = trim($linearray[10]);
			$this->data['EligibilityPayerList']['additional_information'] = trim($linearray[11]);
			$this->save($this->data);
			
			$lines++;
		}
		
		echo "$lines records were inserted.\n";
		@unlink($path);
	}

	public function execute(&$controller, $task)
	{
		switch ($task)
        {
            case "load_autocomplete":
            {
                if (!empty($controller->data))
                {
                    $search_keyword = $controller->data['autocomplete']['keyword'];
                    $search_limit = $controller->data['autocomplete']['limit'];
                    
                    $eligibility_payer_list_items = $controller->EligibilityPayerList->find('all', array(
						'conditions' => array('OR' => array('EligibilityPayerList.payer_ids LIKE ' => '%' . $search_keyword . '%', 'EligibilityPayerList.payer_names LIKE ' => '%' . $search_keyword . '%')),
						'limit' => $search_limit
					));
                    $data_array = array();
                    
                    foreach ($eligibility_payer_list_items as $eligibility_payer_list_item)
                    {
                        $data_array[] = $eligibility_payer_list_item['EligibilityPayerList']['payer_names'] . ' [' . $eligibility_payer_list_item['EligibilityPayerList']['payer_ids'] . ']|' . $eligibility_payer_list_item['EligibilityPayerList']['payer_ids'];
                    }
                    
                    echo implode("\n", $data_array);
                }
                exit();
            }
            break;
        }
	}
}

?>