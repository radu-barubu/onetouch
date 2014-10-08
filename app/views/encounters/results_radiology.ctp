<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$autoURL = $html->url(array('controller' => 'encounters','action' => 'icd9', 'task' => 'load_autocomplete')) . '/';  
$tests_selected = (isset($this->params['named']['tests']))?$this->params['named']['tests']:"";
$tstt = array();
$page = (isset($this->params['named']['page']))?$this->params['named']['page']:"";

if(isset($tests_selected) && $tests_selected!=""){
$tests_selected = explode(',',$tests_selected);

foreach($tests_selected as $test_selected){
	$tstt[] = base64_decode($test_selected);
}
}
$flag_type = 0;
if(empty($this->params['named']['tests']) && !empty($page)){
	$flag_type=1;
}


$page_access = $this->QuickAcl->getAccessType("encounters", "results");
echo $this->element("enable_acl_read", array('page_access' => $page_access));    
echo $this->Html->script('multiple-select-master/jquery.multiple.select.js');
//echo $this->Html->css(array('multiple-select.css'));
?>
<link rel="stylesheet" type="text/css" href="/preferences/multiple_select" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/jquery.autocomplete.css" />
<script type="text/javascript" src="<?php echo $this->Session->webroot; ?>js/jquery/jquery.autocomplete.js"></script>
<script language="javascript" type="text/javascript">
	function processRadiology(){
			//for base64 encoding
			
			var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

			
			var string_test="";
			var test_name = $('#rdiology').val();
			//alert(test_name);
			if(test_name){
			var tst;
			if(test_name.indexOf(',')){
				tst = test_name.toString().split(",");
			}
			count = tst.length;
			var string_test="";
			for(var i=0;i<tst.length;i++){
				var test_value = Base64.encode(tst[i]);
				if(i==(count-1)){
					string_test += test_value;
				} else {
					string_test += test_value+',';
				}
			}
		} else {
			string_test="";
		}
		
			
			var url = '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_radiology', 'encounter_id' => $encounter_id,'task'=>'tst')); ?>/tests:'+string_test;
			
			$('#main_content').html('<?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>');
			$('#main_content').css('min-height','260px');
			$.get(url,function(data){
				$('#main_content').html(data); 
			});
			
		}
	$(document).ready(function()
	{   
		
		$('#main_content').css('min-height','700px');
		$('select#rdiology').multipleSelect({
			  placeholder:"Test",
			   onClick : function(){
				  processRadiology();
			  }
			  });
			  /*
		$('#showall').click(function(){
			var url = '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_radiology', 'encounter_id' => $encounter_id,'task'=>'showall')); ?>';
			$.get(url,function(data){
				$('#main_content').html(''); 
				$('#main_content').html(data); 
			});
		});
		
		$('#filter_test_name').click(function(){
			
			//for base64 encoding
			
			var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

			
			var test_name = Base64.encode($('#test_name').val());
			
			var url = '<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_radiology', 'encounter_id' => $encounter_id,'task'=>'tst')); ?>/tests:'+test_name;
			
			$("#imgLoadInhouseLab").show();
			$('#main_content').hide();
			$.get(url,function(data){
				$('#main_content').html(data); 
			});
			$("#imgLoadInhouseLab").hide();
			$('#main_content').show();
			
		});
*/
		
		initCurrentTabEvents('lab_radiology_area');

		$('#outsideRadiologyBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadInhouseRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'radiology_results', 'encounter_id' => $encounter_id)); ?>");
		});
		 $('.title_area .section_btn').click(function()
        {
            $(".tab_area").html('');
            $("#imgLoadInhouseRadiology").show();
			loadTab($(this),$(this).attr('url'));
        });
		$('#pointofcareBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadInhouseRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_radiology', 'encounter_id' => $encounter_id)); ?>");
		});
		
		//$('select#rdiology').multipleSelect('checkAll');
		var allSelected_tsting = $("select#rdiology option:not(:selected)").length == 0;
		if(allSelected_tsting==true){
			  $('select#rdiology').multipleSelect('checkAll');
		  }
		<?php if(empty($tstt)){ ?>
		$('select#rdiology').multipleSelect('checkAll');
		<?php } ?>
		<?php if($flag_type==1){ ?>
		$('select#rdiology').multipleSelect('uncheckAll');
		<?php } ?>
		<?php echo $this->element('dragon_voice'); ?>
	});  
</script>
<div style="overflow: hidden;">
		 <div class="title_area">
            <?php echo $this->element('../encounters/tabs_results', array('encounter_id' => $encounter_id)); ?>
		 <div class="title_text">
         	<a href="javascript:void(0);" id="pointofcareBtn"  style="float: none;" class="active">Point of Care</a>
            <a href="javascript:void(0);" id="outsideRadiologyBtn" style="float: none;">Outside Radiology</a>
         </div>
	</div>	 
	<span id="imgLoadInhouseRadiology" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	<div id="lab_radiology_area" class="tab_area"> 
<?php
if($task == 'addnew' || $task == 'edit')
{
	if($task == 'edit')
	{
		$poc_encounter_id = $EditItem['EncounterPointOfCare']['encounter_id'];
		unset($EditItem['EncounterPointOfCare']['encounter_id']);
		extract($EditItem['EncounterPointOfCare']);
		$id_field = '<input type="hidden" name="data[EncounterPointOfCare][point_of_care_id]" id="point_of_care_id" value="'.$point_of_care_id.'" />';
		if($date_ordered)
			$date_ordered = __date($global_date_format, strtotime($date_ordered));
	}
	else
	{
		//Init default value here
		$id_field = "";
		$radiology_procedure_name = "";
		$radiology_reason = "";
		$radiology_priority = "";
		$radiology_body_site = "";
		$radiology_laterality = "";
		$cpt = "";
		$cpt_code = "";
		$comment = "";
		$date_ordered = __date($global_date_format, strtotime(date("Y-m-d")));
		$status = "Open";
		$lab_test_reviewed = '';
		$poc_encounter_id = $encounter_id;
		$file_upload = null;
	}
	?>

	<div style="overflow: hidden;">
		<form id="frmInHouseWorkRadiology" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<?php
		echo $id_field.'
		<input type="hidden" name="data[EncounterPointOfCare][encounter_id]" id="encounter_id" value="'.$poc_encounter_id.'" />
		<input type="hidden" name="data[EncounterPointOfCare][order_type]" id="order_type" value="Radiology" />';
		?>
		<table cellpadding="0" cellspacing="0" class="form" width=100%>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label>Procedure Name:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][radiology_procedure_name]" id="radiology_procedure_name" style="width:450px;" value="<?php echo $radiology_procedure_name ?>"></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td width="150"><label>Reason:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][radiology_reason]" id="radiology_reason" value="<?php echo $radiology_reason;?>" style="width:450px;" /></td>
			</tr>
			<tr>
				<td width="150"><label>Priority:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][radiology_priority]" id="radiology_priority">
				<option value="" selected>Select Priority</option>
                <option value="Routine" <?php echo ($radiology_priority=='Routine'? "selected='selected'":''); ?>>Routine</option>
                <option value="Urgent" <?php echo ($radiology_priority=='Urgent'? "selected='selected'":''); ?> > Urgent</option>
			    </select>
				</td>
			</tr>
			<tr>
				<td width="150"><label>Body Site:</label></td>
				<td><input type="text" name="data[EncounterPointOfCare][radiology_body_site]" id="radiology_body_site" style="width:450px;" value="<?php echo @$radiology_body_site; ?>" /></td>
			</tr>
			<tr>
				<td width="150"><label>Laterality:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" style="width: 140px;">
							 <option value="" selected>Select Laterality</option>
                             <option value="Right" <?php echo ($radiology_laterality=='Right'? "selected='selected'":''); ?>>Right</option>
                             <option value="Left" <?php echo ($radiology_laterality=='Left'? "selected='selected'":''); ?> > Left</option>
							 <option value="Bilateral" <?php echo ($radiology_laterality=='Bilateral'? "selected='selected'":''); ?>>Bilateral</option>
							 <option value="Not Applicable" <?php echo ($radiology_laterality=='Not Applicable'? "selected='selected'":''); ?>>Not Applicable</option>
							 </select>
				<!--<input type="radio" name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" value="Right" checked> Right &nbsp; &nbsp;
				<input type="radio" name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" value="Left" <?php echo ($radiology_laterality=="Left"?"checked":""); ?>> Left &nbsp; &nbsp;
				<input type="radio" name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" value="Bilateral" <?php echo ($radiology_laterality=="Bilateral"?"checked":""); ?>> Bilateral &nbsp; &nbsp;
				<input type="radio" name="data[EncounterPointOfCare][radiology_laterality]" id="radiology_laterality" value="Not Applicable" <?php echo ($radiology_laterality=="Not Applicable"?"checked":""); ?>> Not Applicable-->
				</td>
			</tr>
			<?php
			if($task == 'addnew')
			{
				echo '<input type="hidden" name="data[EncounterPointOfCare][radiology_date_performed]" id="radiology_date_performed" value="'.date($global_date_format).'" />';
			}
			else
			{
				?>
				<tr>
					<td width="150" class="top_pos"><label>Date Performed:</label></td>
					<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][radiology_date_performed]', 'id' => 'radiology_date_performed', 'value' => __date($global_date_format, strtotime($radiology_date_performed)), 'required' => false)); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:top; padding-top: 5px;"><label>Test Result:</label></td>
					<td>
						<table cellpadding="0" cellspacing="0">
							<tr>
								<td>
									<input type="hidden" value="<?php echo ($file_upload) ? $file_upload : ''; ?>" style="width:450px;" id="upload_file" name="data[EncounterPointOfCare][file_upload]" />
								<div id="file-upload-area" class="file_upload_area" style="position: relative; width: 264px; height: auto !important; <?php echo ($file_upload) ? 'display: none' : ''; ?>">
									<div class="file_upload_desc" style="position: absolute; top: 0px; height: 19px; width: 250px; text-align: left; padding: 5px; overflow: hidden; color: #000000;"><?php echo isset($radiology_test_result)?$radiology_test_result:''; ?></div>
									<div class="progressbar" style="-moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"></div>
										<?php if($page_access == 'W'): ?>
                                        <div style="position: absolute; top: 1px; right: -125px;">
											<div style="position: relative;">
												<a href="#" class="btn" style="float: left; margin-top: -2px;">Select File...</a>
												<div style="position: absolute; top: 0px; left: 0px;"><input id="file_upload" name="file_upload" type="file" /></div>
											</div>
										</div>
									<?php endif; ?>

						   </div>
								<?php 
									$filename = '';
									if ($file_upload) {
										$filename = explode(DIRECTORY_SEPARATOR, $file_upload);
										$filename = array_pop($filename);
										$tmp = explode('_', $filename);
										unset($tmp[0]);
										$filename = implode('_', $tmp);
									}
								
								?>
								<div id="file-download-area" style="<?php echo (!$file_upload) ? 'display: none' : ''; ?>">
									<?php echo $this->Html->link($this->Html->image('download.png'). 'Download <span>' . htmlentities($filename) . '</span>', array(
										'controller' => 'encounters',
										'action' => 'results_radiology',
										'encounter_id' => $encounter_id,
										'task' => 'download_file',
										'point_of_care_id' => $point_of_care_id,
									), array(
										'class' => 'btn',
										'escape' => false,
										'id' => 'download_file',
									)); ?>
									
									<?php if($page_access == 'W'): ?>
									<?php echo $this->Html->link( $this->Html->image('del.png') . ' Remove file ', array(
										'controller' => 'encounters',
										'action' => 'results_radiology',
										'encounter_id' => $encounter_id,
										'task' => 'remove_file',
										'point_of_care_id' => $point_of_care_id,
									), array(
										'class' => 'btn',
										'escape' => false,
										'id' => 'remove_file',
									)); ?>
									<?php endif; ?>
								</div>
									
									
									

								</td>
							</tr>
							<tr>
								<td style="padding-top: 10px;">
									<input type="hidden" name="data[HelpForm][radiology_test_result_is_selected]" id="radiology_test_result_is_selected" value="false" />
									<input type="hidden" name="data[HelpForm][upload_dir]" id="upload_dir" value="" />
									<input type="hidden" name="data[HelpForm][radiology_test_result_is_uploaded]" id="radiology_test_result_is_uploaded" value="false" />
									<input type="hidden" name="data[HelpForm][radiology_test_result]" id="radiology_test_result" value="<?php echo $radiology_test_result; ?>">
									<span id="radiology_test_result_error"></span>
								</td>
							</tr>
						</table>
						
						<script language="javascript" type="text/javascript">
						$(function() 
						{
		
							function saveFileUpload(name) {
								var url = '<?php echo $this->Html->url(array(
										'controller' => 'encounters',
										'action' => 'results_radiology',
										'encounter_id' => $encounter_id,
										'task' => 'save_file',
										'point_of_care_id' => $point_of_care_id,
									)); ?>';
												
								$.post(url, {name: name}, function(){
									$('#upload_file').val(name);
								});
							}
		
							$('#remove_file').click(function(evt){
								evt.preventDefault();

								var url = $(this).attr('href');

								$.post(url, {'delete': 1}, function(){
									$('#file-download-area').hide();
									$('#file-upload-area').show();
									$('.file_upload_desc').html('');
									$('#upload_file').val('');
									$(".progressbar").progressbar("value", 0);
								});

							});

							
							$(".progressbar").progressbar({value: 0});
							
							$('#file_upload').uploadify(
							{
								'fileDataName' : 'file_input',
								'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
								'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
                                'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
                                'scriptData': {'data[path_index]' : 'encounters'},
								'auto'	  : true,
								'wmode'	 : 'transparent',
								'hideButton': true,
								'onSelect'  : function(event, ID, fileObj) 
								{
									$('#radiology_test_result_is_selected').val("true");
									$('#radiology_test_result').val(fileObj.name);
									$('#radiology_test_result_is_uploaded').val("false");
									$('.file_upload_desc').html(fileObj.name);
									$(".ui-progressbar-value").css("visibility", "hidden");
									$(".progressbar").progressbar("value", 0);
									
									$("#radiology_test_result_error").html("");
									$(".file_upload_desc").css("border", "none");
									$(".file_upload_desc").css("background", "none");
					
									return false;
								},
								'onProgress': function(event, ID, fileObj, data) 
								{
									$(".ui-progressbar-value").css("visibility", "visible");
									$(".progressbar").progressbar("value", data.percentage);
	
									return true;
								},
								'onOpen'	: function(event, ID, fileObj) 
								{
									$(window).css("cursor", "wait");
								},
								'onComplete': function(event, queueID, fileObj, response, data) 
								{
									
									saveFileUpload(response);

									$('#download_file span').text(fileObj.name);

									$('#file-download-area').show();
									$('#file-upload-area').hide();												
									
									$('#radiology_test_result_is_uploaded').val("true");
									
									if(submit_flag == 1)
									{
										$('#frmInHouseWorkRadiology').submit();
									}
								},
								'onError'   : function(event, ID, fileObj, errorObj) 
								{
								}
							});
						});
						</script>
						
					</td>
				</tr>
				<tr>
					<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
					<td><textarea cols="20" name="data[EncounterPointOfCare][radiology_comment]"  style="height:80px"><?php echo $radiology_comment ?></textarea></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="2">
					<table cellpadding="0" cellspacing="0" class="form">
						<tr>
							<td width="150"><label>CPT:</label></td>
							<td style="padding-right: 10px;"><input type="text" name="data[EncounterPointOfCare][cpt]" id="cpt" style="width:450px;" value="<?php echo $cpt ?>"></td>
							<td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
						</tr>
					</table>
					<?php echo '<input type="hidden" name="data[EncounterPointOfCare][cpt_code]" id="cpt_code" value="'.$cpt_code.'" />'; ?>
				</td>
			</tr>
			<tr>
				<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
				<td><textarea cols="20" name="data[EncounterPointOfCare][comment]"  style="height:80px"><?php echo $comment ?></textarea></td>
			</tr>
			<?php
			if($task == 'edit')
			{
				?>
				<tr height=35>
					<td valign='top' style="vertical-align:top"><label>Ordered by:</label></td>
					<td><?php echo $EditItem['OrderBy']['firstname']." ".$EditItem['OrderBy']['lastname'] ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td width="150" class="top_pos"><label>Date Ordered:</label></td>
				<td><?php echo $this->element("date", array('name' => 'data[EncounterPointOfCare][date_ordered]', 'id' => 'date_ordered', 'value' => $date_ordered, 'required' => false)); ?></td>
			</tr>
			<tr>
				<td width="150"><label>Status:</label></td>
				<td>
				<select name="data[EncounterPointOfCare][status]" id="status" style="width: 110px;">
							 <option value="" selected>Select Status</option>
                             <option value="Open" <?php echo ($status=='Open'? "selected='selected'":''); ?>>Open</option>
                             <option value="Done" <?php echo ($status=='Done'? "selected='selected'":''); ?> > Done</option>
							 </select>
				<!--<input type="radio" name="data[EncounterPointOfCare][status]" id="status" value="Open" checked> Open &nbsp; &nbsp;
				<input type="radio" name="data[EncounterPointOfCare][status]" id="status" value="Done" <?php echo ($status=="Done"?"checked":""); ?>> Done-->
				</td>
			</tr>
			<tr>
				<td width="150"><label for="reviewed">Reviewed:</label></td>
				<td>
					<?php echo $form->input('EncounterPointOfCare.lab_test_reviewed', array('type' => 'checkbox', 'value' => 1, 'label' => false, 'checked' => $lab_test_reviewed, 'id' => 'reviewed')); ?>
				</td>
			</tr>
		</table>
		</form>
	</div>
	<div class="actions">
		<ul>
			<?php if($page_access == 'W'): ?><li><a href="javascript: void(0);" onclick="$('#frmInHouseWorkRadiology').submit();">Save</a></li><?php endif; ?>
			<li><a class="ajax" href="<?php echo $html->url(array('action' => 'results_radiology', 'encounter_id' => $encounter_id)); ?>">Cancel</a></li>
		</ul>
	</div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		$("#radiology_procedure_name").autocomplete(['EKG [93000]', 'Holter - 24 hrs [93224]', 'Inhalation TX [94640]', 'Stress Test [93015]', 'Pellet Implantation [11980]'], {
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#cpt").autocomplete('<?php echo $this->Session->webroot; ?>encounters/cpt4/encounter_id:<?php echo $encounter_id; ?>/task:load_autocomplete/', {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});

		$("#cpt").result(function(event, data, formatted)
		{
			$("#cpt_code").val(data[1]);
		});

		$("#frmInHouseWorkRadiology").validate(
		{
			errorElement: "div",
			errorPlacement: function(error, element) 
			{
				if(element.attr("id") == "radiology_date_performed")
				{
					$("#radiology_date_performed_error").append(error);
				}
				else if(element.attr("id") == "radiology_test_result")
				{
					$("#radiology_test_result_error").append(error);
					$(".file_upload_desc").css("border", "2px solid #FBC2C4");
					$(".file_upload_desc").css("background", "#FBE3E4");
				}
				else if(element.attr("id") == "date_ordered")
				{
					$("#date_ordered_error").append(error);
				}
				else
				{
					error.insertAfter(element);
				}
			},
			submitHandler: function(form) 
			{
				if($('#radiology_test_result_is_selected').val() == 'true' && $('#radiology_test_result_is_uploaded').val() == "false")
				{
					$('#frmInHouseWorkRadiology').css("cursor", "wait");
					$('#file_upload').uploadifyUpload();
					submit_flag = 1;
				}
				else
				{
					$('#frmInHouseWorkRadiology').css("cursor", "wait");
					
					$.post(
						'<?php echo $thisURL; ?>', 
						$('#frmInHouseWorkRadiology').serialize(), 
						function(data)
						{
							showInfo("<?php echo $current_message; ?>", "notice");
							loadTab($('#frmInHouseWorkRadiology'), '<?php echo $html->url(array('action' => 'results_radiology', 'encounter_id' => $encounter_id)); ?>');
						},
						'json'
					);
				}
			}
		});
	});
	</script>
	<?php
}
else
{
	?>
	<script type="text/javascript">
		function update_reviewed(obj) 
		{
			var point_of_care_id = obj.value;
			if(obj.checked==true) {
				var reviewed = 1;
			} else {
				var reviewed = 0;
			}
			$.post(
				'<?php echo $html->url(array('action' => 'results_lab', 'task' => 'save_reviewed', 'encounter_id' => $encounter_id)); ?>/point_of_care_id:'+point_of_care_id, 
				{ 'data[EncounterPointOfCare][lab_test_reviewed]': reviewed,'data[EncounterPointOfCare][point_of_care_id]': point_of_care_id  }, 
				function(data)
				{
					showInfo(data.msg, "notice");				
				},
				'json'
			);
		} 
	</script>
	 <div style="margin-bottom:10px;">
		<table>
			<tr style="margin:5px;">
				<td style="width:120px;">Radiology Name: </td>
			
        <?php 
        $tests = array();
        $options = "";
        foreach($AdministrationPointOfCare as $AdministrationPointOfCares){
		$tests[$AdministrationPointOfCares['AdministrationPointOfCare']['radiology_procedure_name']] = $AdministrationPointOfCares['AdministrationPointOfCare']['radiology_procedure_name'];
		
		}
				
		?>
		<td>
		<?php 
		if(!empty($tstt)){
			echo $form->input('rdiology', array('type' => 'select','multiple'=>'true','options' => $tests, 'selected' => $tstt, 'style'=>'width:200px;','label' => false, 'id' => 'rdiology')); 
		} else {
		echo $form->input('rdiology', array('type' => 'select','multiple'=>'true','options' => $tests, 'selected' => '', 'style'=>'width:200px;','label' => false, 'id' => 'rdiology')); 
		}
		?>
		</td>
		
        </tr>
		</table>
        </div>
	<div style="overflow: hidden;min-height:260px;" id="main_content">
		<form id="frmInHouseWorkRadiology" method="post" action="<?php echo $thisURL. '/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" class="listing">
			<tr>
				<th><?php echo $paginator->sort('Procedure Name', 'radiology_procedure_name', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Date', 'radiology_date_performed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
				<th style="text-align:center"><?php echo $paginator->sort('Reviewed', 'lab_test_reviewed', array('model' => 'EncounterPointOfCare', 'class' => 'ajax'));?></th>
			</tr>

			<?php
			$i = 0;
			foreach ($EncounterPointOfCare as $EncounterPointOfCare):
				$i++;
			?>
				<tr editlinkajax="<?php echo $html->url(array('action' => 'results_radiology', 'task' => 'edit', 'encounter_id' => $encounter_id, 'point_of_care_id' => $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']), array('escape' => false)); ?>">
					<td><?php echo $EncounterPointOfCare['EncounterPointOfCare']['radiology_procedure_name']; ?></td>					
					<td><?php $date_performed = $EncounterPointOfCare['EncounterPointOfCare']['radiology_date_performed']; if($date_performed) echo __date($global_date_format, strtotime($date_performed)); ?></td>
					<td style="text-align:center" class="ignore"><label for="reviewed<?php echo $i;?>" class="label_check_box_hx"><input type="checkbox" value="<?php echo $EncounterPointOfCare['EncounterPointOfCare']['point_of_care_id']; ?>" id="reviewed<?php echo $i;?>" onclick="update_reviewed(this);" <?php if($EncounterPointOfCare['EncounterPointOfCare']['lab_test_reviewed']) echo 'checked="checked"'; ?>  /></label></td>
				</tr>
			<?php endforeach; ?>

			</table>
		</form>
		<div style="width: 60%; float: right; margin-top: 15px;">
			<div class="paging">
				<?php echo $paginator->counter(array('model' => 'EncounterPointOfCare', 'format' => __('Display %start%-%end% of %count%', true))); ?>
				<?php
					if($paginator->hasPrev('EncounterPointOfCare') || $paginator->hasNext('EncounterPointOfCare'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
				<?php 
					if($paginator->hasPrev('EncounterPointOfCare'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
				<?php echo $paginator->numbers(array('model' => 'EncounterPointOfCare', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
				<?php 
					if($paginator->hasNext('EncounterPointOfCare'))
					{
						echo $paginator->next('Next >>', array('model' => 'EncounterPointOfCare', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		</div>
	</div>
	<?php
}
?>
	</div>
</div>
<script>
$('input[name=selectAll]').click(function(){
				processRadiology();
			});
</script>
