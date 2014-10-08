<?php
echo $this->Html->script('jquery/Plugins/jsignature/jSignature.js?'.time());
echo $this->Html->script('json2.js?'.time());


App::import('Lib', 'FormBuilder');


$formBuilder = new FormBuilder();


$data = $formData['FormData']['form_data'];
$generated = $formBuilder->build($formData['FormTemplate']['template_content'], $data);


$system_admin_access = (($this->Session->read("UserAccount.role_id") == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)?true:false);

?>
<div>
<button class='btn' onclick="javascript:history.back()"><< Back</button>
<br style="clear:both" /><br />
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
	
	<button class='btn' onclick="javascript:history.back()"><< Back</button>
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
					$field = $(this).next(),
					img = $.trim($(this).parent().find('.background-img').val())
				;
				
				if (img) {
					var 
					 width =0, height = 0,
					 $img = $('<img />')
						 .attr('src', img)
						 .load(function(){
							 width = this.width;
							 height = this.height;

							 $(self).parent().find('.background-img').val(img);

							 $(self).css({
								 'background-image': 'url(' + img + ')',
								 'height': height,
								 'width': width
							 })
							 .find('canvas').remove();


							 $(self)
								 .jSignature({
									 'height': height,
									 'width': width,
									 'showLine': false
								 });				
		
							if ($.trim($field.val())) {
								$(self).jSignature('setData', JSON.parse($field.val()), 'native');
							}
		

						 });					
					
					
					
				} else {
					$(self)
						.jSignature();
		
					if ($.trim($field.val())) {
						$(self).jSignature('setData', JSON.parse($field.val()), 'native');
					}
					
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
				$('.signature-opts').hide();
		});
	</script>	
	
</div>	