<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
	<h2>Create new Form Template</h2>
	
	<?php echo $this->Form->create('FormTemplate', 
		array(
			'url' => array(
				'controller' => 'administration',
				'action' => 'online_forms',
				'task' => 'add'
			))) ?>
		
	
	<?php echo $this->Form->input('template_name'); ?> 
	<div style="display:none">	
	<?php echo $this->Form->input('template_content', array(
		'label' => 'Template Body',
		'type' => 'textarea',
		'style' => 'max-height: 300px'
	));?> 
	</div>	
	<div class="input">
		<label>Who Can Access?</label>
		<br />
		<label for="access-clinical" class="label_check_box"><input type="checkbox" name="access[clinical]" value="1" id="access-clinical" checked="checked" /> Clinical Staff</label>
		<label for="access-non_clinical" class="label_check_box"><input type="checkbox" name="access[non_clinical]" value="1" id="access-non_clinical" checked="checked" /> Non-Clinical Staff</label>
		<label for="access-patient" class="label_check_box"><input type="checkbox" name="access[patient]" value="1" id="access-patient" checked="checked" /> Patients</label>
		<br />
		<br />
	</div>
	<input type="submit" class="btn" value="Proceed to Form Builder" />
	<?php echo $this->Form->end();?>
	
