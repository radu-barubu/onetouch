<h2>Administration</h2>
<?php 

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$user = $this->Session->read('UserAccount');
?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/colorselect.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/jquery.autocomplete.css" />
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery.autocomplete.js"></script>
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/Plugins/Common.js"></script>
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/Plugins/jquery.colorselect.js"></script>
<?php

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['ScheduleType']);
		$id_field = '<input type="hidden" name="data[ScheduleType][appointment_type_id]" id="appointment_type_id" value="'.$appointment_type_id.'" />';
		if($appointment_type_duration==0) 
			$appointment_type_duration = '';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$modified_user_id = $user['user_id'];
		$type = "";
		$color = 0;
		$appointment_type_duration = '';
		$encounter_type_id = '';
	}
	?>
	<div style="overflow: hidden;">
		<?php echo $this->element("administration_general_links"); ?>
        <?php echo $this->element("administration_general_appointment_links"); ?>
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php
		echo "$id_field
		<input type=hidden name=\"data[ScheduleType][modified_user_id]\" id=\"user_id\" value=\"$modified_user_id\">";
		?>
		<table cellpadding="0" cellspacing="0" class="form">
			<tr>
				<td width="150"><label>Appointment Type:</label></td>
				<td><input type="text" name="data[ScheduleType][type]" id="type_desc" style="width:200px;" class="required" value="<?php echo $type; ?>"></td></td>
			</tr>
			<tr>
				<td width="150" class="top_pos"><label>Color Coding:</label></td>
				<td style="padding-bottom: 10px;">
					<span id="calendarcolor"></span>
					<input id="colorvalue" name="data[ScheduleType][color]" type="hidden" value="<?php echo isset($color)?$color:0; ?>" />
				</td>
			</tr>
			<tr>
				<td width="150"><label>Appointment Duration:</label></td>
				<td>
					<div style="position:relative;">
						<input id="appointment_type_duration" name="data[ScheduleType][appointment_type_duration]" type="text" value="<?php echo $appointment_type_duration; ?>" class="number" /> 
						<span style="position: absolute; right: -10px; top: 3px;">min</span>
					</div>
				</td>
				</td>
			</tr>
			<?php if (count($encounterTypes) > 1): ?> 
			<tr>
				<td width="150"><label>Encounter Type:</label></td>
				<td>
					<select name="data[ScheduleType][encounter_type_id]">
						<?php foreach ($encounterTypes as $e): ?>
						<option <?php echo ($e['PracticeEncounterType']['encounter_type_id'] == $encounter_type_id) ? 'selected="selected"' : ''; ?> value="<?php echo $e['PracticeEncounterType']['encounter_type_id']; ?>"> <?php echo htmlentities($e['PracticeEncounterType']['name']); ?> </option>
						<?php endforeach;?>
					</select>
				</td>
				</td>
			</tr>
			<?php endif;?>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="submitForm();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'appointment_types'));?></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	function submitForm()
	{
		$('#frm').submit();
	}
	$(document).ready(function()
	{
		$("#frm").validate({errorElement: "div"});
		$("#state").autocomplete(['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming', 'Other'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		var cv =$("#colorvalue").val() ;
		if(cv=="")
		{
			cv="0";
		}
    	$("#calendarcolor").colorselect({ title: "Color", index: cv, hiddenid: "colorvalue" });
	    	//to define parameters of ajaxform
	   	var options = {
			beforeSubmit: function() {
				return true;
			},
			dataType: "json",
			success: function(data) {
				//alert(data);
				if (data.IsSuccess) {
					$("#loadingpannel").html(data.Msg).show();
					CloseModelWindow(null,true);
		    		}
			}
    	};
	});
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<?php echo $this->element("administration_general_links"); ?>
        <?php echo $this->element("administration_general_appointment_links"); ?>
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th removeonread="true"><label  class="label_check_box"><input type="checkbox" class="master_chk" /></label></th>
				<th width="30%"><?php echo $paginator->sort('Appointment Type', 'type', array('model' => 'ScheduleType'));?></th>
				<th width="20%"><?php echo $paginator->sort('Color', 'color', array('model' => 'ScheduleType'));?></th>
				<th width="40%"><?php echo $paginator->sort('Duration (min)', 'appointment_type_duration', array('model' => 'ScheduleType'));?></th>
			</tr>

			<?php
			$i = 0;
			$color_arr=array("888888","cc3333","dd4477","994499","6633cc","336699","3366cc","22aa99","329262","109618","66aa00","aaaa11","d6ae00","ee8800","dd5511","a87070","8c6d8c","627487","7083a8","5c8d87","898951","b08b59");
			foreach ($ScheduleTypes as $ScheduleType):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'appointment_types', 'task' => 'edit', 'appointment_type_id' => $ScheduleType['ScheduleType']['appointment_type_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label  class="label_check_box"><input name="data[ScheduleType][appointment_type_id][<?php echo $ScheduleType['ScheduleType']['appointment_type_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $ScheduleType['ScheduleType']['appointment_type_id']; ?>" /></label></td>
					<td><?php echo $ScheduleType['ScheduleType']['type']; ?></td>
					<td><span class="colorvaluespan" style="background-color: #<?php echo $color_arr[$ScheduleType['ScheduleType']['color']]; ?>">&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
					<td style="padding-left:40px;"><?php echo ($ScheduleType['ScheduleType']['appointment_type_duration'] > 0)? $ScheduleType['ScheduleType']['appointment_type_duration'] : ''; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'appointment_types', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'ScheduleType', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('ScheduleType') || $paginator->hasNext('ScheduleType'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('ScheduleType'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'ScheduleType', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'ScheduleType', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('ScheduleType'))
					{
						echo $paginator->next('Next >>', array('model' => 'ScheduleType', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
