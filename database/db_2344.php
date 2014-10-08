<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_2344($db){

$message="Hello, we just released a few new features for you! <a href=\"http://youtu.be/LqUqrj3aY7M\" target=_blank>Click here to see a quick video</a>.
<br><p>
<pre>
<li>calendar: move patient name into larger box for easier reading
<li>patient portal: onboarding/check-in feature
<li>Allow Doc to hide free text comments in Plan from patients
<li>Labcorp: modify lab requisition print to include labels
<li>marital status to demographics page
<li>Custom Patient ID on demographics page
<li>allow doc to print patient summary in plan tab
<li>when user is logged out for being idle, when they log back in, bring them back to where they left off
<li>print each area in visit summary in same order as tabs are in encounter
<li>allow to hide tabs in encounter
<li>Create Custom Encounters, and Attach to Appointment Types
<li>Allow to rename Encounter tabs
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


$errorMessage = db_2344($db);


