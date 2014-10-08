<?php
	$value_real = "";
	if(strlen($value) > 0)
	{
		$pos = strpos($value, '_') + 1;
		$value_real = substr($value, $pos);
	}
	
	$onselect = (isset($onselect)) ? $onselect : "null";
	$onprogress = (isset($onprogress)) ? $onprogress : "null";
	$oncomplete = (isset($oncomplete)) ? $oncomplete : "null";
	
	if (!isset($fileExt)) {
		$fileExt = '*.pdf;*.docx;*.doc;*.jpg;*.png;*.jpeg;*.rtf;*.txt;*.xls;*.xlsx';
	}
	
	if (!isset($fileDesc)) {
		$fileDesc = 'Documents and Images';
	}
	
?>

<table cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <div id="<?php echo $id; ?>_file_upload_area" style="position: relative; width: 214px; height: auto !important">
                <div id="<?php echo $id; ?>_file_upload_desc" class="file_upload_desc" style="position: absolute; top: 0px; height: 19px; width: 200px; text-align: left; padding: 5px; overflow: hidden;"><?php echo $value_real; ?></div>
                <div id="<?php echo $id; ?>_progressbar" style="-moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"></div>
                <div style="position: absolute; top: 1px; right: -125px;" removeonread="true">
                    <div style="position: relative;"> <span class="btn" style="float: left; margin-top: -2px;">Select File...</span>
                        <div style="position: absolute; top: 0px; left: 0px;">
                            <input id="<?php echo $id; ?>_file_upload" name="<?php echo $id; ?>_file_upload" type="file" />
                        </div>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td style="padding-top: 10px;">
        	<input type="hidden" name="data[<?php echo $model; ?>][<?php echo $id; ?>_is_selected]" id="<?php echo $id; ?>_is_selected" value="false" />
            <input type="hidden" name="data[<?php echo $model; ?>][<?php echo $id; ?>_is_uploaded]" id="<?php echo $id; ?>_is_uploaded" value="false" />
            <input type="hidden" name="data[<?php echo $model; ?>][<?php echo $id; ?>]" id="<?php echo $id; ?>" value="<?php echo $value; ?>">
            <span id="<?php echo $id; ?>_error"></span>
        </td>
    </tr>
</table>
<script language="javascript" type="text/javascript">
	var <?php echo $id; ?>_onselect = <?php echo $onselect; ?>;
	var <?php echo $id; ?>_onprogress = <?php echo $onprogress; ?>;
	var <?php echo $id; ?>_oncomplete = <?php echo $oncomplete; ?>;
	
    $(document).ready(function() 
    {
        $("#<?php echo $id; ?>_progressbar").progressbar({value: 0});
        
        $('#<?php echo $id; ?>_file_upload').uploadify(
        {
            'fileDataName' : 'file_input',
            'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
            'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
            'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
            'scriptData': {'data[path_index]' : 'temp'},
            'auto'      : true,
            'height'    : 35,
            'width'     : 192,
			<?php if(isset($fileExt) && isset($fileDesc)): ?>
			'fileExt'   : '<?php echo $fileExt; ?>',
            'fileDesc'  : '<?php echo $fileDesc; ?>',
			<?php endif; ?>
            'wmode'     : 'transparent',
            'hideButton': true,
            'onSelect'  : function(event, ID, fileObj) 
            {
                $('#<?php echo $id; ?>_is_uploaded').val("false");
				$('#<?php echo $id; ?>_is_selected').val("true");
                
                $('#<?php echo $id; ?>_file_upload_desc').html(fileObj.name);
                $(".ui-progressbar-value", $("#<?php echo $id; ?>_progressbar")).css("visibility", "hidden");
                $("#<?php echo $id; ?>_progressbar").progressbar("value", 0);
                
                $("#<?php echo $id; ?>_error").html("");
                $("#<?php echo $id; ?>_file_upload_desc").css("border", "none");
                $("#<?php echo $id; ?>_file_upload_desc").css("background", "none");
				
				if(<?php echo $id; ?>_onselect != null)
				{
					<?php echo $id; ?>_onselect(event, ID, fileObj);
				}

                return false;
            },
            'onProgress': function(event, ID, fileObj, data) 
            {
                $(".ui-progressbar-value", $("#<?php echo $id; ?>_progressbar")).css("visibility", "visible");
                $("#<?php echo $id; ?>_progressbar").progressbar("value", data.percentage);
				
				if(<?php echo $id; ?>_onprogress != null)
				{
					<?php echo $id; ?>_onprogress(event, ID, fileObj, data);
				}

                return true;
            },
            'onComplete': function(event, queueID, fileObj, response, data) 
            {
                $('#<?php echo $id; ?>_is_uploaded').val("true");
                var url = new String(response);
                var filename = url.substring(url.lastIndexOf('/')+1);
                $('#<?php echo $id; ?>').val(filename);
                
                $(".ui-progressbar-value", $("#<?php echo $id; ?>_progressbar")).css("visibility", "hidden");
                $("#<?php echo $id; ?>_progressbar").progressbar("value", 0);
                
                $('#<?php echo $id; ?>_is_uploaded').val("true");
				
				if(<?php echo $id; ?>_oncomplete != null)
				{
					<?php echo $id; ?>_oncomplete(event, queueID, fileObj, response, data);
				}

                return true;
            },
            'onError' : function(event, ID, fileObj, errorObj) 
            {
            }
        });
    });
</script>