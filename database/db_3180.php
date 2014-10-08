<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 *
 * @param resource $db MySQL connection object
 */
function db_3180($db){

$message="Hello, we just released a few new features for you! <a href=\"http://youtu.be/AasflVwOT1I\" target=_blank>Click here to see a quick video</a>.
<br><p>
<pre>
<br>Dashboard: add \"Show All\" button for patient schedule
<br>Disclosure Records: add option to include Visit Summary
<br>Documents: allow ability to add more custom document types
<br>Dosespot: import surescripts Rx history bug fix, and formatting improvements
<br>Encounter: add option to lock and not post charges to 3rd party billing systems
<br>Encounters Summary: allow saving of Advanced search filter
<br>Encounters: allow provider to choose data to import for previous visits
<br>Family History improvements for ease of use
<br>Favorites: Separate Medical Diagnoses from Medical History
<br>Form Builder GUI interface v 1.0
<br>HL7: schedule reminders created from 3rd party vendors
<br>Messaging: Add Fax button to more areas
<br>Messaging: add Subject column in listing table
<br>Patient Chart: Add Summary Tab to Medical chart
<br>Patient Chart: add Vitals tab
<br>Patient Portal: add History section to check in process
<br>Patient Portal: improve Family History section
<br>Patient Portal: improve Social history section
<br>Patient Portal: new account registration enhancements
<br>Patient Search: add Phone Number as search criteria
<br>POC: improve speed of selecting items if there are many on the page
<br>Summary tab: add option to display Vital signs
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


$errorMessage = db_3180($db);


