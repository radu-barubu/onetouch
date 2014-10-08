<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'lab_results', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$saveURL = $html->url(array('action' => 'lab_results', 'patient_id' => $patient_id, 'task' => 'save')) . '/';
$mainURL = $html->url(array('action' => 'lab_results', 'patient_id' => $patient_id)) . '/'; 
$diagnosis_autoURL = $html->url(array('action' => 'radiology_results', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';   

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

echo $this->Html->script('ipad_fix.js');

?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
	    //initCurrentTabEvents('lab_results_area');
		
		$("#frmPatientLabResult").validate(
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
                $('#frmPatientLabResult').css("cursor", "wait"); 
                $.post(
                    '<?php echo $thisURL; ?>', 
                    $('#frmPatientLabResult').serialize(), 
                    function(data)
                    {
                        showInfo("<?php echo $current_message; ?>", "notice");
                        loadTab($('#frmPatientLabResult'), '<?php echo $mainURL; ?>');
                    },
                    'json'
                );
            }
        });
		
		$("#lab_facility_name").autocomplete('<?php echo $this->Session->webroot; ?>patients/lab_results/patient_id:<?php echo $patient_id; ?>/task:labname_load/',        {
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
		
        $("#patientlabresult_none").click(function()
		{
			if(this.checked == true)
			{
				var marked_none = 'none';
			}
			else
			{
				var marked_none = '';
			}			
		    var formobj = $("<form></form>");
		    formobj.append('<input name="data[submitted][id]" type="hidden" value="patientlabresult_none">');
		    formobj.append('<input name="data[submitted][value]" type="hidden" value="'+marked_none+'">');	
			$.post('<?php echo $this->Session->webroot; ?>patients/lab_results/patient_id:<?php echo $patient_id; ?>/task:markNone/', formobj.serialize(), 
			function(data){}
			);
		});

		/*$('#pointofcareBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'patients', 'action' => 'in_house_work_labs', 'patient_id' => $patient_id)); ?>");
		});*/
	});
</script>
<div style="overflow: hidden;">	
    <?php echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'action' => 'in_house_work_labs')); ?>
	<div class="title_area">
        <div  class="title_text"> 
			<?php echo $html->link('Point of Care', array('action' => 'in_house_work_labs', 'patient_id'=> $patient_id)); ?>		
            <div class="title_item active" >Outside Labs</div>
        </div>       
    </div>
		<span id="imgLoadLabResults" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
	 <div id="lab_results_area" class="tab_area">
    <?php
    if($task == "addnew" || $task == "edit")  
    { 
	 if($task == "addnew")
        {
            $id_field = "";
			$lab_result_id ="";
			$plan_lab_id = "";
			$diagnosis="";
			$icd_code="";
			$cpt="";
			$cpt_code="";
			$lab_report_id="";
			$ordered_by_id="";
			$ordered_by= "";
			$date_ordered = $global_date_format;
			$lab_address_1="";
			$lab_address_2="";
			$lab_city="";
			$lab_state="";
			$lab_zip_code="";
			$lab_country="";
            $report_date= $global_date_format;
			$lab_facility_name="";
			$test_specimen_source="";
			$condition_of_specimen="";
			$abnormal="";
			$test_result_status="";
			$comment="";
			$status="";
			
			for($i = 1; $i <= 5; $i++):
			${"test_name".$i}="";
			${"lab_loinc_code".$i}="";
			${"normal_range".$i}="";
			${"result_value".$i}="";
			${"unit".$i}="";
			endfor;
			
			
        }
		else
		{
		    extract($EditItem['PatientLabResult']);
		    $id_field = '<input type="hidden" name="data[PatientLabResult][lab_result_id]" id="lab_result_id" value="'.$lab_result_id.'" />';
		 }
    ?>
	<form id="frmPatientLabResult" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
	<?php echo $id_field; ?> 
	 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
	            <tr>
	                <td><label>Diagnosis:</label></td>
                    <td> <input type="text" name="data[PatientLabResult][diagnosis]" id="diagnosis" value="<?php echo $diagnosis;?>" style="width:98%;" />
					</td>
				</tr>
				<tr>
                    <input type="hidden" name="data[PatientLabResult][icd_code]" id="icd_code" value="<?php echo $icd_code;?>" />
					<input type="hidden" name="data[PatientLabResult][cpt]" id="cpt" value="<?php echo $cpt;?>" />
					<input type="hidden" name="data[PatientLabResult][cpt_code]" id="cpt_code" value="<?php echo $cpt_code;?>" />
                </tr>
				<tr>
				    <td><label>Lab Report ID:</label></td>
				    <td><input type="text" name="data[PatientLabResult][lab_report_id]" id="lab_report_id" value="<?php echo $lab_report_id;?>" style="width:170px;" />
					</td>
				</tr>
				<tr>
					 <input type="hidden" name="data[PatientLabResult][ordered_by_id]" id="ordered_by_id" value="<?php echo $ordered_by_id;?>" />
	            </tr>
				<tr>
				    <td><label>Ordered by:</label></td> 
				    <td><input type="text" name="data[PatientLabResult][ordered_by]" id="ordered_by" value="<?php echo $ordered_by;?>" style="width:170px;" />
				    </td>
				</tr>
				<tr>
                    <td style="vertical-align:top; padding-top: 3px;"><label>Date Ordered:</label></td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientLabResult][date_ordered]', 'id' => 'date_ordered', 'value' => $date_ordered, 'required' => false, 'width' => 170)); ?></td>
                </tr>
			    <tr>
                    <td><label>Lab Facility Name:</label></td>
                    <td> <input type="text" name="data[PatientLabResult][lab_facility_name]" id="lab_facility_name" value="<?php echo $lab_facility_name;?>" style="width:480px;" /></td>
			    </tr>
			    <tr>
					<input type="hidden" name="data[PatientLabResult][lab_address_1]" id="lab_address_1" value="<?php echo $lab_address_1;?>"/>
					<input type="hidden" name="data[PatientLabResult][lab_address_2]" id="lab_address_2" value="<?php echo $lab_address_2;?>"/>
					<input type="hidden" name="data[PatientLabResult][lab_city]" id="lab_city" value="<?php echo $lab_city;?>" />
					<input type="hidden" name="data[PatientLabResult][lab_state]" id="lab_state" value="<?php echo $lab_state;?>"/>
					<input type="hidden" name="data[PatientLabResult][lab_zip_code]" id="lab_zip_code" value="<?php echo $lab_zip_code;?>" />
					<input type="hidden" name="data[PatientLabResult][lab_country]" id="lab_country" value="<?php echo $lab_country;?>"/>
			    </tr>
			    <tr>
                    <td style="vertical-align:top; padding-top: 3px;">
                    <label>Report Date:</label>
                    </td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientLabResult][report_date]', 'id' => 'report_date', 'value' => $report_date, 'required' => false, 'width' => 170)); ?></td>
               </tr>
			    <tr>
			        <td>&nbsp;</td>
			        <td>&nbsp;</td>
		        </tr>
                
                <?php for($i = 1; $i <= 5; $i++): ?>
			    <tr>
			        <td><strong><label>Test #<?php echo $i; ?></label></strong></td>
			        <td>&nbsp;</td>
		        </tr>
			    <tr>
                   <td><label>Test Name:</label></td>
                   <td> <input type="text" name="data[PatientLabResult][test_name<?php echo $i; ?>]" id="test_name<?php echo $i; ?>" value="<?php echo ${"test_name".$i};?>" style="width:170px;" />
                   </td>
			 	</tr>
			   <tr>
                   <td><label>LOINC Code:</label></td>
                   <td> <input type="text" name="data[PatientLabResult][lab_loinc_code<?php echo $i; ?>]" id="lab_loinc_code<?php echo $i; ?>" value="<?php echo ${"lab_loinc_code".$i};?>" style="width:170px;" />
                   </td>
			 	</tr>
			   <tr>
                   <td><label>Normal Range:</label></td>
                   <td> <input type="text" name="data[PatientLabResult][normal_range<?php echo $i; ?>]" id="normal_range<?php echo $i; ?>" value="<?php echo ${"normal_range".$i};?>" style="width:170px;" />
				   </td>
			 </tr>
			 <tr>
                <td><label>Test Result Value:</label></td>
                <td> 
                    <input type="text" name="data[PatientLabResult][test_result_value<?php echo $i; ?>]" id="test_result_value<?php echo $i; ?>" value="<?php echo ${"result_value".$i};?>" style="width:170px;" />
                </td>
			 </tr>
			 <tr>
		            <td><label>Unit:</label> </td>
				    <td>
                    	<select name='data[PatientLabResult][unit<?php echo $i; ?>]' id='unit<?php echo $i; ?>' style="width:170px;">
						<option value="">Select Unit</option>
						<?php foreach($units as $unit_item): ?>
                        <option value="<?php echo $unit_item['Unit']['description']; ?>" <?php if(${"unit".$i} == $unit_item['Unit']['description']) { echo 'selected="selected"'; } ?>><?php echo $unit_item['Unit']['description']; ?></option>
                        <?php endforeach; ?>
                        </select>
		            </td>
	          </tr>
			  <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
               <?php endfor; ?>
                
			  <tr>
		            <td><label>Test Specimen Source:</label></td>
				    <td><select name='data[PatientLabResult][test_specimen_source]' id='test_specimen_source' style="width:170px;" >
					    <option></option>
						</select>
		            </td>
	          </tr>
			  <tr>
				    <td><label>Condition of Specimen:</label></td> 
				    <td><input type="text" name="data[PatientLabResult][condition_of_specimen]" id="condition_of_specimen" value="<?php echo $condition_of_specimen;?>" style="width:170px;" />
				       </td>
				   </tr>
				    <tr>
		                <td><label>Abnormal:</label></td>
						<td><select name='data[PatientLabResult][abnormal]' id='abnormal' style="width:170px;">
						    <option value="" selected>Select Option</option>
							<?php                   
                    $status_array = array("Yes", "No", "High","Low");
                    for ($i = 0; $i < count($status_array); ++$i)
                    {
                        echo "<option value=\"$status_array[$i]\" ".($status==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
                    }
                    ?>   </select>
		                </td>
	               </tr>
				   <tr>
		                <td><label>Test Result Status:</label></td>
						<td><select name='data[PatientLabResult][test_result_status]' id='test_result_status' style="width:170px;" >
							<option value="" selected>Select Status</option>
							<?php                   
                    $status_array = array("Preliminary", "Cannot be done", "Final","Corrected","Incompete");
                    for ($i = 0; $i < count($status_array); ++$i)
                    {
                        echo "<option value=\"$status_array[$i]\" ".($status==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
                    }
                    ?>   
							</select>
		                </td>
	               </tr>
				   <tr>
                       <td><label>Status:</label></td>
                           <td>
                               <select name="data[PatientLabResult][status]" id="status" style="width:170px;">
                               <option value="" selected>Select Status</option>
                    <?php                   
                    $status_array = array("Pending Review", "Reviewed", "Hold ");
                    for ($i = 0; $i < count($status_array); ++$i)
                    {
                        echo "<option value=\"$status_array[$i]\" ".($status==$status_array[$i]?"selected":"").">".$status_array[$i]."</option>";
                    }
                    ?>        
                               </select>
                          </td>
                    </tr>
				    <tr>
					    <td valign='top' style="vertical-align:top"><label>Comment:</label></td>
					    <td><textarea cols="20" name="data[PatientLabResult][comment]" style="height:80px"><?php echo $comment ?></textarea></td>
				    </tr>
            </table>
		 	<div class="actions">
                <ul>
                    <li><a href="javascript: void(0);" onclick="$('#frmPatientLabResult').submit();">Save</a></li>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
      </form>
	   <?php	
	}
	else
    {	  
	   ?>
        <form id="frmPatientLabResultGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
          <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">          
                
                <tr deleteable="false">
                    <th width="15">
                    
                <label for="master_chk" class="label_check_box_hx">
                <input type="checkbox" id="master_chk" class="master_chk" />
                </label>
                    
                    </th>
                    <th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'PatientLabResult', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('Date Ordered', 'date_ordered', array('model' => 'PatientLabResult', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('Report Date', 'report_date', array('model' => 'PatientLabResult', 'class' => 'ajax'));?></th>
                    <th>Test Name(s)</th>
                    <th>LOINC Code(s)</th>
                    <th><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientLabResult', 'class' => 'ajax'));?></th>
                </tr>
                <?php
                $i = 0;
                foreach ($PatientLabResult as $PatientLabResult_record):
				
				$test_name_arr = array();
				$lab_loinc_code_arr = array();
				
				for($a = 1; $a <= 5; $a++)
				{
					if(strlen($PatientLabResult_record['PatientLabResult']['test_name'.$a]) > 0)
					{
						$test_name_arr[] = $PatientLabResult_record['PatientLabResult']['test_name'.$a];
					}
					
					if(strlen($PatientLabResult_record['PatientLabResult']['lab_loinc_code'.$a]) > 0)
					{
						$lab_loinc_code_arr[] = $PatientLabResult_record['PatientLabResult']['lab_loinc_code'.$a];
					}
				}
                ?>
                <tr editlinkajax="<?php echo $html->url(array('action' => 'lab_results', 'task' => 'edit', 'patient_id' => $patient_id, 'lab_result_id' => $PatientLabResult_record['PatientLabResult']['lab_result_id'])); ?>">
                        
                        <td class="ignore">
                        <label for="child_chk<?php echo $PatientLabResult_record['PatientLabResult']['lab_result_id']; ?>" class="label_check_box_hx">
                        <input name="data[PatientLabResult][lab_result_id][<?php echo $PatientLabResult_record['PatientLabResult']['lab_result_id']; ?>]" id="child_chk<?php echo $PatientLabResult_record['PatientLabResult']['lab_result_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $PatientLabResult_record['PatientLabResult']['lab_result_id']; ?>" />
                        
                        </td>	
                        <td><?php echo $PatientLabResult_record['PatientLabResult']['diagnosis']; ?></td>
                        <td><?php echo __date($global_date_format, strtotime($PatientLabResult_record['PatientLabResult']['date_ordered'])); ?></td>
                        <td><?php echo __date($global_date_format, strtotime($PatientLabResult_record['PatientLabResult']['report_date'])); ?></td>
                        <td><?php echo implode(", ", $test_name_arr); ?></td>
                        <td><?php echo implode(", ", $lab_loinc_code_arr); ?></td>
                        <td><?php echo $PatientLabResult_record['PatientLabResult']['status']; ?></td>  
                </tr>
                <?php endforeach; ?>
                
            </table>
            <div style="width: 40%; float: left;">
                <div class="actions">
                    <ul>
                        <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                        <li><a href="javascript:void(0);" onclick="deleteData('frmPatientLabResultGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                     </ul>
                </div>
            </div>
        </form>
        <div style="width: 60%; float: right; margin-top: 15px;">
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientLabResult', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientLabResult') || $paginator->hasNext('PatientLabResult'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientLabResult'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientLabResult', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientLabResult', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientLabResult', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
        </div>
       <?php
	}
	?>
    
    </div>
</div>
				     
			
				