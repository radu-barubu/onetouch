<h2>Administration</h2>
<?php
$user = $this->Session->read('UserAccount');
$thisURL = $this->Session->webroot . $this->params['url']['url'];


$planTypes = array();

foreach ($availablePlans as $p) {
    $planTypes[$p['PracticePlan']['practice_plan_id']] = $p['PracticePlan']['practice_plan'];
}
   
?>
<script>
$(document).ready(function()
{
	$(".numeric").keypress(function() {
    	  return (/\d/.test(String.fromCharCode(event.which) ))
	});

    $("#frmServices").validate(
    {
        errorElement: "div",
        submitHandler: function(form) 
        {
			$('input').each(function(){
				$(this).val($.trim($(this).val()));
			}); 

            $.post(
                '<?php echo $html->url(array('controller' => 'administration', 'action' => 'saveServices')); ?>', 
                $('#frmServices').serialize(), 
                function(data) {
                     message("Settings Saved.");
                }
            );
        }, rules: {
            'data[faxage_tagname]': {
              required: false,
              maxlength: 13
            }
        }
    });
    
    //create bubble popups for each element with class "button"
    $('.practice_lbl').CreateBubblePopup();
       //set customized mouseover event for each button
       $('.practice_lbl').mouseover(function(){ 
        //show the bubble popup with new options
        $(this).ShowBubblePopup({
            alwaysVisible: true,
            closingDelay: 200,
            position :'top',
            align     :'left',
            tail     : {align: 'middle'},
            innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
            innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},                                        
                    themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'                                 
         });
       });

       $("#hl7_engine").change(function()
       {
         hl7_credentials();
       });
       
	   function hl7_credentials () {
		if($("#hl7_engine").val() == "0") {
           	$(".hl7_credential").hide();
	    	$(".hl7_credential_sftp").hide();
		} else if ($("#hl7_engine").val() == "MacPractice") {
	    	$(".hl7_credential").show();
	    	$(".hl7_credential_sftp").show();
        } else {
           	$(".hl7_credential").show();
	    	$(".hl7_credential_sftp").hide();
        }
       }

       $("#labs_setup").change(function()
       {
        lab_credential ();
       });
       
       function lab_credential () {
       	if($("#labs_setup").val() == "Electronic") {
        	$(".e_lab_credential").show();
			$(".macpractice_settings").hide();
			$(".hl7_files_settings").hide();
        } else if($("#labs_setup").val() == "MacPractice") {
            $(".e_lab_credential").hide();
            $(".macpractice_settings").show();
			$(".hl7_files_settings").hide();
        } else if($("#labs_setup").val() == "HL7Files") {
            $(".e_lab_credential").hide();
            $(".macpractice_settings").hide();
			$(".hl7_files_settings").show();
        } else {
            $(".e_lab_credential").hide();
            $(".macpractice_settings").hide();
			$(".hl7_files_settings").hide();
        }
       }
       setTimeout(function(){ lab_credential(); }, 300);
              
       $("#rx_setup").change(function()
       {
        rx_credential ();
       });
       
       function rx_credential() 
		{
			if($("#rx_setup").val() == "Electronic_Dosespot") 
			{
				$(".e_rx_credential").show();
				$(".e_rx_credential_emdeon").hide();
			}
			else if($("#rx_setup").val() == "Electronic_Emdeon")
			{
				$(".e_rx_credential_emdeon").show();
				$(".e_rx_credential").hide();
			}
			else 
			{
				$(".e_rx_credential").hide();
				$(".e_rx_credential_emdeon").hide();
			}
		}
       setTimeout(function(){ rx_credential();hl7_credentials();}, 300);
});

function testConnection(type)
{
	if($('#kareo_user').val() == '' || $('#kareo_password').val() == '' || $('#kareo_customer_key').val() == '' || $('#kareo_practice_name').val() == '')
	{
		message('Configuration is incorrect', 'error');
		return false;
	}
	$('#'+type+'_loader').html('<?php echo $html->image('ajax_loaderback.gif'); ?>');
	var data = {
	  'data[type]' : type
	};
	data = $('#frmServices').serialize() + '&' + $.param(data);
	$.post(
		'<?php echo $html->url(array('controller' => 'administration', 'action' => 'service_connection_test')); ?>', 
		data, 
		function(data) {
			 if(data.isValid==1) 
			 	var mesType = 'notice';
			 else 
			 	var mesType = 'error';
			 message(data.message, mesType);
			 $('#'+type+'_loader').html('');
		},
		'json'
	);
}

</script>
<div id='message'></div>
<div style="overflow: hidden;">
    <?php echo $this->element("administration_general_links"); ?>
    <?php echo $this->element("administration_services_menu"); ?>
    <form id="frmServices" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td colspan="2"><h3><label>Subscription</label></h3></td>
            </tr>
            <tr>
                <td><label for="plan">Plan</label></td>
                <td>
                    <select name="data[plan_id]" id="plan">
                        <?php foreach($planTypes as $planId => $planName): ?>
                        <option value="<?php echo $planId; ?>" <?php echo ($planId == $settings->plan_id) ? 'selected="selected"' : ''; ?>><?php echo htmlentities($planName); ?></option>
                        <?php endforeach;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="plan">Providers Allowed?</label></td>
                <td>
			Doctors <input type="text" name="data[allowed_doctors]" value="<?php echo $settings->allowed_doctors; ?>" class="numeric" maxlength="3" size="3"> Midlevels <input type="text" name="data[allowed_midlevels]" value="<?php echo $settings->allowed_midlevels;?>" class="numeric" maxlength="3" size="3">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <br />
                    <h3><label>Speech Recognition</label></h3></td>
            </tr>
            <tr>
                <td><label for="dragon_voice">Dragon Voice </label></td>
                <td>
                    <select name="data[dragon_voice]" id="dragon_voice">
                        <option value="0" <?php echo ($settings->dragon_voice) ? '' : 'selected="selected"'; ?>>Off</option>
                        <option value="1" <?php echo ($settings->dragon_voice) ? 'selected="selected"' : ''; ?>>On</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><br />
                    <h3><label>Private Label</label></h3></td>
            </tr>
            <tr>
                <td><label>Private Label</label></td>
                <td>
                    <select name="data[partner_id]" id="partner_id">
			<option value="0">None</option>
                        <option value="avantmd.com" <?php echo ($settings->partner_id == 'avantmd.com') ? 'selected="selected"':''; ?> >Great Lakes Billing</option>
			<option value="mht-ehr.com" <?php echo ($settings->partner_id == 'mht-ehr.com') ? 'selected="selected"':''; ?> >Medical Home Team</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <br />
                    <h3><label>Lab Settings</label></h3>
               <!-- <p><i>These settings are configured by our staff once your account is ready for this feature.</i></p>
                <br />-->
                </td>
            </tr>        
            <tr>
                <td width="200"><span class="practice_lbl" id="azure" name="You can choose if you want Electronic <br> Labs (inside USA) or Standard Lab entry form" style="text-align:center; width:89px; "><label>Labs Setup:</label> <!--<?php echo $html->image('help.png'); ?>--></span></td>
                <td>
                    <select name="data[labs_setup]" id="labs_setup">
                        <option value="Electronic" <?php if($settings->labs_setup == 'Electronic') { echo 'selected'; } ?>>Electronic</option>
                        <option value="Standard" <?php if($settings->labs_setup == 'Standard') { echo 'selected'; } ?>>Standard</option>
                        <option value="MacPractice" <?php if($settings->labs_setup == 'MacPractice') { echo 'selected'; } ?>>MacPractice</option>
                        <option value="HL7Files" <?php if($settings->labs_setup == 'HL7Files') { echo 'selected'; } ?>>HL7Files</option>
                    </select>
                </td>
            </tr>
            
             <tr class="e_lab_credential">
                 <td>
                    <span class="practice_lbl" id="azure" name="Once registered for electronic labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; ">
                        <label>e-Labs Host:</label><!--<?php echo $html->image('help.png'); ?>-->
                    </span>
                </td>
                <td><input type="text" name="data[emdeon_host]" id="emdeon_host" value="<?php echo $settings->emdeon_host;?>" style="width:150px;" /></td>
            </tr>
             <tr class="e_lab_credential">
                <td width="200"><span class="practice_lbl" id="azure" name="Once registered for electronic labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; ">
                        <label>e-Labs Facility ID:</label> <!--<?php echo $html->image('help.png'); ?>--></span></td>
                <td><input type="text" name="data[emdeon_facility]" id="emdeon_facility" style="width:150px;" value="<?php echo ($settings->emdeon_facility?$settings->emdeon_facility: ''); ?>"></td>
            </tr>           
        <tr class="e_lab_credential">
                <td width="200"><span class="practice_lbl" id="azure" name="Once registered for electronic labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; "><label>e-Labs User Name:</label> <!--<?php echo $html->image('help.png'); ?>--></span></td>
                <td><input type="text" name="data[emdeon_username]" id="emdeon_username" style="width:150px;" value="<?php echo ($settings->emdeon_username?$settings->emdeon_username: ''); ?>"></td>
            </tr> 
        <tr class="e_lab_credential">
                <td width="200"><span class="practice_lbl" id="azure" name="Once registered for electronic labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; "><label>e-Labs Password:</label> <!--<?php echo $html->image('help.png'); ?>--></span></td>
                <td><input type="password" name="data[emdeon_password]" id="emdeon_password" style="width:150px;" value="<?php echo ($settings->emdeon_password?$settings->emdeon_password: ''); ?>"></td>
            </tr>
        <tr class="e_lab_credential">
                <td width="200"><span class="practice_lbl" id="azure" name="Reset the Emdeon Lab listings? fresh copy will be downloaded." style="text-align:center; width:89px; "><label>e-Labs Tests Cache:</label> </td>
                <td><label for="elab_flush_cache" class="label_check_box"><input type="checkbox" name="data[elab_flush_cache]" id="elab_flush_cache" value="1" /> Delete/Flush </label> (So a new copy is downloaded)</td>
            </tr>
 		<tr class="macpractice_settings">
                <td width="200"><span class="practice_lbl" id="azure" name="Once registered for MacPractice labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; "><label>MacPractice Labs Host:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td><input type="text" name="data[macpractice_host]" id="macpractice_host" style="width:150px;" value="<?php echo ($settings->macpractice_host?$settings->macpractice_host: ''); ?>"></td>
            </tr>             
 		<tr class="macpractice_settings">
                <td width="200"><span class="practice_lbl" id="azure" name="Once registered for MacPractice labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; "><label>MacPractice Labs Port:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td><input type="text" name="data[macpractice_port]" id="macpractice_port" style="width:150px;" value="<?php echo ($settings->macpractice_port?$settings->macpractice_port: ''); ?>"></td>
            </tr>             
 		<tr class="macpractice_settings">
                <td width="200"><span class="practice_lbl" id="azure" name="Once registered for MacPractice labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; "><label>MacPractice Labs Username:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td><input type="text" name="data[macpractice_username]" id="macpractice_username" style="width:150px;" value="<?php echo ($settings->macpractice_username?$settings->macpractice_username: ''); ?>"></td>
            </tr>             
 		<tr class="macpractice_settings">
                <td width="200"><span class="practice_lbl" id="azure" name="Once registered for MacPractice labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; "><label>MacPractice Labs Password:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td><input type="password" name="data[macpractice_password]" id="macpractice_password" style="width:150px;" value="<?php echo ($settings->macpractice_password?$settings->macpractice_password: ''); ?>"></td>
            </tr>             
 		<tr class="hl7_files_settings">
                <td width="200"><span class="practice_lbl" id="azure" name="Once registered for HL7Files labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; "><label>HL7 Report Directory:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td><input type="text" name="data[hl7_report_dir]" id="hl7_report_dir" style="width:150px;" value="<?php echo ($settings->hl7_report_dir?$settings->hl7_report_dir: ''); ?>"></td>
            </tr>             
 		<tr class="hl7_files_settings">
                <td width="200"><span class="practice_lbl" id="azure" name="Once registered for HL7Files labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; "><label>HL7 Report Client Id:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td><input type="text" name="data[hl7_report_client_id]" id="hl7_report_client_id" style="width:150px;" value="<?php echo ($settings->hl7_report_client_id?$settings->hl7_report_client_id: ''); ?>"></td>
            </tr>             
 		<tr class="hl7_files_settings">
                <td width="200"><span class="practice_lbl" id="azure" name="Once registered for HL7Files labs, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; "><label>HL7 Report Lab Logo:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td><input type="text" name="data[hl7_report_lab_logo]" id="hl7_report_lab_logo" style="width:150px;" value="<?php echo ($settings->hl7_report_lab_logo?$settings->hl7_report_lab_logo: ''); ?>"></td>
            </tr>             
                         
          <tr>
              <td colspan=2><br><h3><label>Prescription Account Settings</label></h3>
              <!--<p><i>These fax settings are optional and configured by our staff once your account is ready for this feature.</i></p>--></td>
          
          </tr>           
                          
            <tr>
                <td width="230"><span class="practice_lbl" id="azure" name="You can choose if you want Electronic <br> Prescriptions (inside USA) or Standard Prescription entry form" style="text-align:center; width:89px; "><label>Rx Setup:</label> <!--<?php echo $html->image('help.png'); ?>--></span></td>
                <td>
                    <select name="data[rx_setup]" id="rx_setup">
                        <option value="Electronic_Emdeon" <?php if($settings->rx_setup == 'Electronic_Emdeon') { echo 'selected'; } ?>>Electronic (Emdeon)</option>
                        <option value="Electronic_Dosespot" <?php if($settings->rx_setup == 'Electronic_Dosespot') { echo 'selected'; } ?>>Electronic (Dosespot)</option>
                        <option value="Standard" <?php if($settings->rx_setup == 'Standard') { echo 'selected'; } ?>>Standard</option>
                    </select>
                </td>
            </tr>
            <tr class="e_rx_credential_emdeon">
		<td colspan=2><em>for Emdeon e-Rx, the e-Labs User Name and e-Labs Password must be entered in the above boxes</em></td>
	    </tr>
	   <tr class="e_rx_credential_emdeon">	
                <td width="230"><span class="practice_lbl" id="azure" name="Once registered for electronic prescribing, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; ">
                    <label>e-Prescribing Host:</label>
                    <!--<?php echo $html->image('help.png'); ?>--></span></td>
                <td><input type="text" name="data[emdeon_host_rx]" id="emdeon_host_rx" class="field_wide" value="<?php echo ($settings->emdeon_host?$settings->emdeon_host: ''); ?>" onblur="$('#emdeon_host').val($('#emdeon_host_rx').val());"></td>
            </tr>
            <tr class="e_rx_credential_emdeon">
                <td width="230"><span class="practice_lbl" id="azure" name="Once registered for electronic prescribing, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; ">
                    <label>e-Prescribing Facility:</label>
                    <!--<?php echo $html->image('help.png'); ?>--></span></td>
                <td><input type="text" name="data[emdeon_facility_rx]" id="emdeon_facility_rx" class="field_wide" value="<?php echo ($settings->emdeon_facility?$settings->emdeon_facility: ''); ?>" onblur="$('#emdeon_facility').val($('#emdeon_facility_rx').val());"></td>
            </tr>
            
            <!--<tr class="e_rx_credential">
                <td width="230"><span class="practice_lbl" id="azure" name="Once registered for electronic prescribing, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; ">
                    <label>e-Prescribing Host:</label>
                    <?php echo $html->image('help.png'); ?></span></td>
                <td><input type="text" name="data[dosepot_host]" id="dosepot_host" class="field_wide" value="<?php echo (isset($settings->dosepot_host)?$settings->dosepot_host:''); ?>"></td>
            </tr>-->
            <tr class="e_rx_credential">
                <td width="230"><span class="practice_lbl" id="azure" name="Once registered for electronic prescribing, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; ">
                    <label>Clinical ID:</label>
                    <!--<?php echo $html->image('help.png'); ?>--></span></td>
                <td><input type="text" name="data[dosepot_singlesignon_clinicid]" id="dosepot_singlesignon_clinicid" class="field_wide" value="<?php echo ($settings->dosepot_singlesignon_clinicid?$settings->dosepot_singlesignon_clinicid: ''); ?>"></td>
            </tr>
            <tr class="e_rx_credential">
                <td width="230"><span class="practice_lbl" id="azure" name="Once registered for electronic prescribing, <br>we furnish you this information to enter here. Otherwise skip" style="text-align:center; width:89px; ">
                    <label>Clinical Key:</label>
                    <!--<?php echo $html->image('help.png'); ?>--></span></td>
                <td><input type="text" name="data[dosepot_singlesignon_clinickey]" id="dosepot_singlesignon_clinickey" style="width:450px;"  value="<?php echo ($settings->dosepot_singlesignon_clinickey?$settings->dosepot_singlesignon_clinickey: ''); ?>"></td>
            </tr>
           <tr class="e_rx_credential">
                <td><label for="kareo_status">Dosespot QA/Staging Environment? :</label></td>
                <td>                    
                    <select  id="allow_hie" name="data[dosespot_test_flag]" style="width: 150px;">
                        <option value="0" <?php if($settings->dosespot_test_flag=='0') { echo 'selected'; }?>>Off</option>               
                        <option value="1" <?php if($settings->dosespot_test_flag=='1') { echo 'selected'; }?>>On</option>
                    </select>
                </td>
            </tr>            
            <tr>
                <td colspan="2">
                    <br />
                    <h3><label>Eligiblity Account Settings</label></h3>
                </td>
            </tr>        
			<tr>
                <td width="200"><label>ITS Host:</label></td>
                <td><input type="text" name="data[eligiblity_host]" id="eligiblity_host" value="<?php echo $settings->eligiblity_host;?>" class="field_normal" /></td>
            </tr>
            <tr>
                <td width="200"><label>ITS Username:</label></td>
                <td><input type="text" name="data[eligiblity_username]" id="eligiblity_username" value="<?php echo $settings->eligiblity_username;?>" class="field_normal" /></td>
            </tr>
			<tr>
                <td width="200"><label>ITS Password:</label></td>
                <td><input type="password" name="data[eligiblity_password]" id="eligiblity_password" value="<?php echo $settings->eligiblity_password;?>" class="field_normal" /></td>
            </tr>             
			<tr>
                <td width="200"><label>ITS Sender ID:</label></td>
                <td><input type="text" name="data[eligiblity_sender_id]" id="eligiblity_sender_id" value="<?php echo $settings->eligiblity_sender_id;?>" class="field_normal" /></td>
            </tr>             
			<tr>
                <td width="200"><label>ITS Receiver ID:</label></td>
                <td><input type="text" name="data[eligiblity_receiver_id]" id="eligiblity_receiver_id" value="<?php echo $settings->eligiblity_receiver_id;?>" class="field_normal" /></td>
            </tr>             
			  
            <tr>
                <td colspan="2"><br><h3><label>Fax Account Settings</label></h3>
                <input type="hidden" name="data[setting_id]" id="setting_id" value="<?php echo $settings->setting_id;?>" />
                <!--<p><i>These fax settings are optional and configured by our staff once your account is ready for this feature.</i></p>-->
                </td>
            </tr>
            <tr>
                <td width="200"><label>Username:</label></td>
                <td><input type="text" name="data[faxage_username]" id="faxage_username" value="<?php echo $settings->faxage_username;?>" class="field_normal" /></td>
            </tr>
            <tr>
                <td ><label>Password:</label></td>
                <td><input type="password" name="data[faxage_password]" id="faxage_password" value="<?php echo $settings->faxage_password;?>" class="field_normal" /></td>
            </tr>
            <tr>
                <td width="200"><label>Company Name:</label></td>
                <td><input type="text" name="data[faxage_tagname]" id="faxage_tagname" value="<?php echo $settings->faxage_tagname;?>" class="field_normal" /></td>
            </tr>
            <tr>
                <td width="200"><label>Company ID:</label></td>
                <td><input type="text" name="data[faxage_company]" id="faxage_company" value="<?php echo $settings->faxage_company;?>" class="field_normal" /></td>
            </tr>
            
            <tr>
                <td width="200"><label>Fax Number:</label></td>
                <td><input type="text" name="data[faxage_fax_number]" id="faxage_fax_number" value="<?php echo $settings->faxage_fax_number;?>" class="field_normal" /></td>
            </tr>
            <tr>
                <td width="200"><label>Test-Fax Number: (optional)</label></td>
                <td><input type="text" name="data[faxage_test_fax_number]" id="faxage_test_fax_number" value="<?php echo $settings->faxage_test_fax_number;?>" class="field_normal" /></td>
            </tr>
            <tr>
                <td colspan="2"><br><h3><label>HL7 Integration</label></h3>
                  </td>
            </tr>            
            <tr>
                <td><label for="xlink_status">Hl7 Engine:</label></td>
                <td>                    
                    <select  id="hl7_engine" name="data[hl7_engine]" style="width: 140px;">
                    <?php  $hl7array=array('MDConnection','MacPractice','Compulink'); ?>
                    	<option value='0'>Off</option>
                    	<?php foreach ($hl7array as $values) { 
                    		echo '<option value="'.$values.'" ' ;
                    		  if($settings->hl7_engine == $values) echo ' selected ';
                    		echo ' >'.$values.'</option>';
                    	 }
                    	?>
                    </select>
                </td>
            </tr> 
            <tr  class='hl7_credential'>
                <td><label for="hl7_schedule_reminders">Schedule Reminders?</label></td>
                <td>                    
                    <select  id="hl7_schedule_reminders" name="data[hl7_schedule_reminders]" style="width: 140px;">
                        <option value="0" <?php if($settings->hl7_schedule_reminders=='0') { echo 'selected'; }?>>Off</option>                    
                        <option value="1" <?php if($settings->hl7_schedule_reminders=='1') { echo 'selected'; }?>>On</option>

                    </select>
                </td>
            </tr>
             <tr class='hl7_credential'>
                <td width="200"><label>Customer Name:</label></td>
                <td><input type="text" name="data[hl7_customer_name]" id="hl7_customer_name" value="<?php echo $settings->hl7_customer_name;?>" class="field_normal" /></td>
            </tr>
		    <tr class='hl7_credential'>
                <td width="200"><label>Receiver Name:</label></td>
                <td><input type="text" name="data[hl7_receiver]" id="hl7_receiver" value="<?php echo $settings->hl7_receiver;?>" class="field_normal" /></td>
            </tr>
		    <tr class='hl7_credential_sftp'>
                <td width="200"><label>SFTP Output Host:</label></td>
                <td><input type="text" name="data[hl7_sftp_out_host]" id="hl7_sftp_out_host" value="<?php echo $settings->hl7_sftp_out_host;?>" class="field_normal" /></td>
            </tr>
		    <tr class='hl7_credential_sftp'>
                <td width="200"><label>SFTP Output Port:</label></td>
                <td><input type="text" name="data[hl7_sftp_out_port]" id="hl7_sftp_out_port" value="<?php echo $settings->hl7_sftp_out_port;?>" class="field_normal" /></td>
            </tr>
		    <tr class='hl7_credential_sftp'>
                <td width="200"><label>SFTP Output Username:</label></td>
                <td><input type="text" name="data[hl7_sftp_out_username]" id="hl7_sftp_out_username" value="<?php echo $settings->hl7_sftp_out_username;?>" class="field_normal" /></td>
            </tr>
		    <tr class='hl7_credential_sftp'>
                <td width="200"><label>SFTP Output Password:</label></td>
                <td><input type="password" name="data[hl7_sftp_out_password]" id="hl7_sftp_out_password" value="<?php echo $settings->hl7_sftp_out_password;?>" class="field_normal" /></td>
            </tr>
		    <tr class='hl7_credential_sftp'>
                <td width="200"><label>SFTP Input Host:</label></td>
                <td><input type="text" name="data[hl7_sftp_in_host]" id="hl7_sftp_in_host" value="<?php echo $settings->hl7_sftp_in_host;?>" class="field_normal" /></td>
            </tr>
		    <tr class='hl7_credential_sftp'>
                <td width="200"><label>SFTP Input Port:</label></td>
                <td><input type="text" name="data[hl7_sftp_in_port]" id="hl7_sftp_in_port" value="<?php echo $settings->hl7_sftp_in_port;?>" class="field_normal" /></td>
            </tr>
		    <tr class='hl7_credential_sftp'>
                <td width="200"><label>SFTP Input Username:</label></td>
                <td><input type="text" name="data[hl7_sftp_in_username]" id="hl7_sftp_in_username" value="<?php echo $settings->hl7_sftp_in_username;?>" class="field_normal" /></td>
            </tr>
		    <tr class='hl7_credential_sftp'>
                <td width="200"><label>SFTP Input Password:</label></td>
                <td><input type="password" name="data[hl7_sftp_in_password]" id="hl7_sftp_in_password" value="<?php echo $settings->hl7_sftp_in_password;?>" class="field_normal" /></td>
            </tr>
		    <tr class='hl7_credential_sftp'>
                <td width="200"><label>Produce ADT Messages for MacPractice:</label></td>
                <td>
                    <select  id="allow_hie" name="data[hl7_sftp_produce_adts]" style="width: 140px;">
                        <option value="0" <?php if($settings->hl7_sftp_produce_adts=='0') { echo 'selected'; }?>>Off</option>                    
                        <option value="1" <?php if($settings->hl7_sftp_produce_adts=='1') { echo 'selected'; }?>>On</option>

                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><br><h3><label>Integration with X-Link Settings</label></h3>
                <input type="hidden" name="data[setting_id]" id="setting_id" value="<?php echo $settings->setting_id;?>" />
                <!--<p><i>These settings are optional and configured by our staff if using another Practice Manager.</i></p>-->
                </td>
            </tr>            
            <tr>
                <td><label for="xlink_status">Xlink API :</label></td>
                <td>                    
                    <select  id="allow_hie" name="data[xlink_status]" style="width: 140px;">
                        <option value="0" <?php if($settings->xlink_status=='0') { echo 'selected'; }?>>Off</option>                    
                        <option value="1" <?php if($settings->xlink_status=='1') { echo 'selected'; }?>>On</option>

                    </select>
                </td>
            </tr>
			<tr>
                <td width="200"><label>Host Name:</label></td>
                <td><input type="text" name="data[xlink_hostname]" id="xlink_hostname" value="<?php echo $settings->xlink_hostname;?>" class="field_normal" /></td>
            </tr>
            
            <tr>
                <td width="200"><label>Username:</label></td>
                <td><input type="text" name="data[xlink_username]" id="xlink_username" value="<?php echo $settings->xlink_username;?>" class="field_normal" /></td>
            </tr>
            <tr>
                <td width="200"><label>Password:</label></td>
                <td><input type="text" name="data[xlink_password]" id="xlink_password" value="<?php echo $settings->xlink_password;?>" class="field_normal" /></td>
            </tr>
			<tr>
                <td colspan="2"><br><h3><label>Integration with Kareo Settings</label></h3></td>
            </tr>            
            <tr>
                <td><label for="kareo_status">Kareo API :</label></td>
                <td>                    
                    <select  id="allow_hie" name="data[kareo_status]" style="width: 140px;">
                        <option value="0" <?php if($settings->kareo_status=='0') { echo 'selected'; }?>>Off</option>               
                        <option value="1" <?php if($settings->kareo_status=='1') { echo 'selected'; }?>>On</option>
                    </select>
                </td>
            </tr>
			<tr>
                <td width="200"><label>User:</label></td>
                <td><input type="text" name="data[kareo_user]" id="kareo_user" value="<?php echo $settings->kareo_user;?>" class="field_normal" /></td>
            </tr>
            <tr>
                <td width="200"><label>Password:</label></td>
                <td><input type="text" name="data[kareo_password]" id="kareo_password" value="<?php echo $settings->kareo_password;?>" class="field_normal" /></td>
            </tr>
            <tr>
                <td width="200"><label>Customer Key:</label></td>
                <td><input type="text" name="data[kareo_customer_key]" id="kareo_customer_key" value="<?php echo $settings->kareo_customer_key;?>" class="field_normal" /></td>
            </tr>
			<tr>
                <td width="200"><label>Practice Name:</label></td>
                <td><input type="text" name="data[kareo_practice_name]" id="kareo_practice_name" value="<?php echo $settings->kareo_practice_name;?>" class="field_normal" /></td>
            </tr>
			<tr>
                <td width="230"><label>Adjust Schedule Time:</label></td>
                <td>
					<select name="data[kareo_schedule_adjust_time]" id="kareo_schedule_adjust_time" style="width: 140px;">
					<?php 
						for($i = -5; $i<=5; $i++) {
					?>
						<option value="<?php echo $i; ?>" <?php if($settings->kareo_schedule_adjust_time==$i) echo 'selected'; ?>><?php echo $i; ?></option>
					<?php } ?>
					</select> Hours
				</td>
            </tr>
                         <tr>
                                <td><label>Encounter Lock Check:</label></td>
                                <td>
                                <select  name="data[kareo_encounter_lock]" style="width: 140px;">
                                <option value="0" <?php if($settings->kareo_encounter_lock=='0') { echo 'selected'; }?>>Off</option>
                                <option value="1" <?php if($settings->kareo_encounter_lock=='1') { echo 'selected'; }?>>On</option>
                                </select>
                                  <em>when locking an encounter, wait for Kareo's response (to see if successful or error)</em>
                                </td>
                         </tr>
                        <tr>
                <td width="200"><label for="">Export All Patients:</label></td>
                <td><label for="kareo_export_all_patients" class="label_check_box"><input type="checkbox" name="data[kareo_export_all_patients]" id="kareo_export_all_patients" value="1" /> EXPORT all patients currently in OneTouch over to Kareo (one time operation!) </label> <br /> <br /></td>
                        </tr>
			<tr>
                <td width="200"><label for="">Import All Patients:</label></td>
                <td><label for="kareo_import_all_patients" class="label_check_box"><input type="checkbox" name="data[kareo_import_all_patients]" id="kareo_import_all_patients" value="1" /> IMPORT all patients currently in Kareo over to OneTouch (one time operation!) </label></td>
			</tr>   
			<tr>
                <td width="200">&nbsp;</td>
                <td style="height:43px;vertical-align:bottom"><a href="javascript:;" onclick="testConnection('kareo');" class="btn" style="text-decoration:none;">Test Connection</a> <span id="kareo_loader"></span></td>
            </tr>                        
        </table>
        
        <div class="actions">
            <ul>
                <li><a href="javascript: void(0);" onclick="$('#frmServices').submit();">Save</a></li>
            </ul>
        </div>
    </form>
</div>
