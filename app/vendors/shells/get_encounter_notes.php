<?php

App::import('Core', 'Model');
App::import('Core', 'Controller');
App::import('Lib', 'LazyModel', array('file' => 'LazyModel.php'));
App::import('Lib', 'Emdeon_XML_API', array('file' => 'Emdeon_XML_API.php'));
App::import('Lib', 'site');
App::import('Lib', 'pdfReport', array('file' => 'pdfReport.php'));

class GetEncounterNotesShell extends Shell {

	var $uses = array('EncounterMaster');

	function main() {
		if(empty($this->args[1])) {
		  echo "\n usage: php ./cake/console/cake.php -app ./app/ get_encounter_notes \$DB \$OUTPUT_DIRECTORY \$ENCOUNTER_DATE(optional) \n";
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
			$pr=ClassRegistry::init('PracticeSetting')->getSettings();
	    		echo "\nGetting Visit Summary PDFs from  ".$date;
			//this will get all notes - Open or Closed!
    	     		$items=$this->EncounterMaster->find('all', 
				array('conditions' => array('EncounterMaster.encounter_date LIKE' => $date.'%'), 
					'recursive' => -1, 
					'fields' => array('EncounterMaster.encounter_id','EncounterMaster.visit_summary_view_format')));

			$path='/CUSTOMER_DATA/CUSTOMER_DATA/'.$pr->practice_id.'/encounters';
			foreach ($items as $item) { 
			  $encounter_id = $item['EncounterMaster']['encounter_id'];
			  $type=($item['EncounterMaster']['visit_summary_view_format'])?$item['EncounterMaster']['visit_summary_view_format']:'full';
			  if(is_dir($path. '/'.$encounter_id)) {
			     print "\nEncounter dir found: ".$encounter_id;
				//  snapshot_full_encounter_576_summary.pdf
				$sfile='snapshot_'.$type.'_encounter_'.$encounter_id.'_summary.pdf';
				$source_file=$path. '/'.$encounter_id.'/'.$sfile;
				$tmp_file = 'encounter_' . $encounter_id . '_summary.tmp';
				$source_file2=$path. '/'.$encounter_id.'/'.$tmp_file;
				if(file_exists($source_file)) {
				   copy($source_file, $outputpath.'/'.$sfile);
				   print "\n-- $sfile file was copied!";
				} else if (file_exists($source_file2)) { // encounter must still be open, so grab tmp and generate PDF
				   print "\n --  generating a PDF since tmp file was only found ......";
				   $report = file_get_contents($source_file2);
				   site::write(pdfReport::generate($report), $outputpath.'/'.$sfile);
				   print "...done \n";
				} else {
				   print "\n    ===> PDF was not found for ".$encounter_id;
				}
			  }
			}
    	  		echo "\n\n...done\n";
		
	}

}

?>
