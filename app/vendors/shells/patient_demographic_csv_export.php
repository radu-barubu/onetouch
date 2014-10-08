<?php
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Core', 'Controller');


class PatientDemographicCsvExportShell extends Shell
{

	var $uses = array('PatientDemographic'); 
	function main() 
	{
		if($this->args)
		{     
			$file_id = @$this->args[1];	
		$this->PatientDemographic->exportPatientDemographics($file_id);	
		}
	
	}
}

?>