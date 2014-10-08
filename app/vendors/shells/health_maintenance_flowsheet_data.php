<?php 
App::import('Core', 'Model');
App::import('Core', 'Controller');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));

class HealthMaintenanceFlowsheetDataShell extends Shell {
	var $uses = array('HealthMaintenanceFlowsheetData'); 
	 
	function main() 
	{	
          $this->HealthMaintenanceFlowsheetData->beginHMReport();
	}
}

?>
