<?php 
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));

class XlinkImportShell extends Shell {
	var $uses = array('xlink'); 
	 
	function main() 
	{	
		$xlinkCon = $this->xlink->connectXlink();
		if($xlinkCon)
			$this->xlink->import();
	}

}

?>