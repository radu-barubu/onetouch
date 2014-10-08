<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$diagnosis = "";
$icd_9_cm_code = "";
$prescriber = "";
$rx_issue_type = "";
?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
	     diagnosis_icd();
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

		 
		 $('#btnSubmitReportedRx').click(initiateIssueRx);
		 
		 $('#diagnosis').change(function()
            {
            var diagnosis =  $("#diagnosis").val();
						
						if (diagnosis == 'all') {
							$("#icd_9_cm_code").val('0');
						} else {
							var diagnosis_split = diagnosis.split('[');
							var diagnosis_split_array = diagnosis_split[1];
							var diagnosis_split_arrays = diagnosis_split_array.split(']');
							var diagnosis_code = diagnosis_split_arrays[0];
							$("#icd_9_cm_code").val('0');
						}
						

            });
		 
	});
	
	function diagnosis_icd()
    {
		var diagnosis =  $("#diagnosis").val();
		
		if (diagnosis == 'all') {
			$("#icd_9_cm_code").val('0');
		} else {
			var diagnosis_split = diagnosis.split('[');
			var diagnosis_split_array = diagnosis_split[1];
			var diagnosis_split_arrays = diagnosis_split_array.split(']');
			var diagnosis_code = diagnosis_split_arrays[0];
			$("#icd_9_cm_code").val(diagnosis_code);
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
	
	function validateRxForm()
	{
		var valid = true;

		$('#drug_name').removeClass("error");
		$('.error[htmlfor="drug_name"]').remove();
	
		if($('#drug_name').val() == "")
		{
			$('#drug_name').addClass("error");
			$('#drug_name').after('<div htmlfor="drug_name" generated="true" class="error">Select the Drug from the list.</div>');
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
			$issue_reportedrx_url = $html->url(array('mrn' => $mrn, 'task' => 'addnew', 'encounter_id' => $encounter_id)) . '/';
			?>
			getJSONDataByAjax(
				'<?php echo $issue_reportedrx_url; ?>', 
				$('#reportedrxform').serialize(), 
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
	
	function build_rx()
	{
		var sig_verb = $.trim($('#sigVerb').val());
		var sig_factor = $.trim($('#sigFactor').val());
		var sig_form = $.trim($('#sigForm').val());
		var sig_route = $.trim($('#sigRoute').val());
		var sig_freq = $.trim($('#sigFreq').val());
		var sig_mod = $.trim($('#sigMod').val());
		var sig = sig_verb+' '+sig_factor+' '+sig_form+' '+sig_route+' '+sig_freq+' '+sig_mod;
		$('#sig').val(sig);
		$('#sig').removeClass("error");
		$('.error[htmlfor="sig"]').remove();
	}
</script>
<?php 
    echo $this->element("drug_search", array('submit' => 'addTestSearchData', 'open' => 'imgSearchDrugOpen', 'container' => 'drug_search_container'));
    echo $this->element("pharmacy_search", array('submit' => 'addTestSearchData', 'open' => 'imgSearchPharmacyOpen', 'container' => 'pharmacy_search_container')); 
?>
<div style="float:left; padding-left:10px;">
    <span id="btnShowDURreport" class="btn" style="float:right;">DUR Report</span>
    <span id="btnShowDrugHistory" class="btn" style="float:right;">External Drug History</span>
    <span id="btnShowReportedRx" class="btn" style="float:right;">Reported Rx</span>
    <span id="btnShowFreeFormRx" class="btn" style="float:right;">Free Form Rx</span>   
    <span id="btnShowRxHistory" class="btn" style="float:right;">Rx History</span>

</div>  

<div id="div_reportedrx" style="width: 100%; float:left;">
<form name="reportedrxform" id="reportedrxform" method="post" >

<table cellspacing="0" cellpadding="0" class="form" width="100%">
    <tr class="no_hover">
        <td width="15%">&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <!--<tr class="no_hover">
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>-->
    <tr>
        <td class="field_title"><label>Diagnosis:</label></td>
        <td>
            <input type="text" name="data[diagnosis]" id="diagnosis" style="width:420px;background:#eeeeee;" readonly="readonly" value="<?php echo $EncounterAssessment_diagnosis; ?>" >
        </td>
        <input type="hidden" name="data[icd_9_cm_code]" id="icd_9_cm_code" value="<?php echo $icd_9_cm_code; ?>" >
    </tr>
    <tr class="no_hover">
		<td valign=top><label>Drug:</label></td>
		<td>
			<input type="text" name="data[drug_name]" id="drug_name" ></td>
	</tr>
    <tr id="drug_search_row" style="display:none" class="no_hover">
        <td colspan="2">
            <div style="float: left; clear: both; margin-bottom: 10px; width: 80%;">
                <div id="drug_search_container" style="clear:both;"></div>
            </div>
        </td>
    </tr>
    <tr class="no_hover">
        <td><label>Sig:</label></td>
        <td>
            <div style="float:left;"><input type="text" name="data[sig]" id="sig" style="width:400px;" value="" /></div><div style="float:left; padding-left:10px;"><span id="btnShowRxBuilder" class="btn" onclick="$('#rx_builder_row').css('display','table-row');">Show Rx Builder</span></div>
        </td>
    </tr>
    <tr id="rx_builder_row" style="display:none" class="no_hover">
        <td colspan="2">
            <table id="rxbuilder" cellpadding="0" border="0" width="95%">
                <tr class="no_hover">
                <td align="left">
                <div style="float:left">
                    <select name="sigVerb" id="sigVerb" class="dhtmlxsel" style="width:90px">
                    <option value="" selected="selected"></option>
                    <?php foreach($sigverb_list as $sigverb): ?>
                    <option value="<?php echo $sigverb['code']; ?>"><?php echo $sigverb['description']; ?></option>
                    <?php endforeach; ?>   
                    </SELECT>
                </div>
                <div style="float:left">
                    <input size="6" maxLength="6" name="sigFactor" id="sigFactor" type="text"> </div>
                <div style="float:left">
                    <select name="sigForm" id="sigForm" class="dhtmlxsel" style="width:130px">
                    <option value="" selected="selected"> </option>
                    <?php foreach($sigform_list as $sigform): ?>
                    <option value="<?php echo $sigform['code']; ?>"><?php echo $sigform['description']; ?></option>
                    <?php endforeach; ?>   
                    </SELECT></div>
                <div style="float:left">
                    <select name="sigRoute" id="sigRoute" class="dhtmlxsel" style="width:130px">
                    <option value="" selected="selected"> </option>   
                    <?php foreach($sigroute_list as $sigroute): ?>
                    <option value="<?php echo $sigroute['code']; ?>"><?php echo $sigroute['description']; ?></option>
                    <?php endforeach; ?>                       
                    </SELECT></div>
                <div style="float:left">
                    <select name="sigFreq" id="sigFreq" class="dhtmlxsel" style="width:100px">
                    <option value="" selected="selected"> </option>
                    <?php foreach($sigfreq_list as $sigfreq): ?>
                    <option value="<?php echo $sigfreq['code']; ?>"><?php echo $sigfreq['description']; ?></option>
                    <?php endforeach; ?>   
                    </select>                        </div>
                <div style="float:left">
                    <select NAME="sigMod" id="sigMod" class="dhtmlxsel" style="width:95px">
                    <option value="" selected="selected"> </option>
                    <?php foreach($sigmod_list as $sigmod): ?>
                    <option value="<?php echo $sigmod['code']; ?>"><?php echo $sigmod['description']; ?></option>
                    <?php endforeach; ?>   
                    </select>                        </div>
                <div style="float:left; padding-left:10px;"><span id="btnSetRx" class="btn" onclick="build_rx()">Set</span></div>
                </td>
                </tr>                    
            </TABLE>   
        </td>
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
        <td><input type="text" name="data[days_supply]" id="days_supply" style="width:50px" /><label style="padding-left:101px;">Refills:</label> <input type="text" name="data[refills]" id="refills" style="width:50px" />&nbsp;&nbsp;<input type="checkbox" name="daw" id="daw" /> DAW</td>
    </tr>
    <tr class="no_hover">
        <td style="vertical-align:top;"><label>Comment:</label></td>
        <td ><textarea name="data[comments]" id="comments" cols="45" rows="5" style="width: 75%; height: 100px;" ></textarea></td>
    </tr>        
    <tr class="no_hover">
        <td><label>Issue To:</label></td>
        <td>
            <input type="hidden" name="data[pharmacy_id]" id="pharmacy_id" value=""/>
            <div style="float:left;"><input name="data[issue_to]" id="issue_to" type="text" style="width:400px;" value="" /></div>								
            <div style="float:left; padding-left:5px;"><img id="imgSearchPharmacyOpen" style="cursor: pointer;margin-top: 3px;" src="<?php echo $this->Session->webroot . 'img/search_data.png'; ?>" width="20" height="20" onclick="$('#pharmacy_search_row').css('display','table-row');" /></div>
        </td>
    </tr>
    <tr id="pharmacy_search_row" style="display:none;" class="no_hover">
        <td colspan="2">
            <div style="float: left; clear: both; margin-bottom: 10px; width: 80%;">
                <div id="pharmacy_search_container" style="clear:both;"></div>
            </div>
        </td>
    </tr>
    <tr class="no_hover">
        <td><label>Method:</label></td>
        <td><div style="float:left;">
            <select name="data[rx_issue_type]" id="rx_issue_type" style="width:200px;" >
            <option value=""></option>
            <?php foreach($issue_types as $issue_type): ?>
            <option value="<?php echo $issue_type['code']; ?>" <?php if($rx_issue_type == $issue_type['code']):?>selected="selected"<?php endif; ?>><?php echo $issue_type['description']; ?></option>
            <?php endforeach; ?>
            </select></div>									
        </td>
    </tr>
    <tr class="no_hover">
        <td><label>Prescriber:</label></td>
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
        <td valign=top><label>Reported Prescriber (if different):</label></td>
        <<td>
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
        <td colspan="2">
        <div class="actions">
           <ul>
               <li><a id="btnSubmitReportedRx" href="javascript:void(0);" class="btn">Submit</a></li>
               <li><a class="ajax" href="">Cancel</a>&nbsp;&nbsp;<span id="submit_swirl" style="display: none;"><?php echo $smallAjaxSwirl; ?></span></li>
           </ul>
        </div>
        </td>
    </tr>
</table>
</form>
</div>
        
    