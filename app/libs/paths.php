<?php

class paths {
	
	private static $done = false;
	/**
	 * 
	 * This is called automatically, you may specify custom paths here
	 */
	function init()
	{
		if(self::$done) {
			return;
		}
		define('APP_FAX_RECEIVED_PATH', WWW_ROOT. 'documents/fax/received/');
		define('APP_FAX_SENT_PATH', WWW_ROOT. 'documents/fax/sent/');
		
		self::$done= true;
	}
	
	
	function uploadify()
	{
		return '../uploadify/';
	}
}