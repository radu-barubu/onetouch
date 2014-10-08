<?php 
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$task_imm = $this->Session->webroot.'reports/immunization_registries/task:export_imm';
$task_patient =  $this->Session->webroot.'reports/patient_lists/task:export';
$task_hl7 = $this->Session->webroot.'reports/public_health_surveillance/task:export_imm_hl7';
?>

<form id="frm_HL7" method="post" accept-charset="utf-8" enctype="multipart/form-data">
<input type="hidden" name="patient_id" value="" id="patient_id" />
<input type="hidden" name="encounter_id" value="" id="encounter_id" />
<input type="hidden" name="assessment_id" value="" id="assessment_id" />
</form>

<form id="frmHealthData" method="post" accept-charset="utf-8">
    <table id="table_clinical_data" cellpadding="0" cellspacing="0" class="listing">
    <tr>
        <th width="140"><?php echo $paginator->sort('Name', 'PatientDemographic.patientName', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('Age', 'PatientDemographic.age', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
		
        <th><?php echo $paginator->sort('Gender', 'PatientDemographic.gender_str', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
		<th><?php echo $paginator->sort('Diagnosis', 'PatientDemographic.diagnosis_name', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
		<th><?php echo $paginator->sort('Occurance', 'PatientDemographic.occurence', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
		<!--<th><?php echo $paginator->sort('Administrator', 'PatientDemographic.vaccine_administered', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>-->
        <?php if($criterial->list_immunization): ?><th width="100"><?php echo $paginator->sort('Vaccine Name', 'PatientDemographic.vaccine_names', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>
		<th>Download</th>
    </tr>
    <?php
    $i = 0;
    foreach ($patients as $key => $patient)
	{
	//debug($patient);
	$diagnoses = isset($patient['PatientDemographic']['diagnosis_name'])?@$patient['PatientDemographic']['diagnosis_name']:@$patient['EncounterAssessment']['PatientDemographic__diagnosis_name'];
	
	$occurence = isset($patient['PatientDemographic']['occurence'])?@$patient['PatientDemographic']['occurence']:@$patient['EncounterAssessment']['PatientDemographic__occurence'];
    ?>
        <tr>
            <td class="ignore"><?php echo $patient['PatientDemographic']['patientName']; ?>&nbsp;</td>
            <td class="ignore"><?php echo $patient['PatientDemographic']['age']; ?>&nbsp;</td>
            <td class="ignore"><?php echo $patient['PatientDemographic']['gender_str']; ?>&nbsp;</td>
		    <td class="ignore"><?php echo $diagnoses; ?>&nbsp;</td>
			<td class="ignore"><?php echo $occurence; ?>&nbsp;</td>
			<?php
			
			$encounter_id = isset($patient['PatientDemographic']['encounter_id'])?@$patient['PatientDemographic']['encounter_id']:@$patient['EncounterAssessment']['PatientDemographic__encounter_id'];
			$assessment_id = isset($patient['PatientDemographic']['assessment_id'])?@$patient['PatientDemographic']['assessment_id']:@$patient['EncounterAssessment']['PatientDemographic__assessment_id'];
			//debug($assessment_id);
			?>
            
			<td class="ignore"><a href="javascript:void(0);" onclick = "exportData('<?php echo  $patient['PatientDemographic']['patient_id']; ?>','<?php echo  $encounter_id; ?>','<?php echo  $assessment_id; ?>');" > HL7</a>&nbsp;</td>
        </tr>
    <?php 
	}
	?>
    </table>
</form>
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
function exportData(patient_id, encounter_id, assessment_id)
		{
			$('#patient_id').val(patient_id);
			$('#encounter_id').val(encounter_id);
			$('#assessment_id').val(assessment_id);
			$("#frm_HL7").attr("action", "<?php echo $task_hl7; ?>") 
			$("#frm_HL7").submit();
		}
		
		
</script>