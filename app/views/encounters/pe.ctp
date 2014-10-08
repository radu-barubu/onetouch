<?php

$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$emhelper = $session->Read('UserAccount.emhelper');
$page_access = $this->QuickAcl->getAccessType("encounters", "pe");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

$paths['patient_encounter_img'] = $paths['patients'] . $patient_id . DS .'images' . DS . $encounter_id . DS;
$url_abs_paths['patient_encounter_img'] = UploadSettings::toURL($paths['patient_encounter_img']);

UploadSettings::createIfNotExists($paths['patient_encounter_img']);
$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$dragonVoiceStatus = $session->Read('UserAccount.dragon_voice_status');
?>
<script type="text/javascript">
$.fn.editable.defaults.useMacro = true;   
window.doDragon = function doDragon() {
	<?php echo $this->element('dragon_voice'); ?>	
}

window.isDragonActive = <?php echo( !empty($dragonVoiceStatus)) ? 'true' : 'false'; ?>

</script>
<style>
   .pe_section_area {  -webkit-user-select: none; //disable copy/paste }
</style>
<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 16)); ?>
<script language="javascript" type="text/javascript">
	var page_access = '<?php echo $page_access; ?>';
	var pe_comment_link = '<?php echo $this->Session->webroot; ?>encounters/pe/encounter_id:<?php echo $encounter_id; ?>/task:save_comment/';
	var save_pe_add_link = '<?php echo $this->Session->webroot; ?>encounters/pe/encounter_id:<?php echo $encounter_id; ?>/task:add/';
	var save_pe_text_link = '<?php echo $this->Session->webroot; ?>encounters/pe/encounter_id:<?php echo $encounter_id; ?>/task:add_text/';
	var delete_pe_text_link = '<?php echo $this->Session->webroot; ?>encounters/pe/encounter_id:<?php echo $encounter_id; ?>/task:delete_text/';
	var pe_get_list_link = '<?php echo $this->Session->webroot; ?>encounters/pe/encounter_id:<?php echo $encounter_id; ?>/task:get_list/';
	var get_pe_photo_list_link = '<?php echo $html->url(array("controller" => "encounters", "action" => "pe", "encounter_id" => $encounter_id, "task" => "load_image", 'with_path' => 1)); ?>';
	var set_pe_photo_comment_link = '<?php echo $html->url(array("controller" => "encounters", "action" => "pe", "encounter_id" => $encounter_id, "task" => "set_image_comment")); ?>';
	var set_pe_photo_in_summary_link = '<?php echo $html->url(array("controller" => "encounters", "action" => "pe", "encounter_id" => $encounter_id, "task" => "set_image_in_summary")); ?>';
	var pe_photo_list_dir = '<?php echo $url_abs_paths['encounters']; ?>';
	var pe_webroot_link = '<?php echo $this->Session->webroot; ?>';
	var add_pe_photo_link = '<?php echo $html->url(array("controller" => "encounters", "action" => "pe", "encounter_id" => $encounter_id, "task" => "save_image")); ?>';
	var delete_pe_photo_link = '<?php echo $html->url(array("controller" => "encounters", "action" => "pe", "encounter_id" => $encounter_id, "task" => "delete_image")); ?>';
        var uploadify_script = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>';
        var webroot = '<?php echo $this->webroot; ?>';
	var ajaxIndicator = '<?php echo $smallAjaxSwirl; ?>';
	function showNow(elem_id)
	{		
		var currentTime = new Date()
		var hours = currentTime.getHours()
		var minutes = currentTime.getMinutes()
		var suffix = "AM";
		lhours = hours;
		if (lhours < 10)
		lhours = "0" + lhours;
		if (hours >= 12) {
			suffix = "PM";
			hours = hours - 12;
		}
		if (hours == 0) {
			hours = 12;
		}
		if (hours < 10)
			hours = "0" + hours
		if (minutes < 10)
			minutes = "0" + minutes
		var time = hours + ":" + minutes + ' ' + suffix;
		var elem_val = $('#'+elem_id).val()+' @';	
		<?php if ($general_timeformat == 24): ?>
		$('#'+elem_id).val(elem_val+lhours + ":" + minutes);
		<?php else: ?> 
		$('#'+elem_id).val(elem_val+time);	
		<?php endif;?>
		$('#'+elem_id).focus();		
	}
</script>
<?php if ($emhelper): ?>
        <!-- E&M Helper -->
<div class='em_widget' style="width:400px;top:50px;right:3px;">
    <div id='em_headr'>E&M Helper</div>
    <i>For a potential Level 4 or 5, you need 5-7 Systems (12 bullets) on Established visits, 9+ Systems (2 bullets per System) on New visits.</i>

</div>
  <?php endif; ?>
				
<script type="text/javascript">
var current_form = null;
if (typeof (NUSAI_clearHistory) !== "function"){
  function NUSAI_clearHistory() {}
}
function initEditable() {
				<?php if($this->DragonConnectionChecker->checkConnection()): ?>
			 	<?php if (!empty($dragonVoiceStatus)): ?>
				NUSAI_clearHistory();
				<?php endif; ?>
			window.forceFocus = $(this).attr("id") + "_editable";
			<?php echo $this->element('dragon_voice'); ?>
				NUSAI_lastFocusedSpeechElementId = window.forceFocus;
				<?php endif; ?>	
}

	function submit_editable_data()
	{
		if(current_form)
		{
			  <?php if (empty($dragonVoiceStatus)): ?>
			  $('.btn', current_form).trigger("click");
				<?php else: ?>
					
						window.setTimeout(function(){
							
									if (!$('.NUSA_focusedElement', current_form).length && !$('.hasIpadDragon', current_form).length) {
											$('.btn', current_form).trigger("click");
									}
						}, 500);
				<?php endif;?>
		}
	}
	
</script>				
<?php echo $this->Html->script(array('json2.js?')); ?>
<?php echo $this->Html->script(array('sections/encounter_pe.js?'.md5(microtime()))); ?>
<table cellpadding="0" cellspacing="0">
    <tr>
        <td>
        	Template: <strong><span id="pe_template_desc"><?php echo $template_to_use['template_name']; ?></span></strong>
        	<?php if($page_access == 'W'): ?><a href="javascript:void(0);" onclick="showPETemplates();">change...</a></td><td><span  id="imgPELoading" style="">&nbsp;&nbsp;<?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span><?php endif; ?>
        </td>
    </tr>
</table>
<br>
<table id="table_pe_templates" cellpadding="0" cellspacing="0" class="form" width="100%" style="display: none;">
    <tr>
        <td>
        	<table cellpadding="0" cellspacing="0" class="form" style="width:700px">
                <tr>
                    <td style="padding-right: 5px;">
                        <?php if (count($templates) > 12): ?> 
						<!--<select id=compounding interest formula"myTemplatePE" name="myTemplatePE" size="4" style="margin-bottom: 0px;" onchange="changePETemplate();">-->
						<select id="myTemplatePE" name="myTemplatePE" size="4" style="margin-bottom: 0px;" onchange="changePETemplate();">
                            <option value="" disabled="">Focused ↓</option>
                            <?php
							
							foreach($templates as $template)
							{
								?>
                                <option value="<?php echo $template['PhysicalExamTemplate']['template_id']; ?>" <?php if($template_to_use['template_id'] == $template['PhysicalExamTemplate']['template_id']) { echo 'selected="selected"'; } ?>><?php echo $template['PhysicalExamTemplate']['template_name']; ?></option>
                                <?php
							}
							
							
							?>
                        </select>
                        <?php else: ?>
                        <div id="pe_templates">
                            <?php foreach ($templates as $template): ?>
                            <input type="radio" id="pe_templates_<?php echo $template['PhysicalExamTemplate']['template_id']; ?>" value='<?php echo $template['PhysicalExamTemplate']['template_id']; ?>'  name="myTemplatePE" <?php if($template_to_use['template_id'] == $template['PhysicalExamTemplate']['template_id']) { echo 'checked="checked"'; } ?> /><label for="pe_templates_<?php echo $template['PhysicalExamTemplate']['template_id']; ?>"><?php echo $template['PhysicalExamTemplate']['template_name']; ?></label>
                            <?php endforeach;?> 
                        </div>
                        
                        <?php endif;?> 
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

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
<?php echo $patient_photo = ''; ?> 
<div id="pe_section"></div>
<div id="pe_other_section" class="pe_section">
	<div class="pe_section_area">
    	<div class="pe_title_area">Other:</div>
        <div class="pe_item_area" style="overflow: visible">
            <?php if($page_access == 'W'): ?>
            <div id="pe_image_processing">
                <?php echo $this->Html->image('ajax_loaderback.gif', array('alt' => 'Processing')); ?> 
            </div>
            <div style="height: 35px; margin-bottom: 20px;">
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
            <?php endif; ?>            
            
            <form>
                <div class="pe_image_list" id="pe_image_list" style="overflow: visible"></div>
            </form>
                    <br />
                
            <div class="clear"></div>

        </div>

    </div>
</div>
