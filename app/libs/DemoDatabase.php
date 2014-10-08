<?php

class DemoDatabase {
	/**
	* Only enable on certain db name
	* 
	* @return allowed or not
	*/
	public static function is_db_allowed(){
		$allowed_database = "beta_demo"; //exact db name or just prefix
		$database_name = ConnectionManager::getDataSource('default')->config['database'];
		if( strlen($database_name) >= strlen($allowed_database) &&
				substr($database_name, 0, strlen($allowed_database)) == $allowed_database ){
			return true;	
		}
		    	
		// Check for dev account, allow db resets for testing
		$devPath = dirname(dirname(__FILE__)).'/config/dev.flag';
		$isDev = file_exists($devPath) ? true : false;
		if( $isDev ) return true;

		// Default is not to allow a db reset
		return false;
	}
	
	/**
	* reset entire system to demo database
	* 
	* @return number of queries executed 
	*/
	public static function reset_demo_database(){
		$filename = CONFIGS.'sql'.DS.'beta_demo.sql';
		$result = SQLParser::reset_database($filename);
		$filename = CONFIGS.'sql'.DS.'db_encrypt_demo.sql';
		SQLParser::dbschema_do_update($filename);
		return $result;
	}	 
	
	/**
	* create appointments
	* 
	* @return number of appointments created
	*/
	public static function populate_appointments(){
		$created = 0;
		
		//for six weeks - working days only
		$days_enum = array(20, 20, 20, 20, 20, 18, 18, 18, 18, 18, 16, 16, 16, 16, 16, 14, 14, 14, 14, 14, 12, 12, 12, 12, 12, 8, 8, 8, 4, 2);
		$reason_for_visits = array('Fatigue', 'Frequent urination', 'Stuffy/congested nose',
			'Toothache', 'Tongue swelling', 'Backpain', 'Ear pain', 'Hearing loss', 'Fever',
			'Bloody urine', 'Diarrhea', 'Kidney stones', 'Wheezing', 'Headache', 'Soothing',
			'Broken leg', 'Follow Up', 'New Visit', 'Cardiac follow up', 'Cramps', 'Sick',
			'Strep', 'Sore throat', 'leg rash', 'Congestion', 'Testicular Pin', 'Stomach upset',
			'Asthma', 'Flu', 'Injection');
		$time_data = array(
			array('starttime' => '07:00:00', 'duration' => '30', 'endtime' => '07:30:00'), 
			array('starttime' => '07:00:00', 'duration' => '60', 'endtime' => '08:00:00'), 
			array('starttime' => '07:56:00', 'duration' => '0', 'endtime' => '07:56:00'), 
			array('starttime' => '07:58:00', 'duration' => '0', 'endtime' => '07:58:00'), 
			array('starttime' => '08:00:00', 'duration' => '15', 'endtime' => '08:15:00'), 
			array('starttime' => '08:00:00', 'duration' => '30', 'endtime' => '08:30:00'), 
			array('starttime' => '08:30:00', 'duration' => '30', 'endtime' => '09:00:00'), 
			array('starttime' => '08:37:00', 'duration' => '60', 'endtime' => '09:37:00'), 
			array('starttime' => '09:00:00', 'duration' => '15', 'endtime' => '09:00:00'), 
			array('starttime' => '09:00:00', 'duration' => '45', 'endtime' => '09:45:00'), 
			array('starttime' => '09:30:00', 'duration' => '45', 'endtime' => '10:15:00'), 
			array('starttime' => '09:37:00', 'duration' => '45', 'endtime' => '10:22:00'), 
			array('starttime' => '10:00:00', 'duration' => '30', 'endtime' => '10:30:00'), 
			array('starttime' => '10:30:00', 'duration' => '20', 'endtime' => '10:50:00'), 
			array('starttime' => '10:31:00', 'duration' => '60', 'endtime' => '11:31:00'), 
			array('starttime' => '10:36:00', 'duration' => '20', 'endtime' => '10:56:00'), 
			array('starttime' => '10:40:00', 'duration' => '15', 'endtime' => '10:55:00'), 
			array('starttime' => '10:45:00', 'duration' => '12', 'endtime' => '10:57:00'), 
			array('starttime' => '11:00:00', 'duration' => '30', 'endtime' => '11:30:00'), 
			array('starttime' => '12:30:00', 'duration' => '15', 'endtime' => '12:45:00'), 
			array('starttime' => '13:00:00', 'duration' => '15', 'endtime' => '13:15:00'), 
			array('starttime' => '14:00:00', 'duration' => '30', 'endtime' => '14:30:00'), 
			array('starttime' => '14:01:00', 'duration' => '30', 'endtime' => '14:31:00'), 
			array('starttime' => '14:30:00', 'duration' => '20', 'endtime' => '14:50:00'), 
			array('starttime' => '14:42:00', 'duration' => '20', 'endtime' => '15:02:00'), 
			array('starttime' => '15:15:00', 'duration' => '30', 'endtime' => '15:45:00'), 
			array('starttime' => '16:00:00', 'duration' => '30', 'endtime' => '16:30:00'), 
			array('starttime' => '16:15:00', 'duration' => '15', 'endtime' => '16:30:00'), 
			array('starttime' => '18:00:00', 'duration' => '45', 'endtime' => '18:45:00'), 
			array('starttime' => '19:00:00', 'duration' => '30', 'endtime' => '19:30:00'), 
			array('starttime' => '19:00:00', 'duration' => '60', 'endtime' => '20:00:00'), 
			array('starttime' => '19:15:00', 'duration' => '45', 'endtime' => '20:00:00'), 
			array('starttime' => '20:15:00', 'duration' => '30', 'endtime' => '20:45:00'), 
			array('starttime' => '21:43:00', 'duration' => '30', 'endtime' => '22:13:00')
		);
		
		$locations = array();
		$PracticeLocation =& ClassRegistry::init('PracticeLocation');
		$PracticeLocation->recursive = -1;
		$practice_locations = $PracticeLocation->find('all', array('fields' => array('PracticeLocation.location_id')));
		foreach($practice_locations as $practice_location){ $locations[] = $practice_location['PracticeLocation']['location_id']; }
		
		$providers = array();
		$UserAccount =& ClassRegistry::init('UserAccount');
		$UserAccount->recursive = -1;
		$provider_items = $UserAccount->find('all', array(
			'fields' => array('UserAccount.user_id'),
			'conditions' =>	 array('UserAccount.role_id	 ' => array(EMR_Roles::PHYSICIAN_ROLE_ID, EMR_Roles::PHYSICIAN_ASSISTANT_ROLE_ID, EMR_Roles::NURSE_PRACTITIONER_ROLE_ID ))
		));
		foreach($provider_items as $provider_item){
			$providers[] = $provider_item['UserAccount']['user_id'];
		}
		
		$patients = array();
		$PatientDemographic =& ClassRegistry::init('PatientDemographic');
		$PatientDemographic->recursive = -1;
		$patient_items = $PatientDemographic->find('all', array('fields' => array('PatientDemographic.patient_id')));
		foreach($patient_items as $patient_item){
			$patients[] = $patient_item['PatientDemographic']['patient_id'];
		}
		
		$visit_types = array();
		$ScheduleType =& ClassRegistry::init('ScheduleType');
		$ScheduleType->recursive = -1;
		$visit_type_items = $ScheduleType->find('all', array('fields' => array('ScheduleType.appointment_type_id')));
		foreach($visit_type_items as $visit_type_item){
			$visit_types[] = $visit_type_item['ScheduleType']['appointment_type_id'];
		}
		
		$ScheduleCalendar =& ClassRegistry::init('ScheduleCalendar');
		$EncounterMaster =& ClassRegistry::init('EncounterMaster');
		$EncounterChiefComplaint =& ClassRegistry::init('EncounterChiefComplaint');
		
		$day_count = 0;
		foreach($days_enum as $days_item){
			$current_date = __date("Y-m-d", strtotime($day_count . " weekdays"));
			for($i = 0; $i < $days_item; $i++){
				$data = array();
				$data['ScheduleCalendar']['color'] = '';
				$data['ScheduleCalendar']['location'] = $locations[rand(0,(count($locations)-1))];
				$data['ScheduleCalendar']['patient_id'] = $patients[rand(0,(count($patients)-1))];
				$data['ScheduleCalendar']['reason_for_visit'] = $reason_for_visits[rand(0,(count($reason_for_visits)-1))];
				$data['ScheduleCalendar']['date'] = $current_date;
				
				$current_time = $time_data[rand(0,(count($time_data)-1))];
				$data['ScheduleCalendar']['starttime'] = $current_time['starttime'];
				$data['ScheduleCalendar']['duration'] = $current_time['duration'];
				$data['ScheduleCalendar']['endtime'] = $current_time['endtime'];
				
				$data['ScheduleCalendar']['provider_id'] = $providers[rand(0,(count($providers)-1))];
				$data['ScheduleCalendar']['referred_by'] = '';
				$data['ScheduleCalendar']['visit_type'] = $visit_types[rand(0,(count($visit_types)-1))];
				$data['ScheduleCalendar']['room'] = '';
				$data['ScheduleCalendar']['status'] = '';
				$data['ScheduleCalendar']['approved'] = '';
				$data['ScheduleCalendar']['referred_by'] = '0';
				$data['ScheduleCalendar']['modified_timestamp'] =  __date("Y-m-d");
				$data['ScheduleCalendar']['modified_user_id'] = (int)@$_SESSION['UserAccount']['user_id'];
				
				$ScheduleCalendar->create();
				$ScheduleCalendar->save($data);
				
				$created++;
				
				$calendar_id = $ScheduleCalendar->getLastInsertID();
				$EncounterMaster->newEncounter = false;
				$encounter_id = $EncounterMaster->getEncounter($calendar_id, $data['ScheduleCalendar']['patient_id'], $data['ScheduleCalendar']['modified_user_id']);
				
				if ($EncounterMaster->newEncounter) {
					$EncounterChiefComplaint->addItem($data['ScheduleCalendar']['reason_for_visit'], $encounter_id, $data['ScheduleCalendar']['modified_user_id']);
				}
				
			}
			$day_count++;
		}
		return $created;
	}
}


?>