$(function(){
	var $form = $('#form-preview').find('form');
	var $activeComponent = null;
	
	var $textOptDialog = $('#text-opts').dialog({
		autoOpen: false,
		modal: true,
		width: 400,
		buttons: {
			'Save': function(){
                                var $name = $(this).find('.opt_name');
                                
                                if (!$.trim($name.val())) {
                                    $name.val('auto_'+uniqid());
                                }
                            
				var data = $(this).find('form').serialize();
				var self = this;
				$.post(window.componentCreateUrl, data, function(html){
					$(self).dialog('close');
					
					var $html = $($.trim(html));
					
					$html.prepend($componentMenu.clone(true));
					
					if ($activeComponent) {
						$activeComponent.replaceWith($html);
						$activeComponent = null;
					} else {
						$form.append($html);
					}
				});
				
				
			},
			'Cancel': function() {
				$(this).dialog('close');
			}
			
		},
    close: function(){
      $(this).find('.opt_name').val('');
    }
		
	});

	var $snippetDialog = $('#snippet-opts').dialog({
		autoOpen: false,
		modal: true,
		width: 400,
		buttons: {
			'Save': function(){
				var data = $(this).find('form').serialize();
				var self = this;
				$.post(window.componentCreateUrl, data, function(html){
					$(self).dialog('close');
					
					var $html = $($.trim(html));
					
					$html.prepend($componentMenu.clone(true));
					
					if ($activeComponent) {
						$activeComponent.replaceWith($html);
						$activeComponent = null;
					} else {
						$form.append($html);
					}
					
					
					
					
				});
				
				
			},
			'Cancel': function() {
				$(this).dialog('close');
			}
			
		}
		
	});
	
  window.updateDefaultBg = function updateDefaultBg () {
		var $component = $(this).closest('.form-component-element');
		var data = $component.data('json_format');    
    data.component.background = $component.find('.background-img').val();
    $component.data('json_format', data); 
  };
  
	var $signatureDialog = $('#signature-opts').dialog({
		autoOpen: false,
		modal: true,
		width: 400,
		buttons: {
			'Save': function(){
                                var $name = $(this).find('.opt_name');
                                
                                if (!$.trim($name.val())) {
                                    $name.val('auto_'+uniqid());
                                }
                                
				var data = $(this).find('form').serialize();
				var self = this;
				$.post(window.componentCreateUrl, data, function(html){
					$(self).dialog('close');

          var $html = $($.trim(html));

          $html.prepend($componentMenu.clone(true));

          if ($activeComponent) {
            $activeComponent.replaceWith($html);
            $activeComponent = null;
          } else {
            $form.append($html);
          }					

          $html.find('.form_signature').each(function(){
            var 
              self = this,
              $field = $(this).next(),
              $imgUpload = $(self).parent().find('.img-upload'),
              $removeImg = $(self).parent().find('.remove_image').hide(),
              img = $.trim($(this).parent().find('.background-img').val()),
              $btnUploadImg = $(self).parent().find('.btn_upload_img')
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
                    'uploader'  : uploader,
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
                            window.updateDefaultBg.apply(self);

                          });


                  }
                  }
                );            

          });              
						
				});
				
				
			},
			'Cancel': function() {
				$(this).dialog('close');
			}
			
		},
    close: function(){
      $(this).find('.opt_name').val('');
    }    
		
	});	
	

	var $selectableDialog = $('#selectable-opts').dialog({
		autoOpen: false,
		modal: true,
		width: 800,
		buttons: {
			'Save': function(){
                                var $name = $(this).find('.opt_name');
                                
                                if (!$.trim($name.val())) {
                                    $name.val('auto_'+uniqid());
                                }
                                
				var elementOptions = [], defaults = [];
				
				$(this).find('tr.option-row').each(function(){
					var $optRow = $(this);
					elementOptions.push({
						'label': $optRow.find('.option_text').val(),
						'value': $optRow.find('.option_value').val()
					});
					
					if ($optRow.find('.option_default:checked').length) {
						defaults.push($optRow.find('.option_value').val());
					}
					
				});
				
				var data = $(this).find('form').serializeArray();
				var self = this;
				
				data.push(
					{
						name: 'data[component][elementOptions]',
						value: JSON.stringify(elementOptions)
					}, 
					{
						name: 'data[component][default]',
						value: JSON.stringify(defaults)
					}
				);
				
				$.post(window.componentCreateUrl, data, function(html){
					$(self).dialog('close');
					
					var $html = $($.trim(html));
					
					$html.prepend($componentMenu.clone(true));
					
					$html.find('.form-radio-wrap ').buttonset();
					if ($activeComponent) {
						$activeComponent.replaceWith($html);
						$activeComponent = null;
					} else {
						$form.append($html);
					}										
					
					
				});
				
				
			},
			'Cancel': function() {
				$(this).dialog('close');
			}			
		},
		close: function(){
				$(this).find('.option-row').remove();
				$tbody.append($row);
				$(this).find('.opt_name').val('');
		}
	});
	
	
	
	var $row = null, $base = null, $clone = null;
	var $tbody = $selectableDialog.find('.field-options').find('tbody');


	$('#form-controls .btn').click(function(evt){
		evt.preventDefault();
		var controlType = $(this).attr('id').split('_').pop();

		if (controlType.match(/text|textarea/gi)) {
                    
                        if (controlType.match(/^text$/gi)) {
                         $textOptDialog.find('.opt_size').removeAttr('disabled').closest('tr').show();
                        } else {
                         $textOptDialog.find('.opt_size').attr('disabled', 'disabled').closest('tr').hide();
                        }
                    
			$textOptDialog.find('form')[0].reset();
			$textOptDialog.find('.opt_type').val(controlType);
			$textOptDialog
				.dialog('option', 'title', 'Add ' + ((controlType == 'text') ? 'Text' : 'Textarea') + ' Field'  )
				.dialog('open');
		}

		if (controlType.match(/snippet/gi)) {
			$snippetDialog.find('form')[0].reset();
			$snippetDialog
				.dialog('option', 'title', 'Add HTML Snippet'  )
				.dialog('open');
		}

		if (controlType.match(/signature/gi)) {
			$signatureDialog.find('form')[0].reset();
			$signatureDialog
				.dialog('option', 'title', 'Add Signature Field'  )
				.dialog('open');
		}
		
		
		if (controlType.match(/select|radio|checkbox/gi)) {
			
			$selectableDialog.find('form')[0].reset();
			$selectableDialog.find('.opt_type').val(controlType);		
			
			
			$row = $tbody.find('tr.option-row').remove();
			
			$base = $row.clone();
			switch(controlType) {
				case 'select':
					$selectableDialog.dialog('option', 'title', 'Add Dropdown Field');
					$base.find('input[type=checkbox]').parent().remove();
					break;
				case 'radio':
					$selectableDialog.dialog('option', 'title', 'Add Radio Field');
					$base.find('input[type=checkbox]').parent().remove();
					break;
					
				default:
					$selectableDialog.dialog('option', 'title', 'Add Checkbox Field');
					$base.find('input[type=radio]').parent().remove();
					break;
			}
			
			$base.find('.del-option').click(function(evt){
				evt.preventDefault();
				
				if ($('tr.option-row').length == 1) {
					return false;
				}
				
				$(this).closest('tr').remove();
			});
			
			$clone = $base.clone(true);
			$tbody.append($clone);
			
			$selectableDialog.dialog('open');
		}

	});
	
	$('#add-option').click(function(evt){
		evt.preventDefault();
		$clone = $base.clone(true);
		$tbody.append($clone);
		$clone.find('.option_text').focus();
	});		
	

	var $formCode = $('#form-code').dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			'Close' : function(){
				$(this).dialog('close');
			}
		},
		width: 500
	});

	$('#get_code').click(function(evt){
		evt.preventDefault();
		var code = {
			components: []
		};
		
		$('.form-component-element, .form-component-snippet').each(function(){
			var json_format = $(this).data('json_format');
			code.components.push(json_format);
		});
		
		
		$formCode.find('textarea').val(JSON.stringify(code, null, 2));
		$formCode.dialog('open');
		
	});
	
	
	
	var $componentMenu = $('div.component-menu').remove()
	
	$componentMenu.find('.del-component').click(function(evt){
		evt.preventDefault();
		$(this).closest('.component-menu').parent().remove();
	});
	
	
	$componentMenu.find('.edit-component').click(function(evt){
		evt.preventDefault();
		
		var $component = $(this).closest('.component-menu').parent();
		var data = $component.data('json_format');
		
		$activeComponent = $component;
		
		if (data.type == 'snippet') {
			$snippetDialog.find('form')[0].reset();
			$snippetDialog.find('.opt_content').val(data.component.content);
			
			$snippetDialog.dialog('open');
			return false;
		}
		
		if (data.component.type.match(/text|textarea/gi)) {
			$textOptDialog.find('form')[0].reset();
                    
                        if (data.component.type.match(/^text$/gi)) {
                         $textOptDialog.find('.opt_size').removeAttr('disabled').closest('tr').show();
                         $textOptDialog.find('.opt_size').val(data.component.size);
                         
                        } else {
                         $textOptDialog.find('.opt_size').attr('disabled', 'disabled').closest('tr').hide();
                        }                    
			$textOptDialog.find('.opt_type').val(data.component.type);
			
			$textOptDialog.find('.opt_name').val(data.component.name);
			$textOptDialog.find('.opt_label').val(data.component.label);
      $textOptDialog.find('.opt_suffix').val(data.component.suffix);
			$textOptDialog.find('.opt_default').val(data.component['default']);
			$textOptDialog.find('.opt_class').val(data['class']);
			
			if (data.component.required) {
				$textOptDialog.find('.opt_required').attr('checked', 'checked');
			} else {
				$textOptDialog.find('.opt_required').removeAttr('checked', 'checked');
			}
			
			$textOptDialog
				.dialog('option', 'title', 'Edit ' + ((data.component.type == 'text') ? 'Text' : 'Textarea') + ' Field'  )
				.dialog('open');
		}		
		
		if (data.component.type.match(/signature/gi)) {
			$signatureDialog.find('form')[0].reset();
			$signatureDialog.find('.opt_name').val(data.component.name);
			$signatureDialog.find('.opt_label').val(data.component.label);
			
			$signatureDialog
				.dialog('option', 'title', 'Edit Signature Field'  )
				.dialog('open');
		}


		if (data.component.type.match(/select|radio|checkbox/gi)) {
			
			$selectableDialog.find('form')[0].reset();
			$selectableDialog.find('.opt_type').val(data.component.type);		
			
			$selectableDialog.find('.opt_name').val(data.component.name);
			$selectableDialog.find('.opt_label').val(data.component.label);
      $selectableDialog.find('.opt_suffix').val(data.component.suffix);
			$selectableDialog.find('.opt_class').val(data['class']);			
			
			if (data.component.required) {
				$selectableDialog.find('.opt_required').attr('checked', 'checked');
			} else {
				$selectableDialog.find('.opt_required').removeAttr('checked', 'checked');
			}			
			
			$row = $tbody.find('tr.option-row').remove();
			
			$base = $row.clone();
			switch(data.component.type) {
				case 'select':
					$selectableDialog.dialog('option', 'title', 'Edit Dropdown Field');
					$base.find('input[type=checkbox]').parent().remove();
					break;
				case 'radio':
					$selectableDialog.dialog('option', 'title', 'Edit Radio Field');
					$base.find('input[type=checkbox]').parent().remove();
					break;
					
				default:
					$selectableDialog.dialog('option', 'title', 'Edit Checkbox Field');
					$base.find('input[type=radio]').parent().remove();
					break;
			}
			
			$base.find('.del-option').click(function(evt){
				evt.preventDefault();
				
				if ($('tr.option-row').length == 1) {
					return false;
				}
				
				$(this).closest('tr').remove();
			});
			
			
			if (Object.prototype.toString.call( data.component['default'] ) !== '[object Array]') {
				data.component['default'] = [data.component['default']];
			}
			
			$.each(data.component.elementOptions, function(){
				var opt = this;
				
				$clone = $base.clone(true);
				
				$clone.find('.option_text').val(opt.label);
				$clone.find('.option_value').val(opt.value);
				
				if ($.inArray(opt.value, data.component['default']) !== -1) {
					$clone.find('.option_default').attr('checked', 'checked');
				}
				
				$tbody.append($clone);
			});
			
			
			$selectableDialog.dialog('open');
		}


		
		
		
	});	
	
	$('.form-component-element, .form-component-snippet').each(function(){
		$(this).prepend($componentMenu.clone(true));
	})
	
	$form.on('mouseover', '.form-component-element, .form-component-snippet', function(){
		$(this).addClass('component-highlight');
	})

	$form.on('mouseout', '.form-component-element, .form-component-snippet', function(){
		$(this).removeClass('component-highlight');
	})

	$('#save-form').click(function(evt){
		evt.preventDefault();
		var code = {
			components: []
		};
		
		$('.form-component-element, .form-component-snippet').each(function(){
			var json_format = $(this).data('json_format');
			code.components.push(json_format);
		});
		
		$('#FormTemplateTemplateContent').val(JSON.stringify(code, null, 2));
		
		$('#FormTemplateOnlineFormsForm').submit();
		
	})
	
	

});
