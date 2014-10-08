<html>
<head>
<title>Plan Referral</title>
</head>
<body>
<div style="float:left; width:100%">
<h2></h2>
<input id="_referred_to" type="hidden" value="<?php echo @$referral->referred_to; ?>">
<table align="left" width="100%">
	 <tr>
		 <td width="140">Specialties: </td>
		 <td><?php echo  @$referral->specialties; ?></td>
	 </tr>
	 <tr>
		 <td>Practice Name: </td>
		 <td><?php echo @$referral->practice_name;?></td>
	 </tr><tr>
		 <td>Diagnosis: </td>
		 <td><?php echo @$referral->diagnosis;?></td>
	 </tr>
	 <tr>
		 <td>Reason: </td>
		 <td><?php echo @$referral->reason;?></td>
	 </tr>
	 <tr>
		<td>Referred By: </td>
		<td>
		 <?php echo @$referral->referred_by;?>
		</td>
	 </tr>
	 <tr>
		 <td>Visit Summary: </td>
		 <td>
		 <input type='checkbox' name='visit_summary' id='visit_summary' <?php echo (@$visit_summary ==1)?'checked':''; ?>> Attached
		 &nbsp;&nbsp;
		 
		 </td>
	 	
	 	
	 </tr>
	 <tr>
		 <td>Status: </td>
		 <td>
		 <?php echo @$referral->status;?>
		 </td>
	 </tr>
</table>
</div>
<body>