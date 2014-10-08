<?php
App::import('Lib',    'HL7Segment', 	array('file'=>'HL7Segment.php'));
App::import( 'Core',  'Model');
App::import( 'Lib',   'LazyModel', 		array( 'file' => 'LazyModel.php' ));
App::import( 'Lib',   'Emdeon_XML_API', array( 'file' => 'Emdeon_XML_API.php' ));
App::import( 'Core',  'Controller' );
App::import( 'Core',  'Router' );

class HL7SegmentPID extends HL7Segment {
	const SEGMENT_TYPE = 'PID';
	public $patient_id;				// our patient id
	public $msg_pid;				// PID.2.CX
	public $alt_pid;				// PID.4.CX
	public $last_name;				// PID.5.1
	public $first_name;				// PID.5.2
	public $middle_name;			// PID.5.3
	public $mother_maiden_name;		// PID.6.1
	public $dob;					// PID.7
	public $gender;					// PID.8
	public $race;					// PID.10.CE0005
	public $address1;				// PID.11.1
	public $address2;				// PID.11.2
	public $city;					// PID.11.3
	public $state;					// PID.11.4
	public $zipcode;				// PID.11.5
	public $home_phone;				// PID.13
	public $email;					// PID.13.4
	public $work_phone;				// PID.14
	public $work_phone_extension;	// PID.14.8
	public $preferred_language;		// PID.15
	public $marital_status;			// PID.16
	public $ssn;					// PID.19
	public $driver_license_id;		// PID.20.1
	public $driver_license_state;	// PID.20.2
	public $ethnicity;				// PID.22.CE0189

	function interpret( $segment ) {
		if( $segment[0] != self::SEGMENT_TYPE )
			throw new Exception( "Message segment must start with ".self::SEGMENT_TYPE );

		// fill out terminal missing fields with nulls
		while( count( $segment ) < 23 ) {
			$segment[] = null;
		}
		$dummy = null;

		HL7Message::interpretXPN( $segment[5], $this->last_name, $this->first_name, $this->middle_name );
		HL7Message::interpretXPN( $segment[6], $this->mother_maiden_name );
		HL7Message::interpretXAD( $segment[11], $this->address1, $this->address2, $this->city, $this->state, $this->zipcode );

		$this->msg_pid            = HL7Message::interpretCX(    $segment[ 2] );
		$this->alt_pid            = HL7Message::interpretCX(    $segment[ 4] );
		$this->dob                = HL7Message::interpretTS(    $segment[ 7] );
		$this->gender             = HL7Message::interpret0001(	$segment[ 8] );
		$this->race               = HL7Message::interpretCE(	$segment[10], '0005' );
		$this->home_phone         = HL7Message::interpretXTN(	$segment[13], $dummy, $this->email );
		$this->work_phone         = HL7Message::interpretXTN(	$segment[14], $this->work_phone_extension );
		$this->preferred_language = HL7Message::interpretCE(	$segment[15], '0296' );
		$this->marital_status     = HL7Message::interpretCE(	$segment[16], '0002' );
		$this->ssn                = HL7Message::interpretSsn(	$segment[19] );
		$this->driver_license_id  = HL7Message::interpretDLN(	$segment[20], $this->driver_license_state );
		$this->ethnicity          = HL7Message::interpretCE(	$segment[22], '0189' );
	}

	/**
	 * commit the patient represented by this PID segment into patient_demographics table
	 *
	 * @param int $not_used
	 * @param bool $lookupOnly		If true, only gets patient_id, if exists and returns
	 * @return string				Log of actions
	 */
	public function commitSegment( $not_used = null, $lookupOnly = false ) {
		// In a cooperating app like MDConnection, the alt_pid is our PID if the patient originated in OT, otherwise alt_pid will be a copy of msg_pid or blank
		if( isset( $this->alt_pid ) && ( 0 != intval( $this->alt_pid ))) {
			$mrn = intval( $this->alt_pid );
			$cpi = $this->msg_pid;
		} elseif( isset( $this->msg_pid ) && ( 0 != intval( $this->msg_pid ))) {
			$mrn = intval( $this->msg_pid );
			$cpi = $this->alt_pid;
		} else {
			$mrn = 
			$cpi = null;
		}
		return self::commitSegmentP( $lookupOnly, $mrn, $cpi );
	}

	/**
	 * commit the patient represented by this PID segment into patient_demographics table
	 *
	 * @param bool $lookupOnly		If true, only gets patient_id, if exists and returns
	 * @param string $mrn			MRN to use for this patient
	 * @param string $cpi			Customer patient id to use for this patient
	 * @return string				Log of actions
	 */
	public function commitSegmentP( $lookupOnly, $mrn, $cpi ) {
		$ln  = is_null( $ln  = $this->last_name  ) ? null : $ln;
		$fn  = is_null( $fn  = $this->first_name ) ? null : $fn;
		$sex = is_null( $sex = $this->gender     ) ? null : $sex;
		$dob = is_null( $dob = $this->dob        ) ? null : $dob->format( 'Y-m-d' );

		$story = ' ' . $fn . ' ' . $ln . ' (' . $dob . ') ' . $sex . ' mrn:' . $mrn . ' cpi:' . $cpi;

		$mn = 'PatientDemographic';
		$m 	= ClassRegistry::init( $mn );

		// construct the data we would like to see in a find() per HL7 message
		$goal = array( $mn => array(
				'mrn'						=> $mrn,
				'custom_patient_identifier'	=> $cpi,
			
				'last_name'				=> $ln,
				'first_name'			=> $fn,
				'dob'					=> $dob,
				'gender'				=> $sex,
				'middle_name'			=> $this->middle_name,
				'preferred_language'	=> $this->preferred_language,
				'race'					=> $this->race,
				'ethnicity'				=> $this->ethnicity,
				'ssn'					=> $this->ssn,
				'driver_license_id'		=> $this->driver_license_id,
				'driver_license_state'	=> ( $this->driver_license_state == $this->state ) ? '' : $this->driver_license_state,
				'address1'				=> $this->address1,
				'address2'				=> $this->address2,
				'city'					=> $this->city,
				'state'					=> $this->state,
				'zipcode'				=> $this->zipcode,
				'work_phone'			=> $this->work_phone,
				'work_phone_extension'	=> $this->work_phone_extension,
				'home_phone' 			=> $this->home_phone,
				'email'					=> $this->email,
				'marital_status' 		=> $this->marital_status				// note that this is stored in patient_social_history but is retrievable here with virtual field
				// 'mother' is not $this->mother_maiden_name
				// 'status' use default of 'New' for new patients
				// 'source_system_id' FIXME: do we want this set to something, e.g., sending application?
				 ));

		// see if patient already exists (last_name, first_name, dob, gender) and get its current data
		if( is_null( $mrn )) {
			$key = array( 'last_name' => $ln, 'first_name' => $fn, 'dob' => $dob, 'gender' => $sex, 'custom_patient_identifier' => $cpi );
		} else {
			$key = array( 'mrn' => $mrn );
		}
		if( is_null( $ln ))
		{
			unset( $goal['last_name'] );
			unset( $key['last_name'] );
		}
		if( is_null( $fn ))
		{
			unset( $goal['first_name'] );
			unset( $key['first_name'] );
		}
		if( is_null( $dob ))
		{
			unset( $goal['dob'] );
			unset( $key['dob'] );
		}
		if( is_null( $sex ))
		{
			unset( $goal['gender'] );
			unset( $key['gender'] );
		}
		if( is_null( $cpi )) {
			unset( $goal['custom_patient_identifier'] );
			unset( $key['custom_patient_identifier'] );
		}
		$get	= $m->find( 'first', array('conditions' => $key ));
		$is_new	= false === $get;
		if( $is_new ) {
			if( $lookupOnly )
				return false;
			$m->create();
			$m->save( array( $mn => $key) , false );
			$m->saveAudit( 'New' );
			$get = $m->find( 'first', array( 'conditions' => $key ));
		}
		$m->id = $this->patient_id = $get[$mn][$m->primaryKey];
		$this->event_timestamp = $get[$mn]['modified_timestamp'];
		$story .= ' (id:' . $this->patient_id . ')';
		if( $lookupOnly )
			return $story;

		// set MRN if new patient and no MRN passed in message
		if( empty( $mrn )) {
			$mrn = $m->getNewMRN();
			$goal[$mn]['mrn'] = $mrn;
			if( is_null( $cpi )) {
				$cpi = isset( $this->msg_pid ) ? $this->msg_pid : $this->alt_pid;
				$goal['custom_patient_identifier'] = $cpi;
			}
		}

		// accumulate fields to be set (not really necessary to weed out data that is the same, but it is interesting to know and we can avoid a db op if nothing is different)
		// essentially:  $set[$mn] = array_diff_assoc( $goal[$mn], $get[$mn] ) but with null check
		$set = array( $mn => null );
		foreach( array_keys( $goal[$mn] ) as $fn )
			if( !is_null( $goal[$mn][$fn] ) && $goal[$mn][$fn] != $get[$mn][$fn] )
				$set[$mn][$fn] = $goal[$mn][$fn];

		// marital status is in a patient_social_history record
		if( isset( $set[$mn]['marital_status'] ))
			if( !$this->setMaritalStatus( $set[$mn]['marital_status'] ))
				$story .= ' setting marital status failed';

		// commit to datasource
		$story .= ' ' . $mn;
		if( is_null( $set[$mn] )) {
			$story .= ' no changes';
		} else {
			$success = $m->save( $set );
			if( $success !== false && !$is_new )
				$m->saveAudit( 'Update' );
				
			// tell the story
			$story .= ( $success === false ? ' failed to save' : ( $is_new ? ' created': ' updated' ));
			if( $is_new )
				foreach( array_keys( $set[$mn] ) as $fn )
					$get[$mn][$fn] = '';
			foreach( array_keys( $set[$mn] ) as $fn )
				$story .= ' ' . $fn . '(' . $get[$mn][$fn] . '-->' . $set[$mn][$fn] . ')';

			$get = $m->findByPatientId( $this->patient_id );
			$this->event_timestamp = $get[$mn]['modified_timestamp'];
		}
		return $story;
	}

	/**
	 * Add/update marital status for this patient in the patient_social_history table
	 *
	 * @param string $marital_status	Goal marital_status value
	 * @return boolean
	 */
	private function setMaritalStatus( $marital_status ) {
		$mn		= 'PatientSocialHistory';
		$m		= ClassRegistry::init( $mn );
		$key	= array( 'patient_id' => $this->patient_id, 'type' => 'Marital Status' );
		$goal	= array_merge( $key, array( 'marital_status' => $marital_status ));
		$get	= $m->find( 'first', array( 'conditions' => $key ));
		if( false === $get )
			$m->create();
		else
			$m->id = $get[$mn][$m->primaryKey];
		return false !== $m->save( array( $mn => $goal ));
	}

	/**
	 * create a new HL7SegmentPID from PatientDemographic model where patient_id is $patient_id
	 *
	 * @param int $patient_id
	 * @param string $receiver			Receiving application
	 * @param string $processing_id		One of HL7Message::PT_*
	 * @return false|HL7SegmentPID
	 */
	public static function createFromDb( $patient_id, $receiver, $processing_id ) {
		$cl = get_called_class();
		$me = new $cl();
		$mn = 'PatientDemographic';
		$m  = ClassRegistry::init( $mn );
		$me->patient_id = $patient_id;
		$get = $m->findByPatientId( $me->patient_id );
		if( false === $get )
			return false;
		$pd = $get[$mn];
		foreach( array( 'last_name', 'first_name', 'middle_name', 'gender', 'race', 'address1', 'address2',
				'city', 'state', 'zipcode', 'home_phone', 'email', 'work_phone', 'work_phone_extension',
				'preferred_language', 'marital_status', 'ssn', 'driver_license_id', 'ethnicity' ) as $fn ) {
			$me->$fn = $pd[$fn];
		}
		$me->msg_pid				= $pd['mrn'];
		$me->alt_pid				= $pd['custom_patient_identifier'];
		$me->dob					= isset( $pd['dob'] ) ? new DateTime( $pd['dob'] ) : null;
		$me->driver_license_state	= isset( $pd['driver_license_state'] ) ? $pd['driver_license_state'] : ( 0 != strlen( $pd['driver_license_id'] ) ? $pd['state'] : null );
		$me->event_timestamp		= $pd['modified_timestamp'];
		return $me;
	}

	/**
	 * Make the segment text for the PID segment to be sent.
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The PID segment text
	 */
	public function produceSegment( HL7Message $msh ) {
		return self::produceSegmentP( $msh, $this->msg_pid, $this->alt_pid );
	}
	
	/**
	 * Make the segment text for the PID segment to be sent.
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @param string $pid2		The patient id to be placed in PID.2
	 * @param string $pid4		The patient id to be placed in PID.4
	 * @return string			The PID segment text
	 */
	protected function produceSegmentP( HL7Message $msh, $pid2, $pid4 ) {
		$fd = $msh->field_delimiter;
		$cd = $msh->component_delimiter;
		$q = $fd . $cd . $msh->repeat_delimiter . $msh->subcomponent_delimiter;
		$s  = self::SEGMENT_TYPE;
		$s .= $fd . '1';																										// PID.1
		$s .= $fd . $pid2;																								// PID.2
		$s .= $fd;																							// PID.3
		$s .= $fd . $pid4;																								// PID.4
		$s .= $fd . $this->last_name . $cd . $this->first_name . $cd . $this->middle_name;										// PID.5
		$s .= $fd . $this->mother_maiden_name;																					// PID.6.1
		$s .= $fd . ( isset( $this->dob ) ? $this->dob->format( 'Ymd') : '' );													// PID.7
		$s .= $fd . HL7Message::encode0001( $this->gender );																	// PID.8.IS0001
		$s .= $fd;																												// PID.9
		$s .= $fd . HL7Message::encodeCE( $this->race, $cd, '0005' );															// PID.10.CE0005
		$s .= $fd . addcslashes( $this->address1, $q ) . $cd . addcslashes( $this->address2, $q ) . $cd . $this->city . $cd . $this->state . $cd . $this->zipcode;	// PID.11
		$s .= $fd;																												// PID.12
		$s .= $fd . $this->home_phone . $cd . $cd . $cd . $this->email;															// PID.13
		$s .= $fd . $this->work_phone . str_repeat( $cd, 7 ) . $this->work_phone_extension;										// PID.14
		$s .= $fd . HL7Message::encodeCE( $this->preferred_language, $cd, '0296' );												// PID.15.CE0296
		$s .= $fd . HL7Message::encodeCE( $this->marital_status, $cd, '0002');													// PID.16.CE0002
		$s .= $fd . $fd;																										// PID.17-18
		$s .= $fd . $this->ssn;																									// PID.19
		$s .= $fd . addcslashes( $this->driver_license_id, $q ) . $cd . $this->driver_license_state;												// PID.20
		$s .= $fd;																												// PID.21
		$s .= $fd . HL7Message::encodeCE( $this->ethnicity, $cd, '0189' );														// PID.22.CE0189
		return $s;
	}
}

?>
