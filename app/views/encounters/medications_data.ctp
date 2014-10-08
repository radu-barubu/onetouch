<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$mainURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id)) . '/';
$diagnosis_autoURL = $html->url(array('action' => 'icd9', 'task' => 'load_autocomplete')) . '/'; 
$medication_autoURL = $html->url(array('action' => 'meds_list', 'task' => 'load_autocomplete')) . '/';   
$provider_autoURL = $html->url(array('controller' =>'patients','action' => 'medication_list', 'task' => 'load_provider_autocomplete')) . '/';   

extract($EditItem['PatientMedicationList']);

$start_date = (isset($start_date) and (!strstr($start_date, "0000") and (!empty($start_date))) )?date($global_date_format, strtotime($start_date)):'';
$end_date = (isset($end_date) and (!strstr($end_date, "0000") and (!empty($end_date))) )?date($global_date_format, strtotime($end_date)):'';

$page_access = $this->QuickAcl->getAccessType("encounters", "meds");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

?>
<script language="javascript" type="text/javascript">
    function updateMedication(field_id, field_val)
	{
		var medication_list_id = $("#medication_list_id").val();
		var formobj = $("<form></form>");
		formobj.append('<input name="medication_list_id" type="hidden" value="'+medication_list_id+'">');
		formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
		formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');
	
		$.post('<?php echo $this->Session->webroot; ?>encounters/medications_data/patient_id:<?php echo $patient_id; ?>/task:edit/', formobj.serialize(), 
		function(data){}
		);
		
		setTimeout("resetMedication(null)",200);
	}
    $(document).ready(function()
    {
        $("#diagnosis").autocomplete('<?php echo $diagnosis_autoURL; ?>', {
            max: 20,
           mustMatch: false,
            matchContains: false
        });
        
        $("#diagnosis").result(function(event, data, formatted)
        {
            //alert('1. '+data[0]+' 2. '+data[1]+' 3. '+data[2]);
			updateMedication('diagnosis', data[0]);
			updateMedication('icd_code', data[1]);               
            $("#icd_code").val(data[1]);
        });
        
		$("#medication").autocomplete('<?php echo $medication_autoURL; ?>', {
            max: 20,
           mustMatch: false,
            matchContains: false
        });
        
		$("#provider").autocomplete('<?php echo $provider_autoURL; ?>', {
            max: 20,
           mustMatch: false,
            matchContains: false
        });
		
		$("#provider").result(function(event, data, formatted)
        {
		    //alert('Test'+data[1]);
            $('#provider_id').val(data[1]);
			
			updateMedication('provider', data[0]);
			updateMedication('provider_id', data[1]);
			
        });
		
        $("#medication").result(function(event, data, formatted)
        {
			$('#rxnorm').val(data[1]);
			$('#medication_form').val(data[2]);
			$('#medication_strength_value').val(data[3]);
			$('#medication_strength_unit').val(data[4]);
            
			updateMedication('medication', data[0]);
			updateMedication('rxnorm', data[1]);
			updateMedication('medication_form', data[2]);
			updateMedication('medication_strength_value', data[3]);
			updateMedication('medication_strength_unit', data[4]);
			
        });
        
        $("#start_date").datepicker(
		{ 
			changeMonth: true,
			changeYear: true,
			showOn: 'button',
			buttonText: '',
			yearRange: 'c-90:c+10',
			dateFormat: "<?php if($global_date_format=='d/m/Y') { echo 'dd/mm/yy'; } else if($global_date_format=='Y/m/d') { echo 'yy/mm/dd'; } else{ echo 'mm/dd/yy'; } ?>",
			onSelect: function() { updateMedication(this.id, this.value); }
		});
		
		$("#end_date").datepicker(
		{ 
			changeMonth: true,
			changeYear: true,
			showOn: 'button',
			buttonText: '',
			yearRange: 'c-90:c+10',
			dateFormat: "<?php if($global_date_format=='d/m/Y') { echo 'dd/mm/yy'; } else if($global_date_format=='Y/m/d') { echo 'yy/mm/dd'; } else{ echo 'mm/dd/yy'; } ?>",
			onSelect: function() { updateMedication(this.id, this.value); }
		});
		<?php echo $this->element('dragon_voice'); ?>
    });
</script>
<h4>Medication Details:</h4>
<div style="float:left; width:100%">
     <form id="frmPatientMedicationList" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data"> 
	 <input type="hidden" name="medication_list_id" id="medication_list_id" value="<?php echo $medication_list_id; ?>" />
         <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
		        <tr>
                    <td width="140"><label>Medication:</label></td>
                    <td> 
                    	<input type="text" name="data[PatientMedicationList][medication]" id="medication" value="<?php echo $medication;?>" style="width:98%;" />
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
                    <td> <input type="text" name="data[PatientMedicationList][diagnosis]" id="diagnosis" value="<?php echo $diagnosis;?>" style="width:98%;" onblur="updateMedication(this.id, this.value);" class="dragon" />
                    <input type="hidden" name="data[PatientMedicationList][icd_code]" id="icd_code" value="<?php echo $icd_code;?>" /></td>
                </tr>
                <tr>
					<td style="vertical-align:top;"><label>SIG:</label></td>
					<td align="left">
                        <table cellpadding="0" cellspacing="0" style="width:85%">
                            <tr>
                                <td style="width:5%">
                                    <select name="data[PatientMedicationList][quantity]" id="quantity" size="10"  onchange="updateMedication(this.id, this.value);">
				     <option value="">--</option>
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
                                    <select name="data[PatientMedicationList][unit]" id="unit" size="10" onchange="updateMedication(this.id, this.value);">
				    <option value="">--</option>
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
                                    <select name="data[PatientMedicationList][route]" id="route" size="10" onchange="updateMedication(this.id, this.value);">
				    <option value="">--</option>
                                    <?php
                                    foreach($rx_route as $value)
                                    {
                                        if($value == $route)
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
                                    <select name="data[PatientMedicationList][frequency]" id="frequency" size="10"  onchange="updateMedication(this.id, this.value);">
				    <option value="">--</option>
                                    <?php					
                                    foreach($rx_freq as $values)
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
                                <td style="width:5%">
                                    <select name="data[PatientMedicationList][rx_alt]" id="rx_alt" size="10"  onchange="updateMedication(this.id, this.value);">
                                    <option value="">--</option>
                                    <?php
 					foreach($rx_alt1 as $value1)
                                        {
                                        if($value1 == $rx_alt)
                                        {
                                            echo '<option value="'.$value1.'" selected>'.$value1.'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value="'.$value1.'">'.$value1.'</option>';
                                        }
                                       }

                                   ?>
                                    </select>
                                </td>
								<td style="vertical-align:top; padding-left:20px;width:18%;"><a href="javascript: void(0);" onclick="$('#direction_table').toggle();">Manual Data >></a></td>
								<td style="vertical-align:top; padding-left:20px; width:70%">
								    <div id="direction_table" style="width:100%; display: <?php echo ($direction!='')?'':'none'; ?>">
									   <textarea id="direction" name="data[PatientMedicationList][direction]" style="height: 85px;" onblur="updateMedication(this.id, this.value);"><?php echo isset($direction)?$direction:''; ?></textarea><!--<input type="text" id="direction" name="data[PatientMedicationList][direction]" style="width: 510px;" value="<?php echo isset($direction)?$direction:''; ?>" />--></div>
								</td>
                            </tr>
                         </table>
				     </td>
			    </tr>
                <!--<tr>
				    <td class="top_pos"><label>Direction: </label></td>
				    <td><textarea id="direction" name="data[PatientMedicationList][direction]" style="width: 98%; height: 85px;" onblur="updateMedication(this.id, this.value);"><?php echo isset($direction)?$direction:''; ?></textarea></td>
			    </tr>-->
                <tr>
				    <td><label>Quantity: </label></td>
				    <td>
                    	<input type='text' name='data[PatientMedicationList][quantity_value]' id='quantity_value' class="numeric_only" value="<?php echo isset($quantity_value)?$quantity_value:''; ?>" style="width: 50px;" onblur="updateMedication(this.id, this.value);" />
                       <!-- // this is not needed on patient reported medications.... only for when doc orders new Rx  <select name="data[PatientMedicationList][quantity_unit]" id="quantity_unit" onchange="updateMedication(this.id, this.value);">
						<?php
						/*
                        foreach($rx_unit as $value0)
                        {
                            if($value0 == @$quantity_unit)
                            {
                                echo '<option value="'.$value0.'" selected>'.$value0.'</option>';
                            }
                            else
                            {
                                echo '<option value="'.$value0.'">'.$value0.'</option>';
                            }
                        }  */
                        ?>
                        </select> -->
                    </td>
			    </tr>
			    <tr>
				    <td><label>Dispensed #: </label></td>
				    <td><input type='text' name='data[PatientMedicationList][dispense]' id='dispense' value="<?php echo isset($dispense)?$dispense:'0'; ?>" onblur="updateMedication(this.id, this.value);" />				 
				    </td>
			    </tr>
			    <tr>
				    <td><label>Refills #: </label></td>
				    <td><input type='text' class="refill_field" medication_list_id="<?php echo $medication_list_id; ?>" name='data[PatientMedicationList][refill_allowed]' id='refill_allowed' value="<?php echo isset($refill_allowed)?$refill_allowed:''; ?>" onblur="updateMedication(this.id, this.value);" />				 
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
                <tr>
                    <td><label>Source:</label></td>
                    <td>
                    <select name="data[PatientMedicationList][source]" id="source" onchange="updateMedication(this.id, this.value);" >
                    <option value=""></option>
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
					<input type="hidden" name="data[PatientMedicationList][provider_id]" id="provider_id" value="<?php echo $provider_id; ?>" style="width:200px;"/>
					 
					<input type="text" name="data[PatientMedicationList][provider]" id="provider" value="<?php echo $provider; ?>" style="width:200px;" onblur="updateMedication(this.id, this.value);" />
					</td>
                </tr>
				<tr>
                    <td><label>Status:</label></td>
                    <td>
                    <select name="data[PatientMedicationList][status]" id="status" onchange="updateMedication(this.id, this.value);" >
                    <option value=""></option>
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
      </form>   
</div>
