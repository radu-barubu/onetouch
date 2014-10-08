<?php

ob_start();

App::import('Lib', 'FormBuilder');


$formBuilder = new FormBuilder();




$formBuilder->pdfVersion = true;
$formBuilder->pdfData = true;
$generated = $formBuilder->build($formData['FormTemplate']['template_content'], $formData['FormData']['form_data']);

$system_admin_access = (($this->Session->read("UserAccount.role_id") == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)?true:false);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo htmlentities($formData['FormTemplate']['template_name']); ?></title>
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
    
		.selected {
			background-color: #000;
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
		
		#form-preview {
			float: left;
			min-height: 400px;
			width: 70%;
			border: 1px #ccc dotted;
			
		}

		#form-preview form{
			margin: 1em;
		}


		#form-controls {
			float: right;
			min-height: 400px;
			width: 28%;
		}

		button.btn {
			float: none;
			margin-bottom: 1em;
		}

		#form-controls button.btn {
			width: 200px;
			text-align: left;
		}

		.dialog td {
			padding: 0.5em;
			vertical-align: top;
		}

		#selectable-opts {
			width: 650px;
		}

		.field-main {
			float: left;
			width: 40%;	
		}

		.field-options {
			float: right;
			width: 55%;	
		}

		div.form-component-element {
				margin-bottom: 2em;
				clear: both;
		}

		.two-column, div.form-component-element.two-column {
				width: 45%;
				margin-right: 1em;
				float: left;
				clear: none;
		}

		.three-column, div.form-component-element.three-column {
				width: 30% !important;
				margin-right: 1em;
				float: left;
				clear: none;
				
		}

		.multi-column, div.form-component-element.multi-column {
				margin-right: 1em;
				float: left;
				clear: none;
		}


		.single-column {
			clear: both;
		}

		.clear {
				clear: both;
		}


		.form_signature {
				width: 750px;
				border: 1px dotted #000;
		}

		.btn img {
				vertical-align: middle;
		}	

		.component-menu {
				text-align: right;
		}

		.component-highlight {
				background-color: #FAFAB5;
		}

		#form-preview .form-component-element {
		  border: 1px dashed #ccc;
		}

		.form-component-element input[type=text] {
			margin: 0;
		}


		.form-component-snippet{
			clear: both;
		}        

				
				
		html, body, form input[type="text"], form input[type="date"], form input[type="time"], form input[type="password"], form input[type="file"], form select, form textarea, form.dynamic_select select {
			font-weight: normal;
			font-size: 14px;
			font-family: "Arial";
			font-style: normal;
			color: #464646;
		}



		table.listing, table.listingDis {
			border: 1px solid #dfdfdf;
		}
		table.small_table {
			border: 1px solid #dfdfdf;
		}
		table.listing tr th, table.listingDis tr th {
			background: #e5e5e5;
		}
		table.listing tr td, table.listingDis tr td {
			border-bottom: 1px solid #dfdfdf;
		}


		form input[type="text"], form input[type="date"], form input[type="time"], form input[type="password"], form input[type="file"], form select, form textarea {
			border: 1px solid #AAAAAA;
		}
		<!-- 0.1556s -->
	</style>
</head>

<body>
	<label for="patient_autocomplete">Patient: </label>
  <?php echo $formData['UserAccount']['full_name']; ?>
	<br />
	<br />

  
<h2><?php echo htmlentities($formData['FormTemplate']['template_name']); ?></h2>
	<?php if ($generated): ?>
	<?php		echo $generated; ?> 
		
	<br />
	<br />
	<br />
	<?php else:?> 
	<div class="error-message">Error building form. Check form definition</div>
	<?php endif;?> 
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

// PDF file
$file = 'form_' . $formData['FormData']['form_data_id'] . '.pdf';

$paths['patient_documents'] = $paths['patients'] . $formData['FormData']['patient_id'] . DS . 'documents' . DS;
UploadSettings::createIfNotExists($paths['patient_documents']);
$targetPath = UploadSettings::existing($paths['patient_documents'], $paths['patients']);

$targetFile = $targetPath . $file;

// Write pdf
site::write(pdfReport::generate($contents), $targetFile);

if (!is_file($targetFile))
{
		die("Invalid File: does not exist");
}
//echo $contents;
//exit;

if (!isset($this->params['named']['generate_only'])) {
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
}




?>
