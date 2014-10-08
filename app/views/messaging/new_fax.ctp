<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
if (!isset($recipname)) {
    $recipname = '';
}

if (!isset($faxno)) {
    $faxno = '';
}


/*$tabs =  array(
	'Incoming Fax' => 'fax',
	'Outgoing Fax' => array('messaging'=> 'fax_outbox')
);

echo $this->element('tabs',array('tabs'=> $tabs));*/

if(!isset($priority)) $priority="";

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

var document_id = '<?php echo isset($document_id)? $document_id: ''; ?>';

$(document).ready(function()
{
	
	$("#recipname").autocomplete('<?php echo $html->url('fax_patient_list_and_fax_ac/'); ?>', {
		minChars: 2,
		max: 20,
		mustMatch: false,
		matchContains: false,
                width: 400,
                formatItem: function(data, i, total) {
			ret = data[0];
			if(data[2]) ret += ' ('+data[2]+')';
                        return ret;
                }                                           
	});
	
	$("#recipname").change(function(){
		switch($(this).val()) {
			case 'local':
				$('#faxno').val('<?php echo fax::getLocalFaxNumber();?>');
			break;
			case 'test':
				$('#faxno').val('<?php echo fax::getLocalFaxNumberTest();?>');
			break;
		}
	});
	
	$("#recipname").result(function(event, data, formatted){
		if(data[1]) {
			$('#faxno').val(data[1]);
		}
	});
		
	$('#bt_send').click(function() {
		$("#frm_sendfax").submit();
		if (document_id == '' && $('#fax_id').val() == '')
		{
			$('#file_upload_error').show();
			$('.file_upload_desc').css("background", "#FBE3E4");
		}
	});
	
	$("#frm_sendfax").validate(
	{
		debug: true, 
		errorElement: "div", 
		submitHandler: function(form) {
	
		if(document_id) {
			$('#sending').html("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			$('#send').html('Sending...');
			
			var faxType = 'none';
			
			if ($('#fax_type').length) {
				faxType = $("#fax_type").val();
			}
			$.post('<?php echo $this->Session->webroot; ?>messaging/new_fax/'+faxType+'/'+document_id, $("#frm_sendfax").serialize(), function(response) {
				
				if(response && response.error) {
					
					switch(response.id) {
						case 'ERR02':
						case 'ERR04':
							message('An error Occurred: '+response.error + "- Please check your fax settings.");
							
							$('#sending').html('');
							$('#send').html('Error!');
						break;
						default:
							message('An error Occurred: '+response.error);
							$('#sending').html('');
							$('#send').html('Error!');
					}
				} else {
					$('#sending').html('');
					$('#send').html('Sent!');
					window.location.replace('<?php echo $html->url(array('controller' => 'messaging', 'action' => '/fax_outbox'));?>');
				}
            },'json');
		
		} else if( $('#fax_id').val()  ) {
			$('#sending').html("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			$('#send').html('Sending...');
			
			var faxType = 'none';
			
			if ($('#fax_type').length) {
				faxType = $("#fax_type").val();
			}
			
			$.post('<?php echo $this->Session->webroot; ?>messaging/new_fax/'+ faxType, $("#frm_sendfax").serialize(), function(response) {
			
				if(response && response.error) {
				
					switch(response.id) {
						case 'ERR02':
							message('An error Occurred: '+response.error + "- Please check your fax settings.");
							
							$('#sending').html('');
							$('#send').html('Error!');
						break;
						default:
							message('An error Occurred: '+response.error);
							$('#sending').html('');
							$('#send').html('Error!');
					}
				} else {
					$('#sending').html('');
					$('#send').html('Sent!');
					window.location.replace('<?php echo $html->url(array('controller' => 'messaging', 'action' => '/fax_outbox'));?>');
				}
            },'json');
            
		} else {
			
			//alert('Please select a document');
			return false;
		}
		return true;
	}, rules: {
			faxno: {
				required: true
			},
		    recipname: {
		      required: true,
		      minlength: 3
		    }
	},
	messages: {
			faxno: {
				required: "Please enter a valid fax number"
			},
			recipname: {
				required: "Enter the name the fax is going to"
			}
	}
});
});
</script>
<div id='message'></div>
<form class='form' id='frm_sendfax'>
<input type="hidden" name="data[MessagingFax][fax_id]" id="fax_id" value='<?php echo (isset($fax_id)? $fax_id:'');?>'>
<input type="hidden" name="data[MessagingFax][filename]" id="filename" value=''>
<table cellpadding="0" cellspacing="0" class="form">
	<tr>
    	<td colspan="2">
    	<table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td width="200"><label>Recipient Name:</label></td>
                <td style="padding-right: 10px;">
                <?php if (!empty($patient)) {  ?>
                <input type="hidden" name="patient" id="patient" value="<?php echo $patient ?>"> 
                <?php echo "<a href=\"".$this->Session->webroot."patients/index/task:edit/patient_id:$patient_id/view:medical_information\">".$patient."</a>";
                
                } else { 
                ?>
                <input type="text" name="data[MessagingFax][recipname]" id="recipname" class="required" style="width:200px;" value="<?php echo $recipname; ?>">
                <?php } ?>
                </td>
                <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
            </tr>
        </table></td>
	</tr>
	<tr>
    <td colspan="2">
    	<table cellpadding="0" cellspacing="0" class="form">
            <tr>
                <td width="200">
                	<label>Recipient Fax Number:</label>
                </td>
                <td style="padding-right: 10px;">
                <input type="text" name="data[MessagingFax][faxno]" id="faxno" style="width:200px;" class="required phone" value="<?php echo $faxno; ?>">
                </td>
                <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
            </tr>
        </table></td>
	</tr>
	<tr>
	    <td width="200">
	    	<label>Priority:</label>
	    </td>
	    <td>
		    <select name="data[MessagingFax][priority]" id=priority>
	            <option value="Normal" <?php echo ($priority=="Normal"?"selected":""); ?>>Normal</option>
	            <option value="Low" <?php echo ($priority=="Low"?"selected":""); ?>>Low</option>
	            <option value="High" <?php echo ($priority=="High"?"selected":""); ?>>High</option>
	        </select>
        </td>
</tr>
<tr>
    <td style="vertical-align:top; padding-top: 5px; width: 200px;"><label>Fax Document:</label></td>
    <td>
<?php if($fax_type=='document' && $document_id):?>
	 <input type="hidden" name="data[MessagingFax][fax_type]" id="fax_type" value="document">
	<?php

	echo $document['document_name'] . ' - '. $html->link($document['attachment'], array('controller'=>'patients','action' => 'documents', 'task' => 'download_file', 'document_id' => $document['document_id'])); 
	
	
	?>
<?php elseif($fax_type=='plan_referral'): ?>
	<input type="hidden" name="data[MessagingFax][fax_type]" id="fax_type" value="plan_referral">
	Plan/Referral 
    <?php echo $this->Html->link(
            '( Preview )', 
            array(
                'controller'=> 'encounters',
                'action' => 'plan_referrals_data',
                'plan_referrals_id' => $referral['plan_referrals_id'],
                'encounter_id' => $referral['encounter_id'],
                'task' => 'referral_preview'
            ), 
            array(
                'target' => '_blank'
            )); ?> 
<?php elseif($fax_type=='fax_doc'): ?>
	<input type="hidden" name="data[MessagingFax][fax_type]" id="fax_type" value="fax_doc">
	<input type="hidden" name="data[MessagingFax][fileName]" id="fax_type" value="<?php echo $fileName;?>">
	Document/Fax    

<?php else: ?>
    	<table cellpadding="0" cellspacing="0">
            <tr>
                <td>
                	<div class="file_upload_area" style="position: relative; width: 264px; height: auto !important">
						<div class="file_upload_desc" style="position: absolute; top: 1px; left: 1px; height: 18px; width: 252px; text-align: left; padding: 5px; overflow: hidden; color: #000000;">
						</div>
						<div class="progressbar" style="-moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;">
						</div>
						<div style="position: absolute; top: -1px; right: -125px;">
							<div style="position: relative;"> <span class="btn" style="float: left; margin-top: -2px;">Select File...</span>
								<div id='file_container' style="position: absolute; top: 0px; left: 0px;">
									<input id="file_upload" name="file_upload" type="file" class="required"/>
								</div>
							</div>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td style="padding-top: 10px;">
					<div id="file_upload_error" class="error" style="display:none">This field is required.</div>
				</td>
			</tr>
        </table>

<script language="javascript" type="text/javascript">
$(function()
{
	
  	$(".progressbar").progressbar({value: 0});
		
							$('#file_upload').uploadify(
							{
								// this was put into the library itself
								//'fileDesc' : 'Fax Document',
								//'fileExt' : '*.pdf;*.docx;*.doc;*.jpg;*.png',
								'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
								'script'    : '<?php echo $html->url(array('controller' => 'file_handler', 'action' => 'fax', 'session_id' => $session->id())); ?>',
								'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
								'auto'	  : true,
								'wmode'	 : 'transparent',
								'hideButton': true,
								'onSelect'  : function(event, ID, fileObj) 
								{
										$('#file_upload_error').hide();

										$('.file_upload_desc').html(fileObj.name);

										$('.ui-progressbar-value').css("visibility", "hidden");
										$('.progressbar').progressbar("value", 0);

										$('.file_upload_desc').css('border', 'none');
										$('.file_upload_desc').css('background', 'none');

										$('#attachment').val(fileObj.name);
					
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
									$('.file_upload_desc').html(fileObj.name+ " - ready");
									if(response) {
										response = eval("("+response+")");
										$('#fax_id').val(response.fax_id);
										$('#filename').val(response.filename);

									}									
									
								},
								'onError'   : function(event, ID, fileObj, errorObj) 
								{
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
<?php endif; ?>
    	</td>
    </tr>
	<tr>
		<td colspan="3">
			<div class="actions">
			<ul>
		      
		        <li><a id='bt_send' href="javascript: void(0);"><div  id='sending' class='flower' style='float:left;'>&nbsp;</div><div id='send' style='float: left'>Send</div></a></li>
		  		  <li><?php echo $html->link(__('Cancel', true), array('action' => 'fax'));?></li>
		    </ul>
			</div>
		</td>
	</tr>
</table>
</form>
</div>
