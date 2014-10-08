<?php
App::import( 'Lib', 'HL7MessageIncoming', array( 'file' => 'HL7MessageIncoming.php' ));
App::import( 'Lib', 'LazyModel', array( 'file' => 'LazyModel.php' ));
App::import('AppController', 'Controller');

class Hl7ConsumeShell extends Shell {
	public function main() {
		// This should work for handling files from any os, but doesn't (so write my own): ini_set('auto_detect_line_endings', 1); fgets( $this->Dispatch->stdin )...
		$message_text = null;
		while( false !== ( $line = $this->get_line_from_stdin() )) {
			if( strlen( $line ) == 0 )
				continue;
//			$this->err( $line );
			if( substr( $line, 0, 3 ) == 'MSH' ) {
				$this->consumeMessage( $message_text );
				$message_text = null;
			}
			$message_text .= $line . HL7Message::SEGMENT_DELIMITER;
		}
		$this->consumeMessage( $message_text );
	}

	/**
	 * get line from stdin up to but not including \r\n, \r, or \n
	 *
	 * @return string|false	the line just read not including newline string, false at EOF
	 */
	private function get_line_from_stdin() {
///*
		static $buffer = '';
		while( !feof( $this->Dispatch->stdin )) {
			$c = fgetc( $this->Dispatch->stdin );
			if( $c == "\r" ) {
				$ret = $buffer;
				$buffer = '';
				if( feof( $this->Dispatch->stdin ))
					return $ret;
				$c = fgetc( $this->Dispatch->stdin );
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
//*/
/*
		static $i = 0;
		static $x = array(
				'MSH|^~\&|MacPractice|MacPractice, Inc.|||20120807141331|NO SECURITY|SIU^S17|11|P|2.5.1||||||UNICODE UTF-8|||',
				'SCH|688||||||^ROUTINE^^^^^|NORMAL|||^^^20120806153000^20120807155000^^^^^^^||||||||||||||',
				'PID|1|63-1|63-1|63-1|Blow^Joe^J||20000801000000|M|||Street Address^Suite^Lincoln^NE^68516^^M||(555)444-3333^PRN^PH^joe.blow@email.com^^555^4443333^|(333)444-5555^WPN^PH^^^333^4445555^||U||63-1|123-45-6789|||U^Unknown^HL70189||||||||N',
				'RGS|1|4|',
				'AIP|4|1|RL^Le^Ryan^^^^|||20120806155000|||70|min||',
				);
		return $i < count( $x ) ? $x[$i++] : false;
//*/
	}

	/**
	 * commit the given message into our database
	 *
	 * @param string $message_text
	 */
	private function consumeMessage( $message_text ) {
		if( is_null( $message_text ))
			return;
		$hl7 = HL7MessageIncoming::createFromMessageText( $message_text );
		if( is_object( $hl7 )) {
			$this->err( $hl7->commitMessage() );
		} else {
			$this->err( 'not parsable' );
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