<?php
App::import('Lib', 'HL7Segment', array('file'=>'HL7Segment.php'));

class HL7SegmentAIP extends HL7Segment {
	const SEGMENT_TYPE = 'AIP';
	public $provider_id;			// our provider_id
	public $their_id;				// AIP.3.XCN.1
	public $last_name;				// AIP.3.XCN.2
	public $first_name;				// AIP.3.XCN.3
	public $start_time;				// AIP.6.TS
	public $end_time;				// $start_time + $duration
	public $duration;				// AIP.9.NM and AIP.10.CE

	/**
	 * interpret the given parsed $segment into $this properties
	 *
	 * @param array $segments			Parsed HL7 message (with correct message_type)
	 * @return false|HL7Message
	 */
	function interpret( $segment ) {
		if( $segment[0] != self::SEGMENT_TYPE )
			throw new Exception("Message segment must start with ".self::SEGMENT_TYPE);
		while( count($segment) < 11 ) {
			$segment[] = null;
		}
		$middle_name = $suffix = null;
		HL7Message::interpretXCN( $segment[3], $this->last_name, $this->first_name, $middle_name, $suffix, $this->their_id );
		$this->start_time 	= HL7Message::interpretTS( $segment[6] );
		$this->duration 	= HL7Message::interpretDuration( $this->start_time, $this->end_time, $segment[9], HL7Message::interpretCE( $segment[10] ));
		
		// Save their_id as custom_provider_id in user_account table for this provider
		$mn 		= 'UserAccount';
		$user_model = ClassRegistry::init( $mn );
		$conditions	= array( "$mn.lastname" => $this->last_name, "$mn.firstname" => $this->first_name );
		$get 		= $user_model->find( 'first', array( 'conditions' => $conditions ));
		if( false !== $get ) {
			$this->provider_id = $get[$mn]['user_id'];
			if( !is_null( $this->their_id ) && $get[$mn]['custom_provider_id'] != $this->their_id ) {
				$get[$mn]['custom_provider_id'] = $this->their_id;
				$user_model->save( $get );
			}
		}
	}

	/**
	 * Make the segment text for the AIP segment to be sent.
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The AIP segment text
	 */
	public function produceSegment( HL7Message $msh ) {
		$fd = $msh->field_delimiter;
		$cd = $msh->component_delimiter;
		$s  = self::SEGMENT_TYPE;
		$s .= $fd . '1';
		$s .= $fd . 'A';		// action code: Add
		$s .= $fd . $this->provider_id . $cd . $this->last_name . $cd . $this->first_name;
		$s .= $fd . $cd . 'PROVIDER';
		$s .= $fd;
		$s .= $fd . ( isset( $this->start_time ) ? $this->start_time->format( 'YmdHi' ) : '' );
		$s .= $fd . $fd;
		$s .= $fd . $this->duration;
		$s .= $fd . 'min';
		return $s;
	}
}