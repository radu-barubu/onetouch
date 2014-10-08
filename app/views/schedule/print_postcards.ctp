<?php

if (isset($AppointmentSetupDetail['AppointmentSetupDetail']))
{
	extract($AppointmentSetupDetail['AppointmentSetupDetail']);
}

$count = 0;
foreach ($AppointmentReminders as $AppointmentReminder):
	$count++;
	if ($count % 2)
	{
		echo "<table cellpadding=0 cellspacing=0 style=\"width: 8in; height: ".(strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")?"11":"10.5")."in; \"><tr valign=top><td align=center><div style=\"height: 1.25in; \"></div>";
	}
	echo "<table cellpadding=0 cellspacing=0 style=\"width: 6in; height: 4in; \">
	<tr height=1><td class=text width=60% valign=top>$sender_name<br>".$sender_address.($sender_address?"<br>":"")."</td><td width=40% rowspan=2 style=\"border-left: 1px solid #000000\" align=center><table cellpadding=0 cellspacing=0><tr><td width=15></td><td>".$AppointmentReminder['Patient']['first_name']." ".$AppointmentReminder['Patient']['last_name']."<br>".$AppointmentReminder['Patient']['address1'].($AppointmentReminder['Patient']['address1']?"<br>":"").$AppointmentReminder['Patient']['city'].(($AppointmentReminder['Patient']['city'] and ($AppointmentReminder['Patient']['state'] or $AppointmentReminder['Patient']['zipcode']))?",":"")." ".$AppointmentReminder['Patient']['state']." ".$AppointmentReminder['Patient']['zipcode']."</td></tr></table></td></tr>
	<tr><td class=text><table cellpadding=0 cellspacing=0><tr><td class=text>".nl2br(str_replace("[Date]", $AppointmentReminder['AppointmentReminder']['appointment_call_date'], str_replace("[Phone Number]", $phone_number, $AppointmentReminder['AppointmentReminder']['message'])))."</td><td width=15></td></tr></table></td></tr>
	</table>";
	if (!($count % 2) or $count == count($AppointmentReminders))
	{
		echo "</td></tr></table>";
		if ($count < count($AppointmentReminders))
		{
			echo "<p style='page-break-after: always; ' /></p>";
		}
	}
	else
	{
		echo "<div style=\"height: 0.5in; \"></div>";
	}
endforeach;
?>
<?php
	$scriptArray = array('/js/jquery/jquery.js');
	if(isset($isiPadApp)&&$isiPadApp)
		$scriptArray[] = '/js/iPad/jquery.ipadapp.js';
	echo $this->Html->script($scriptArray);
?>       
<script language="javascript" type="text/javascript">window.print();</script>
