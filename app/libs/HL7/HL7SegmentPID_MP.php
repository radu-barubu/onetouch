<?php
App::import( 'Lib',   'HL7SegmentPID', array( 'file' => 'HL7SegmentPID.php' ));

/**
 * Subclass to override standard behavior for MacPractice PID (only difference is in outgoing PID)
 */
class HL7SegmentPID_MP extends HL7SegmentPID {

	/**
	 * commit the patient represented by this PID segment into patient_demographics table
	 *
	 * @param int $not_used
	 * @param bool $lookupOnly		If true, only gets patient_id, if exists and returns
	 * @return string				Log of actions
	 */
	public function commitSegment( $not_used = null, $lookupOnly = false ) {
		// with MacPractice we are expecting our mrn in msg_pid and their id in alt_pid if originating in OT, otherwise their id in both msg_pid and alt_pid
		$cpi = $this->alt_pid;
		if( isset( $this->msg_pid ) && false === strpos( $this->msg_pid, '-' )) {
			$mrn = intval( $this->msg_pid );
		} else {
			$mn = 'PatientDemographic';
			$m 	= ClassRegistry::init( $mn );
			$conditions = array( $mn . '.custom_patient_identifier' => $cpi );
			$get = $m->find( 'first', array( 'conditions' => $conditions ));
			if( $get !== false )
				$mrn = $get[$mn]['mrn'];
			else 
				$mrn = null;	// new patient, use generated mrn			
		}
		return self::commitSegmentP( $lookupOnly, $mrn, $cpi );
	}
	
	/**
	 * Make the segment text for the PID segment to be sent (MacPractice-specific: swap PID.2 and PID.4).
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The PID segment text
	 */
	public function produceSegment( HL7Message $msh ) {
		
		// for 21-Nov version of MP, they expect our MRN in PID.2 (msg_pid) for a new patient originating in OT
		if( empty( $this->alt_pid ))
			$this->alt_pid = $this->msg_pid;
		
		return self::produceSegmentP( $msh, $this->alt_pid, $this->msg_pid );
	}
}
