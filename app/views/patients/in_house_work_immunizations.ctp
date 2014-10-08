<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$user = $this->Session->read('UserAccount');

$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$mainURL = $html->url(array('action' => 'in_house_work_lab', 'patient_id' => $patient_id)) . '/';
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';   

echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));  

?>

<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/jquery.autocomplete.css" />
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery.autocomplete.js"></script>
<script language="javascript" type="text/javascript">
	
	function showNow()
	{
		var currentTime = new Date();
		var hours = currentTime.getHours();
		var minutes = currentTime.getMinutes();
		
		if (minutes < 10)
			minutes = "0" + minutes;
		
		var time = hours + ":" + minutes ;
		var val = document.getElementById('vaccine_time').value=time;
	}
        
	$(document).ready(function()
	{   
		initCurrentTabEvents('immunization_area');

		$('.in_house_work_submenuitem').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoad").show();
			loadTab($(this),$(this).attr('url'));
		});

		$('#immunization').click(function()
		{
		    //$("#immunization").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoad").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_injections', 'patient_id' => $patient_id)); ?>");
		});

		$("#vaccine_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });

		$("#vaccine_administered_by").autocomplete('<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_administered_by')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#vaccine_name").result(function(event, data, formatted)
		{
			//alert('1. '+data[0]+' 2. '+data[1]+' 3. '+data[2]);
			var code = data[0].split('[');
			var code = code[1].split(']');
			var code = code[0].split(',');
			$("#cvx_code").val(code);
		});
                
                <?php if ($task === 'addnew'): ?>
                    showNow();
                <?php endif; ?> 
		
		$("#immu_table").click(function(){
			$("#linechartContainer").show(); 
			$("#linechartIFrame").show();
			$('#patient_content_area').css('overflow','visible');
			$("#linechartContainer").css('background-image','none'); 
		});
		
		$("#linechart_close").click(function(){
			$("#linechartContainer").hide(); 
			$("#linechartIFrame").hide(); 
		});
		
		$('#injectionbtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoad").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_immunizations', 'patient_id' => $patient_id)); ?>");
		});
	
	});  
</script>
<div style="overflow: hidden;">
	<div class="title_area">
		<div class="title_text">
        	<a href="javascript:void(0);"  id="injectionbtn"  style="float: none;" class="active">Immunizations</a>
            <a href="javascript:void(0);" id="immunization" style="float: none;">Injections</a>
		  </div>	   
	</div>
	<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	<div id="immunization_area" class="tab_area"> 
<?php
if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		unset($EditItem['EncounterPointOfCare']['patient_id']);
		extract($EditItem['EncounterPointOfCare']);
		$id_field = '<input type="hidden" name="data[EncounterPointOfCare][point_of_care_id]" id="point_of_care_id" value="'.$point_of_care_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$vaccine_name = "";
		$vaccine_reason = "";
		$vaccine_priority = "";
		$cpt = "";
		$cpt_code = "";
		$comment = "";
		$date_ordered = $global_date_format;
		$status = "Open";
		$cvx_code = "";
                $vaccine_lot_number = '';
                $vaccine_date_performed = __date($global_date_format);
                $vaccine_manufacturer = '';
                $manufacturer_code = '';
                $vaccine_dose = '';
                $administered_units = '';
                $vaccine_body_site = '';
                $vaccine_expiration_date = '';
                $vaccine_administered_by = '';
                $vaccine_time = '';
                $vaccine_comment = '';
                $vaccine_route = '';
	}
	?>

	<div style="overflow: hidden;">
		<form id="frmInHouseWorkImmunization" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php
		echo $id_field.'
		<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="'.$encounter_id.'" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Immunization" />';
		?>
                    <?php if ($task === 'addnew'): ?> 
                    <h2>Add Immunization History</h2>
                    <?php endif; ?> 
                    
                    <?php if ($task === 'edit'): ?> 
                    <h2>Edit Immunization History</h2>
                    <?php endif; ?> 
                    
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td width="170"><label>Vaccine Name:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][vaccine_name]" id="vaccine_name" style="width:450px;" value="<?php echo $vaccine_name; ?>" />
				<input type="hidden" name="data[EncounterPointOfCare][cvx_code]" id="cvx_code" value="<?php echo isset($cvx_code)?$cvx_code:''; ?>" />
				</td>
			</tr>
			<tr>
				<td width="170"><label>Reason:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][vaccine_reason]" id="vaccine_reason" value="<?php echo $vaccine_reason;?>" style="width:450px;" /></td>
			</tr>
			<tr>
				<td width="170"><label>Priority:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][vaccine_priority]" id="vaccine_priority">
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($vaccine_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($vaccine_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
				</td>
			</tr>
				<tr>
					<td width="170" class="top_pos"><label>Date Performed:</label></td>
					<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][vaccine_date_performed]', 'id' => 'vaccine_date_performed', 'value' => __date($global_date_format, strtotime($vaccine_date_performed)), 'required' => false)); ?></td>
				</tr>
				<tr>
					<td width="170"><label>Time:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][vaccine_time]" id="vaccine_time" value="<?php echo $vaccine_time; ?>" size="4"/>
                                        <a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a> 
                                        </td>
				</tr>
				<tr>
					<td width="170"><label>Lot Number:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][vaccine_lot_number]" id="vaccine_lot_number" style="width:450px;" value="<?php echo $vaccine_lot_number; ?>" /></td>
				</tr>
				<tr>
					<td width="170"><label>Manufacturer:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][vaccine_manufacturer]" id="vaccine_manufacturer" style="width:450px;" value="<?php echo $vaccine_manufacturer; ?>" /></td>
				</tr>
				<tr>
			       <td width="170"><label>Manufacturer Code:</label></td>
			       <td><input type="text" name="data[EncounterPointOfCare][manufacturer_code]" id="manufacturer_code" value="<?php echo $manufacturer_code;?>" style="width:225px;"/></td>
		        </tr>
				<tr>
					<td width="170"><label>Dose:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][vaccine_dose]" id="vaccine_dose" style="width:450px;" value="<?php echo $vaccine_dose; ?>" /></td>
				</tr>
				<tr>
			<td width="170"><label>Unit:</label></td>
			<td><input type="text" name="data[EncounterPointOfCare][administered_units]" id="administered_units" value="<?php echo $administered_units;?>" style="width:225px;"/></td>
		</tr>
				<tr>
					<td colspan="2">
						<table cellpadding="0" cellspacing="0" class="form">
							<tr>
								<td width="170"><label>Body Site:</label></td>
								<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_body_site]" id="vaccine_body_site" style="width:450px;" value="<?php echo $vaccine_body_site ?>"></td>
								<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td width="170"><label>Route:</label></td>
					<td>
					<select name="data[EncounterPointOfCare][vaccine_route]" id="vaccine_route">
							 <option value="" selected>Select Option</option>
							 <option value="Intradermal" <?php echo ($vaccine_route=='Intradermal'? "selected='selected'":''); ?>>Intradermal</option>
                             <option value="Intramuscular" <?php echo ($vaccine_route=='Intramuscular'? "selected='selected'":''); ?> >Intramuscular</option>
							 <option value="Subcutaneous" <?php echo ($vaccine_route=='Subcutaneous'? "selected='selected'":''); ?> >Subcutaneous</option>
							 </select>
					</td>
				</tr>
				<tr>
					<td width="170" class="top_pos"><label>Expiration Date:</label></td>
					<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][vaccine_expiration_date]', 'id' => 'vaccine_expiration_date', 'value' => ($vaccine_expiration_date ? __date($global_date_format, strtotime($vaccine_expiration_date)):""), 'required' => false)); ?></td>
				</tr>
				<tr>
					<td width="170"><label>Administered by:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][vaccine_administered_by]" id="vaccine_administered_by" style="width:450px;" value="<?php echo $vaccine_administered_by; ?>" /></td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
					<td><textarea cols="20" name="data[EncounterPointOfCare][vaccine_comment]"  style=" height:80px"><?php echo $vaccine_comment ?></textarea></td>
				</tr>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="170"><label>CPT:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][cpt]" id="cpt" style="width:450px;" value="<?php echo $cpt ?>"></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
					<?php echo '<input type="hidden" name="data[EncounterPointOfCare][cpt_code]" id="cpt_code" value="'.$cpt_code.'" />'; ?>
				</td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
				<td><textarea cols="20" name="data[EncounterPointOfCare][comment]" style="height:80px"><?php echo $comment ?></textarea></td>
			</tr>
			<?php
			if($task == 'edit')
			{
				?>
				<tr height=35>
					<td valign='top' style="vertical-align:top"><label>Ordered by:</label></td>
					<td><?php echo $EditItem['OrderBy']['firstname']." ".$EditItem['OrderBy']['lastname'] ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td width="170"><label>Status:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][status]" id="status">
							 <option value="" selected>Select Status</option>
                             <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                             <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
							 </select>
				</td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmInHouseWorkImmunization').submit();">Save</a></li>
			<li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_immunizations', 'patient_id' => $patient_id)); ?>">Cancel</a></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#vaccine_name").autocomplete('<?php echo $this->Session->webroot; ?>encounters/vaccine_list/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#vaccine_body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#cpt").autocomplete('<?php echo $this->Session->webroot; ?>encounters/cpt4/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#cpt").result(function(event, data, formatted)
		{
			$("#cpt_code").val(data[1]);
		});

		$("#frmInHouseWorkImmunization").validate(
		{
			errorElement: "div",
			errorPlacement: function(error, element) 
			{
				if(element.attr("id") == "vaccine_date_performed")
				{
					$("#vaccine_date_performed_error").append(error);
				}
				else if(element.attr("id") == "vaccine_expiration_date")
				{
					$("#vaccine_expiration_date_error").append(error);
				}
				else if(element.attr("id") == "date_ordered")
				{
					$("#date_ordered_error").append(error);
				}
				else
				{
					error.insertAfter(element);
				}
			},
			submitHandler: function(form) 
			{
				$('#frmInHouseWorkImmunization').css("cursor", "wait");
				
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmInHouseWorkImmunization').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmInHouseWorkImmunization'), '<?php echo $html->url(array('action' => 'in_house_work_immunizations', 'patient_id' => $patient_id)); ?>');
					},
					'json'
				);
			}
		});
	});
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<form id="frmInHouseWorkImmunization" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true">
                  <label for="master_chk_immunization" class="label_check_box_hx">
                  <input type="checkbox" id="master_chk_immunization" class="master_chk" />
                  </label>
                </th>
				<th><?php echo $paginator->sort('Vaccine Name', 'vaccine_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Priority', 'vaccine_priority', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Date Performed', 'vaccine_date_performed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Lot Number', 'vaccine_lot_number', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Manufacturer', 'vaccine_manufacturer', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Dose', 'vaccine_dose', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Administered by', 'vaccine_administered_by', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
			++$i;
			?>
				<tr editlinkajax="<?php echo $html->url(array('action' => 'in_house_work_immunizations', 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" class="label_check_box_hx">
                    <input name="data[EncounterPointOfCare][point_of_care_id][<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>]" id="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" /></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['vaccine_name']; ?></td>
				    <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['vaccine_priority']; ?></td>
					<td><?php echo __date($global_date_format, strtotime($EncounterPointOfCare['EncounterPointOfCare']['vaccine_date_performed'])); ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['vaccine_lot_number']; ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['vaccine_manufacturer']; ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['vaccine_dose']; ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['vaccine_administered_by']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;">
			<div class="actions">
				<ul>
					<li removeonread="true"><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_immunizations', 'patient_id' => $patient_id, 'task' => 'addnew')); ?>">Add New</a></li>
					<li removeonread="true"><a href="javascript:void(0);" onclick="deleteData('frmInHouseWorkImmunization', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
					<li><a href="javascript:void(0);" id="immu_table">Immunization Table</a></li>
					<li><a href="<?php echo $html->url(array('controller' => 'patients', 'action' =>'immunizations_record', 'patient_id' => $patient_id)); ?>" id="immu_record">Immunization Record</a></li>
				</ul>
			</div>
		</div>
		<div id="linechartContainer" style="margin-left:-575px;width:1000px;height:450px;">
			<div id="linechart_close" title="close chart"></div>
			<iframe id="linechartIFrame" name="linechartIFrame" src="<?php echo $html->url(array('controller' => 'patients', 'action' =>'immunizations_chart', 'patient_id' => $patient_id)); ?>" style="display:none;" scrolling="no" height="450" width="1000" frameBorder="0" align="left"></iframe>
		</div>
        <script language="javascript" type="text/javascript">
            $(function() {
				$('#immu_record').bind('click',function(event)
				{
					event.preventDefault();
					var href = $(this).attr('href');
					$('.visit_summary_load').attr('src',href).fadeIn(400,
					function()
					{
							$('.iframe_close').show();
							$('.visit_summary_load').load(function()
							{
									$(this).css('background','white');

							});
					});
				});
				
				$('.iframe_close').bind('click',function(){
				$(this).hide();
				$('.visit_summary_load').attr('src','').fadeOut(400,function(){
					$(this).removeAttr('style');
					});
				});
			});
       </script>
			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'EncounterPointOfCare', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('EncounterPointOfCare') || $paginator->hasNext('EncounterPointOfCare'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('EncounterPointOfCare'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'EncounterPointOfCare', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('EncounterPointOfCare'))
					{
						echo $paginator->next('Next >>', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		
		<!--<table cellpadding="0" cellspacing="0" class="form" width=100%>
		<?php
		if ($i == 0)
		{
			?><tr height=35><td><input type=checkbox id='immunization_none' name='immunization_none' <?php echo isset($MarkedNone['EncounterPointOfCare']) == 1?'checked':''; ?>> Marked as None</td></tr><?php
		}
		?>
		<tr height=35><td><input type=checkbox id='immunization_reviewed' name='immunization_reviewed' <?php echo isset($ReviewedBy['EncounterPointOfCare']) == 1?'checked':''; ?>> Reviewed by <?php echo $user['firstname'].' '.$user['lastname']; ?><?php
		if (isset($ReviewedBy['EncounterPointOfCare']))
		{
			echo ", Time: ".$ReviewedBy['EncounterPointOfCare']['immunization_reviewed_time'];
		}
		?>
		</td></tr></table>-->
	</div>
	<?php
}
?>
	</div>
</div>
