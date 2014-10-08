<?php

App::import('Core', array('Model','Controller','Router'));
App::import('Lib', 'LazyModel', array('file' => 'LazyModel.php'));
App::import('Lib', 'Emdeon_XML_API', array('file' => 'Emdeon_XML_API.php'));
App::import('Lib', 'site');
App::import('Lib', 'pdfReport', array('file' => 'pdfReport.php'));
App::import('Lib', 'EMR_Roles', array('file' => 'EMR_Roles.php'));
App::import('Lib', 'EMR_Account', array('file' => 'EMR_Account.php'));

class GetSuperbillNotesShell extends Shell {

	var $uses = array('EncounterMaster');

	function main() {
		if(empty($this->args[1])) {
		  echo "\n usage: php ./cake/console/cake.php -app ./app/ get_superbill_notes \$DB \$OUTPUT_DIRECTORY \$ENCOUNTER_DATE(optional) \n";
		  exit;
		}
		if(!is_dir($this->args[1])) {
		  echo "ATTENTION: output path was not found. try again \n";
		  exit;
		} else {
		 $outputpath=$this->args[1];
		}
	 	if(!empty($this->args[2])) {
		  $date=$this->args[2];
		} else {
		  $date=date('Y-m-d'); //default to today
		}
    
    $user = ClassRegistry::init('UserAccount')->find('first', array(
        'conditions' => array(
            'UserAccount.role_id' => EMR_Roles::SYSTEM_ADMIN_ROLE_ID,
            'UserAccount.status' => 1,
        ),
    ));

    $_SESSION = array('UserAccount' => $user['UserAccount']);
    App::import('Controller', 'Encounters');
    App::import('Component', 'Session');
    $encounter = new EncountersController();
    $encounter->loadModel('EncounterMaster');
    $encounter->user_id = $user['UserAccount']['user_id'];
    $encounter->Session = new SessionComponent();
    $encounter->params['url'] = array();
    $encounter->beforeFilter();
    
			$pr=ClassRegistry::init('PracticeSetting')->getSettings();
	    		echo "\nGenerating Superbill PDFs from  ".$date;
			//this will get all notes - Open or Closed!
    	     		$items=$this->EncounterMaster->find('all', 
				array('conditions' => array('EncounterMaster.encounter_date LIKE' => $date.'%'), 
					'recursive' => -1, 
					'fields' => array('EncounterMaster.encounter_id','EncounterMaster.visit_summary_view_format')));

			foreach ($items as $item) { 
			  $encounter_id = $item['EncounterMaster']['encounter_id'];
			     print "\nEncounter: ".$encounter_id. "\n";
        			$encounter->params['named']['encounter_id'] = $encounter_id;
				$data =  $encounter->requestAction('/encounters/superbill_print/encounter_id:'. $encounter_id .'/cli:1', array('return'));
        			$sfile = 'encounter_'. $encounter_id . '_superbill.pdf';
        			site::write(pdfReport::generate($data), $outputpath.'/'.$sfile);
			}
    	  		echo "...done\n";
		
	}

}

?>
