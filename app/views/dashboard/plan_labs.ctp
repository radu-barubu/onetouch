<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

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
    <?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'action' => 'in_house_work_labs')); ?>
<?php echo (empty($patient_checkin_id))? $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 107)):''; ?>
    <div class="title_area">
        <div class="title_text">
		   <?php echo $html->link('Point of Care', array('action' => 'in_house_work_labs', 'patient_id' => $patient_id)); ?>
		   <div class="title_item active">Outside Labs</div>
		</div>
    </div>
    <span id="imgLoadInhouseLab" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="lab_results_area" class="tab_area">
        <div style="overflow: hidden;">
            <form id="frmPlanLab" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <table cellpadding="0" cellspacing="0" class="listing">
                    <tr>
                        <th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'EncounterPlanLab', 'class' => 'ajax'));?></th>
                        <th><?php echo $paginator->sort('Test Name', 'test_name', array('model' => 'EncounterPlanLab', 'class' => 'ajax'));?></th>
                        <th width="150"><?php echo $paginator->sort('Priority', 'priority', array('model' => 'EncounterPlanLab', 'class' => 'ajax'));?></th>
                        <th width="150"><?php echo $paginator->sort('Date Performed', 'date_ordered', array('model' => 'EncounterPlanLab', 'class' => 'ajax'));?></th>
                        <th width="120"><?php echo $paginator->sort('Status', 'status', array('model' => 'EncounterPlanLab', 'class' => 'ajax'));?></th>
                    </tr>
                    <?php foreach ($encounter_plan_labs as $item): ?>
                    <tr editlinkajax="<?php echo $html->url(array('task' => 'edit', 'patient_id' => $patient_id, 'plan_labs_id' => $item['EncounterPlanLab']['plan_labs_id']), array('escape' => false)); ?>">
                        <td><?php echo $item['EncounterPlanLab']['diagnosis']; ?></td>
                        <td><?php echo $item['EncounterPlanLab']['test_name']; ?></td>
                        <td><?php echo ucwords($item['EncounterPlanLab']['priority']); ?></td>
                        <td><?php echo __date($global_date_format, strtotime($item['EncounterPlanLab']['date_ordered'])); ?></td>
                        <td><?php echo $item['EncounterPlanLab']['status']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </form>
            <div class="paging"> <?php echo $paginator->counter(array('model' => 'EncounterPlanLab', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
                if($paginator->hasPrev('EncounterPlanLab') || $paginator->hasNext('EncounterPlanLab'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
                ?>
                <?php 
                if($paginator->hasPrev('EncounterPlanLab'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'EncounterPlanLab', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
                ?>
                <?php echo $paginator->numbers(array('model' => 'EncounterPlanLab', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                if($paginator->hasNext('EncounterPlanLab'))
                {
                    echo $paginator->next('Next >>', array('model' => 'EncounterPlanLab', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
                ?>
            </div>
        </div>
    </div>
</div>
