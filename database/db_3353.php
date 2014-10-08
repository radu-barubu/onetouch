<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 *
 * @param resource $db MySQL connection object
 */
function db_3353($db){

$message="Hello, we just released a few new features for you! <a href=\"http://youtu.be/R7sbbKPEOkU\" target=_blank>Click here to see a quick video</a>.
<br><p>
<pre>
ICD-10 Support!
e-Labs: speed up new lab orders entry
Encounter Plan Frequent tests to store all order details
Dosespot: refills screen not matching up, and overall code improvements
Letter Templates: improving functionality/features
Form Builder - add capability to POC procedures area
Messaging: allow patient to respond and confirm appointments by text & email
Macros: make select box of available macros so user can see them
POC: if many items, it is hard to tell which is checked, so CSS color change if activated
Clinical Reports - add more POC items to search criteria
Disclosure Records - allow limit Visit Summaries by time in the report download
Add common complaint feature to more HPI elements
Assessment: add ability to move them up/down by priority
Administration - Allow practice to switch from ICD9 to ICD10 or vice versa
ICD-9 to ICD-10 converter tool!
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


$errorMessage = db_3353($db);


