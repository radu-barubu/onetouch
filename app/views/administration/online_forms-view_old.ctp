<?php

echo $this->Html->script('jquery/Plugins/jsignature/jSignature.js?'.time());
echo $this->Html->script('json2.js?'.time());


App::import('Lib', 'FormBuilder');


$formBuilder = new FormBuilder();


$data = $formBuilder->triggerLoad($template['FormTemplate']['template_content']);


if ($jsonData) {
	$data = $jsonData;
}

$generated = $formBuilder->build($template['FormTemplate']['template_content'], $data);
?>


<div>
<?php echo $this->Html->link('<< Back', array('controller' => 'administration', 'action' => 'online_forms'), array('style' => 'font-size: 12px;')); ?>	
	
	<h2>Generated Form: <?php echo htmlentities($template['FormTemplate']['template_name']); ?> </h2>
	<?php if ($generated): ?>
	<form action="" method="post">
	<?php		echo $generated; ?> 
		
	<br />
	<br />
	<br />
	<input type="submit" class="btn" name="json_data" value="Submit and view JSON Data" />	
		
	</form>
	<?php else:?> 
	<div class="error-message">Error building form. Check form definition</div>
	<?php endif;?> 
	<style type="text/css">
		div.form-component-element {
			margin-bottom: 2em;
		}
		
		.two-column {
			width: 49.5%;
			float: left;
		}
		
		.three-column {
			width: 33%;
			float: left;
		}
		
		
		.clear {
			clear: both;
		}
		
		
		.form_signature {
			width: 800px;
			border: 1px dotted #000;
		}
		
		
	</style>
	<script type="text/javascript">
		$(function(){
			
			$('.form-radio-wrap').buttonset();
			
			$('.form_signature').each(function(){
				var 
					self = this,
					$field = $(this).next()
				;
				
				$(self)
					.jSignature();
				
				
				if ($.trim($field.val())) {
					$(self).jSignature('setData', JSON.parse($field.val()), 'native');
				}
				
				
				$(self).bind('change', function(evt){
					
					var 
						value = $(this).jSignature('getData', 'native'),
						name = $(this).attr('name');
					
					$field.val(JSON.stringify(value));
					
				});
				
				
				$(self).parent().find('.clear_signature').click(function(evt){
					evt.preventDefault();
					
					$(self).jSignature('reset');
					
				});
				
			});
			
			
		});
	</script>
	<br />
	<br />
	
	<h2>JSON Data</h2>
	<p>This is the JSON data extracted from the form. You may edit the data and submit it. 
		The form will be reloaded using the JSON data submitted.</p>
	<form action="" method="post">
		<textarea name="current_json_data" style="max-height: 200px;"><?php echo $jsonData ?></textarea>
		<input type="submit" class="btn" name="load_json" value="Reload Form Using JSON Data" />
		<br class="clear"/>
	</form>
		
	
	
	
	<h2 style="margin-top: 3em;">Quick Edit</h2>
	<p>Edit the form template and submit to immediately view the changes.</p>
	<?php 
	
	echo $this->Form->create('FormTemplate', 
		array(
			'url' => array(
				'controller' => 'administration',
				'action' => 'online_forms',
				'task' => 'view',
				'template_id' => $this->data['FormTemplate']['template_id'],
			))) ?>
		
	
	<?php echo $this->Form->input('template_name'); ?> 
		
	<?php echo $this->Form->input('template_content', array(
		'label' => 'Template Body',
		'type' => 'textarea',
		'style' => 'max-height: 300px;'
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
	<br class="clear" />
	<?php echo $this->Form->end();?>

	<br />
	<br />
	
	<h2>Delete Template</h2>
	<form method="post" action="<?php echo $this->Html->url(array('controller' => 'administration', 'action' => 'online_forms', 'task' => 'delete', 'template_id' => $template['FormTemplate']['template_id'])); ?>">
		<div class="notice">
			Deleting this template will <strong>NOT</strong> affect any previously entered data that uses the template.
			<input type="hidden" name="delete_template" value="1" />
			<input type="submit" value="Delete Template" class="btn" style="float: none;" />
			
		</div>
	</form>
	
	
</div>	