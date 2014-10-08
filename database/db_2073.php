<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_2073($db){

	$defaultAcls = '{"2":"W","85":"NA","86":"NA","102":"W","11":"W","12":"W","13":"W","14":"W","15":"W","16":"W","17":"W","18":"W","19":"W","70":"W","71":"W","72":"W","73":"W","74":"W","4":"W","7":"W","106":"W","8":"W","10":"W","5":"W","6":"W","9":"W","80":"W","25":"W","26":"W","75":"W","107":"W","28":"W","29":"W","30":"W","68":"W","32":"W","33":"NA","34":"NA","84":"W","36":"NA","37":"NA","39":"NA","67":"NA","69":"NA","108":"W","42":"W","43":"W","47":"W","48":"NA","49":"W","52":"W","104":"R","53":"W","105":"W","54":"W"}';
	
	$sql ="
		UPDATE
			`user_roles`
		SET
			`default_acls` = '$defaultAcls'
		WHERE
			`role_desc` = 'Medical Assistant'
	";
	
	$result = $db->query($sql);
	
	if( $db->errno ) {
		return 'Failed update default acl for Medical Assistant Role '.$db->error;		
	}		
	
	/* $sql ="
		SELECT
			`role_id`
		FROM
			`user_roles`
		WHERE
			`role_desc` = 'Medical Assistant'
	";
	
	$result = $db->query($sql);
	
	if( $db->errno ) {
		return 'Failed retrieve Medical Assistant User Role '.$db->error;		
	}		
	
	$medAssistanRole = $result->fetch_assoc();
	*/
	$sql = "
		UPDATE
			`acls`
		SET
			`acl_write` = 1,
			`acl_read` = 0
		WHERE
			`role_id` = 7
		AND
			`menu_id` IN (12, 13, 14, 15, 16, 17, 18, 19, 70, 71, 72, 73, 74)
	";
	
	$result = $db->query($sql);
	
	if( $db->errno ) {
		return 'Failed to update acls for Medical Assistant User Role '.$db->error;		
	}			
	return true;

}


$errorMessage = db_2073($db);


