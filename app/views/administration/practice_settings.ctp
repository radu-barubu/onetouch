<?php 

echo $this->Html->script('ipad_fix.js');

$thisURL = $this->Session->webroot . $this->params['url']['url']."/task:save";
$user = $this->Session->read('UserAccount');

if (isset($PracticeSetting['PracticeSetting']))
{
        extract($PracticeSetting['PracticeSetting']);
		$notify = json_decode(html_entity_decode($reminder_notify_json), true);
}

if(isset($setting_id))
{
        $id_field = '<input type="hidden" name="data[PracticeSetting][setting_id]" id="setting_id" value="'.$setting_id.'" />';
}
else
{
        //Init default value here
        $id_field = "";
        $practice_id  = "";
        $instant_notification  = "Yes";
        $notification_time  = "30";
        $mrn_start = "10000";
        $encounter_start = "10000";
        $scale = "English";
        $autologoff = 20;
        $patient_status='yes';
    $test_patient_data = 'Yes';

}

?>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
<script type="text/javascript">
$(document).ready(function()
{
                $("#frm").validate({errorElement: "div"});
                //create bubble popups for each element with class "button"
                $('.practice_lbl').CreateBubblePopup();
                   //set customized mouseover event for each button
                   $('.practice_lbl').mouseover(function(){ 
                        //show the bubble popup with new options
                        $(this).ShowBubblePopup({
                                alwaysVisible: true,
                                closingDelay: 200,
                                position :'top',
                                align    :'left',
                                tail     : {align: 'middle'},
                                innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
                                innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},                                                                         
                                                themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'                                                              
                         });
                   });
                   
                   $("#rx_setup").change(function()
                   {
                                if($(this).val() == "Electronic")
                                {
                                        $(".e_rx_credential").show();
                                }
                                else
                                {
                                        $(".e_rx_credential").hide();
                                }
                   });

 $('#instant_notification,#scale,#general_dateformat,#general_timeformat,#software_upgrades,#notification_time,#test_patient_data').buttonset();

});
</script>
<div style="overflow: hidden;">
<h2>Administration</h2>
    <?php echo $this->element("administration_general_links"); ?>
    <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <input type="hidden" name="data[usedefault]" id="usedefault" value="false" />
                
                <?php 
                
                    if (!$locationCount) {
                        echo $this->element("tutor_mode", array('tutor_mode' => @$tutor_mode, 'tutor_id' => 20)); 
                    }
                    ?>        
        
        <?php
                echo "$id_field";
                ?>
        <table cellpadding="0" cellspacing="0" class="form">
            <tr height=35>
                <td width="200"><span class="practice_lbl" id="azure" name="Pop-Up notification if a New Message <br>is received in staff's mailbox" style="text-align:center; width:89px; "><label>Instant Notification:</label>  <?php echo $html->image('help.png'); ?></span> </td>
                <td>
                    <div id="instant_notification">
                        <input type="radio" name="data[PracticeSetting][instant_notification]" id="instant_notification_yes" value="Yes" <?php echo ($instant_notification=='Yes'? "checked":''); ?> ><label for="instant_notification_yes">Yes</label>
                                                <input type="radio" name="data[PracticeSetting][instant_notification]" id="instant_notification_no" value="No" <?php echo ($instant_notification=="No"?"checked":""); ?> ><label for="instant_notification_no">No</label>
                                </div>  
                                </td>
            </tr>
            <tr>
                <td width="200"><span class="practice_lbl" id="azure" name="How long should Pop-Up notification remain?" style="text-align:center; width:89px; "><label>Notification Time:</label> <?php echo $html->image('help.png'); ?></span> </td>
                <td>
                    
                    <span id="notification_time">
                    <?php
                                        for ($i = 30; $i <= 120; $i += 30)
                                        {
                                          echo '<input type=radio name="data[PracticeSetting][notification_time]" id="notification_time'.$i.'" value="'.$i.'" '.($notification_time==$i?"checked":"").' ><label for="notification_time'.$i.'">'.$i.'</label>';
                                        }
                                        ?>
                   </span>
                    
                    seconds
                </td>
            </tr>
            <!--
            <tr>
                <td width="200"><span class="practice_lbl" id="azure" name="You can choose a starting number <br> for your Medical Records" style="text-align:center; width:89px; "><label>MRN Start Number:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td><input type="text" name="data[PracticeSetting][mrn_start]" id="mrn_start" style="width:150px;" class="required numeric_only" value="<?php echo $mrn_start ?>"></td>
            </tr>
            -->
            <tr height=30>
                <td width="200"><label>Scale:</label></td>
                <td>          
                                        <div id="scale">
                        <input type="radio" name="data[PracticeSetting][scale]" id="scale_english" value="English" <?php echo ($scale=='English'? "checked":''); ?> ><label for="scale_english">English</label> 
                                                <input type="radio" name="data[PracticeSetting][scale]" id="scale_metric" value="Metric" <?php echo ($scale=="Metric"?"checked":""); ?> ><label for="scale_metric">Metric</label>
                                        </div>
                                </td>
            </tr>
            <tr>
                <td><span class="practice_lbl" id="azure" name="How long if you're idle before auto logged out" style="text-align:center; width:89px; "><label>Auto-logoff Timer:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td><table cellpadding="0" cellspacing="">
                        <tr>
                            <td width="200"><div id="slider_autologoff"></div></td>
                            <td style="padding-left: 10px;">
                                <input type="hidden" name="data[PracticeSetting][autologoff]" id="autologoff" readonly="readonly" size="2" />
                                <span id="autologoff_value"></span>
                            </td>
                        </tr>
                    </table>
                    <script>
                                                $(function() {
                                                        $( "#slider_autologoff").slider({
                                                                range: "max",
                                                                min: 5,
                                                                max: 120,
                                                                step: 5,
                                                                value: <?php echo $autologoff; ?>,
                                                                slide: function( event, ui ) {
                                                                        $( "#autologoff" ).val( ui.value + ' minutes' );
                                                                        $('#autologoff_value').html(ui.value + ' minutes');
                                                                }
                                                        });
                                                        $("#autologoff").val($("#slider_autologoff").slider("value") + ' minutes');
                                                        $('#autologoff_value').html($("#slider_autologoff").slider("value") + ' minutes');
                                                });
                                        </script>
                </td>
            </tr>
            <tr>
                <td><span class="practice_lbl" id="azure" name="How long are persons allowed Emergency Access?" style="text-align:center; width:89px; "><nobr><label>Emergency Access Duration:</label> <?php echo $html->image('help.png'); ?></nobr></span></td>
                <td style="padding: 10px 0px;"><table cellpadding="0" cellspacing="">
                        <tr>
                            <td width="200"><div id="slider_emergency_duration"></div></td>
                            <td style="padding-left: 10px;"><input type="hidden" name="data[PracticeSetting][emergency_duration]" id="emergency_duration" readonly="readonly" size="2" />
                                <span id="emergency_duration_value"></span></td>
                        </tr>
                    </table>
                    <script>
                                $(function() {
                                        $( "#slider_emergency_duration" ).slider({
                                                range: "max",
                                                min: 24,
                                                max: 72,
                                                step: 24,
                                                value: <?php echo $emergency_duration; ?>,
                                                slide: function( event, ui ) {
                                                        $( "#emergency_duration" ).val( ui.value + ' hours' );
                                                        $('#emergency_duration_value').html(ui.value + ' hours');
                                                }
                                        });
                                        $("#emergency_duration").val($("#slider_emergency_duration").slider("value") + ' hours');
                                        $('#emergency_duration_value').html($("#slider_emergency_duration").slider("value") + ' hours');
                                });
                                </script></td>
            </tr>
            <!--
            <tr>
                                <td><span class="practice_lbl" id="azure" name="You can choose how you want Dates formatted" style="text-align:center; width:89px; "><label>Date Format:</label> <?php echo $html->image('help.png'); ?></span></td>
                                <td>
                                
                                <div id="general_dateformat">
                                  <input type=radio name="data[PracticeSetting][general_dateformat]" id="general_dateformat1" value="m/d/Y" <?php echo $general_dateformat=="m/d/Y"?"checked":"" ?> ><label for="general_dateformat1">MM/DD/YYYY</label>
                                  <input type=radio name="data[PracticeSetting][general_dateformat]" id="general_dateformat2" value="Y/m/d" <?php echo $general_dateformat=="Y/m/d"?"checked":"" ?> ><label for="general_dateformat2">YYYY/MM/DD</label>
                                  <input type=radio name="data[PracticeSetting][general_dateformat]" id="general_dateformat3" value="d/m/Y" <?php echo $general_dateformat=="d/m/Y"?"checked":"" ?> ><label for="general_dateformat3">DD/MM/YYYY</label>
                                </div>
                                </td>
                        </tr>
                        
            <tr>
                                <td><span class="practice_lbl" id="azure" name="You can choose how you want Time formatted" style="text-align:center; width:89px; "><label>Time Format:</label> <?php echo $html->image('help.png'); ?></span></td>
                                <td>
                                
                                <div id="general_timeformat">
                                  <input type=radio name="data[PracticeSetting][general_timeformat]" id="general_timeformat1" value="12" <?php echo $general_timeformat=="12"?"checked":"" ?> ><label for="general_timeformat1">12 hr</label>
                                  <input type=radio name="data[PracticeSetting][general_timeformat]" id="general_timeformat2" value="24" <?php echo $general_timeformat=="24"?"checked":"" ?> ><label for="general_timeformat2">24 hr</label>
                                </div>
                                 
                                
                                </td>
                        </tr>
            <tr>
            -->
                <td width="200"><label>Administrator Name:</label></td>
                <td><input type="text" name="data[PracticeSetting][sender_name]" id="sender_name" style="width:150px;" class="smtp_field required" value="<?php echo ($sender_name? $sender_name: 'Admin'); ?>"></td>
            </tr>
            <tr>
                <td width="200"><label>Administrator Email:</label></td>
                <td><input type="text" name="data[PracticeSetting][sender_email]" id="sender_email" style="width:300px;" class="smtp_field required email" value="<?php echo ($sender_email? $sender_email: ''); ?>"></td>
            </tr>

            <tr>
                <td width="200"><span class="practice_lbl" id="azure" name="Major software version updates/upgrades <br>reflected to your account?" style="text-align:center; width:89px; "><label>Software Updates:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td>
                    <div id="software_upgrades">
                      <input type=radio value="Automatic" id="software_upgrades1" name="data[PracticeSetting][software_upgrades]" <?php if($software_upgrades == 'Automatic') { echo 'checked'; } ?> ><label for="software_upgrades1">Automatic</label>
                      <input type=radio value="Ask" id="software_upgrades2" name="data[PracticeSetting][software_upgrades]" <?php if($software_upgrades == 'Ask') { echo 'checked'; } ?> ><label for="software_upgrades2">Ask Me First</label>
                    </div>
                    
                </td>
            </tr>
          <tr>
                <td class="top_pos"><span class="practice_lbl" id="azure" name="Rules state that you can change <br>Established Patients to 'New' if they return <br>3 years later allowing higher reimbursement." style="text-align:center; width:89px; "><label>Patient Status:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td style="padding:0 0 10px 0;">
                                <label for="data[PracticeSetting][patient_status]" class="label_check_box"> 
                    <input type='checkbox' id='data[PracticeSetting][patient_status]' name='data[PracticeSetting][patient_status]' <?php echo $patient_status!='no'?'checked':''; ?>/>
                Change to "New" if no appointment in 3 years
                </label></td>
           </tr>
           <tr height=35>
                <td width="200"><span class="practice_lbl" id="azure" name="Turn on/off example patient data" style="text-align:center; width:89px; "><label>Example Patient Data:</label>  <?php echo $html->image('help.png'); ?></span> </td>
                <td>
                    <div id="test_patient_data">
                        <input type="radio" name="data[PracticeSetting][test_patient_data]" id="test_patient_data_yes" value="Yes" <?php echo ($test_patient_data=='Yes'? "checked":''); ?> ><label for="test_patient_data_yes">Show</label>
                                                <input type="radio" name="data[PracticeSetting][test_patient_data]" id="test_patient_data_no" value="No" <?php echo ($test_patient_data=="No"?"checked":""); ?> ><label for="test_patient_data_no">Hide</label>
                                </div>  
                                </td>
            </tr>
			<tr>
                <td width="200"><label>Open item notification:</label></td>
                <td>
					<input type="text" name="data[PracticeSetting][notify_frequency]" id="notify_frequency" style="width:50px;" value="<?php echo isset($notify['notify_frequency'])? $notify['notify_frequency']:''; ?>">
					<?php $notify_frequency_types = array('day' => 'Day(s)', 'week' => 'Week(s)', 'month' => 'Month(s)', 'year' => 'Year(s)'); ?>
					<select name="data[PracticeSetting][notify_frequency_type]" style="width:100px;">
						<option value=""></option>
					<?php 
						foreach($notify_frequency_types as $key => $notify_frequency_type) {
					?>
						<option value="<?php echo $key; ?>" <?php echo (isset($notify['notify_frequency_type']) && $notify['notify_frequency_type'] == $key)? ' selected="selected"':''; ?>><?php echo $notify_frequency_type; ?></option>
					<?php } ?>
					</select>
				</td>
            </tr>
           <tr height=35>
                <td width="200" style="vertical-align: middle;"><span class="practice_lbl" id="azure" name="ICD Version" style="text-align:center; width:89px; "><label>ICD Version</label>  <?php echo $html->image('help.png'); ?></span> </td>
                <td>
                  <div id="test_patient_data" style="float: left;">
                        <input type="radio" name="data[PracticeSetting][icd_version]" id="icd_version_9" value="9" <?php echo ($icd_version==9? "checked":''); ?> ><label for="icd_version_9">9</label>
                                                <input type="radio" name="data[PracticeSetting][icd_version]" id="icd_version_10" value="10" <?php echo ($icd_version==10?"checked":""); ?> ><label for="icd_version_10">10</label>
                                </div>  
                  
                  <div style="float: left; margin: 0.25em 0 0 2em;">
                    <label for="icd_converter" class="label_check_box"> 
                      <input type="hidden" name='data[PracticeSetting][icd_converter]' value="0" />
                      <input type='checkbox' id='icd_converter' name='data[PracticeSetting][icd_converter]' <?php echo $icd_converter == 1 ? 'checked':''; ?>/>
                  Enable ICD9 to ICD10 Converter
                  </label>
                  </div>
                  
                                </td>
            </tr>
        </table>
 
    </form>
</div>
<div class="actions" removeonread="true">
    <ul>
        <li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
        <li><a href="javascript: void(0);" onclick="$('#usedefault').val('true'); $('#frm').submit();">Use Default</a></li>
    </ul>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
