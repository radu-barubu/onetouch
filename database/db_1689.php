<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_1689($db){
	
	$menus = array(
		'Vendor Settings' => array(
			'controller' => 'administration',
			'action' => 'vendor_settings',
			'system_admin_only' => 1,
			'acl_writable' => array(10),
		),
		'Patient Import' => array(
			'controller' => 'administration',
			'action' => 'patient_import',
			'system_admin_only' => 1,
			'acl_writable' => array(10),
		),
		'ROS Template' => array(
			'controller' => 'preferences',
			'action' => 'ros_template',
			'system_admin_only' => 0,
			'acl_writable' => array(1, 3, 11),
		),
		'PE Template' => array(
			'controller' => 'preferences',
			'action' => 'pe_template',
			'system_admin_only' => 0,
			'acl_writable' => array(1, 3, 11),
		),
		'Favorite Diagnoses' => array(
			'controller' => 'preferences',
			'action' => 'favorite_diagnoses',
			'system_admin_only' => 0,
			'acl_writable' => array(1, 3, 11),
		),
		'Common Complaints' => array(
			'controller' => 'preferences',
			'action' => 'common_complaints',
			'system_admin_only' => 0,
			'acl_writable' => array(1, 3, 11),
		),
		'Favorite Test Codes' => array(
			'controller' => 'preferences',
			'action' => 'favorite_test_codes',
			'system_admin_only' => 0,
			'acl_writable' => array(1, 3, 11),
		),
		'Favorite Test Groups' => array(
			'controller' => 'preferences',
			'action' => 'favorite_test_groups',
			'system_admin_only' => 0,
			'acl_writable' => array(1, 3, 11),
		),
		'Favorite Prescriptions' => array(
			'controller' => 'preferences',
			'action' => 'favorite_prescriptions',
			'system_admin_only' => 0,
			'acl_writable' => array(1, 3, 11),
		),
		'Unmatched Lab Reports' => array(
			'controller' => 'reports',
			'action' => 'unmatched_lab_reports',
			'system_admin_only' => 0,
			'acl_writable' => array(1, 3, 11),
		),
		'Unmatched RX Refill Request' => array(
			'controller' => 'reports',
			'action' => 'unmatched_rxrefill_requests',
			'system_admin_only' => 0,
			'acl_writable' => array(1, 3, 11),
		),
		'User Locations' => array(
			'controller' => 'administration',
			'action' => 'user_locations',
			'system_admin_only' => 1,
			'acl_writable' => array(10),
		),
	);
	
	$date = __date('Y-m-d H:i:s');
	foreach ($menus as $name => $opts) {
		$sql = sprintf("
				INSERT INTO 
					`system_menus` 
						(`menu_name`, `menu_controller`, `menu_action`, `menu_variation`, `menu_url`, `menu_options`, `menu_group`, `group_options`, `menu_parent`, `menu_inherit`, `menu_show`, `menu_show_roles`, `menu_enable_link`, `system_admin_only`, `modified_timestamp`, `modified_user_id`) 
						VALUES
						('%s', '%s', '%s', '', '', '', 0, '[]', 35, 0, 0, 0, 0, %d, '%s', 0);", 
			
			$name, $opts['controller'], $opts['action'], $opts['system_admin_only'], $date);

		
		$db->query($sql);
		
		if( $db->errno ) {
			return 'Error adding system menus: '.$db->error;		
		}
		
		$lastInsertId = $db->insert_id;
		
		$sql = "INSERT INTO `acls` (`menu_id`, `role_id`, `acl_read`, `acl_write`, `modified_timestamp`, `modified_user_id`) VALUES ";
		
		$userRoleIds = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 11);
		
		$data = array();
		foreach ($userRoleIds as $roleId) {
			
			$writable = 0;
			
			if (in_array($roleId, $opts['acl_writable'])) {
				$writable = 1;
			}
			
			$data[] = sprintf("(%d, %d, 0, %d, '%s', 1)", $lastInsertId, $roleId, $writable, $date);
			
		}
		
		$sql .= implode(', ', $data);
		
		$db->query($sql);
		
		if( $db->errno ) {
			return 'Error adding ACL items: '.$db->error;		
		}
		
		
	}
	
	
	return true;
}


$errorMessage = db_1689($db);


