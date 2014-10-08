<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$mainURL = $html->url(array('action' => 'in_house_work_procedures', 'patient_id' => $patient_id)) . '/';

$point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';  

echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));
   
?>

<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/jquery.autocomplete.css" />
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery.autocomplete.js"></script>
<script language="javascript" type="text/javascript">
	
	$(document).ready(function()
	{   
		$('textarea').autogrow();
		
		initCurrentTabEvents('lab_procedure_area');

		$('#outsideProcedureBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadInhouseRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'procedures', 'patient_id' => $patient_id)); ?>");
		});

		$("#procedure_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		
		$('#procedurePocBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadInhouseRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_procedures', 'patient_id' => $patient_id)); ?>");
		});
	});  
</script>
<div style="overflow: hidden;">
	<div class="title_area">
		<div class="title_text">
            <a href="javascript:void(0);" id="procedurePocBtn"  style="float: none;" class="active">Point of Care</a>
            <a href="javascript:void(0);"  id="outsideProcedureBtn" style="float: none;">Outside Procedure</a>
		</div>	   
	</div>
	<span id="imgLoadInhouseRadiology" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	<div id="lab_procedure_area" class="tab_area"> 
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
		$procedure_name = "";
		$procedure_reason = "";
		$procedure_priority = "";
		$procedure_details = "";
		$procedure_body_site = "";
		$cpt = "";
		$cpt_code = "";
		$comment = "";
		$status = "Open";
	}
	?>

	<div style="overflow: hidden;">
		<form id="frmInHouseWorkProdecure" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php
		echo $id_field.'
		<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="'.$encounter_id.'" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Procedure" />';
		?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label>Procedure Name:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][procedure_name]" id="procedure_name" style="width:450px;" value="<?php echo $procedure_name ?>"></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td width="150"><label>Reason:</label></td>
				<td><input type="text" name="data[AdministrationPointOfCare][procedure_reason]" id="procedure_reason" value="<?php echo $procedure_reason;?>" style="width:450px;" /></td>
			</tr>
			<tr>
				<td width="150"><label>Priority:</label></td>
				<td>
				<select name="data[AdministrationPointOfCare][procedure_priority]" id="procedure_priority">
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($procedure_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($procedure_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
				</td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top"><label>Procedure Notes:</label></td>
				<td><textarea cols="20" name="data[EncounterPointOfCare][procedure_details]" rows="2" style="width:650px; height:80px"><?php echo $procedure_details ?></textarea></td>
			</tr>
			<tr>
				<td width="150"><label>Body Site:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][procedure_body_site]" id="procedure_body_site" style="width:450px;" value="<?php echo $procedure_body_site; ?>" /></td>
			</tr>
			<?php
			if($task == 'addnew')
			{
				echo '<input type="hidden" name="data[EncounterPointOfCare][procedure_date_performed]" id="procedure_date_performed" value="'.date($global_date_format).'" />';
			}
			else
			{
				?>
				<tr>
					<td width="150" class="top_pos"><label>Date Performed:</label></td>
					<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][procedure_date_performed]', 'id' => 'procedure_date_performed', 'value' => __date($global_date_format, strtotime($procedure_date_performed)), 'required' => false)); ?></td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
					<td><textarea cols="20" name="data[EncounterPointOfCare][procedure_comment]" rows="2" style="width:450px; height:80px"><?php echo $procedure_comment ?></textarea></td>
				</tr>
				<?php
			}
			?>
			
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmInHouseWorkProdecure').submit();">Save</a></li>
			<li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_procedures', 'patient_id' => $patient_id)); ?>">Cancel</a></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#procedure_name").autocomplete(['EKG [93000]', 'Holter - 24 hrs [93224]', 'Inhalation TX [94640]', 'Stress Test [93015]', 'Pellet Implantation [11980]'], {
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#procedure_body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
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

		$("#frmInHouseWorkProdecure").validate(
		{
			errorElement: "div",
			errorPlacement: function(error, element) 
			{
				if(element.attr("id") == "procedure_date_performed")
				{
					$("#procedure_date_performed_error").append(error);
				}
				else
				{
					error.insertAfter(element);
				}
			},
			submitHandler: function(form) 
			{
				$('#frmInHouseWorkProdecure').css("cursor", "wait");
				
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmInHouseWorkProdecure').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmInHouseWorkProdecure'), '<?php echo $html->url(array('action' => 'in_house_work_procedures', 'patient_id' => $patient_id)); ?>');
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
		<form id="frmInHouseWorkProdecure" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true">
                  <label for="master_chk_procedures" class="label_check_box_hx">
                  <input type="checkbox" id="master_chk_procedures" class="master_chk" />
                  </label>
                </th>
				<th><?php echo $paginator->sort('Procedure Name', 'procedure_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Priority', 'procedure_priority', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Details', 'procedure_details', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Date Performed', 'procedure_date_performed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Comment', 'procedure_comment', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
			?>
				<tr editlinkajax="<?php echo $html->url(array('action' => 'in_house_work_procedures', 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" class="label_check_box_hx">
                    <input name="data[EncounterPointOfCare][point_of_care_id][<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>]" id="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['procedure_name']; ?></td>
				    <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['procedure_priority']; ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['procedure_details']; ?></td>
					<td><?php echo __date($global_date_format, strtotime($EncounterPointOfCare['EncounterPointOfCare']['procedure_date_performed'])); ?></td>
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['procedure_comment']; ?></td>
					
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width:auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<!--<li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_procedures', 'patient_id' => $patient_id, 'task' => 'addnew')); ?>">Add New</a></li>-->
					<li><a href="javascript:void(0);" onclick="deleteData('frmInHouseWorkProdecure', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
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
	</div>
	<?php
}
?>
	</div>
</div>
