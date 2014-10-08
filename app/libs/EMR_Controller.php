<?php

class EMR_Controller {
	
	/**
	 * 
	 * Checks if the system is installed, if not proceed for installation
	 */
	function run()
	{
		paths::init();
		
		App::import('CONFIG', 'DATABASE_CONFIG', array('file' => CONFIGS . 'database.php'));
		
		$config  = new DATABASE_CONFIG;
		
		App::import('Model', 'ConnectionManager', false);

		$db = ConnectionManager::getDataSource('default');
		$tables = $db->listSources();
		
		if(empty($tables)) {
			//$url = Router::url(array('controller'=>'install'),true);
		
			exit(header("location: installation"));
		}
		
	}
	
}