<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_2878($db){

$message="Hello, your account has been updated to 1.6.20. Here are the highlights:
 
<a href=\"http://youtu.be/Al7jpIKWZRs\" target=_blank>Click here to see video</a>.
<br><p>
<p>Sincerely, <br>One Touch EMR Development Team";
$subject="Account Update!";

        $sql ="SELECT `user_id` FROM user_accounts WHERE `role_id` != '10' AND  `role_id` != '8'        ";

      if($result = $db->query($sql))
      {

        if( $db->errno ) {
                return 'Failed to get role_id  '.$db->error;
        }
	
	
	while($inf = $result->fetch_assoc())
	{
		$to=$inf['user_id'];
		$sql = "
		INSERT INTO `messaging_messages`
                                (`sender_id`, `recipient_id`, `patient_id`, 
				`reply_id`, `calendar_id`, `type`, `attachment`, 
				`subject`, `message`, `priority`, `status`, `archived`, 
				`inbox`, `outbox`, `sender_folder`, `recipient_folder`, 
				`created_timestamp`, `time`, `modified_timestamp`, `modified_user_id`)
                                VALUES
                                (1, ".$to.", 0, 0, 0, 'New Features', '', '$subject', 
				'$message', 'High', 'New', '0', 1, 1, NULL, NULL, NOW(), '', NOW(), 0);
		";
	
		$result2 = $db->query($sql);
	
		if( $db->errno ) {
			return 'Failed to send message about upgrade  '.$db->error;		
		}
	}
     } 

			
	return true;

}


$errorMessage = db_2878($db);


