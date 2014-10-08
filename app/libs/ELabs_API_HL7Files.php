<?php
/**
 * ELabs implemented with lab functionality via HL7 files in incoming labs directory.
 */
class ELabs_API_HL7Files extends ELabs_API_MacPractice {
	private $dir = null;
		
	/**
	 * Check if this API is functional in given environment
	 * 
	 * @return boolean true if functional
	 */
	public function isOK() {
		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		if( $practice_settings->labs_setup != 'HL7Files' )
			return false;
		if( is_null( $this->dir ))
			$this->dir = $practice_settings->hl7_report_dir;
		$this->recClientId = $practice_settings->hl7_report_client_id;
		$this->labLogo = $practice_settings->hl7_report_lab_logo;
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
		$data_list = array();
		$files = scandir( $this->dir );
		foreach( $files as $file ) {
			if( $file[0] == '.' )
				continue;
			$ft = explode( '.', $file );
			$ft = strtolower( $ft[count($ft) - 1] );
			if( $ft == 'pdf' ) {
				// FIXME: do something with reports that are PDFs and not in HL7
				continue;
			}
			$msh = file_get_contents( $this->dir . DS . $file );
			if( $msh !== false && substr( $msh, 0, 3 ) == 'MSH' ) {
				$fields = explode( $msh[3], $msh );
				$data = array();
				$data['unique_id'] 				= $file;
				$data['sponsor_name'] 			= $fields[2];
				$data['receiving_client_id'] 	= $this->recClientId;
				$data['report_service_date'] 	= __date( "Y-m-d H:i:s", strtotime( $fields[6] ));
				$data_list[] = $data;
			}
		}
		return $data_list;
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
		foreach( $unique_ids as $fn ) {
			$content_hl7 = file_get_contents( $this->dir . DS . $fn );
			if( $content_hl7 !== false ) {
				$x[$fn] = array( 
						'found' 		=> true, 
						'content_hl7' 	=> $content_hl7, 
						'content_html' 	=> null // filled in by composeHTML later (in EmdeonLabResult::extractSource) 
				);
				unlink( $this->dir . DS . $fn );
			} else {
				$x[$fn] = array( 
						'found' 		=> false, 
						'content_html'	=> null, 
						'content_hl7' 	=> null 
				);
			}
		}
		return $x;
	}	
}
