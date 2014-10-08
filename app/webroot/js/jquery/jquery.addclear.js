// Author: Stephen Korecky
// Website: http://stephenkorecky.com
// Plugin Website: http://github.com/skorecky/Add-Clear

(function($){  
	$.fn.extend({   
		addClear: function(options) {  
			var options =  $.extend({  
				closeImage: "/img/clear.png",
				top: 0,
				right: 4,
				returnFocus: true,
				showOnLoad: false,
				onClear: null
				}, options);
                                
                                // Dear plugin developer,
                                // Check if you are actually working on something
                                // - rolan
                                if (!this.length) {
                                    return this;
                                }

				$(this).wrap("<span style='position:relative;' class='add-clear-span'>");
				$(".add-clear-span").append("<a href='#clear'></a>");
				$("a[href='#clear']").css({
					'background' : 'transparent url('+options.closeImage+') 0 0 no-repeat', 
					'display' : 'none',
					'height' : '19px',
					'overflow' : 'hidden',
					'position' : 'absolute',
					'right' : options.right+'px',
					'text-indent' : '-9999px',
					'top' : options.top+'px',
					'width' : '19px'
					}, this);
					
					if($(this).val().length >= 1 && options.showOnLoad === true) {
						$(this).siblings("a[href='#clear']").show();
					}
					
					$(this).keyup(function() {
						if($(this).val().length >= 1) {
							$(this).siblings("a[href='#clear']").show();
						} else {
							$(this).siblings("a[href='#clear']").hide();
						}
					});

					$("a[href='#clear']").click(function(){
						$(this).siblings("input").val("");
						$(this).hide();
						if(options.returnFocus === true){
							$(this).siblings("input").focus();
						}
						if (options.onClear){
              options.onClear($(this).siblings("input"));
            }
						return false;
					});
					return this; 
				}  
			});  
			})(jQuery);