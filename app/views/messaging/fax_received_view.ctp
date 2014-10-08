<!--http://docs.google.com/viewer?pli=1 -->
<?php echo $this->FaxConnectionChecker->checkConnection(); ?>
<?php
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$mark_as = (isset($this->params['named']['mark_as'])) ? $this->params['named']['mark_as'] : "";
/*$tabs =  array(
        'Incoming Fax' => 'fax',
        'Outgoing Fax' => array('messaging'=> 'fax_outbox')
);

echo $this->element('tabs',array('tabs'=> $tabs));*/
?>
<?php echo $this->FaxConnectionChecker->checkConnection(); ?>
<div class="title_area">
        <div class="title_text">
   <?php 
   echo $html->link('Incoming Fax', array('action' => 'fax', 'task' => $task, 'patient_id' => $patient_id), array('class' => 'active'));
   echo $html->link('Outgoing Fax', array('action' => 'fax_outbox', 'task' => $task, 'patient_id' => $patient_id));
   ?>
        </div>
</div>
<script language="javascript" type="text/javascript">
var __isiPadApp = readCookie('iPad');	
	
$(document).ready(function() {
        
        $('#container').html('Loading Document...');
        <?php if(is_numeric($filename)): ?>
        //fax is not loaded so we need to load it...
        
    $.get('<?php echo $html->url(array('controller' => 'file_handler', 'action' => 'getFaxDocument', $filename)); ?>',function(url) {
                
                
								if (__isiPadApp) {
									$('#container').html('<p class="center"><a class="btn no-float" href="'+url+'">View Document</a></p>');
									
								} else {
									var _url = url+'#zoom=100&scrollbar=1&toolbar=1&navpanes=0';
									$('#container').html("<iframe src='"+_url+"' width='100%' style='min-width: 600px; min-height:600px;height:auto'>Download</iframe>");
								}
                

    });
        <?php else: ?>
								if (__isiPadApp) {
									$('#container').html('<p class="center"><a class="btn no-float" href="<?php echo $file_url;?>">View Document</a></p>');
									
								} else {
									$('#container').html("<iframe src='<?php echo $file_url;?>#zoom=100&scrollbar=1&toolbar=1&navpanes=0' width='100%' style='min-width: 600px; min-height:600px;height:auto'>Download</iframe>");
								}
        
        
        <?php endif; ?>
        
        $('#patient_id_gettter').editable('<?php echo $html->url('fax_save_patient_id/');?>',
        {
           
                type      : 'text',
                id : 'patient_id_txt',
                width     : 220,
                height    : 25,
                cancel    : '<button class="btn">Cancel</button>', 
                submit    : '<button type="submit" class="btn">OK</button>',
                tooltip   : 'Click here to add patient',
                <?php if(!$fax['patient_id']): ?>
                placeholder: 'Enter Patient name here',
                <?php else: ?>
                placeholder: '<?php echo patient::getPatientName($fax['patient_id']); ?>',
                <?php endif; ?>
                submitdata : function(value, settings) {
                        return {'data[patient_id]': $('#patient_id').val(),'data[fax_id]': '<?php echo $fax['fax_id'];?>'};
                },
                onedit  : function() {
                
                        setTimeout(function() {
                        
                                $('form input[name=value]').unbind('blur');
                                $('form input[name=value]').autocomplete('<?php echo $html->url('fax_patient_list_and_patient_id_ac/'); ?>', {
                                        minChars: 2,
                                        max: 20,
                                        mustMatch: false,
                                        matchContains: false,
                                        width: 400,
                                        formatItem: function(data, i, total) {
                                                return data[0] + ' (DOB: ' + data[2] +') ';
                                        }                                           
                                });
                                
                                $('form input[name=value]').result(function(event, data, formatted) {
                                        
                                        $('#patient_id').val(data[1]);
                                });
                        
                        },100);
                },
                callback : function (result, settings) {
									var patientId = $('#patient_id').val();
									var $patientDetails = $('#patient_details');
									
									if ($patientDetails.length) {
										$patientDetails.attr('href', 
										'<?php echo $this->Session->webroot;?>patients/index/task:edit/patient_id:' + patientId +'/view:general_information'									
									);
										
										
									}
										
										
									$.post('<?php echo $this->here; ?>', {
										'get_status' : patientId
									}, function(val){
										$('#status-row').removeClass('hidden');
										$('#doc-status').val(val);
									})
                }
        });
		
		$('#update_fax_document_name').editable('<?php echo $this->here; ?>',
        {
                type      : 'text',
                id 		  : 'fax_file_names',
                width     : 220,
                height    : 25,
                cancel    : '<button class="btn">Cancel</button>', 
                submit    : '<button class="btn">OK</button>',
                tooltip   : 'Click here to enter Document name',
                placeholder: 'Enter Document name here',
                submitdata : function(value, settings) {
                	return {'data[fax_id]': '<?php echo $fax['fax_id'];?>', 'data[task]': 'add_doc_name'};
                },
        });
        
        <!--<?php echo $html->url(array('action' => 'phone_calls', 'task' => 'autocomplete')); ?>-->
        //$("#provider_text").autocomplete('<?php echo $html->url(array('action' => 'phone_calls', 'task' => 'autocomplete')); ?>', 
        $("#provider_text").autocomplete('<?php echo $html->url(array('action' => 'phone_calls', 'task' => 'autocomplete')); ?>', 
    {
        cacheLength: 20,
        minChars: 2,
        max: 20,
        mustMatch: false,
        matchContains: false,
        scrollHeight: 300
    });
    $("#provider_text").result(function(event, data, formatted)
    {
                 $("#provider_id").val(data[1]);
                 
                 //Assigning required values via post method 
                /* var formobj = $("<form></form>");
                 var fax_id = '<?php echo $fax['fax_id'];?>';
                 var patient_id = '<?php echo $fax['patient_id']; ?>';
                 var recvid = '<?php echo $fax['recvid']; ?>';
                 var notify = $('.notify_radio').val();
                 var provider_text = $('#provider_text').val();
                 var provider_id = $('#provider_id').val();
                 formobj.append('<input name="data[fax_id]" id="fax_id" type="hidden" value="'+fax_id+'">');
                 formobj.append('<input name="data[patient_id]" id="patient_id" type="hidden" value="'+patient_id+'">');
                 formobj.append('<input name="data[recvid]" id="recvid" type="hidden" value="'+recvid+'">');
                 formobj.append('<input name="data[notify]" id="notify" type="hidden" value="'+notify+'">');
                 formobj.append('<input name="data[provider_text]" id="provider_text" type="hidden" value="'+provider_text+'">');
                 formobj.append('<input name="data[provider_id]" id="provider_id" type="hidden" value="'+provider_id+'">');
                //Passing values via post method to controller
                 $.post('<?php echo $this->Session->webroot; ?>messaging/phone_calls/task:notifyProvider/', 
                 formobj.serialize(), 
                 function(data)
                 { 
                 },
                 'json'
                 );*/
    });
    
    
    var 
        $notifyRadio = $("#notify_radio"),
        $providerInfo = $('#provider_info')
    ;
        
    $notifyRadio
        .buttonset()
        .bind('checkNotification', function(evt){
            var opt = $(this).find('input.notify_radio:checked').val();
            
            if (opt === '1') {
                $providerInfo.show();
                                $('#notify_provider').show();
            } else {
                $providerInfo.hide();
                                $('#notify_provider').hide()
            }
            
        })
        .trigger('checkNotification')
        .find('input.notify_radio')
            .click(function(evt){
                $notifyRadio.trigger('checkNotification');
            })
        
        $('#notify_provider').click(function()
        { 
                 //Assigning required values via post method 
                 var formobj = $("<form></form>");
                 var fax_id = '<?php echo $fax['fax_id'];?>';
                 if($('#patient_id').val() == 0)
                 {
                 var patient_id = '<?php echo $fax['patient_id']; ?>';
                 }
                 else
                 {
                 var patient_id = $('#patient_id').val();
                 }
                 var recvid = '<?php echo $fax['recvid']; ?>';
                 var notify = $('.notify_radio').val();
                 var provider_text = $('#provider_text').val();
                 
                 formobj.append('<input name="data[fax_id]" id="fax_id" type="hidden" value="'+fax_id+'">');
                 formobj.append('<input name="data[patient_id]" id="patient_id" type="hidden" value="'+patient_id+'">');
                 formobj.append('<input name="data[recvid]" id="recvid" type="hidden" value="'+recvid+'">');
                 formobj.append('<input name="data[notify]" id="notify" type="hidden" value="'+notify+'">');
                 formobj.append('<input name="data[provider_text]" id="provider_text" type="hidden" value="'+provider_text+'">');
                 
                //Passing values via post method to controller
                 $.post('<?php echo $this->Session->webroot; ?>messaging/phone_calls/task:notifyProvider/', 
                 formobj.serialize(), 
                 function(data)
                 { 
                 window.location = '<?php echo $this->Session->webroot; ?>messaging/fax/notified:1';
                 },
                 'json'
                 );
        
        });
        
        
});

</script>
<h2>Fax</h2>
<?php //echo "<pre>"; print_r($fax); exit;?>
<form id="frm_fax" method="post" action="" accept-charset="utf-8" enctype="multipart/form-data">
        <input type=hidden name="reply" id="reply" value="">
    <table cellpadding="0" cellspacing="0" class="form" width="85%">
        <tr height=35>
            <td width="150">
                                <label>Patient:</label>
                        </td>
                         <td width="150">
                                <input type='hidden' id='patient_id' name='data[patient_id]' value='<?php echo ($fax['patient_id']) ? $fax['patient_id'] : '0';  ?>' />
                                 <div class="editable_field" id="patient_id_gettter" style="width:220px; float:left">
                                 </div>
                                 <?php if( !empty($fax['patient_id']) )
                                 { ?>
                                 <div style="float:left;margin-left:10px "><a id="patient_details" class=btn href="<?php echo $this->Session->webroot;?>patients/index/task:edit/patient_id:<?php echo trim(isset($fax['patient_id'])?$fax['patient_id']:""); ?>/view:general_information">Go to Chart >></a></div>
                                 <?php }?>
                        </td>
        </tr>
        <tr height=35>
            <td width="150"><label>Document Name:</label></td>
            <td width="150"><div class="editable_field" id="update_fax_document_name" style="width:220px;"><?php echo $fax['fax_file_names']; ?></div></td>
        </tr>
        <tr height=35>
            <td width="150">
                <label>Sender:</label>
            </td>
            <td>
                <?php echo $fax['sender']; ?>
            </td>
        </tr>
        <tr height=35>
            <td><label>Receiver:</label></td>
            <td>
                <?php echo $fax['receiver']; ?>
            </td>
        </tr>
        <tr height=35>
            <td width="150">
                                <label>Received:</label>
                        </td>
                         <td>
                                <?php echo date( site::dateFormat(), strtotime($fax['starttime']) ); ?>         
                        </td>
        </tr>
				<tr height="35" id="status-row" class="<?php echo ($document) ? '' : 'hidden'; ?>">
					<td width="150">
						<label>Status:</label>
					</td>
					<td>
						<select name="doc-status" id="doc-status">
							<?php 
							
								$status = ($document) ? ucwords(strtolower($document['PatientDocument']['status'])) : '';
							?> 
							<option value="Open" <?php echo ($status == 'Open') ? 'selected="selected"' : ''; ?>>Open</option>
							<option value="Reviewed" <?php echo ($status == 'Reviewed') ? 'selected="selected"' : ''; ?>>Reviewed</option>
						</select>
					</td>
				</tr>
				
				
                <?php 
                        if($mark_as != 'reviewed')
                        { ?>
                <tr height=35>
                            <td style="vertical-align: top;" width="150"><label>Send Notification?</label></td>
                            <td>
                                <?php echo $this->element('notify_staff', array('model' => 'MessagingFax')); ?>
                            </td>
                        </tr>
                                                <?php } ?>
        </table>
</form>
<div class="actions">
                <ul><?php 
                        if($mark_as != 'reviewed')
                        { ?>
                        <li><a href="javascript: void(0);" id ="notify_provider">Notify Staff</a></li>
                        <?php } ?>
                        <li><?php echo $html->link(__('Cancel', true), array('action' => 'fax'));?></li>
                </ul>
        </div>
<div class="title_area"></div>
<div id='container'>
<div style='text-align:center'>Loading Document</div>
</div>
<script type="text/javascript">
	
	$(function(){
		var $docStatus = $('#doc-status');
		var url = '<?php echo $this->here; ?>';
		
		$docStatus.change(function(){
			var patientId = $('#patient_id').val();
			var status = $(this).val();
			$.post(
				url, 
				{
					'patient_id' : patientId,
					'status': status
				}, 
				function(){
				
			});
			
			
			
		});
		
	});
	
	
</script>