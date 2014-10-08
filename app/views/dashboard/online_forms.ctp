<?php

$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$system_admin_access = (($this->Session->read("UserAccount.role_id") == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)?true:false);

?>

<div style="overflow: hidden;">
	<?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?> 
	<?php 
			$links = array();
	
			if ($hasPrintableForms && empty($patient_checkin_id)) {
				$links['Printable Forms'] = 'printable_forms';
				
			}
	
			$links['Online Forms'] = $this->params['action'];
			
			
			echo $this->element('links', array('links' => $links));
	?>	
	<?php include dirname(__FILE__) . DS . $this->action . '-' . $task .'.ctp'; ?> 
</div>
