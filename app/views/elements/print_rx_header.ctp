<html>
<head>
       <title>Prescription</title>
       
       <style>
       
       h1 {
               font-family:Georgia,serif;
               color:#000;
               font-variant: small-caps; text-transform: none; font-weight: bold;
               margin-top: 5px; margin-bottom: -5px;
       }

       h3 {
               font-family: times, Times New Roman, times-roman, 	, serif;
               margin-top: 5px; margin-bottom: 2px;
               letter-spacing: -1px;color: #000;
       }
       ol {
        	margin-top: 3px;
        	margin-bottom: 1px;
        	margin-left: 0px;
       }
      
	.lrg {
		font-size: 20px; font-weight:bold; font-variant: small-caps; text-transform: none;
	}
	.reportborder { background-color:#E0F2F7; padding:7px 0px 7px 5px; font-weight:bold;}
	
   body,table {
   font-family: "Helvetica Neue", "Lucida Grande", Helvetica, Arial, Verdana, sans-serif;
   font-size: 12px;
   color: #000;
   }
   .content1 {
        border: 5px solid black;height:0px;width:67%
   }
</style>     
</head>
<body onLoad="window.print();">
<div>
  <table cellpadding="0" cellspacing="0" width="67%">
    <tr>
      <td width="25%" style="vertical-align:bottom;"><?php if ($provider['UserAccount']['license_number']) echo '<b>License #:</b> '.$provider['UserAccount']['license_number'] . ' ' . $provider['UserAccount']['license_state']; ?></td>
      <td width="50%" align="center">
	    <div class="lrg"><?php echo $practice_profile['PracticeProfile']['practice_name']; ?></div>
<?php   if(!empty($location['address_line_1'])) {
                $loc='';
                print '<div>';
                        if(!empty($location['address_line_1'])) $loc .= $location['address_line_1'].' ';
                        if(!empty($location['address_line_2'])) $loc .= ', '.$location['address_line_2'].'<br>';
                        if(!empty($location['city'])) $loc .= $location['city'].', ';
                        if(!empty($location['state'])) $loc .= $location['state'].' ';
                        if(!empty($location['zip'])) $loc .= $location['zip'].' <br>';
                        if(!empty($location['phone'])) $loc .= $location['phone'].' ';
                        if(!empty($location['fax'])) $loc .= ' fax: '.$location['fax'].' ';

                print $loc.'</div>';
        }
?>

		<h3>
			<?php 
				echo $provider['UserAccount']['title'] . ' ' .$provider['UserAccount']['firstname'], ' ', $provider['UserAccount']['lastname']; 
				if($provider['UserAccount']['degree']) echo ', ', $provider['UserAccount']['degree'];
			?>
		</h3>
		<?php echo ($practice_profile['PracticeProfile']['type_of_practice'] != 'Other')?$practice_profile['PracticeProfile']['type_of_practice']:''; ?>
	  </td>      
      <td width="25%" style="vertical-align:bottom;">
	    	<b>DEA #:</b> <?php echo $provider['UserAccount']['dea']; ?><br>
        	<b>NPI#:</b> <?php echo $provider['UserAccount']['npi']; ?><br>
      </td>
    </tr>
  </table>
  
</div>
<div class="content1"></div>
<table border="0" width="67%">
  <tr>
    <td width="40%"><h3>Name: <?php echo $encounter['PatientDemographic']['patientName']; ?> &nbsp; (<?php echo ($encounter['PatientDemographic']['gender']=='M') ? 'Male':'Female'; ?>)</h3></td>
    <td width="25%"><h3>DOB: <?php if($encounter['PatientDemographic']['dob'] && $encounter['PatientDemographic']['dob'] != '0000-00-00') echo __date($global_date_format, strtotime($encounter['PatientDemographic']['dob'])); ?></h3>  </td>
    <td><b>Rx Date:</b> <?php echo __date($global_date_format); ?></td>
  </tr>
  <tr>
    <td width="45%" style="vertical-align:top">
		<b>Address:</b> 
		<?php 
			echo $encounter['PatientDemographic']['address1'], ', ', $encounter['PatientDemographic']['city'], ', ', $encounter['PatientDemographic']['state'], ', ', $encounter['PatientDemographic']['zipcode']; 
		?>
	</td>
    <td style="vertical-align:top" colspan="2"><?php if($encounter['PatientDemographic']['home_phone']) echo '<b>Phone:</b> '.$encounter['PatientDemographic']['home_phone'];?></td>
  </tr>
  <tr>
    <td colspan="3" style="padding:20px"><h2>
