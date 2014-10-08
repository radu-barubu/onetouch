<html>
<head>
	<title>Plan Summary Report</title>
	<style>
		h1 {
			font-family:Georgia,serif;
			color:#000;
			font-variant: small-caps; text-transform: none; font-weight: bold;
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
		ol {
			margin-top: 3px;
			margin-bottom: 1px;
			margin-left: 0px;
		}
		/*
		li {
			margin: 1px 0px 1px 0px;
		}
		*/
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
	</style>
	<?php
		$scriptArray = array('/js/jquery/jquery.js');
		if(isset($isiPadApp)&&$isiPadApp)
			$scriptArray[] = '/js/iPad/jquery.ipadapp.js';
		echo $this->Html->script($scriptArray);
	?>       
</head>
<?php

//print_r($provider);
//print_r($user);
//print_r($demographics);

/* 
* 	how will this note be formatted? Doctor preference brought in
*
if($user->new_pt_note == '' || $user->est_pt_note == '') {
 $dofull=true; $dosoap=false;
} else {
 $dosoap=true; $dofull=false;
}
*/
$dofull=true; $dosoap=false;

$fullname = $demographics->first_name.' '.$demographics->last_name;

if ($demographics->gender == 'M')
{
  $gendr='Male'; $prep='his';
}
 else if ($demographics->gender == 'F')
{
  $gendr='Female'; $prep='her';
}
else
{
  $gendr = ''; $prep='';
}

$dob = __date("m/d/Y", strtotime($demographics->dob));
$visit_date = __date("m/d/Y H:m", strtotime($provider->date));

function formVitals($val) {
 return str_replace(', @ 00:00:00','',
 str_replace('Position:','',
 str_replace('Exact Time:','@',
 str_replace('Location:','',
 str_replace('Description:','',
 str_replace('Source:','',$val))))));
}
?>
<body>
       <div>
			<span class="hide_for_print">Print report as: <!--a href="<?php echo $html->url(array('action' => 'plansummary', 'encounter_id' => $demographics->encounter_id, 'task' => 'get_report_ccr')); ?>" target="_blank">CCR</a-->&nbsp;<a href="<?php echo $html->url(array('action' => 'plansummary', 'encounter_id' => $demographics->encounter_id, 'task' => 'get_report_pdf')); ?>" target="_blank">PDF</a></span>

               <hr />
               <span class=lrg><?php echo $demographics->first_name.' '.$demographics->last_name.'</span>'; ?>
			   <table cellpadding=0 cellspacing=0 width=100%><tr><td width=50%>
                <?php
		       print 'DOB: '.$dob.' <br />';
                       $demographics->address1? printf("%s <br>", $demographics->address1 ) : '<br>';
                       $demographics->address2? printf("%s <br />", $demographics->address2 ) : '<br>';

                       $demographics->city? printf("%s, ", $demographics->city ) : '';
                       $demographics->state? printf("%s, ", $demographics->state ) : '';
                       $demographics->zipcode? printf("%s <br />", $demographics->zipcode ) : '<br />';

               ?>
			   </td><td align=right><?php
			echo empty($provider->practice_name)?'': '<span class=lrg>'.ucwords($provider->practice_name). '</span><br>';
			echo empty($provider->type_of_practice)?'': ucfirst($provider->type_of_practice). '<br>';
			//echo empty($provider->description)?'': ucfirst($provider->description). '<br>';
		?>
				</td></tr></table>
               <hr />
       </div>
	<div style="text-align:center"><i>date of service: <?php echo $visit_date;?> </i></div>
		   <hr>
       <h3>Labs</h3>
       <div style='margin-left: 15px'>
			<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
				<tr>
					<th>Diagnosis</th><th width="33%">Test Name</th><th width="10%">Status</th><th width="10%">Date Ordered</th>
				</tr>
					
				<?php
				foreach ($plan as $plan_lab_order_item)
				{
					if(isset($plan_lab_order_item['lab'])){
					?>
								<tr>
									<td><?php echo $plan_lab_order_item['lab'][$plan_lab_order_item['lab']['items'][0]]['diagnosis']; ?></td>
									<td><?php echo $plan_lab_order_item['lab'][$plan_lab_order_item['lab']['items'][0]]['test_name']; ?></td>
									<td><?php echo $plan_lab_order_item['lab'][$plan_lab_order_item['lab']['items'][0]]['status']; ?></td>					
									<td width="10%" ><?php echo __date("m/d/Y", strtotime($plan_lab_order_item['lab'][$plan_lab_order_item['lab']['items'][0]]['date_ordered'])); ?></td>					
								</tr>                      
					<?php
					}
				}
				?>
			</table>
		</div>
	   <hr>		
       <h3>Radiology</h3>
       <div style='margin-left: 15px'>
			<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
				<tr>
					<th>Diagnosis</th><th width="33%">Procedure Name</th><th width="10%">Status</th><th width="10%">Date Ordered</th>
				</tr>
					
				<?php
				foreach ($plan as $patient_radiology_order_items)
				{
					if(isset($patient_radiology_order_items['radiology'])){
					?>
								<tr>
									<td><?php echo $patient_radiology_order_items['radiology'][$patient_radiology_order_items['radiology']['items'][0]]['diagnosis']; ?></td>
									<td><?php echo $patient_radiology_order_items['radiology'][$patient_radiology_order_items['radiology']['items'][0]]['procedure_name']; ?></td>					
									<td><?php echo $patient_radiology_order_items['radiology'][$patient_radiology_order_items['radiology']['items'][0]]['status']; ?></td>					
									<td width="10%" ><?php echo __date("m/d/Y", strtotime($patient_radiology_order_items['radiology'][$patient_radiology_order_items['radiology']['items'][0]]['date_ordered'])); ?></td>					
								</tr>                      
					<?php
					}
				}
				?>
			</table>
		</div>
	   <hr>
       <h3>Procedures</h3>
       <div style='margin-left: 15px'>
			<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
				<tr>
					<th>Diagnosis</th><th width="33%">Test Name</th><th width="10%">Status</th><th width="10%">Date Ordered</th>
				</tr>
					
				<?php
				foreach ($plan as $patient_procedure_order_item)
				{
					if(isset($patient_procedure_order_item['procedure'])){
					?>
						<tr>
							<td><?php echo $patient_procedure_order_item['procedure'][$patient_procedure_order_item['procedure']['items'][0]]['diagnosis']; ?></td>
							<td><?php echo $patient_procedure_order_item['procedure'][$patient_procedure_order_item['procedure']['items'][0]]['test_name']; ?></td>					
							<td><?php echo $patient_procedure_order_item['procedure'][$patient_procedure_order_item['procedure']['items'][0]]['status']; ?></td>					
							<td width="10%" ><?php echo __date("m/d/Y", strtotime($patient_procedure_order_item['procedure'][$patient_procedure_order_item['procedure']['items'][0]]['date_ordered'])); ?></td>					
						</tr>                      
					<?php
					}
				}
				?>
			</table>
		</div>
	   <hr>
       <h3>Rx</h3>
       <div style='margin-left: 15px'>
			<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;" >
				<tr>
					<th>Diagnosis</th><th width="33%">Drug</th><th width="10%">Status</th><th width="10%">Date Ordered</th>
				</tr>
					
				<?php
				foreach ($plan as $patient_rx_order_item)
				{
					if(isset($patient_rx_order_item['rx'])){
					?>
								<tr>
									<td><?php echo $patient_rx_order_item['rx'][$patient_procedure_order_item['rx']['items'][0]]['diagnosis']; ?></td>
									<td><?php echo $patient_rx_order_item['rx'][$patient_procedure_order_item['rx']['items'][0]]['drug']; ?></td>					
									<td><?php echo $patient_rx_order_item['rx'][$patient_procedure_order_item['rx']['items'][0]]['status']; ?></td>					
									<td width="10%" ><?php echo __date("m/d/Y", strtotime($patient_rx_order_item['rx'][$patient_procedure_order_item['rx']['items'][0]]['date_ordered'])); ?></td>					
								</tr>                      
					<?php
					}
				}
				?>
			</table>
		</div>
	   <hr>
       <h3>Referrals</h3>
       <div style='margin-left: 15px'>
			<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
				<tr>
					<th>Diagnosis</th><th width="33%">Test Name</th><th width="10%">Status</th><th width="10%">Date Ordered</th>
				</tr>
					
				<?php
				foreach ($plan as $patient_referral_order_item)
				{
					if(isset($patient_referral_order_item['referral'])){
					?>
								<tr>
									<td><?php echo $patient_referral_order_item['referral'][$patient_referral_order_item['referral']['items'][0]]['diagnosis']; ?></td>
									<td><?php echo $patient_referral_order_item['referral'][$patient_referral_order_item['referral']['items'][0]]['referred_to']; ?></td>					
									<td><?php echo $patient_referral_order_item['referral'][$patient_referral_order_item['referral']['items'][0]]['status']; ?></td>					
									<td width="10%" ><?php echo __date("m/d/Y", strtotime($patient_referral_order_item['referral'][$patient_referral_order_item['referral']['items'][0]]['date_ordered'])); ?></td>					
								</tr>                      
					<?php
					}
				}
				?>
			</table>
		</div>
	   <hr>		
       <h3>Advice/Instructions</h3>
       <div style='margin-left: 15px'>
			<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
				<tr>
					<th>Diagnosis</th><th width="33%">Patient Instruction</th>
				</tr>
					
				<?php
				foreach ($plan as $patient_advice_order_item)
				{
					if(isset($patient_advice_order_item['advice'])){
					?>
								<tr>
									<td><?php echo $patient_advice_order_item['advice']['diagnosis']; ?></td>
									<td><?php echo $patient_advice_order_item['advice']['patient_instruction']; ?></td>					
								</tr>                      
					<?php
					}
				}
				?>
			</table>
		</div>

	<?php
       if($follow = $encounter->followup == 'Yes') {
         echo "<li>Follow Up: in {$encounter->return_period} {$encounter->return_period_type}";
       }
       ?>
       </div></ol>

	<?php
       if($encounter->visit_summary_given == 'Yes') {
         echo "<p><i>A copy of this report was given to the patient.</i></p>";
       } 
	?>
       
<p><b>Signed by Provider: 
<br>
<?php 

/*$docImg=$url_abs_paths['preferences'].$provider->signature_image;
if(file_exists($docImg)) echo '<img src="'.Router::url("/", true).$docImg.'"><br>';*/

echo $user->firstname . ' ' .$user->lastname; 
   if($user->title) echo ', '.$user->title;
?>
</b>       
       <hr />
       <table border=0 width=100%><tr><td width=50%><b>Patient: <?php echo $demographics->first_name.' '.$demographics->last_name;?></b></td><td><b>Date of Service: <?php echo $visit_date; ?></b></td><td align=right><b>DOB: <?php echo $dob; ?></td></tr></table>
	<hr />
       <center>Report generated by: One Touch EMR Software (www.onetouchemr.com) </center>      
</body>
<?php

function mkpretty($value) {
 //$value=strtolower($value);

 if (!strstr($value, ' +') && !strstr($value, ' -') )
 {

  if($value == 'R' or $value == 'right')
    $ret = 'on Right';
  else if ($value == 'L' or $value == 'left')
    $ret = 'on left';
  else 
    $ret = $value;

 } else {
  $ret ='';
 }

return $ret;

}

function formatROS($report, $note_type) {
	if (!empty($report->ROS) ) {
               foreach($report->ROS as $kr => $vr) {
               	   if($note_type == 'full') {
                       echo "<div style='margin-bottom:2px'>";
                       echo "<u>".$kr."</u>: ";
                   } else {
                        echo " <i><u>".strtolower($kr)."</u></i>: ";                  
                   }
                   $Rneg=array(); $Rpos=array();
                   foreach( $vr as $k2r => $v2r) {
                           // ucwords($k2r.' '.$v2r.', ');
                           if($v2r == '-')
                             $Rneg[] = ($k2r);
                           else if ($v2r == '+')
                             $Rpos[] = ($k2r);
                           else
                             continue;

                   }
                   if (sizeof($Rneg) > 0) {
                      print ' <i>denies</i> '. implode(', ', $Rneg);
                      if (sizeof($Rpos) > 0) print ';';

                   }
                   if (sizeof($Rpos) > 0) {
                      print ' <i>positive</i> for '. implode(', ', $Rpos);
                   }
                   
                   if($note_type == 'full') { print '</div>';}
               }
        }
}
?>