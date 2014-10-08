<?php

class HelpController extends AppController {

    public $name = 'Help';
    public $helpers = array('Html', 'Ajax', 'Form', 'Javascript', 'QuickAcl');
    public $components = array('RequestHandler', 'Email');
    public $uses = array('UserAccount', 'PracticeSetting', 'PatientDemographic');
    public $useTable = false;

    public function upgrade() {
        if (!empty($this->data)) {
            update::run_update();

            $this->Session->setFlash(__('Item(s) saved.', true));
            $this->redirect(array('action' => 'upgrade'));
        }
    }

    function forgot_password() {
        $this->layout = "login";
        $this->loadModel('PracticeProfile');
        $practice_profile = $this->PracticeProfile->find('first');
        $this->set('practice_name', $practice_profile['PracticeProfile']['practice_name']);
        $this->set('practice_logo', $practice_profile['PracticeProfile']['logo_image']);
    }

    function reset_password() {
        $this->layout = "login";

		$params = (isset($this->params['named']) ? $this->params['named'] : null);
		
       	if (isset($params['token']) && $token = $params['token']) {
			$user = $this->UserAccount->getUserByToken($token);
		}
		else
		{
			$this->redirect(array('controller' => 'administration', 'action' => 'login'));
		}

        if (isset($this->params['form']) && $form = $this->params['form']) {
            $this->layout = "empty";

            if (!$token) {

                $this->redirect(array('controller' => 'administration', 'action' => 'login'));
                return;
            }

            $user = $this->UserAccount->getUserByToken($token);

            if (!$user || !$user->token_delivery) {
                //error invalid token
                $this->Session->setFlash('Invalid Token.', 'default', array('class' => 'error'));
                $this->redirect(array('controller' => 'administration', 'action' => 'login'));
                exit('Invalid Token');
            }

            if (time() > $user->token_delivery + (3600 * 2)) {
                //error token is expired.  expires after  2 hours
                $this->Session->setFlash('Token has expired.', 'default', array('class' => 'error'));
                $this->redirect(array('controller' => 'administration', 'action' => 'login'));
                exit('Token has expired');
            }

            $PracticeSetting = $this->PracticeSetting->getSettings();

            $this->set('url', $this->Session->host);

            $content = $this->render('../elements/email/html/reset_password_confirmation');
            
	    $form['password'] = trim($form['password']);
            $form['password2'] = trim($form['password2']);
            if (strlen($form['password']) < 6) {
                $this->Session->setFlash('Password length must be at least 6 characters.', 'default', array('class' => 'error'));
                $this->redirect(array('controller' => 'help', 'action' => 'reset_password'));
                exit();
            }

            if ($form['password2'] != $form['password']) {
                $this->Session->setFlash('Passwords must match.', 'default', array('class' => 'error'));
                $this->redirect(array('controller' => 'help', 'action' => 'reset_password'));
                exit();
            }

            $data['password'] = $form['password'];
            $data['token'] = null;
            $data['token_delivery'] = null;
            $data['password_last_update'] = time() + site::setting('password_expires');
            $data['user_id'] = $user->user_id;
            $data['status'] = 1;
            $this->UserAccount->save($data);
                        
	    $sender_name=$sender_email="";
            ///send email
	    email::send($user->firstname, $user->email, email_formatter::formatSubject('Your Password has been reset'), $content, $sender_name, $sender_email, "true",'','','','',email_formatter::fetchPracticeLogo() );

            $this->Session->destroy();

            $this->Session->setFlash('Your password has been reset. You may now login.', 'default');
            $this->redirect(array('controller' => 'administration', 'action' => 'login'));

            return;
        }

        if (!$user) {

            $this->redirect(array('controller' => 'administration', 'action' => 'login'));

            return;
        }
        $this->set('user', $user);
        $this->set('token', $token);

        $this->Session->delete('user');
    }

    function check_user() {
        echo "false";
        exit;
    }

    function recover_password() {
        $this->layout = "empty";

        $params = (isset($this->params['named']) ? $this->params['named'] : null);

        $response = array();
        $response['error'] = "";

        if (isset($params['token']) && $token = $params['token']) {
            $this->layout = "login";

            $user = $this->UserAccount->getUserByToken($token);


            if (!$token || !$user || !$user->token_delivery) {
                //error invalid token
                $this->Session->setFlash('Invalid Token.', 'default', array('class' => 'error'));
                $this->redirect(array('controller' => 'administration', 'action' => 'login'));
                exit('Invalid Token');
            }
            if (time() > $user->token_delivery + (3600 * 2)) {
                //error token is expired.  expires after  2 hours
                $this->Session->setFlash('Token has expired.', 'default', array('class' => 'error'));
                $this->redirect(array('controller' => 'administration', 'action' => 'login'));
                exit('Token has expired');
            }

            $this->Session->write('token', $token);
            $this->Session->write('user', $user);
            $this->redirect(array('controller' => 'help', 'action' => 'reset_password', 'token' => $token));

            return;
        }

        $form = data::object($this->params['form']);

        if (strpos($form->username, '@') !== false) {
            $user = $this->UserAccount->getUserByEmail($form->username);
        } else {
            $user = $this->UserAccount->getUserByUsername($form->username);
        }

        if (!$user) {
            $response['error'] = "User was not found.";

            exit(json_encode($response));
        }

        $data = array();
        $data['token'] = $token = sha1($user->email . $user->username . ':salt' . md5(microtime()) . Configure::read('Security.salt'));
        $data['token_delivery'] = time();

        $url = "help/reset_password/token:$token";

        $this->set('token', $token);
        $this->set('url', $this->Session->host);
        $this->set('user', $user);


        $content = $this->render('../elements/email/html/reset_password');
	$sender_name=$sender_email="";
        if (($r = email::send($user->firstname, $user->email, email_formatter::formatSubject('Password Reset Instructions'), $content, $sender_name, $sender_email, "true",'','','','',email_formatter::fetchPracticeLogo() )) === true) {

            $data['user_id'] = $user->user_id;
            $this->UserAccount->save($data);

            App::import('Helper', 'Html');
            $html = new HtmlHelper();

            $response['msg'] = "Please check your email for more information. <a href='" . Router::url(array('controller' => 'administration', 'action' => 'login')) . "'>Click here to continue</a>";
        } else {
            $response['msg'] = $r;
        }


        exit(json_encode($response));
    }

    function patient_registration() {
        $this->layout = "login";
        $this->loadModel('PracticeProfile');
	$practice_profile = $this->PracticeProfile->find('first');
	$this->set('practice_name', $practice_profile['PracticeProfile']['practice_name']);
	$this->set('practice_logo', $practice_profile['PracticeProfile']['logo_image']);
        
    }

    /**
     * Action to serve ajax request from
     * form in help/patient_registration
     */
    function new_patient_reg() {
        $this->layout = "empty";

        $params = (isset($this->params['named']) ? $this->params['named'] : null);

        $response = array();
        $response['error'] = "";

        // Get posted form data, convert as object
        // Dunno why it had to be converted to object
        // but will just leave it as it is
        $form = data::object($this->params['form']);
        $user = $this->UserAccount->getUserByEmail($form->email);

        // Validate email address first
        $validate = new Validation();
        
        if (!$validate->email($form->email)) {
            $response['error'] = "1";
            $response['msg'] = 'Invalid Email address';
            exit(json_encode($response));
        }
        
				App::import('Helper', 'HtmlHelper');
				
				$html = new HtmlHelper();
				
        $form->resend = intval($form->resend);
        
        if ($user && !$form->resend) {
          
            if ($user->username) {
              $response['error'] = "1";
              $response['msg'] = 'That Email address is already in our system. If this is your email address, 
                you can use the ' . $html->link('Forgot Password Form', array(
                  'controller' => 'help',
                  'action' => 'forgot_password',
                )) . ' to request your password.';
              exit(json_encode($response));              
            } else {
              $response['error'] = "1";
              $response['resend'] = "1";
              $response['msg'] = 'A confirmation email has already been sent to this address. If you have not received it yet, '
                      . ' click continue to resend email.';
              exit(json_encode($response));                  
            }
          
          

        }

        /*
        $this->loadModel('PatientDemographic');
        $patient = $this->PatientDemographic->find('first', array('conditions' => array(
            'PatientDemographic.email' => $form->email,
        )));
        
        
        if ($patient) {
            $dob = isset($form->dob) ? strtotime($form->dob) : '';
            
            if ($dob == '') {
              $response['error'] = "1";
              $response['msg'] = 'That Email address is already used by a patient. If you are the patient who owns this address, please confirm by entering your date of birth';
              $response['check_dob'] = '1';
              exit(json_encode($response));
            }
            
            $dob = __date('Y-m-d', $dob);
            
            if ($patient['PatientDemographic']['dob'] != $dob) {
              $response['error'] = "1";
              $response['msg'] = 'Birth date does not match patient info using the given email address';
              $response['check_dob'] = '1';
              exit(json_encode($response));              
            }
            
            
            
        }        
        */
        $data = array();

        
        if (!$form->resend) {
          // Create new patient user account 
          // subject for confirmation. See UserAccount::registerNewPatient()
          $token = $this->UserAccount->registerNewPatient($form->email);
          
        } else {
          $token = $user->token;
        }

        // Set necessary info for message generation
        $this->set('token', $token);
        $this->set('hostName', $this->Session->host);

        /*
        if ($patient) {
          $user = $this->UserAccount->getRawUserByEmail($form->email);        
          $user['UserAccount']['patient_id'] = $patient['PatientDemographic']['patient_id'];
          $this->UserAccount->save($user);
        }
        */
        
        // Get output from a view file to
        // server as body for the email we want to send
        $content = $this->render('../elements/email/html/confirm_registration');
	$content .= email_formatter::generateFooter("");
	$sender_name=$sender_email="";
        // Try to send the email
        if (($r = email::send($form->email, $form->email, email_formatter::formatSubject('Confirm Account Registration'), $content, $sender_name, $sender_email, "true",'','','','',email_formatter::fetchPracticeLogo() ) ) === true) {
          
            if ($form->resend) {
              $response['msg'] = "Confirmation email sent again.";
            } else {
              $response['msg'] = "Instructions on how to continue registration has been sent to the email address you provided. ";
            }
          
        } else {
            $response['msg'] = $r;
        }

        // Output response
        exit(json_encode($response));
    }

    /**
     * Confirmation page
     * 
     * Handles checking of valid registration token,
     * entry of additional patient info
     * and creationg of patient account
     * 
     */
    public function confirm_registration() {

				$isAjax = $this->RequestHandler->isAjax();
			
        $this->layout = 'login';

				if ($isAjax) {
					Configure::write('debug', 0);
					$this->layout = 'empty';
				}
        // Check existence of token parameter
        $token = isset($this->params['named']['token']) ? $this->params['named']['token'] : '';

        $user = array();
        $invalidToken = false;
        $errors = array();


        // No token, raise invalid token flag
        if (!$token) {
            $invalidToken = true;

            // token was given ...
        } else {

            // ... try to fetch the user associated with this token ...
            $user = $this->UserAccount->getUserByToken($token);

            // ... set invalid token flag if no user was found
            // given the token
            if (!$user) {
                $invalidToken = true;
            }
        }
        
        $patient = array();
        //if ($user->patient_id) {
        //  $this->PatientDemographic->id = $user->patient_id;
        //  $patient = $this->PatientDemographic->read();
        //}

        if (isset($this->data['UserAccount'])) {
						$dob = data::formatDateToStandard($this->__global_date_format, $this->data['UserAccount']['dob']);
						$this->data['UserAccount']['dob'] = $dob;

            $this->UserAccount->set($this->data);
            
            //if ($patient) {
            //  $this->UserAccount->setValidation('confirm_existing');
            //} else {
              $this->UserAccount->setValidation('confirm_registration');
            //}
						
            if ($this->UserAccount->validates()) {
                $mrn = 0;

                if (!$patient) {
                  // Begin creating Patient Demographic Record
                  // Generate mrn
                  $LastMrn = $this->PatientDemographic->find('first', array('order' => array('PatientDemographic.mrn DESC'),'recursive' => -1));
                  if (!empty($LastMrn['PatientDemographic']['mrn'])) { // if records already exist, increment by 1
                      $mrn = $LastMrn['PatientDemographic']['mrn'] + 1;
                  }


                  $this->PatientDemographic->create();

                  // Basic patient demoggraphic data
                  $patient = array('PatientDemographic' => array(
                          'dob' => $dob,
                          'first_name' => $this->data['UserAccount']['firstname'],
                          'last_name' => $this->data['UserAccount']['lastname'],
                          'modified_user_id' => $user->user_id,
                          'email' => $user->email,
                          'mrn' => $mrn,
                          'status' => 'Pending',
                          ));

                  $this->PatientDemographic->save($patient, false);                  
                  // Note patient id
                  $patientId = $this->PatientDemographic->getLastInsertID();
                } else {
                  $dob = $patient['PatientDemographic']['dob'];
                  $patientId = $patient['PatientDemographic']['patient_id'];
                  $this->data['UserAccount']['firstname'] = $patient['PatientDemographic']['first_name'];
                  $this->data['UserAccount']['lastname'] = $patient['PatientDemographic']['last_name'];
                  $this->data['UserAccount']['dob'] = $patient['PatientDemographic']['dob'];
                }

                
                $date_ar = explode("-",$dob);
				$lastdigits_year = $date_ar[0];
				$username_code = $date_ar[1].$date_ar[2].$lastdigits_year[2].$lastdigits_year[3];


                // Sanitize first name that will be used as basis for username
                $firstname = Sanitize::paranoid($this->data['UserAccount']['firstname']);
				$firstinitial = $firstname[0];
				$username = $firstinitial.Sanitize::paranoid($this->data['UserAccount']['lastname']).$username_code;
				$count = $this->UserAccount->find('count', array('conditions' => array('UserAccount.username' => $username)));
                // Generate unique username based on first name
                while ($count > 0) {
                    $username = $firstname . rand(10000, 99999);
                    $count = $this->UserAccount->find('count', array('conditions' => array('UserAccount.username' => $username)));

                    if (!$count) {
                        break;
                    }
                }

                // Fields that are allowed to be saved for the user account
                // This will filter out dob plus other invalid and malicious fields
                $whiteList = array(
                    'user_id', 'firstname', 'lastname', 'username', 'password',
                    'token', 'token_delivery', 'patient_id',
                );

                // Build user data to be updated
                $this->data['UserAccount']['user_id'] = $user->user_id;
                $this->data['UserAccount']['token'] = null;
                $this->data['UserAccount']['token_delivery'] = null;
                $this->data['UserAccount']['patient_id'] = $patientId;
                $this->data['UserAccount']['username'] = $username;
                
                // Set password last update time 
                // so we don't get prompted for an expired password 
                // when we login after registration
                $this->data['UserAccount']['password_last_update'] = time();

                $this->UserAccount->save($this->data, false, $whiteList);


                // Send New Account creation notice
                // that includes generate username
                $this->set('hostName', $this->Session->host);
                $this->set('username', $username);
                // Get output from a view file to
                // server as body for the email we want to send
                $content = $this->render('../elements/email/html/account_created');

                // Write new username to session
                $this->Session->write('new_username', $username);
                
                // Send message to front desk users
                $this->loadModel("MessagingMessage");
                $this->data['MessagingMessage']['sender_id'] = $user->user_id;
                $this->data['MessagingMessage']['patient_id'] = $patientId;
                $this->data['MessagingMessage']['type'] = "Other";
                $this->data['MessagingMessage']['subject'] = "New Patient Registration";
                $patient_url = Router::url(array('controller'=>'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patientId));
                $this->data['MessagingMessage']['message'] = "A new patient has registered and currently in Pending status :<br><a href=".$patient_url.">View Patient</a>";                        
                $this->data['MessagingMessage']['priority'] = "Normal";
                $this->data['MessagingMessage']['status'] = "New";
                $this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
                $this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
                $this->data['MessagingMessage']['modified_user_id'] = $patientId;

                $frontdesk_users = $this->UserAccount->find('all', array('conditions' => array('UserAccount.role_id' => EMR_Roles::FRONT_DESK_ROLE_ID)));

                if(count($frontdesk_users)!=0) {
                    foreach($frontdesk_users as $frontdesk_user) {
                        $this->MessagingMessage->create();							
                        $this->data['MessagingMessage']['recipient_id'] = $frontdesk_user['UserAccount']['user_id'];
                        $this->MessagingMessage->save($this->data);
                    }
                }                
                $sender_name=$sender_email="";
                // Try to send the email
                if (($r = email::send($user->email, $user->email, email_formatter::formatSubject('Patient Portal Account Created'), $content, $sender_name, $sender_email, "true",'','','','',email_formatter::fetchPracticeLogo() )) === true) {
                    $this->Session->setFlash('New Account created. Your username has been sent to your email address');
                } else {
                    $this->Session->setFlash('New Account created');
                }

								if (!$isAjax) {
									$this->redirect(array('controller' => 'help', 'action' => 'account_created'));
								}
                exit();

                // We cannot update Emdeon and Dosespot patient info yet since
                // we are still missing important fields
            } else {
                $errors = $this->UserAccount->invalidFields();
            }
        }

	$this->loadModel('PracticeProfile');
	$practice_profile = $this->PracticeProfile->find('first');
	$this->set('practice_name', $practice_profile['PracticeProfile']['practice_name']);
	$this->set('practice_logo', $practice_profile['PracticeProfile']['logo_image']);

        $this->set(compact('invalidToken', 'user', 'errors', 'isAjax', 'patient'));
    }
    
    /**
     * Just shows the page that tells the account
     * was successfully created
     */
    function account_created(){
        $this->layout = 'login';
        
    }

    function update() {
        $updates_dir = realpath("../../") . DS . "database/updates/";

        App::Import('Lib', 'site');

        if (!$version = site::setting('version')) {
            $version = '1.1.1';
        }

        $this->set("version", $version);
        $this->set("dir", $this->_updateDirs($updates_dir));
    }

    private function _updateDirs($updates_dir) {
        $dir = scandir($updates_dir);
        unset($dir[0], $dir[1]);
        array_multisort($dir, SORT_DESC);

        return $dir;
    }

    function doUpdate() {
        $form = $this->params['form'];
        if (!isset($form['update'])) {
            return;
        }

        $update = $form['update'];

        $updates_dir = realpath("../../") . DS . "database/updates/";

        $dirs = $this->_updateDirs($updates_dir);

        foreach ($dirs as $k => $v) {

            if (isset($update[$v])) {

                $sql_dir = $updates_dir . "$v/";

                $dir = $this->_updateDirs($sql_dir);

                foreach ($dir as $k => $v) {
                    if (file_exists($f = $sql_dir . $v)) {
                        $response = xinstall::runQueries($f);

                        echo("
		<pre>
		*******Upgrade results****
		" . basename($f) . "
		***************************
		</pre>
		" .
                        implode("\n<br />", $response));

                        $this->site_settings->set('list', array(
                            'conditions' => array('site_settings.setting' => 'version'),
                            'fields' => "value"
                        ));
                    }
                }
            }
        }
        exit(0);
    }

    public function beforeFilter() {
        $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
        $session_id = (isset($this->params['named']['session_id'])) ? $this->params['named']['session_id'] : "";

        if ($task == "upload_file" || $task == "download_file") {
            $this->Session->id($session_id);
            $this->Session->start();
        }

        parent::beforeFilter();
    }

    public function index() {
        $this->redirect('/forms');
    }

    private function getVersion($a) {
        $filename = APP . 'VERSION.txt';
        if (is_file($filename)) {
            $handle = fopen($filename, 'r');
            $data = fread($handle, filesize($filename));
            $content = trim($data);
            list($major,$minor,$sub)=explode('.',$content);
            $contents = $major.'.'.$minor . ' &nbsp&nbsp;  <em>rev '.$sub. '</em>';
        } else {
            $contents = "1";
        }
        if($a == 'full')
        return $content;
        else
        return $contents;
    }

	public function contact_us()
	{
		$this->loadModel('PracticeLocation');
		$this->loadModel('PracticeProfile');
		$details['locations'] = $this->PracticeLocation->find('all');
		$details['profile']=$this->PracticeProfile->find('first');		
		$this->set('details',$details);
	}
	
    public function about() {
        $this->set('version', $this->getVersion('1'));
    }

    public function changes_log() {
        $ver = $this->getVersion('full');
        $this->set('version', $ver);
        $filename = APP . 'changes_log/' . $ver . '.txt';
        if (is_file($filename)) {
            $handle = fopen($filename, 'r');
            $contents = fread($handle, filesize($filename));
        } else {
            $contents = "< NONE >";
        }
        $this->set("contents", $contents);
    }

    /*
	* To send the issues or ticket for the OTEMR clients.
	* @params void
	* @return no return value
	*/
    
   	public function support() 
	{
		$this->loadModel("PracticeProfile");
		$user = $this->Session->read('UserAccount');
		$userDetail = $this->UserAccount->find('first', array('conditons' => array('user_id' => $user['user_id']), 'fields' => array('firstname', 'lastname', 'email', 'cell_phone', 'work_phone'), 'recursive' => -1));
		$practiceProfile = $this->PracticeProfile->find('first', array('fields' => array('practice_name'), 'recursive' => -1));
		$this->set(compact('userDetail', 'practiceProfile'));	
		if (!empty($this->data)) {
			$controller = new Controller();
			$view = new View($controller);
			$message = $view->element('email/html/support', array('data'=>$this->data)); // get mail content 
			$send = email::send("One touch EMR Support","support@onetouchemr.com", "Support Ticket From ".$practiceProfile['PracticeProfile']['practice_name'], $message);
			if($send) {					
				$this->Session->setFlash(__('Mail has been sent.', true));
				$this->redirect(array('action' => 'support'));
				exit;
			} else {
				$this->Session->setFlash('Sorry, mail not sent.', 'default', array('class' => 'error'));
			}
		}			
    }
		
		public function check_network() {
			
		}
	
    public function tutorial() {
			//grab partner ID if present
			$pr = $this->Session->read("PracticeSetting");
			$partner_id=$pr['PracticeSetting']['partner_id'];

                        if ($_SERVER['HTTPS'] == 'on') {
                          $htt='https';
                        } else {
                          $htt='http';
                        }

			if(!empty($partner_id))
			{
				$dom=$htt."://tutorial.".$partner_id;
				$jsonFile = $dom."/videos/".$partner_id."/toc.json";
			}
			else
			{
				$dom=$htt."://tutorial.onetouchemr.com";
				$jsonFile = $dom."/videos/toc.json";
			}
			$toc = file_get_contents($jsonFile);
			$toc = str_replace('"/videos/', '"'.$dom.'/videos/', $toc);
			$this->set('toc', $toc);
    }

    public function feature() {
     // suggest new features
     // just calling iFrame to uservoice website forum
    }    
}

?>
