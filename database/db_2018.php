<?php


/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_2018($db){
    
    
	//push into background since this can take a while to execute
	$result = $db->query('SELECT DATABASE()');
	$result = $result->fetch_assoc();
	$dbName = $result['DATABASE()'];
	$shellcommand="php -q ".CAKE_CORE_INCLUDE_PATH."/cake/console/cake.php -app '".APP."' meaningful_use_update_dosespot ".$dbName."  >> /dev/null 2>&1 & ";
	exec($shellcommand);

	
	return true;
}


$errorMessage = db_2018($db);


