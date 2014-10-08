<html>
<head>
	   <title>Medication List Report</title>
	   <style>
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
        	margin-top: 4px;
        	margin-bottom: 1px;
        	margin-left: 0px;
       }
  
	
	.lrg {
		font-size: 25px; font-weight:bold; font-variant: small-caps; text-transform: none;
	}
	   body,table {
			   font-family: "Helvetica Neue", "Lucida Grande", Helvetica, Arial, Verdana, sans-serif;
	   font-size: 14px;
	   color: #000;
	   }
		@media print{
		  .hide_for_print {
			display: none;
		  }
		}
		
 .btn, a.btn {
			color: #464646;
			cursor: pointer;
			padding: 5px 6px;
			margin-right: 5px;
			text-decoration: none;
			font-weight: bold;
			-moz-border-radius: 4px;
			-webkit-border-radius: 4px;
			border-radius: 4px;
			border: 1px solid #ddd;
			background: -moz-linear-gradient(center top, #fefefe, #eee) repeat scroll 0 0 transparent;
			background: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#eee));
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fefefe', endColorstr='#eeeeee');
		}
		.reportborder { background-color:#E0F2F7; padding:7px 0px 7px 5px; font-weight:bold;}		
	   </style>
</head>
<body>
<div class="hide_for_print reportborder" style="padding-bottom:10px;">
	<a class=btn href="javascript:window.print()">Print <img src="<?php echo Router::url("/", true).'img/printer_icon_small.png'; ?>"  style="vertical-align:bottom;padding-left:3px"></a>
	<!--<span style="margin-left:20px"><a class="btn" href="<?php echo $html->url(array('action' => 'medication_list', 'patient_id' => $demographic['PatientDemographic']['patient_id'], 'task' => 'get_report_ccr', 'show_all_medications' =>$show_all_medications)); ?>" target="_blank">CCR <img src="<?php echo Router::url("/", true).'img/exchange_icon.png'; ?>" style="vertical-align:bottom;padding-left:3px"></a></span>-->
	<span style="margin-left:20px"><a class="btn" href="<?php echo $html->url(array('action' => 'medication_list', 'patient_id' => $demographic['PatientDemographic']['patient_id'],  'task' => 'get_report_pdf', 'show_all_medications' =>$show_all_medications)); ?>" target="_blank">PDF <img src="<?php echo Router::url("/", true).'img/pdf.png'; ?>" style="vertical-align:bottom;padding-left:3px"></a></span>
	<span style="margin-left:20px; width:100px"><a class="btn" href="<?php echo $html->url(array('action' => 'medication_list', 'patient_id' => $demographic['PatientDemographic']['patient_id'],  'task' => 'get_report_pdf', 'show_all_medications' =>$show_all_medications , 'view'=>'fax')); ?>" target="_blank">Fax</a></span>
</div>
<hr />
	<div>
		<?php
		echo '<div class=lrg>Patient: '.$demographic['PatientDemographic']['first_name'].' '.$demographic['PatientDemographic']['last_name'].'  <br>DOB: ' . __date("m/d/Y", strtotime($demographic['PatientDemographic']['dob'])). '</div>';
		echo 'MRN: '.$demographic['PatientDemographic']['mrn'].'<br>';
		?>
	</div><br>

    <div>
	<b>Medication List</b><hr />
	<ol>
		<?php
		$i = 0;
		foreach ($medication_lists as $medication_list):
			++$i;
			if ($i > 1)
			{
				//echo "<br>";
			}
			if ($medication_list['PatientMedicationList']['medication'])
			{

				if ($medication_list['PatientMedicationList']['status'])
				{
				$status = '['.$medication_list['PatientMedicationList']['status'].']';
				}
				else
				{
				$status="";
				}

				echo '<li> '.$status.' '.$medication_list['PatientMedicationList']['medication'];


				if ($medication_list['PatientMedicationList']['quantity'])
				{
				echo ' ' . $medication_list['PatientMedicationList']['quantity'];
				}
				if ($medication_list['PatientMedicationList']['unit'])
				{
				echo ' ' . $medication_list['PatientMedicationList']['unit'];
				}
				if ($medication_list['PatientMedicationList']['route'])
				{
				echo ' ' . $medication_list['PatientMedicationList']['route'];
				}
				if ($medication_list['PatientMedicationList']['frequency'])
				{
				echo ' ' . $medication_list['PatientMedicationList']['frequency'];
				}
				if ($medication_list['PatientMedicationList']['direction'])
				{
				echo ' ' . $medication_list['PatientMedicationList']['direction'];
				}				
				if ($medication_list['PatientMedicationList']['diagnosis'])
				{
				echo ' for ' . $medication_list['PatientMedicationList']['diagnosis'];
				}



				if ($medication_list['PatientMedicationList']['taking'])
				{
				echo '<br> <i>taking?:</i> '.$medication_list['PatientMedicationList']['taking'];
				}
				if ($medication_list['PatientMedicationList']['start_date'] && $medication_list['PatientMedicationList']['start_date'] != '0000-00-00')
				{
				echo '<br><i>start date:</i> '.$medication_list['PatientMedicationList']['start_date'];
				}
				if ($medication_list['PatientMedicationList']['end_date']  && $medication_list['PatientMedicationList']['end_date'] != '0000-00-00')
				{
				echo '<br><i>end date:</i> '.$medication_list['PatientMedicationList']['end_date'];
				}
				if ($medication_list['PatientMedicationList']['long_term'])
				{
				echo '<br><i>long term?:</i> '.$medication_list['PatientMedicationList']['long_term'];
				}
				if ($medication_list['PatientMedicationList']['source'])
				{
				echo '<br><i>source:</i> '.$medication_list['PatientMedicationList']['source'];
				}
				if ($medication_list['PatientMedicationList']['provider'])
				{
				echo '<br><i>provider:</i> '.$medication_list['PatientMedicationList']['provider'];
				}

			}
		endforeach;
		?>
	</ol>
	</div>

	<hr />
	<div class='footer_patient'>Report generated by: One Touch EMR Software (www.onetouchemr.com)</div>

</body>
</html>
