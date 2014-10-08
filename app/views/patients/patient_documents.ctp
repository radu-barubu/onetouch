<?php
/*
	NOTE: This view file is identical to documents.ctp
	so any updates here will likely need updated there too

*/


$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$deleteURL = $html->url(array('task' => 'delete', 'patient_id' => $patient_id)) . '/';
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';
$plan_lab_link = $html->url(array('action' => 'plan_labs', 'patient_id' => $patient_id));

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$document_id = (isset($this->params['named']['document_id'])) ? $this->params['named']['document_id'] : "";

$lab_result_link = $html->url(array('controller' => 'patients', 'action' => 'lab_results', 'patient_id' => $patient_id));
if($session->read('PracticeSetting.PracticeSetting.labs_setup') == 'Electronic' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'MacPractice' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'HL7Files')
{
	$lab_result_link = $html->url(array('controller' => 'patients', 'action' => 'lab_results_electronic', 'patient_id' => $patient_id));
}

$page_access = $this->QuickAcl->getAccessType('patients', 'attachments');
echo $this->element("enable_acl_read", array('page_access' => $page_access));
?>
<script language="javascript" type="text/javascript">
$(document).ready(function()
{
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
            //$('#frmPatientDocuments').css("cursor", "wait");
			
			if($('#attachment_is_selected').val() == 'false' && $('#attachment_is_uploaded').val() == 'false')
			{
				alert("Please Select file");
				return false;
			}
			
			if($('#attachment_is_selected').val() == 'true' && $('#attachment_is_uploaded').val() == 'false')
			{
				$('#frmPatientDocuments').css("cursor", "wait");
				//wait 1 second before submitting the form
				window.setTimeout("$('#frmPatientDocuments').submit();", 1000);
			}
			else
			{
				$('#frmPatientDocuments').css("cursor", "wait");
				$.post(
					'<?php echo $thisURL; ?>', 
					$('#frmPatientDocuments').serialize(), 
					function(data)
					{
						showInfo("<?php echo $current_message; ?>", "notice");
						loadTab($('#frmPatientDocuments'), '<?php echo $mainURL; ?>');
					},
					'json'
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
	
	$('#outsideLabBtn').click(function()
		{		
            $(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $lab_result_link; ?>");
		});
		
		$('#pointofcareBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $html->url(array('action' => 'in_house_work_labs', 'patient_id' => $patient_id)); ?>");
		});
		
		/*$('#documentsBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'patient_documents', 'patient_id' => $patient_id)); ?>");
		});*/
		
		$('#planLabBtn').click(function()
		{		
            $(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $plan_lab_link; ?>");
		});
        
        $('#documentsBtn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadInhouseLab").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'patient_documents', 'patient_id' => $patient_id)); ?>");
		});
    
});
</script>
<style type="text/css"> .ui-tabs-nav { height:37px; } .ui-tabs .ui-tabs-panel { padding:25px 0px; } </style>
<div style="overflow: hidden;width:100%">
    <div class="title_area">
	<div class="title_text">
        <a href="javascript:void(0);" id="pointofcareBtn"  style="float: none;">Point of Care</a>
        <?php if($session->read('PracticeSetting.PracticeSetting.labs_setup') == 'Electronic' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'MacPractice' || $session->read('PracticeSetting.PracticeSetting.labs_setup') == 'HL7Files'): ?>
        <a href="javascript:void(0);" id="outsideLabBtn" style="float:none;">Outside Labs</a>
        <?php else: ?>
        <a href="javascript:void(0);" id="planLabBtn" style="float:none;">Outside Labs</a>
        <?php endif; ?>
		<a href="javascript:void(0);" id="documentsBtn" style="float:none;" class="active">Documents</a>
    </div>
    </div>
	<span id="imgLoadInhouseLab" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
<div id="patient_document_area" class="tab_area">
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
        ?>
		<form id="frmPatientDocuments" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
		<? echo $id_field; ?>
		<table cellpadding="0" cellspacing="0" class="form" width="100%">
			<tr>
				<td class="top_pos"><label>Document Name:</label></td>
				<td><div style="float:left;"><input type="text" name="data[PatientDocument][document_name]" id="document_name" class="required" style="width:984px;" value="<?php echo $document_name; ?>" /></div></td>
			</tr>
			<tr>
				<td width="130"><label>Type:</label></td>
				<td><input type="text" name="data[PatientDocument][document_type]" value="Lab" readonly="readonly" style="background:#eeeeee;">   <!-- <select name="data[PatientDocument][document_type]" id="document_type">
					<?php
					$document_type_array = array("Medical", "Lab", "Legal", "Personal", "Continuity of Care Record", "Continuity of Care Document");
					for ($i = 0; $i < count($document_type_array); ++$i)
					{
						echo "<option value=\"$document_type_array[$i]\"".($document_type==$document_type_array[$i]?"selected":"").">".$document_type_array[$i]."</option>";
					}
					?>
					</select> -->
				</td>
			</tr>		
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
				<td><?php echo $this->element("file_upload", array('model' => 'PatientDocument', 'name' => 'data[PatientDocument][attachment]', 'id' => 'attachment', 'value' => $attachment, 'fileExt' => '*.pdf;*.docx;*.doc;*.jpg;*.png;*.xml', 'fileDesc' => 'Documents')); ?></td>
			</tr>
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
                        <tr>
                            <td style="vertical-align: top;">Send Notification?</td>
                            <td>
                                <?php echo $this->element('notify_staff', array('model' => 'PatientDocument')); ?>
                            </td>
                        </tr>
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
							'<?php echo $html->url(array('action' => 'patient_documents', 'task' => 'validate_document')); ?>', 
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
        <form id="frmPatientDocumentsGrid" method="post" accept-charset="utf-8">
		<table cellpadding="0" cellspacing="0" class="listing">
		<tr>
		    <th width="3%"><label for="master_chk_documents" class="label_check_box_hx"><input type="checkbox" id="master_chk_documents" class="master_chk" /></label></th>
            <th colspan="2"><?php echo $paginator->sort('Document Name', 'document_name', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
			<th width="200"><?php echo $paginator->sort('Document Type', 'document_type', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>				
			<th width="120"><?php echo $paginator->sort('Service Date', 'service_date', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
			<th width="120"><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
			<!--<th width="80"><?php echo $paginator->sort('Fax', 'fax');?></th>-->
			 
        </tr>
		<?php 
		foreach ($PatientDocument as $PatientDocument_record):
		?>
		
		<?php
		//if($PatientDocument_record['PatientDocument']['document_type']=="Lab")
		//{
		 ?>
		    <tr id="parent_tr_<?php echo $PatientDocument_record['PatientDocument']['document_id']; ?>" filename="<?php echo $PatientDocument_record['PatientDocument']['attachment']; ?>" class="document_row" isopen="false" document_id="<?php echo $PatientDocument_record['PatientDocument']['document_id']; ?>" editlinkajax="<?php echo $html->url(array('action' => 'patient_documents', 'task' => 'edit', 'patient_id' => $patient_id, 'document_id' => $PatientDocument_record['PatientDocument']['document_id'])); ?>">
            <td class="ignore"><label for="child_chk<?php echo $PatientDocument_record['PatientDocument']['document_id']; ?>" class="label_check_box_hx"><input name="data[PatientDocument][document_id][<?php echo $PatientDocument_record['PatientDocument']['document_id']; ?>]" id="child_chk<?php echo $PatientDocument_record['PatientDocument']['document_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientDocument_record['PatientDocument']['document_id']; ?>" /></label></td>
			
			<td class="<?php echo ($PatientDocument_record['PatientDocument']['attachment']!="")?'ignore':'';?>">
                    	<div class="link_hash" style="float: left; margin-top: 3px; cursor: pointer;"><?php echo $html->image('valid_hash.png', array('alt' => '')); ?></div>
                   
                    
            <?php 
            if($PatientDocument_record['PatientDocument']['attachment']!="")
            {
             echo $html->link($PatientDocument_record['PatientDocument']['document_name'], array('action' => 'patient_documents', 'task' => 'download_file', 'document_id' => $PatientDocument_record['PatientDocument']['document_id'])); 
             echo $this->Html->image("download.png", array(    "alt" => "Download",    'url' => array('action' => 'patient_documents', 'task' => 'download_file', 'document_id' => $PatientDocument_record['PatientDocument']['document_id']) ));
             }
             else
             {
                  echo $PatientDocument_record['PatientDocument']['document_name'];
             }
			 ?>
             </td>
             <td><?php echo $PatientDocument_record['PatientDocument']['description'];?></td>
			 <td><?php echo $PatientDocument_record['PatientDocument']['document_type']; ?></td>
			 <td><?php echo __date($global_date_format, strtotime($PatientDocument_record['PatientDocument']['service_date'])); ?></td>
             <td><?php echo $PatientDocument_record['PatientDocument']['status']; ?></td>
			 <!--<td>
                    	<?php if($PatientDocument_record['PatientDocument']['document_type'] != 'Continuity of Care Record' && $PatientDocument_record['PatientDocument']['document_type'] != 'Continuity of Care Document'): ?>
                    	<a href='<?php echo $html->url(array('controller'=>'messaging', 'action' => 'new_fax', 'document',$PatientDocument_record['PatientDocument']['document_id']));?>'><?php echo $html->image('fax_icon.jpg', array('alt' => 'fax out')); ?></a>
                        <?php endif; ?>
                    </td>-->
		<?php 
		//}
	 ?>
	</tr>
	<?php endforeach; ?>
       </table>
		</form>
        
        <?php if($page_access == 'W'): ?>
        <div style="width: auto; float: left;">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
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
        <?php
    }
    ?>
</div>
</div>
