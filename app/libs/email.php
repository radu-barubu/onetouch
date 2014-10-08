<?php
App::import('Lib', 'site');

App::import('Lib', 'PHPMailer', array('file' => 'PHPMailer_v5.1' . DS . 'class.phpmailer.php'));

class email extends Object {

	private static $to_email, $subject, $body, $to_name, $attach_string, $attach_name, $attach_encoding, $attach_type;
	private static $error;
	private static $log = true;
	private static $cache = false;
	private static $debug = true;
	private static $response;
	private static $info = array();
	private static $isHtml = false;

	public static function send($to_name = null, $to_email = '', $subject = '', $body = '', $sender_name = '', $sender_email = '', $html = true, $attach_string='', $attach_name='',$attach_encoding='', $attach_type='',$embed_logo='' , $file_attachment = '') {
		App::import('Core', 'Router');		
		$controller = new Controller;
		$controller->loadModel('practiceSetting');
		$view = new View($controller);


		$settings = $controller->practiceSetting->getSettings();

		if ($sender_email != '') {
			$settings->sender_email = $sender_email;
		}

		if ($sender_name != '') {
			$settings->sender_name = $sender_name;
		}

		if ($html) {
			self::$isHtml = true;
			$body = $view->renderLayout(nl2br($body), 'email/html/default');
		}

		if($attach_string) {
		  $settings->attach_string = $attach_string;
		}

		if($attach_name) {
		  $settings->attach_name = $attach_name;
		}
		if($attach_encoding) {
		  $settings->attach_encoding = $attach_encoding;
		} else {
		  $settings->attach_encoding ='';
		}
		if($attach_type) {
		  $settings->attach_type = $attach_type;
		} else {
		  $settings->attach_type ='';
		}		

		if($embed_logo) {
		  $settings->embed_logo = $embed_logo;
		} else {
		  $settings->embed_logo='';
		}		

    $settings->file_attachment = $file_attachment;
    
		$method = $settings->email_method;

		self::$to_email = self::$info['to_email'] = $to_email;
		self::$subject = self::$info['subject'] = $subject;
		self::$body = self::$info['body'] = $body;
		self::$to_name = self::$info['to_name'] = $to_name;

		$sent = false;

		if (!$method) {
			$method = 'php';
		}
		self::$info['method'] = $method;

		if ($method == 'smtp' && $settings->connection_security) {
			switch ($settings->connection_security) {
				case 'ssl':
				case 'tsl':
				case 'starttsl':

					if (!extension_loaded('openssl')) {

						die("php_openssl extension from php.ini is required in order to connect to smtp server.");
					}
					break;
				default:
			}
		}

		ob_start();
		switch ($method) {
			case 'smtp':
				$sent = self::smtp($settings);
				break;
			case 'php':
			default:
				$sent = self::mail($settings);
		}
		self::$response = ob_get_contents();
		ob_end_clean();

		return $sent;
	}

	public function info() {
		return self::$info;
	}

	public function response() {
		return self::$response;
	}

	public function error() {
		return self::$error;
	}

	public function isHTML() {
		return self::$isHtml;
	}

	private function mail($settings) {
		$mail = new PHPMailer(); // defaults to using php "mail()"
		//$body = $mail->getFile('contents.html');
		//$body = eregi_replace("[\]",'',self::$body);
		
                //if partner domain for private label is used, use their domain, not ours
                if(!empty($settings->partner_id))
                {
                        $dom = $settings->partner_id;
                }
                else
                {
                        $dom = 'onetouchemr.com';
                }
		
		
		$body = self::$body;
		$replyToEmail = ( $settings->sender_email ? $settings->sender_email : 'devteam@'.$dom);
		$replyToName = (($settings->sender_name && $settings->sender_name !== null) ? $settings->sender_name : 'Administrator');

		$mail->Sender = 'notifications@'.$dom;
		$mail->From = 'notifications@'.$dom;
		$mail->FromName = 'Notifications';
		$mail->AddReplyTo($replyToEmail, $replyToName);
		$mail->Subject = self::$subject;
		if($settings->embed_logo) {
		  $mail->AddEmbeddedImage($settings->embed_logo, 'customer_logo');
		}
		//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		//$mail->body = self::$body;

		if (true || self::isHTML() == 'html') {
			$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
			$mail->MsgHTML($body);
		} else {
			$mail->IsHTML(false);
			$mail->Body = $body;
		}

		$mail->AddAddress(self::$to_email, self::$to_name);

		if(isset($settings->attach_string) && $settings->attach_string  && $settings->attach_name) {
		  $mail->AddStringAttachment($settings->attach_string ,$settings->attach_name, $settings->attach_encoding,$settings->attach_type); // string file attachment
		}
    
    if (isset($settings->file_attachment) && $settings->file_attachment) {
      $mail->AddAttachment($settings->file_attachment);
    }
    
		//die("<pre>".print_r($mail,1));

		if (!$mail->Send()) {
			self::$error = "Mailer Error: " . $mail->ErrorInfo;
			return self::$error;
		} else {
			return true;
		}
	}

	private function smtp($settings) {
		//date_default_timezone_set(date_default_timezone_get());

		$mail = new PHPMailer();

		if (!$timeout = (int) site::setting('email_timeout')) {
			$timeout = 10;
		}
		$mail->Timeout = $timeout;

		//$body = $mail->getFile('contents.html');
		//$body = eregi_replace("[\]",'',self::$body);
		$body = self::$body;

		$mail->IsSMTP(); // telling the class to use SMTP

		if ($settings->connection_security) {

			switch ($settings->connection_security) {
				case 'ssl':
					$mail->SMTPAuth = true;
					$mail->SMTPSecure = "ssl";
					break;
				case 'tsl':
					$mail->SMTPAuth = true;
					$mail->SMTPSecure = "tls";
					break;
			}
		}

		$mail->Host = $settings->smtp_host; // SMTP server
		$mail->Username = $settings->smtp_username;
		$mail->Password = $settings->smtp_password;

		$replyToEmail = ( $settings->sender_email ? $settings->sender_email : 'devteam@onetouchemr.com');
		$replyToName = (($settings->sender_name && $settings->sender_name !== null) ? $settings->sender_name : 'Administrator');

		$mail->Sender = 'notifications@onetouchemr.com';
		$mail->From = 'notifications@onetouchemr.com';
		$mail->FromName = 'Notifications';
		$mail->AddReplyTo($replyToEmail, $replyToName);


		$port = $settings->smtp_port;
		if (!$port) {
			$port = 25;
		}
		$mail->Port = $port;
		$mail->Subject = self::$subject;

		//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

		if (self::isHTML() == 'html') {
			$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
			$mail->MsgHTML($body);
		} else {
			$mail->IsHTML(false);
			$mail->Body = $body;
		}

		$mail->AddAddress(self::$to_email, self::$to_name);

		//$mail->AddAttachment("images/phpmailer.gif"); // attachment
		if(isset($settings->attach_string) && $settings->attach_string  && $settings->attach_name) {
		  $mail->AddStringAttachment($settings->attach_string ,$settings->attach_name, $settings->attach_encoding,$settings->attach_type); // string file attachment
		}
    
    if (isset($settings->file_attachment) && $settings->file_attachment) {
      $mail->AddAttachment($settings->file_attachment);
    }

		if (!$mail->Send()) {

			self::$error = "Mailer Error: " . $mail->ErrorInfo;
			return self::$error;
		} else {
			return true;
		}
	}

}
