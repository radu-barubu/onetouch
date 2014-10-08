/**
 * Admin
 *
 * for admin pages
 */
var Admin = {};

function scrollToTop()
{
	$("html, body").animate({ scrollTop: 0 }, "slow");	
}

function message(message, type, howlong)
{
	if(!howlong)  {
		howlong="6000";
	}
	var error_msg_obj = $('#message');

	if(!type) {
		type = 'notice';
	}
	
	if(!error_msg_obj) {
		$('html, body').animate( { scrollTop: 0 }, 'slow');
	} else {

		error_msg_obj.html(message);
		error_msg_obj.attr("class", "");
		error_msg_obj.addClass(type);
		
		$('html, body').animate( { scrollTop: 0 }, 'slow');
	}
	

	error_msg_obj.fadeIn("slow", function()
	{
		error_msg_obj.delay(howlong).slideUp("slow");
	});
}

function getJSONDataByAjax(url, post_data, before_callback, after_callback)
{
	before_callback();
	
	$.post(
		url, 
		post_data, 
		function(data)
		{
			after_callback(data);
		},
		'json'
	);
}

/**
 * Navigation
 *
 * @return void
 */
Admin.navigation = function() {
    $('ul.sf-menu').supersubs({
        minWidth: 12,
        maxWidth: 27,
        extraWidth: 1
    }).superfish({
        delay: 200,
        animation: {opacity:'show',height:'show'},
        speed: 'fast',
        autoArrows: true,
        dropShadows: false,
        disableHI: true
    });
}


/**
 * Forms
 *
 * @return void
 */
Admin.form = function() {
	/*
    $("input[type=text][rel], select[rel]").not(":hidden").each(function() {
        var sd = $(this).attr('rel');
        $(this).after("<span class=\"description\">"+sd+"</span>");
    });
	*/
    
    $("textarea[rel]").not(":hidden").each(function() {
        var sd = $(this).attr('rel');
        if (sd != '') {
            $(this).after("<br /><span class=\"description nospace\">"+sd+"</span>");
        }
    });
	
	$(".master_chk").click(function() {
		if($(this).is(':checked'))
		{
			$('.child_chk').attr('checked','checked');
		}
		else
		{
			$('.child_chk').removeAttr('checked');
		}
	});
	
	$('.child_chk').click( function() {
		if(!$(this).is(':checked'))
		{
			$('.master_chk').removeAttr('checked');
		}
	});
	
	$(".numeric_only").keydown(function(event) {
		//alert(event.keyCode);
		// Allow only backspace and delete
		if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9  || event.keyCode == 110  || event.keyCode == 190 ) {
			// let it happen, don't do anything
		}
		else {
			if(!((event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 48 && event.keyCode <= 57)))
			{
				event.preventDefault();	
			}
		}
	});
	
	Admin.extra();
	var objs = $('form:first *:input[type!=hidden][type!=file][readonly!=readonly]:first').not('.hasKeypad');
	
	if (!isTouchEnabled() && objs.length) {
		
		objs.each(function(){
			if ($(this).is(':visible') && $(this).css('visibility') !== 'hidden' && parseInt($(this).css('opacity'), 10)) {
				$(this).focus();
			}
			
		});
		
	}
	
	var obj_focus2 = $('input[autofocus="autofocus"]');
	
	if (!isTouchEnabled() && obj_focus2.length) {
		obj_focus2.each(function(){
			if ($(this).is(':visible') && $(this).css('visibility') !== 'hidden' && parseInt($(this).css('opacity'), 10)) {
				$(this).focus();
			}
			
		});
	}

	//console.log(objs);
}


/**
 * Extra stuff
 *
 * rounded corners, striped table rows, etc
 *
 * @return void
 */
Admin.extra = function() {
    $("table.listing tr:nth-child(odd)").not('.controller-row').addClass("striped");
	$("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
    $("div.message").addClass("notice");
    $('#loading p').addClass('ui-corner-bl ui-corner-br');
	
	$("table.listing tr td").not('table.listing tr td.ignore').not('table.listing tr:first td').each(function()
	{
		$(this).click(function()
		{
			var edit_url = $(this).parent().attr("editlink");
		
			if (typeof edit_url  != "undefined") 
			{
				$(this).parent().css("background", "#FDF5C8");
				window.location = edit_url;
			}
		});
		
		$(this).css("cursor", "pointer");
	});
	/*$(".datemask").mask("99/99/9999",{placeholder: "mm/dd/yyyy"});*/
	$(".ssn").mask("999-99-9999",{placeholder:"x"});
	$(".phone").mask("999-999-9999",{placeholder:"x"});
	
        
        $.mask.definitions['H']='[0-2]';
        $.mask.definitions['M']='[0-5]';
        
        $('.mask-time').mask('H9:M9', {
            placeholder: 'x',
            completed: function(){
                var 
                    time = this.val().split(':'),
                    hours = parseInt(time[0], 10),
                    minutes = parseInt(time[0], 10)
                ;

                if (hours > 24 || minutes > 59) {
                    this.val('');
                }

            }
        });
        
	//Add color picker
	$(".color_picker").jPicker({
		
		images: 
		{
			clientPath: basePath + 'img/jpicker/', /* Path to image files */
		}
	});
}

$.fn.outerHTML = function(s) {
  return (s)
  ? this.before(s).remove()
  : jQuery("<p>").append(this.eq(0).clone()).html();
}




function dateMe()
{
	$('.date').datepicker(
    {
    	changeMonth: true,
    	changeYear: true,
    	showOn: 'button',
    	buttonImage: '../img/date.png',
    	buttonImageOnly: true,
    	yearRange: 'c-90:c+10'
    });
    
    var next;
    var parent = $('.date').each(function(){ return $(this).parent(); });
    

    
    $('.date').each(function(i) {
    	
    	var position = $(this).position();
    	
    	
    	$(this).next().css('position','relative');
    	$(this).next().css('overflow','visible');
    	$(this).next().css('float','none');
    	$(this).next().css('left', $(this).width()+ 10 +'px');
    	$(this).next().css('width', '40px !important');
    	$(this).next().css('top', '-35px');
    	$(this).next().css('display','block');
    });
}


/**
 * Document ready
 *
 * @return void
 */
$(document).ready(function() {
    Admin.navigation();
    Admin.form();

    $('.tabs').tabs();
    $('a.tooltip').tipsy({gravity: 's', html: false});
    $('textarea').not('.content').elastic();
    
    
    dateMe();
    
    $(document.body)
        .bind('markRequiredFields', function(){
            // Find all form fields that have a class named "required"
            // Iterate through each of them
            $('input.required, select.required, textarea.required').filter(':visible').each(function(){

                var 
                    // Get the table cell adjacent to the cell containing the current field
                    $adjacent = $(this).closest('td').prev(), 
                    $label, 
                    $asterisk
                ;

                // Generate asterisk element to be inserted
                $asterisk = $('<span class="asterisk">*</span>');

                // There is an adjacent table cell found
                // it means that we have a form formatted using a table
                if ($adjacent.length) {

                    // Check first if there already is an asterisk element
                    if (!$adjacent.find('.asterisk').length) {

                        // None found check if there is a label element
                        $label =  $(this).find('label');

                        // If there is a label element
                        if ($label.length) {
                            // append the asterisk inside that label element
                            $label.append($asterisk);
                            return true;
                        } else {
                            // no label found, just append inside the table cell
                            $adjacent.append($asterisk);
                            return true;
                        }
                    } else {
                        // Asterisk already exist, continue
                        return true;
                    }
                }
                
                // See if inside a table, inside a cell, with adjacent label
                $adjacent = $(this).closest('table').closest('td').prev();

                // There is an adjacent table cell found
                // it means that we have a form formatted using a table
                if ($adjacent.length) {

                    // Check first if there already is an asterisk element
                    if (!$adjacent.find('.asterisk').length) {

                        // None found check if there is a label element
                        $label =  $(this).find('label');

                        // If there is a label element
                        if ($label.length) {
                            // append the asterisk inside that label element
                            $label.append($asterisk);
                            return true;
                        } else {
                            // no label found, just append inside the table cell
                            $adjacent.append($asterisk);
                            return true;
                        }
                    } else {
                        // Asterisk already exist, continue
                        return true;
                    }
                }
                
            });         
        })
        .trigger('markRequiredFields');
		
		// Add Sorting Arrows To Listing Headers
		$(document.body).bind('addSortingArrowsToListingHeaders', function()
		{
        	$('table.listing, table.listingDis, table.small_table').not('.hasSortingArrow').each(function() 
			{	
				$(this).find("th a").each(function() 
				{
					if($(this).hasClass('asc'))
						$(this).append(' <img src="'+basePath+'img/down_arrow_icon_small.png" />');
					else if($(this).hasClass('desc'))
						$(this).append(' <img src="'+basePath+'img/up_arrow_icon_small.png" />');
					//else
						//$(this).append(' <img src="'+basePath+'img/right_arrow_icon_small.png" />');
				});
				$(this).addClass('hasSortingArrow');
			});
		})
		.trigger('addSortingArrowsToListingHeaders');
		
		$(document.body).bind('goToSpecificPage', function()
		{
			$('input.specific_page_num').keypress(function(e) {
				if (e.which == 13) {
					$(this).parent().prev('.goto_btn').trigger('click');
				}
			});
		}).trigger('goToSpecificPage');
        
        $(document.body).ajaxStop(function(){            
           setTimeout("triggers_ajax_end();", 5);
 			$(this).trigger('goToSpecificPage'); 
        });
    
		$('#nav').find('a').click(function(){
			var url = $(this).attr('href');
			
			if (url.indexOf('/help/') === -1) {
				createCookie('last_menu', url);
			}
		});
	
});

function triggers_ajax_end()
{	
	$(document.body).trigger('markRequiredFields');
	$(document.body).trigger('addSortingArrowsToListingHeaders');
	if (isTouchEnabled()) {
		$('form').css('cursor', 'auto');
	}	
}

function uniqid()
{
	var newDate = new Date;
	return newDate.getTime();
}

String.prototype.capitalize = function(){
   return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
  };

// Default setting for validation plugin
// should ignore invisible/hidden fields
jQuery.validator.setDefaults({ 
    ignore: ':hidden' 
});

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

function isTouchEnabled(){
	return ("ontouchstart" in document.documentElement) ? true : false;
} 

(function(){

    if (isTouchEnabled()) {
        
        // If we are on a touch screen device,
        // chances are, it already have a way of selecting
        // dates via date type input fields.
        // We override the datepicker plugin
        // so it doesn't get triggered for date type fields.
        $.fn.datepicker = function(options){

                /* Verify an empty collection wasn't passed - Fixes #6976 */
                if ( !this.length ) {
                        return this;
                }


                /* Initialise the date picker. */
                if (!$.datepicker.initialized) {
                        $(document).mousedown($.datepicker._checkExternalClick).
                                find('body').append($.datepicker.dpDiv);
                        $.datepicker.initialized = true;
                }

                var otherArgs = Array.prototype.slice.call(arguments, 1);
                if (typeof options == 'string' && (options == 'isDisabled' || options == 'getDate' || options == 'widget'))
                        return $.datepicker['_' + options + 'Datepicker'].
                                apply($.datepicker, [this[0]].concat(otherArgs));
                if (options == 'option' && arguments.length == 2 && typeof arguments[1] == 'string')
                        return $.datepicker['_' + options + 'Datepicker'].
                                apply($.datepicker, [this[0]].concat(otherArgs));
                return this.each(function() {
                    
                        // Skip date type field
                        if ($(this).attr('type') == 'date') {
                            return true;
                        }
                    
                    
                        typeof options == 'string' ?
                                $.datepicker['_' + options + 'Datepicker'].
                                        apply($.datepicker, [this].concat(otherArgs)) :
                                $.datepicker._attachDatepicker(this, options);
                });
        };            
        
        
        
        $('img').live('click', function(){
            var width = $(this).width();
            
            // Check if large enough
            // We don't want to include icons
            if (width > 32) {
                window.location.href = $(this).attr('src');
            }
        })
    }
   
})();

/**
 * Parse date (account for Date parsing quirk in Safari)
 * see: http://stackoverflow.com/a/3085993
 */
Admin.parseDate = function parseDate(input, format) {
	format = format || 'yyyy-mm-dd'; // default format
	var parts = input.match(/(\d+)/g), 
			i = 0, fmt = {};
	// extract date-part indexes from the format
	format.replace(/(yyyy|dd|mm)/g, function(part) { fmt[part] = i++; });

	return new Date(parts[fmt['yyyy']], parts[fmt['mm']]-1, parts[fmt['dd']]);
}

Admin.getDateObject = function getDateObject(el){
	var 
		$dateField = $(el),
		dateObj = null
	;
		
	if ($dateField.attr('type') == 'date') {
		dateObj = $.trim($dateField.val());
		if (dateObj) {
			dateObj = Admin.parseDate($dateField.val());
			dateObj.setHours(0, 0, 0, 0);
		}	

	} else {
		dateObj = $dateField.datepicker('getDate');
	}		
	
	return	dateObj;
}

/**
 * Check if date from given input field is in the future
 * Used for datepicker fields and 
 * takes into consideration "date" input types
 * 
 * @params el Selector/jQuery object of the input field
 * @return boolean true if in the future. False otherwise
 * 
 */
Admin.dateIsInFuture = function dateIsInFuture(el) {
	
	var 
		dob = Admin.getDateObject(el),
		today = new Date()
	;

	today.setHours(0, 0, 0, 0);	
	
	return (dob && (dob.getTime() > today.getTime()) );
}

/**
 * Compute age/years given field
 */
Admin.getAge = function getAge(el){
	var 
		dob = Admin.getDateObject(el);
		
		if (dob === null) {
			return null;
		}
		
	var
		today = new Date(),
		age = today.getFullYear() - dob.getFullYear(),
		m = today.getMonth() - dob.getMonth()
	;

	if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
			age--;
	}

	return age;
}


/**
 * Additional validator method for validating date range
 */
jQuery.validator.addMethod("dateRange", function(value, element, params) { 
	var
		from = Admin.getDateObject(params.from),
		to = Admin.getDateObject(params.to)
	;
	
	if (!from || !to) {
		return true;
	}
	
  return this.optional(element) || (from.getTime() < to.getTime()); 
	
	
}, "Invalid Date range");

/**
 * Additional validator method for validating age range
 */
jQuery.validator.addMethod("ageRange", function(value, element, params) { 
	var
		from = parseInt($(params.from).val(), 10),
		to = parseInt($(params.to).val(), 10)
	;
	
	if (!from || !to) {
		return true;
	}
	
  return this.optional(element) || (from < to); 
	
	
}, "Invalid Age range");

/**
 * Additional validator method check if date is not later than current date
 */
jQuery.validator.addMethod("dob", function(value, element, params) { 
  return this.optional(element) || (!Admin.dateIsInFuture(element)); 
}, "DOB can not be later than current date");

/**
 * Additional validator method check for max age for dob
 */
jQuery.validator.addMethod("maxAge", function(value, element, params) {
	
	var
		maxAge = parseInt(params, 10);
	
  return this.optional(element) || Admin.getAge(element) == null || (Admin.getAge(element) <= maxAge); 
}, jQuery.format("Age cannot be over {0}"));	

function goto(id, obj)
{
	var par = $(obj).next();
	var num = $('.specific_page_num', par).val();
	var anchorObj = $('#'+id+num, par)[0];
	if(anchorObj)
		anchorObj.click();
}