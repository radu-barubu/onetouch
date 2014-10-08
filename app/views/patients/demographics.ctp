<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$url_abs_paths['patient_id'] = $url_abs_paths['patients'] . $patient_id . DS; 
$paths['patient_id'] = $paths['patients'] . $patient_id . DS; 

UploadSettings::createIfNotExists($paths['patient_id']);

$deleteImg = $this->Html->image('del.png', array('alt' => 'delete'));

echo $this->Html->script('ipad_fix.js');
echo $this->Html->script('jquery/cloud-zoom.1.0.2.js?'.time());
echo $this->Html->css('cloud-zoom.css');

if($task == 'edit')
{
	$patient_user_id = (isset($patient_user_id)) ? $patient_user_id : "";
	extract($EditItem['PatientDemographic']);
	$id_field = '<input type="hidden" name="data[PatientDemographic][patient_id]" id="patient_id" value="'.$patient_id.'" />';
	$dob = __date($global_date_format, strtotime($dob));
}
else
{
	//Init default value here
	$id_field = "";
	if(!empty($search['first_name']))
	$first_name = $search['first_name'];
	else
	$first_name = "";
	
	$middle_name = "";
	if(!empty($search['last_name']))
	$last_name = $search['last_name'];
	else
	$last_name = "";
	
	$address1 = "";
	$address2 = "";
	$city = "";
	$state = $StateCodes;
	$zipcode = "";
	$gender = "";
	if(!empty($search['dob']))
	$dob = $search['dob'];
	else
	$dob = "";
	
	if(!empty($search['ssn']))
	$ssn = $search['ssn'];
	else
	$ssn = "";
	
	$marital_status = "";
	$race = "";
	$ethnicity = "";
	$preferred_language = 'English';
	$home_phone = "";
	$email = "";
	$work_phone = "";
	$work_phone_extension = "";
	$cell_phone = "";
	$driver_license_id = "";
	$driver_license_state = "";
	$occupation = "";
	$emergency_contact = "";
	$emergency_phone = "";
	$patient_photo = "";
	$driver_license = "";
	$custom_patient_identifier='';
	$immtrack_county = "";
	$immtrack_country = "US";
	$guardian = "";
	$relationship = "";
	$immtrack_vfc = "";
	$status = "New";
	$immtrack_county = "";
	$mrn = "";
}

?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "general_information"))); ?>

<script language="javascript" type="text/javascript">
var allowduplicate = 0;
var check_mrn_url = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'demographics', 'patient_id' => $patient_id, 'task' => 'check_mrn')); ?>';
var thisURL = '<?php echo $thisURL; ?>';
var webroot = '<?php echo $this->webroot; ?>';
var uploadify_script = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>';
var patient_id = '<?php echo $patient_id; ?>';
var save_photo_url = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'demographics', 'task' => 'save_photo')); ?>';
var blank_img = '<?php echo $this->Session->webroot.'img/blank.png'; ?>';
var ajax_loader = '<?php echo $this->Session->webroot.'img/ajax_loaderback.gif'; ?>';

<?php if($task == 'edit'): ?>
	var url_abs_path = '<?php echo UploadSettings::toURL(UploadSettings::existing($paths['patient_id'], $paths['patients'])); ?>';
<?php else: ?>
var url_abs_path = '<?php echo $url_abs_paths['temp']; ?>';
<?php endif; ?>

<?php if ($patient_checkin_id) { ?>
  var patientCheckin=1;
  function goToNext() {
        NextUrl='<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'general_information', 'task' => 'edit', 'nexttab' => 'patient_preferences', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?>';
	setTimeout("location.href=NextUrl;",300);
 }
<?php } else { ?>
var patientCheckin='';
<?php } ?>
$(document).ready(function(){
        $("select#state").change(function(){
          $.getJSON("<?php echo $html->url(array('controller' => 'patients', 'action' => 'demographics_counties'));?>/"+$(this).val(),function(j){
                    var options = '';
                    for (key in j) {
                        options += '<option value="' + key + '">' + j[key]+ '</option>';
                    }
                    $("select#immtrack_county").html(options);
            });
        });
        
        $('#dl_state_btn').buttonset();
        
        $("#no_home_phone").click(function() {
          if($("#no_home_phone").is(":checked")) {
            $('#home_phone').val('000-000-0000');
            $('#home_phone').valid();
          } 
        });
        
        if($('#home_phone').val()=='000-000-0000') {
           $('#no_home_phone').attr('checked','checked')		
        }
});
	function chk_duplicate()
	{
	   if(allowduplicate)
	   {
	   	$("html, body").animate({ scrollTop: $('#checkPatient_result').offset().top - 60 }, 600);	
	   } 
	}
	// if patient status is set to PENDING, remove required fields to allow minimal info capture. can finalize later when patient arrives 
	function checkPending()
	{
	  if($('#status').val() == 'Pending')
	  {
	     $("#address1,#preferred_language,#race,#ethnicity,#city,#state,#zipcode,#gender").removeClass('required');
	     $("#frm_demographics").validate().element("#address1");
	     $("#frm_demographics").validate().element("#preferred_language");
	     $("#frm_demographics").validate().element("#race");
	     $("#frm_demographics").validate().element("#ethnicity");
	     $("#frm_demographics").validate().element("#city");
	     $("#frm_demographics").validate().element("#state");
	     $("#frm_demographics").validate().element("#zipcode");
	     $("#frm_demographics").validate().element("#gender");	     
	  }
	}

  <?php if(isset($patient_user)): ?>
	$("#send_email").click(function() {
	  var _href="/messaging/inbox_outbox/view:inbox/archived:0/task:addnew/patient_id:<?php echo $patient_id; ?>/send_to:<?php echo $patient_user['UserAccount']['user_id'];?>/send_mail:"+$('#email').val();
		location.href=_href; 
	});
  <?php endif;
	if ($session->read("UserAccount.role_id") != EMR_Roles::PATIENT_ROLE_ID) {
   ?>
	function sendEmailLink() 
	{
	  if($('#email').val() != '') {
		$('#send_email').addClass("smallbtn");
		$('#send_email').html('Send Email');
	  } else {
		$('#send_email').html('');
		$('#send_email').removeClass('smallbtn');
	  }
	}
	sendEmailLink();
	<?php } ?>

  	function SubmitCheck()
  	{
  	  <?php if(empty($patient_checkin_id)): ?>
		checkPending();  
		chk_duplicate(); 
		if($('#user_frm_link').hasClass('create-patient-account'))
		{ 
			if (!allowduplicate) 
			{ 
			   $('#frm_demographics').submit();
			}
		} 
		else 
		{ 
			if (!allowduplicate) 
			    $('#user_frm').submit();
		} 
		sendEmailLink();
	  <?php else: ?>
	     $('#frm_demographics').submit();
	  <?php endif; ?>   	 	
  	}

</script>
<?php echo $this->Html->script(array('sections/patient_demographics.js?'.md5(microtime()))); ?>
<div id="webcam_capture_area" title="Webcam Capture">
        <?php

        $url_port = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':'.$_SERVER['SERVER_PORT'];
        $url_pre = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$url_port : "http://".$_SERVER['SERVER_NAME'].$url_port;
        
        ?>
        <script language="javascript" type="text/javascript">
                function webcam_callback(data)
                {
                        updateWebcamPhoto(data);
                }
        </script>
        <div id="flashArea" class="flashArea" style="height:370; margin: 0px; padding: 0px;"></div>
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
        swfobject.embedSWF("<?php echo $this->Session->webroot; ?>swf/webcam.swf", "flashArea", "700", "370", "9.0.0", "<?php echo $this->Session->webroot; ?>swf/expressInstall.swf", flashvars, params, attributes);
        </script>
        <object width="0" height="0" type="application/x-shockwave-flash">
        <p>Please <a href="http://get.adobe.com/flashplayer/" target=_blank>install Adobe Flash</a> to use this Web Cam feature</p>
        </object>
</div>

<!--<?php if(!$isEmdeonOk): ?><div class="error">Error: Unable to synchronize with Emdeon.</div><br /><?php endif; ?>-->
<!--<?php if(!$isDosespotOk): ?><div class="error">Error: Unable to synchronize with Dosespot.</div><br /><?php endif; ?>-->
<div class="id_hide" id="checkPatient_result"></div>

<?php if (($session->read("UserAccount.role_id") != EMR_Roles::PATIENT_ROLE_ID) && $status == 'Pending'): ?> 
<div class="notice" style="float: left; width: 99%; height: 25px; text-align: right; margin: 10px auto; position: static; padding: 5px;">
    This patient is still pending and waiting for approval 
    <?php echo $this->Html->link('Approve', array(
        'controller' => 'patients',
        'action' => 'demographics',
        'task' =>  'approve',
        'patient_id' => $patient_id,
    ), array(
        'class' => 'btn',
        'style' => 'float: none;'
    )); ?>
</div>
<br style="clear: both;"/>
<?php endif;?> 

<?php  
//patient portal patient_checkin_id 
if(!empty($patient_checkin_id)):
?>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>
    <td>Please review and update your information we have on file and ensure it is accurate. The areas with red asterisk<span class='asterisk'>*</span> are required. </td>
    <td style="width:120px">When finished  <button class="btn" onclick="SubmitCheck();">Next >> </button></td>
  </tr>
</table>  
</div>
<?php endif; ?>
<form id="frm_demographics" method="post" accept-charset="utf-8">
    <?php echo $id_field; ?>
    <div style="float: left; width: 53%;">    
        <table width="100%" cellpadding="0" cellspacing="0" class="form">
<?php if($mrn): ?>
            <tr>
                <td width="180"><label>MRN:</label></td>
                <td><input name="data[PatientDemographic][mrn]" type="text" class="field_normal" id="mrn" value="<?php echo $mrn; ?>" maxlength="15" readonly="readonly" style="background:#eeeeee;"  /></td>
            </tr>
<?php else: ?>
 		<br /><input name="data[PatientDemographic][mrn]" type="hidden" id="mrn" value="">
<?php endif; ?>
            <tr>
                <td><label>First Name: <span class='asterisk'>*</span></label></td>
                <td><input name="data[PatientDemographic][first_name]" type="text" class="required field_normal" id="first_name" value="<?php echo $first_name; ?>" maxlength="25" onkeyup="checkPatient()"/></td>
            </tr>
            <tr>
                <td><label>Middle Name:</label></td>
                <td><input name="data[PatientDemographic][middle_name]" type="text" class="field_normal" id="middle_name" value="<?php echo $middle_name; ?>" maxlength="25" /></td>
            </tr>
            <tr>
                <td><label>Last Name: <span class='asterisk'>*</span></label></td>
                <td><input name="data[PatientDemographic][last_name]" type="text" class="required field_normal" id="last_name" value="<?php echo $last_name; ?>" maxlength="25" onkeyup="checkPatient()"/><span id="checkPatient_load" style="float: none; display:none; margin-left: 5px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
            </tr>
           <tr>
                <td style="vertical-align:top; padding-top: 3px;">
                    <label>DOB: <span class='asterisk'>*</span></label>                </td>
                <td><?php echo $this->element("date", array('name' => 'data[PatientDemographic][dob]', 'id' => 'dob', 'value' => $dob, 'required' => true, 'width' => 170)); ?>                         
                                </td>
            </tr>            
            <tr>
                <td><label>Gender: <span class='asterisk' >*</span></label></td>
                <td>
                                        <select  id="gender" name="data[PatientDemographic][gender]" class="required" style="width: 214px;">
        <option value="" selected>Select Gender</option>
        <option value="M" <?php if($gender=='M') { echo 'selected'; }?>>Male</option>
    <option value="F" <?php if($gender=='F') { echo 'selected'; }?>>Female</option>
        </select>                </td>
            </tr>
			<tr>
                <td><label>Ethnicity: <span class='asterisk'>*</span></label></td>
                <td>
                        <select name="data[PatientDemographic][ethnicity]" id="ethnicity" class="required" style="width: 214px;">
                         <option value="">Select Ethnicity</option>
                    <?php 
                                        foreach($Ethnicities as $ethnicity_item)
                                        {
                                                ?>
                                                <option value="<?php echo $ethnicity_item['Ethnicity']['description']; ?>" <?php if($ethnicity == $ethnicity_item['Ethnicity']['description']) { echo 'selected="selected"'; } ?>><?php echo $ethnicity_item['Ethnicity']['description']; ?></option>
                                                <?php
                                        }

                                        ?>
                        </select>                </td>
            </tr>
			<tr>
                <td><label>Race: <span class='asterisk'>*</span></label></td>
                <td><select name="data[PatientDemographic][race]" id="race" class="required" style="width: 214px;">
                <option value="">Select Race</option>
                  <?php
                                                foreach($Race as $race_item)
                                                {
                                                        ?>
                  <option value="<?php echo $race_item['Race']['race']; ?>" <?php if($race == $race_item['Race']['race']) { echo 'selected="selected"'; } ?>><?php echo $race_item['Race']['race']; ?></option>
                  <?php
                                                }
        
                                                ?>
                </select></td>
            </tr>
			<tr>
                <td><label>Preferred Language: <span class='asterisk'>*</span></label></td>
                <td><select name="data[PatientDemographic][preferred_language]" id="preferred_language" class="required" style="width: 214px;">
                    <option value="">Select Language</option>
                    <?php
                        foreach($PreferredLanguages as $preferred_language_item)
                        {
                            ?>
                            <option value="<?php echo trim($preferred_language_item['PreferredLanguage']['language']); ?>" <?php if(trim($preferred_language) == trim($preferred_language_item['PreferredLanguage']['language'])) { echo 'selected="selected"'; } ?>><?php echo $preferred_language_item['PreferredLanguage']['language']; ?></option>
                            <?php
                        }
                    ?>
                </select></td>
            </tr> 
           <tr>
                <td><label>Address 1: <span class='asterisk'>*</span></label></td>
                <td><input name="data[PatientDemographic][address1]" type="text" id="address1" style="width:300px;" value="<?php echo $address1; ?>" maxlength="64" class="required field_normal" /></td>
                </tr>
            <tr>
                <td><label>Address 2:</label></td>
                <td><input name="data[PatientDemographic][address2]" type="text" id="address2" style="width:300px;" value="<?php echo $address2; ?>" maxlength="64" /></td>
                </tr>
            <tr>
                <td><label>City: <span class='asterisk'>*</span></label></td>
                <td><input name="data[PatientDemographic][city]" type="text" id="city" style="width:200px;" value="<?php echo $city; ?>" maxlength="100" class="required field_normal" /></td>
                </tr>
            <tr>
                <td style="vertical-align:top; padding-top: 3px;"><label>State: <span class='asterisk'>*</span></label></td>
                <td><select name="data[PatientDemographic][state]" id="state" class="required" style="width: 214px;">
                    <option value="">Select State </option>
                    <?php
                
                foreach($StateCode as $state_item)
                {
                    ?>
                    <option  value="<?php echo $state_item['StateCode']['state']; ?>" <?php if($state == $state_item['StateCode']['state']) { echo 'selected="selected"'; } ?>><?php echo $state_item['StateCode']['fullname']; ?></option>
                    <?php
                }

                ?>
                </select></td>
                </tr>
				
				<tr>
                <td style="vertical-align:top; padding-top: 3px;"><label>Zip Code: <span class='asterisk'>*</span></label></td>
                <td>
                                    <table cellpadding="0" cellspacing="0">
                                        <tr>
                                           <td><input name="data[PatientDemographic][zipcode]" type="text" class="required" id="zipcode" size="6" value="<?php echo $zipcode; ?>" maxlength="10" /></td>
                                          <td style="padding-left: 17px;">Country: <select name="data[PatientDemographic][immtrack_country]" id="immtrack_country" style="width: 165px;">
                    <option value="">Select Country</option>
                    <?php
                        foreach($ImmtrackCountries as $immtrack_country_item)
                        {
                            ?>
                            <option value="<?php echo $immtrack_country_item['ImmtrackCountry']['code']; ?>" <?php if($immtrack_country == $immtrack_country_item['ImmtrackCountry']['code']) { echo 'selected="selected"'; } ?>><?php echo $immtrack_country_item['ImmtrackCountry']['code'] . ' - ' . $immtrack_country_item['ImmtrackCountry']['country']; ?></option>
                            <?php
                        }
                    ?>
                    </select></td>
                                        </tr></table>
                                </td>
            </tr>
				
            <!--<tr>
                <td style="vertical-align:top; padding-top: 3px;"><label>Zip Code: <span class='asterisk'>*</span></label></td>
                <td><input name="data[PatientDemographic][zipcode]" type="text" class="required numeric_only" id="zipcode" style="width:200px;" value="<?php echo $zipcode; ?>" maxlength="10" /></td>
                </tr>-->
            <tr>  
            <tr>
                <td><label>Home Phone: <span class='asterisk'>*</span></label></td>
                <td><input type="text" name="data[PatientDemographic][home_phone]" id="home_phone" class="required phone areacode" style="width:100px;" value="<?php echo $home_phone; ?>" />
                  <label for="no_home_phone" class="label_check_box_hx"><input type="checkbox" id="no_home_phone"  /> Not Given</label>
                </td>
             </tr> 
			 <tr>
                <td><label>Work Phone:</label></td>
                <td>
                                    <table cellpadding="0" cellspacing="0">
                                        <tr>
                                           <td><input type="text" name="data[PatientDemographic][work_phone]" id="work_phone" class="phone" style="width:100px;" value="<?php echo $work_phone; ?>" /></td>
                                          <td style="padding-left: 15px;">Extension: <input name="data[PatientDemographic][work_phone_extension]" type="text" id="work_phone_extension" style="width:80px;" value="<?php echo $work_phone_extension; ?>" maxlength="5" /></td>
                                        </tr></table>
                                </td>
            </tr>
            <tr>
                <td><label>Cell Phone:</label></td>
                <td><input type="text" name="data[PatientDemographic][cell_phone]" id="cell_phone" class="phone" style="width:100px;" value="<?php echo $cell_phone; ?>" /></td>
                </tr>       
			<tr>
                <td>
                <P><p> <a removeonread="true" href="javascript: void(0);" class="btn" onclick="SubmitCheck()"><?php echo ($task == 'addnew') ? 'Add' : 'Save'; ?></a><span id="imgLoad" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
                </td>
            </tr>
			<tr>
			<td>&nbsp;</td></tr>
	  </table>
    </div>
    <div style="float: right; width: 47%;">
	<table border="0" cellspacing="0" cellpadding="0" class="photo_table">
            <tr>
                <td width="160">Photo:</td>
                <td width="263">Driver License:</td>
            </tr>
            <tr>
                <td>
                    <?php echo $this->Html->link(
                            $deleteImg, 
                            array(
                                'controller' => 'patients',
                                'action' => 'demographics',
                                'patient_id' => $patient_id,
                                'task' => 'delete_photo',
                            ), array(
                                'rel' => $patient_id,
                                'class' => 'delete-image ' . ((strlen($patient_photo) > 0) ? '' : 'hide'),
                                'escape' => false,
                                'style' => 'float: left; position: relative; top: -15px; z-index: 9999; left: 125px;'
                            )); ?>

                    <div class="photo_area_vertical">
												<?php 
													$imgPath = UploadSettings::existing($paths['patients'].$patient_photo, $paths['patient_id'].$patient_photo);
													
													$imgUrl = UploadSettings::toURL($imgPath);
													
												?> 
                        <img id="photo_img" class="photoimgvert p_img" src="<?php echo (strlen($patient_photo) > 0 && file_exists($imgPath)) ? $imgUrl : $this->Session->webroot.'img/blank.png'; ?>" />
                        <div class="photo_area_text" id="photo_area_div"><?php echo (strlen($patient_photo) > 0 && file_exists($imgPath)) ? "" : 'Image Not Available'; ?></div>
                    </div>
                </td>
                <td>
                    <?php echo $this->Html->link(
                            $deleteImg, 
                            array(
                                'controller' => 'patients',
                                'action' => 'demographics',
                                'patient_id' => $patient_id,
                                'task' => 'delete_license',
                            ), array(
                                'rel' => $patient_id,
                                'class' => 'delete-image ' . ((strlen($driver_license) > 0) ? '' : 'hide'),
                                'escape' => false,
                                'style' => 'float: left; position: relative; top: -15px; z-index: 9999; left: 220px;'
                            )); ?>
                    <div class="photo_area_horizontal">
												<?php 
													$imgPath = UploadSettings::existing($paths['patients'].$driver_license, $paths['patient_id'].$driver_license);
													$imgUrl = UploadSettings::toURL($imgPath);
												?> 											
                        <a rel="position: 'left', zoomWidth: '400', zoomHeight: '300'" class="cloud-zoom" href="<?php echo (strlen($driver_license) > 0 && file_exists($imgPath)) ? $imgUrl : $this->Session->webroot.'img/blank.png'; ?>" ><img id="driving_license_img" class="photoimghor p_img" src="<?php echo (strlen($driver_license) > 0) ? $imgUrl : $this->Session->webroot.'img/blank.png'; ?>" /></a>
                        <div class="photo_area_text" id="driving_license_div"><?php echo (strlen($driver_license) > 0 && file_exists($imgPath)) ? "" : 'Image Not Available'; ?></div>
                    </div>
                </td>
            </tr>
            <tr removeonread="true">
                <td height="35">
                    <div class="photo_upload_control_area">
                        <div class="btn_area">
                                <span id="patient_photo_upload_button" class="btn">Select Photo...</span><img title="Webcam Capture" onclick="current_photo_mode = 'photo'; $('#webcam_capture_area').dialog('open');" src="<?php echo $this->Session->webroot . 'img/webcam.png'; ?>" width="16" height="16" />
                        </div>
                        <div class="uploadfield">
                            <input id="photo" name="photo" type="file" />
                            <input type="hidden" name="data[PatientDemographic][patient_photo]" id="photo_val" value="<?php echo $patient_photo; ?>" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="photo_upload_control_area">
                        <div class="btn_area">
                                <span id="patient_licene_upload_button" class="btn">Select Driver License...</span><img title="Webcam Capture" onclick="current_photo_mode = 'license'; $('#webcam_capture_area').dialog('open');" src="<?php echo $this->Session->webroot . 'img/webcam.png'; ?>" width="16" height="16" />
                        </div>
                        <div class="uploadfield">
                            <input id="driving_license" name="driving_license" type="file" />
                            <input type="hidden" name="data[PatientDemographic][driver_license]" id="driving_license_val" value="<?php echo $driver_license; ?>" />
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <table width="100%" cellpadding="0" cellspacing="0" class="form demographic_a">
            <tr>
                <td width="190"><label>Custom Patient ID: </label></td>
                <td><input name="data[PatientDemographic][custom_patient_identifier]" type="text" id="custom_patient_identifier" value="<?php echo $custom_patient_identifier; ?>" maxlength="32" <?php if (!empty($_SESSION['PracticeSetting']['PracticeSetting']['hl7_engine']) || !empty($_SESSION['PracticeSetting']['PracticeSetting']['kareo_status'])) echo 'readonly="readonly" style="background:#eeeeee;width:200px;"'; else echo 'style="width:200px;"'; ?> />  </td>
            </tr>
            <tr>
                <td width="190"><label>Driver License/ID:</label></td>
                <td><input name="data[PatientDemographic][driver_license_id]" type="text" id="driver_license_id" style="width:200px;" value="<?php echo $driver_license_id; ?>" maxlength="32" /></td>
            </tr>
           <tr>
                <td><label>Driver License State:</label></td>
                <td>Same as Address? 
                
                <?php if($driver_license_state)
                	{
                	  $sshow="''"; $ychk='checked'; $nchk='';
                	}
                	else
                	{
                 	  $sshow="none"; $ychk=''; $nchk='checked';               	
                	}
                ?>
                <span id="dl_state_btn">
                <input type=radio name='dlstate' id="dlstateYes" value='Y' OnClick="$('#dlstate2').css('display','none'); $('#driver_license_state').val(''); "  <?php echo $nchk;?>><label for="dlstateYes"> Yes </label> 
                <input type=radio name='dlstate' id="dlstateNo" value='N' OnClick="$('#dlstate2').css('display','');" <?php echo $ychk;?>><label for="dlstateNo"> No </label>
 		</span>
                <div id="dlstate2" style="display:<?php echo $sshow;?>;">
		<select name="data[PatientDemographic][driver_license_state]" id="driver_license_state" >
                    <option value="">Select State </option>
                    <?php
                
                foreach($StateCode as $state_item2)
                {
                    ?>
                    <option  value="<?php echo $state_item2['StateCode']['state']; ?>" <?php if($driver_license_state == $state_item2['StateCode']['state']) { echo 'selected="selected"'; } ?>><?php echo $state_item2['StateCode']['fullname']; ?></option>
                    <?php
                }

                ?>
                </select>
                </div>
                
                </td>
            </tr>  
            <tr>
                <td><label>Marital Status:</label></td>
                <td>
                                        <select  id="gender" name="data[PatientDemographic][marital_status]" style="width: 214px;">
                                                        <option value="" selected>Select Marital Status</option>
                                                        <?php foreach ($MaritalStatus as $m):?>
                                                                <option value="<?php echo $m['MaritalStatus']['name']; ?>" <?php if ($m['MaritalStatus']['name'] == $marital_status) { echo 'selected="selected"';} ?> ><?php echo $m['MaritalStatus']['name']; ?></option>
                                                        <?php endforeach;?>
                                        </select>
                </td>
            </tr>

            <tr>
                <td><span style="vertical-align:top; padding-top: 3px;">
                    <label>SSN:</label>
                </span></td>
                <td><span style="vertical-align:top; padding-top: 0px;">
                    <input name="data[PatientDemographic][ssn]" type="text" class="ssn" id="ssn" style="width:200px;" value="<?php echo $ssn; ?>" maxlength="15" />
                </span></td>
            </tr> 
	   <tr>
                <td><label>Guardian's Name:</label></td>
                <td><input name="data[PatientDemographic][guardian]" type="text" id="guardian" style="width:200px;" value="<?php echo $guardian; ?>" maxlength="150" /></td>
            </tr>                                
			<tr id='tr_relationship'>
                <td><label>Relationship to Guardian:<span class='asterisk'>*</span></label></td>
                <td>
                    <?php $relationship_array = array("Spouse", "Child", "Other/Unknown", "Foster Care"); ?>
             <!--       <fieldset>
                        <?php
                        foreach($relationship_array as $relationship_item)
                        {
                            ?>
                        <label for="relationship_<?php echo $relationship_item; ?>">
                                    <input type="radio" name="data[PatientDemographic][relationship]" value="<?php echo $relationship_item; ?>" id="relationship_<?php echo $relationship_item; ?>" <?php if($relationship == $relationship_item) { echo 'checked="checked"'; } ?> />
                            <?php echo $relationship_item; ?> </label>
                                  
                                <?php
                        }
                        ?>
                    </fieldset>-->
                                        
                                        <select name="data[PatientDemographic][relationship]" id="relationship" style="width: 214px;">
										<option value="">Select Relationship</option>
                        <?php
                        foreach($relationship_array as $relationship_item)
                        {
                            ?>
                              <option  value="<?php echo $relationship_item; ?>"   <?php if($relationship == $relationship_item) { echo 'selected="selected"'; } ?>><?php echo $relationship_item; ?>
                                                                </option>
                                <?php
                        }
                        ?>
                                                </select>
                                        
                </td>
            </tr> 
            <tr>
                <td><label>Emergency Contact:</label></td>
                <td><input name="data[PatientDemographic][emergency_contact]" type="text" id="emergency_contact" style="width:200px;" value="<?php echo $emergency_contact; ?>" maxlength="48" /></td>
                </tr>
            <tr>
                <td><label>Emergency Phone:</label></td>
                <td><input type="text" name="data[PatientDemographic][emergency_phone]" id="emergency_phone" class="phone" style="width:200px;" value="<?php echo $emergency_phone; ?>" /></td>
                </tr>
            <tr>
                <td><label>Email Address:</label></td>
                <td><input name="data[PatientDemographic][email]" type="text" id="email" style="width:200px;" value="<?php echo $email; ?>" maxlength="64" /> <span id="send_email"></span>
            </td>
          </tr>
                    <?php if ($session->read("UserAccount.role_id") != EMR_Roles::PATIENT_ROLE_ID): ?> 
                	<tr height="10px"><td></td><td></td></tr><!--<?php  //} ?>-->
				<tr>
                   <td width="190"><label>Status:</label></td>
                   <td>
                    <?php $status_array = PatientDemographic::getStatusList(); ?>
                     <select name="data[PatientDemographic][status]" id="status" OnChange="checkPending();" style="width: 214px;">
                     <option value="">Select Status</option>
                     <?php
                      foreach($status_array as $status_item)
                      {
                       ?>
                      <option  value="<?php echo $status_item; ?>"   <?php if($status == $status_item) { echo 'selected="selected"'; } ?>><?php echo $status_item; ?>
                      </option>
                      <?php
                        }
                        ?>
                        </select>
                    </td>
                </tr>
                <?php endif;?>             
		</table>
        <input type="hidden" value="<?php echo __date($global_date_format, time()); ?>" id="dummyDate" />
		<input type="hidden" id="patient_user_user_id" name="data[UserAccount][patient_user_user_id]">
		<input type="hidden" id="patient_user_username" name="data[UserAccount][patient_user_username]">
		<input type="hidden" id="patient_user_password" name="data[UserAccount][patient_user_password]">
		<input type="hidden" name="data[PatientDemographic][immtrack_county]" id="immtrack_county" value="<?php echo $immtrack_county; ?>">
</form>
			<div style="float:left"><table width="100%" cellpadding="0" cellspacing="0" class="form">
                    <?php 
                    if(strlen($patient_id) == 0)
                    {
			$patient_user['UserAccount'] = array();
                    }
				if(empty($patient_checkin_id))
				{
				?>
                        <tr>
                            <td width="<?php echo (count($patient_user['UserAccount']) > 0) ? '180' : '232'; ?>"><label>Patient Portal:</label></td>
                            <td>
                            
                            <?php if (count($patient_user['UserAccount']) > 0): ?> 
                                <?php if ($session->read("UserAccount.role_id") != EMR_Roles::PATIENT_ROLE_ID) { ?><a href="" id="user_frm_link" class="edit-patient-account smallbtn">Edit Account</a> 
                                &nbsp; &nbsp; &nbsp; &nbsp;
                              <?php   echo ' '.$html->link("Send Message Through Portal", array("controller" => "messaging", "action" => "inbox_outbox", "view" => "inbox", "archived" => "0", "task" => "addnew", "patient_id" => $patient_id, 'send_to'=> $patient_user['UserAccount']['user_id'], ), array('class'=>'smallbtn')); ?>
                            <?php    } extract($patient_user['UserAccount']); ?> 
                            <?php else: ?> 
                                <a href="" id="user_frm_link" class="create-patient-account smallbtn"><span removeonread='true'>Create Account for Patient Portal</span></a>    
                            <?php 
                                    $username = "";
                                    $password = "";
                                    $user_id = "0";
                            ?> 
                            <?php endif; ?> 
                            <span id="user_frm_load" style="float: none; display:none; margin-left: 5px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>

                    <div id="user_frm_layout" style="display:none">
                    <form name="user_frm" id="user_frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                    <input type="hidden" id="user_id" name="data[UserAccount][user_id]" value="<?php echo $user_id; ?>" >
                    <table cellpadding="0" cellspacing="0" class="form">
                    <tr class="<?php echo (@$is_patient) ? 'hidden' : ''; ?>"><td colspan=2>(case sensitive)</td></tr>
                    <tr class="<?php echo (@$is_patient) ? 'hidden' : ''; ?>">
                    	
                        <td width="95"><label>Username: <span class='asterisk'>*</span></label></td>
                        <td>
                            <input type="text" name="data[UserAccount][patient_username]" id="username" value="<?php echo $username; ?>" class="field_normal <?php echo (@$is_patient) ? 'disabled' : ''; ?>" <?php echo (@$is_patient) ? 'disabled="disabled"' : ''; ?>>
                        </td>
                    </tr>
                    <?php
                    if ($session->read("UserAccount.role_id") != EMR_Roles::PATIENT_ROLE_ID)
                    {
                        ?>
                        <tr>
                            <td><label>Password: <span class='asterisk'>*</span></label></td>
                            <td>
                                <input type="text" name="data[UserAccount][patient_password]" id="password" value="<?php echo $password; ?>" class="field_normal">
                            </td>
                        </tr>
                        <?php
                    }
                    else
                    {
                        ?>
                        <tr>
                            <td><label>Password: <span class='asterisk'>*</span></label></td>
                            <td>
                                <input type="password" name="data[UserAccount][patient_password]" id="password" value="<?php echo $password; ?>" class="field_normal">
                            </td>
                        </tr>
                        <tr>
                            <td><label>Retype Password: <span class='asterisk'>*</span></label></td>
                            <td>
                                <input type="password" name="data[UserAccount][retype_password]" id="retype_password" value="<?php echo $password; ?>" class="field_normal">
                                
                                
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </table>
                    </form>
				</div>
                
					</td>
				</tr>
				<?php 
				} //if patient patient_checkin_id is not defined
				?>
				<tr>
				<td>
				
				<script language="javascript" type="text/javascript">
				$(document).ready(function()
				{
					<?php if ($session->read("UserAccount.role_id") == EMR_Roles::PATIENT_ROLE_ID) { ?>
				  	$("#user_frm_layout").show('slow');
					<?php } ?>
					$("#user_frm_link").click(function(evt)
					{
                                            // Use event.preventDefault()
                                            // because href="javascript:void(0)" is so web 1.0 ;)
                                            evt.preventDefault();
                                            
                                                // Never rely on the html content to check status
						if ($("#user_frm_link").hasClass('create-patient-account'))
						{
							$("#frm_demographics").validate().element("#first_name");
							$("#frm_demographics").validate().element("#last_name");
							if (!$.trim($("#first_name").val()))
							{
								<?php if (!$isMobile): ?> 
									$("#first_name").focus();
								<?php endif;?> 
							}
							else if (!$.trim($("#last_name").val()))
							{
								<?php if (!$isMobile): ?> 
									$("#last_name").focus();
								<?php endif;?> 
							}
							else
							{
								$("#user_frm_link").hide(); 
								$("#user_frm_load").show();

								var formobj = $("<form></form>");
								formobj.append('<input name="data[UserAccount][role_id]" type="hidden" value="<?php echo EMR_Roles::PATIENT_ROLE_ID; ?>">');
								formobj.append('<input name="data[UserAccount][patient_id]" type="hidden" value="<?php echo $patient_id ?>">');
								formobj.append('<input name="data[UserAccount][firstname]" type="hidden" value="'+$.trim($("#first_name").val().toLowerCase())+'">');
								formobj.append('<input name="data[UserAccount][lastname]" type="hidden" value="'+$.trim($("#last_name").val().toLowerCase())+'">');
								formobj.append('<input name="data[UserAccount][email]" type="hidden" value="'+$.trim($("#email").val())+'">');
								formobj.append('<input name="data[UserAccount][dob]" type="hidden" value="'+$.trim($("#dob").val().toLowerCase())+'">');

								$.post('<?php echo $this->Session->webroot; ?>patients/demographics/task:patient_user/', formobj.serialize(), 
								function(data)
								{
									document.user_frm.username.name = 'data[UserAccount][username]';
									document.user_frm.password.name = 'data[UserAccount][password]';
									$("#user_id").val(data[0]);
									$("#username").val(data[1]);
									$("#password").val(data[2]);
									$("#user_frm_layout").show('slow');
									$("#user_frm_link").removeClass('create-patient-account');
									$("#user_frm_link").hide();
									$("#user_frm_load").hide();
									<?php
									if($task == 'edit')
									{
										?>
										$("#username").rules("remove");
										$("#username").rules("add", {
											required: true,
                                                                                        minlength: 8,
											remote: 
											{
												url: '<?php echo $this->Session->webroot; ?>administration/check_username/',
												type: 'post',
												data: {'data[user_id]' : '' + $('#user_id').val() + '', 'data[task]' : '<?php echo $task; ?>'}
											}
										});
										<?php
									}
									?>
								}, 'json');
							}
						}
						else
						{
							$("#user_frm_link").html('');
							$("#user_frm_link").removeClass('smallbtn');
							document.user_frm.username.name = 'data[UserAccount][username]';
							document.user_frm.password.name = 'data[UserAccount][password]';
							$("#user_frm_layout").show('slow');
						}
					}); 
					$("#user_frm").validate(
					{
						errorElement: "div",
						rules: 
						{
							'data[UserAccount][username]': 
							{
								required: true,
                                                                minlength: 8,
								remote: 
								{
									url: '<?php echo $this->Session->webroot; ?>administration/check_username/',
									type: 'post',
									data: {'data[user_id]' : '' + $('#user_id').val() + '', 'data[task]' : '<?php echo $task; ?>'}
								}
							},
							'data[UserAccount][password]': 
							{
								required: true,
								minlength: 8
							}
							<?php
							if ($session->read("UserAccount.role_id") == EMR_Roles::PATIENT_ROLE_ID)
							{
								?>,
								'data[UserAccount][retype_password]': 
								{
									required: true,
									equalTo: "#password"
								}
								<?php
							}

							?>
						},
						messages: 
						{
							'data[UserAccount][username]': 
							{
								remote: "Username is already in used."	
							}
						},
						errorPlacement: function(error, element) 
						{
							if(element.attr("id") == "dob")
							{
								$("#dob_error").append(error);
							}
							else
							{
								error.insertAfter(element);
							}
						},
						submitHandler: function(form) 
						{

							<?php
							if(strlen($patient_id) > 0)
							{
								if ($task == 'edit')
								{
									?>
									if ($("#user_id").val())
									{
										$("#patient_user_user_id").val($("#user_id").val());
										$("#patient_user_username").val($("#username").val());
										$("#patient_user_password").val($("#password").val());
										$('#frm_demographics').submit();
									}
									<?php
								}
								else
								{
									?>
									$("#user_frm_load").show();
	
									$.post('<?php echo $this->Session->webroot; ?>patients/demographics/task:patient_user/', $('#user_frm').serialize(), 
									function(data)
									{
										$("#user_frm_load").hide();
									});
									<?php
								}
							}
							else
							{
								?>
								$("#patient_user_username").val($("#username").val());
								$("#patient_user_password").val($("#password").val());
								$('#frm_demographics').submit();
								<?php
							}
							?>
						}
					});
				});
				</script>
					</td>
		</tr>

            
        </table></div>
    </div>
<script language="javascript" type="text/javascript">

$.validator.addMethod("lessThan", function(value, element, params) {

        if (!/Invalid|NaN/.test(new Date(value))) {
                if(new Date(value) > new Date($(params).val())) return false;
        }
        return isNaN(value) && isNaN($(params).val()) || (parseFloat(value) > parseFloat($(params).val())); 
},'DOB can not be later than current date.');

$(document).ready(function()
{
        $("#dob").rules('add', { dob: "#dummyDate" });
        $("#dob").rules('add', { maxAge: 120 });
        
        $("#dob").change(function()
        {	$('#dob').valid();
			if ($.trim($("#dob").val()))
			{
					checkPatient();
			}
        }); 
        $("#dob").blur(function()
        {	$('#dob').valid();
			if ($.trim($("#dob").val()))
			{
					checkPatient();
			}
        });
		$("#dob").focus(function()
        {
			if ($.trim($("#dob").val()) != 'mm/dd/yyyy')
				$('#dob').valid();
        });
		
		$('#zipcode').blur(function()
        {
		 var zipcode = $(this).val();
             if($(this).val() != '')
             {
                 $('#immtrack_county').val('');	
				 var formobj = $("<form></form>");
				 formobj.append('<input name="data[zipcode]" id="zipcode" type="hidden" value="'+zipcode+'">');
     
				 $.post('<?php echo $this->Session->webroot; ?>patients/zipcode/task:get_zipcode/', 
				 formobj.serialize(), 
				 function(data)
				 {					
					$('#immtrack_county').val(data.countyname);	
				 },
				 'json'
				 );
		      }
		}); 

		$("#guardian").keyup(function()
		{
			if ($('#guardian').val())
			{
				$('#tr_relationship').show();
				$("#relationship").rules("add", {
					required: true,
				});
			}
			else
			{
				$('#tr_relationship').hide();
				$('#relationship').val('');
				$('#relationship').removeClass('error');
				$('.error[htmlfor=relationship]').hide();
			}
		});

		<?php
		if(trim($guardian)=='') :?>
			$('#tr_relationship').hide();
		<?php endif;?>
});

function hidelink()
{
$(".id_hide").hide();
 allowduplicate=0;
}

function checkPatient()
{
        <?php
        if ($task == 'addnew')
        { ?>
        	$("#checkPatient_result").hide();
                if ($.trim($("#first_name").val()) && $.trim($("#last_name").val()) && $.trim($("#dob").val()) && $("#checkPatient_load").is(":visible") == false)
                {
                        $("#checkPatient_load").show();
                        var formobj = $("<form></form>");
                        formobj.append('<input name="data[check][first_name]" type="hidden" value="'+$.trim($("#first_name").val())+'">');
                        formobj.append('<input name="data[check][last_name]" type="hidden" value="'+$.trim($("#last_name").val())+'">');
                        formobj.append('<input name="data[check][dob]" type="hidden" value="'+$.trim($("#dob").val())+'">');
                
                        $.post('<?php echo $this->Session->webroot; ?>patients/index/task:checkPatient/', formobj.serialize(), 
                        function(data)
                        {
                                $("#checkPatient_result").html('');
                                if(data && !allowduplicate)
                                {
                                	 allowduplicate=1;
                                	 var msg='<div class="error" style="margin-bottom:20px">NOTICE: Already have patient record for: ' + data + ' with same DOB. <a href = "javascript: void(0);" onclick ="hidelink()" class="smallbtn">Click here</a> to dismiss this warning and proceed.</div>';
                                         $("#checkPatient_result").show();
                                         $("#checkPatient_result").html(msg);
                                	 $("#checkPatient_load").hide();
                                }
                                else
                                {
                                  $("#checkPatient_load").hide();
                                }
                        });
                }
        <?php
        } 
        ?>
}
</script>
