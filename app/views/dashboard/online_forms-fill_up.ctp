<?php
echo $this->Html->script('jquery/Plugins/jsignature/jSignature.js?'.time());
echo $this->Html->script('json2.js?'.time());
echo $this->Html->css('online_form_builder.css?' . time());

    
App::import('Lib', 'FormBuilder');


$formBuilder = new FormBuilder();


$data = $formBuilder->triggerLoad($template['FormTemplate']['template_content']);


$generated = $formBuilder->build($template['FormTemplate']['template_content'], $data);

$system_admin_access = (($this->Session->read("UserAccount.role_id") == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)?true:false);

?>
<div>
<button class='btn' onclick="javascript:history.back()">Cancel</button>
<br style="clear:both" /><br />
<h2><?php echo htmlentities($template['FormTemplate']['template_name']); ?></h2>
	<?php if ($generated): ?>
	<form action="" method="post"><input type=hidden name='patient_checkin_id' value="<?php echo $patient_checkin_id;?>">
	<?php		echo $generated; ?> 
		
	<br />
	<br />
	<br />
	<input type="submit" class="btn" name="submit" value="Submit" />
		
	</form>
	<?php else:?> 
	<div class="error-message">Error building form. Check form definition</div>
	<?php endif;?> 
	
<button class='btn' onclick="javascript:history.back()">Cancel</button>	

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
<?php echo $this->element('online_form_init'); ?>	
</div>