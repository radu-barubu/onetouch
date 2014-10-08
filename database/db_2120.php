<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_2120($db){
	
	$sql = '


	CREATE TABLE `encounter_orders` (
	`encounter_order_id` int(11) NOT NULL AUTO_INCREMENT,
	`data_id` INT NULL ,
	`encounter_id` INT NULL ,
	`patient_id` INT NULL ,
	`encounter_status` VARCHAR( 100 ) NULL ,
	`test_name` VARCHAR( 100 ) NULL ,
	`source` VARCHAR( 100 ) NULL ,
	`patient_firstname` VARCHAR( 50 ) NULL ,
	`patient_lastname` VARCHAR( 50 ) NULL ,
	`provider_name` VARCHAR( 100 ) NULL ,
	`priority` VARCHAR( 100 ) NULL ,
	`order_type` VARCHAR( 100 ) NULL ,
	`status` VARCHAR( 100 ) NULL ,
	`item_type` VARCHAR( 100 ) NULL ,
	`date_performed` DATE NULL ,
	`date_ordered` DATE NULL ,
	`modified_timestamp` TIMESTAMP NULL,
	 PRIMARY KEY (`encounter_order_id`)
);	
	';
	
	$db->query($sql);

	if( $db->errno ) {
		return 'Failed create encounter_orders table '.$db->error;		
	}			
	
	$result = $db->query('SELECT DATABASE()');
	$result = $result->fetch_assoc();
	$dbName = $result['DATABASE()'];
	$shellcommand="php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app '".APP."' rebuild_orders ".$dbName."  >> /dev/null 2>&1 & ";
	exec($shellcommand);

	return true;
}

$errorMessage = db_2120($db);