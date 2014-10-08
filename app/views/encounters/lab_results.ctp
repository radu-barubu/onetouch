<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$addURL = $html->url(array('action' => 'lab_results', 'encounter_id' => $encounter_id, 'task' => 'addnew')) . '/';
$saveURL = $html->url(array('action' => 'lab_results', 'encounter_id' => $encounter_id, 'task' => 'save')) . '/';
$mainURL = $html->url(array('action' => 'lab_results', 'encounter_id' => $encounter_id)) . '/'; 
$diagnosis_autoURL = $html->url(array('action' => 'radiology_results', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';   

$from_electronic = (isset($this->params['named']['from_electronic'])) ? $this->params['named']['from_electronic'] : "";
$original_id = (isset($this->params['named']['original_id'])) ? $this->params['named']['original_id'] : "";


$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

echo $this->Html->script('ipad_fix.js');

$page_access = $this->QuickAcl->getAccessType("encounters", "results");

if($from_electronic == "1")
{
	$page_access = 'R';	
}

echo $this->element("enable_acl_read", array('page_access' => $page_access)); 

$real_encounter_id = $encounter_id;

?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		initCurrentTabEvents('lab_results_area');
		
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

		$('#pointofcareBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'results_lab', 'encounter_id' => $encounter_id)); ?>");
		});
		
		$('#documentsBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('controller' => 'encounters', 'action' => 'encounter_documents', 'encounter_id' => $encounter_id)); ?>");
		});
		
		$('.title_area .section_btn').click(function()
		{
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this),$(this).attr('url'));
		});
		
		$('#outsideLabBtn').click(function()
		{
			
			$(".tab_area").html('');
			$("#imgLoadLabResults").show();
			loadTab($(this), "<?php echo $html->url(array('action' => $this->params['action'], 'encounter_id' => $encounter_id)); ?>");
		});
		
		<?php echo $this->element('dragon_voice'); ?>
	});
</script>


<div style="overflow: hidden;">	
	
    
        <div class="title_area">
            <?php echo $this->element('../encounters/tabs_results', array('encounter_id' => $encounter_id)); ?>
            <div  class="title_text"> 
                <a href="javascript:void(0);" id="pointofcareBtn"  style="float: none;">Point of Care</a>
                <a href="javascript:void(0);" id="outsideLabBtn" style="float: none;" class="active">Outside Labs</a>
                <a href="javascript:void(0);" id="documentsBtn"  style="float: none;">Documents</a>
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
    <script language="javascript" type="text/javascript">
		$(document).ready(function()
		{
			$('#lab_report_id').change(function()
			{
				$('#encounter_id').val($('#lab_report_id option[value="'+$(this).val()+'"]').attr('encounter_id'));
				$('#diagnosis').val($('#lab_report_id option[value="'+$(this).val()+'"]').attr('diagnosis'));
				$('#icd_code').val($('#lab_report_id option[value="'+$(this).val()+'"]').attr('icd_code'));
			});
		});
	</script>
	<form id="frmPatientLabResult" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
	<?php echo $id_field; ?> 
    <input type="hidden" name="data[PatientLabResult][encounter_id]" id="encounter_id" value="<?php echo $encounter_id; ?>" />
    <?php if($from_electronic == "1"): ?>
        	<table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="padding: 0px;"><h3>Lab Results</h3></td>
                    <td style="padding: 0px;" align="right"><a href="<?php echo $html->url(array('action' => 'lab_results_electronic', 'task' => 'view_order', 'encounter_id' => $real_encounter_id, 'patient_id' => $patient_id, 'order_id' => $original_id)); ?>" class="ajax">View Report</a></td>
                </tr>
            </table>
        <?php endif; ?>
	 <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
     		<?php if($labs_setup == 'Standard'): ?>
            <tr>
                <td><label>Order:</label></td>
                <td>
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="padding: 0px;">
                                <select name="data[PatientLabResult][lab_report_id]" id="lab_report_id" class="required">
                                    <option value="">Select Order</option>
                                    <?php foreach($standard_order_list as $order_item): ?>
                                    <option value="<?php echo $order_item['plan_labs_id']; ?>" <?php echo (($lab_report_id == $order_item['plan_labs_id'])?'selected="selected"':''); ?> encounter_id="<?php echo $order_item['encounter_id']; ?>" diagnosis="<?php echo $order_item['diagnosis']; ?>" icd_code="<?php echo $order_item['icd_code']; ?>"><?php echo $order_item['test_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php endif; ?>
	            <tr>
	                <td width="160"><label>Diagnosis:</label></td>
                    <td> <input type="text" name="data[PatientLabResult][diagnosis]" id="diagnosis" value="<?php echo $diagnosis;?>" style="width:98%;" class="dragon" />
					</td>
				</tr>
				<tr>
                    <input type="hidden" name="data[PatientLabResult][icd_code]" id="icd_code" value="<?php echo $icd_code;?>" />
					<input type="hidden" name="data[PatientLabResult][cpt]" id="cpt" value="<?php echo $cpt;?>" />
					<input type="hidden" name="data[PatientLabResult][cpt_code]" id="cpt_code" value="<?php echo $cpt_code;?>" />
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
                    <td><?php echo $this->element("date", array('name' => 'data[PatientLabResult][date_ordered]', 'id' => 'date_ordered', 'value' => __date($global_date_format, strtotime($date_ordered)), 'required' => false, 'width' => 170)); ?></td>
                </tr>
                <tr>
                    <td style="vertical-align:top; padding-top: 3px;">
                    <label>Report Date:</label>
                    </td>
                    <td><?php echo $this->element("date", array('name' => 'data[PatientLabResult][report_date]', 'id' => 'report_date', 'value' => __date($global_date_format, strtotime($report_date)), 'required' => false, 'width' => 170)); ?></td>
                </tr>
                <tr>
			        <td>&nbsp;</td>
			        <td>&nbsp;</td>
		        </tr>
                <tr>
			        <td colspan="2"><strong><label>Lab Information</label></strong></td>
		        </tr>
			    <tr>
                    <td><label>Lab Facility Name:</label></td>
                    <td> <input type="text" name="data[PatientLabResult][lab_facility_name]" id="lab_facility_name" value="<?php echo $lab_facility_name;?>" style="width:480px;" /></td>
			    </tr>
                <tr>
                    <td><label>Address #1:</label></td>
                    <td> <input type="text" name="data[PatientLabResult][lab_address_1]" id="lab_address_1" value="<?php echo $lab_address_1;?>" style="width:480px;" /></td>
			    </tr>
                <tr>
                    <td><label>Address #2:</label></td>
                    <td> <input type="text" name="data[PatientLabResult][lab_address_2]" id="lab_address_2" value="<?php echo $lab_address_2;?>" style="width:480px;" /></td>
			    </tr>
                <tr>
                    <td><label>City:</label></td>
                    <td> <input type="text" name="data[PatientLabResult][lab_city]" id="lab_city" value="<?php echo $lab_city;?>" style="width:170px;" /></td>
			    </tr>
                <tr>
                    <td><label>State:</label></td>
                    <td> 
                    	<select name="data[PatientLabResult][lab_state]" id="lab_state">
                            <option value="">Select State </option>
                            <?php
							foreach($StateCode as $state_item)
							{
								?>
								<option  value="<?php echo $state_item['StateCode']['state']; ?>" <?php if($lab_state == $state_item['StateCode']['state']) { echo 'selected="selected"'; } ?>><?php echo $state_item['StateCode']['fullname']; ?></option>
								<?php
							}
							?>
                        </select>
                    </td>
			    </tr>
                <tr>
                    <td><label>Zip Code:</label></td>
                    <td>
                    	<input type="text" name="data[PatientLabResult][lab_zip_code]" id="lab_zip_code" value="<?php echo $lab_zip_code;?>" style="width:80px;" />
                        <input type="hidden" name="data[PatientLabResult][lab_country]" id="lab_country" value="<?php echo $lab_country;?>"/>
                    </td>
			    </tr>
			    <tr>
			        <td>&nbsp;</td>
			        <td>&nbsp;</td>
		        </tr>
                <tr>
                	<td colspan="2"><strong><label>Speciment:</label></strong></td>
                </tr>
                <tr>
                <td><label>Test Specimen Source:</label></td>
                <td><input type="text" name="data[PatientLabResult][test_specimen_source]" id="test_specimen_source" value="<?php echo $test_specimen_source;?>" style="width:170px;" /></td>
	          </tr>
			  <tr>
                <td><label>Condition of Specimen:</label></td> 
                <td><input type="text" name="data[PatientLabResult][condition_of_specimen]" id="condition_of_specimen" value="<?php echo $condition_of_specimen;?>" style="width:170px;" /></td>
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
                    <input type="text" name="data[PatientLabResult][result_value<?php echo $i; ?>]" id="result_value<?php echo $i; ?>" value="<?php echo ${"result_value".$i};?>" style="width:170px;" />
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
                <td><label>Flag:</label> </td>
                <td>
                	<?php
					$abnormal_flags = array(
						'L' => 'Below low normal',
						'H' => 'Above high normal',
						'LL' => 'Below lower panic limits',
						'HH' => 'Above upper panic limits',
						'<' => 'Below absolute low',
						'>' => 'Above absolute high',
						'N' => 'Normal',
						'A' => 'Abnormal',
						'AA' => 'Very abnormal',
						'U' => 'Significant change up',
						'D' => 'Significant change down',
						'B' => 'Better',
						'W' => 'Worse',
						'S' => 'Susceptible',
						'I' => 'Intermediate',
						'R' => 'Resistant',
						'MS' => 'Moderately Susceptible',
						'VS' => 'Very Susceptible'
					);
					?>
                    <select name='data[PatientLabResult][abnormal<?php echo $i; ?>]' id='abnormal<?php echo $i; ?>'>
                        <option value="">Select Flag</option>
                        <?php foreach($abnormal_flags as $key => $abnormal_flag): ?>
                        <option value="<?php echo $key; ?>" <?php if(${"abnormal".$i} == $key) { echo 'selected="selected"'; } ?>><?php echo $key; ?> - <?php echo $abnormal_flag; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
	          </tr>
              <tr>
                    <td><label>Test Result:</label></td>
                    <td>
                        <select name="data[PatientLabResult][test_result_status<?php echo $i; ?>]" id="test_result_status<?php echo $i; ?>" style="width:170px;">
                           <option value="" selected>Select Status</option>
                            <?php                   
                            $status_array = array("P" => "Preliminary", "I" => "Pending", "F" => "Completed", "C" => "Corrected", "X" => "Cancel");
                            
                            foreach($status_array as $key => $status_val):
                                echo "<option value=\"$key\" ".(${"test_result_status".$i}==$key?"selected":"").">".$status_val."</option>";
                            endforeach;
                            ?>        
                           </select>
                    </td>
               </tr>
              <tr>
              	<td class="top_pos"><label>Test Report Date:</label></td>
                <td><?php echo $this->element("date", array('name' => 'data[PatientLabResult][test_report_date'.$i.']', 'id' => 'test_report_date'.$i, 'value' => __date($global_date_format, strtotime(${"test_report_date".$i})), 'required' => false, 'width' => 170)); ?></td>
              </tr>
              
              
              
			  <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
               <?php endfor; ?>
				   <tr>
		                <td><label>Test Result Status:</label></td>
						<td>
                            <select name="data[PatientLabResult][overall_test_result_status]" id="overall_test_result_status" style="width:170px;">
                               <option value="" selected>Select Status</option>
								<?php                   
                                $status_array = array("P" => "Partial", "F" => "Final", "C" => "Corrected", "X" => "Cancel");
								
								foreach($status_array as $key => $status_val):
                                    echo "<option value=\"$key\" ".($overall_test_result_status==$key?"selected":"").">".$status_val."</option>";
								endforeach;
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
                	<?php if($from_electronic == "1"): ?>
                    <li><a href="<?php echo $html->url(array('action' => 'lab_results_electronic', 'encounter_id' => $real_encounter_id, 'patient_id' => $patient_id)); ?>" class="ajax">Cancel</a></li>
                    <?php else: ?>
                    <?php if($page_access == 'W'): ?><li><a href="javascript: void(0);" onclick="$('#frmPatientLabResult').submit();">Save</a></li><?php endif; ?>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    <?php endif; ?>
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
				//var_dump($PatientLabResult_record);
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
                <tr editlinkajax="<?php echo $html->url(array('action' => 'lab_results', 'task' => 'edit', 'encounter_id' => $encounter_id, 'lab_result_id' => $PatientLabResult_record['PatientLabResult']['lab_result_id'])); ?>">
                        
	
                        <td><?php echo $PatientLabResult_record['PatientLabResult']['diagnosis']; ?></td>
                        <td><?php echo __date($global_date_format, strtotime($PatientLabResult_record['PatientLabResult']['date_ordered'])); ?></td>
                        <td><?php echo __date($global_date_format, strtotime($PatientLabResult_record['PatientLabResult']['report_date'])); ?></td>
                        <td><?php echo implode(", ", $test_name_arr); ?></td>
                        <td><?php echo implode(", ", $lab_loinc_code_arr); ?></td>
                        <td><?php echo $PatientLabResult_record['PatientLabResult']['status']; ?></td>  
                </tr>
                <?php endforeach; ?>
                
            </table>
            <!--<div style="width: 40%; float: left;">
                <div class="actions">
                    <ul>
                        <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                        <li><a href="javascript:void(0);" onclick="deleteData('frmPatientLabResultGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                     </ul>
                </div>
            </div>-->
        </form>
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
                <?php echo $paginator->numbers(array('model' => 'PatientLabResult', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientLabResult', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
       <?php
	}
	?>
    
    </div>
</div>
				     
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access));  ?>