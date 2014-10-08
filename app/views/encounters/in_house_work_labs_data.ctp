<?php
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$page_access = $this->QuickAcl->getAccessType("encounters", "point_of_care");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

	$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';
	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
	if(isset($LabItem))
	{
	   extract($LabItem);
	}
	/*if(isset($LabItem1))
	{
	   extract($LabItem1);
	}*/
	
	$hours = __date("H", strtotime($lab_date_performed));
	$minutes = __date("i", strtotime($lab_date_performed));
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
		var val=document.getElementById('lab_time').value=time;
		updateLabDate();
	}
	
	function updateLabDate()
	{
		updateLabsData('lab_date_performed', $('#lab_date_performed').val())
	}
	function updateLabsData(field_id, field_val)
	{
        var point_of_care_id = $("#point_of_care_id").val();
		$.post('<?php echo $this->Session->webroot; ?>encounters/in_house_work_labs_data/encounter_id:<?php echo $encounter_id; ?>/task:edit/', 
    {
      'data[submitted][id]': field_id,
      'data[submitted][value]' : field_val,
      'data[submitted][time]' : $('#lab_time').val(),
      'point_of_care_id' : point_of_care_id
              
    }, 
		function(data){
		$('#frmInHouseWorkLab').validate().form();
		}
		);
	}
	$(document).ready(function()
	{
	
	$("#frmInHouseWorkLab").validate(
        {
            errorElement: "div",
            submitHandler: function(form) 
            {
                $('#frmPatientMedicationList').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmInHouseWorkLab').serialize(), 
                    function(data)
                    {
                    },
                    'json'
                );
            }
        });
	
	    $('#frmInHouseWorkLab').validate().form();
	    <?php 
	    $total_providers=count($users);
        if($total_providers== 1)
        {?>
		var ordered_by_id = $("#ordered_by_id").val();
		updateLabsData('ordered_by_id', ordered_by_id);
		<?php } ?>
		/*
		$("#lab_test_name").autocomplete(['Basic Med. Panel [80048]', 'CBC [85024]', 'Comp. Met. Panel [80053]', 'Drug Screen [80100]', 'Estradiol [82670]', 'Free T3 [84481]', 'Free T4 [84439]', 'Glucose [82947]', 'Hepatic Panel [80076]', 'Lipid Profile [80061]', 'Liver Profile [80076]', 'Progesterone [84144]', 'ProTime [85610]', 'PSA [84153]', 'Testosterone [84403]', 'TSH [84443]', 'UA [81002]', 'UA Culture [87088]', 'Venipuncture [36415/G0001]', 'Veni. By phys. [36410]', 'Vitamin B12 [82607]', 'Vitamin D [82306]'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#lab_specimen").autocomplete(['Urine', 'Blood', 'Feces', 'Cerebrospinal Fluid', 'Discharge'], {
			max: 20,
			mustMatch: false,
			matchContains: false
		});
		*/
		
		$("#cpt").autocomplete('<?php echo $html->url(array('task' => 'load_autocomplete', 'action' => 'cpt4')); ?>', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false
		});

		$("#cpt").result(function(event, data, formatted)
		{
			updateLabsData('cpt_code', data[1]);
			$("#cpt_code").val(data[1]);
		});
		
		$("#lab_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });

        <?php echo $this->element('dragon_voice'); ?>
	});
	</script>
	<div style="float:left; width:100%">
	<form id="frmInHouseWorkLab" method="post" accept-charset="utf-8" enctype="multipart/form-data">
		<input type="hidden" name="point_of_care_id" id="point_of_care_id" style="width:450px;" value="<?php echo isset($point_of_care_id)?$point_of_care_id:'' ;?>">
		<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="<?php echo isset($encounter_id)?$encounter_id:'' ;?>" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Labs" />
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label>Test Name:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][lab_test_name]" id="lab_test_name" style="width:450px; background:#eeeeee;" value="<?php echo isset($lab_test_name)?$lab_test_name:'' ;?>" readonly="readonly"></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
				</td>
			</tr>
                        
                        <?php if ($lab_test_type === 'Panel'): ?> 
                        <tr id="panels_row">
                            <td>&nbsp;</td>
                            <td>
                                <br />
                                <?php echo $this->element("encounter_lab_panels", compact('lab_panels')); ?>
                                <br />
                            </td>
                        </tr>                        
                        <?php endif; ?> 
                        
			<tr>
			<td width="150"><label>LOINC Code:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][lab_loinc_code]" id="lab_loinc_code" style="width:450px;" value="<?php echo isset($lab_loinc_code)?$lab_loinc_code:'' ;?>" onblur="updateLabsData(this.id, this.value);"></td>
			</tr>	
			<tr>
				<td width="150" style="vertical-align:top;"><label>Reason:</label></td>
				<td><div style="float:left;"><input type="text" name="data[EncounterPointOfCare][lab_reason]" id="lab_reason" value="<?php echo $lab_reason;?>" class="required" style="width:450px;" onblur="updateLabsData(this.id, this.value);" /></div></td>
			</tr>			
			<tr>
				<td width="150"><label>Priority:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][lab_priority]" id="lab_priority" onchange="updateLabsData(this.id, this.value);">
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($lab_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($lab_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
			</td>
			</tr>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label>Specimen:</label></td>
							<td style="padding-right: 10px;">
							<!--<input type="text" name="data[EncounterPointOfCare][lab_specimen]" id="lab_specimen" style="width:450px;" value="<?php echo isset($lab_specimen)?$lab_specimen:''; ?>" onblur="updateLabsData(this.id, this.value);">-->
							<select name="data[EncounterPointOfCare][lab_specimen]" id="lab_specimen" onchange="updateLabsData(this.id, this.value);">
					        <option selected="selected">Select Specimen</option>
		         			<?php foreach($specimen_sources as $specimen_source): ?>
                            <option value="<?php echo $specimen_source['SpecimenSource']['description']; ?>" <?php if($lab_specimen == $specimen_source['SpecimenSource']['description']) { echo 'selected="selected"'; } ?>><?php echo $specimen_source['SpecimenSource']['description']; ?></option>
                            <?php endforeach; ?>
                            </select>
							</td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td width="150" class="top_pos"><label>Date Performed:</label></td>
				<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][lab_date_performed]', 'id' => 'lab_date_performed', 'value' => (isset($lab_date_performed) and (!strstr($lab_date_performed, "0000")))?date($global_date_format, strtotime($lab_date_performed)):date($global_date_format), 'required' => false)); ?></td>
				</tr>
            <tr>
		   <td width="150"><label>Time:</label></td>
		<td style="padding-right: 10px;"><input type='text' id='lab_time' size='5' name='lab_time' value='<?php 
		 echo "$hours:$minutes" ; ?>' onblur='updateLabDate();'>  <a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a>           </td>
	   </tr>
				<tr>
					<td width="150"><label>Test Result:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][lab_test_result]" id="lab_test_result" style="width:225px;" value="<?php echo isset($lab_test_result)?$lab_test_result:''; ?>" onblur="updateLabsData(this.id, this.value);" /></td>
				</tr>
				<tr>
					<td width="150"><label>Unit:</label></td>
					<td>
					    <select name="data[EncounterPointOfCare][lab_unit]" id="lab_unit" onchange="updateLabsData(this.id, this.value);">
					        <option selected="selected">Select Unit</option>
		         			<?php foreach($units as $unit_item): ?>
                            <option value="<?php echo $unit_item['Unit']['description']; ?>" <?php if($lab_unit == $unit_item['Unit']['description']) { echo 'selected="selected"'; } ?>><?php echo $unit_item['Unit']['description']; ?></option>
                            <?php endforeach; ?>
                        </select>
					</td>
				</tr>
				<tr>
					<td width="150"><label>Normal Range:</label></td>
					<td><input type="text" name="data[EncounterPointOfCare][lab_normal_range]" id="lab_normal_range" style="width:225px;" value="<?php echo isset($lab_normal_range)?$lab_normal_range:''; ?>" onblur="updateLabsData(this.id, this.value);" /></td>
				</tr>
				<tr>
					<td width="150"><label>Abnormal:</label></td>
					<td><select name="data[EncounterPointOfCare][lab_abnormal]" id="lab_abnormal" onchange="updateLabsData(this.id, this.value);">
					<?php
					$lab_abnormal_array = array("Select Abnormal","Yes", "No", "High", "Low");
					for ($i = 0; $i < count($lab_abnormal_array); ++$i)
					{
						echo "<option value=\"$lab_abnormal_array[$i]\"".($lab_abnormal==$lab_abnormal_array[$i]?"selected":"").">".$lab_abnormal_array[$i]."</option>";
					}
					?>
					</select></td>
				</tr>
				<tr>
					<td width="150"><label>Test Result Status:</label></td>
					<td><select name="data[EncounterPointOfCare][lab_test_result_status]" id="lab_test_result_status" onchange="updateLabsData(this.id, this.value);">
					<?php
					$lab_test_result_status_array = array("Select Status","Preliminary", "Cannot be done", "Final", "Corrected", "Incompete");
					for ($i = 0; $i < count($lab_test_result_status_array); ++$i)
					{
						echo "<option value=\"$lab_test_result_status_array[$i]\"".($lab_test_result_status==$lab_test_result_status_array[$i]?"selected":"").">".$lab_test_result_status_array[$i]."</option>";
					}
					?>
					</select></td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
					<td><textarea cols="20" id="lab_comment" name="data[EncounterPointOfCare][lab_comment]" style=" height:80px" onblur="updateLabsData(this.id, this.value);"><?php echo isset($lab_comment)?$lab_comment:''; ?></textarea></td>
				</tr>
                <tr>
				    <td><label>CPT:</label></td>
					<td>
                    	<input type="text" name="cpt" id="cpt" style="width:964px;" value="<?php echo isset($cpt)?$cpt:'' ;?>" onblur="updateLabsData(this.id, this.value);">
                        <input type="hidden" name="cpt_code" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
                    </td>
				</tr>
				<tr>
				    <td valign='top' style="vertical-align:top"><label>Fee:</label></td>
					<td><input type="text" name="fee" id="fee" style="width:90px;" value="<?php echo isset($fee)?$fee:'' ;?>" onblur="updateLabsData(this.id, this.value);"></td>
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
			 <select name="data[EncounterPointOfCare][ordered_by_id]" id="ordered_by_id" onchange="updateLabsData(this.id, this.value);">
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
                        	<select name="data[EncounterPointOfCare][status]" id="status" style="width: 130px;" onchange="updateLabsData(this.id, this.value);">
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
					updateLabsData(id, value);
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
				updateLabsData(id, value);
				isDatepickerOpen = false;
			}
		});	
			
		
	});
</script>
