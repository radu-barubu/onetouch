<h2>Broadcasting</h2>
<?php 

$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "inbox";
$thisURL = $this->Session->webroot . $this->params['url']['url']."/task:addnew";
$user = $this->Session->read('UserAccount');

$priority = "Normal";

?>

<div style="overflow: hidden;">
    <?php
    $links = array();
    $links['By User Roles'] = array('action' => 'broadcasting', 'view' => 'by_userroles');
    $links['By User Groups'] = array('action' => 'broadcasting', 'view' => 'by_usergroups');
    $links['By Patients'] = array('action' => 'broadcasting', 'view' => 'by_patient');

    $compare_params = array('view');

    echo $this->element('links', compact('links', 'compare_params', 'view'));
    ?>
	<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" class="form">
	<?php

	echo "
	<input type=hidden name=\"data[MessagingMessage][sender_id]\" id=\"sender_id\" value=\"".$user['user_id']."\">
	<input type=hidden name=\"data[MessagingMessage][patient_id]\" id=\"patient_id\">";

	switch($view)
	{
		case "by_userroles" :
		{
			?>
			<tr>
                            <td width="150"><label>User Roles:<span class="asterisk">*</span></label></td>
				<td>
                    <table cellpadding="0" cellspacing="0" class="form" style="padding:0 0 10px 0;">
						<tr>
						<?php
							echo "<td width=\"auto\" style=\"padding:5px 10px 5px 5px;\"><label for=\"user_roles_0\" class=\"label_check_box_hx\"><input type=checkbox id=\"user_roles_0\" class=\"recipient_chk\" value=\"0\" onclick=\"if(this.checked==true){ $('.recipient_chk').attr('checked', true);  }else{ $('.recipient_chk').attr('checked', false);  }\">&nbsp;&nbsp;All Staff</label></td>";
							echo "<td width=\"auto\" style=\"padding:5px 10px 5px 5px;\"><label for=\"user_roles_1\" class=\"label_check_box_hx\"><input type=checkbox name=\"user_roles[1]\" id=\"user_roles_1\" value=\"8\">&nbsp;&nbsp;Patients</label></td>";
						?>
						</tr>
                        <tr style="background:#e2e2e2">
                        <?php
                        $i = 2;
                        foreach ($UserRoles as $UserRole):
							if ($UserRole['UserRole']['role_desc'] == 'Patient'|| $UserRole['UserRole']['role_desc'] == 'API')
							{
								continue;
							}
                            echo "<td width=\"auto\" style=\"padding:5px 10px 5px 5px;\"><label for=\"user_roles_".$i."\" class=\"label_check_box_hx\"><input type=checkbox name=\"user_roles[$i]\" id=\"user_roles_".$i."\" class=\"recipient_chk\" value=\"".$UserRole['UserRole']['role_id']."\">&nbsp;&nbsp;".$UserRole['UserRole']['role_desc']."</label></td>";
                            if ($i % 2 == 1)
                            {
                                echo "</tr><tr style='background:#e2e2e2'>";
                            }
                            ++$i;
                        endforeach;
						if ($i % 2 == 1)
						{
							echo "<td>&nbsp;</td>";
						}  ?>
                        </tr>						
                    </table>
                </td>
			</tr>
			<?php
		} break;
		case "by_usergroups" :
		{
			?>
			<tr>
				<td width="150"><label>User Groups:<span class="asterisk">*</span></label></td>
				<td>
                <table cellpadding="0" cellspacing="0" class="form" style="padding:0 0 10px 0;" ><tr>
				<?php
				$i = 0;
				foreach ($UserGroups as $UserGroup):
					echo "<td width=\"auto\" style=\"padding:5px 10px 5px 5px;\"><label for=\"user_groups[$i]\" class=\"label_check_box_hx\"><input type=checkbox name=\"user_groups[$i]\" id=\"user_groups[$i]\"  class=\"recipient_chk\" value=\"".$UserGroup['UserGroup']['group_function']."\">&nbsp;&nbsp;".$UserGroup['UserGroup']['group_desc']."</label></td>";
					if ($i % 2 == 1)
					{
						echo "</tr><tr>";
					}
					++$i;
				endforeach; ?>
				</tr></table></td>
			</tr>
			<?php
		} break;
		case "by_patient" :
		{
			?>
			<tr>
				<td width="auto">
                <label>Patients:<span class="asterisk">*</span></label>
                </td>
                <td>
                <table cellspacing="0" cellpadding="0" class="form">
				<tr valign="top">
                <td width="120">
                <label for="patients[0]" class="label_check_box_hx">
                <input type=checkbox name="patients[0]" id="patients[0]" class="recipient_chk" value="age">&nbsp;&nbsp;Age
                </label>
                </td>
                <td> From &nbsp;<input type=text name=start_age id=start_age size=5 class="numeric_only"> To &nbsp;<input type=text name=end_age id=end_age size=5 class="numeric_only"></td></tr>
				<tr valign=top>
                <td>
                <label for="patients[1]" class="label_check_box_hx">
                <input type=checkbox name="patients[1]" id="patients[1]" class="recipient_chk" value="gender">&nbsp;&nbsp;Gender&nbsp;&nbsp;
                </label>
                </td>
                <td style="padding:0 0 10px 0;">
				<select name="gender" id="gender" style="width:100px; margin:0 0 0 10px;">
				<option value="Both">Both</option>
				<option value="Male">Male</option>
				<option value="Female">Female</option></select></td></tr></table></td>
			</tr>
			<?php
		} break;
	}
	?>
		<tr>
			 <td width="150"></td>
			<td><div class="error" id="recipient_error" style="display:none">Please choose recipients</div></td>
		</tr>
        <tr>
            <td width="150"><label>Priority:</label></td>
            <td><select name="data[MessagingMessage][priority]" id=priority>
                    <option value="" selected>Select Priority</option>
                    <option value="Low" <?php echo ($priority=="Low"?"selected":""); ?>>Low</option>
                    <option value="Normal" <?php echo ($priority=="Normal"?"selected":""); ?>>Normal</option>
                    <option value="High" <?php echo ($priority=="High"?"selected":""); ?>>High</option>
                    <option value="Urgent" <?php echo ($priority=="Urgent"?"selected":""); ?>>Urgent</option>
                </select></td>
        </tr>
        <tr>
			<td width="150"><label>Type:</label></td>
			<td>
            	<select name="data[MessagingMessage][type]" id=type>
                    <option value="" selected>Select Type</option>
                    <option value="Staff Meeting">Staff Meeting</option>
                    <option value="Training">Training</option>
                    <option value="Interview">Interview</option>
                    <option value="Finance">Finance</option>
                    <option value="Operations">Operations</option>
                    <option value="Meeting">Meeting</option>
                    <option value="Breakfast">Breakfast</option>
                    <option value="Lunch">Lunch</option>
                    <option value="Dinner">Dinner</option>
                    <option value="Patient">Patient</option>
                    <option value="Appointment">Appointment</option>
                    <option value="Advice Needed">Advice Needed</option>
                    <option value="Rx Refill Request">Rx Refill Request</option>
                    <option value="Other">Other</option>
                </select>
            </td>
		</tr>
		<tr>
			<td width="150"><label>Subject:</label></td>
			<td><input type="text" name="data[MessagingMessage][subject]" id="subject" class="required" style="width:670px;"></td>
		</tr>
		<tr>
			<td width="150" style="vertical-align:top; padding-top: 5px;"><label>Attachment:</label></td>
			<td>
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td>
							<div class="file_upload_area" style="position: relative; width: 264px; height: auto !important">
								<div class="file_upload_desc" style="position: absolute; top: 0px; height: 19px; width: 250px; text-align: left; padding: 5px; overflow: hidden; color: #000000;"></div>
								<div class="progressbar" style="-moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"></div>
								<div style="position: absolute; top: 1px; right: -125px;">
									<div style="position: relative;">
										<a href="#" class="btn" style="float: left; margin-top: -2px;">Select File...</a>
										<div style="position: absolute; top: 0px; left: 0px;"><input id="file_upload" name="file_upload" type="file" /></div>
									</div>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td style="padding-top: 10px;">
							<input type="hidden" name="data[MessagingMessage][attachment_is_selected]" id="attachment_is_selected" value="false" />
							<input type="hidden" name="data[MessagingMessage][upload_dir]" id="upload_dir" value="<?php echo $url_abs_paths['messaging']; ?>" />
							<input type="hidden" name="data[MessagingMessage][attachment_is_uploaded]" id="attachment_is_uploaded" value="false" />
							<input type="hidden" name="data[MessagingMessage][attachment]" id="attachment">
							<span id="attachment_error"></span>
						</td>
					</tr>
				</table>
				
				<script language="javascript" type="text/javascript">
				$(function() 
				{
					$(".progressbar").progressbar({value: 0});
					
					$('#file_upload').uploadify(
					{
						'fileDataName' : 'file_input',
						'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
						'script'    : '<?php echo $html->url(array('controller' => 'messaging', 'action' => 'index', 'task' => 'upload_file', 'session_id' => $session->id())); ?>',
                        'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
                        'scriptData': {'data[path_index]' : 'messaging'},
						'auto'	  : false,
						'wmode'	 : 'transparent',
						'hideButton': true,
						'onSelect'  : function(event, ID, fileObj) 
						{
							$('#attachment_is_selected').val("true");
							$('#attachment').val(fileObj.name);
							$('#attachment_is_uploaded').val("false");
							$('.file_upload_desc').html(fileObj.name);
							$(".ui-progressbar-value").css("visibility", "hidden");
							$(".progressbar").progressbar("value", 0);
							
							$("#attachment_error").html("");
							$(".file_upload_desc").css("border", "none");
							$(".file_upload_desc").css("background", "none");
			
							return false;
						},
						'onProgress': function(event, ID, fileObj, data) 
						{
							$(".ui-progressbar-value").css("visibility", "visible");
							$(".progressbar").progressbar("value", data.percentage);

							return true;
						},
						'onOpen'	: function(event, ID, fileObj) 
						{
							$(window).css("cursor", "wait");
						},
						'onComplete': function(event, queueID, fileObj, response, data) 
						{
							$('#attachment_is_uploaded').val("true");
							
							if(submit_flag == 1)
							{
								$('#frm').submit();
							}
						},
						'onError'   : function(event, ID, fileObj, errorObj) 
						{
						}
					});
				});
				</script>
				
			</td>
		</tr>
		<tr>
			<td valign='top' style="vertical-align:top"><label>Message:</label></td>
			<td><textarea cols="20" name="data[MessagingMessage][message]" rows="2" style="width:670px; height:150px"></textarea></td>
		</tr>
	</table>
	<script language="javascript" type="text/javascript">
	function submitForm()
	{
		if(checkRecipient()==false)
			return false;
		if ($("#patient_id").val() == "")
		{
			$("#patient").val("");
		}
		$('#frm').submit();
	}
	
	function checkRecipient()
	{
		var length = $('.recipient_chk:checked').length;
		if(length <=0 ) {
			$('#recipient_error').show();
			return false;
		} else {
			$('#recipient_error').hide();
			return true;
		}		
	}
	
	$(document).ready(function()
	{
		$("#frm").validate({
		errorElement: "div",
		submitHandler: function(form) 
		{						
			if($('#attachment_is_selected').val() == 'true' && $('#attachment_is_uploaded').val() == "false")
			{
				$('#frm').css("cursor", "wait");
				$('#file_upload').uploadifyUpload();
				submit_flag = 1;
			}
			else
			{
				if ($('#user_roles_1').is(':checked') == true)
				{
					$('#dialog_box').dialog({
					width: 300,
					height: 150,
					modal: true,
					resizable: false,
					draggable: false,
					buttons: [{
						text: 'Send to all Patients',
						click: function(){
							form.submit();
						}
					},
					{
						text: 'Don\'t Send',
						click: function() {
							$(this).dialog('close');
						}
					}]});
				}
				else
				{
					form.submit();
				}
			}
		}});

		$("#type").autocomplete(['Staff Meeting', 'Training', 'Interview', 'Finance', 'Operations', 'Meeting', 'Breakfast', 'Lunch', 'Dinner', 'Patient', 'Appointment', 'Advice Needed', 'Rx Refill Request'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#patient").autocomplete('<?php echo $this->Session->webroot; ?>messaging/index/task:patient_load/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#patient").result(function(event, data, formatted)
		{
			$("#patient_id").val(data[1]);
		});
		
		$(".recipient_chk").click(function(event)
		{
			if ($(this).val() != '0')
			{
				checkRecipient();
			}
		});

		$('.send').click(function()
		{
		});
	});
	</script>
	</form>
</div>
<div class="actions">
	<ul>
		<li><a href="javascript: void(0);" onclick="submitForm();" class="send">Send</a></li>
	</ul>
</div>
<div id="dialog_box" style="display: none;">Do you want to send to all patients?</div> 
