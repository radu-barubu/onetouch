<?php
App::import( 'Lib', 'HL7MessageADT', array( 'file' => 'HL7MessageADT.php' ));
App::import( 'Lib', 'HL7MessageDFT', array( 'file' => 'HL7MessageDFT.php' ));
App::import( 'Lib', 'HL7MessageSIU', array( 'file' => 'HL7MessageSIU.php' ));
App::import( 'Lib', 'HL7MessageIncoming', array( 'file' => 'HL7MessageIncoming.php' ));
App::import( 'Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
#App::import( 'Model', 'PracticeSetting' );
#App::import( 'Model', 'EmdeonLabResult' );
App::import( 'Sanitize' );
App::import( 'Core', 'Controller' );
App::import( 'Lib', 'email', array( 'file' => 'email.php' ));
App::import('AppController', 'Controller');

class Hl7MacpracticeShell extends Shell {
	
	public function main() {
	   $practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
	   $this->practice_id = $practice_settings->practice_id;
	   //just grab 1 of the settings
	   $host_test=(!empty($practice_settings->hl7_sftp_out_host))? $practice_settings->hl7_sftp_out_host:$practice_settings->hl7_sftp_in_host;
	   $port_test=(!empty($practice_settings->hl7_sftp_out_port))? $practice_settings->hl7_sftp_out_port:$practice_settings->hl7_sftp_in_port;
	   if ( $this->MPconnCheck($practice_settings->practice_id, $host_test, $port_test) )
	   {	
		set_include_path( get_include_path() . PATH_SEPARATOR . WWW_ROOT . 'phpseclib' );
		require_once 'Net/SFTP.php';
		// check for labs
		if( $practice_settings->labs_setup === 'MacPractice' ) {
			$sftp = new Net_SFTP( $practice_settings->macpractice_host, $practice_settings->macpractice_port );
			$success = $sftp->login( $practice_settings->macpractice_username, $practice_settings->macpractice_password );
			if( $success ) {
				$files = $sftp->nlist();
				$count = count( $files );
				foreach( $files as $file ) {
					if( $file[0] == '.' )
						$count--;
				}
				$do_sync = ( $count > 0 );
				$this->err( "There are $count lab reports to process." );
			} else {
				$do_sync = false;
				$this->err( 'Failed to login to ' . $practice_settings->macpractice_username );
				$this->err( $sftp->getSFTPLog() );
			}
		} else {
			$do_sync = false;
		}
		
		// produce DFTs and ADTs
		if( $practice_settings->hl7_engine === 'MacPractice' ) {
			$sftp = new Net_SFTP( $practice_settings->hl7_sftp_out_host, $practice_settings->hl7_sftp_out_port );
			$success = $sftp->login( $practice_settings->hl7_sftp_out_username, $practice_settings->hl7_sftp_out_password );
			if( !$success ) {
				$this->err( 'Failed to login to ' . $practice_settings->hl7_sftp_out_username );
				$this->err( $sftp->getSFTPLog() );
			} else {
				$this->err( 'Logged into ' . $practice_settings->hl7_sftp_out_username );
				$receiver  = 'MacPractice';
				$this->processMsgType( 'DFT', $receiver, $sftp );
				if( $practice_settings->hl7_sftp_produce_adts )
					$this->processMsgType( 'ADT', $receiver, $sftp );
			}
		}
		
		// consume ADTs and SIUs
		if( $practice_settings->hl7_engine === 'MacPractice' ) {
			$sftp = new Net_SFTP( $practice_settings->hl7_sftp_in_host, $practice_settings->hl7_sftp_in_port );
			$success = $sftp->login( $practice_settings->hl7_sftp_in_username, $practice_settings->hl7_sftp_in_password );
			if( !$success ) {
				$this->err( 'Failed to login to ' . $practice_settings->hl7_sftp_in_username );
				$this->err( $sftp->getSFTPLog() );
			} else {
				$this->err( 'Logged into ' . $practice_settings->hl7_sftp_in_username );
				$files = $sftp->nlist();
				$count = count( $files );
				foreach( $files as $file ) {
					if( $file[0] == '.' )
						$count--;
				}
				$this->err( "There are $count incoming HL7 messages." );
				foreach( $files as $file ) {
					if( $file[0] == '.' )
						continue;
					$this->consumeFile( $sftp->get( $file ));
					$sftp->delete( $file );
				}
			}
		}

		// consume lab ORUs
		if( $do_sync ) {
			unset( $sftp );
			$this->err( 'Doing lab report sync.' );
			ClassRegistry::init( 'EmdeonLabResult' )->sync();
		}
	  }		
	}
	
	private function processMsgType( $msg_type, $receiver, $sftp ) {
		$class_name = 'HL7Message' . $msg_type;
		$id = $class_name::getNextId( $receiver );
		$last_used = $id;
		while( $id > 0 && ( $hl7 = $class_name::createFromDb( (int) $id, $receiver, HL7Message::PT_PRODUCTION ))) {
			$this->err( '-' . $msg_type . ' Record ' . $id );
			$HL7_message = $hl7->produceMessage();
			$this->writeHL7File( $sftp, $msg_type, $id, $receiver, $HL7_message );
			$id = $class_name::getNextId( $receiver );
			if( $last_used == $id )
				break;	// just a little extra check which is useful to avoid a never-ending loop if something goes haywire, especially during development tasks
			$last_used = $id;
		}
	}
	
	private function writeHL7File( $sftp, $msg_type, $id, $receiver, $msg_text ) {
		if( empty( $msg_text ))
			return;
		$receiver = str_replace( ' ', '_', $receiver );
		$sftp->put( "outgoing_{$receiver}_{$msg_type}{$id}.hl7", $msg_text );
		$this->keepCopy("outgoing_{$receiver}_{$msg_type}{$id}.hl7",$msg_text);
	}

	private function keepCopy($file,$data) {
                //keep copy
                $cpath='/home/HL7/macpractice_'.$this->practice_id.'/tmp';
                if(is_dir($cpath)){
                   $out = fopen($cpath."/".$file, "w");
                        fwrite($out, $data);
                        fclose($out);
                }
	}

	// check remote connection, alert us if down
	private function MPconnCheck($practice_id, $host, $port) {
		$cacheKey='MP_'.$practice_id.'_connection_check';
		$fp = @fsockopen($host, $port, $errno, $errstr, 5);
		if ($fp) {
                   Cache::set(array('duration' => '+1 month'));
                   $conn_check = Cache::read($cacheKey);
		   if($conn_check) {
			$message = "MacPractice client ".$practice_id." is back up";
			email::send('MP Connect Error', 'errors@onetouchemr.com', "MP Connect Error (resolved)", $message,'','',false,'','','','','');
		  	Cache::delete($cacheKey);
		   }

		  return true;
	
		} else {
                   //unable to connect, or error
                   Cache::set(array('duration' => '+1 month'));
                   $conn_check = Cache::read($cacheKey);
		   if(!$conn_check) {
		      Cache::set(array('duration' => '+1 month'));
                      Cache::write($cacheKey, date('n/j/Y h:i:s'));
		      //alert us
		      $message = "Unable to connect to MacPractice client ".$practice_id." @ ".$host." on port ".$port;
		      email::send('MP Connect Error', 'errors@onetouchemr.com', "MP Connect Error", $message,'','',false,'','','','','');
		   }
		  echo "unable to connect...aborting\n"; 
		  return false;
		}
	}

	/**
	 * Break incoming file into hl7 messages and consume each
	 * 
	 * @param string $file_contents
	 */
	public function consumeFile( $file_contents ) {
		$message_text = null;
		$i = 0;
		while( false !== ( $line = $this->get_line( $file_contents, $i ))) {
			if( strlen( $line ) == 0 )
				continue;
			if( substr( $line, 0, 3 ) == 'MSH' ) {
				$this->consumeMessage( $message_text );
				$message_text = null;
			}
			$message_text .= $line . HL7Message::SEGMENT_DELIMITER;
		}
		$this->consumeMessage( $message_text );
	}

	/**
	 * get line from string up to but not including \r\n, \r, or \n
	 *
	 * @param string &$file_contents
	 * @param int &$i offset
	 * @return string|false	the line just read not including newline string, false at EOF
	 */
	private function get_line( &$file_contents, &$i ) {
		static $buffer = '';
		while( $i < strlen( $file_contents )) {
			$c = $file_contents[$i++];
			if( $c == "\r" ) {
				$ret = $buffer;
				$buffer = '';
				if( $i == strlen( $file_contents ))
					return $ret;
				$c = $file_contents[$i++];
				if( $c != "\n" && $c != "\r" )
					$buffer .= $c;
				return $ret;
			} else if( $c == "\n" ) {
				$ret = $buffer;
				$buffer = '';
				return $ret;
			} else {
				$buffer .= $c;
			}
		}
		$ret = $buffer;
		$buffer = false;
		return $ret;
	}

	/**
	 * commit the given message into our database
	 *
	 * @param string $message_text
	 */
	private function consumeMessage( $message_text ) {
		if( empty( $message_text ))
			return;
		$hl7 = HL7MessageIncoming::createFromMessageText( $message_text );
		if( is_object( $hl7 )) {
			$this->err( $hl7->commitMessage() );
		} else {
			$this->err( "unparsable message: $message_text" );
		}
		$this->keepCopy("incoming_".time().".hl7",$message_text);	
	}
}
