<?php
$thisURL = $this->Session->webroot . $this->params['url']['url'];

$order_types = array(
	'in_house_work_labs' => 'Labs', 
	'in_house_work_radiology' => 'Radiology', 
	'in_house_work_procedures' => 'Procedure', 
	'in_house_work_immunizations' => 'Immunization', 
	'in_house_work_injections' => 'Injection', 
	'in_house_work_meds' => 'Meds', 
	'in_house_work_supplies' => 'Supplies'
);

$order_type = $order_types[$this->params['action']];

?>
<script language="javascript" type="text/javascript">
    var current_poc_url = '<?php echo $thisURL; ?>';
    
    function refreshPOCPage()
    {
        $(".tab_area").html('');
        $("#imgLoad").show();
        loadTab($('#poc_area'),current_poc_url);
    }
    
    $(document).ready(function()
    {
        var webroot = '<?php echo $this->webroot; ?>';
        var uploadify_script = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>';
        
        $('#poc_csv_upload').uploadify(
        {
            'fileDataName' : 'file_input',
            'uploader'  : webroot + 'swf/uploadify.swf',
            'script'    : uploadify_script,
            'cancelImg' : webroot + 'img/cancel.png',
            'scriptData': {'data[path_index]' : 'temp'},
            'auto'      : true,
            'height'    : 30,
            'width'     : 130,
            'fileExt'   : '*.csv',
            'fileDesc'  : 'CSV (Comma delimited) (*.csv)',
            'wmode'     : 'transparent',
            'hideButton': true,
            'onSelect'  : function(event, ID, fileObj) 
            {
                $('#poc_upload_progress').show();
                return false;
            },
            'onProgress': function(event, ID, fileObj, data) 
            {
                return true;
            },
            'onOpen'    : function(event, ID, fileObj) 
            {
                return true;
            },
            'onComplete': function(event, queueID, fileObj, response, data) 
            {
                var url = new String(response);
                var filename = url.substring(url.lastIndexOf('/')+1);
                
                getJSONDataByAjax(
                    '<?php echo $html->url(array('task' => 'process_csv')); ?>', 
                    {'data[filename]': filename, 'data[order_type]': '<?php echo $order_type; ?>'}, 
                    function()
					{	
					}, 
                    function(data)
                    {
                        $('#poc_upload_progress').hide();
                        location.reload(true);
                    }
                );
                
                return true;
            },
            'onError'   : function(event, ID, fileObj, errorObj) 
            {
                return true;
            }
        });
    });
</script>
<form id="frmDownloadTemplate" action="<?php echo $thisURL.'/task:download_template'; ?>" method="post">
	<input type="hidden" name="data[order_type]" value="<?php echo $order_type; ?>" />
</form>
<div style="width:auto; float: left;" removeonread="true">
    <div class="actions">
        <ul>
            <li><a href="javascript:void(0);" onclick="$('#frmDownloadTemplate').submit();">Download Template</a></li>
            <li>
                <div style="position: relative;">
                    <span class="btn poc_upload_btn">Import from CSV</span>
                    <div class="poc_upload_btn" style="position: absolute; top: 0px; left: 0px;">
                        <input id="poc_csv_upload" name="poc_csv_upload" type="file" />
                    </div>
                </div>
            </li>
            <li id="poc_upload_progress" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...', 'style' => 'float: left; margin-top: 7px;')); ?></li>
        </ul>
    </div>
</div>