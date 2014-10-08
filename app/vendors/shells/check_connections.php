<?php
/*
	check_connections.php
	
	Checks for connections to OTEMR services and sends email notifications if not connected

*/

App::import('Core', 'Model');
App::import('Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('Core', 'Controller');
App::import('Lib', 'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import('Lib', 'Emdeon_HL7', array( 'file' => 'Emdeon_HL7.php' ));
App::import('Lib', 'PHPMailer', array('file' => 'PHPMailer_v5.1' . DS . 'class.phpmailer.php'));

function connectionOK($url, $timeout=30){
	// Check an http connection to $url
	$connection_result = true;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if(curl_errno($ch)){
			$connection_result = false;
	}
	curl_close($ch);
	if(!($httpcode >= 200 && $httpcode < 308) && $httpcode != '403'){
			$connection_result = false;
	}
	return $connection_result;
}

function sendEmail($toName, $toEmail, $subject, $body){
	// Send email using PHPMailer
	$mail = new PHPMailer(); // defaults to using php "mail()"

	// From
	$replyToEmail = 'devteam@onetouchemr.com';
	$replyToName = 'Administrator';
	$mail->Sender = 'notifications@onetouchemr.com';
	$mail->From = 'notifications@onetouchemr.com';
	$mail->FromName = 'Connection Notifications';

	// To
	$mail->AddReplyTo($replyToEmail, $replyToName);
	$mail->Subject = $subject;
	$mail->AddAddress($toEmail, $toName);
	
	// Body
	$mail->IsHTML(false);
	$mail->Body = $body;
	
	$mail->Send();
}

class CheckConnectionsShell extends Shell {
	
	function main(){
		$errors = '';
		
		// Check emdeon connection
		if( !connectionOK('cli-cert.emdeon.com', 15) ){
			$errors .= "Error connecting to emdeon at cli-cert.emdeon.com\n";
		}
		
		// Check dosespot connection
		if( !connectionOK('my.dosespot.com') ){
			$errors .= "Error connecting to dosespot at my.dosespot.com\n";
		}

		// Check dragon connection
		if( !connectionOK('speechanywhere.nuancehdp.com') ){
			$errors .= "Error connecting to dragon at www.faxage.com\n";
		}
		
		// Check faxage connection
		if( !connectionOK('www.faxage.com') ){
			$errors .= "Error connecting to faxage at www.faxage.com\n";
		}
		
		// Simulate an error for testing
		//$errors .= "Simulated error\n";
		
		// Report errors
		if( $errors != '' ){
			sendEmail("Dr Abbate", "doctorabbate@gmail.com", "Connection errors", $errors);
			sendEmail("Tim Lundeen", "lundeen.tim@gmail.com", "Connection errors", $errors);
		} else {
			$errors = "No connection errors\n";
		}
		$this->out($errors, 0);
	}
}
?>