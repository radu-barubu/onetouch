<h2>Preferences</h2>
<?php
$user = $this->Session->read('UserAccount');
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteImg = $this->Html->image('del.png', array('alt' => 'delete'));

extract($preferences_account);

$signature_image_desc = "";
if(strlen($signature_image) > 0)
{
	$pos = strpos($signature_image, '_') + 1;
	$signature_image_desc = substr($signature_image, $pos);
}

?>
  
<script language="javascript" type="text/javascript">
    var user_id = '<?php echo $user_id; ?>';
    var save_photo_url = '<?php echo $html->url(array('controller' => 'preferences', 'action' => 'save_photo')); ?>';
    var blank_img = '<?php echo $this->Session->webroot.'img/blank.png'; ?>';
    var ajax_loader = '<?php echo $this->Session->webroot.'img/ajax_loaderback.gif'; ?>';
    var url_abs_path = '<?php echo $url_abs_paths['preferences']; ?>';    
    
    $(document).ready(function()
    {
        
        $('.delete-image').each(function(){
            
            if ($(this).is('.hide')) {
                $(this).hide();
            }
            
            $(this).click(function(evt){
               evt.preventDefault();
               
               var 
                    self = this,
                    url = $(this).attr('href'),
                    patient_id = $(this).attr('rel');

               $(self).closest('td')
                    .find('.photo_area_text').append($('<img />').attr('src', ajax_loader));


               $.post(url, {patient_id: patient_id}, function(){
                   var 
                    data = 
                       $(self).closest('td')
                            .find('.p_img').attr('src', blank_img)
                            .end()
                            .find('.photo_area_text').html('Image Not Available')
                            .end()
                            .find('.cloud-zoom')
                                .data('zoom')
                            
                   if (data) {
                       data.destroy();
                   }
                            
                   $(self).hide();
               });
               
               
            });
            
            
            
        });        
        
        
        
        $("#frmAccount").validate(
        {
            errorElement: "div",
            rules: 
            {
				'data[password][password1]': 
				{
					required: false,
					minlength: 6
				},
				'data[password][password2]': 
				{
					required: false,
					equalTo: "#password1",
					minlength: 6
				},
                'data[UserAccount][email]': 
                {
                    required: true,
                    email: true
                },
                'data[UserAccount][notification_email]': 
                {
                    required: false,
                    email: true
                }
            },
			submitHandler: function(form) 
			{
				$.post(
					'<?php echo $html->url(array('controller' => 'preferences', 'action' => 'system_settings', 'task' => 'check_password')); ?>', 
					$('#frmAccount').serialize(), 
					function(data)
					{
						if(data.valid)
						{
							form.submit();
						}
						else
						{
							$('#password').addClass("error");
							$('<div htmlfor="password" generated="true" class="error" style="display: block;">Invalid Password.</div>').insertAfter($('#password'));
							$('#password').focus();
						}
					},
					'json'
				);
			}
        });
		
		$("#webcam_capture_area").dialog(
		{
			width: 730,
			height: 455,
			modal: true,
			resizable: false,
			autoOpen: false
		});
		
		$('.alert_preference_opt').click(function()
		{
			
			if($(this).val() == 2)
			{
				$('.carrier_area').show();
			}
			else
			{
				$('.carrier_area').hide();
			}
		});
		
        var btn_upload_width = parseInt($('#account_select_photo_button').width()) + 
            parseInt($('#account_select_photo_button').css("padding-left").replace("px", "")) + 
            parseInt($('#account_select_photo_button').css("padding-right").replace("px", "")) + 
            parseInt($('#account_select_photo_button').css("margin-left").replace("px", "")) + 
            parseInt($('#account_select_photo_button').css("margin-right").replace("px", ""))
        ;
        
        var btn_upload_height = parseInt($('#account_select_photo_button').height()) +
            parseInt($('#account_select_photo_button').css("padding-top").replace("px", "")) + 
            parseInt($('#account_select_photo_button').css("padding-bottom").replace("px", "")) + 
            parseInt($('#account_select_photo_button').css("margin-top").replace("px", "")) + 
            parseInt($('#account_select_photo_button').css("margin-bottom").replace("px", ""))
        ;
		
		$('#photo').uploadify(
		{
			'fileDataName' : 'file_input',
    		'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
			'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
			'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
			'scriptData': {'data[path_index]' : 'temp'},
			'auto'      : true,
			'height'    : btn_upload_height,
			'width'     : btn_upload_width,
			'wmode'     : 'transparent',
			'hideButton': true,
			'imageArea'	: 'photo_img',
			'fileDesc'  : 'Image Files',
			'fileExt'   : '*.gif; *.jpg; *.jpeg; *.png;', 
			'onSelect'  : function(event, ID, fileObj) 
			{
				$('#photo_img').attr('src', webroot + 'img/blank.png');
				$('#photo_area_div').html("Uploading: 0%");
				return false;
			},
			'onProgress': function(event, ID, fileObj, data) 
			{
				$('#photo_area_div').html("Uploading: "+data.percentage+"%");
				return true;
			},
			'onOpen'    : function(event, ID, fileObj) 
			{
				return true;
			},
			'onComplete': function(event, queueID, fileObj, response, data) 
			{
				var url = new String(response);
				var filename = url.substring(url.lastIndexOf('/')+1);
                                
				$('#photo_area_div')
                                    .html("")
                                    .closest('td').find('.delete-image:hidden').show();
                                    
                                setTimeout(function(){
                                    $('#photo_img').attr('src', url_abs_path + filename);
                                }, 1000);
                                
				
				$('#photo_val').val(filename);
				$('#photo_is_uploaded').val('true');
				saveUserPhoto(user_id, filename);
				
				return true;
			},
			'onError'   : function(event, ID, fileObj, errorObj) 
			{
				return true;
			}
		});
		/*<?php if($role_id != 3): ?>
		$(".physician_only").hide();
		<?php endif; ?>*/
    });
	
	function saveUserPhoto(user_id, photo)
	{
		if(user_id == '')
		{
			return;
		}
		
		var formobj = $("<form></form>");
		formobj.append('<input name="data[UserAccount][user_id]" type="hidden" value="'+user_id+'">');
		formobj.append('<input name="data[UserAccount][photo]" type="hidden" value="'+photo+'">');
	
		$.post(
			save_photo_url, 
			formobj.serialize(), 
			function(data){
				$('#photo_img').attr('src', '<?php echo $url_abs_paths['preferences']; ?>' + photo);
				$('#photo_area_div')
                    .html("")
                    .closest('td').find('.delete-image:hidden').show();
				$("#webcam_capture_area").dialog("close");
			}
		);
	}

	function updatePhoto(response)
	{
		var url = new String(response);
		var filename = url.substring(url.lastIndexOf('/')+1);
		$('#photo_val').val(filename);
		saveUserPhoto(user_id, filename);
	}

</script>
<style>
	<?php if($alert_preference != "2"): ?>
	.carrier_area { display: none; }
	<?php endif; ?>
</style>

<div id="webcam_capture_area" title="Webcam Capture" style="display: none;">
	<?php

	$url_port = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':'.$_SERVER['SERVER_PORT'];
	$url_pre = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$url_port : "http://".$_SERVER['SERVER_NAME'].$url_port;
	
	?>
	<script language="javascript" type="text/javascript">
		function webcam_callback(data)
		{
			updatePhoto(data);
		}
	</script>
	<div id="flashArea" class="flashArea" style="height:370; margin: 0px; padding: 0px;">
            <p style="text-align: center;">
            Adobe Flash Player Plugin is required to use the Web Cam Capture Feature
                <br />
                <a href="http://get.adobe.com/flashplayer"> <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /> </a>
            </p>
        </div>
	<script type="text/javascript">
	var flashvars = {
	  save_file: "<?php echo $url_pre . $html->url(array('controller' => 'patients', 'action' => 'webcam_save')); ?>",
	  parentFunction: "webcam_callback",
	  snap_sound: "<?php echo $this->Session->webroot; ?>sound/camera_sound.mp3",
	  save_sound: "<?php echo $this->Session->webroot; ?>sound/save_sound.mp3"
	};
	var params = {
	  scale: "noscale",
	  wmode: "window",
	  allowFullScreen: "true"
	};
	var attributes = {};
	swfobject.embedSWF("<?php echo $this->Session->webroot; ?>swf/webcam2.swf", "flashArea", "700", "370", "9.0.0", "<?php echo $this->Session->webroot; ?>swf/expressInstall.swf", flashvars, params, attributes);
	</script>
</div>

<div style="overflow: hidden;">
	<?php echo $this->element('preferences_system_settings_links', array(compact('emergency_access_type', 'user'))); ?>
    <form id="frmAccount" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <input type="hidden" name="data[UserAccount][user_id]" value="<?php echo $user_id; ?>" />
		<table cellpadding="0" cellspacing="0" class="form">
			<tr>
				<td valign="top" style="vertical-align: top; width: 600px;">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td colspan="2"><h3><label>Personal Information</label></h3></td>
						</tr>
						<tr>
							<td width="210"><label>Title:</label></td>
							<td>
								<!--<input type="text" name="data[UserAccount][title]" id="title" value="<?php echo $title;?>" class="field_normal" />-->
								<select name="data[UserAccount][title]" id="title" class="field_normal">
								<option value="">Select...</option>
								<?php 
									  $person_titles = array('Mr.', 'Ms.', 'Mrs.', 'Dr.', 'Prof.');
									  foreach($person_titles as $person_title) { 
								?>
									<option value="<?php echo $person_title;?>" <?php if($person_title==$title) { ?> selected="selected" <?php } ?> ><?php echo $person_title;?></option>
								<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<td ><label>First Name:</label></td>
							<td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[UserAccount][firstname]" id="firstname" value="<?php echo $firstname;?>" class="required field_normal disabled"  disabled="disabled" /></td></tr></table></td>
						</tr>
						<tr>
							<td ><label>Last Name:</label></td>
							<td><table cellpadding="0" cellspacing="0"><tr><td><input type="text" name="data[UserAccount][lastname]" id="lastname" value="<?php echo $lastname;?>" class="required field_normal disabled" disabled="disabled" /></td></tr></table></td>
						</tr>
						
						<tr>
							<td ><label>Web Site:</label></td>
							<td><input type="text" name="data[UserAccount][website]" id="website" value="<?php echo $website;?>" class="field_normal" /></td>
						</tr>
						<?php 
						if(in_array($role_id, $provider_roles)) 
						{
						   echo '<tr>
							<td style="vertical-align:top; padding-top: 5px;"><label>Signature Image:</label></td>
							<td>
                            	<table cellpadding="0" cellspacing="0">
									<tr>
										<td>
											<table cellpadding="0" cellspacing="0">
												<tr>
													<td>
														<div class="file_upload_area" style="position: relative; width: 214px; height: auto !important">
															<div id="sig_file_upload_desc" style="position: absolute; top: 0px; height: 19px; width: 200px; text-align: left; padding: 5px; overflow: hidden; color: #000000;">'.$signature_image_desc.'</div>
															<div id="sig_progressbar" style="-moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"></div>
															<div style="position: absolute; top: 1px; right: -125px;">
																<div style="position: relative;"> <a href="#" class="btn" style="float: left; margin-top: -2px;">Select File...</a>
																	<div style="position: absolute; top: 0px; left: 0px;">
																		<input id="file_upload_sig" name="file_upload_sig" type="file" />
																	</div>
																</div>
															</div>
														</div>
													</td>
												</tr>
												<tr>
													<td style="padding:10px 0 5px 0;">
<span class="error" id="filename_error" style="display:none"></span>
														<input type="hidden" name="data[UserAccount][upload_dir]" id="upload_dir" value="<?php echo $this->Session->webroot; ?>app/webroot/uploads/accounts" />
														<input type="hidden" name="data[UserAccount][signature_image]" id="signature_image" value="'.$signature_image.'">
                                                        <input type="hidden" name="data[UserAccount][sig_is_uploaded]" id="sig_is_uploaded" value="false" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>';
								?>
								
								<script language="javascript" type="text/javascript">
									$(function() 
									{
										$("#sig_progressbar").progressbar({value: 0});
										
										$('#file_upload_sig').uploadify(
										{
											'fileDataName' : 'file_input',
											'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
											'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
											'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
											'scriptData': {'data[path_index]' : 'temp', 'data[max_width]': 600, 'data[max_height]': 200},
											'auto'      : true,
											'height'    : 35,
											'width'     : 95,
											'wmode'     : 'transparent',
											'hideButton': true,
											'fileDesc'  : 'Image Files',
											'fileExt'   : '*.gif; *.jpg; *.jpeg; *.png;', 
											
											'onSelect'  : function(event, ID, fileObj) 
											{
                        
			$('#filename_error').hide();
                        
												$('#sig_file_upload_desc').html(fileObj.name);
												$(".ui-progressbar-value", $("#sig_progressbar")).css("visibility", "hidden");
												$("#sig_progressbar").progressbar("value", 0);
												
												$("#sig_file_upload_desc").css("border", "none");
												$("#sig_file_upload_desc").css("background", "none");
								
												return false;
											},
											'onProgress': function(event, ID, fileObj, data) 
											{
												$(".ui-progressbar-value", $("#sig_progressbar")).css("visibility", "visible");
												$("#sig_progressbar").progressbar("value", data.percentage);

												return true;
											},
											'onOpen' : function(event, ID, fileObj) 
											{
												//$(window).css("cursor", "wait");
											},
											'onComplete': function(event, queueID, fileObj, response, data) 
											{
                        
                        
                        if (/\[Error\]/.test(response)) {
                          $(".ui-progressbar-value", $("#sig_progressbar")).css("visibility", "hidden");
                          $("#sig_progressbar").progressbar("value", 0);

                          $('#sig_is_uploaded').val("false");
                          
                          //$('#file_upload_sig').closest('td').append(
			    $('#filename_error').html(response).show();//;
                            //$('<div />').addClass('error').text(response)
                          //);
                        } else {
                          var url = new String(response);
                          var filename = url.substring(url.lastIndexOf('/')+1);
                          $('#signature_image').val(filename);

                          $(".ui-progressbar-value", $("#sig_progressbar")).css("visibility", "hidden");
                          $("#sig_progressbar").progressbar("value", 0);

                          $('#sig_is_uploaded').val("true");

                          return false;                          
                        }
                        

											},
											'onError' : function(event, ID, fileObj, errorObj) 
											{
											}
										});
									});
								</script>
								<?php
                           echo '</td>
							</tr>';
							
							?>
                            <tr>
                                <td style="vertical-align:top;"><label>Provider PIN:</label></td>
                                <td><div style="float:left;"><input type="text" name="data[UserAccount][provider_pin]" id="provider_pin" value="<?php echo $provider_pin;?>" class="required field_normal" /></div></td>
                            </tr>
                            
                            <?php
						?>
						<?php } ?>						
						<tr>
							<td><label>Degree:</label></td>
							<td>
								<select name="data[UserAccount][degree]" id="degree" class="field_normal">
									<option value="">Select Degree</option>
								<?php 
									  
									  $person_degrees = array('PhD', 'PsyD', 'MD', 'DO', 'NP', 'OD', 'DC', 'PA');
									  foreach($person_degrees as $person_degree) { 
								?>
									<option value="<?php echo $person_degree;?>" <?php if($person_degree==$degree) { ?> selected="selected" <?php } ?> ><?php echo $person_degree;?></option>
								<?php } ?>
								</select>
							</td>
						</tr>

						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2"><h3><label>Contact Information</label></h3></td>
						</tr>
						<tr>
							<td style="vertical-align:top;"><label>Email:</label></td>
							<td><div style="float:left;"><input type="text" name="data[UserAccount][email]" id="email" value="<?php echo $email;?>" class="required field_normal" /></div></td>
						</tr>
						<tr>
							<td><label>Work Phone:</label></td>
							<td>
                            	
                            	<input type="text" name="data[UserAccount][work_phone]" id="work_phone" value="<?php echo $work_phone;?>" class="phone field_normal" /> 
							    Extension: 
						        <input type="text" name="data[UserAccount][work_phone_extension]" id="work_phone_extension" value="<?php echo $work_phone_extension;?>" class="field_smallest" /></td>
						</tr>
						<tr>
							<td ><label>Cell Phone:</label></td>
							<td><input type="text" name="data[UserAccount][cell_phone]" id="cell_phone" value="<?php echo $cell_phone;?>" class="phone field_normal" /></td>
						</tr>
<?php
if($session->read("UserAccount.role_id") != EMR_Roles::PATIENT_ROLE_ID)
{
?>
                        <tr>
							<td ><label>Default Work Location:</label></td>
							<td>
                            	<select id="work_location" name="data[UserAccount][work_location]" class="field_normal">
									<option value="">Select Location</option>
									<?php
									foreach($work_locations as $location_id => $location_name)
									{
										?>
										<option value="<?php echo $location_id; ?>" <?php if($work_location == $location_id) { echo 'selected="selected"'; } ?>><?php echo $location_name; ?></option>
										<?php
									}
									
									?>
								</select>
                            </td>
						</tr>
<?php
}
?>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2"><h3><label>Notification</label></h3></td>
						</tr>
						<tr>
							<td ><label>Alert Preference:</label></td>
							<td><table border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 8px;">
									<tr>
                                    	<td>
										<select name="data[UserAccount][alert_preference]" class="alert_preference_opt" id="alert_preference " style="width: 150px;">
										<option value="" selected>Select Preference</option>
							 
                             <option value="1" <?php echo ($alert_preference =='1'? "selected='selected'":''); ?>>Phone</option>
                             <option value="0" <?php echo ($alert_preference =='0'? "selected='selected'":''); ?> > Email</option>
							  <option value="2" <?php echo ($alert_preference =='2'? "selected='selected'":''); ?> > SMS</option>
							 </select>
										
										<!--<input type="radio" class="alert_preference_opt" name="data[UserAccount][alert_preference]" id="alert_phone" value="1" <?php if($alert_preference == "1") { echo 'checked="checked"'; } ?>></td>
										<td><label for="alert_phone">Phone</label></td>
										<td width="18">&nbsp;</td>
										<td width="15"><input type="radio" class="alert_preference_opt" name="data[UserAccount][alert_preference]" id="alter_email" value="0" <?php if($alert_preference == "0") { echo 'checked="checked"'; } ?>></td>
										<td><label for="alter_email">Email</label></td>
										<td width="18">&nbsp;</td>
										<td width="15"><input type="radio" class="alert_preference_opt" name="data[UserAccount][alert_preference]" id="alert_sms" value="2" <?php if($alert_preference == "2") { echo 'checked="checked"'; } ?>></td>
										<td><label for="alert_sms">SMS</label></td>
										<td width="18">&nbsp;</td>-->
										</td>
									</tr>
								</table></td>
						</tr>
						<tr class="carrier_area">
							<td><label>Cell Phone Carrier:</label></td>
							<td>
								<select id="carrier_id" name="data[UserAccount][carrier_id]" class="field_normal">
									<option value="">Select Carrier</option>
									<?php
									foreach($sms_carriers as $sms_carrier)
									{
										?>
										<option value="<?php echo $sms_carrier['SmsCarrier']['carrier_id']; ?>" <?php if($carrier_id == $sms_carrier['SmsCarrier']['carrier_id']) { echo 'selected="selected"'; } ?>><?php echo $sms_carrier['SmsCarrier']['carrier_name']; ?></option>
										<?php
									}
									
									?>
								</select>
							</td>
						</tr>
                        <tr>
							<td colspan="2">&nbsp;</td>
						</tr>
                        <tr>
							<td colspan="2"><h3>
							    <label>Change Password (Optional)</label></h3></td>
						</tr>
                        <tr>
							<td ><label>Current Password:</label></td>
							<td><input type="password" name="data[password][password]" id="password" value="" class="field_normal" /></td>
						</tr>
                        <tr>
							<td ><label>New Password:</label></td>
							<td><input type="password" name="data[password][password1]" id="password1" value="" class="field_normal" /></td>
						</tr>
                        <tr>
							<td ><label>Confirm Password:</label></td>
							<td><input type="password" name="data[password][password2]" id="password2" value="" class="field_normal" /></td>
						</tr>
					</table>
				</td>
				<td style="padding-top: 30px; width: 170px;" height="35">
                                    <?php echo $this->Html->link(
                                            $deleteImg, 
                                            array(
                                                'controller' => 'preferences',
                                                'action' => 'remove_photo',
                                                'user_id' => $user_id,
                                            ), array(
                                                'rel' => $user_id,
                                                'class' => 'delete-image ' . ((strlen($photo) > 0) ? '' : 'hide'),
                                                'escape' => false,
                                                'style' => 'float: left; position: relative; top: -15px; z-index: 9999; left: 130px;'
                                            )); ?>                                    
					<div class="photo_area_vertical">
						<img id="photo_img" class="photoimgvert p_img" src="<?php echo (strlen($photo) > 0) ? $url_abs_paths['preferences'].$photo : $this->Session->webroot.'img/blank.png'; ?>">
						<div class="photo_area_text" id="photo_area_div"><?php echo (strlen($photo) > 0) ? "" : 'Image Not Available'; ?></div>
					</div>
					 <div class="photo_upload_control_area" style="margin-top:7px;">
                        <div class="btn_area">
                        	<span id="account_select_photo_button" class="btn">Select Photo...</span><img title="Webcam Capture" onclick="current_photo_mode = 'photo'; $('#webcam_capture_area').dialog('open');" src="<?php echo $this->Session->webroot . 'img/webcam.png'; ?>" width="16" height="16" />
                        </div>
                        <div class="uploadfield">
                            <input id="photo" name="photo" type="file" />
                            <input type="hidden" name="data[UserAccount][photo]" id="photo_val" value="<?php echo $photo; ?>" />
														<input type="hidden" name="data[UserAccount][photo_is_uploaded]" id="photo_is_uploaded" value="false" />
                        </div>
                    </div>
				</td>
			</tr>
		</table>
		
        <div class="actions">
            <ul>
                <li><a href="javascript: void(0);" onclick="$('#frmAccount').submit();">Save</a></li>
            </ul>
        </div>
    </form>
</div>
