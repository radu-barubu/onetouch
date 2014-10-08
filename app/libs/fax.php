<?php

/**
 * 
 * fax class functionality
 * @author cj
 *
 */
class fax  {
	
	public static $instance;
	//(972)-767-0057
	
	/**
    * Verify connectivity
    * 
    *
    * @return boolean - true if connection succeeded
    */
    public function checkConnection()
    {
        $practice_settings = ClassRegistry::init('PracticeSetting')->getSettings();
        if(!($practice_settings->faxage_fax_number && $practice_settings->faxage_username && $practice_settings->faxage_company && $practice_settings->faxage_password))
        {
            return false;
        }
        return true;
    }
	
	public $url_httpsfax_test = 'https://www.faxage.com/httpsfax-debug.php';
	public $url_httpsfax = 'https://www.faxage.com/httpsfax.php';
	public $url_cdr =  'https://www.faxage.com/getcdr.php';
	public $url_provision = 'https://www.faxage.com/provision.php';
	
	public static function getInstance()
	{
		if(self::$instance) {
			return self::$instance;
		}
		return self::$instance = new fax;
	}
	
	public function __get($setting)
	{
		$controller = & new Controller;
		
		$controller->loadModel('practiceSetting');

		$settings  = $controller->practiceSetting->getSettings();
		
		if(isset($settings->{$setting})) {
			return $settings->{$setting};
		}
	}
	
	/**
	 * 
	 * get a fax of type plan referral without knowing the  fax_id
	 * @param unknown_type $referral_id
	 */
	public function getFaxByReferralId($referral_id, $draft = false)
	{
		$controller = & new Controller;
		$controller->loadModel('MessagingFax');
		
		$conditions = array(
					'media_type' => 'plan_referral', 
					'media_id' => $referral_id,
		);
		
		if ($draft) {
			$conditions['status'] = 'draft';
		}
		
		$fax = $controller->MessagingFax->row(
			array('conditions'=> $conditions)
		);
		
		return $fax;
	}
	
	/**
	 * 
	 * Used to create an initial fax reference in  the encounter plan referral section
	 * @param unknown_type $plan_referral_id
	 */
	public function createReferralFax($plan_referral_id, $fax_data = array())
	{
		$data = array();
		$data['media_type'] = 'plan_referral';
		$data['status'] = 'draft';
		$data['media_id'] = $plan_referral_id;
		if($fax_data) {
			$data = array_merge($data, $fax_data);
		}
		$fax = $this->create($data);
		
		return $fax;
	}
	
	public function create($fax_data = array())
	{
		$controller = & new AppController();
		$controller->loadModel('MessagingFax');
		
		$controller->MessagingFax->create();
		//save fax
		$controller->MessagingFax->save($fax_data);
		
		$fax_data['fax_id'] = $controller->MessagingFax->getInsertID();
		
		return $fax_data;
	}
	
	private static $statuses = array();
	/**
	 * get status of a fax by supplying a jobid,  if no jobid is supplied, then it will return all faxes statuses
	 * 
	 * this fuction works with sent faxes not with received faxes.
	 * 
	 * @param unknown_type $jobid
	 */
	public function status($jobid = null)
	{
		if(self::$statuses) {
			return self::$statuses;
		}
		$controller = & new Controller();
		
		$controller->loadModel('practiceSetting');
	
		$settings  = $controller->practiceSetting->getSettings();
		
		$to_name = "Test";
		
		$callerid = $settings->faxage_fax_number;
		$username = $settings->faxage_username;
		$company = $settings->faxage_company;
		$password = $settings->faxage_password;
		
		$tagname="OneTouchEMR";
		//$tagname="Testing";
		$tagnumber=$this->getFaxnumber();
		
		$post['username'] = $username;
		$post['company'] = $company;
		$post['password'] = $password;
		$post['callerid'] = $callerid;
		$post['recipname'] = $to_name;
		$post['operation'] = 'status';
		$post['tagname'] = $tagname;
		$post['tagnumber'] = $tagnumber;
		$post['idasc'] = 1; //force order by recvid
		$post['filename'] = 1;
		$post['jobid'] = $jobid;
		
		$response = rtrim(self::fetchRequest($this->url_httpsfax,$post),"\n");
		
		if(!$response) {
			return;
		}
		
		$response = explode("\n", $response);
		
		$new_response = array();
		
		foreach($response as $k => $v) {
			$r = array();
			list($objid, $x_number, $recipname, $faxno, $status, $status_message, $date) = explode("\t", $v);
			
			$r['jobid'] = $objid;
			$r['recipname'] = $recipname;
			$r['x_number'] = $x_number;
			$r['faxno'] = $faxno;
			$r['status'] = $status;
			$r['status_message'] = $status_message;
			$r['date'] = $date;
			$r['data'] = $v;
			
			if($jobid) {
				return $r;
			}
			$new_response[$objid] = $r;
		}
		
		return self::$statuses = $new_response;		
	}
	
	function getLocalFaxNumber()
	{
		$fax = & new fax;
		
		return $fax->faxage_fax_number;
	}
	
	function getLocalFaxNumberTest()
	{
		$fax = & new fax;
		
		return $fax->faxage_test_fax_number;
	}
	
	function getFaxDocument($recvid)
	{
		$controller = & new Controller();
		
		$controller->loadModel('practiceSetting');
	
		$settings  = $controller->practiceSetting->getSettings();
		
		$callerid = $settings->faxage_fax_number;
		$username = $settings->faxage_username;
		$company = $settings->faxage_company;
		$password = $settings->faxage_password;
		
		$tagname="Testing";
		$tagnumber=$this->getFaxnumber();
		
		$post['username'] = $username;
		$post['company'] = $company;
		$post['password'] = $password;
		$post['operation'] = 'getfax';
		$post['faxid'] = $recvid;
		
		$response = rtrim(self::fetchRequest($this->url_httpsfax,$post),"\n");
		
		if(!$response) {
			return;
		}
		
		return $response;
	}
	
	function delete($jobid)
	{
		$controller = & new Controller();
		
		$controller->loadModel('practiceSetting');
	
		$settings  = $controller->practiceSetting->getSettings();
		
		$callerid = $settings->faxage_fax_number;
		$username = $settings->faxage_username;
		$company = $settings->faxage_company;
		$password = $settings->faxage_password;
		
		$tagname="Testing";
		$tagnumber=$this->getFaxnumber();
		
		$post['username'] = $username;
		$post['company'] = $company;
		$post['password'] = $password;
		$post['operation'] = 'delfax';
		$post['faxid'] = $jobid;
		
		$response = self::fetchRequest($this->url_httpsfax, $post);
		
		die("response<pre>".print_r($response,1));
	}
	
	/**
	 * 
	 * Wrapper to send fax
	 */
	function send($to_fax_number, $file)
	{
		//send fax
		$fax = $this->_send($to_fax_number, $file);
		
		if(!$fax) {
			return;
		}
		
		if(isset($fax['jobid'])) {
			//verify that the fax was sent and check status
			$request = $this->status($fax['jobid']);
			
			if($request) {
				$fax['status_message'] = $request['status_message'];
				$fax['status']  = $request['status'];
				$fax['starttime']  = $request['date'];
			}
		}
		$controller = & new AppController();
		$controller->loadModel('MessagingFax');
		
		//save fax
		$controller->MessagingFax->save($fax);
		
		return $fax;
	}

	function updateSent()
	{
		$controller = & new Controller();
		$controller->loadModel('MessagingFax');
		
		$fax = new fax();
		
		$faxes = $fax->status();
		
		$local_faxes = $this->_prepareLocalFaxes($controller->MessagingFax->find('all' , array(
			'conditions' => array('time >'  => time()-(3600*24*30)) //update only faxes that are 30 days old
		)));
		
		foreach($faxes as $k => $v) {
			$local = $v;
			if(isset($local_faxes[$k])) {
				//update local faxes
				$local = $local_faxes[$k];
				
				$local['status'] = $v['status'];
				$local['status_message'] = $v['status_message'];
				$local['starttime'] = $v['date'];
			} else {
				$local['operation'] = 'sendfax';
				$local['time'] = time();
				$controller->MessagingFax->create();
			}
			
			$controller->MessagingFax->save(array('MessagingFax' => $local));
		}
		
		site::setting('faxes_imported', count(array_keys($faxes)));
		
		return $faxes;
	}
	
	/**
	 * 
	 * function that downloads faxes to the local fax incoming
	 */
	function receive()
	{
		$faxes = $this->_receive();
		
		if($faxes) {
			$controller = & new Controller();
			
			$controller->loadModel('MessagingFax');
			
			$local_faxes = $this->_prepareLocalFaxes($controller->MessagingFax->find('all' , array(
				'conditions' => array(
					'operation'=> 'listfax'
				),
				'order' => array('MessagingFax.fax_id ASC'),
			)));
			
			if($local_faxes) {
				
				$recvids = array();
				
				foreach($local_faxes as $k => $v) {
					$recvids[$v['recvid']] = $v['fax_id'];
				}
				
				foreach($faxes as $k => $v) {
					if(!isset($recvids[$v['recvid']])) {
						$v['time']  = time();
						$controller->MessagingFax->create();
						$controller->MessagingFax->save($v);
						unset($faxes[$v['recvid']]);
	
					} else {
						$faxes[$v['recvid']]['fax_id'] = $recvids[$v['recvid']];
					}
				}
			} else {
				
				foreach($faxes as $k => $v) {
					$v['time'] = time();
					$controller->MessagingFax->create();
					$controller->MessagingFax->save($v);
					$faxes[$v['recvid']]['fax_id'] = $controller->MessagingFax->getLastInsertId();
				}
			}
			return $faxes;
		}
	}
	
	function _prepareLocalFaxes($data)
	{
		if(!$data) {
			return;
		}
		
		$faxes = array();
		
		foreach($data as $k => $v) {
			$v = $v['MessagingFax'];
			
			$faxes[$v['fax_id']] = $v;
		}
		
		return $faxes;
	}
	
	/**
	 * 
	 * Send a fax
	 * 
	 * 
	 * will return  a job_id
	 * 
	 * @param mixed $to_fax_number string or  array
	 * @param $file
	 * 
	 * @return  $jobid
	 */
	function _send($to_fax_number , $file )
	{
		if(!$this->checkConnection())
		{
			return false;	
		}
		
		$to_name = "Test";
		
		$controller = & new Controller;
		$controller->loadModel('practiceSetting');
	
		$settings  = $controller->practiceSetting->getSettings();
		
		$callerid = $settings->faxage_fax_number;
		$username = $settings->faxage_username;
		$company = $settings->faxage_company;
		$password = $settings->faxage_password;
		
		if(isset($settings->faxeage_email) && $settings->faxeage_email) {
			$post['em_notify'] = 1;
		}
		
		if($settings->faxage_tagname) {
			$tagname = $settings->faxage_tagname;
		} else {
			$tagname = 'Testing';
		}
		$tagnumber=$this->getFaxnumber();
		
		if(!is_file($file)) {
			die("File: $file does not exist.");
		}
		
		$fdata  = file_get_contents($file);
		
		$b64data = base64_encode($fdata);
		
		$post['username'] = $username;
		$post['company'] = $company;
		$post['password'] = $password;
		$post['callerid'] = $callerid;
		$post['recipname'] = $to_name;
		$post['operation'] = 'sendfax';
		$post['tagname'] = $tagname;
		$post['tagnumber'] = $tagnumber;
		$post['faxfilenames'][0] = $file;
		$post['faxfiledata'][0] = $b64data;
		
		if(!access::isLocalHost()) {
			$url = Router::url(array('controller' => 'messaging', 'action' => 'FaxNotify'), true);
			$post['url_notify'] = $url;
		}
		
		if(is_array($to_fax_number)) {
			
			$post = array_merge($post, $to_fax_number);
		} else {
			$post['faxno'] = $to_fax_number;
		}
		
		$response = self::fetchRequest($this->url_httpsfax, $post);
		
		preg_match("/^(.+)\:(.+)/", $response, $match);
		
		if(isset($match[1]) && strpos($match[1],'ERR0')!==false) {
			$error['id'] = $match[1];
			
			$error['error'] = $match[1].'-'.$match[2];
			
			die(json_encode($error));
		}
		
		if(strpos($response,'JOBID')===false) {
			return;
		}
		preg_match("/[^:]([0-9]+)/", $response, $match);
		$jobid = $match[1];
		
		
		unset($post['faxfilenames']);
		unset($post['faxfiledata']);
		
		$post['filename'] = $file;
		$post['time'] = time();
		$post['priority'] = 'Normal';
 		$post['jobid'] = $jobid;
		
		return $post;
	}
	
	
	function checkForResponseErrors($response)
	{
		if(!$response) {
			return;
		}
		
		preg_match("/^(.+)\:(.+)/", $response, $match);
		
		if(isset($match[1]) && strpos($match[1],'ERR0')!==false) {
			$error['id'] = $match[1];
			
			$error['error'] = $match[1].'-'.$match[2];
			
			die(json_encode($error));
		}
		
	}
	
	/**
	 * 
	 * List faxes
	 * 
	 * $begin_date - pass a full date time stamp, which tells from what date to retrive new faxes
	 */
	public function _receive($begin_date = null)
	{
		if(!$this->checkConnection())
		{
			return false;	
		}
		
		$to_name = "Test";
		
		$tagname="Testing";
		$tagnumber=$this->getFaxnumber();
		
		$controller = & new Controller;
		
		$controller->loadModel('practiceSetting');
		
		$settings  = $controller->practiceSetting->getSettings();
		
		$callerid = $settings->faxage_fax_number;
		$username = $settings->faxage_username;
		$company = $settings->faxage_company;
		$password = $settings->faxage_password;
		
		$post['username'] = $username;
		$post['company'] = $company;
		$post['password'] = $password;
		$post['callerid'] = $callerid;
		$post['recipname'] = $to_name;
		$post['operation'] = 'listfax';
		$post['tagname'] = $tagname;
		$post['tagnumber'] = $tagnumber;
		$post['idasc'] = 1; //force order by recvid
		$post['filename'] = 1;
		
		if($begin_date) {
			$post['begin'] = $begin_date;
		}
		
		$response = rtrim(self::fetchRequest($this->url_httpsfax,$post),"\n");
		
        	$db_config = $controller->practiceSetting->getDataSource()->config;
        	$cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';
		$cache_key=$cache_file_prefix.'fax_https_request';

		if(strpos($response,'ERR') !== false) {
		    // we received an error
		    Cache::set(array('duration' => '+1 day'));
		    Cache::write($cache_key,$response);	
		    return;
		} else {
		    Cache::delete($cache_key);
		}
		
		$new_response = array();
		$response = explode("\n", $response);
		
		if($response) {
			foreach($response as $k => $v) {
				$r = array();
        
        if (empty($v) ) {
          continue;
        }
        
				list($recvid, $date, $sender, $reciver, $filename) = explode("\t", $v);

				// Remove non-numeric character from receiver
				// and caller id fields
				$_receiver = preg_replace('/[^\d]+/', '', $reciver);
				$_callerid = preg_replace('/[^\d]+/', '', $callerid);

				// If the receiver number is not equal to the caller id
				// then  the fax is not for this installation. Skip it.
				if ($_receiver != $_callerid) {
					continue;
				}
				
				$r['recvid'] = $recvid;
				$r['starttime'] = $date;
				$r['receiver'] = $reciver;
				$r['sender'] = $sender;
				$r['filename'] = $filename;
				$r['operation'] = 'listfax';
				$new_response[$recvid] = $r;
			}
		} else {
		 return;
		}
		return $new_response;
	}
		
	function cdr()
	{
		$controller = & new Controller;
		
		$controller->loadModel('practiceSetting');
		$settings  = $controller->practiceSetting->getSettings();
		
		$callerid = $settings->faxage_fax_number;
		$username = $settings->faxage_username;
		$company = $settings->faxage_company;
		$password = $settings->faxage_password;
		
		
		$post['username'] = $username;
		$post['company'] = $company;
		$post['password'] = $password;
		$post['operation'] = 'listfax';
		
		//die(date('Y-m-d h:m:s',time()-(3600*24*90)));
		$post['begin'] = __date('Y-m-d h:m:s',time()-(3600*24*90));
		$post['end'] = __date('Y-m-d h:m:s',time());
		
		$response = rtrim(self::fetchRequest($this->url_cdr,$post),"\n");
		
		die("response<pre>".print_r($response,1));
	}
	
	/**
	* @param array $request
	* @param boolean $debug
	* @param boolean $clean_response
	* @return string
	*/
	public function fetchRequest($url, $post_data)
	{
		$link = curl_init();
		curl_setopt($link, CURLOPT_URL, $url);
		curl_setopt($link, CURLOPT_POSTFIELDS, http_build_query($post_data));
		curl_setopt($link, CURLOPT_VERBOSE, 0);
		curl_setopt($link, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($link, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($link, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($link, CURLOPT_MAXREDIRS, 6);
		curl_setopt($link, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($link, CURLOPT_TIMEOUT, 15); // 60
		$results=curl_exec($link);
		
		if(!$results) {
			return false;
		}

		return $results;
	}
	
	
	function provisions()
	{
		$url = "https://www.faxage.com/provision.php";
		
		$args = $_SERVER['argv'];
		$operation = $args[1];	// Either 'listdids' or 'provdid'
		$didnumber = $args[2];	// 10-digit DID to provision, only used
					// with the 'provdid' operation
		
		$username = "XXX";
		$company = "XXX";
		$password = "XXX";
		
		$post_data = "username=$username&company=$company&password=$password&";
		$post_data .= "operation=$operation&didnumber=$didnumber";
		
		$curl_cmd = "/usr/bin/curl -k -d \"$post_data\" $url 2>/dev/null";
		
		$result = `$curl_cmd`;
		
		$results = explode('\n', $result);
		
		// In the case of listdids, a list of 
		// available DIDs will spit out here
		//
		// In the case of provdid, will either
		// return <DIDNUMBER> provisioned (success)
		// or ERR<ERRNUM>: <SOME DESCRIPTION> (failure)
		// e.g.: '3035551212 provsioned' would be
		// a successful response
		while(list($key, $val) = each($results)) {
		    # Do something useful here
		    echo "$val\n";
		}
	}
	
	function enableDisable()
	{
		$url = "https://www.faxage.com/httpsfax.php";
		
		$args = $_SERVER['argv'];
		$operation = $args[1]; // Either 'enabledid' or 'disabledid'
		$didnumber = $args[2]; // 10-digit DID to enable or disable
		
		$username = "XXXXXX";
		$company = "XXXXXX";
		$password = "XXXXXX";
		
		$post_data = "username=$username&company=$company&password=$password&";
		$post_data .= "operation=$operation&didnumber=$didnumber";
		
		$curl_cmd = "/usr/bin/curl -k -d \"$post_data\" $url 2>/dev/null";
		
		$result = `$curl_cmd`;
		
		$results = explode('\n', $result);
		
		while(list($key, $val) = each($results)) {
		    # Do something useful here
		    echo "$val\n";
		}
	}
	/*Return fax number given in Fax Account Settings of Services*/
	function getFaxnumber()
	{
		$fax_number = ClassRegistry::init('PracticeSetting')->field('faxage_fax_number');
		return $fax_number;
	}
}
