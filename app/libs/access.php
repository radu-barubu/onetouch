<?php

class access {
	
	const PASSWORD_EXPIRES_TIME_DEFAULT = 7776000;//90 days
	
	
	/**
	 * 
	 * checks if the password has expired and prompt to change password page.
	 * @param unknown_type $user
	 * @param unknown_type $controller
	 */
	public static function checkExpiredPassword($user, & $controller)
	{
		if ($user)
		{
      
      if ($controller->name == 'Api') {
        return true;
      }
      
			if($controller->name=='Administration' && $controller->action=='expired_password' || access::isAjaxRequest()) {
				return;
			}

                        // User first login! Both last_login and password_last_update are null
                        if (!$user['last_login'] && !$user['password_last_update'] && $user['role_desc'] != 'Patient') {
                            header("location: ".Router::url(array('controller' => 'administration', 'action' => 'expired_password')));
                            exit();
                        }

                        // Check for expired password
                        $expiration = self::getExpirePasswordTime();
                        
                        // Note that this also covers the case when an admin changes a patient's password
                        // When an admin changes a patient's password, 
                        // it sets the last password updated to the past
                        if ($expiration < (time() - $user['password_last_update']) ) {
                            header("location: ".Router::url(array('controller' => 'administration', 'action' => 'expired_password')));
                            exit();
                        }
                        
		}
	}
	
	
	public static function setExpirePasswordTime($time = null)
	{
		if(!$time) 
		{
			$time = (3600 * 24 * 90); //90 days
		}
		return site::setting('password_expires', $time);
	}
	
	public static function getExpirePasswordTime()
	{
		$time = site::setting('password_expires');
		if(!$time)
		{
			$time =  self::PASSWORD_EXPIRES_TIME_DEFAULT;
		}
		return time() + $time;
	}
	
	/**
	 * 
	 * All  the methods defined below will be given access to guests.
	 * Only  use for public help or the like.
	 */
	public static function guestControllers()
	{
		return array(
			'administration' => array('logout','login'),
                        'dashboard' => array('check_login'),
			'schedule' => array('verify'),
			'help' => array('forgot_password','recover_password','reset_password', 
                            'patient_registration', 'confirm_registration', 'new_patient_reg', 'account_created')
		);
		
	}
	
	
	public static function isLocalHost()
	{
		$local = array('localhost', '127.0.0.1');

		if(isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], $local)){
		    return true;
		}
		
	}
	
	/**
	 * 
	 * Verify if an action has public access
	 * 
	 * @param unknown_type $controller
	 * @param unknown_type $action
	 */
	public function guestActionHasAccess($controller,$action)
	{
		$controllers = self::guestControllers();
		
    if ($controller == 'api') {
      return true;
    }
    
		if(isset($controllers[$controller]) && in_array($action,$controllers[$controller])) {
			
			return true;
		}
	}
	
	function isAjaxRequest()
	{
		$headers = array();
		if(function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
		} else {
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
				$headers['X-Requested-With'] = $_SERVER['HTTP_X_REQUESTED_WITH'];
			}
		}
		
		if(isset($headers['X-Requested-With']) && $headers['X-Requested-With']=='XMLHttpRequest') {
			return true;
		}
	}
}
