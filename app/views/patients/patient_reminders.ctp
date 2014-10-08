<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$reminder_id = (isset($this->params['named']['reminder_id'])) ? $this->params['named']['reminder_id'] : "";
$addURL = $html->url(array('action' => 'patient_reminders', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'patient_reminders', 'patient_id' => $patient_id)) . '/'; 
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
echo $this->Html->script('ipad_fix.js');

echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));

?>
<script language="javascript" type="text/javascript">
 $(document).ready(function()
    {
	    initCurrentTabEvents('patient_reminders_area');
		
		$("#frmPatientReminder").validate(
        {
            errorElement: "div",
			 submitHandler: function(form) 
            {
                $('#frmPatientReminder').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmPatientReminder').serialize(), 
					function(data)
                    {
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmPatientReminder'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
		});
		
		$('.section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoad").show();
            loadTab($(this),$(this).attr('url'));
        });

    });	
</script>
<div style="overflow: hidden;">    
    <div class="title_area">   
        <a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'health_maintenance_plans', 'patient_id' => $patient_id)); ?>">Health Maintenance Plans</a>
        <a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'patients', 'action' => 'patient_reminders', 'patient_id' => $patient_id)); ?>">Patient Reminders</a>
<span style="float:right"><a href="javascript:void(0);" class="btn section_btn" url="<?php echo $html->url(array('controller' => 'preferences', 'action' => 'view_health_maintenance_summary', 'patient_id' => $patient_id)); ?>">Health Maintenance Flow Sheet</a></span>
    </div>
    <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="patient_reminders_area" class="tab_area"><?php
	if($task == 'addnew' || $task == 'edit')
	{
		if (isset($SetupDetail['SetupDetail']))
		{
			extract($SetupDetail['SetupDetail']);
		}
		if($task == "addnew")
		{
			$id_field = "";
			$subject = "";
			$appointment_call_date = __date("Y-m-d");
			$days_in_advance = "";
			$messaging = "";
			$postcard = "";
			$type = "";
			$message = "";
		}
		else
		{
			extract($EditItem['PatientReminder']);
			$id_field = '<input type="hidden" name="data[PatientReminder][reminder_id]" id="reminder_id" value="'.$reminder_id.'" />';
			$appointment_call_date = (isset($appointment_call_date) and (!strstr($appointment_call_date, "0000")))?__date($global_date_format, strtotime($appointment_call_date)):'';
		}
		?>
		<form id="frmPatientReminder" name="frmPatientReminder" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
			<tr>
				<td width=180><label>Subject:</label></td>
				<td><input type="text" name="data[PatientReminder][subject]" id="subject" value="<?php echo $subject; ?>" class="required" style="width:450px"></td>
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
				$type_array = array("Scheduled Appointment", "New Appointment", "Need Appointment", "Missed Appointment", "Health Maintenance - Reminder", "Health Maintenance - Followup");
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
		<div class="actions">
			<ul>
				<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPatientReminder').submit();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
                <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
			</ul>
		</div>
		</form>
		<script language=javascript>
		$(document).ready(function()
		{
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
		</script><?php
	} 
	else
    {	  
	   ?>
    <form id="frmPatientReminderGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
      <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">          
            
            <tr deleteable="false">
			    <th width="15" removeonread="true">
				<label for="master_chk_maintenance" class="label_check_box_hx">
                  <input type="checkbox" id="master_chk_maintenance" class="master_chk" />
                 </label>
                </th>
				<th><?php echo $paginator->sort('Subject', 'subject', array('model' => 'PatientReminder', 'class' => 'ajax'));?></th>
				<th width=160>Contact Preference</th>
				<th width=250><?php echo $paginator->sort('Type', 'type', array('model' => 'PatientReminder', 'class' => 'ajax'));?></th>
				<th width=200><?php echo $paginator->sort('Appointment/Call', 'appointment_call_date', array('model' => 'PatientReminder', 'class' => 'ajax'));?></th>
				<th width=130><?php echo $paginator->sort('Messaging', 'messaging', array('model' => 'PatientReminder', 'class' => 'ajax'));?></th>
				<th width=130><?php echo $paginator->sort('Postcard', 'postcard', array('model' => 'PatientReminder', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($PatientReminder as $PatientReminder_record):
            ?>
            <tr editlinkajax="<?php echo $html->url(array('action' => 'patient_reminders', 'task' => 'edit', 'patient_id' => $patient_id, 'reminder_id' => $PatientReminder_record['PatientReminder']['reminder_id'])); ?>">
			<td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $PatientReminder_record['PatientReminder']['reminder_id']; ?>" class="label_check_box_hx">
            <input name="data[PatientReminder][reminder_id][<?php echo $PatientReminder_record['PatientReminder']['reminder_id']; ?>]" id="child_chk<?php echo $PatientReminder_record['PatientReminder']['reminder_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientReminder_record['PatientReminder']['reminder_id']; ?>" />
            </td>
					<td><?php echo $PatientReminder_record['PatientReminder']['subject']; ?></td>
					<td>
					<?php
					
					switch($preferred_contact_method)
					{
						case "phone": echo "Phone Call"; break;
						case "email": echo "Email"; break;
						case "sms": echo "SMS"; break;
					}
					?>
					</td>
					<td><?php echo $PatientReminder_record['PatientReminder']['type']; ?></td>
					<td><?php echo __date($global_date_format, strtotime($PatientReminder_record['PatientReminder']['appointment_call_date'])); ?></td>
					<td><?php echo $PatientReminder_record['PatientReminder']['messaging']; ?></td>
					<td><?php echo $PatientReminder_record['PatientReminder']['postcard']; ?></td>
            </tr>
            <?php endforeach; ?>
            
        </table>
        <div style="width: auto; float: left;" removeonread="true">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmPatientReminderGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                 </ul>
            </div>
        </div>		
    </form>
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
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientReminder', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
    <?php
	}
	?>
    
    </div>
</div>
