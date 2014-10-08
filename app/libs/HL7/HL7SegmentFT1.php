<?php
App::import('Lib', 'HL7Segment', array('file'=>'HL7Segment.php'));

class HL7SegmentFT1 extends HL7Segment {
	const SEGMENT_TYPE = 'FT1';
	public $calendar_id;
	public $patient_id;
	public $encounter_id;
	public $start;					// FT1.4.DR.1
	public $end;					// FT1.4.DR.2
	public $posting_date;			// FT1.5.TS
	public $units;					// FT1.10.NM
	public $diagnosis_code;			// FT1.19.CE0051.1  (icd code, can be array for multiple)
	public $diagnosis_desc;			// FT1.19.CE0051.2
	public $performed_by_id;		// FT1.20.XCN.1
	public $performed_by_last;		// FT1.20.XCN.2
	public $performed_by_first;		// FT1.20.XCN.3
	public $unit_cost;				// FT1.22.CP
	public $procedure_code;			// FT1.25.CD0088.1 (cpt code)
	public $procedure_desc;			// FT1.25.CD0088.2 (cpt code description)
	public $modifier_code;			// FT1.26.CE0340.1 (can be array for multiple)
	public $modifier_desc;			// FT1.26.CE0340.2 (modifier description, needs to match structure of modifier_code)
	
	/**
	 * interpret the given parsed $segment into $this properties
	 *
	 * @param array $segments			Parsed HL7 message (with correct message_type)
	 * @return false|HL7Message
	 */
	public function interpret( $segment ) {
		if( $segment[0] != self::SEGMENT_TYPE )
			throw new Exception( "Message segment must start with ".self::SEGMENT_TYPE );
		while( count($segment) < 27 ) {
			$segment[] = null;
		}
		$dummy1 = $dummy2 = null;
		HL7Message::interpretDR( $segment[ 4], $this->start, $this->end );
		$this->posting_date = HL7Message::interpretTS( $segment[ 5] );
		HL7Message::interpretPairedTable( $segment[19], $this->diagnosis_code, $this->diagnosis_desc );
		HL7Message::interpretXCN( $segment[20], $this->performed_by_last, $this->performed_by_first, $dummy1, $dummy2, $this->performed_by_id );
		HL7Message::interpretPairedTable( $segment[25], $this->procedure_code, $this->procedure_desc );
		HL7Message::interpretPairedTable( $segment[26], $this->modifier_code,  $this->modifier_desc  );
		$this->units		= $segment[10];
		$this->unit_cost	= $segment[22];
	}
	
	/**
	 * create a new array HL7MessageFT1 from the encounter where key = $encounter_id
	 *
	 * @param int $encounter_id			Primary key id for EncounterMaster model
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return array|false
	 */
	public static function createFromDb( $encounter_id, $receiver = null, $processing_id = HL7Message::PT_DEBUGGING ) {
		return self::createFromDbP( $encounter_id );
	}

	/**
	 * create a new array HL7MessageFT1 from the encounter where key = $encounter_id
	 *
	 * @param int $encounter_id			Primary key id for EncounterMaster model
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @param string $default_units		null or "1", what to send in FT1.10, transaction quantity, for procedures
	 * @return array|false
	 */
	protected static function createFromDbP( $encounter_id, $default_units = '1', $doc_id_field = 'user_id' ) {
	
		// Get list of procedures to bill.  Each of these gets its own FT1.
		$mn  = 'EncounterSuperbill';
		$m   = ClassRegistry::init( $mn );
		$superbill = $m->findByEncounterId( $encounter_id );
		$procedures = null;
		// 1. superbill.service_level, e.g., '99202 (Level 2 [New])'
		$value = $superbill[$mn]['service_level'];
		if( !empty( $value )) {
			preg_match( "/^(.*)\s+\((.*)\)$/", $value, $matches );
			if( isset( $matches[1] ))
				$procedures[$matches[1]] = array( 'desc' => $matches[2] );
		}
		// 2. superbill.service_level_advanced, json_encoded array, e.g., '["99050 (After posted hours)","99024 (Post-op follow-up)"]'
		$value = $superbill[$mn]['service_level_advanced'];
		if( !empty( $value )) {
			$values = json_decode( $value, true );
			foreach( $values as $value ) {
				preg_match( "/^(.*)\s+\((.*)\)$/", $value, $matches );
				if( isset( $matches[1] ))
					$procedures[$matches[1]] = array( 'desc' => $matches[2] );
			}
		}
		// 3. superbill.other_codes, json_encoded associative array, e.g., [{"description":"","code":"55454"},{"description":"","code":""},{"description":"","code":""}]
		$value = $superbill[$mn]['other_codes'];
		if( !empty( $value )) {
			$values = json_decode( $value, true );
			foreach( $values as $value ) {
				if( !empty( $value['code'] ))
					$procedures[$value['code']] = array( 'desc' => $value['description'] );
			}
		}
		// 4. POC immunizations, labs, radiology, procedure, meds, injection, supplies
		$sb = $superbill['EncounterSuperbill'];
		$mn = 'EncounterPointOfCare';
		$m = ClassRegistry::init( $mn );
		$gets = $m->findAllByEncounterId( $encounter_id );
		foreach( $gets as $get ) {
			$codes = self::getCode( $get[$mn] );
			foreach( $codes as $code )
			{
				if( isset( $procedures[$code] )) {
					if( isset( $procedures[$code]['repeat'] ))
						$procedures[$code]['repeat']++;
					else
						$procedures[$code]['repeat'] = 1;
					continue;
				}
				switch( $get[$mn]['order_type'] ) {
					case 'Immunization':
						$procedures[$code] = array(
								'desc' 	=> $get[$mn]['vaccine_name'],
								'from' 	=> $get[$mn]['vaccine_date_performed'],
								'to' 	=> $get[$mn]['vaccine_expiration_date'],
								'diag' 	=> $get[$mn]['vaccine_reason'],
								'fee' 	=> in_array( $get[$mn]['vaccine_name'], json_decode( $sb['ignored_in_house_immunizations'], true )) ? '0.00' : $get[$mn]['fee'] );
						break;
					case 'Labs':
						$procedures[$code] = array(
								'desc'	=> $get[$mn]['lab_test_name'],
								'from'	=> $get[$mn]['lab_date_performed'],
								'diag'	=> $get[$mn]['lab_reason'],
								'units'	=> $get[$mn]['lab_unit'],
								'fee'	=> in_array( $get[$mn]['lab_test_name'], json_decode( $sb['ignored_in_house_labs'], true )) ? '0.00' : $get[$mn]['fee']	);
						break;
					case 'Radiology':
						$procedures[$code] = array(
								'desc'	=> $get[$mn]['radiology_procedure_name'],
								'from'	=> $get[$mn]['radiology_date_performed'],
								'diag'	=> $get[$mn]['radiology_reason'],
								'fee'	=> in_array( $get[$mn]['radiology_procedure_name'], json_decode( $sb['ignored_in_house_radiologies'], true )) ? '0.00' : $get[$mn]['fee']	);
						break;
					case 'Procedure':
						$procedures[$code] = array(
								'desc'	=> $get[$mn]['procedure_name'],
								'from'	=> $get[$mn]['procedure_date_performed'],
								'diag'	=> $get[$mn]['procedure_reason'],
								'mod'	=> $get[$mn]['modifier'],
								'units'	=> $get[$mn]['procedure_unit'],
								'fee'	=> in_array( $get[$mn]['procedure_name'], json_decode( $sb['ignored_in_house_procedures'], true )) ? '0.00' : $get[$mn]['fee']	);
						break;
					case 'Meds':
						$procedures[$code] = array(
								'desc'	=> $get[$mn]['drug'],
								'from'	=> $get[$mn]['drug_date_given'],
								'diag'	=> $get[$mn]['drug_reason'],
								'units'	=> $get[$mn]['quantity'],
								'fee'	=> in_array( $get[$mn]['drug'], json_decode( $sb['ignored_in_house_meds'], true )) ? '0.00' : $get[$mn]['fee']	);
						break;
					case 'Injection':
						$procedures[$code] = array(
								'desc'	=> $get[$mn]['injection_name'],
								'from'	=> $get[$mn]['injection_date_performed'],
								'diag'	=> $get[$mn]['injection_reason'],
								'units'	=> $get[$mn]['injection_unit'],
								'fee'	=> in_array( $get[$mn]['injection_name'], json_decode( $sb['ignored_in_house_injections'], true )) ? '0.00' : $get[$mn]['fee']	);
						break;
					case 'Supplies':
						$procedures[$code] = array(
								'desc'	=> $get[$mn]['supply_name'],
								'units'	=> $get[$mn]['supply_quantity'],
								'fee'	=> in_array( $get[$mn]['supply_name'], json_decode( $sb['ignored_in_house_supplies'], true )) ? '0.00' : $get[$mn]['fee']	);
						break;
					default:
						break;
				}
			}
		}
		// 5. plan lab, radiology, procedure
		foreach( array( 'Lab' => 'test', 'Radiology' => 'procedure', 'Procedure' => 'test' ) as $pmn => $dfn )
		{
			$mn = "EncounterPlan$pmn";
			$m = ClassRegistry::init( $mn );
			$gets = $m->findAllByEncounterId( $encounter_id );
			foreach( $gets as $get ) {
				$codes = self::getCode( $get[$mn] );
				foreach( $codes as $code ) {
					if( isset( $procedures[$code] )) {
						if( isset( $procedures[$code]['repeat'] ))
							$procedures[$code]['repeat']++;
						else
							$procedures[$code]['repeat'] = 1;
						continue;
					}
					$procedures[$code] = array(
							'desc'	=> $get[$mn][$dfn . '_name'],
							'diag'	=> $get[$mn]['diagnosis'] );
				}
			}
		}
		if( is_null( $procedures ))
			return array();		// so why is there a superbill??

		// construct the common elements of each FT1
		$cl  	= get_called_class();
		$mn0 	= 'EncounterMaster';
		$m   	= ClassRegistry::init( $mn0 );
		$mcode 	= ClassRegistry::init( 'Cpt4' );
		$get 	= $m->findByEncounterId( $encounter_id );
		if( false === $get )
			return false;
		$common  				= new $cl();
		$common->encounter_id	= $encounter_id;
		$common->patient_id 	= $get[$mn0]['patient_id'];
		$common->calendar_id 	= $get[$mn0]['calendar_id'];
		$common->start		 	= new DateTime( $get[$mn0]['encounter_date'] );
		$common->event_timestamp = $get[$mn0]['modified_timestamp'];
		
		// get doc from UserAccount
		$mn1						= 'UserAccount';
		$common->performed_by_id 	= $get[$mn1][$doc_id_field];
		$common->performed_by_last 	= $get[$mn1]['lastname'];
		$common->performed_by_first = $get[$mn1]['firstname'];

		// get diagnosis from EncounterAssessment
		$mn1 = 'EncounterAssessment';
		switch( count( $get[$mn1] )) {
			case 0:
				break;
			case 1:
				$common->diagnosis_code = $get[$mn1][0]['icd_code'];
				$diag = isset( $get[$mn1][0]['diagnosis'] ) ? $get[$mn1][0]['diagnosis'] : $get[$mn1][0]['occurence'];
				$common->diagnosis_desc = substr( $diag, 0, strpos( $diag, ' [' ));
				break;
			default:
				foreach( $get[$mn1] as $assessment ) {
					if( empty( $assessment['icd_code'] ))
						continue;
					$common->diagnosis_code[] = $assessment['icd_code'];
					$diag = isset( $assessment['diagnosis'] ) ? $assessment['diagnosis'] : $assessment['occurence'];
					$common->diagnosis_desc[] = substr( $diag, 0, strpos( $diag, ' [' ));
				}
				if( count( $common->diagnosis_code ) == 1 ) {
					$common->diagnosis_code = $common->diagnosis_code[0];
					$common->diagnosis_desc = $common->diagnosis_desc[0];
				}
				break;
		}

		// Generate an FT1 for each procedure
		$ft1s 	= array();
		foreach( $procedures as $procedure => $details ) {
			$me = clone $common;
			$me->procedure_code	= $procedure;
			if( !isset( $details['desc'] ) || empty( $details['desc'] )) {
				$get  = $mcode->findByCode( $procedure );
				$details['desc'] = substr( $get['Cpt4']['description'], 0, 80 );		// FIXME:  arbitrarily truncate at 80 chars and no filtering for delimiters, etc. (which would have to happen in produceSegment below)
			}
			$me->procedure_desc = $details['desc'];
			if( isset( $details['from'] ) && !empty( $details['from'] ))
				$me->start = new DateTime( $details['from'] );
			if( isset( $details['to'] ) && !empty( $details['to'] ))
				$me->end = new DateTime( $details['to'] );
			if( isset( $details['diag'] ) && !empty( $details['diag'] )) {
				preg_match("/^(.*)\s+\[(.*)\]$/", $details['diag'], $matches );
				if( isset( $matches[2] )) {
					$me->diagnosis_code = $matches[2];
					$me->diagnosis_desc = $matches[1];
				} else {
					$me->diagnosis_code = null;
					$me->diagnosis_desc = $details['diag'];
				}
			}
			if( isset( $details['fee'] ))
				$me->unit_cost = $details['fee'];
			if( isset( $details['units'] ) && intval( $details['units'] ) > 0 ) {
				$me->units = $details['units'];
			} else {
				$me->units = $default_units;
			}
			if( isset( $details['mod'] )) {
				$ts = $ds = null;
				$ms = explode( ',', $details['mod'] );
				foreach( $ms as $item ) {
					$item = trim( $item );
					if( !empty( $item )) {
						$ts[] = $item;
						$ds[] = 'No Description';		// FIXME: do we look up modifier_code somewhere to find description?  Right now just sending this.
					}
				}
				if( count( $ts ) == 1 ) {
					$ts = $ts[0];
					$ds = $ds[0];
				}
				$me->modifier_code = $ts;
				$me->modifier_desc = $ds;
			}
			$me->posting_date = clone $me->start;
			$ft1s[] = $me;
			if( isset( $details['repeat'] )) {
				while( $details['repeat']-- )
					$ft1s[] = clone $me;
			}
		}
		return $ft1s;
	}

	/**
	 * Figure out what the procedure code is from the various possible columns
	 *
	 * @param array $value		Row from table
	 * @return array of string	The procedure codes
	 */
	private static function getCode( $value )
	{
		if( isset( $value['cpt'] ) && $value['cpt'] != '' ) {
			preg_match_all( "/(\d|[A-Za-z])\d{3}(\d|[A-Za-z])/", $value['cpt'], $matches );
			if( isset( $matches[0][0] ))
				return $matches[0];
			return array( $value['cpt'] );
		} else if( isset( $value['cpt_code'] ) && $value['cpt_code'] != '' )
			return array( $value['cpt_code'] );
		else if( isset( $value['code'] ) && $value['code'] != '' )
			return array( $value['code'] );
		return array( '' );
	}


	/**
	 * return the HL7 message text that corresponds to this object
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @param int $seqno		The ordinal number within the message for this FT1
	 * @return string			The segment text
	 */
	public function produceSegment( HL7Message $msh, $seqno = 1 ) {
		$fd = $msh->field_delimiter;
		$cd = $msh->component_delimiter;
		$rd = $msh->repeat_delimiter;
		$q = $fd . $cd . $rd . $msh->subcomponent_delimiter;
		$s  = self::SEGMENT_TYPE;
		$s .= $fd . $seqno;
		$s .= $fd . $this->encounter_id . '-' . $seqno;
		$s .= $fd . $this->encounter_id;				// FT1.3 transaction batch id

		// FT1.4.DR transaction date
		$s .= $fd . ( isset( $this->start ) ? $this->start->format( 'Ymd' ) : '' );
		if( isset( $this->end ))
			$s .= $cd . $this->end->format( 'Ymd' );

		$s .= $fd . ( isset( $this->posting_date ) ? $this->posting_date->format( 'Ymd' ) : '' );
		$s .= $fd . 'CG';	// FT1.6.IS0017, transaction type: Charge
		$s .= $fd;			// FT1.7.CE0132, transaction code
		$s .= $fd . $fd;
		$s .= $fd . $this->units;	// FT1.10.NM, transaction quantity
		$s .= str_repeat( $fd, 8 );

		// FT1.19.CE0051, diagnosis code
		$s .= $fd;
		if( is_array( $this->diagnosis_code )) {
			$s .= addcslashes( $this->diagnosis_code[0], $q ) . $cd . addcslashes( $this->diagnosis_desc[0], $q );
			for( $i = 1; $i < count( $this->diagnosis_code ); ++$i )
				$s .= $rd . addcslashes( $this->diagnosis_code[$i], $q ) . $cd . addcslashes( $this->diagnosis_desc[$i], $q );
		} else {
			$s .= addcslashes( $this->diagnosis_code, $q ) . $cd . addcslashes( $this->diagnosis_desc, $q );
		}

		$s .= $fd . $this->performed_by_id . $cd . $this->performed_by_last . $cd . $this->performed_by_first;		// FT1.20.XCN, performed by
		$s .= $fd . $this->performed_by_id . $cd . $this->performed_by_last . $cd . $this->performed_by_first;		// FT1.21.XCN, ordered by  FIXME: ordered_by same as performed_by?
		$s .= $fd . $this->unit_cost;			// FT1.22.CP
		$s .= $fd;
		$s .= $fd;

		// FT1.25.CE0088, procedure code
		$s .= $fd;
		if( is_array( $this->procedure_code )) {
			$s .= addcslashes( $this->procedure_code[0], $q ) . $cd . addcslashes( $this->procedure_desc[0], $q );
			for( $i = 1; $i < count( $this->procedure_code ); ++$i )
				$s .= $rd . addcslashes( $this->procedure_code[$i], $q ) . $cd . addcslashes ( $this->procedure_desc[$i], $q );
		} else {
			$s .= addcslashes( $this->procedure_code, $q ) . $cd . addcslashes( $this->procedure_desc, $q );
		}
		
		// FT1.26.CE0340
		$s .= $fd;
		if( is_array( $this->modifier_code )) {
			$s .= addcslashes( $this->modifier_code[0], $q );
			if( !empty( $this->modifier_desc[0] ))
				$s .= $cd . addcslashes( $this->modifier_desc[0], $q );
			for( $i = 1; $i < count( $this->modifier_code ); ++$i ) {
				$s .= $rd . addcslashes( $this->modifier_code[$i], $q );
				if( !empty( $this->modifier_desc[$i] ))
					$s .= $cd . addcslashes ( $this->modifier_desc[$i], $q );
			}
		} else if( !is_null( $this->modifier_code )) {
			$s .= addcslashes( $this->modifier_code, $q );
			if( !empty( $this->modifier_desc ))
				$s .= $cd . addcslashes( $this->modifier_desc, $q );
		}
				
		return $s . $fd;
	}
	
	/**
	 * Is this semantically the same as other?
	 * 
	 * @param unknown $other
	 * @return boolean true if the same
	 */
	public function equal( $other ) {
		if( !is_object( $other ))
			return false;
		if( get_class( $this ) != get_class( $other ))
			return false;
		$compare_fields = array(
//				'encounter_id',			FIXME: may not be filled in, but ok for what we are using this for
				'diagnosis_code',
				'performed_by_id',
				'unit_cost',
				'procedure_code',
				'modifier_code'
		);
		foreach( $compare_fields as $fn ) {
			if( $this->{$fn} != $other->{$fn} )
				return false;
		}
		$compare_fields = array(
				'start',
				'end' );
		foreach( $compare_fields as $fn ) {
			$a = ( is_null(  $this->{$fn} ) ? null :  $this->{$fn}->format( 'Ymd' ));
			$b = ( is_null( $other->{$fn} ) ? null : $other->{$fn}->format( 'Ymd' ));
			if( $a != $b )
				return false;
		}
		return true;
	}
}
