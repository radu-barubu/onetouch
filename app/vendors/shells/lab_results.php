<?php

App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Lib', 'Emdeon_HL7', array( 'file' => 'Emdeon_HL7.php' ));
App::import('Lib', 'EMR_Roles', array( 'file' => 'EMR_Roles.php' ));
App::import('Core', 'Controller');
App::import('Lib', 'email');
App::import('Core','Sanitize');

class LabResultsShell extends Shell
{
	function main() 
	{
		global $argv;
		if(!isset($this->args[1])) {
		 echo "Not enough flags. \n\t Usage: $argv[0] -app './app' lab_results ".$this->args[0]." {batch_download=1|0} |  {date_from=YYYY-MM-DD date_to=YYYY-MM-DD} | {reset_status=1|0}} \n";
		 exit;
		}
		
		$batch_download = 0;
		$created= 0;		
		$reset_status = 0;
		$date_from = '';
		$date_to = '';

		$validParams = array('batch_download', 'date_from', 'date_to', 'reset_status');
		
		foreach ($this->args as $param) {
			
			$map = explode('=', $param);
			
			if (!in_array($map[0], $validParams)) {
				continue;
			}
			
			${$map[0]} = isset($map[1]) ? $map[1] : 1 ;
		}
		
		$batch_download = intval($batch_download) ? true : false;
		$reset_status = intval($reset_status) ? true : false;

		if ($date_from && $date_to) {
			echo "searching date range from: [".$date_from. "] to: [" .$date_to. "]\n";
		} else {
			echo "warning! no date ranges were specified, this will now search from Jan 1 - Dec 31 of this entire year! \n";
		}
		//if Emdeon labs are enabled
		$practice_settings = ClassRegistry::init('PracticeSetting')->getSettings();
        if($practice_settings->labs_setup == 'Standard'){
		  echo "no Emdeon labs on this account \n";
		  exit;
		}
	
		
		if ($reset_status) {
			ClassRegistry::init('EmdeonLabResult')->resetDownloadStatus($date_from, $date_to);
		}
		
		$created = ClassRegistry::init('EmdeonLabResult')->sync($batch_download, $date_from, $date_to);
        
        if($created > 0)
        {
            echo $created . " Lab Result(s) Imported.\n";
        }
        else
        {
            echo "No Lab Result Available.\n";
        }
	}
}

?>
