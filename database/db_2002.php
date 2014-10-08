<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_2002($db){

$message="Hello, we just released a few new features for you! <a href=\"http://youtu.be/AszVZkikZ-I\" target=_blank>Click here to see a quick video</a>.
<br><p>
<pre>
<li>Import All records function added for Kareo
<li>Allow import of completed Patient form data into the HPI
<li>Allow to Void an Encounter
<li>Only show Approved Labs in the Patient Portal
<li>Notify nurses when doctor ordered POC items
<li>Allow providers to override default practice settings such as type, logo, and name
<li>Improved the Password change screen
<li>Add e-Rx to Order Feed
<li>Hide History tab for Phone Encounters
<li>Allow practice to increase Auto-Log off Timer to 120 minutes
<li>Set patient portal usernames & passwords to all lower case
<li>Make patient portal links clickable inside the visit summary
<li>Allow Dragon voice dictation for all users
<li>various bug fixes
</pre>
<br><br>
<p>Sincerely, <br>One Touch EMR Development Team";

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
                                (1, ".$to.", 0, 0, 0, 'New Features', '', 'New Features Released!', 
				'$message', 'High', 'New', '0', 1, 1, NULL, NULL, NOW(), 0, NOW(), 0);
		";
	
		$result2 = $db->query($sql);
	
		if( $db->errno ) {
			return 'Failed to send message about upgrade  '.$db->error;		
		}
	}
     } 

			
	return true;

}


$errorMessage = db_2002($db);


