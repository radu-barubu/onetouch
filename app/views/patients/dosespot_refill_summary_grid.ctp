<div id="summary_area" class="tab_area">
    <table id="summary_table" cellpadding="0" cellspacing="0" class="listing">
        <tr>
        	<th width="160"><?php echo $paginator->sort('Patient Name', 'patient_name', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
            <th><?php echo $paginator->sort('Medication', 'medication_name', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
		<th><?php echo $paginator->sort('Provider', 'prescriber_name', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
            <th width="140"><?php echo $paginator->sort('Refills', 'refills', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
            <th width="140" nowrap="nowrap"><?php echo $paginator->sort('Refill/Request Date', 'requested_date', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
            <th><?php echo $paginator->sort('Refill Status', 'request_status', array('model' => 'DosespotRefillRequest', 'class' => 'ajax'));?></th>
        </tr>
        <?php
		$i = 0;
		foreach ($refills as $refill):
            $skip = '';
        if($refill['DosespotRefillRequest']['patient_exist'] == 0)
        {
            $editlink = $html->url(array('controller' => 'reports', 'action' => 'unmatched_rxrefill_requests', 'task' => 'view_refill_request', 'refill_request_id' => $refill['DosespotRefillRequest']['refill_request_id'], array('escape' => false)));             
        }
        else
        {
            $editlink = $html->url(array('controller' => 'patients', 'action' => 'index', 'view' => 'medical_information',  'task' => 'edit', 'patient_id' => $refill['DosespotRefillRequest']['patient_id'], 'view_medications' => 1, 'dosespot' => 'show_dosespot_refill'), array('escape' => false)); 
        }
		?>
			<tr  editlink="<?php echo $editlink; ?>">
            	<td><?php echo $refill['DosespotRefillRequest']['patient_name']; ?></td>
				<td><?php echo $refill['DosespotRefillRequest']['medication_name']; ?></td>
				<td><?php echo $refill['DosespotRefillRequest']['prescriber_name']; ?></td>
				<td><?php echo $refill['DosespotRefillRequest']['refills']; ?></td>					
				<td><?php 
				if($refill['DosespotRefillRequest']['requested_date'] != '')
				{
					echo __date($global_date_format, strtotime($refill['DosespotRefillRequest']['requested_date']));
				}
				 ?></td>
				<td><?php echo ($refill['DosespotRefillRequest']['request_status'] == 'Queued') ? 'Pending Approval':$refill['DosespotRefillRequest']['request_status']; ?></td>
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
        <?php echo $paginator->counter(array('model' => 'DosespotRefillRequest', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('DosespotRefillRequest') || $paginator->hasNext('DosespotRefillRequest'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('DosespotRefillRequest'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'DosespotRefillRequest', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'DosespotRefillRequest', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('Demo'))
            {
                echo $paginator->next('Next >>', array('model' => 'DosespotRefillRequest', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>

</div>
