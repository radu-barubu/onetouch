<?php 
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Lib', 'EMR_Groups', array( 'file' => 'EMR_Groups.php' ));
App::import('Core', array('Router'));

class OrderReminderShell extends Shell {
	var $uses = array('ReminderNotification'); 
	 
	function main() 
	{	
		$client = $this->ReminderNotification->sendReminderNotification();
	}
}

?>