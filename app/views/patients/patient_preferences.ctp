<?php

echo $this->Html->css(array('sections/patient_preferences.css'));

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$current_message = 'Item(s) saved.';

extract($patient_preferences);

$singleProvider = (isset($singleProvider)) ? $singleProvider : "";
if(empty($pcp_text)) {
  $pcp_text=$singleProvider;
}
?>
<?php echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "general_information"))); ?>
<script>

$(document).ready(function()
{
    initCurrentTabEvents('patient_preferences_area');
        
    $("#frmPatientPreferences").validate(
    {
        errorElement: "div",
         ignore: ':hidden',
        submitHandler: function(form) 
        {
            $('#frmPatientPreferences').css("cursor", "wait");
            $('#imgLoadPatientPreferences').css('display', 'block');
            $.post(
                '<?php echo $thisURL; ?>', 
                $('#frmPatientPreferences').serialize(), 
                function(data)
                {
                    showInfo("<?php echo $current_message; ?>", "notice");
                    loadTab($('#frmPatientPreferences'), '<?php echo $thisURL; ?>');
                },
                'json'
            );
        }
    });
       
    $("#pcp_text").autocomplete('<?php echo $html->url(array('task' => 'autocomplete')); ?>', 
    {
        cacheLength: 20,
        minChars: 2,
        max: 20,
        mustMatch: false,
        matchContains: false,
        scrollHeight: 300
    });
	
	/*$("#referred_by_val").autocomplete('<?php echo $html->url(array('controller' => 'schedule', 'action' => 'provider_autocomplete')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: true,
			scrollHeight: 200
		});
		
		$("#referred_by_val").result(function(event, data, formatted)
		{
			$("#referred_by").val(data[1]);
		});*/
		
		/*$("#recommended_by_val").autocomplete('<?php echo $html->url(array('controller' => 'schedule', 'action' => 'provider_autocomplete')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: true,
			scrollHeight: 200
		});
		
		$("#recommended_by_val").result(function(event, data, formatted)
		{
			$("#recommended_by").val(data[1]);
		});*/
		
	//$("#sms_dropdown").hide();
	$("#preferred_contact_method").change(function(){
		if($(this).val()=="phone"){
			$("#phone_dropdown").show();
			$("#sms_dropdown").hide();
		}
		else if($(this).val()=="sms"){
			$("#phone_dropdown").hide();
			$("#sms_dropdown").show();
		}
		else{
			$("#phone_dropdown").hide();
			$("#sms_dropdown").hide();
		}
		
	})    
    $("#pcp_text").result(function(event, data, formatted)
    {
        $("#pcp").val(data[1]);
    });
});
function addTestSearchData(data)
{
	var test_codes = $("#tableTestCode").data('data');
	
	var found = false;
	
	for(var i = 0; i < test_codes.length; i++)
	{
		if(test_codes[i]['orderable'] == data['orderable'])
		{
			found = true;
		}
	}
	
	if(!found)
	{
		test_codes[test_codes.length] = data;
	}
	
	$("#tableTestCode").data('data', test_codes);

}
</script>
<div id="patient_preferences_area" class="tab_area">
<?php  
//patient portal patient_checkin_id 
if(!empty($patient_checkin_id)):
?>
<script>
function goForward() {
 $('#frmPatientPreferences').submit();
 setTimeout("location='<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'allergies', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?>';",600);
}
</script>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>    <td style="width:100px"><button class='btn' onclick="javascript:history.back()"><< Back</button></td>
    <td style="vertical-align:top"> Please review the information below and click 'Save' at the bottom when finished. This page is optional, and not required. Click the 'Next' button to proceed. </td>
    <td style="width:100px"><button class="btn" onclick="goForward()">Next >></button></td>
  </tr>
</table>  
</div>
<?php endif; ?>
    <?php echo $this->element("pharmacy_search", array('submit' => 'addTestSearchData', 'open' => 'imgSearchPharmacyOpen', 'container' => 'pharmacy_search_container', 'form_name' => 'patient_preference')); ?>
	<form id="frmPatientPreferences" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
	<div style="float: left; width: 60%;">
    
        <input type="hidden" name="data[PatientPreference][preference_id]" id="preference_id" value="<?php echo $preference_id; ?>" />
        <table cellpadding="0" cellspacing="0" class="form" width="100%">
            <tr>
                <td width="190"><label>Primary Care Physician:</label></td>
                <td>
				<?php
				if($session->read("UserAccount.role_id") == EMR_Roles::PATIENT_ROLE_ID)
				{
                    ?><input type="text" name="pcp_text" value="<?php echo $pcp_text; ?>" style="width: 200px; background:#eeeeee;" readonly="readonly" /><?php
				}
				else
				{ ?>
                    
                                    <?php 
                                    $pcp_text = trim($pcp_text);
                                    if (count($availableProviders) === 1 && $pcp_text == ''): ?>
                                    <?php
                                        $p = $availableProviders[0]['UserAccount'];
                                        $pcp_text = htmlentities($p['firstname'] . ' ' . $p['lastname']);
                                        $pcp = $p['user_id'];
                                    ?>
                                    <?php endif;?> 
                                    <input type="text" name="pcp_text" id="pcp_text" value="<?php echo $pcp_text; ?>" style="width: 200px;" />
                    <?php
                                }?>
                    <input type="hidden" name="data[PatientPreference][pcp]" id="pcp" value="<?php echo $pcp; ?>" />
                </td>
            </tr>
            <tr>
                <td><label>Preferred Contact Method:</label></td>
                <td>
				 <select name="data[PatientPreference][preferred_contact_method]" id="preferred_contact_method" style="width: 140px;">
				 <option value="" selected>Select Method</option>
                      <option value="phone" <?php echo ($preferred_contact_method=='phone'? "selected='selected'":''); ?> > Phone Call</option>
                     <option value="email" <?php echo ($preferred_contact_method=='email'? "selected='selected'":''); ?> > Email</option>
                     <option value="sms" <?php echo ($preferred_contact_method=='sms'? "selected='selected'":''); ?> > Text Message/SMS</option>
					</select>
                </td>
            </tr>
			<tr id='sms_dropdown' style="display: <?php echo ($preferred_contact_method=='sms')?'table-row':'none'; ?>">
                <td><label>Your Cell Phone Carrier:</label></td>
                <td>
                    <select name="data[PatientPreference][carrier_id]" id="carrier_id" style="width: 140px;">
                        <option value="">Select Carrier</option>
                        <?php
                        foreach($SmsCarrier as $code => $value)
                        {
                            ?>
                            <option value="<?php echo $code; ?>" <?php if($carrier_id == $code) { echo 'selected="selected"'; } ?>><?php echo $value; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr id='phone_dropdown' style="display: <?php echo ($preferred_contact_method=='phone')?'table-row':'none'; ?>">
                <td><label>Phone Preference:</label></td>
                <td>
				 <select name="data[PatientPreference][phone_preference]" id="phone_preference" style="width: 140px;">
				 <option value="" selected>Select Phone</option>
                      <option value="work" <?php if($phone_preference=='work') { echo 'selected'; } ?> > Work</option>
                     <option value="home" <?php if($phone_preference=='home') { echo 'selected'; } ?> > Home</option>
                     <option value="cell" <?php if($phone_preference=='cell') { echo 'selected'; } ?> > Cell</option>
					</select>
                </td>
            </tr>            
	    <tr>
                 <td colspan=2>
		   <table style="padding: 7px; border: 1px solid <?php echo $display_settings['color_scheme_properties']['background']; ?>">
		     <th colspan=2>Favorite Pharmacy Information</th>
	            <tr>
 	               <td><label>Favorite Pharmacy: </label></td>
	                <td>
	                    <input type="text" name="data[PatientPreference][pharmacy_name]" id="pharmacy_name" value="<?php echo $pharmacy_name; ?>" style="width: 200px;"/>
	                    <!-- disabled for now <img id="imgSearchPharmacyOpen" style="cursor: pointer;margin-top: 3px;" src="<?php echo $this->Session->webroot . 'img/search_data.png'; ?>" width="20" height="20" onclick="$('#pharmacy_search_row').css('display','table-row');" /> -->
	                    <input type="hidden" name="data[PatientPreference][pharmacy_id]" id="pharmacy_id" value="<?php echo $pharmacy_id; ?>" />
	                </td>
	            </tr>
	            <tr id="pharmacy_search_row" style="display:none;">
	                <td colspan="2">
	                    <div style="float: left; clear: both; margin-bottom: 10px; width: 90%;">
	                        <div id="pharmacy_search_container" style="clear:both;"></div>
	                    </div>
	                </td>
	            </tr>
	            <tr>
	                <td><label>Address 1:</label></td>
	                <td><input type="text" name="data[PatientPreference][address_1]" id="address_1" value="<?php echo $address_1; ?>" style="width: 300px;" /></td>
	            </tr>
	            <tr>
	                <td><label>Address 2:</label></td>
 	               <td><input type="text" name="data[PatientPreference][address_2]" id="address_2" value="<?php echo $address_2; ?>" style="width: 300px;" /></td>
 	           </tr>
 	           <tr>
 		               <td><label>City:</label></td>
  	              <td><input type="text" name="data[PatientPreference][city]" id="city" maxlength="100" value="<?php echo $city; ?>" /></td>
  	          </tr>
       		     <tr>
                	<td><label>State:</label></td>
                	<td>
                   	 <select name="data[PatientPreference][state]" id="state">
                       		 <option value="">Select State</option>
                        	<?php
                    	
                       		 foreach($states as $code => $fullname)
                        	{
                           	 ?>
                            		<option value="<?php echo $code; ?>" <?php if($state == $code) { echo 'selected="selected"'; } ?>><?php echo $fullname; ?></option>
                            	<?php
                      		  }
        	
                       		 ?>
                   	 </select>
	                </td>
	            </tr>
	            <tr>
 	               <td><label>Zip Code:</label></td>
	                <td><input type="text" name="data[PatientPreference][zip_code]" id="zip_code" value="<?php echo $zip_code; ?>" /></td>
	            </tr>
	            <tr>
 	               <td><label>Country:</label></td>
 	               <td>
 	                   <select name="data[PatientPreference][country]" id="country" style="width: 140px;">
 	                       <option value="">Select Country</option>
   	                     <?php
   		                     foreach($ImmtrackCountries as $code => $value)
                       		 {
                            	?>
                            		<option value="<?php echo $code; ?>" <?php if($country == $code) { echo 'selected="selected"'; } ?>><?php echo $value; ?></option>
                            		<?php
                        	}
	                        ?>
 	                   </select>
 	               </td>
	            </tr>
 		           <tr>
     	     		      <td><label>Contact Name:</label></td>
     	      		     <td><input type="text" name="data[PatientPreference][contact_name]" id="contact_name" value="<?php echo $contact_name; ?>" style="width: 200px;" /></td>
     	  	     </tr>
    	  	      <tr>
     	 	          <td ><label>Phone Number:</label></td>
      	 	         <td><input type="text" name="data[PatientPreference][phone_number]" id="phone_number" value="<?php echo $phone_number; ?>" class="phone" /></td>
       		     </tr>
       		     <tr>
       		         <td><label>Fax Number:</label></td>
       		         <td><input type="text" name="data[PatientPreference][fax_number]" id="fax_number" value="<?php echo $fax_number; ?>" class="phone" /></td>
        	    </tr>

			</table>
		</td>
  	   </tr>		
<?php  
//patient portal patient_checkin_id 
if(empty($patient_checkin_id)):
?>            
            <!-- we already have this in patient demographics
            <tr>
                <td><label>Email Address:</label></td>
                <td><input type="text" name="data[PatientPreference][email_address]" id="email_address" value="<?php echo $email_address; ?>" class="email" style="width:200px;" /></td>
            </tr>
            -->
            <tr>
                <td><label>Alternate Email Address:</label></td>
                <td><input type="text" name="data[PatientPreference][email_address2]" id="email_address2" value="<?php echo $email_address2; ?>" class="email" style="width:200px;" /></td>
            </tr>            
            <tr>
                <td><label>HIPAA Notice Received:</label></td>
                <td>
				<select  id="hippa_notice" name="data[PatientPreference][hippa_notice]" style="width: 140px;">
			<option value="" selected>Select Status</option>
	<option value="1" <?php if($hippa_notice=='1') { echo 'selected'; }?>>Yes</option>
	<option value="0" <?php if($hippa_notice=='0') { echo 'selected'; }?>>No</option>
				</select>	
                   <!-- <label><input type="radio" name="data[PatientPreference][hippa_notice]" id="hippa_notice_yes" value="1" <?php echo $hippa_notice? "checked='checked'":''; ?> /> Yes</label>
                    &nbsp;&nbsp;
                    <label><input type="radio" name="data[PatientPreference][hippa_notice]" id="hippa_notice_no" value="0" <?php echo $hippa_notice? '':"checked='checked'"; ?>/> No</label>-->
                </td>
            </tr>
            <tr>
                <td><label>Immunization Registry Use:</label></td>
                <td>
					<select  id="allow_immunization_use" name="data[PatientPreference][allow_immunization_use]" style="width: 140px;">
			<option value="" selected>Select Use</option>
	<option value="1" <?php if($allow_immunization_use=='1') { echo 'selected'; }?>>Yes</option>
	<option value="0" <?php if($allow_immunization_use=='0') { echo 'selected'; }?>>No</option>
				</select>
                    <!--<label><input type="radio" name="data[PatientPreference][allow_immunization_use]"  value="1" id="allow_immunization_use_yes" <?php echo ($allow_immunization_use? "checked='checked'":''); ?> /> Yes</label>
                    &nbsp;&nbsp;
                    <label><input type="radio" name="data[PatientPreference][allow_immunization_use]"  value="0"  id="allow_immunization_use_no" <?php echo ($allow_immunization_use? '':"checked='checked'"); ?> /> No</label>-->
                </td>
            </tr>
            <tr>
                <td><label>Immunization Sharing:</label></td>
                <td>
				<select  id="allow_immunization_sharing" name="data[PatientPreference][allow_immunization_sharing]" style="width: 140px;">
			<option value="" selected>Select Sharing</option>
	<option value="1" <?php if($allow_immunization_sharing=='1') { echo 'selected'; }?>>Yes</option>
	<option value="0" <?php if($allow_immunization_sharing=='0') { echo 'selected'; }?>>No</option>
				</select>
				
                    <!--<label><input type="radio" name="data[PatientPreference][allow_immunization_sharing]" value="1"  id="allow_immunization_sharing_yes" <?php echo ($allow_immunization_sharing? "checked='checked'":''); ?> /> Yes</label>
                    &nbsp;&nbsp;
                    <label><input type="radio" name="data[PatientPreference][allow_immunization_sharing]" value="0"   id="allow_immunization_sharing_no" <?php echo ($allow_immunization_sharing? '':"checked='checked'"); ?> /> No</label>-->
                </td>
            </tr>
            <tr>
                <td><label>Health Information Exchange:</label></td>
                <td>
				<select  id="allow_hie" name="data[PatientPreference][allow_hie]" style="width: 140px;">
			<option value="" selected>Select Exchange</option>
	<option value="1" <?php if($allow_hie=='1') { echo 'selected'; }?>>Yes</option>
	<option value="0" <?php if($allow_hie=='0') { echo 'selected'; }?>>No</option>
				</select>
                    <!--<label><input type="radio" name="data[PatientPreference][allow_hie]" id="allow_hie_yes" value="1" <?php echo ($allow_hie? "checked='checked'":''); ?>/> Yes</label>
                    &nbsp;&nbsp;
                    <label><input type="radio" name="data[PatientPreference][allow_hie]" id="allow_hie_no" value="0" <?php echo ($allow_hie? '':"checked='checked'"); ?> /> No</label>-->
                </td>
            </tr>
            <tr>
                <td><!-- <label>Status:</label>  --> </td>
                <td>
			<!--		<select  id="status" name="data[PatientPreference][status]" style="width: 140px;">
			  <option value="" selected>Select Status</option>
	<option value="open" <?php if($status=='open') { echo 'selected'; }?>>Open</option>
	<option value="reviewed" <?php if($status=='reviewed') { echo 'selected'; }?>>Reviewed</option>
				</select>
                    <label><input type="radio" name="data[PatientPreference][status]" id="status_open" value="open"  <?php echo ($status=='open'? "checked='checked'":''); ?> /> Open</label>
                    &nbsp;&nbsp;
                    <label><input type="radio" name="data[PatientPreference][status]" value="reviwed" id="status_reviewed" <?php echo ($status=='reviwed'? "checked='checked'":''); ?> /> Reviewed</label>-->
                </td>
            </tr>
<?php endif; //if patient patient_checkin_id ?>
            
        </table>
        <div class="actions" removeonread="true">
            <ul>
                <li><a href="javascript: void(0);" onclick="$('#frmPatientPreferences').submit()">Save</a></li>
            </ul>
			<span id="imgLoadPatientPreferences" style="float: left; margin-top: 5px; display:none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
        </div>
    
    </div>
    <div style="float: right; width: 40%;">
    	<div style="padding-left: 10px; border: 1px solid <?php echo $display_settings['color_scheme_properties']['background']; ?>">
    	<table cellpadding="0" cellspacing="0" class="form"  width="450" >
			<tr><td style="vertical-align:top; padding-top: 20px;font-weight:bold" colspan=2><label>How did you hear about us?</label></td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
               <td style="vertical-align:top; padding-top: 3px; width:150px;"><label>Referred by Doctor:</label></td>
               <td width="120px;">
                   <input type="text" id="referred_by_doctor" name="data[PatientPreference][referred_by_doctor]" value="<?php echo isset($referred_by_doctor)?$referred_by_doctor:""; ?>" style="width:200px;" />
                   <!--<input type="hidden" id="referred_by" name="data[PatientPreference][referred_by]" value="<?php echo trim(isset($referred_by)?$referred_by:""); ?>" />-->
                </td>
            </tr>
			<tr>
               <td style="vertical-align:top; padding-top: 3px;"><label>From Friend/Colleague:</label></td>
               <td>
                   <input type="text" id="from_friend_colleague" name="data[PatientPreference][from_friend_colleague]" value="<?php echo isset($from_friend_colleague)?$from_friend_colleague:""; ?>" style="width:200px;" />
				   <!--<input type="hidden" id="recommended_by" name="data[PatientPreference][recommended_by]" value="<?php echo trim(isset($recommended_by)?$recommended_by:""); ?>" />-->
                </td>
            </tr>
			<tr>
               <td style="vertical-align:top; padding-top: 3px;"><label>From Internet Search:</label></td>
               <td>
                   <input type="text" id="from_internet_search" name="data[PatientPreference][from_internet_search]" value="<?php echo isset($from_internet_search)?$from_internet_search:""; ?>" style="width:200px;" />
				   <!--<input type="hidden" id="recommended_by" name="data[PatientPreference][recommended_by]" value="<?php echo trim(isset($recommended_by)?$recommended_by:""); ?>" />-->
                </td>
            </tr><tr><td style="vertical-align:top; padding-top: 15px;"></td></tr></table>
        </div>
    </div>
    </form>
</div>
