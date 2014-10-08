<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$lab_result_link = $html->link('Outside Labs', array('action' => 'lab_results', 'patient_id' => $patient_id));

if($session->read('PracticeSetting.PracticeSetting.labs_setup') == 'Electronic' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'MacPractice' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'HL7Files' )
{
	$lab_result_link = $html->link('Outside Labs', array('action' => 'lab_results_electronic', 'patient_id' => $patient_id));
}
else
{
	$lab_result_link = $html->link('Outside Labs', array('action' => 'plan_labs', 'patient_id' => $patient_id));
}

$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';     

?>
<script language="javascript" type="text/javascript">
	
	$(document).ready(function()
	{   		
		$("#lab_reason").autocomplete('<?php echo $autoURL ; ?>', {
            max: 20,
			minChars: 2,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
	});  
</script>

<div style="overflow: hidden;">
    <?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id)); ?>
    <?php echo (empty($patient_checkin_id))? $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 106)):''; ?>
    <div class="title_area">
        <div class="title_text">
		   <div class="title_item active">Point of Care</div> 
		   <?php echo $lab_result_link; ?>		
		</div>
    </div>
    <span id="imgLoadInhouseLab" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="lab_results_area" class="tab_area">
        <?php
		if($task == 'addnew' || $task == 'edit')
		{
			if($task == 'edit')
			{
				extract($EditItem['EncounterPointOfCare']);
				$id_field = '<input type="hidden" name="data[EncounterPointOfCare][point_of_care_id]" id="point_of_care_id" value="'.$point_of_care_id.'" />';
			}
			else
			{
				//Init default value here
				$id_field = "";
				$lab_test_name = "";
				$lab_loinc_code = "";
				$lab_reason = "";
				$lab_priority = "";
				$lab_specimen = "";
				$cpt = "";
				$cpt_code = "";
				$comment = "";
				$date_ordered = __date("Y-m-d");
				$status = "Open";
			}
			?>
        	<div style="overflow: hidden;">
            <form id="frmInHouseWorkLab" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <?php
				echo $id_field.'
				<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="'.$encounter_id.'" />
				<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Labs" />';
				?>
                <table cellpadding="0" cellspacing="0" class="form" width=100%>
                    <tr>
                        <td colspan="2"><table cellpadding="0" cellspacing="0" class="form">
                                <tr>
                                    <td width="150"><label>Test Name:</label></td>
                                    <td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][lab_test_name]" id="lab_test_name" style="width:450px;" value="<?php echo $lab_test_name ?>"></td>
                                    <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                </tr>
                            </table></td>
                    </tr>
					<tr>
						<td width="150"><label>Reason:</label></td>
						<td><input type="text" name="data[AdministrationPointOfCare][lab_reason]" id="lab_reason" value="<?php echo $lab_reason;?>" style="width:450px;" /></td>
					</tr>
					<tr>
						<td width="150"><label>Priority:</label></td>
						<td>
						<select name="data[AdministrationPointOfCare][lab_priority]" id="lab_priority">
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
                                    <td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][lab_specimen]" id="lab_specimen" style="width:450px;" value="<?php echo $lab_specimen ?>"></td>
                                    <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <?php
					if($task == 'addnew')
					{
						echo '<input type="hidden" name="data[EncounterPointOfCare][lab_date_performed]" id="lab_date_performed" value="'.date($global_date_format).'" />';
					}
					else
					{
					?>
                    <tr>
                        <td width="150" class="top_pos"><label>Date Performed:</label></td>
                        <td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][lab_date_performed]', 'id' => 'lab_date_performed', 'value' => __date($global_date_format, strtotime($lab_date_performed)), 'required' => false)); ?></td>
                    </tr>
                    <tr>
                        <td width="150"><label>Test Result:</label></td>
                        <td><input type="text" name="data[EncounterPointOfCare][lab_test_result]" id="lab_test_result" style="width:450px;" value="<?php echo $lab_test_result; ?>" /></td>
                    </tr>
                    <tr>
                        <td width="150"><label>Unit:</label></td>
                        <td><select name="data[EncounterPointOfCare][lab_unit]" id=lab_unit>
                            </select></td>
                    </tr>
                    <tr>
                        <td width="150"><label>Normal Range:</label></td>
                        <td><input type="text" name="data[EncounterPointOfCare][lab_normal_range]" id="lab_normal_range" style="width:450px;" value="<?php echo $lab_normal_range; ?>" /></td>
                    </tr>
                    <tr>
                        <td width="150"><label>Abnormal:</label></td>
                        <td>
                        	<select name="data[EncounterPointOfCare][lab_abnormal]" id=lab_abnormal>
                                <?php
								$lab_abnormal_array = array("Yes", "No", "High", "Low");
								for ($i = 0; $i < count($lab_abnormal_array); ++$i)
								{
									echo "<option value=\"$lab_abnormal_array[$i]\"".($lab_abnormal==$lab_abnormal_array[$i]?"selected":"").">".$lab_abnormal_array[$i]."</option>";
								}
								?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td width="150"><label>Test Result Status:</label></td>
                        <td>
                        	<select name="data[EncounterPointOfCare][lab_test_result_status]" id=lab_test_result_status>
                                <?php
								$lab_test_result_status_array = array("Preliminary", "Cannot be done", "Final", "Corrected", "Incompete");
								for ($i = 0; $i < count($lab_test_result_status_array); ++$i)
								{
									echo "<option value=\"$lab_test_result_status_array[$i]\"".($lab_test_result_status==$lab_test_result_status_array[$i]?"selected":"").">".$lab_test_result_status_array[$i]."</option>";
								}
								?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td valign='top' style="vertical-align:top"><label>Comment:</label></td>
                        <td><textarea cols="20" name="data[EncounterPointOfCare][lab_comment]" rows="2" style="width:450px; height:80px"><?php echo $lab_comment ?></textarea></td>
                    </tr>
                    <?php
					}
					?>
                    <tr>
                        <td colspan="2"><table cellpadding="0" cellspacing="0" class="form">
                                <tr>
                                    <td width="150"><label>CPT:</label></td>
                                    <td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][cpt]" id="cpt" style="width:450px;" value="<?php echo $cpt ?>"></td>
                                    <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
                                </tr>
                            </table>
                            <?php echo '<input type="hidden" name="data[EncounterPointOfCare][cpt_code]" id="cpt_code" value="'.$cpt_code.'" />'; ?></td>
                    </tr>
                    <tr>
                        <td valign='top' style="vertical-align:top"><label>Comment:</label></td>
                        <td><textarea cols="20" name="data[EncounterPointOfCare][comment]"  style="height:80px"><?php echo $comment ?></textarea></td>
                    </tr>
                    <?php
					if($task == 'edit')
					{
						?>
                        <tr height=35>
                            <td valign='top' style="vertical-align:top"><label>Ordered by:</label></td>
                            <td><?php echo $EditItem['OrderBy']['firstname']." ".$EditItem['OrderBy']['lastname'] ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td width="150"><label>Status:</label></td>
                        <td>
                        	<select name="data[EncounterPointOfCare][status]" id="status" style="width: 110px;">
                                <option value="" selected>Select Status</option>
                                <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                                <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
                            </select>
                         </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="actions">
            <ul>
                <li><a href="javascript: void(0);" onclick="$('#frmInHouseWorkLab').submit();">Save</a></li>
                <li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_labs', 'patient_id' => $patient_id)); ?>">Cancel</a></li>
            </ul>
        </div>
        <script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#lab_test_name").autocomplete(['Basic Med. Panel [80048]', 'CBC [85024]', 'Comp. Met. Panel [80053]', 'Drug Screen [80100]', 'Estradiol [82670]', 'Free T3 [84481]', 'Free T4 [84439]', 'Glucose [82947]', 'Hepatic Panel [80076]', 'Lipid Profile [80061]', 'Liver Profile [80076]', 'Progesterone [84144]', 'ProTime [85610]', 'PSA [84153]', 'Testosterone [84403]', 'TSH [84443]', 'UA [81002]', 'UA Culture [87088]', 'Venipuncture [36415/G0001]', 'Veni. By phys. [36410]', 'Vitamin B12 [82607]', 'Vitamin D [82306]'], {
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#lab_specimen").autocomplete(['Urine', 'Blood', 'Feces', 'Cerebrospinal Fluid', 'Discharge'], {
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#cpt").autocomplete('<?php echo $this->Session->webroot; ?>encounters/cpt4/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#cpt").result(function(event, data, formatted)
		{
			$("#cpt_code").val(data[1]);
		});

		$("#frmInHouseWorkLab").validate(
		{
			errorElement: "div",
			errorPlacement: function(error, element) 
			{
				if(element.attr("id") == "lab_date_performed")
				{
					$("#lab_date_performed_error").append(error);
				}
				else if(element.attr("id") == "date_ordered")
				{
					$("#date_ordered_error").append(error);
				}
				else
				{
					error.insertAfter(element);
				}
			},
			submitHandler: function(form) 
			{
				$('#frmInHouseWorkLab').css("cursor", "wait");
				
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmInHouseWorkLab').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmInHouseWorkLab'), '<?php echo $html->url(array('action' => 'in_house_work_labs', 'patient_id' => $patient_id)); ?>');
					},
					'json'
				);
			}
		});
	});
	</script>
        <?php
}
else
{
	?>
        <div style="overflow: hidden;">
            <form id="frmInHouseWorkLab" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table cellpadding="0" cellspacing="0" class="listing">
                    <tr>
                        <!--<th width="15">
                        <label for="master_chk_labs" class="label_check_box_hx">
                        <input type="checkbox" id="master_chk_labs" class="master_chk" />
                        </label>
                        </th>-->
                        <th><?php echo $paginator->sort('Test Name', 'lab_test_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
						<th><?php echo $paginator->sort('Priority', 'lab_priority', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
						<th><?php echo $paginator->sort('Date Performed', 'lab_date_performed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
						<th><?php echo $paginator->sort('Test Result Status', 'lab_test_result_status', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
						<th><?php echo $paginator->sort('Status', 'status', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
                    </tr>
                    <?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
			?>
                    <tr editlinkajax="<?php echo $html->url(array('action' => 'in_house_work_labs', 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
                       <!-- <td class="ignore">
                        <label for="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" class="label_check_box_hx">
                        <input name="data[EncounterPointOfCare][point_of_care_id][<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>]" id="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" />
                        </label>
                        </td>-->
                        <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['lab_test_name']; ?></td>
						<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['lab_priority']; ?></td>
						<td><?php echo __date($global_date_format, strtotime($EncounterPointOfCare['EncounterPointOfCare']['lab_date_performed'])); ?></td>
						<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['lab_test_result_status']; ?></td>
						<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['status']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </form>
            <div style="width: 40%; float: left;">
                <div class="actions">
                    <ul>
                        <!--<li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_labs', 'patient_id' => $patient_id, 'task' => 'addnew')); ?>">Add New</a></li>-->
                        <!--<li><a href="javascript:void(0);" onclick="deleteData('frmInHouseWorkLab', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>-->
                    </ul>
                </div>
            </div>
            <div style="width: 60%; float: right; margin-top: 15px;">
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
                    <?php echo $paginator->numbers(array('model' => 'EncounterPointOfCare', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
                    <?php 
					if($paginator->hasNext('EncounterPointOfCare'))
					{
						echo $paginator->next('Next >>', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
                </div>
            </div>
        </div>
        <?php
}
?>
    </div>
</div>
