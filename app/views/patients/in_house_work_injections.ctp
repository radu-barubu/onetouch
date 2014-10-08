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
     var val=   document.getElementById('injection_time').value=time;
}        
        
	$(document).ready(function()
	{   
		initCurrentTabEvents('injection_area');

		$('#injectionbtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoad").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_immunizations', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#immunization').click(function()
		{
		    //$("#immunization").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoad").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_injections', 'patient_id' => $patient_id)); ?>");
		});

		$("#injection_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
        
                <?php if ($task === 'addnew'): ?>
                    showNow();
                <?php endif; ?> 
	});  
</script>
<div style="overflow: hidden;">
	<div class="title_area">
		<div class="title_text">
        	<a href="javascript:void(0);"  id="injectionbtn"  style="float: none;">Immunizations</a>
            <a href="javascript:void(0);" id="immunization" style="float: none;" class="active">Injections</a>
		</div>	   
	</div>
	<span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	<div id="injection_area" class="tab_area"> 
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
		$injection_name = "";
		$injection_reason = "";
		$injection_priority = "";
		$cpt = "";
		$cpt_code = "";
		$comment = "";
		$status = "Open";
                
                $injection_date_performed = __date($global_date_format);
                $injection_lot_number = '';
                $injection_manufacturer = '';
		$injection_unit = '';
                $injection_dose = '';
                $injection_body_site = '';
                $injection_route = '';
                $injection_expiration_date = '';
                $injection_administered_by = '';
                $injection_time = '';
                $injection_comment = '';
                
                
	}
	?>

	<div style="overflow: hidden;">
		<form id="frmInHouseWorkInjection" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php
		echo $id_field.'
		<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="'.$encounter_id.'" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Injection" />';
		?>
                    <?php if ($task === 'addnew'): ?> 
                    <h2>Add Injection History</h2>
                    <?php endif; ?> 
                    
                    <?php if ($task === 'edit'): ?> 
                    <h2>Edit Injection History</h2>
                    <?php endif; ?> 
                    
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td width="150" style="vertical-align:middle"><label>Injection:</label></td>
				<td><div style="float:left"><input type="text" name="data[EncounterPointOfCare][injection_name]" id="injection_name" style="width:450px;" value="<?php echo $injection_name; ?>" class="required" /></div></td>
			</tr>
			<tr>
				<td width="150"><label>Reason:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][injection_reason]" id="injection_reason" value="<?php echo $injection_reason;?>" style="width:450px;" /></td>
			</tr>
			<tr>
				<td width="150"><label>Priority:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][injection_priority]" id="injection_priority">
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($injection_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($injection_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
				</td>
			</tr>
			<tr>
			<input type="hidden" name="data[EncounterPointOfCare][rxnorm_code]" id="rxnorm_code" value="rxnorm_code" />
				<tr>
					<td width="150" class="top_pos"><label>Date Performed:</label></td>
					<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][injection_date_performed]', 'id' => 'injection_date_performed', 'value' => ($injection_date_performed ? __date($global_date_format, strtotime($injection_date_performed)):""), 'required' => false));?></td>
				</tr>
				<tr>
					<td width="150"><label>Time:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][injection_time]" id="injection_time" value="<?php echo $injection_time; ?>" size="4" />
                                            <a removeonread="true" href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a>                                          
                                        </td>
				</tr>
				<tr>
					<td width="150"><label>Lot Number:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][injection_lot_number]" id="injection_lot_number" style="width:450px;" value="<?php echo $injection_lot_number; ?>" /></td>
				</tr>
				<tr>
					<td width="150"><label>Manufacturer:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][injection_manufacturer]" id="injection_manufacturer" style="width:450px;" value="<?php echo $injection_manufacturer; ?>" /></td>
				</tr>
				<tr>
					<td width="150"><label>Dose:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][injection_dose]" id="injection_dose" style="width:450px;" value="<?php echo $injection_dose; ?>" /></td>
				</tr>
                                <tr>
                                        <td width="150"><label>Unit(s):</label></td>
                                        <td><input type="text" name="data[EncounterPointOfCare][injection_unit]" id="injection_unit" style="width:100px;" value="<?php echo $injection_unit; ?>" /></td>
                                </tr>
				<tr>
					<td colspan="2">
						<table cellpadding="0" cellspacing="0" class="form">
							<tr>
								<td width="150"><label>Body Site:</label></td>
								<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][injection_body_site]" id="injection_body_site" style="width:450px;" value="<?php echo $injection_body_site ?>"></td>
								<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td width="150"><label>Route:</label></td>
					<td>
					<select name="data[EncounterPointOfCare][injection_route]" id="injection_route" style="width: 140px;">
							 <option value="" selected>Select Option</option>
                             <option value="Intradermal" <?php echo ($injection_route=='Intradermal'? "selected='selected'":''); ?>>Intradermal</option>
                             <option value="Intramuscular" <?php echo ($injection_route=='Intramuscular'? "selected='selected'":''); ?> >Intramuscular</option>
							<option value="Intravenous" <?php echo ($injection_route=='Intravenous'? "selected='selected'":''); ?> >Intravenous</option>
							 <option value="Subcutaneous" <?php echo ($injection_route=='Subcutaneous'? "selected='selected'":''); ?> >Subcutaneous</option>
							 </select>
					
					<!--<input type="radio" name="data[EncounterPointOfCare][injection_route]" id="injection_route" value="Injection" checked> Injection &nbsp; &nbsp;
					<input type="radio" name="data[EncounterPointOfCare][injection_route]" id="injection_route" value="Oral Intake" <?php echo ($vaccine_route=="Oral Intake"?"checked":""); ?>> Oral Intake-->
					</td>
				</tr>
				<tr>
					<td width="150" class="top_pos"><label>Expiration Date:</label></td>
					<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][injection_expiration_date]', 'id' => 'injection_expiration_date', 'value' => ($injection_expiration_date ? __date($global_date_format, strtotime($injection_expiration_date)):""), 'required' => false)); ?></td>
				</tr>
				<tr>
					<td width="150"><label>Administered by:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][injection_administered_by]" id="injection_administered_by" style="width:450px;" value="<?php echo $injection_administered_by; ?>" /></td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
					<td><textarea cols="20" name="data[EncounterPointOfCare][injection_comment]" style="height:80px"><?php echo $injection_comment ?></textarea></td>
				</tr>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label>CPT:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][cpt]" id="cpt" style="width:450px;" value="<?php echo $cpt ?>"></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
					<?php echo '<input type="hidden" name="data[EncounterPointOfCare][cpt_code]" id="cpt_code" value="'.$cpt_code.'" />'; ?>
				</td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
				<td><textarea cols="20" name="data[EncounterPointOfCare][comment]"  style="height:80px"><?php echo $comment ?></textarea></td>
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
				<td width="150"><label>Status:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][status]" id="status" style="width: 110px;">
							 <option value="" selected>Select Status</option>
                             <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                             <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
							 </select>
				<!--<input type="radio" name="data[EncounterPointOfCare][status]" id="status" value="Open" checked> Open &nbsp; &nbsp;
				<input type="radio" name="data[EncounterPointOfCare][status]" id="status" value="Done" <?php echo ($status=="Done"?"checked":""); ?>> Done-->
				</td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmInHouseWorkInjection').submit();">Save</a></li>
			<li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_injections', 'patient_id' => $patient_id)); ?>">Cancel</a></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#injection_name").autocomplete('<?php echo $this->Session->webroot; ?>encounters/injection_list/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#injection_body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#injection_administered_by").autocomplete('<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_administered_by')); ?>', {
			minChars: 2,
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

		$("#frmInHouseWorkInjection").validate(
		{
			errorElement: "div",
			errorPlacement: function(error, element) 
			{
				if(element.attr("id") == "injection_date_performed")
				{
					$("#injection_date_performed_error").append(error);
				}
				else if(element.attr("id") == "injection_expiration_date")
				{
					$("#injection_expiration_date_error").append(error);
				}
				else
				{
					error.insertAfter(element);
				}
			},
			submitHandler: function(form) 
			{
				$('#frmInHouseWorkInjection').css("cursor", "wait");
				
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmInHouseWorkInjection').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmInHouseWorkInjection'), '<?php echo $html->url(array('action' => 'in_house_work_injections', 'patient_id' => $patient_id)); ?>');
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
		<form id="frmInHouseWorkInjection" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true">
                  <label for="master_chk" class="label_check_box_hx">
                  <input type="checkbox" id="master_chk" class="master_chk" />
                  </label>
                </th>
				<th><?php echo $paginator->sort('Injection', 'injection_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Priority', 'injection_priority', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Date Performed', 'injection_date_performed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Lot Number', 'injection_lot_number', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Manufacturer', 'injection_manufacturer', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Dose', 'injection_dose', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Administered by', 'injection_administered_by', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
			++$i;
			?>
				<tr editlinkajax="<?php echo $html->url(array('action' => 'in_house_work_injections', 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" class="label_check_box_hx">
                    <input name="data[EncounterPointOfCare][point_of_care_id][<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>]" id="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['injection_name']; ?></td>
				    <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['injection_priority']; ?></td>
					<td><?php echo __date($global_date_format, strtotime($EncounterPointOfCare['EncounterPointOfCare']['injection_date_performed'])); ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['injection_lot_number']; ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['injection_manufacturer']; ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['injection_dose']; ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['injection_administered_by']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_injections', 'patient_id' => $patient_id, 'task' => 'addnew')); ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmInHouseWorkInjection', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
				</ul>
			</div>
		</div>

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
			?><tr height=35><td><input type=checkbox id='injection_none' name='injection_none' <?php echo isset($MarkedNone['EncounterPointOfCare']) == 1?'checked':''; ?>> Marked as None</td></tr><?php
		}
		?>
		<tr height=35><td><input type=checkbox id='injection_reviewed' name='injection_reviewed' <?php echo isset($ReviewedBy['EncounterPointOfCare']) == 1?'checked':''; ?>> Reviewed by <?php echo $user['firstname'].' '.$user['lastname']; ?><?php
		if (isset($ReviewedBy['EncounterPointOfCare']))
		{
			echo ", Time: ".$ReviewedBy['EncounterPointOfCare']['injection_reviewed_time'];
		}
		?>
		</td></tr></table>-->
	</div>
	<?php
}
?>
	<!--</div>
</div>-->
