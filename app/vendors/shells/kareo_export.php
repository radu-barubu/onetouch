<?php 
App::import('Core', 'Model');
App::import('Core', 'Controller');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'email');
class KareoExportShell extends Shell {
	var $uses = array('kareo'); 
	 
	function main() 
	{	
		//$client = $this->kareo->client();
		//if($client)
			$this->kareo->export();
	}
}

?>
