<?php
echo $this->Html->script('jquery/Plugins/jsignature/jSignature.js?'.time());
echo $this->Html->script('json2.js?'.time());


App::import('Lib', 'FormBuilder');


$formBuilder = new FormBuilder();


$data = $formData['FormData']['form_data'];
$generated = $formBuilder->build($formData['FormTemplate']['template_content'], $data);


$system_admin_access = (($this->Session->read("UserAccount.role_id") == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)?true:false);

?>
<div style="overflow: hidden;">
    <?php 
    if($this->Session->read("UserAccount.role_id") == EMR_Roles::PATIENT_ROLE_ID)
    {
        echo $this->element('patient_general_links', array('patient_id' => $formData['FormData']['patient_id'], 'action' => 'forms'));
    }
    else
    {
        $links = array('Forms' => $this->params['action']);
        echo $this->element('links', array('links' => $links));
    }
    
    ?>




<h2><?php echo htmlentities($formData['FormTemplate']['template_name']); ?></h2>
	<?php if ($generated): ?>
	<form id="disabled-form" action="" method="post">
	<?php		echo $generated; ?> 
		
	<br />
	<br />
	<br />
		
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
			
				$('.clear_signature').hide();
				$('#disabled-form').find('input, select, textarea').attr('disabled', 'disabled');
				$('.form-radio-wrap').buttonset({disabled: true});
				
		});
	</script>	
	
</div>	