<?php
App::import('Lib', 'HL7MessageADT', array('file'=>'HL7MessageADT.php'));
App::import('Lib', 'HL7MessageSIU', array('file'=>'HL7MessageSIU.php'));
App::import('Lib', 'HL7MessageSIU_S17', array('file'=>'HL7MessageSIU_S17.php'));
App::import('Lib', 'HL7MessageDFT', array('file'=>'HL7MessageDFT.php'));

class HL7MessageIncoming extends HL7Message {

	/**
	 * Factory to receive any HL7 message that we can understand in preparation for commitMessage
	 *
	 * @param string $message_text 	Well-formed HL7 message (CR-separated segments starting with MSH)
	 * @return HL7MessageXXX 		Returns HL7 message class of appropriate type based on message contents
	 */
	public static function createFromMessageText($message_text) {
		// NOTE:  We are allowing non-standard segment delimiters, i.e., allow \n or \r\n instead of HL7 standard \r (aka self::SEGMENT_DELIMITER).  This approach assumes no valid \n characters in contents of message.
		$seg_delim     = strpos($message_text, "\n") === false ? "\r" : (strpos($message_text, "\r\n") === false ? "\n" : "\r\n");
		$segments_text = explode($seg_delim, $message_text);

		$hl7               = new HL7MessageIncoming();
		$hl7->message_text = $message_text;
		$segments          = $hl7->parseMessage($segments_text);
		$hl7->interpretMSH($segments[0]); // interpret the message header segment so we know message_type (interpretation of the subsequent segments is left to subclasses)

		// dispatch to correct subclass based on message type
		switch($hl7->message_type) {
			// treat all ADT events that we know about the same, i.e., add/modify patient
			case array('ADT', 'A01'):
			case array('ADT', 'A04'):
			case array('ADT', 'A08'):
			case array('ADT', 'A28'):
			case array('ADT', 'A31'):
				$hl7 = HL7MessageADT::interpretMessage( $hl7, $segments );
				break;

			// S12 and S14 SIU events: add/modify appointment
			case array('SIU', 'S12'):
			case array('SIU', 'S14'):
				$hl7 = HL7MessageSIU::interpretMessage( $hl7, $segments );
				break;

			// S15 and S17 SIU events: cancel/delete appointment
			case array('SIU', 'S15'):
			case array('SIU', 'S17'):
				$hl7 = HL7MessageSIU_S17::interpretMessage( $hl7, $segments );
				break;
				
			// P03 DFT event
			case array('DFT', 'P03'):
				$hl7 = HL7MessageDFT::interpretMessage( $hl7, $segments );
				break;

			default:
				break;  // default: return the generic HL7Message
		}
		return $hl7;
	}

	/**
	 * Parse the HL7 text in segments_text to structured data in $this->segments
	 *
	 * @param $segments_text array[string]     	the text of the HL7 segments to be parsed
	 * @return HL7 segments
	 */
	protected function parseMessage($segments_text) {
		// verify that message begins with MSH segment
		$msh = $segments_text[0];
		if( substr($msh, 0, 3) != 'MSH' )
			throw new Exception("HL7 message needs to start with well-formed MSH segment");

			// next field is the delimiter definition set, typically "|^~\&"
		$this->field_delimiter        = $msh{3};
		$this->component_delimiter    = $msh{4};
		$this->repeat_delimiter       = $msh{5};
		$this->escape_character       = $msh{6};
		$this->subcomponent_delimiter = $msh{7};

		// now we can actually parse the segments

		/*
		 * Do a nasty little hand-crafted lexer here to handle the
		 * omission/repeat complexities as well as escapes. We choose not to
		 * rely on any HL7 data definitions here since it appears that the HL7
		 * way to deal with ill-formed segments is to parse them as though the
		 * data definition supported the ill-formed segment, so the logic for
		 * interpreting that should be in the model interpretation code, e.g.,
		 * if repeats are not supported for a field that has them, the model
		 * code should just take the first non-empty value.
		 *
		 * Structure of the results of parsing are one of the following for each field:
		 * 		segments[i][j] 			string 			means field.j of ith segment value is that string (no repeats, no components)
		 * 		segments[i][j][r] 		string 			means field.j of ith segment repeat #r value is that string (no components)
		 * 		segments[i][j][r][k] 	string			means field.j of ith segment repeat #r kth component value is that string (no subcomponents)
		 * 		segemets[i][j][r][k][h]	string			means field.j of ith segment repeat #r kth component, hth subcomponent value is that string
		 */

		// hand build MSH.1 and MSH.2 from above while initializing lexer loop
		$segments_text[0] = substr($msh, 9);
		$fields = array(
				'MSH',
				$this->field_delimiter . $this->component_delimiter . $this->repeat_delimiter . $this->escape_character . $this->subcomponent_delimiter
		);
		$repeats       = null;
		$components    = null;
		$subcomponents = null;
		$buffer        = null;
		$segments      = null;

		// main lexer loop
		foreach( $segments_text as $segment_text ) {
			$segment_text .= $this->field_delimiter; // so we always terminate the last field inside the foreach below (and extra field would always be ok)
			for($ci = 0; $ci < strlen($segment_text); $ci++) {
				$ch = $segment_text[$ci];
				switch($ch) {
					case $this->subcomponent_delimiter:
						$subcomponents[] = $buffer;
						$buffer          = null;
						break;

					case $this->component_delimiter:
						if( !is_null($subcomponents) ) {
							$subcomponents[] = $buffer;
							$buffer          = null;
							$components[]    = $subcomponents;
							$subcomponents   = null;
						} else {
							$components[]    = $buffer;
							$buffer          = null;
						}
						break;

					case $this->repeat_delimiter:
						if( !is_null($subcomponents) ) {
							$subcomponents[] = $buffer;
							$buffer          = null;
							$components[]    = $subcomponents;
							$subcomponents   = null;
							$repeats[]       = $components;
							$components      = null;
						} else if( !is_null($components) ) {
							$components[]    = $buffer;
							$buffer          = null;
							$repeats[]       = $components;
							$components      = null;
						} else {
							$repeats[]       = $buffer;
							$buffer          = null;
						}
						break;

					case $this->field_delimiter:
						if( !is_null($subcomponents) ) {
							$subcomponents[] = $buffer;
							$buffer          = null;
							$components[]    = $subcomponents;
							$subcomponents   = null;
							$repeats[]       = $components;
							$components      = null;
							$fields[]        = $repeats;
							$repeats         = null;
						} else if( !is_null($components) ) {
							$components[]    = $buffer;
							$buffer          = null;
							$repeats[]       = $components;
							$components      = null;
							$fields[]        = $repeats;
							$repeats         = null;
						} else if( !is_null($repeats) ) {
							$repeats[]       = $buffer;
							$buffer          = null;
							$fields[]        = $repeats;
							$repeats         = null;
						} else {
							$fields[]        = $buffer;
							$buffer          = null;
						}
						break;

					case $this->escape_character:
						$buffer .= $segment_text[++$ci]; // note that if the string ends with an escape, the trailing escape character is just discarded
						break;

					default:
						$buffer .= $ch;
						break;
				}
			}
			$segments[] = $fields;
			$fields = array();
		}
		return $segments;
	}
}
