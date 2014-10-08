<?php
App::import( 'Lib',   'HL7SegmentPID', array( 'file' => 'HL7SegmentPID.php' ));

/**
 * Subclass to override standard behavior for MDConnection PID
 */
class HL7SegmentPID_MDC extends HL7SegmentPID {

	/**
	 * commit the patient represented by this PID segment into patient_demographics table
	 *
	 * @param int $not_used
	 * @param bool $lookupOnly		If true, only gets patient_id, if exists and returns
	 * @return string				Log of actions
	 */
	public function commitSegment( $not_used = null, $lookupOnly = false ) {
		// with MDConnection we have to look for two cases-case 1 legacy, case 2 swapped PID.2/PID.4, (see ticket #3454 for doc)
		// Search in order:
		
		// Special case: to handle weird legacy cases where the MDC-chart# has a valid MDC-id of another, e.g., in rlaw
		if( $this->mrnAndCpiExists( $this->msg_pid, $this->alt_pid )) {
			$mrn = $this->msg_pid;
			$cpi = $this->alt_pid;
			
		// Another special case for multiple back-ends like MHT, so we can get a dup MRN match so go off of CPI if we can
		} else if( !empty( $this->alt_pid ) && ( $mrn = $this->hasCpiMatch( $this->alt_pid ))) {
			$cpi = $this->alt_pid;

		// A: case 2 existing in OT: MDC-PID.4(MDC-chart#) matches existing OT-mrn, MDC-PID.2(MDC-id) will match or overwrite OT-cpi
		} else if( $this->hasMrnMatch( $this->alt_pid )) {
			$mrn = $this->alt_pid;
			$cpi = $this->msg_pid;
		
		// B: case 1 existing in OT: MDC-PID.2(MDC-id) matches existing OT-mrn, MDC-PID.4(MDC-chart#), if present, will match or overwrite OT-cpi
		} else if( $this->hasMrnMatch( $this->msg_pid )) {
			$mrn = $this->msg_pid;
			$cpi = $this->alt_pid;
		
		// C: case 2 new in OT: MDC-PID.4 is blank, new patient created with OT-generated OT-mrn, OT-cpi gets MDC-PID.2
		} else if( empty( $this->alt_pid )) {
			$mrn = null;
			$cpi = $this->msg_pid;
				
		// D: case 1 new in OT (for legacy systems that have random MDC-chart#): MDC-PID.4 is not blank, new patient created with OT-mrn set to MDC-PID.2.
		} else {
			$mrn = intval( $this->msg_pid );
			if( $mrn == 0 || $this->mrnExists( $mrn ))
				$mrn = null;
			$cpi = $this->alt_pid;
		}
		return self::commitSegmentP( $lookupOnly, $mrn, $cpi );
	}
	
	/**
	 * get a matching patient's mrn if found a match by cpi and at least three other fields
	 * @param string $cpi
	 * @return boolean|string
	 */
	private function hasCpiMatch( $cpi ) {	
		if( empty( $cpi ) || 0 == intval( $cpi ))
			return false;
		$mn = 'PatientDemographic';
		$possible =  ClassRegistry::init( $mn )->find( 'first', array( 'conditions' => array( $mn . '.custom_patient_identifier' => $cpi )));
		if( false === $possible )
			return false;
		$matches = 0;
		$matches += $possible[$mn]['last_name'] == $this->last_name;
		$matches += $possible[$mn]['first_name'] == $this->first_name;
		$matches += $possible[$mn]['gender'] == $this->gender;
		$matches += @$possible[$mn]['dob'] == $this->dob->format( 'Y-m-d' );
		if( $matches < 3 )
			return false;
		return $possible[$mn]['mrn'];
	}
	
	/**
	 * see if matching patient with given mrn exists
	 * @param string $mrn
	 * @return boolean
	 */
	private function hasMrnMatch( $mrn ) {
		if( empty( $mrn ) || 0 == intval( $mrn ))
			return false;
		$mn = 'PatientDemographic';
		$possible =  ClassRegistry::init( $mn )->find( 'first', array( 'conditions' => array( $mn . '.mrn' => $mrn )));
		if( false === $possible )
			return false;
		$matches = 0;
		$matches += $possible[$mn]['last_name'] == $this->last_name;
		$matches += $possible[$mn]['first_name'] == $this->first_name;
		$matches += $possible[$mn]['gender'] == $this->gender;
		$matches += @$possible[$mn]['dob'] == $this->dob->format( 'Y-m-d' );
		return $matches >= 3;
	}
	
	/**
	 * See if there is an existing patient with given mrn
	 * 
	 * @param string	$mrn - mrn to look for
	 * @return boolean 	patient with given mrn exists
	 */
	private function mrnExists( $mrn )
	{
		if( empty( $mrn ) || 0 == intval( $mrn ))
			return false;
		$mn = 'PatientDemographic';
		return false !== ClassRegistry::init( $mn )->find( 'first', array( 'conditions' => array( $mn . '.mrn' => $mrn )));
	}

	/**
	 * See if there is an existing patient with given mrn and cpi
	 *
	 * @param string	$mrn - mrn to look for
	 * @param string	$cpi - custom_patient_identifier to look for
	 * @return boolean 	patient with given mrn and cpi exists
	 */
	private function mrnAndCpiExists( $mrn, $cpi )
	{
		if( empty( $mrn ) || 0 == intval( $mrn ) || empty( $cpi ))
			return false;
		$mn = 'PatientDemographic';
		return false !== ClassRegistry::init( $mn )->find( 'first', array( 
				'conditions' => array( 
						$mn . '.mrn' => $mrn,
						$mn . '.custom_patient_identifier' => $cpi 
				) 
		) );
	}
	
	/**
	 * Make the segment text for the PID segment to be sent (MacPractice-specific: swap PID.2 and PID.4).
	 *
	 * @param HL7Message $msh	The message header to be sent (so we know what delimiters to use)
	 * @return string			The PID segment text
	 */
	public function produceSegment( HL7Message $msh ) {
		// we are not doing anything special for MDConnection OT-mrn:PID.2 and OT-cpi:PID.4
		return self::produceSegmentP( $msh, $this->msg_pid, $this->alt_pid );
	}
}
