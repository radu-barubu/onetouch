 <?php
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$page_access = $this->QuickAcl->getAccessType("encounters", "point_of_care");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/'; 
	if(isset($MedsItem))
	{
	  extract($MedsItem);
	 
	 } 
	 /*if(isset($MedsItem1))
	{
	  extract($MedsItem1);
	 
	 }*/
	 
	$hours = __date("H", strtotime($drug_date_given));
	$minutes = __date("i", strtotime($drug_date_given));
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
		var val=document.getElementById('drug_given_time').value=time;
		updateMedsDate();
	}
	
	function updateMedsDate()
	{
		updateMedsData('drug_date_given', $('#drug_date_given').val())
	}
	
  function updateMedsData(field_id, field_val)
	{
        var point_of_care_id = $("#point_of_care_id").val();
		$.post('<?php echo $this->Session->webroot; ?>encounters/in_house_work_meds_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
    {
      'data[submitted][id]': field_id,
      'data[submitted][value]' : field_val,
      'data[submitted][time]' : $('#drug_given_time').val(),
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
		updateMedsData('ordered_by_id', ordered_by_id);
		<?php } ?>
	    
		$("#drug").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#drug").result(function(event, data, formatted)
		{
			$("#rxnorm").val(data[1]);
		});

		$("#unit").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		$("#drug_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		
		$("#cpt").autocomplete('<?php echo $html->url(array('task' => 'load_autocomplete', 'action' => 'cpt4')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#cpt").result(function(event, data, formatted)
		{
			updateMedsData('cpt_code', data[1]);
			$("#cpt_code").val(data[1]);
		});	
		
		$("#drug_administered_by").autocomplete('<?php echo $this->Html->url(array('controller' => 'schedule', 'action' => 'provider_autocomplete')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: true,
			scrollHeight: 200
		});						
		
		<?php echo $this->element('dragon_voice'); ?>
	});
  </script>
  <div style="float:left; width:100%">
  <form id="frmInHouseWorkInjection" method="post" accept-charset="utf-8" enctype="multipart/form-data">
  <input type="hidden" name="point_of_care_id" id="point_of_care_id" style="width:450px;" value="<?php echo isset($point_of_care_id)?$point_of_care_id:'' ;?>">
  <input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="<?php echo isset($encounter_id)?$encounter_id:'' ;?>" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Meds" />
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
		<tr>
			<td width="150"><label>Drug:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][drug]" id="drug" style="width:450px; background:#eeeeee;" value="<?php echo isset($drug)?$drug:'' ;?>" readonly="readonly"></td>
		</tr>	
		<tr>
			<td width="150"><label>RxNorm/NDC:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][rxnorm]" id="rxnorm" style="width:225px;" value="<?php echo isset($rxnorm)?$rxnorm:'' ;?>" onblur="updateMedsData(this.id, this.value);"></td>
		</tr>
		<tr>
			<td width="150" style="vertical-align:top;"><label>Reason:</label></td>
			<td><div style="float:left;"><input type="text" name="data[EncounterPointOfCare][drug_reason]" id="drug_reason" value="<?php echo $drug_reason;?>" style="width:450px;" class="required" onblur="updateMedsData(this.id, this.value);" /></div></td>
		</tr>
		<tr>
			<td width="150"><label>Priority:</label></td>
			<td>
			    <select name="data[EncounterPointOfCare][drug_priority]" id="drug_priority" onchange="updateMedsData(this.id, this.value);">
			    <option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($drug_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($drug_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
			</td>
		</tr>
		<tr>
			<td width="150"><label>Quantity:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][quantity]" id="quantity" style="width:225px;" value="<?php echo isset($quantity)?$quantity:'' ;?>" onblur="updateMedsData(this.id, this.value);"></td>
		</tr>
		<tr>
            <td><label>Unit:</label></td>
            <td>
                <select name="data[EncounterPointOfCare][unit]" id="unit" onchange="updateMedsData(this.id, this.value);">
                    <option selected="selected">Select Unit</option>
		         	<?php foreach($units as $unit_item): ?>
                      <option value="<?php echo $unit_item['Unit']['description']; ?>" <?php if($unit == $unit_item['Unit']['description']) { echo 'selected="selected"'; } ?>><?php echo $unit_item['Unit']['description']; ?></option>
                    <?php endforeach; ?>   
                </select>
            </td>
        </tr>
		<tr>
            <td><label>Route:</label></td>
            <td>
                <select name="data[EncounterPointOfCare][drug_route]" id="drug_route" onchange="updateMedsData(this.id, this.value);">
                <option value="" selected>Select Route</option>
                 <?php                    
                  $taking_array = array("Injection", "Oral Intake");
                   for ($i = 0; $i < count($taking_array); ++$i)
                   {
                     echo "<option value=\"$taking_array[$i]\" ".($drug_route==$taking_array[$i]?"selected":"").">".$taking_array[$i]."</option>";
                   }
                   ?>        
                 </select>
            </td>
        </tr>
		<tr>
			<td width="150"><label>Administered By:</label></td>
			<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][drug_administered_by]" id="drug_administered_by" style="width:225px;" value="<?php echo isset($drug_administered_by) ? $drug_administered_by:'' ;?>" onblur="updateMedsData(this.id, this.value);"></td>
		</tr>
		<tr>
			<td width="150" class="top_pos"><label>Date Given:</label></td>
			<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][drug_date_given]', 'id' => 'drug_date_given', 'value' => (isset($drug_date_given) and (!strstr($drug_date_given, "0000")))?date($global_date_format, strtotime($drug_date_given)):date($global_date_format), 'required' => false)); ?></td>
		</tr>
		<tr>
		   <td width="150"><label>Time:</label></td>
		<td style="padding-right: 10px;"><input type='text' id='drug_given_time' size='5' name='drug_given_time' value='<?php 
		 echo "$hours:$minutes" ; ?>' onblur='updateMedsDate();'>  <a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a>           </td>
	   </tr>
		<tr>
			<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
			<td><textarea cols="20" name="data[EncounterPointOfCare][drug_comment]" id="drug_comment" style="height:80px" onblur="updateMedsData(this.id, this.value);"><?php echo isset($drug_comment)?$drug_comment:''; ?></textarea></td>
		</tr>
        <tr>
            <td><label>CPT:</label></td>
            <td>
                <input type="text" name="cpt" id="cpt" style="width:964px;" value="<?php echo isset($cpt)?$cpt:'' ;?>" onblur="updateMedsData(this.id, this.value);">
                <input type="hidden" name="cpt_code" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
            </td>
        </tr>
		<tr>
            <td valign='top' style="vertical-align:top"><label>Fee:</label></td>
            <td><input type="text" name="fee" id="fee" style="width:90px;" value="<?php echo isset($fee)?$fee:'' ;?>" onblur="updateMedsData(this.id, this.value);"></td>
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
			 <select name="data[EncounterPointOfCare][ordered_by_id]" id="ordered_by_id" onchange="updateMedsData(this.id, this.value);">
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
                <select name="data[EncounterPointOfCare][status]" id="status" style="width: 130px;" onchange="updateMedsData(this.id, this.value);">
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
					updateMedsData(id, value);
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
				updateMedsData(id, value);
				isDatepickerOpen = false;
			}
		});	
			
		
	});
</script>
