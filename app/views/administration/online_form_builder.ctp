<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
		echo $this->Html->script('jquery/Plugins/jsignature/jSignature.js?'.time());
		echo $this->Html->script('json2.js?' . time());
    echo $this->Html->script('online_form_builder.js?' . time());
		echo $this->Html->css('online_form_builder.css?' . time());
		
		$addIcon =  $this->Html->image('add.png');
		
?>
<script type="text/javascript">
	window.componentCreateUrl = '<?php echo $this->Html->url(array('controller' => 'administration', 'action' => 'online_form_builder', 'task' => 'generate_component')); ?>';
</script>
<div style="overflow: hidden;">
	<?php 
			$links = array(
				'Printable Forms' => 'printable_forms',
				'Online Forms' => $this->params['action'],
			);
			
			echo $this->element('links', array('links' => $links));
	?>	
	
	
	<div id="form-builder">
		<div class="component-menu">
			<a href="" class="btn edit-component no-float"><?php echo $this->Html->image('icons/edit.png') ?></a>
			<a href="" class="btn del-component no-float"><?php echo $this->Html->image('icons/cross.png') ?></a>
		</div>
		
		<div id="form-preview">
			<form method="post" action="">
				
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
				<button id="add_signature" class="btn"><?php echo $addIcon ?> Add Signature</button>
				<br />
				<button id="add_snippet" class="btn"><?php echo $addIcon ?> Add HTML Snippet</button>

				<br />
				<br />
				<br />
				<br />
				
				<button id="get_code" class="btn">Get JSON Code</button>
				
			</div>
			
			
			
			
		</div>
		<br class="clear" />
		
		
		<div id="text-opts" class="dialog">
			<form method="post" action="">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td>Field Name</td>
						<td><input type="text" name="data[component][name]" value="" class="opt_name"/></td>
					</tr>
					<tr>
						<td>
							Label
						</td>
						<td>
							<textarea name="data[component][label]" class="opt_label"></textarea>
						</td>
					</tr>
					<tr>
						<td>Default Value</td>
						<td><input type="text" name="data[component][default]" value="" class="opt_default"/></td>
					</tr>
					<tr>
					<tr>
						<td>Class Name</td>
						<td><input type="text" name="data[class]" value="" class="opt_class"/></td>
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
							<td>Field Name</td>
							<td><input type="text" name="data[component][name]" value="" class="opt_name"/></td>
						</tr>
						<tr>
							<td>
								Label
							</td>
							<td>
								<textarea name="data[component][label]" class="opt_label"></textarea>
							</td>
						</tr>
						<tr>
						<tr>
							<td>Class Name</td>
							<td><input type="text" name="data[class]" value="" class="opt_class"/></td>
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
						<td>Field Name</td>
						<td><input type="text" name="data[component][name]" value="" class="opt_name"/></td>
					</tr>
					<tr>
						<td>
							Label
							<input type="hidden" name="data[component][type]" value="signature" class="opt_type" />
						</td>
						<td>
							<textarea name="data[component][label]" class="opt_label"></textarea>
						</td>
					</tr>
				</table>				
			</form>
		</div>		
		
		<div id="form-code" class="dialog" title="Online Form JSON Code">
			<textarea name="form-code" rows="20" cols="50"></textarea>
		</div>
		
		
		
		
		
	</div>
	
	
	
	
</div>

