<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'radiology_results', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$saveURL = $html->url(array('action' => 'radiology_results', 'patient_id' => $patient_id, 'task' => 'save')) . '/';
$mainURL = $html->url(array('action' => 'radiology_results', 'patient_id' => $patient_id)) . '/';
$diagnosis_autoURL = $html->url(array('action' => 'radiology_results', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';    

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$radiology_result_id = (isset($this->params['named']['radiology_result_id'])) ? $this->params['named']['radiology_result_id'] : "";

echo $this->Html->script('ipad_fix.js');

echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));

?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
	    initCurrentTabEvents('radiology_results_area');
		
		$("#frmPatientRadiologyResults").validate(
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
                $('#frmPatientRadiologyResults').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmPatientRadiologyResults').serialize(), 
					
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmPatientRadiologyResults'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
		});
		
		<?php if($task == 'addnew' || $task == 'edit'): ?>
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'PatientRadiologyResult', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[plan_radiology_id]': function()
					{
						return $('#plan_radiology_id', $("#frmPatientRadiologyResults")).val();
					},
					'data[exclude]': '<?php echo $radiology_result_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#diagnosis", $("#frmPatientRadiologyResults")).rules("add", {required: true});
		$("#lab_facility_name", $("#frmPatientRadiologyResults")).rules("add", {required: true});
		$("#test_name", $("#frmPatientRadiologyResults")).rules("add", {required: true});
		$("#status", $("#frmPatientRadiologyResults")).rules("add", {required: true});
		<?php endif; ?>
		
		$("#lab_facility_name").autocomplete('<?php echo $this->Session->webroot; ?>patients/radiology_results/patient_id:<?php echo $patient_id; ?>/task:labname_load/',        {
			minChars: 2,
			max: 20,
			mustMatch: false,
			matchContains: false,
			scrollHeight: 300
		});
		
		$("#lab_facility_name").result(function(event, data, formatted)
		{
			$("#lab_address_1").val(data[1]);
			
			$("#lab_address_2").val(data[2]);
			
			$("#lab_city").val(data[3]);
			
			$("#lab_state").val(data[4]);
			
			$("#lab_zip_code").val(data[5]);
			
			$("#lab_country").val(data[6]);
			
		});
		
		$("#diagnosis").autocomplete('<?php echo $diagnosis_autoURL;?>', {
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
        
        $("#diagnosis").result(function(event, data, formatted)
        {
            //alert('1. '+data[0]+' 2. '+data[1]+' 3. '+data[2]);
            var code = data[0].split('[');
            var code = code[1].split(']');
            var code = code[0].split(',');
            $("#icd_code").val(code);
        });
		/*$('#attachment').uploadify(
		{
		'fileDataName' : 'file_input',
		'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
		'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
		'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
		'scriptData': {'data[path_index]' : 'temp'},
		'multi'		: true,
		'auto'      : true,
		'height'    : 35,
		'width'     : 192,
		'wmode'     : 'transparent',
		'hideButton': true,
		'onSelect'  : function(event, ID, fileObj) 
		{
			//$('#attachment_img').attr('src', '<?php echo $this->Session->webroot; ?>img/blank.png');
			$('#attachment_div').html("Uploading: 0%");
			return false;
		},
		'onProgress': function(event, ID, fileObj, data) 
		{
			$('#attachment_div').html("Uploading: "+data.percentage+"%");
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
			filenme =  filenme + filename + ", ";
			return true;
		},
		'onAllComplete': function(event, data) 
		{
			$('#attachment_div').html("");
			
			var filenamelist = filenme;
			$('#attachment_val').val(filenamelist);
			return true;
		},
		'onError'   : function(event, ID, fileObj, errorObj) 
		{
			return true;
		}
	    });*/	
		
		$('#radiologyPocBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadResultRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_radiology', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#planRadiologyBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadResultRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('action' => 'plan_radiology', 'patient_id' => $patient_id)); ?>");
		});
		
		$('#outsideRadiologyBtn').click(function()
		{
		    $("#sub_tab_table").css('display', 'none');
			$(".tab_area").html('');
			$("#imgLoadPlanRadiology").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'radiology_results', 'patient_id' => $patient_id)); ?>");
		});
		
    });

</script>
<div style="overflow: hidden;">    
    <div class="title_area">
		<div class="title_text"> 
            <a href="javascript:void(0);" id="radiologyPocBtn"  style="float: none;">Point of Care</a>
            <a href="javascript:void(0);" id="planRadiologyBtn" style="float: none;">Outside Radiology</a>
            <a href="javascript:void(0);" id="outsideRadiologyBtn" style="float: none;" class="active">Results</a>
    	</div>       
    </div>
	<span id="imgLoadResultRadiology" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	 <div id="radiology_results_area" class="tab_area">
    <?php
    if($task == "addnew" || $task == "edit")  
    { 
	 if($task == "addnew")
        {
		    $id_field="";
            $radiology_result_id="";
			$plan_radiology_id="";
			$diagnosis="";
			$icd_code="";
			$cpt="";
			$cpt_code="";
			$rad_report_id="";
			$ordered_by_id="";
			$ordered_by="";
			$date_ordered = $global_date_format;            
			$lab_facility_name="";
			$lab_address_1="";
			$lab_address_2="";
			$lab_city="";
			$lab_state="";
			$lab_zip_code="";
			$lab_country="";
			$report_date= $global_date_format;
			$test_name="";
			$result="";
			$comment  =""; 
			$attachment ="";
			$status ="";

        }
		else
		{
		    extract($EditItem['PatientRadiologyResult']);
				if (!strtotime($date_ordered)) {
					$date_ordered = '';
				} else {
					$date_ordered = __date($global_date_format, strtotime($date_ordered));
				}
				
				if (!strtotime($report_date)) {
					$report_date = '';
				} else {
					$report_date = __date($global_date_format, strtotime($report_date));
				}
				
				
		    $id_field = '<input type="hidden" name="data[PatientRadiologyResult][radiology_result_id]" id="radiology_result_id" value="'.$radiology_result_id.'" />';
		 }
    ?>
	 <form id="frmPatientRadiologyResults" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
	 <?php echo $id_field; ?>
	 <input type="hidden" name="radiology_result_id" id="radiology_result_id" value="<?php echo $radiology_result_id; ?>" />
	 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
				<tr>
	                <td><label>Diagnosis: <span class='asterisk'>*</span></label></td>
                    <td> <input type="text" name="data[PatientRadiologyResult][diagnosis]" id="diagnosis" value="<?php echo $diagnosis;?>" style="width:98%" />
					</td>
				</tr>
				<tr>
                    <input type="hidden" name="data[PatientRadiologyResult][icd_code]" id="icd_code" value="<?php echo $icd_code;?>" />
					<input type="hidden" name="data[PatientRadiologyResult][cpt]" id="cpt" value="<?php echo $cpt;?>" />
					<input type="hidden" name="data[PatientRadiologyResult][cpt_code]" id="cpt_code" value="<?php echo $cpt_code;?>" />
                </tr>
				<tr>
				    <td><label>Radiology Report ID:</label></td>
                    <td> <input type="text" name="data[PatientRadiologyResult][rad_report_id]" id="rad_report_id" value="<?php echo $rad_report_id;?>" style="width:170px;" />
					<input type="hidden" name="ordered_by_id" id="ordered_by_id" value="<?php echo $ordered_by_id; ?>" />
			        </td>
				</tr>
				<tr>
	                <td><label>Ordered by:</label></td>
                    <td> <input type="text" name="data[PatientRadiologyResult][ordered_by]" id="ordered_by" value="<?php echo $ordered_by;?>" style="width:170px;" />
					</td>
				</tr>
				<tr>
                    <td style="vertical-align:top; padding-top: 3px;"><label>Date Ordered:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientRadiologyResult][date_ordered]', 'id' => 'date_ordered', 'value' => $date_ordered, 'required' => false, 'width' => 170)); ?></td>
                </tr>
				<tr>
                    <td><label>Lab Facility Name: <span class='asterisk'>*</span></label></td>
                    <td> <input type="text" name="data[PatientRadiologyResult][lab_facility_name]" id="lab_facility_name" value="<?php echo $lab_facility_name;?>" style="width:480px;" /></td>
			    </tr>
			    <tr>
					<input type="hidden" name="data[PatientRadiologyResult][lab_address_1]" id="lab_address_1" value="<?php echo $lab_address_1;?>"/>
					<input type="hidden" name="data[PatientRadiologyResult][lab_address_2]" id="lab_address_2" value="<?php echo $lab_address_2;?>"/>
					<input type="hidden" name="data[PatientRadiologyResult][lab_city]" id="lab_city" value="<?php echo $lab_city;?>" />
					<input type="hidden" name="data[PatientRadiologyResult][lab_state]" id="lab_state" value="<?php echo $lab_state;?>"/>
					<input type="hidden" name="data[PatientRadiologyResult][lab_zip_code]" id="lab_zip_code" value="<?php echo $lab_zip_code;?>" />
					<input type="hidden" name="data[PatientRadiologyResult][lab_country]" id="lab_country" value="<?php echo $lab_country;?>"/>
			    </tr>
				<tr>
                    <td style="vertical-align:top; padding-top: 3px;">
                    <label>Report Date:</label>
                    </td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientRadiologyResult][report_date]', 'id' => 'report_date', 'value' => $report_date, 'required' => false, 'width' => 170)); ?></td>
               </tr>
			   <tr>
                   <td><label>Test Name: <span class='asterisk'>*</span></label></td>
                   <td> <input type="text" name="data[PatientRadiologyResult][test_name]" id="test_name" value="<?php echo $test_name;?>" style="width:170px;" />
                   </td>
			   </tr>
			   <tr>
			       <td valign='top' style="vertical-align:top"><label>Result:</label></td>
				   <td><textarea cols="20" name="data[PatientRadiologyResult][result]"  style="height:80px"><?php echo $result ?></textarea></td>
			   </tr>
			   <tr>
					<td valign='top' style="vertical-align:top"><label>Comment:</label></td>
					<td><textarea cols="20" name="data[PatientRadiologyResult][comment]"  style="height:80px"><?php echo $comment ?></textarea></td>
			   </tr>
			   <tr><td class="top_pos"><label>Attachment</label></td>
			  <td>
				
					<?php
					
						if ($attachment) {
							
							$paths['patient_id'] = $paths['patients'] . $patient_id . DS;
							$attachmentPath = UploadSettings::existing(
								$paths['patients'] . $attachment,
								$paths['patient_id'] . 'radiology' . DS . '0' . DS . $attachment
							);
							$attachmentUrl = UploadSettings::toURL($attachmentPath);
							
							if (file_exists($paths['temp'] . $attachment)) {
								echo $this->Html->link('Download ' . $this->Html->image('download.png'), $url_rel_paths['temp'].$attachment, array('escape' => false));
							} else if (file_exists($attachmentPath)) {
								echo $this->Html->link('Download ' . $this->Html->image('download.png'), $attachmentUrl, array('escape' => false));
							}
							
							
						}
					?> 					
					<?php echo $this->element("file_upload", array('model' => 'PatientRadiologyResult', 'name' => 'data[PatientRadiologyResult][attachment]', 'id' => 'attachment', 'value' => $attachment, 'fileExt' => '*.pdf;*.docx;*.doc;*.jpg;*.png;*.xml', 'fileDesc' => 'Documents')); ?>

				</td>
			</tr>
			  <tr>
                   <td width="140"><label>Status: <span class='asterisk'>*</span></label></td>
                   <td>
                   <select name="data[PatientRadiologyResult][status]" id="status"  >
                   <option value="" selected>Select Status</option>
                   <?php                    
                   $status_array = array("Pending Review", "Reviewed");
                   for ($i = 0; $i < count($status_array); ++$i)
                   {
                    echo "<option value=\"$status_array[$i]\" ".($status==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
                   }
                   ?>        
                   </select>
                   </td>
               </tr>
	   </table>
			   <div class="actions">
                <ul>
                    <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPatientRadiologyResults').submit();">Save</a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
     </form>  
	 	   <?php	
	}
	else
    {	  
	   ?>
    <form id="frmPatientRadiologyResultsGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
      <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">          
            
            <tr deleteable="false">
			    <th width="15" removeonread="true">
                  <label for="master_chk" class="label_check_box_hx">
                  <input type="checkbox" id="master_chk" class="master_chk" />
                  </label>
                </th>
				<th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'PatientRadiologyResult', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Lab Facility Name', 'lab_facility_name', array('model' => 'PatientRadiologyResult', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Test Name', 'test_name', array('model' => 'PatientRadiologyResult', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientRadiologyResult', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($PatientRadiologyResult as $PatientRadiologyResult_record):
            ?>
            <tr editlinkajax="<?php echo $html->url(array('action' => 'radiology_results', 'task' => 'edit', 'patient_id' => $patient_id, 'radiology_result_id' => $PatientRadiologyResult_record['PatientRadiologyResult']['radiology_result_id'])); ?>">
			        <td class="ignore" removeonread="true">
                    <label for="child_chk<?php echo $PatientRadiologyResult_record['PatientRadiologyResult']['radiology_result_id']; ?>" class="label_check_box_hx">
                    <input name="data[PatientRadiologyResult][radiology_result_id][<?php echo $PatientRadiologyResult_record['PatientRadiologyResult']['radiology_result_id']; ?>]" id="child_chk<?php echo $PatientRadiologyResult_record['PatientRadiologyResult']['radiology_result_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientRadiologyResult_record['PatientRadiologyResult']['radiology_result_id']; ?>" /></td>
			        <td><?php echo $PatientRadiologyResult_record['PatientRadiologyResult']['diagnosis']; ?></td>
                    <td><?php echo $PatientRadiologyResult_record['PatientRadiologyResult']['lab_facility_name']; ?></td>
					 <td><?php echo $PatientRadiologyResult_record['PatientRadiologyResult']['test_name']; ?></td>
                    <td><?php echo $PatientRadiologyResult_record['PatientRadiologyResult']['status']; ?></td>  
            </tr>
            <?php endforeach; ?>
            
        </table>
        <div style="width: auto; float: left;" removeonread="true">
            <div class="actions">
                <ul>
				    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
					<li><a href="javascript:void(0);" onclick="deleteData('frmPatientRadiologyResultsGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                 </ul>
            </div>
        </div>
    </form>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientRadiologyResult', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientRadiologyResult') || $paginator->hasNext('PatientRadiologyResult'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientRadiologyResult'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientRadiologyResult', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientRadiologyResult', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientRadiologyResult', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
    <?php 
	 if(count($PatientRadiologyResult) == 0)
	 {
	 ?>
	   <div style="float:left; width:100%">
	     <table cellpadding="0" cellspacing="0" align="left">
		    <tr>
			    <td>
				    <!--<input type="checkbox" name="allergies_none" id="allergies_none" <?php if($allergies_none == 'none') { echo 'checked="checked"'; } else { echo ''; } ?> />&nbsp;<label for="allergies_none">Marked as None</label>-->
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		</table>
		</div>
	   <?php
	   }
	}?>
    
    </div>
</div> 
			   
				