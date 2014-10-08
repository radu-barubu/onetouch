<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       cake
 * @subpackage    cake.app
 */


App::import('Sanitize');
App::import('Helper', 'Html');
App::import('Helper', 'AutoComplete');

spl_autoload_register('AppController::autoloadFilter');

class AppController extends Controller 
{
	function __construct() 
	{
		parent::__construct();
		if ($this->name == 'CakeError') 
		{
			$this->constructClasses();
			$this->beforeFilter();
		}
	}

	public static function autoloadFilter($class)
	{
		$libs =  APP.'libs'.DS;
		! file_exists($f = sprintf( '%s%s.%s' , $libs , $class , 'php' ) ) OR App::import('Lib', $class , array( 'file' => $f ) );
	}
	
	public function beforeFilter()
	{
		//this is for any custom paths
		
		EMR_Controller::run();
		
		$controller = @$this->params['controller'];
		$action = @$this->params['action'];
		$tab = (isset($this->params['named']['tab'])) ? $this->params['named']['tab'] : "";

		$session_id = (isset($this->params['named']['session_id'])) ? $this->params['named']['session_id'] : "";
		
		if(@$this->params['action'] == 'upload_file')
		{
			$this->Session->id($session_id);
			$this->Session->start();
		}
        
		$this->__loadTheme();
		$this->validateLoginStatus(); 
		
		//Enable output compression
		
		//ini_set('zlib.output_compression', 'On');
		//@ob_start("ob_gzhandler");
		
		UploadSettings::initUploadPath($this);
		
		$user = $this->Session->read('UserAccount');
		if(!access::isAjaxRequest()) 
        {
			$this->checkAccess($controller, $action);
			
			access::checkExpiredPassword($user, & $this);
		}
		
		// Notes session id for current user
		$sid = $this->Session->id();

		// Read any existing info on this user, if any
		$info = Cache::read('user_' . $user['user_id']);

		if ($info && !($controller == 'administration' && $action=='logout')) {
			if ($info['kick'] && isset($info['kick'][$sid])) {
				$this->Session->destroy('user');
				$this->redirect(array('controller' => 'administration', 'action' => 'login'));
			}
		}

		EMR_Account::setUserId( $user['user_id'] );
		EMR_Roles::setCurrentRoleID( $user['role_id'] );
        
		$this->set("page_access", $this->getAccessType());
        
		$this->user_id = $user['user_id'];
		$this->current_session_user = $user;
		$this->set("user_id", $this->user_id);
		$this->set("tutor_mode", @$user['tutor_mode']);

		$this->__setTimeZone();
        
		$this->__loadSettings();
		$this->__loadMessages();
		
		$this->__checkEmergencyAccessExpire();
		$this->__loadTheme();
		$this->__loadGlobalArrays();
				
		$_SESSION['webroot'] = $this->webroot;
		$_SERVER['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT'] : '';
		//determine and set global variable if they are on mobile device - iPad / Android
		$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
		$isDroid = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'Android');
		$isiPhone = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPhone');		
		if($isiPad || $isDroid || $isiPhone)
		{
		  $isMobile=1;
		}
		else
		{
		  $isMobile=0;		
		}
		$this->set("isMobile", $isMobile);
		  
		//determine and set global variable if they are on iPad only (does not consider android)
		$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
		$isiPhone = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPhone');		
		if($isiPad || $isiPhone)
		{
		  $isiPad=1;
		}
		else
		{
		  $isiPad=0;		
		}
		$this->set("isiPad", $isiPad);
		
		//determine and set global variable if they are running an iPad App
		$isiPadApp = isset($_COOKIE["iPad"]);
		//$isiPadApp = true;	// For debugging on a browser
		$this->set("isiPadApp", $isiPadApp);
		
		// Build iPad App info
		if( $isiPadApp )
		{
			// account
			$this->set("account_ipad", $_SERVER['HTTP_HOST']);
			
			// build change_account URL for iPad apps
			$https = isset($_SERVER['HTTPS']) ? 'https' : 'http';
			$this->set("change_account", "$https://ipad.onetouchemr.com/default.php");
		}
	}
	
	public function check_duplicate()
	{
		if(!empty($this->data))
        {
			$this->loadModel($this->data['model']);
			
			if($this->{$this->data['model']}->checkUnique($this->data))
			{
				echo "true";
			}
			else
			{
				echo "false";
			}
		}
		
		exit;
	}
	
	public function __setTimeZone()
	{
		$this->loadModel('UserAccount');
		$user = $this->UserAccount->getCurrentUser($this->user_id);
		$user_location_id = $user['work_location'];
		
		$this->loadModel('PracticeLocation');
		$location = $this->PracticeLocation->getLocationItem($user_location_id);
		$timezone = $location['general_localtime'];
		if(! $timezone )
		{
			$timezone = date_default_timezone_get();
		}
		
		date_default_timezone_set($timezone);
	}
	
	public function __checkEmergencyAccessExpire()
	{
		$this->loadModel('UserAccount');
		$PracticeSetting = $this->Session->read('PracticeSetting');
		$user = $this->Session->read('UserAccount');
		$emergency = $user['emergency'];
		$emergency_date = $user['emergency_date'];
		
		if($emergency == 1)
		{
			$current_timestamp = mktime();
			
			$emergency_date_timestamp = mktime(
											date("h", strtotime($emergency_date)), 
											date("i", strtotime($emergency_date)), 
											date("s", strtotime($emergency_date)), 
											date("n", strtotime($emergency_date)), 
											date("j", strtotime($emergency_date)), 
											date("Y", strtotime($emergency_date)));
			$emergency_date_expire = mktime(
											(int)date("h", strtotime($emergency_date)) + (int)$PracticeSetting['PracticeSetting']['emergency_duration'], 
											date("i", strtotime($emergency_date)), 
											date("s", strtotime($emergency_date)), 
											date("n", strtotime($emergency_date)), 
											date("j", strtotime($emergency_date)), 
											date("Y", strtotime($emergency_date)));
			
			if($current_timestamp >= $emergency_date_expire)
			{
				$data = array();
				$data['UserAccount']['user_id'] = $this->user_id;
				$data['UserAccount']['emergency'] = 0;
				
				$this->UserAccount->save($data);
				$this->Session->write("UserAccount", $this->UserAccount->getCurrentUser($this->user_id));
			}
		}
	}
	
	public function upload_file()
	{
		if (!empty($_FILES)) 
		{
			// while scan and upload check file type are valid or not if there is request of allowed_file_types 
			if(isset($this->data['allowed_file_types'])) {
				$ext = end(explode('.', $_FILES['file_input']['name']));
				$checkExt = stristr($this->data['allowed_file_types'], '.'.$ext.';');
				if($checkExt===false)
					exit('Invalid File Type');
			}
      
      $maxWidth = isset($this->data['max_width']) ? intval($this->data['max_width']) : '' ;
      $maxHeight = isset($this->data['max_height']) ? intval($this->data['max_height']) : '' ;
      if ($maxWidth || $maxHeight) {
        $imgInfo = getimagesize($_FILES['file_input']['tmp_name']);
        
        if ($maxWidth && ($imgInfo[0] > $maxWidth) ) {
          echo '[Error] Uploaded image is too large (over 200x600 pixels)';
          die();
        }
        
        if ($maxHeight && ($imgInfo[1] > $maxHeight) ) {
          echo '[Error] Uploaded image is too large (over 200x600 pixels)';
          die();
        }
        
      }
      
      
      
			$tempFile = $_FILES['file_input']['tmp_name'];
			if($tempFile==""){
			  echo '[Error] File Name cannot be blank';
			  die();
			}
			$targetPath = $this->paths[$this->data['path_index']];
			$targetFile =  str_replace('//','/',$targetPath) . FileHash::getHash($tempFile) . '_' . Sanitize::paranoid($_FILES['file_input']['name'], array('.'));

			move_uploaded_file($tempFile, $targetFile);
			
			echo str_replace($this->paths[$this->data['path_index']], $this->url_abs_paths[$this->data['path_index']], $targetFile);
		}
		
		exit;
	}
	
	public function download_file()
	{
		$demo_id = (isset($this->params['named']['demo_id'])) ? $this->params['named']['demo_id'] : "";
		$items = $this->Demo->find(
				'all', 
				array(
					'conditions' => array('Demo.demo_id' => $demo_id)
				)
		);
		
		$current_item = $items[0];
		
		$file = $current_item['Demo']['filename'];
		$folder = $this->webroot.'app/webroot/documents/examples';
		$targetPath = $_SERVER['DOCUMENT_ROOT'] . $folder . '/';
		$targetFile =  str_replace('//','/',$targetPath) . $file;
		header('Content-Type: application/octet-stream; name="'.$file.'"'); 
		header('Content-Disposition: attachment; filename="'.$file.'"'); 
		header('Accept-Ranges: bytes'); 
		header('Pragma: no-cache'); 
		header('Expires: 0'); 
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
		header('Content-transfer-encoding: binary'); 
		header('Content-length: ' . @filesize($targetFile)); 
		@readfile($targetFile);
		
		exit;
	}
	
	public function __loadSettings()
	{
		$this->loadModel("PracticeSetting");
		$PracticeSetting = $this->PracticeSetting->find('first');
		if ($PracticeSetting)
		{
			$this->Session->write('PracticeSetting', $PracticeSetting);
		}
		
		$this->__global_date_format = $PracticeSetting['PracticeSetting']['general_dateformat'];
		$this->__global_time_format = $PracticeSetting['PracticeSetting']['general_timeformat'];
		$this->set("global_date_format", $this->__global_date_format);
		
		$global_time_format = "h:i:s A";
		
		if($this->__global_time_format == 24)
		{
			$global_time_format = "H:i:s";
		}
		
		$this->set("general_timeformat", $this->__global_time_format);
		$this->set("global_time_format", $global_time_format);
		
		// if administration wants to display an important message for this client on screen
		if($this->Session->check('UserAccount') && $PracticeSetting['PracticeSetting']['administration_messsage'])
		{
		   $this->Session->setFlash($PracticeSetting['PracticeSetting']['administration_messsage']);
		}
		
		//private label setting data. if defined, grab partner logo, data
		if(!empty($PracticeSetting['PracticeSetting']['partner_id']))
		{
			$this->loadModel("PartnerData");
		   	$this->Session->write('PartnerData', $this->PartnerData->grabdata($PracticeSetting['PracticeSetting']['partner_id']));
		} 
	}

	public function __loadMessages()
	{
		$this->loadModel("MessagingMessage");
		$user = $this->Session->read('UserAccount');
		$messages_count = $this->MessagingMessage->find('count', 
			array('conditions' => array(
				"AND" => array(
					'MessagingMessage.recipient_id' => $user['user_id'], 
					'MessagingMessage.status' => array('New'),
					'MessagingMessage.sender_folder' => null,
					'MessagingMessage.inbox' => 1,
					
			))));
		$this->Session->write('messages_count', $messages_count);
        
        //Possible CakePHP Bug
        //$MessagingMessage = $this->MessagingMessage->find('all', array('conditions' => array("AND" => array('MessagingMessage.recipient_id' => $user['user_id'], 'MessagingMessage.status' => array('New', 'Sent')))));
		//$this->Session->write('MessagingMessage', $MessagingMessage);
	}

	public function __loadGlobalArrays()
	{
		$_practiceTypes = array("Allergy &amp; Immunology","Anesthesiology/Pain Management", "Cardiology", "Cardiothoracic Surgery", "Chiropractor", "Dermatology", "Endocrinology", "Family Practice", "General Surgery", "Gastroenterology", "Internal Medicine", "Mental Health", "Neurology", "Neurological Surgery", "Obstetrics/Gynecology", "Ophthalmology/Optometry", "Orthopedic Surgery", "Other", "Pain Management", "Pediatrics", "Physical Medicine and Rehabilitation/Physiatry", "Pediatrics", "Plastic Surgery", "Pulmonology", "Psychiatry", "Rheumatology", "Urgent Care", "Urology", "Vascular Surgery");
		$this->set(compact('_practiceTypes'));
		
		$country_array = array("United States", " Albania", " Algeria", " American Samoa", " Andorra", " Angola", " Anguilla", " Antarctica", " Antigua and Barbuda", " Argentina", " Armenia", " Aruba", " Australia", " Austria", " Azerbaijan", " Bahamas", " Bahrain", " Bangladesh", " Barbados", " Belarus", " Belgium", " Belize", " Benin", " Bermuda", " Bhutan", " Bolivia", " Bosnia and Herzegovina", " Botswana", " Bouvet Island", " Brazil", " British Indian Ocean Territory", " Brunei Darussalam", " Bulgaria", " Burkina Faso", " Burundi", " Canada", " Cambodia", " Cameroon", " Cape Verde", " Cayman Islands", " Central African Republic", " Channel Islands", " Chad", " Chile", " China", " Christmas Island", " Cocos (Keeling) Islands", " Colombia", " Comoros", " Congo", " Congo ,Democratic Republic", " Cook Islands", " Costa Rica", " Croatia", " Cyprus", " Czech Republic", " Denmark", " Djibouti", " Dominica", " Dominican Republic", " East Timor", " Ecuador", " Egypt", " El Salvador", " Equatorial Guinea", " Eritrea", " Estonia", " Ethiopia", " Falkland Islands", " Faroe Islands", " Fiji", " Finland", " France", " French Guiana", " French Polynesia", " French Southern Territories", " Gabon", " Gambia", " Georgia", " Germany", " Ghana", " Gibraltar", " Greece", " Greenland", " Grenada", " Guadeloupe", " Guam", " Guatemala", " Guinea", " Guinea Bissau", " Guyana", " Haiti", " Heard Island and McDonald Islands", " Holy See ,Vatican", " Honduras", " Hong Kong", " Hungary", " Iceland", " India", " Indonesia", " Ireland", " Israel", " Italy", " Jamaica", " Japan", " Jordan", " Kazakhstan", " Kenya", " Kiribati", " Kuwait", " Kyrgyzstan", " Laos", " Latvia", " Lebanon", " Lesotho", " Libya", " Liechtenstein", " Lithuania", " Luxembourg", " Macau", " Macedonia", " Madagascar", " Malawi", " Malaysia", " Maldives", " Mali", " Malta", " Marshall Islands", " Martinique", " Mauritania", " Mauritius", " Mayotte", " Mexico", " Micronesia", " Moldova", " Monaco", " Mongolia", " Montserrat", " Morocco", " Mozambique", " Namibia", " Nauru", " Nepal", " Netherlands", " Netherlands Antilles", " New Caledonia", " New Zealand", " Nicaragua", " Niger", " Nigeria", " Niue", " Norfolk Island", " North Korea", " Northern Mariana Islands", " Norway", " Oman", " Pakistan", " Palau", " Palestinian Territory", " Panama", " Papua New Guinea", " Paraguay", " Peru", " Philippines", " Pitcairn", " Poland", " Portugal", " Puerto Rico", " Qatar", " Reunion", " Romania", " Russian Federation", " Rwanda", " Saint Helena", " Saint Kitts and Nevis Anguilla", " Saint Lucia", " Saint Pierre and Miquelon", " Saint Vincent and The Grenadines", " Samoa", " San Marino", " Sao Tome and Principe", " Saudi Arabia", " Senegal", " Seychelles", " Singapore", " Slovakia", " Slovenia", " Solomon Islands", " Somalia", " South Africa", " South Georgia", " Spain", " Sri Lanka", " Suriname", " Svalbard and Jan Mayen", " Swaziland", " Sweden", " Switzerland", " Syrian Arab Republic", " Taiwan", " Tajikistan", " Tanzania", " Thailand", " Togo", " Tokelau", " Tonga", " Trinidad and Tobago", " Tunisia", " Turkey", " Turkmenistan", " Turks and Caicos Islands", " Tuvalu", " Uganda", " Ukraine", " United Arab Emirates", " United Kingdom", " Uruguay", " Uzbekistan", " Vanuatu", " Venezuela", " Vietnam", " Virgin Islands, British", " Wallis and Futuna Islands", " Western Sahara", " Yemen", " Yugoslavia", " Zambia");
		$this->set(compact('country_array'));
		
		$rx_quantity = array("1/2","1","1-2","2","3","4","5","6","7","8","9","10");
		$rx_unit = array("tab","Tbsp","tsp","Capsule","Puff(s)","Spray(s)","mg","Drop(s)","Box","cc","ml","oz","gm");
		$rx_route = array("PO","Inj","Inh","Subq","Otic","Topical","Oph","Sublingual","Vaginal");
		$rx_freq = array("BID|BID","QID|QID", "TID|TID","Q1|Q1&#176","Q2|Q2&#176","Q4|Q4&#176","Q6|Q6&#176","Q8|Q8&#176","Q12|Q12&#176","Q2-4|Q2-4&#176","Q4-6|Q4-6&#176","Q6-8|Q6-8&#176","Qday|Q day","Qwk|Q wk","Qac|Q ac","Qac/hs|Q ac/hs","Qam|Q am","Qhs|Q hs","Qpm|Q pm","Qmonth|Q month","Qyear|Q year");
		$rx_alt1=array("PRN","With Food","Sparingly","Liberally");
		$this->set(compact('rx_quantity','rx_unit','rx_route','rx_freq','rx_alt1'));

	}	
	public function __loadTheme()
	{
		$this->loadModel("PreferencesDisplay");
		
		if($this->Session->check('UserAccount') == false)
		{
			$display_settings = $this->PreferencesDisplay->getDisplaySettings(1);
		}
		else
		{
			$user = $this->Session->read('UserAccount');
			$this->set("user",$user);
			$display_settings = $this->PreferencesDisplay->getDisplaySettings($user['user_id']);
		}
		
		$this->Session->write('display_settings', $display_settings);
		$this->set("display_settings", $display_settings);
	}
	
	public function autocomplete()
	{
		$this->loadModel("AutocompleteOption");
		$this->loadModel("AutocompleteCache");
		
		$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
		$autocomplete_id = (isset($this->params['named']['autocomplete_id'])) ? $this->params['named']['autocomplete_id'] : "";
		
		
		switch($task)
		{
			case "save":
			{
				if (!empty($this->data)) 
                		{
					$this->AutocompleteCache->save($this->data);
				}
				
				$ret = array();
				echo json_encode($ret);
				
			} break;
			default:
			{
				$all_array = array();
				
				$search_keyword = $this->data['autocomplete']['keyword'];
				$search_limit = $this->data['autocomplete']['limit'];
				
				/*****Customer's preference Data comes first *********/
				$items = $this->AutocompleteCache->find('all', 
							array(
								'conditions' => array('AutocompleteCache.cache_item LIKE ' => '%'.$search_keyword.'%', 'AutocompleteCache.autocomplete_id' => $autocomplete_id),
								'order' => array('AutocompleteCache.citation_count' => 'DESC'),
								'limit' => $search_limit
							)
				);
				
				foreach($items as $item)
				{
					$all_array[$item['AutocompleteCache']['cache_item']] = $item['AutocompleteCache']['citation_count'];
				}

				
				/*****Main Data*********/
				$autocomplete_options = $this->AutocompleteOption->find(
					'first', 
					array(
						'conditions' => array(
							'AutocompleteOption.autocomplete_id' => $autocomplete_id
						)
					)
				);

				if(count($autocomplete_options) > 0)
				{
					$autocomplete_option = $autocomplete_options['AutocompleteOption'];
					
					if ($autocomplete_option['autocomplete_model']) {
						$this->loadModel($autocomplete_option['autocomplete_model']);
					}
					
					$modelLoaded = isset($this->{$autocomplete_option['autocomplete_model']}) && !empty($this->{$autocomplete_option['autocomplete_model']});
					if (!empty($this->data) && $modelLoaded) 
					{
						$items = $this->{$autocomplete_option['autocomplete_model']}->find('all', 
									array(
										'conditions' => array($autocomplete_option['autocomplete_model'].'.'.$autocomplete_option['autocomplete_valuefield'].' LIKE ' => '%'.$search_keyword.'%'),
										'order' => array($autocomplete_option['autocomplete_model'].'.'.$autocomplete_option['autocomplete_orderby'] => 'DESC'),
										'limit' => $search_limit
									)
						);
						foreach($items as $item)
						{
							$all_array[$item[$autocomplete_option['autocomplete_model']][$autocomplete_option['autocomplete_valuefield']]] = $item[$autocomplete_option['autocomplete_model']][$autocomplete_option['autocomplete_orderby']];
						}
					}
				}
				
				arsort($all_array); //sort high to low so the citation_count is at top (the more frequently used value)

				$data_array = array();
				
				foreach($all_array as $key=>$items)
				{
					$data_array[] = $key. '|' . $items;
				}
				
				
				
				if ($data_array) {
					echo implode("\n", $data_array);
				} else {
					echo ' ';
				}
				
			}
		}
		
		exit;
	}
	
	public function resetMenuList()
	{
		$this->loadModel("SystemMenu");
		$this->SystemMenu->recursive = 0;
		
		$menu_list = $this->getMenuList(0);
		$this->SystemMenu->truncate();
		
		foreach($menu_list as $menu)
		{
			$this->SystemMenu->create();
			
			$data = array();
			$data['SystemMenu']['menu_name'] = $menu['menu_name'];
			$data['SystemMenu']['menu_controller'] = $menu['menu_controller'];
			$data['SystemMenu']['menu_action'] = $menu['menu_action'];
			$data['SystemMenu']['menu_options'] = $menu['menu_options'];
			$data['SystemMenu']['menu_parent'] = '0';
			$data['SystemMenu']['menu_show'] = $menu['menu_show'];
			$data['SystemMenu']['menu_show_roles'] = $menu['menu_show_roles'];
			
			$this->SystemMenu->save($data);
			
			$parent_id = $this->SystemMenu->getLastInsertID();
			
			if(count($menu['submenu']) > 0)
			{
				foreach($menu['submenu'] as $submenu)
				{
					$this->SystemMenu->create();
					
					$data = array();
					$data['SystemMenu']['menu_name'] = $submenu['menu_name'];
					$data['SystemMenu']['menu_controller'] = $submenu['menu_controller'];
					$data['SystemMenu']['menu_action'] = $submenu['menu_action'];
					$data['SystemMenu']['menu_options'] = $submenu['menu_options'];
					$data['SystemMenu']['menu_parent'] = $parent_id;
					$data['SystemMenu']['menu_show'] = $submenu['menu_show'];
					$data['SystemMenu']['menu_show_roles'] = $submenu['menu_show_roles'];
					
					$this->SystemMenu->save($data);
				}
			}
		}
	}
	
	public function getMenuList($parent)
	{
		$arr = array();
		
		
		$menu_list = $this->SystemMenu->find('all',
			array(
				'conditions' => array('SystemMenu.menu_parent' => $parent)
			)
		);
		
		foreach($menu_list as $menu)
		{
			$tmp_arr = $this->getMenuList($menu['SystemMenu']['menu_id']);
			
			$arr[] = array(
				'menu_name' => $menu['SystemMenu']['menu_name'],
				'menu_controller' => $menu['SystemMenu']['menu_controller'],
				'menu_action' => $menu['SystemMenu']['menu_action'],
				'menu_options' => $menu['SystemMenu']['menu_options'],
				'menu_parent' => $menu['SystemMenu']['menu_parent'],
				'menu_show' => $menu['SystemMenu']['menu_show'],
				'menu_show_roles' => $menu['SystemMenu']['menu_show_roles'],
				'submenu' => $tmp_arr
			);
		}
		
		return $arr;
	}
	
	public function getAccessType($current_controller = "", $current_action = "", $api_user = array())
	{
		$current_controller = (strlen($current_controller) > 0) ? $current_controller : @$this->params['controller'];
		$current_action = (strlen($current_action) > 0) ? $current_action : @$this->params['action'];
        
        if(count($api_user) > 0) {
            $user = $api_user;
        }
        else {
            $user = $this->Session->read('UserAccount');
        }
        
        $role_id = $user['role_id'];
		
		if($role_id == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)
		{
			return 'W';
		}
		
		$real_user_role = $role_id;
		
		if($user['emergency'] == '1' && $real_user_role != EMR_Roles::PRACTICE_ADMIN_ROLE_ID)
		{
			$real_user_role = EMR_Roles::EMERGENCY_ACCESS_ROLE_ID;
		}
		
		$this->loadModel("SystemMenu");
		$current_access_point = $this->SystemMenu->find('first', 
			array(
				'conditions' => array(
					'SystemMenu.menu_controller' => $current_controller,
					'SystemMenu.menu_action' => $current_action,
				),
				'order' => array('SystemMenu.menu_id' => 'DESC')
			)
		);
		
		if($current_access_point)
		{
			if($current_access_point['SystemMenu']['menu_inherit'] != 0 && $current_access_point['SystemMenu']['menu_show_roles'] == 0)
			{
				$parent_access_point = $this->SystemMenu->find('first', 
					array(
						'conditions' => array(
							'SystemMenu.menu_id' => $current_access_point['SystemMenu']['menu_inherit']
						)
					)
				);
				
				if($parent_access_point)
				{
					return $this->getAccessType($parent_access_point['SystemMenu']['menu_controller'], $parent_access_point['SystemMenu']['menu_action']);
				}
				else
				{
					return 'Undefined Parent Access Point';
				}
			}
			else
			{
				if($current_access_point['SystemMenu']['system_admin_only'] == 1)
				{
					return "NA";
				}
				else
				{
					$this->loadModel("Acl");
					$this->Acl->recursive = 0;
							
					$acl = $this->Acl->find('first', 
						array(
							'conditions' => array(
								'Acl.role_id' => $real_user_role,
								'SystemMenu.menu_controller' => $current_controller,
								'SystemMenu.menu_action' => $current_action
							)
						)
					);
					
					if($acl)
					{
						if($acl['Acl']['acl_write'] == "1")
						{
							return 'W';
						}
						
						if($acl['Acl']['acl_read'] == "1")
						{
							return 'R';
						}
						
						if($acl['Acl']['acl_write'] == "0" && $acl['Acl']['acl_read'] == "0")
						{
							return "NA";
						}
					}
					else
					{
						return 'Undefined ACL';
					}
				}
			}
		}
		else
		{
			return 'Undefined Access Point';
		}
	}
	
	public function getRealUserRole(){
		// Returns the current (real) user role, includes checking for emergency access
		$real_user_role = EMR_Roles::FRONT_DESK_ROLE_ID;
		if($this->Session->check('UserAccount') != false){
			$user = $this->Session->read('UserAccount');
			$real_user_role = $user['role_id'];
			if($real_user_role != EMR_Roles::SYSTEM_ADMIN_ROLE_ID &&
				 $user['emergency'] == '1' &&
				 $real_user_role != EMR_Roles::PRACTICE_ADMIN_ROLE_ID){
				$real_user_role = EMR_Roles::EMERGENCY_ACCESS_ROLE_ID;
			}
		} else {
			return 0;
		}
		return $real_user_role;
	}
	
	public function validateAccess($current_controller, $current_action, $current_variarion = '', $first = true) //for menu
	{
		//Only applies to Menu Item
		$allow_access = true;

		if($this->Session->check('UserAccount') != false){
			$real_user_role = $this->getRealUserRole();
			if($real_user_role == EMR_Roles::SYSTEM_ADMIN_ROLE_ID){
				return true;
			}
			
			$this->loadModel("Acl");
			$this->Acl->recursive = 0;
			
			if($current_action == 'index' && $current_variarion == '' && $first) //Entry page - probably just a blank page
			{
				$acls = $this->Acl->find('all', 
					array(
						'conditions' => array(
							'Acl.role_id' => $real_user_role,
							'SystemMenu.menu_controller' => $current_controller
						)
					)
				);
				
				if(count($acls) > 0)
				{
					$allow_access = false;
					
					foreach($acls as $acl)
					{
						if($this->validateAccess($acl['SystemMenu']['menu_controller'], $acl['SystemMenu']['menu_action'], $acl['SystemMenu']['menu_variation'], false))
						{
							$allow_access = true;
						}
					}
				}
			}
			else
			{
				$this->loadModel("SystemMenu");
				$current_access_point = $this->SystemMenu->find('first', 
					array(
						'conditions' => array(
							'SystemMenu.menu_controller' => $current_controller,
							'SystemMenu.menu_action' => $current_action,
							'SystemMenu.menu_variation' => $current_variarion
						)
					)
				);
				
				if($current_access_point)
				{
					if($current_access_point['SystemMenu']['system_admin_only'] == 1)
					{
						$allow_access = false;
					}
					else
					{
					
						if($current_access_point['SystemMenu']['menu_inherit'] != 0 && $current_access_point['SystemMenu']['menu_show_roles'] == 0)
						{
							$allow_access = true;
							
							$parent_access_point = $this->SystemMenu->find('first', 
								array(
									'conditions' => array(
										'SystemMenu.menu_id' => $current_access_point['SystemMenu']['menu_inherit']
									)
								)
							);
							
							if($parent_access_point)
							{
								$allow_access = $this->validateAccess($parent_access_point['SystemMenu']['menu_controller'], $parent_access_point['SystemMenu']['menu_action'], $parent_access_point['SystemMenu']['menu_variation']);
							}
							else
							{
								//loose - Just allow access for undefined parent access point
								$allow_access = true;
							}
						}
						else
						{
							$acl = $this->Acl->find('first', 
								array(
									'conditions' => array(
										'Acl.role_id' => $real_user_role,
										'SystemMenu.menu_controller' => $current_controller,
										'SystemMenu.menu_action' => $current_action,
										'SystemMenu.menu_variation' => $current_variarion
									)
								)
							);
			
							if($acl)
							{
								if($acl['Acl']['acl_read'] == "0" && $acl['Acl']['acl_write'] == "0")
								{
									$allow_access = false;
								}
							}
							else
							{
								//loose - No ACL set for access point
								$allow_access = true;
							}
						}
					}
				}
				else
				{
					//loose - Just allow access for undefined access point
					$allow_access = true;
				}
			}
		}
		
		return $allow_access;
	}
	
	//This function Check For Access. Redirect to No Access Page if current user doesn't have access
	public function checkAccess($controller, $action, $variation = '')
	{
		$this->set("messages_count", 0);
		$this->loadModel("SystemMenu");
		
		$allow_access = $this->validateAccess($controller, $action, $variation);
		
		if(!$allow_access)
		{
			$menudata = $this->SystemMenu->find('all');
			$user = $this->Session->read('UserAccount');
			
			$acls = $this->Acl->find('all', 
				array(
					'conditions' => array(
						'Acl.role_id' => $user['role_id'],
						"AND" => array(
							"OR" => array(
								"Acl.acl_read" => 1,
								"Acl.acl_write" => 1
							)
						)
					)
				)
			);
			
			$this->redirect(array('controller' => 'dashboard', 'action' => 'index'));
		}
		else
		{
			if( isset($_COOKIE["iPad"]) )
			{
				// Build menu info for iPad App
				$menu_ipad = $this->loadiPadMenu('0');
				$this->set("menu_ipad", $menu_ipad);
			
				// Set new message count for the iPad App
				ClassRegistry::init('MessagingMessage');
				$message = new MessagingMessage();
				$user = $this->Session->read('UserAccount');
				$count = $message->countNewMessages($user['user_id']);
      	$this->set("messages_count", $count);
			}
			else
			{
				$menu_html = $this->loadMenu('0');
				$this->set("menu_html" , $menu_html);
			}
		}
	}
	
	public function loadMenu($parent){		
		// Check cache for existing menu string
		$real_user_role = $this->getRealUserRole();
		$user = $this->Session->read('UserAccount');
		
		if (!$user) {
			return '';
		}
		
		$db_config = $this->SystemMenu->getDataSource()->config;
		$cache_file_prefix = $db_config['host'].'_'.$db_config['database'].'_';								
		
		Cache::set(array('duration' => '+30 days'));
		$str = ($parent == '0') ? Cache::read($cache_file_prefix .'loadMenu'.$real_user_role) : false;

		if (isset($this->params['named']['refresh_menu'])) {
				$str = false;
		}		
		
		if( !$user || $str === false ){
			// Need to build the menu string for this user role
			$html = new HtmlHelper();
			if($parent == '0'){
				$class = 'class="sf-menu"';
				$li_class = 'class="mainmenu_item"';
			} else {
				$class = 'class="sf-submenu"';
				$li_class = 'class="submenu_item"';
			}
			$str = "";
			$menudata = $this->SystemMenu->find(
					'all', 
					array(
						'conditions' => array('SystemMenu.menu_parent' => $parent, 'SystemMenu.menu_show' => '1'),
						'order' => array('SystemMenu.menu_id')
					)
			);
			if(count($menudata) > 0){
				$str .= "<ul $class>";
				foreach ($menudata as $menu){
					if($this->validateAccess($menu['SystemMenu']['menu_controller'], 
							$menu['SystemMenu']['menu_action'], $menu['SystemMenu']['menu_variation'])){
						$options = array();
						if(strlen($menu['SystemMenu']['menu_url']) > 0){
							if(substr($menu['SystemMenu']['menu_url'], 0, strlen('local://')) == 'local://'){
								$param_str = str_replace('local://', '', $menu['SystemMenu']['menu_url']);
								$param_arr = explode('|', $param_str);
								$link_params = array('controller' => $menu['SystemMenu']['menu_controller'], 'action' => $menu['SystemMenu']['menu_action']);
								foreach($param_arr as $param_item){
									$param_details = explode(":", $param_item);
									$link_params[$param_details[0]] = $param_details[1];
								}
							} else {
								$link_params = $menu['SystemMenu']['menu_url'];
								$options['target'] = '_blank';
							}
						} else {
							$link_params = array('controller' => $menu['SystemMenu']['menu_controller'], 'action' => $menu['SystemMenu']['menu_action']);
						}
						
						if($menu['SystemMenu']['menu_enable_link'] == '0'){
							$balloon = '';
							if ($menu['SystemMenu']['menu_name'] == 'Messaging') {
								$balloon = 'xxMessageCountxx';
							} else if ($menu['SystemMenu']['menu_name'] == 'Help'){
								$li_class = 'class="mainmenu_item help_menu"';
							}
							$str .= '<li '.$li_class.'><a href="javascript:void(0);">'.$menu['SystemMenu']['menu_name']. $balloon .'</a>';
						} else {
							$str .= '<li '.$li_class.'>'.$html->link($menu['SystemMenu']['menu_name'], $link_params, $options);
						}
						$str .= $this->loadMenu($menu['SystemMenu']['menu_id']);
						$str .= '</li>';
					}
				}
				$str .= "</ul>";
				if( $parent == '0'){
					//CakeLog::write('debug', 'write loadMenu'.$real_user_role);
					Cache::set(array('duration' => '+30 days'));
					Cache::write($cache_file_prefix .'loadMenu'.$real_user_role, $str);
				}
			}
		}
		
		// Update menu string with current messages count
		$this->set("messages_count", 0);
		$balloon = '';
		if( $user ){
			ClassRegistry::init('MessagingMessage');
			$message = new MessagingMessage();
			$count = $message->countNewMessages($user['user_id']);
			$this->set("messages_count", $count);							
			if ($count) {
				$balloon = '<span class="mcircle msg-count">'.$count.'</span>';
			} else {
				$balloon = '<span class="mcircle msg-count" style="display: none">'.$count.'</span>';
			}
		}
		$str = str_replace('xxMessageCountxx', $balloon, $str);
		
		return $str;
	}
	
	public function loadiPadMenu($parent)
	{
		// Returns an array of menu items in the parent menu
		// If the parent is '0' then this is the top-level menu array
		//
		// Each menu item is an object with:
		//		'name'		: the text to display for the menu
		//		'url'			: the url of the menu object ('' for a top-level menu)
		//		'nested'	: nested menus (null or [] if none)

		// Check cache for existing menu string
		$real_user_role = $this->getRealUserRole();
		$iPadMenu = ($parent == '0') ? Cache::read('iPadLoadMenu'.$real_user_role) : false;
		if( $iPadMenu !== false )
			return $iPadMenu;
			
		// Need to build the menu
		$iPadMenu = array();
		$html = new HtmlHelper();
		$menudata = $this->SystemMenu->find(
			'all', 
			array(
				'conditions' => array('SystemMenu.menu_parent' => $parent, 'SystemMenu.menu_show' => '1'),
				'order' => array('SystemMenu.menu_id')
			)
		);
		if(count($menudata) > 0)
		{
			foreach ($menudata as $menu)
			{
				if($this->validateAccess($menu['SystemMenu']['menu_controller'], $menu['SystemMenu']['menu_action'], $menu['SystemMenu']['menu_variation']))
				{
					$menuItem = array();
					$options = array();
					if(strlen($menu['SystemMenu']['menu_url']) > 0)
					{
						if(substr($menu['SystemMenu']['menu_url'], 0, strlen('local://')) == 'local://')
						{
							$param_str = str_replace('local://', '', $menu['SystemMenu']['menu_url']);
							$param_arr = explode('|', $param_str);
							$link_params = array('controller' => $menu['SystemMenu']['menu_controller'], 'action' => $menu['SystemMenu']['menu_action']);
							foreach($param_arr as $param_item)
							{
								$param_details = explode(":", $param_item);
								$link_params[$param_details[0]] = $param_details[1];
							}
						}
						else
						{
							$link_params = $menu['SystemMenu']['menu_url'];
							$options['target'] = '_blank';
						}
					}
					else
					{
						$link_params = array('controller' => $menu['SystemMenu']['menu_controller'], 'action' => $menu['SystemMenu']['menu_action']);
					}

					$menuItem['name'] = $menu['SystemMenu']['menu_name'];
					$menuItem['url'] = '';
					if($menu['SystemMenu']['menu_enable_link'] != '0')
					{
						$str = $html->link($menu['SystemMenu']['menu_name'], $link_params, $options);
						$href = 'href="';
						$start = strpos($str, $href);
						if( $start !== false ){
							$start += strlen($href);
							$end = strpos($str, '"', $start);
							if( $end !== false ){
								$menuItem['url'] = substr($str, $start, $end-$start);
							}
						}
					}
					$menuItem['nested'] = $this->loadiPadMenu($menu['SystemMenu']['menu_id']);
					$iPadMenu[] = $menuItem;
				}
			}
		}
		
		if( $parent == '0'){
			Cache::write('iPadLoadMenu'.$real_user_role, $iPadMenu);
			//CakeLog::write('debug', 'write iPadLoadMenu'.$real_user_role);
		}
		return $iPadMenu;
	}
	
	public function validateLoginStatus() 
    {
		$current_controller = @$this->params['controller'];
		$current_action = @$this->params['action'];
        
		if(!access::guestActionHasAccess($current_controller, $current_action)) 
        {
			if($this->Session->check('UserAccount') == false)
            {
            	//if($current_controller && $current_action && !($current_controller == 'administration' && $current_action!='login')) {
            		$this->redirect(array('controller' => 'administration', 'action' => 'login'));
            	//}
				$this->Session->setFlash('The URL you\'ve followed requires you login.');
			}
		}
    }
	
	public function sanitizeHTML($data)
	{
		$ret = array();
		
		if(is_array($data))
		{
			foreach($data as $key => $value)
			{
				if(is_array($value))
				{
					$ret[$key] = self::sanitizeHTML($value, array('charset' => 'ISO-8859-1'));
				}
				else
				{
					$ret[$key] = Sanitize::html($value, array('charset' => 'ISO-8859-1'));
				}
			}
		}
		else
		{
			$ret = Sanitize::html($data);
		}
		
		return $ret;
	}
}
 
