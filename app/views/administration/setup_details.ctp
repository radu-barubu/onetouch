<div style="overflow: hidden;">
	<?php echo $this->element('administration_health_maintenance_links'); ?>
</div>


<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
<?php 

$thisURL = $this->Session->webroot . $this->params['url']['url']."/task:save";

if (isset($SetupDetail['SetupDetail']))
{
	extract($SetupDetail['SetupDetail']);
}

if(isset($detail_id))
{
	$id_field = '<input type="hidden" name="data[SetupDetail][detail_id]" id="detail_id" value="'.$detail_id.'" />';
}
else
{
	//Init default value here
	$id_field = "";
	$sender_name = "";
	$sender_address = "";
	$email_address = "";
	$phone_number = "";
	$salutation = "Dear [Patient Name]";
	$salutation_email = "Yes";
	$salutation_postcard = "Yes";
	$salutation_sms = "";
	$signature = "Regards,\r\n[Sender Name]";
	$signature_email = "Yes";
	$signature_postcard = "Yes";
	$signature_sms = "";
	$days_in_advance_1 = "14";
	$message_1 = "You have a doctor appointment scheduled on [Date] at [Time].";
	$days_in_advance_2 = "14";
	$message_2 = "It is time to be rechecked. Please call our office for an appointment. [Phone Number]";
	$days_in_advance_3 = "14";
	$message_3 = "It is time to schedule an appointment. Please call our office for an appointment. [Phone Number]";
	$days_in_advance_4 = "14";
	$message_4 = "You missed your last scheduled appointment. Please call our office and we will make another one for you. [Phone Number]";
	$days_in_advance_5 = "7";
	$message_5 = "You have a health maintenance activity targeted [Date]. Please call our office for more information and to schedule an appointment if needed.";
	$days_in_advance_6 = "7";
	$message_6 = "You have a health maintenance activity targeted [Date]. Please call our office for more information and to schedule an appointment if needed.";
}

?>

<div style="overflow: hidden;">
    <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
    <input type="hidden" name="data[usedefault]" id="usedefault" value="false" />
	<?php
	echo "$id_field";
	?>
        <table cellpadding="0" cellspacing="0" class="form" width=100%>
            <tr>
                <td width="200"><label>Sender Name:</label></td>
                <td><input type="text" name="data[SetupDetail][sender_name]" id="sender_name" style="width:250px;" class="required" value="<?php echo $sender_name; ?>"></td>
            </tr>
            <tr>
                <td valign='top' style="vertical-align:top"><label>Sender Address:</label></td>
				<td><textarea name="data[SetupDetail][sender_address]" style="width:372px; height:100px" class="required"><?php echo $sender_address ?></textarea></td>
            </tr>
            <tr>
                <td><label>Email Address:</label></td>
                <td><input type="text" name="data[SetupDetail][email_address]" id="email_address" style="width:250px;" class="required email" value="<?php echo $email_address; ?>"></td>
            </tr>
            <tr>
                <td><label>Phone Number:</label></td>
                <td><input type="text" name="data[SetupDetail][phone_number]" id="phone_number" style="width:250px;" class="required phone" value="<?php echo $phone_number; ?>"></td>
            </tr>
            <tr>
                <td><label>Salutation:</label></td>
                <td><input type="text" name="data[SetupDetail][salutation]" id="salutation" style="width:250px;" class="required" value="<?php echo $salutation; ?>"></td>
			</tr>
			<tr><td></td><td>
            <label  class="label_check_box" style="margin:5px 10px 10px 0;">
            <input type=checkbox name="data[SetupDetail][salutation_email]" id="salutation_email" value="Yes" <?php echo ($salutation_email=="Yes"?"checked":""); ?>>
            Email</label>
			<label  class="label_check_box" style="margin:5px 10px 10px 0;">
            <input type=checkbox name="data[SetupDetail][salutation_postcard]" id="salutation_postcard" value="Yes" <?php echo ($salutation_postcard=="Yes"?"checked":""); ?>>
            Postcard</label>
			<label  class="label_check_box" style="margin:5px 10px 10px 0;">
            <input type=checkbox name="data[SetupDetail][salutation_sms]" id="salutation_sms" value="Yes" <?php echo ($salutation_sms=="Yes"?"checked":""); ?>>
            SMS</label>
            </td>
            </tr>
            <tr>
                <td valign='top' style="vertical-align:top"><label>Signature:</label></td>
				<td><textarea name="data[SetupDetail][signature]" style="width:372px; height:100px" class="required"><?php echo $signature ?></textarea></td>
			</tr>
			<tr><td></td><td>
            <label  class="label_check_box" style="margin:5px 10px 10px 0;">
            <input type=checkbox name="data[SetupDetail][signature_email]" id="signature_email" value="Yes" <?php echo ($signature_email=="Yes"?"checked":""); ?>>
            Email</label>
			<label  class="label_check_box" style="margin:5px 10px 10px 0;">
            <input type=checkbox name="data[SetupDetail][signature_postcard]" id="signature_postcard" value="Yes" <?php echo ($signature_postcard=="Yes"?"checked":""); ?>>
            Postcard</label>
			<label  class="label_check_box" style="margin:5px 10px 10px 0;">
            <input type=checkbox name="data[SetupDetail][signature_sms]" id="signature_sms" value="Yes" <?php echo ($signature_sms=="Yes"?"checked":""); ?>>
            SMS
            </label>
            </td>
            </tr>
			<?php
			$type_array = array("Scheduled Appointment", "New Appointment", "Need Appointment", "Missed Appointment", "Reminder", "Followup");
			for ($i = 4; $i < count($type_array); ++$i)
			{
				?>
				<tr>
					<td><br><u><label><?php echo $type_array[$i] ?></label></u></td>
				</tr>
				<tr>
					<td><label>Days in Advance:</label></td>
					<td><input type="text" name="data[SetupDetail][days_in_advance_<?php echo ($i + 1) ?>]" id="days_in_advance_<?php echo ($i + 1) ?>" style="width:50px;" class="required numeric_only" value="<?php echo ${"days_in_advance_".($i + 1)}; ?>"></td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Message:</label></td>
					<td><textarea name="data[SetupDetail][message_<?php echo ($i + 1) ?>]" style="height:100px" class="required"><?php echo ${"message_".($i + 1)} ?></textarea></td>
				</tr>
				<?php
			}
			?>
        </table>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#frm").validate({errorElement: "div"});
	});
	</script>
    </form>
</div>
<div class="actions" removeonread="true">
    <ul>
        <li><a href="javascript: void(0);" onclick="$('#usedefault').val('false'); $('#frm').submit();">Save</a></li>
        <li><a href="javascript: void(0);" onclick="$('#frm').validate().cancelSubmit = true; $('#usedefault').val('true'); $('#frm').submit();">Use Default</a></li>
    </ul>
</div>
