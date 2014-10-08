<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_2610($db){

	
	$sql ="
		SELECT
			`encounter_type_id`
		FROM
			`practice_encounter_types`
		WHERE
			`name` = 'Phone'
	";
	
	$result = $db->query($sql);
	
	if( $db->errno ) {
		return 'Failed to query Phone encounter type '.$db->error;		
	}		

	
	$encounterType = $result->fetch_assoc();
	
	if (!$encounterType) {
		return ' No Phone encounter type ' . $db->error;		
	}
	
	$sql = "
		UPDATE
			`practice_encounter_tabs`
		SET
			`hide` = '0'
		WHERE
			`encounter_type_id` = '". $encounterType['encounter_type_id']."'
		AND 
			`tab` = 'Plan'
	";
	
	$result = $db->query($sql);
	
	if( $db->errno ) {
		return 'Failed to update default Phone Encounter tab '.$db->error;		
	}			
	return true;

}


$errorMessage = db_2610($db);


