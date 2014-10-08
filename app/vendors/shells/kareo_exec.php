<?php 
App::import('Core', 'Model');
App::import('Core', 'Controller');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Lib', 'email');

class KareoExecShell extends Shell {
	var $uses = array('kareo'); 
	 
	function main() 
	{	
		//$client = $this->kareo->client();
		//if($client) 
		//{		
                        $this->kareo->importSchedule();
                        $this->kareo->import();
                        $this->kareo->deleteSchedule();
		//}
	}

}

?>
