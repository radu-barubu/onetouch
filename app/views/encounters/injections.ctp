<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';     

$page_access = $this->QuickAcl->getAccessType("encounters", "results");
echo $this->element("enable_acl_read", array('page_access' => $page_access));  

?>
<script type="text/javascript">
	$(document).ready(function()
	{   
		initCurrentTabEvents('results_tabs_area');
	});  
		function update_reviewed(obj) 
		{
			var point_of_care_id = obj.value;
			if(obj.checked==true) {
				var reviewed = 1;
			} else {
				var reviewed = 0;
			}
			$.post(
				'<?php echo $html->url(array('action' => 'results_lab', 'task' => 'save_reviewed', 'encounter_id' => $encounter_id)); ?>/point_of_care_id:'+point_of_care_id, 
				{ 'data[EncounterPointOfCare][lab_test_reviewed]': reviewed,'data[EncounterPointOfCare][point_of_care_id]': point_of_care_id  }, 
				function(data)
				{
					showInfo(data.msg, "notice");				
				},
				'json'
			);
		} 
	</script>
	<div style="overflow: hidden;">
		<form id="frmInHouseWorkProdecure" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
			<?php if(!empty($EncounterPointOfCare)) { ?>
				<th><?php echo $paginator->sort('Injection Name', 'injection_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Date', 'injection_date_performed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th style="text-align:center"><?php echo $paginator->sort('Reviewed', 'lab_test_reviewed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<?php } else { ?>
				<th>Injection Name</th>
				<th>Date</th>
				<th style="text-align:center">Reviewed</th>
				<?php } ?>
			</tr>

			<?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
				$i++;
			?>
				<tr editlinkajax="<?php echo $html->url(array('action' => 'results_injections', 'task' => 'edit', 'encounter_id' => $encounter_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>" style="cursor:pointer;">
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['injection_name']; ?></td>
					<td><?php $date_performed = $EncounterPointOfCare['EncounterPointOfCare']['injection_date_performed']; if($date_performed) echo __date($global_date_format, strtotime($date_performed)); ?></td>
					<td style="text-align:center" class="ignore"><label for="reviewed<?php echo $i;?>" class="label_check_box_hx"><input type="checkbox" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" id="reviewed<?php echo $i;?>" onclick="update_reviewed(this);" <?php if($EncounterPointOfCare['EncounterPointOfCare']['lab_test_reviewed']) echo 'checked="checked"'; ?>  /></label></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		
		<div style="width: 60%; float: right; margin-top: 15px;">
			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'EncounterPointOfCare', 'format' => __('Display %start%-%end% of %count%', true))); ?>
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