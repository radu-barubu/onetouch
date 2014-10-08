<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 * 
 * @param resource $db MySQL connection object
 */
function db_2448($db){

$message="Hello, we just released a few new features for you! 
<a href=\"http://youtu.be/qhOg4ZG7V0s\" target=_blank>Click here to see a quick video</a>.
<br><p>
<pre>
<li>when adding new user, send email confirmation
<li>Calendar: Recurring Appts
<li>Superbill: Advanced feature improvements
<li>Superbill: allow ability for practice to choose which E&M codes are shown
<li>Superbill: allow ability for practice to choose which ADVANCED codes are shown
<li>Messaging: allow reply to person be editable
<li>Remove character restrictions in vitals height
<li>Macros: allow increase to 3 chars, and allow Macros to work in any area of EMR
<li>Templates: ability to edit the word inline
<li>History: Allow favorite surgeries to speed up data entry
<li>Kareo Billing: integration enhancements
<li>Speed Enhancements
<li>many more, & bug fixes

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


$errorMessage = db_2448($db);


