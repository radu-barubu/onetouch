<?php 
App::import('Core', 'Model');
App::import('Core', 'Controller');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Lib', 'email');

class KareoBillShell extends Shell {
	var $uses = array('kareo'); 
	 
	function main() 
	{	
	  if($this->args)
	  {
		$patient_id = $this->args[1]; //passed by command line
		$encounter_id = $this->args[2];
		//$client = $this->kareo->client();
		//if($client)
			$this->kareo->bill($patient_id, '', $encounter_id);

	  }
	}

}

?>
