<?php
/**
 * Base class for ELabs_API functionality.
 */
class ELabs_API {
	public $genericLabNum = '111';
	public $genericLabName = 'Generic';
	public $genericFacilityNum = '999';
	public $recClientId = '678';
	
	/**
	 * Check if this API is functional in given environment
	 * 
	 * @return boolean true if functional (base class is always trivially functional)
	 */
	public function isOK() {
		return true;
	}
	
	/**
	 * Get the list of reports that are available
	 * Reports are later fetched with getReport()
	 * 
	 * @param boolean if true, get all available reports, not just new ones
	 * @param date get all reports from this date
	 * @param date get all reports to this date
	 * @return array  of { unique_id, sponsor_name, receiving_client_id, report_service_date }
	 */
	public function getReportList( $batchDownload = false, $date_from = '', $date_to = '' ) {
		return array();
	}
	
	/**
	 * Get the report data, i.e., both HL7 and HTML
	 * 
	 * @param	array	list of unique_ids of reports to fetch
	 * @param boolean if true, get all available reports, not just new ones
	 * @param date get all reports from this date
	 * @param date get all reports to this date
	 * @return 	array	by unique_id of { found, content_html, content_hl7 }
	 */
	public function getReport( $unique_ids, $batchDownload = false, $date_from = '', $date_to = '' ) {
		$x = array();
		foreach( $unique_ids as $id ) {
			$x[$id] = array( 'found' => false, 'content_html' => null, 'content_hl7' => null );
		}
		return $x;
	}

	/**
	 * Get a list of clients supported by this installation
	 *
	 * @param boolean $compact
	 * @return array compact: by client id of details, !compact: list of client ids
	 */
	public function getClients( $compact = false ) {
		return $compact ? array( $this->recClientId ) : array( $this->recClientId => array( 'lab' => $this->genericLabNum ));
	}
	
	/**
	 * filter the html from the db table as desired
	 * 
	 * @param string $html
	 * @return string
	 */
	public function filterHTML( $html ) {
		// Keep the following four replacements to support the correct viewing of any pre-existing emdeon reports
		$html = str_replace( "https://clinician.emdeon.com/images/lab/sbcllogo.gif",	$_SESSION['webroot'] . 'img/labs/sbcllogo.gif', 		$html );
		$html = str_replace( '<body style="position: absolute; left: 0px; top: 0px;">',	'<body style="margin-top: 0px; margin-left: 2px;">', 	$html );
		$html = str_replace( '<script type="text/javascript" src="https://clinician.emdeon.com/javascript/lab-reports/LCA.2.js"></script>',
				'<script type="text/javascript" src="https://clinician.emdeon.com/javascript/lab-reports/LCA.2.js"></script>'
				. '<script language="javascript" type="text/javascript" src="' . $_SESSION['webroot'] . 'js/labs/quest.js?' . time() . '"></script>',
				$html );
		$html = str_replace( 'https://clinician.emdeon.com/javascript/lab-reports/QUEST.3.js',
				$_SESSION['webroot'] . 'js/labs/quest.js?' . time(), 																$html );

		return $html;
	}
	
	/**
	 * Compose the HTML for displaying the given HL7 lab report
	 *
	 * @param Emdeon_HL7	HL7 message
	 * @param array			mostly filled-in lab result model, i.e., $data['EmdeonLabResult']
	 * @return string to display (or false if not supported, in which case the content_html has to be filled in by getReport)
	 */
	public function composeHTML( $emdeon_hl7, $lab_result ) {
		return false;
	}

	/**
	 * Get a list of labs supported by this installation
	 * 
	 * @return array: list of labs [n]['lab_name'] and [n]['lab']
	 */
	public function getLabs() {
		return array( array( 'lab' => $this->genericLabNum, 'lab_name' => $this->genericLabName ));
	}
	
	/**
	 * Get a list of lab names
	 * 
	 * @return array: list of lab names
	 */
	public function getValidLabs() {
		$validLabs = array();
		foreach( $this->getLabs() as $lab )
			$validLabs[] = $lab['lab'];
		return $validLabs;
	}
	
	/**
	 * Get info about this installation
	 * 
	 * @return array including ['facility']
	 */
	public function getInfo() {
		return array( 'facility' => $this->genericFacilityNum );
	}
	
	/**
	 * Get list of clients for given lab
	 * 
	 * @param  $lab
	 * @return array: [n]['id_value']
	 */
	public function getLabClients( $lab ) {
		return array( array( 'id_value' => $this->recClientId ));
	}
}