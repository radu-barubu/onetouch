<?php 
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));

class XlinkShell extends Shell {

	var $uses = array('xlink'); 
	 
	function main(){		
	}
	
	function startup(){
		$this->xlink->connectXlink();
	}
	
	function export(){
		$this->xlink->demographic();
	}
	
	function import(){
		$this->xlink->import();
	}
}

?>