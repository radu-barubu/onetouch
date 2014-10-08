<?php

App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array('file' => 'LazyModel.php'));
App::import('Lib', 'EMR_Groups', array('file' => 'EMR_Groups.php'));
App::import('Core', array('Router'));
App::import('Lib', 'Emdeon_XML_API', array('file' => 'Emdeon_XML_API.php'));
App::import('Lib', 'Emdeon_HL7', array('file' => 'Emdeon_HL7.php'));

class EmdeonOrdersQueueShell extends Shell {

	var $uses = array('EmdeonOrder');

	function main() {
	 	if(!empty($this->args[1]))
		{
			$emdeonOrderId = $this->args[1];
	    		echo "\nProcessing Emdeon Patient Order Queue... ";
    	     		$this->EmdeonOrder->processPatientOrderQueue($emdeonOrderId);
    	  		echo "done\n";
		}
	}

}

?>
