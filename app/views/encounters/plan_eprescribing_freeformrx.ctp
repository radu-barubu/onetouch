<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$prescriber = "";

?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{		 
		 $('#btnShowDURreport').click(function()
		 {            
			 loadRxElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_dur_report/encounter_id:<?php echo $encounter_id; ?>/mrn:<?php echo $mrn; ?>/');
		 });
		 
		 $('#btnShowDrugHistory').click(function()
		 {            
			 loadRxElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_drug_history/encounter_id:<?php echo $encounter_id; ?>/mrn:<?php echo $mrn; ?>/');
		 });
		 
		 $('#btnShowFreeFormRx').click(function()
		 {            
			 loadRxElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_freeformrx/encounter_id:<?php echo $encounter_id; ?>/mrn:<?php echo $mrn; ?>/');
		 });
		 
		 $('#btnShowReportedRx').click(function()
		 {            
			 loadRxElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_reportedrx/encounter_id:<?php echo $encounter_id; ?>/mrn:<?php echo $mrn; ?>/');
		 });
		 
		 $('#btnShowRxHistory').click(function()
         {
             loadRxElectronicTable('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/mrn:<?php echo $mrn; ?>/');
         });

		 
		 $('#drug_name').change(function()
		 {
			$(this).removeClass("error");
			$('.error[htmlfor="drug_name"]').remove();
		 });
		
		 $('#sig').change(function()
		 {
			$(this).removeClass("error");
			$('.error[htmlfor="sig"]').remove();
		 });
		
		 $('#quantity').change(function()
		 {
			$(this).removeClass("error");
			$('.error[htmlfor="quantity"]').remove();
		 });
		
		 $('#days_supply').change(function()
		 {
			$(this).removeClass("error");
			$('.error[htmlfor="days_supply"]').remove();
		 });
		
		 $('#refills').change(function()
		 {
			$(this).removeClass("error");
			$('.error[htmlfor="refills"]').remove();
		 });
		 
		 $('#btnSubmitFreeFormRx').click(initiateIssueRx);
	});
	
	function validateRxForm()
	{
		var valid = true;
		
		$('#prescriber').removeClass("error");
		$('.error[htmlfor="prescriber"]').remove();
			
		$('#drug_name').removeClass("error");
		$('.error[htmlfor="drug_name"]').remove();
		
		$('#sig').removeClass("error");
		$('.error[htmlfor="sig"]').remove();
		
		$('#quantity').removeClass("error");
		$('.error[htmlfor="quantity"]').remove();
		
		$('#days_supply').removeClass("error");
		$('.error[htmlfor="days_supply"]').remove();
		
		$('#refills').removeClass("error");
		$('.error[htmlfor="refills"]').remove();
		
		if($('#prescriber').val() == "")
		{
			$('#prescriber').addClass("error");
			$('#prescriber').after('<div htmlfor="prescriber" generated="true" class="error">This field is required.</div>');
			valid = false;
		}
		
		if($('#drug_name').val() == "")
		{
			$('#drug_name').addClass("error");
			$('#drug_name').after('<div htmlfor="drug_name" generated="true" class="error">This field is required.</div>');
			valid = false;
		}
		
		if($('#sig').val() == "")
		{
			$('#sig').addClass("error");
			$('#sig').after('<div htmlfor="sig" generated="true" class="error">This field is required.</div>');
			valid = false;
		}
		
		if($('#quantity').val() == "")
		{
			$('#quantity').addClass("error");
			$('#quantity').after('<div htmlfor="quantity" generated="true" class="error">This field is required.</div>');
			valid = false;
		}
		
		if($('#days_supply').val() == "")
		{
			$('#days_supply').addClass("error");
			$('#days_supply').after('<div htmlfor="days_supply" generated="true" class="error">This field is required.</div>');
			valid = false;
		}
		
		if($('#refills').val() == "")
		{
			$('#refills').addClass("error");
			$('#refills').after('<div htmlfor="refills" generated="true" class="error">This field is required.</div>');
			valid = false;
		}
		
		
		return valid;
	}
	
	function initiateIssueRx()
	{
		if(validateRxForm())
		{
			$('#btnSubmitFreeFormRx').addClass("button_disabled");
			//$('#btnSubmitFreeFormRx').unbind('click');
			$('#submit_swirl').show();
			
			<?php
			$issue_freeformrx_url = $html->url(array('mrn' => $mrn, 'task' => 'addnew', 'encounter_id' => $encounter_id)) . '/';
			?>
			getJSONDataByAjax(
				'<?php echo $issue_freeformrx_url; ?>', 
				$('#rxfreeform').serialize(), 
				function(){}, 
				function(data)
				{
					$('#btnSubmitFreeFormRx').removeClass("button_disabled");
					$('#btnSubmitFreeFormRx').click(initiateIssueRx);
					$('#submit_swirl').hide();
					loadRxElectronicTable(data.redir_link);
				}
			);
		}
	}
		
	function addTestSearchData(data)
	{
		var test_codes = $("#tableTestCode").data('data');
		
		var found = false;
		
		for(var i = 0; i < test_codes.length; i++)
		{
			if(test_codes[i]['orderable'] == data['orderable'])
			{
				found = true;
			}
		}
		
		if(!found)
		{
			test_codes[test_codes.length] = data;
		}
		
		$("#tableTestCode").data('data', test_codes);

	}
</script>
<?php

if($task == 'printrx')
{
    ?>
	<form name="rxprint_form" id="rxprint_form" method="post">
	<input type="hidden" name="rx" id="rx" value="<?php echo trim($rx); ?>">
	<input type="hidden" name="patient_id" id="patient_id" value="<?php echo $patient_id; ?>" />
	<table cellpadding="0" cellspacing="0" class="form" width="100%">
	<tr><td><?php createButton("backtoRxHistory", "Rx History", "", "right"); createButton("backtoFreeform", "Create Another", "", "right"); createButton("printBtn", "Print", "", "right"); ?>
		</td>
	</tr>
	<tr><td align="left"><?php echo $drug_name; ?></td></tr>
    <tr><td align="left">Qty: <?php echo $quantity; ?></td></tr>
    <tr><td align="left">Days Supply: <?php echo $days_supply; ?></td></tr>
	<tr><td align="left">SIG: <?php echo $sig; ?></td></tr>
	<tr><td align="left">Refills: <?php echo $refills; ?></td></tr> 
	<tr><td align="left">&nbsp;</td></tr> 
	<tr><td align="left"><strong><?php echo $providercaregiver_data['cg_last_name'].' '.$providercaregiver_data['cg_first_name']; ?></strong></td></tr> 
	<tr><td align="left"><?php echo $providercaregiver_data['provider_name']; ?></td></tr> 
	</table>
	</form>
    <form name="temp_form" id="temp_form" action="" method="post">  </form>
    <div id="iframeplaceholder" style="position: absolute; top: 0px; left: 0px; width: 0px; height: 0px;"></div>
    <?php
}
else
{
$diagnosis = '';
	?>
    <div style="float:left; padding-left:10px;">
        <span id="btnShowDURreport" class="btn" style="float:right;">DUR Report</span>
        <span id="btnShowDrugHistory" class="btn" style="float:right;">External Drug History</span>
        <span id="btnShowReportedRx" class="btn" style="float:right;">Reported Rx</span>
        <span id="btnShowFreeFormRx" class="btn" style="float:right;">Free Form Rx</span>  
        <span id="btnShowRxHistory" class="btn" style="float:right;">Rx History</span>
 
    </div><br />
	<div id="div_rxfreeform" style="width: 100%; float: left;">
	<form name="rxfreeform" id="rxfreeform" action="" method="post" >
    	 
	<table cellspacing="0" cellpadding="0" class="form" width="100%">
    <tr class="no_hover">
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr class="no_hover">
        <td class="field_title"><label>Prescriber:</label></td>
        <td><div style="float:left;">
            <select name="data[prescriber]" id="prescriber" style="width:200px;" >
                <option value=""></option>
                <?php foreach($caregivers as $caregiver): ?>
                <option value="<?php echo $caregiver['caregiver'].'|'.$caregiver['cg_first_name'].' '.$caregiver['cg_last_name']; ?>" <?php if($prescriber == $caregiver['caregiver'] or $user['clinician_reference_id'] == $caregiver['caregiver']):?>selected="selected"<?php endif; ?>><?php echo $caregiver['cg_first_name']; ?>, <?php echo $caregiver['cg_last_name']; ?></option>
                <?php endforeach; ?>
            </select></div>
        </td>
    </tr>
    <?php
    if($user['clinician_reference_id'] == '' or $user['clinician_reference_id'] == NULL)
    {
    ?>
    <tr class="no_hover">
        <td><label>Supervisor:</label></td>
        <td>
            <select name="data[supervising_prescriber]" id="supervising_prescriber" style="width:200px;">
            <option value=""></option>
            <?php foreach($caregivers as $caregiver): ?>
            <option value="<?php echo $caregiver['caregiver'].'|'.$caregiver['cg_first_name'].' '.$caregiver['cg_last_name']; ?>" <?php if($supervising_prescriber == $caregiver['caregiver']):?>selected="selected"<?php endif; ?>><?php echo $caregiver['cg_first_name']; ?>, <?php echo $caregiver['cg_last_name']; ?></option>
            <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php
    }
    else
    {
    ?>
        <input type="hidden" name="data[supervising_prescriber]" id="supervising_prescriber" value="<?php echo $user['clinician_reference_id'].'|'.$user['firstname'].' '.$user['lastname']; ?>" >                       
    <?php
    }
    ?>
	<tr class="no_hover">
        <td valign=top><label>Diagnosis:</label></td>
        <td>
            <input type="text" name="data[diagnosis]" id="diagnosis" style="width:420px;background:#eeeeee;" readonly="readonly" value="<?php echo $EncounterAssessment_diagnosis; ?>" >
                </td>
        </tr>

	<tr class="no_hover">
		<td valign=top><label>Drug:</label></td>
		<td>
			<input type="text" name="data[drug_name]" id="drug_name" ></td>
	</tr>
	<tr class="no_hover">
		<td valign=top><label>SIG:</label></td>
		<td>
		<input type="text" name="data[sig]" id="sig" style="width:400px;"/></td>
	</tr>		
	<tr class="no_hover">
		<td valign=top><label>Quantity:</label></td>
		<td><input type="text" name="data[quantity]" id="quantity" style="width:100px"><label style="padding-left:65px;">Unit:</label> <select name="data[unit_of_measure]" id="unit_of_measure" style="width:200px;">
            <option value=""></option>
			<?php foreach($unit_of_measures as $unit_of_measure): ?>
            <option value="<?php echo $unit_of_measure['code']; ?>" <?php if($unit_of_measure == $unit_of_measure['code']):?>selected="selected"<?php endif; ?>><?php echo $unit_of_measure['description']; ?></option>
            <?php endforeach; ?>
            </select></td>
	</tr>
	<tr class="no_hover">
		<td valign=top><label>Days Supply:</label></td>
		<td><input type="text" name="data[days_supply]" id="days_supply" style="width:50px" /><label style="padding-left:101px;;">Refills:</label> <input type="text" name="data[refills]" id="refills" style="width:50px" />&nbsp;&nbsp;<input type="checkbox" name="daw" id="daw" /> DAW</td>
	</tr>
	<tr class="no_hover">
		<td style="vertical-align:top;"><label>Comment:</label></td>
		<td ><textarea name="data[comments]" id="comments"></textarea></td>
	</tr>		
	
	<tr class="no_hover">
		<td colspan="2">
        <div class="actions">
            <ul>
                <li><a id="btnSubmitFreeFormRx" href="javascript:void(0);" class="btn">Submit</a>&nbsp;&nbsp;<span id="submit_swirl" style="display: none;"><?php echo $smallAjaxSwirl; ?></span></li>
            </ul>
        </div>
        </td>
	</tr>
	</table>
	</form>
	</div>
<?php
}
?>
<script language="javascript" type="text/javascript">

</script>
		
    