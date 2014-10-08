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
	
	$(document).ready(function()
	{   
	    initCurrentTabEvents('supplies_area');
	});  
</script>

<div style="overflow: hidden;">
    <div class="title_area">
    </div>
    <div id="supplies_area" class="tab_area">
        <?php
		if($task == 'addnew' || $task == 'edit')
		{
			if($task == 'edit')
			{
				unset($EditItem['EncounterPointOfCare']['patient_id']);
				extract($EditItem['EncounterPointOfCare']);
				$id_field = '<input type="hidden" name="data[EncounterPointOfCare][point_of_care_id]" id="point_of_care_id" value="'.$point_of_care_id.'" />';
			}
			else
			{
				//Init default value here
				$id_field = "";
				$supply_name = "";
				$supply_description = "";
				$supply_quantity = "";
			}
			?>
        	<div style="overflow: hidden;">
            <form id="frmInHouseWorkSupply" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <?php
				echo $id_field.'
				<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="'.$encounter_id.'" />
				<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Supplies" />';
				?>
                <table cellpadding="0" cellspacing="0" class="form" width=100%>
					<tr>
						<td width="150" style="vertical-align:middle"><label>Supply Name:</label></td>
						<td><div style="float:left"><input type="text" name="data[EncounterPointOfCare][supply_name]" id="supply_name" value="<?php echo $supply_name;?>" style="width:450px;" class="required" /></div></td>
					<tr>
						<td valign='top' style="vertical-align:top"><label>Description:</label></td>
						<td><textarea cols="20" name="data[EncounterPointOfCare][supply_description]" id="supply_description" style="height:80px"><?php echo $supply_description; ?></textarea></td>
					</tr>
					<tr>
						<td><label>Quantity:</label></td>
						<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][supply_quantity]" id="supply_quantity" style="width:150px;" value="<?php echo $supply_quantity ;?>" class="numeric_only"> <?php echo @$supply_unit ?></td>
					</tr>
					</tr>
                </table>
            </form>
        </div>
        <div class="actions">
            <ul>
                <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmInHouseWorkSupply').submit();">Save</a></li>
                <li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_supplies', 'patient_id' => $patient_id)); ?>">Cancel</a></li>
            </ul>
        </div>
        <script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#frmInHouseWorkSupply").validate(
		{
			errorElement: "div",
			submitHandler: function(form) 
			{
				$('#frmInHouseWorkSupply').css("cursor", "wait");
				
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmInHouseWorkSupply').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmInHouseWorkSupply'), '<?php echo $html->url(array('action' => 'in_house_work_supplies', 'patient_id' => $patient_id)); ?>');
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
            <form id="frmInHouseWorkSupply" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table cellpadding="0" cellspacing="0" class="listing">
                    <tr>
                        <th width="15" removeonread="true">
                        <label for="master_chk_labs" class="label_check_box_hx">
                        <input type="checkbox" id="master_chk_labs" class="master_chk" />
                        </label>
                        </th>
                        <th><?php echo $paginator->sort('Supply Name', 'supply_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Date', 'modified_timestamp', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
                        <th width=250><?php echo $paginator->sort('Quantity', 'supply_quantity', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
                    </tr>
                    <?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
			?>
                    <tr editlinkajax="<?php echo $html->url(array('action' => 'in_house_work_supplies', 'task' => 'edit', 'patient_id' => $patient_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
                        <td class="ignore" removeonread="true">
                        <label for="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" class="label_check_box_hx">
                        <input name="data[EncounterPointOfCare][point_of_care_id][<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>]" id="child_chk<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" />
                        </label>
                        </td>
                        <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['supply_name']; ?></td>
                        <td><?php echo __date("m/d/Y", strtotime($EncounterPointOfCare['EncounterPointOfCare']['modified_timestamp'])); ?></td>
                        <td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['supply_quantity']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </form>
            <div style="width: auto; float: left;" removeonread="true">
                <div class="actions">
                    <ul>
                        <li><a class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_supplies', 'patient_id' => $patient_id, 'task' => 'addnew')); ?>">Add New</a></li>
                        <li><a href="javascript:void(0);" onclick="deleteData('frmInHouseWorkSupply', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
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
</div>
