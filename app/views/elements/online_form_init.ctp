<?php

$pathIndex = isset($pathIndex) ? $pathIndex : 'temp';

?>
<script type="text/javascript">
		var 
			uploader = '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
			uploadScript   = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',				
			scriptData = {
					'data[path_index]' : '<?php echo $pathIndex ?>'
				},
			cancelImg = '<?php echo $this->Session->webroot; ?>img/cancel.png'
		;
		$(function(){
			
			$('.form-radio-wrap').buttonset();
			
			$('.form_signature').each(function(){
				var 
					self = this,
					$field = $(this).next(),
					$imgUpload = $(self).parent().find('.img-upload'),
					$removeImg = $(self).parent().find('.remove_image').hide(),
					img = $.trim($(this).parent().find('.background-img').val()),
					$btnUploadImg = $(self).parent().find('.btn_upload_img')
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
				
				$(self).parent().find('.clear_signature').click(function(evt){
					evt.preventDefault();
					
					$(self).jSignature('reset');
					
				});				
				
				$removeImg.click(function(evt){
					evt.preventDefault();
					$(self)
						.css({
							'width': 750, 
							'height': 188,
							'background-image' : 'none'
						})
						.find('canvas').remove();
					
					$(self).jSignature();
					$removeImg.hide();
					$(self).parent().find('.background-img').val('');
				});				
				
		$imgUpload.uploadify(
		{
			'fileDataName' : 'file_input',
            'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
            'script'    : uploadScript,
            'cancelImg' : cancelImg,
            'scriptData': scriptData,
            'auto'      : true,
            'height'    : 25,
            'width'     : 192,
						'fileExt'   : '*.jpg;*.png;*.jpeg;*.gif',
            'fileDesc'  : 'Image Files',
            'wmode'     : 'transparent',
            'hideButton': true	,
						'onSelect'  : function(event, ID, fileObj) 
						{

							return false;
						},						
            'onProgress': function(event, ID, fileObj, data) 
            {
				

                return true;
            },						
						 'onComplete': function(event, queueID, fileObj, response, data) {
							 
							 
							 var 
								width =0, height = 0,
								$img = $('<img />')
									.attr('src', response)
									.load(function(){
										width = this.width;
										height = this.height;

										$(self).parent().find('.background-img').val(response);

										$(self).css({
											'background-image': 'url(' + response + ')',
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
											$removeImg.show();
										
											if (window.updateDefaultBg) {
												window.updateDefaultBg.apply(self);
											}
										
										
									});


					}
					}
				);
				
			});
			
			
		});
	</script>