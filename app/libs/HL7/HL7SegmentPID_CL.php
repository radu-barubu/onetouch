<?php
App::import( 'Lib',   'HL7SegmentPID', array( 'file' => 'HL7SegmentPID.php' ));

/**
 * Subclass to override standard behavior for Compulink PID (only difference is patient keyed by PID.4 on incoming)
 */
class HL7SegmentPID_CL extends HL7SegmentPID {

	/**
	 * commit the patient represented by this PID segment into patient_demographics table
	 *
	 * @param int $not_used
	 * @param bool $lookupOnly		If true, only gets patient_id, if exists and returns
	 * @return string				Log of actions
	 */
	public function commitSegment( $not_used = null, $lookupOnly = false ) {
		// With Compulink we can get distinct msg_pid (PID.2) for the same patient, so use alt_pid (PID.4) if present 
		// (but also have to accommodate patients already in db with mrn already set to msg_pid from before)
		
		if( isset( $this->msg_pid ) && ( 0 != intval( $this->msg_pid ))) {
			$mrn = intval( $this->msg_pid );
		} else {
			$mrn = null;
		}
		if( isset( $this->alt_pid )) {
			if( 0 != intval( $this->alt_pid )) {
				$cpi = intval( $this->alt_pid );
			} else {
				$cpi = $this->alt_pid;
			}
		} else {
			$cpi = null;
		}
		
		// if we have an alt_pid, look that up to see if the patient was already added and use the already assigned MRN (note that in compulink the alt id is per FAMILY, not per PATIENT, so we have to find a matching name, etc., too)
		if( !is_null( $cpi )) {
			$mn = 'PatientDemographic';
			$m 	= ClassRegistry::init( $mn );
			$conditions = array( $mn . '.custom_patient_identifier' => $cpi );
			if( !is_null( $ln  = $this->last_name  ))
				$conditions[$mn . '.last_name'] = $ln;
			if( !is_null( $fn  = $this->first_name ))
				$conditions[$mn . '.first_name'] = $fn;
			if( !is_null( $sex = $this->gender ))
				$conditions[$mn . '.gender'] = $sex;
			if( !is_null( $dob = $this->dob ))
				$conditions[$mn . '.dob'] = $dob->format( 'Y-m-d' );	
			$get = $m->find( 'first', array( 'conditions' => $conditions ));
			if( $get !== false )
				$mrn = $get[$mn]['mrn'];
		}

		return self::commitSegmentP( $lookupOnly, $mrn, $cpi );
	}
}
