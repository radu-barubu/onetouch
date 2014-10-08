<?php
$page_access = $this->QuickAcl->getAccessType("encounters", "plan");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$smoking_status = isset($smoking_status)?$smoking_status:'';
if(isset($HealthMaintenanceItem))
{
   extract($HealthMaintenanceItem);
}
$action_completed = isset($action_completed)?$action_completed:'';
$status = isset($status)?$status:'';

if(isset($PlanAdviceItem))
{
   extract($PlanAdviceItem);
}
$patient_education = isset($patient_education)?$patient_education:'';
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
	
    function updatePlanAdviceInstructions(field_id, field_val)
	{
		var diagnosis = $("#table_plans_table").attr("planname");
		var formobj = $("<form></form>");
		formobj.append('<input name="data[submitted][diagnosis]" type="hidden" value="'+diagnosis+'">');
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
	
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan_advice_instructions/encounter_id:<?php echo $encounter_id; ?>/task:edit/', formobj.serialize(), 
		function(data){}
		);
	}

	
    function updateUpToDateResult(id)
	{
		if (document.getElementById(id).value)
		{
			updatePlanAdviceInstructions(id, document.getElementById(id).value)
			document.getElementById('uptodate_iframe').style.display = 'block';
			document.getElementById('uptodate_iframe').src = 'http://www.uptodate.com/contents/search?search=' + document.getElementById(id).value;
		}
		else
		{
			updatePlanAdviceInstructions(id, '')
			document.getElementById('uptodate_iframe').style.display = 'none';
			document.getElementById('uptodate_iframe').src = '';
		}
	}

	$(document).ready(function()
	{
		
		var diagnosisList = [];
		if ($("#table_plans_table").attr("planname") == 'all') {
			diagnosisList = [];
			$('.assesssment_item').each(function(){
				diagnosisList.push($(this).attr('diagnosis_val'));
			});
			
			diagnosisList = diagnosisList.join(', ');
			$('#uptodate_result').val(diagnosisList);
			
		} else {
			$('#uptodate_result').val($("#table_plans_table").attr("planname"));
			
		}				
		
		$("input").addClear();
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
		
		$('#linkto_social_history').click(function()
        {
				 tabByHash('hx:social');
        });

		$("#medlineplus_result").autocomplete('<?php echo $this->Session->webroot; ?>encounters/education/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$('#insert_medlineplus_result').click(function()
        {
			if ($("#medlineplus_result").val())
			{
				var medline_result = $("#medlineplus_result").val().replace('[', '[ ').replace(']', ' ]'); //put a space so it's easier to parse
				
				if ($("#patient_education_comment").val())
				{
					$("#patient_education_comment").val($("#patient_education_comment").val()+'\n'+medline_result);
				}
				else
				{
					$("#patient_education_comment").val(medline_result);
				}
				$("#medlineplus_result").val('');
				updatePlanAdviceInstructions('patient_education_comment', $("#patient_education_comment").val());
			}
        });

		$('#search_uptodate_result').click(function()
        {
			if ($("#uptodate_result").val())
			{
				$("#plan_advice_instructions_form").attr("action", "<?php if ($isiPadApp) echo 'safari'; else echo 'https'; ?>://www.uptodate.com/contents/search?sp=3&source=USER_PREF&search="+$("#uptodate_result").val());
				$("#plan_advice_instructions_form").submit();
				$("#uptodate_result").val('');
			}
        });
		<?php echo $this->element('dragon_voice'); ?>
      
     $('#education-macro').macros({
       target: '#patient_education_comment'
     });       
      
	});
	
</script>
<div>
      <div style="clear: both;"></div>
      <div style="text-align: left; width: 100%; margin-top: 10px; float:left">
      <form id="plan_advice_instructions_form" name="plan_advice_instructions_form" method="post" target="_blank">
	  <table class="form" width="100%" style="display: <?php echo ($smoking_status=='empty')?'table':'none'; ?>">
	     <tr>
             <td width="165">Smoking Status: </td>
			 <td><a style="color:#FF0000; cursor:pointer;" id="linkto_social_history">Please check the smoking status of patient.</a>
			 <?php //echo $html->link('HX', array('id' => 'linkto_social_history', 'controller' => 'encounters', 'action' => 'hx_medical', 'encounter_id' => $encounter_id)); ?>
			 </td>
         </tr>
		 <tr>
             <td colspan="2">&nbsp;</td>
         </tr>
	  </table>
      <table class="form" width="100%">   
         <tr>
             <td colspan="2"><b>Patient Education or Anticipatory Guidance</b></td>
         </tr>
	     <tr>
             <td width="165">Search MedLinePlus (NIH):</td> 
			 <td><input type=text id="medlineplus_result" style="width:400px; ">&nbsp;<?php if($page_access == 'W'): ?><a class="btn" href="javascript:void(0);" style="float: none;" id="insert_medlineplus_result">Insert into Patient Education</a><?php endif; ?></td>
         </tr>
	     <tr>
             <td width="165">Search UpToDate:</td> 
			 <td><input type=text id="uptodate_result" style="width:400px; ">&nbsp;<?php if($page_access == 'W'): ?><a class="btn" href="javascript:void(0);" style="float: none;" id="search_uptodate_result">Go to UpToDate</a><?php endif; ?></td>
         </tr>
         <tr>
             <td valign="top" width="165">Patient Education & Instructions: </td>
             <td><textarea name='patient_education_comment' id='patient_education_comment' cols="20" style="height:150px;" onblur="updatePlanAdviceInstructions(this.id, this.value)"><?php echo isset($patient_education_comment)?$patient_education_comment:''; ?></textarea>
               <div id="education-macro"></div>
             
             </td>
         </tr>
    </table>
    </form>
</div>
</div>
