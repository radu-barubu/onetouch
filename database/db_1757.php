<?php


/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_1757($db){

	$date = __date('Y-m-d H:i:s');
	
	$sql = "
		INSERT INTO `system_menus` (`menu_name`, `menu_controller`, `menu_action`, `menu_variation`, `menu_url`, `menu_options`, `menu_group`, `group_options`, `menu_parent`, `menu_inherit`, `menu_show`, `menu_show_roles`, `menu_enable_link`, `system_admin_only`, `modified_timestamp`, `modified_user_id`) VALUES
		('Lab Results Summary', 'patients', 'lab_result_summary', '', '', 'R,NA', 0, '[]', 3, 0, 1, 1, 1, 0, '" . $date . "', 0);
	";

		
	$db->query($sql);

	if( $db->errno ) {
		return 'Error adding system menu: '.$db->error;		
	}
		
	$lastInsertId = $db->insert_id;
		
	$sql = "
		INSERT INTO `system_menu_roles` (`menu_id`, `menu_description`, `menu_parent`, `menu_group`, `group_options`, `menu_options`) VALUES
		(" . $lastInsertId . ", 'Lab Results Summary', 2, 0, '[]', 'S,H');		
";

	$db->query($sql);

	if( $db->errno ) {
		return 'Error adding system menu role: '.$db->error;		
	}
	
	$sql = "
INSERT INTO `acls` (`menu_id`, `role_id`, `acl_read`, `acl_write`, `modified_timestamp`, `modified_user_id`) VALUES
(" . $lastInsertId .", 1, 0, 1, '" . $date ."', 1),
(" . $lastInsertId .", 2, 0, 0, '" . $date ."', 1),
(" . $lastInsertId .", 3, 0, 1, '" . $date ."', 1),
(" . $lastInsertId .", 4, 0, 1, '" . $date ."', 1),
(" . $lastInsertId .", 5, 0, 1, '" . $date ."', 1),
(" . $lastInsertId .", 6, 0, 1, '" . $date ."', 1),
(" . $lastInsertId .", 7, 0, 1, '" . $date ."', 1),
(" . $lastInsertId .", 8, 0, 0, '" . $date ."', 1),
(" . $lastInsertId .", 9, 0, 1, '" . $date ."', 1),
(" . $lastInsertId .", 11, 0, 1, '" . $date ."', 1),
(" . $lastInsertId .", 15, 0, 0, '" . $date ."', 1);		
		";
	
	$db->query($sql);

	if( $db->errno ) {
		return 'Error acls '.$db->error;		
	}	
	
	return true;
}


$errorMessage = db_1757($db);


