// JS scripts for the iPad App

$ipad = {
	// All iPad-app-specific functions and object go here
	
	// Printing
	printButton:null,
	getPrintButton:function(){
		if( !$ipad.printButton || $ipad.printButton.length === 0 ){
			$ipad.printButton = $('a[onclick="printPage();"]');
		}
		if( !$ipad.printButton || $ipad.printButton.length === 0 ){
			$ipad.printButton = $('a[onclick="printPage();"]');
		}
		if( !$ipad.printButton || $ipad.printButton.length === 0 ){
			$ipad.printButton = $('a[onclick="printPage(false);"]'); 
		}
		if( !$ipad.printButton || $ipad.printButton.length === 0 ){
			$ipad.printButton = $('a[onclick="printPage(true);"]'); 
		}
		if( !$ipad.printButton || $ipad.printButton.length === 0 ){
			$ipad.printButton = $('a[href="javascript:window.print()"]'); 
		}
		if( !$ipad.printButton || $ipad.printButton.length === 0 ){
			$ipad.printButton = $('rect[title="Print the chart"]'); 
		}
		if( $ipad.printButton && $ipad.printButton.length === 0 ){
			$ipad.printButton = null;
		}
	},
	
	// Dragon speach-to-text
	dragonReady:function(){
		// Find all text input and add dragon button
		// Touching the dragon button will notify the app to bring up a dictation UI
		var inputs;
		$("img.dragonButton").remove();
		if( $('#iPadDragon').length === 0 ) return;
		inputs = $("textarea, input[type='text']");
		if( inputs.length === 0 ) return;
		
		// Mark text inputs as .dragonText:
		// None with the "noDragon" class
		// All with a width >= 250px
		// All with the "dragon" class 
		inputs.each(function(index,el){
			if( $(el).prop("tagName").toLowerCase() === 'input' &&
			    !$(el).hasClass("noDragon") &&
			    ( $(el).hasClass('dragon') ||
			      parseInt($(el).css('width'), 10) >= 250 ||
			      $(el).attr("placeholder") === 'other') )
				$(el).addClass('dragonText');
		});

		$("<span>").css({
			position: 'relative'
		}).insertBefore("textarea, .dragonText");

		$("textarea, .dragonText").each(function(index,el){
			if( $(el).parent()[0].tagName.toLowerCase() === 'span' ){
				$(el).parent().css({
					position: 'relative'
				});
				return;
			}
			$(el).appendTo($(el).prev());
		});

		$("<img>", {
			'class':"dragonButton",
			src:"/img/dragon/SpeechBubble_Flame_Normal.png",
			alt:"dragon",
			click:function(event){
				// Send dragon button click to iPad app
				var			button, input, message, topOffset = 0, leftOffset = 0, topi = 0, lefti = 0, p, i, frames;
				event.stopPropagation();
				button = (event && event.target) ? $(event.target) : null;
				if( !button || !button.length ) return;
				input = button.prev("textarea, .dragonText");
				if( !input || !input.length ) return;
				
				input.addClass('hasIpadDragon');
				
				if( !button.attr('#id') ){
					// Add an id to the button
					p = Math.floor(Math.random()*10000);
					while($("#"+p).length) p = Math.floor(Math.random()*10000);
					button.attr("id",""+p);
				}
				topOffset = input.offset().top;
				leftOffset = input.offset().left;
				if( self !== top ){
					// Walk up iframe containers
					p = self;
					while( p !== top ){
						frames = p.parent.document.getElementsByTagName("IFRAME");
						for (i = 0; i < frames.length; i++) {
							if (frames[i].contentWindow === p) break;
						}
						if( i < frames.length ){
							if( p.parent === top ){
								topi = $(frames[i]).offset().top;
								lefti = $(frames[i]).offset().left;
							} else {
								topOffset += $(frames[i]).offset().top;
								leftOffset += $(frames[i]).offset().left;
							}
						}
						p = p.parent;
					}
				}
				message = {
					action:'dragon',
					top:topOffset,
					left:leftOffset,
					topi:topi,
					lefti:lefti,
					width:input.width(),
					height:input.height(),
					pageOffset:$(top).scrollTop(),
					pageWidth:$(top).width(),
					buttonId:button.attr("id"),
					text:input.val(),
					type:input.get(0).tagName
				};
				top.WebViewJavascriptBridge.sendMessage(JSON.stringify(message));
			}
		}).css({
			position: 'absolute',
			right: '0px',
			zIndex: '2'
		}).insertAfter("textarea, .dragonText");

		$("textarea, .dragonText").each(function(index,el){
			var position = $(el).position();
			$(el).next().css('top', $(el).position().top + 'px');
			$(el).resize(function(){
				$(this).next().css('top', $(this).position().top + 'px');
			});
		});
		
		$("textarea, .dragonText").
			mouseup(function(){
				$(this).resize();
			}).
			mousemove(function(){
				$(this).resize();
			});
	},
	
	// Dragon input back into the web form
	dragonInput:function(args){
		// args[0] is the buttonID
		// args[1] is the text value
		// Also blurs the current textarea to take down a jeditable field
		var					button = $("#"+args[0]), input;
		if( button.length ){
			input = button.prev("textarea, .dragonText");
			if( input.length ){
				input.val(args[1]);
				input.removeClass('hasIpadDragon');
				input.blur();
			}
		}
	},
	
	// Ready function to be called after new html is loaded
	ready:function(){
		$ipad.getPrintButton();
		$ipad.dragonReady();
	},
	
	// Editable function to be called after editable changes state
	editable:function(){
		$ipad.getPrintButton();
		$ipad.dragonReady();
	},
	
	// Editable function to be called to check whether focus should be set
	editableSetFocus:function(){
		if( $('#iPadDragon').length === 0 ) return true;
		return false;
	}
};
$(document).ready(function(){
	$ipad.ready();
});

// Tabs
$(document).ready(function(){
	$("#tabs").tabs().bind('tabsload', function(){
		$ipad.ready();
	});
});

// Printing
window.print = function(event){
	var			message, topOffset = 0, leftOffset = 0, topi = 0, lefti = 0, p, i;
	if( !$ipad.printButton || $ipad.printButton.length === 0 ){
		$ipad.printButton = (event && event.target) ? $(event.target) : null;
	}
	$ipad.getPrintButton();
	if( $ipad.printButton ){
		topOffset = $ipad.printButton.offset().top;
		leftOffset = $ipad.printButton.offset().left;
		if( self !== top ){
			p = self;
			while( p !== top ){
				var frames = p.parent.document.getElementsByTagName("IFRAME");
				for (i = 0; i < frames.length; i++) {
					if (frames[i].contentWindow === p) break;
				}
				if( i < frames.length ){
					if( p.parent === top ){
						topi = $(frames[i]).offset().top;
						lefti = $(frames[i]).offset().left;
					} else {
						topOffset += $(frames[i]).offset().top;
						leftOffset += $(frames[i]).offset().left;
					}
				}
				p = p.parent;
			}
		}
	}
	message = {
		action:'print',
		top:topOffset,
		left:leftOffset,
		topi:topi,
		lefti:lefti,
		width:$ipad.printButton ? $ipad.printButton.width() : 0,
		height:$ipad.printButton ? $ipad.printButton.height() : 0,
		pageOffset:$(top).scrollTop(),
		pageWidth:$(top).width(),
		baseUrl:window.location.href.split('?')[0],
		html:$('html').html()
	};
	top.WebViewJavascriptBridge.sendMessage(JSON.stringify(message));
};
