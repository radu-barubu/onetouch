 <?php 
 $task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$deleteURL = $html->url(array('task' => 'delete', 'patient_id' => $patient_id)) . '/';
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$document_id = (isset($this->params['named']['document_id'])) ? $this->params['named']['document_id'] : "";

$page_access = $this->QuickAcl->getAccessType('patients', 'attachments');
echo $this->element("enable_acl_read", array('page_access' => $page_access));

echo $this->Html->script('document_types.js');
 
?>
<script>
	initCurrentTabEvents('patient_document_area');
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


</script>
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
                <tr id="parent_tr_<?php echo $patient_document['PatientDocument']['document_id']; ?>" filename="<?php echo $patient_document['PatientDocument']['attachment']; ?>" class="document_row" isopen="false" document_id="<?php echo $patient_document['PatientDocument']['document_id']; ?>" editlinkajax="<?php echo $html->url(array('action' => 'documents', 'task' => 'edit', 'patient_id' => $patient_id, 'document_id' => $patient_document['PatientDocument']['document_id'])); ?>" style="cursor:pointer;">
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
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
