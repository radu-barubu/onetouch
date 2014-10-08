<?php 
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$task_imm = $this->Session->webroot.'reports/immunization_registries/task:export_imm';
$task_patient =  $this->Session->webroot.'reports/patient_lists/task:export';
$task_hl7 = $this->Session->webroot.'reports/immunization_registries/task:export_imm_hl7';
?>

<form id="frm" method="post" action="<?php echo $this->Session->webroot.'reports/immunization_registries/task:export_imm'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
<input type="hidden" name="data[header_row]" value="<?php echo implode("|", $header_row); ?>" />
<?php
if(count($all_data_csv)>0)
{
echo "<input type='hidden' name='data[data][0]' id='data0' value='' />";
}
?>
</form>

<form id="frm_HL7" method="post" accept-charset="utf-8" enctype="multipart/form-data">
<input type="hidden" name="patient_id" value="" id="patient_id" />
<input type="hidden" name="encounter_id" value="" id="encounter_id" />
<input type="hidden" name="point_of_care_id" value="" id="point_of_care_id" />
</form>

<form id="frmClinicalData" method="post" accept-charset="utf-8">
    <table id="table_clinical_data" cellpadding="0" cellspacing="0" class="listing">
    <tr>
        <th width="140"><?php echo $paginator->sort('Name', 'PatientDemographic.patientName', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('Age', 'PatientDemographic.age', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
		
        <th><?php echo $paginator->sort('Gender', 'PatientDemographic.gender_str', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
		<th><?php echo $paginator->sort('Vaccine Name', 'PatientDemographic.vaccine_name_imm', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
		<th><?php echo $paginator->sort('Vaccine Date', 'PatientDemographic.vaccine_date_imm', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
		<th><?php echo $paginator->sort('Administrator', 'PatientDemographic.vaccine_administered', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <?php if($criterial->list_immunization): ?><th width="100"><?php echo $paginator->sort('Vaccine Name', 'PatientDemographic.vaccine_names', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>
		<th>Downloads<!--<?php echo $paginator->sort('Downloads', array('model' => 'PatientDemographic', 'class' => 'ajax'));?>--></th>
    </tr>
    <?php
    $i = 0;
    foreach ($patients as $key => $patient)
	{
	//var_dump($patient);
	$vaccine_administered = isset($patient['PatientDemographic']['vaccine_administered'])?@$patient['PatientDemographic']['vaccine_administered']:@$patient['EncounterPointOfCare']['PatientDemographic__vaccine_administered'];
	$vaccine_code = isset($patient['PatientDemographic']['vaccine_code'])?@$patient['PatientDemographic']['vaccine_code']:@$patient['EncounterPointOfCare']['PatientDemographic__vaccine_code'];
	
	$vaccine_lot_number = isset($patient['PatientDemographic']['vaccine_lot_number_demo'])?@$patient['PatientDemographic']['vaccine_lot_number_demo']:@$patient['EncounterPointOfCare']['PatientDemographic__vaccine_lot_number_demo'];
	
	$vaccine_manufacturer = isset($patient['PatientDemographic']['vaccine_manufacturer_demo'])?@$patient['PatientDemographic']['vaccine_manufacturer_demo']:@$patient['EncounterPointOfCare']['PatientDemographic__vaccine_manufacturer_demo'];
	
	$immtrack_vac_code = isset($patient['PatientDemographic']['immtrack_vac_code_demo'])?@$patient['PatientDemographic']['immtrack_vac_code_demo']:@$patient['EncounterPointOfCare']['PatientDemographic__immtrack_vac_code_demo'];
	
	$vaccine_name_imm = isset($patient['PatientDemographic']['vaccine_name_imm'])?@$patient['PatientDemographic']['vaccine_name_imm']:@$patient['EncounterPointOfCare']['PatientDemographic__vaccine_name_imm'];
	
	$vaccine_date_imm = isset($patient['PatientDemographic']['vaccine_date_imm'])?@$patient['PatientDemographic']['vaccine_date_imm']:@$patient['EncounterPointOfCare']['PatientDemographic__vaccine_date_imm'];
	$print_data = ($key+1)."|"."C"."|".$patient['PatientDemographic']['last_name']."|".$patient['PatientDemographic']['first_name']."|".$patient['PatientDemographic']['middle_name']."|".$patient['PatientDemographic']['ssn']."|".$patient['PatientDemographic']['gender_str']."|".$patient['PatientDemographic']['race']."|".""."|".$patient['PatientDemographic']['dob']."|".""."|".""."|".""."|".""."|".""."|".""."|".$patient['PatientDemographic']['address1']."|".$patient['PatientDemographic']['address2']."|".$patient['PatientDemographic']['city']."|".$patient['PatientDemographic']['state']."|".$patient['PatientDemographic']['zipcode']."|".$patient['PatientDemographic']['immtrack_county']."|".$patient['PatientDemographic']['immtrack_country']."|".$patient['PatientDemographic']['home_phone']."|".""."|"."I"."|".$vaccine_code."|".$vaccine_date_imm."|".$vaccine_lot_number."|".$vaccine_manufacturer."|".$immtrack_vac_code."|"."TR";

    ?>
        <tr>
            <td class="ignore"><?php echo $patient['PatientDemographic']['patientName']; ?>&nbsp;</td>
           <td class="ignore"><?php echo $patient['PatientDemographic']['age']; ?>&nbsp;</td>
           <td class="ignore"><?php echo $patient['PatientDemographic']['gender_str']; ?>&nbsp;</td>
			<?php if($criterial->list_immunization_registries){ ?> <td class="ignore"><?php echo $vaccine_name_imm; ?>&nbsp;</td>
			<td class="ignore"><?php echo $vaccine_date_imm; ?>&nbsp;</td>
			<td class="ignore"><?php echo $vaccine_administered; ?>&nbsp;</td><?php	} ?>
			<?php
			$point_of_care_id_array = array();
			$vaccine_names = isset($patient['PatientDemographic']['vaccine_names'])?@$patient['PatientDemographic']['vaccine_names']:@$patient['EncounterPointOfCare']['PatientDemographic__vaccine_names'];
			$encounter_id = isset($patient['PatientDemographic']['encounter_id'])?@$patient['PatientDemographic']['encounter_id']:@$patient['EncounterPointOfCare']['PatientDemographic__encounter_id'];
			$point_of_care_id_array = isset($patient['PatientDemographic']['point_of_care_id'])?@$patient['PatientDemographic']['point_of_care_id']:@$patient['EncounterPointOfCare']['PatientDemographic__point_of_care_id'];
			
			$point_of_care_id_te = explode(",", $point_of_care_id_array);
			$point_of_care_id = implode("|", $point_of_care_id_te);
	
			if($criterial->list_lab_test)
			{
				$lab_results = $patient['PatientDemographic']['lab_results'];
				
				if($lab_results == ':')
				{
					$lab_results = "";
				}
			}	
			?>
            <?php if($criterial->list_immunization): ?><td class="ignore"><?php echo $vaccine_names; ?>&nbsp;</td><?php endif; ?>
			<td class="ignore"><a href="javascript:void(0);" onclick = "exportData(<?php echo  $patient['PatientDemographic']['patient_id']; ?>,<?php echo  $encounter_id; ?>,'<?php echo  $point_of_care_id; ?>');" > HL7</a> | <a class="btnDownload" href="javascript:void(0);" val="<?php echo $print_data; ?>">Text</a>&nbsp;</td>
        </tr>
    <?php 
	}
	?>
    </table>
</form>
<div style="width: 20%; float: left;">
    <div class="actions">
        <ul>
                <li><a id="btnGenerate" href="javascript:void(0);">Merge</a></li>
            <!--<li><a id="btnDownload" href="javascript:void(0);">Download</a></li>-->
        </ul>
    </div>
</div>
<div style="width: 80%; float: right; margin-top: 15px;">
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'PatientDemographic', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('PatientDemographic') || $paginator->hasNext('PatientDemographic'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('PatientDemographic'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'PatientDemographic', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'PatientDemographic', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => ',&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('PatientDemographic'))
            {
                echo $paginator->next('Next >>', array('model' => 'PatientDemographic', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
</div>
<script>
function exportData(patient_id, encounter_id, point_of_care_id)
		{
			$('#patient_id').val(patient_id);
			$('#encounter_id').val(encounter_id);
			$('#point_of_care_id').val(point_of_care_id);
			$("#frm_HL7").attr("action", "<?php echo $task_hl7; ?>") 
			$("#frm_HL7").submit();
		}
		
		
</script>