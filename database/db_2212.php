<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 *
 * @param resource $db MySQL connection object
 */
function db_2212($db){

$message="Hello, we just released a few new features for you! <a href=http://youtu.be/CvIkXmMD2Ww target=_blank>Click here to see a quick video</a>.
<br><p>
<pre>
<li>Make Phone call area easier to use
<li>Allow doctor to assign e-Rx authority to other users
<li>Lab comments box for the patient portal
<li>Reminder notifications for OPEN ordered items
<li>Add user name to Addendum
<li>Social History - added \"Other\" option for free text comments
<li>Add ability to insert the Time into the text input box in Physical Exam templates
<li>Track time of an encounter (in Superbill -> Advanced)
<li>Input shortcuts/macros for when needing to print long phrases
<li>Show Patient middle name in Encounter if defined
<li>Add patient MRN to Visit Summary report
<li>Rank autocomplete/look-ahead results by higher weight if used more often.
<li>Speed enhancements!
<li>Various bug fixes...
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


$errorMessage = db_2212($db);