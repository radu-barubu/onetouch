<?php
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if(isset($ProcedureItem))
{
   extract($ProcedureItem);
   if(!empty($reminder_notify_json))
		$notify = $reminder_notify_json;
   $notify = json_decode($notify, true);
}
$plan_procedures_id = isset($plan_procedures_id)?$plan_procedures_id:'';
$laterality = isset($laterality)?$laterality:'';
$status = isset($status)?$status:'';
$date_ordered = isset($date_ordered)?date("m/d/Y", strtotime($date_ordered)):'';

$page_access = $this->QuickAcl->getAccessType("encounters", "plan");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>
<script language="javascript" type="text/javascript">
    function updatePlanProcedure(field_id, field_val)
	{
		var diagnosis = $("#table_plans_table").attr("planname");
		var test_name = $("#procedures_form_area").attr("planname");
		var formobj = $("<form></form>");
		formobj.append('<input name="diagnosis" type="hidden" value="'+diagnosis+'">');
		formobj.append('<input name="test_name" type="hidden" value="'+test_name+'">');
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
	
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan_procedures_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', formobj.serialize(), 
		function(data){}
		);
	}
		
	$(document).ready(function()
	{
		$("#date_ordered").datepicker(
		{ 
			changeMonth: true,
			changeYear: true,
			showOn: 'button',
			buttonText: '',
			yearRange: 'c-90:c+10',
			onSelect: function() { updatePlanProcedure(this.id, this.value); }
		});
		
		$("#body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
		max: 20,
		mustMatch: false,
		matchContains: false
		});
		
		$("#test_name").autocomplete(['X-Ray', 'Imaging', 'CT Scan', 'MRI', 'Ultrasonic', 'Fluoroscopy'], {
		max: 20,
		mustMatch: false,
		matchContains: false
		});
		
		$("#test_name").blur(function()
		{
			if(this.value)
			{
				updatePlanProcedure(this.id, this.value);
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
			updatePlanProcedure('cpt_code', code);
        });
		
		$("#cpt").blur(function()
		{
			if(this.value)
			{
				updatePlanProcedure(this.id, this.value);
			}
		});
		
		$("input").addClear();

                $("#reason").autocomplete('<?php echo $this->Session->webroot; ?>encounters/icd9/task:load_autocomplete/', {
                        minChars: 2,
                        max: 20,
                        mustMatch: false,
                        matchContains: false
                });  

		
		<?php echo $this->element('dragon_voice'); ?>

	});
	
</script>

<div style="float:left; width: 100%;">
<form id="plan_procedures_form" name="plan_procedures_form" action="" method="post">

<table class="form" width="100%">
<input type='hidden' name='plan_procedures_id' id='plan_procedures_id' value="<?php echo isset($plan_procedures_id)?$plan_procedures_id:''; ?>">

		 <input type='hidden' name='icd_code' id='icd_code' value="<?php echo isset($icd_code)?$icd_code:''; ?>">
	 <tr>
		 <td width="140">Procedure Name: </td>
		 <td>
		     <input type='text' name='test_name' id='test_name' value="<?php echo isset($test_name)?$test_name:''; ?>" style="width:400px;">
		 </td>
	 </tr>
	 <tr>
		<td>Reason: </td>
		<td><input type="text" name="reason" id="reason" value="<?php echo $reason;?>" style="width:400px;" onblur="updatePlanProcedure(this.id, this.value)"/></td>
	</tr>	
	<tr>
		 <td>CPT: </td>
		 <td>
		     <input type='text' name='cpt' id='cpt' value="<?php echo isset($cpt)?$cpt:''; ?>" style="width:400px;"> 
		     <input type='hidden' name='cpt_code' id='cpt_code' value="<?php echo isset($cpt_code)?$cpt_code:''; ?>">
           </td>
	 </tr>
	 <tr>
		 <td>Body Site: </td>
		 <td><input type='text' name='body_site' id='body_site' value="<?php echo isset($body_site)?$body_site:''; ?>" onblur="updatePlanProcedure(this.id, this.value)"></td>
	 </tr>
	 <tr>
		 <td>Laterality: </td>
		 <td>
		 <select name='laterality' id='laterality' onchange="updatePlanProcedure(this.id, this.value)">
		 <option value=""></option>
		 <option value="right" <?php echo ($laterality=='right')?'selected':''; ?> >Right</option>
		 <option value="left" <?php echo ($laterality=='left')?'selected':''; ?> >Left</option>
		 <option value="bilateral" <?php echo ($laterality=='bilateral')?'selected':''; ?> >Bilateral</option>
		 <option value="not_applicable" <?php echo ($laterality=='not_applicable')?'selected':''; ?> >Not Applicable</option>
		 </select>
		 
		 </td>
	 </tr>
	 <tr>
		 <td valign="top">Comment: </td>
		 <td>
		 <textarea name='comment' id='comment' cols="20" style="height:80px;" onblur="updatePlanProcedure(this.id, this.value)"><?php echo isset($comment)?$comment:''; ?></textarea>
		 </td>
	 </tr>
	 <tr>
		 <td valign="top"><label>Open item notification:</label> </td>
		 <td>
		 	<?php echo $this->element('order_open_item_notify', array('update_fn' => 'updatePlanProcedure', 'notify' => $notify)); ?>
		 </td>
	 </tr>
	 <tr id="date_ordered_row" style="display: none;">
	      <td>Date Performed:</td>
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
	  <td><?php echo $html->link("Print Procedure Order", array('controller' => 'encounters', 'action' => 'print_plan_procedures', 'plan_procedures_id' => $plan_procedures_id), array('target' => '_blank', 'class' => 'btn'));
	   echo $html->link("Fax Procedure Order", array('controller' => 'encounters', 'action' => 'print_plan_procedures', 'plan_procedures_id' => $plan_procedures_id , 'task' => 'fax'), array('target' => '_blank', 'class' => 'btn'));  
	  ?>

	   </td>
	 </tr>
</table>
</form>
</div>
