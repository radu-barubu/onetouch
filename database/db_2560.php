<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_2560($db){

  $t=array('emdeon_lab_results','emdeon_orderinsurance','emdeon_orders');
  foreach ($t as $tbls)
  {
        $sql ="show index from $tbls where Key_name = 'order_id' ";

        $result = $db->query($sql);

        if( $db->errno ) {
               return 'Failed update in result for db_2560.php: '.$db->error;
        }
        $row = $result->fetch_row();
        if(!$row) {
                $sql2="ALTER TABLE $tbls ADD INDEX ( `order_id` ) ";
                $result2 = $db->query($sql2);
                if( $db->errno ) {
                  return 'Failed to update result2 in db_2560.php '.$db->error;
                }
        } 
  }

  return true;
}


$errorMessage = db_2560($db);


