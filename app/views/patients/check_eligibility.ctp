<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$insurance_info_id = (isset($this->params['named']['insurance_info_id'])) ? $this->params['named']['insurance_info_id'] : "";

$suffix_list = array('I', 'II', 'III', 'IV', 'Jr', 'Sr');
$gender_list = array('F' => 'Female', 'M' => 'Male', 'A' => 'Ambiguous', 'O' => 'Other', 'N' => 'Not Applicable', 'U' => 'Unknown');

?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "general_information"))); ?>

<script language="javascript" type="text/javascript">
$(document).ready(function()
{
	initCurrentTabEvents('check_eligibility_form_area');
	$("#frmCheckEligibility").validate(
	{
		errorElement: "div",
		submitHandler: function(form) 
		{
			$('#frmCheckEligibility').css("cursor", "wait");
			$('#imgLoadInsuranceInfo').css('display', 'block');
			$.post(
				'<?php echo $thisURL.'/task:check_eligibility'; ?>', 
				$('#frmCheckEligibility').serialize(), 
				function(data)
				{
					$('#frmCheckEligibility').css("cursor", "auto");
					loadTab($('#frmCheckEligibility'), '<?php echo $thisURL.'/task:eligibility_respond/insurance_info_id:'.$insurance_info_id; ?>');
				},
				'json'
			);
		}
	});
	
	$("#payer_list").autocomplete('<?php echo $html->url(array('controller' => 'patients', 'action' => 'check_eligibility', 'task' => 'payer_list_autocomplete')); ?>', 
    {
        cacheLength: 20,
        minChars: 2,
        max: 20,
        mustMatch: false,
        matchContains: false,
        scrollHeight: 300
    });

    $("#payer_list").result(function(event, data, formatted)
    {
		$("#payer_list_code").val(data[1]);
    });
	
	$("#service_type").autocomplete('<?php echo $html->url(array('controller' => 'patients', 'action' => 'check_eligibility', 'task' => 'service_type_autocomplete')); ?>', 
    {
        cacheLength: 20,
        minChars: 2,
        max: 20,
        mustMatch: false,
        matchContains: false,
        scrollHeight: 300
    });

    $("#service_type").result(function(event, data, formatted)
    {
		$("#service_type_code").val(data[1]);
    });
	
	$("#provider_list").autocomplete('<?php echo $html->url(array('controller' => 'patients', 'action' => 'check_eligibility', 'task' => 'provider_list_autocomplete')); ?>', 
    {
        cacheLength: 20,
        minChars: 2,
        max: 20,
        mustMatch: false,
        matchContains: false,
        scrollHeight: 300
    });
});
function submitForm()
{
	if ($("#payer_list_code").val() == "")
	{ 
		//$("#payer_list").val("");
	}

	if ($("#service_type_code").val() == "")
	{ 
		$("#service_type").val("");
	}

	$('#frmCheckEligibility').submit();
}
</script>

<div id="check_eligibility_form_area" class="tab_area">
<?php
if ($task == "eligibility_respond")
{
	$edi_271_details = EDI271::get_271_info($EligibilityRespond);
	?>
    <table cellspacing="0" cellpadding="0" class="small_table form">
        <tr>
            <th colspan="2">Payer Information</th>
        </tr>
        <tr class="no_hover">
            <td width="150">Payer ID:</td>
            <td><?php echo $edi_271_details['payer']['payer_identification']; ?></td>
        </tr>
        <tr class="no_hover">
            <td width="150">Payer Name:</td>
            <td><?php echo $edi_271_details['payer']['payer_name']; ?></td>
        </tr>
    </table>
    <table cellspacing="0" cellpadding="0" class="small_table form" style="margin-top: 10px;">
        <tr>
            <th colspan="2">Provider Information</th>
        </tr>
        <tr class="no_hover">
            <td width="150">Provider NPI:</td>
            <td><?php echo @$edi_271_details['provider']['provider_npi']; ?></td>
        </tr>
        <tr class="no_hover">
            <td width="150">Provider Name:</td>
            <td><?php echo @$edi_271_details['provider']['provider_name']; ?></td>
        </tr>
    </table>
    <table cellspacing="0" cellpadding="0" class="small_table form" style="margin-top: 10px;">
        <tr>
            <th colspan="2">Subscriber Information</th>
        </tr>
        <tr class="no_hover">
            <td width="150">Subscriber ID:</td>
            <td><?php echo @$edi_271_details['subscriber']['subscriber_id']; ?></td>
        </tr>
        <tr class="no_hover">
            <td width="150">Subscriber Name:</td>
            <td><?php echo @$edi_271_details['subscriber']['first_name']; ?> <?php echo @$edi_271_details['subscriber']['last_name']; ?></td>
        </tr>
        <tr class="no_hover">
            <td width="150">Date of Birth:</td>
            <td><?php echo __date($global_date_format, strtotime(@$edi_271_details['subscriber']['dob'])); ?></td>
        </tr>
        <tr class="no_hover">
            <td width="150">Gender:</td>
            <td><?php echo @$edi_271_details['subscriber']['gender']; ?></td>
        </tr>
        <tr class="no_hover">
            <td width="150"><?php echo @$edi_271_details['subscriber']['id_type']; ?>:</td>
            <td><?php echo @$edi_271_details['subscriber']['id_value']; ?></td>
        </tr>
        <tr class="no_hover">
            <td width="150">Address:</td>
            <td><?php echo @$edi_271_details['subscriber']['address1']; ?></td>
        </tr>
        <tr class="no_hover">
            <td width="150">City:</td>
            <td><?php echo @$edi_271_details['subscriber']['city']; ?></td>
        </tr>
        <tr class="no_hover">
            <td width="150">State:</td>
            <td><?php echo @$edi_271_details['subscriber']['state']; ?></td>
        </tr>
        <tr class="no_hover">
            <td width="150">Zip Code:</td>
            <td><?php echo @$edi_271_details['subscriber']['zipcode']; ?></td>
        </tr>
    </table>
    <table cellspacing="0" cellpadding="0" class="small_table form" style="margin-top: 10px;">
        <tr>
            <th colspan="2">Eligibility Status</th>
        </tr>
        <tr class="no_hover">
            <td colspan="2"><?php echo $edi_271_details['eligibility']['status']; ?></td>
        </tr>
    </table>
    <div class="actions">
		<ul>
			<li><a class="ajax" href="<?php echo $html->url(array('action' => 'check_eligibility', 'patient_id' => $patient_id, 'insurance_info_id' => $insurance_info_id)); ?>" onclick="$('#imgLoadInsuranceInfo').show()">Cancel</a></li>
		</ul>
		<span id="imgLoadInsuranceInfo" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	</div>
    <?php
}
else
{
	extract($EditItem['PatientInsurance']);
	$start_date = __date($global_date_format, strtotime($start_date));
	$end_date  = __date($global_date_format, strtotime($end_date));
	$insured_birth_date  = __date($global_date_format, strtotime($insured_birth_date));
?>
<form id="frmCheckEligibility" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
    <input type="hidden" name="data[patient_id]" value="<?php echo $patient_id; ?>" />
	<table cellpadding="0" cellspacing="0" class="form" width="65%">
		<tr>
			<td colspan="2"><h3>
					<label>Eligibility Information:</label>
			</h3></td>
		</tr>
		<tr>
			<td><label>Payer List:</label></td>
			<td>
			<input type="hidden" name="data[payer_list_code]" id="payer_list_code" />
			<input type="text" name="data[payer_list]" id="payer_list" class="field_small required" style="width:450px" /></td>
		</tr>
		<tr>
			<td><label>Member/Policy Number:</label></td>
			<td><input type="text" name="data[subscriber_id]" id="subscriber_id"  value="<?php echo $policy_number;  ?>" class="field_small required" /></td>
		</tr>
		<tr>
			<td><label>First Name:</label></td>
			<td><input type="text" name="data[subscriber_fname]" id="subscriber_fname" value="<?php echo $insured_first_name;  ?>" class="field_small required" /></td>
		</tr>
		<tr>
			<td><label>Last Name:</label></td>
			<td><input type="text" name="data[subscriber_lname]" id="subscriber_lname" value="<?php echo $insured_last_name;  ?>" class="field_small required" /></td>
		</tr>
		<!--
		<tr>
			<td><label>Suffix:</label></td>
			<td>
				<select name="data[name_suffix]" id="name_suffix" class="field_small required">
					<option value="">Select Suffix</option>
					<?php
						foreach($suffix_list as $suffix_item)
						{
							?>
							<option value="<?php echo $suffix_item; ?>" <?php if($insured_name_suffix == $suffix_item) { echo 'selected="selected"'; } ?>><?php echo $suffix_item; ?></option>
							<?php
						}
					?>
				</select>
			</td>
		</tr>
		-->
		<tr>
			<td style="vertical-align:top; "><label>Birth Date:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[dob]', 'id' => 'dob', 'value' => $insured_birth_date, 'required' => true)); ?></td>
		</tr>
		<!--
		<tr>
			<td><label>Gender:</label></td>
			<td>
				<select name="data[sex]" id="sex" class="field_small required">
					<option value="">Select Gender</option>
					<?php
						foreach($gender_list as $gender_code => $gender_item)
						{
							?>
							<option value="<?php echo $gender_code; ?>" <?php if($insured_sex == $gender_code) { echo 'selected="selected"'; } ?>><?php echo $gender_item; ?></option>
							<?php
						}
					?>
				</select>
			</td>
		</tr>
		-->
		<tr>
			<td><label>Service Type:</label></td>
			<td>
			<input type="hidden" name="data[service_type_code]" id="service_type_code" />
			<input type="text" name="data[service_type]" id="service_type" class="field_small required" style="width:450px" /></td>
		</tr>
		<tr>
			<td style="vertical-align:top; "><label>Service Date:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[service_date]', 'id' => 'service_date', 'value' => $start_date, 'required' => true)); ?></td>
		</tr>
		<!--
		<tr>
			<td style="vertical-align:top; "><label>Service End Date:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[end_date]', 'id' => 'end_date', 'value' => $end_date, 'required' => true)); ?></td>
		</tr>
		-->
		<tr>
			<td><label>Provider NPI:</label></td>
			<td><input type="text" name="data[provider_npi]" id="provider_npi"  value="<?php echo $provider_npi;  ?>" class="field_small required" /></td>
		</tr>
		<tr>
			<td><label>Provider List:</label></td>
			<td><input type="text" name="data[provider_list]" id="provider_list" class="field_small required" style="width:450px" /></td>
		</tr>
	</table>
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="submitForm();">Send to Payer</a></li>
			<li><a class="ajax" href="<?php echo $html->url(array('action' => 'insurance_information', 'task' => 'edit', 'patient_id' => $patient_id, 'insurance_info_id' => $insurance_info_id)); ?>" onclick="$('#imgLoadInsuranceInfo').show()">Cancel</a></li>
		</ul>
		<span id="imgLoadInsuranceInfo" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	</div>
</form>
<?php
}
?>
</div>
