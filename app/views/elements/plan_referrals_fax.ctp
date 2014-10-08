<?php
$fullname = $demographics->first_name . ' ' . $demographics->last_name;
if($demographics->address2) $addr2='<br>'. $demographics->address2; else $addr2="";
$Demograph = $demographics->address1 . 
		  $addr2 .
		'<br>' . $demographics->city . ', '. 
		$demographics->state . ' ' .
		$zip=$demographics->zipcode ;
if($demographics->home_phone && $demographics->home_phone != '000-000-0000') $Demograph .='<br>'.$ph=$demographics->home_phone. ' (home)';
if($demographics->cell_phone) $Demograph .= ' ' . $demographics->cell_phone.' (cell)';

if ($demographics->gender == 'M') {
    $gendr = 'Male';
    $prep = 'his';
} else if ($demographics->gender == 'F') {
    $gendr = 'Female';
    $prep = 'her';
} else {
    $gendr = '';
    $prep = '';
}

$dob = __date("m/d/Y", strtotime($demographics->dob));


$noSummary = true;

foreach ($info as $x) {
    if ($x) {
        $noSummary = false;
        break;
    }
}


?>
<html>
    <head>
        <title>Plan Referral</title>
        <style>
                   body,table {
                           font-family: "Helvetica Neue", "Lucida Grande", Helvetica, Arial, Verdana, sans-serif;
                   font-size: 13px;
                   color: #000;
                   
                   }            
		  .hide_for_print {
			display: none;
		  }
                  
                  a {
                      color: black;
                      text-decoration: none;
                  }

                  .hide_for_referral {
                      display: none;
                  }
                  .lrg {
		font-size: 20px; font-weight:bold; font-variant: small-caps; text-transform: none;
	}
        </style>
        
    </head>
    <body <?php if( isset($print_referrals) ) {
	echo $print_referrals = 'onload="window.print();"';
}?>>
		<div class="hide_for_referral" style="display:block;">
			<hr>
			<table cellpadding=0 cellspacing=0 width=100%><tr><td width=25%  style="text-align:left;vertical-align:top;">
				<?php
				echo '<div class=lrg> '.$demographics->first_name.' '.$demographics->last_name.'</div>'; 
				print 'DOB: '.$dob.' <br />';
				$demographics->custom_patient_identifier? printf("ID: %s, ", $demographics->custom_patient_identifier ) : '';
				print 'MRN: '.$demographics->mrn. '<br />';
				$demographics->address1? printf("%s <br>", $demographics->address1 ) : '<br>';
				$demographics->address2? printf("%s <br />", $demographics->address2 ) : '<br>';

				$demographics->city? printf("%s, ", $demographics->city ) : '';
				$demographics->state? printf("%s, ", $demographics->state ) : '';
				$demographics->zipcode? printf("%s <br />", $demographics->zipcode ) : '<br />';
				
		$corp_logo = $url_abs_paths['administration'].'/'.$provider->logo_image;
		$corp_logo = ltrim($corp_logo, '/');
		if(is_file($corp_logo))
		{
			print '</td><td width=40% ><center><img src="'.Router::url("/", true).$corp_logo.'"></center></td>';
		}
		else
		{
			print '</td><td width=40%>&nbsp;</td>';
		}

				?>
				   </td><td style="text-align:right;vertical-align:top;" width="35%"><?php
				echo empty($provider->practice_name)?'': '<span class=lrg>'.ucwords($provider->practice_name). '</span><br>';
				if (!empty($provider->type_of_practice) && $provider->type_of_practice != 'Other') echo ucwords($provider->type_of_practice). '<br>';
				//echo empty($provider->description)?'': ucfirst($provider->description). '<br>';
							$location = $report->location;
							echo htmlentities($location['location_name']), '<br />';
							
							$fullAddress = '';
							
							$fullAddress = htmlentities($location['address_line_1']) . '<br />';
							
							$addr2 = (isset($location['address_line_2'])) ? trim($location['address_line_2']) : '';
							
							if ($addr2) {
								$fullAddress .= $addr2 . '<br />';
							}
							
							$fullAddress .= htmlentities($location['city']) .', ' . htmlentities($location['state']) . ' ' . $location['zip'];
			                        $fullAddress .= (isset($location['phone'])) ? '<br>'.trim($location['phone']). ' ' : '';
                        $fullAddress .= (isset($location['fax'])) ? 'Fax: '.trim($location['fax']) : '';				
                        echo $fullAddress;                       
			?>
					</td>
				</tr>
			</table>
			<hr>
		</div>
        
        <p><strong><?php echo htmlentities($referral->referred_to); ?>,</strong></p>
        
        <p>
<?php if ($referral->refer_type == 'referred_to') { ?>
I am referring the following patient to you.
<?php } else { ?>
Thank you for your referral. Your patient has been seen and my findings are below.
<?php } ?> 
        </p>
        
        <p style="margin-left: 20px;">
                <?php
		     if(!empty($referral->diagnosis)) {
			echo 'Diagnosis: <em>', htmlentities($referral->diagnosis) , '</em><br />';
		     }
                 ?> 
                <u>Patient name</u>: <em><?php echo $fullname ?></em><br />
                <u>DOB</u>: <?php echo $dob; ?>,  <?php echo $gendr ?> <br />
                  <?php if($Demograph) { echo '<u>Contact Information</u>: <br />'.$Demograph;}
		   $reason = trim($referral->reason);
                    if ($reason) {
                        echo '<p>Comments/Reason: <div style="margin-left:10px;">', nl2br(htmlentities($reason)). '</div>';
                    }
                ?>             
        </p>
<?php if (!empty($referral->referred_by)): ?>        
        <p>
            Sincerely,
            <br />
            <strong><?php echo htmlentities($referral->referred_by); ?></strong>
        </p>
<?php endif; ?>        
        <br />

        
	<?php if (!$noSummary): ?> 
            <table align="left" width="100%" style="margin-bottom: 20px;">
            <?php 
			if(isset($insurance_data))
			{
			?>
			<h4>Patient Insurance</h4>
			<?
			 if(sizeof($insurance_data) > 0):
			 foreach($insurance_data as $insurance_data1)
			 { 
			?> 
				<tr> 
					<td width='120'>Payer:</td><td> <?php if(!empty($insurance_data1['PatientInsurance']['payer'])) echo $insurance_data1['PatientInsurance']['payer']; echo (!empty($insurance_data1['PatientInsurance']['priority']))? ' ('.$insurance_data1['PatientInsurance']['priority'].')':'';  ?></td> 
				</tr> 
				<tr valign="top"> 
					<td>Insured Policy Holder:</td><td><?php if(!empty($insurance_data1['PatientInsurance']['insured_first_name'])){ echo $insurance_data1['PatientInsurance']['insured_first_name']." ".$insurance_data1['PatientInsurance']['insured_last_name']."<br>"; 
					echo $insurance_data1['PatientInsurance']['insured_address_1']." ".$insurance_data1['PatientInsurance']['insured_address_2']."<br>"; 
					echo $insurance_data1['PatientInsurance']['insured_city']." ".$insurance_data1['PatientInsurance']['insured_state'].",".$insurance_data1['PatientInsurance']['insured_zip']; 
					echo ' '.$insurance_data1['PatientInsurance']['insured_home_phone_number']." (home) / ".$insurance_data1['PatientInsurance']['insured_work_phone_number']. ' (work)';} 
					 
					?> </td> 
				</tr> 
				<tr> 
					<td>Member/Policy #:</td><td> <?php if(!empty($insurance_data1['PatientInsurance']['policy_number'])) echo $insurance_data1['PatientInsurance']['policy_number'];?></td> 
				</tr> 
				<tr> 
					<td>Group Name/Number:</td><td> <?php  if(!empty($insurance_data['group_name']) || !empty($insurance_data1['PatientInsurance']['group_id'])) { echo $insurance_data1['PatientInsurance']['group_name']." / ".$insurance_data1['PatientInsurance']['group_id'];}?></td> 
				</tr>
				<tr><td>&nbsp;</td></tr>
			<?php
			}
			else: 
			  print '<tr><td>No Insurance on file</td></tr>';
			endif; 
		     }
			?>
            </table>
<span style="font-size:1.1em;font-weight:bold;"><em>Attached below is a summary of information relevant to the patient</em></span><hr>
<br />
<br />
		<?php echo $summary; ?>
			<?php endif; ?>
    </body>
</html>    
