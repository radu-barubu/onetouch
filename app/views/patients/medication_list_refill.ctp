<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$mainURL = $html->url(array('patient_id' => $patient_id));
$provider_autoURL = $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id, 'task' => 'load_provider_autocomplete')) . '/';  

echo $this->element("enable_acl_read", array('page_access' => $this->QuickAcl->getAccessType("patients", "medical_information")));
?>

<script language="javascript" type="text/javascript">
$(document).ready(function()
{
	initCurrentTabEvents('medical_records_area');
	
	$("#frmPatientMedicationRefill").validate(
    {
        errorElement: "div",
		errorPlacement: function(error, element) 
		{
			error.insertAfter(element);
		},
        submitHandler: function(form) 
        {
            $('#frmPatientMedicationRefill').css("cursor", "wait");
			
            $.post(
                '<?php echo $thisURL; ?>', 
                $('#frmPatientMedicationRefill').serialize(), 
                function(data)
                {
                    showInfo("Item(s) saved.", "notice");
					loadTab($('#frmPatientMedicationRefill'), '<?php echo $mainURL; ?>');
                },
                'json'
            );
        }
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
});
</script>
<div id="medical_records_area" class="tab_area" style="overflow: hidden;">    
    <div class="title_area">
        <div class="title_text">
            <a style="float: none;" class="ajax" href="<?php echo $html->url(array('action' => 'medication_list', 'patient_id' => $patient_id)); ?>">Medications</a>
            <a style="float: none;" class="ajax" href="<?php echo $html->url(array('action' => 'in_house_work_meds', 'patient_id' => $patient_id)); ?>">Point of Care</a>
            <a style="float: none;" class="ajax active" href="<?php echo $html->url(array('action' => 'medication_list_refill', 'patient_id' => $patient_id)); ?>">Refill Summary</a>
        </div>
    </div>
    <?php
    if($task == "edit")
    {
        extract($EditItem['PatientMedicationRefill']);
		
        ?>
        <form id="frmPatientMedicationRefill" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        	<input type="hidden" name="data[PatientMedicationRefill][refill_id]" id="refill_id" value="<?php echo $refill_id; ?>" />
            <table cellpadding="0" cellspacing="0" class="form" width="100%"> 
                <tr>
                    <td width="140"><label>Medication:</label></td>
                    <td> 
                        <input type="text" name="data[PatientMedicationRefill][medication]" id="medication" value="<?php echo $medication; ?>" style="width:98%; background: #eeeeee;" class="required" readonly/>
                    </td>
                </tr>
                <tr>
                    <td><label>RxNorm:</label></td>
                    <td> <input type="text" name="data[PatientMedicationRefill][rxnorm]" id="rxnorm" value="<?php echo $rxnorm; ?>" style="background: #eeeeee;" readonly /></td>
                </tr>
                <tr>
                    <td><label>Diagnosis:</label></td>
                    <td> <input type="text" name="data[PatientMedicationRefill][diagnosis]" id="diagnosis" value="<?php echo $diagnosis; ?>" style="width:98%;" />
                        <input type="hidden" name="data[PatientMedicationRefill][icd_code]" id="icd_code" value="<?php echo $icd_code; ?>" /></td>
                </tr>
                <tr>
                    <td style="vertical-align:top;"><label>SIG:</label></td>
                    <td align="left">
                        <table cellpadding="0" cellspacing="0" style="width:85%">
                            <tr>
                                <td style="width:5%">
                                    <select name="data[PatientMedicationRefill][quantity]" id="quantity" size="10" multiple="multiple">
                                        <?php
                                        $quantity_arr = array("1", "1-2", "2", "3", "4", "5", "6", "7", "8", "9", "10");
                                        foreach ($quantity_arr as $value)
                                        {
                                            if ($value == $quantity)
                                            {
                                                echo '<option value="' . $value . '" selected>' . $value . '</option>';
                                            }
                                            else
                                            {
                                                echo '<option value="' . $value . '">' . $value . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td style="width:5%">
                                    <select name="data[PatientMedicationRefill][unit]" id="unit" size="10" multiple="multiple">
                                        <?php
                                        $unit_arr = array("tab", "Tbsp", "tsp", "Capsule", "Puff(s)", "Spray(s)", "mg", "Drop(s)", "Box", "cc", "ml", "oz", "gm");
                                        foreach ($unit_arr as $value)
                                        {
                                            if ($value == $unit)
                                            {
                                                echo '<option value="' . $value . '" selected>' . $value . '</option>';
                                            }
                                            else
                                            {
                                                echo '<option value="' . $value . '">' . $value . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td style="width:5%">
                                    <select name="data[PatientMedicationRefill][route]" id="route" size="10" multiple="multiple">
                                        <?php
                                        $route_arr = array("PO", "Inj", "Inh", "Subq", "Otic", "Topical", "Oph", "Sublingual", "Vaginal");
                                        foreach ($route_arr as $value)
                                        {
                                            if ($value == $route)
                                            {
                                                echo '<option value="' . $value . '" selected>' . $value . '</option>';
                                            }
                                            else
                                            {
                                                echo '<option value="' . $value . '">' . $value . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td style="width:5%">
                                    <select name="data[PatientMedicationRefill][frequency]" id="frequency" size="10" multiple="multiple">
                                        <?php
                                        $frequency_arr = array("Q2|Q2&#176", "Q4|Q4&#176", "Q6|Q6&#176", "Q8|Q8&#176", "Q12|Q12&#176", "Q2-4|Q2-4&#176", "Q4-6|Q4-6&#176", "Q6-8|Q6-8&#176", "Qday|Q day", "Qwk|Q wk", "Qhs|Q hs", "Qam|Q am", "Qpm|Q pm", "Qmonth|Q month", "Qyear|Q year");
                                        foreach ($frequency_arr as $values)
                                        {
                                            $value = explode('|', $values);
                                            if ($value[0] == $frequency)
                                            {
                                                echo '<option value="' . $value[0] . '" selected>' . $value[1] . '</option>';
                                            }
                                            else
                                            {
                                                echo '<option value="' . $value[0] . '">' . $value[1] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td style="vertical-align:top; padding-left:20px; width:8%" ><a href="javascript: void(0);" onclick="$('#direction_table').toggle();">Manual</a></td>
                                <td style="vertical-align:top; padding-left:20px; width:80%" >
                                    <div id="direction_table" style="width:100%; display: <?php echo ($direction != '') ? '' : 'none'; ?>">
                                        <textarea id="direction" name="data[PatientMedicationRefill][direction]" cols="80" style="height: 85px;"><?php echo isset($direction) ? $direction : ''; ?></textarea>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td><label>Source:</label></td>
                    <td>
                        <select name="data[PatientMedicationRefill][source]" id="source" class="required">
                            <option value="" selected>Select Source</option>
                            <?php
                            $source_array = array("Practice Prescribed", "Patient Reported", "e-Prescribing History");
                            for ($i = 0; $i < count($source_array); ++$i)
                            {
                                echo "<option value=\"$source_array[$i]\" " . ($source == $source_array[$i] ? "selected" : "") . ">" . $source_array[$i] . "</option>";
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
                    	<input type="text" name="data[PatientMedicationRefill][provider]" id="provider" value="<?php echo $provider; ?>" style="width:200px;" />
                        <input type="hidden" name="data[PatientMedicationRefill][provider_id]" id="provider_id" value="<?php echo (isset($provider_id)?$provider_id:""); ?>" />
                    </td>
                </tr>
                <?php
					if($refill_status == "Requested")
					{
						$provider_name = $EditItem['UserAccountRequest']['firstname'] . ' ' . $EditItem['UserAccountRequest']['lastname'];
					}
					else
					{
						$provider_name = $EditItem['UserAccountRefill']['firstname'] . ' ' . $EditItem['UserAccountRefill']['lastname'];
					}
				?>
                <tr>
                    <td colspan="2"><label><em><strong><?php echo $refill_status; ?> by <?php echo $provider_name; ?> on <?php echo __date($global_date_format, strtotime($refill_request_date)); ?></strong></em></label></td>
                </tr>
            </table>
            <div class="actions">
                <ul>
                	<?php if($role_id == EMR_Roles::PHYSICIAN_ROLE_ID && $refill_status == "Requested"): ?>
                	<li removeonread="true"><a href="javascript:void(0);" onclick="$('#frmPatientMedicationRefill').submit();">Approve</a></li>
                    <?php endif; ?>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
            </div>
        </form>
        <?php
    }
	else if($task=='show_dosespot_refill')
	{
		$dosespot_url = $dosespot_info['dosespot_api_url']."LoginSingleSignOn.aspx?b=2&SingleSignOnClinicId=".$dosespot_info['SingleSignOnClinicId']."&SingleSignOnUserId=".$dosespot_info['SingleSignOnUserId']."&SingleSignOnPhraseLength=".$dosespot_info['SingleSignOnPhraseLength']."&SingleSignOnCode=".$dosespot_info['SingleSignOnCode']."&SingleSignOnUserIdVerify=".$dosespot_info['SingleSignOnUserIdVerify']."&RefillsErrors=1";

		?>
    	<iframe name="dosepotIFrame" id="dosepotIFrame" src="<?php echo $dosespot_url; ?>" width="98%" height="500" frameborder="0" scrolling="auto" ></iframe>
        <div class="actions">
                <ul>
                    <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                </ul>
         </div>
	<?php
	}
    else
    {
        ?>
        <form id="frmPatientMedicationRefill" method="post" accept-charset="utf-8">
            <table cellpadding="0" cellspacing="0" class="listing" border=1>
            <tr>
                <th><?php echo $paginator->sort('Medication', 'medication', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
				<th width="140"><?php echo $paginator->sort('Source', 'source', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
				<th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
                <th><?php echo $paginator->sort('RxNorm', 'rxnorm', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
                <th width="140" nowrap="nowrap"><?php echo $paginator->sort('Refill/Request Date', 'refill_request_date', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
                <th width="80"><?php echo $paginator->sort('Status', 'refill_status', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($refills as $refill):
            ?>
                <tr editlinkajax="<?php echo $html->url(array('task' => 'edit', 'patient_id' => $patient_id, 'refill_id' => $refill['PatientMedicationRefill']['refill_id'])); ?>">
                    <td><?php echo $refill['PatientMedicationRefill']['medication']; ?></td>
                    <td><?php echo $refill['PatientMedicationRefill']['source']; ?></td>					
                    <td><?php echo $refill['PatientMedicationRefill']['diagnosis']; ?></td>
					<td><?php echo $refill['PatientMedicationRefill']['rxnorm']; ?></td>
                    <td><?php echo __date($global_date_format, strtotime($refill['PatientMedicationRefill']['refill_request_date'])); ?></td>
                    <td><?php echo $refill['PatientMedicationRefill']['refill_status']; ?></td>
                </tr>
            <?php endforeach; ?>
            </table>
        </form>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientMedicationRefill', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientMedicationRefill') || $paginator->hasNext('PatientMedicationRefill'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientMedicationRefill'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientMedicationRefill', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientMedicationRefill', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('PatientMedicationRefill'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientMedicationRefill', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
        <?php
    }
    ?>
</div>