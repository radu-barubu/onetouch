<?php

App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Lib', 'UploadSettings', array( 'file' => 'UploadSettings.php' ));
App::import('Lib', 'Visit_Summary', array( 'file' => 'Visit_Summary.php' ));
App::import('Lib', 'EMR_Account', array( 'file' => 'EMR_Account.php' ));
App::import('Lib', 'EMR_Roles', array( 'file' => 'EMR_Roles.php' ));
App::import('Lib', 'patient', array( 'file' => 'patient.php' ));
App::import('Core','Sanitize');
App::import('Core', 'Controller');
//App::Import('ConnectionManager');

class OfficeBackupShell extends Shell
{
	public $uses = array('EncounterMaster','PatientDemographic','PracticeSetting','EncounterPhysicalExamImage','PatientDocument','PatientRadiologyResult'); 

	function main() 
	{
	   $this->date_from = '';
	   $this->date_to = '';
	   //  support for date range 
	   if (isset($this->args[1]) && isset($this->args[2]) ) {
		$this->date_from = date('Y-m-d', strtotime($this->args[1]));
		$this->date_to = date('Y-m-d', strtotime($this->args[2]));
		echo "date range from: [".$this->date_from. "] to: [" .$this->date_to. "]\n";
  	   } else {
		echo "doing last 24 hours (default). you may also specify date range: {date_from} {date_to} \n";
	   }

	   $practiceSetting = $this->PracticeSetting->getSettings();
	   $this->customer= 'remote_'.$practiceSetting->practice_id;
	   $this->cpath ="/home/".$this->customer."/outgoing";	
	   //make sure output dir exists
	   if(!is_dir($this->cpath)) {
 	    echo "WARNING: customer path ".$this->cpath." was not found. aborting... \n";
	    exit;
	   }
	  $this->upload_settings = UploadSettings::getUploadSettings();
	  $this->date = date("Y-m-d", strtotime(date('Y-m-d') . ' -1 day'));
	  //process 
	  $this->copyEncounters();
	  $this->copyPictures();
	  $this->copyDocuments();
	  //housekeeping
	  $this->cleanUp();
	}

	/*
	*	find & copy over patient encounters
	*/
	function copyEncounters() {
		if($this->date_from && $this->date_to) {
		   $conditions =  array( 'EncounterMaster.modified_timestamp >=' => $this->date_from , 
					 'EncounterMaster.modified_timestamp <=' => $this->date_to ,
					 'EncounterMaster.encounter_status' => 'Closed' );
		} else {
		   $conditions =  array( 'EncounterMaster.modified_timestamp >=' => $this->date ,
                                         'EncounterMaster.encounter_status' => 'Closed' );
		}
		$items=$this->EncounterMaster->find('all', array(
							'conditions' => $conditions,
							'fields' => array('encounter_id','patient_id','visit_summary_view_format','encounter_date'),
							'recursive' => -1 
							));
		foreach($items as $item) {
			echo "\n encounter: ".$item['EncounterMaster']['encounter_id']."\n ";
			$snapShots = Visit_Summary::getSnapShot($item['EncounterMaster']['encounter_id'], 'pdf');
			$format= (!empty($item['EncounterMaster']['visit_summary_view_format']))? $item['EncounterMaster']['visit_summary_view_format']: 'full'; // TO DO -- pull useraccount settings 
			$targetFile = $snapShots[$format];
			//make file name understandable to end-user by putting encounter date
			$encounter_date= date('m-d-Y', strtotime($item['EncounterMaster']['encounter_date']));
			$file = 'encounter_' . $encounter_date.'.pdf';
			$f=file_get_contents($targetFile);
			$tmpfile="/tmp/".$file;
			file_put_contents( $tmpfile , $f);
			echo "\nworking: ".$file." -> ".$format."\n";
			$this->pushOut($item['EncounterMaster']['patient_id'], "/tmp/", $file, "encounters", "");
			sleep(1);
			unlink( $tmpfile );
		}
	}

	/*
	*	find & copy over patient photos
	*/
	function copyPictures() {
                if($this->date_from && $this->date_to) {
                   $conditions =  array( 'EncounterPhysicalExamImage.modified_timestamp >=' => $this->date_from ,
                                         'EncounterPhysicalExamImage.modified_timestamp <=' => $this->date_to );
                } else {
                   $conditions =  array( 'EncounterPhysicalExamImage.modified_timestamp >=' => $this->date );
                }
		$items = $this->EncounterPhysicalExamImage->find('all', array('conditions' => $conditions, 'fields' => array('EncounterPhysicalExamImage.patient_id','EncounterPhysicalExamImage.image','EncounterPhysicalExamImage.encounter_id', 'EncounterPhysicalExamImage.modified_timestamp', 'EncounterPhysicalExamImage.comment', 'EncounterMaster.patient_id'), 
			));

		foreach($items as $i) {	
			$patient_id = (!empty($i['EncounterPhysicalExamImage']['patient_id']))? $i['EncounterPhysicalExamImage']['patient_id']: $i['EncounterMaster']['patient_id'];
			$encounter_id = $i['EncounterPhysicalExamImage']['encounter_id'];
			$file= $i['EncounterPhysicalExamImage']['image'];

			//account for new file system design - ticket #2597
			$dir_path= (is_file( $this->upload_settings['encounters'] . $file )) ? $this->upload_settings['encounters'] : $this->upload_settings['patients']. $patient_id . DS . 'images' . DS . $encounter_id . DS;

			//make file name human readable
			$comment="";
			$picture_date= date('m-d-Y_H_i', strtotime($i['EncounterPhysicalExamImage']['modified_timestamp']));
			if($i['EncounterPhysicalExamImage']['comment']) {
			  $comment= Sanitize::paranoid($i['EncounterPhysicalExamImage']['comment'], array('.', ' '));
			  $comment = preg_replace('/\s+/', '_', $comment);
			}
			$new_filename = (!empty($comment)) ? $picture_date.'_'.$comment:$picture_date;
			$this->pushOut($patient_id, $dir_path, $file, "pictures", $new_filename);
		}
	}

	/*
	*	find & copy over patient documents
	*/
	function copyDocuments() {
		//standard patient documents
                if($this->date_from && $this->date_to) {
                   $conditions =  array( 'PatientDocument.modified_timestamp >=' => $this->date_from ,
                                         'PatientDocument.modified_timestamp <=' => $this->date_to );
                } else {
                   $conditions =  array( 'PatientDocument.modified_timestamp >=' => $this->date );
                }
		$items= $this->PatientDocument->find('all', array('conditions'  => $conditions));
		foreach($items as $i) {
			$patient_id=$i['PatientDocument']['patient_id'];
			$file=$i['PatientDocument']['attachment'];

			//account for new file system design - ticket #2597
			$dir_path= (is_file( $this->upload_settings['patients'] . $file )) ? $this->upload_settings['patients'] :  $this->upload_settings['patients'] . $patient_id . DS . 'documents' . DS ;			

                        //make file name human readable
			$comment="";
                        $doc_date= date('m-d-Y', strtotime($i['PatientDocument']['service_date']));
                        if($i['PatientDocument']['document_name']) {
                          $comment= Sanitize::paranoid($i['PatientDocument']['document_name'], array('.','_',' '));
                          $comment = preg_replace('/\s+/', '_', $comment);
                        }
			//get file type?
			$ext = pathinfo($i['PatientDocument']['attachment']);
                        $new_filename = (!empty($comment)) ? $doc_date.'_'.$comment. '.'.$ext['extension']:$doc_date. '.'.$ext['extension'];

			$this->pushOut($patient_id, $dir_path, $file, "documents", $new_filename);
		}

		//radiology documents/reults
                if($this->date_from && $this->date_to) {
                   $conditions2 =  array( 'PatientRadiologyResult.modified_timestamp >=' => $this->date_from ,
                                         'PatientRadiologyResult.modified_timestamp <=' => $this->date_to );
                } else {
                   $conditions2 =  array( 'PatientRadiologyResult.modified_timestamp >=' => $this->date );
                }
		$ritems=$this->PatientRadiologyResult->find('all', array('conditions' => $conditions2 ));
		foreach($ritems as $r) {
			$patient_id = $r['PatientRadiologyResult']['patient_id'];
			$file=$r['PatientRadiologyResult']['attachment'];
			$dir_path=$this->upload_settings['patients'] . $patient_id . DS . 'radiology' . DS . '0' . DS;

                        //make file name human readable
			$comment="";
                        $rdoc_date= date('m-d-Y', strtotime($r['PatientRadiologyResult']['modified_timestamp']));
                        if($r['PatientRadiologyResult']['test_name']) {
                          $comment= Sanitize::paranoid($r['PatientRadiologyResult']['test_name'], array('.', ' '));
                          $comment = preg_replace('/\s+/', '_', $comment);
                        }
                        $rnew_filename = (!empty($comment)) ? $rdoc_date.'_'.$comment:$rdoc_date;
			$this->pushOut($patient_id, $dir_path, $file, "documents", $rnew_filename);
		}

	}

	/*
	* get patient info to create dir structure
	*/
	function getPatientInfo($patient_id) {
		return $this->PatientDemographic->find('first', array('conditions' => array('PatientDemographic.patient_id' => $patient_id), 'fields' => array('first_name','middle_name','last_name','dob'), 'recursive' => -1));

		
	}

	/*
	*  take processed data and copy over for pickup later by customer's office backup machine
	*/
	function pushOut($patient_id, $dir_path, $source_file, $output_dir, $destination_file) {
	  //make sure SOURCE file exists
	  if(is_file( $dir_path . $source_file  )) {
		$pts=$this->getPatientInfo($patient_id);
		$dob = __date('m-d-Y', strtotime($pts['PatientDemographic']['dob']));
		$middle = (!empty($pts['PatientDemographic']['middle_name']))? '_'.$this->formatNames($pts['PatientDemographic']['middle_name']):'';
		$tp=$this->cpath.'/'.$this->formatNames($pts['PatientDemographic']['last_name']).'_'.$this->formatNames($pts['PatientDemographic']['first_name']).$middle.'_('.$dob.')';
echo "\n ".$tp;
		//create main cust dir
		if(!is_dir( $tp )) {
			mkdir( $tp, 0700 );
			chown( $tp, $this->customer );
		}
		// create OUTPUT cust file type dir
                if(!is_dir( $tp .'/' .$output_dir )) {
                        mkdir( $tp .'/' .$output_dir, 0700 );
			chown( $tp .'/' .$output_dir, $this->customer );
                }
		$destination_file = (!empty($destination_file)) ? $destination_file:$source_file;
		$xout= $tp . '/'.$output_dir. '/'.$destination_file;
		// copy to destination
		copy($dir_path . $source_file, $xout );
		//set perms
		chown( $xout, $this->customer );

	  }

	}
	/*
	* flush out old directories > 30 days ago that have no files inside of them (which means files were already picked up)
	*/
	function cleanUp() { 
	  system("find ".$this->cpath." -type d -empty -mtime 30 -delete"); //easier in bash :-)
	}

	/*
	* format patient names to directory format
	*/
	function formatNames($str) {
		$str=trim($str); //take off spaces at edges
		$str=Sanitize::paranoid($str, array(' '));//remove funky stuff
		return preg_replace('/\s+/', '_', $str); //change space to underscore
	}
}



?>
