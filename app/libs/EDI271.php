<?php

class EDI271
{
	public static function get_271_info($Response271)
	{
		$all_data = array('payer' => array(), 'provider' => array(), 'subscriber' => array(), 'eligibility' => array());

		/* * ***** This will be a two dimensional array ****************
		 * ******* that holds the content nicely organized ************ */
		
		$DataSegment271 = array();
		$Segments271 = array();
		
		/* * ******* We will use this as an index ********************* */
		$i = 0;
		$j = 0;
		$patientId = "";
		
		
		/* * ***** In the array store this line **************  
		 * ****** with values delimited by ^ (tilt) ********* 
		 * ****** as separate array values ***************** */
		
		$DataSegment271 = explode("~", $Response271);
		
		if (count($DataSegment271) < 6)
		{
			$DataSegment271 = explode("^", $Response271);
		}
		
		if (count($DataSegment271) < 6)
		{
			$messageEDI = true;
			$message = "";
		}
		else
		{
			$forCount = 1;
			
			$current_loop = "";
			
			$is_error = false;
		
			foreach ($DataSegment271 as $datastrings)
			{
				$Segments271[$j] = explode("*", $datastrings); 
				$Segments271[$j] = array_map("trim", $Segments271[$j]);
		
				$segment = $Segments271[$j][0];
		
				/** **** Switch Case for Segment ************ */
		
				switch($segment)
				{
					case "HL":
						if((string)@$Segments271[$j][1] == "1" && (string)@$Segments271[$j][3] == "20" && (string)@$Segments271[$j][4] == "1")
						{
							$current_loop = "payer";  
						}
						
						if((string)@$Segments271[$j][1] == "2" && (string)@$Segments271[$j][3] == "21" && (string)@$Segments271[$j][4] == "1")
						{
							$current_loop = "provider";  
						}
						
						if((string)@$Segments271[$j][1] == "3" && (string)@$Segments271[$j][3] == "22" && (string)@$Segments271[$j][4] == "0")
						{
							$current_loop = "subscriber";  
						}
						
						break;
					case 'ISA':
		
						$x12PartnerId = (string)@$Segments271[0][6];
						break;
		
					case 'NM1':
		
						if($current_loop == "payer")
						{
							$all_data['payer']['payer_name'] = (string)@$Segments271[$j][3];
							$all_data['payer']['payer_identification'] = (string)@$Segments271[$j][9];
						}
						else if($current_loop == "provider")
						{
							$all_data['provider']['provider_name'] = (string)@$Segments271[$j][3];
							
							if($Segments271[$j][8] == 'XX')
							{
								$all_data['provider']['provider_npi'] = (string)@$Segments271[$j][9];
							}
							else if($Segments271[$j][8] == 'SV')
							{
								$all_data['provider']['service_provider_number_medicaid'] = (string)@$Segments271[$j][9];
							}
						}
						else if($current_loop == "subscriber")
						{
							if ((string)@$Segments271[$j][1] == "IL" && (string)@$Segments271[$j][2] == "1")
							{
								$all_data['subscriber']['last_name'] = (string)@$Segments271[$j][3];
								$all_data['subscriber']['first_name'] = (string)@$Segments271[$j][4];
								$all_data['subscriber']['middle_name'] = (string)@$Segments271[$j][5];
								$all_data['subscriber']['subscriber_id'] = (string)@$Segments271[$j][9];
							}
						}
						break;
					case "N3":
						if($current_loop == "subscriber")
						{
							$all_data['subscriber']['address1'] = (string)@$Segments271[$j][1];
							$all_data['subscriber']['address2'] = (string)@$Segments271[$j][2];
						}
						break;
					case "N4":
						if($current_loop == "subscriber")
						{
							$all_data['subscriber']['city'] = (string)@$Segments271[$j][1];
							$all_data['subscriber']['state'] = (string)@$Segments271[$j][2];
							$all_data['subscriber']['zipcode'] = (string)@$Segments271[$j][3];
						}
						break;
					case 'DMG':
						if($current_loop == "subscriber")
						{
							if($Segments271[$j][1] == 'D8')
							{
								$all_data['subscriber']['dob'] = substr($Segments271[$j][2], 0, 4).'-'.substr($Segments271[$j][2], 4, 2).'-'.substr($Segments271[$j][2], 6, 2);
								$all_data['subscriber']['gender'] = (string)@$Segments271[$j][3];
							}
						}
						break;
					case 'AAA':
						if($current_loop == "provider")
						{
							if($Segments271[$j][1] == 'Y' && $Segments271[$j][3] == '43')
							{
								$all_data['eligibility']['status'] = "Provider Not on File";
							}
							else
							{
								$all_data['eligibility']['status'] = "Unknown Provider Error.";
							}
						}
						else if($current_loop == "subscriber")
						{
							if($Segments271[$j][1] == 'Y' && $Segments271[$j][3] == '75')
							{
								$all_data['eligibility']['status'] = "Patient Not Found";
							}
							else if($Segments271[$j][1] == 'Y' && $Segments271[$j][3] == '76')
							{
								$all_data['eligibility']['status'] = "Duplicate Subscriber";
							}
							else
							{
								$all_data['eligibility']['status'] = "Unknown Subscriber Error.";
							}
						}
						else
						{
							$all_data['eligibility']['status'] = "Unknown error";
						}
						
						$is_error = true;
						
						break;
					case 'REF':
						
						if($current_loop == "subscriber")
						{
							$EBVAL = (string)@$Segments271[$j][1];
		
							switch($EBVAL)
							{
		
								case "EJ":
									$all_data['subscriber']['id_type'] = "ID";
									$all_data['subscriber']['id_value'] = (string)@$Segments271[$j][2];
									break;
		
								case "18": /* Contract Number followed by Plan Number */
									$all_data['subscriber']['id_type'] = "Plan No";
									$all_data['subscriber']['id_value'] = (string)@$Segments271[$j][2];
									break;
		
								case "IG": /* Insurance Policy Number */
									$all_data['subscriber']['id_type'] = "Insurance Policy Number";
									$all_data['subscriber']['id_value'] = (string)@$Segments271[$j][2];
									break;
		
								case "49": /* Family ID Number */
									$all_data['subscriber']['id_type'] = "Family ID Number";
									$all_data['subscriber']['id_value'] = (string)@$Segments271[$j][2];
									break;
		
								case "SY": /* Reference ID Qualifier - SSN */
									$all_data['subscriber']['id_type'] = "Social Security Number";
									$all_data['subscriber']['id_value'] = (string)@$Segments271[$j][2];
									break;
		
								case "F6": /* Health Insurance Claim Number - Medicare ID Number */
									$all_data['subscriber']['id_type'] = "Medicare ID";
									$all_data['subscriber']['id_value'] = (string)@$Segments271[$j][2];
									break;
							}
						}
		
						break;
					case 'EB':
		
						$EBVAL = $Segments271[$j][1];
		
						switch($EBVAL)
						{
							case '1':
								$all_data['eligibility']['status'] = "Active Coverage";
								break;
							case '6':
								$all_data['eligibility']['status'] = "Subscriber is Not Eligible";
								break;
							case 'B':
								if ((string)@$Segments271[$j][3] == "47")
								{
									$all_data['eligibility']['details'][] = array(
										'field' => "Hospital Copayment Days Remaining",
										'value' => (string)@$Segments271[$j][10]
									);
								}
								else if ((string)@$Segments271[$j][3] == "AG")
								{
									$all_data['eligibility']['details'][] = array(
										'field' => "SNF Copayment Days Remaining",
										'value' => (string)@$Segments271[$j][10]
									);
								}
								else
								{
									$all_data['eligibility']['details'][] = array(
										'field' => "Hospital Copayment Days Remaining",
										'value' => (string)@$Segments271[$j][10]
									);
								}
								break;
							case 'C':
								if ((string)@$Segments271[$j][4] == "MA")
								{
									$all_data['eligibility']['details'][] = array(
										'field' => "Part A Deductible Remaining",
										'value' => (string)@$Segments271[$j][7]
									);
								}
								else if ((string)@$Segments271[$j][4] == "MB")
								{
									$all_data['eligibility']['details'][] = array(
										'field' => "Part B Deductible Remaining",
										'value' => (string)@$Segments271[$j][7]
									);
								}
								else if ((string)@$Segments271[$j][2] == "IND")
								{
									$all_data['eligibility']['details'][] = array(
										'field' => "Blood Deductible - Number of Units Remaining",
										'value' => (string)@$Segments271[$j][10]
									);
								}
								else
								{
									$all_data['eligibility']['details'][] = array(
										'field' => "Deductible Remaining",
										'value' => (string)@$Segments271[$j][7]
									);
								}
								break;
		
							case 'D': 
								
								if ((string)@$Segments271[$j][3] == "44")
								{
									$all_data['eligibility']['details'][] = array(
										'field' => "Number of Home Health Visits Remaining",
										'value' => (string)@$Segments271[$j][10]
									);
								}
								else if ((string)@$Segments271[$j][4] == "HM")
								{
									$all_data['eligibility']['details'][] = array(
										'field' => "Mental Health Services - PAID",
										'value' => (string)@$Segments271[$j][5]
									);
								}
								else if ((string)@$Segments271[$j][3] == "67")
								{
									$segmentVal = "Next Eligible Smoking Counseling Date";
									$segmentValDataArray = explode("*", $DataSegment271[$forCount]);
									$segmentValDataArrayDates = "";
									$segmentValDataArr = array();
									$segmentValDataArrayDates = explode("-", $segmentValDataArray[3]);
									foreach ($segmentValDataArrayDates as $date)
									{
										$timeX = strtotime($date);
										$segmentValDataArr[] = date('j F Y', $timeX);
									}
									$segmentValData = implode("-", $segmentValDataArr);
									
									$all_data['eligibility']['details'][] = array(
										'field' => $segmentVal,
										'value' => (string)@$segmentValData
									);
								}
								else
								{
									$segmentVal = "Preventive Care with the same Professional(HCPCS Code)";
									$segmentValDataArray = explode(":", (string)@$Segments271[$j][13]);
									$segmentValData = (string)@$segmentValDataArray[1];
									
									$all_data['eligibility']['details'][] = array(
										'field' => $segmentVal,
										'value' => (string)@$segmentValData
									);
								}
								break;
		
							case 'F': 
								if ((string)@$Segments271[$j][3] == "AD")
								{
									$segmentVal = "Occupational Therapy - Therapy Capitation Amount Remaining";
									$segmentValData = (string)@$Segments271[$j][7];
								}
								else if ((string)@$Segments271[$j][3] == "AE")
								{
									$segmentVal = "Therapy Capitation Amount Remaining";
									$segmentValData = (string)@$Segments271[$j][7];
								}
								else if ((string)@$Segments271[$j][3] == "47")
								{
									$segmentVal = "Hospital Full Days Remaining";
									$segmentValData = (string)@$Segments271[$j][10];
								}
								else if ((string)@$Segments271[$j][3] == "AG")
								{
									$segmentVal = "SNF Full Days Remaining";
									$segmentValData = (string)@$Segments271[$j][10];
								}
								else if ((string)@$Segments271[$j][3] == "67")
								{
									$segmentVal = "Number of Sessions Remaining";
									$segmentValData = (string)@$Segments271[$j][10];
								}
								else
								{
									$segmentVal = "Physical and Speech Therapy - Amount Remaining";
									$segmentValData = (string)@$Segments271[$j][7];
								}
								
								$all_data['eligibility']['details'][] = array(
									'field' => $segmentVal,
									'value' => (string)@$segmentValData
								);
								break;
							case 'J':
								if ((string)@$Segments271[$j][3] == "13")
								{
									$segmentVal = "Number of Ambulatory Visits Remaining";
									$segmentValData = (string)@$Segments271[$j][10];
								}
								else if ((string)@$Segments271[$j][3] == "33")
								{
									$segmentVal = "Number of Chiropractic Visits Remaining";
									$segmentValData = (string)@$Segments271[$j][10];
								}
								else
								{
									$segmentVal = "Cost Containment";
									$segmentValData = (string)@$Segments271[$j][10];
								}
		
								$all_data['eligibility']['details'][] = array(
									'field' => $segmentVal,
									'value' => (string)@$segmentValData
								);
								break;
							case 'K':
								if ((string)@$Segments271[$j][4] == "MA")
								{
									$segmentVal = "Part A Lifetime Days Remaining";
									$segmentValData = (string)@$Segments271[$j][10];
								}
								else if ((string)@$Segments271[$j][3] == "AG")
								{
									$segmentVal = "SNF Full Days Remaining";
									$segmentValData = (string)@$Segments271[$j][10];
								}
								else
								{
									$segmentVal = "Lifetime Days Remaining";
									$segmentValData = (string)@$Segments271[$j][10];
								}
								
								$all_data['eligibility']['details'][] = array(
									'field' => $segmentVal,
									'value' => (string)@$segmentValData
								);
								break;
							case 'L':
		
								$segmentVal = " RSP Code Description";
								$segmentValData = (string)@$Segments271[$j][5];
								
								$all_data['eligibility']['details'][] = array(
									'field' => $segmentVal,
									'value' => (string)@$segmentValData
								);
								break;
							case 'R':
								if ((string)@$Segments271[$j][4] == "MA")
								{
									$segmentVal = "MEDICARE PART A DESC";
									$segmentValData = (string)@$Segments271[$j][5];
								}
								else if ((string)@$Segments271[$j][4] == "MB")
								{
									$segmentVal = "MEDICARE PART B DESC";
									$segmentValData = (string)@$Segments271[$j][5];
								}
								else if ((string)@$Segments271[$j][4] == "OT")
								{
									$segmentVal = "Policy Description";
									$segmentValDataArray = explode("*", (string)@$DataSegment271[$forCount]);
									$segmentValData = (string)@$segmentValDataArray[2];
								}
		
								$all_data['eligibility']['details'][] = array(
									'field' => $segmentVal,
									'value' => (string)@$segmentValData
								);
								break;
							case 'X':
								if ((string)@$Segments271[$j][4] == "MA" && (string)@$Segments271[$j][3] == "42")
								{
									$segmentVal = "Home Health Dates";
									$segmentValDataArray = explode("*", (string)@$DataSegment271[$forCount]);
									$segmentValDataArrayDates = "";
									$segmentValDataArr = array();
									$segmentValDataArrayDates = explode("-", (string)@$segmentValDataArray[3]);
									foreach ($segmentValDataArrayDates as $date)
									{
										$timeX = strtotime($date);
										$segmentValDataArr[] = date('j F Y', $timeX);
									}
									$segmentValData = implode("-", $segmentValDataArr);
								}
								else if ((string)@$Segments271[$j][4] == "MA" && (string)@$Segments271[$j][3] == "45")
								{
									$segmentVal = "Hospice Care Dates";
									$segmentValDataArray = explode("*", $DataSegment271[$forCount]);
									$segmentValDataArrayDates = "";
									$segmentValDataArr = array();
									$segmentValDataArrayDates = explode("-", $segmentValDataArray[3]);
									foreach ($segmentValDataArrayDates as $date)
									{
										$timeX = strtotime($date);
										$segmentValDataArr[] = date('j F Y', $timeX);
									}
									$segmentValData = implode("-", $segmentValDataArr);
								}
		
								$all_data['eligibility']['details'][] = array(
									'field' => $segmentVal,
									'value' => (string)@$segmentValData
								);
								break;
						}
						break;
		
					case 'MSG':
						$all_data['eligibility']['messages'][] = (string)@$Segments271[$j][1];
						
						break;
				}
				
				if($is_error)
				{
					break;
				}
		
				/*         * ***** Increase the line index ************** */
				$j++;
				$forCount++;
			}
		}
		
		return $all_data;
	}
	
}

?>