 <?php
 $thisURL = $this->Session->webroot . $this->params['url']['url'];
  $page_access = $this->QuickAcl->getAccessType("encounters", "point_of_care");
echo $this->element("enable_acl_read", array('page_access' => $page_access));
	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';
	if(isset($InjectionItem))
	{
	  extract($InjectionItem);
    }
	/*if(isset($InjectionItem1))
	{
	  extract($InjectionItem1);
    }*/
	
	$hours = __date("H", strtotime($injection_date_performed));
	$minutes = __date("i", strtotime($injection_date_performed));
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
     var val=   document.getElementById('injection_time').value=time;
        updateInjectionDate();
}
	function updateInjectionDate()
	{
		updateInjectionsData('injection_date_performed', $('#injection_date_performed').val());
	}
	
  function updateInjectionsData(field_id, field_val)
	{
        var point_of_care_id = $("#point_of_care_id").val();
		$.post('<?php echo $this->Session->webroot; ?>encounters/in_house_work_injections_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
    {
      'data[submitted][id]': field_id,
      'data[submitted][value]' : field_val,
      'data[submitted][time]' : $('#injection_time').val(),
      'point_of_care_id' : point_of_care_id
              
    }, 
		function(data){
		$('#frmInHouseWorkInjection').validate().form();
		}
		);
	}
	$(document).ready(function()
	{
	    $("#frmInHouseWorkInjection").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                $('#frmPatientMedicationList').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmInHouseWorkInjection').serialize(), 
                    function(data)
                    {
                    },
                    'json'
                );
            }
        });
	
	    $('#frmInHouseWorkInjection').validate().form();
	    <?php 
	    $total_providers=count($users);
        if($total_providers== 1)
        {?>
		var ordered_by_id = $("#ordered_by_id").val();
		updateInjectionsData('ordered_by_id', ordered_by_id);
		<?php } ?>
	
		$("#injection_name").autocomplete('<?php echo $this->Session->webroot; ?>encounters/injection_list/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});


		$("#injection_body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		
	    $("#injection_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        }); 

		$("#injection_administered_by").autocomplete('<?php echo $this->Html->url(array('controller' => 'schedule', 'action' => 'provider_autocomplete')); ?>', {
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
  <form id="frmInHouseWorkInjection" method="post" accept-charset="utf-8" enctype="multipart/form-data">
  <input type="hidden" name="point_of_care_id" id="point_of_care_id" style="width:450px;" value="<?php echo isset($point_of_care_id)?$point_of_care_id:'' ;?>">
  <input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="<?php echo isset($encounter_id)?$encounter_id:'' ;?>" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Injection" />
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
		<tr>
			<td width="150"><label>Injection:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][injection_name]" id="injection_name" style="width:450px; background:#eeeeee;" value="<?php echo isset($injection_name)?$injection_name:'' ;?>" readonly="readonly"></td>
			<input type="hidden" name="data[EncounterPointOfCare][rxnorm_code]" id="rxnorm_code" value="<?php echo isset($rxnorm_code)?$rxnorm_code:'';?>" />
		</tr>
		<tr>
			<td width="150"><label>RxNorm/NDC:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][rxnorm]" id="rxnorm" style="width:225px;" value="<?php echo isset($rxnorm)?$rxnorm:'' ;?>" onblur="updateInjectionsData(this.id, this.value);"></td>
		</tr>
		<tr>
			<td width="150" style="vertical-align:top;"><label>Reason:</label></td>
			<td><div style="float:left;"><input type="text" name="data[EncounterPointOfCare][injection_reason]" id="injection_reason" value="<?php echo $injection_reason;?>" class="required" style="width:450px;" onblur="updateInjectionsData(this.id, this.value);" /></div></td>
		</tr>
		<tr>
			<td width="150"><label>Priority:</label></td>
			<td>
				<select name="data[EncounterPointOfCare][injection_priority]" id="injection_priority" onchange="updateInjectionsData(this.id, this.value);">
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($injection_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($injection_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
			</td>
		</tr>
		<tr>
			<td width="150" class="top_pos"><label>Date Performed:</label></td>
			<td id="injection_date"><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][injection_date_performed]', 'id' => 'injection_date_performed', 'value' => (isset($injection_date_performed) and (!strstr($injection_date_performed, "0000")))?date($global_date_format, strtotime($injection_date_performed)):date($global_date_format), 'required' => false)); ?></td>
		</tr>
        <tr>
		   <td width="150"><label>Time:</label></td>
			<td style="padding-right: 10px;"><input type='text' id='injection_time' size='5' name='injection_time' value='<?php echo "$hours:$minutes" ; ?>' onblur='updateInjectionDate();'>  <a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a>           </td>
	    </tr>
		<tr>
			<td width="150"><label>Lot Number:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][injection_lot_number]" id="injection_lot_number" style="width:225px;" value="<?php echo isset($injection_lot_number)?$injection_lot_number:'' ;?>" onblur="updateInjectionsData(this.id, this.value);"></td>
		</tr>
		<tr>
			<td width="150"><label>Manufacturer:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][injection_manufacturer]" id="injection_manufacturer" style="width:225px;" value="<?php echo isset($injection_manufacturer)?$injection_manufacturer:'' ;?>" onblur="updateInjectionsData(this.id, this.value);"></td>
		</tr>
		<tr>
			<td width="150"><label>Dose:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][injection_dose]" id="injection_dose" style="width:225px;" value="<?php echo isset($injection_dose)?$injection_dose:'' ;?>" onblur="updateInjectionsData(this.id, this.value);"></td>
		</tr>
                <tr>
                        <td width="150"><label>Unit(s):</label></td>
                        <td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][injection_unit]" id="injection_unit" style="width:225px;" value="<?php echo isset($injection_unit)?$injection_unit:'' ;?>" onblur="updateInjectionsData(this.id, this.value);"></td>
                </tr>
		<tr>
			<td width="150"><label>Body Site:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][injection_body_site]" id="injection_body_site" style="width:225px;" value="<?php echo isset($injection_body_site)?$injection_body_site:'' ;?>" onblur="updateInjectionsData(this.id, this.value);"></td>
		</tr>
		<tr>
            <td><label>Route:</label></td>
            <td>
                <select name="data[EncounterPointOfCare][injection_route]" id="injection_route" onchange="updateInjectionsData(this.id, this.value);">
                <option value="" selected>Select Route</option>
                 <?php                    
                  $taking_array = array("Intradermal", "Intramuscular", "Intravenous", "Subcutaneous");
                   for ($i = 0; $i < count($taking_array); ++$i)
                   {
                     echo "<option value=\"$taking_array[$i]\" ".($injection_route==$taking_array[$i]?"selected":"").">".$taking_array[$i]."</option>";
                   }
                   ?>        
                 </select>
            </td>
        </tr>
	    <tr>
			<td width="150" class="top_pos"><label>Expiration Date:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][injection_expiration_date]', 'id' => 'injection_expiration_date', 'value' => (isset($injection_expiration_date) and (!strstr($injection_expiration_date, "0000")))?date($global_date_format, strtotime($injection_expiration_date)):'', 'required' => false)); ?></td>
	   </tr>
	   <tr>
		    <td width="150"><label>Administered by:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][injection_administered_by]" id="injection_administered_by" style="width:450px;" value="<?php echo isset($injection_administered_by)?$injection_administered_by:'' ;?>" onblur="updateInjectionsData(this.id, this.value);">            </td>
	   </tr>
	    
	   <tr>
			<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
			<td><textarea cols="20" name="data[EncounterPointOfCare][injection_comment]" id="injection_comment" style="height:80px" onblur="updateInjectionsData(this.id, this.value);"><?php echo isset($injection_comment)?$injection_comment:''; ?></textarea></td>
		</tr>
        <tr>
            <td><label>CPT:</label></td>
            <td>
                <input type="text" name="cpt" id="cpt" style="width:964px;" value="<?php echo isset($cpt)?$cpt:'' ;?>" onblur="updateInjectionsData(this.id, this.value);">
                <input type="hidden" name="cpt_code" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
            </td>
        </tr>
		<tr>
            <td valign='top' style="vertical-align:top"><label>Fee:</label></td>
            <td><input type="text" name="fee" id="fee" style="width:90px;" value="<?php echo isset($fee)?$fee:'' ;?>" onblur="updateInjectionsData(this.id, this.value);" ></td>
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
			 <select name="data[EncounterPointOfCare][ordered_by_id]" id="ordered_by_id" onchange="updateInjectionsData(this.id, this.value);">
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
                <select name="data[EncounterPointOfCare][status]" id="status" style="width: 130px;" onchange="updateInjectionsData(this.id, this.value);">
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
					updateInjectionsData(id, value);
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
				updateInjectionsData(id, value);
				isDatepickerOpen = false;
			}
		});	
			
		
	});
</script>
