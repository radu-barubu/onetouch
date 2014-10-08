<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$point_of_care_id = (isset($this->params['named']['point_of_care_id'])) ? $this->params['named']['point_of_care_id'] : "";

echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));

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
	}
		
    $(document).ready(function()
    {   
        initCurrentTabEvents('poc_meds_area');
    });  
</script>

<div id="poc_meds_area" class="tab_area" style="overflow: hidden;">
    <div class="title_area">
    	<div class="title_text">
            <a style="float: none;" class="ajax" href="<?php echo $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id)); ?>">Medications</a>
            <a style="float: none;" class="ajax active" href="<?php echo $html->url(array('action' => 'in_house_work_meds', 'patient_id' => $patient_id)); ?>">Point of Care</a>
            <a style="float: none;" class="ajax" href="<?php echo $html->url(array('action' => 'medication_list_refill', 'patient_id' => $patient_id)); ?>">Refill Summary</a>
        </div>
    </div>
    <?php
    if($task == 'addnew' || $task == 'edit')
    {
        unset($EditItem['EncounterPointOfCare']['patient_id']);
        extract($EditItem['EncounterPointOfCare']);
		
		$hours = __date("H", strtotime($drug_date_given));
		$minutes = __date("i", strtotime($drug_date_given));
        ?>
        <script language="javascript" type="text/javascript">
			$(document).ready(function()
			{
				$("#frmInHouseWorkMeds").validate(
				{
					errorElement: "div",
					submitHandler: function(form) 
					{
						$('#frmInHouseWorkMeds').css("cursor", "wait");
						
						$.post(
							'<?php echo $thisURL; ?>', 
							$('#frmInHouseWorkMeds').serialize(), 
							function(data)
							{
								showInfo("<?php echo $current_message; ?>", "notice");
								loadTab($('#frmInHouseWorkMeds'), '<?php echo $html->url(array('action' => 'in_house_work_meds', 'patient_id' => $patient_id)); ?>');
							},
							'json'
						);
					}
				});
				
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
				
				$("#drug_reason").autocomplete('<?php echo $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')); ?>', {
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
					$("#cpt_code").val(data[1]);
				});	
			});
        </script>
        <form id="frmInHouseWorkMeds" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <input type="hidden" name="data[EncounterPointOfCare][point_of_care_id]" id="point_of_care_id" value="<?php echo $point_of_care_id; ?>" />
            <table cellpadding="0" cellspacing="0" class="form" width=100%>
                <tr>
                    <td width="150"><label>Drug:</label></td>
                    <td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][drug]" id="drug" style="width:450px;" value="<?php echo isset($drug)?$drug:'' ;?>"></td>
                </tr>	
                <tr>
                    <td width="150"><label>Code:</label></td>
                    <td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][rxnorm]" id="rxnorm" style="width:225px;" value="<?php echo isset($rxnorm)?$rxnorm:'' ;?>"></td>
                </tr>
                <tr>
                    <td width="150"><label>Reason:</label></td>
                    <td><input type="text" name="data[EncounterPointOfCare][drug_reason]" id="drug_reason" value="<?php echo $drug_reason;?>" style="width:450px;" /></td>
                </tr>
                <tr>
                    <td width="150"><label>Priority:</label></td>
                    <td>
                        <select name="data[EncounterPointOfCare][drug_priority]" id="drug_priority">
                        <option value="" selected>Select Priority</option>
                        <option value="Routine" <?php echo ($drug_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                        <option value="Urgent" <?php echo ($drug_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td width="150"><label>Quantity:</label></td>
                    <td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][quantity]" id="quantity" style="width:225px;" value="<?php echo isset($quantity)?$quantity:'' ;?>"></td>
                </tr>
                <tr>
                    <td><label>Unit:</label></td>
                    <td>
                        <select name="data[EncounterPointOfCare][unit]" id="unit">
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
                        <select name="data[EncounterPointOfCare][drug_route]" id="drug_route">
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
                    <td width="150" class="top_pos"><label>Date Given:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][drug_date_given]', 'id' => 'drug_date_given', 'value' => (isset($drug_date_given) and (!strstr($drug_date_given, "0000")))?date($global_date_format, strtotime($drug_date_given)):date($global_date_format), 'required' => false)); ?></td>
                </tr>
                <tr>
                   <td width="150"><label>Time:</label></td>
                <td style="padding-right: 10px;"><input type='text' id='drug_given_time' size='4' name='data[EncounterPointOfCare][drug_given_time]' value='<?php echo "$hours:$minutes" ; ?>'> <a href="javascript:void(0)" id='exacttimebtn' onclick="showNow()"><?php echo $html->image('time.gif', array('alt' => 'Time now'));?> NOW</a>           </td>
               </tr>
                <tr>
                    <td valign='top' style="vertical-align:top"><label>Comment:</label></td>
                    <td><textarea cols="20" name="data[EncounterPointOfCare][drug_comment]" id="drug_comment" style="height:80px"><?php echo isset($drug_comment)?$drug_comment:''; ?></textarea></td>
                </tr>
                <tr>
                    <td><label>CPT:</label></td>
                    <td>
                        <input type="text" name="cpt" id="cpt" style="width:964px;" value="<?php echo isset($cpt)?$cpt:'' ;?>">
                        <input type="hidden" name="cpt_code" id="cpt_code" value="<?php echo isset($cpt_code)?$cpt_code:'' ;?>">
                    </td>
                </tr>
                <tr>
                    <td valign='top' style="vertical-align:top"><label>Fee:</label></td>
                    <td><input type="text" name="fee" id="fee" style="width:90px;" value="<?php echo isset($fee)?$fee:'' ;?>"></td>
                </tr>
                <tr height="35">
                     <td valign='top' style="vertical-align:top"><label>Ordered by:</label></td>
                     <td><?php echo $EditItem['OrderBy']['firstname']." ".$EditItem['OrderBy']['lastname'] ?></td>
                </tr>
                <tr>
                    <td width="150"><label>Status:</label></td>
                    <td>
                        <select name="data[EncounterPointOfCare][status]" id="status" style="width: 130px;">
                        <option value="" selected>Select Status</option>
                        <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                        <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
                        </select>
                    </td>
                </tr>
            </table>
        </form>
        <div class="actions">
            <ul>
                <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmInHouseWorkMeds').submit();">Save</a></li>
                <li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_meds', 'patient_id' => $patient_id)); ?>">Cancel</a></li>
            </ul>
        </div>
        <?php
    }
    else
    {
        ?>
        <div style="overflow: hidden;">
            <form id="frmInHouseWorkMeds" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table cellpadding="0" cellspacing="0" class="listing">
                    <tr>
                        <th width="15" removeonread="true"><label for="master_chk_labs" class="label_check_box_hx"><input type="checkbox" id="master_chk_labs" class="master_chk" /></label></th>
                        <th><?php echo $paginator->sort('Drug', 'drug', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Priority', 'drug_priority', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Date Given', 'drug_date_given', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Status', 'status', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
                    </tr>
                    <?php foreach ($EncounterPointOfCare as $EncounterPointOfCare): ?>
                    <tr editlinkajax="<?php echo $html->url(array('action' => 'in_house_work_meds', 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
                        <td class="ignore" removeonread="true"><label for="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" class="label_check_box_hx"><input name="data[EncounterPointOfCare][point_of_care_id][<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>]" id="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" /></label></td>
                        <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['drug']; ?></td>
                        <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['drug_priority']; ?></td>
                        <td><?php echo __date("m/d/Y", strtotime($EncounterPointOfCare['EncounterPointOfCare']['drug_date_given'])); ?></td>
                        <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['status']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </form>
            <div style="width: auto; float: left;" removeonread="true">
                <div class="actions">
                    <ul>
                        <li><a href="javascript:void(0);" onclick="deleteData('frmInHouseWorkMeds', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                    </ul>
                </div>
            </div>
            <div class="paging"> <?php echo $paginator->counter(array('model' => 'EncounterPointOfCare', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                if($paginator->hasPrev('EncounterPointOfCare') || $paginator->hasNext('EncounterPointOfCare'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
                ?>
                <?php 
                if($paginator->hasPrev('EncounterPointOfCare'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
                ?>
                <?php echo $paginator->numbers(array('model' => 'EncounterPointOfCare', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                if($paginator->hasNext('EncounterPointOfCare'))
                {
                    echo $paginator->next('Next >>', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
                ?>
            </div>
        </div>
        <?php
    }
    ?>
</div>