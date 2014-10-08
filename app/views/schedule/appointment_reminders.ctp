<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$reminder_id = (isset($this->params['named']['reminder_id'])) ? $this->params['named']['reminder_id'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$mainURL = $html->url(array('action' => 'appointment_reminders')) . '/'; 
?>
<style>
#call_date_td div{
	float:left;
}
	
#appointment_call_date
{
	float:left;
}
#reminder_img {
	margin-top:4px;
}
</style>
<script language="javascript" type="text/javascript">
var appointment_request = null;
var current_url = '';

function convertAppointmentLink(obj)
{
	var href = $(obj).attr('href');
	$(obj).attr('href', 'javascript:void(0);');
	$(obj).attr('url', href);
	$(obj).click(function()
	{
		loadAppointmentTable(href);
	});
}

function showNow()
{
    var currentTime = new Date();
    var hours = currentTime.getHours();
    var minutes = currentTime.getMinutes();

    if (minutes < 10)
        minutes = "0" + minutes;

    var time = hours + ":" + minutes ;
        document.getElementById('exact_time').value=time;
    
}





function initAppointmentTable()
{
	$("#appointment_table tr:nth-child(odd)").addClass("striped");
	$('#appointment_area a').not('.actions a').each(function()
	{
		convertAppointmentLink(this);
	});
	
	$(".master_chk").click(function() {
		if($(this).is(':checked'))
		{
			$('.child_chk').attr('checked','checked');
		}
		else
		{
			$('.child_chk').removeAttr('checked');
		}
	});
	
	$('.child_chk').click( function() {
		if(!$(this).is(':checked'))
		{
			$('.master_chk').removeAttr('checked');
		}
	});
}

function loadAppointmentTable(url)
{
	current_url = url;
	
	initAutoLogoff();
	
	$('#table_loading').show();
	$('#appointment_area').html('');
	
	if(appointment_request)
	{
		appointment_request.abort();
	}

	appointment_request = $.post(
		url, 
		{'data[patient_id]': $('#patient_id').val()}, 
		function(html)
		{
			$('#table_loading').hide();
			$('#appointment_area').html(html);
			initAppointmentTable();
		}
	);
}
$(document).ready(function()
{	
	$('#dummy-form').submit(function(evt){
		evt.preventDefault();
	});
    
	loadAppointmentTable('<?php echo $html->url(array('action' => 'appointment_reminders_grid')); ?>');
	
	$('#patient_id').keyup(function(evt)
	{
            if (evt.which === 13) {
                return false;
            }
            
            if($('#patient_id').val().length > 0)
                loadAppointmentTable(current_url);
	});
	
	$("#patient_id").addClear(
	{
		closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
		onClear: function()
		{
			loadAppointmentTable('<?php echo $html->url(array('action' => 'appointment_reminders_grid')); ?>');
		}
	});
    
	$('#appointment_area').delegate('tr td', 'click', function(evt){
		
		if($(this).hasClass('ignore'))
		{
			return;	
		}
		
		var reminder_id = $(this).parent().attr('reminder_id');
		window.location.href = '<?php echo $this->Html->url(array('task' => 'edit')); ?>/reminder_id:'+reminder_id;
	})  
});
</script>
<div class="schedules index" style="overflow: hidden;">
	<h2>Appointment Reminders</h2>
    
    <?php if($task == 'addnew' || $task == 'edit'): ?>
    	<?php 
		
   		if (isset($AppointmentSetupDetail['AppointmentSetupDetail']))
		{
			extract($AppointmentSetupDetail['AppointmentSetupDetail']);
		}
		
		@extract($EditItem['Patient']);
		$patient = @$first_name." ".@$last_name;
		
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
			$appointment_time = "";
		}
		else
		{
			extract($EditItem['AppointmentReminder']);
			$id_field = '<input type="hidden" name="data[AppointmentReminder][reminder_id]" id="reminder_id" value="'.$reminder_id.'" />';
			$appointment_call_date = (isset($appointment_call_date) and (!strstr($appointment_call_date, "0000")))?__date($global_date_format, strtotime($appointment_call_date)):'';
			
			if($appointment_time)
			{
				$split_time = explode(':',$appointment_time);	
				$appointment_time = ($appointment_time and $appointment_time!="00:00:00")?($split_time[0].':'.$split_time[1]):"";				
			}
		}
		?>
		<form id="frm" name="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php echo $id_field; ?>
        <input type="hidden" name="data[AppointmentReminder][patient_id]" id="patient_id" value="<?php echo @$patient_id; ?>" />
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
			<tr>
				<td width=180><label>Subject:</label></td>
				<td><input type="text" name="data[AppointmentReminder][subject]" id="subject" value="<?php echo $subject; ?>" class="required" style="width:450px"></td>
			</tr>
			<tr>
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" class="form">
                        <tr>
                            <td width=180><label>Patient:</label></td>
                            <td style="padding-right: 10px;"><input type="text" name="patient" id="patient" style="width:200px;" class="required" value="<?php echo trim($patient); ?>"></td>
                            <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
<?php if (!empty($patient_id)): ?>
			    <td style="vertical-align:top"><span style="float:right"><a id="patient_details" class=btn href="<?php echo $this->Session->webroot;?>patients/index/task:edit/patient_id:<?php echo $patient_id; ?>/view:general_information">Go to Chart >></a></span></td>
<?php endif; ?>
                        </tr>
                    </table>
                </td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top">Appointment/Call Date:</td>
				<td id="call_date_td"><?php echo $this->element("date", array('name' => 'data[AppointmentReminder][appointment_call_date]', 'js' => '', 'id' => 'appointment_call_date', 'value' => __date($global_date_format, strtotime($appointment_call_date)), 'required' => false)); ?><span style="margin: 2px 12px 0 15px; float:left">Exact Time:  </span><input type='text' style="float:left; margin-right:5px;" id='exact_time' size='4'   name="data[AppointmentReminder][appointment_time]"  value="<?php echo $appointment_time; ?>" ><a href="javascript:void(0)" id='exacttimebtn' style="float:left;" onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now', 'id'=>'reminder_img')); ?></a></td>
				
			</tr>
			
			<tr>
				<td>Days in Advance:</td>
				<td><input type="text" name="data[AppointmentReminder][days_in_advance]" id="days_in_advance" style="width:50px;" value="<?php echo $days_in_advance; ?>" class="numeric_only"> Days</td>
			</tr>
			<tr>
				<td><label>Messaging:</label></td>
				<td><select id="messaging" name="data[AppointmentReminder][messaging]">
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
				<td><select id="postcard" name="data[AppointmentReminder][postcard]">
				<option value="" selected>Select Postcard</option>
				<option value="New" <?php if($postcard=='New') { echo 'selected'; }?>>New</option>
				<option value="Printed" <?php if($postcard=='Printed') { echo 'selected'; }?>>Printed</option>
				</select></td>
			</tr>
			<tr>
				<td><label>Type:</label></td>
				<td><select id="type" name="data[AppointmentReminder][type]">
				<option value="" selected>Select Type</option>
				<?php
				$type_array = array("Scheduled Appointment", "New Appointment", "Need Appointment", "Missed Appointment");
				for ($i = 0; $i < count($type_array); ++$i)
				{
					echo "<option value=\"$type_array[$i]\"".($type==$type_array[$i]?"selected":"").">".$type_array[$i]."</option>";
				}
				?>
				</select></td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top"><label>Message:</label></td>
				<td><textarea cols="20" name="data[AppointmentReminder][message]" id="message" style=" height:80px"><?php echo $message ?></textarea></td>
			</tr>
<?php if (!empty($reminder_comment)): ?>
			<tr>
				<td valign='top' style="vertical-align:top"><label>System Comments:</label></td>
				<td style="background-color:#FFFFE0"><em><?php echo $reminder_comment; ?></em></td>
			</tr>
<?php endif; ?>
		</table>
		<div class="actions">
			<ul>
				<li removeonread="true"><a href="javascript: void(0);" onclick="submitForm();"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a></li>
                <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
			</ul>
		</div>
		</form>
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
				scrollHeight: 300,
				width: 300,
				formatItem: function(row) 
				{
					return row[0] + ' (DOB: ' + row[2] + ' )';
				}				
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
		
    
    <?php else: ?>
    <form id="dummy-form">
        <div class="form"> 
        	<span>Find Patient: </span>
        	<span class="form" style="padding-left:5px; height:10px">
        		<input name="data[patient_id]" type="text" id="patient_id" autofocus="autofocus" size="40" />
        	</span>
        </div>
  	</form>
    <table cellpadding="0" cellspacing="0" id="table_loading" width="100%" style="display: none;">
        <tr>
            <td align="center">
                <?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?>
            </td>
        </tr>
    </table>
    <div id="appointment_area"></div>
    
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
		
		function exportData(page)
		{
			$("#frm").attr("action", '<?php echo $html->url(array('task' => 'export')); ?>'+'/page:'+page); 
			$("#frm").submit();
		}
		
		function print_postcards(page)
		{
			var total_selected = 0;
			var chkbxValues = "";
			$(".child_chk").each(function()
			{
				if($(this).is(":checked"))
				{
					total_selected++;
				}
			});
			
			if(total_selected > 0)
			{
				$(".child_chk").each(function(){
				if($(this).is(":checked")){
					chkbxValues += $(this).val();
					chkbxValues +=",";
				}
				});
				var href = '<?php echo $html->url(array('action' => 'print_postcards')); ?>' + '/page:'+page+'/data:'+chkbxValues;
				//var href = '<?php echo $html->url(array('action' => 'print_postcards')); ?>' + '/page:'+page;
				$('.print_postcards_load').attr('src',href).fadeIn(400,function()
				{
					$('.print_postcards_close').show();
					$('.print_postcards_load').load(function()
					{
						$(this).css('background','white');
					});
				});
				
				$('.print_postcards_close').bind('click',function()
				{
					$(this).hide();
					$('.print_postcards_load').attr('src','').fadeOut(400,function()
					{
						$(this).removeAttr('style');
					});
				});
			}
			else
			{
				alert("No Item Selected.");
			}
			/*
			var href = '<?php echo $html->url(array('action' => 'print_postcards')); ?>' + '/page:'+page;
			$('.print_postcards_load').attr('src',href).fadeIn(400,function()
			{
				$('.print_postcards_close').show();
				$('.print_postcards_load').load(function()
				{
					$(this).css('background','white');
				});
			});
			
			$('.print_postcards_close').bind('click',function()
			{
				$(this).hide();
				$('.print_postcards_load').attr('src','').fadeOut(400,function()
				{
					$(this).removeAttr('style');
				});
			}); */
		}
	</script>
   
    
    <?php endif; ?>
</div>
