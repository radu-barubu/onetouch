<?php

App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array('file' => 'LazyModel.php'));
App::import('Lib', 'Emdeon_XML_API', array('file' => 'Emdeon_XML_API.php'));
App::import('Core', 'Controller');

class PatientDemographicCcrExportShell extends Shell {

	var $uses = array('PatientDemographic');

	function main() {
		echo "\n Generating Patient CCR Files ...";
		if ( $this->args ) {
			$file_id = @$this->args[1];
			$user_id = @$this->args[2];
			$url = @$this->args[3];
			
			$settings = ClassRegistry::init('PracticeSetting')->find('first');
			$this->settings = $settings['PracticeSetting'];

			$practice_id =$this->settings['practice_id'];
			$tmp=$this->settings['uploaddir_temp'];
			
			$folderName = rand();
			$tmp_folder=APP.'webroot/CUSTOMER_DATA/'.$practice_id.'/'.$tmp. '/'.$folderName;
			//make folder to put all files into
			mkdir( $tmp_folder );			
			
			$this->PatientDemographic->contain();
			
			$patients = $this->PatientDemographic->find('all', array(
				'fields' => array(
					'PatientDemographic.patient_id'
				),
			));

			$patientIds = Set::extract('/PatientDemographic/patient_id', $patients);

			foreach ( $patientIds as $pId ) {
				$output = $this->PatientDemographic->generatePatientCcr($pId, $user_id, $url);
				file_put_contents($tmp_folder . '/patient_ccr-' . $pId .'.xml' , $output);
				$ct++;
				
			}
			
			
			$zipCommand = 'zip -r ' . $file_id . ' ' . $folderName;
			chdir(APP.'webroot/CUSTOMER_DATA/'.$practice_id.'/'.$tmp);
			
			$zip = new Zipper();
			
			if ($zip->open($file_id, ZipArchive::CREATE)) {
				$zip->addDir($folderName);
			} else {
				echo "\n Failed to create zip archive. \n";
			}
			
			
			echo "\nDone!\n";
		}
	}


}


class Zipper extends ZipArchive {
   
	public function addDir($path) {
			$this->addEmptyDir($path);
			$nodes = glob($path . '/*');
			foreach ($nodes as $node) {
					if (is_dir($node)) {
							$this->addDir($node);
					} else if (is_file($node))  {
							$this->addFile($node);
					}
			}
	}
   
}

?>