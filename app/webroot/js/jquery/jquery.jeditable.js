/*
	11APR12 - made changes to support dragon dictation on iPad App
*/

/*
 * Jeditable - jQuery in place edit plugin
 *
 * Copyright (c) 2006-2009 Mika Tuupola, Dylan Verheul
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.appelsiini.net/projects/jeditable
 *
 * Based on editable by Dylan Verheul <dylan_at_dyve.net>:
 *    http://www.dyve.net/jquery/?editable
 *
 */

/**
  * Version 1.7.1
  *
  * ** means there is basic unit tests for this parameter. 
  *
  * @name  Jeditable
  * @type  jQuery
  * @param String  target             (POST) URL or function to send edited content to **
  * @param Hash    options            additional options 
  * @param String  options[method]    method to use to send edited content (POST or PUT) **
  * @param Function options[callback] Function to run after submitting edited content **
  * @param String  options[name]      POST parameter name of edited content
  * @param String  options[id]        POST parameter name of edited div id
  * @param Hash    options[submitdata] Extra parameters to send when submitting edited content.
  * @param String  options[type]      text, textarea or select (or any 3rd party input type) **
  * @param Integer options[rows]      number of rows if using textarea ** 
  * @param Integer options[cols]      number of columns if using textarea **
  * @param Mixed   options[height]    'auto', 'none' or height in pixels **
  * @param Mixed   options[width]     'auto', 'none' or width in pixels **
  * @param String  options[loadurl]   URL to fetch input content before editing **
  * @param String  options[loadtype]  Request type for load url. Should be GET or POST.
  * @param String  options[loadtext]  Text to display while loading external content.
  * @param Mixed   options[loaddata]  Extra parameters to pass when fetching content before editing.
  * @param Mixed   options[data]      Or content given as paramameter. String or function.**
  * @param String  options[indicator] indicator html to show when saving
  * @param String  options[tooltip]   optional tooltip text via title attribute **
  * @param String  options[event]     jQuery event such as 'click' of 'dblclick' **
  * @param String  options[submit]    submit button value, empty means no button **
  * @param String  options[cancel]    cancel button value, empty means no button **
  * @param String  options[cssclass]  CSS class to apply to input form. 'inherit' to copy from parent. **
  * @param String  options[style]     Style to apply to input form 'inherit' to copy from parent. **
  * @param String  options[select]    true or false, when true text is highlighted ??
  * @param String  options[placeholder] Placeholder text or html to insert when element is empty. **
	* @param String  options[activeClass] Element class if editable is active
  * @param String  options[onblur]    'cancel', 'submit', 'ignore' or function ??
  *             
  * @param Function options[onsubmit] function(settings, original) { ... } called before submit
  * @param Function options[onreset]  function(settings, original) { ... } called before reset
  * @param Function options[onerror]  function(settings, original, xhr) { ... } called on error
  * @param Boolean  options[useMacro]    Set true to display select box of available macros
  * @param Hash    options[ajaxoptions]  jQuery Ajax options. See docs.jquery.com.
  *             
  */

(function($) {

    $.fn.editable = function(target, options) {
            
        if ('disable' == target) {
            $(this).data('disabled.editable', true);
            return;
        }
        if ('enable' == target) {
            $(this).data('disabled.editable', false);
            return;
        }
        if ('destroy' == target) {
            $(this)
                .unbind($(this).data('event.editable'))
                .removeData('disabled.editable')
                .removeData('event.editable');
            return;
        }
        
        var settings = $.extend({}, $.fn.editable.defaults, {target:target}, options);
        
        /* setup some functions */
        var plugin   = $.editable.types[settings.type].plugin || function() { };
        var submit   = $.editable.types[settings.type].submit || function() { };
        var buttons  = $.editable.types[settings.type].buttons 
                    || $.editable.types['defaults'].buttons;
        var content  = $.editable.types[settings.type].content 
                    || $.editable.types['defaults'].content;
        var element  = $.editable.types[settings.type].element 
                    || $.editable.types['defaults'].element;
        var reset    = $.editable.types[settings.type].reset 
                    || $.editable.types['defaults'].reset;
        var callback = settings.callback || function() { };
        var onedit   = settings.onedit   || function() { }; 
		var oninitialized   = settings.oninitialized   || function() { }; 
        var onsubmit = settings.onsubmit || function() { };
        var onreset  = settings.onreset  || function() { };
        var retry    = $.editable.types[settings.type].retry 
                    || $.editable.types['defaults'].retry;
				var onerror  = settings.retryOnError ? retry  : (settings.onerror  || reset);

        /* show tooltip */
        if (settings.tooltip) {
            $(this).attr('title', settings.tooltip);
        }
        
        settings.autowidth  = 'auto' == settings.width;
        settings.autoheight = 'auto' == settings.height;
        
        return this.each(function() {
                        
            /* save this to self because this changes when scope changes */
            var self = this;
            
            self.statechange = function(){
							if( typeof settings.statechange !== undefined ){
								if( typeof window[settings.statechange] === 'object' &&
										window[settings.statechange] !== null &&
										typeof window[settings.statechange].editable === 'function' ){
									window[settings.statechange].editable();
								}
							}
						};
            self.setfocus = function(){
							if( typeof settings.statechange !== undefined ){
								if( typeof window[settings.statechange] === 'object' &&
										window[settings.statechange] !== null &&
										typeof window[settings.statechange].editableSetFocus === 'function' ){
									return window[settings.statechange].editableSetFocus();
								}
							}
							return true;
						};

            /* inlined block elements lose their width and height after first edit */
            /* save them for later use as workaround */
            var savedwidth  = $(self).width();
            var savedheight = $(self).height();
            
            /* save so it can be later used by $.editable('destroy') */
            $(this).data('event.editable', settings.event);
            
            /* if element is empty add something clickable (if requested) */
            if (!$.trim($(this).html())) {
                $(this).html(settings.placeholder);
            }
            
            $(this).bind(settings.event, function(e) {
                
                /* abort if disabled for this element */
                if (true === $(this).data('disabled.editable')) {
                    return;
                }
                
                /* prevent throwing an exeption if edit field is clicked again */
                if (self.editing) {
                    return;
                }
                
                /* abort if onedit hook returns false */
                if (false === onedit.apply(this, [settings, self])) {
                   return;
                }
                
                /* prevent default action and bubbling */
                e.preventDefault();
                e.stopPropagation();
                
                /* remove tooltip */
                if (settings.tooltip) {
                    $(self).removeAttr('title');
                }
                
                /* figure out how wide and tall we are, saved width and height */
                /* are workaround for http://dev.jquery.com/ticket/2190 */
                if (0 == $(self).width()) {
                    //$(self).css('visibility', 'hidden');
                    settings.width  = savedwidth;
                    settings.height = savedheight;
                } else {
                    if (settings.width != 'none') {
                        settings.width = 
                            settings.autowidth ? $(self).width()  : settings.width;
													
												savedwidth = settings.width;
                    }
                    if (settings.height != 'none') {
                        settings.height = 
                            settings.autoheight ? $(self).height() : settings.height;
													
												savedheight = settings.height;
                    }
                }
                //$(this).css('visibility', '');
                
                /* remove placeholder text, replace is here because of IE */
                if ($(this).html().toLowerCase().replace(/(;|")/g, '') == 
                    settings.placeholder.toLowerCase().replace(/(;|")/g, '')) {
                        $(this).html('');
                }
                                
                self.editing    = true;
								
								var div = $(self).prev();

								if (div.hasClass('retry-editable')) {
									div.remove();
									$(self).html(self.revert);
								}								
								
								
                self.revert     = $(self).html();
                $(self).html('');
								
                /* create the form object */
                var form = $('<form />');


								if ($.isFunction(settings.onformcreate)) {
									settings.onformcreate.apply(form, [settings, self]);
								}

                if (typeof NUSAI_isRecording === 'undefined') {
                        var NUSAI_isRecording;
                }
								// Stop any currently running dictation
								if (window.dragonOn && NUSAI_isRecording) {
									NUSAICtrl_stop(0);
									$('.' + NUSA_focusedElement).removeClass(NUSA_focusedElement);
								}								
								
                /* apply css or style or both */
                if (settings.cssclass) {
                    if ('inherit' == settings.cssclass) {
                        form.attr('class', $(self).attr('class'));
                    } else {
                        form.attr('class', settings.cssclass);
                    }
                }

                if (settings.style) {
                    if ('inherit' == settings.style) {
                        form.attr('style', $(self).attr('style'));
                        /* IE needs the second line or display wont be inherited */
                        form.css('display', $(self).css('display'));                
                    } else {
                        form.attr('style', settings.style);
                    }
                }

                /* add main input element to form and store it in input */
                var input = element.apply(form, [settings, self]);

                /* set input content via POST, GET, given data or existing value */
                var input_content;
                
                if (settings.loadurl) {
                    var t = setTimeout(function() {
                        input.disabled = true;
                        content.apply(form, [settings.loadtext, settings, self]);
                    }, 100);

                    var loaddata = {};
                    loaddata[settings.id] = self.id;
                    if ($.isFunction(settings.loaddata)) {
                        $.extend(loaddata, settings.loaddata.apply(self, [self.revert, settings]));
                    } else {
                        $.extend(loaddata, settings.loaddata);
                    }
                    $.ajax({
                       type : settings.loadtype,
                       url  : settings.loadurl,
                       data : loaddata,
                       async : false,
                       success: function(result) {
                          window.clearTimeout(t);
                          input_content = result;
                          input.disabled = false;
                       }
                    });
                } else if (settings.data) {
                    input_content = settings.data;
                    if ($.isFunction(settings.data)) {
                        input_content = settings.data.apply(self, [self.revert, settings]);
                    }
                } else {
                    input_content = self.revert; 
                }
                content.apply(form, [input_content, settings, self]);

                input.attr('name', settings.name);
        
                var lastCaret = null;
                
                input.blur(function(){
                    lastCaret = $(this).caret();
                });
                
         
                var macros = null;
                if ( /text/.test(settings.type) && settings.useMacro && MacrosArr && !$.isEmptyObject(MacrosArr)) {
                    macros = $('<select />').addClass('macros-select').append('<option value="">...</option>');
                    
                    macros.append($.map(MacrosArr, function(val, i) {
                     return $('<option />').attr('value', val).html(i);
                    }));
                    
                    macros
                        .change(function(){
                            var val = $(this).val();
                    
                            if (!val) {
                                return true;
                            }
                    
                            input.caret(lastCaret);
                            input.insertAtCaret(val);
                            input.caret(lastCaret.begin, lastCaret.begin + val.length);
                        })
                        .blur(function(){
                            setTimeout(function() {
                                if (!input.is(':focus')) {
                                    input.trigger('blur');
                                }
                            }, 200);                              
                        });
                    
                    form.append(macros);

                }
                
                /* add buttons to the form */
                buttons.apply(form, [settings, self]);
         
                /* add created form to self */
                $(self).append(form);
         
								if (settings.activeClass) {
									$(self).addClass(settings.activeClass);
								}
				 
                /* attach 3rd party plugin if requested */
                plugin.apply(form, [settings, self]);

                /* focus to first visible form element */
                if( self.setfocus() ){
                	$(':input:visible:enabled:first', form).focus();
                }

                /* highlight input contents when requested */
                if (settings.select) {
                    input.select();
                }
        
                /* discard changes if pressing esc */
                input.keydown(function(e) {
                    if (e.keyCode == 27) {
                        e.preventDefault();
                        //self.reset();
                        reset.apply(form, [settings, self]);
												if (settings.activeClass) {
													$(self).removeClass(settings.activeClass);
												}												
                    }
                });

                /* discard, submit or nothing with changes when clicking outside */
                /* do nothing is usable when navigating with tab */
                var t;
				
				if (!settings.submit) 
				{
					$('select', this).change(function() 
					{
						form.submit();
					});
				}
				
				
                if ('cancel' == settings.onblur) {
                    input.blur(function(e) {
                        /* prevent canceling if submit was clicked */
                        t = setTimeout(function() {
                            if (!macros || !macros.is(':focus')) {
                                reset.apply(form, [settings, self]);
                            } 														if (settings.activeClass) {
														}														
                        }, 500);
                    });
                } else if ('submit' == settings.onblur) {
                    input.blur(function(e) {
                        /* prevent double submit if submit was clicked */
                        t = setTimeout(function() {
                            if (!macros || !macros.is(':focus')) {
                                form.submit();
                            }                            
                        }, 200);
                    });
                } else if ($.isFunction(settings.onblur)) {
                    input.blur(function(e) {
                        
                        setTimeout(function() {
                            if (!macros || !macros.is(':focus')) {
                                settings.onblur.apply(self, [form, input.val(), settings]);
                            }
                        }, 200);                        
                    });
                } else {
                    input.blur(function(e) {
                      /* TODO: maybe something here */
                    });
                }

                form.submit(function(e) {

                    if (t) { 
                        clearTimeout(t);
                    }

                    /* do no submit */
                    e.preventDefault(); 
            
                    /* call before submit hook. */
                    /* if it returns false abort submitting */                    
                    if (false !== onsubmit.apply(form, [settings, self])) { 
                        /* custom inputs call before submit hook. */
                        /* if it returns false abort submitting */
                        if (false !== submit.apply(form, [settings, self])) { 

                          /* check if given target is function */
                          if ($.isFunction(settings.target)) {
                              var str = settings.target.apply(self, [input.val(), settings]);
                              $(self).html(str);
                              self.editing = false;
                              callback.apply(self, [self.innerHTML, settings]);
                              /* TODO: this is not dry */                              
                              if (!$.trim($(self).html())) {
                                  $(self).html(settings.placeholder);
                              }
                              self.statechange();
															if (settings.activeClass) {
																$(self).removeClass(settings.activeClass);
															}															
                          } else {
                              /* add edited content and id of edited element to POST */
                              var submitdata = {};
                              submitdata[settings.name] = input.val();
                              submitdata[settings.id] = self.id;
                              
							  /* add extra data to be POST:ed */
                              if ($.isFunction(settings.submitdata)) {
                                  $.extend(submitdata, settings.submitdata.apply(self, [self.revert, settings]));
                              } else {
                                  $.extend(submitdata, settings.submitdata);
                              }
							  

                              /* quick and dirty PUT support */
                              if ('PUT' == settings.method) {
                                  submitdata['_method'] = 'put';
                              }

                              /* show the saving indicator */
                              $(self).html(settings.indicator);
                              self.statechange();
							  
							  var ajax_submitdata = {"data[submitted][id]" : submitdata.id, "data[submitted][value]" : submitdata.value};
							  
							  if ($.isFunction(settings.submitdata))
							  {
								  
								  $.extend(ajax_submitdata, settings.submitdata.apply(self, [self.revert, settings]));
							  }
                              
                              /* defaults for ajaxoptions */
                              var ajaxoptions = {
                                  type    : 'POST',
                                  data    : ajax_submitdata,
                                  dataType: 'html',
                                  url     : settings.target,
																	complete: function(xhr, status){
																		if ($.isFunction(settings.ajaxSubmitStop)) {
																			settings.ajaxSubmitStop.apply(self, [xhr, status]);
																		}																		
																	},
                                  success : function(result, status) {
                                      if (ajaxoptions.dataType == 'html') {
                                        $(self).html(result);
                                      }
                                      self.editing = false;
                                      callback.apply(self, [result, settings]);
                                      if (!$.trim($(self).html())) {
                                          $(self).html(settings.placeholder);
                                      }
                                      self.statechange();
																			if (settings.activeClass) {
																				$(self).removeClass(settings.activeClass);
																			}																			
																			var div = $(self).prev();
																			
																			if (div.hasClass('retry-editable')) {
																				div.remove();
																			}
                                  },
                                  error   : function(xhr, status, error) {
																			if (settings.activeClass) {
																				$(self).removeClass(settings.activeClass);
																			}																		
																			//retrying = false;
																			if (status === 'abort') {
																				reset.apply(form, [settings, self, xhr, ajaxoptions]);
																			} else {
																				onerror.apply(form, [settings, self, xhr, ajaxoptions]);
																			}
                                  }
                              };
                              
                              /* override with what is given in settings.ajaxoptions */
                              $.extend(ajaxoptions, settings.ajaxoptions);   
															
															if ($.isFunction(settings.ajaxSubmitStart)) {
																settings.ajaxSubmitStart.apply(self,[ajaxoptions]);
															}
															
                              $.ajax(ajaxoptions);          
                              
                            }
                        }
                    }
                    
                    /* show tooltip again */
                    $(self).attr('title', settings.tooltip);
                    
                    return false;
                });
				
				self.statechange();
				if (false === oninitialized.apply(this, [settings, self])) {
                   return;
                }
            });
            
            /* privileged methods */
            this.reset = function(form) {
                /* prevent calling reset twice when blurring */
                if (this.editing) {
                    /* before reset hook, if it returns false abort reseting */
                    if (false !== onreset.apply(form, [settings, self])) { 
                        $(self).html(self.revert);
                        self.editing   = false;
                        if (!$.trim($(self).html())) {
                            $(self).html(settings.placeholder);
                        }
                        /* show tooltip again */
                        if (settings.tooltip) {
                            $(self).attr('title', settings.tooltip);                
                        }
                        self.statechange();
                    }                    
                }
            };

            this.retry = function(settings, self, xhr, ajaxoptions) {
							$(self).html(ajaxoptions.data['data[submitted][value]']);
							self.editing   = false;
							
							if (!$.trim($(self).html())) {
									$(self).html(settings.placeholder);
							}
							/* show tooltip again */
							if (settings.tooltip) {
									$(self).attr('title', settings.tooltip);                
							}
							self.statechange();						
							
							var div = $(self).prev();
							
							if (!div.hasClass('retry-editable')) {
								div = $('<div />')
									.addClass('retry-editable')
									.html('There was a problem sending the data. You can <a href="" class="retry-do-retry">try to submit the data again</a> or <a href="" class="retry-do-cancel">cancel to revert changes</a>.');
									

								div.find('a.retry-do-retry').click(function(evt){
									evt.preventDefault();
									div.remove();
									/* show the saving indicator */
									$(self).html(settings.indicator);
									self.statechange();									
									$.ajax(ajaxoptions);
								});
								
								div.find('a.retry-do-cancel').click(function(evt){
									evt.preventDefault();
									div.remove();
									$(self).html(self.revert);
									self.editing   = false;
									if (!$.trim($(self).html())) {
											$(self).html(settings.placeholder);
									}									
								});
								

								$(self).before(div);

							}
							
							
								
							
							
            };
						
						window.onbeforeunload = function() {
						
							return;
						}
        });

    };


    $.editable = {
        types: {
            defaults: {
                element : function(settings, original) {
                    var input = $('<input type="hidden"></input>');                
                    $(this).append(input);
                    return(input);
                },
                content : function(string, settings, original) {
                    $(':input:first', this).val(string);
                },
                reset : function(settings, original) {
                  original.reset(this);
                },
                retry : function(settings, original, xhr, ajaxoptions) {
                  original.retry.apply(this, [settings, original, xhr, ajaxoptions]);
                },								
                buttons : function(settings, original) {
                    var form = this;
                    if (settings.submit) {
                        /* if given html string use that */
                        if (settings.submit.match(/>$/)) {
                            var submit = $(settings.submit).click(function() {
                                if (submit.attr("type") != "submit") {
                                    form.submit();
                                }
                            });
                        /* otherwise use button with given string as text */
                        } else {
                            var submit = $('<button type="submit" />');
                            submit.html(settings.submit);                            
                        }
                        $(this).append(submit);
                    }
                    if (settings.cancel) {
                        /* if given html string use that */
                        if (settings.cancel.match(/>$/)) {
                            var cancel = $(settings.cancel);
                        /* otherwise use button with given string as text */
                        } else {
                            var cancel = $('<button type="cancel" />');
                            cancel.html(settings.cancel);
                        }
                        $(this).append(cancel);

                        $(cancel).click(function(event) {
                            //original.reset();
                            if ($.isFunction($.editable.types[settings.type].reset)) {
                                var reset = $.editable.types[settings.type].reset;                                                                
                            } else {
                                var reset = $.editable.types['defaults'].reset;                                
                            }
                            reset.apply(form, [settings, original]);
														if (settings.activeClass) {
															$(this).removeClass(settings.activeClass);
														}														
                            return false;
                        });
                    }
                }
            },
            text: {
                element : function(settings, original) {
                    var input = $('<input />');
                    if (settings.width  != 'none') { input.width(settings.width);  }
                    if (settings.height != 'none') { input.height(settings.height); }
                    /* https://bugzilla.mozilla.org/show_bug.cgi?id=236791 */
                    //input[0].setAttribute('autocomplete','off');
                    input.attr('autocomplete','off');
                    $(this).append(input);
                    return(input);
                }
            },
            textarea: {
                element : function(settings, original) {
                    var textarea = $('<textarea />');
					
										textarea.attr("id", $(original).attr("id") + "_editable");
					
                    if (settings.rows) {
                        textarea.attr('rows', settings.rows);
                    } else if (settings.height != "none") {
                        textarea.height(settings.height);
                    }
                    if (settings.cols) {
                        textarea.attr('cols', settings.cols);
                    } else if (settings.width != "none") {
                        textarea.width(settings.width);
                    }
                    $(this).append(textarea);
                    return(textarea);
                }
            },
            select: {
               element : function(settings, original) {
                    var select = $('<select />');
                    $(this).append(select);
                    return(select);
                },
                content : function(data, settings, original) {
                    /* If it is string assume it is json. */
                    if (String == data.constructor) {      
                        eval ('var json = ' + data);
                    } else {
                    /* Otherwise assume it is a hash already. */
                        var json = data;
                    }
                    for (var key in json) {
                        if (!json.hasOwnProperty(key)) {
                            continue;
                        }
                        if ('selected' == key) {
                            continue;
                        } 
                        var option = $('<option />').val(key).append(json[key]);
                        $('select', this).append(option);    
                    }                    
                    /* Loop option again to set selected. IE needed this... */ 
                    $('select', this).children().each(function() {
                        if ($(this).val() == json['selected'] || 
                            $(this).text() == $.trim(original.revert)) {
                                $(this).attr('selected', 'selected');
                        }
                    });
                }
            }
        },

        /* Add new input type */
        addInputType: function(name, input) {
            $.editable.types[name] = input;
        }
    };

    // publicly accessible defaults
    $.fn.editable.defaults = {
        name       : 'value',
        id         : 'id',
        type       : 'text',
        width      : 'auto',
        height     : 'auto',
        event      : 'click.editable',
        onblur     : 'cancel',
        loadtype   : 'GET',
        loadtext   : 'Loading...',
        placeholder: 'Click to edit',
        activeClass: 'editable-active',
        useMacro    : false,
        loaddata   : {},
        submitdata : {},
        ajaxoptions: {},
        statechange: '$ipad',
				// If retryOnError is true, it will prompt user
				// options to resubmit or cancel the failed data submission
				// instead of doing the onerror routine
				retryOnError: true,
				ajaxSubmitStart: null,
				ajaxSubmitStop: null
    };

})(jQuery);
