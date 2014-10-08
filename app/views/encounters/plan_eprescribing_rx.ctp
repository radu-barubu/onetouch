<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
$order_id = (isset($this->params['named']['order_id'])) ? $this->params['named']['order_id'] : "";
$addURL = $html->url(array('mrn' => $mrn, 'task' => 'addnew', 'patient_id' => $patient_id, 'encounter_id' => $encounter_id)) . '/';
$deleteURL = $html->url(array('task' => 'delete', 'mrn' => $mrn, 'patient_id' => $patient_id, 'encounter_id' => $encounter_id)) . '/';
$mainURL = $html->url(array('mrn' => $mrn, 'encounter_id' => $encounter_id)) . '/';
$smallAjaxSwirl=$html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$autoPrint = isset($this->params['named']['auto_print']) ? '1' : '';
$from_patient = (isset($this->params['named']['from_patient'])) ? $this->params['named']['from_patient'] : NULL;

$label_width = 120;

if($from_patient)
{
	$label_width = 140;	
}

?>

<style>

td .field_title {
	width: 110px;
}

td .field_title2 {
	width: 80px;
}
</style>

<script language="javascript" type="text/javascript">
var task = '<?php echo $task; ?>';
</script>

<div id="plan_rx_electronic_table_area">
<?php if($task == 'print'): ?>
	<?php
	$rx_unique_id = (isset($this->params['named']['rx_unique_id'])) ? $this->params['named']['rx_unique_id'] : "";
	?>
	<script language="javascript" type="text/javascript">
		function issue_new_rx()
		{
			if($('.section_btn[labtype="Rx"]').length > 0)
			{
				$('.section_btn[labtype="Rx"]').click();
			}
			else
			{
				loadRxElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'patient_id' => $patient_id, 'from_patient' => 1)); ?>');
			}
		}
		
		function adjustIframeHeight(h)
        {
            $("#frmPrintRx").css("height", h + "px");
        }
		
		function printPage()
        {
            window.frames['frmPrintRx'].focus(); 
            document.getElementById('frmPrintRx').contentWindow.printPage();
        }
	</script>
    <div id="frm_print_loading"><?php echo $smallAjaxSwirl; ?></div>
    <iframe name="frmPrintRx" id="frmPrintRx" onload="$('#frm_print_loading').hide();" src="<?php echo $html->url(array('action' => 'plan_eprescribing_rx_print', 'rx_unique_id' => $rx_unique_id, 'auto_print' => 1)); ?>" frameborder="0" width="100%" style="height: 400px;" scrolling="no"></iframe>
    <div class="actions">
        <ul>
        	<li><a onclick="printPage();" href="javascript:void(0);">Print Rx</a></li>
            <li><a onclick="issue_new_rx();" href="javascript:void(0);">Issue New Rx</a>&nbsp;&nbsp;<span id="submit_swirl" style="display: none;"><?php echo $smallAjaxSwirl; ?></span></li>
        </ul>
     </div>
<?php elseif($task == 'success'): ?>
	<?php
	$type = (isset($this->params['named']['type'])) ? $this->params['named']['type'] : "";
	$rx_unique_id = (isset($this->params['named']['rx_unique_id'])) ? $this->params['named']['rx_unique_id'] : "";
	?>
    <script language="javascript" type="text/javascript">
		function issue_new_rx()
		{
			if($('.section_btn[labtype="Rx"]').length > 0)
			{
				$('.section_btn[labtype="Rx"]').click();
			}
			else
			{
				loadRxElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'patient_id' => $patient_id, 'from_patient' => 1)); ?>');
			}
		}
		
		function print_rx()
		{
			loadRxElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_eprescribing_rx', 'task' => 'print', 'encounter_id' => $encounter_id, 'mrn' => $mrn, 'patient_id' => $patient_id, 'rx_unique_id' => $rx_unique_id)); ?>');
		}
	</script>
	<table width="100%">
        <tr class="no_hover">
            <td><strong><?php if($type == 'issue_rx'): ?>Issued<?php else: ?>Hold<?php endif; ?></strong></td>
        </tr>
        <tr class="no_hover">
            <td><strong><?php echo $rx['EmdeonPrescription']['created_date']; ?></strong></td>
        </tr>
        <tr class="no_hover">
        	<td><div id="rx_error" class="notice_red_dur" style="display: none;"></div></td>
        </tr>
        <tr class="no_hover">
        	<td><?php echo $rx['EmdeonPrescription']['drug_name']; ?></td>
        </tr>
        <tr class="no_hover">
        	<td><?php echo $rx['EmdeonPrescription']['sig']; ?></td>
        </tr>
        <tr class="no_hover">
        	<td>Quantity: <?php echo $rx['EmdeonPrescription']['quantity']; ?>&nbsp;&nbsp;&nbsp;&nbsp;Refills: <?php echo $rx['EmdeonPrescription']['refills']; ?></td>
        </tr>
        <tr class="no_hover">
        	<td><?php echo $rx['EmdeonPrescription']['pharmacy_name']; ?></td>
        </tr>
        <tr class="no_hover">
        	<td>
            	 <div class="actions">
                    <ul>
                    	<li><a onclick="print_rx();" href="javascript:void(0);">Print Rx</a></li>
                        <li><a onclick="issue_new_rx();" href="javascript:void(0);">Issue New Rx</a>&nbsp;&nbsp;<span id="submit_swirl" style="display: none;"><?php echo $smallAjaxSwirl; ?></span></li>
                    </ul>
                 </div>
            </td>
        </tr>
    </table>
<?php elseif($task == "view"):
	$mrn = (isset($this->params['named']['mrn'])) ? $this->params['named']['mrn'] : "";
	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	$person = (isset($this->params['named']['person'])) ? $this->params['named']['person'] : "";
	$rx = (isset($this->params['named']['rx_ref'])) ? $this->params['named']['rx_ref'] : "";
	$prescription_id = (isset($this->params['named']['prescription_id'])) ? $this->params['named']['prescription_id'] : "";

	$discontinue_rx_url = $html->url(array('mrn' => $mrn, 'patient_id' => $patient_id, 'person' => $person, 'rx' => $rx, 'prescription_id' => $prescription_id, 'task' => 'discontinue', 'encounter_id' => $encounter_id)) . '/';
	$void_rx_url = $html->url(array('mrn' => $mrn, 'patient_id' => $patient_id, 'person' => $person, 'rx' => $rx, 'prescription_id' => $prescription_id, 'task' => 'void', 'encounter_id' => $encounter_id)) . '/';
	$renew_rx_url = $html->url(array('mrn' => $mrn, 'patient_id' => $patient_id, 'person' => $person, 'rx' => $rx, 'prescription_id' => $prescription_id, 'task' => 'renew', 'encounter_id' => $encounter_id)) . '/';
	$authorize_rx_url = $html->url(array('mrn' => $mrn, 'patient_id' => $patient_id, 'person' => $person, 'rx' => $rx, 'prescription_id' => $prescription_id, 'task' => 'authorize', 'encounter_id' => $encounter_id)) . '/';
 	
	//echo $authorize_rx_url;
	?>
	<script language="javascript" type="text/javascript">
	function adjustIframeHeight(h)
	{
		$("#frmPrintManifest").css("height", h + "px");
	}

	function printPage()
	{
		window.frames['frmPrintManifest'].focus(); 
		document.getElementById('frmPrintManifest').contentWindow.printPage();
	}
	
	$('#voidRxBtn').click(function()
	{            
		 getJSONDataByAjax(
			'<?php echo $void_rx_url; ?>', 
			'', 
			function(){}, 
			function(data){
				if(typeof loadElectronicRxTables == 'function')
				{
					loadElectronicRxTables();
				}
				loadRxElectronicTable(data.redir_link);
			});
	 });
	 
	$('#discontinueRxBtn').click(function(){            
		 var denial_reason = $('#discontinue_reason').val();
		 
		 var form = $("<form></form>");

		 form.append('<input type="hidden" name="data[denial_reason]" id="denial_reason" value="'+denial_reason+'" />');
		 
		 getJSONDataByAjax(
			'<?php echo $discontinue_rx_url; ?>', 
			form.serialize(), 
			function(){}, 
			function(data){
				if(typeof loadElectronicRxTables == 'function')
				{
					loadElectronicRxTables();
				}
				loadRxElectronicTable(data.redir_link);
			});
	 });
	 
	 $('#renewRxBtn').click(function(){            
		 loadRxElectronicTable('<?php echo $renew_rx_url; ?>');
	 });
	 
	 $('#authorizeRxBtn').click(function() {
		$('#authorizeRxBtn').addClass("button_disabled");
		$('#authorizeRxBtn').unbind('click');
		$('#submit_swirl').show();
		
		getJSONDataByAjax(
			'<?php echo $authorize_rx_url; ?>', 
			$('#frmElectronicOrder').serialize(), 
			function(){}, 
			function(data) {
				$('#submit_swirl').hide();
				$('#btnIssueRx').click(initiateIssueRx);
				if(typeof loadElectronicRxTables == 'function')
				{
					loadElectronicRxTables();
				}
				loadRxElectronicTable(data.redir_link);
			}
		);
	 });
	 
	function cancel_view()
	{
		if($('.section_btn[labtype="Rx"]').length > 0)
		{
			$('.section_btn[labtype="Rx"]').click();
		}
		else
		{
			loadRxElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_eprescribing_rx', 'mrn' => $mrn, 'patient_id' => $patient_id, 'from_patient' => 1)); ?>');
		}
	}
	</script>
	<table width="100%">
    <tr class="no_hover">
        <td><strong>Rx Details</strong>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <!--
	<tr class="no_hover">
        <td> 
		
		<?php
            if($rx_details['rx_status'] == "Pending")
            {
				echo '<span id="authorizeRxBtn" class="btn" >Authorize</span>';
			}
            ?>
            
            
            <span id="renewRxBtn" class="btn" >Renew</span>
			<?php 
            if($rx_details['rx_status'] == "Authorized")
            {
				echo '<span id="voidRxBtn" class="btn" >Void</span>';
			}
			?>
            <?php 
            if($rx_details['rx_status'] == "Authorized" and $rx_details['rx_status'] != "Discontinued")
            {
				echo '<span id="discontinueRxBtn" class="btn" >Discontinue</span>';
			}
			?>
            </td>

    </tr>
    -->
    </table>
    <table width="100%">
    <?php
    if($rx_details['rx_status'] == "Authorized" and $rx_details['rx_status'] != "Discontinued")
    {
    ?>
    <tr class="no_hover">
        <td valign=top>Reason to Discontinue:</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <?php
    }
    ?>
    <tr class="no_hover">
        <td width="20%" height="25" valign=top>Status:</td>
        <td><?php 
		echo $rx_details['rx_status']; 
		if($rx_details['rx_status'] == "Discontinued" and $rx_details['denial_reason']!='') 
		{
		    echo " [".$rx_details['denial_reason']."]";
		}
		?>
        <input type="hidden" id="discontinue_reason" name="discontinue_reason"/></td>
    </tr>
    <tr class="no_hover">
        <td valign=top width="20%">Diagnosis:</td>
        <td><?php echo $rx_details['icd_9_cm_code']; ?></td>
    </tr>
    <tr class="no_hover">
        <td valign=top width="20%">Drug:</td>
              <td><?php echo $rx_details['drug_name']; ?></td>
    </tr>
    <tr class="no_hover">
        <td valign=top>SIG:</td>
        <td><?php echo $rx_details['sig']; ?></td>
    </tr>
    <tr class="no_hover">
        <td valign=top>Quantity:</td>
        <td><?php echo $rx_details['quantity']; ?></td>
    </tr>
    <tr class="no_hover">
        <td valign=top>Units of Measure:</td>
        <td>
        	<?php 
			$unit_of_measure_text = "";
			foreach($unit_of_measures as $unit_of_measure): 
				if($rx_details['unit_of_measure']==$unit_of_measure['code'])
				{
					$unit_of_measure_text = $unit_of_measure['description'];
				}
			endforeach;
			?>
			<?php echo $unit_of_measure_text; ?>
        </td>
    </tr>
    <tr class="no_hover">
        <td valign=top>Days Supply:</td>
        <td><?php echo $rx_details['days_supply']; ?></td>
    </tr>
    <tr class="no_hover">
        <td valign=top>Refills:</td>
        <td>
		<?php echo $rx_details['refills']; ?> <?php if($rx_details['daw']=='y'): ?>(Dispense as written)<?php endif; ?></td>
    </tr>
    <tr class="no_hover">
        <td>Start Date:</td>
        <td><?php echo $rx_details['created_date']; ?></td>
    </tr> 
    <tr class="no_hover">
        <td style="vertical-align:top;">Comment:</td>
        <td><?php echo $rx_details['comments']; ?></td>
    </tr>
    <tr class="no_hover"><td>Pharmacy:</td>
        <td><?php echo $rx_details['pharmacy_name']; ?></td>
    </tr>
    <tr class="no_hover"><td>Method:</td>
        <td><?php echo $rx_details['rx_issue_type']; ?></td>
    </tr>
    <tr class="no_hover">
        <td style="vertical-align:top;">Prescriber:</td>
        <td ><?php echo $rx_details['prescriber_name']; ?></td>
    </tr>
    <tr class="no_hover">
        <td style="vertical-align:top;">Supervisor:</td>
        <td ><?php echo $rx_details['supervising_prescriber_name']; ?></td>
    </tr>
    <tr class="no_hover">
        <td colspan="2">
            <div class="actions">
                <ul>
                	<li><a href="javascript: void(0);" id="authorizeRxBtn">Authorize</a></li>
                    <li><a href="javascript:void(0);" onclick="cancel_view();">Cancel</a>&nbsp;&nbsp;<span id="submit_swirl" style="display: none;"><?php echo $smallAjaxSwirl; ?></span></li>
                </ul>
             </div>
         </td>
    </tr>
    </table>
</form>
    <table id="table_loading_rxdetails" width="100%" border="0" cellspacing="0" cellpadding="0" class="no_padding" style="margin-top: 20px; display: none;">
        <tr class="no_hover">
            <td align="center"><img src="gui/images/loading_big.gif" width="160" height="24" /></td>
        </tr>
    </table>

<?php else: 
?>  
<script language="javascript" type="text/javascript">
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
function addIcd9SearchData(data)
{
	var icd9s = $("#tableICD9").data('data');
	
	var found = false;
	
	for(var i = 0; i < icd9s.length; i++)
	{
		if(icd9s[i]['icd_9_cm_code'] == data['icd_9_cm_code'])
		{
			found = true;
		}
	}
	
	if(!found)
	{
		icd9s[icd9s.length] = data;
	}
	
	$("#tableICD9").data('data', icd9s);
	resetIcd9Table();
}
		
function resetPharmacyPreferenceTable(data)
{
				
	$("#table_fav_pharmacy_list tr").each(function()
	{
		if($(this).attr("deleteable") == "true")
		{
			$(this).remove();
		}
	});
	
	if(data.emdeon_pharmacy_id != '0')
	{
		/*
		for(var i = 0; i < data.length; i++)
		{
			//alert('city'+data[i].EmdeonFavoritePharmacy.pharmacy_city);
			var html = '<tr deleteable="true" pharmacy_id="'+data[i].EmdeonFavoritePharmacy.pharmacy_id+'" pharmacy_name="'+data[i].EmdeonFavoritePharmacy.pharmacy_name+'" pharmacy_address_1="'+data[i].EmdeonFavoritePharmacy.pharmacy_address_1+'" pharmacy_address_2="'+data[i].EmdeonFavoritePharmacy.pharmacy_address_2+'" pharmacy_city="'+data[i].EmdeonFavoritePharmacy.pharmacy_city+'" pharmacy_state="'+data[i].EmdeonFavoritePharmacy.pharmacy_state+'" pharmacy_phone="'+data[i].EmdeonFavoritePharmacy.pharmacy_phone+'" pharmacy_zip="'+data[i].EmdeonFavoritePharmacy.pharmacy_zip+'">';
			html += '<td width=15 >';
			html += '<span class="add_icon"><?php echo $html->image('add.png', array('alt' => '')); ?></span>';					
			html += '</td>';
			html += '<td>'+data[i].EmdeonFavoritePharmacy.pharmacy_name+'</td>';
			html += '</tr>';
			
			$("#table_fav_pharmacy_list").append(html);
		}
		*/
		
		// Populate patient favorite pharmacy
		var html = '<tr deleteable="true" pharmacy_id="'+data.emdeon_pharmacy_id+'" pharmacy_name="'+data.pharmacy_name+'" pharmacy_address_1="'+data.address_1+'" pharmacy_address_2="'+data.address_2+'" pharmacy_city="'+data.city+'" pharmacy_state="'+data.state+'" pharmacy_phone="'+data.phone_number+'" pharmacy_zip="'+data.zip_code+'">';
		html += '<td width=15 >';
		html += '<span class="add_icon"><?php echo $html->image('add.png', array('alt' => '')); ?></span>';					
		html += '</td>';
		html += '<td>'+data.pharmacy_name+'</td>';
		html += '</tr>';
		
		$("#table_fav_pharmacy_list").append(html);
		
		$("#table_fav_pharmacy_list tr:even td").addClass("striped");				

		$("#table_fav_pharmacy_list tr").each(function()
		{
			$(this).attr("oricolor", "");
		});
		
		$("#table_fav_pharmacy_list tr:even").each(function()
		{
			$(this).attr("oricolor", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
			$(this).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
		});
		
		$("#table_fav_pharmacy_list tr").not('#table_fav_pharmacy_list tr:first').each(function()
		{
			$(this).click(function()
			{
				var pharmacy_id = $(this).attr("pharmacy_id");
				var pharmacy_name = $(this).attr("pharmacy_name");
				var address_1 = $(this).attr("pharmacy_address_1");
				var address_2 = $(this).attr("pharmacy_address_2");
				var city = $(this).attr("pharmacy_city");
				var state = $(this).attr("pharmacy_state");
				var phone = $(this).attr("pharmacy_phone");
				var zip = $(this).attr("pharmacy_zip");

				$('#pharmacy_id').val(pharmacy_id);
				$('#address_1').val(address_1);
				$('#address_2').val(address_2);
				$('#city').val(city);
				$('#state').val(state);	
				$('#phone').val(phone);	
				$('#zip').val(zip);	
				$('#issue_to').val(pharmacy_name);
				$('#issue_to').removeClass("error");
				$('.error[htmlfor="issue_to"]').remove();
		
			});
			
			$('td', $(this)).each(function()
			{
				$(this).css("cursor", "pointer");
				
				$(this).mouseover(function()
				{
					var parent_tr = $(this).parent();
					
					$('td', parent_tr).each(function()
					{
						$(this).attr("prev_color", $(this).css("background"));
						$(this).css("background", "#FDF5C8");
					});
				}).mouseout(function()
				{
					var parent_tr = $(this).parent();
					
					$('td', parent_tr).each(function()
					{
						$(this).css("background", $(this).attr("prev_color"));
						$(this).attr("prev_color", "");
					});
				});
			});
		});
	}
	else
	{
		var html = '<tr deleteable="true">';
		html += '<td colspan="2">None</td>';
		html += '</tr>';
		
		$("#table_fav_pharmacy_list").append(html);
	}
}

function resetDrugPreferenceTable(data)
{			
	$("#table_fav_drug_list tr").each(function()
	{
		if($(this).attr("deleteable") == "true")
		{
			$(this).remove();
		}
	});
	if(data.length > 0)
	{
		for(var i = 0; i < data.length; i++)
		{
			//alert('drug'+data[i].EmdeonFavoritePrescription.drug_name);
			var html = '<tr deleteable="true" sig="'+data[i].EmdeonFavoritePrescription.sig+'" quantity="'+data[i].EmdeonFavoritePrescription.quantity+'" days_supply="'+data[i].EmdeonFavoritePrescription.days_supply+'" refills="'+data[i].EmdeonFavoritePrescription.refills+'" unit_of_measure="'+data[i].EmdeonFavoritePrescription.unit_of_measure+'" daw="'+data[i].EmdeonFavoritePrescription.daw+'" dose_type="'+data[i].EmdeonFavoritePrescription.dose_type+'" single_dose_amount="'+data[i].EmdeonFavoritePrescription.single_dose_amount+'" frequency="'+data[i].EmdeonFavoritePrescription.frequency+'" dose_unit="'+data[i].EmdeonFavoritePrescription.dose_unit+'" drug_id="'+data[i].EmdeonFavoritePrescription.drug_id+'" drug_name="'+data[i].EmdeonFavoritePrescription.drug_name+'" deacode="'+data[i].EmdeonFavoritePrescription.deacode+'">';
			html += '<td width=15 >';
			html += '<span class="add_icon"><?php echo $html->image('add.png', array('alt' => '')); ?></span>';					
			html += '</td>';
			html += '<td>'+data[i].EmdeonFavoritePrescription.drug_name+'</td>';
			html += '<td width=15>';
			html += '<a class="del_icon" rx_preference_id="'+data[i].EmdeonFavoritePrescription.rx_preference_id+'"><?php echo $html->image('del.png', array('alt' => '')); ?></a>';					
			html += '</td>';
			html += '</tr>';
			
			$("#table_fav_drug_list").append(html);
		}
		
		$("#table_fav_drug_list tr:even td").addClass("striped");				

		$("#table_fav_drug_list tr").each(function()
		{
			$(this).attr("oricolor", "");
		});
		
		$("#table_fav_drug_list tr:even").each(function()
		{
			$(this).attr("oricolor", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
			$(this).css("background-color", "<?php echo $display_settings['color_scheme_properties']['table_stripped']; ?>");
		});
		
		$("#table_fav_drug_list tr").not('#table_fav_drug_list tr:first').each(function()
		{
			$(this).click(function()
			{				
				//$('#submit_swirl_drug').show();	
				
				var drug_id = $(this).attr("drug_id");	
				var drug_name = $(this).attr("drug_name");
				var deacode = $(this).attr("deacode");
				var sig = $(this).attr("sig");
				var quantity = $(this).attr("quantity");
				var days_supply = $(this).attr("days_supply");
				var refills = $(this).attr("refills");
				var unit_of_measure = $(this).attr("unit_of_measure");
				var daw = $(this).attr("daw");
				var dose_unit = $(this).attr("dose_unit");
				var dose_type = $(this).attr("dose_type");
				var single_dose_amount = $(this).attr("single_dose_amount");
				var frequency = $(this).attr("frequency");
				
				processDEACode($(this).attr("deacode"));
				
				$('#drug_id').val(drug_id);
				$('#drug_name').val(drug_name);
				$('#deacode').val(deacode);
				$('#sig').val(sig);
				$('#quantity').val(quantity);
				$('#days_supply').val(days_supply);
				$('#refills').val(refills);
				$('select[id=unit_of_measure] option[value='+unit_of_measure+']').attr('selected', 'true');
				$('select[id=daw] option[value='+daw+']').attr('checked', 'true');
				$('select[id=dose_unit] option[value='+dose_unit+']').attr('selected', 'true');
				$('select[id=dose_type] option[value='+dose_type+']').attr('selected', 'true');
				$('#single_dose_amount').val(single_dose_amount);
				$('#frequency').val(frequency);
				
				var encounter_id = '<?php echo $encounter_id; ?>';

				var icd_9_cm_code = $('#icd_9_cm_code').val();
				var age = $('#age').val();
				var mrn = $('#mrn').val();
			 
				/*var formobj = $("<form></form>");
				formobj.append('<input name="data[encounter_id]" type="hidden" value="'+encounter_id+'">');
				formobj.append('<input name="data[drug_name]" type="hidden" value="'+drug_name+'">');
				formobj.append('<input name="data[drug_id]" type="hidden" value="'+drug_id+'">');
				formobj.append('<input name="data[icd_9_cm_code]" type="hidden" value="'+icd_9_cm_code+'">');
				formobj.append('<input name="data[age]" type="hidden" value="'+age+'">');
				formobj.append('<input name="data[mrn]" type="hidden" value="'+mrn+'">');
				$.post('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:dur_warning/', 
				formobj.serialize(), 
				function(data)
				{
					if(data != '')
					{
						 var reaction = data[0]['reaction'];
						 var severity = data[0]['severity'];
						 var data_link = reaction+'&nbsp;'+'<a id= "hide_drug" href="javascript:void(0);" class="smallbtn">Override</a>';
						 change_divclass(severity);
						 $('#dur_warning_message').html(data_link);	
						 $('.dur_warning').show();
						 //$('#drug_search_data_area').hide();
						// $('#submit_swirl').hide();
						 $("#hide_drug").click(function()
						{
							$('.dur_warning').hide();
						});
					}
					//$('#drug_search_data_area').hide();
					$.post('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:dur_allergy_warning/', 
							formobj.serialize(), 
							function(data)
							{
							   var data_split=data.split("|");
							   var reaction_array = data_split[0];
							   var reaction = data_split[0];
							   var severity = data_split[1];
							   change_divclass_allergy(severity)
							   var data_link = reaction+'&nbsp;'+'<a id="hide_allergy" href="javascript:void(0);" class="smallbtn">Override</a>';
							   $('#dur_allergy_warning_message').html(data_link);	
								 if(data != '')
								 {
									 $('.dur_allergy_warning').show();
									 //$('#drug_search_data_area').hide();
									 $('#submit_swirl_drug').hide();
									 $("#hide_allergy").click(function()
									 {
										$('.dur_allergy_warning').hide();
									 });
								 }
								 $('#drug_search_data_area').hide();
								 $('#submit_swirl_drug').hide();
						   },
						   'json'
						   );
					
				},
				'json'
				);*/

				$('#drug_name').removeClass("error");
				$('.error[htmlfor="drug_name"]').remove();
				
				executeDrugSearchformulary('');
				$("#drug_search_result_area_formulary").css('display', 'block');
				$("#drug_search_result_area_formularys").css('display', 'block');
				
				
		
			});

			
			$('td', $(this)).each(function()
			{
				$(this).css("cursor", "pointer");
				
				$(this).mouseover(function()
				{
					var parent_tr = $(this).parent();
					
					$('td', parent_tr).each(function()
					{
						$(this).attr("prev_color", $(this).css("background"));
						$(this).css("background", "#FDF5C8");
					});
				}).mouseout(function()
				{
					var parent_tr = $(this).parent();
					
					$('td', parent_tr).each(function()
					{
						$(this).css("background", $(this).attr("prev_color"));
						$(this).attr("prev_color", "");
					});
				});
			});


			$('.del_icon', $(this)).each(function()
			{
			    $(this).click(function(e)
			    {
			        e.stopPropagation();
			        var rx_preference_id = $(this).attr('rx_preference_id');
				$('#imgLoadDrugPreference').show();
				var formobj = $("<form></form>");
				formobj.append('<input name="data[rx_preference_id]" id="rx_preference_id" type="hidden" value="'+rx_preference_id+'">');
				$.post(
					'<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:delete_favorite_prescriptions/',
					formobj.serialize(),
					function(data)
					{
						resetDrugPreferenceTable(data);
						$('#imgLoadDrugPreference').hide();
					},
					'json'
				);
			    });
			});

		});

	}
	else
	{
		var html = '<tr deleteable="true">';
		html += '<td colspan="2">None</td>';
		html += '</tr>';
		
		$("#table_fav_drug_list").append(html);
	}
}

function get_single_favorite_prescriptions()
{
	
		rx_preference_id = $(this).find('option:selected').attr("rx_preference_id");
		icd_9_cm_code = $(this).find('option:selected').attr("icd_9_cm_code");
		//alert('rx_preference_id:'+rx_preference_id);
		if(rx_preference_id != '')
		{
			$('#drug_id').val('');
			$('#drug_name').val('');
			$('#sig').val('');
			$('#icd_9_cm_code').val(icd_9_cm_code);
			var formobj = $("<form></form>");
			formobj.append('<input name="data[rx_preference_id]" id="rx_preference_id" type="hidden" value="'+rx_preference_id+'">');
			
			$.post('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:get_single_favorite_prescriptions/', 
			formobj.serialize(), 
			function(data)
			{
				if(data.length > 0)
				{    
					for(var i = 0; i < data.length; i++)
					{
						drug_id = data[i].EmdeonFavoritePrescription.drug_id;
						drug_name = data[i].EmdeonFavoritePrescription.drug_name;
						sig = data[i].EmdeonFavoritePrescription.sig;
						$('#drug_id').val(drug_id);
						$('#drug_name').val(drug_name);
						$('#sig').val(sig);
				
					}	
				}		
			},
			'json'
			);
		}
		
}

function change_divclass(severity)
{

	 var NAME = document.getElementById("dur_warning_message");
	 var currentClass = NAME.className;
	 if (severity == 1) 
	 { 
		NAME.className = "notice_red_dur dur_warning";   // Set other class name
	 } 
	 else if (severity == 2) 
	 {
		NAME.className = "notice_yellow_dur dur_warning";  // Otherwise, use `second_name`
	 }
	 else 
	 {
		NAME.className = "notice_green_dur dur_warning";  // Otherwise, use `third_name`
	 }
}   

function change_divclass_allergy(severity)
{

	 var NAME = document.getElementById("dur_allergy_warning_message");
	 var currentClass = NAME.className;
	 
	 if (severity == 'SEVERE') 
	 { 
		NAME.className = "notice_red_dur dur_allergy_warning";   // Set other class name
	 } 
	 else if (severity == 'MODERATE') 
	 {
		NAME.className = "notice_yellow_dur dur_allergy_warning";  // Otherwise, use `second_name`
	 }
	 else 
	 {
		NAME.className = "notice_green_dur dur_allergy_warning";  // Otherwise, use `third_name`
	 }
}

function dur_warning()
{
	$('#submit_swirl_drug').show();
	var encounter_id = '<?php echo $encounter_id; ?>';
	var drug_name = $('#drug_name').val();
	var drug_id = $('#drug_id').val();
	var icd_9_cm_code = $('#icd_9_cm_code').val();
	var age = $('#age').val();
	var mrn = $('#mrn').val();
	var formobj = $("<form></form>");
	formobj.append('<input name="data[encounter_id]" type="hidden" value="'+encounter_id+'">');
	formobj.append('<input name="data[drug_name]" type="hidden" value="'+drug_name+'">');
	formobj.append('<input name="data[drug_id]" type="hidden" value="'+drug_id+'">');
	formobj.append('<input name="data[icd_9_cm_code]" type="hidden" value="'+icd_9_cm_code+'">');
	formobj.append('<input name="data[age]" type="hidden" value="'+age+'">');
	formobj.append('<input name="data[mrn]" type="hidden" value="'+mrn+'">');
	$.post('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:dur_warning/', 
	formobj.serialize(), 
	function(data)
	{	
		if(data != '')
		{
			var reaction = data[0]['reaction'];
			var severity = data[0]['severity'];
			var data_link = reaction+'&nbsp;'+'<a id= "hide_drug" href="javascript:void(0);" class="smallbtn">Override</a>';
			change_divclass(severity);
			$('#dur_warning_message').html(data_link);
			$('.dur_warning').show();
			//$('#drug_search_data_area').hide();
			//$('#submit_swirl').hide();
			$("#hide_drug").click(function()
			{
				$('.dur_warning').hide();
			});
		}
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:dur_allergy_warning/', 
				formobj.serialize(), 
				function(data)
				{
					
					 if(data != '')
					 {
						 var data_split=data.split("|");
						 var reaction = data_split[0];
						 var severity = data_split[1];
						 change_divclass_allergy(severity);
						 var data_link = reaction+'&nbsp;'+'<a id="hide_allergy" href="javascript:void(0);" class="smallbtn">Override</a>';
						 $('#dur_allergy_warning_message').html(data_link);
						 $('.dur_allergy_warning').show();
						 //$('#drug_search_data_area').hide();
						 $('#submit_swirl_drug').hide();
						 $("#hide_allergy").click(function()
						 {
							$('.dur_allergy_warning').hide();
						 });
					 }
					$('#drug_search_data_area').hide();
					$('#drug_search_data_area_formulary').hide();
					$('#submit_swirl_drug').hide();
			   },
			   'json'
			   );
		
		
		
	},
	'json'
	);

 }

function resetDrugPreference(items, prescriber_id)
{
	//var diagnosis = $("#table_plans_table").attr("planname");
	//$('#diagnosis').html('<option value=""></option>');
	$('#imgLoadDrugPreference').show();
	var formobj = $("<form></form>");
	formobj.append('<input name="data[prescriber_id]" id="prescriber_id" type="hidden" value="'+prescriber_id+'">');
	if(items == null)
	{
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:get_favorite_prescriptions/',
			formobj.serialize(),
			function(data)
			{
				resetDrugPreferenceTable(data);
				$('#imgLoadDrugPreference').hide();
			},
			'json'
		);
	}
	else
	{
		resetDrugPreferenceTable(items);
		$('#imgLoadDrugPreference').hide();
	}
}

function resetPharmacyPreference(items, prescriber_id)
{
	//var diagnosis = $("#table_plans_table").attr("planname");
	//$('#diagnosis').html('<option value=""></option>');
	$('#imgLoadPharmacyPreference').show();
	var formobj = $("<form></form>");
	formobj.append('<input name="data[prescriber_id]" id="prescriber_id" type="hidden" value="'+prescriber_id+'">');
	if(items == null)
	{
		$.post(
			'<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:get_favorite_pharmacy/', 
			formobj.serialize(), 
			function(data)
			{
				resetPharmacyPreferenceTable(data);
				$('#imgLoadPharmacyPreference').hide();
			},
			'json'
		);
	}
	else
	{
		resetPharmacyPreferenceTable(items);
		$('#imgLoadPharmacyPreference').hide();
	}
}
function validateRxForm()
{
	var valid = true;
	var drug_name = $('#drug_name').val();
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
	
	$('#issue_to').removeClass("error");
	$('.error[htmlfor="issue_to"]').remove();
	
	$('#rx_issue_type').removeClass("error");
	$('.error[htmlfor="rx_issue_type"]').remove();
	
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
		$('#refills').after('<div style="margin-left:5px;" htmlfor="refills" generated="true" class="error">This field is required.</div>');
		valid = false;
	}
	
	if($('#issue_to').val() == "" && !$('#issue_to').is(':disabled'))
	{
		$('#issue_to').addClass("error");
		$('#issue_to').after('<div htmlfor="issue_to" generated="true" class="error">This field is required.</div>');
		valid = false;
	}
	
	if($('#rx_issue_type').val() == "")
	{
		$('#rx_issue_type').addClass("error");
		$('#rx_issue_type').after('<div htmlfor="rx_issue_type" generated="true" class="error">This field is required.</div>');
		valid = false;
	}

	return valid;
}
		
function initiateIssueRx()
{
	
	//collectOrderData();
	if(validateRxForm())
	{
		$('#btnIssueRx').addClass("button_disabled");
		//$('#btnIssueRx').unbind('click');
		$('.submit_swirl_issue').show();
		
		<?php
		$issue_rx_url = $html->url(array('task' => 'issue_rx', 'from_patient' => $from_patient, 'patient_id' => $patient_id, 'encounter_id' => $encounter_id));
		?>
		getJSONDataByAjax(
			'<?php echo $issue_rx_url; ?>', 
			$('#frmElectronicOrder').serialize(), 
			function(){}, 
			function(data)
			{
				$('#btnIssueRx').click(initiateIssueRx);
				if(typeof loadElectronicRxTables == 'function')
				{
					loadElectronicRxTables();
				}
				
				loadRxElectronicTable(data.redir_link, data.error);

			}
			
		);
	}
}

function initiateHoldRx()
{
   // collectOrderData();
	if(validateRxForm())
	{
		$('#btnHoldRx').addClass("button_disabled");
		$('#btnHoldRx').unbind('click');
		$('#submit_swirl').show();
		 
		<?php
		$hold_rx_url = $html->url(array('task' => 'hold_rx', 'from_patient' => 1, 'patient_id' => $patient_id, 'encounter_id' => $encounter_id));
		?>
		getJSONDataByAjax(
			'<?php echo $hold_rx_url; ?>', 
			$('#frmElectronicOrder').serialize(), 
			function(){}, 
			function(data)
			{
				$('#btnHoldRx').removeClass("button_disabled");
				$('#btnHoldRx').click(initiateIssueRx);
				$('#submit_swirl').hide();
				
				if(typeof loadElectronicRxTables == 'function')
				{
					loadElectronicRxTables();
				}
				
				loadRxElectronicTable(data.redir_link, data.error);
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

function showDURWarningImages()
{
	var drug_id = $("#drug_id").val();
	var drug_name = $("#drug_name").val();
	var weight = $("#weight").val();
	var organization = $("#organization").val();
	var icd_9_cm_code =  $("#icd_9_cm_code").val();
	var now = new Date();
	
	$("#warning_green").attr('src', 'https://cli-cert.emdeon.com/servlet/DxLogin?userid=p_solismed2&PW=practice00&apiLogin=true&hdnBusiness='+organization+'&target=servlet/rxServlet&level=green&drug_name='+drug_name+'&drug_id='+drug_id+'&icd9='+icd_9_cm_code+'&Weight='+weight+'&unique=' + now.getMilliseconds());
	$("#warning_yellow").attr('src','https://cli-cert.emdeon.com/servlet/DxLogin?userid=p_solismed2&PW=practice00&apiLogin=true&hdnBusiness='+organization+'&target=servlet/rxServlet&level=yellow&drug_name='+drug_name+'&drug_id='+drug_id+'&icd9='+icd_9_cm_code+'&Weight='+weight+'&unique=' + now.getMilliseconds());
	$("#warning_red").attr('src','https://cli-cert.emdeon.com/servlet/DxLogin?userid=p_solismed2&PW=practice00&apiLogin=true&hdnBusiness='+organization+'&target=servlet/rxServlet&level=red&drug_name='+drug_name+'&drug_id='+drug_id+'&icd9='+icd_9_cm_code+'&Weight='+weight+ '&unique=' + now.getMilliseconds());
}

function resetDrugSearch()
{
	$('#drug_name').val('');
	
	$('#drug_search_loading_area').hide();
	$('#drug_search_data_area').hide();
	$('#drug_search_error_area').hide();
	
	if(drug_task != null)
	{
		drug_task.abort();
	}
	
	initAutoLogoff();
}

var drug_task = null;

function executeDrugSearch(url)
{
	initAutoLogoff();
	
	if(drug_task != null)
	{
		drug_task.abort();
	}
	
	var url_to_execute = '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'drug_search')); ?>';
	
	if(url)
	{
		url_to_execute = url;
	}
	
	$('#drug_search_loading_area').show();
	$('#drug_search_data_area').hide();
	$('#drug_search_error_area').hide();
	
	
	if($.trim($('#drug_name').val()) == "")
	{
		$('#drug_search_error_area').show();
		$('#drug_search_loading_area').hide();
		$('#drug_search_data_area').hide();
	}
	else
	{
		drug_task = $.post(
			url_to_execute, 
			{'data[name]': $('#drug_name').val()}, 
			function(html)
			{
				initDrugTable(html);
			}
		);
	}
}
	
function convertDrugSearchLink(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).click(function()
	{
		executeDrugSearch(href);
	});
}


function executeDrugSearchformulary(url)
{
	initAutoLogoff();
	
	if(drug_task != null)
	{
		drug_task.abort();
	}
	var drug_id = $('#drug_id').val();
	var mrn = '<?php echo $mrn; ?>';
	var test = '/drug_id:'+drug_id+'/mrn:'+mrn;
	var url_to_executes= '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'drug_search_formulary')); ?>';
	var url_to_execute = url_to_executes+test;
	if(url)
	{
		url_to_execute = url;
	}
	
	$('#drug_search_loading_area').show();
	$('#drug_search_data_area_formulary').hide();
	$('#drug_search_error_area').hide();
	
	
	if($.trim($('#drug_name').val()) == "")
	{
		$('#drug_search_error_area').show();
		$('#drug_search_loading_area_formulary').hide();
		$('#drug_search_data_area_formulary').hide();
	}
	else
	{
			drug_task = $.post(
				url_to_execute, 
				{'data[name]': $('#drug_name').val()}, 
				function(html)
				{
					initDrugTableFormulary(html);
				}
			);
	}
}
			
function convertDrugSearchLinkformulary(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).click(function()
	{
		executeDrugSearchformulary(href);
	});
}		
		
function initDrugTableFormulary(html)
{
	$('#drug_search_loading_area').hide();
	$('#drug_search_data_area_formulary').show();
	$('#submit_swirl_drug').hide();
	$('#drug_search_result_area_formulary').html(html);
	
	$('#drug_search_result_area_formulary a.ajax').each(function()
	{
		convertDrugSearchLinkformulary(this);
	});
	
	$('#drug_search_result_area_formulary .paging a').each(function()
	{
		convertDrugSearchLinkformulary(this);
	});
	
	
	$(".master_chk", $('#dialogSearchDrug')).click(function() 
	{
		if($(this).is(':checked'))
		{
			$('.child_chk', $('#dialogSearchDrug')).attr('checked','checked');
		}
		else
		{
			$('.child_chk', $('#dialogSearchDrug')).removeAttr('checked');

		}
	});
	
	$('.child_chk', $('#dialogSearchDrug')).click( function() 
	{
		if(!$(this).is(':child_chk'))
		{
			$('.master_chk', $('#dialogSearchDrug')).removeAttr('checked');
		}
	});

	$('#drug_item_formulary tr[class ="drug_value_selected"]').click(function()
	{
		$('.child_chk', $('#frmDrugSearchResultFormularyGrid')).each(function()
		{
			if($(this).is(':checked'))
			{
				var parent_tr = $(this).parents('tr');
				var description = parent_tr.attr("drug_name");

				var id = parent_tr.attr("drug_id");
				
				$('#drug_id').val(id);
				$('#drug_name').val(description);
				$('#deacode').val(parent_tr.attr("deacode"));
				$('#drug_name').removeClass("error");
                $('.error[htmlfor="drug_name"]').remove();
				$('#dialogSearchDrug').slideUp('slow');
				$('#drug_search_data_area_formulary').hide();
				$('#submit_swirl_drug').hide();
				
				processDEACode(parent_tr.attr("deacode"));
				//selectDrugSearchItem(current_item_arr);
			}
		});
		
		dur_warning();

	});
	
	
	
}

function processDEACode(code)
{
	if(code == 0)
	{
		$('#rx_issue_type option[value="ELECTRONIC"]').show();
		$('#rx_issue_type option[value="ELECTRONIC/PRINT"]').show();
		
		$('#rx_issue_type').val('ELECTRONIC');
	}
	else
	{
		$('#rx_issue_type option[value="ELECTRONIC"]').hide();
		$('#rx_issue_type option[value="ELECTRONIC/PRINT"]').hide();
		
		$('#rx_issue_type').val('PRINT');
	}
	
	$('#rx_issue_type').change();
}	
		
function initDrugTable(html)
{
	$('#drug_search_loading_area').hide();
	$('#drug_search_data_area').show();
	
	$('#drug_search_result_area').html(html);
	
	$('#drug_search_result_area a.ajax').each(function()
	{
		convertDrugSearchLink(this);
	});
	
	$('#drug_search_result_area .paging a').each(function()
	{
		convertDrugSearchLink(this);
	});
	
	$(".master_chk", $('#dialogSearchDrug')).click(function() 
	{
		if($(this).is(':checked'))
		{
			$('.child_chk', $('#dialogSearchDrug')).attr('checked','checked');
		}
		else
		{
			$('.child_chk', $('#dialogSearchDrug')).removeAttr('checked');

		}
	});
	
	$('.child_chk', $('#dialogSearchDrug')).click( function() 
	{
		if(!$(this).is(':checked'))
		{
			$('.master_chk', $('#dialogSearchDrug')).removeAttr('checked');
		}
	});

	$('#drug_item tr[class="drug_value_select"]').click(function() {
		$('.child_chk', $('#frmDrugSearchResultGrid')).each(function()
		{
		   //$('#submit_swirl').show();
		   
			if($(this).is(':checked'))
			{
				var parent_tr = $(this).parents('tr');

				var description = parent_tr.attr("name");

				var id = parent_tr.attr("id");

				$('#drug_id').val(id);
				$('#drug_name').val(description);
				$('#drug_name').removeClass("error");
                $('.error[htmlfor="drug_name"]').remove();
				$('#dialogSearchDrug').slideUp('slow');
				$('#drug_search_data_area').hide();
				$('#deacode').val(parent_tr.attr("deacode"));
				
				processDEACode(parent_tr.attr("deacode"));
			}
		});
		
		//dur_warning();
		executeDrugSearchformulary('');
		$("#drug_search_result_area_formulary").css('display', 'block');
		$("#drug_search_result_area_formularys").css('display', 'block');
	});
}

function diagnosis_icd()
{	
	$("#<?php echo $icd_var; ?>").val($('#table_plans_table').attr("icd"));	
	
	// Get Emdeon translated ICD
	/*
	getJSONDataByAjax(
		'<?php echo $html->url(array('task' => 'get_single_icd')); ?>', 
		{'data[icd_cm_code]': $('#table_plans_table').attr("icd"), 'data[required]': 'yes'}, 
		function(){}, 
		function(data)
		{
			if(data.has_data)
			{
				$('#diagnosis').val(data.diagnosis.description);
				$("#<?php echo $icd_var; ?>").val(data.diagnosis.<?php echo $icd_var; ?>);	
			}
			else
			{
				$("#<?php echo $icd_var; ?>").val($('#table_plans_table').attr("icd"));	
			}
		}
	);
	*/
}

		
		$(document).ready(function()
		{	
		   diagnosis_icd();
		   get_single_favorite_prescriptions();
           $("#drug_name").addClear(
			{
				closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
				onClear: function()
				{
					resetDrugSearch();
					$('.dur_warning').hide();
					$('.dur_allergy_warning').hide();
					$('#drug_search_data_area_formulary').hide();
				}
			});    
			
			$('#keep_current_drug').click(function()
             {            
				 $('#drug_search_data_area_formulary').hide();
				 dur_warning();
			 });
											
			$('#drug_name').keyup(function()
			{
				
				var search_string = new String($('#drug_name').val());
				if(search_string.length >= 4)
				{
					executeDrugSearch('');
					$('#drug_search_row').css('display','table-row');
					$('.dur_warning').hide();
					$('.dur_allergy_warning').hide();
					$('#drug_search_data_area_formulary').hide();
				}
				else
				{
					$('#test_search_loading_area').hide();
					$('#test_search_data_area').hide();    
				}
			});
			
			$(".drug_search_field_item").keyup(function(e)
			{
				if(e.keyCode == 13)
				{
					executeDrugSearch('');
				}
				$("#drug_search_result_area").css('display', 'block');
			});

		   $(".show_form_description_fields").css('display', 'none');
		   $(".show_form_pharmacy_fields").css('display', 'none');
			
			$('#drug_details').click(function()
            {
				$("#show_fields_emdeon").css('display', 'block');
				//$("#show_fields_pharmacy").css('display', 'none');
				$(".show_form_pharmacy_fields").css('display', 'none');
				$(".show_search_fields").css('display', 'none');
				$(".show_form_description_fields").css('display', 'none');
				$("#show_prescription_fields").css('display', 'none');
				$(".show_form_description_fields").css('display', 'block');
			});
			
			//Load the favorite drugs of the Physician
			resetDrugPreference(null, "<?php echo $user['clinician_reference_id'].'|'.$user['firstname'].' '.$user['lastname']; ?>");
			
			//Load the favorite pharmacy of the Physician
			resetPharmacyPreference(null, "<?php echo $user['clinician_reference_id'].'|'.$user['firstname'].' '.$user['lastname']; ?>");
			
			$('#btnIssueRx').click(initiateIssueRx);
			
			$('#btnHoldRx').click(initiateHoldRx);
			
			$('#prescriber').change(function()
			{
				if($(this).val() != '')
				{					
					var prescriber_id = $(this).val();
					
					resetDrugPreference(null, prescriber_id);
					resetPharmacyPreference(null, prescriber_id);
				}
				
				$(this).removeClass("error");
                $('.error[htmlfor="prescriber"]').remove();
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
			
			$('#issue_to').change(function()
			{
                $(this).removeClass("error");
                $('.error[htmlfor="issue_to"]').remove();
			});
			
			$('#rx_issue_type').change(function()
			{
                $(this).removeClass("error");
                $('.error[htmlfor="rx_issue_type"]').remove();
				
				if($(this).val() == 'ELECTRONIC' || $(this).val() == 'ELECTRONIC/PRINT')
				{
					$('#imgSearchPharmacyOpen').show();
					$('#issue_to').removeAttr('disabled');
					$('#issue_to_asterisk').css('visibility', 'visible');
				}
				else
				{
					$('#imgSearchPharmacyOpen').hide();
					$('#issue_to').attr('disabled', 'disabled');
					$('#issue_to_asterisk').css('visibility', 'hidden');
				}
			});
			
			$('#rx_issue_type').change();
			
			$('#btnShowRxBuilder').click(function()
			{
				$('#rx_builder_row').css('display','table-row');
			});
			
			$('#lab').change();
			
			$('#btnShowMonograph').click(function()
			{
				$('#div_monograph').html('');
				var drug_id = $('#drug_id').val();
				if(drug_id == "")
				{
					$('#drug_name').addClass("error");
					$('#drug_name').after('<div htmlfor="drug_name" generated="true" class="error">This field is required.</div>');
				}
				else
				{
					$('#monograph_row').css('display','table-row');
					$('#dialogMonograph').slideDown('slow');
					$('#monograph_loading_area').css('display','table-row');
					
					$.post('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:view_monograph/', 
					'drug_id='+drug_id, 
					function(data)
					{
						$('#monograph_loading_area').css('display','none');
						$('#div_monograph').html(data.monograph);
						if(typeof($ipad)==='object')$ipad.ready();
					},
					'json'
					);
				}
			});
			
			$('#btnShowDosage').click(function()
			{
				var drug_id = $('#drug_id').val();
				if(drug_id != "")
				{
				    var html = "<select id='dose_unit' name='dose_unit'><option value=''/>";
				    $.post('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:get_dose_units/', 
					'drug_id='+drug_id, 
					function(data)
					{
						for(var i = 0; i < data.length; i++)
					    {
						    dose_unit = data[i].dose_units;
							html += "<option value="+dose_unit+">"+dose_unit+"</option>";
						}
						html += "</select>";
				    $('#div_dose_unit').html(html);
				    if(typeof($ipad)==='object')$ipad.ready();
					},
					'json'
					);
				    
				}
				var weight = $('#weight').val();
				var weight_unit = $('#weight_unit').val();
				if(weight_unit=='kg')
				{
				    weight = eval(weight*2.2);					
				}
				$('#div_patient_weight').html(weight+' lb');
				$('#patient_weight').val(weight);
				$('#dosage_check_row').css('display','table-row');
				$('#dialogDosageCheck').slideDown('slow');
			});
			
			$('#manual_dosage').keyup(function()
			{
				var patient_weight = $('#patient_weight').val();
				var manual_dosage = $(this).val();
				var calculated_dosage = 0;
				if(manual_dosage!='')
				{
				    calculated_dosage = eval(patient_weight*manual_dosage);					
				}
				$('#calculated_dosage').html(calculated_dosage+' mg');
			});
			
			$('#btnDosageCheck').click(function()
			{
				$('#dosage_search_result_area').html('');
				$("#dosage_search_data_area").css('display', 'none');		
				var age = $('#age').val();
				var drug_id = $('#drug_id').val();
				var drug_name = $('#drug_name').val();
				var dose_type = $('#dose_type').val();
				var single_dose_amount = $('#single_dose_amount').val();
				var dose_unit = $('#dose_unit').val();
				var frequency = $('#frequency').val();
				var encounter_id = '<?php echo $encounter_id; ?>';
				/*if(drug_id == "")
				{
					$('#drug').addClass("error");
					$('#drug').after('<div htmlfor="drug" generated="true" class="error">This field is required.</div>');
				}
				else
				{*/
					$('#dosage_search_loading_area').css('display','table-row');
					
					var formobj = $("<form></form>");
					formobj.append('<input name="data[age]" type="hidden" value="'+age+'">');
		            formobj.append('<input name="data[drug_id]" type="hidden" value="'+drug_id+'">');
					formobj.append('<input name="data[drug_name]" type="hidden" value="'+drug_name+'">');
		            formobj.append('<input name="data[dose_type]" type="hidden" value="'+dose_type+'">');
					formobj.append('<input name="data[single_dose_amount]" type="hidden" value="'+single_dose_amount+'">');
		            formobj.append('<input name="data[dose_unit]" type="hidden" value="'+dose_unit+'">');
					formobj.append('<input name="data[frequency]" type="hidden" value="'+frequency+'">');
					formobj.append('<input name="data[encounter_id]" type="hidden" value="'+encounter_id+'">');
					
					$.post('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/encounter_id:<?php echo $encounter_id; ?>/task:check_dosage/', 
					formobj.serialize(), 
					function(data)
					{
						$('#dosage_search_loading_area').css('display','none');
						var html = '';
						if(data.length > 0)
						{						
							html += data[0].daily_dose_message+'<br>';
							html += data[0].frequency_message+'<br>';
							//html += data[0].max_daily_dose_message+'<br>';
							html += data[0].duration_message+'<br>';
							$('#dosage_search_result_area').html(html);
							if(typeof($ipad)==='object')$ipad.ready();
						}
						else
						{
							$('#dosage_search_result_area').html('No reactions found');
						}	

						
						$("#dosage_search_data_area").css('display', 'table-row');					
					},
					'json'
					);
				//}
			});
		});
	</script>
    
    <?php echo $this->element("rx_icd9_search", array('submit' => 'addIcd9SearchData', 'open' => 'imgIcd9Open', 'container' => 'icd9_search_container', 'input_type' => 'dropdown')); ?>
    <?php echo $this->element("pharmacy_search", array('submit' => 'addTestSearchData', 'open' => 'imgSearchPharmacyOpen', 'container' => 'pharmacy_search_container')); ?>
    <?php
        $diagnosis = '';
		$icd_9_cm_code = '';
		$drug_id = '';
		$drug_name = '';
		$sig = '';
		$daw = '';
		$days_supply = '';
		$refills = '';
		$quantity = '';
		$unit_of_measure = '';
		$comments = '';
		$rx_issue_type = '';		
		$prescriber = '';
		$supervising_prescriber = '';
		$rx_unique_id = '';
		$address_1 = '';
		$address_2 = '';
		$city = '';
		$state = '';
		$phone = '';
		$zip = '';
        $weight = isset($EncounterVital_weight)?$EncounterVital_weight:'';
        
       /* Default patients favorite pharmacy in "Issue To" field*/
        /*if(isset($PatientPreference_items))
        {
            extract($PatientPreference_items);
        }*/
		
		if($PatientPreference_items['PatientPreference']['emdeon_pharmacy_id'] != '0')
		{
			$pharmacy_id = $PatientPreference_items['PatientPreference']['emdeon_pharmacy_id'];
			$pharmacy_name = $PatientPreference_items['PatientPreference']['pharmacy_name'];
			$address_1 = $PatientPreference_items['PatientPreference']['address_1'];
			$address_2 = $PatientPreference_items['PatientPreference']['address_2'];
			$city = $PatientPreference_items['PatientPreference']['city'];
			$state = $PatientPreference_items['PatientPreference']['state'];
			$phone = $PatientPreference_items['PatientPreference']['phone_number'];
			$zip = $PatientPreference_items['PatientPreference']['zip_code'];
		}
		else
		{
			$pharmacy_id = '';
			$pharmacy_name = '';
		}
		
        //$patientpreference_pharmacy_id = $PatientPreference_items['PatientPreference']['pharmacy_id'];
        //$patientpreference_pharmacy_name = $PatientPreference_items['PatientPreference']['pharmacy_name'];
        //$pharmacy_id = isset($patientpreference_pharmacy_id)?$patientpreference_pharmacy_id:'';
        //$pharmacy_name = isset($patientpreference_pharmacy_name)?$patientpreference_pharmacy_name:'';
    ?>
    <form id="frmElectronicOrder" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <input type="hidden" name="data[mrn]" id="mrn" value="<?php echo $mrn; ?>" />
        <input type="hidden" name="data[encounter_id]" id="encounter_id" value="<?php echo $encounter_id; ?>" />
		<input type="hidden" name="rx_data[rx_unique_id]" id="rx_unique_id" value="<?php echo $rx_unique_id; ?>" />
        <?php
		$age=date_diff(date_create($dob), date_create('now'))->y;
	    $age2 = empty($age) ? date_diff(date_create($dob), date_create('now'))->m . " mo" : $age;
	    if($age2 == '0 mo')
		{ 
		   $age2 = date_diff(date_create($dob), date_create('now'))->d . ' days'; //if less than 1 month old
	    }
		?>
		<input type="hidden" name="age" id="age" value="<?php echo $age2; ?>" />
        <div id="rx_order_form_area">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                        <table cellspacing="0" cellpadding="0" class="form order_table" width="100%">       
							<tr style="display:none" id="monograph_row" class="no_hover">
                                <td colspan="2">
                                    <div style="float: left; clear: both; margin-bottom: 10px; width: 100%;">
                                        <div style="clear: both;" id="monograph_container">
										<div style="float: left; width: 100%; margin: 10px 0px; display: none;" title="Dosage Monograph" id="dialogMonograph">
										<form>
											<table width="100%" cellspacing="0" cellpadding="0" style="" class="small_table">
												<tbody><tr class="no_hover">
													<th>Drug Monograph</th>
													<th width="15"><div onclick="$('#dialogMonograph').slideUp('slow');" style="cursor: pointer;"><img alt="Loading..." src="/img/cancel.png"/></div></th>
												</tr>
												<tr class="no_hover">
													<td colspan="2">
														<div id="div_monograph">
														</div>
													</td>
												</tr>
												<tr class="no_hover" style="display: none;" id="monograph_loading_area">
													<td align="center"><div align="center"><img alt="Loading..." src="/img/ajax_loader.gif"/></div></td>
												</tr>
											</tbody></table>
										</form>
									</div></div>
                                    </div>
                    
                            </tr>
							<tr style="display:none" id="dosage_check_row" class="no_hover">
                                <td colspan="2">
                                    <div style="float: left; clear: both; margin-bottom: 10px; width: 100%;">
                                        <div style="clear: both;" id="dosage_check_container">
										<div style="float: left; width: 100%; margin: 10px 0px; display: none;" title="Dosage Check" id="dialogDosageCheck">
										<form>
											<table width="100%" cellspacing="0" cellpadding="0" style="" class="small_table">
												<tbody><tr class="no_hover">
													<th>Dosage Check</th>
													<th width="15"><div onclick="$('#dialogDosageCheck').slideUp('slow');" style="cursor: pointer;"><img alt="Loading..." src="/img/cancel.png"/></div></th>
												</tr>
												<tr class="no_hover">
													<td colspan="2">
														<table width="100%" cellspacing="0" cellpadding="0" class="form">
															<tbody>
																<tr class="no_hover">
																<td style="padding: 0px;">
																	<div style="float: left; margin-top: 5px;">
																		<table cellspacing="0" cellpadding="0" class="form">
																			<tbody>
																			<tr class="no_hover">
																				<td style="padding: 0px;" colspan="2"><strong>Automatic Dosage Checking</strong></td>
																			</tr>
																			<tr class="no_hover">
																				<td>Type:</td>
																				<td>
																					<select id="dose_type" name="dose_type">
																						<option value=""/>
																						<option value="01">LOADING</option> 
																						<option value="02">MAINTENANCE</option> 
																						<option value="03">SUPPLEMENTAL DOSE AFTER DIALYSIS</option> 
																						<option value="04">PROPHYLACTIC</option> 
																						<option value="06">TEST DOSE</option> 
																						<option value="07">SINGLE DOSE</option> 
																						<option value="08">INITIAL DOSE</option> 
																						<option value="09">INTERMEDIATE DOSE</option> 
																					</select>
																				</td>
																			</tr>						
																			<tr class="no_hover">
																				<td>Single Dose Amount:</td>
																				<td>
																					<div style="float:left"><input type="text" style="width:50px;" id="single_dose_amount" name="single_dose_amount" class="pharmacy_field_items ignore_validate"/></div>
																					<div style="float:left;padding-left:5px;" id="div_dose_unit"><select id="dose_unit" name="dose_unit">
																					<option value=""/>
																					</select></div>
																				</td>
																			</tr>
																			<tr class="no_hover">
																				<td>Frequency:</td>
																				<td><input type="text" style="width:50px;" id="frequency" name="frequency" class="pharmacy_field_items ignore_validate"/></td>
																			</tr>
																			<tr class="no_hover">
																				<td style="padding: 0px;"><span class="btn" id="btnDosageCheck" style="width:102px;">Check Dosage</span></td>
																				<td style="padding: 0px;">&nbsp;</td>
																			</tr>
																		</tbody></table>
																	</div>
																</td>
																<td style="padding: 0px;">
																	<div style="float: left; margin-top: 5px;">
																		<table cellspacing="0" cellpadding="0" class="form">
																			<tbody>
																			<tr class="no_hover">
																				<td style="padding: 0px;" colspan="2"><strong>Manual Dosage Calculation</strong></td>
																			</tr>
																			<tr class="no_hover">
																				<td>Patient Weight:</td>
																				<td><div id="div_patient_weight"></div><input type="hidden" id="patient_weight" name="patient_weight" value="" /></td>
																			</tr>						
																			<tr class="no_hover">
																				<td>Enter mg/lb to calculate dosage:</td>
																				<td><input type="text" style="width:50px;" id="manual_dosage" name="manual_dosage" /></td>
																			</tr>
																			<tr class="no_hover">
																				<td>Calculated Dosage:</td>
																				<td><div id="calculated_dosage" name="calculated_dosage"></div></td>
																			</tr>																			
																		</tbody></table>
																	</div>
																</td>
															</tr>
															
															</tr>															
														</tbody></table>
														<table width="100%" >
														<tr class="no_hover" style="display: none;" id="dosage_search_error_area">
																<td style="color: rgb(255, 0, 0); padding: 0px;">Please enter Description.</td>
															</tr>
															<tr class="no_hover" style="display: none;" id="dosage_search_loading_area">
																<td align="center"><div align="center"><img alt="Loading..." src="/img/ajax_loader.gif"/></div></td>
														<tr class="no_hover" style="display: none;" id="dosage_search_data_area">
																<td>
																    <table width="100%" cellspacing="0" cellpadding="0" style="" class="small_table">
												                       <tbody>
																	     <tr class="no_hover"><th>Results</th></tr>
																	     <tr class="no_hover"><td><div style="margin: 0px;" id="dosage_search_result_area"></div></td></tr>
																	   </tbody>
																	</table>
																</td>
														</tr>
														</table>
													</td>
												</tr>
											</tbody></table>
										</form>
									</div></div>
                                    </div>
                            </tr>
                            </table>
                    </td>
                </tr>
            </table> 
                <table cellspacing="0" cellpadding="0" width="100%">
						    <tr>
                                <td style="vertical-align: top;" width="<?php echo $label_width; ?>"><label>Prescriber:</label></td>
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
							<tr>
                                <td style="vertical-align: top;"><label>Supervisor:</label></td>
                                <td>
                                    <select name="data[supervising_prescriber]" id="supervising_prescriber" style="width:200px;">
                                        <option value=""></option>
                                        <?php foreach($caregivers as $caregiver): ?>
                                        <option value="<?php echo $caregiver['caregiver'].'|'.$caregiver['cg_first_name'].' '.$caregiver['cg_last_name']; ?>" <?php if($supervising_prescriber == $caregiver['caregiver'] or $user['clinician_reference_id'] == $caregiver['caregiver']):?>selected="selected"<?php endif; ?>><?php echo $caregiver['cg_first_name']; ?>, <?php echo $caregiver['cg_last_name']; ?></option>
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
                            <tr <?php if(!$from_patient): ?>style="display: none;"<?php endif; ?>>
                                <td style="vertical-align: top;"><label>Diagnosis:</label></td>
                                <td>
                                    <input type="text" name="data[diagnosis_search]" id="diagnosis_search" style="width:420px;" >
                                    
                                    <script language="javascript" type="text/javascript">
										$(document).ready(function() {
											$("#diagnosis_search").autocomplete('<?php echo  $html->url(array('task' => 'load_Icd9_autocomplete')); ?>', {
												max: 20,
												mustMatch: false,
												matchContains: false,
												scrollHeight: 300
											});
											
											$("#diagnosis_search").result(function(event, data, formatted)
											{
												$('#diagnosis').val(data[0]);
												
												var code = data[0].split('[');
												var code = code[1].split(']');
												var code = code[0].split(',');
												$("#<?php echo $icd_var; ?>").val(code);
											});
										});
									</script>
                                </td>
                            </tr>
							<tr <?php if($from_patient): ?>style="display: none;"<?php endif; ?>>
                                <td style="vertical-align: top;"><label>Diagnosis:</label></td>
                                <td>
                                    <input type="text" name="data[diagnosis]" id="diagnosis" style="width:420px;background:#eeeeee;" readonly="readonly" value="<?php echo $EncounterAssessment_diagnosis; ?>" >
                                </td>
                                <!--<input type="hidden" name="data[diagnosis]" id="diagnosis" value="<?php echo $diagnosis; ?>" >-->
								<input type="hidden" name="data[icd_9_cm_code]" id="icd_9_cm_code" value="<?php echo $icd_9_cm_code; ?>" >
                            </tr>
							<tr id="icd9_search_row" style="display:none">
                                <td colspan="2">
                                    <div style="float: left; clear: both; margin-bottom: 10px; width: 80%;">
                                        <div id="icd9_search_container" style="clear:both;"></div>
                                    </div>
                                </td>
                            </tr>
      
                            <tr>
                                <td colspan="2">
                                    <table id="table_fav_drug_list" class="small_table" style="width: 80%;" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr deleteable="false">
                                                <th colspan="3">Drug preference list<span id="imgLoadDrugPreference" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
                                            </tr>
                                            <tr deleteable="true">
                                                <td colspan="2">None</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>                        
                            <tr>
                            
                            <table id="tableTestSearch" cellspacing="0" cellpadding="0" style="margin-top:10px;" width="100%">
                                                <tr class="no_hover">
                                                    <td style="padding: 0px;">
                                                        <table cellpadding="0" cellspacing="0" class="form" width="100%">
                                                            <tr class="no_hover">
                                                                <td style="padding: 0px;">
                                                                    <table cellpadding="0" cellspacing="0" class="form" width="100%">
                                                                        <tr class="no_hover">
                                                                            <td style="vertical-align: top; width:<?php echo $label_width; ?>px;"><label>Drug: <span class="asterisk">*</span></label></td>
                                                                            <td>
                                                                            	<div style="float:left;">
                                                                                	<input hidden="" name="data[deacode]" id="deacode" value="" />
                                                                                	<input type="text" class="drug_search_field_item" name="data[drug_name]" id="drug_name" style="width:380px;" value="<?php echo $drug_name; ?>" />
                                                                                </div>
                                                                                <span id="btnShowMonograph" class="btn" style="float:left;margin-left:15px;" >View Monograph</span><span id="btnShowDosage" class="btn" style="margin-left:10px;" >View Dosage</span>&nbsp;&nbsp;<span id="submit_swirl_drug" style="display: none; padding-top:10px;"><?php echo $smallAjaxSwirl; ?></span>
 </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr id="drug_search_error_area" style="display: none;" class="no_hover">
                                                                <td style="color: #F00; padding: 0px 0px;">Please enter Test Description or Test Code.</td>
                                                            </tr>
                                                            <tr id="drug_search_loading_area" style="display: none;" class="no_hover">
                                                                <td align="center"><div align="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div></td>
                                                            </tr>
                                                            <tr id="drug_search_data_area" style="display: none;" class="no_hover">
                                                                <td style="padding: 0px;">
                                                                    <div id="drug_search_result_area" style="margin: 0px 0px 0px 10px;"></div>
                                                                </td>
                                                            </tr>
															
															<tr id="drug_search_data_area_formulary" style="display: none;" class="no_hover">
															
                                                                <td style="padding: 0px;">
									 <div id="drug_search_result_area_formularys" class="notice">The following are on <b>Formulary</b>. You can choose an alternative below or <a class="smallbtn"  id="keep_current_drug">Keep Current Drug</a></div>   
                                                                    <div id="drug_search_result_area_formulary" style="margin: 0px 0px 0px 10px;"></div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                <input type="hidden" name="data[drug_id]" id="drug_id" value="<?php echo $drug_id; ?>" >
                                <div align="left" class="" id = "dur_warning_message" style="width:auto; display:none; "></div>
                                <div align="left" class="" id = "dur_allergy_warning_message" style="width:auto; display:none; "></div>
                            </tr>

                        <table cellspacing="0" cellpadding="0" class="form order_table" width="100%">
                        <tr>
                                <td style="vertical-align: top;" width="<?php echo $label_width; ?>"><label>Sig: <span class="asterisk">*</span></label></td>
                                <td>
                                    <div style="float:left;"><textarea name="data[sig]" id="sig" cols="56" style="height:45px;"><?php echo $sig; ?></textarea></div><div style="float:left; padding-left:7px;"><span id="btnShowRxBuilder" class="btn" onclick="$('#rx_builder_row').css('display','table-row');">Show Rx Builder</span></div>
                                </td>
                          </tr>
							<tr id="rx_builder_row" style="display:none">
							    <!--<td>&nbsp;</td>-->
                                <td colspan="2">
							        <table id="rxbuilder" cellpadding="0" border="0" width="100%">
										<tr>
										<td align="left">
										<div style="float:left;padding-left:125px;">
											<select name="sigVerb" id="sigVerb" class="dhtmlxsel" style="width:90px">
											<option value="" selected="selected"></option>
											<?php foreach($sigverb_list as $sigverb): ?>
											<option value="<?php echo $sigverb['code']; ?>"><?php echo $sigverb['description']; ?></option>
											<?php endforeach; ?>   
											</SELECT>&nbsp;&nbsp;&nbsp;
										</div>
										<div style="float:left">

											<input size="1" maxLength="6" name="sigFactor" id="sigFactor" type="text">&nbsp;&nbsp;&nbsp;</div>
										<div style="float:left">
											<select name="sigForm" id="sigForm" class="dhtmlxsel" style="width:<?php echo $label_width; ?>px">
											<option value="" selected="selected"> </option>
											<?php foreach($sigform_list as $sigform): ?>
											<option value="<?php echo $sigform['code']; ?>"><?php echo $sigform['description']; ?></option>
											<?php endforeach; ?>   
											</SELECT>&nbsp;&nbsp;&nbsp;</div>
										<div style="float:left">
											<select name="sigRoute" id="sigRoute" class="dhtmlxsel" style="width:125px">
											<option value="" selected="selected"> </option>   
											<?php foreach($sigroute_list as $sigroute): ?>
											<option value="<?php echo $sigroute['code']; ?>"><?php echo $sigroute['description']; ?></option>
											<?php endforeach; ?>                       
											</SELECT>&nbsp;&nbsp;&nbsp;</div>
										<div style="float:left">
											<select name="sigFreq" id="sigFreq" class="dhtmlxsel" style="width:100px">
											<option value="" selected="selected"> </option>
									        <?php foreach($sigfreq_list as $sigfreq): ?>
											<option value="<?php echo $sigfreq['code']; ?>"><?php echo $sigfreq['description']; ?></option>
											<?php endforeach; ?>   
											</SELECT>&nbsp;&nbsp;&nbsp;</div>
										<div style="float:left">
											<select NAME="sigMod" id="sigMod" class="dhtmlxsel" style="width:95px">
										    <option value="" selected="selected"> </option>
											<?php foreach($sigmod_list as $sigmod): ?>
											<option value="<?php echo $sigmod['code']; ?>"><?php echo $sigmod['description']; ?></option>
											<?php endforeach; ?>   
											</select>&nbsp;</div>
										<div style="float:left; padding-left:5px;"><span id="btnSetRx" class="btn" onclick="build_rx()">Set</span></div>
										</td>
									    </tr>                    
									</TABLE>   
							    </td>
							</tr>
                            <tr>
                                <td style="vertical-align: top;"><label>Quantity: <span class="asterisk">*</span></label></td>
                                <td><div style="float:left;"><input type="text" size="12" maxlength="10" name="data[quantity]" id="quantity" value="<?php echo $quantity; ?>"></div><div><label style="padding-left:15px;">Unit:</label>&nbsp; <select name="data[unit_of_measure]" id="unit_of_measure" style="width:160px;">
                                        <option value=""></option>
									    <?php foreach($unit_of_measures as $unit_of_measure): ?>
                                        <option value="<?php echo $unit_of_measure['code']; ?>" <?php if($unit_of_measure == $unit_of_measure['code']):?>selected="selected"<?php endif; ?>><?php echo $unit_of_measure['description']; ?></option>
                                        <?php endforeach; ?>
                                    </select><label style="padding-left:15px;">Patient Weight:</label>&nbsp; <input type="text" value="<?php echo $weight; ?>" size="1" maxlength="10" name="data[weight]" id="weight">
								    <select name="weight_unit" id="weight_unit" style="width:50px;">
										<option value="lb">lb</option>
                                        <option value="kg">kg</option>
                                    </select></div></td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;"><label>Days Supply: <span class="asterisk">*</span></label></td>
                                <td><div style="float:left;"><input type="text" size="12" maxlength="10" name="data[days_supply]" id="days_supply" value="<?php echo $days_supply; ?>"></div><div style="width:auto;float:left;vertical-align:top;"><label style="padding-left:15px; float:left;">Refills: <span class="asterisk">*</span></label><div style="float:left;"> &nbsp;<input type="text" size="12" maxlength="10" name="data[refills]" id="refills" value="<?php echo $refills; ?>" ></div></div></td>
                            </tr>
							<tr>
                                <td style="vertical-align:top;"><label>Comment:</label></td>
                                <td ><textarea name="data[comments]" id="comments" cols="80" style="height: 85px;" ><?php echo $comments; ?></textarea></td>
                            </tr>                        						
							<tr>
                                <td style="vertical-align:top;"><label>Optional:</label></td>
                                <td>
<label for="daw" class="label_check_box"><input type="checkbox" name="data[daw]" id="daw" >&nbsp;Dispense as written</label> &nbsp; 
<label for="rxpreference_type" class="label_check_box"><input type="checkbox" name="data[rxpreference_type]" id="rxpreference_type" value="physician" /> Save as My Favorite</label></td>
                             </tr>
                             <tr>
                                <td colspan="2" style="padding-top: 10px; padding-bottom: 10px;">
                                    <table id="table_fav_pharmacy_list" class="small_table" style="width: 80%;" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr deleteable="false">
                                                <th colspan="2">Pharmacy preference list<span id="imgLoadPharmacyPreference" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
                                            </tr>
                                            <tr deleteable="true">
                                                <td colspan="2">None</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;"><label>Issue Via: <span class="asterisk">*</span></label></td>
                                <td>
                                	<div style="float:left;">
                                    <select name="data[rx_issue_type]" id="rx_issue_type" style="width:240px;" >
										<?php foreach($issue_types as $issue_type): ?>
                                        	<option value="<?php echo $issue_type['code']; ?>" <?php if($rx_issue_type == $issue_type['code']):?>selected="selected"<?php endif; ?>><?php echo $issue_type['description']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    </div>									
                                </td>
                            </tr>
                             <tr>
                                <td style="vertical-align: top;"><label>Issue To: <span id="issue_to_asterisk" class="asterisk" style="visibility: hidden;">*</span></label></td>
                                <td>
                                    <input type="hidden" name="data[pharmacy_id]" id="pharmacy_id" value="<?php echo $pharmacy_id; ?>"/>
									<input type="hidden" name="data[address_1]" id="address_1" value="<?php echo $address_1; ?>"/>
									<input type="hidden" name="data[address_2]" id="address_2" value="<?php echo $address_2; ?>"/>
									<input type="hidden" name="data[city]" id="city" value="<?php echo $city; ?>"/>
									<input type="hidden" name="data[state]" id="state" value="<?php echo $state; ?>"/>
									<input type="hidden" name="data[phone]" id="phone" value="<?php echo $phone; ?>"/>
									<input type="hidden" name="data[zip]" id="zip" value="<?php echo $zip; ?>"/>
                                    <div style="float:left;"><input name="data[issue_to]" id="issue_to" type="text" style="width:400px;" value="<?php echo $pharmacy_name; ?>" placeholder="Click on magnifying glass to find a pharmacy" readonly="readonly" onclick="$('#pharmacy_search_row').css('display','table-row'); resetPharmacySearch(); $('#dialogSearchPharmacy').slideDown('slow');" /></div>								
									<div style="float:left; padding-left:5px;"><img id="imgSearchPharmacyOpen" style="cursor: pointer;margin-top: 3px;" src="<?php echo $this->Session->webroot . 'img/search_data.png'; ?>" width="20" height="20" onclick="$('#pharmacy_search_row').css('display','table-row');" /></div>
                                </td>
                            </tr>
							<tr id="pharmacy_search_row" style="display:none;">
                                <td colspan="2">
                                    <div style="float: left; clear: both; margin-bottom: 10px; width: 80%;">
                                        <div id="pharmacy_search_container" style="clear:both;"></div>
                                    </div>
                                </td>
                            </tr>
                            
							   </td>
                            </tr>  
							<tr>
							    <td colspan="2"> 
								    <div class="actions">
                                    <ul>
                                       <li><a id="btnIssueRx" href="javascript:void(0);" class="btn">Issue</a></li>
					                   <li><a id="btnHoldRx" href="javascript:void(0);" class="btn">Hold</a></li>
                                       <li>
                                       <?php if($from_patient): ?>
                                       <a class="ajax" href="<?php echo $html->url(array('controller' => 'patients', 'action' => 'medication_list', 'patient_id' => $patient_id)); ?>">Cancel</a>
                                       <?php else: ?>
                                       <a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a>
                                       <?php endif; ?>
                                       &nbsp;&nbsp;<span  class="submit_swirl_issue" id="submit_swirl" style="display: none;"><?php echo $smallAjaxSwirl; ?></span></li>
                                    </ul>
                                    </div>
							    </td>
							</tr>
              </table>
                    <!--</td>
                </tr>-->
            </table>   
</div>
	</form>
<?php endif; ?>
</div>
