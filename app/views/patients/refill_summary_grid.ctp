<?php
$practice_settings = $this->Session->read("PracticeSetting");
$rx_setup =  $practice_settings['PracticeSetting']['rx_setup'];
?>
<div id="summary_area" class="tab_area">
    <table id="summary_table" cellpadding="0" cellspacing="0" class="listing">
        <tr>
        	<th width="120" nowrap="nowrap"><?php echo $paginator->sort('Patient Name', 'PatientMedicationRefill.patientName', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
            <th><?php echo $paginator->sort('Medication', 'medication', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
            <th width="140"><?php echo $paginator->sort('Source', 'source', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
            <th><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
            <th width="140"><?php echo $paginator->sort('Request Date', 'refill_request_date', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
            <th width="80"><?php echo $paginator->sort('Status', 'refill_status', array('model' => 'PatientMedicationRefill', 'class' => 'ajax'));?></th>
        </tr>
        <?php
		$i = 0;
		foreach ($refills as $refill):
		if($rx_setup=='Electronic_Dosespot')
		{
		    $editlink = $html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information',  'task' => 'edit', 'patient_id' => $refill['PatientMedicationRefill']['patient_id'], 'view_medications' => 1, 'dosespot' => 'show_dosespot_refill'), array('escape' => false));
		}
		else
		{		
		    $editlink = $html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information',  'task' => 'edit', 'patient_id' => $refill['PatientMedicationRefill']['patient_id'], 'view_medications' => 1, 'refill_id' => $refill['PatientMedicationRefill']['refill_id']), array('escape' => false));
		}
		?>
			<tr editlink="<?php echo $editlink; ?>">
            	<td><?php echo $refill['PatientDemographic']['patientName']; ?></td>
				<td><?php echo $refill['PatientMedicationRefill']['medication']; ?></td>
				<td><?php echo $refill['PatientMedicationRefill']['source']; ?></td>					
				<td><?php echo $refill['PatientMedicationRefill']['diagnosis']; ?></td>
				<td><?php echo __date($global_date_format, strtotime($refill['PatientMedicationRefill']['refill_request_date'])); ?></td>
				<td><?php echo $refill['PatientMedicationRefill']['refill_status']; ?></td>
			</tr>
		<?php endforeach;
				
		if(empty($refills)) {
		?>
			<tr>
				<td class="ignore" colspan="8" align="center">None</td>
			</tr>
		<?php } ?>
    </table>
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
</div>