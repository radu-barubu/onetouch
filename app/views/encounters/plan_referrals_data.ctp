<?php
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if(isset($referral))
{
   extract($referral);
   if(!empty($reminder_notify_json))
		$notify = $reminder_notify_json;
   $notify = json_decode($notify, true);
}
$status = isset($status)?$status:'';
$visit_summary = isset($visit_summary)?$visit_summary:'';

$page_access = $this->QuickAcl->getAccessType("encounters", "plan");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

?>
<script language="javascript" type="text/javascript">

	function updatePlanReferral(field_id, field_val)
	{
		var diagnosis = $("#table_plans_table").attr("planname");
		var referred_to = $("#referred_to_hidden").val();

		var formobj = $("<form></form>");
		formobj.append('<input name="data[submitted][diagnosis]" type="hidden" value="'+diagnosis+'">');
		formobj.append('<input name="referred_to" type="hidden" value="'+referred_to+'">');
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
		
		
		$.post('<?php echo $this->Session->webroot; ?>encounters/plan_referrals_data/plan_referrals_id:<?php echo $referral['plan_referrals_id'];?>/encounter_id:<?php echo $encounter_id; ?>/task:edit/', formobj.serialize(), 
		function(data){
				if (field_id == "visit_summary")
				{
					resetPlan(data);
				}
			},
			'json'
		);
	}
		
	$(document).ready(function()
	{
		/*$('#plan_referral_fax_link').click( function() 
			{
				
				$.post('<?php echo Router::url(array('controller'=>'encounters','action'=>'plan_referral_fax_print'));?>/<?php echo $encounter_id;?>', 
					$('#plan_referrals_form').serialize(), 
					function(data)
					{
						
						$('#sending').html("");
						
						window.location.replace('<?php echo $html->url(array('controller' => 'messaging', 'action' => '/new_fax'));?>/plan_referral/<?php echo $referral['plan_referrals_id'];?>');
					}
				)
			}
		);
		*/	
		$("input").addClear();

                $("#diagnosis").autocomplete('<?php echo $this->Session->webroot; ?>encounters/icd9/task:load_autocomplete/', {
                        minChars: 2,
                        max: 20,
                        mustMatch: false,
                        matchContains: false
                });  
                
		$("#visit_summary").click(function()
		{
			if(this.checked == true)
			{
				updatePlanReferral(this.id, 1);
			}
			else
			{
				updatePlanReferral(this.id, 0);
			}
		});
                
           (function(){
               var xhr = null;
               
                $('.related-info-chk').click(function(evt){
                    
                    if (xhr) {
                        xhr.abort();
                    }
                    
                    xhr = $.post(
                        '<?php echo $this->Session->webroot; ?>encounters/plan_referrals_data/plan_referrals_id:<?php echo $referral['plan_referrals_id'];?>/encounter_id:<?php echo $encounter_id; ?>/task:related_information/', 
                        $('.related-info-chk').serialize(),
                        function(data){
                        xhr = null;
                    });
                    
                    
                });
               
           })();                
                

           $('#referred_by, #office_phone, #specialties, #practice_name, #diagnosis, #reason').trigger('blur');
	    <?php echo $this->element('dragon_voice'); ?>
	});
	
	
	
</script>
<div style="float:left; width:100%">
	<div class="notice">
	<?php if($refer_type == 'referred_to'): ?> 
	You are referring this patient out to the following practice/doctor: <span style="font-weight:bold"><?php echo $referred_to;?></span> 
	<?php else: ?> 
	This patient was referred to you by the following practice/doctor: <span style="font-weight:bold"><?php echo $referred_to;?></span>
	<?php endif;?>
	</div> 
	<br />
	
<form id="plan_referrals_form" name="plan_referrals_form" action="" method="post">
<input id="referred_to_hidden" type="hidden" value="<?php echo $referred_to; ?>">
<table align="left" width="100%">
	 <tr>
		 <td width="140">Specialties: </td>
		 <td><input type='text' name='specialties' id='specialties' value="<?php echo isset($specialties)?$specialties:''; ?>" onblur="updatePlanReferral(this.id, this.value);" style="width:100%" ></td>
	 </tr>
	 <tr>
		 <td>Practice Name: </td>
		 <td><input type='text' name='practice_name' id='practice_name' value="<?php echo isset($practice_name)?$practice_name:''; ?>" onblur="updatePlanReferral(this.id, this.value);" style="width:100%" ></td>
	 </tr>
     <input type='hidden' name='address1' id='address1' value="<?php echo isset($address1)?$address1:''; ?>" >
	 <input type='hidden' name='address2' id='address2' value="<?php echo isset($address2)?$address2:''; ?>" >
	 <input type='hidden' name='city' id='city' value="<?php echo isset($city)?$city:''; ?>" >
     <input type='hidden' name='state' id='state' value="<?php echo isset($state)?$state:''; ?>" >
	 <input type='hidden' name='zipcode' id='zipcode' value="<?php echo isset($zipcode)?$zipcode:''; ?>" >
	 <input type='hidden' name='country' id='country' value="<?php echo isset($country)?$country:''; ?>" >
	 <input type='hidden' name='office_phone' id='office_phone' value="<?php echo isset($office_phone)?$office_phone:''; ?>" onblur="updatePlanReferral(this.id, this.value);" >
	 <tr>
		 <td>Diagnosis: </td>
		 <td><input type="text" name='diagnosis' id='diagnosis' onchange="updatePlanReferral(this.id, this.value);" style="width:100%;" value="<?php echo isset($diagnosis)?$diagnosis:''; ?>" /></td>
	 </tr>
	 <tr>
		 <td>Reason/Comments: </td>
		 <td><textarea name='reason' id='reason' rows='5' onblur="updatePlanReferral(this.id, this.value);" style="height:85px;width:100%;"><?php echo isset($reason)?$reason:''; ?></textarea></td>
	 </tr>
<?php if($refer_type == 'referred_to'): ?>
	 <tr>
		 <td>Referred By: </td>
		 <td>
		 <?php 
		    $referred_by = isset($referred_by)?$referred_by:'';
			$physician_name = isset($physician_name)?$physician_name:'';
		 ?>		 
		   <input type='text' name='referred_by' id='referred_by' value="<?php echo ($referred_by != '')?$referred_by:$physician_name; ?>" onblur="updatePlanReferral(this.id, this.value);" style="width:100%" >
		   </td>
	 </tr>
<?php endif; ?>
	 <tr>
		 <td>Include in Summary: </td>
		 <td>
                     <?php 
												$map = array(
													'CC' => 'cc',
													'HPI' => 'hpi',
													'HX' => 'medical_history',
													'Meds & Allergy' => 'meds_allergies',
													'ROS' => 'ros',
													'PE' => 'pe',
													'Results' => 'labs_procedures',
													'POC' => 'poc',
													'Assessment' => 'assessment',
													'Plan' => 'plan',
													'Vitals' => 'vitals',	
												);
										 
												$info = array();
												foreach ($tabs as $t) {
													if (!isset($map[$t['PracticeEncounterTab']['tab']])) {
														continue;
													}
													
													$info[$map[$t['PracticeEncounterTab']['tab']]] = $t['PracticeEncounterTab']['name'];
												}
												$info['Insurance'] = "Patient's Insurance";
                     ?> 
                        <?php foreach ($info as $key => $val): ?> 
                        <label for="info-<?php echo $key ?>" class="label_check_box" style="margin: 0.25em;">
                            <input type="checkbox" id="info-<?php echo $key ?>" name="related_information[<?php echo $key; ?>]" value="1" <?php if (isset($relatedInformation[$key]) && $relatedInformation[$key]) { echo 'checked="checked"'; } ?> class="related-info-chk"  />
                            <?php echo $val ?>
                        </label>
                        <?php endforeach;?>                      
                     
                     
            <?php if($this->QuickAcl->getAccessType("messaging", "fax") == 'W'): ?>
		 	<div class="actions" style=''>
		      <a id='plan_referral_fax_link' href="<?php echo $html->url(array('controller' => 'messaging', 'action' => '/new_fax'));?>/plan_referral/<?php echo $referral['plan_referrals_id'];?>" target="_blank"><div id='send' style='float: left' class=btn>Fax Referral</div></a>
		      <a id='plan_referral_print_link' href="<?php echo $html->url(array('controller' => 'encounters', 'action' => '/plan_referrals_data'));?>/plan_referrals_id:<?php echo $referral['plan_referrals_id'];?>/encounter_id:<?php echo $encounter_id;?>/task:referral_preview/print:true" target="_blank"><div style='float: left' class=btn>Print Referral</div></a>
		    </div>
            <?php endif; ?>
		 </td>
	 </tr>
	 <?php if($refer_type == 'referred_to'): ?> 
	 <tr>
		 <td>Status: </td>
		 <td>
		 <select name='status' id='status' onchange="updatePlanReferral(this.id, this.value);">
		 <option value="Open" <?php echo ($status=='Open' or $status=='')?'selected':''; ?> >Open</option>
		 <option value="Done" <?php echo ($status=='Done')?'selected':''; ?> >Done</option>
		 </select>		 
		 </td>
	 </tr>
	 <tr>
		 <td valign="top"><label>Open item notification:</label> </td>
		 <td>
		 	<?php echo $this->element('order_open_item_notify', array('update_fn' => 'updatePlanReferral', 'notify' => $notify)); ?>
		 </td>
	 </tr>
	 <?php endif;?> 
</table>
</form>
</div>
