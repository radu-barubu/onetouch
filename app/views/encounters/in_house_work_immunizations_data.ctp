 <?php
 $thisURL = $this->Session->webroot . $this->params['url']['url'];
 $page_access = $this->QuickAcl->getAccessType("encounters", "point_of_care");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';
	if(isset($ImmunizationItem))
	{
	  extract($ImmunizationItem);
    }
	/*if(isset($ImmunizationItem1))
	{
	  extract($ImmunizationItem1);
    }*/
	
	$hours = __date("H", strtotime($vaccine_date_performed));
	$minutes = __date("i", strtotime($vaccine_date_performed));
	
  ?>
  <script language="javascript" type="text/javascript">
	function showNow()
	{
		var currentTime = new Date();
		var hours = currentTime.getHours();
		var minutes = currentTime.getMinutes();
		
		if (minutes < 10)
			minutes = "0" + minutes;
		
		var time = hours + ":" + minutes ;
		var val = document.getElementById('vaccine_time').value=time;
		updateImmunizationDate();
	}
	
	function updateImmunizationDate()
	{
		updateImmunizationsData('vaccine_date_performed', $('#vaccine_date_performed').val())
	}
	
  	function updateImmunizationsData(field_id, field_val)
	{
        var point_of_care_id = $("#point_of_care_id").val();
		$.post('<?php echo $this->Session->webroot; ?>encounters/in_house_work_immunizations_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
    {
      'data[submitted][id]': field_id,
      'data[submitted][value]' : field_val,
      'data[submitted][time]' : $('#vaccine_time').val(),
      'point_of_care_id' : point_of_care_id
              
    }, 
		function(data){
			$('#frmInHouseWorkImmunization').validate().form();
		}
		);
	}
	$(document).ready(function()
	{
	    $("#frmInHouseWorkImmunization").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                $('#frmPatientMedicationList').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmInHouseWorkImmunization').serialize(), 
                    function(data)
                    {
                    },
                    'json'
                );
            }
        });
	
	     $('#frmInHouseWorkImmunization').validate().form();
	
	    <?php 
	    $total_providers=count($users);
        if($total_providers== 1)
        {?>
		var ordered_by_id = $("#ordered_by_id").val();
		updateImmunizationsData('ordered_by_id', ordered_by_id);
		<?php } ?>
		
	     var vaccine_name_cvx = $("#vaccine_name").val();
		 var mySplitResult = vaccine_name_cvx.split("[");
		  mySplitResult = mySplitResult[1].split("]");
		  mySplitResult = mySplitResult[0].split("]");
		 $("#cvx_code").val(mySplitResult);
		 updateImmunizationsData("cvx_code", $("#cvx_code").val());
		//updateImmunizationsData('cpt_code', data[1]);
		
		
		$("#vaccine_name").autocomplete('<?php echo $this->Session->webroot; ?>encounters/vaccine_list/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#vaccine_body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		$("#vaccine_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });

		$("#vaccine_administered_by").autocomplete('<?php echo $this->Html->url(array('controller' => 'schedule', 'action' => 'provider_autocomplete')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: true,
			scrollHeight: 200
		});				
		
		$("#cpt").autocomplete('<?php echo $html->url(array('task' => 'load_autocomplete', 'action' => 'cpt4')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#cpt").result(function(event, data, formatted)
		{
			updateImmunizationsData('cpt_code', data[1]);
			$("#cpt_code").val(data[1]);
		});
		
		<?php echo $this->element('dragon_voice'); ?>

     });
  </script>
  <div style="float:left; width:100%">
  <form id="frmInHouseWorkImmunization" method="post" accept-charset="utf-8" enctype="multipart/form-data">
  <input type="hidden" name="point_of_care_id" id="point_of_care_id" style="width:450px;" value="<?php echo isset($point_of_care_id)?$point_of_care_id:'' ;?>">
  <input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="<?php echo isset($encounter_id)?$encounter_id:'' ;?>" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Immunization" />
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
		<tr>
			<td width="150"><label>Vaccine Name:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_name]" id="vaccine_name" style="width:450px; background:#eeeeee;" value="<?php echo isset($vaccine_name)?$vaccine_name:'' ;?>" readonly="readonly">
			<input type="hidden" name="data[EncounterPointOfCare][cvx_code]" id="cvx_code" value="<?php echo isset($cvx_code)?$cvx_code:''; ?>" />
			</td>
		 </tr>
		 <tr>
			 <td width="150" style="vertical-align:top;"><label>Reason:</label></td>
			 <td><div style="float:left;"><input type="text" name="data[EncounterPointOfCare][vaccine_reason]" id="vaccine_reason" class="required" value="<?php echo $vaccine_reason;?>" style="width:450px;" class="required" onblur="updateImmunizationsData(this.id, this.value);" /></div></td>
		</tr>
		<tr>
			<td width="150"><label>Priority:</label></td>
			<td>
				<select name="data[EncounterPointOfCare][vaccine_priority]" id="vaccine_priority" onblur="updateImmunizationsData(this.id, this.value);" >
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($vaccine_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($vaccine_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
			</td>
		</tr>
		<tr>
		   <input type="hidden" name="data[EncounterPointOfCare][rxnorm_code]" id="rxnorm_code" value="<?php echo isset($rxnorm_code)?$rxnorm_code:'';?>" />
		   <input type="hidden" name="data[EncounterPointOfCare][immtrack_vac_code]" id="immtrack_vac_code" value="<?php echo isset($immtrack_vac_code)?$immtrack_vac_code:'';?>" />
	    </tr>
		<tr>
			<td width="150" class="top_pos"><label>Date Performed:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][vaccine_date_performed]', 'id' => 'vaccine_date_performed', 'value' => (isset($vaccine_date_performed) and (!strstr($vaccine_date_performed, "0000")))?date($global_date_format, strtotime($vaccine_date_performed)):date($global_date_format), 'required' => false)); ?></td>
		</tr>
        <tr>
		   <td width="150"><label>Time:</label></td>
		<td style="padding-right: 10px;"><input type='text' id='vaccine_time' size='5' name='data[EncounterPointOfCare][vaccine_date_performed]' value='<?php echo "$hours:$minutes" ; ?>' onblur='updateImmunizationDate();'>  <a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a>           </td>
	   </tr>
		<tr>
		  <td width="150"><label>Lot Number:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_lot_number]" id="vaccine_lot_number" style="width:225px;" value="<?php echo isset($vaccine_lot_number)?$vaccine_lot_number:'' ;?>" onblur="updateImmunizationsData(this.id, this.value);"></td>
		</tr>
		<tr>
			<td width="150"><label>Manufacturer:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_manufacturer]" id="vaccine_manufacturer" style="width:225px;" value="<?php echo isset($vaccine_manufacturer)?$vaccine_manufacturer:'' ;?>" onblur="updateImmunizationsData(this.id, this.value);"></td>
		</tr>
		<tr>
			<td width="150"><label>Manufacturer Code:</label></td>
			<td><input type="text" name="data[EncounterPointOfCare][manufacturer_code]" id="manufacturer_code" value="<?php echo $manufacturer_code;?>" style="width:225px;" onblur="updateImmunizationsData(this.id, this.value);" /></td>
		</tr>
		<tr>
			<td width="150"><label>Dose:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_dose]" id="vaccine_dose" style="width:225px;" value="<?php echo isset($vaccine_dose)?$vaccine_dose:'' ;?>" onblur="updateImmunizationsData(this.id, this.value);"></td>
		</tr>
		<tr>
			<td width="150"><label>Unit:</label></td>
			<td><input type="text" name="data[EncounterPointOfCare][administered_units]" id="administered_units" value="<?php echo $administered_units;?>" style="width:225px;" onblur="updateImmunizationsData(this.id, this.value);" /></td>
		</tr>
		<tr>
			<td width="150"><label>Body Site:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_body_site]" id="vaccine_body_site" style="width:225px;" value="<?php echo isset($vaccine_body_site)?$vaccine_body_site:'' ;?>" onblur="updateImmunizationsData(this.id, this.value);"></td>
		</tr>
		<tr>
            <td><label>Route:</label></td>
            <td>
                <select name="data[PatientMedicationList][vaccine_route]" id="vaccine_route" onchange="updateImmunizationsData(this.id, this.value);">
                <option value="" selected>Select Route</option>
                 <?php                    
                  $taking_array = array("Intradermal", "Intramuscular", "Subcutaneous");
                   for ($i = 0; $i < count($taking_array); ++$i)
                   {
                     echo "<option value=\"$taking_array[$i]\" ".($vaccine_route==$taking_array[$i]?"selected":"").">".$taking_array[$i]."</option>";
                   }
                   ?>        
                 </select>
            </td>
        </tr>
	    <tr>
			<td width="150" class="top_pos"><label>Expiration Date:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][vaccine_expiration_date]', 'id' => 'vaccine_expiration_date', 'value' => (isset($vaccine_expiration_date) and (!strstr($vaccine_expiration_date, "0000")))?date($global_date_format, strtotime($vaccine_expiration_date)):'', 'required' => false)); ?></td>
	   </tr>
	   <tr>
		    <td width="150"><label>Administered by:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][vaccine_administered_by]" id="vaccine_administered_by" style="width:450px;" value="<?php echo isset($vaccine_administered_by)?$vaccine_administered_by:'' ;?>" onblur="updateImmunizationsData(this.id, this.value);">            </td>
	   </tr>
	   <tr>
			<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
			<td><textarea cols="20" name="data[EncounterPointOfCare][vaccine_comment]" id="vaccine_comment" style="height:80px" onblur="updateImmunizationsData(this.id, this.value);"><?php echo isset($vaccine_comment)?$vaccine_comment:''; ?></textarea></td>
		</tr>
        <tr>
            <td><label>CPT:</label></td>
            <td>
                <input type="text" name="cpt" id="cpt" style="width:964px;" value="<?php echo isset($cpt)?$cpt:'' ;?>" onblur="updateImmunizationsData(this.id, this.value);">
                <input type="hidden" name="cpt_code" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
            </td>
        </tr>
		<tr>
            <td valign='top' style="vertical-align:top"><label>Fee:</label></td>
            <td><input type="text" name="fee" id="fee" style="width:90px;" value="<?php echo isset($fee)?$fee:'' ;?>"onblur="updateImmunizationsData(this.id, this.value);"></td>
        </tr>
		
		<?php 
				      $total_providers=count($users);
                      if($total_providers== 1)
                      {?>
        <tr height="35">
             <td valign='top' style="vertical-align:top"><label>Ordered by:</label></td>
             <td>
			     
					   <input type="hidden" id="ordered_by_id" name="data[EncounterPointOfCare][ordered_by_id]" value="<?php echo $users[0]['UserAccount']['user_id']; ?>" />
                       <?php echo $users[0]['UserAccount']['firstname']. ' '. $users[0]['UserAccount']['lastname']; ?>
					 
					  </td></tr>
			<?php	 } 	 else  
					 {
					   ?>
			 <tr>
             <td><label>Ordered by:</label></td>
             <td>		   
			 <select name="data[EncounterPointOfCare][ordered_by_id]" id="ordered_by_id" onchange="updateImmunizationsData(this.id, this.value);">
                        <option value="" selected>Select Provider</option>
                         <?php foreach($users as $user): 
						   $provider_id = $user['UserAccount']['user_id'];
						   $provider_name = $user['UserAccount']['firstname'].' '.$user['UserAccount']['lastname'];
						 ?>
                            <option value="<?php echo $provider_id; ?>" <?php if($ordered_by_id==$provider_id) { echo 'selected'; }?>><?php echo $provider_name; ?></option>
                            <?php endforeach; ?>
                        </select>
					
			 </td>
        </tr>
		<?php }
		?>	
        <tr>
            <td width="150"><label>Status:</label></td>
            <td>
                <select name="data[EncounterPointOfCare][status]" id="status" style="width: 130px;" onchange="updateImmunizationsData(this.id, this.value);">
                <option value="" selected>Select Status</option>
                <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
                </select>
            </td>
        </tr>
</table>
</form>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
<script>
	$(function(){
		var isDatepickerOpen = false;
		
		$('.hasDatepicker')
			.unbind('blur.injection')
			.bind('blur.injection',function(){
				var 
					id = $(this).attr('id'),
					value = $(this).val()
				;
				
				if (!isDatepickerOpen) {
					updateImmunizationsData(id, value);
				}

			});
			
		$('.hasDatepicker').datepicker('option', {
			beforeShow: function(){
				isDatepickerOpen = true;
			},
			onClose: function(){
				var 
					id = $(this).attr('id'),
					value = $(this).val()
				;
				updateImmunizationsData(id, value);
				isDatepickerOpen = false;
			}
		});	
			
		
	});
</script>