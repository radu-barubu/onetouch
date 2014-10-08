<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$plan_id = (isset($this->params['named']['plan_id'])) ? $this->params['named']['plan_id'] : "";
$addURL = $html->url(array('action' => 'health_maintenance_plans', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'health_maintenance_plans', 'patient_id' => $patient_id)) . '/'; 
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
echo $this->Html->script('ipad_fix.js');
$thisURL = str_replace("plan_id:$plan_id", "", $thisURL);

$page_access = $this->QuickAcl->getAccessType('patients', 'medical_information');
echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>
<script language="javascript" type="text/javascript">
 $(document).ready(function()
    {
	    initCurrentTabEvents('health_maintenance_plans_area');
		
		$("#frmHealthMaintenance").validate(
        {
            errorElement: "div",
			 submitHandler: function(form) 
            {
                $('#frmHealthMaintenance').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmHealthMaintenance').serialize(), 
					function(data)
                    {
						if (data.plan_id)
						{
							loadTab($('#frmHealthMaintenance'), '<?php echo $thisURL; ?>' + 'plan_id:' + data.plan_id);
						}
						else
						{
							showInfo("<?php echo $current_message; ?>", "notice");
							loadTab($('#frmHealthMaintenance'), '<?php echo $mainURL; ?>');
						}
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
    <div id="health_maintenance_plans_area" class="tab_area">
    <?php	
	if($task == "addnew" || $task == "edit")  
    { 
        if($task == "edit")
        {
            extract($Enrollments['EncounterPlanHealthMaintenanceEnrollment']);
            $signup_date = (isset($signup_date) and (!strstr($signup_date, "0000")))?__date($global_date_format, strtotime($signup_date)):'';
		}
	?>
	<form id="frmHealthMaintenance" name="frmHealthMaintenance" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
	<?php
	if(isset($PlanDetails))
	{
		extract($PlanDetails['HealthMaintenancePlan']);
	}
	if($task == "addnew")
	{ ?>
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
			<tr>
				<td width=180><label>Plan Name:</label></td>
				<td><table cellpadding="0" cellspacing="0"><tr><td><select name="plan_id" id="plan_id" onchange="$('#imgLoadPlanDetails').show();$('#frmHealthMaintenance').submit();">
				<option value="0" selected>Select Plan Name</option>
				<?php
				foreach ($Plans as $Plan):
					echo "<option value='".$Plan['HealthMaintenancePlan']['plan_id']."'".($plan_id==$Plan['HealthMaintenancePlan']['plan_id']?"selected":"").">".$Plan['HealthMaintenancePlan']['plan_name']."</option>";
				endforeach;
				?>
				</select></td><td>
				<?php
				if($plan_id)
				{
					?>
					<input type="hidden" name="data[HealthMaintenancePlan][patient_reminders]" id="patient_reminders" value="<?php echo $patient_reminders; ?>">
					&nbsp;&nbsp;&nbsp;&nbsp;<label class="label_check_box" for="clinical_alerts"><input id="clinical_alerts" type="checkbox" <?php if($clinical_alerts == "Yes") { echo 'checked="checked"'; } ?> disabled/>&nbsp;Clinical Alerts</label>&nbsp;&nbsp;&nbsp;&nbsp;<label class="label_check_box" for="patient_reminders"><input id="patient_reminders" type="checkbox" <?php if($patient_reminders == "Yes") { echo 'checked="checked"'; } ?> disabled/>&nbsp;Patient Reminders</label></td><td>
					<?php
				}
				?><span id="imgLoadPlanDetails" style="float: none; display:none; margin-left: 5px; margin-top: 5px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td></tr></table>&nbsp;</td>
			</tr>
		</table><?php
	}
	if($plan_id)
	{ ?>
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
		<?php
		if ($task == "edit")
		{	?>
			<input type="hidden" name="data[EncounterPlanHealthMaintenanceEnrollment][plan_id]" id="plan_id" value="<?php echo $plan_id; ?>">
			<input type="hidden" name="data[EncounterPlanHealthMaintenanceEnrollment][hm_enrollment_id]" id="hm_enrollment_id" value="<?php echo $Enrollments['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'] ?>">
			<input type="hidden" name="data[HealthMaintenancePlan][patient_reminders]" id="patient_reminders" value="<?php echo $patient_reminders; ?>">
			<tr>
				<td width=180><label>Plan Name:</label></td>
				<td><input type="text" name="data[EncounterPlanHealthMaintenanceEnrollment][plan_name]" id="plan_name" value="<?php echo $plan_name; ?>" style="width:450px" readonly></td>
			</tr><?php
		}
		else
		{
			?><input type="hidden" name="data[EncounterPlanHealthMaintenanceEnrollment][plan_name]" id="plan_name" value="<?php echo $plan_name; ?>"><?php
		}
		?>
		<tr>
			<td valign='top' style="vertical-align:top" width=180><label>Description:</label></td>
			<td><textarea cols="20" style=" height:80px" readonly><?php echo $description ?></textarea></td>
		</tr>
		<tr>
			<td><label>Category:</label></td>
			<td>
			<select style="background-color:#FFFFFF" disabled>
			<option value="" selected>Select Category</option>
			<option value="Disease Management" <?php echo ($category=='Disease Management'? "selected='selected'":''); ?>>Disease Management</option>
			<option value="Preventive Health" <?php echo ($category=='Preventive Health'? "selected='selected'":''); ?> > Preventive Health</option>
			<option value="Wellness" <?php echo ($category=='Wellness'? "selected='selected'":''); ?> > Wellness</option>
			</select>
		</td>
		<tr>
			<td><label>Gender:</label></td>
			<td>
			<select style="background-color:#FFFFFF" disabled>
			<option value="" selected>Both</option>
			<option value="F" <?php echo ($gender=='F'? "selected='selected'":''); ?> > Female</option>
			<option value="M" <?php echo ($gender=='M'? "selected='selected'":''); ?> > Male</option>
			</select></td>
		</tr>
		<tr>
			<td><label>From Age:</label></td>
			<td><select style="width:60px; background-color:#FFFFFF" disabled><option><?php echo $from_age ?></option></select>&nbsp;Year(s)&nbsp;<select style="width:60px; background-color:#FFFFFF" disabled><option><?php echo $from_month ?></option></select>&nbsp;Month(s)</td>
		</tr>
		<tr>
			<td><label>To Age:</label></td>
			<td><select style="width:60px; background-color:#FFFFFF" disabled><option><?php echo $to_age ?></option></select>&nbsp;Year(s)&nbsp;<select style="width:60px; background-color:#FFFFFF" disabled><option><?php echo $to_month ?></option></select>&nbsp;Month(s)</td>
		</tr>
        <tr>
            <td class="top_pos"><label>Include Rule:</label></td>
            <td style="padding-bottom: 10px;">
                <script language="javascript" type="text/javascript">
                    $(document).ready(function()
                    {
                        $(".lbl_btn").button(
                        {
                            text: false,
                            icons: {
                                primary: "ui-icon-triangle-1-s"
                            }
                        }).buttonset();
                        
                        $('.lbl_btn').click(function(e)
                        {
                            e.preventDefault();
                            var target_item = $(this).attr('target_item');
                            var target_chk = $(this).attr('target_chk');
                            
                            if($('#'+target_chk).is(':checked'))
                            {
                                $('.parent_tr').hide();
                                $('.rule_item').hide();
                                $('#'+target_item).show();
                                
                                $('#'+target_item).parents('.parent_tr').show();
                            }
                        });
						
						$('.small_table tr:nth-child(odd)').addClass("striped");
                    });
                </script>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="padding-left: 0px;">
                            <input type="hidden" name="data[HealthMaintenancePlan][include_rule_icd]" id="include_rule_icd" value="<?php echo $include_rule_icd ?>" />
                            <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                <label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="include_rule_icd_check" >
                                <input disabled="disabled" type="checkbox" name="data[HealthMaintenancePlan][include_rule_icd_check]" id="include_rule_icd_check" value="Yes" <?php echo ($include_rule_icd?"checked":"") ?> >&nbsp;ICD
                                </label><button target_item="include_rule_icd_add" target_chk="include_rule_icd_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;">&nbsp;</button>
                            </div>
                            
                            <input type="hidden" name="data[HealthMaintenancePlan][include_rule_medication]" id="include_rule_medication" value="<?php echo $include_rule_medication ?>" />
                            <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                <label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="include_rule_medication_check" >
                                <input disabled="disabled" type="checkbox" name="data[HealthMaintenancePlan][include_rule_medication_check]" id="include_rule_medication_check" value="Yes" <?php echo ($include_rule_medication?"checked":"") ?> >&nbsp;Medication
                                </label><button target_item="include_rule_medication_add" target_chk="include_rule_medication_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;">&nbsp;</button>
                            </div>
                            
                            <input type="hidden" name="data[HealthMaintenancePlan][include_rule_allergy]" id="include_rule_allergy" value="<?php echo $include_rule_allergy ?>" />
                            <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                <label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="include_rule_allergy_check" >
                                <input disabled="disabled" type="checkbox" name="data[HealthMaintenancePlan][include_rule_allergy_check]" id="include_rule_allergy_check" value="Yes" <?php echo ($include_rule_allergy?"checked":"") ?> >&nbsp;Allergy
                                </label><button target_item="include_rule_allergy_add" target_chk="include_rule_allergy_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                            </div>
                            
                            <input type="hidden" name="data[HealthMaintenancePlan][include_rule_patient_history]" id="include_rule_patient_history" value="<?php echo $include_rule_patient_history ?>" />
                            <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                <label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="include_rule_patient_history_check" >
                                <input disabled="disabled" type="checkbox" name="data[HealthMaintenancePlan][include_rule_patient_history_check]" id="include_rule_patient_history_check" value="Yes" <?php echo ($include_rule_patient_history?"checked":"") ?> >&nbsp;Patient History
                                </label><button target_item="include_rule_patient_history_add" target_chk="include_rule_patient_history_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                            </div>
                            
                            <input type="hidden" name="data[HealthMaintenancePlan][include_rule_lab_test_result]" id="include_rule_lab_test_result" value="<?php echo $include_rule_lab_test_result ?>" />
                            <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                <label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="include_rule_lab_test_result_check" >
                                <input disabled="disabled" type="checkbox" name="data[HealthMaintenancePlan][include_rule_lab_test_result_check]" id="include_rule_lab_test_result_check" value="Yes" <?php echo ($include_rule_lab_test_result?"checked":"") ?> >&nbsp;Lab Test Result
                                </label><button target_item="include_rule_lab_test_result_add" target_chk="include_rule_lab_test_result_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="parent_tr" style="display: none;">
                        <td style="padding-left: 0px;">
                            <span class="rule_item" id="include_rule_icd_add" style="display: none;">
                                <div id="show_include_rule_icd" style="display:<?php echo ($include_rule_icd?"block":"none") ?>">
                                	<?php if($include_rule_icd): ?>
                                    <?php
									$include_rule_icd = explode("|", $include_rule_icd);
									?>
                                    <table id="list_include_rule_icd" cellpadding="0" cellspacing="0" class="small_table form">
                                    	<tr><th>ICD</th></tr>
                                        <?php
										for ($i = 0; $i < count($include_rule_icd); ++$i)
										{
											echo "<tr><td>".$include_rule_icd[$i]."</td></tr>";
										}
										?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </span>
                            
                            <span class="rule_item" id="include_rule_medication_add" style="display: none;">
                                <div id="show_include_rule_medication" style="display:<?php echo ($include_rule_medication?"block":"none") ?>">
                                    <?php if($include_rule_medication): ?>
                                    <?php
									$include_rule_medication = explode("|", $include_rule_medication);
									?>
                                    <table cellpadding="0" cellspacing="0" class="small_table form">
                                    	<tr><th>Medication</th></tr>
                                        <?php
										for ($i = 0; $i < count($include_rule_medication); ++$i)
										{
											echo "<tr><td>".$include_rule_medication[$i]."</td></tr>";
										}
										?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </span>
                            
                            <span class="rule_item" id="include_rule_allergy_add" style="display: none;">
                                <div id="show_include_rule_allergy" style="display:<?php echo ($include_rule_allergy?"block":"none") ?>">
                                	<?php if($include_rule_allergy): ?>
                                    <?php
									$include_rule_allergy = explode("|", $include_rule_allergy);
									?>
                                    <table cellpadding="0" cellspacing="0" class="small_table form">
                                    	<tr><th>Allergy</th></tr>
                                        <?php
										for ($i = 0; $i < count($include_rule_allergy); ++$i)
										{
											echo "<tr><td>".$include_rule_allergy[$i]."</td></tr>";
										}
										?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </span>
                            
                            <span class="rule_item" id="include_rule_patient_history_add" style="display: none;">
                                <div id="show_include_rule_patient_history" style="display:<?php echo ($include_rule_patient_history?"block":"none") ?>">
                                	<?php if($include_rule_patient_history): ?>
                                    <?php
									$include_rule_patient_history = explode("|", $include_rule_patient_history);
									?>
                                    <table cellpadding="0" cellspacing="0" class="small_table form">
                                    	<tr><th>Patient History</th></tr>
                                        <?php
										for ($i = 0; $i < count($include_rule_patient_history); ++$i)
										{
											echo "<tr><td>".$include_rule_patient_history[$i]."</td></tr>";
										}
										?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </span>
                            
                            <span class="rule_item" id="include_rule_lab_test_result_add" style="display: none;">
                                <div id="show_include_rule_lab_test_result" style="display:<?php echo ($include_rule_lab_test_result?"block":"none") ?>">
                                    <?php if($include_rule_lab_test_result): ?>
                                    <?php
									$include_rule_lab_test_result = explode("|", $include_rule_lab_test_result);
									?>
                                    <table cellpadding="0" cellspacing="0" class="small_table form">
                                    	<tr><th>Lab Test Result</th></tr>
                                        <?php
										for ($i = 0; $i < count($include_rule_lab_test_result); ++$i)
										{
											echo "<tr><td>".$include_rule_lab_test_result[$i]."</td></tr>";
										}
										?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </span>&nbsp;
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
		<tr>
            <td class="top_pos"><label>Exclude Rule:</label></td>
            <td style="padding-bottom: 10px;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="padding-left: 0px;">
                            <input type="hidden" name="data[HealthMaintenancePlan][exclude_rule_icd]" id="exclude_rule_icd" value="<?php echo $exclude_rule_icd ?>" />
                            <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                <label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="exclude_rule_icd_check" >
                                <input disabled="disabled" type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_icd_check]" id="exclude_rule_icd_check" value="Yes" <?php echo ($exclude_rule_icd?"checked":"") ?> >&nbsp;ICD
                                </label><button target_item="exclude_rule_icd_add" target_chk="exclude_rule_icd_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;">&nbsp;</button>
                            </div>
                            
                            <input type="hidden" name="data[HealthMaintenancePlan][exclude_rule_medication]" id="exclude_rule_medication" value="<?php echo $exclude_rule_medication ?>" />
                            <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                <label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="exclude_rule_medication_check" >
                                <input disabled="disabled" type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_medication_check]" id="exclude_rule_medication_check" value="Yes" <?php echo ($exclude_rule_medication?"checked":"") ?> >&nbsp;Medication
                                </label><button target_item="exclude_rule_medication_add" target_chk="exclude_rule_medication_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;">&nbsp;</button>
                            </div>
                            
                            <input type="hidden" name="data[HealthMaintenancePlan][exclude_rule_allergy]" id="exclude_rule_allergy" value="<?php echo $exclude_rule_allergy ?>" />
                            <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                <label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="exclude_rule_allergy_check" >
                                <input disabled="disabled" type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_allergy_check]" id="exclude_rule_allergy_check" value="Yes" <?php echo ($exclude_rule_allergy?"checked":"") ?> >&nbsp;Allergy
                                </label><button target_item="exclude_rule_allergy_add" target_chk="exclude_rule_allergy_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                            </div>
                            
                            <input type="hidden" name="data[HealthMaintenancePlan][exclude_rule_patient_history]" id="exclude_rule_patient_history" value="<?php echo $exclude_rule_patient_history ?>" />
                            <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                <label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="exclude_rule_patient_history_check" >
                                <input disabled="disabled" type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_patient_history_check]" id="exclude_rule_patient_history_check" value="Yes" <?php echo ($exclude_rule_patient_history?"checked":"") ?> >&nbsp;Patient History
                                </label><button target_item="exclude_rule_patient_history_add" target_chk="exclude_rule_patient_history_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                            </div>
                            
                            <input type="hidden" name="data[HealthMaintenancePlan][exclude_rule_lab_test_result]" id="exclude_rule_lab_test_result" value="<?php echo $exclude_rule_lab_test_result ?>" />
                            <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                <label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="exclude_rule_lab_test_result_check" >
                                <input disabled="disabled" type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_lab_test_result_check]" id="exclude_rule_lab_test_result_check" value="Yes" <?php echo ($exclude_rule_lab_test_result?"checked":"") ?> >&nbsp;Lab Test Result
                                </label><button target_item="exclude_rule_lab_test_result_add" target_chk="exclude_rule_lab_test_result_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="parent_tr" style="display: none;">
                        <td style="padding-left: 0px;">
                            <span class="rule_item" id="exclude_rule_icd_add" style="display: none;">
                                <div id="show_exclude_rule_icd" style="display:<?php echo ($exclude_rule_icd?"block":"none") ?>">
                                	<?php if($exclude_rule_icd): ?>
                                    <?php
									$exclude_rule_icd = explode("|", $exclude_rule_icd);
									?>
                                    <table id="list_exclude_rule_icd" cellpadding="0" cellspacing="0" class="small_table form">
                                    	<tr><th>ICD</th></tr>
                                        <?php
										for ($i = 0; $i < count($exclude_rule_icd); ++$i)
										{
											echo "<tr><td>".$exclude_rule_icd[$i]."</td></tr>";
										}
										?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </span>
                            
                            <span class="rule_item" id="exclude_rule_medication_add" style="display: none;">
                                <div id="show_exclude_rule_medication" style="display:<?php echo ($exclude_rule_medication?"block":"none") ?>">
                                    <?php if($exclude_rule_medication): ?>
                                    <?php
									$exclude_rule_medication = explode("|", $exclude_rule_medication);
									?>
                                    <table cellpadding="0" cellspacing="0" class="small_table form">
                                    	<tr><th>Medication</th></tr>
                                        <?php
										for ($i = 0; $i < count($exclude_rule_medication); ++$i)
										{
											echo "<tr><td>".$exclude_rule_medication[$i]."</td></tr>";
										}
										?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </span>
                            
                            <span class="rule_item" id="exclude_rule_allergy_add" style="display: none;">
                                <div id="show_exclude_rule_allergy" style="display:<?php echo ($exclude_rule_allergy?"block":"none") ?>">
                                	<?php if($exclude_rule_allergy): ?>
                                    <?php
									$exclude_rule_allergy = explode("|", $exclude_rule_allergy);
									?>
                                    <table cellpadding="0" cellspacing="0" class="small_table form">
                                    	<tr><th>Allergy</th></tr>
                                        <?php
										for ($i = 0; $i < count($exclude_rule_allergy); ++$i)
										{
											echo "<tr><td>".$exclude_rule_allergy[$i]."</td></tr>";
										}
										?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </span>
                            
                            <span class="rule_item" id="exclude_rule_patient_history_add" style="display: none;">
                                <div id="show_exclude_rule_patient_history" style="display:<?php echo ($exclude_rule_patient_history?"block":"none") ?>">
                                	<?php if($exclude_rule_patient_history): ?>
                                    <?php
									$exclude_rule_patient_history = explode("|", $exclude_rule_patient_history);
									?>
                                    <table cellpadding="0" cellspacing="0" class="small_table form">
                                    	<tr><th>Patient History</th></tr>
                                        <?php
										for ($i = 0; $i < count($exclude_rule_patient_history); ++$i)
										{
											echo "<tr><td>".$exclude_rule_patient_history[$i]."</td></tr>";
										}
										?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </span>
                            
                            <span class="rule_item" id="exclude_rule_lab_test_result_add" style="display: none;">
                                <div id="show_exclude_rule_lab_test_result" style="display:<?php echo ($exclude_rule_lab_test_result?"block":"none") ?>">
                                    <?php if($exclude_rule_lab_test_result): ?>
                                    <?php
									$exclude_rule_lab_test_result = explode("|", $exclude_rule_lab_test_result);
									?>
                                    <table cellpadding="0" cellspacing="0" class="small_table form">
                                    	<tr><th>Lab Test Result</th></tr>
                                        <?php
										for ($i = 0; $i < count($exclude_rule_lab_test_result); ++$i)
										{
											echo "<tr><td>".$exclude_rule_lab_test_result[$i]."</td></tr>";
										}
										?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </span>&nbsp;
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
		<tr>
			<td valign='top' style="vertical-align:top"><label>Goal:</label></td>
			<td><textarea cols="20" style=" height:80px" readonly><?php echo $goal ?></textarea></td>
		</tr>
		<tr>
            <td><label>Frequency:</label></td>
            <td>
                <table border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td style="padding-left: 0px;">
                        Every&nbsp;
                        <select id="frequency_year" name="data[HealthMaintenancePlan][frequency_year]" disabled>
                        <?php
                        for ($i = 0; $i <= 20; ++$i)
                        {
                            ?><option value="<?php echo $i ?>" <?php if($frequency_year==$i) { echo 'selected'; }?>><?php echo $i ?></option><?php
                        }
                        ?>
                        </select>
                        &nbsp;Year(s) 
                    </td>
                    <td style="padding-left: 0px; padding-right: 30px;">
                        ,&nbsp;Every&nbsp;
                        <select id="frequency_month" name="data[HealthMaintenancePlan][frequency_month]" disabled>
                        <?php
                        for ($i = 0; $i <= 12; ++$i)
                        {
                            ?><option value="<?php echo $i ?>" <?php if($frequency_month==$i) { echo 'selected'; }?>><?php echo $i ?></option><?php
                        }
                        ?>
                        </select>
                        &nbsp;Month(s)
                    </td>
                  </tr>
                </table>
            </td>
        </tr>
		<tr>
		    <td class="top_pos"><label>Start Date:</label></td>
		    <td><?php echo $this->element("date", array('name' => 'data[EncounterPlanHealthMaintenanceEnrollment][enrollment_start]', 'js' => '', 'id' => 'enrollment_start', 'value' => (isset($Enrollments['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start'])?__date($global_date_format, strtotime(@$Enrollments['EncounterPlanHealthMaintenanceEnrollment']['enrollment_start'])):__date($global_date_format)), 'required' => false, 'onselect' => 'function(){start_date_on_select();}')); ?></td>
		    </tr>
		<tr>
		    <td class="top_pos"><label>End Date:</label></td>
		    <td><?php echo $this->element("date", array('name' => 'data[EncounterPlanHealthMaintenanceEnrollment][enrollment_end]', 'js' => '', 'id' => 'enrollment_end', 'value' => __date($global_date_format, strtotime(@$enrollment_end)), 'required' => false)); ?></td>
		    </tr>
		<tr>
			<td><label>Plan Action:</label></td>
			<td><select style="width:60px; background-color:#FFFFFF" disabled><option><?php echo $action ?></option></select></td>
		</tr>
	</table>
    
	<?php
	if ($task == "edit")
	{
		if(isset($Enrollments))
		{
			extract($Enrollments['EncounterPlanHealthMaintenanceEnrollment']);
			$enrollment_actions_json = $enrollment_actions;
			$enrollment_actions = json_decode($enrollment_actions, true);
		}
	}
	else
	{
		$signup_date = __date("Y-m-d");
		$status = "In Progress";
	}
	
	?>
    <script language="javascript" type="text/javascript">
	var targetdates = [];
	
	<?php
	if(isset($enrollment_actions))
	{
		foreach($enrollment_actions as $i => $enrollment_action)
		{
			?>
			var targetdates_data = [];
			<?php
			
			if (isset($enrollment_action['targetdates'])) {
				foreach($enrollment_action['targetdates'] as $j => $target_date)
				{
					?>
					var target_date = [];
					target_date['targetdate_month'] = '<?php echo @$target_date['targetdate_month']; ?>';
					target_date['targetdate_day'] = '<?php echo @$target_date['targetdate_day']; ?>';
					target_date['targetdate_completed'] = '<?php echo @$target_date['targetdate_completed']; ?>';
					target_date['identifier'] = '<?php echo @$target_date['identifier']; ?>';
					targetdates_data[<?php echo $j; ?>] = target_date;
					<?php
				}
			}
			
			?>
			targetdates[<?php echo $i; ?>] = targetdates_data;
			<?php
		}
	}
	?>
	
	function start_date_on_select()
	{
		$('.action_frequency_select').change();
	}
	
	function actionFrequency(obj, i)
	{
		var index = $(obj).attr("index");
		
		$('#frequency_list_data_'+i).html('');
		
		var frequency_year = parseInt($('#frequency_year').val());
		var frequency_month = parseInt($('#frequency_month').val());
		var total_frequency_month = (frequency_year * 12) + frequency_month;
		var start_date = $('#enrollment_start').datepicker("getDate");
		
		for(var a = 1; a <= $('#frequency_' + i).val(); a++)
		{
			var html = '<div id="frequency_target_date_'+index+'_'+a+'">';
			html += '<input type="hidden" id="identifier_'+index+'_'+a+'" name="data[actions]['+index+'][targetdates]['+a+'][identifier]" value="" />';
			html += '<table cellspacing="0" cellpadding="0" class="form">';
			html += '<tr>';
			html += '<td width="180" valign="top" style="vertical-align:top; padding-top: 5px;"><label><span class="">Target Date</span> #'+a+':</label></td>';
			
			html += '<td>';
			html += 'Month:&nbsp; ';
			html += '<select onchange="showDay('+index+', '+a+')" id="targetdate_month_'+index+'_'+a+'" name="data[actions]['+index+'][targetdates]['+a+'][targetdate_month]">';
			html += '<option value="">Select Month</option>';
			html += '<option value="1">January</option><option value="2">February</option><option value="3">March</option><option value="4">April</option><option value="5">May</option><option value="6">June</option><option value="7">July</option><option value="8">August</option><option value="9">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option></select>';
			html += '</td>';
			
			html += '<td style="padding-left: 10px;">';			
			html += 'Day:&nbsp; ';
			html += '<select style="display: inline;" id="targetdate_31day_'+index+'_'+a+'" name="data[actions]['+index+'][targetdates]['+a+'][targetdate_day]">';
			html += '<option value="">Select Day</option>';
			html += '<option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option></select>';
							
			html += '<select disabled="disabled" style="display:none" id="targetdate_30day_'+index+'_'+a+'" name="data[actions]['+index+'][targetdates]['+a+'][targetdate_day]">';
			html += '<option selected="" value="">Select Day</option>';
			html += '<option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option></select>';
							
			html += '<select disabled="disabled" style="display:none" id="targetdate_28day_'+index+'_'+a+'" name="data[actions]['+index+'][targetdates]['+a+'][targetdate_day]">';
			html += '<option value="">Select Day</option>';
			html += '<option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option></select>';
			html += '</td>';
			
			html += '<td style="padding-left: 10px;"><label class="label_check_box_hx"><input type="checkbox" value="1" id="targetdate_completed_'+index+'_'+a+'" name="data[actions]['+index+'][targetdates]['+a+'][targetdate_completed]"> Completed</label></td>';
			html += '</tr>';
			html += '</table>';
			html += '</div>';
			
			$('#frequency_list_data_'+i).append(html);
			
			if(typeof targetdates[index] == "undefined" || typeof targetdates[index][a] == "undefined")
			{
				var val_month = start_date.getMonth() + 1;
				var val_day = start_date.getDate()
				$('#targetdate_month_'+index+'_'+a).val(val_month);
				$('#targetdate_31day_'+index+'_'+a).val(val_day);
				$('#targetdate_30day_'+index+'_'+a).val(val_day);
				$('#targetdate_28day_'+index+'_'+a).val(val_day);
				/*
				$('#targetdate_month_'+index+'_'+a).val('<?php echo __date("n"); ?>');
				$('#targetdate_31day_'+index+'_'+a).val('<?php echo __date("d"); ?>');
				$('#targetdate_30day_'+index+'_'+a).val('<?php echo __date("d"); ?>');
				$('#targetdate_28day_'+index+'_'+a).val('<?php echo __date("d"); ?>');
				*/
			}
			else
			{
				$('#targetdate_month_'+index+'_'+a).val(targetdates[index][a]['targetdate_month']);
				$('#targetdate_31day_'+index+'_'+a).val(targetdates[index][a]['targetdate_day']);
				$('#targetdate_30day_'+index+'_'+a).val(targetdates[index][a]['targetdate_day']);
				$('#targetdate_28day_'+index+'_'+a).val(targetdates[index][a]['targetdate_day']);
				
				$('#identifier_'+index+'_'+a).val(targetdates[index][a]['identifier']);
				
				if(targetdates[index][a]['targetdate_completed'] == '1')
				{
					$('#targetdate_completed_'+index+'_'+a).attr("checked", "checked");
				}
			}
			
			start_date.setMonth(start_date.getMonth()+total_frequency_month);
		}
	}
	
	function showDay(i, j)
	{
		$('#targetdate_28day_' + i + '_' + j).hide().attr('disabled', 'disabled');
		$('#targetdate_30day_' + i + '_' + j).hide().attr('disabled', 'disabled');
		$('#targetdate_31day_' + i + '_' + j).hide().attr('disabled', 'disabled');

		switch($('#targetdate_month_' + i + '_' + j).val())
		{
			case "2": $('#targetdate_28day_' + i + '_' + j).show().removeAttr('disabled'); break;
			case "4":
			case "6":
			case "9":
			case "11": $('#targetdate_30day_' + i + '_' + j).show().removeAttr('disabled'); break;
			default: $('#targetdate_31day_' + i + '_' + j).show().removeAttr('disabled'); break;
		}
	}
	</script>
    <?php
	
	for ($i = 1; $i <= $action; ++$i)
	{
		if($task == 'addnew' || $task == 'get')
		{
			$HealthMaintenanceAction = $PlanDetails['HealthMaintenanceAction'][($i - 1)];
			${"action_$i"} = $HealthMaintenanceAction['action'];
			${"action_id_$i"} = $HealthMaintenanceAction['action_id'];
			${"frequency_year_$i"} = $HealthMaintenanceAction['frequency_year'];
			${"frequency_month_$i"} = $HealthMaintenanceAction['frequency_month'];
			${"frequency_$i"} = $HealthMaintenanceAction['frequency'];
			${"subaction_$i"} = $HealthMaintenanceAction['subaction'];
			${"reminder_timeframe_$i"} = $HealthMaintenanceAction['reminder_timeframe'];
			${"followup_timeframe_$i"} = $HealthMaintenanceAction['followup_timeframe'];
		}
		else
		{
			${"action_$i"} = @$enrollment_actions[$i]['action'];
			${"action_id_$i"} = @$enrollment_actions[$i]['action_id'];
			${"frequency_$i"} = @$enrollment_actions[$i]['frequency'];
			${"reminder_timeframe_$i"} = @$enrollment_actions[$i]['reminder_timeframe'];
			${"followup_timeframe_$i"} = @$enrollment_actions[$i]['followup_timeframe'];
			${"completed_$i"} = @$enrollment_actions[$i]['completed'];
		}
		
		?>
		<table cellpadding="0" cellspacing="0" class="form" width="100%"><tr><td width=180></td><td>
		<table cellpadding="0" cellspacing="0" class="form" width="100%" style="border: 1px SOLID <?php echo $display_settings['color_scheme_properties']['field_border_color']; ?>; padding: 5px 5px 5px 5px">
			<tr><td>
            
            <table cellpadding="0" cellspacing="0" class="form" width="100%">
                <tr>
                	<td width=180 valign='top' style="vertical-align:top"><label>Action #<?php echo $i ?>:</label></td>
                	<td><textarea cols="20" name="data[actions][<?php echo $i; ?>][action]" id="action_<?php echo $i ?>" style=" height:80px"><?php echo ${"action_$i"} ?></textarea></td>
                </tr>
                <tr>
                    <td><label>Frequency:</label></td>
                    <td><select class="action_frequency_select" id="frequency_<?php echo $i ?>" index="<?php echo $i ?>" name="data[actions][<?php echo $i; ?>][frequency]" onchange="actionFrequency(this, '<?php echo $i ?>')">
                    <?php
                    for ($j = 0; $j <= 52; ++$j)
                    {
                        ?><option value="<?php echo $j ?>" <?php if(${"frequency_$i"}==$j) { echo 'selected'; }?>><?php echo $j ?></option><?php
                    }
                    ?>
                    </select> Time(s) a year
                    <script language="javascript" type="text/javascript">
                    $(document).ready(function()
                    {
                        $('#frequency_<?php echo $i ?>').change();
                    });
                    </script>
                    </td>
                </tr>
            </table>
            <div id="frequency_list_data_<?php echo $i; ?>"></div>
            <input type="hidden" name="data[actions][<?php echo $i ?>][action_id]" value="<?php echo ${"action_id_$i"}; ?>" />
            <table cellpadding="0" cellspacing="0" class="form" width="100%">
                <tr><td width=180><label>Reminder Timeframe:</label></td><td><input type="text" name="data[actions][<?php echo $i ?>][reminder_timeframe]" id="reminder_timeframe_<?php echo $i ?>" style="width:50px;" value="<?php echo ${"reminder_timeframe_$i"}; ?>" class="numeric_only"> Days</td></tr>
                <tr><td><label>Followup Timeframe:</label></td><td><input type="text" name="data[actions][<?php echo $i ?>][followup_timeframe]" id="followup_timeframe_<?php echo $i ?>" style="width:50px;" value="<?php echo ${"followup_timeframe_$i"}; ?>" class="numeric_only"> Days</td></tr>
                <tr><td><label>Completed:</label></td><td><label class="label_check_box" for="completed_<?php echo $i ?>"><input type="checkbox" name="data[actions][<?php echo $i ?>][completed]" id="completed_<?php echo $i ?>" value="Yes" <?php if(@${"completed_$i"} == 'Yes'): ?>checked<?php endif; ?> /></label></td></tr>
            </table>
            
			</td></tr>
		</table></td></tr></table><br>
		<?php
	}
	?>
	<table cellpadding="0" cellspacing="0" class="form" width="100%">
		<tr><td width=180 valign='top' style="vertical-align:top"><label>Signup Date:</label></td><td><?php echo $this->element("date", array('name' => 'data[EncounterPlanHealthMaintenanceEnrollment][signup_date]', 'js' => '', 'id' => 'signup_date', 'value' => __date($global_date_format, strtotime($signup_date)), 'required' => false)); ?></td></tr>
        <tr><td><label>Status:</label></td><td><select name="data[EncounterPlanHealthMaintenanceEnrollment][status]" id="status" >
		<option value="" selected>Select Status</option><?php                    
		$status_array = array("Patient Refused", "In Progress", "Completed", "On Hold", "Cancelled", "Not Done - Contraindication", "Not Done - Patient Declined", "Not Done - Patient Reason", "Not Done - Medical Reason", "Not Done - System Reason");
		for ($i = 0; $i < count($status_array); ++$i)
		{
			echo "<option value=\"$status_array[$i]\" ".($status==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
		}
		?></select></td></tr>
	</table>
	<div class="actions">
                <ul>
                    <?php if($page_access == 'W'): ?><li><a href="javascript: void(0);" onclick="<?php if($task == 'addnew'){?>document.frmHealthMaintenance.plan_id.name = 'data[EncounterPlanHealthMaintenanceEnrollment][plan_id]';<?php }?>$('#frmHealthMaintenance').submit();">Save</a></li><?php endif; ?>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
    
	<?php
		} ?>
		</form><?php
	} 
	else
    {	  
	   ?>
    <form id="frmHealthMaintenanceGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
      <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">          
            
            <tr deleteable="false">
			    <?php if($page_access == 'W'): ?><th width="15"><label for="master_chk_maintenance" class="label_check_box_hx"><input type="checkbox" id="master_chk_maintenance" class="master_chk" /></label></th><?php endif; ?>
				<th width=275><?php echo $paginator->sort('Plan Name', 'HealthMaintenancePlan.plan_name', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'class' => 'ajax'));?></th>
                <th width=275><?php echo $paginator->sort('Category', 'HealthMaintenancePlan.category', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Enrollment Type', 'diagnosis', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'class' => 'ajax'));?></th>
				<th width=150><?php echo $paginator->sort('Signup Date', 'signup_date', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'class' => 'ajax'));?></th>
				<th width=150><?php echo $paginator->sort('Status', 'status', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($EncounterPlanHealthMaintenanceEnrollment as $EncounterPlanHealthMaintenanceEnrollment_record):
            ?>
            <tr editlinkajax="<?php echo $html->url(array('action' => 'health_maintenance_plans', 'task' => 'edit', 'patient_id' => $patient_id, 'hm_enrollment_id' => $EncounterPlanHealthMaintenanceEnrollment_record['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id'])); ?>">
				<?php if($page_access == 'W'): ?><td class="ignore"><label for="child_chk<?php echo $EncounterPlanHealthMaintenanceEnrollment_record['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id']; ?>" class="label_check_box_hx"><input name="data[EncounterPlanHealthMaintenanceEnrollment][hm_enrollment_id][<?php echo $EncounterPlanHealthMaintenanceEnrollment_record['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id']; ?>]" id="child_chk<?php echo $EncounterPlanHealthMaintenanceEnrollment_record['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $EncounterPlanHealthMaintenanceEnrollment_record['EncounterPlanHealthMaintenanceEnrollment']['hm_enrollment_id']; ?>" /></td><?php endif; ?>
                <td><?php echo $EncounterPlanHealthMaintenanceEnrollment_record['HealthMaintenancePlan']['plan_name']; ?></td>
                <td><?php echo $EncounterPlanHealthMaintenanceEnrollment_record['HealthMaintenancePlan']['category']; ?></td>
                <td><?php echo $EncounterPlanHealthMaintenanceEnrollment_record['EncounterPlanHealthMaintenanceEnrollment']['diagnosis']; ?></td>
                <td><?php echo ((!strstr($EncounterPlanHealthMaintenanceEnrollment_record['EncounterPlanHealthMaintenanceEnrollment']['signup_date'], "0000")) and ($EncounterPlanHealthMaintenanceEnrollment_record['EncounterPlanHealthMaintenanceEnrollment']['signup_date'] !=''))?__date($global_date_format, strtotime($EncounterPlanHealthMaintenanceEnrollment_record['EncounterPlanHealthMaintenanceEnrollment']['signup_date'])):''; ?></td>
                <td><?php echo $EncounterPlanHealthMaintenanceEnrollment_record['EncounterPlanHealthMaintenanceEnrollment']['status']; ?></td>
            </tr>
            <?php endforeach; ?>
            
        </table>
        <?php if($page_access == 'W'): ?>
        <div style="width: auto; float: left;">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmHealthMaintenanceGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                 </ul>
            </div>
        </div>
        <?php endif; ?>		
    </form>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('EncounterPlanHealthMaintenanceEnrollment') || $paginator->hasNext('EncounterPlanHealthMaintenanceEnrollment'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('EncounterPlanHealthMaintenanceEnrollment'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'EncounterPlanHealthMaintenanceEnrollment', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
    <?php
	}
	?>
    
    </div>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
