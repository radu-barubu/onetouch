<?php
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Core', 'Controller');
App::import('Lib', 'email');
App::import('Lib', 'email_formatter');
App::import('Lib', 'data');

class AppointmentRemindersShell extends Shell
{
	function main() 
	{
		$send = ClassRegistry::init('AppointmentReminder')->sent();       
		if($send > 0){
			echo $send . " Appointment Reminder(s) Send.";
		} else {
			echo "No Appointment Reminder Sent.";
		}
	}
}

?>
