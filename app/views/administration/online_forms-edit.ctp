<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
	<h2>Edit Form Template</h2>
	
	<?php echo $this->Form->create('FormTemplate', 
		array(
			'url' => array(
				'controller' => 'administration',
				'action' => 'online_forms',
				'task' => 'edit',
				'template_id' => $this->data['FormTemplate']['template_id']
			))) ?>
		
	
	<?php echo $this->Form->input('template_name'); ?> 
		
	<?php echo $this->Form->input('template_content', array(
		'label' => 'Template Body',
		'type' => 'textarea',
		'style' => 'max-height: 300px'
	));?> 
	<div class="input">
		<label>Dashboard Access</label>
		<br />
		<label for="access-clinical" class="label_check_box"><input type="checkbox" name="access[clinical]" value="1" id="access-clinical" <?php if ($template['FormTemplate']['access_clinical'] == '1') { echo 'checked="checked"';} ?> /> Clinical</label>
		<label for="access-non_clinical" class="label_check_box"><input type="checkbox" name="access[non_clinical]" value="1" id="access-non_clinical" <?php if ($template['FormTemplate']['access_non_clinical'] == '1') { echo 'checked="checked"';} ?> /> Non-Clinical</label>
		<label for="access-patient" class="label_check_box"><input type="checkbox" name="access[patient]" value="1" id="access-patient" <?php if ($template['FormTemplate']['access_patient'] == '1') { echo 'checked="checked"';} ?>  /> Patient</label>
		<br />
		<br />
	</div>	
	<input type="submit" class="btn" value="Save Template" />
	<?php echo $this->Form->end();?>