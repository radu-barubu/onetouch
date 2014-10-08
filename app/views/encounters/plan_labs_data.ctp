<?php

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if(isset($LabItem))
{
   extract($LabItem);
   if(!empty($reminder_notify_json))
		$notify = $reminder_notify_json;
   $notify = json_decode($notify, true);
}
$plan_labs_id = isset($plan_labs_id)?$plan_labs_id:'';
$priority = isset($priority)?$priority:'';
$status = isset($status)?$status:'';
$date_ordered = isset($date_ordered)?date("m/d/Y", strtotime($date_ordered)):'';
$reconciliated = isset($reconciliated)?$reconciliated:'';

$page_access = $this->QuickAcl->getAccessType("encounters", "plan");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>
<script language="javascript" type="text/javascript">
	function updatePlanLab(field_id, field_val)
	{
		var diagnosis = $("#table_plans_table").attr("planname");
		var test_name = $("#labs_form_area").attr("planname");
		var lab_facility_count = $("#lab_facility_count").val();
		var formobj = $("<form></form>");
		formobj.append('<input name="diagnosis" type="hidden" value="'+diagnosis+'">');
		formobj.append('<input name="test_name" type="hidden" value="'+test_name+'">');
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
		formobj.append('<input name="data[submitted][lab_facility_count]" type="hidden" value="'+lab_facility_count+'">');
	
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan_labs_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', formobj.serialize(), 
		function(data){}
		);
	}
		
	$(document).ready(function()
	{		
		$("#test_name").autocomplete('<?php echo $this->Session->webroot; ?>encounters/lab_test/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});		
		$("#lab_facility_name").autocomplete('<?php echo $this->Session->webroot; ?>encounters/plan_labs/encounter_id:<?php echo $encounter_id; ?>/task:labname_load/',        {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#lab_facility_name").result(function(event, data, formatted)
		{
			$("#lab_address_1").val(data[1]);
			updatePlanLab("lab_address_1", data[1]);
			
			$("#lab_address_2").val(data[2]);
			updatePlanLab("lab_address_2", data[2]);
			
			$("#lab_city").val(data[3]);
			updatePlanLab("lab_city", data[3]);
			
			$("#lab_state").val(data[4]);
			updatePlanLab("lab_state", data[4]);
			
			$("#lab_zip_code").val(data[5]);
			updatePlanLab("lab_zip_code", data[5]);
			
			$("#lab_country").val(data[6]);
			updatePlanLab("lab_country", data[6]);
			
		});
		
		$("#date_ordered").datepicker(
		{ 
			changeMonth: true,
			changeYear: true,
			showOn: 'button',
			buttonText: '',
			yearRange: 'c-90:c+10',
			onSelect: function() { updatePlanLab(this.id, this.value); }
		});
		
		$("#specimen").autocomplete(['Urine', 'Blood', 'Feces', 'Cerebrospinal Fluid', 'Discharge'], {
		max: 20,
		mustMatch: false,
		matchContains: false
		});
		
		$("#test_name").blur(function()
		{
			if(this.value)
			{
				updatePlanLab(this.id, this.value);
			}
		});
		
		$("#cpt").autocomplete('<?php echo $this->Session->webroot; ?>encounters/cpt4/task:load_autocomplete/',        {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
		$("#cpt").result(function(event, data, formatted)
        {
            //alert('1. '+data[0]+' 2. '+data[1]+' 3. '+data[2]);
            var code = data[0].split('[');
            var code = code[1].split(']');
            var code = code[0].split(',');
            $("#cpt_code").val(code);
			updatePlanLab('cpt_code', code);
        });
		
		$("#cpt").blur(function()
		{
			if(this.value)
			{
				updatePlanLab(this.id, this.value);
			}
		});		
	

		$("#reconciliated").click(function()
		{
			if(this.checked == true)
			{
				var reviewed = 1;
			}
			else
			{
				var reviewed = 0;
			}			
		    var formobj = $("<form></form>");
		    formobj.append('<input name="data[submitted][id]" type="hidden" value="lab_result">');
		    formobj.append('<input name="data[submitted][value]" type="hidden" value="'+reviewed+'">');	
			$.post('<?php echo $this->Session->webroot; ?>encounters/plan_labs_data/encounter_id:<?php echo $encounter_id; ?>/task:updateReview/', formobj.serialize(), 
			function(data){}
			);
		});
		<?php echo $this->element('dragon_voice'); ?>
	});
	
</script>
<style>

.plan_free_text {
	cursor: pointer;
}
.plan_free_text:hover {
	background: #e2e2e2;
}

</style>
<div style="float:left; width:100%">
<form id="plan_labs_form" name="plan_labs_form" action="" method="post">

<table align="left" width="100%">
     <input type='hidden' name='plan_labs_id' id='plan_labs_id' value="<?php echo isset($plan_labs_id)?$plan_labs_id:''; ?>">
	<input type='hidden' name='icd_code' id='icd_code' value="<?php echo isset($icd_code)?$icd_code:''; ?>">
	 <tr>
		 <td width="140" height="25">Reason/Diagnosis: </td>
		 <td>
		     <input type='text' name='reason' id='reason' value="<?php echo isset($reason) ? $reason : ''; ?>" onblur="updatePlanLab(this.id, this.value);" style="width:400px;">
		 </td>
	 </tr>	 <tr>
		 <td width="140" height="25">Test Name: </td>
		 <td>
		     <input type='text' name='test_name' id='test_name' value="<?php echo isset($test_name)?$test_name:''; ?>" style="width:400px;">
		 </td>
	 </tr>
	 <tr>
		 <td>Test Type: </td>
		 <td><input type='text' name='test_type' id='test_type' value="<?php echo isset($test_type)?$test_type:''; ?>" onblur="updatePlanLab(this.id, this.value);"></td>
	 </tr>	 
    <input type='hidden' name='loinc_code' id='loinc_code' value="<?php echo isset($loinc_code)?$loinc_code:''; ?>">
	<tr>
		 <td>CPT: </td>
		 <td>
		     <input type='text' name='cpt' id='cpt' value="<?php echo isset($cpt)?$cpt:''; ?>" style="width:400px;"> 
		     <input type='hidden' name='cpt_code' id='cpt_code' value="<?php echo isset($cpt_code)?$cpt_code:''; ?>">
		 </td>
	 </tr>
	 <tr>
		 <td>Priority: </td>
		 <td>
		 <select name='priority' id='priority' onchange="updatePlanLab(this.id, this.value);">
		 <option value="routine" <?php echo ($priority=='routine' or $priority=='')?'selected':''; ?>>Routine</option>
		 <option value="urgent" <?php echo ($priority=='urgent')?'selected':'' ?>>Urgent</option>
		 </select>
		 </td>
	 </tr>
	 <tr>
		 <td>Specimen: </td>
		 <td><input type='text' name='specimen' id='specimen' value="<?php echo isset($specimen)?$specimen:''; ?>" onblur="updatePlanLab(this.id, this.value);"></td>
	 </tr>
	 <tr id="lab_facility_name_row" style="display: <?php echo ($LabFacilityCount == 1)?'none':'table-row'; ?>">
		 <td>Lab Facility Name: </td>
		 <td><input type='text' name='lab_facility_name' id='lab_facility_name' style="width:400px;" value="<?php echo isset($lab_facility_name)?$lab_facility_name:''; ?>" onblur="updatePlanLab(this.id, this.value);">
		 </td>
	 </tr>
	 <input type='hidden' name='lab_facility_count' id='lab_facility_count' value="<?php echo isset($LabFacilityCount)?$LabFacilityCount:''; ?>" >
	 <input type='hidden' name='lab_address_1' id='lab_address_1' value="<?php echo isset($lab_address_1)?$lab_address_1:''; ?>" >
	 <input type='hidden' name='lab_address_2' id='lab_address_2' value="<?php echo isset($lab_address_2)?$lab_address_2:''; ?>"  >
	 <input type='hidden' name='lab_city' id='lab_city' value="<?php echo isset($lab_city)?$lab_city:''; ?>">
	 <input type='hidden' name='lab_state' id='lab_state' value="<?php echo isset($lab_state)?$lab_state:''; ?>" >
	 <input type='hidden' name='lab_zip_code' id='lab_zip_code' value="<?php echo isset($lab_zip_code)?$lab_zip_code:''; ?>" >
	 <input type='hidden' name='lab_country' id='lab_country' value="<?php echo isset($lab_country)?$lab_country:''; ?>" >
	 <tr>
		 <td valign="top">Patient Instruction: </td>
		 <td><textarea name='patient_instruction' id='patient_instruction' cols="20" style="height:80px;" onblur="updatePlanLab(this.id, this.value);"><?php echo isset($patient_instruction)?$patient_instruction:''; ?></textarea></td>
	 </tr>
	 <tr>
		 <td valign="top">Comment: </td>
		 <td>
		 <textarea name='comment' id='comment' cols="20" style="height:80px;" onblur="updatePlanLab(this.id, this.value)"><?php echo isset($comment)?$comment:''; ?></textarea>
		 </td>
	 </tr>
	 <tr>
		 <td valign="top"><label>Open item notification:</label> </td>
		 <td>
		 	<?php echo $this->element('order_open_item_notify', array('update_fn' => 'updatePlanLab', 'notify' => $notify)); ?>
		 </td>
	 </tr>
	 <tr id="date_ordered_row" style="display: none;">
	      <td>Date Ordered:</td>
		  <td>
		  <?php echo $this->element("date", array('name' => 'date_ordered', 'id' => 'date_ordered', 'value' => isset($date_ordered)?$date_ordered:'', 'required' => true, 'width' => 150)); ?>
		  </td>
	 </tr>
	 <tr id="status_row" style="display: none;">
		 <td>Status: </td>
		 <td><input type='radio' name='status' id='status' value="Open" <?php echo ($status=='Open')?'checked':''; ?> >&nbsp;Open&nbsp;&nbsp;<input type='radio' id='status' name='status' value="Done" <?php echo ($status=='Done')?'checked':''; ?> >&nbsp;Done</td>
	 </tr>
	 <tr>
								  <td></td>
								  <td><?php echo $html->link("Print Lab Order", array('controller' => 'encounters', 'action' => 'print_plan_labs', 'plan_labs_id' => $plan_labs_id), array('target' => '_blank', 'class' => 'btn'));
								   echo $html->link("Fax Lab Order", array('controller' => 'encounters', 'action' => 'print_plan_labs', 'plan_labs_id' => $plan_labs_id , 'task' => 'fax'), array('target' => '_blank', 'class' => 'btn'));  
								  ?>

								   </td>
								 </tr>
		<?php
		/*foreach($fields as $field_item)
		{
			echo '<tr><td colspan=2 >'.$field_item.'</td></tr>';
		}*/
		?>
</table>
</form>
</div>
