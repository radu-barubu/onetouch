<?php
/*
        NOTE: This view file is identical to patient_documents.ctp
        so any updates here will likely need updated there too

*/
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$deleteURL = $html->url(array('task' => 'delete', 'patient_id' => $patient_id)) . '/';
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';
$smallAjaxSwirl = $html->image('ajax_loaderback.gif', array('alt' => 'Loading...'));
$page = (isset($this->params['named']['page']))?$this->params['named']['page']:"";
$doc_type = (isset($this->params['named']['doc_type']))?$this->params['named']['doc_type']:"";
$flag_no_doc_type = 0;

if(isset($saved_search_array["doc_type"]) && (!is_array($saved_search_array["doc_type"]) && $saved_search_array["doc_type"]=="")){
	$flag_no_doc_type = 1;
} 
$flag_type = 0;
if(empty($this->params['named']['doc_type']) && !empty($page)){
	$flag_type=1;
}

$doc = array();
if(isset($doc_type) && $doc_type!=""){
$doc_type = explode(',',$doc_type);

foreach($doc_type as $doc_typee){
	$doc[] = base64_decode($doc_typee);
}
}
$doc_status = (isset($this->params['named']['doc_status']))?base64_decode($this->params['named']['doc_status']):"";
$doc_fromdate = (isset($this->params['named']['doc_fromdate']))?base64_decode($this->params['named']['doc_fromdate']):"";
$doc_todate = (isset($this->params['named']['doc_todate']))?base64_decode($this->params['named']['doc_todate']):"";
$doc_name = (isset($this->params['named']['doc_name']))?base64_decode($this->params['named']['doc_name']):"";
//exit;
$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$document_id = (isset($this->params['named']['document_id'])) ? $this->params['named']['document_id'] : "";

$page_access = $this->QuickAcl->getAccessType('patients', 'attachments');
echo $this->element("enable_acl_read", array('page_access' => $page_access));

echo $this->Html->script('document_types.js');
echo $this->Html->script('multiple-select-master/jquery.multiple.select.js');
//echo $this->Html->css(array('multiple-select.css'));

?>
<link rel="stylesheet" type="text/css" href="/preferences/multiple_select" />

<style>
#from_date,#to_date{
	border: 1px solid #AAAAAA;
	font-size:14px;
	width:105px;
	padding:5px;
	
}
table,tr,td,tbody{
	vertical-align:middle;
}

</style>
<script language="javascript" type="text/javascript">
function processRequest(){
		
		//for base64 encoding
			
			var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}
			
			var string_test="";
			var test_name = $('#doc_types').val();
			
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
			
			var doc_name = Base64.encode($('#doc_name').val());
			var doc_status = Base64.encode($('#doc_status').val());
			var doc_fromdate = Base64.encode($('#from_date').val());
			var doc_todate = Base64.encode($('#to_date').val());
		
		var url = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'documents', 'patient_id' => $patient_id)); ?>/doc_name:'+doc_name+'/doc_type:'+string_test+'/doc_status:'+doc_status+'/doc_fromdate:'+doc_fromdate+'/doc_todate:'+doc_todate;
		$('#docs_content').show();
		$('#docs_content').html('<?php echo $smallAjaxSwirl; ?>');
		$.get(url,function(data){			
				$('#docs_content').html(data); 			
				});
		$('#docs_content').show();
	}
$(document).ready(function()
{
		

		<?php if( !empty( $saved_search_array ) && empty($page)){ ?>
		$('#doc_name').val('<?php echo $saved_search_array["doc_name"];?>');
		$('#doc_status').val('<?php echo $saved_search_array["doc_status"];?>');
		$('#from_date').val('<?php echo $saved_search_array["doc_fromdate"];?>');
		$('#to_date').val('<?php echo $saved_search_array["doc_todate"];?>');
		processRequest();
		
		<?php } ?>
		
		<?php  if(isset($doc_status) && $doc_status!=""){ ?>
			$('#doc_status').val('<?php echo $doc_status; ?>');
		<?php  } ?>
		<?php if(isset($doc_name) && $doc_name!=""){ ?>
			$('#doc_name').val('<?php echo $doc_name; ?>');
		<?php } ?>
		<?php if(isset($doc_fromdate) && $doc_fromdate!=""){ ?>
			$('#from_date').val('<?php echo $doc_fromdate; ?>');
		<?php } ?>
		<?php if(isset($doc_todate) && $doc_todate!=""){ ?>
			$('#to_date').val('<?php echo $doc_todate; ?>');
		<?php } ?>
		
		$('select#doc_types').multipleSelect({
			  placeholder:"Document Type",
			   onClick : function(){
				  processRequest();
			  }
		});
	
		$('#show_advanced').click(function(){		
			if ($('#show_advanced').attr('checked')) {			
			   $('#new_advanced_area').slideDown("slow");
			} else {
			   $('#new_advanced_area').slideUp("slow");
			}
		});
		
		$("#doc_name").addClear(
		{
			closeImage: "<?php echo $this->Session->webroot; ?>img/clear.png",
			onClear: function()
			{
				processRequest();
				
			}
		});

		var allSelected_doctypes = $("select#doc_types option:not(:selected)").length == 0;
			  if(allSelected_doctypes==true){
				  $('select#doc_types').multipleSelect('checkAll');
			  }
		var globalTimeout = null;
		function SearchFunc(){  
			globalTimeout = null;  
			processRequest();
		}
    
	$('#doc_name').keyup(function(evt){
                        if (evt.which === 13) {
                            return false;
                        }
						
						//1 second delay on the keyup                   
                        if(globalTimeout != null) clearTimeout(globalTimeout);  
						globalTimeout =setTimeout(SearchFunc,1000); 
                        //loadEncounterTable(current_url);

			
	});
	
	$('#doc_name').siblings('a').css('right','10px');
	
	$('#doc_status').bind('change',function(){
		processRequest();
	});
	
	$('#to_date').bind('change',function(){
		if($('#from_date').val() && $('#to_date').val()){
		processRequest();
		}
	});
	$('#from_date').bind('change',function(){
		if($('#from_date').val() && $('#to_date').val()){
		processRequest();
		}
	});
	
	$('#save_filter').click(function(){
		
		//for base64 encoding
			
			var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

			
		var doc_name = Base64.encode($('#doc_name').val());
		var doc_type = $('#doc_types').val();
		var doc_status = Base64.encode($('#doc_status').val());
		var doc_fromdate = Base64.encode($('#from_date').val());
		var doc_todate = Base64.encode($('#to_date').val());
	
		
		
		var url = '<?php echo $html->url(array('controller' => 'patients','task'=>'save_filter', 'action' => 'documents', 'patient_id' => $patient_id)); ?>';
		$.post(url,{doc_name:doc_name,doc_status:doc_status,doc_type:doc_type,doc_fromdate:doc_fromdate,doc_todate:doc_todate},function(data){			
				if( data ) {
					$('#search_saved_message').show('slow');
					setTimeout(function(){$('#search_saved_message').hide('slow');} , 4000);
				}
		});
		
	});
	<?php if((empty($saved_search_array["doc_type"]) && empty($doc))){ ?>
		$('select#doc_types').multipleSelect('checkAll');
	<? } ?>
	<?php if($flag_type==1 || $flag_no_doc_type==1){ ?>
		$('select#doc_types').multipleSelect('uncheckAll');
	<?php } ?>
	
	$('#reset_cache_filter').click(function(){
		
		$(this).hide();
		var url = '<?php echo $html->url(array('controller' => 'patients','task'=>'delete_filter', 'action' => 'documents', 'patient_id' => $patient_id)); ?>';
		$.post(url,'',function(){
			$('#search_filter').hide();
			$('#reset_cache_filter').hide();	
			$('#doc_name').val('');
			$('select#doc_types').multipleSelect('checkAll');
			$('#doc_status').val('all');
			$('#from_date').val('');
			$('#to_date').val('');
			
			
			processRequest();		
		});
		
	});
		
	
	$("#document_type").autocomplete(["Medical", "Lab", "Dropdownlogy", "Legal", "Personal"], {
		max: 20,
		mustMatch: false,
		matchContains: false,
		scrollHeight: 300
	});
	initCurrentTabEvents('patient_document_area');

    $("#frmPatientDocuments").validate(
    {
        errorElement: "div",
		errorPlacement: function(error, element) 
		{
			if(element.attr("id") == "status_open")
			{
				$("#status_error").append(error);
			}
			else
			{
				error.insertAfter(element);
			}
		},
        submitHandler: function(form) 
        {			
			if($('#attachment_is_selected').val() == 'false' && $('#attachment_is_uploaded').val() == 'false')
			{
				alert("Please Select file");
				return false;
			}
			$('#frmPatientDocuments').css("cursor", "wait");
			
			if($('#attachment_is_selected').val() == 'true' && $('#attachment_is_uploaded').val() == 'false')
			{
				//wait 1 second before submitting the form
				window.setTimeout("$('#frmPatientDocuments').submit();", 1000);
			}
			else
			{
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmPatientDocuments').serialize(), 
					function(data)
					{
					   // if wasn't uploaded, warn client!
            				   if (data == 'upload_error') {
						$('#attachment_is_selected').val("false");
                                                $('#attachment_is_uploaded').val("false");
                                                $('#attachment_file_upload_desc').html('');
                                                $("#attachment_progressbar").progressbar("value", 0);
						showInfo("WARNING: File was not uploaded. Try again.", "error");
						return false;
					   }
              					showInfo("<?php echo $current_message; ?>", "notice");  
						loadTab($('#frmPatientDocuments'), '<?php echo $mainURL; ?>');
					}
				
				);
			}
        }
    });
	
	<?php if($task == 'addnew' || $task == 'edit'): ?>
	var duplicate_rules = {
		remote: 
		{
			url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
			type: 'post',
			data: {
				'data[model]': 'PatientDocument', 
				'data[patient_id]': <?php echo $patient_id; ?>, 
				'data[document_name]': function()
				{
					return $('#document_name', $("#frmPatientDocuments")).val();
				},
				'data[exclude]': '<?php echo $document_id; ?>'
			}
		},
		messages: 
		{
			remote: "Duplicate value entered."
		}
	}
	
	$("#document_name", $("#frmPatientDocuments")).rules("add", duplicate_rules);
	<?php endif; ?>  
        
});

function scanInit()
{
	 if (navigator.appName == 'Microsoft Internet Explorer') {
		uploadurl = '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id()), true); ?>';
		FormTagName = 'file_input';
		path_index = 'patients';
		$('#scanner').attr('src', '<?php echo $html->url('/', true); ?>/scan_document.php');
		$('#scanner').toggle();
		$('#scanner_wrap').toggle();
	} else {
		 showInfo("This feature only works with Internet explorer.", 'notice');
	}
}
function scanFinishUpload(response)
{
	var url = new String(response);
	var filename = url.substring(url.lastIndexOf('/')+1);
	$('#attachment').val(filename);
	$('#attachment_is_uploaded').val('true');
	$('#scanner').toggle();
	$('#scanner_wrap').toggle();
	if(filename) {
		$("#scan_upload_completed").show();
	}		
}

</script>
<div id="patient_document_area" class="tab_area" style="min-height: 700px;">
	<?php
    if($task == "addnew" || $task == "edit")
    {
		if($task == "addnew")
		{
			$service_date = __date($global_date_format, time());
			$document_name = "";
			$document_type = "";
			$attachment = "";
			$description = "";			
			$status = "";
			$id_field = "";
		}
		else
		{
			extract($EditItem['PatientDocument']);
			$id_field = '<input type="hidden" name="data[PatientDocument][document_id]" id="document_id" value="'.$document_id.'" />';
			$service_date = __date($global_date_format, strtotime($service_date));
		}
		
		$isForm = ($document_type == 'Online Form');
		
        ?>
		<form id="frmPatientDocuments" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data" >
		<? echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
			<tr>
				<td class="top_pos"><label>Document Name:</label></td>
				<td><input type="text" name="data[PatientDocument][document_name]" id="document_name" style="width:984px; "  class="required" value="<?php echo $document_name; ?>" <?php if ($isForm) { echo 'readonly="readonly"';} ?> /></td>
			</tr>
			<tr>
				<td width="130"><label>Type:</label></td>
				<td>
					
					<?php if ($isForm): ?>
					Online Form
					<input type="hidden" name="data[PatientDocument][attachment_is_uploaded]" value="false" />
					<input type="hidden" name="data[PatientDocument][document_type]" value="Online Form" />
					
					<?php $f = $formData?> 
					(<?php echo $this->Html->link('View Form', array('controller' => 'forms', 'action' => 'view_html_data', 'data_id' => $f['FormData']['form_data_id']), array('class' => 'formdata-link')); ?>)
					
					
					
					<div class="past_visit_close"></div>
					<iframe class="past_visit_load" src="" frameborder="0" ></iframe>
							<script language="javascript" type="text/javascript">
									$(function() {
							$('.formdata-link').bind('click',function(event)
							{
								event.preventDefault();
								var href = $(this).attr('href');
								$('.past_visit_load').attr('src',href).fadeIn(400,
								function()
								{
										$('.past_visit_close').show();
										$('.past_visit_load').load(function()
										{
												$(this).css('background','white');

										});
								});
							});

							$('.past_visit_close').bind('click',function(){
							$(this).hide();
							$('.past_visit_load').attr('src','').fadeOut(400,function(){
								$(this).removeAttr('style');
								});
							});
						});
						 </script>						
					
					
					<?php else: ?> 
					<select name="data[PatientDocument][document_type]" id="document_type">
					<?php
					$document_type_array = $patient_document_types;
					for ($i = 0; $i < count($document_type_array); ++$i)
					{
						echo "<option value=\"$document_type_array[$i]\"".($document_type==$document_type_array[$i]?"selected":"").">".$document_type_array[$i]."</option>";
					}
					?>
					</select>
					<span class="smallbtn" id="dTtoggle" style="margin:0 0 0 10px">Edit</span> <div id="dToptions" class="notice" style="display:none;width:400px">Add a new Type? <!--<img src="/img/del.png"> --> <input type="text" style="width:150px" id="dtValue" placeholder="type name"> <a id="dtSave" style="float:right" class="btn">Save</a> </div>
<div id="dialog-confirm" title="Confirmation" style="display:none">
  <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You are about to add a new File Type to the system. Are you sure? 
</div>


					<?php endif;?> 
				</td>
			</tr>		
			<?php if (!$isForm): ?> 
			<tr>
				<td class="top_pos" width="130">
				<label>Description:</label>
				</td>
				<td><textarea rows="5" cols="20" name="data[PatientDocument][description]" id="description" ><?php echo $description; ?></textarea></td>
			</tr>
			<tr>
				<td class="top_pos">
					<label>Attachment:</label>
					<span class="asterisk">*</span>
				</td>
				<td>
					<?php if($attachment): ?>
          <?php
                $file = $rawItem['PatientDocument']['attachment'];
                $paths['patient_documents'] = $paths['patients'] . $rawItem['PatientDocument']['patient_id'] . DS . 'documents' . DS;
                UploadSettings::createIfNotExists($paths['patient_documents']);
                $targetFile = UploadSettings::existing($paths['patients'] . $file, $paths['patient_documents'] . $file);
          ?>
          <?php if(is_file($targetFile)): ?>
					<?php echo $this->Html->link($document_name . ' ' . $this->Html->image('download.png'), array(
                        'controller'=>'patients', 
                        'action' =>'documents', 
                        'task' => 'download_file',
                        'document_id' => $document_id,
                        'mark_as' => 'reviewed'
					), array('escape' => false)); ?>
          
          <?php else:?>
          <?php echo $document_name; ?> <em>(file is missing)</em>
          <?php endif;?>
          
					<br />
					<br />
					<?php else:?>
					<div style="position:relative"><?php echo $this->element("file_upload", array('model' => 'PatientDocument', 'name' => 'data[PatientDocument][attachment]', 'id' => 'attachment', 'value' => $attachment, 'fileExt' => '*.pdf;*.docx;*.doc;*.jpg;*.png;*.xml', 'fileDesc' => 'Documents')); ?> <span style="position:absolute;left:350px;top:5px;">OR <a href="javascript:;" onclick="scanInit();">Scan Document</a></span><span id="scan_upload_completed" class="notice" style="position:absolute;left:500px;top:0px;width:16%;display:none">Document was uploaded, now click Save below to finish.</span></div>
					<?php endif;?>
					
					
					
				</td>
			</tr>
			<tr id="scanner_wrap" style="display:none;">
				<td class="top_pos">&nbsp;</td>
				<td><iframe id="scanner" src="" frameborder="0" scrolling="no" height="600" width="800" style="display:none;"></iframe></td>
			</tr>
			<?php endif;?> 
			<tr>
				<td class="top_pos"><label>Service Date:</label></td>
				<td><?php echo $this->element("date", array('name' => 'data[PatientDocument][service_date]', 'id' => 'service_date', 'value' => $service_date, 'required' => false)); ?></td>
			</tr>
			<tr>
				<td><label>Status:</label></td>
				<td>         
					<select name="data[PatientDocument][status]" id=status>
					<?php
					$status_array = array("Open", "Reviewed");
					for ($i = 0; $i < count($status_array); ++$i)
					{
						echo "<option value=\"$status_array[$i]\"".($status==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
					}
					?>
					</select>
				</td>
			</tr>			
                        <!--
			<tr>
                                <?php if(count($availableProviders) === 1): ?> 
                                <?php 
                                    $p = $availableProviders[0]['UserAccount'];
                                    $provider_text = htmlentities($p['firstname'] . ' ' . $p['lastname']);
                                    $provider_id = $p['user_id'];
                                ?> 
                                <td colspan="2">
                                    <input type="hidden" name="data[PatientDocument][provider_id]" value="" />
                                    <label for="provider_id" class="label_check_box"><input type="checkbox" id="provider_id" name="data[PatientDocument][provider_id]" value="<?php echo @$provider_id; ?>"  /> Notify provider</label>
                                    <input type="hidden" name="data[PatientDocument][provider_text]" id="provider_text"  value="<?php echo @$provider_text; ?>" style="width: 200px;" /> 
                                    <br />
                                    <br />
                                </td>
                                <?php else: ?>
				<td class="top_pos"><label>Notify Provider?</label></td>
				<td> 
                                    <input type="text" name="data[PatientDocument][provider_text]" id="provider_text"  value="<?php echo @$provider_text; ?>" style="width: 200px;" /> 
                                    <input type="hidden" id="provider_id" name="data[PatientDocument][provider_id]" value="<?php echo @$provider_id; ?>" />
                                </td>
                                <?php endif; ?> 
			</tr>
                        -->
												<?php if (!$isForm): ?> 
                        <tr>
                            <td style="vertical-align: top;">Send Notification?</td>
                            <td>
                                <?php echo $this->element('notify_staff', array('model' => 'PatientDocument')); ?>
                            </td>
                        </tr>
												<?php endif;?> 
		</table>
		<div class="actions">
			<ul>
				<?php if($page_access == 'W'): ?><li><a href="javascript: void(0);" onclick="$('#frmPatientDocuments').submit();" class='flower' style='float:left;'>Save</a></li><?php endif; ?>
				<li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
			</ul>
		</div>
	</form>
<?php
    }
    else
    {
        ?>
        <style>
			table.noborder td {
				border-bottom-width: 0px;
			}
		
		</style>
        <script language="javascript" type="text/javascript">
			$(document).ready(function()
			{
				$('.link_hash').click(function()
				{
					var parent_tr = $(this).parents('.document_row');
					var document_id = parent_tr.attr('document_id');
					var filename = parent_tr.attr('filename');
					
					if(parent_tr.attr('isopen') == 'true')
					{
						$('#hashrow_'+document_id).remove();
						parent_tr.attr('isopen', 'false');
						$('td', parent_tr).css('border-bottom-width', '1px');
					}
					else
					{
						$('td', parent_tr).css('border-bottom-width', '0px');
						
						parent_tr.hover(function()
						{
							$('#parent_tr_'+document_id).css("background", "#FDF5C8");
							$('#hashrow_'+document_id).css("background", "#FDF5C8");
						},
						function()
						{
							$('#parent_tr_'+document_id).css("background", "");
							$('#hashrow_'+document_id).css("background", "");
						});
						
						var row_html = '<tr id="hashrow_'+document_id+'">';
						row_html += '<td>&nbsp;</td>';
						row_html += '<td colspan="6">';
						
						row_html += '<?php echo $html->image('ajax_loaderback.gif', array('alt' => '', 'class' => 'img_file_validator_loader')); ?>';
						
						row_html += '<table border="0" cellspacing="0" cellpadding="0" class="form" class="noborder" style="display:none;">';
						row_html += '<tr>';
						row_html += '<td width="120"><label>File Name:</label></td>';
						row_html += '<td><span class="span_document_validator_file_name"></span></td>';
						row_html += '</tr>';
						row_html += '<tr>';
						row_html += '<td><label>Current Hash:</label></td>';
						row_html += '<td><span class="span_document_validator_file_hash"></span></td>';
						row_html += '</tr>';
						row_html += '<tr>';
						row_html += '<td><label>Original Hash:</label></td>';
						row_html += '<td><span class="span_document_validator_stored_hash"></span></td>';
						row_html += '</tr>';
						row_html += '<tr>';
						row_html += '<td><label>Status:</label></td>';
						row_html += '<td><span class="span_document_validator_status"></span></td>';
						row_html += '</tr>';
						row_html += '</table>';
						
						row_html += '</td>';
						row_html += '</tr>';
						
						parent_tr.after(row_html);
						
						$('#hashrow_'+document_id).css("background", "#FDF5C8");
						
						$('#hashrow_'+document_id).hover(function()
						{
							$('#parent_tr_'+document_id).css("background", "#FDF5C8");
							$('#hashrow_'+document_id).css("background", "#FDF5C8");
						},
						function()
						{
							$('#parent_tr_'+document_id).css("background", "");
							$('#hashrow_'+document_id).css("background", "");
						});
						
						parent_tr.attr('isopen', 'true');
						
						$('td', $('#hashrow_'+document_id)).css('border-bottom-width', '0px');
						
						if($('#parent_tr_'+document_id).hasClass('striped'))
						{
							$('#hashrow_'+document_id).addClass('striped');
						}
						
						$('.img_file_validator_loader', $('#hashrow_'+document_id)).show();
						$('table', $('#hashrow_'+document_id)).hide();
						
						$.post(
							'<?php echo $html->url(array('action' => 'documents', 'task' => 'validate_document')); ?>', 
							{'data[file]': filename, 'data[document_id]': document_id}, 
							function(data)
							{
								$('table', $('#hashrow_'+document_id)).show();
								$('.img_file_validator_loader', $('#hashrow_'+data.document_id)).hide();
								$('.span_document_validator_file_name', $('#hashrow_'+data.document_id)).html(data.file_name);
								$('.span_document_validator_file_hash', $('#hashrow_'+data.document_id)).html(data.hash);
								$('.span_document_validator_stored_hash', $('#hashrow_'+data.document_id)).html(data.original_hash);
								
								if(data.valid)
								{
									$('.span_document_validator_status', $('#hashrow_'+data.document_id)).html('Valid');
									$('.span_document_validator_status', $('#hashrow_'+data.document_id)).css('color', '#090');
								}
								else
								{
									$('.span_document_validator_status', $('#hashrow_'+data.document_id)).html('Invalid');
									$('.span_document_validator_status', $('#hashrow_'+data.document_id)).css('color', '#F00');
								}
							},
							'json'
						);
					}
				});
			});
			
			function view_ccr_ccd(filename)
			{
				$('#upload_file_name').val(filename);
				$('#upload_folder').val('patients');
				$('#upload_enable_import').val('1');
				$('#upload_validate_mode').val('2');
				$('#upload_patient_id').val('<?php echo $patient_id; ?>');
				
				var href = '';
				$('.visit_summary_load').fadeIn(400,function()
				{
					$('#frmSubmitRender').submit();
					
					$('.iframe_close').show();
					$('.visit_summary_load').load(function()
					{
						$(this).css('background','white');
					});
				});
			}
		</script>
		<?php if( !empty( $saved_search_array )){ ?>
		<div class="small_notice" style="position:relative;width:270px;" id="search_filter">Your search filter is in effect.  <input type="button"  id="reset_cache_filter" class="smallbtn" style="margin-left:5px;float:none;display:inline-block;" value="Reset"></div>
		<?php } ?>
		<div class="notice" id="search_saved_message" style="display:none;">
				Your search preference has been saved.
		</div>
		<div style="margin-bottom:20px;">
		<table class="form" cellspacing="0" cellpadding="0" border='1' style="font-size: 14px;vertical-align: middle;">
			<tr>
				<td style="width:118px;">Document Name: </td><td><input type="text" id="doc_name" style="border: 1px solid #AAAAAA;font-size:14px;margin-left:5px;margin-right:5px;padding: 5px;width:300px;"></td>
				<td> <span style="margin-left:20px"> <label for="show_advanced" class="label_check_box"><input type="checkbox" id="show_advanced" name="show_advanced"> Advanced</label></span></td>
				</tr>
				</table>
				<div id="new_advanced_area" style="display:none;">
				<?php 
				echo $this->element('adavanced_document_search', array('doc_types' => $doc_types,'saved_search_array'=>(!empty($saved_search_array["doc_type"]))?$saved_search_array["doc_type"]:'','doc'=>$doc)); 
				?>
			<!--	<table border='1' style="margin-top:10px;vertical-align:none;">
					<tr style="vertical-align:none;">
						
				<td>Document Type: </td>
				<td>
				
					<?php
					/*
					$options =array();
					$doc_types_array = $doc_types;
					
					for ($i = 0; $i < count($doc_types_array); ++$i)
					{
						$options[$doc_types_array[$i]] = $doc_types_array[$i];
						//$options .= "<option value=\"$doc_types_array[$i]\">".$doc_types_array[$i]."</option>";
					}
					$options['Online Form'] = "Online Form";
					*/	
					//$options .= "<option value='Online Form'>Online Form</option>";
					
					?>
					<!--
					<select id="doc_type" style="border: 1px solid #AAAAAA;margin-left:5px;margin-right:5px;padding: 5px;">
					<option value="">All</option>
						<?php //echo $options; ?>
					</select> -->
					<?php 
					/*
					if(!empty($saved_search_array["doc_type"])){
					//$options = explode(",",$saved_search_array["doc_type"]);
					echo $form->input('doc_types', array('type' => 'select','multiple'=>'true','options' => $options,'selected'=>$saved_search_array["doc_type"], 'style'=>'width:200px;','label' => false, 'id' => 'doc_types'));
					} else {
						if(!empty($doc)){
							echo $form->input('doc_types', array('type' => 'select','multiple'=>'true','options' => $options, 'selected' => $doc, 'style'=>'width:200px;','label' => false, 'id' => 'doc_types'));
						} else { 
							
							echo $form->input('doc_types', array('type' => 'select','multiple'=>'true','options' => $options, 'selected' => '', 'style'=>'width:200px;','label' => false, 'id' => 'doc_types'));
						}
					} */
					 ?>
<!--
				</td>
						<td style="padding-left:10px;">Service Date:</td>				
				<td><?php //echo $this->element("date", array('name'=>'from_date','id' => 'from_date', 'value' => '', 'required' => false)); ?>
				</td>
				<td><?php //echo $this->element("date", array('name'=>'to_date','id' => 'to_date', 'value' => '', 'required' => false)); ?>
				</td>
				<td style="padding-left:10px;">Status: </td><td>
					<select id='doc_status' style="border:1px solid #AAAAAA;margin-left:5px;margin-right:5px;padding:5px;" >
							<option value="">All</option>
							<?php/*
								$statuses_array = array("Open", "Reviewed");
								for ($i = 0; $i < count($statuses_array); ++$i)
								{
									echo "<option value=\"$statuses_array[$i]\" >".$statuses_array[$i]."</option>";
								} */
							?>
							</select>
					</td>
					
					<td><input type="button"  id="save_filter" class="btn" value="Save"></td>
			</tr>
		</table> -->
		</div>
		</div>
		<div id="docs_content">
        <form id="frmPatientDocumentsGrid" method="post" accept-charset="utf-8">
            <table cellpadding="0" cellspacing="0" class="listing" border=1>
                <tr>
                    <?php if($page_access == 'W'): ?><th width="3%"><label for="master_chk_documents" class="label_check_box_hx"><input type="checkbox" id="master_chk_documents" class="master_chk" /></label></th><?php endif; ?>
                    <th colspan="2"><?php echo $paginator->sort('Document Name', 'document_name', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
                    <th width="200"><?php echo $paginator->sort('Document Type', 'document_type', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>				
                    <th width="120"><?php echo $paginator->sort('Service Date', 'service_date', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
                    <th width="120"><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
                    <th width="80">Fax</th>
                </tr>
                <?php
                $i = 0;
                foreach ($patient_documents as $patient_document):
                  
                  $formDataId = '';
                  if ($patient_document['PatientDocument']['document_type'] == 'Online Form') {
                    $formDataId = $patient_document['PatientDocument']['attachment'];
                  }
                  
                ?>
                <tr id="parent_tr_<?php echo $patient_document['PatientDocument']['document_id']; ?>" filename="<?php echo $patient_document['PatientDocument']['attachment']; ?>" class="document_row" isopen="false" document_id="<?php echo $patient_document['PatientDocument']['document_id']; ?>" editlinkajax="<?php echo $html->url(array('action' => 'documents', 'task' => 'edit', 'patient_id' => $patient_id, 'document_id' => $patient_document['PatientDocument']['document_id'])); ?>">
                    <?php if($page_access == 'W'): ?><td class="ignore"><label for="child_chk<?php echo $patient_document['PatientDocument']['document_id']; ?>" class="label_check_box_hx"><input name="data[PatientDocument][document_id][<?php echo $patient_document['PatientDocument']['document_id']; ?>]" id="child_chk<?php echo $patient_document['PatientDocument']['document_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $patient_document['PatientDocument']['document_id']; ?>" /></td><?php endif; ?>
                    <td class="ignore" width="18">
                    	<div class="link_hash" style="float: left; margin-top: 3px; cursor: pointer;"><?php echo $html->image('valid_hash.png', array('alt' => '')); ?></div>
                    </td>
                    <td class="<?php echo ($patient_document['PatientDocument']['attachment'] !="" || $patient_document['PatientDocument']['document_type'] == 'Online Form')?'ignore':'';?>">
                    <?php 
                    if($patient_document['PatientDocument']['attachment']!="" && $patient_document['PatientDocument']['document_type'] != 'Online Form')
                    {
                    	echo $html->link($patient_document['PatientDocument']['document_name'], array('action' => 'documents', 'task' => 'download_file', 'document_id' => $patient_document['PatientDocument']['document_id']),array('escape'=>false));
                    	echo $this->Html->image("download.png", array(    "alt" => "Download",    'url' => array('action' => 'documents', 'task' => 'download_file', 'document_id' => $patient_document['PatientDocument']['document_id'] )));
						
						if($patient_document['PatientDocument']['document_type'] == 'Continuity of Care Record' || $patient_document['PatientDocument']['document_type'] == 'Continuity of Care Document')
						{
							echo '&nbsp;<a href="javascript:void(0);" onclick="view_ccr_ccd(\''.$patient_document['PatientDocument']['attachment'].'\');">(View/Import)</a>';
						}
                    } else if ($patient_document['PatientDocument']['document_type'] == 'Online Form') {
											echo $this->Html->link($patient_document['PatientDocument']['document_name'], array('controller' => 'forms', 'action' => 'view_html_data', 'data_id' => $formDataId), array('class' => 'formdata-link'));
										}
                    else {
                        echo $patient_document['PatientDocument']['document_name'];
                    }
                    ?>
                    </td>
                    <td><?php echo $patient_document['PatientDocument']['document_type']; ?></td>					
                    <td><?php echo __date($global_date_format, strtotime($patient_document['PatientDocument']['service_date'])); ?></td>
                    <td><?php echo $patient_document['PatientDocument']['status']; ?></td>
					<?php if($patient_document['PatientDocument']['document_type'] != 'Continuity of Care Record' && $patient_document['PatientDocument']['document_type'] != 'Continuity of Care Document' && ($patient_document['PatientDocument']['attachment']!="" || $patient_document['PatientDocument']['document_type'] == 'Online Form') ): ?>
                   	<td>
                    	<a href='<?php echo $html->url(array('controller'=>'messaging', 'action' => 'new_fax', 'document',$patient_document['PatientDocument']['document_id']));?>'><?php echo $html->image('fax_icon.jpg', array('alt' => 'fax out')); ?></a>
                    </td>
					<?php else: ?>
					<td class="ignore">
						<a href="" onclick="showInfo('No attachment to FAX.', 'error');return false;"><?php echo $html->image('fax_icon.jpg', array('alt' => 'fax out')); ?></a>
					</td>
					<?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </table>
        </form>
        
        <?php if($page_access == 'W'): ?>
        <div style="width: auto; float: left;">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add Document</a></li>
										<li><a href="<?php echo $this->Html->url(array('controller'=> 'administration', 'action' => 'online_forms', 'patient_id' => $patient_id)); ?>">Add Form</a></li>
                    <li><a href="javascript:void(0);" onclick="deleteData('frmPatientDocumentsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientDocument', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientDocument') || $paginator->hasNext('PatientDocument'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientDocument'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientDocument', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientDocument', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientDocument', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
		</div>
		
					<div class="past_visit_close"></div>
					<iframe class="past_visit_load" src="" frameborder="0" ></iframe>
							<script language="javascript" type="text/javascript">
									$(function() {
							$('.formdata-link').bind('click',function(event)
							{
								event.preventDefault();
								var href = $(this).attr('href');
								$('.past_visit_load').attr('src',href).fadeIn(400,
								function()
								{
										$('.past_visit_close').show();
										$('.past_visit_load').load(function()
										{
												$(this).css('background','white');

										});
								});
							});

							$('.past_visit_close').bind('click',function(){
							$(this).hide();
							$('.past_visit_load').attr('src','').fadeOut(400,function(){
								$(this).removeAttr('style');
								});
							});
						});
						 </script>			
        <?php
    }
    ?>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
<script>
$('input[name=selectAll]').click(function(){
				processRequest();
			});
</script>

