<?php
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));

class PatientRemindersShell extends Shell
{
	function main() 
	{
		$send = ClassRegistry::init('PatientReminder')->sent();       
		if($send > 0){
			echo $send . " Patient Reminder(s) Send.";
		} else {
			echo "No Patient Reminder Sent.";
		}
	}
}

?>