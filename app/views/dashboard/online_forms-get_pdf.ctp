<?php

ob_start();

App::import('Lib', 'FormBuilder');


$formBuilder = new FormBuilder();


$data = $formBuilder->triggerLoad($template['FormTemplate']['template_content']);

$formBuilder->pdfVersion = true;
$generated = $formBuilder->build($template['FormTemplate']['template_content'], $data);

$system_admin_access = (($this->Session->read("UserAccount.role_id") == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)?true:false);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo htmlentities($template['FormTemplate']['template_name']); ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="generator" content="Geany 0.19.1" />
	<?php 
		echo $this->Html->script('jquery/jquery-1.8.2.min.js', array('inline' => true));
		echo $this->Html->script('jquery/Plugins/jsignature/jSignature.js?'.time(), array('inline' => true));
		echo $this->Html->script('json2.js?'.time(), array('inline' => true));
	?>
	
	<style type="text/css">
	
	
		.pdf-select-box {
			border: 1px solid #000;
			width: 0.5em;
			height: 0.5em;
			display: block;
		}
		
		.pdf-select-option {
			display: block;
			margin-left: 0.75em;
			margin-top: -1em;
			float: left;
			border: 1px solid red;
		}
		
		label {
			font-weight: bold;
		}
		
	</style>
</head>

<body>

<h2><?php echo htmlentities($template['FormTemplate']['template_name']); ?></h2>
	<?php if ($generated): ?>
	<label for="patient_autocomplete">Patient: </label>
	<br />
	<br />
	__________________________________________________
	
	<br />
	<br />
	<?php		echo $generated; ?> 
		
	<br />
	<br />
	<br />
	<?php else:?> 
	<div class="error-message">Error building form. Check form definition</div>
	<?php endif;?> 
	
	
	<style type="text/css">
		div.form-component-element {
			margin-bottom: 2em;
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
			var 
				$form = $('#template-form'),
				patientName = ''
			;
			
			//$('.form-radio-wrap').buttonset();
			
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
	
	
</body>
</html>
<?php 

$contents = ob_get_clean();

// Get path were files are being saved/read
$targetPath = str_replace('//', '/', $paths['temp']);


// PDF file
$file = 'form_' . $template['FormTemplate']['template_id'] . '.pdf';


$targetFile = $targetPath . $file;

// Write pdf
site::write(pdfReport::generate($contents), $targetFile);

if (!is_file($targetFile))
{
		die("Invalid File: does not exist");
}


header('Content-Type: application/octet-stream; name="' . $file . '"');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Accept-Ranges: bytes');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-transfer-encoding: binary');
header('Content-length: ' . @filesize($targetFile));
@readfile($targetFile);
exit;


?>
