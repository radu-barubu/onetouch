<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 *
 * @param resource $db MySQL connection object
 */
function db_3482($db){

$message="Hello, we just released a few new features for you! <a href=\"http://youtu.be/oQ0sGoBZ3Ak\" target=_blank>Click here to see a quick video</a>.
<br><p>
<pre>
Medications - tracking changes in Plan, highlight new Rx items
Appointments - add a Pending Appointment area
Documents - add ability to Fax out \"Online Forms\" from patient chart
Summary Tab - add Insurance Name
Forms - add ability to use a background image
Schedule Calendar - allow multi-select
POC - add ability to categorize POC items
Documents - add filtering capabilities
Patient Portal - autosave the checkin process
Calendars - move \"Loading\" text to center for easier recognition
Messaging Fax - autocomplete needs to be pulling in Directory data, not patients
Dashboard - create more advanced filtering options
Dashboard - add RSS feeds for Medicine topics
Patient Labs - if standard lab setting, add ability to generate an outside Lab prescription
Printing RX - and add patient phone number in the header
Order Feed - the time of the order always says 12:00 AM
Referrals - allow ability to add Visit Summary and attach it (from chart area)
Medications - Add PRN medication choices
Encounters - only allowed the assigned provider to be able to Void
Forms - fix format to support multi-columns class in the POC Procedures area
Forms - add ability to put text after the element
bug fixes
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


$errorMessage = db_3482($db);


