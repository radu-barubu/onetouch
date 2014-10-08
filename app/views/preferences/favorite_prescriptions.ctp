<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if(isset($PracticeData))
{
$rx_setup =  $PracticeData['PracticeSetting']['rx_setup'];
}

if($task == 'addnew' || $task == 'edit')
{
    ?>
	<script language="javascript" type="text/javascript">
	function searchDrug(data)
	{
	     
	}
	function searchIcd9(data)
	{
	     
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
	
	function validateRxForm()
	{
		var valid = true;
			
		$('#drug_name').removeClass("error");
		$('.error[htmlfor="drug_name"]').remove();
		
		$('#dose_type').removeClass("error");
		$('.error[htmlfor="dose_type"]').remove();
		
		$('#sig').removeClass("error");
		$('.error[htmlfor="sig"]').remove();
		
		$('#quantity').removeClass("error");
		$('.error[htmlfor="quantity"]').remove();
		
		$('#refills').removeClass("error");
		$('.error[htmlfor="refills"]').remove();

	   
		if($('#drug_name').val() == "")
		{
			$('#drug_name').addClass("error");
			$('#drug_name').after('<div htmlfor="drug_name" generated="true" class="error">This field is required.</div>');
			valid = false;
		}
		
		if($('#dose_type').val() == "")
		{
			$('#dose_type').addClass("error");
			$('#dose_type').after('<div htmlfor="dose_type" generated="true" class="error">This field is required.</div>');
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
		
		if($('#refills').val() == "")
		{
			$('#refills').addClass("error");
			$('#refills').after('<div htmlfor="refills" generated="true" class="error">This field is required.</div>');
			valid = false;
		}			
		return valid;
	}
	
	function submitRx()
	{
		//collectOrderData();
		if(validateRxForm())
		{
			$('#saveBtn').addClass("button_disabled");
			$('#saveBtn').unbind('click');
			$('#submit_swirl').show();
			$('#frm').submit();
		}
	}
	
	$(document).ready(function()
	{
		$('#saveBtn').click(submitRx);
		
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
	
		$('#refills').change(function()
		{
			$(this).removeClass("error");
			$('.error[htmlfor="refills"]').remove();
		});
		
		$('#dose_type').change(function()
		{
			$(this).removeClass("error");
			$('.error[htmlfor="dose_type"]').remove();
			var drug_id = $('#drug_id').val();
			var task = '<?php echo $task; ?>';
			if(drug_id != "")
			{
				$('.submit_swirl_unit').show();
				var html = "<select id='dose_unit' name='data[dose_unit]'><option value=''/>";
				$.post('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/task:get_dose_units/', 
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
				$('.submit_swirl_unit').hide();
				},
				'json'
				);
			}
		});
	});
	</script>
	<?php
	if($task == 'edit')
	{
		extract($EditItem['EmdeonFavoritePrescription']);
		$id_field = '<input type="hidden" name="data[rx_preference_id]" id="rx_preference_id" value="'.$rx_preference_id.'" />';
		$rx_preference_unique_id_field = '<input type="hidden" name="data[rx_preference_unique_id]" id="rx_preference_unique_id" value="'.$rx_preference_unique_id.'" />'; ?>
        
        <script language="javascript" type="text/javascript">
		
	function EditDoseUnit()
	{ 
		var drug_id = $('#drug_id').val();
		var task = "<?php echo $task; ?>";
		var edit_dose_unit = "<?php echo $EditItem['EmdeonFavoritePrescription']['dose_unit']; ?>";

		if(drug_id != "")
		{ 
			$('#submit_swirl').show();
			var html = "<select id='dose_unit' name='data[dose_unit]'><option value=''/>";
			$.post('<?php echo $this->Session->webroot; ?>encounters/plan_eprescribing_rx/task:get_dose_units/', 
			'drug_id='+drug_id, 
			function(data)
			{
				for(var i = 0; i < data.length; i++)
				{
					dose_unit = data[i].dose_units;
					 html += "<option  value="+dose_unit+">"+dose_unit+"</option>";
				}
				html += "</select>";
				
			$('#div_dose_unit').html(html);
			$('select[id=dose_unit] option[value='+edit_dose_unit+']').attr('selected', 'true');
			if(typeof($ipad)==='object')$ipad.ready();
			$('#submit_swirl').hide();
			},
			'json'
			);
		}
	}
	$(document).ready(function()
	{
			EditDoseUnit();		
	});
	</script>   
	<?php }
	else
	{
		//Init default value here
		$id_field = "";
		$rx_preference_unique_id_field = "";
		$icd_description = '';
		$icd_9_cm_code = '';
		$drug_id = '';
		$drug_name = '';
		$sig = '';
		$daw = '';
		$refills = '';
		$quantity = '';
		$unit_of_measure = '';
		$dose_type = '';
		$dose_unit = '';
		$single_dose_amount = '';
		$frequency = '';
		$prescriber_id = '';
	}
	
	echo $this->element("drug_search", array('submit' => 'searchDrug', 'open' => 'imgSearchDrugOpen', 'container' => 'drug_search_container')); 
    echo $this->element("rx_icd9_search", array('submit' => 'searchIcd9', 'open' => 'imgIcd9Open', 'container' => 'icd9_search_container', 'input_type' => 'text')); 
	?>

	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php 
		echo $id_field; 
		echo $rx_preference_unique_id_field;
		?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td class="field_title"><label>Physician:</label></td>
				<td>
					<select name="data[prescriber_id]" id="prescriber_id" style="width:200px;" >
						<option value=""></option>
						<?php foreach($caregivers as $caregiver): ?>
						<option value="<?php echo $caregiver['caregiver']; ?>" <?php if($prescriber_id == $caregiver['caregiver']):?>selected="selected"<?php endif; ?>><?php echo $caregiver['cg_first_name']; ?>, <?php echo $caregiver['cg_last_name']; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="field_title"><label>Diagnosis:</label></td>
				<td>
					<input type="text" name="data[diagnosis]" id="diagnosis" style="width:400px;" readonly="readonly" value="<?php echo $icd_description; ?>">
					<img id="imgIcd9Open" style="cursor: pointer;" src="<?php echo $this->Session->webroot . 'img/search_data.png'; ?>" width="20" height="20" onclick="$('#icd9_search_row').css('display','table-row');" />
				</td>
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
				<td class="field_title" style="vertical-align:top;"><label>Drug:</label></td>
				<td>
					<div style="float:left;"><input type="text" name="data[drug_name]" id="drug_name" style="width:400px;" readonly="readonly" value="<?php echo $drug_name; ?>" class="required" /></div>
					<div style="float:left; padding-left:5px;"><img id="imgSearchDrugOpen" style="cursor: pointer;" src="<?php echo $this->Session->webroot . 'img/search_data.png'; ?>" width="20" height="20" onclick="$('#drug_search_row').css('display','table-row');" /></div>					
				</td>
				<input type="hidden" name="data[drug_id]" id="drug_id" value="<?php echo $drug_id; ?>" >
			</tr>
			<tr id="drug_search_row" style="display:none">
				<td colspan="2">
					<div style="float: left; clear: both; margin-bottom: 10px; width: 80%;">
						<div id="drug_search_container" style="clear:both;"></div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="field_title" style="vertical-align:top;"><label>Sig:</label></td>
				<td>
					<div style="float:left;"><input type="text" name="data[sig]" id="sig" style="width:400px;" value="<?php echo $sig; ?>" class="required"/></div><div style="float:left; padding-left:10px;"><span id="btnShowRxBuilder" class="btn" onclick="$('#rx_builder_row').css('display','table-row');">Show Rx Builder</span></div>
				</td>
			</tr>
			<tr id="rx_builder_row" style="display:none">
                <td>&nbsp;</td>
				<td>
					<table id="rxbuilder" cellpadding="0" border="0" width="95%">
						<tr>
						<td align="left">
						<div style="float:left">
							<select name="sigVerb" id="sigVerb" class="dhtmlxsel" style="width:90px">
							<option value="" selected="selected"></option>
							<?php foreach($sigverb_list as $sigverb): ?>
							<option value="<?php echo $sigverb['code']; ?>"><?php echo $sigverb['description']; ?></option>
							<?php endforeach; ?>   
							</SELECT>&nbsp;&nbsp;&nbsp;
						</div>
						<div style="float:left">
							<input size="6" maxLength="6" name="sigFactor" id="sigFactor" type="text">&nbsp;&nbsp;&nbsp;</div>
						<div style="float:left">
							<select name="sigForm" id="sigForm" class="dhtmlxsel" style="width:130px">
							<option value="" selected="selected"> </option>
							<?php foreach($sigform_list as $sigform): ?>
							<option value="<?php echo $sigform['code']; ?>"><?php echo $sigform['description']; ?></option>
							<?php endforeach; ?>   
							</SELECT>&nbsp;&nbsp;&nbsp;</div>
						<div style="float:left">
							<select name="sigRoute" id="sigRoute" class="dhtmlxsel" style="width:130px">
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
							</select>&nbsp;&nbsp;&nbsp;</div>
						<div style="float:left">
							<select NAME="sigMod" id="sigMod" class="dhtmlxsel" style="width:95px">
							<option value="" selected="selected"> </option>
							<?php foreach($sigmod_list as $sigmod): ?>
							<option value="<?php echo $sigmod['code']; ?>"><?php echo $sigmod['description']; ?></option>
							<?php endforeach; ?>   
							</select>&nbsp;&nbsp;&nbsp;&nbsp;</div>
						<div style="float:left;"><span id="btnSetRx" class="btn" onclick="build_rx()">Set</span></div>
						</td>
						</tr>                    
					</TABLE>   
				</td>
			</tr>
			<tr>
				<td class="field_title" style="vertical-align:top;"><label>Quantity:</label></td>
				<td><div style="float:left;"><input type="text" size="12" maxlength="10" name="data[quantity]" id="quantity" value="<?php echo $quantity; ?>" class="required"></div></td>
			</tr>
			<tr>
				<td class="field_title"><label>Unit:</label></td>
				<td>
					<select name="data[unit_of_measure]" id="unit_of_measure" style="width:200px;">
						<option value=""></option>
						<?php foreach($unit_of_measures as $unit): ?>
						<option value="<?php echo $unit['code']; ?>" <?php if($unit_of_measure == $unit['code']):?>selected="selected"<?php endif; ?>><?php echo $unit['description']; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="field_title" style="vertical-align:top;"><label>Refills:</label></td>
				<td>
				<div style="float:left;"><input type="text" size="12" maxlength="10" name="data[refills]" id="refills" value="<?php echo $refills; ?>" class="required"></div>
				<div style="float:left;">&nbsp;&nbsp;&nbsp;	<label for="daw" class="label_check_box"><input type="checkbox" name="data[daw]" id="daw" <?php if($daw == 'y'):?>checked="checked"<?php endif; ?>>&nbsp;DAW</label></div>
				</td>
			</tr>
			<tr>
				<td class="field_title"><label>Dosage:</label></td>
				<td>
				    <table>
						<tr class="no_hover">
							<td>Type:</td>
							<td>
								<select id="dose_type" name="data[dose_type]" class="required">
									<option value=""/>
									<option value="01" <?php if($dose_type == '01'):?>selected="selected"<?php endif; ?>>LOADING</option> 
									<option value="02" <?php if($dose_type == '02'):?>selected="selected"<?php endif; ?>>MAINTENANCE</option> 
									<option value="03" <?php if($dose_type == '03'):?>selected="selected"<?php endif; ?>>SUPPLEMENTAL DOSE AFTER DIALYSIS</option> 
									<option value="04" <?php if($dose_type == '04'):?>selected="selected"<?php endif; ?>>PROPHYLACTIC</option> 
									<option value="06" <?php if($dose_type == '06'):?>selected="selected"<?php endif; ?>>TEST DOSE</option> 
									<option value="07" <?php if($dose_type == '07'):?>selected="selected"<?php endif; ?>>SINGLE DOSE</option> 
									<option value="08" <?php if($dose_type == '08'):?>selected="selected"<?php endif; ?>>INITIAL DOSE</option> 
									<option value="09" <?php if($dose_type == '09'):?>selected="selected"<?php endif; ?>>INTERMEDIATE DOSE</option> 
								</select>
							</td>
							<td>&nbsp;&nbsp;Unit:</td>
							<td>                   
                            <div  id="div_dose_unit"><select id="dose_unit" name="data[dose_unit]">
								<option value=""/>
								</select></div>
                                </td>
							<td>&nbsp;&nbsp;Single Amount:</td>
							<td>
								<input type="text" style="width:50px;" id="single_dose_amount" name="data[single_dose_amount]" value="<?php echo $single_dose_amount; ?>" />								
							</td>
							<td>&nbsp;&nbsp;Frequency:</td>
							<td><input type="text" style="width:50px;" id="frequency" name="data[frequency]" value="<?php echo $frequency; ?>" /></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li><a id="saveBtn" href="javascript:void(0);" class="btn">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'favorite_prescriptions'));?>&nbsp;&nbsp;</li>
		</ul>
		<span class = "submit_swirl_unit" id="submit_swirl" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	</div>
	<?php
}
else
{
	if($rx_setup == 'Standard'): ?>
	<div class="error"><b>Warning:</b> e-Prescribing service is not turned on.</div><br /><?php endif; ?>
	<div style="overflow: hidden;">
		<?php echo $this->element('preferences_favorite_links'); ?>

		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15">
                <label class="label_check_box">

                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Drug', 'drug_name', array('model' => 'EmdeonFavoritePrescription'));?></th>
				<th><?php echo $paginator->sort('SIG', 'sig', array('model' => 'EmdeonFavoritePrescription'));?></th>
				<th><?php echo $paginator->sort('ICD9 Description', 'icd_description', array('model' => 'EmdeonFavoritePrescription'));?></th>
			</tr>

			<?php
			foreach ($EmdeonFavoritePrescription as $EmdeonFavoritePrescription):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'favorite_prescriptions', 'task' => 'edit', 'rx_preference_id' => $EmdeonFavoritePrescription['EmdeonFavoritePrescription']['rx_preference_id']), array('escape' => false)); ?>">
					<td class="ignore">
                    <label class="label_check_box">
                    <input name="data[EmdeonFavoritePrescription][rx_preference_id][<?php echo $EmdeonFavoritePrescription['EmdeonFavoritePrescription']['rx_preference_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $EmdeonFavoritePrescription['EmdeonFavoritePrescription']['rx_preference_id'].'|'.$EmdeonFavoritePrescription['EmdeonFavoritePrescription']['rx_preference_unique_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $EmdeonFavoritePrescription['EmdeonFavoritePrescription']['drug_name']; ?></td>
					<td><?php echo $EmdeonFavoritePrescription['EmdeonFavoritePrescription']['sig']; ?></td>
					<td><?php echo $EmdeonFavoritePrescription['EmdeonFavoritePrescription']['icd_description']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'favorite_prescriptions', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'EmdeonFavoritePrescription', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('EmdeonFavoritePrescription') || $paginator->hasNext('EmdeonFavoritePrescription'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('EmdeonFavoritePrescription'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'EmdeonFavoritePrescription', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'EmdeonFavoritePrescription', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('EmdeonFavoritePrescription'))
					{
						echo $paginator->next('Next >>', array('model' => 'EmdeonFavoritePrescription', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
	</div>

	<script language="javascript" type="text/javascript">
		function deleteData()
		{
			var total_selected = 0;
			
			$(".child_chk").each(function()
			{
				if($(this).is(":checked"))
				{
					total_selected++;
				}
			});
			
			if(total_selected > 0)
			/*{
				var answer = confirm("Delete Selected Item(s)?")
				if (answer)*/
				{
					$("#frm").submit();
				}
			/*}*/
			else
			{
				alert("No Item Selected.");
			}
		}
	</script>
	<?php
}
?>