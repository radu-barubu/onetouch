<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_2976($db){

         $sql ="select `user_id`,`dosepot_singlesignon_userid` from `user_accounts` where `dosepot_singlesignon_userid` != '' and `dosepot_singlesignon_userid` != '0' and `role_id` NOT IN (3,4,5,8) ";

        $result = $db->query($sql);

        if( $db->errno ) {
                return 'Failed  '.$db->error;
        }

        while($signonroles = $result->fetch_assoc()) {
                $sql2="select `user_id` from `user_accounts` where
                        `dosepot_singlesignon_userid`='".$signonroles['dosepot_singlesignon_userid']."'
                        and `role_id` IN (3,4,5) LIMIT 1 ";
                $result2 = $db->query($sql2);
                $provider=$result2->fetch_assoc();
                if(!empty($provider['user_id'])){
                $sql3="insert into `administration_prescription_auth`
                        (`prescribing_user_id`,`authorized_user_id`)
                        VALUES ('".$provider['user_id']."','".$signonroles['user_id']."')   ";
                   $result3 = $db->query($sql3);
                   if( $db->errno ) {
                        return 'Failed to insert into administration_prescription_auth '.$db->error;
                   }
                }
           $result2->free();
        }
        $result->free();
                $sql4 = "update user_accounts
                        set `dosepot_singlesignon_userid` = '' and `dosespot_clinician_id` = ''
                        where `role_id` NOT IN (3,4,5)  ";

                $result4 = $db->query($sql4);

                if( $db->errno ) {
                        return 'Failed to update user_accounts '.$db->error;
                }


	
	return true;

}


$errorMessage = db_2976($db);


