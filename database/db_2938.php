<?php

/**
 * Use filename as function name to prevent possible conflict
 * with other db changes that also use PHP code
 *
 * @param resource $db MySQL connection object
 */
function db_2938($db){

$message="Hello, we just released a few new features for you! <a href=\"http://youtu.be/Cz6DOtVPrT4\" target=_blank>Click here to see a quick video</a>.
<br><p>
<pre>
<li>Ability to print Outside Lab/Radiology/Procedures Rx from Encounter & patient chart
<li>Add Reviewed labs for Outside labs to visit summary
<li>Add who scribed encounter for provider
<li>Allow generation of Specialist Specialty Report (to send back to PCP)
<li>Encounters page - show more search options
<li>Patient Portal: Create Administration Options -> med, surgeries history
<li>Patient Portal: Hide Add/Edit/Delete buttons in Insurance, Advance Directives, Guarantor
<li>Patient Portal: Med, Surgical Hx autocomplete data to use Portal favorites
<li>Patient Portal: Medical Hx screen - simplify data entry for patient
<li>Patient Portal: menu options - move labs to end, rename to lab results
<li>Patient Portal: simplify Allergies area
<li>Patient Portal: Surgical Hx screen - simplify data entry for patient
<li>Patient Portal: Tutor Mode helpers to assist patients
<li>Allow provider to customize Encounter Summary tab
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


$errorMessage = db_2938($db);


