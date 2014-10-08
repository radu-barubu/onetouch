<?php
App::import('Lib', 'HL7Segment', array('file'=>'HL7Segment.php'));

class HL7SegmentIN1 extends HL7Segment {
	const SEGMENT_TYPE = 'IN1';
	public $company_name;			// IN1.4.XON
	public $company_address1;		// IN1.5.XAD.1
	public $company_address2;		// IN1.5.XAD.2
	public $company_city;			// IN1.5.XAD.3
	public $company_state;			// IN1.5.XAD.4
	public $company_zipcode;		// IN1.5.XAD.5
	public $company_contact_person;	// IN1.6.XPN
	public $company_phone;			// IN1.7.XTN
	public $company_email;			// IN1.7.XTN.4
	public $group_number;			// IN1.8.ST
	public $group_name;				// IN1.9.XON
	public $employer;				// IN1.11.XON
	public $start_date;				// IN1.12.DT
	public $end_date;				// IN1.13.DT
	public $type;					// IN1.15.IS0086
	public $insured_last_name;		// IN1.16.XPN.1
	public $insured_first_name;		// IN1.16.XPN.2
	public $insured_middle_name;	// IN1.16.XPN.3
	public $insured_suffix;			// IN1.16.XPN.4
	public $insured_relationship;	// IN1.17.CE0063
	public $insured_dob;			// IN1.18.TS
	public $insured_address1;		// IN1.19.XAD.1
	public $insured_address2;		// IN1.19.XAD.2
	public $insured_city;    		// IN1.19.XAD.3
	public $insured_state;   		// IN1.19.XAD.4
	public $insured_zipcode; 		// IN1.19.XAD.5
	public $policy_number;			// IN1.36.ST
	public $insured_employ_status;	// IN1.42.CE0066
	public $insured_gender;      	// IN1.43.IS0001
	public $insured_id;          	// IN1.49.CX

	/**
	 * interpret the given parsed $segment into $this properties
	 *
	 * @param array $segments			Parsed HL7 message (with correct message_type)
	 * @return false|HL7Message
	 */
	function interpret($segment) {
		if( $segment[0] != self::SEGMENT_TYPE )
			throw new Exception("Message segment must start with ".self::SEGMENT_TYPE);
		while( count($segment) < 50 ) {
			$segment[] = null;
		}
		$dummy = null;
		HL7Message::interpretXAD($segment[ 5], $this->company_address1, $this->company_address2, $this->company_city, $this->company_state, $this->company_zipcode);
		HL7Message::interpretXAD($segment[19], $this->insured_address1, $this->insured_address2, $this->insured_city, $this->insured_state, $this->insured_zipcode);
		HL7Message::interpretXPN($segment[16], $this->insured_last_name, $this->insured_first_name, $this->insured_middle_name, $this->insured_suffix);
		$this->company_name				= HL7Message::interpretXON(   $segment[ 4]);
		$this->company_contact_person	= HL7Message::interpretXPN(   $segment[ 6]);
		$this->company_phone			= HL7Message::interpretXTN(   $segment[ 7], $dummy, $this->company_email);
		$this->group_number				=                             $segment[ 8];
		$this->group_name				= HL7Message::interpretXON(   $segment[ 9]);
		$this->employer					= HL7Message::interpretXON(   $segment[11]);
		$this->start_date				= HL7Message::interpretTS(    $segment[12]);
		$this->end_date					= HL7Message::interpretTS(    $segment[13]);
		$this->type						=                             $segment[15];
		$this->insured_relationship		= HL7Message::interpretCE(    $segment[17], '0063' );
		$this->insured_dob				= HL7Message::interpretTS(    $segment[18]);
		$this->policy_number			=                             $segment[36];
		$this->insured_employ_status	= HL7Message::interpretCE(    $segment[42], '0066' );
		$this->insured_gender			= HL7Message::interpret0001(  $segment[43]);
		$this->insured_id				= HL7Message::interpretCX(    $segment[49]);
		
		// pull off annoying initial asterisk
		if( !empty( $this->company_name) && $this->company_name[0] == '*' )
			$this->company_name = substr( $this->company_name, 1 );
	}

	/**
	 * commit this segment
	 *
	 * @param string $patient_id
	 * @param int $priority			priority number where 1 is 'primary', 2 is 'secondary', etc.
	 * @return string				log of actions taken
	 */
	public function commitSegment( $patient_id, $priority = 1 ) {
		switch( (int) $priority ) {
			case 1: 	$priority = 'Primary'; 		break;
			case 2: 	$priority = 'Secondary';	break;
			case 3: 	$priority = 'Tertiary';		break;
			default:	$priority = 'Other';		break;
		}
		$mn		= 'PatientInsurance';
		$m		= ClassRegistry::init( $mn );
		$story 	= $mn;

		$goal	= array( $mn => array(
				'patient_id'				=> $patient_id,

				'payer'						=> $this->company_name,
				'priority'					=> $priority,
				'type'						=> $this->type,
				'relationship'				=> HL7Message::getRelationshipCode( $this->insured_relationship ),
				'insured_first_name'		=> $this->insured_first_name,
				'insured_middle_name'		=> $this->insured_middle_name,
				'insured_last_name'			=> $this->insured_last_name,
				'insured_name_suffix'		=> $this->insured_suffix,
				'insured_birth_date'		=> is_null( $value = $this->insured_dob ) ? null : $value->format( 'Y-m-d' ),
				'insured_sex'				=> $this->insured_gender,
				'insured_address_1'			=> $this->insured_address1,
				'insured_address_2'			=> $this->insured_address2,
				'insured_city'				=> $this->insured_city,
				'insured_state'				=> $this->insured_state,
				'insured_zip'				=> $this->insured_zipcode,
				'start_date'				=> is_null( $value = $this->start_date ) ? null : $value->format( 'Y-m-d' ),
				'end_date'					=> is_null( $value = $this->end_date ) ? null : $value->format( 'Y-m-d' ),
				'group_id'					=> $this->group_number,
				'group_name'				=> $this->group_name,
				'employer_name'				=> $this->employer,
				'policy_number'				=> $this->policy_number,
				'insured_employment_status'	=> $this->insured_employ_status,
				'insured_employee_id'		=> $this->insured_id,
				'status'					=> 'Active' )

				// other fields without corresponding data in IN1 segment:
					// 'insurance'
					// 'date'
					// 'ownerid'
					// 'clearance'
					// 'isphsi'
					// 'person'
					// 'last_used_date'
					// 'isp'
					// 'payer'
					// 'insurance_code'
					// 'plan_identifier'
					// 'plan_name'
					// 'insured_ssn'
					// 'insured_home_phone_number'
					// 'insured_work_phone_number'
					// 'policy_number'
					// 'payment_type'
					// 'copay_amount'
					// 'copay_percentage'
					// 'insurance_card_front'
					// 'insurance_card_back'
					// 'notes'
					// 'texas_vfc_status'
					// 'use_default'
					// 'kareo_insurance_id'
				 );

		// we will replace/create the record for this patient with the given priority
		$story .= HL7Message::commitData( $m, $goal, array( 'patient_id', 'priority' ));
		$get = $m->findByInsuranceInfoId( $m->id );
		$this->event_timestamp = $get[$mn]['modified_timestamp'];
		return $story;
	}

	/**
	 * create a new HL7SegmentIN1 array from PatientInsurance model where patient_id is $patient_id
	 *
	 * @param int $patient_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return false|array
	 */
	public static function createFromDb( $patient_id, $receiver, $processing_id ) {
		$cl = get_called_class();
		$mn = 'PatientInsurance';
		$m  = ClassRegistry::init( $mn );
		$gets = $m->findAllByPatientId( $patient_id );
		if( false === $gets )
			return false;
		$result = array();
		foreach( $gets as $get ) {
			$me = new $cl();
			$pd = $get[$mn];
			foreach( array(
					'patient_id'				=> 'patient_id',
					'payer'						=> 'company_name',
					'type'						=> 'type',
					'insured_first_name'		=> 'insured_first_name',
					'insured_middle_name'		=> 'insured_middle_name',
					'insured_last_name'			=> 'insured_last_name',
					'insured_name_suffix'		=> 'insured_suffix',
					'insured_sex'				=> 'insured_gender',
					'insured_address_1'			=> 'insured_address1',
					'insured_address_2'			=> 'insured_address2',
					'insured_city'				=> 'insured_city',
					'insured_state'				=> 'insured_state',
					'insured_zip'				=> 'insured_zipcode',
					'group_id'					=> 'group_number',
					'group_name'				=> 'group_name',
					'employer_name'				=> 'employer',
					'insured_employment_status'	=> 'insured_employ_status',
					'insured_employee_id'		=> 'insured_id',
					'policy_number'				=> 'policy_number',
					'modified_timestamp'		=> 'event_timestamp'
					) as $dbfn => $myfn ) {
				$me->$myfn = $pd[$dbfn];
			}
			$me->insured_dob		  = isset( $pd['insured_birth_date'] ) 	? new DateTime( $pd['insured_birth_date'] )	: null;
			$me->start_date			  = isset( $pd['start_date'] )			? new DateTime( $pd['start_date'] )			: null;
			$me->end_date			  = isset( $pd['end_date'] )			? new DateTime( $pd['end_date'] )			: null;
			$me->insured_relationship = HL7Message::getRelationshipDescription( $pd['relationship'] );
			$result[] = $me;
		}
		return $result;
	}

	/**
	 * Make the segment text for the IN1 segment to be sent.
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The IN1 segment text
	 */
	public function produceSegment( HL7Message $msh, $seqno ) {
		$fd = $msh->field_delimiter;
		$cd = $msh->component_delimiter;
		$q = $fd . $cd . $msh->repeat_delimiter . $msh->subcomponent_delimiter;
		$s  = self::SEGMENT_TYPE;
		$s .= $fd . $seqno;																				// IN1.1
		$s .= $fd . '0';   																				// IN1.2 plan id (required)
		$s .= $fd . '0';   																				// IN1.3 company id (required)
		$s .= $fd . addcslashes( $this->company_name, $q );																// IN1.4.XON
		$s .= $fd . addcslashes( $this->company_address1, $q ) . $cd . addcslashes( $this->company_address2, $q ) . $cd . $this->company_city . $cd . $this->company_state . $cd . $this->company_zipcode;		// IN1.5.XAD
		$s .= $fd . addcslashes( $this->company_contact_person, $q );														// IN1.6.XPN  FIXME: explode into last_name, first_name?
		$s .= $fd . $this->company_phone. $cd . $cd . $cd . addcslashes( $this->company_email, $q );						// IN1.7.XTN
		$s .= $fd . addcslashes( $this->group_number, $q );																// IN1.8.ST
		$s .= $fd . addcslashes( $this->group_name, $q );																	// IN1.9.XON
		$s .= $fd;																						// IN1.10
		$s .= $fd . $this->employer;																	// IN1.11.XON
		$s .= $fd . ( isset( $this->start_date ) ? $this->start_date->format( 'Ymd' ) : '' );			// IN1.12.DT
		$s .= $fd . ( isset( $this->end_date )   ? $this->end_date->format( 'Ymd' )   : '' );			// IN1.13.DT
		$s .= $fd;																						// IN1.14
		$s .= $fd . $this->type;																		// IN1.15.IS0086
		$s .= $fd . $this->insured_last_name . $cd . $this->insured_first_name . $cd . $this->insured_middle_name . $cd . $this->insured_suffix;			// IN1.16.XPN
		$s .= $fd . HL7Message::encodeCE( $this->insured_relationship, $cd, '0063' );					// IN1.17.CE0063
		$s .= $fd . ( isset( $this->insured_dob ) ? $this->insured_dob->format( 'Ymd' ) : '' );			// IN1.18.TS
		$s .= $fd . addcslashes( $this->insured_address1, $q ) . $cd . addcslashes( $this->insured_address2, $q ) . $cd . $this->insured_city . $cd . $this->insured_state . $cd . $this->insured_zipcode; 		// IN1.19.XAD
		$s .= str_repeat( $fd, 16 );																	// IN1.20-35
		$s .= $fd . addcslashes( $this->policy_number, $q );											// IN1.36.ST
		$s .= str_repeat( $fd, 5 );																		// IN1.37-41
		$s .= $fd . HL7Message::encodeCE( $this->insured_employ_status, $cd, '0066' );					// IN1.42.CE0066
		$s .= $fd . HL7Message::encode0001( $this->insured_gender );      								// IN1.43.IS0001
		$s .= str_repeat( $fd, 5 );																		// IN1.44-48
		$s .= $fd . addcslashes( $this->insured_id, $q );          														// IN1.49.CX
		return $s;
	}
}