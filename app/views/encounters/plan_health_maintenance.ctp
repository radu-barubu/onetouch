<?php
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if(isset($HealthMaintenanceItem))
{
   extract($HealthMaintenanceItem);
}
$action_completed = isset($action_completed)?$action_completed:'';
?>
<script language="javascript" type="text/javascript">
    function updatePlanHealthMaintenance(field_id, field_val)
	{
		var diagnosis = $("#table_plans_table").attr("planname");
		var formobj = $("<form></form>");
		formobj.append('<input name="data[submitted][diagnosis]" type="hidden" value="'+diagnosis+'">');
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
	
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan_health_maintenance/encounter_id:<?php echo $encounter_id; ?>/task:edit/', formobj.serialize(), 
		function(data){}
		);
	}
		
	$(document).ready(function()
	{
		
		$("#action_date").datepicker(
        { 
            changeMonth: true,
            changeYear: true,
            showOn: 'button',
            buttonText: '',
            yearRange: 'c-90:c+10',
			onSelect: function() { updatePlanHealthMaintenance(this.id, this.value); }
        });
		
		$("#signup_date").datepicker(
        { 
            changeMonth: true,
            changeYear: true,
            showOn: 'button',
            buttonText: '',
            yearRange: 'c-90:c+10',
			onSelect: function() { updatePlanHealthMaintenance(this.id, this.value); }
        });
		
		$("#state").blur(function()
		{
			if(this.value)
			{
				updatePlanHealthMaintenance(this.id, this.value);
			}
		});
	
		$("input[name=action_completed]:radio").click(function()
		{
			if(this.value)
			{
				updatePlanHealthMaintenance(this.id, this.value);
			}
		});
		<?php echo $this->element('dragon_voice'); ?>
	});
	
</script>
<div>
      <div style="clear: both;"></div>
      <div style="text-align: left; width: 100%; margin-top: 10px; float:left">
      <form id="plan_health_maintenance_form" name="plan_health_maintenance_form" action="" method="post">
    
      <table class="form" width="100%">
         <tr>
             <td width="140">Plan Name: </td>
             <td><input type='text' name='plan_name' id='plan_name' value="<?php echo isset($plan_name)?$plan_name:''; ?>" onblur="updatePlanHealthMaintenance(this.id, this.value)"></td>
         </tr>
		 <tr>
              <td>Signup Date:</td>
              <td>
			  <?php echo $this->element("date", array('name' => 'signup_date', 'id' => 'signup_date', 'value' => isset($signup_date)?$signup_date:'', 'required' => true, 'width' => 150)); ?>
			  </td>
         </tr>
		 <tr>
              <td>Action Date: </td>
              <td>
			  <?php echo $this->element("date", array('name' => 'action_date', 'id' => 'action_date', 'value' => isset($action_date)?$action_date:'', 'required' => true, 'width' => 150)); ?>
			  </td>
         </tr>
         <tr>
             <td>Action Completed: </td> 
			 <td><input type='radio' name='action_completed' id='action_completed' value="Yes" <?php echo ($action_completed=='Yes')?'checked':''; ?> >&nbsp;Yes&nbsp;&nbsp;<input type='radio' name='action_completed' id='action_completed' value="No" <?php echo ($action_completed=='No')?'checked':''; ?> >&nbsp;No</td>
         </tr>     
         <tr>
             <td>Status: </td>
             <td>
			 <select style="width: 150px;" name="status" id="status" onchange="updatePlanHealthMaintenance(this.id, this.value)">
	         <option value="" selected="selected"></option>
			 <option value="In Progress" <?php echo ($status=='In Progress')?'selected':''; ?> >In Progress</option>
			 <option value="Completed" <?php echo ($status=='Completed')?'selected':''; ?> >Completed</option>
			 <option value="Patient Refused" <?php echo ($status=='Patient Refused')?'selected':''; ?> >Patient Refused</option>
			 <option value="On Hold" <?php echo ($status=='On Hold')?'selected':''; ?> >On Hold</option>
			 <option value="Cancelled" <?php echo ($status=='Cancelled')?'selected':''; ?> >Cancelled</option>
		     </select>
			 </td>
         </tr>   

    </table>
    </form>
</div>
</div>