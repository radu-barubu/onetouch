<form id="frmClinicalData" method="post" accept-charset="utf-8">
    <table id="table_clinical_data" cellpadding="0" cellspacing="0" class="listing">
    <tr>
        <th width="140" nowrap="nowrap"><?php echo $paginator->sort('Name', 'PatientDemographic.first_name', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th nowrap="nowrap"><?php echo $paginator->sort('Age', 'PatientDemographic.age', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th nowrap="nowrap"><?php echo $paginator->sort('Gender', 'PatientDemographic.gender_str', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <?php if($criterial->list_problem): ?><th><?php echo $paginator->sort('Diagnosis', 'PatientDemographic.diagnoses', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>
        <?php if($criterial->list_medication): ?><th><?php echo $paginator->sort('Medication', 'PatientDemographic.medications', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>
        <?php if($criterial->list_immunization): ?><th nowrap="nowrap"><?php echo $paginator->sort('Vaccine Name', 'PatientDemographic.vaccine_names', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>
        <?php if($criterial->list_lab_test): ?><th><?php echo $paginator->sort('Lab Test Results', 'PatientDemographic.lab_results', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>
        <?php if($criterial->poc_lab_test): ?><th><?php echo $paginator->sort('POC Lab Test', 'PatientDemographic.poc_lab_results', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>    
        <?php if($criterial->poc_radiology): ?><th><?php echo $paginator->sort('POC Radiology', 'PatientDemographic.poc_radiology', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?> 
        <?php if($criterial->poc_procedure): ?><th><?php echo $paginator->sort('POC Procedure', 'PatientDemographic.poc_procedure', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>    
        <?php if($criterial->poc_injection): ?><th><?php echo $paginator->sort('POC Injection', 'PatientDemographic.poc_injection', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>    
        <?php if($criterial->poc_medication): ?><th><?php echo $paginator->sort('POC Meds', 'PatientDemographic.poc_medication', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>    
        <?php if($criterial->poc_supply): ?><th><?php echo $paginator->sort('POC Supplies', 'PatientDemographic.poc_supply', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php endif; ?>    </tr>
    <?php
    $i = 0;
    foreach ($patients as $patient):
    ?>
        <tr>
            <td class="ignore" nowrap="nowrap"><?php echo $patient['PatientDemographic']['patientName']; ?> </td>
            <td class="ignore" nowrap="nowrap"><?php echo $patient['PatientDemographic']['age']; ?> </td>
            <td class="ignore" nowrap="nowrap"><?php echo $patient['PatientDemographic']['gender_str']; ?> </td>
            
                        <?php

                        $diagnoses = isset($patient['PatientDemographic']['diagnoses'])?@$patient['PatientDemographic']['diagnoses']:@$patient['PatientProblemList']['PatientDemographic__diagnoses'];
                        $medications = isset($patient['PatientDemographic']['medications'])?@$patient['PatientDemographic']['medications']:@$patient['PatientMedicationList']['PatientDemographic__medications'];
                        $vaccine_names = isset($patient['PatientDemographic']['vaccine_names'])?@$patient['PatientDemographic']['vaccine_names']:@$patient['EncounterPointOfCare']['PatientDemographic__vaccine_names'];
                        $poc_lab_results = isset($patient['PatientDemographic']['poc_lab_results'])?@$patient['PatientDemographic']['poc_lab_results']:@$patient['EncounterPointOfCare']['PatientDemographic__poc_lab_results'];
                        $poc_radiology = isset($patient['PatientDemographic']['poc_radiology'])?@$patient['PatientDemographic']['poc_radiology']:@$patient['EncounterPointOfCare']['PatientDemographic__poc_radiology'];
                        $poc_procedure = isset($patient['PatientDemographic']['poc_procedure'])?@$patient['PatientDemographic']['poc_procedure']:@$patient['EncounterPointOfCare']['PatientDemographic__poc_procedure'];
                        $poc_injection = isset($patient['PatientDemographic']['poc_injection'])?@$patient['PatientDemographic']['poc_injection']:@$patient['EncounterPointOfCare']['PatientDemographic__poc_injection'];
                        $poc_medication = isset($patient['PatientDemographic']['poc_medication'])?@$patient['PatientDemographic']['poc_medication']:@$patient['EncounterPointOfCare']['PatientDemographic__poc_medication'];
                        $poc_supply = isset($patient['PatientDemographic']['poc_supply'])?@$patient['PatientDemographic']['poc_supply']:@$patient['EncounterPointOfCare']['PatientDemographic__poc_supply'];
                        
                        if($criterial->list_lab_test)
                        {
                                $lab_results = $patient['PatientDemographic']['lab_results'];
                                
                                if($lab_results == ':')
                                {
                                        $lab_results = "";
                                }
                        }
                        
                        ?>

                        <?php if($criterial->list_problem): ?><td class="ignore"><?php echo $diagnoses; ?> </td><?php endif; ?>
            <?php if($criterial->list_medication): ?><td class="ignore"><?php echo $medications; ?> </td><?php endif; ?>
            <?php if($criterial->list_immunization): ?><td class="ignore"><?php echo $vaccine_names; ?> </td><?php endif; ?>
            <?php if($criterial->list_lab_test): ?><td class="ignore"><?php echo $lab_results; ?> </td><?php endif; ?>
            <?php if($criterial->poc_lab_test): ?><td class="ignore"><?php echo $poc_lab_results; ?> </td><?php endif; ?>
            <?php if($criterial->poc_radiology): ?><td class="ignore"><?php echo $poc_radiology; ?> </td><?php endif; ?>
            <?php if($criterial->poc_procedure): ?><td class="ignore"><?php echo $poc_procedure; ?> </td><?php endif; ?>
            <?php if($criterial->poc_injection): ?><td class="ignore"><?php echo $poc_injection; ?> </td><?php endif; ?>
            <?php if($criterial->poc_medication): ?><td class="ignore"><?php echo $poc_medication; ?> </td><?php endif; ?>
            <?php if($criterial->poc_supply): ?><td class="ignore"><?php echo $poc_supply; ?> </td><?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </table>
</form>

<div style="width: 40%; float: left;">
    <div class="actions">
        <ul>
                <li><a id="btnGenerate" href="javascript:void(0);">Merge All Results</a></li>
                <li><a id="btnGenerate2" href="javascript:void(0);">Separate Results</a></li>
            	<li><a id="btnDownload" href="javascript:void(0);">Download</a></li>
        </ul>
    </div>
</div>
<div style="width: 60%; float: right; margin-top: 15px;">
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'PatientDemographic', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('PatientDemographic') || $paginator->hasNext('PatientDemographic'))
            {
                echo '  &mdash;  ';
            }
        ?>
        <?php 
            if($paginator->hasPrev('PatientDemographic'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'PatientDemographic', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'PatientDemographic', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('PatientDemographic'))
            {
                echo $paginator->next('Next >>', array('model' => 'PatientDemographic', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
</div>
