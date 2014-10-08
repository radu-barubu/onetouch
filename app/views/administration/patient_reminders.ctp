<div style="overflow: hidden;">
	<?php echo $this->element('administration_health_maintenance_links'); ?>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
<?php 

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$page = (isset($this->params['named']['page'])) ? $this->params['named']['page'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

if (isset($SetupDetail['SetupDetail']))
{
	extract($SetupDetail['SetupDetail']);
}

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['PatientReminder']);
		$id_field = '<input type="hidden" name="data[PatientReminder][reminder_id]" id="reminder_id" value="'.$reminder_id.'" />';
		extract($EditItem['Patient']);
		$patient = $first_name." ".$last_name;
	}
	else
	{
		//Init default value here
		$id_field = "";
		$patient_id = "";
		$subject = "";
		$patient = "";
		$appointment_call_date = __date("Y-m-d");
		$days_in_advance = "";
		$messaging = "";
		$postcard = "";
		$type = "";
		$message = "";
	}
	?>
	<div style="overflow: hidden;">
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field."<input type=hidden name=\"data[PatientReminder][patient_id]\" id=\"patient_id\" value=\"$patient_id\">"; ?>
			<table cellpadding="0" cellspacing="0" class="form" width="100%">
				<tr>
					<td width=180><label>Subject:</label></td>
					<td><input type="text" name="data[PatientReminder][subject]" id="subject" value="<?php echo $subject; ?>" class="required" style="width:450px"></td>
				</tr>
				<tr>
					<td colspan="2">
						<table cellpadding="0" cellspacing="0" class="form">
							<tr>
								<td width=180><label>Patient:</label></td>
								<td style="padding-right: 10px;"><input type="text" name="patient" id="patient" style="width:200px;" class="required" value="<?php echo $patient ?>"></td>
								<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top">Appointment/Call Date:</td>
					<td><?php echo $this->element("date", array('name' => 'data[PatientReminder][appointment_call_date]', 'js' => '', 'id' => 'appointment_call_date', 'value' => __date($global_date_format, strtotime($appointment_call_date)), 'required' => false)); ?></td>
				</tr>
				<tr>
					<td>Days in Advance:</td>
					<td><input type="text" name="data[PatientReminder][days_in_advance]" id="days_in_advance" style="width:50px;" value="<?php echo $days_in_advance; ?>" class="numeric_only"> Days</td>
				</tr>
				<tr>
					<td><label>Messaging:</label></td>
					<td><select id="messaging" name="data[PatientReminder][messaging]">
					<option value="" selected>Select Messaging</option>
					<?php
					$messaging_array = array("Sent", "Failed", "Pending", "On Hold", "Cancelled");
					for ($i = 0; $i < count($messaging_array); ++$i)
					{
						echo "<option value=\"$messaging_array[$i]\"".($messaging==$messaging_array[$i]?"selected":"").">".$messaging_array[$i]."</option>";
					}
					?>
					</select></td>
				</tr>
				<tr>
					<td><label>Postcard:</label></td>
					<td><select id="postcard" name="data[PatientReminder][postcard]">
					<option value="" selected>Select Postcard</option>
					<option value="New" <?php if($postcard=='New') { echo 'selected'; }?>>New</option>
					<option value="Printed" <?php if($postcard=='Printed') { echo 'selected'; }?>>Printed</option>
					</select></td>
				</tr>
				<tr>
					<td><label>Type:</label></td>
					<td><select id="type" name="data[PatientReminder][type]">
					<option value="" selected>Select Type</option>
					<?php
					$type_array = array("Health Maintenance - Reminder", "Health Maintenance - Followup");
					for ($i = 0; $i < count($type_array); ++$i)
					{
						echo "<option value=\"$type_array[$i]\"".($type==$type_array[$i]?"selected":"").">".$type_array[$i]."</option>";
					}
					?>
					</select></td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Message:</label></td>
					<td><textarea cols="20" name="data[PatientReminder][message]" id="message" style=" height:80px"><?php echo $message ?></textarea></td>
				</tr>
			</table>
        </form>
    </div>
    <div class="actions">
        <ul>
			<li removeonread="true"><a href="javascript: void(0);" onclick="submitForm();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'patient_reminders'));?></li>
        </ul>
    </div>
	<script language=javascript>
	function submitForm()
	{
		if ($("#patient_id").val() == "")
		{
			$("#patient").val("");
		}
		$('#frm').submit();
	}
	$(document).ready(function()
	{
		$("#frm").validate({errorElement: "div"});

		$("#patient").autocomplete('<?php echo $this->Session->webroot; ?>administration/health_maintenance/task:patient_load/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#patient").result(function(event, data, formatted)
		{
			$("#patient_id").val(data[1]);
		});

		$('#type').change(function()
		{
			if ($(this).val())
			{
				$('#subject').val($(this).val());
				switch($(this).val())
				{
					case "Scheduled Appointment": $('#days_in_advance').val('<?php echo $days_in_advance_1 ?>'); $('#message').val('<?php echo $message_1 ?>'); break;
					case "New Appointment": $('#days_in_advance').val('<?php echo $days_in_advance_2 ?>'); $('#message').val('<?php echo $message_2 ?>'); break;
					case "Need Appointment": $('#days_in_advance').val('<?php echo $days_in_advance_3 ?>'); $('#message').val('<?php echo $message_3 ?>'); break;
					case "Missed Appointment": $('#days_in_advance').val('<?php echo $days_in_advance_4 ?>'); $('#message').val('<?php echo $message_4 ?>'); break;
					case "Health Maintenance - Reminder": $('#days_in_advance').val('<?php echo $days_in_advance_5 ?>'); $('#message').val('<?php echo $message_5 ?>'); break;
					case "Health Maintenance - Followup": $('#days_in_advance').val('<?php echo $days_in_advance_6 ?>'); $('#message').val('<?php echo $message_6 ?>'); break;
				}
			}
			else
			{
				$('#subject').val('');
				$('#days_in_advance').val('');
				$('#message').val('');
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
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true">
                <label  class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Subject', 'subject', array('model' => 'PatientReminder'));?></th>
				<th width=250><?php echo $paginator->sort('Type', 'type', array('model' => 'PatientReminder'));?></th>
				<th width=200><?php echo $paginator->sort('Appointment/Call', 'appointment_call_date', array('model' => 'PatientReminder'));?></th>
				<th width=150><?php echo $paginator->sort('Messaging', 'messaging', array('model' => 'PatientReminder'));?></th>
				<th width=150><?php echo $paginator->sort('Postcard', 'postcard', array('model' => 'PatientReminder'));?></th>
			</tr>

			<?php
			foreach ($PatientReminders as $PatientReminder):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'patient_reminders', 'task' => 'edit', 'reminder_id' => $PatientReminder['PatientReminder']['reminder_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label  class="label_check_box">
                    <input name="data[PatientReminder][reminder_id][<?php echo $PatientReminder['PatientReminder']['reminder_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $PatientReminder['PatientReminder']['reminder_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $PatientReminder['PatientReminder']['subject']; ?></td>
					<td><?php echo $PatientReminder['PatientReminder']['type']; ?></td>
					<td><?php echo __date($global_date_format, strtotime($PatientReminder['PatientReminder']['appointment_call_date'])); ?></td>
					<td><?php echo $PatientReminder['PatientReminder']['messaging']; ?></td>
					<td><?php echo $PatientReminder['PatientReminder']['postcard']; ?></td>
				</tr>
			<?php endforeach; ?>
			</table>
		</form>
		
		<div style="width: auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'patient_reminders', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
					<li><?php echo '<a href="'. $html->url(array('action' => 'print_postcards')) . '"id="print_postcards" class="btn section_btn">Print Postcards</a>' ?></li>
					<li><a href="javascript: void(0);" onclick="exportData();">Export Data</a></li>
				</ul>
			</div>
		</div>

			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'PatientReminder', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('PatientReminder') || $paginator->hasNext('PatientReminder'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('PatientReminder'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PatientReminder', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'PatientReminder', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('PatientReminder'))
					{
						echo $paginator->next('Next >>', array('model' => 'PatientReminder', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
		</div>
	</div>
    <div class="print_postcards_close"></div>
    <iframe class="print_postcards_load" src="" frameborder="0" ></iframe>

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
		function exportData()
		{
			$("#frm").attr("action", "<?php echo $thisURL. '/task:export/page:$page'; ?>") 
			$("#frm").submit();
		}
		$(function() {
			$('#print_postcards').bind('click',function(a){
				a.preventDefault();
				var href = $(this).attr('href') + '/page:<?php echo $page ?>';
				$('.print_postcards_load').attr('src',href).fadeIn(400,function(){
				$('.print_postcards_close').show();
				$('.print_postcards_load').load(function(){
					$(this).css('background','white');
					
					});
				});
				});
				$('.print_postcards_close').bind('click',function(){
					$(this).hide();
					$('.print_postcards_load').attr('src','').fadeOut(400,function(){
						$(this).removeAttr('style');
						});
					});
		});
	</script>
	<?php
}
?>
