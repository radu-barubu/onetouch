<?php
App::import( 'Lib', 'HL7MessageADT', array( 'file' => 'HL7MessageADT.php' ));
App::import( 'Lib', 'HL7MessageDFT', array( 'file' => 'HL7MessageDFT.php' ));
App::import( 'Lib', 'HL7MessageSIU', array( 'file' => 'HL7MessageSIU.php' ));
App::import( 'Lib', 'HL7MessageIncoming', array( 'file' => 'HL7MessageIncoming.php' ));

class Hl7IgnoreShell extends Shell {
	public function main() {
		// check args/params
		$valid_params 	= array( 'app'=>null, 'working'=>null, 'webroot'=>null, 'root'=>null, 'help'=>null, 'encounters'=>null, 'patients'=>null, 'appointments'=>null, 'receiver'=>null, 'host'=>null, 'login'=>null, 'pw'=>null, 'count'=>null );
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
		$receiver  = isset( $this->params['receiver'] )	? $this->params['receiver'] : null;
		if( ( empty( $receiver ) || $receiver == 1 ) && !isset( $this->params['help'] ))
		{
			$valid_command = false;
			$this->err( "-receiver must be specified" );
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
cake hl7_ignore dbname [-help] -receiver name [-count [n]] [-encounters [id|'next']] [-patients [id|'next']] [-appointments [id|'next']] [-host name] [-login name] [-pw password]

This is exactly like hl7_produce except no actual message is produced, 
but the event is marked as having been sent already to the given receiver.
Typically used when turning on outgoing messages for the first time when
you do NOT want all previous events generating messages.

dbname:          name of database to use (default: $database)

-help:           this description
-count:          number of messages to ignore (of each type), default is all but -count without arg means -count 1

-receiver:       name of receiving application

-encounters:     ignore DFT messages starting where encounter_id = id (default id: next)
-patients:       ignore ADT messages starting where patient_id   = id (default id: next)
-appointments:   ignore SIU messages starting where calendar_id  = id (default id: next)

-host:           $driver server where dbname database is found (default: $host)
-login:          $driver username (default: $login)
HELP_TEXT;
			//-pw:             $driver password (default: $password)
			$this->err( "\n" . $help_text );
			return;
		}

		$max_count = isset( $this->params['count'] ) 	? (int) $this->params['count'] : PHP_INT_MAX;
		$event_timestamp = __date( "Y-m-d H:i:s" );
		foreach( array( 
					'patients' => array( 'ADT', 'Patient' ), 
					'encounters' => array( 'DFT', 'Encounter' ), 
					'appointments' => array( 'SIU', 'Appointment' )
				) as $param => $types ) {
			if( isset( $this->params[$param] )) {
				$msg_type = $types[0];
				$event_type = $types[1];
				$class_name = 'HL7Message'. $msg_type;
				$count = 0;
				$id = $this->params[$param];
				$use_next = ( $id == 'next' );
				if( $use_next ) {
					$id = $class_name::getNextId( $receiver );
					$last_used = $id;
				}
				while( $id > 0 && $count++ < $max_count && (
						$hl7 = new HL7MessageNull( (int) $id, $event_type, $receiver, $event_timestamp, 'Ignore' )
						)) {
					$this->err( '-' . $param . ' ' . $id );
					$hl7->produceMessage();
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
