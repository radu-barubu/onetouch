
function getViewPortWidth()
{
	var viewportwidth;
	var viewportheight;
	
	// the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
	
	if (typeof window.innerWidth != 'undefined')
	{
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
	}
	
	// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
	
	else if (typeof document.documentElement != 'undefined'
	&& typeof document.documentElement.clientWidth !=
	'undefined' && document.documentElement.clientWidth != 0)
	{
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
	}
	
	// older versions of IE
	
	else
	{
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
	
	return viewportwidth;
}

function getWindowCenterLimit()
{
	return getViewPortWidth() / 2;
}

/***************************************************/

var pe_data = null;
var pe_saved_data = null;
var mouse_is_inside_pe = false;

function savePE(section, item_value, subelement_index, answer, answer_value, modifier, body_system_id, element_id, sub_element_id, observation_id, specifier_id, element_as_observation)
{
	if(typeof element_as_observation == "undefined")
	{
		element_as_observation = false;
	}
	
	var element = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element;
	var element_id = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element_id;
	
	var sub_element = "";
	if(subelement_index != "")
	{
		var sub_element = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[subelement_index].sub_element;
		var sub_element_id = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[subelement_index].sub_element_id;
		
                var $subDescription = $('#subelement_text_description_'+sub_element_id);
                var $detailsBox = $('.pe_item_details_box[itemvalue="'+item_value+'"]').has('.pe_btn');

                if ($detailsBox) {
                    if ($detailsBox.find('.pe_btn_active').length) {
                        var 
                            $strong = $subDescription.find('strong'),
                            text= ''
                        ;

                        if (!$strong.length) {
                            text = $subDescription.text();
                            $subDescription
                                .text('')
                                .append($('<strong />').text(text));
                        }
                        
                        var $description = $('#element_text_description_'+element_id);
                        
                        $strong = $description.find('strong') ;
                        text= '';

                        if (!$strong.length) {
                            text = $description.text();
                            $description
                                .text('')
                                .append($('<strong />').text(text));
                        }                        

                        }
                }
                
                
                
	} else {
            var $description = $('#element_text_description_'+element_id);
            var $detailsBox = $('.pe_item_details_box[itemvalue="'+item_value+'"]').has('.pe_btn');

            if ($detailsBox) {
                if ($detailsBox.find('.pe_btn_active').length) {
                    var 
                        $strong = $description.find('strong'),
                        text= ''
                    ;

                    if (!$strong.length) {
                        text = $description.text();
                        $description
                            .text('')
                            .append($('<strong />').text(text));
                    }
                }
            }	
        }
	
	var formobj = $("<form></form>");
	formobj.append('<input name="data[body_system]" type="hidden" value="'+pe_data.PhysicalExamBodySystem[section].body_system+'">');
	
	if(element_as_observation)
	{
		formobj.append('<input name="data[element]" type="hidden" value="">');
	}
	else
	{
		formobj.append('<input name="data[element]" type="hidden" value="'+element+'">');
	}
	
	formobj.append('<input name="data[sub_element]" type="hidden" value="'+sub_element+'">');
	formobj.append('<input name="data[observation]" type="hidden" value="'+answer+'">');
	formobj.append('<input name="data[observation_value]" type="hidden" value="'+answer_value+'">');
	formobj.append('<input name="data[specifier]" type="hidden" value="'+modifier+'">');
	formobj.append('<input name="data[body_system_id]" type="hidden" value="'+body_system_id+'">');
	formobj.append('<input name="data[element_id]" type="hidden" value="'+element_id+'">');
	formobj.append('<input name="data[sub_element_id]" type="hidden" value="'+sub_element_id+'">');
	formobj.append('<input name="data[observation_id]" type="hidden" value="'+observation_id+'">');
	formobj.append('<input name="data[specifier_id]" type="hidden" value="'+specifier_id+'">');

	$.post(
		save_pe_add_link, 
		formobj.serialize(), 
		function(data)
		{
			pe_saved_data = data.pe_saved_data;
		},
		'json'
	);
	
	initAutoLogoff();
}

// Just copying savePE() and changing it so that it deletes
function deletePE(section, item_value, subelement_index, answer, answer_value, modifier, body_system_id, element_id, sub_element_id, observation_id, specifier_id)
{
        var save_pe_delete_link = save_pe_add_link.replace('add', 'delete');
	var element = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element;
	var element_id = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element_id;


	var sub_element = "";
	if(subelement_index != "")
	{
		var sub_element = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[subelement_index].sub_element;
		var sub_element_id = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[subelement_index].sub_element_id;
		
                var $subDescription = $('#subelement_text_description_'+sub_element_id);
		
                
                $detailsBox = $('.pe_item_details_box[itemvalue="'+item_value+'"]').has('.pe_btn');

                if ($detailsBox) {
                    if (!$detailsBox.find('.pe_btn_active').length) {

                        var 
                            $strong = $subDescription.find('strong'),
                            text= ''
                        ;

                        if ($strong.length) {
                            text = $strong.text();
                            $strong.remove();
                            $subDescription
                                .text(text);
                        }

                        var $description = $('#element_text_description_'+element_id);

                        if (!$subDescription.closest('.pe_subelement_area').find('strong').length) {

                            $strong = $description.find('strong');
                            text= '';

                            if ($strong.length) {
                                text = $strong.text();
                                $strong.remove();
                                $description
                                    .text(text);
                            }
                        }
                    }
                }
                
                
	} else {
            var $description = $('#element_text_description_'+element_id);
            var $detailsBox = $('.pe_item_details_box[itemvalue="'+item_value+'"]').has('.pe_btn');

            if ($detailsBox) {
                if (!$detailsBox.find('.pe_btn_active').length) {

                    var 
                        $strong = $description.find('strong'),
                        text= ''
                    ;

                    if ($strong.length) {
                        text = $strong.text();
                        $strong.remove();
                        $description
                            .text(text);
                    }

                }
            }            
        }
	
	var formobj = $("<form></form>");
	formobj.append('<input name="data[body_system]" type="hidden" value="'+pe_data.PhysicalExamBodySystem[section].body_system+'">');
	formobj.append('<input name="data[element]" type="hidden" value="'+element+'">');
	formobj.append('<input name="data[sub_element]" type="hidden" value="'+sub_element+'">');
	formobj.append('<input name="data[observation]" type="hidden" value="'+answer+'">');
	formobj.append('<input name="data[observation_value]" type="hidden" value="'+answer_value+'">');
	formobj.append('<input name="data[specifier]" type="hidden" value="'+modifier+'">');
	formobj.append('<input name="data[body_system_id]" type="hidden" value="'+body_system_id+'">');
	formobj.append('<input name="data[element_id]" type="hidden" value="'+element_id+'">');
	formobj.append('<input name="data[sub_element_id]" type="hidden" value="'+sub_element_id+'">');
	formobj.append('<input name="data[observation_id]" type="hidden" value="'+observation_id+'">');
	formobj.append('<input name="data[specifier_id]" type="hidden" value="'+specifier_id+'">');

	$.post(
		save_pe_delete_link, 
		formobj.serialize(), 
		function(data)
		{
			pe_saved_data = data.pe_saved_data;
		},
		'json'
	);
	
	initAutoLogoff();
}

function deletePEText(text_type, item_id) {
	var formobj = $("<form></form>");
	formobj.append('<input name="data[text_type]" type="hidden" value="'+text_type+'">');
	formobj.append('<input name="data[item_id]" type="hidden" value="'+item_id+'">');
	
	$.post(
		delete_pe_text_link, 
		formobj.serialize(), 
		function(data)
		{
			pe_saved_data = data.pe_saved_data;
	
		},
		'json'
	);	
}

function savePEText(text_type, item_id, item_value, element_id, subelement_id)
{
	if(! item_value ) 
	{
		return;
	}
 
	/*
	if(text_type == 'element')
	{
		$('#element_text_description_'+item_id).css('font-weight', 'bold');
	}
	
	if(text_type == 'subelement')
	{
		$('#subelement_text_description_'+item_id).css('font-weight', 'bold');
	}
	
	if(element_id != '')
	{
		$('#element_text_description_'+element_id).css('font-weight', 'bold');
	}
	
	if(subelement_id != '')
	{
		$('#subelement_text_description_'+subelement_id).css('font-weight', 'bold');
	}
	*/
	
	var formobj = $("<form></form>");
	formobj.append('<input name="data[text_type]" type="hidden" value="'+text_type+'">');
	formobj.append('<input name="data[item_id]" type="hidden" value="'+item_id+'">');
	formobj.append('<input name="data[item_value]" type="hidden" value="'+item_value+'">');
	
	item_value = $.trim(item_value);
	if ( item_value != '') {
		$('#'+text_type+'_text_description_'+item_id).text('').html('<strong>other</strong>');
	} else {
		$('#'+text_type+'_text_description_'+item_id).text('').html('other');
	}


	
	$.post(
		save_pe_text_link, 
		formobj.serialize(), 
		function(data)
		{
			pe_saved_data = data.pe_saved_data;
			if (text_type == 'subelement') {
				var len = pe_saved_data.EncounterPhysicalExamText.length, i = 0;
				for (i = 0; i < len; i++) {
					if (parseInt(pe_saved_data.EncounterPhysicalExamText[i].sub_element_id, 10) == parseInt(item_id, 10)) {
						
						if (item_value != '') {
							var btn_label = $('#element_text_description_'+pe_saved_data.EncounterPhysicalExamText[i].element_id).text();
							$('#element_text_description_'+pe_saved_data.EncounterPhysicalExamText[i].element_id).text('').html('<strong>'+btn_label+'</strong>');
						} else {
							$('#element_text_description_'+pe_saved_data.EncounterPhysicalExamText[i].element_id).text('').html('other');
						}


					}
				}
			}			
		},
		'json'
	);
}

function textElementHasData(element_id, subelement_id) {
	
	if (!pe_saved_data.EncounterPhysicalExamText) {
		return false;
	}
	
	var len = pe_saved_data.EncounterPhysicalExamText.length;
	var i;
	
	var elementType = (element_id) ? 'element' : 'subelement';
	var item_id = element_id || subelement_id;
	for (i=0; i < len; i++) {
		if (
			parseInt(pe_saved_data.EncounterPhysicalExamText[i].item_id, 10) == parseInt(item_id, 10)
			&&
			pe_saved_data.EncounterPhysicalExamText[i].text_type == elementType
		) {
				
			if (elementType == 'subelement') {
				$('#element_text_data_'+pe_saved_data.EncounterPhysicalExamText[i].element_id).html('<strong>other<strong>');
			}	
				
			return true;
		}
		
		if (
			elementType == 'element' 
			&& 
			pe_saved_data.EncounterPhysicalExamText[i].text_type == 'subelement' 
			&&
			parseInt(pe_saved_data.EncounterPhysicalExamText[i].element_id, 10) == parseInt(item_id, 10)
		) {
			return true;
		}
	}
	
	return false;
}

function isElementHasData(body_system_id, element, element_id)
{
	var ret = false;
	
	for(var i in pe_saved_data.EncounterPhysicalExamDetail)
	{
		if(pe_saved_data.EncounterPhysicalExamDetail[i].body_system_id == body_system_id && pe_saved_data.EncounterPhysicalExamDetail[i].element_id == element_id)
		{
			ret = true;
		}
	}
	
	if (!pe_saved_data.EncounterPhysicalExamText) {
		return ret;
	}
	
	for(var i in pe_saved_data.EncounterPhysicalExamText)
	{
		switch(pe_saved_data.EncounterPhysicalExamText[i].text_type)
		{
			case 'element':
			case 'subelement':
			case 'observation':
			{
				//do nothing
			} //break;
			/*
			case 'element':
			{
				if(pe_saved_data.EncounterPhysicalExamText[i].item_id == element_id)
				{
					ret = true;
				}
			}break;
			*/
			default:
			{
				if(pe_saved_data.EncounterPhysicalExamText[i].element_id == element_id)
				{
					ret = true;
				}
			}
		}
	}
	
	return ret;
}

function isSubElementHasData(body_system_id, element_id, sub_element, sub_element_id)
{
	var ret = false;
	
	for(var i in pe_saved_data.EncounterPhysicalExamDetail)
	{
		if(pe_saved_data.EncounterPhysicalExamDetail[i].body_system_id == body_system_id && pe_saved_data.EncounterPhysicalExamDetail[i].element_id == element_id && pe_saved_data.EncounterPhysicalExamDetail[i].sub_element_id == sub_element_id)
		{
			ret = true;
		}
	}
	
	if (!pe_saved_data.EncounterPhysicalExamText) {
		return ret;
	}	
	
	for(var i in pe_saved_data.EncounterPhysicalExamText)
	{	
		
		switch(pe_saved_data.EncounterPhysicalExamText[i].text_type)
		{
			case 'element':
			case 'subelement':
			case 'observation':
			{
				//do nothing;
			}break;
			/*
			case 'subelement':
			{
				if(pe_saved_data.EncounterPhysicalExamText[i].item_id == sub_element_id)
				{
					ret = true;
				}
			}break;
			*/
			default:
			{
				if(pe_saved_data.EncounterPhysicalExamText[i].sub_element_id == sub_element_id)
				{
					ret = true;
				}
			}
		}
	}
	
	return ret;
}

function changePETemplate(template_id, template_desc)
{
	$("#imgPELoading").show();
	var formobj = $("<form></form>");
        var $myTemplatePE = $('#myTemplatePE');
        
        if ($myTemplatePE.length) {
            formobj.append('<input name="data[template_id]" type="hidden" value="'+$myTemplatePE.val()+'">');
            $('#pe_template_desc').html($("#myTemplatePE option:selected").text());
        } else {
            formobj.append('<input name="data[template_id]" type="hidden" value="'+ template_id +'">');
            $('#pe_template_desc').html(template_desc);
        }
		
	$.post(
		pe_get_list_link, 
		formobj.serialize(), 
		function(data)
		{
			pe_data = data.pe_data;
			pe_saved_data = data.pe_saved_data;
			
			resetPE(pe_data, pe_saved_data);
			
			closePEItemDetails();
			$("#imgPELoading").hide();
			$("#table_pe_templates").delay(3500).slideUp("slow");
		},
		'json'
	);
}

function resetPEButtonState()
{
	$(".encounter_item", $('#pe_section')).each(function()
	{
		$(this).unbind('mouseover');
		$(this).unbind('mouseout');
		
		$(this).removeClass('ui-state-active');
		$(this).removeClass('ui-state-hover');
		
		$(this).mouseover(function()
		{
			$(this).addClass('ui-state-hover');
		}).mouseout(function()
		{
			$(this).removeClass('ui-state-hover');
		});
		
		$(this).attr("isopen", "false");
		
		initPEWindowEvent(this);
	});
}

function initPEWindowEvent(obj)
{
	$(obj).unbind('mouseenter');
	$(obj).mouseenter(function()
	{
		mouse_is_inside_pe = true;
	});
	
	$(obj).unbind('mouseleave');
	$(obj).mouseleave(function()
	{
		mouse_is_inside_pe = false;
	});
}

function slideDownPEDetails(unique_id)
{
	var pe_item_details_box = $('#'+unique_id);
	
	pe_item_details_box.css("visibility", "hidden");
	pe_item_details_box.show();
	var dimension = {'top': pe_item_details_box.offset().top, 'left': pe_item_details_box.offset().left, 'width': pe_item_details_box.width(), 'height': pe_item_details_box.height()};
	
	var center_limit = getWindowCenterLimit();
	
	//get content total height
	var wrapper_div_height = $('#wrapper').height();
	var current_max_top = dimension.top + dimension.height + 10; //10 is adjustment
	var new_top = dimension.top - dimension.height - 60;
	
	if(current_max_top > wrapper_div_height && new_top > 0)
	{
		$('.arrow-up', pe_item_details_box).hide();
		$('.arrow-up2', pe_item_details_box).hide();
		$('.arrow-down', pe_item_details_box).show();
		
		pe_item_details_box.css('top', new_top + 'px');
	}
	else
	{
		$('.arrow-up', pe_item_details_box).show();
		$('.arrow-up2', pe_item_details_box).show();
		$('.arrow-down', pe_item_details_box).hide();
	}
	
	var new_left = dimension.left - dimension.width + 30;
	pe_item_details_box.css("visibility", "visible");
	pe_item_details_box.hide();
	
	if(dimension.left > center_limit)
	{	
		pe_item_details_box.css('left', new_left + 'px');
		$('.arrow-up', pe_item_details_box).css('float', 'right');
		$('.arrow-up2', pe_item_details_box).css('float', 'right');
		$('.arrow-down', pe_item_details_box).css('float', 'right');
	}
	
	pe_item_details_box.fadeIn("fast", function()
	{
            
                // Clicking on the row cycles through the
                // available button choices - rolan
                $('tbody', pe_item_details_box)
                    .css('cursor', 'pointer')
                    .unbind('click')
                    .bind('click', function(evt){
						if(page_access == 'R')
						{
							return;
						}
						
                        evt.stopPropagation();

                        if ($(evt.target).hasClass('pe_btn') || $(evt.target).hasClass('right')) {
                            return false;
                        }

                        if ($(evt.target).is('input')) {
                            return false;
                        }
                        
                        if ($(evt.target).has('.pe_txt_editable').length) {
                            //return false;
                        }
                        
                     
                        var 
                            $activeBtn = $(this).find('tr').find('.pe_btn_active'),
                            $next = null
                        ;
                        
                        if ($activeBtn.length) {
                            $next = $activeBtn.next();
                            
                            if ($next.length) {
                                $next.click();
                            } else {
                                // No button to cycle next, turn off!
                                // When turning we also need to remove
                                // related data should also be deleted
                                $activeBtn.click();
                            }
                            
                        } else {
                            $(this).find('tr').find('.pe_btn').first().click();
                        }
                        
                    })
                    .find('td.right')
                        .css('cursor', 'default');
            
		$(pe_item_details_box).find('.pe_item_table').each(function(){
			var pe_answer_modifier_area = $('.pe_answer_modifier_area', this);
			
			var $activeBtn = $(this).find('.pe_btn_active');
			
			if ($activeBtn.length && $activeBtn.attr("answervalue") != "NC") {
				if(!pe_answer_modifier_area.is(":visible"))	{
								pe_answer_modifier_area.fadeIn("fast");
				}				
			}
			
			
		});
		
		$('.pe_btn', pe_item_details_box).unbind('click');
		$('.pe_btn', pe_item_details_box)
                        .click(function(evt)
                        {
																if(page_access == 'R')
																{
																	return;
																}

																var $peTxt = $(this).closest('td').prev().find('.pe_txt_editable');

																if ($peTxt.length) {
																	var peTxtVal = $.trim($peTxt.val());

																	if (peTxtVal == '' && $(this).attr("answervalue") != "NC") {
																		return false;
																	}

																}
							
							
                                evt.stopPropagation();

                                var section = $(this).attr("section");
                                var item_value = $(this).attr("itemvalue");
                                var answer = $(this).attr("answer");
                                var answer_value = $(this).attr("answervalue");
                                var modifier = $(this).attr("modifier");
                                var subelement_index = $(this).attr("subelement_index");

                                var body_system_id = $(this).attr("body_system_id");
                                var element_id = $(this).attr("element_id");
                                var sub_element_id = $(this).attr("sub_element_id");
                                var observation_id = $(this).attr("observation_id");
                                var specifier_id = $(this).attr("specifier_id");


                                if(!$(this).hasClass('pe_btn_active'))
                                {
                                    var parent = $(this).parent();
                                    $('.pe_btn', parent).removeClass('pe_btn_active');
                                    $(this).addClass("pe_btn_active");
                                    
                                    if ($(this).attr("answervalue") != "NC") {
                                        savePE(section, item_value, subelement_index, answer, answer_value, modifier, body_system_id, element_id, sub_element_id, observation_id, specifier_id);
                                    } else {
                                        // If NC remove data from database
                                        deletePE(section, item_value, subelement_index, answer, answer_value, modifier, body_system_id, element_id, sub_element_id, observation_id, specifier_id);
                                    }
                                    
                                    
                                } else {
                                    $(this).removeClass("pe_btn_active");
                                    deletePE(section, item_value, subelement_index, answer, answer_value, modifier, body_system_id, element_id, sub_element_id, observation_id, specifier_id);
                                }

                                var pe_item_table = $(this).parents(".pe_item_table")
                                var pe_answer_modifier_area = $('.pe_answer_modifier_area', pe_item_table);

                                if(pe_answer_modifier_area.length)
                                {
                                        if($(this).attr("answervalue") == "+" || $(this).attr("answervalue") == "-")
                                        {
                                                if(!pe_answer_modifier_area.is(":visible"))
                                                {
                                                        pe_answer_modifier_area.fadeIn("fast");
                                                }
                                        }
                                        else
                                        {
                                                if(pe_answer_modifier_area.is(":visible"))
                                                {
                                                        pe_answer_modifier_area.slideUp("fast");
                                                }
                                        }
                                }
                        });
		
		$('.pe_btn_modifier', pe_item_details_box).unbind('click');
		$('.pe_btn_modifier', pe_item_details_box).click(function()
		{
			$('.pe_modifier_opt', $(this)).attr("checked", "checked");
			
			var section = $(this).attr("section");
			var item_value = $(this).attr("itemvalue");
			var answer = $(this).attr("answer");
			var answer_value = $(this).attr("answervalue");
			var modifier = $(this).attr("modifier");
			var subelement_index = $(this).attr("subelement_index");
			
			var body_system_id = $(this).attr("body_system_id");
			var element_id = $(this).attr("element_id");
			var sub_element_id = $(this).attr("sub_element_id");
			var observation_id = $(this).attr("observation_id");
			var specifier_id = $(this).attr("specifier_id");
			
			answer_value= $(this).closest('.pe_item_table').find('.pe_btn_active').attr('answervalue');
			
			savePE(section, item_value, subelement_index, answer, answer_value, modifier, body_system_id, element_id, sub_element_id, observation_id, specifier_id);
		});
		
		$('.pe_btn_subelement', pe_item_details_box).unbind('click');
		$('.pe_btn_subelement', pe_item_details_box).click(function()
		{
			resetPEClickEvent(this, true, true);
		});
		
		$('.pe_txt_editable', pe_item_details_box).unbind('blur');
		$('.pe_txt_editable', pe_item_details_box).blur(function()
		{
			var txt = $.trim($(this).val());
			
			if (txt == '') {
				deletePEText($(this).attr('text_type'), $(this).attr('item_id'));
				
				$(this).closest('td').next().find('.pe_btn[answervalue="NC"]').click();
				
			} else {
				savePEText($(this).attr('text_type'), $(this).attr('item_id'), $(this).val(), $(this).attr('element_id'), $(this).attr('subelement_id'));
			}
			
		});
		
		$('.pe_element_text_edit', pe_item_details_box).unbind('blur');
		$('.pe_element_text_edit', pe_item_details_box).blur(function()
		{
			savePEText($(this).attr('text_type'), $(this).attr('item_id'), $(this).val(), $(this).attr('element_id'), $(this).attr('subelement_id'));
			
			if($(this).attr('text_type') == 'element' && $(this).attr('enable_save_item') == 'true')
			{
				savePE($(this).attr('section'), $(this).attr('itemvalue'), 0, $(this).val(), '+', '', $(this).attr('body_system_id'), $(this).attr('element_id'), $(this).attr('subelement_id'), 0, 0, true);
			}
		});
	});
	
	$(".pe_item_details_box").each(function()
	{
		initPEWindowEvent(this);
	});
	if(typeof($ipad)==='object')$ipad.ready();
}

function hideAllPETable()
{
	$('.pe_item_details_box').each(function()
	{
		$(this).fadeOut('fast', function() 
		{
			$(this).remove();
		});
	});
}

function hideAllSubPETable()
{
	$('.pe_sub_item_details_box').each(function()
	{
		$(this).fadeOut('fast', function() 
		{
			$(this).remove();
		});
	});
}

function resetPEClickEvent(btn, open_cmd, subelement, subelement_btn)
{
	resetPEButtonState();
	
	if(open_cmd)
	{
		if(!subelement)
		{
			$(btn).removeClass('ui-state-hover');
			$(btn).addClass('ui-state-active');
			$(btn).unbind('mouseover');
			$(btn).unbind('mouseout');
		}
		
		if(subelement)
		{
			hideAllSubPETable();
		}
		else
		{
			hideAllPETable();
		}
		
		var position = $(btn).offset();
		var top = position.top + 40;
		var left = position.left + 0;
		var section = $(btn).attr('section');
		var item_value = $(btn).attr('itemvalue');
		
		var html = '';
		
		var pe_item_details_box_add_class = '';
		
		if(subelement)
		{
			pe_item_details_box_add_class = 'pe_sub_item_details_box';
		}
		
		var unique_id = 'pe_item_details_box_' + uniqid();
		
		html += '<div id="'+unique_id+'" class="pe_item_details_box '+pe_item_details_box_add_class+'" style="top: '+top+'px; left: '+left+'px;" section="'+section+'" itemvalue="'+item_value+'">';
		html += '<div class="arrow-up"></div>';
		html += '<div class="arrow-up2"></div>';
		
		var observation_array = null;
		var current_subelement_id = '';
		var current_element_id = '';
		var element_text = "";
		var placeholder_element = "";
		var element_text_type = "";
		var element_text_id = "";
		var current_body_system_id = "";
		
		if(subelement)
		{
			var subelement_index = $(btn).attr('subelement_index');
			observation_array = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[subelement_index].PhysicalExamObservation;
			current_element_id = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element_id;
			current_subelement_id = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[subelement_index].sub_element_id;
			
			element_text = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[subelement_index].sub_element;
			placeholder_element = "other";
			element_text_type = "subelement";
			element_text_id = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[subelement_index].sub_element_id;
			
			var saved_text = '';
				
			for(var b in pe_saved_data.EncounterPhysicalExamText)
			{
				if(pe_saved_data.EncounterPhysicalExamText[b].text_type == 'subelement' && pe_saved_data.EncounterPhysicalExamText[b].item_id == element_text_id)
				{
					saved_text = pe_saved_data.EncounterPhysicalExamText[b].item_value;
					break;
				}
			}
		}
		else
		{
			var subelement_index = "";
			observation_array = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamObservation;
			current_element_id = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element_id;
			
			element_text = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element;
			placeholder_element = "other";
			element_text_type = "element";
			element_text_id = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element_id;
			
			var saved_text = '';
				
			for(var b in pe_saved_data.EncounterPhysicalExamText)
			{
				if(pe_saved_data.EncounterPhysicalExamText[b].text_type == 'element' && pe_saved_data.EncounterPhysicalExamText[b].item_id == element_text_id)
				{
					saved_text = pe_saved_data.EncounterPhysicalExamText[b].item_value;
					break;
				}
			}
		}
		
		if(element_text == '[text]')
		{
			var dis_text_edit_str = '';
			if(page_access == 'R')
			{
				dis_text_edit_str = 'disabled="disabled"';
			}
			
			var enable_save_item = 'false';
			
			if(element_text_type == 'element')
			{
				var element_observation = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamObservation;
				var element_subelement = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement;
				
				if(element_observation.length == 0 && element_subelement.length == 0)
				{
					enable_save_item = 'true';
				}
			}
			
			if(element_text_type == 'subelement')
			{
				var subelement_observation = pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[subelement_index].PhysicalExamObservation;
				
				if(subelement_observation.length == 0)
				{
					enable_save_item = 'true';
				}
			}
			
			html += '<div class="pe_item_table pe_element_text_edit_area">';
			html += '<textarea section="'+section+'" body_system_id="'+pe_data.PhysicalExamBodySystem[section].body_system_id+'" itemvalue="'+item_value+'" subelement_index="'+subelement_index+'" type="text" '+dis_text_edit_str+' class="pe_element_text_edit" enable_save_item="'+enable_save_item+'" element_id="'+current_element_id+'" subelement_id="'+current_subelement_id+'" placeholder="'+placeholder_element+'" text_type="'+element_text_type+'" item_id="'+element_text_id+'" value="'+saved_text+'" id="element_'+element_text_id+'" rows="3">'+saved_text+'</textarea>&nbsp;&nbsp;<a onclick="showNow(\'element_'+element_text_id+'\')" id="exacttimebtn" style="vertical-align: middle;" href="javascript:void(0)"><img alt="Time now" src="/img/time.gif"></a></span>';
			html += '</div>';
		}
		
		for(var i in observation_array)
		{
			var saved_modifier_value = "";
			var saved_answer_value = "";
			
			for(var b in pe_saved_data.EncounterPhysicalExamDetail)
			{
				var logic = false;
				
				if(subelement)
				{
					logic = (pe_saved_data.EncounterPhysicalExamDetail[b].body_system_id == pe_data.PhysicalExamBodySystem[section].body_system_id && pe_saved_data.EncounterPhysicalExamDetail[b].element_id == pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element_id && pe_saved_data.EncounterPhysicalExamDetail[b].sub_element_id == pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[subelement_index].sub_element_id && pe_saved_data.EncounterPhysicalExamDetail[b].observation_id == observation_array[i].observation_id);
				}
				else
				{
					logic = (pe_saved_data.EncounterPhysicalExamDetail[b].body_system_id == pe_data.PhysicalExamBodySystem[section].body_system_id && pe_saved_data.EncounterPhysicalExamDetail[b].element_id == pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element_id && pe_saved_data.EncounterPhysicalExamDetail[b].observation_id == observation_array[i].observation_id);
				}
				
				if(logic)
				{
					saved_modifier_value = pe_saved_data.EncounterPhysicalExamDetail[b].specifier;
					saved_answer_value = pe_saved_data.EncounterPhysicalExamDetail[b].observation_value;
				}
			}

			html += '<div class="pe_item_table">';
			html += '<table class="pe_item_table_obj" cellpadding="0" cellspacing="0">';
			html += '<tr>';
			html += '<td class="left"><span class="pe_item_text">';
			
			if(observation_array[i].observation == '[text]')
			{
				var saved_text = '';
				
				for(var b in pe_saved_data.EncounterPhysicalExamText)
				{
					if(pe_saved_data.EncounterPhysicalExamText[b].text_type == 'observation' && pe_saved_data.EncounterPhysicalExamText[b].item_id == observation_array[i].observation_id)
					{
						saved_text = pe_saved_data.EncounterPhysicalExamText[b].item_value;
						break;
					}
				}
				
				var dis_text_edit_str = '';
				if(page_access == 'R')
				{
					dis_text_edit_str = 'disabled="disabled"';
				}
				
				html += '<span class="pe_item_editable"><input class="pe_txt_editable" '+dis_text_edit_str+' type="text" element_id="'+current_element_id+'" subelement_id="'+current_subelement_id+'" placeholder="other" text_type="observation" item_id="'+observation_array[i].observation_id+'" value="'+saved_text+'" /></span>';
			}
			else
			{
				html += observation_array[i].observation;
			}

			html += '</span></td>';
			html += '<td class="right">';
			
			var class_to_use = '';
			if(saved_answer_value == '+')
			{
				class_to_use = 'pe_btn_active';
			}
				
			html += '<span class="pe_btn '+class_to_use+'" section="'+section+'" body_system_id="'+pe_data.PhysicalExamBodySystem[section].body_system_id+'" itemvalue="'+item_value+'" element_id="'+current_element_id+'" subelement_index="'+subelement_index+'" sub_element_id="'+current_subelement_id+'" answer="'+observation_array[i].observation+'" observation_id="'+observation_array[i].observation_id+'" answervalue="+" modifier="" specifier_id="0">(+)</span>&nbsp;';
			
			var class_to_use = '';
			if(saved_answer_value == '-')
			{
				class_to_use = 'pe_btn_active';
			}
			
			html += '<span class="pe_btn '+class_to_use+'" section="'+section+'" body_system_id="'+pe_data.PhysicalExamBodySystem[section].body_system_id+'" itemvalue="'+item_value+'" element_id="'+current_element_id+'" subelement_index="'+subelement_index+'" sub_element_id="'+current_subelement_id+'" answer="'+observation_array[i].observation+'" observation_id="'+observation_array[i].observation_id+'" answervalue="-" modifier="" specifier_id="0">(-)</span>&nbsp;';
			
			var class_to_use = '';
			if(saved_answer_value == 'NC')
			{
				class_to_use = 'pe_btn_active';
			}
			
			html += '<span class="pe_btn '+class_to_use+'" section="'+section+'" body_system_id="'+pe_data.PhysicalExamBodySystem[section].body_system_id+'" itemvalue="'+item_value+'" element_id="'+current_element_id+'" subelement_index="'+subelement_index+'" sub_element_id="'+current_subelement_id+'" answer="'+observation_array[i].observation+'" observation_id="'+observation_array[i].observation_id+'" answervalue="NC" modifier="" specifier_id="0">(NC)</span>';
			
			html += '</td>';
			html += '</tr>';
			html += '</table>';
			
			if(observation_array[i].PhysicalExamSpecifier.length > 0)
			{
				html += '<div class="pe_answer_modifier_area">';

				for(var a = 0; a < observation_array[i].PhysicalExamSpecifier.length; a++)
				{
					var checked_value = '';

					if(saved_modifier_value == observation_array[i].PhysicalExamSpecifier[a].specifier)
					{
						checked_value = 'checked="checked"';
					}
					
					html += '<span class="pe_btn_modifier" section="'+section+'" body_system_id="'+pe_data.PhysicalExamBodySystem[section].body_system_id+'" itemvalue="'+item_value+'" element_id="'+current_element_id+'" subelement_index="'+subelement_index+'" sub_element_id="'+current_subelement_id+'" answer="'+observation_array[i].observation+'" observation_id="'+observation_array[i].observation_id+'" answervalue="+" modifier="'+observation_array[i].PhysicalExamSpecifier[a].specifier+'" specifier_id="'+observation_array[i].PhysicalExamSpecifier[a].specifier_id+'">';
					html += '<input type="radio" name="pe_modifier_opt_'+i+'" class="pe_modifier_opt" '+checked_value+' /> ';
					
					if(observation_array[i].PhysicalExamSpecifier[a].specifier == '[text]')
					{
						var saved_text = '';
				
						for(var b in pe_saved_data.EncounterPhysicalExamText)
						{
							if(pe_saved_data.EncounterPhysicalExamText[b].text_type == 'specifier' && pe_saved_data.EncounterPhysicalExamText[b].item_id == observation_array[i].PhysicalExamSpecifier[a].specifier_id)
							{
								saved_text = pe_saved_data.EncounterPhysicalExamText[b].item_value;
								break;
							}
						}
						
						html += '<textarea class="pe_txt_editable pe_txt_editable_specifier" element_id="'+current_element_id+'" subelement_id="'+current_subelement_id+'" type="text" placeholder="other" text_type="specifier" item_id="'+observation_array[i].PhysicalExamSpecifier[a].specifier_id+'" value="'+saved_text+'" id="element_'+observation_array[i].PhysicalExamSpecifier[a].specifier_id+'" rows="3">'+saved_text+'</textarea>&nbsp;&nbsp;<a onclick="showNow(\'element_'+observation_array[i].PhysicalExamSpecifier[a].specifier_id+'\')" id="exacttimebtn" style="vertical-align: middle;" href="javascript:void(0)"><img alt="Time now" src="/img/time.gif"></a></span>';
					}
					else
					{
						html += observation_array[i].PhysicalExamSpecifier[a].specifier;
					}
					
					html += '</span>';
				}
				html += '</div>';
			}
			
			html += '</div>';
		}
		
		if(!subelement)
		{
			if(pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement.length > 0)
			{
				html += '<div class="pe_subelement_area">';

				for(var i in pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement)
				{
					html += '<span id="subelement_text_description_'+pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[i].sub_element_id+'" class="pe_btn_subelement" section="'+section+'" itemvalue="'+item_value+'" subelement_index="'+i+'">';
					
					if(isSubElementHasData(pe_data.PhysicalExamBodySystem[section].body_system_id, pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element_id, pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[i].sub_element, pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[i].sub_element_id))
					{
						html += '<strong>';
					}
					
					if(pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[i].sub_element == '[text]')
					{
						if (textElementHasData('', pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[i].sub_element_id)) {
							html += '<strong>other</strong>';
						} else {
							html += 'other';
						}
					}
					else
					{
						html += pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[i].sub_element;
					}
					
					if(isSubElementHasData(pe_data.PhysicalExamBodySystem[section].body_system_id, pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].element_id, pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[i].sub_element, pe_data.PhysicalExamBodySystem[section].PhysicalExamElement[item_value].PhysicalExamSubElement[i].sub_element_id))
					{
						html += '</strong>';
					}
					
					html += '</span>';
				}
				
				html += '</div>';
			}
		}
		
		html += '<div class="arrow-down"><div class="arrow-down2"></div></div>';
		html += '</div>';
		
		
		$("#wrapper").append(html);
		
		$('#wrapper').delegate('.pe_txt_editable', 'keyup', function(){
			var txt = $.trim($(this).val());
			
			if (txt) {
				
				var $btn = $(this).closest('td').next().find('.pe_btn:first');
				if ($btn.length && !$btn.hasClass('pe_btn_active')) {
					$btn.click();
				}
			}
			
		});
		
		
		slideDownPEDetails(unique_id);
        add_IE8_placeholder(unique_id);
	}
	
	window.doDragon();
}

function add_IE8_placeholder(unique_id) {
    if($.browser.msie) {
        $('input[type="text"]').each(function() {
            var current_placeholder = $(this).attr("placeholder");
            
            if(typeof current_placeholder != "undefined") {
                //init text if empty
                if($(this).val() == "") {
                    $(this).addClass("placeholder_text");
                    $(this).val(current_placeholder);
                }
                
                $(this).click(function() {
                    if($(this).val() == current_placeholder) {
                        $(this).removeClass("placeholder_text");
                        $(this).val("");
                    }
                });
                
                $(this).blur(function() {
                    if($(this).val() == "") {
                        $(this).addClass("placeholder_text");
                        $(this).val(current_placeholder);
                    }
                });
            }
        });
    }
}

function closePEItemDetails()
{
	resetPEButtonState();
	hideAllPETable();
}

function initPEEvent()
{
	resetPEButtonState();
	
	$(".encounter_item", $('#pe_section')).each(function()
	{
		$(this).click(function()
		{
			if($(this).attr("isopen") == "false")
			{
				resetPEClickEvent(this, true, false);
				$(this).attr("isopen", "true");
			}
			else
			{
				closePEItemDetails();
				$(this).attr("isopen", "false");
				$(this).addClass('ui-state-hover');
			}
			
			initPEWindowEvent(this);
		});
		
		initPEWindowEvent(this);
	});
}

function resetPE(data, saved_data)
{
	$('#pe_other_section').css("visibility", "hidden");
	$('#pe_section').html('');
			
	var html = "";
	var commentData = {};
	
	if (saved_data && saved_data.EncounterPhysicalExam && saved_data.EncounterPhysicalExam.comments) {
		commentData = JSON.parse(saved_data.EncounterPhysicalExam.comments);
	}
	
	
	for(var i in data.PhysicalExamBodySystem)
	{
		html += '<div class="pe_section_area">';
		html += '<div class="pe_title_area">'+data.PhysicalExamBodySystem[i].body_system.capitalize()+':</div>';
		html += '<div class="pe_item_area">';
		
		for(var a in data.PhysicalExamBodySystem[i].PhysicalExamElement)
		{
			html += '<div isopen="false" style="position:relative;" class="encounter_item ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" section="'+i+'" itemvalue="'+a+'">';
			html += '<span class="ui-button-icon-primary ui-icon ui-icon-carat-1-s"></span>';
			html += '<span class="ui-button-text" id="element_text_description_'+data.PhysicalExamBodySystem[i].PhysicalExamElement[a].element_id+'">';
			
			if(isElementHasData(data.PhysicalExamBodySystem[i].body_system_id, data.PhysicalExamBodySystem[i].PhysicalExamElement[a].element, data.PhysicalExamBodySystem[i].PhysicalExamElement[a].element_id))
			{
				html += '<strong>';
			}

			if(data.PhysicalExamBodySystem[i].PhysicalExamElement[a].element == '[text]')
			{
				if (textElementHasData(data.PhysicalExamBodySystem[i].PhysicalExamElement[a].element_id)) {
					html += '<strong>other</strong>';
				} else {
					html += 'other';
				}
			}
			else
			{
				html += data.PhysicalExamBodySystem[i].PhysicalExamElement[a].element;
			}
			
			if(isElementHasData(data.PhysicalExamBodySystem[i].body_system_id, data.PhysicalExamBodySystem[i].PhysicalExamElement[a].element, data.PhysicalExamBodySystem[i].PhysicalExamElement[a].element_id))
			{
				html += '</strong>';
			}
			
			html += '</span>';
			html += '</div>';	
		}
		
		html += '</div>';
		
		
		comment = commentData[data.PhysicalExamBodySystem[i].body_system_id] || '';
		comment = $('<div />').text(comment).html();
		comment = comment.replace(/\n/g, '<br />');
		html += '<div class="pe_comments_area"><span class="editable_field" id="pe_comment-' + data.PhysicalExamBodySystem[i].body_system_id +'">'+comment+'</span></div>';
		html += '</div>';
	}
	$('#pe_section').html(html);
	initPEEvent();
	$('#pe_other_section').css("visibility", "visible");
  $('#pe_image_list').find('.cloud-zoom').CloudZoom();   
	
	$('.editable_field').editable(pe_comment_link, {
		 type        : 'textarea',
		 width	     : 1080,
		 height      : 100,
		 oninitialized: function(){
			 window.initEditable.apply(this);
		 },
		 onblur    : (window.isDragonActive) ? function(){
				NUSAI_lastFocusedSpeechElementId = $(this).attr("id") + "_editable";

		 }  : function(form, value, settings) {
			 current_form = form;
			 window.setTimeout("submit_editable_data()", 1000);
		 },		 
		 submit      : '<span class="btn" style="margin: -5px 0px 10px 0px;">OK</span>',
		 
		 tooltip   : '(Click to Add Comment)',
			data: function(value, settings) {
					var retval = html_entity_decode(value.replace(/<br\s*(.*?)\/?>\n?/gi, '\n'));
					return retval;
			},			 
		 placeholder: '(Click to Add Comment)'		
	});
	
}

function loadPE()
{
	$("#imgPELoading").show();
	$.post(
		pe_get_list_link, 
		'', 
		function(data)
		{
			pe_data = data.pe_data;
			pe_saved_data = data.pe_saved_data;
			resetPE(pe_data, pe_saved_data);
			$("#imgPELoading").hide();
		},
		'json'
	);
}

$(document).ready(function()
{
	$('#wrapper').click(function()
	{
		if(!mouse_is_inside_pe)
		{
			hideAllSubPETable();
			hideAllPETable();
			resetPEButtonState();
		}
	});
	
	loadPE();
	
	pe_trigger_func = resetPEButtonState;
	
	$("#webcam_capture_area").dialog(
	{
		width: 730,
		height: 455,
		modal: true,
		resizable: false,
		autoOpen: false
	});
	
	getPEPhotoList();
});

function showPETemplates()
{
	if ($("#table_pe_templates").is(":hidden")) 
	{
		$('#table_pe_templates').slideDown("slow");
	} 
	else 
	{
		$("#table_pe_templates").slideUp("slow");
	}
}

function getPEPhotoList()
{
    
        $('#pe_image_processing').show();
	$.post(
		get_pe_photo_list_link, 
		'', 
		function(data)
		{
			$('#pe_image_list').html('');
			
			var 
                            html = '', 
                            $html,
                            fragment = document.createDocumentFragment(); 
                        
			if(data.length > 0)
			{
				for(var i = 0; i < data.length; i++)
				{
                                        html = '';
                                        
					html += '<div class="pe_image_item">';
                                        html += '<a  rel="position: \'top\', zoomWidth: \'300\', zoomHeight: \'300\'" class="cloud-zoom" href="'+ data[i].image +'">';
					html += '<img class="pe_img" src="'+data[i].image +'" height="145" width="228">';
                                        html += '</a>';
					html += '<img pe_image_id="'+data[i].physical_exam_image_id+'" imagefilename="'+ data[i].image +'" class="pe_img_del" src="'+pe_webroot_link+'img/del.png" alt="">';
                                        html += '<div class="pe_image_item_details"> <br /> <input type="text" name="pe_image_item_comment[' + data[i].physical_exam_image_id 
                                                +']" value="" class="pe_image_item_comment" rel="' + data[i].physical_exam_image_id + '"/><br /><label class="label_check_box"> <input type="checkbox" name="pe_image_item_in_summary[' + data[i].physical_exam_image_id + ']" value="1" rel="' + data[i].physical_exam_image_id + '" class="pe_image_item_in_summary" /> Print in Visit Summary </label></div>';
					html += '</div>';
                                        
                                        $html = $(html);
                                        
                                        $html.find('.pe_image_item_comment').val(data[i].comment);
                                        
                                        if (parseInt(data[i].display_flag_visit_summary, 10)) {
                                            $html.find('.pe_image_item_in_summary').attr('checked', 'checked');
                                        }
                                        
                                        fragment.appendChild($html.get(0));
				}
                                
                                
			}
			else
			{
				$html = $('<div class="pe_image_not_available">No Image Available</div>');
                                fragment.appendChild($html.get(0));
			}

			$('#pe_image_list')
                            .html('')[0]
                            .appendChild(fragment)

                        $('#pe_image_list')
                            .find('.pe_image_item_comment')
                                .blur(function(){
                                    var
                                        peImageId = $(this).attr('rel'),
                                        peImageComment = $(this).val();
                                        
                                    $.post(set_pe_photo_comment_link, {
                                        id: peImageId,
                                        comment: peImageComment
                                    });
                                })
                                .end()
                            .find('.pe_image_item_in_summary')
                                .click(function(){
                                    var 
                                        peImageId = $(this).attr('rel'),
                                        inSummary = $(this).is(':checked') ? 1: 0;
                                    
                                    $.post(set_pe_photo_in_summary_link, {
                                        id: peImageId,
                                        display_flag_visit_summary: inSummary
                                    });
                                    
                                });
                                
                                
                            
                        
                            
                        $('#pe_image_list:visible')
                            .find('.cloud-zoom').CloudZoom();

			$('.pe_img_del').click(function()
			{
				deletePEPhoto($(this).attr('pe_image_id'));
			});
                        
                        $('#pe_image_processing').hide();

		},
		'json'
	);
}

function addPEPhoto(filename)
{
	var formobj = $("<form></form>");
	formobj.append('<input name="data[image_file_name]" type="hidden" value="'+filename+'">');
	$('#pe_image_processing').show();
	$.post(
		add_pe_photo_link, 
		formobj.serialize(), 
		function(data)
		{
                        $('#pe_image_processing').hide();                    
			getPEPhotoList();
		},
		'json'
	);
}

function deletePEPhoto(pe_image_id)
{
	var formobj = $("<form></form>");
	formobj.append('<input name="data[pe_image_id]" type="hidden" value="'+pe_image_id+'">');
	
        $('#pe_image_processing').show();
	$.post(
		delete_pe_photo_link, 
		formobj.serialize(), 
		function(data)
		{
                        $('#pe_image_processing').hide();
			getPEPhotoList();
		},
		'json'
	);
}

function updateWebcamPhoto(response)
{
	var url = new String(response);
	var filename = url.substring(url.lastIndexOf('/')+1);
	
	addPEPhoto(filename);
	
	$("#webcam_capture_area").dialog("close");
}

String.prototype.capitalize = function(){
   return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){return p1+p2.toUpperCase();} );
  };
  
$( "#pe_templates" )
    .buttonset()
    .find(':radio')
    .change(function(){
        var id = $(this).attr('id');

        changePETemplate($(this).val(), $('label[for='+id+']').text());

    });


	var allowedFileTypes = '*.gif; *.jpg; *.jpeg; *.png;'; // photo file types
	
	$('#photo').uploadify(
	{
		'fileDesc'  : 'Image Files',
		'fileExt'   : allowedFileTypes, 
		'fileDataName' : 'file_input',
		'uploader'  : webroot + 'swf/uploadify.swf',
		'script'    : uploadify_script,
		'cancelImg' : webroot + 'img/cancel.png',
		'scriptData': {'data[path_index]' : 'temp'},
		'auto'      : true,
		'height'    : 35,
		'width'     : 100,
		'wmode'     : 'transparent',
		'hideButton': true,
		'onSelect'  : function(event, ID, fileObj) 
		{
                        $('#pe_image_processing').show();
			//$('#photo_img').attr('src', webroot + 'img/blank.png');
			//$('#photo_area_div').html("Uploading: 0%");
			return false;
		},
		'onProgress': function(event, ID, fileObj, data) 
		{
			//$('#photo_area_div').html("Uploading: "+data.percentage+"%");
			return true;
		},
		'onOpen'    : function(event, ID, fileObj) 
		{
			return true;
		},
		'onComplete': function(event, queueID, fileObj, response, data) 
		{
			$('#pe_image_processing').hide();
			var url = new String(response);
			var filename = url.substring(url.lastIndexOf('/')+1);
			$('#photo_val').val(filename);
			
			addPEPhoto(filename);
			
			return true;
		},
		'onError'   : function(event, ID, fileObj, errorObj) 
		{
			return true;
		}
	});

