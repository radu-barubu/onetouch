<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$nexttab = (isset($this->params['named']['nexttab'])) ? $this->params['named']['nexttab'] : "";
//$start_checkin = (isset($this->params['named']['start_checkin'])) ? $this->params['named']['start_checkin'] : "";
$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "general_information";
$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));

if(empty($patient_checkin_id))
   $patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";

echo $this->Html->script(array('sections/tab_navigation.js'));

?>
<div class="main_content_area">
	<?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?>
</div>
<?php echo (!$patient_checkin_id)? $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 101)):''; ?>
<div id="patient_content_area" style="overflow: hidden;">
    <div id="error_message" class="notice" style="display: none;"></div>
	<div id="tabs">
		<ul>
			<?php echo (!empty($nexttab) ? '' : '<li>'.$html->link('Demographics', array('controller' => 'patients', 'action' => 'demographics', 'task' => $task, 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)).'</li>'); ?>
			<?php echo (empty($patient_checkin_id) || $nexttab == 'patient_preferences') ? '<li>'.$html->link('Patient Preferences', array('controller' => 'patients', 'action' => 'patient_preferences', 'task' => $task, 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)).'</li>': ''; ?>
			<?php echo (!empty($patient_checkin_id) ? '' : '<li>'.$html->link('Advance Directives', array('controller' => 'patients', 'action' => 'advance_directives', 'patient_id' => $patient_id)).'</li>'); ?>
			<?php echo (!empty($patient_checkin_id) ? '' : '<li>'.$html->link('Guarantor Information', array('controller' => 'patients', 'action' => 'guarantor_information', 'patient_id' => $patient_id)).'</li>'); ?>
			<?php echo (!empty($patient_checkin_id) ? '' : '<li>'.$html->link('Insurance Information', array('controller' => 'patients', 'action' => 'insurance_information', 'patient_id' => $patient_id)).'</li>'); ?>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
		MacrosArr={};
		$(function() {
			$("#tabs").tabs(
			{
				ajaxOptions: { cache: false },
				<?php
				if($task == 'addnew' && empty($patient_checkin_id))
				{
					echo 'disabled: [1,2,3,4,5],';
				}
				?>
				load: initTabEvents,
				select: function(event, ui) 
				{
					var tabID = "#ui-tabs-" + (ui.index + 1);
					$(tabID).html('<?=$smallAjaxSwirl?> <i>Loading...</i>');
				}
			});
		});
	</script>
</div>
