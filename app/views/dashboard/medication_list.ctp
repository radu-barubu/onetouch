<?php

$practice_settings = $this->Session->read("PracticeSetting");
$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];

$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$patient_checkin_id = (isset($this->params['named']['patient_checkin_id'])) ? $this->params['named']['patient_checkin_id'] : "";
$addURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'addnew', 'patient_checkin_id' => $patient_checkin_id)) . '/';
$mainURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)) . '/';
$deleteURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'delete', 'patient_checkin_id' => $patient_checkin_id));
$showURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)) . '/';

$dosespot_screen_URL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'dosespot')) . '/';

$diagnosis_autoURL = $html->url(array('controller' => 'patients', 'action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'load_Icd9_autocomplete')) . '/';   
$medication_autoURL = $html->url(array('controller' => 'patients', 'action' => 'meds_list', 'patient_id' => $patient_id, 'task' => 'load_autocomplete')) . '/';   
$provider_autoURL = $html->url(array('controller' => 'patients', 'action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'load_provider_autocomplete')) . '/';   

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";

$current_message = ($task == 'addnew') ? $added_message : $edit_message;

$medication_list_id = (isset($this->params['named']['medication_list_id'])) ? $this->params['named']['medication_list_id'] : "";

echo $this->Html->script('ipad_fix.js');

?>
<script language="javascript" type="text/javascript">
	
	function selectMedicationItems()
	{
	    var showAllMedication = ($('#show_all_medications').is(':checked'))?'yes':'no';
	    
	    <?php //patient portal patient_checkin_id 
		if(!empty($patient_checkin_id)): ?>
		  var showSurescripts = showReported = showPrescribed = 'yes';
		<?php else: ?>
		  var showSurescripts = ($('#show_surescripts').is(':checked'))?'yes':'no';
		  var showReported = ($('#show_reported').is(':checked'))?'yes':'no';
		  var showPrescribed = ($('#show_prescribed').is(':checked'))?'yes':'no';
		<?php endif; ?>
		window.location = '<?php echo $showURL; ?>show_all_medications:'+showAllMedication+'/show_surescripts:'+showSurescripts+'/show_reported:'+showReported+'/show_prescribed:'+showPrescribed+'/';
		
	}
	
	$(document).ready(function()
    {
		$("#frmPatientMedicationList").validate(
        {
            errorElement: "div"
        });
		
		<?php if($task == 'addnew' || $task == 'edit'): ?>
		var duplicate_rules = {
			remote: 
			{
				url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
				type: 'post',
				data: {
					'data[model]': 'PatientMedicationList', 
					'data[patient_id]': <?php echo $patient_id; ?>, 
					'data[medication]': function()
					{
						return $('#medication', $("#frmPatientMedicationList")).val();
					},
					'data[exclude]': '<?php echo $medication_list_id; ?>'
				}
			},
			messages: 
			{
				remote: "Duplicate value entered."
			}
		}
		
		$("#medication", $("#frmPatientMedicationList")).rules("add", duplicate_rules);
		<?php endif; ?>
		
		$("#diagnosis").autocomplete('<?php echo $diagnosis_autoURL; ?>', {
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
        
		$("#medication").autocomplete('<?php echo $this->Session->webroot; ?>encounters/meds_list/task:load_autocomplete/', {
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
        
		$("#provider").autocomplete('<?php echo $provider_autoURL; ?>', {
            max: 20,
            mustMatch: false,
            matchContains: false,
            scrollHeight: 300
        });
		
		$("#provider").result(function(event, data, formatted)
		{
			$('#provider_id').val(data[1]);
		});
		
        $("#medication").result(function(event, data, formatted)
        {
			$('#rxnorm').val(data[1]);
			$('#medication_form').val(data[2]);
			$('#medication_strength_value').val(data[3]);
			$('#medication_strength_unit').val(data[4]);
        });
		
	});
</script>
<div style="overflow: hidden;">    
    <?php echo $this->element("idle_timeout_warning"); echo $this->element('patient_general_links', array('patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?>
<?php echo (empty($patient_checkin_id))? $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 104)):''; ?>
    <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
    <div id="medical_records_area" class="tab_area">
    	<?php if($task == 'addnew' || $task == 'edit'): ?>
        	<?php
			$disabled_refill = true;
			if($task == "addnew")
			{
				$id_field = "";
				$medication="";
				$diagnosis="";
				$icd_code="";
				$frequency="";
				$start_date = "";
				$end_date = "";   
				$taking = "";
				$long_term = "";     
				$source = "";    
				$provider="";
				$status="";
				$rxnorm="";
				$direction="";
				$quantity="";
				$unit="";
				$route="";
			}
			else
			{
				extract($EditItem['PatientMedicationList']);
				$id_field = '<input type="hidden" name="data[PatientMedicationList][medication_list_id]" id="medication_list_id" value="'.$medication_list_id.'" />';
				
				$start_date = (strtotime($start_date) !== false && $start_date != '0000-00-00') ? __date($global_date_format, strtotime($start_date)) : "";
				$end_date = (strtotime($end_date) !== false  && $end_date != '0000-00-00') ? __date($global_date_format, strtotime($end_date)) : "";
				
				$refill_count = (int)$refill_allowed;
			}
			?>
            <form id="frmPatientMedicationList" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
            <input type="hidden" name="data[PatientMedicationList][source]" id="source" value="Patient Reported" />
			 <?php echo $id_field; ?>
             <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
                    <tr>
                        <td width="140"><label>Medication:</label></td>
                        <td> 
                            <input type="text" name="data[PatientMedicationList][medication]" id="medication" value="<?php echo $medication;?>" style="width:98%;" class="required"/>
                            <input type="hidden" name="data[PatientMedicationList][medication_form]" id="medication_form" value="<?php echo @$medication_form;?>" />
                            <input type="hidden" name="data[PatientMedicationList][medication_strength_value]" id="medication_strength_value" value="<?php echo @$medication_strength_value;?>" />
                            <input type="hidden" name="data[PatientMedicationList][medication_strength_unit]" id="medication_strength_unit" value="<?php echo @$medication_strength_unit;?>" />
                        </td>
                    </tr>
                    <tr>
                        <td><label>RxNorm:</label></td>
                        <td> <input type="text" name="data[PatientMedicationList][rxnorm]" id="rxnorm" value="<?php echo $rxnorm;?>" readonly="readonly" style="background:#eeeeee;" /></td>
                    </tr>
                    <tr>
                        <td><label>Diagnosis:</label></td>
                        <td> <input type="text" name="data[PatientMedicationList][diagnosis]" id="diagnosis" value="<?php echo $diagnosis;?>" style="width:98%;" />
                        <input type="hidden" name="data[PatientMedicationList][icd_code]" id="icd_code" value="<?php echo $icd_code;?>" /></td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top;"><label>SIG:</label></td>
                        <td align="left">
                            <table cellpadding="0" cellspacing="0" style="width:85%">
                                <tr>
                                    <td style="width:5%">
                                        <select name="data[PatientMedicationList][quantity]" id="quantity" size="10" multiple="multiple">
                                        <?php

                                        foreach($rx_quantity as $value)
                                        {
                                            if($value == $quantity)
                                            {
                                                echo '<option value="'.$value.'" selected>'.$value.'</option>';
                                            }
                                            else
                                            {
                                                echo '<option value="'.$value.'">'.$value.'</option>';
                                            }
                                        }
                                        ?>
                                        </select>
                                    </td>
                                    <td style="width:5%">
                                        <select name="data[PatientMedicationList][unit]" id="unit" size="10" multiple="multiple">
                                        <?php

                                        foreach($rx_unit as $value)
                                        {
                                            if($value == $unit)
                                            {
                                                echo '<option value="'.$value.'" selected>'.$value.'</option>';
                                            }
                                            else
                                            {
                                                echo '<option value="'.$value.'">'.$value.'</option>';
                                            }
                                        }
                                        ?>
                                        </select>
                                    </td>
                                    <td style="width:5%">
                                        <select name="data[PatientMedicationList][route]" id="route" size="10" multiple="multiple">
                                        <?php
                                        $rx_route2 = array("PO|By mouth","Inj|Injected","Inh|Inhaled","Subq|Subcutaneous","Otic|Ear(s)","Topical|Topical","Oph|Eye(s)","Sublingual|Under Tongue","Vaginal|Vaginally");
                                        foreach($rx_route2 as $values)
                                        {
                                            $value = explode('|',$values);
                                            if($value[0] == $route)
                                            {
                                                echo '<option value="'.$value[0].'" selected>'.$value[1].'</option>';
                                            }
                                            else
                                            {
                                                echo '<option value="'.$value[0].'">'.$value[1].'</option>';
                                            }
                                        }
                                        ?>
                                        </select>
                                    </td>
                                    <td style="width:5%">
                                        <select name="data[PatientMedicationList][frequency]" id="frequency" size="10" multiple="multiple">
                                        <?php					

					//make it readable for patients to understand, so convert medical lingo
					$rx_freq2  = array("BID|Twice a day","QID|Four Times a day", "TID|Three Times a day","Q1|Every hour","Q2|Every 2 hrs","Q4|Every 4 hrs","Q6|Every 6 hrs","Q8|Every 8 hrs","Q12|Every 12 hrs","Q2-4|Every 2-4 hrs","Q4-6|Every 4-6 hrs","Q6-8|Every 6-8 hrs","Qday|Every day","Qwk|Every Week","Qac|After meals","Qac/hs|After meal & at bed time","Qam|Every morning","Qhs|before bed","Qpm|Every evening","Qmonth|Every month","Qyear|Every year");
                                        foreach($rx_freq2 as $values)
                                        {
                                            $value = explode('|',$values);
                                            if($value[0] == $frequency)
                                            {
                                                echo '<option value="'.$value[0].'" selected>'.$value[1].'</option>';
                                            }
                                            else
                                            {
                                                echo '<option value="'.$value[0].'">'.$value[1].'</option>';
                                            }
                                        }
                                        ?>
                                        </select>
                                    </td>
                                    <td style="vertical-align:top; padding-left:20px; width:8%" ><a href="javascript: void(0);" onclick="$('#direction_table').toggle();">Manual</a></td>
                                    <td style="vertical-align:top; padding-left:20px; width:80%" >
                                        <div id="direction_table" style="width:100%; display: <?php echo ($direction!='')?'':'none'; ?>">
                                            <textarea id="direction" name="data[PatientMedicationList][direction]" cols="80" style="height: 85px;"><?php echo isset($direction)?$direction:''; ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                             </table>
                         </td>
                    </tr>
                    <tr style="display:none;">
                        <td><label>Taking?:</label></td>
                        <td>
                        <select name="data[PatientMedicationList][taking]" id="taking">
                        <option value="" selected>Select Taking</option>
                        <?php                    
                        $taking_array = array("Yes", "No");
                        for ($i = 0; $i < count($taking_array); ++$i)
                        {
                            echo "<option value=\"$taking_array[$i]\" ".($taking==$taking_array[$i]?"selected":"").">".$taking_array[$i]."</option>";
                        }
                        ?>        
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><label>Start Date:</label></td>
                        <td><?php echo $this->element("date", array('name' => 'data[PatientMedicationList][start_date]', 'id' => 'start_date', 'value' => $start_date, 'required' => false)); ?></td>
                    </tr>
                    <tr id="end_date_row">
                        <td style="vertical-align: top;"><label>End Date:</label></td>
                        <td><?php echo $this->element("date", array('name' => 'data[PatientMedicationList][end_date]', 'id' => 'end_date', 'value' =>  $end_date, 'required' => false)); ?></td>
                    </tr>
                    <tr style="display:none;">
                        <td><label>Long Term?:</label></td>
                        <td>
                        <select name="data[PatientMedicationList][long_term]" id="long_term">
                        <option value="" selected>Select Term</option>
                        <?php                    
                        $long_term_array = array("Yes", "No");
                        for ($i = 0; $i < count($long_term_array); ++$i)
                        {
                            echo "<option value=\"$long_term_array[$i]\" ".($long_term==$long_term_array[$i]?"selected":"").">".$long_term_array[$i]."</option>";
                        }
                        ?>        
                        </select>
                        </td>
                    </tr>
					<tr>
						<td><label>Source:</label></td>
						<td>
						<select id="source" disabled style="background:#eeeeee;">
						<option value="" selected>Select Source</option>
						<?php                    
						$source_array = array("Practice Prescribed", "Patient Reported", "e-Prescribing History");
						for ($i = 0; $i < count($source_array); ++$i)
						{
							echo "<option value=\"$source_array[$i]\" ".($source==$source_array[$i]?"selected":"").">".$source_array[$i]."</option>";
						}
						?>        
						</select>
						</td>
					</tr>
                    <tr>
                        <td><label>Provider:</label></td>
                        <td> 
                            
                            <?php 
                            $provider = trim($provider);
                            if (count($availableProviders) === 1 && $provider == ''): ?> 
                            <?php 
                                $p = $availableProviders[0]['UserAccount'];
                                $provider = htmlentities($p['firstname'] . ' ' . $p['lastname']);
                                $provider_id = $p['user_id'];
                            ?> 
                            <?php endif;?> 
                            <input type="text" name="data[PatientMedicationList][provider]" id="provider" value="<?php echo $provider; ?>" style="width:200px;" />
                            <input type="hidden" name="data[PatientMedicationList][provider_id]" id="provider_id" value="<?php echo (isset($provider_id)?$provider_id:""); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td><label>Status:</label></td>
                        <td>
                        <select name="data[PatientMedicationList][status]" id="status">
                        <option value="" selected>Select Status</option>
                        <?php                    
                        $status_array = array("Active", "Inactive", "Cancelled", "Discontinued","Completed");
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
                        <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPatientMedicationList').submit();">Save</a></li>
                        <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                    </ul>
             </div>
          </form>
        <?php else: ?>
<?php  
//patient portal patient_checkin_id 
if(!empty($patient_checkin_id)):
?>
<script>
 function goforward() {
  setTimeout("location='<?php echo $this->Html->url(array('controller' => 'dashboard', 'action' => 'hx_medical', 'patient_id' => $patient_id, 'patient_checkin_id' => $patient_checkin_id)); ?>';",600);
 }
</script>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>
    <td style="width:100px"><button class='btn' onclick="javascript:history.back()"><< Back</button></td>
    <td style="vertical-align:top">Please review your <b>Medication data</b> below. You may provide updates to the information or make notes in the comments box at the bottom. When finished, click the 'Next' button.
    </td>
    <td style="width:100px;"><button class="btn" id="toNext" OnClick="goforward()">Next >> </button></td>
  </tr>
</table>  
</div>
<?php else: //only show these if no patient_checkin_id process is going on ?>	        
	    <div style="float:left; width:60%">
	        <table cellpadding="0" cellspacing="0" align="left">
		    <tr>
			    <td>
				    <label for="show_surescripts" class="label_check_box"><input type="checkbox" name="show_surescripts" id="show_surescripts"  <?php if($show_surescripts == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> onclick="selectMedicationItems()" />&nbsp;e-Prescribing History</label>&nbsp;&nbsp;
				</td>
				<td>
				    <label for="show_reported" class="label_check_box"><input type="checkbox" name="show_reported" id="show_reported" <?php if($show_reported == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> onclick="selectMedicationItems()" />&nbsp;Patient Reported</label>&nbsp;&nbsp;
				</td>
				<td>
				    <label for="show_prescribed" class="label_check_box"><input type="checkbox" name="show_prescribed" id="show_prescribed" <?php if($show_prescribed == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> onclick="selectMedicationItems()" />&nbsp;Practice Prescribed</label>
				</td>
			</tr>
			<tr><td colspan="3">&nbsp;</td></tr>
		    </table>
		</div>
<?php endif; ?>
		
	    <div style="float:right; width:40%">
	        <table cellpadding="0" cellspacing="0" align="right">
		    <tr>
			    <td>
				    <label for="show_all_medications" class="label_check_box"><input type="checkbox" name="show_all_medications" id="show_all_medications" <?php if($show_all_medications == 'yes') { echo 'checked="checked"'; } else { echo ''; } ?> onclick="selectMedicationItems()" />&nbsp;Show All Medications</label>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		    </table>
		</div>
       	<form id="frmPatientMedicationListGrid" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
            <table id="table_medical" cellpadding="0" cellspacing="0"  class="listing">
                <tr deleteable="false">
                    <th><?php echo $paginator->sort('Medication', 'medication', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('Source', 'source', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('RxNorm', 'rxnorm', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('Start Date', 'start_date', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('End Date', 'end_date', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
                    <th><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientMedicationList', 'class' => 'ajax'));?></th>
                </tr>
                <?php
                $i = 0;
                foreach ($PatientMedicationList as $PatientMedical_record):
					if ($PatientMedical_record['PatientMedicationList']['source'] == "Patient Reported")
					{
						?><tr editlink="<?php echo $html->url(array('action' => 'medication_list', 'task' => 'edit', 'patient_id' => $patient_id, 'medication_list_id' => $PatientMedical_record['PatientMedicationList']['medication_list_id'], 'patient_checkin_id' => $patient_checkin_id)); ?>"><?php
					}
					else
					{
						?><tr><?php
					}
                ?>
                    <td><?php echo $PatientMedical_record['PatientMedicationList']['medication']; ?></td>
                    <td><?php echo $PatientMedical_record['PatientMedicationList']['source']; ?></td>
                    <td><?php echo $PatientMedical_record['PatientMedicationList']['diagnosis']; ?></td>
                    <td><?php echo $PatientMedical_record['PatientMedicationList']['rxnorm']; ?></td>
                    <td><?php echo __date($global_date_format, strtotime($PatientMedical_record['PatientMedicationList']['start_date'])); ?></td>
                    <td><?php echo __date($global_date_format, strtotime($PatientMedical_record['PatientMedicationList']['end_date'])); ?></td>
                    <td><?php echo $PatientMedical_record['PatientMedicationList']['status']; ?></td> 
                </tr>
                <?php endforeach; ?>
            </table>
    	</form>
        <div style="width: auto; float: left;" removeonread="true">
            <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                 </ul>
            </div>
        </div>
        <div style="width: 70%; float: left;"></div>
        <div style="width: 30%; float: right; margin-top: 15px;">
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientMedicationList', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientMedicationList') || $paginator->hasNext('PatientMedicationList'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';  
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientMedicationList'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientMedicationList', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientMedicationList', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientMedicationList', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
        </div> 
<?php echo $this->element("patient_checkin_note", array('patient_id' => $patient_id, 'field' => 'medications','patient_checkin_id' => $patient_checkin_id)); ?>            
         
        
        <?php endif; ?>
    </div>
</div>
