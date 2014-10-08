<?php

class EMR_Security {
	
	
	/**
	 * 
	 * An account that has been disabled through tooManyTries() can be reenable with this function.
	 * Notications to parties and email handling.
	 */
	public static function reEnableAccountProcess($user_id)
	{
		$controller = new Controller;
		
		$controller->loadModel('practiceSetting');
		$controller->loadModel('userAccount');
		
		$settings  = $controller->practiceSetting->getSettings();
		
		$user = $controller->userAccount->getUserByID($user_id);
		
		
		if(!$user) {
			return;
		}
		
		$controller->layout = "blank";
		
		$url = Router::url(array('controller'=> 'administration','action'=> 'users'), true);
		$url .= "task:edit/user_id:$user->user_id";
		
		$controller->set('user', $user);
		
		$controller->set('url', $url);
		
		
		$msg_admin = $controller->render("/elements/email/html/user_login_to_admin_account_reactivation");
		
		
		//Message Admin
		message::send( system::getUserId() , system::getUserId(), "{$user->username}: Account reactivated ", data::html2Text($msg_admin));
			
		//Email Admin
	    email::send( $settings->sender_name , $settings->sender_email , "{$user->username}: Account reactivated ", $msg_admin);
	    
	    
	}
	
	
	/**
	 * 
	 * try to login too many times and the account gets suspendend. Notication to parties, and email handling.
	 * 
	 * @param unknown_type $user_data
	 */
	public static function tooManyTries($user_data)
	{
		if(!$user_data) {
			return;
		}
		$controller = new Controller;
		
		$controller->loadModel('practiceSetting');
		$controller->loadModel('userAccount');
		
		$settings  = $controller->practiceSetting->getSettings();
		
		$user = $controller->userAccount->getUserByUsername($user_data['username']);
		
		if(!$user) {
			return;
		}
		
		$controller->layout = "empty";
		
		$controller->set('user', $user);
		
		$url = Router::url(array('controller'=> 'administration','action'=> 'users'), true);
		$url .= "task:edit/user_id:$user->user_id";
		
		$controller->set('url',$url);
		
		$controller->set('user_url', Router::url(null, true));
		
		$msg_user = $controller->render("/elements/email/html/system_to_user_tries_exhausted_account_disabled");
		$msg_admin = $controller->render("/elements/email/html/user_login_to_admin_tries_exhausted");
	    
		if(!$settings->sender_name) {
			$settings->sender_name = "Admin";
		}
		if(!$settings->sender_email) {
			$settings->sender_email = "devteam@onetouchemr.com";
		}
		
		$data['user_id'] = $user->user_id;
		$data['status'] = 0;
		$data['account_disabled_reason'] = "Login tries exhausted";
		
		$controller->userAccount->save( $data );

		if(system::getUserId()!==$user->user_id)
		{
			//Message Admin
			message::send( system::getUserId() , system::getUserId(), "Login tries exhausted", strip_tags($msg_admin));
		}
		
		//Message User
		message::send( system::getUserId() , $user->user_id, "Login tries exhausted", strip_tags($msg_user));
		
		//Email Admin
	    email::send( $settings->sender_name , $settings->sender_email , "{$user->username}: Login tries exhausted" , $msg_admin);
	    
	    //Email User
		if(!email::send( $user->username , $user->email , "Login tries exhausted" , $msg_user)) {
	    	return  true;
	    }
	}
}