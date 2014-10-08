<?php
App::import('Lib', 'HL7Segment', array('file'=>'HL7Segment.php'));

class HL7SegmentGT1 extends HL7Segment {
	const SEGMENT_TYPE = 'GT1';
	public $last_name;					// GT1.3.XPN.1
	public $first_name;					// GT1.3.XPN.2
	public $middle_name;				// GT1.3.XPN.3
	public $suffix;						// GT1.3.XPN.4
	public $spouse_name;				// GT1.4.XPN
	public $address1;					// GT1.5.XAD.1
	public $address2;					// GT1.5.XAD.2
	public $city;						// GT1.5.XAD.3
	public $state;						// GT1.5.XAD.4
	public $zipcode;					// GT1.5.XAD.5
	public $home_phone;					// GT1.6.XTN
	public $email;						// GT1.6.XTN
	public $work_phone;					// GT1.7.XTN
	public $work_phone_extension;		// GT1.7.XTN
	public $dob;						// GT1.8.TS
	public $gender;						// GT1.9.IS0001
	public $relationship;				// GT1.11.CE0063
	public $ssn;						// GT1.12.ST
	public $begin_date;					// GT1.13.DT
	public $end_date;					// GT1.14.DT
	public $employer_name;				// GT1.16.XPN
	public $employer_address1;			// GT1.17.XAD.1
	public $employer_address2;			// GT1.17.XAD.2
	public $employer_city;				// GT1.17.XAD.3
	public $employer_state;				// GT1.17.XAD.4
	public $employer_zipcode;			// GT1.17.XAD.5
	public $employer_phone;				// GT1.18.XTN
	public $employee_id;				// GT1.19.CX
	public $employment_status;			// GT1.20.IS0066

	/**
	 * interpret the given parsed $segment into $this properties
	 *
	 * @param array $segments			Parsed HL7 message (with correct message_type)
	 * @return false|HL7Message
	 */
	public function interpret( $segment ) {
		if( $segment[0] != self::SEGMENT_TYPE )
			throw new Exception( "Message segment must start with ".self::SEGMENT_TYPE );
		while( count($segment) < 21 ) {
			$segment[] = null;
		}
		$dummy = null;
		HL7Message::interpretXPN( $segment[ 3], $this->last_name, $this->first_name, $this->middle_name, $this->suffix );
		HL7Message::interpretXAD( $segment[ 5], $this->address1, $this->address2, $this->city, $this->state, $this->zipcode );
		HL7Message::interpretXAD( $segment[17], $this->employer_address1, $this->employer_address2, $this->employer_city, $this->employer_state, $this->employer_zipcode );
		$this->spouse_name       = HL7Message::interpretXPN(   $segment[ 4] );
		$this->home_phone        = HL7Message::interpretXTN(   $segment[ 6], $dummy, $this->email );
		$this->work_phone        = HL7Message::interpretXTN(   $segment[ 7], $this->work_phone_extension );
		$this->dob               = HL7Message::interpretTS(    $segment[ 8] );
		$this->gender            = HL7Message::interpret0001(  $segment[ 9] );
		$this->relationship      = HL7Message::interpretCE(	   $segment[11], '0063' );
		$this->ssn               = HL7Message::interpretSsn(   $segment[12] );
		$this->begin_date        = HL7Message::interpretTS(    $segment[13] );
		$this->end_date          = HL7Message::interpretTS(    $segment[14] );
		$this->employer_name     = HL7Message::interpretXPN(   $segment[16] );
		$this->employer_phone    = HL7Message::interpretXTN(   $segment[18] );
		$this->employment_status = HL7Message::interpret0066(  $segment[20] );
		$this->employee_id       = HL7Message::interpretCX(    $segment[19] );
	}

	/**
	 * commit the GT1 segment
	 *
	 * @param string $patient_id
	 * @param bool $replaceAll		if true, deletes all previous guarantor records for this patient
	 * @return string				log of actions taken
	 */
	public function commitSegment( $patient_id, $replaceAll = false ) {
		$mn	= 'PatientGuarantor';
		$m	= ClassRegistry::init( $mn );
		$story = $mn;

		$goal = array( $mn => array(
				'patient_id'			=> $patient_id,

				'birth_date'			=> is_null( $value = $this->dob ) 			? null : $value->format( 'Y-m-d' ),
				'date'					=> is_null( $value = $this->begin_date )	? null : $value->format( 'Y-m-d' ),
				'home_phone'			=> $this->home_phone,
				'work_phone'			=> $this->work_phone,
				'ssn'					=> $this->ssn,
				'guarantor_sex'			=> $this->gender,
				'first_name'			=> $this->first_name,
				'last_name'				=> $this->last_name,
				'middle_name'			=> $this->middle_name,
				'suffix'				=> $this->suffix,
				'relationship'			=> HL7Message::getRelationshipCode( $this->relationship ),
				'spouse_name'			=> $this->spouse_name,
				'address_1'				=> $this->address1,
				'address_2'				=> $this->address2,
				'city'					=> $this->city,
				'state'					=> $this->state,
				'zip'					=> $this->zipcode,
				'employee_id'			=> $this->employee_id,
				'employer_address1'		=> $this->employer_address1,
				'employer_address2'		=> $this->employer_address2,
				'employer_city'			=> $this->employer_city,
				'employer_name'			=> $this->employer_name,
				'employer_phone'		=> $this->employer_phone,
				'employer_state'		=> $this->employer_state,
				'employer_zip'			=> $this->employer_zipcode,
				'employment_status'		=> $this->employment_status,
				'work_phone_ext'		=> $this->work_phone_extension )

				// other fields without corresponding data in GT1 segment:
					// 'guarantor'
					// 'guarantor_type'
					// 'alt_home_phone'
					// 'alt_work_phone'
					// 'alt_work_phone_ext'
					// 'person' => person,
					// 'use_default' => use_default,
				 );

		// FIXME: we are assuming that all previous guarantors with different name stay intact.  Potentially we could have logic based on which ADT subtype we are.  OR we could assume only a single guarantor for each patient, in which case it would just be the last one seen (note that HL7 allows for multiple GT1 segments in the message)--replaceAll flag is for this
		if( $replaceAll ) {
			$success = $m->deleteAll( array( $mn . '.patient_id' => $patient_id ));
			$story .= ' replace (id:' . $patient_id . ( false === $success ? ') failed' : ') with' );
		}
		$story .= HL7Message::commitData( $m, $goal, array( 'patient_id', 'last_name', 'first_name' ));
		$get = $m->findByGuarantorId( $m->id );
		$this->event_timestamp = $get[$mn]['modified_timestamp'];
		return $story;
	}

	/**
	 * create a new HL7SegmentGT1 array from PatientGuarantor model where patient_id is $patient_id
	 *
	 * @param int $patient_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return false|array
	 */
	public static function createFromDb( $patient_id, $receiver, $processing_id ) {
		$cl = get_called_class();
		$mn = 'PatientGuarantor';
		$m  = ClassRegistry::init( $mn );
		$gets = $m->findAllByPatientId( $patient_id );
		if( false === $gets )
			return false;
		$result = array();
		foreach( $gets as $get ) {
			$me = new $cl();
			$pd = $get[$mn];
			foreach( array(
				'patient_id'			=> 'patient_id',
				'home_phone'			=> 'home_phone',
				'work_phone'			=> 'work_phone',
				'ssn'					=> 'ssn',
				'first_name'			=> 'first_name',
				'last_name'				=> 'last_name',
				'middle_name'			=> 'middle_name',
				'suffix'				=> 'suffix',
				'guarantor_sex'			=> 'gender',
				'spouse_name'			=> 'spouse_name',
				'address_1'				=> 'address1',
				'address_2'				=> 'address2',
				'city'					=> 'city',
				'state'					=> 'state',
				'zip'					=> 'zipcode',
				'employee_id'			=> 'employee_id',
				'employer_address1'		=> 'employer_address1',
				'employer_address2'		=> 'employer_address2',
				'employer_city'			=> 'employer_city',
				'employer_name'			=> 'employer_name',
				'employer_phone'		=> 'employer_phone',
				'employer_state'		=> 'employer_state',
				'employer_zip'			=> 'employer_zipcode',
				'employment_status'		=> 'employment_status',
				'work_phone_ext'		=> 'work_phone_extension',
				'modified_timestamp'	=> 'event_timestamp' ) as $dbfn => $myfn ) {
				$me->$myfn = $pd[$dbfn];
			}
			$me->dob 		  = isset( $pd['birth_date'] ) 	? new DateTime( $pd['birth_date'] )	: null;
			$me->begin_date	  = isset( $pd['date'] )		? new DateTime( $pd['date'] )		: null;
			$me->relationship = HL7Message::getRelationshipDescription( $pd['relationship'] );
			$result[] = $me;
		}
		return $result;
	}

	/**
	 * Make the segment text for the GT1 segment to be sent.
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The GT1 segment text
	 */
	public function produceSegment( HL7Message $msh, $seqno ) {
		$fd = $msh->field_delimiter;
		$cd = $msh->component_delimiter;
		$q = $fd . $cd . $msh->repeat_delimiter . $msh->subcomponent_delimiter;
		$s  = self::SEGMENT_TYPE;
		$s .= $fd . $seqno;
		$s .= $fd;
		$s .= $fd . $this->last_name . $cd . $this->first_name . $cd . $this->middle_name . $cd . $this->suffix;
		$s .= $fd . $this->spouse_name;  // FIXME: explode spouse name into last^first
		$s .= $fd . addcslashes( $this->address1, $q ) . $cd . addcslashes( $this->address2, $q ) . $cd . $this->city . $cd . $this->state . $cd . $this->zipcode;
		$s .= $fd . $this->home_phone . $cd . $cd . $cd . addcslashes( $this->email, $q );
		$s .= $fd . $this->work_phone . str_repeat( $cd, 7 ) . addcslashes( $this->work_phone_extension, $q );
		$s .= $fd . ( isset( $this->dob ) ? $this->dob->format( 'Ymd' ) : '' );
		$s .= $fd . HL7Message::encode0001( $this->gender );
		$s .= $fd;
		$s .= $fd . HL7Message::encodeCE( $this->relationship, $cd, '0063' );
		$s .= $fd . $this->ssn;
		$s .= $fd . ( isset( $this->begin_date ) ? $this->begin_date->format( 'Ymd' ) : '' );
		$s .= $fd . ( isset( $this->end_date )   ? $this->end_date->format( 'Ymd' )   : '' );
		$s .= $fd;
		$s .= $fd . addcslashes( $this->employer_name, $q );
		$s .= $fd . addcslashes( $this->employer_address1, $q ) . $cd . addcslashes( $this->employer_address2, $q ) . $cd . $this->employer_city . $cd . $this->employer_state . $cd . $this->employer_zipcode;
		$s .= $fd . $this->employer_phone;
		$s .= $fd . addcslashes( $this->employee_id, $q );
		$s .= $fd . $this->employment_status;
		return $s;
	}
}
