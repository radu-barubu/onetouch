<html>
<head>
	<title>Disclosure Report</title>
	<style>
       .btn, a.btn {
	color: #464646;
	cursor: pointer;
	padding: 5px 6px;
	margin-right: 5px;
	text-decoration: none;
	font-weight: bold;
	//float: left;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	border: 1px solid #ddd;
	background: -moz-linear-gradient(center top, #fefefe, #eee) repeat scroll 0 0 transparent;
	background: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#eee));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fefefe', endColorstr='#eeeeee');
	}	
	h1 {
		 font-family:Georgia,serif;
		 color:#000;
		 font-variant: small-caps; text-transform: none; font-weight: 100;
		 margin-top: 5px; margin-bottom: -5px;
	}
	
	h3 {
		 font-family: times, Times New Roman, times-roman, georgia, serif;
		 margin-top: 5px; margin-bottom: 2px;
		 letter-spacing: -1px;color: #000;
	}
	
	b {
		font-family: Georgia,"Times New Roman",serif;
		font-size: 12px;
		font-weight: bold;
		color: #000;
		line-height: 17px;
		margin: 0;
		letter-spacing: 1px
	}
	
	li {
		margin: 4px 0px 3px px;
	}
	
	body,table {
		font-family: "Helvetica Neue", "Lucida Grande", Helvetica, Arial, Verdana, sans-serif;
		font-size: 14px;
		color: #000;
	}
	.reportborder { background-color:#E0F2F7; padding:7px 0px 7px 5px; font-weight:bold;}
	@media print{
		.hide_for_print {
		display: none;
		}
	}
	</style>
	<?php
		$scriptArray = array('/js/jquery/jquery.js');
		if(isset($isiPadApp)&&$isiPadApp)
			$scriptArray[] = '/js/iPad/jquery.ipadapp.js';
		echo $this->Html->script($scriptArray);
	?>       
</head>
<body>
<div class="hide_for_print reportborder"> <a class=btn href="javascript:window.print()">Print <img src="<?php echo Router::url("/", true).'img/printer_icon_small.png'; ?>"  style="vertical-align:bottom;padding-left:3px"></a>  <span style="margin-left:20px"> <a class=btn href="<?php echo $html->url(array('action' => 'disclosure_records', 'patient_id' => $demographic['PatientDemographic']['patient_id'], 'disclosure_id' => $disclosure['PatientDisclosure']['disclosure_id'], 'task' => 'get_report_pdf'));  ?>" target="_blank">PDF <img src="<?php echo Router::url("/", true).'img/pdf.png'; ?>" style="vertical-align:bottom;padding-left:3px"></a></span>  
                            <?php if (!$isiPadApp): ?> 
                            <span style="margin-left:20px"><a class=btn href="<?php echo $html->url(array('action' => 'disclosure_records', 'patient_id' => $demographic['PatientDemographic']['patient_id'], 'disclosure_id' => $disclosure['PatientDisclosure']['disclosure_id'], 'task' => 'get_report_ccr')); ?>">CCR <img src="<?php echo Router::url("/", true).'img/exchange_icon.png'; ?>" style="vertical-align:bottom;padding-left:3px"></a></span> 
							<?php endif;?> 
</div>							
<hr />
	<div>
		<?php
		echo 'Patient: '.$demographic['PatientDemographic']['first_name'].' '.$demographic['PatientDemographic']['last_name'].'<br>';
		echo 'MRN: '.$demographic['PatientDemographic']['mrn'].'<br>';
		?>
	</div><br>

	<?php
	
	$patient_disclosure = (isset($_COOKIE['patient_disclosure_'.$demographic['PatientDemographic']['patient_id']])) ? $_COOKIE['patient_disclosure_'.$demographic['PatientDemographic']['patient_id']] : "";
	$patient_disclosure = explode("|", $patient_disclosure);
	
	if ($patient_disclosure[0] == "checked")
	{
	?>
	<div>
	<b>Demographic</b><hr />
		<?php
		if ($demographic['PatientDemographic']['first_name'])
		{
			echo 'First name: '.$demographic['PatientDemographic']['first_name'].'<br>';
		}
		if ($demographic['PatientDemographic']['middle_name'])
		{
			echo 'Middle name: '.$demographic['PatientDemographic']['middle_name'].'<br>';
		}
		if ($demographic['PatientDemographic']['last_name'])
		{
			echo 'Last name: '.$demographic['PatientDemographic']['last_name'].'<br>';
		}
		if ($demographic['PatientDemographic']['gender'])
		{
			echo 'Gender: '.($demographic['PatientDemographic']['gender']=='M'?"Male":"Female").'<br>';
		}
		if ($demographic['PatientDemographic']['preferred_language'])
		{
			echo 'Preferred Language: '.$demographic['PatientDemographic']['preferred_language'].'<br>';
		}
		if ($demographic['PatientDemographic']['race'])
		{
			echo 'Race: '.$demographic['PatientDemographic']['race'].'<br>';
		}
		if ($demographic['PatientDemographic']['ethnicity'])
		{
			echo 'Ethnicity: '.$demographic['PatientDemographic']['ethnicity'].'<br>';
		}
		if ($demographic['PatientDemographic']['dob'])
		{
			echo 'DOB: '.$demographic['PatientDemographic']['dob'].'<br>';
		}
		if ($demographic['PatientDemographic']['ssn'])
		{
			echo 'SSN: '.$demographic['PatientDemographic']['ssn'].'<br>';
		}
		if ($demographic['PatientDemographic']['driver_license_id'])
		{
			echo 'Driver License ID: '.$demographic['PatientDemographic']['driver_license_id'].'<br>';
		}
		if ($demographic['PatientDemographic']['occupation'])
		{
			echo 'Occupation: '.$demographic['PatientDemographic']['occupation'].'<br>';
		}
		if ($demographic['PatientDemographic']['marital_status'])
		{
			echo 'Marital Status: '.$demographic['PatientDemographic']['marital_status'].'<br>';
		}
		if ($demographic['PatientDemographic']['address1'])
		{
			echo 'Address 1: '.$demographic['PatientDemographic']['address1'].'<br>';
		}
		if ($demographic['PatientDemographic']['address2'])
		{
			echo 'Address 2: '.$demographic['PatientDemographic']['address2'].'<br>';
		}
		if ($demographic['PatientDemographic']['city'])
		{
			echo 'City: '.$demographic['PatientDemographic']['city'].'<br>';
		}
		if ($demographic['PatientDemographic']['state'])
		{
			echo 'State: '.$demographic['PatientDemographic']['state'].'<br>';
		}
		if ($demographic['PatientDemographic']['zipcode'])
		{
			echo 'Zip Code: '.$demographic['PatientDemographic']['zipcode'].'<br>';
		}
		if ($demographic['PatientDemographic']['immtrack_county'])
		{
			echo 'County: '.$demographic['PatientDemographic']['immtrack_county'].'<br>';
		}
		if ($demographic['PatientDemographic']['immtrack_country'])
		{
			echo 'Country: '.$demographic['PatientDemographic']['immtrack_country'].'<br>';
		}
		if ($demographic['PatientDemographic']['work_phone'])
		{
			echo 'Work Phone: '.$demographic['PatientDemographic']['work_phone'];
			if ($demographic['PatientDemographic']['work_phone_extension'])
			{
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Extension: '.$demographic['PatientDemographic']['work_phone_extension'];
			}
			echo '<br>';
		}
		if ($demographic['PatientDemographic']['home_phone'])
		{
			echo 'Home Phone: '.$demographic['PatientDemographic']['home_phone'].'<br>';
		}
		if ($demographic['PatientDemographic']['cell_phone'])
		{
			echo 'Cell Phone: '.$demographic['PatientDemographic']['cell_phone'].'<br>';
		}
		if ($demographic['PatientDemographic']['email'])
		{
			echo 'Email Address: '.$demographic['PatientDemographic']['email'].'<br>';
		}
		if ($demographic['PatientDemographic']['guardian'])
		{
			echo 'Guardian\'s Name: '.$demographic['PatientDemographic']['guardian'].'<br>';
		}
		if ($demographic['PatientDemographic']['relationship'])
		{
			echo 'Relationship to Guardian: '.$demographic['PatientDemographic']['relationship'].'<br>';
		}
		if ($demographic['PatientDemographic']['emergency_contact'])
		{
			echo 'Emergency Contact: '.$demographic['PatientDemographic']['emergency_contact'].'<br>';
		}
		if ($demographic['PatientDemographic']['emergency_phone'])
		{
			echo 'Emergency Phone: '.$demographic['PatientDemographic']['emergency_phone'].'<br>';
		}
		if ($demographic['PatientDemographic']['status'])
		{
			echo 'Status: '.$demographic['PatientDemographic']['status'].'<br>';
		}
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[1] == "checked")
	{
	?>
	<div>
	<b>Medical History</b><hr />
		<?php
		$i = 0;
		
		foreach ($medical_histories as $medical_history):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($medical_history['PatientMedicalHistory']['diagnosis'])
			{
				echo 'Diagnosis: '.$medical_history['PatientMedicalHistory']['diagnosis'].'<br>';
			}
			if ($medical_history['PatientMedicalHistory']['status'])
			{
				echo 'Status: '.$medical_history['PatientMedicalHistory']['status'].'<br>';
			}
			if ($medical_history['PatientMedicalHistory']['start_month'] && $medical_history['PatientMedicalHistory']['start_year'])
			{
				echo 'Start Date: '. __date("F Y", strtotime($medical_history['PatientMedicalHistory']['start_year']."-".$medical_history['PatientMedicalHistory']['start_month']."-15"))."<br>";
			}
			if ($medical_history['PatientMedicalHistory']['end_month'] && $medical_history['PatientMedicalHistory']['end_year'])
			{
				echo 'End Date: '. __date("F Y", strtotime($medical_history['PatientMedicalHistory']['end_year']."-".$medical_history['PatientMedicalHistory']['end_month']."-15"))."<br>";
			}
			if ($medical_history['PatientMedicalHistory']['occurrence'])
			{
				echo 'Occurrence: '.$medical_history['PatientMedicalHistory']['occurrence'].'<br>';
			}
			if ($medical_history['PatientMedicalHistory']['comment'])
			{
				echo 'Comment: '.$medical_history['PatientMedicalHistory']['comment'].'<br>';
			}
		endforeach;
		?>
	</div><br>

	<div>
	<b>Surgical History</b><hr />
		<?php
		$i = 0;
		foreach ($surgical_histories as $surgical_history):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($surgical_history['PatientSurgicalHistory']['surgery'])
			{
				echo 'Surgery: '.$surgical_history['PatientSurgicalHistory']['surgery'].'<br>';
			}
			if ($surgical_history['PatientSurgicalHistory']['type'])
			{
				echo 'Type: '.$surgical_history['PatientSurgicalHistory']['type'].'<br>';
			}
			if ($surgical_history['PatientSurgicalHistory']['hospitalization'])
			{
				echo 'Hospitalization: '.$surgical_history['PatientSurgicalHistory']['hospitalization'].'<br>';
			}
			if ($surgical_history['PatientSurgicalHistory']['date_from'])
			{
				echo 'Date From: '.$surgical_history['PatientSurgicalHistory']['date_from'].'<br>';
			}
			if ($surgical_history['PatientSurgicalHistory']['date_to'])
			{
				echo 'Date To: '.$surgical_history['PatientSurgicalHistory']['date_to'].'<br>';
			}
			if ($surgical_history['PatientSurgicalHistory']['reason'])
			{
				echo 'Reason: '.$surgical_history['PatientSurgicalHistory']['reason'].'<br>';
			}
			if ($surgical_history['PatientSurgicalHistory']['outcome'])
			{
				echo 'Outcome: '.$surgical_history['PatientSurgicalHistory']['outcome'].'<br>';
			}
		endforeach;
		?>
	</div><br>

	<div>
	<b>Social History</b><hr />
		<?php
		$i = 0;
		foreach ($social_histories as $social_history):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($social_history['PatientSocialHistory']['type'])
			{
				echo 'Type: '.$social_history['PatientSocialHistory']['type'].'<br>';
			}
			if ($social_history['PatientSocialHistory']['type'] == 'Activities')
			{
				if ($social_history['PatientSocialHistory']['routine'])
				{
					echo 'Routine: '.$social_history['PatientSocialHistory']['routine'].'<br>';
				}
			}
			else
			{
				if ($social_history['PatientSocialHistory']['substance'])
				{
					echo 'Substance: '.$social_history['PatientSocialHistory']['substance'].'<br>';
				}
			}
			if ($social_history['PatientSocialHistory']['comment'])
			{
				echo 'Comment: '.$social_history['PatientSocialHistory']['comment'].'<br>';
			}
			if ($social_history['PatientSocialHistory']['type'] == 'Activities')
			{
				if ($social_history['PatientSocialHistory']['routine_status'])
				{
					echo 'Status: '.$social_history['PatientSocialHistory']['routine_status'].'<br>';
				}
			}
			else
			{
				if ($social_history['PatientSocialHistory']['consumption_status'])
				{
					echo 'Status: '.$social_history['PatientSocialHistory']['consumption_status'].'<br>';
				}
			}
		endforeach;
		?>
	</div><br>

	<div>
	<b>Family History</b><hr />
		<?php
		$i = 0;
		foreach ($family_histories as $family_history):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($family_history['PatientFamilyHistory']['name'])
			{
				echo 'Name: '.$family_history['PatientFamilyHistory']['name'].'<br>';
			}
			if ($family_history['PatientFamilyHistory']['relationship'])
			{
				echo 'Relationship: '.$family_history['PatientFamilyHistory']['relationship'].'<br>';
			}
			if ($family_history['PatientFamilyHistory']['problem'])
			{
				echo 'Problem: '.$family_history['PatientFamilyHistory']['problem'].'<br>';
			}
			if ($family_history['PatientFamilyHistory']['comment'])
			{
				echo 'Comment: '.$family_history['PatientFamilyHistory']['comment'].'<br>';
			}
			if ($family_history['PatientFamilyHistory']['status'])
			{
				echo 'Status: '.$family_history['PatientFamilyHistory']['status'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[2] == "checked")
	{
	?>
	<div>
	<b>Allergies</b><hr />
		<?php
		$i = 0;
		foreach ($allergies as $allergy):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($allergy['PatientAllergy']['agent'])
			{
				echo 'Agent: '.$allergy['PatientAllergy']['agent'].'<br>';
			}
			if ($allergy['PatientAllergy']['type'])
			{
				echo 'Type: '.$allergy['PatientAllergy']['type'].'<br>';
			}
			for ($count = 1; $count <= $allergy['PatientAllergy']['reaction_count']; ++$count)
			{
				if ($allergy['PatientAllergy']['reaction'.$count])
				{
					echo 'Reaction #'.$count.': '.$allergy['PatientAllergy']['reaction'.$count];
					if ($allergy['PatientAllergy']['severity'.$count])
					{
						echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Severity: '.$allergy['PatientAllergy']['severity'.$count];
					}
					echo '<br>';
				}
			}
			if ($allergy['PatientAllergy']['status'])
			{
				echo 'Status: '.$allergy['PatientAllergy']['status'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[3] == "checked")
	{
	?>
	<div>
	<b>Problem List</b><hr />
		<?php
		$i = 0;
		foreach ($problem_lists as $problem_list):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($problem_list['PatientProblemList']['diagnosis'])
			{
				echo 'Diagnosis: '.$problem_list['PatientProblemList']['diagnosis'].'<br>';
			}
			if ($problem_list['PatientProblemList']['status'])
			{
				echo 'Status: '.$problem_list['PatientProblemList']['status'].'<br>';
			}
			if ($problem_list['PatientProblemList']['start_date'])
			{
				echo 'Start Date: '.$problem_list['PatientProblemList']['start_date'].'<br>';
			}
			if ($problem_list['PatientProblemList']['status'] != 'Active')
			{
				if ($problem_list['PatientProblemList']['end_date'])
				{
					echo 'End Date: '.$problem_list['PatientProblemList']['end_date'].'<br>';
				}
			}
			if ($problem_list['PatientProblemList']['occurrence'])
			{
				echo 'Occurrence: '.$problem_list['PatientProblemList']['occurrence'].'<br>';
			}
			if ($problem_list['PatientProblemList']['comment'])
			{
				echo 'Comment: '.$problem_list['PatientProblemList']['comment'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[4] == "checked")
	{
	?>
	<div>
	<b>Labs</b><hr />
		<?php
		$i = 0;
		foreach ($lab_results as $lab_result):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($lab_result['EncounterPointOfCare']['lab_test_name'])
			{
				echo 'Test Name: '.$lab_result['EncounterPointOfCare']['lab_test_name'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['lab_priority'])
			{
				echo 'Priority: '.$lab_result['EncounterPointOfCare']['lab_priority'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['lab_specimen'])
			{
				echo 'Specimen: '.$lab_result['EncounterPointOfCare']['lab_specimen'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['lab_date_performed'])
			{
				echo 'Date Performed: '.$lab_result['EncounterPointOfCare']['lab_date_performed'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['lab_test_result'])
			{
				echo 'Test Result: '.$lab_result['EncounterPointOfCare']['lab_test_result'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['lab_unit'])
			{
				echo 'Unit: '.$lab_result['EncounterPointOfCare']['lab_unit'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['lab_normal_range'])
			{
				echo 'Normal Range: '.$lab_result['EncounterPointOfCare']['lab_normal_range'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['lab_abnormal'])
			{
				echo 'Abnormal: '.$lab_result['EncounterPointOfCare']['lab_abnormal'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['lab_test_result_status'])
			{
				echo 'Test Result Status: '.$lab_result['EncounterPointOfCare']['lab_test_result_status'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['lab_comment'])
			{
				echo 'Comment: '.$lab_result['EncounterPointOfCare']['lab_comment'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['cpt'])
			{
				echo 'CPT: '.$lab_result['EncounterPointOfCare']['cpt'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['comment'])
			{
				echo 'Comment: '.$lab_result['EncounterPointOfCare']['comment'].'<br>';
			}
			if ($lab_result['OrderBy']['firstname'] and $lab_result['OrderBy']['lastname'])
			{
				echo 'Ordered by: '.$lab_result['OrderBy']['firstname'].' '.$lab_result['OrderBy']['lastname'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['date_ordered'])
			{
				echo 'Date Ordered: '.$lab_result['EncounterPointOfCare']['date_ordered'].'<br>';
			}
			if ($lab_result['EncounterPointOfCare']['status'])
			{
				echo 'Status: '.$lab_result['EncounterPointOfCare']['status'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[5] == "checked")
	{
	?>
	<div>
	<b>Radiology</b><hr />
		<?php
		$i = 0;
		foreach ($radiology_results as $radiology_result):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($radiology_result['EncounterPointOfCare']['radiology_procedure_name'])
			{
				echo 'Procedure Name: '.$radiology_result['EncounterPointOfCare']['radiology_procedure_name'].'<br>';
			}
			if ($radiology_result['EncounterPointOfCare']['radiology_body_site'])
			{
				echo 'Body Site: '.$radiology_result['EncounterPointOfCare']['radiology_body_site'].'<br>';
			}
			if ($radiology_result['EncounterPointOfCare']['radiology_laterality'])
			{
				echo 'Laterality: '.$radiology_result['EncounterPointOfCare']['radiology_laterality'].'<br>';
			}
			if ($radiology_result['EncounterPointOfCare']['radiology_date_performed'])
			{
				echo 'Date Performed: '.$radiology_result['EncounterPointOfCare']['radiology_date_performed'].'<br>';
			}
			if ($radiology_result['EncounterPointOfCare']['radiology_test_result'])
			{
				echo 'Test Result: '.$radiology_result['EncounterPointOfCare']['radiology_test_result'].'<br>';
			}
			if ($radiology_result['EncounterPointOfCare']['radiology_comment'])
			{
				echo 'Comment: '.$radiology_result['EncounterPointOfCare']['radiology_comment'].'<br>';
			}
			if ($radiology_result['EncounterPointOfCare']['cpt'])
			{
				echo 'CPT: '.$radiology_result['EncounterPointOfCare']['cpt'].'<br>';
			}
			if ($radiology_result['EncounterPointOfCare']['comment'])
			{
				echo 'Comment: '.$radiology_result['EncounterPointOfCare']['comment'].'<br>';
			}
			if ($radiology_result['OrderBy']['firstname'] and $radiology_result['OrderBy']['lastname'])
			{
				echo 'Ordered by: '.$radiology_result['OrderBy']['firstname'].' '.$radiology_result['OrderBy']['lastname'].'<br>';
			}
			if ($radiology_result['EncounterPointOfCare']['date_ordered'])
			{
				echo 'Date Ordered: '.$radiology_result['EncounterPointOfCare']['date_ordered'].'<br>';
			}
			if ($radiology_result['EncounterPointOfCare']['status'])
			{
				echo 'Status: '.$radiology_result['EncounterPointOfCare']['status'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[6] == "checked")
	{
	?>
	<div>
	<b>Procedures</b><hr />
		<?php
		$i = 0;
		foreach ($plan_procedures as $plan_procedure):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($plan_procedure['EncounterPointOfCare']['procedure_name'])
			{
				echo 'Procedure Name: '.$plan_procedure['EncounterPointOfCare']['procedure_name'].'<br>';
			}
			if ($plan_procedure['EncounterPointOfCare']['procedure_details'])
			{
				echo 'Details: '.$plan_procedure['EncounterPointOfCare']['procedure_details'].'<br>';
			}
			if ($plan_procedure['EncounterPointOfCare']['procedure_body_site'])
			{
				echo 'Body Site: '.$plan_procedure['EncounterPointOfCare']['procedure_body_site'].'<br>';
			}
			if ($plan_procedure['EncounterPointOfCare']['procedure_date_performed'])
			{
				echo 'Date Performed: '.$plan_procedure['EncounterPointOfCare']['procedure_date_performed'].'<br>';
			}
			if ($plan_procedure['EncounterPointOfCare']['procedure_comment'])
			{
				echo 'Comment: '.$plan_procedure['EncounterPointOfCare']['procedure_comment'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[7] == "checked")
	{
	?>
	<div>
	<b>Immunizations</b><hr />
		<?php
		$i = 0;
		foreach ($immunizations as $immunization):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($immunization['EncounterPointOfCare']['vaccine_name'])
			{
				echo 'Vaccine Name: '.$immunization['EncounterPointOfCare']['vaccine_name'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['vaccine_date_performed'])
			{
				echo 'Date Performed: '.$immunization['EncounterPointOfCare']['vaccine_date_performed'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['vaccine_lot_number'])
			{
				echo 'Lot Number: '.$immunization['EncounterPointOfCare']['vaccine_lot_number'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['vaccine_manufacturer'])
			{
				echo 'Manufacturer: '.$immunization['EncounterPointOfCare']['vaccine_manufacturer'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['vaccine_dose'])
			{
				echo 'Dose: '.$immunization['EncounterPointOfCare']['vaccine_dose'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['vaccine_body_site'])
			{
				echo 'Body Site: '.$immunization['EncounterPointOfCare']['vaccine_body_site'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['vaccine_route'])
			{
				echo 'Route: '.$immunization['EncounterPointOfCare']['vaccine_route'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['vaccine_expiration_date'])
			{
				echo 'Expiration Date: '.$immunization['EncounterPointOfCare']['vaccine_expiration_date'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['vaccine_administered_by'])
			{
				echo 'Administered by: '.$immunization['EncounterPointOfCare']['vaccine_administered_by'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['vaccine_time'])
			{
				echo 'Time: '.$immunization['EncounterPointOfCare']['vaccine_time'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['vaccine_comment'])
			{
				echo 'Comment: '.$immunization['EncounterPointOfCare']['vaccine_comment'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['cpt'])
			{
				echo 'CPT: '.$immunization['EncounterPointOfCare']['cpt'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['comment'])
			{
				echo 'Comment: '.$immunization['EncounterPointOfCare']['comment'].'<br>';
			}
			if ($immunization['OrderBy']['firstname'] and $immunization['OrderBy']['lastname'])
			{
				echo 'Ordered by: '.$immunization['OrderBy']['firstname'].' '.$immunization['OrderBy']['lastname'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['date_ordered'])
			{
				echo 'Date Ordered: '.$immunization['EncounterPointOfCare']['date_ordered'].'<br>';
			}
			if ($immunization['EncounterPointOfCare']['status'])
			{
				echo 'Status: '.$immunization['EncounterPointOfCare']['status'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[8] == "checked")
	{
	?>
	<div>
	<b>Injections</b><hr />
		<?php
		$i = 0;
		foreach ($injections as $injection):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($injection['EncounterPointOfCare']['injection_name'])
			{
				echo 'Injection: '.$injection['EncounterPointOfCare']['injection_name'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['injection_date_performed'])
			{
				echo 'Date Performed: '.$injection['EncounterPointOfCare']['injection_date_performed'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['injection_lot_number'])
			{
				echo 'Lot Number: '.$injection['EncounterPointOfCare']['injection_lot_number'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['injection_manufacturer'])
			{
				echo 'Manufacturer: '.$injection['EncounterPointOfCare']['injection_manufacturer'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['injection_dose'])
			{
				echo 'Dose: '.$injection['EncounterPointOfCare']['injection_dose'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['injection_body_site'])
			{
				echo 'Body Site: '.$injection['EncounterPointOfCare']['injection_body_site'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['drug_route'])
			{
				echo 'Route: '.$injection['EncounterPointOfCare']['drug_route'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['injection_expiration_date'])
			{
				echo 'Expiration Date: '.$injection['EncounterPointOfCare']['injection_expiration_date'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['injection_administered_by'])
			{
				echo 'Administered by: '.$injection['EncounterPointOfCare']['injection_administered_by'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['injection_time'])
			{
				echo 'Time: '.$injection['EncounterPointOfCare']['injection_time'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['injection_comment'])
			{
				echo 'Comment: '.$injection['EncounterPointOfCare']['injection_comment'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['cpt'])
			{
				echo 'CPT: '.$injection['EncounterPointOfCare']['cpt'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['comment'])
			{
				echo 'Comment: '.$injection['EncounterPointOfCare']['comment'].'<br>';
			}
			if ($injection['OrderBy']['firstname'] and $injection['OrderBy']['lastname'])
			{
				echo 'Ordered by: '.$injection['OrderBy']['firstname'].' '.$injection['OrderBy']['lastname'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['date_ordered'])
			{
				echo 'Date Ordered: '.$injection['EncounterPointOfCare']['date_ordered'].'<br>';
			}
			if ($injection['EncounterPointOfCare']['status'])
			{
				echo 'Status: '.$injection['EncounterPointOfCare']['status'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[9] == "checked")
	{
	?>
	<div>
	<b>Medication List</b><hr />
		<?php
		$i = 0;
		foreach ($medication_lists as $medication_list):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($medication_list['PatientMedicationList']['medication'])
			{
				echo 'Medication: '.$medication_list['PatientMedicationList']['medication'].'<br>';
			}
			if ($medication_list['PatientMedicationList']['diagnosis'])
			{
				echo 'Diagnosis: '.$medication_list['PatientMedicationList']['diagnosis'].'<br>';
			}
			if ($medication_list['PatientMedicationList']['frequency'])
			{
				echo 'Frequency: '.$medication_list['PatientMedicationList']['frequency'].'<br>';
			}
			if ($medication_list['PatientMedicationList']['taking'])
			{
				echo 'Taking?: '.$medication_list['PatientMedicationList']['taking'].'<br>';
			}
			if ($medication_list['PatientMedicationList']['start_date'])
			{
				echo 'Start Date: '.$medication_list['PatientMedicationList']['start_date'].'<br>';
			}
			if ($medication_list['PatientMedicationList']['end_date'])
			{
				echo 'End Date: '.$medication_list['PatientMedicationList']['end_date'].'<br>';
			}
			if ($medication_list['PatientMedicationList']['long_term'])
			{
				echo 'Long Term?: '.$medication_list['PatientMedicationList']['long_term'].'<br>';
			}
			if ($medication_list['PatientMedicationList']['source'])
			{
				echo 'Source: '.$medication_list['PatientMedicationList']['source'].'<br>';
			}
			if ($medication_list['PatientMedicationList']['provider'])
			{
				echo 'Provider: '.$medication_list['PatientMedicationList']['provider'].'<br>';
			}
			if ($medication_list['PatientMedicationList']['status'])
			{
				echo 'Status: '.$medication_list['PatientMedicationList']['status'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[10] == "checked")
	{
	?>
	<div>
	<b>Referrals</b><hr />
		<?php
		$i = 0;
		foreach ($plan_referrals as $plan_referral):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($plan_referral['EncounterPlanReferral']['referred_to'])
			{
				echo 'Referred To: '.$plan_referral['EncounterPlanReferral']['referred_to'].'<br>';
			}
			if ($plan_referral['EncounterPlanReferral']['specialties'])
			{
				echo 'Specialties: '.$plan_referral['EncounterPlanReferral']['specialties'].'<br>';
			}
			if ($plan_referral['EncounterPlanReferral']['practice_name'])
			{
				echo 'Practice Name: '.$plan_referral['EncounterPlanReferral']['practice_name'].'<br>';
			}
			if ($plan_referral['EncounterPlanReferral']['reason'])
			{
				echo 'Reason: '.$plan_referral['EncounterPlanReferral']['reason'].'<br>';
			}
			if ($plan_referral['EncounterPlanReferral']['diagnosis'])
			{
				echo 'Diagnosis: '.$plan_referral['EncounterPlanReferral']['diagnosis'].'<br>';
			}
			if ($plan_referral['EncounterPlanReferral']['referred_by'])
			{
				echo 'Referred By: '.$plan_referral['EncounterPlanReferral']['referred_by'].'<br>';
			}
			if ($plan_referral['EncounterPlanReferral']['status'])
			{
				echo 'Status: '.$plan_referral['EncounterPlanReferral']['status'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}

	if ($patient_disclosure[11] == "checked" )
	{
	?>
	<div>
	<b>Health Maintenance</b><hr />
		<?php
		$i = 0;
		foreach ($plan_health_maintenance as $plan_health_maintenance):
			++$i;
			if ($i > 1)
			{
				echo "<br>";
			}
			if ($plan_health_maintenance['EncounterPlanHealthMaintenance']['plan_name'])
			{
				echo 'Plan Name: '.$plan_health_maintenance['EncounterPlanHealthMaintenance']['plan_name'].'<br>';
			}
			if ($plan_health_maintenance['EncounterPlanHealthMaintenance']['action_date'])
			{
				echo 'Action Date: '.$plan_health_maintenance['EncounterPlanHealthMaintenance']['action_date'].'<br>';
			}
			if ($plan_health_maintenance['EncounterPlanHealthMaintenance']['action_completed'])
			{
				echo 'Action Completed: '.$plan_health_maintenance['EncounterPlanHealthMaintenance']['action_completed'].'<br>';
			}
			if ($plan_health_maintenance['EncounterPlanHealthMaintenance']['signup_date'])
			{
				echo 'Signup Date: '.$plan_health_maintenance['EncounterPlanHealthMaintenance']['signup_date'].'<br>';
			}
			if ($plan_health_maintenance['EncounterPlanHealthMaintenance']['status'])
			{
				echo 'Status: '.$plan_health_maintenance['EncounterPlanHealthMaintenance']['status'].'<br>';
			}
		endforeach;
		?>
	</div><br>
	<?php
	}
	?>
	<?php if ($patient_disclosure[12] == "checked"): ?> 
	<div>
	<b>Insurance Information</b><hr />	
	
	<?php if (!empty($patient_insurance)): ?> 	
			<?php foreach($patient_insurance as $p): ?>
				Payer Name: <?php echo htmlentities($p['PatientInsurance']['payer']); ?> <br />
				Priority: <?php echo $p['PatientInsurance']['priority']; ?> <br />
				Member/Policy Number: <?php echo $p['PatientInsurance']['policy_number']; ?><br />
				Group Number: <?php echo $p['PatientInsurance']['group_id']. ' ' . $p['PatientInsurance']['group_name']; ?> <br />
				Type:  <?php echo $p['PatientInsurance']['type']; ?><br />
				Insured: 
					<?php  
						echo (isset($p['PatientInsurance']['insured_first_name'])) ? $p['PatientInsurance']['insured_first_name']. ' ' :''; 
						echo (isset($p['PatientInsurance']['insured_middle_name'])) ? $p['PatientInsurance']['insured_middle_name']. ' ':'';
						echo (isset($p['PatientInsurance']['insured_last_name'])) ? $p['PatientInsurance']['insured_last_name']. ', ':'';
						echo (isset($p['PatientInsurance']['insured_address_1'])) ? $p['PatientInsurance']['insured_address_1']. ', ':'';
						echo (isset($p['PatientInsurance']['insured_address_2'])) ? $p['PatientInsurance']['insured_address_2']. ', ':'';
						echo (isset($p['PatientInsurance']['insured_city'])) ? $p['PatientInsurance']['insured_city']. ', ':'';
						echo (isset($p['PatientInsurance']['insured_state'])) ? $p['PatientInsurance']['insured_state']. ' ':'';
						echo (isset($p['PatientInsurance']['insured_zip'])) ? $p['PatientInsurance']['insured_zip']. ' ':'';
						echo (isset($p['PatientInsurance']['insured_home_phone_number'])) ? $p['PatientInsurance']['insured_home_phone_number']. ' ':'';
					?>
			<?php endforeach;?> 
	<?php endif;?> 
		
	</div><br/>
	<?php endif;?> 
	<hr />
	<div class='footer'>
		   (office details)
	</div>
	<hr />
	<div class='footer_patient'>Report generated by: One Touch EMR Software (www.onetouchemr.com)</div>

</body>
</html>
