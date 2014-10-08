<div style="overflow: hidden;">
	<?php echo $this->element('administration_health_maintenance_links'); ?>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
<?php 

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$ICD_autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';
$Medication_autoURL = $html->url(array('controller' => 'encounters','action' => 'meds_list', 'task' => 'load_autocomplete')) . '/';
$Allergy_autoURL = $html->url(array('controller' => 'administration','action' => 'health_maintenance', 'task' => 'allergy_load')) . '/';
$History_autoURL = $html->url(array('controller' => 'administration','action' => 'health_maintenance', 'task' => 'history_load')) . '/';
$Result_autoURL = $html->url(array('controller' => 'administration','action' => 'health_maintenance', 'task' => 'result_load')) . '/';

if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		extract($EditItem['HealthMaintenancePlan']);
		$id_field = '<input type="hidden" name="data[HealthMaintenancePlan][plan_id]" id="plan_id" value="'.$plan_id.'" />';
	}
	else
	{
		//Init default value here
		$id_field = "";
		$plan_name = "";
		$description = "";
		$category = "";
		$gender = "";
		$from_age = "";
		$from_month = "";
		$to_age = "";
		$to_month = "";
		$include_rule_icd = "";
		$include_rule_icd_series = "";
		$include_rule_medication = "";
		$include_rule_allergy = "";
		$include_rule_patient_history = "";
		$include_rule_lab_test_result = "";
		$exclude_rule_icd = "";
		$exclude_rule_icd_series = "";
		$exclude_rule_medication = "";
		$exclude_rule_allergy = "";
		$exclude_rule_patient_history = "";
		$exclude_rule_lab_test_result = "";
		$goal = "";
		$action = "";
		$frequency_year = 0;
		$frequency_month = 0;
		$status = "";
		$auto_enrollment = "";
		$clinical_alerts = "";
		$patient_reminders = "";
		$plan_start = date("Y-m-d");
		$plan_end = "";
		$continuous = 1;
	}
	?>
	<script language=javascript>
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
	<div style="overflow: hidden;">
		<form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <?php echo $id_field; ?>
			<table cellpadding="0" cellspacing="0" class="form" width="100%">
				<tr>
					<td width=180><label>Plan Name:</label></td>
					<td><input type="text" name="data[HealthMaintenancePlan][plan_name]" id="plan_name" value="<?php echo $plan_name; ?>" class="required" style="width:450px"><!--&nbsp;&nbsp;&nbsp;&nbsp;<label class="label_check_box" for="auto_enrollment"><input type="checkbox" name="data[HealthMaintenancePlan][auto_enrollment]" id="auto_enrollment" value="Yes" <?php if($auto_enrollment == "Yes") { echo 'checked="checked"'; } ?>/>&nbsp;Auto Enrollment</label>-->&nbsp;&nbsp;&nbsp;&nbsp;<label class="label_check_box" for="clinical_alerts"><input type="checkbox" name="data[HealthMaintenancePlan][clinical_alerts]" id="clinical_alerts" value="Yes" <?php if($clinical_alerts == "Yes") { echo 'checked="checked"'; } ?>/>&nbsp;Clinical Alerts</label>&nbsp;&nbsp;&nbsp;&nbsp;<label class="label_check_box" for="patient_reminders"><input type="checkbox" name="data[HealthMaintenancePlan][patient_reminders]" id="patient_reminders" value="Yes" <?php if($patient_reminders == "Yes") { echo 'checked="checked"'; } ?>/>&nbsp;Patient Reminders</label></td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Description:</label></td>
					<td><textarea cols="20" name="data[HealthMaintenancePlan][description]" id="description" style=" height:80px"><?php echo $description ?></textarea></td>
				</tr>
				<tr>
					<td><label>Category:</label></td>
					<td>
					<select name="data[HealthMaintenancePlan][category]" id="category">
					<option value="" selected>Select Category</option>
					<option value="Disease Management" <?php echo ($category=='Disease Management'? "selected='selected'":''); ?>>Disease Management</option>
					<option value="Preventive Health" <?php echo ($category=='Preventive Health'? "selected='selected'":''); ?> > Preventive Health</option>
					<option value="Wellness" <?php echo ($category=='Wellness'? "selected='selected'":''); ?> > Wellness</option>
					</select>
				</td>
				<tr>
					<td><label>Gender:</label></td>
					<td>
					<select name="data[HealthMaintenancePlan][gender]" id="category">
					<option value="" selected>Both</option>
					<option value="F" <?php echo ($gender=='F'? "selected='selected'":''); ?> > Female</option>
					<option value="M" <?php echo ($gender=='M'? "selected='selected'":''); ?> > Male</option>
					</select></td>
				</tr>
				<tr>
					<td><label>From Age:</label></td>
					<td><select id="from_age" name="data[HealthMaintenancePlan][from_age]">
					<?php
					for ($i = 0; $i <= 130; ++$i)
					{
						?><option value="<?php echo $i ?>" <?php if($from_age==$i) { echo 'selected'; }?>><?php echo $i ?></option><?php
					}
					?>
					</select>&nbsp;Year(s)&nbsp;<select id="from_month" name="data[HealthMaintenancePlan][from_month]">
					<?php
					for ($i = 0; $i <= 11; ++$i)
					{
						?><option value="<?php echo $i ?>" <?php if($from_month==$i) { echo 'selected'; }?>><?php echo $i ?></option><?php
					}
					?>
					</select>&nbsp;Month(s)</td>
				</tr>
				<tr>
					<td><label>To Age:</label></td>
					<td><select id="to_age" name="data[HealthMaintenancePlan][to_age]">
					<?php
					for ($i = 0; $i <= 130; ++$i)
					{
						?><option value="<?php echo $i ?>" <?php if($to_age==$i) { echo 'selected'; }?>><?php echo $i ?></option><?php
					}
					?>
					</select>&nbsp;Year(s)&nbsp;<select id="to_month" name="data[HealthMaintenancePlan][to_month]">
					<?php
					for ($i = 0; $i <= 11; ++$i)
					{
						?><option value="<?php echo $i ?>" <?php if($to_month==$i) { echo 'selected'; }?>><?php echo $i ?></option><?php
					}
					?>
					</select>&nbsp;Month(s)</td>
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
							});
						</script>
                    	<table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="padding-left: 0px;">
                                	<input type="hidden" name="data[HealthMaintenancePlan][include_rule_icd]" id="include_rule_icd" value="<?php echo $include_rule_icd ?>" />
                                	<div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                    	<label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="include_rule_icd_check" >
                                    	<input type="checkbox" name="data[HealthMaintenancePlan][include_rule_icd_check]" id="include_rule_icd_check" value="Yes" <?php echo ($include_rule_icd?"checked":"") ?> >&nbsp;ICD
                                    	</label><button target_item="include_rule_icd_add" target_chk="include_rule_icd_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;">&nbsp;</button>
                                    </div>
                                    
                                    <input type="hidden" name="data[HealthMaintenancePlan][include_rule_medication]" id="include_rule_medication" value="<?php echo $include_rule_medication ?>" />
                                    <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                    	<label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="include_rule_medication_check" >
                                    	<input type="checkbox" name="data[HealthMaintenancePlan][include_rule_medication_check]" id="include_rule_medication_check" value="Yes" <?php echo ($include_rule_medication?"checked":"") ?> >&nbsp;Medication
                                    	</label><button target_item="include_rule_medication_add" target_chk="include_rule_medication_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;">&nbsp;</button>
                                    </div>
                                    
                                    <input type="hidden" name="data[HealthMaintenancePlan][include_rule_allergy]" id="include_rule_allergy" value="<?php echo $include_rule_allergy ?>" />
                        			<div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                    	<label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="include_rule_allergy_check" >
                                    	<input type="checkbox" name="data[HealthMaintenancePlan][include_rule_allergy_check]" id="include_rule_allergy_check" value="Yes" <?php echo ($include_rule_allergy?"checked":"") ?> >&nbsp;Allergy
                                    	</label><button target_item="include_rule_allergy_add" target_chk="include_rule_allergy_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                                    </div>
                        			
                                    <input type="hidden" name="data[HealthMaintenancePlan][include_rule_patient_history]" id="include_rule_patient_history" value="<?php echo $include_rule_patient_history ?>" />
                        			<div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                    	<label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="include_rule_patient_history_check" >
                                    	<input type="checkbox" name="data[HealthMaintenancePlan][include_rule_patient_history_check]" id="include_rule_patient_history_check" value="Yes" <?php echo ($include_rule_patient_history?"checked":"") ?> >&nbsp;Patient History
                                    	</label><button target_item="include_rule_patient_history_add" target_chk="include_rule_patient_history_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                                    </div>
                                    
                                    <input type="hidden" name="data[HealthMaintenancePlan][include_rule_lab_test_result]" id="include_rule_lab_test_result" value="<?php echo $include_rule_lab_test_result ?>" />
                        			<div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                    	<label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="include_rule_lab_test_result_check" >
                                    	<input type="checkbox" name="data[HealthMaintenancePlan][include_rule_lab_test_result_check]" id="include_rule_lab_test_result_check" value="Yes" <?php echo ($include_rule_lab_test_result?"checked":"") ?> >&nbsp;Lab Test Result
                                    	</label><button target_item="include_rule_lab_test_result_add" target_chk="include_rule_lab_test_result_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="parent_tr" style="display: none;">
                                <td style="padding-left: 0px;">
                                	<span class="rule_item" id="include_rule_icd_add" style="display: none;">
                                        <table cellpadding="0" cellspacing="0" class="form" width=650>
                                            <tr>
                                                <td><input type=text name="include_rule_icd_text" id ="include_rule_icd_text" style="width:450px">&nbsp;<a class="btn" href="javascript:void(0);" style="float: none;" onclick="addRule('include_rule_icd')">Add</a></td>
                                                <td><span id="add_include_rule_icd" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                                <td width=120><label class="label_check_box" for="include_rule_icd_series"><input type="checkbox" name="data[HealthMaintenancePlan][include_rule_icd_series]" id="include_rule_icd_series" value="Yes" <?php echo ($include_rule_icd_series=="Yes"?"checked":"") ?>>&nbsp;Use Series</label></td>
                                            </tr>
                                        </table>
                                        <div id="show_include_rule_icd" style="display:<?php echo ($include_rule_icd?"block":"none") ?>">
                                            <table id="list_include_rule_icd" cellpadding="0" cellspacing="0" class="small_table form">
                                                <tr deleteable="false">
                                                    <th colspan=2>ICD<span id="load_include_rule_icd" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
                                                </tr>
                                            </table>
                                        </div>
                                    </span>
                                    
                                    <span class="rule_item" id="include_rule_medication_add" style="display: none;">
                                        <table cellpadding="0" cellspacing="0" class="form" width=550>
                                            <tr>
                                                <td><input type=text name="include_rule_medication_text" id ="include_rule_medication_text" style="width:450px">&nbsp;<a class="btn" href="javascript:void(0);" style="float: none;" onclick="addRule('include_rule_medication')">Add</a></td>
                                                <td><span id="add_include_rule_medication" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                            </tr>
                                        </table>
                                        <div id="show_include_rule_medication" style="display:<?php echo ($include_rule_medication?"block":"none") ?>">
                                            <table id="list_include_rule_medication" cellpadding="0" cellspacing="0" class="small_table form">
                                                <tr deleteable="false">
                                                    <th colspan=2>Medication<span id="load_include_rule_medication" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
                                                </tr>
                                            </table>
                                        </div>
                                    </span>
                                    
                                    <span class="rule_item" id="include_rule_allergy_add" style="display: none;">
                                        <table cellpadding="0" cellspacing="0" class="form" width=550>
                                            <tr>
                                                <td><input type=text name="include_rule_allergy_text" id ="include_rule_allergy_text" style="width:450px">&nbsp;<a class="btn" href="javascript:void(0);" style="float: none;" onclick="addRule('include_rule_allergy')">Add</a></td>
                                                <td><span id="add_include_rule_allergy" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                            </tr>
                                        </table>
                                        <div id="show_include_rule_allergy" style="display:<?php echo ($include_rule_allergy?"block":"none") ?>">
                                            <table id="list_include_rule_allergy" cellpadding="0" cellspacing="0" class="small_table form">
                                                <tr deleteable="false">
                                                    <th colspan=2>Allergy<span id="load_include_rule_allergy" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
                                                </tr>
                                            </table>
                                        </div>
                                    </span>
                                    
                                    <span class="rule_item" id="include_rule_patient_history_add" style="display: none;">
                                        <table cellpadding="0" cellspacing="0" class="form" width=550>
                                            <tr>
                                                <td><input type=text name="include_rule_patient_history_text" id ="include_rule_patient_history_text" style="width:450px">&nbsp;<a class="btn" href="javascript:void(0);" style="float: none;" onclick="addRule('include_rule_patient_history')">Add</a></td>
                                                <td><span id="add_include_rule_patient_history" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                            </tr>
                                        </table>
                                        <div id="show_include_rule_patient_history" style="display:<?php echo ($include_rule_patient_history?"block":"none") ?>">
                                            <table id="list_include_rule_patient_history" cellpadding="0" cellspacing="0" class="small_table form">
                                                <tr deleteable="false"><th colspan=2>Patient History<span id="load_include_rule_patient_history" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th></tr>
                                            </table>
                                        </div>
                                    </span>
                                    
                                    <span class="rule_item" id="include_rule_lab_test_result_add" style="display: none;">
                                        <table cellpadding="0" cellspacing="0" class="form" width=550>
                                            <tr>
                                                <td><input type=text name="include_rule_lab_test_result_text" id ="include_rule_lab_test_result_text" style="width:450px">&nbsp;<a class="btn" href="javascript:void(0);" style="float: none;" onclick="addRule('include_rule_lab_test_result')">Add</a></td>
                                                <td><span id="add_include_rule_lab_test_result" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                            </tr>
                                        </table>
                                        <div id="show_include_rule_lab_test_result" style="display:<?php echo ($include_rule_lab_test_result?"block":"none") ?>">
                                            <table id="list_include_rule_lab_test_result" cellpadding="0" cellspacing="0" class="small_table form">
                                                <tr deleteable="false"><th colspan=2>Lab Test Result<span id="load_include_rule_lab_test_result" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th></tr>
                                            </table>
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
                                    	<input type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_icd_check]" id="exclude_rule_icd_check" value="Yes" <?php echo ($exclude_rule_icd?"checked":"") ?> >&nbsp;ICD
                                    	</label><button target_item="exclude_rule_icd_add" target_chk="exclude_rule_icd_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;">&nbsp;</button>
                                    </div>
                                    
                                    <input type="hidden" name="data[HealthMaintenancePlan][exclude_rule_medication]" id="exclude_rule_medication" value="<?php echo $exclude_rule_medication ?>" />
                                    <div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                    	<label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="exclude_rule_medication_check" >
                                    	<input type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_medication_check]" id="exclude_rule_medication_check" value="Yes" <?php echo ($exclude_rule_medication?"checked":"") ?> >&nbsp;Medication
                                    	</label><button target_item="exclude_rule_medication_add" target_chk="exclude_rule_medication_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;">&nbsp;</button>
                                    </div>
                                    
                                    <input type="hidden" name="data[HealthMaintenancePlan][exclude_rule_allergy]" id="exclude_rule_allergy" value="<?php echo $exclude_rule_allergy ?>" />
                        			<div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                    	<label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="exclude_rule_allergy_check" >
                                    	<input type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_allergy_check]" id="exclude_rule_allergy_check" value="Yes" <?php echo ($exclude_rule_allergy?"checked":"") ?> >&nbsp;Allergy
                                    	</label><button target_item="exclude_rule_allergy_add" target_chk="exclude_rule_allergy_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                                    </div>
                        			
                                    <input type="hidden" name="data[HealthMaintenancePlan][exclude_rule_patient_history]" id="exclude_rule_patient_history" value="<?php echo $exclude_rule_patient_history ?>" />
                        			<div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                    	<label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="exclude_rule_patient_history_check" >
                                    	<input type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_patient_history_check]" id="exclude_rule_patient_history_check" value="Yes" <?php echo ($exclude_rule_patient_history?"checked":"") ?> >&nbsp;Patient History
                                    	</label><button target_item="exclude_rule_patient_history_add" target_chk="exclude_rule_patient_history_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                                    </div>
                                    
                                    <input type="hidden" name="data[HealthMaintenancePlan][exclude_rule_lab_test_result]" id="exclude_rule_lab_test_result" value="<?php echo $exclude_rule_lab_test_result ?>" />
                        			<div style="float:left; display:inline; margin-left:2px; margin-right: 6px;">
                                    	<label style="vertical-align:middle; cursor:pointer; float:none;" class="label_check_box_home" for="exclude_rule_lab_test_result_check" >
                                    	<input type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_lab_test_result_check]" id="exclude_rule_lab_test_result_check" value="Yes" <?php echo ($exclude_rule_lab_test_result?"checked":"") ?> >&nbsp;Lab Test Result
                                    	</label><button target_item="exclude_rule_lab_test_result_add" target_chk="exclude_rule_lab_test_result_check" class="lbl_btn" style="vertical-align:middle; border:1px solid #fff; display:inline-block;border: 1px solid #fff;-webkit-box-shadow:0 0 2px rgba(0,0,0,0.3); -moz-box-shadow:0 0 2px rgba(0,0,0,0.3);box-shadow:0 0 2px rgba(0,0,0,0.3);-webkit-transition:all .2s ease-out;-moz-transition:all .2s ease-out;-o-transition:all .2s ease-out;" onclick="">&nbsp;</button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="parent_tr" style="display: none;">
                                <td style="padding-left: 0px;">
                                	<span class="rule_item" id="exclude_rule_icd_add" style="display: none;">
                                        <table cellpadding="0" cellspacing="0" class="form" width=650>
                                            <tr>
                                                <td><input type=text name="exclude_rule_icd_text" id ="exclude_rule_icd_text" style="width:450px">&nbsp;<a class="btn" href="javascript:void(0);" style="float: none;" onclick="addRule('exclude_rule_icd')">Add</a></td>
                                                <td><span id="add_exclude_rule_icd" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                                <td width=120><label class="label_check_box" for="exclude_rule_icd_series"><input type="checkbox" name="data[HealthMaintenancePlan][exclude_rule_icd_series]" id="exclude_rule_icd_series" value="Yes" <?php echo ($exclude_rule_icd_series=="Yes"?"checked":"") ?>>&nbsp;Use Series</label></td>
                                            </tr>
                                        </table>
                                        <div id="show_exclude_rule_icd" style="display:<?php echo ($exclude_rule_icd?"block":"none") ?>">
                                            <table id="list_exclude_rule_icd" cellpadding="0" cellspacing="0" class="small_table form">
                                                <tr deleteable="false">
                                                    <th colspan=2>ICD<span id="load_exclude_rule_icd" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
                                                </tr>
                                            </table>
                                        </div>
                                    </span>
                                    
                                    <span class="rule_item" id="exclude_rule_medication_add" style="display: none;">
                                        <table cellpadding="0" cellspacing="0" class="form" width=550>
                                            <tr>
                                                <td><input type=text name="exclude_rule_medication_text" id ="exclude_rule_medication_text" style="width:450px">&nbsp;<a class="btn" href="javascript:void(0);" style="float: none;" onclick="addRule('exclude_rule_medication')">Add</a></td>
                                                <td><span id="add_exclude_rule_medication" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                            </tr>
                                        </table>
                                        <div id="show_exclude_rule_medication" style="display:<?php echo ($exclude_rule_medication?"block":"none") ?>">
                                            <table id="list_exclude_rule_medication" cellpadding="0" cellspacing="0" class="small_table form">
                                                <tr deleteable="false">
                                                    <th colspan=2>Medication<span id="load_exclude_rule_medication" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
                                                </tr>
                                            </table>
                                        </div>
                                    </span>
                                    
                                    <span class="rule_item" id="exclude_rule_allergy_add" style="display: none;">
                                        <table cellpadding="0" cellspacing="0" class="form" width=550>
                                            <tr>
                                                <td><input type=text name="exclude_rule_allergy_text" id ="exclude_rule_allergy_text" style="width:450px">&nbsp;<a class="btn" href="javascript:void(0);" style="float: none;" onclick="addRule('exclude_rule_allergy')">Add</a></td>
                                                <td><span id="add_exclude_rule_allergy" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                            </tr>
                                        </table>
                                        <div id="show_exclude_rule_allergy" style="display:<?php echo ($exclude_rule_allergy?"block":"none") ?>">
                                            <table id="list_exclude_rule_allergy" cellpadding="0" cellspacing="0" class="small_table form">
                                                <tr deleteable="false">
                                                    <th colspan=2>Allergy<span id="load_exclude_rule_allergy" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th>
                                                </tr>
                                            </table>
                                        </div>
                                    </span>
                                    
                                    <span class="rule_item" id="exclude_rule_patient_history_add" style="display: none;">
                                        <table cellpadding="0" cellspacing="0" class="form" width=550>
                                            <tr>
                                                <td><input type=text name="exclude_rule_patient_history_text" id ="exclude_rule_patient_history_text" style="width:450px">&nbsp;<a class="btn" href="javascript:void(0);" style="float: none;" onclick="addRule('exclude_rule_patient_history')">Add</a></td>
                                                <td><span id="add_exclude_rule_patient_history" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                            </tr>
                                        </table>
                                        <div id="show_exclude_rule_patient_history" style="display:<?php echo ($exclude_rule_patient_history?"block":"none") ?>">
                                            <table id="list_exclude_rule_patient_history" cellpadding="0" cellspacing="0" class="small_table form">
                                                <tr deleteable="false"><th colspan=2>Patient History<span id="load_exclude_rule_patient_history" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th></tr>
                                            </table>
                                        </div>
                                    </span>
                                    
                                    <span class="rule_item" id="exclude_rule_lab_test_result_add" style="display: none;">
                                        <table cellpadding="0" cellspacing="0" class="form" width=550>
                                            <tr>
                                                <td><input type=text name="exclude_rule_lab_test_result_text" id ="exclude_rule_lab_test_result_text" style="width:450px">&nbsp;<a class="btn" href="javascript:void(0);" style="float: none;" onclick="addRule('exclude_rule_lab_test_result')">Add</a></td>
                                                <td><span id="add_exclude_rule_lab_test_result" style="display:none"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                            </tr>
                                        </table>
                                        <div id="show_exclude_rule_lab_test_result" style="display:<?php echo ($exclude_rule_lab_test_result?"block":"none") ?>">
                                            <table id="list_exclude_rule_lab_test_result" cellpadding="0" cellspacing="0" class="small_table form">
                                                <tr deleteable="false"><th colspan=2>Lab Test Result<span id="load_exclude_rule_lab_test_result" style="float: right; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></th></tr>
                                            </table>
                                        </div>
                                    </span>&nbsp;
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Goal:</label></td>
					<td><textarea cols="20" name="data[HealthMaintenancePlan][goal]" id="goal" style=" height:80px"><?php echo $goal ?></textarea></td>
				</tr>
				<tr>
					<td><label>Frequency:</label></td>
					<td>
                    	<table border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td style="padding-left: 0px;">
                            	Every&nbsp;
                                <select id="frequency_year" name="data[HealthMaintenancePlan][frequency_year]">
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
                                <select id="frequency_month" name="data[HealthMaintenancePlan][frequency_month]">
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
				    <td><?php echo $this->element("date", array('name' => 'data[HealthMaintenancePlan][plan_start]', 'js' => '', 'id' => 'plan_start', 'value' => __date($global_date_format, strtotime($plan_start)), 'required' => false)); ?></td>
			    </tr>
				<tr>
				    <td class="top_pos"><label>End Date:</label></td>
				    <td><?php echo $this->element("date", array('name' => 'data[HealthMaintenancePlan][plan_end]', 'js' => '', 'id' => 'plan_end', 'value' => __date($global_date_format, strtotime($plan_end)), 'required' => false)); ?></td>
			    </tr>
				<tr>
					<td><label>Plan Action:</label></td>
					<td><select id="action" name="data[HealthMaintenancePlan][action]">
					<?php
					for ($i = 0; $i <= 10; ++$i)
					{
						?><option value="<?php echo $i ?>" <?php if($action==$i) { echo 'selected'; }?>><?php echo $i ?></option><?php
					}
					?>
					</select></td>
				</tr>
			</table>
			<?php
			for ($i = 1; $i <= 10; ++$i)
			{
				if($task == 'edit' and $action >= $i)
				{
					$HealthMaintenanceAction = $EditItem['HealthMaintenanceAction'][($i - 1)];
					${"id_field_$i"} = '<input type="hidden" name="data[HealthMaintenanceAction][action_id_'.$i.']" id="action_id_'.$i.'" value="'.$HealthMaintenanceAction['action_id'].'" />';
					${"action_$i"} = $HealthMaintenanceAction['action'];
					${"frequency_year_$i"} = $HealthMaintenanceAction['frequency_year'];
					${"frequency_month_$i"} = $HealthMaintenanceAction['frequency_month'];
					${"frequency_$i"} = $HealthMaintenanceAction['frequency'];
					${"subaction_$i"} = $HealthMaintenanceAction['subaction'];
					
					${"reminder_timeframe_$i"} = $HealthMaintenanceAction['reminder_timeframe'];
					${"followup_timeframe_$i"} = $HealthMaintenanceAction['followup_timeframe'];
					${"completed_$i"} = $HealthMaintenanceAction['completed'];
				}
				else
				{
					${"id_field_$i"} = "";
					${"action_$i"} = "";
					${"frequency_year_$i"} = "";
					${"frequency_month_$i"} = "";
					${"frequency_$i"} = "1";
					${"subaction_$i"} = "";
					${"targetdate_$i"} = __date("Y-m-d");
					
					${"reminder_timeframe_$i"} = "";
					${"followup_timeframe_$i"} = "";
					${"completed_$i"} = "";
				}

				echo "<div id='action_$i' style='display:".($action < $i?"none":"block")."'>".${"id_field_$i"}; 
				
				?>
				<table cellpadding="0" cellspacing="0" class="form" width="100%"><tr><td width=180></td><td>
				<table cellpadding="0" cellspacing="0" class="form" width="100%" style="border: 1px SOLID <?php echo $display_settings['color_scheme_properties']['field_border_color']; ?>; padding: 5px 5px 5px 5px">
					<tr><td>
                    
                    <table cellpadding="0" cellspacing="0" class="form" width="100%">
						<tr><td width=180 valign='top' style="vertical-align:top"><label>Action #<?php echo $i ?>:</label></td><td><textarea cols="20" name="data[HealthMaintenanceAction][action_<?php echo $i ?>]" id="action_<?php echo $i ?>" style=" height:80px"><?php echo ${"action_$i"} ?></textarea></td></tr>
						<tr>
							<td><label>Frequency:</label></td>
							<td><select id="frequency_<?php echo $i ?>" index="<?php echo $i ?>" name="data[HealthMaintenanceAction][frequency_<?php echo $i ?>]">
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

					<?php echo "<div id='action_details_$i' style='display:".(${"subaction_$i"} > 0?"none":"block")."'>"; ?>
					
					<table cellpadding="0" cellspacing="0" class="form" width="100%">
						<tr><td width=180><label>Reminder Timeframe:</label></td><td><input type="text" name="data[HealthMaintenanceAction][reminder_timeframe_<?php echo $i ?>]" id="reminder_timeframe_<?php echo $i ?>" style="width:50px;" value="<?php echo ${"reminder_timeframe_$i"}; ?>" class="numeric_only"> Days</td></tr>
						<tr><td><label>Followup Timeframe:</label></td><td><input type="text" name="data[HealthMaintenanceAction][followup_timeframe_<?php echo $i ?>]" id="followup_timeframe_<?php echo $i ?>" style="width:50px;" value="<?php echo ${"followup_timeframe_$i"}; ?>" class="numeric_only"> Days</td></tr>
					</table></div></td></tr>
				</table></td></tr></table><br>
				</div>
				<?php
			}
			?>
			<table cellpadding="0" cellspacing="0" class="form" width="100%">
				<tr height="35px">
					<td width=180><label>Activation Status:</label></td><td>
					<label class="label_check_box" for="status_activate"><input type="checkbox" name="data[HealthMaintenancePlan][status]" id="status_activate" value="Activated" <?php if($status == 'Activated'): ?>checked<?php endif; ?>/> Activate Plan for All Patients</label>&nbsp;&nbsp;&nbsp;&nbsp;
					</td></tr>
				</tr>
			</table>
        </form>
    </div>
    <div class="actions">
        <ul>
        	<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
			<li><?php echo $html->link(__('Cancel', true), array('action' => 'health_maintenance_plans'));?></li>
        </ul>
    </div>
	<script language=javascript>
	$(document).ready(function()
	{
		$("#frm").validate({errorElement: "div"});
		$("#include_rule_icd_text").autocomplete('<?php echo $ICD_autoURL ; ?>series:'+$('#include_rule_icd_series').is(':checked')+'/', {
			max: 20,
			minChars: 2,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});
		$('#include_rule_icd_series').change(function()
		{
			$("#include_rule_icd_text").unautocomplete();
			$("#include_rule_icd_text").autocomplete('<?php echo $ICD_autoURL ; ?>series:'+$('#include_rule_icd_series').is(':checked')+'/', {
				max: 20,
				minChars: 2,
				mustMatch: false,
				matchContains: false,
				scrollHeight: 300
			});
		});
		$("#include_rule_medication_text").autocomplete('<?php echo $Medication_autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		$("#include_rule_allergy_text").autocomplete('<?php echo $Allergy_autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		$("#include_rule_patient_history_text").autocomplete('<?php echo $History_autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		$("#include_rule_lab_test_result_text").autocomplete('<?php echo $Result_autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		$("#exclude_rule_icd_text").autocomplete('<?php echo $ICD_autoURL ; ?>series:'+$('#exclude_rule_icd_series').is(':checked')+'/', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		$('#exclude_rule_icd_series').change(function()
		{
			$("#exclude_rule_icd_text").unautocomplete();
			$("#exclude_rule_icd_text").autocomplete('<?php echo $ICD_autoURL ; ?>series:'+$('#exclude_rule_icd_series').is(':checked')+'/', {
				max: 20,
				minChars: 2,
				mustMatch: false,
				matchContains: false,
				scrollHeight: 300
			});
		});
		$("#exclude_rule_medication_text").autocomplete('<?php echo $Medication_autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		$("#exclude_rule_allergy_text").autocomplete('<?php echo $Allergy_autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		$("#exclude_rule_patient_history_text").autocomplete('<?php echo $History_autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		$("#exclude_rule_lab_test_result_text").autocomplete('<?php echo $Result_autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		$('#action').change(function()
		{
			for (i = 1; i <= 10; ++i)
			{
				if (i <= $(this).val())
				{
					$('#action_' + i).show();
				}
				else
				{
					$('#action_' + i).hide();
				}
			}
		});
		resetRule('include_rule_icd', '<?php echo $include_rule_icd ?>');
		resetRule('include_rule_medication', '<?php echo $include_rule_medication ?>');
		resetRule('include_rule_allergy', '<?php echo $include_rule_allergy ?>');
		resetRule('include_rule_patient_history', '<?php echo $include_rule_patient_history ?>');
		resetRule('include_rule_lab_test_result ', '<?php echo $include_rule_lab_test_result  ?>');
		resetRule('exclude_rule_icd', '<?php echo $exclude_rule_icd ?>');
		resetRule('exclude_rule_medication', '<?php echo $exclude_rule_medication ?>');
		resetRule('exclude_rule_allergy', '<?php echo $exclude_rule_allergy ?>');
		resetRule('exclude_rule_patient_history', '<?php echo $exclude_rule_patient_history ?>');
		resetRule('exclude_rule_lab_test_result ', '<?php echo $exclude_rule_lab_test_result  ?>');
	});
	function addRule(rule)
	{
		if ($('#' + rule + '_text').val())
		{
			$("#add_" + rule).show();
			var formobj = $("<form></form>");
			formobj.append('<input name="data[current]" id="current_data" type="hidden" value="'+$('#' + rule).val()+'">');
			formobj.append('<input name="data[new]" id="new_data" type="hidden" value="'+$('#' + rule + '_text').val()+'">');
			$.post('<?php echo $this->Session->webroot; ?>administration/health_maintenance_plans/task:add_rule', formobj.serialize(),
			function(data)
			{
				$('#' + rule).val(data);
				$('#' + rule + '_text').val('');
				$("#add_" + rule).hide();
				resetRule(rule, data);
			});
		}
	}
	function deleteRule(rule, value)
	{
		$("#load_" + rule).show();
		var formobj = $("<form></form>");
		formobj.append('<input name="data[current]" id="current_data" type="hidden" value="'+$('#' + rule).val()+'">');
		formobj.append('<input name="data[new]" id="new_data" type="hidden" value="'+value+'">');
		$.post('<?php echo $this->Session->webroot; ?>administration/health_maintenance_plans/task:delete_rule', formobj.serialize(),
		function(data)
		{
			$('#' + rule).val(data);
			resetRule(rule, data);
		});
	}
	function resetRule(rule, data)
	{
		$('#list_'+rule+' tr').each(function()
		{
			if($(this).attr('deleteable') == 'true')
			{
				$(this).remove();
			}
		});

		if (data)
		{
			data = data.split('|');
			if(data.length > 0)
			{
				$("#show_" + rule).show();

				if (data.length > 3)
				{
					$("#show_" + rule).css("height", "120px").css("overflow", "auto");
				}

				$("#load_" + rule).show();
	
				var html = '';
	
				for(var i = 0; i < data.length; i++)
				{
					var html = '<tr deleteable="true">';
					html += '<td width="15"><span class="del_icon" value="'+data[i]+'" onclick="deleteRule(\''+rule+'\', $(this).attr(\'value\'))"><?php echo $html->image('del.png', array('class' => '')); ?></span></td>';
					html += '<td><span style="padding-top: 10px;"><label>'+data[i]+'</label></span></td>';
					html += '</tr>';
	
					$('#list_'+rule).append(html);
				}
	
				$('#list_'+rule+' tr:even').css('background-color', '#F8F8F8');
	
				$("#load_" + rule).hide();
			}
			else
			{
				$("#show_" + rule).hide();
			}
		}
		else
		{
			$("#show_" + rule).hide();
		}
	}
	function planSubaction(i)
	{
		if ($('#subaction_' + i).val() > 0)
		{
			$('#action_details_' + i).hide();
		}
		else
		{
			$('#action_details_' + i).show();
		}
		for (j = 1; j <= 10; ++j)
		{
			if (j <= $('#subaction_' + i).val())
			{
				$('#subaction_' + i + '_' + j).show();
			}
			else
			{
				$('#subaction_' + i + '_' + j).hide();
			}
		}
	}
	</script>
	<?php
}
else
{
	?>
	<div style="overflow: hidden;">
		<form id="frm" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th width="15" removeonread="true">
                <label  class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
				<th><?php echo $paginator->sort('Plan Name', 'plan_name', array('model' => 'HealthMaintenancePlan'));?></th>
				<th width=300><?php echo $paginator->sort('Category', 'category', array('model' => 'HealthMaintenancePlan'));?></th>
				<th width=200><?php echo $paginator->sort('Status', 'status', array('model' => 'HealthMaintenancePlan'));?></th>
			</tr>

			<?php
			foreach ($HealthMaintenancePlans as $HealthMaintenancePlan):
			?>
				<tr editlink="<?php echo $html->url(array('action' => 'health_maintenance_plans', 'task' => 'edit', 'plan_id' => $HealthMaintenancePlan['HealthMaintenancePlan']['plan_id']), array('escape' => false)); ?>">
					<td class="ignore" removeonread="true">
                    <label  class="label_check_box">
                    <input name="data[HealthMaintenancePlan][plan_id][<?php echo $HealthMaintenancePlan['HealthMaintenancePlan']['plan_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $HealthMaintenancePlan['HealthMaintenancePlan']['plan_id']; ?>" />
                    </label>
                    </td>
					<td><?php echo $HealthMaintenancePlan['HealthMaintenancePlan']['plan_name']; ?></td>
					<td><?php echo $HealthMaintenancePlan['HealthMaintenancePlan']['category']; ?></td>
					<td><?php echo $HealthMaintenancePlan['HealthMaintenancePlan']['status']; ?></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width:auto; float: left;" removeonread="true">
			<div class="actions">
				<ul>
					<li><?php echo $html->link(__('Add New', true), array('action' => 'health_maintenance_plans', 'task' => 'addnew')); ?></li>
					<li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
				</ul>
			</div>
		</div>
			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'HealthMaintenancePlan', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('HealthMaintenancePlan') || $paginator->hasNext('HealthMaintenancePlan'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('HealthMaintenancePlan'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'HealthMaintenancePlan', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'HealthMaintenancePlan', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('HealthMaintenancePlan'))
					{
						echo $paginator->next('Next >>', array('model' => 'HealthMaintenancePlan', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
		</div>
	</div>

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
	</script>
	<?php
}
?>
