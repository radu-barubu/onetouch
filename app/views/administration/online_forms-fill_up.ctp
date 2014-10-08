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
<h2><?php echo htmlentities($template['FormTemplate']['template_name']); ?></h2>
	<?php if ($generated): ?>
	<form id="template-form" action="" method="post" style="width: 775px;" accept-charset="utf-8" enctype="multipart/form-data">
	<label for="patient_autocomplete">Patient: </label>
	<input type="text" id="patient_autocomplete" name="patient_name" value="<?php echo $patientName; ?>" class="required autocompleted"/>
	<input type="hidden" id="patient_autocomplete_id" name="patient_id" value="<?php echo $patient_id; ?>" />
	
	<br />
	<br />
	<?php		echo $generated; ?> 
	
	<br />
	<br />
	<br />
	<input type="submit" class="btn" name="submit" value="Save" />	
	<input type="submit" class="btn" name="submit_to_chart" value="Save and Go to Chart" />	
		
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
	<?php echo $this->element('online_form_init'); ?>
	<script type="text/javascript">
		$(function(){
			var 
				$form = $('#template-form'),
				patientName = ''
			;

			$("#patient_autocomplete").autocomplete('<?php echo $this->Html->url(array('controller' => 'messaging', 'action' => 'index', 'task' => 'patient_load')); ?>', {
				minChars: 2,
				max: 20,
				mustMatch: false,
				matchContains: false,
				scrollHeight: 300,
				width: 400,
				formatItem: function(data, i, total) {
					return data[0] + ' (DOB: ' + data[6] +') ';
				}                        
			});

			$("#patient_autocomplete").result(function(event, data, formatted){
				if (data) {
					patientName = data[0];
					$('#patient_autocomplete_id').val(data[1]);
				} else {
					patientName = '';
					$('#patient_autocomplete_id').val('');
				}
			});			
			
			$('#patient_autocomplete').change(function(evt){
				
				if (patientName != $.trim($("#patient_autocomplete").val())) {
					$('#patient_autocomplete_id').val('');
				}
				
			});
			
			$form.validate({
				errorElement: 'div',
				onfocusout: false,
				onkeyup: false
			});
			
			jQuery.validator.addMethod("autocompleted", function(value, element) { 
				var autocompleted = true;

				if ($("#patient_autocomplete_id").val() == "") {
					autocompleted = false;
				}

				var valid = this.optional(element) || autocompleted;
				
				if (!valid) {
					$('#patient_autocomplete').val('');
				}
				
				return valid;
			}, "Patient not found");			
			
		});
	</script>	
