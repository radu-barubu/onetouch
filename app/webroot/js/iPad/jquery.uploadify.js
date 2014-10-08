/*
		Uploadify for iPad app, based on jquery.uploadify v2.1.4
		
		Upload is multipart-mime POST with
		* URL == script
		* first form-data is { 'Filename', 'photo.jpg' }
		* second form-data is { 'folder', pagePath }
		* third form-data is { scriptDataName : scriptDataValue }
		* fourth form-data is the image, with name=fileDataName, filename="photo.jpg"
*/

if(jQuery){
	(function($){
		'use strict';
		$.extend($.fn,{
			uploadify:function(options) {
				$(this).each(function(){
					// Save settings in 'uploadify'
					var			onClickFn,
									button,
									pagePath,
									settings = $.extend({
					id              : $(this).attr('id'), // The ID of the object being Uploadified
					fileDataName    : 'Filedata', // The name of the file collection object in the backend upload script
					script          : 'uploadify.php', // The path to the uploadify backend upload script
					cancelImg       : 'cancel.png', // The path to the cancel image for the default file queue item container
					height          : 30, // The height of the flash button
					width           : 120, // The width of the flash button
					wmode           : 'opaque', // The wmode of the flash file
					uniqueId				: Math.floor(Math.random()*1000000000.),
					onSelect        : function() {}, // Function to run when a file is selected
					onProgress      : function() {}, // Function to run each time the upload progress is updated
					onAllComplete   : function() {}, // Function to run when all uploads are completed
					onError         : function() {}  // Function to run when an upload item returns an error
				}, options);
				$(this).data('uploadify',settings);

				// Get the folder for this page
				pagePath = location.pathname;
				pagePath = pagePath.split('/');
				pagePath.pop();
				pagePath = pagePath.join('/') + '/';
				
				// Attach a click handler to the select button
				onClickFn = function(event){
					var							imageSize = "", imageEl, height, width;
					if( typeof(settings.imageArea) === 'string' && (imageEl = $('#'+settings.imageArea)).length ){
						// Get image size
						height = parseInt(imageEl.css('height'));
						width = parseInt(imageEl.css('width'));
						imageSize = {height:height, width:width};
					}
					var							message = {
						action:'uploadify',
						id:settings.id,
						uniqueId:settings.uniqueId,
						top:button.offset().top,
						left:button.offset().left,
						width:button.width(),
						height:button.height(),
						pageOffset:$(window).scrollTop(),
						pageWidth:$(window).width(),
						postUrl:settings.script,							// URL to POST to
						folder:pagePath,											// second form-data value
						thirdNameValue:settings.scriptData,		// third form-data name/value pair
						fileDataName:settings.fileDataName,		// form-data name for image data field
						imageSize:imageSize};
					if( WebViewJavascriptBridge && typeof WebViewJavascriptBridge.sendMessage === 'function' ){
						WebViewJavascriptBridge.sendMessage(JSON.stringify(message));
					}
				};
				
				// Find associated button and set its click function
				button = $(this).closest('div').children('.btn');
				if( button.size() > 0 ){
					button.bind('click', onClickFn);
				} else {
					button = $('.btn', $(this).closest('div').parent().closest('div')[0]);
					button.bind('click', onClickFn);
				}
				
				// Hide this element
				$(this).hide();

				// Hide any webcam buttons
				$('img[title*="Webcam Capture"]').hide();
			});
		}
	});
}(jQuery));}

