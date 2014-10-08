<?php

App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Core', 'Controller');
App::import('Lib', 'email', array('file' => 'email.php'));

class OpenEncountersNotifyShell extends Shell
{
	public $uses = array('EncounterMaster','MessagingMessage','PracticeSetting','PracticeProfile'); 
	public $actsAs = array('Containable');

	function main() 
	{
	    $items=$this->EncounterMaster->find('all', array('conditions' => array('DATE(encounter_date) < DATE_SUB(CURDATE(), INTERVAL 90 DAY)'), 
						'fields' => array('EncounterMaster.encounter_date'),
						'contain' => array( 'UserAccount' => array('firstname','lastname','title','email')) 
						)
				);
	    $provider=array();
	    foreach ( $items as $encounter  )
	    {
		$user_id=$encounter['UserAccount']['user_id'];
		$email=$encounter['UserAccount']['email'];
		if($user_id) {
			$provider[$user_id] = array('firstname'=> $encounter['UserAccount']['firstname'],
					    'lastname' => $encounter['UserAccount']['lastname'],
					    'email' =>$encounter['UserAccount']['email'],
					    'title' => $encounter['UserAccount']['title'], 
					    'user_id' => $user_id
					  );
		}
	    }
		$this->message="This is to inform you that you have open encounters that are over 90 days old. It's important to lock your notes once you have completed your encounters as this will take a snapshot and file the note away in the patient's chart for safe keeping. ";
	
		$this->notifyprovider($provider);

	}


	function notifyprovider($p)
	{
		$practiceSetting=$this->PracticeSetting->getSettings();
		$partner_id = $practiceSetting->partner_id;
		$customer = $practiceSetting->practice_id;
		//if partner domain is defined for private label customer
		$domain = (!empty($partner_id)) ? $partner_id : 'onetouchemr.com';

		foreach ($p as $v) {
		   $who=$v['title']. ' '.$v['firstname']. ' '. $v['lastname'];
		   $subject= "NOTICE: Open Encounters";
		   $body=$who. ",\n\n". $this->message;
		  //send an email if one defined
		  if(!empty($v['email']))
		  {
			$url = 'https://';
                   	$url .= ($customer) ? $customer.'.'.$domain : $domain;
			$footer="\n\nTo find your open encounters, visit the 'Patients' -> 'Encounters' menu once you login at ".$url. " or with the iPad App.";
			$practiceProfile = $this->PracticeProfile->find('first');
			$embed_logo_path='';
			//see if practice has their own logo, if so use it
			$practice_logo = $practiceProfile['PracticeProfile']['logo_image'];
           		if($practice_logo ) {
           	 	    $embed_logo_path = WWW_ROOT.'/CUSTOMER_DATA/'.$practiceSetting->practice_id.'/' . $practiceSetting->uploaddir_administration.'/'.$practice_logo;
           	      	 	    if(!file_exists($embed_logo_path)) {$embed_logo_path='';  }
           	 	}
			email::send($who, $v['email'], $subject, $body.$footer, $practiceSetting->sender_name, $practiceSetting->sender_email, "true",'','','','',$embed_logo_path);
		  }
		  else
		  {

                                $url =  Router::url(array(
                                  'controller' => 'encounters',
                                  'action' => 'index',
                                ));
			$footer="<br><br> To review your open encounters, just <a href=".$url.">visit here.</a> ";
                                        $this->MessagingMessage->create();
					$this->data['MessagingMessage']['sender_id']= '1';
					$this->data['MessagingMessage']['type']='Other';
                                        $this->data['MessagingMessage']['recipient_id'] = $v['user_id'];
                                        $this->data['MessagingMessage']['created_timestamp'] = __date("Y-m-d H:i:s");
                                        $this->data['MessagingMessage']['modified_timestamp'] = __date("Y-m-d H:i:s");
                                        $this->data['MessagingMessage']['modified_user_id'] = '1';
					$this->data['MessagingMessage']['subject']=$subject;
					$this->data['MessagingMessage']['message']=$body.$footer;
                                        $this->MessagingMessage->save($this->data);
		  }
		}
	}


}
