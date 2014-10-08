<?php
App::import( 'Lib', 'HL7MessageADT', array( 'file' => 'HL7MessageADT.php' ));
App::import( 'Lib', 'HL7MessageDFT', array( 'file' => 'HL7MessageDFT.php' ));
App::import( 'Lib', 'HL7MessageSIU', array( 'file' => 'HL7MessageSIU.php' ));
App::import( 'Lib', 'HL7MessageIncoming', array( 'file' => 'HL7MessageIncoming.php' ));

class Hl7ProduceShell extends Shell {
	public function main() {
		// check args/params
		$valid_params 	= array( 'app'=>null, 'working'=>null, 'webroot'=>null, 'root'=>null, 'help'=>null, 'encounters'=>null, 'patients'=>null, 'appointments'=>null, 'receiver'=>null, 'host'=>null, 'login'=>null, 'pw'=>null, 'files'=>null, 'count'=>null );
		$unimplemented	= array( 'host'=>null, 'login'=>null, 'pw'=>null);
		$max_args		= 1;

		$valid_command = true;
		foreach( array_diff_key( $this->params, $valid_params ) as $p => $v ) {
			$valid_command = false;
			$this->err( "invalid parameter: $p" );
			unset( $this->params[$p] );
		}
		foreach( array_diff_key( $this->params, array_diff_key( $valid_params, $unimplemented )) as $p => $v ) {
			$valid_command = false;
			$this->err( "unimplemented parameter: $p" );
		}
		if( count( $this->args ) > $max_args ) {
			$valid_command = false;
			$this->err( "exceeded max args: $max_args" );
		}

		if( !$valid_command || isset( $this->params['help'] )) {
			$ps_model 	= ClassRegistry::init( 'PracticeSetting' );
			$db_config 	= $ps_model->getDataSource()->config;
			$driver		= $db_config['driver'];
			$host		= $db_config['host'];
			$login		= $db_config['login'];
			$password	= $db_config['password'];
			$database	= $db_config['database'];
			$help_text	= <<<HELP_TEXT
cake hl7_produce dbname [-help] [-count [n]] [-encounters [id|'next']] [-patients [id|'next']] [-appointments [id|'next']] [-receiver name] [-files [dirname]] [-host name] [-login name] [-pw password]

dbname:          name of database to use (default: $database)

-help:           this description
-count:          number of messages to produce (of each type), default is all but -count without arg means -count 1
-files:          output messages, one per file, to files created in dirname (default: output messages to stdout)

-receiver:       name of receiving application

-encounters:     output DFT P03 messages starting where encounter_id = id (default id: next)
-patients:       output ADT A04 messages starting where patient_id   = id (default id: next)
-appointments:   output SIU S12 messages starting where calendar_id  = id (default id: next)

-host:           $driver server where dbname database is found (default: $host)
-login:          $driver username (default: $login)
HELP_TEXT;
//-pw:             $driver password (default: $password)
			$this->err( "\n" . $help_text );
			return;
		}

		$dir       = isset( $this->params['files'] )	? ( true === $this->params['files'] ? '.' : $this->params['files'] ) : null;
		$max_count = isset( $this->params['count'] ) 	? (int) $this->params['count'] : PHP_INT_MAX;
		$receiver  = isset( $this->params['receiver'] )	? $this->params['receiver'] : null;
		foreach( array( 'patients' => 'ADT', 'encounters' => 'DFT', 'appointments' => 'SIU' ) as $param => $msg_type ) {
			if( isset( $this->params[$param] )) {
				$class_name = 'HL7Message'. $msg_type;
				$count = 0;
				$id = $this->params[$param];
				$use_next = ( $id == 'next' );
				if( $use_next ) {
					$id = $class_name::getNextId( $receiver );
					$last_used = $id;
				}
				while( $id > 0 && $count++ < $max_count && ( $hl7 = $class_name::createFromDb( (int) $id, $receiver, HL7Message::PT_PRODUCTION ))) {
					$this->err( '-' . $param . ' ' . $id );
					$HL7_message = $hl7->produceMessage();
					if( is_null( $dir ))
						$this->out( $HL7_message );
					else
						$this->writeHL7File( $dir, $msg_type, $id, $receiver, $HL7_message );
					if( $use_next ) {
						$id = $class_name::getNextId( $receiver );
						if( $last_used == $id )
							break;	// just a little extra check which is useful to avoid a never-ending loop if something goes haywire, especially during development tasks
						$last_used = $id;
					} else {
						$id++;
					}
				}
			}
		}
	}

	private function writeHL7File( $dir, $msg_type, $id, $receiver, $msg_text ) {
		if( empty( $msg_text ))
			return;
		$receiver=str_replace(' ','_',$receiver);
		$file=$dir.DS."outgoing_{$receiver}_{$msg_type}{$id}.hl7";
		$f = fopen( $file, 'wb' );
		fwrite( $f, $msg_text );
		fclose( $f );
		chmod($file, 0777);
	}

	/**
	 * Displays a header for the shell
	 * (override Shell class version of this method that writes to stdout)
	 *
	 * @access protected
	 */
	function _welcome() {
		$this->err();
		$this->err('Welcome to CakePHP v' . Configure::version() . ' Console');
		$this->errhr();
		$this->err('App : '. $this->params['app']);
		$this->err('Path: '. $this->params['working']);
		$this->errhr();
	}

	/**
	 * Outputs a series of minus characters to the standard output, acts as a visual separator.
	 * (override Shell class version of this method that writes to stdout)
	 *
	 * @param integer $newlines Number of newlines to pre- and append
	 * @access public
	 */
	function errhr($newlines = 0) {
		$this->err(null, $newlines);
		$this->err('---------------------------------------------------------------');
		$this->err(null, $newlines);
	}
}
