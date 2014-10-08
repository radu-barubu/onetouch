<?php
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if(isset($RadiologyItem))
{
   extract($RadiologyItem);
   if(!empty($reminder_notify_json))
		$notify = $reminder_notify_json;
   $notify = json_decode($notify, true);
}
$plan_radiology_id = isset($plan_radiology_id)?$plan_radiology_id:'';
$date_ordered = isset($date_ordered)?date("m/d/Y", strtotime($date_ordered)):'';
$priority = isset($priority)?$priority:'';
$laterality = isset($laterality)?$laterality:'';
$status = isset($status)?$status:'';
$reconciliated = isset($reconciliated)?$reconciliated:'';

$page_access = $this->QuickAcl->getAccessType("encounters", "plan");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>
<script language="javascript" type="text/javascript">
    function updatePlanRadiology(field_id, field_val)
	{
		var lab_facility_count = $("#lab_facility_count").val();
		var diagnosis = $("#table_plans_table").attr("planname");
		var procedure_name = $("#radiology_form_area").attr("planname");
		var formobj = $("<form></form>");
		formobj.append('<input name="diagnosis" type="hidden" value="'+diagnosis+'">');
		formobj.append('<input name="procedure_name" type="hidden" value="'+procedure_name+'">');
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
	    formobj.append('<input name="data[submitted][lab_facility_count]" type="hidden" value="'+lab_facility_count+'">');
		
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan_radiology_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', formobj.serialize(), 
		function(data){}
		);
	}
		
	$(document).ready(function()
	{
		$("#procedure_name").autocomplete(['X-Ray', 'Imaging', 'CT Scan', 'MRI', 'Ultrasonic', 'Fluoroscopy'], {
		max: 20,
		mustMatch: false,
		matchContains: false
		});
		
		$("#procedure_name").blur(function()
		{
			if(this.value)
			{
				updatePlanRadiology(this.id, this.value);
			}
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
			updatePlanRadiology("lab_address_1", data[1]);
			
			$("#lab_address_2").val(data[2]);
			updatePlanRadiology("lab_address_2", data[2]);
			
			$("#lab_city").val(data[3]);
			updatePlanRadiology("lab_city", data[3]);
			
			$("#lab_state").val(data[4]);
			updatePlanRadiology("lab_state", data[4]);
			
			$("#lab_zip_code").val(data[5]);
			updatePlanRadiology("lab_zip_code", data[5]);
			
			$("#lab_country").val(data[6]);
			updatePlanRadiology("lab_country", data[6]);
			
		});
		
		$("#date_ordered").datepicker(
		{ 
			changeMonth: true,
			changeYear: true,
			showOn: 'button',
			buttonText: '',
			yearRange: 'c-90:c+10',
			onSelect: function() { updatePlanRadiology(this.id, this.value); }
		});
		
		$("#body_site1").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
		max: 20,
		mustMatch: false,
		matchContains: false
		});		
		
		$("#body_site2").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
		max: 20,
		mustMatch: false,
		matchContains: false
		});		
		
		$("#body_site3").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
		max: 20,
		mustMatch: false,
		matchContains: false
		});		
		
		$("#body_site4").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
		max: 20,
		mustMatch: false,
		matchContains: false
		});		
		
		$("#body_site5").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
		max: 20,
		mustMatch: false,
		matchContains: false
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
			updatePlanRadiology('cpt_code', code);
        });
		
		$("#cpt").blur(function()
		{
			if(this.value)
			{
				updatePlanRadiology(this.id, this.value);
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
		    formobj.append('<input name="data[submitted][id]" type="hidden" value="radiology_result">');
		    formobj.append('<input name="data[submitted][value]" type="hidden" value="'+reviewed+'">');	
			$.post('<?php echo $this->Session->webroot; ?>encounters/plan_radiology_data/encounter_id:<?php echo $encounter_id; ?>/task:updateReview/', formobj.serialize(), 
			function(data){}
			);
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

<div style="float:left; width:100%">

<form id="plan_radiology_form" name="plan_radiology_form" action="" method="post">
   <table cellpadding="0" cellspacing="0" class="form" width="100%">
   <input type='hidden' name='plan_radiology_id' id='plan_radiology_id' value="<?php echo isset($plan_radiology_id)?$plan_radiology_id:''; ?>">
   <input type='hidden' name='icd_code' id='icd_code' value="<?php echo isset($icd_code)?$icd_code:''; ?>">	 
	<tr>
		 <td width="140">Procedure Name: </td>
		 <td>
     		 <input type='text' name='procedure_name' id='procedure_name' value="<?php echo isset($procedure_name)?$procedure_name:''; ?>" style="width:400px;"> 
		 </td>
	 </tr>
     <tr>
		 <td># of Views: </td>
		 <td>
     		 <input type='text' name='number_of_views' id='number_of_views' value="<?php echo isset($number_of_views)?$number_of_views:''; ?>" size="24" onblur="updatePlanRadiology(this.id, this.value)"> 
		 </td>
	 </tr>
	 <tr>
		<td>Reason: </td>
		<td><input type="text" name="reason" id="reason" value="<?php echo $reason;?>" style="width:400px;" onblur="updatePlanRadiology(this.id, this.value)"/></td>
	</tr>	
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
		 <select name='priority' id='priority' onchange="updatePlanRadiology(this.id, this.value);">		 
		 <option value="routine" <?php echo ($priority=='routine' or $priority=='')?'selected':''; ?>>Routine</option>
		 <option value="urgent" <?php echo ($priority=='urgent')?'selected':'' ?>>Urgent</option>
		 </select>
		 </td>
	 </tr>
     </table>
     <table cellpadding="0" cellspacing="0" class="form" width="100%">
	 <tr> 
		 <!--<td width="140">Body Site: </td>
		 <td><input type='text' name='body_site' id='body_site' value="<?php echo isset($body_site)?$body_site:''; ?>" onblur="updatePlanRadiology(this.id, this.value)"></td>-->
         <td align="left" style="padding-left:5px;">
            <div id='body_site_table_advanced' style="float:left;">
                <?php $body_site_count = isset($body_site_count)?$body_site_count:1; ?>
                <input type="hidden" name="body_site_count" id="body_site_count" value="<?php echo $body_site_count; ?>"/>
                <?php
                for ($i = 1; $i <= 5; ++$i)
                {
                    echo "<div id=\"body_site_table$i\" style=\"display:".(($i > 1 and $body_site_count < $i)?"none":"block").";\">"; 
                    
                    ?>
                    <table style="margin-bottom:0px " width="100%" border="0" > 
                        <tr height="10">
                            <td width='136' style="padding-left:0px;">Body Site #<?php echo $i ?>:</td>
                            <td>
                                <table cellpadding="0" cellspacing="0" style="margin-bottom:-5px " border="0">
                                    <tr>
                                        <td style="padding-left:0px;"><input type="text" style="width:400px;" name="body_site<?php echo $i ?>" id="body_site<?php echo $i ?>" value="<?php echo ${"body_site$i"}; ?>" onblur="updatePlanRadiology(this.id, this.value)" /></td>
                                        <td valign=middle>
                                        <?php
                                        if ($i > 0 and $i < 5)
                                        {
                                            if($body_site_count > $i)
                                            {
                                                $display = 'display: none;';
                                            }
                                            else
                                            {
                                                $display = '';
                                            }
                                            echo "<a id='body_siteadd_$i' removeonread='true' style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('body_site_table".($i + 1)."').style.display='block';jQuery('#body_site_count').val('".($i + 1)."');this.style.display='none'; document.getElementById('body_sitedelete_".($i+1)."').style.display=''; updatePlanRadiology('body_site_count', '".($i + 1)."');".($i>1?"document.getElementById('body_sitedelete_".$i."').style.display='none';":"")."\" ".($body_site_count <= $i?"":"style=\"display:none\"").">Add</a>";
                                        }
                                        
                                        if ($i > 1 and $i <= 5)
                                        {
                                            if($body_site_count > $i)
                                            {
                                                $display = 'display: none;';
                                            }
                                            else
                                            {
                                                $display = '';
                                            }
                                            echo "&nbsp;&nbsp;<a  id=\"body_sitedelete_$i\" removeonread='true' style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('body_site_table".$i."').style.display='none';jQuery('#body_site_count').val('".($i - 1)."');this.style.display='none'; updatePlanRadiology('body_site_count', '".($i - 1)."'); document.getElementById('body_siteadd_".($i-1)."').style.display='';jQuery('#body_sitedelete_".($i-1)."').css('display', '');\" ".($body_site_count <= $i?"":"style=\"display:none\"").">Delete</a>";
                                        } 
                                        ?>
                                    </td>
                                </tr>
                               
                            </table></td>
                        <td></td>
                    </tr>
                </table>
            </div>
            <?php
            } 
            ?>
        </td>
        
	 </tr>
     </table>
     <table cellpadding="0" cellspacing="0" class="form" width="100%">
	 <!--<tr>
		 <td width="140">Laterality: </td>
		 <td>
		 <select name='laterality' id='laterality' onchange="updatePlanRadiology(this.id, this.value)">
		 <option value=""></option>
		 <option value="right" <?php echo ($laterality=='right')?'selected':''; ?> >Right</option>
		 <option value="left" <?php echo ($laterality=='left')?'selected':''; ?> >Left</option>
		 <option value="bilateral" <?php echo ($laterality=='bilateral')?'selected':''; ?> >Bilateral</option>
		 <option value="not_applicable" <?php echo ($laterality=='not_applicable')?'selected':''; ?> >Not Applicable</option>
		 </select>		 
		 </td>
	 </tr>-->
	 <tr id="lab_facility_name_row" style="display: <?php echo ($LabFacilityCount == 1)?'none':'table-row'; ?>">
		 <td  width="140">Lab Facility Name: </td>
		 <td>
		 <input type='text' name='lab_facility_name' id='lab_facility_name' style="width:400px;" value="<?php echo isset($lab_facility_name)?$lab_facility_name:''; ?>" onblur="updatePlanRadiology(this.id, this.value);">
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
		 <td><textarea name='patient_instruction' id='patient_instruction' cols="20" style="height:80px;" onblur="updatePlanRadiology(this.id, this.value)"><?php echo isset($patient_instruction)?$patient_instruction:''; ?></textarea></td>
	 </tr>
	 <tr>
		 <td valign="top">Comment: </td>
		 <td><textarea name='comment' id='comment' cols="20" style="height:80px;" onblur="updatePlanRadiology(this.id, this.value)"><?php echo isset($comment)?$comment:''; ?></textarea></td>
	 </tr>
	 <tr>
		 <td valign="top"><label>Open item notification:</label> </td>
		 <td>
		 	<?php echo $this->element('order_open_item_notify', array('update_fn' => 'updatePlanRadiology', 'notify' => $notify)); ?>
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
		  <td><?php echo $html->link("Print Radiology Order", array('controller' => 'encounters', 'action' => 'print_plan_radiology', 'plan_radiology_id' => $plan_radiology_id), array('target' => '_blank', 'class' => 'btn'));  
		  echo $html->link("Fax Radiology Order", array('controller' => 'encounters', 'action' => 'print_plan_radiology', 'plan_radiology_id' => $plan_radiology_id, 'task'=>'fax'), array('target' => '_blank', 'class' => 'btn'));  
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
