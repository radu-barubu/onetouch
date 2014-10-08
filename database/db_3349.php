<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 *
 * @param resource $db MySQL connection object
 */
function db_3349($db){
	
	// Find all emdeon orders that do have an empty placer_order_number and their associated lab results
	$sql = 'SELECT res.lab_result_id, res.order_id, res.placer_order_number, res.date_time_transaction, ord.`comment`, ord.patient_comment';
	$sql .= ' FROM emdeon_lab_results AS res, emdeon_orders AS ord';
	$sql .= ' WHERE res.order_id = ord.order_id AND ord.placer_order_number=""';
	$sql .= ' ORDER BY res.date_time_transaction DESC';
	$sql .= ';';
	$result = $db->query( $sql );
	if( $db->errno )
		return 'Error getting broken emdeon_orders: '.$db->error;
	if( $result->num_rows == 0 )
		return true;
	
	$orders = null;
	for( $i = $result->num_rows; $i > 0; $i-- ) {
		$row = $result->fetch_row();
		$placer_order_number = $row[2];
		if(! isset( $orders[$placer_order_number] )) {
			$orders[$placer_order_number] = array( 
					'lab_result_id' => array( $row[0] ),
					'order_id' => $row[1],
					'date' => __date( "n/j/Y H:i:A", strtotime( $row[3] )),
					'comment' => $row[4],
					'patient_comment' => $row[5],
			);
		} else {
			$orders[$placer_order_number]['lab_result_id'][] = $row[0];
			$orders[$placer_order_number]['comment'] .= $row[4];
			$orders[$placer_order_number]['patient_comment'] .= $row[5];
		}
	}
	
	// Now we need to update the orders (only the first order found for the given placer_order_number--the rest are left as-is and orphaned).
	// Then have all the associated reports point back to the one selected order record.
	foreach( $orders as $placer_order_number => $orderData ) {
		$sql = 'UPDATE emdeon_orders SET';
		$sql .= ' `placer_order_number` = "' . $placer_order_number . '"';
		$sql .= ', `date`="' . $orderData['date'] . '"';
		$sql .= ', `comment`="' . $orderData['comment'] . '"';
		$sql .= ', `patient_comment`="' . $orderData['patient_comment'] . '"';
		$sql .= ' WHERE order_id = ' . $orderData['order_id'];
		$sql .= ';';
		$result = $db->query( $sql );
		if( $db->errno )
			return "Error repairing emdeon_order $placer_order_number:".$db->error;

		$sql = 'UPDATE emdeon_lab_results SET';
		$sql .= ' order_id="'. $orderData['order_id'] . '"';
		$sql .= ' WHERE';
		$disjunction = ' ';
		foreach( $orderData['lab_result_id'] as $lab_result_id ) {
			$sql .= $disjunction . 'lab_result_id = ' . $lab_result_id;
			$disjunction = ' OR ';
		}
		$sql .= ';';
		$result = $db->query( $sql );
		if( $db->errno )
			return "Error repairing emdeon_lab_reports for emdeon_order $placer_order_number:".$db->error;
	}
	
	return true;	// all went well
}


$errorMessage = db_3349($db);


