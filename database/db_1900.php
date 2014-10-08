<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_1900($db){

	$sql ="
		SELECT
			`menu_id`
		FROM
			`system_menus`
		WHERE
			`menu_controller` = 'administration'
			AND
			`menu_action` = 'users'
	";
	
	$result = $db->query($sql);
	
	if( $db->errno ) {
		return 'Failed to fetch User Administration menu '.$db->error;		
	}		
	
	
	$userMainMenu = $result->fetch_assoc();
	
	// Update menu for user location
	
	$sql = "
		UPDATE
			`system_menus`
		SET
			`menu_inherit` = '" . $userMainMenu['menu_id'] . "',
			`system_admin_only`	 = '0'
		WHERE
			`menu_controller` = 'administration'
			AND
			`menu_action` = 'user_locations'
	";
	
	$result = $db->query($sql);
	
	if( $db->errno ) {
		return 'Failed to update User Location Menu '.$db->error;		
	}		
	
	return true;
}


$errorMessage = db_1900($db);


