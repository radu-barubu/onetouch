<h2>Messaging</h2>
<?php 

$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "inbox";
$archived = (isset($this->params['named']['archived'])) ? $this->params['named']['archived'] : "0";
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$redirectTo = (isset($this->params['named']['redirect_to'])) ? $this->params['named']['redirect_to'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$user = $this->Session->read('UserAccount');

?>
<style type="text/css">
	
	.title_area .title_text a {
		display: block;
		position: relative;
	}	
	
	.title_area .msg-count {
		right: 0;
		top: -0.5em;
	}
	
</style>
<script type="text/javascript">
$(function(){
    $('#ajax-loader').hide();
});    
</script>
<?php

$inboxBubble = '';
if (intval($newMessagesCount)) {
	$inboxBubble = '<span class="mcircle msg-count">'.$newMessagesCount.'</span>';
}

$draftBubble = '';

if (intval($draftMessagesCount)) {
	$draftBubble = '<span class="mcircle msg-count">'.$draftMessagesCount.'</span>';
}


$links = array();
$links['Inbox' . $inboxBubble] = array('action' => 'inbox_outbox', 'view' => 'inbox', 'archived' => $archived);
if (!isset($this->params['named']['patient_id']))
{
	$links['Outbox'] = array('action' => 'inbox_outbox', 'view' => 'outbox', 'archived' => $archived);
	$links['Drafts'. $draftBubble] = array('action' => 'inbox_outbox', 'view' => 'drafts', 'archived' => $archived);
}

$compare_params = array('action', 'view');

$additional_contents = '<div style="float: right; margin-top: 10px;">';
if ($task == "")
{
    if ($this->params['named']['archived'] == "1")
    {
        $archived_url = $this->Session->webroot . "messaging/inbox_outbox/view:$view/archived:0";
    }
    else
    {
        $archived_url = $this->Session->webroot . "messaging/inbox_outbox/view:$view/archived:1";
    }

    if ($view != "drafts")
    {
        $additional_contents .= "<label for='archived_messages' class='label_check_box'><input id='archived_messages' type=checkbox onclick=\"window.location='$archived_url';\" " . ($this->params['named']['archived'] == "1" ? "checked" : "") . "> Show Archived Messages</label>";
    }
}
$additional_contents .= '</div>';

$action = $this->params['action'];

$escape = false;
echo $this->element('links', compact('links', 'additional_contents', 'compare_params', 'action', 'view', 'escape'));
?>
<?php
if($task == 'addnew' || $task == 'edit')
{
	$patient = "";
	
	if($task == 'edit')
	{
		unset($EditItem['MessagingMessage']['archived']);
		extract($EditItem['MessagingMessage']);
		
		if (isset($_POST['reply']) or $view == "drafts")
		{
			if ($view == "drafts")
			{
				$id_field = '<input type="hidden" name="data[MessagingMessage][message_id]" id="message_id" value="'.$message_id.'" /><input type="hidden" name="data[MessagingMessage][status]" id="status" value="New" />';
			}
			else
			{
				$id_field = '<input type="hidden" name="data[MessagingMessage][reply_id]" id="reply_id" value="'.$message_id.'" />';
			}
			if ($view == "inbox")
			{
				extract($EditItem['Sender']);
				$recipient_id = $sender_id;
			}
			else
			{
				extract($EditItem['Recipient']);
			}
			$recipient = trim($firstname." ".$lastname);
			$sender_id = $user['user_id'];
			extract($EditItem['Patient']);
			$patient = trim($first_name." ".$last_name);
			if ($view != "drafts")
			{
				$subject = "RE: $subject";
			}
			$thisURL = str_replace("edit", "addnew", $thisURL);
			$task = "addnew";
		}
		else
		{
			$id_field = '<input type="hidden" name="data[MessagingMessage][message_id]" id="message_id" value="'.$message_id.'" />';
			if ($priority == "Urgent" || $priority == "High")
			{
				$message = "<font color='#FF0000'>$message</font>";
			}
		}
	}
	else
	{
		//Init default value here
		$init = $this->data['MessagingMessage'];
		$id_field = "";
		$sender_id = $user['user_id'];
		$type = "";
		$recipient_id = "";
		$patient_id = $initial_patient_id;
		$recipient = "";
		$priority = (isset($init['priority']))? $init['priority']:"Normal";
		$patient = $initial_patient_name;
		$subject = $init['subject'];
		$message = $init['message'];
                
		if ($send_to) {
			$recipient_id = $send_to;
			$recipient = $initial_patient_name;
		}              
	}
	?>
<script>
  MacrosArr = { <?php foreach ($FavoriteMacros as $FavM) { 
  $mtext=htmlentities(preg_replace('/\r\n?/', '\n', $FavM['FavoriteMacros']['macro_text'] ));
  echo "'?".str_replace("'","\'",$FavM['FavoriteMacros']['macro_abbreviation'])."' : '".str_replace("'","\'",$mtext)."',"; 
  } 
  ?> };
</script>
<?php echo $this->Html->script(array('macros')); ?>
<div style="overflow: hidden;">
    
    <div id="ajax-loader">
        <?php echo $this->Html->image('ajax_loaderback.gif', array('alt' => 'Processing')); ?> Saving draft ...
    </div>
    
    <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <?php
		if($task == 'edit' and $view != 'drafts')
		{
			echo "
			<input type=hidden name=\"reply\" id=\"reply\" value=\"$message_id\">";
			?>
        <table cellpadding="0" cellspacing="0" class="form" width=70%>
        <tr>
            <td colspan="2"><table cellpadding="0" cellspacing="0" class="form" width=100%>
                    <tr height=35>
                        <td width="150"><label>Sent:</label></td>
                        <td><?php echo __date("$global_date_format, g:i:s A", strtotime($created_timestamp)); ?></td>
                        <td align=right><table cellpadding="0" cellspacing="0" class="form">
                                <tr>
                                    <td width="150"><label>Status:</label></td>
                                    <td><?php
								if ($view == "inbox")
								{	?>
                                       <input type="hidden" name="data[MessagingMessage][calendar_id]" id="calendar_id" value="<?php echo $calendar_id ?>" />
									    <select name=status id=status>
                                            <option value="New" <?php echo ($status=="New"?"selected":""); ?>>New</option>
                                            <option value="Read" <?php echo ($status=="Read"?"selected":""); ?>>Read</option>
                                            <option value="Done" <?php echo ($status=="Done"?"selected":""); ?>>Done</option>
                                        </select>
                                        <?php
								}
								else
								{
									echo "Sent";
								} ?></td>
                                </tr>
                            </table></td>
                    </tr>
                </table></td>
        </tr>
        <tr height=35>
            <td width="150"><label>From:</label></td>
            <td><?php echo $EditItem['Sender']['firstname']." ".$EditItem['Sender']['lastname']; ?></td>
        </tr>
        <tr height=35>
            <td><label>To:</label></td>
            <td><?php echo $EditItem['Recipient']['firstname']." ".$EditItem['Recipient']['lastname']; ?></td>
        </tr>
        <tr height=35>
            <td><label>Type:</label></td>
            <td><?php echo $type ?></td>
        </tr>
        <?php if(isset($EditItem['Patient']) && !empty($EditItem['Patient']['patient_id'])): ?>
        <tr height=35 style="display: <?php echo ($user_role_id==8)?'none':''; ?>">
            <td><label>Patient</label></td>
            <td><?php extract($EditItem['Patient']);
					if ($LinkAccess != "NA")
					{
						echo "<a href=\"".$this->Session->webroot."patients/index/task:edit/patient_id:$patient_id/view:medical_information\">";
					}
					else
					{
						echo "<a href=\"".$this->Session->webroot."patients/index/task:edit/patient_id:$patient_id\">";
					}
					echo $first_name." ".$last_name; ?>
                </a></td>
        </tr>
        <?php endif; ?>
        <tr height=35>
            <td><label>Priority:</label></td>
            <td><?php 
		        if ($priority == "Urgent" || $priority == "High")
			{
				echo "<font color='#FF0000'>$priority</font>";
			} else {
				echo $priority ;
			}
		?></td>
        </tr>
        <tr height=35>
            <td><label>Subject:</label></td>
            <td><?php echo $subject ?></td>
        </tr>
        <?php
				if ($attachment)
				{
					?>
        <tr height=35>
            <td><label>Attachment:</label></td>
            <td><?php echo $html->link("$attachment", array('action' => 'inbox_outbox', 'task' => 'download_file', 'message_id' => $message_id)); ?></td>
        </tr>
        <?php
				}
				?>
		</table>
		<div class="title_area"></div>
		<table cellpadding="0" cellspacing="0" class="form" width=70%>
        <tr>
            <td><table width=100% cellpadding="3" cellspacing="3" class="form">
                    <tr>
                        <td>
				<?php 
				// you guys can perfect this later - Robert
				$message3="";
				$message2 = explode("\n", $message);
				$i=0;
				foreach($message2 as $vl) {
				  //strip out sender name since it's redundant
				  if(strstr($vl,$EditItem['Sender']['firstname']." ".$EditItem['Sender']['lastname']. " on") && $i < 2)
				  {
				    continue;
				  }
				  else
				  {
				    if(strstr($vl, '&gt;&gt;')) 
				      $message3 .= '<font color=#A8A8A8 > '.$vl.'</font>'."\n";
				     else if(strstr($vl, '&gt; ')) 
				      $message3 .= '<font color=#707070> '.$vl.'</font>'."\n";				   
				     else
				       $message3 .= '<b>'.$vl.'</b>'."\n"; 
				  }
				  $i++;     
				}
				
				echo html_entity_decode(nl2br($message3)); 
				
				
				?>
			</td>
                    </tr>
                </table></td>
        </tr>
        <script language="javascript" type="text/javascript">
			$(document).ready(function()
			{
				$("#status").change(function()
				{
					var formobj = $("<form></form>");
					formobj.append('<input name="data[MessagingMessage][message_id]" type="hidden" value="'+$("#reply").val()+'">');
					formobj.append('<input name="data[MessagingMessage][status]" type="hidden" value="'+$("#status").val()+'">');
					formobj.append('<input name="data[MessagingMessage][calendar_id]" type="hidden" value="'+$("#calendar_id").val()+'">');
					$.post("<?php echo $this->Session->webroot; ?>messaging/inbox_outbox/task:update_status/", formobj.serialize());
				});
			});
			</script>
        <?php
		}
		else
		{
			echo "
			$id_field
			<input type=hidden name=\"frm_action\" id=\"frm_action\">
			<input type=hidden name=\"data[MessagingMessage][sender_id]\" id=\"sender_id\" value=\"$sender_id\">
			<input type=hidden name=\"data[MessagingMessage][recipient_id]\" id=\"recipient_id\" value=\"$recipient_id\">";
			if($user_role_id==8)
			{
			    echo "<input type=hidden name=\"data[MessagingMessage][patient_id]\" id=\"patient_id\" value=\"$patient_user_id\">";
			}
			else
			{
			    echo "<input type=hidden name=\"data[MessagingMessage][patient_id]\" id=\"patient_id\" value=\"$patient_id\">";
			}

			if ( $redirectTo == 'patient_charts' && $patient and $task == 'addnew')
			{
				$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
				if($encounter_id)
				{
					$BackURL = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'view' => 'attachments', 'view_tab' => 3, 'view_actions' => 'messages', 'encounter_id' => $encounter_id));
				}
				else
				{
					$BackURL = $html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $patient_id, 'view' => 'attachments', 'view_tab' => 3, 'view_actions' => 'messages'));
				}
				echo "<input type=hidden name=\"data[BackURL]\" id=\"BackURL\" value=\"$BackURL\">";
			}
			else
			{
					$BackURL = $html->url(array('action' => 'inbox_outbox'));
			}
			?>
      <?php if(isset($send_mail) && $send_mail):?>
                              <div class="error2">
                              WARNING: Don't include any patient health information in your message since it's not protected    
                              </div>    <br />
      <?php endif;?>
        <table cellpadding="0" cellspacing="0" class="form">
            <tr height=35>
                <td colspan="2"><table cellpadding="0" cellspacing="0" class="form">
                        <tr>
                            <td width="150" style="vertical-align: top;"><label>To:</label></td>
                            <td style="">
                            <?php 
					/*  if (!empty($recipient) and $view != "drafts") {
								if($user_role_id==8)
								{
									echo 'Advice Nurse</br>';
								}
								else
								{
									?>
									<input type="hidden" name="data[MessagingMessage][recipient]" id="recipient" class="required" value="<?php echo $recipient ?>" /><?php echo $recipient ?>
									<?php
					      		}
							}
							else
							{ */ 
								if($user_role_id==8)
								{
								echo 'Advice Nurse</br>';
								}
								else
								{
                  
                  
                  if (isset($send_mail) && $send_mail) {
                    echo $send_mail;
                  } 
                  
								?>
                  <?php if(!isset($send_mail) || !$send_mail):?> 
									 <script type="text/javascript">
										$(document).ready(function() {
											$("#recipient").tokenInput("<?php echo $this->Session->webroot; ?>messaging/index/task:user_load/", {
											 theme: "facebook",<?php if(!empty($recipient_id)){  ?>
											 prePopulate: [{id: <?php echo $recipient_id;  ?>, name: "<?php echo ($recipient)?$recipient : ''; ?>"}],<?php }?>
											 onAdd: function (item) {
												var prev_val = $('#recipient_id').val();
												$('#recipient_id').val(prev_val+','+item.id);
												
											},
											onDelete: function (item) {
												var prev_val = $('#recipient_id').val();						
												var get = prev_val.split(item.id);
												var cnt = get.length;
												var new_val = '';
												for(var i = 0; i<=cnt-1 ; i++) {
													if(get[i] != item.id){
														new_val += get[i];
													}
												}	
												$('#recipient_id').val(new_val);						
											}
											});
											setTimeout(function(){document.getElementById('token-input-recipient').focus();},500);
											
										});
									</script>		
                  <?php endif;?>
									<input type="<?php echo (isset($send_mail) && $send_mail) ? 'hidden' : 'text'; ?>" name="data[MessagingMessage][recipient]" id="recipient" style="width:772px;" class="required" value="<?php echo ($recipient)?$recipient.', ':''; ?>" placeholder="Start typing recipient's name">
								<?php
					      		}
                //} 
							?>
                            </td>
                            <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                        </tr>
                    </table></td>
            </tr>
        <?php if(!isset($send_mail) || !$send_mail):?> 
            <tr>
				<td width="150"><label>Type:</label></td>
				<td>
				<?php if (empty($type) or $view == "drafts") {
				     //Patient users
				     if($user_role_id==8)
				     {
					 ?>
					 <select name="data[MessagingMessage][type]" id=type>
						<option value="" selected>Select Type</option>
						<option value="Medical Advice Needed" <?php echo ($type=="Medical Advice Needed"?"selected":""); ?>>Medical Advice Needed</option>
						<option value="Refill Request" <?php echo ($type=="Refill Request"?"selected":""); ?>>Refill Request</option>
					</select>	
					 <?php
				     }
				     else
				     {
				     ?>
				     <select name="data[MessagingMessage][type]" id=type>
						<option value="" selected>Select Type</option>
						<option value="Staff Meeting" <?php echo ($type=="Staff Meeting"?"selected":""); ?>>Staff Meeting</option>
						<option value="Training" <?php echo ($type=="Training"?"selected":""); ?>>Training</option>
						<option value="Interview" <?php echo ($type=="Interview"?"selected":""); ?>>Interview</option>
						<option value="Finance" <?php echo ($type=="Finance"?"selected":""); ?>>Finance</option>
						<option value="Operations" <?php echo ($type=="Operations"?"selected":""); ?>>Operations</option>
						<option value="Meeting" <?php echo ($type=="Meeting"?"selected":""); ?>>Meeting</option>
						<option value="Breakfast" <?php echo ($type=="Breakfast"?"selected":""); ?>>Breakfast</option>
						<option value="Lunch" <?php echo ($type=="Lunch"?"selected":""); ?>>Lunch</option>
						<option value="Dinner" <?php echo ($type=="Dinner"?"selected":""); ?>>Dinner</option>
						<option value="Patient" <?php echo ($type=="Patient"?"selected":""); ?>>Patient</option>
						<option value="Appointment" <?php echo ($type=="Appointment"?"selected":""); ?>>Appointment</option>
						<option value="Advice Needed" <?php echo ($type=="Advice Needed"?"selected":""); ?>>Advice Needed</option>
						<option value="Rx Refill Request" <?php echo ($type=="Rx Refill Request"?"selected":""); ?>>Rx Refill Request</option>
						<option value="Other" <?php echo ($type=="Other"?"selected":""); ?>>Other</option>
					</select>	
				   <?php 
				   }
				}
				else { ?>
				   	<input type=hidden name="data[MessagingMessage][type]" value="<?php echo $type;?>"><?php echo $type;?>
				<?php } ?>
				
				</td>
            </tr>
<?php endif;?>            
            <tr style="display: <?php echo ($user_role_id==8)?'none':''; ?>">
                <td colspan="2"><table cellpadding="0" cellspacing="0" class="form">
                        <tr>
                            <td width="150"><label>Patient:</label></td>
                            <td style="padding-right: 10px;">
                            <?php if (!empty($patient) and $view != "drafts") {  ?>
                            <input type="hidden" name="patient" id="patient" value="<?php echo $patient ?>"> 
                            <?php echo "<a href=\"".$this->Session->webroot."patients/index/task:edit/patient_id:$patient_id/view:medical_information\">".$patient."</a>";
                            
                            } else { 
                            ?>
                            <input type="text" name="patient" id="patient" style="width:200px;" value="<?php echo $patient ?>" placeholder="Start typing patient's name">
                            <?php } ?>
                            </td>
                            <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                        </tr>
                    </table></td>
            </tr>
        <?php if(!isset($send_mail)  || !$send_mail):?> 
            
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
          <?php endif;?>            
            <tr>
                <td><label>Subject:</label></td>
                <td><input type="text" name="data[MessagingMessage][subject]" id="subject" style="width:772px;" value="<?php echo str_replace('RE: RE: ', '', $subject); ?>" placeholder="Type Subject here" class="required"></td>
            </tr>
            <tr>
                <td style="vertical-align:top; padding-top: 5px;"><label>Attachment:</label></td>
                <td>
                	<table cellpadding="0" cellspacing="0">
                        <tr>
                            <td><div class="file_upload_area" style="position: relative; width: 264px; height: auto !important">
                                    <div class="file_upload_desc" style="position: absolute; top: 0px; height: 19px; width: 250px; text-align: left; padding: 5px; overflow: hidden; color: #000000;"></div>
                                    <div class="progressbar" style="-moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"></div>
                                    <div style="position: absolute; top: 1px; right: -125px;">
                                        <div style="position: relative;"> <a href="#" class="btn" style="float: left; margin-top: -2px;">Select File...</a>
                                            <div style="position: absolute; top: 0px; left: 0px;">
                                                <input id="file_upload" name="file_upload" type="file" />
                                            </div>
                                        </div>
                                    </div>
                                </div></td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;"><input type="hidden" name="data[MessagingMessage][attachment_is_selected]" id="attachment_is_selected" value="false" />
                                <input type="hidden" name="data[MessagingMessage][upload_dir]" id="upload_dir" value="<?php echo $url_abs_paths['messaging']; ?>" />
                                <input type="hidden" name="data[MessagingMessage][attachment_is_uploaded]" id="attachment_is_uploaded" value="false" />
                                <input type="hidden" name="data[MessagingMessage][attachment]" id="attachment">
                                <span id="attachment_error"></span></td>
                        </tr>
                    </table>
                    <script language="javascript" type="text/javascript">
						$(function() 
						{
							$(".progressbar").progressbar({value: 0});
							
							$('#file_upload').uploadify(
							{
								//this was put into library itself
								//'fileExt': '*.pdf;*.docx;*.doc;*.jpg;*.png;*.xml',
								//'fileDesc': 'Documents',
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
										$("#frm_action").val("Yes");
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
                <td>
		<textarea cols="40" id="txtreply" name="data[MessagingMessage][message]" rows="2" style="width:772px; height:70px" placeholder="Type message here"><?php /*
		
						// you guys can perfect this later - Robert
						$message3="";
						$message2 = explode("\n", $message);
						foreach($message2 as $vl) {
						 $vl=trim($vl);
						 $regexp = "&lt;a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*&gt;See schedule&lt;\/a&gt;";
				  		 $vl = preg_replace("/$regexp/siU", '', $vl);
						 if($vl) {
				  		   if(strstr($vl, '&gt;')) 
				  		     $message3 .= "&gt;".$vl; 
				  		   else
				   		     $message3 .= "&gt; ".$vl;
				   		 }  
						}
					
					
					if($message3) echo "\n\n\n".$message3; 
					*/
			?></textarea>
		<input type="hidden" name="data[MessagingMessage][former_message]" value="<?php echo $message; ?>">	
		<?php if ($message): ?>	
				<br /><em>Previous messages:</em>
				<div style="background-color:whitesmoke;padding:5px"><?php echo html_entity_decode(nl2br($message)); ?></div>
		<?php endif; ?>

			
		</td>
            </tr>
            <script language="javascript" type="text/javascript">
			$(document).ready(function()
			{
				$.fn.selectRange = function(start, end) {
					return this.each(function() {
						if (this.setSelectionRange) {
							this.focus();
							this.setSelectionRange(start, end);
						} else if (this.createTextRange) {
							var range = this.createTextRange();
							range.collapse(true);
							range.moveEnd('character', end);
							range.moveStart('character', start);
							range.select();
						}
					});
				};
				
				//$('#txtreply').selectRange(0,0);


                                if ($("#recipient_id").val() == "")
                                {
                                        $("#recipient").val("");
                                }
                                
                                jQuery.validator.addMethod("autocompleted", function(value, element) { 
                                  var autocompleted = true;
                                    
                                  if ($("#recipient_id").val() == "") {
                                    autocompleted = false;
                                  }
                                  
                                  return this.optional(element) || autocompleted; 
                                }, "Recipient not found");

				$("#frm").validate({
				
                                rules : {
                                    'recipient' : {
                                        required: true,
                                    }
                                },
                                onfocusout: false,
                                onkeyup: false,
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
						$("#frm_action").val("Yes");
						form.submit();
					}
				}});
		
				/*$("#recipient").autocomplete('<?php echo $this->Session->webroot; ?>messaging/index/task:user_load/', {
					minChars: 2,
					max: 20,
					mustMatch: false,
					matchContains: false,
					scrollHeight: 300,
					multiple: true,
					multipleSeparator: ", ",
					width: 300					
				});
						
				$("#recipient").result(function(event, data, formatted)
				{
					$("#recipient_id").val('');
				});                                
				*/		
						
				$("#type").autocomplete(['Staff Meeting', 'Training', 'Interview', 'Finance', 'Operations', 'Meeting', 'Breakfast', 'Lunch', 'Dinner', 'Patient', 'Appointment', 'Advice Needed', 'Rx Refill Request'], {
					max: 20,
					mustMatch: false,
					matchContains: false,
					scrollHeight: 300
				});
		
				$("#patient").autocomplete('<?php echo $this->Session->webroot; ?>messaging/index/task:patient_load/', {
					minChars: 2,
					max: 20,
					mustMatch: false,
					matchContains: false,
					scrollHeight: 300,
					width: 400,
					formatItem: function(data, i, total) {
						return data[0] + ' (DOB: ' + data[6] +') ';
					}                        
                                        
				});
		
				$("#patient").result(function(event, data, formatted)
				{
					$("#patient_id").val(data[1]);
				});
			});
			</script>
            <?php
		}

		if(!empty($send_mail)) {
		?>
		<tr><td colspan=2><label for="send_portal_credentials" class="label_check_box"><input type=checkbox name="data[MessagingMessage][send_portal_credentials]" id="send_portal_credentials"> Send Patient Portal address and login credentials</label></td></tr>
		<?php
		}
		?>
        </table>
    </form>
    <!-- submit form for deleting message -->
    <form id="del_form" method="post" action="<?php echo $thisURL. '/task:'; ?>delete_single">
    	<input type="hidden" name="data[MessagingMessage][message_id]" value="<?php echo $this->data['MessagingMessage']['message_id'] ?>" />
    </form>
</div>
<div class="actions">
    <ul>
        <li><a href="javascript: void(0);" onclick="submitForm();"><?php echo ($task == 'addnew') ? 'Send' : 'Reply'; ?></a></li>
		<?php
		if ($view == "drafts")
		{
			?><li><?php echo $html->link(__('Back To '.ucfirst($view), true), array('action' => 'inbox_outbox', 'view' => $view));?></li><?php
		}
		else
		{
			if ($patient and $task == 'addnew')
			{
				?><li><a href="<?php echo $BackURL ?>" onclick='$("#frm_action").val("Yes");'>Cancel</a></li><?php
			}
			else
			{
				?><li><?php echo $html->link(__('Back To '.ucfirst($view), true), array('action' => 'inbox_outbox', 'view' => $view, 'archived' => $archived), array('onclick' => '$("#frm_action").val("Yes");'));?></li><?php
			}
		}
		// added delete button for inbox,outbox and draft
		if($view != 'addnew'){?>
			<li class="del_but"><a href="javascript: void(0);" onclick="$('#del_form').submit();">Delete</a></li><?php
		}
		?>
    </ul>
</div>
<script language="javascript" type="text/javascript">
	function submitForm()
	{
		<?php if($task == 'addnew' && empty($send_mail))
		{ ?>
		var get = $('ul.token-input-list-facebook > li > p').text();		
		if(!get)
		{
			 $('input#token-input-recipient').focus();
			 $('ul.token-input-list-facebook').css('border','1px solid red');
			return false;
		}
		<?php }
		?>
		if ($("#patient_id").val() == "")
		{
			$("#patient").val("");
		}
		$('#frm').submit();
	}
	</script>
<?php
}
else
{
	?>
<div style="overflow: hidden;">
    <form id="frm" method="post" action="<?php echo $thisURL. '/task:'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <table cellpadding="0" cellspacing="0" class="listing">
            <tr>
                <th width="2%"><label for='master_chk' class='label_check_box'><input type="checkbox" id="master_chk" class="master_chk" /></label></th>
                <th><?php
				if ($view == "inbox")
				{
					echo $paginator->sort('From', 'Sender.firstname', array('model' => 'MessagingMessage'));
				}
				else
				{
					echo $paginator->sort('To', 'Recipient.firstname', array('model' => 'MessagingMessage'));
				}
				?></th>
                <th width="13%"><?php echo $paginator->sort('Type', 'type', array('model' => 'MessagingMessage'));?></th>
                <?php if($user_role_id != 8) { 
                	echo "<th width='20%'>".$paginator->sort('Patient', 'MessagingMessage.patient_search_name', array('model' => 'MessagingMessage'))."</th>"; } 
                ?>
		<th width="25%"><?php echo $paginator->sort('Subject', 'MessagingMessage.subject', array('model' => 'MessagingMessage'));?></th>
                <th width="10%"><?php echo $paginator->sort('Priority', 'MessagingMessage.priority', array('model' => 'MessagingMessage'));?></th>
                <th width="17%"><?php 
                if ($view == "drafts")
				{ 
				 echo $paginator->sort('Date', 'MessagingMessage.created_timestamp', array('model' => 'MessagingMessage'));
				}
				else
				{
				 echo $paginator->sort('Sent', 'MessagingMessage.created_timestamp', array('model' => 'MessagingMessage'));
				}
                ?></th>
            </tr>
            <?php
			$i = 0;
			foreach ($MessagingMessages as $MessagingMessage):
			if ($view == "drafts")
			{
            	?><tr editlink="<?php echo $html->url(array('action' => 'inbox_outbox', 'view' => $view, 'task' => 'edit', 'message_id' => $MessagingMessage['MessagingMessage']['message_id']), array('escape' => false)); ?>" style="font-style:italic"><?php
			}
			else
			{
            	//If the message type is Appointment and the status is 'Done', the users can't open it
				?><tr editlink="<?php echo $html->url(array('action' => 'inbox_outbox', 'view' => $view, 'archived' => $archived, 'task' => 'edit', 'message_id' => $MessagingMessage['MessagingMessage']['message_id']), array('escape' => false)); ?>" <?php echo (($view == "inbox" or $view == "drafts")&& ($MessagingMessage['MessagingMessage']['status'] == 'New'))? 'style="font-weight:bold"':''?>><?php
			}
			?>
                <td class="ignore">
                <label for='child_chk<?php echo $MessagingMessage['MessagingMessage']['message_id']; ?>' class='label_check_box'>
                <input name="data[MessagingMessage][message_id][<?php echo $MessagingMessage['MessagingMessage']['message_id']; ?>]" id='child_chk<?php echo $MessagingMessage['MessagingMessage']['message_id']; ?>' type="checkbox" class="child_chk" value="<?php echo $MessagingMessage['MessagingMessage']['message_id']; ?>" /></label></td>
                <td><?php
					if ($view == "inbox")
					{
						echo $MessagingMessage['Sender']['firstname']." ".$MessagingMessage['Sender']['lastname'];
					}
					else
					{
						if($user_role_id==8)
						{
							echo 'Advice Nurse';
						}
						else
						{
							echo $MessagingMessage['Recipient']['firstname']." ".$MessagingMessage['Recipient']['lastname'];
						}
					}
					?></td>
                <td><?php echo $MessagingMessage['MessagingMessage']['type']; ?></td>
                <?php if($user_role_id != 8) {  echo "<td>".$MessagingMessage['Patient']['first_name']." ".$MessagingMessage['Patient']['last_name']."</td>"; } ?>
		<td><?php echo $MessagingMessage['MessagingMessage']['subject']; ?></td>
                <td><?php 
			if($MessagingMessage['MessagingMessage']['priority'] == 'High' || $MessagingMessage['MessagingMessage']['priority'] == 'Urgent')
				echo '<font color=#FF0000>'.$MessagingMessage['MessagingMessage']['priority'].'</font>'; 
			else if($MessagingMessage['MessagingMessage']['priority'] == 'Low')
				echo '<font color=blue>'.$MessagingMessage['MessagingMessage']['priority'].'</font>'; 
			else
				echo $MessagingMessage['MessagingMessage']['priority']; 							

		     ?></td>
                <td><?php echo __date("$global_date_format, g:i:s A", strtotime($MessagingMessage['MessagingMessage']['created_timestamp'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </form>
    <div style="width: auto; float: left;">
        <div class="actions">
            <ul>
                <?php			   
				if($view == 'inbox')
				{
			    ?>
                <li><?php echo $html->link(__('Compose Message', true), array('action' => 'inbox_outbox', 'view' => $view, 'archived' => $archived, 'task' => 'addnew')); ?></li>
				<?php
				}
				?>
                <li><a href="javascript: void(0);" onclick="selectData('delete');">Delete Selected</a></li>
				<?php
				if ($view != "drafts")
				{
			    ?>
                <li><a href="javascript: void(0);" onclick="selectData('archive');">Archive Selected</a></li>
				<?php
				}
				?>
            </ul>
        </div>
    </div>
        <div class="paging"> <?php echo $paginator->counter(array('model' => 'MessagingMessage', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('MessagingMessage') || $paginator->hasNext('MessagingMessage'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('MessagingMessage'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'MessagingMessage', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
            <?php echo $paginator->numbers(array('model' => 'MessagingMessage', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('MessagingMessage'))
					{
						echo $paginator->next('Next >>', array('model' => 'MessagingMessage', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
        </div>
</div>
<script language="javascript" type="text/javascript">
		function selectData(type)
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
			{
				/*var answer = confirm(ucwords(type) + " Selected Item(s)?")
				if (answer)
				{*/
					$("#frm").attr('action', $("#frm").attr('action') + type); 
					$("#frm").submit();
				//}
			}
			else
			{
				alert("No Item Selected.");
			}
		}
		function ucwords(str)
		{
			return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1){ return $1.toUpperCase(); });
		}
	</script>
<?php
}
?>
<script language="javascript" type="text/javascript">
<?php
if (($view == "inbox" && $task == "addnew") || ($view == "drafts" && $task == "addnew"))
{ ?>
	var isRefresh = "No";
	$(document).keypress(function(e)
	{
		if (e.ctrlKey == true && keycode == 114)
		{
			isRefresh = "Yes";
		}
	});
	$(document).keydown(function(e)
	{
		if (e.keyCode == 116)
		{
			isRefresh = "Yes";
		}
	});
	var userAgent = navigator.userAgent.toLowerCase();
	$.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());  
        
        /*$(window).unload(function()
        {
                saveDraft();
        });*/
        
        window.onbeforeunload = function(){
			 saveDraft();
		};
                
	function saveDraft()
	{
            
                var emptyForm = true;
                
                // Do not save if form is empty
                $('#frm').find('input[type=text], textarea').each(function(){
                    var val = $.trim($(this).val());
                    
                    if (val != '') {
                        emptyForm = false;
                    }
                    
                });
                
                if (emptyForm) {
                    return false;
                }
            
            
		if ($("#frm_action").val() != "Yes" && isRefresh != "Yes")
		{
                        $('#ajax-loader').show();
			var formobj = $("<form></form>");
			if ($('#recipient_id').val())
			{
				formobj.append('<input name="data[MessagingMessage][recipient_id]" type="hidden" value="'+$('#recipient_id').val()+'">');
			} else {
                            
                            var recipient = $.trim($('#recipient').val());
                            
                            if (recipient) {
                                formobj.append('<input name="data[MessagingMessage][recipient]" type="hidden" value="'+recipient+'">');
                            }
                            
                        }
			formobj.append('<input name="data[MessagingMessage][type]" type="hidden" value="'+$('#type').val()+'">');
			if ($('#patient_id').val())
			{
				formobj.append('<input name="data[MessagingMessage][patient_id]" type="hidden" value="'+$('#patient_id').val()+'">');
			}
			formobj.append('<input name="data[MessagingMessage][priority]" type="hidden" value="'+$('#priority').val()+'">');
			formobj.append('<input name="data[MessagingMessage][subject]" type="hidden" value="'+$('#subject').val()+'">');
			if($('#attachment_is_selected').val() == 'true' && $('#attachment_is_uploaded').val() == "false")
			{
				//$('#file_upload').uploadifyUpload();
			}
			formobj.append('<input name="data[MessagingMessage][attachment]" type="hidden" value="'+$('#attachment').val()+'">');
			formobj.append('<input name="data[MessagingMessage][message]" type="hidden" value="'+$('#txtreply').val()+'">');
			formobj.append('<input name="data[MessagingMessage][status]" type="hidden" value="Draft">');
			<?php
				if ($view == "drafts" && $task == "addnew") {
			?>
			formobj.append('<input name="data[MessagingMessage][message_id]" type="hidden" value="<?php echo $this->params['named']['message_id']; ?>">');
			<?php } ?>

                        $.ajaxSetup({
                            async: false
                        });
                        
			$.post("<?php echo $this->Session->webroot; ?>messaging/inbox_outbox/task:save_draft/", formobj.serialize());
                        

		}
	}
        
	function sleep(milliseconds)
	{
		var start = new Date().getTime();
		for (var i = 0; i < 1e7; i++)
		{
			if ((new Date().getTime() - start) > milliseconds)
			{
				break;
			}
		}
	}
<?php
} ?>
</script>
