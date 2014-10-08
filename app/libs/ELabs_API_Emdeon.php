<?php
/**
 * ELabs implemented with Emdeon lab functionality (using Emdeon_XML_API).
 */
class ELabs_API_Emdeon extends ELabs_API {
	public $emdeon_xml_api;

	/**
	 * Check if this API is functional in given environment, i.e., for Emdeon if practice is so configured
	 * 
	 * @return boolean true if functional
	 * @staticvar $this->emdeon_xml_api set (so this must be called before any others  FIXME: could be in ctor)
	 */
	public function isOK() {
		$this->emdeon_xml_api = new Emdeon_XML_API();
		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		if( $practice_settings->labs_setup == 'Electronic' ) {
			return $this->emdeon_xml_api->checkConnection();
		}
        return false;
	}

	/**
	 * Get the list of reports that are available
	 * Reports are later fetched with fetchReport()
	 * 
	 * @param boolean if true, get all available reports, not just new ones
	 * @param date get all reports from this date
	 * @param date get all reports to this date
	 * @return array  of { unique_id, sponsor_name, receiving_client_id, report_service_date }
	 */
	public function getReportList( $batchDownload = false, $date_from = '', $date_to = '' ) {
		$this->emdeon_xml_api->batchDownload = $batchDownload;
		$results 	= $this->emdeon_xml_api->getReportList( "LABRES", $date_from, $date_to );
		$data_list 	= array();
		foreach( $results as $result ) {
			$data 							= array();
			$data['unique_id'] 				= $result['id'];
			$data['sponsor_name'] 			= str_replace( '_', ' ', $result['sponsor_name'] );
			$data['receiving_client_id'] 	= $result['receiving_client_id'];
			$data['report_service_date'] 	= __date( "Y-m-d H:i:s", strtotime( $result['report_service_date'] ));
			$data_list[] 					= $data;
		}
		return $data_list;
	}

	/**
	 * Get a list of clients supported by this installation
	 * 
	 * @param boolean $compact
	 * @return array by client id of details
	 */
	public function getClients( $compact = false ) {
		return $this->emdeon_xml_api->getClients( $compact );
	}
	
	/**
	 * filter the html from the db table as desired
	 * 
	 * @param string $html
	 * @return string
	 */
	public function filterHTML( $html ) {
		$lab_configs = $this->emdeon_xml_api->getInfo();
		$html = str_replace( "filename: 'https://" . $lab_configs['host'], 				"filename: '", 											$html );
		$html = str_replace( "filename: '", 											"filename: 'https://" . $lab_configs['host'], 			$html );
		$html = str_replace( "https://clinician.emdeon.com/images/lab/sbcllogo.gif",	$_SESSION['webroot'] . 'img/labs/sbcllogo.gif', 		$html );
		$html = str_replace( '<body style="position: absolute; left: 0px; top: 0px;">',	'<body style="margin-top: 0px; margin-left: 2px;">', 	$html );
		
		$html = str_replace( '<script type="text/javascript" src="https://clinician.emdeon.com/javascript/lab-reports/LCA.2.js"></script>', 
							 '<script type="text/javascript" src="https://clinician.emdeon.com/javascript/lab-reports/LCA.2.js"></script>' 
							 . '<script language="javascript" type="text/javascript" src="' . $_SESSION['webroot'] . 'js/labs/quest.js?' . time() . '"></script>', 
																																				$html );
		
		// js adjustment for Quest
		$html = str_replace( 'https://clinician.emdeon.com/javascript/lab-reports/QUEST.3.js', 
							 $_SESSION['webroot'] . 'js/labs/quest.js?' . time(), 																$html );
		
		return $html;
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
		return $this->emdeon_xml_api->getReport( $unique_ids, 'LABRES', $date_from, $date_to );
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
		return $this->emdeon_xml_api->getLabs();
	}
	
	/**
	 * Get a list of lab names
	 *
	 * @return array: list of lab names
	 */
	public function getValidLabs() {
		return $this->emdeon_xml_api->getValidLabs();
	}
	
	/**
	 * Get info about this installation
	 *
	 * @return array including ['facility']
	 */
	public function getInfo() {
		return $this->emdeon_xml_api->getInfo();
	}
	
	/**
	 * Get list of clients for given lab
	 *
	 * @param  $lab
	 * @return array: [n]['id_value']
	 */
	public function getLabClients( $lab ) {
		return $this->emdeon_xml_api->getLabClients( $lab );
	}
}