<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 * This template returns filtered seach results for unmatched lab reports
 */
?>

	<table id="table_medical" cellpadding="0" cellspacing="0" class="listing">
        <tr deleteable="false">
            <th width="85" nowrap="nowrap"><?php echo $paginator->sort('Order #', 'EmdeonLabResult.placer_order_number', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
            <th nowrap="nowrap"><?php echo $paginator->sort('Patient', 'EmdeonLabResult.report_patient_name', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
            <th>Test List</th>
            <th width="180" nowrap="nowrap"><?php echo $paginator->sort('Service Date/Time', 'EmdeonLabResult.report_service_date', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
            <th width="220" nowrap="nowrap"><?php echo $paginator->sort('Transaction Date/Time', 'EmdeonLabResult.date_time_transaction', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
            <th width="120" nowrap="nowrap"><?php echo $paginator->sort('Ordered by', 'EmdeonLabResult.ordering_client', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
            <th width="120" nowrap="nowrap"><?php echo $paginator->sort('Status', 'EmdeonLabResult.status', array('model' => 'EmdeonLabResult', 'class' => 'ajax'));?></th>
        </tr>
        <?php if(!empty($lab_results)): ?>
			<?php foreach($lab_results as $result): ?>
				
            	<tr class="clickable" rel="<?php echo $this->Html->url(array('controller' => 'reports', 'action' => 'unmatched_lab_reports', 'task' => 'view_order', 'lab_result_id' => $result['EmdeonLabResult']['lab_result_id'])); ?>">
                    
                    <td><?php echo $result['EmdeonLabResult']['placer_order_number']; ?></td>
                    <td><?php echo $result['EmdeonLabResult']['report_patient_name']; ?></td>
                    <td><?php echo implode("<br>", $result['test_list']); ?></td>
                    <td><?php echo __date($global_date_format . ' ' . $global_time_format, strtotime($result['EmdeonLabResult']['report_service_date'])); ?></td>
                    <td><?php echo __date($global_date_format . ' ' . $global_time_format, strtotime($result['EmdeonLabResult']['date_time_transaction'])); ?></td>
                    <td><?php echo $result['EmdeonLabResult']['ordering_client']; ?></td>
                    <td><?php echo $result['EmdeonLabResult']['status']; ?></td>
            	</tr>
			<?php endforeach;?>
        <?php else: ?>
            <tr>
                <td colspan="7">
                <p id="no-result">These are lab results which were received, but did not match with a patient in the system.</p>
                </td>
            </tr>
        <?php endif; ?>
	</table>
	
	<?php // shows pagination ?>
	<div style="width: 60%; float: right; margin-top: 15px;">
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'EmdeonLabResult', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('EmdeonLabResult') || $paginator->hasNext('EmdeonLabResult'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('EmdeonLabResult'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'EmdeonLabResult', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'EmdeonLabResult', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('EmdeonLabResult'))
                {
                    echo $paginator->next('Next >>', array('model' => 'EmdeonLabResult', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
        </div>	
	</div>