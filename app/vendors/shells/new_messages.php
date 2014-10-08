<?php
App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Core', 'Controller');
App::import('Lib', 'email');
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Lib', 'Emdeon_HL7', array( 'file' => 'Emdeon_HL7.php' ));

class NewMessagesShell extends Shell
{
	function main() 
	{
		$sent = ClassRegistry::init('MessagingMessage')->newMessageNotification();
        
        if($sent > 0)
        {
            echo $sent . " Notification(s) Sent.\n";
        }
        else
        {
            echo "No notifications Sent.\n";
        }
	}
}

?>