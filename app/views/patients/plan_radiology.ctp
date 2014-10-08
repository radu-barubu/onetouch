<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$addURL = $html->url(array('action' => 'plan_radiology', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'plan_radiology', 'patient_id' => $patient_id)) . '/';
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';

if($this->Session->read('last_saved_id_radiology'))
{
	$session_radiology_id = $this->Session->read('last_saved_id_radiology'); 
	$this->Session->delete('last_saved_id_radiology');	
}
echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));

?>
<script language="javascript" type="text/javascript">
	
	$(document).ready(function()
	{   
		<?php if(isset($session_radiology_id))	{?>
		
		var test_url = '<?php echo Router::url("/", true)."encounters/print_plan_radiology/plan_radiology_id:".$session_radiology_id;?>';
		window.open(test_url,'_blank');
		
		<?php }?>
	   
	    initCurrentTabEvents('plan_radiology_area');
       
	    $("#frmPlanRadiology").validate(
        {
        errorElement: "div",
		errorPlacement: function(error, element) 
		{
			if(element.attr("id") == "status_open")
			{
				$("#status_error").append(error);
			}
			else
			{
				error.insertAfter(element);
			}
		},
        submitHandler: function(form) 
        {
            $('#frmPlanRadiology').css("cursor", "wait");
			
			if($('#attachment_is_selected').val() == 'true' && $('#attachment_is_uploaded').val() == 'false')
			{
				//wait 1 second before submitting the form
				window.setTimeout("$('#frmPlanRadiology').submit();", 1000);
			}
			else
			{
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmPlanRadiology').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmPlanRadiology'), '<?php echo $mainURL; ?>');
					},
					'json'
				);
			}
        }
    });
				$("#procedure_name").autocomplete(['X-Ray', 'Imaging', 'CT Scan', 'MRI', 'Ultrasonic', 'Fluoroscopy'], 
				{
					max: 20,
					mustMatch: false,
					matchContains: false
				});
				
				$("#lab_facility_name").autocomplete('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_labs', 'task' => 'labname_load')); ?>',{
					minChars: 2,
					max: 20,
					mustMatch: false,
					matchContains: false
				});
				$('#save_print_radiology_edit').click(function()
				{			
					var id = $('#plan_radiology_id').val();						
					$("#frmPlanRadiology").submit();			
					setTimeout(function() {							
					var test_url = '<?php echo $this->Session->webroot;?>encounters/print_plan_radiology/plan_radiology_id:'+id;				
					window.open(test_url,'_blank');},2000);
				});
				$('#save_print_radiology_addnew').click(function()
				{	
					$("#frmPlanRadiology").append('<input type="hidden" id="" name="data[EncounterPlanRadiology][print_save_add]" value="1"/>');					
					$("#frmPlanRadiology").submit();
				});
				
				$("#lab_facility_name").result(function(event, data, formatted)
				{
					$("#lab_address_1").val(data[1]);
					$("#lab_address_2").val(data[2]);
					$("#lab_city").val(data[3]);
					$("#lab_state").val(data[4]);
					$("#lab_zip_code").val(data[5]);
					$("#lab_country").val(data[6]);
				});
				
				$("#body_site").autocomplete(['Head', 'Eye', 'Ear', 'Nose', 'Mouth', 'Throat', 'Neck', 'Shoulder', 'Arm', 'Hand', 'Chest', 'Breast', 'Abdomen', 'Back', 'Genital', 'Thigh', 'Leg', 'Foot'], 
				{
					max: 20,
					mustMatch: false,
					matchContains: false
				});		
				
				$("#cpt").autocomplete('<?php echo $this->Session->webroot; ?>encounters/cpt4/task:load_autocomplete/',        
				{
					minChars: 2,
					max: 20,
					mustMatch: false,
					matchContains: false
				});
				
				$("#cpt").result(function(event, data, formatted)
				{
					var code = data[0].split('[');
					var code = code[1].split(']');
					var code = code[0].split(',');
					$("#cpt_code").val(code);
				});
				
				$("#reason").autocomplete('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'icd9', 'task' => 'load_autocomplete')); ?>', {
					max: 20,
					minChars: 2,
					mustMatch: false,
					matchContains: false,
					scrollHeight: 300
				});

		
		$('#radiologyPocBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadPlanRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_radiology', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#outsideRadiologyBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadPlanRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'radiology_results', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#planRadiologyBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadPlanRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('action' => 'plan_radiology', 'patient_id' => $patient_id)); ?>");
		});
	});  
</script>

<div style="overflow: hidden;">
    <div class="title_area">
        <div class="title_text">
            <a href="javascript:void(0);" id="radiologyPocBtn"  style="float: none;">Point of Care</a>
            <a href="javascript:void(0);" id="planRadiologyBtn" style="float: none;" class="active">Outside Radiology</a>
            <a href="javascript:void(0);" id="outsideRadiologyBtn" style="float: none;">Results</a>
    	</div>
    </div>
    <span id="imgLoadPlanRadiology" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="plan_radiology_area" class="tab_area">
	<?php
    if($task == "addnew" || $task == "edit")
    {
	 if($task == "addnew")
        {
		    $id_field="";
            $procedure_name="";
			$number_of_views="";
			$reason="";
			$icd_code="";
			$cpt="";
			$cpt_code="";
			$priority="";
			$body_site_count="";
			$lab_facility_name="";
			$date_ordered = $global_date_format;            
			$lab_facility_name="";
			$patient_instruction="";
			$comment="";
            $laterality ="";
            $status ="";

        }
		else
		{
		    extract($EditItem['EncounterPlanRadiology']);
			$id_field = '<input type="hidden" name="data[PatientRadiologyResult][plan_radiology_id]" id="plan_radiology_id" value="'.$plan_radiology_id.'" />';
		 }
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";		 
    ?>
        <div style="overflow: hidden;">
        <form id="frmPlanRadiology" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
         <?php echo $id_field; ?>
            <table cellpadding="0" cellspacing="0" class="form" width=100%>
                 <tr>
                     <td width="140"><label>Procedure Name:</label></td>
                     <td>
                         <input type='text' name='data[EncounterPlanRadiology][procedure_name]' id='procedure_name' value="<?php echo isset($procedure_name)?$procedure_name:''; ?>" style="width:400px;"> 
                     </td>
                 </tr>
                 <tr>
		             <td><label># of Views:<label></td>
		             <td><input type='text' name='data[EncounterPlanRadiology][number_of_views]' id='number_of_views' value="<?php echo isset($number_of_views)?$number_of_views:''; ?>" size="24"></td>
	             </tr>
                 <tr>
                    <td><label>Reason:</label></td>
                    <td><input type="text" name="data[EncounterPlanRadiology][reason]" id="reason" value="<?php echo $reason;?>" style="width:400px;"/></td>
                 </tr>	
                <tr>
                     <td><label>CPT:</label></td>
                     <td>
                         <input type='text' name='data[EncounterPlanRadiology][cpt]' id='cpt' value="<?php echo isset($cpt)?$cpt:''; ?>" style="width:400px;"> 
                         <input type='hidden' name='data[EncounterPlanRadiology][cpt_code]' id='cpt_code' value="<?php echo isset($cpt_code)?$cpt_code:''; ?>">
                     </td>
                 </tr>
                 <tr>
                     <td><label>Priority:</label></td>
                     <td>
                         <select name='data[EncounterPlanRadiology][priority]' id='priority'>		 
                             <option value="routine" <?php echo ($priority=='routine' or $priority=='')?'selected':''; ?>>Routine</option>
                             <option value="urgent" <?php echo ($priority=='urgent')?'selected':'' ?>>Urgent</option>
                         </select>
                     </td>
                 </tr>
                 </table>
                 <table cellpadding="0" cellspacing="0" class="form" width=100%>
                    <tr>
                       <!--<td width="150"><label>Body Site:</label></td>
                       <td><input type="text" name="data[EncounterPointOfCare][radiology_body_site]" id="radiology_body_site" style="width:225px;" value="<?php echo isset($radiology_body_site)?$radiology_body_site:''; ?>" onblur="updateRadiologyData(this.id, this.value);" /></td>-->
                       <td align="left">
                       <div id='body_site_table_advanced' style="float:left;">
                            <?php $body_site_count = isset($body_site_count)?$body_site_count:1; ?>
                            <input type="hidden" name="data[EncounterPlanRadiology][body_site_count]" id="body_site_count" value="<?php echo isset($body_site_count)?$body_site_count:''; ?>"/>
                            <?php
                            for ($i = 1; $i <= 5; ++$i)
                            {
                                echo "<div id=\"body_site_table$i\" style=\"display:".(($i > 1 and $body_site_count < $i)?"none":"block").";\">"; 
                                
                                ?>
                                <table style="margin-bottom:0px " width="100%" border="0" > 
                                    <tr height="10">
                                        <td width='136'>Body Site #<?php echo $i ?>:</td>
                                        <td>
                                            <table cellpadding="0" cellspacing="0" style="margin-bottom:-5px " border="0">
                                                <tr>
                                                    <td><input type="text" style="width:400px;" name="data[EncounterPlanRadiology][body_site<?php echo $i ?>]" id="body_site<?php echo $i ?>" value="<?php echo isset(${"body_site$i"})?${"body_site$i"}:''; ?>" /></td>
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
                                                        echo "&nbsp;&nbsp;<a removeonread='true' id='body_siteadd_$i' style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('body_site_table".($i + 1)."').style.display='block';jQuery('#body_site_count').val('".($i + 1)."');this.style.display='none'; document.getElementById('body_sitedelete_".($i+1)."').style.display=''; ".($i>1?"document.getElementById('body_sitedelete_".$i."').style.display='none';":"")."\" ".($body_site_count <= $i?"":"style=\"display:none\"").">Add</a>";
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
                                                        echo "&nbsp;&nbsp;<a removeonread='true' id=\"body_sitedelete_$i\" style='float:none; ".$display."'  class='btn' onclick=\"document.getElementById('body_site_table".$i."').style.display='none';jQuery('#body_site_count').val('".($i - 1)."');this.style.display='none'; document.getElementById('body_siteadd_".($i-1)."').style.display='';jQuery('#body_sitedelete_".($i-1)."').css('display', '');\" ".($body_site_count <= $i?"":"style=\"display:none\"").">Delete</a>";
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
               <table cellpadding="0" cellspacing="0" class="form" width=100%>
                 <!--<tr>
                     <td><label>Body Site:</label></td>
                     <td><input type='text' name='data[EncounterPlanRadiology][body_site]' id='body_site' value="<?php echo isset($body_site)?$body_site:''; ?>"></td>
                 </tr>-->
                 <tr>
                     <td class="top_pos"  style="width:140px;"><label>Laterality:</label></td>
                     <td>
                         <select name='data[EncounterPlanRadiology][laterality]' id='laterality'>
                             <option value="">Select Laterality</option>
                             <option value="right" <?php echo ($laterality=='right')?'selected':''; ?> >Right</option>
                             <option value="left" <?php echo ($laterality=='left')?'selected':''; ?> >Left</option>
                             <option value="bilateral" <?php echo ($laterality=='bilateral')?'selected':''; ?> >Bilateral</option>
                             <option value="not applicable" <?php echo ($laterality=='not applicable')?'selected':''; ?> >Not Applicable</option>
                         </select>		 
                     </td>
                 </tr>
                 <tr id="lab_facility_name_row" style="display: <?php echo ($LabFacilityCount == 1)?'none':'table-row'; ?>">
                     <td width="140"><label>Lab Facility Name:</label></td>
                     <td>
                         <input type='text' name='data[EncounterPlanRadiology][lab_facility_name]' id='lab_facility_name' style="width:400px;" value="<?php echo isset($lab_facility_name)?$lab_facility_name:''; ?>">
                         <input type='hidden' name='data[EncounterPlanRadiology][lab_facility_count]' id='lab_facility_count' value="<?php echo isset($LabFacilityCount)?$LabFacilityCount:''; ?>" >
                         <input type='hidden' name='data[EncounterPlanRadiology][lab_address_1]' id='lab_address_1' value="<?php echo isset($lab_address_1)?$lab_address_1:''; ?>" >
                         <input type='hidden' name='data[EncounterPlanRadiology][lab_address_2]' id='lab_address_2' value="<?php echo isset($lab_address_2)?$lab_address_2:''; ?>"  >
                         <input type='hidden' name='data[EncounterPlanRadiology][lab_city]' id='lab_city' value="<?php echo isset($lab_city)?$lab_city:''; ?>">
                         <input type='hidden' name='data[EncounterPlanRadiology][lab_state]' id='lab_state' value="<?php echo isset($lab_state)?$lab_state:''; ?>" >
                         <input type='hidden' name='data[EncounterPlanRadiology][lab_zip_code]' id='lab_zip_code' value="<?php echo isset($lab_zip_code)?$lab_zip_code:''; ?>" >
                         <input type='hidden' name='data[EncounterPlanRadiology][lab_country]' id='lab_country' value="<?php echo isset($lab_country)?$lab_country:''; ?>" >
                     </td>
                 </tr>
                 
                 <tr>
                     <td class="top_pos"  style="width:140px;"><label>Patient Instruction:</label></td>
                     <td style="align:left"><textarea name='data[EncounterPlanRadiology][patient_instruction]' id='patient_instruction' cols="20" style="height:80px;"><?php echo isset($patient_instruction)?$patient_instruction:''; ?></textarea></td>
                 </tr>
                 <tr>
                     <td class="top_pos"  style="width:140px;"><label>Comment:</label></td>
                     <td style="align:left"><textarea name='data[EncounterPlanRadiology][comment]' id='comment' cols="20" style="height:80px;"><?php echo isset($comment)?$comment:''; ?></textarea></td>
                 </tr>
                 <tr>
                     <td class="top_pos"  style="width:140px;"><label>Status:</label></td>
                     <td>
                        <select name="data[EncounterPlanRadiology][status]" id="status" style="width: 130px;">
                        <option value="" selected>Select Status</option>
                        <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                        <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
                        </select>
                    </td>
                </tr>
            </table>
		<div class="actions">
            <ul>
				<li>
					<a href="javascript:void(0);" class="btn" id="save_print_radiology_<?php echo $task;?>">Print and Save</a>
				</li>
				<li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPlanRadiology').submit();">Save</a></li>
				<li><a class="ajax" href="<?php echo $html->url(array('patient_id' => $patient_id)); ?>">Cancel</a></li>
			</ul>
		</div>
        </form>
    <?php
}
else
{
	?>
        <form id="frmPlanRadiologyGrid" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table cellpadding="0" cellspacing="0" class="listing">
                <tr>
                    <th width="15" removeonread="true">
                    	<label for="master_chk_labs" class="label_check_box_hx"><input type="checkbox" id="master_chk_labs" class="master_chk" /></label>
                    </th>
                    <th>
                      
                      <?php if(isset($combine) && $combine): ?>
                      Diagnosis
                      <?php else:?> 
                      <?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'EncounterPlanRadiology', 'class' => 'ajax'));?>
                      <?php endif;?>
                      
                    </th>
                    <th><?php echo $paginator->sort('Procedure Name', 'procedure_name', array('model' => 'EncounterPlanRadiology', 'class' => 'ajax'));?></th>
                    <th width="150"><?php echo $paginator->sort('Priority', 'priority', array('model' => 'EncounterPlanRadiology', 'class' => 'ajax'));?></th>
                    <th width="150"><?php echo $paginator->sort('Laterality', 'laterality', array('model' => 'EncounterPlanRadiology', 'class' => 'ajax'));?></th>
                    <th width="150"><?php echo $paginator->sort('Date Performed', 'date_ordered', array('model' => 'EncounterPlanRadiology', 'class' => 'ajax'));?></th>
                    <th width="120"><?php echo $paginator->sort('Status', 'status', array('model' => 'EncounterPlanRadiology', 'class' => 'ajax'));?></th>
                </tr>
                <?php foreach ($encounter_plan_radiology as $item): ?>
                <tr editlinkajax="<?php echo $html->url(array('task' => 'edit', 'patient_id' => $patient_id, 'plan_radiology_id' => $item['EncounterPlanRadiology']['plan_radiology_id']), array('escape' => false)); ?>">
                    <td class="ignore" removeonread="true">
                        <label for="child_chk<?php echo $item['EncounterPlanRadiology']['plan_radiology_id']; ?>" class="label_check_box_hx">
                        <input name="data[EncounterPlanRadiology][plan_radiology_id][<?php echo $item['EncounterPlanRadiology']['plan_radiology_id']; ?>]" id="child_chk<?php echo $item['EncounterPlanRadiology']['plan_radiology_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $item['EncounterPlanRadiology']['plan_radiology_id']; ?>" />
                        </label>
                    </td>
                    <td><?php 
                    
                      if (isset($combine) && $combine) {
                        
                        $diagnosis = Set::extract('/EncounterAssessment/diagnosis', $item['EncounterMaster']);
                        
                        foreach($diagnosis as $index => $val) {
                          if ($val == $item['EncounterPlanRadiology']['diagnosis']) {
                            unset($diagnosis[$index]);
                          }
                        }
                        
                        $diagnosis = array_merge(array($item['EncounterPlanRadiology']['diagnosis']), $diagnosis);
                        
                        $item['EncounterPlanRadiology']['diagnosis'] = implode(', ', $diagnosis);
                      }                    
                    
                    
                    echo $item['EncounterPlanRadiology']['diagnosis']; ?></td>
                    <td><?php echo $item['EncounterPlanRadiology']['procedure_name']; ?></td>
                    <td><?php echo ucwords($item['EncounterPlanRadiology']['priority']); ?></td>
                    <td><?php echo ucwords($item['EncounterPlanRadiology']['laterality']); ?></td>
                    <td><?php echo __date($global_date_format, strtotime($item['EncounterPlanRadiology']['date_ordered'])); ?></td>
                    <td><?php echo $item['EncounterPlanRadiology']['status']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </form>
        <div style="width: auto; float: left;" removeonread="true">
            <div class="actions">
                <ul>
				    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                    <li><a href="javascript:void(0);" onclick="deleteData('frmPlanRadiologyGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                </ul>
            </div>
        </div>
        <div class="paging"> <?php echo $paginator->counter(array('model' => 'EncounterPlanRadiology', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
            if($paginator->hasPrev('EncounterPlanRadiology') || $paginator->hasNext('EncounterPlanRadiology'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
            ?>
            <?php 
            if($paginator->hasPrev('EncounterPlanRadiology'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'EncounterPlanRadiology', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
            ?>
            <?php echo $paginator->numbers(array('model' => 'EncounterPlanRadiology', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
            if($paginator->hasNext('EncounterPlanRadiology'))
            {
                echo $paginator->next('Next >>', array('model' => 'EncounterPlanRadiology', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        </div>
    <?php
}
?>
    </div>
</div>
