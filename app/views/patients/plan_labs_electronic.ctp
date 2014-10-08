<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';

$order_id = (isset($this->params['named']['order_id'])) ? $this->params['named']['order_id'] : "";

$lab_result_link = $html->url(array('controller' => 'patients', 'action' => 'lab_results', 'patient_id' => $patient_id));

if($session->read('PracticeSetting.PracticeSetting.labs_setup') == 'Electronic' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'MacPractice' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'HL7Files')
{
	$lab_result_link = $html->url(array('controller' => 'patients', 'action' => 'lab_results_electronic', 'patient_id' => $patient_id));
}

?>
<?php echo $this->Html->script(array('sections/electronic_plan_labs_init.js?'.md5(microtime()))); ?>

<script language="javascript" type="text/javascript">
	
	$(document).ready(function()
	{   
     	$('#outsideLabBtn').click(function()
		{		
            $(".tab_area").html('');
			$("#imgLoadPlan").show();
			loadTab($(this), "<?php echo $lab_result_link; ?>");
		});
		
		$('#pointofcareBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadPlan").show();
			loadTab($(this), "<?php echo $html->url(array('action' => 'in_house_work_labs', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#documentsBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'patient_documents', 'patient_id' => $patient_id)); ?>");
		});
		
		<?php if($order_id): ?>
			loadLabElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'task' => 'print_requisition', 'mrn' => $mrn, 'order_id' => $order_id)); ?>');
		<?php else: ?>
			loadLabElectronicTable('<?php echo $html->url(array('controller' => 'encounters', 'action' => 'plan_labs_electronic', 'mrn' => $mrn)); ?>');
		<?php endif; ?>
	});  
</script>
<table id="table_plans_table" icd='all'></table>
<div style="overflow: hidden; position: relative;">
    <div class="title_area">
        <div class="title_text">
            <a href="javascript:void(0);" id="pointofcareBtn"  style="float: none;">Point of Care</a>
            <span class="title_item active" style="cursor:pointer; float:none;" >Outside Labs</span>
            <a href="javascript:void(0);" id="documentsBtn" style="float:none;">Documents</a>
    	</div>
    </div>
    <span id="imgLoadPlan" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="table_plan_types" class="tab_area"></div>
</div>