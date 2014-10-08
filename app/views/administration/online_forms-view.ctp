<?php

App::import('Lib', 'FormBuilder');


$formBuilder = new FormBuilder();
$formBuilder->withJSON = true;
$generated = $formBuilder->build($template['FormTemplate']['template_content']);

		echo $this->Html->script('jquery/Plugins/jsignature/jSignature.js?'.time());
		echo $this->Html->script('json2.js?' . time());
    		echo $this->Html->script('online_form_builder.js?' . time());
		echo $this->Html->css('online_form_builder.css?' . time());
		
		$addIcon =  $this->Html->image('add.png');
	
    
$allowedClasses = array('single-column', 'two-column', 'three-column', 'multi-column');    
    
?>
<style>
</style>
<script type="text/javascript">
	window.componentCreateUrl = '<?php echo $this->Html->url(array('controller' => 'administration', 'action' => 'online_form_builder', 'task' => 'generate_component')); ?>';
</script>
<div style="overflow: hidden;">
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
		'label' => false,
		'type' => 'textarea',
		'style' => 'display: none;'
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
	
	<br class="clear" />
  <div class="notice">NOTE: 
  <?php if(!intval($this->data['FormTemplate']['published'])) { ?>
    When you are sure the form is complete and finished all edits then click 
  
  <label for="form-publish" class="label_check_box no-float"><input type="checkbox" name="data[FormTemplate][published]" value="1" id="form-publish"/> Publish</label> and then click 'Save Template' to have it activated.
  <?php } else { ?>
    This template has already been published. Any edits you make will create a new release so former patients who completed the old version will be preserved. 
  <?php } ?>
  </div>
  <br class="clear" />
  <br />
	<?php echo $this->Form->end();?>

	<div id="form-builder">
		<div class="component-menu">
			<a href="" class="btn edit-component no-float"><?php echo $this->Html->image('icons/edit.png') ?></a>
			<a href="" class="btn del-component no-float"><?php echo $this->Html->image('icons/cross.png') ?></a>
		</div>
		
		<div id="form-preview">
			<form method="post" action="">
				<?php echo $generated; ?>
			</form>
		</div>
		
		<div id="form-controls">
			<br />
			<div>
				<button id="add_text" class="btn"><?php echo $addIcon ?> Add Text</button>
				<br />
				<button id="add_textarea" class="btn"><?php echo $addIcon ?> Add Textarea</button>
				<br />
				<button id="add_select" class="btn"><?php echo $addIcon ?> Add Dropdown</button>
				<br />
				<button id="add_radio" class="btn"><?php echo $addIcon ?> Add Radio Button</button>
				<br />
				<button id="add_checkbox" class="btn"><?php echo $addIcon ?> Add Checkbox</button>
				<br />
				<button id="add_signature" class="btn"><?php echo $addIcon ?> Add Signature/Background Image</button>
				<br />
				<button id="add_snippet" class="btn"><?php echo $addIcon ?> Add HTML Snippet</button>
	
				<br />
				<br />
				<br />
				<br />
				
				<a href="" class="btn no-float" id="save-form">Save Template</a>	
				
			</div>
			
			
			
			
		</div>
		<br class="clear" />
		
		
		<div id="text-opts" class="dialog">
			<form method="post" action="">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td>
							Prefix
						</td>
						<td>
              <input type="hidden" name="data[component][name]" value="" class="opt_name"/>
							<textarea name="data[component][label]" class="opt_label"></textarea>
						</td>
					</tr>
					<tr>
						<td>
							Suffix
						</td>
						<td>
							<textarea name="data[component][suffix]" class="opt_suffix"></textarea>
						</td>
					</tr>
					<tr>
						<td>Default Value</td>
						<td><input type="text" name="data[component][default]" value="" class="opt_default"/></td>
					</tr>
					<tr>
					<tr>
						<td>Class Name</td>
						<td>
              <select name="data[class]" class="opt_class">
                <option></option>
                <?php foreach($allowedClasses as $c): ?>
                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                <?php endforeach;?>
              </select>
            </td>
					</tr>
          <tr>
            <td>Text Box Size</td>
            <td>
              <input type="text" name="data[component][size]" value="100" class="opt_size" size="4" />
            </td>
          </tr>
          <tr>
            <td>Width</td>
            <td><input type="text" name="data[width]" value="" class="opt_width" size="4"/></td>
          </tr>
          <tr>
            <td>Height</td>
            <td><input type="text" name="data[height]" value="" class="opt_height" size="4"/></td>
          </tr>
          
					<tr>
						<td colspan="2"><label for="opt_required" class="label_check_box"><input type="checkbox" name="data[component][required]" value="1" class="opt_required" id="opt_required" /> Required</label>


							<input type="hidden" name="data[component][type]" value="" class="opt_type" />
						</td>
					</tr>
				</table>				
			</form>
		</div>
		
		<div id="snippet-opts" class="dialog">
			<form method="post" action="">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td>
							Content
						</td>
						<td>
							<textarea name="data[component][content]" class="opt_content"></textarea>
							<input type="hidden" name="data[component][type]" value="snippet" class="opt_type" />
						</td>
					</tr>
				</table>				
			</form>
		</div>		
		
		<div id="selectable-opts" class="dialog">
			<form method="post" action="">
				
				<div class="field-main">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td>
								Prefix
							</td>
							<td>
                <input type="hidden" name="data[component][name]" value="" class="opt_name"/>
								<textarea name="data[component][label]" class="opt_label"></textarea>
							</td>
						</tr>
						<tr>
							<td>
								Suffix
							</td>
							<td>
								<textarea name="data[component][suffix]" class="opt_suffix"></textarea>
							</td>
						</tr>
						<tr>
							<td>Class Name</td>
						<td>
              <select name="data[class]" class="opt_class">
                <option></option>
                <?php foreach($allowedClasses as $c): ?>
                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                <?php endforeach;?>
              </select>
            </td>
						</tr>
          <tr>
            <td>Width</td>
            <td><input type="text" name="data[width]" value="" class="opt_width" size="4"/></td>
          </tr>
          <tr>
            <td>Height</td>
            <td><input type="text" name="data[height]" value="" class="opt_height" size="4"/></td>
          </tr>
            
						<tr>
							<td colspan="2"><label for="opt_required" class="label_check_box"><input type="checkbox" name="data[component][required]" value="1" class="opt_required" id="opt_required" /> Required</label>
								<input type="hidden" name="data[component][type]" value="" class="opt_type" />
							</td>
						</tr>
					</table>									
				</div>
				
				<div class="field-options">
					<p><strong>Options</strong><p>
					
					<div class="option-wrap"> 
						
						<table>
							<thead>
								<tr>
									<td>Text</td>
									<td>Value</td>
									<td>Default</td>
									<td>&nbsp;</td>
								</tr>
							</thead>
							<tbody>
								<tr class="option-row">
									<td>
										<input type="text" name="option_text[]" value="" class="option_text" size="10" />
									</td>
									<td>
										<input type="text" name="option_value[]" value="" class="option_value" size="10"/> 
									</td>
									<td>
										<label class="label_check_box">
											<input type="checkbox" name="option_default[]" value="1" class="option_default"/> 
										</label>
										<label class="label_check_box">
											<input type="radio" name="option_default[]" value="1" class="option_default"/> 
										<label>	
										
									</td>
									<td>
										<button class="del-option btn"><?php echo $this->Html->image('del.png'); ?></button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					
					
					
						<button id="add-option" class="btn"><?php echo $addIcon; ?> Add Option</button>
				</div>
				
				<br class="clear" />

			</form>
		</div>
		
		<div id="signature-opts" class="dialog">
			<form method="post" action="">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td>
							Label
              <input type="hidden" name="data[component][name]" value="" class="opt_name"/>
							<input type="hidden" name="data[component][type]" value="signature" class="opt_type" />
						</td>
						<td>
							<textarea name="data[component][label]" class="opt_label"></textarea>
						</td>
					</tr>
				</table>				
			</form>
		</div>		
		
		<!--
		<div id="form-code" class="dialog" title="Online Form JSON Code">
			<textarea name="form-code" rows="20" cols="50"></textarea>
		</div>
		-->
	</div>
	
	<br />
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
        <br />
        <br />
	<?php  echo $this->Html->link('Advanced: View JSON Data', array('controller' => 'administration', 'action' => 'online_forms', 'task' => 'edit', 'template_id' => $template['FormTemplate']['template_id'] ), array('class' => 'btn')); ?>	
</div>
<?php echo $this->element('online_form_init', array('pathIndex' => 'administration')); ?>
