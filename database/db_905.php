<?php
/*
*
*	Included to process a database update via php code
*
* Code must set $errorMessage to true iff all went well, otherwise to an error message
*
*/

	function addExampleUsers($db){
		// Add example users
		// Returns an error message or true if no error
		
		// Remove the patient_demographics::test_patient_data 
		$sql = "ALTER TABLE patient_demographics DROP COLUMN test_patient_data;";
		$result = $db->query($sql);
		if( $db->errno )
			return 'Error dropping  from patient_demographics: '.$db->error;
		
		// Add patient_demographics::type_indicator, which is NULL for normal patients, 'E' for example patients
		$sql = "ALTER TABLE patient_demographics ADD COLUMN type_indicator CHAR(1) NULL AFTER xlink_chart_number;";
		$result = $db->query($sql);
		if( $db->errno )
			return 'Error adding type_indicator to patient_demographics: '.$db->error;
		
		// Get count of existing patients
		$sql = "SELECT COUNT(*) FROM patient_demographics;";
		$result = $db->query($sql);
		if( $db->errno )
			return 'Error getting count of patient_demographics: '.$db->error;
                if(is_object($result)) {
                 $row = $result->fetch_row();
                 $existingPatientCount =$row[0];
                } else {
                 $existingPatientCount =0;
                }		
		CakeLog::write('dbschema_update', "existingPatientCount: $existingPatientCount");
		
		// Get mrnStart
		$sql = "SELECT mrn_start FROM practice_settings;";
		$result = $db->query($sql);
		if( $db->errno )
			return 'Error getting mrn_start: '.$db->error;
		$row = $result->fetch_row();
		$mrnStart = (int)$row[0];
		CakeLog::write('dbschema_update', "mrnStart: $mrnStart");

		// Insert example patients
		$sql = "DELETE FROM patient_demographics WHERE patient_id < 6;";
		$result = $db->query($sql);
		if( $db->errno )
			return 'Error deleting test patients: '.$db->error;
		$sql = "INSERT INTO patient_demographics (patient_id, mrn, first_name, middle_name, last_name, gender, preferred_language, race, ethnicity, dob, `address1`, `city`,
			`state`, `zipcode`, `home_phone`, status, problem_list_none, modified_timestamp, modified_user_id, type_indicator) VALUES
			(1,0, DES_ENCRYPT('Janson'), DES_ENCRYPT('J'), DES_ENCRYPT('Example'), DES_ENCRYPT('M'), DES_ENCRYPT('English'),DES_ENCRYPT('White'), DES_ENCRYPT('Not Hispanic or Latino'),
			DES_ENCRYPT('2011-03-15'), DES_ENCRYPT('3345 Elm Street'), DES_ENCRYPT('Aurora'), DES_ENCRYPT('CO'), DES_ENCRYPT('80011'), DES_ENCRYPT('303-554-8889'),
			DES_ENCRYPT('Active'), NULL, NOW(), 9999, 'E'),
			(2,0, DES_ENCRYPT('Susan'), DES_ENCRYPT(''), DES_ENCRYPT('Example'), DES_ENCRYPT('F'),
			DES_ENCRYPT('English'),DES_ENCRYPT('White'), DES_ENCRYPT('Not Hispanic or Latino'), DES_ENCRYPT('1984-12-16'), DES_ENCRYPT('3345 16th Street'),
			DES_ENCRYPT('Fargo'), DES_ENCRYPT('NO'), DES_ENCRYPT('58104'), DES_ENCRYPT('701-454-8989'), DES_ENCRYPT('Active'), NULL, NOW(), 9999, 'E'),
			(3,0, DES_ENCRYPT('Alexander '), DES_ENCRYPT('C'), DES_ENCRYPT('Example'), DES_ENCRYPT('M'), DES_ENCRYPT('English'),DES_ENCRYPT('Black or African American'),
			DES_ENCRYPT('Not Hispanic or Latino'), DES_ENCRYPT('1988-11-11'), DES_ENCRYPT('3567 Maple Street'), DES_ENCRYPT('Elizabeth City'), DES_ENCRYPT('NC'),
			DES_ENCRYPT('27909'), DES_ENCRYPT('252-227-5887'), DES_ENCRYPT('Active'), 'none', NOW(), 9999, 'E'),
			(4,0, DES_ENCRYPT('Marianne'), DES_ENCRYPT('O'),
			DES_ENCRYPT('Example'), DES_ENCRYPT('F'), DES_ENCRYPT('English'),DES_ENCRYPT('Native Hawaiian or Other Pacific Islander'), DES_ENCRYPT('Not Hispanic or
			Latino'), DES_ENCRYPT('1940-05-27'), DES_ENCRYPT('6778 Kaulula Road'), DES_ENCRYPT('Honolulu'), DES_ENCRYPT('HA'), DES_ENCRYPT('96805'),
			DES_ENCRYPT('808-727-8755'), DES_ENCRYPT('Active'), NULL, NOW(), 9999, 'E'),
			(5,0, DES_ENCRYPT('Carl'), DES_ENCRYPT('K'), DES_ENCRYPT('Example'),
			DES_ENCRYPT('M'), DES_ENCRYPT('English'),DES_ENCRYPT('American Indian or Alaska Native'), DES_ENCRYPT('Not Hispanic or Latino'), DES_ENCRYPT('1989-06-24'),
			DES_ENCRYPT('354 Glacier Road'), DES_ENCRYPT('Anchorage'), DES_ENCRYPT('AL'), DES_ENCRYPT('99505'), DES_ENCRYPT('907-755-2189'), DES_ENCRYPT('Active'),
			NULL, NOW(), 9999, 'E');";
		$result = $db->query($sql);
		if( $db->errno )
			return 'Error adding example patients: '.$db->error;
		$sql = "UPDATE patient_demographics SET mrn = patient_id + $mrnStart WHERE type_indicator = 'E';";
		$result = $db->query($sql);
		if( $db->errno )
			return 'Error updating MRN for example patients: '.$db->error;
			
		// Check to mark example patients as Deleted, and set practice_settings::test_patient_data
		if( $existingPatientCount > 0 ){
			// Don't show them if the database already has users
			$sql = "UPDATE patient_demographics SET status = DES_ENCRYPT('Deleted') WHERE type_indicator = 'E';";
			$result = $db->query($sql);
			if( $db->errno )
				return 'Error marking example patients as deleted: '.$db->error;
			$sql = "UPDATE practice_settings SET test_patient_data = 'No';";
			CakeLog::write('dbschema_update', "disable test_patient_data");
		} else {
			// Show them if they are it
			$sql = "UPDATE practice_settings SET test_patient_data = 'Yes';";
			CakeLog::write('dbschema_update', "enable test_patient_data");
		}
		$result = $db->query($sql);
		if( $db->errno )
			return 'Error setting practice_settings::test_patient_data: '.$db->error;
		
		return true;	// All went well
	}
	$errorMessage = addExampleUsers($db);
?>