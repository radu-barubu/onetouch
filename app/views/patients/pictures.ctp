<?php echo $this->Html->css(array('sections/dashboard.css')); ?>
<script language="javascript" type="text/javascript">
	<?php $encounter_id = 0; ?> 
	var page_access = '<?php echo $page_access; ?>';
	var save_pe_add_link = '<?php echo $this->Session->webroot; ?>encounters/pe/encounter_id:<?php echo $encounter_id; ?>/task:add/';
	var save_pe_text_link = '<?php echo $this->Session->webroot; ?>encounters/pe/encounter_id:<?php echo $encounter_id; ?>/task:add_text/';
	var pe_get_list_link = '<?php echo $this->Session->webroot; ?>encounters/pe/encounter_id:<?php echo $encounter_id; ?>/task:get_list/';
	var get_pe_photo_list_link = '<?php echo $html->url(array("controller" => "encounters", "action" => "pe", "encounter_id" => $encounter_id, "task" => "load_image")); ?>';
	var set_pe_photo_comment_link = '<?php echo $html->url(array("controller" => "encounters", "action" => "pe", "encounter_id" => $encounter_id, "task" => "set_image_comment")); ?>';
	var set_pe_photo_in_summary_link = '<?php echo $html->url(array("controller" => "encounters", "action" => "pe", "encounter_id" => $encounter_id, "task" => "set_image_in_summary")); ?>';
	var pe_photo_list_dir = '<?php echo $url_abs_paths['encounters']; ?>';
	var pe_webroot_link = '<?php echo $this->Session->webroot; ?>';
	var add_pe_photo_link = '<?php echo $html->url(array("controller" => "patients", "action" => "pictures", "patient_id" => $patient_id, "task" => "save_image", 'with_path' => 1)); ?>';
	var delete_pe_photo_link = '<?php echo $html->url(array("controller" => "encounters", "action" => "pe", "encounter_id" => $encounter_id, "task" => "delete_image")); ?>';
	var uploadify_script = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>';
	var webroot = '<?php echo $this->webroot; ?>';
	var allowedFileTypes = '*.gif; *.jpg; *.jpeg; *.png;'; // photo file types

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
				$('#picture_search_results').trigger('search', '');
			},
			'json'
		);
	}
	
	// added function for post data to delete image on patient controller via ajax
	function delPEPhoto(url, id, ul)
	{
		var formobj = $("<form></form>");
		formobj.append('<input name="data[image_file_id]" type="hidden" value="'+id+'">');
		formobj.append('<input name="data[image_file_p]" type="hidden" value="'+ul+'">');
		$('#pe_image_processing').show();
		$.post(
			url,
			formobj.serialize(), 
			function(data)
			{
				$('#pe_image_processing').hide();                    
				$('#picture_search_results').trigger('search', '');
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


	$("#webcam_capture_area").dialog(
	{
		width: 730,
		height: 455,
		modal: true,
		resizable: false,
		autoOpen: false
	});




</script>
<div id="webcam_capture_area" title="Webcam Capture">
	<?php

	$url_port = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':'.$_SERVER['SERVER_PORT'];
	$url_pre = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$url_port : "http://".$_SERVER['SERVER_NAME'].$url_port;
	
	?>
	<script language="javascript" type="text/javascript">
		function webcam_callback(data)
		{
			updateWebcamPhoto(data);
		}
	</script>
	<div id="flashArea" class="flashArea" style="height:370; margin: 0px; padding: 0px;"></div>
	<script type="text/javascript">
	var flashvars = {
	  save_file: "<?php echo $url_pre . $html->url(array('controller' => 'patients', 'action' => 'webcam_save')); ?>",
	  parentFunction: "webcam_callback",
	  snap_sound: "<?php echo $this->Session->webroot; ?>sound/camera_sound.mp3",
	  save_sound: "<?php echo $this->Session->webroot; ?>sound/save_sound.mp3"
	};
	var params = {
	  scale: "noscale",
	  wmode: "window",
	  allowFullScreen: "true"
	};
	var attributes = {};
	swfobject.embedSWF("<?php echo $this->Session->webroot; ?>swf/webcam.swf", "flashArea", "700", "370", "9.0.0", "<?php echo $this->Session->webroot; ?>swf/expressInstall.swf", flashvars, params, attributes);
	</script>
	<object width="0" height="0" type="application/x-shockwave-flash">
	<p>Please <a href="http://get.adobe.com/flashplayer/" target=_blank>install Adobe Flash</a> to use this Web Cam feature</p>
	</object>
</div>
<div style="overflow: hidden">
    <form id="dummy-form">
        <table border="0" cellspacing="0" cellpadding="0" class="form" style="width: 100%">
            <tr>
                <td style="padding-right: 10px; width: 100px;">Find Picture:</td>
                <td style="padding-right: 10px;"><input name="search_term" type="text" id="search_term" autofocus="autofocus" size="40" /></td>
								<td style="width: 150px;">
									
									<div style="height: auto; margin-bottom: 20px;">
													<div class="photo_upload_control_area">
															<div class="btn_area">
																			<span class="btn">Add Photo...</span>
																			<img title="Webcam Capture" onclick="$('#webcam_capture_area').dialog('open');" src="<?php echo $this->Session->webroot . 'img/webcam.png'; ?>" width="16" height="16" />
															</div>
															<div class="uploadfield">
																	<input id="photo" name="photo" type="file" />
																	<input type="hidden" name="data[PatientDemographic][patient_photo]" id="photo_val" value="<?php //echo $patient_photo; ?>" />
															</div>
													</div>
									</div>									
									
									
								</td>
									
            </tr>
        </table>
    </form>    
    <br />
    <br />
    <div id="p_loading" class="center"><?php echo $html->image('ajax_loader.gif', array('alt' => 'Loading...')); ?></div>
    <div id="picture_search_results">

    </div>
</div>

<script type="text/javascript">
$(function(){
    initCurrentTabEvents('pe_images_area');

    (function(){
        var 
            searchUrl = '<?php echo $this->Html->url(array('controller' => 'patients', 'action' => 'picture_search', 'patient_id' => $patient_id)); ?>',
            $searchTerm = $('#search_term'),
            $picSearch = $('#picture_search_results'),
            $loader = $('#p_loading'),
            xhr = null;
        
            $searchTerm.keyup(function(){
                var term = $(this).val();
                $picSearch.trigger('search', term);
            });
        
            $picSearch
                .bind('search', function(evt, term) {
                    
                    if (xhr) {
                        xhr.abort();
                    }
                    
                    $picSearch.empty();
                    $loader.show();
                    
                    xhr = $.get(searchUrl + '/term:'+term, function(result){
                        $loader.hide();
                        $picSearch.html(result);
                    });
                    
                })
                .trigger('search', '');
                
            $picSearch.delegate('.paging a', 'click', function(evt){
                evt.preventDefault();
                var url = $(this).attr('href');
                
                    $picSearch.empty();
                    $loader.show();
                    
                    xhr = $.get(url, function(result){
                        $loader.hide();
                        $picSearch.html(result);
                    });
                
            })
    })();


    
});    
    
</script>
