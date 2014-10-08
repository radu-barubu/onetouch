<?php

App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'Dosespot_XML_API', array( 'file' => 'Dosespot_XML_API.php' ));
App::import('Lib', 'EMR_Roles', array( 'file' => 'EMR_Roles.php' ));
App::import('Core', 'Controller');
App::import('Lib', 'email');

class DosespotPatientIDShell extends Shell
{

	var $uses = array('PatientDemographic'); 
	function main() 
	{
		if($this->args)
		{
			$patient_id = @$this->args[1];
			$data = @unserialize($this->args[2]); //convert serialized to array
			if($patient_id && is_array($data)) 
			{
				$this->out('getting Dosespot ID for ' . $patient_id);	
				$dosespot_patient_id = ClassRegistry::init('Dosespot_XML_API')->getDosespotPatientID($patient_id, $data);
				//cakelog::write('dosespot','running dosespot shell for patient_id='.$patient_id. ' returned dosespot_patient_id='.$dosespot_patient_id );
				$data2 = array();
				$data2['PatientDemographic']['patient_id'] = $patient_id;
				$data2['PatientDemographic']['modified_timestamp'] = __date("Y-m-d H:i:s");
				$data2['PatientDemographic']['dosespot_patient_id'] = $dosespot_patient_id;
				$this->PatientDemographic->save($data2);				
        		}
        		else
        		{
        			$this->out('not enough Args were provided, exiting.');
        		}
   		} 
   		else
   		{
   			$this->out('No command was given. try again');
   		}
	}
}

?>
