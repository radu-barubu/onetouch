<script language="javascript" type="text/javascript">
$(function()
{

  	$(".progressbar").progressbar({value: 0});
	
	$('#file_upload').uploadify(
	{
		'buttonClass': 'btn uploadify_bt',
		'buttonText' : 'Select Document...',
		'width' : 145,
		'swf'  : '../uploadify/uploadify.swf',
		'checkExisting' :  false,// the other option is - '<?php echo $html->url(array('controller' => 'file_handler', 'action' => 'file_exists','fax')); ?>',
		'uploader'	: '<?php echo $html->url(array('controller' => 'file_handler', 'action' => 'fax', 'session_id' => $session->id())); ?>',
		'cancelImage' : '../img/cancel.png',
		'auto'	  : true,
  		'fileTypeDesc' : 'Fax Document',
  		'fileTypeExts' : '*.pdf;*.docx;*.doc;*.jpg;*.png',
  		'queueID' : 'custom-queue',
  		'removeCompleted' : true,
  		'onInit': function(fileObj)
  		{
  		},
		'onDialogOpen'	: function()
		{
			$(window).css("cursor", "wait");
		},
		'onSelect'  : function(fileObj)
		{
			
			$('.file_upload_desc').html(fileObj.name);
			
			$('.ui-progressbar-value').css("visibility", "hidden");
			$('.progressbar').progressbar("value", 0);
			
			$('.file_upload_desc').css('border', 'none');
			$('.file_upload_desc').css('background', 'none');
			
			$('#attachment').val(fileObj.name);
			
			return true;
		},
		'onUploadStart': function(fileObj)
		{
		
			$(".ui-progressbar-value").css("visibility", "visible");
			
			$(".progressbar").progressbar("value", 5);
		
		},
		'onUploadProgress': function(fileObj, fileBytesLoaded, fileTotalBytes) 
		{
			var percentage = Math.round(fileBytesLoaded / fileTotalBytes * 100);
			
			setTimeout(function() {
				$(".progressbar").progressbar("value", percentage);
			},200);
			
		
		},
		'onUploadSuccess': function(fileObj, response) {
			if(response) {
				response = eval("("+response+")");
				$('#fax_id').val(response.fax_id);
				$('#filename').val(response.filename);
				
			}
		},
		'onUploadComplete': function(fileObj,response) 
		{
			
			if(response.errorMsg) {
			}
			
			$('.file_upload_desc').html(fileObj.name+ " - ready");
		},
		'onUploadError'   : function(event, ID, fileObj, errorObj) 
		{
		},
		'onError': function (event, queueID ,fileObj, errorObj) {
			var msg;
			if (errorObj.status == 404) {
				alert('Could not find upload script. Use a path relative to: '+'<?php echo getcwd(); ?>');
				msg = 'Could not find upload script.';
			} else if (errorObj.type === "HTTP") {
				msg = errorObj.type+": "+errorObj.status;
			} else if (errorObj.type ==="File Size") {
				msg = fileObj.name+'<br>'+errorObj.type+' Limit: '+Math.round(errorObj.sizeLimit/1024)+'KB';
			} else {
				msg = errorObj.type+": "+errorObj.text;
				alert(msg);
				$("#fileUpload" + queueID).fadeOut(250, function() { $("#fileUpload" + queueID).remove()});
				return false;
			}
      }
	});
});
</script>