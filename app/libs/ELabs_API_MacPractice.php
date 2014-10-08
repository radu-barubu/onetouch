<?php
/**
 * ELabs implemented with MacPractice lab functionality (just HL7 files in MP sftp directory).
 */
class ELabs_API_MacPractice extends ELabs_API {
	protected $labLogo = null;						// FIXME: Get lab logo for MP clients
	protected $labLogoText = "logo";
	private $sftp = null;
		
	/**
	 * Check if this API is functional in given environment
	 * 
	 * @return boolean true if functional
	 */
	public function isOK() {
		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		if( $practice_settings->labs_setup != 'MacPractice' )
			return false;
		$this->recClientId = 'MacPracticeClient';
		return true;
	}
	
	/**
	 * Connect on sftp
	 * 
	 * @return boolean
	 */
	private function connect() {
		if( !is_null( $this->sftp ))
			return true;

		$practice_settings = ClassRegistry::init( 'PracticeSetting' )->getSettings();
		$practice_id = $practice_settings->practice_id;
		$host = $practice_settings->macpractice_host;
		$port = $practice_settings->macpractice_port;
		
		$success = @fsockopen($host, $port, $errno, $errstr, 5);
		if( $success ) {
			set_include_path( get_include_path() . PATH_SEPARATOR . WWW_ROOT . 'phpseclib' );
			require_once 'Net/SFTP.php';
			$this->sftp = new Net_SFTP( $host, $port );
			$success = $this->sftp->login( $practice_settings->macpractice_username, $practice_settings->macpractice_password );
			if( $success ) {
				$message = "MacPractice labs client ".$practice_id." is back up";
			} else {
				$message = "Unable to login to MacPractice labs client ".$practice_id." @ ".$host." on port ".$port;
				$this->sftp = null;
			}
		}
		else {
			$message = "Unable to connect to MacPractice labs client ".$practice_id." @ ".$host." on port ".$port;
		}
		$cacheKey = 'MP_'.$practice_id.'_labs_connection_check';
		Cache::set( array( 'duration' => '+1 month' ));
		$wasDown = Cache::read( $cacheKey );
		if( !$success && !$wasDown ) {
			// went down since last we checked, so alert us
			Cache::set( array( 'duration' => '+1 month' ));
			Cache::write( $cacheKey, date( 'n/j/Y h:i:s' ));
			email::send( 'MP Labs Connect Error', 'errors@onetouchemr.com', "MP Labs Connect Error", $message,'','',false,'','','','','' );
		} else if( $success && $wasDown ) {
			// came up since last we checked, so alert us that all is now well
			email::send('MP Labs Connect Error', 'errors@onetouchemr.com', "MP Labs Connect Error (resolved)", $message,'','',false,'','','','','' );
			Cache::delete($cacheKey);
		}
		return !!$success;	// explicitly returns true or false
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
		if( !$this->connect() )
			return $data_list;
		$files = $this->sftp->nlist();
		foreach( $files as $file ) {
			if( $file[0] == '.' )
				continue;
			$ft = explode( '.', $file );
			$ft = strtolower( $ft[count($ft) - 1] );
			if( $ft == 'pdf' ) {
				// FIXME: do something with reports that are PDFs and not in HL7
				$this->sftp->delete( $file );
				continue;
			}
			$msh = $this->sftp->get( $file );
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
	 * Compose the HTML for displaying the given HL7 lab report
	 *
	 * @param Emdeon_HL7	HL7 message
	 * @param array			mostly filled-in lab result model, i.e., $data['EmdeonLabResult']
	 * @return string to display
	 */
	public function composeHTML( $emdeon_hl7, $lab_result ) {
		$hl7_data = $emdeon_hl7->getData();
		$html = '';
		$html .= '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD><TITLE>LABORATORY REPORT</TITLE></HEAD>
<body style="margin-top: 0px; margin-left: 2px;">
<script type="text/javascript">
var s_report={
organization:
{
';
		$ploc	= ClassRegistry::init('PracticeLocation')->getHeadOfficeLocation();
		$addr1 	= $ploc['address_line_1'];
		$addr2 	= $ploc['address_line_2'];
		$city	= $ploc['city'];
		$state	= $ploc['state'];
		$zip	= $ploc['zip'];
		$name	= ClassRegistry::init('PracticeProfile')->getPracticeName();
		foreach( array(	'addr1', 'addr2', 'city', 'state', 'zip', 'name' ) as $vn )
			$$vn = addslashes( $$vn );
		$html .= "mailing_address_1: '$addr1',\n";
		$html .= "mailing_address_2: '$addr2',\n";
		$html .= "mailing_city: '$city',\n";
		$html .= "mailing_state: '$state',\n";
		$html .= "mailing_zip: '$zip',\n";
		$html .= "organization_name: '$name'\n";
		$html .= '
	},
clinicalreport:
{
report_subject: \'\',
';
		switch( $lab_result['status'] ) {
			case 'Final': 		$report_status = 'F'; break;
			case 'Partial': 	$report_status = 'P'; break;
			case 'Corrected': 	$report_status = 'C'; break;
			case 'Cancel': 		$report_status = 'X'; break;
			default: 			$report_status = '';  break;
		}
		$html .= "report_status: '$report_status',\n";
		$html .= '
		report_type: \'LABRES\'
},
orderresult:
{
';
		$receiving_cg_id 			= $this->recClientId;
		$referring_caregiver_name 	= $lab_result['physician_last_name'] . ', ' . $lab_result['physician_first_name'] . ' ' . $lab_result['physician_middle_name'];
		$person_account_number 		= $lab_result['mrn'];
		$filler_order_number 		= $lab_result['filler_order_number']; // e.g., 'DL751259M'
		$placer_order_number 		= $lab_result['placer_order_number']; // e.g., '44707'
		$result_status 				= $report_status; // FIXME: can/should we distinguish between the two in some way?
		$login_datetime 			= ''; // e.g., '5/21/2013 10:19 AM'
		// pull the first OBR for collection and results times
		$collection_datetime 	=
		$result_datetime		= null;
        foreach( $hl7_data['test_segments'] as $test_segment ) {
        	foreach( $test_segment as $x ) {
        		if( $x['segment_type'] == "OBR" ) {
        			$collection_datetime	= @$x['obr_request_date_time'];
        			if( empty( $collection_datetime ))
        				$collection_datetime = @$x['obr_observation_date_time'];
        			$result_datetime		= @$x['obr_results_date_time'];
        			if( empty( $filler_order_number ))
        				$filler_order_number = @$x['obr_filler_order_number'];
        			if( empty( $placer_order_number ))
        				$placer_order_number = @$x['obr_placer_order_number'];
        			break;
        		}
        	}
        	break;
        }
        foreach( array( 'collection_datetime', 'result_datetime' ) as $vn )
	        if( !is_null( $$vn ))
    	    	$$vn = __date( 'n/j/Y g:i a', strtotime( $$vn ));
		
        $birth_date			= @$hl7_data['patient_identification']['date_of_birth']; //'11/19/1956'
		if( !is_null( $birth_date )) {
			$birth_date = __date( 'n/j/Y', strtotime( $birth_date ));
		}
        $person_age_type	= 'YEARS';
        $person_age			= static::calculateAge( $birth_date, $result_datetime, $person_age_type );
        
		// clean up: some fields are composed of subfield0^subfield1^etc. and some may have single quotes, etc.
		foreach( array (
				'receiving_cg_id',		'referring_caregiver_name',		'person_account_number',
				'filler_order_number',	'placer_order_number'	
		) as $vn ) {
			if(! is_null ( $$vn )) {
				$subfields = explode( '^', $$vn );
				$$vn = addslashes( $subfields[0] );
			}
		}
        $html .= "receiving_cg_id: '$receiving_cg_id',\n";
		$html .= "referring_caregiver_name: '$referring_caregiver_name',\n";
		$html .= "person_account_number: '$person_account_number',\n";
		$html .= "person_age: '$person_age',\n";
		$html .= "person_age_type: '$person_age_type',\n";
		$html .= "filler_order_number: '$filler_order_number',\n";
		$html .= "placer_order_number: '$placer_order_number',\n";
		$html .= "login_datetime: '$login_datetime',\n";
		$html .= "collection_datetime: '$collection_datetime',\n";
		$html .= "result_datetime: '$result_datetime',\n";
		$html .= "result_status: '$result_status'\n";
		$html .= '
},
caregiver:
{
first_name: \'\',
last_name: \'\',
middle_name: \'\',
suffix: \'\'
},
person:
{
';
		$last_name				= @$hl7_data['patient_identification']['last_name'];
		$first_name				= @$hl7_data['patient_identification']['first_name'];
		$middle_name			= @$hl7_data['patient_identification']['middle_name'];
		$suffix					= @$hl7_data['patient_identification']['suffix'];
		$sex					= @$hl7_data['patient_identification']['sex'];
		$ssn					= @$hl7_data['patient_identification']['ssn'];
		$home_phone_number		= @$hl7_data['patient_identification']['home_phone_number'];
		$home_phone_area_code	= @$hl7_data['patient_identification']['home_phone_code'];
		foreach( array(	'last_name', 'first_name', 'middle_name', 'suffix', 'sex', 'ssn', 'home_phone_number', 'home_phone_area_code' ) as $vn )
			$$vn = addslashes( $$vn );
		$html .= "last_name: '$last_name',\n";
		$html .= "first_name: '$first_name',\n";
		$html .= "middle_name: '$middle_name',\n";
		$html .= "suffix: '$suffix',\n";
		$html .= "sex: '$sex',\n";
		$html .= "ssn: '$ssn',\n";
		$html .= "birth_date: '$birth_date',\n";
		$html .= "home_phone_number: '$home_phone_number',\n";
		$html .= "home_phone_area_code: '$home_phone_area_code'\n";
		$html .= '
	},
lab:
{
';
		// FIXME:  Lab director -- don't see where we could get this...currently get it from Emdeon directly
		$director_name_1	= ''; // e.g., 'Elisabeth S Brockie'
		$phone_area_code	= ''; // e.g., '972'
		$phone_number		= ''; // e.g., '9163200'
		$lab_name			= ''; // e.g., 'QUEST DIAGNOSTICS-DALLAS'
		$address_1			= ''; // e.g., '4770 Regent Blvd'
		$address_2			= '';
		$city				= ''; // e.g., 'Irving'
		$state				= ''; // e.g., 'TX',
		$zip				= ''; // e.g., '75063'
		$html .= "director_name_1: '$director_name_1',\n";
		$html .= "phone_area_code: '$phone_area_code',\n";
		$html .= "phone_number: '$phone_number',\n";
		$html .= "lab_name: '$lab_name',\n";
		$html .= "address_1: '$address_1',\n";
		$html .= "address_2: '$address_2',\n";
		$html .= "city: '$city',\n";
		$html .= "state: '$state',\n";
		$html .= "zip: '$zip'\n";
		$html .= '
}
';
		if( !is_null( $this->labLogo ))
		{
			$filename = $this->labLogo; // e.g., '/img/labs/sbcllogo.gif' or 'https://clinician.emdeon.com/images/lab/Quest.jpg'
			$contents = $this->labLogoText;
		}
		else {
			$filename = '';
			$contents = '';
		}
		$html .= ", file: {\n";
		$html .= "filename: '$filename',\n";
		$html .= "contents: '$contents'\n}\n";
		
		$html .= '
};//end s_report
var s_sLabLogo= s_report.file.filename;
var s_sLabLogoText= s_report.file.contents;
</script>
<script type="text/javascript" src="/js/labs/quest.js?1371580625"></script>
<script type="text/javascript">
var z_theReportLines= new Array();
				';
			
		/*
		 * Fill in z_theReportLines
		 */
		$n = 0;
		$labs = array ();
        
        // initialize in case we have segments out of order (especially NTEs)
		$nSeq 			=		$nTestLines 	=		$bAbnormal 		= 0;
		$sType 			=		$sTest 			=		$sTestCode 		=
		$sSpecimen 		=		$sProfile 		=		$sAnalyte 		=
		$sStatus 		=		$sUnits 		=		$sRefRange 		=
		$sSiteCode 		=		$sResultStatus 	=		$sAnalyteCode 	=
		$sResultTime 	= '';

        foreach( $hl7_data['test_segments'] as $test_segment ) {
        	foreach( $test_segment as $x ) {
        		if( $x['segment_type'] == "OBR" ) {
			        // initialize in case we have segments out of order (especially NTEs)
					$bAbnormal 		= 0;
					$sAnalyte 		=			$sStatus 		=			$sUnits 		=
					$sRefRange 		=			$sSiteCode 		=			$sResultStatus 	=
					$sAnalyteCode 	=			$sResultTime 	= '';
					// get test-wide parameters from OBR
        			$nSeq		= 0;
        			$nTestLines	= count( $test_segment );
        			$sType		= '';
        			$sTest 		= @$x['obr_description'];
        			$sTestCode	= @$x['obr_order_code'];
        			$sSpecimen	= @$x['obr_specimen_source_description'];  // FIXME: just guessing, no examples found
        			$sProfile	= @$x['obr_reason_for_study'];
        		} else if( $x['segment_type'] == "OBX" || $x['segment_type'] == "NTE" ) {
        			if( $x['segment_type'] == "OBX" ) {
	        			$sType 		= @$x['obx_value_type'];
	        			if( empty( $sType ))
	        				$sType = "ST";
        				$sAnalyte 	= @( !empty( $x['obx_LOINC_description'] ) ? $x['obx_LOINC_description'] : $x['obx_analyte_description'] );
        				$sStatus	= @$x['obx_abnormal_flags'];
        				$bAbnormal	= ( !is_null( $sStatus ) && $sStatus != 'N' && $sStatus != '' ) ? 1 : 0;
        				$sUnits		= @$x['obx_unit_code'];
        				if( !is_null( $sUnits )) {
        					$subfields = explode( '^', $sUnits );
        					$sUnits = $subfields[0];
        				} 
       		 			$sRefRange	= @$x['obx_range'];
        				$sSiteCode	= @$x['obx_lab_hospital_id'];
        				$sResultStatus 	= @$x['obx_observe_result_status'];
        				$sAnalyteCode	= @$x['obx_analyte_code'];
        				$sResultTime	= @$x['obx_date_time_of_the_observation'];
        				if( !is_null( $sResultTime ))
        					$sResultTime = __date( 'n/j/Y g:i a', strtotime( $sResultTime ));
        				if( $sType == 'TX' )
        					$sType = 'NTE';  // just treat the text data like an NTE
        				else if( $sType == 'ED' )
        					continue;  // skip embedded PDFs 
        				$sResultStatus 	= @$x['obx_observe_result_status'];
        				$sResult		= @$x['obx_result_value'];
        				if( is_null( $sSiteCode ) || $sSiteCode == '' ) {
        					$sSiteCode = '--';	// use a made-up code
        					$labs[$sSiteCode] = 'unspecified site';
        				} else {
	        				$subfields = explode( '^', $sSiteCode );
    	    				$sSiteCode = $subfields[0];
        					if( !isset( $labs[$sSiteCode] ))
	        					$labs[$sSiteCode] = isset( $subfields[1] ) ? $subfields[1] : "site '$sSiteCode'";
        				}
        			} else {
        				$sType			= "NTE";
        				$sResultStatus 	= '';
        				$sResult		= @$x['comment'];
        				if( is_null( $sResult) || $sResult == '' )
        					continue; // skip blank NTE lines FIXME: saw some examples where it skips blank lines and others where it uses ' ' 
        			}

					// clean up: some fields are composed of subfield0^subfield1^etc. and some may have single quotes, etc.
					foreach( array (
							'sType',		'sTest',		'sAnalyte',		'sStatus',			'sResult',							
							'sUnits',		'sRefRange',	'sSiteCode',	'sResultStatus',	'sTestCode',			
							'sAnalyteCode',	'sResultTime',	'sSpecimen',	'sProfile' 
					) as $vn ) {
						if(! is_null ( $$vn )) {
							$subfields = explode( '^', $$vn );
							$$vn = addslashes( $subfields[0] );
						}
					}
	        		$html .= "z_theReportLines[$n]=new ReportLine($nSeq,$nTestLines,'$sType','$sTest','$sAnalyte',$bAbnormal,'$sStatus','$sResult','$sUnits','$sRefRange','$sSiteCode','$sResultStatus','$sTestCode','$sAnalyteCode','$sResultTime','$sSpecimen','$sProfile');\n";
	        		$n++;
        			$nSeq++;
        		}
        	}
        }
		$html .= '
var z_theLabs= new Array();
		';
		$n = 0;
		foreach( $labs as $code => $name ) {
			$name = addslashes( $name );
			$html .= "z_theLabs[$n]=new Lab('$code','$name','','','','','','','','');\n";
			$n++;
		}
		$html .= '
process_lines(s_nMainEdge, z_theReportLines, z_theLabs);
</script>
</body>
</HTML>
		';
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
		$x = array();
		if( !$this->connect() )
			return $x;
		foreach( $unique_ids as $fn ) {
			$content_hl7 = $this->sftp->get( $fn );
			if( $content_hl7 !== false ) {
				$x[$fn] = array( 
						'found' 		=> true, 
						'content_hl7' 	=> $content_hl7, 
						'content_html' 	=> null // filled in by composeHTML later (in EmdeonLabResult::extractSource) 
				);
				$this->sftp->delete( $fn );
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
	
	/**
	 * calculate age and units for javascript parameters
	 * 
	 * @param 	string	date of birth
	 * @param  	string	time/date of test
	 * @param  	&string output units to use, 'YEARS' or 'MONTHS' (used for under 2 years)
	 * @return string of years or months old
	 */
	protected function calculateAge( $dob, $now, &$units ) {
		$dob = new DateTime( $dob );
		$now = new DateTime( $now );
		$age = $now->diff( $dob, true );
		if( $age->y < 2 ) {
			$units = 'MONTHS';
			return $age->y * 12 + $age->m;
		} else {
			$units = 'YEARS';
			return $age->y;
		}
	}
}
