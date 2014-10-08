<?php 
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));

class XlinkExportShell extends Shell {

	var $uses = array('xlink'); 
	 
	function main() 
	{	
		$xlinkCon = $this->xlink->connectXlink();
		if($xlinkCon)
			$this->xlink->demographic();
	}
}

?>