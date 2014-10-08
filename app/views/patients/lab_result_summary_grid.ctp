<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script type="text/javascript">
$(document).ready(function()
{
                //create bubble popups for each element with class "button"
                $('.orphan_lbl').CreateBubblePopup();
                   //set customized mouseover event for each button
                   $('.orphan_lbl').mouseover(function(){ 
                        //show the bubble popup with new options
                        $(this).ShowBubblePopup({
                                alwaysVisible: true,
                                closingDelay: 200,
                                position :'top',
                                align    :'left',
                                tail     : {align: 'middle'},
                                innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
                                innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},                                                                         
                                                themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'                                                              
                         });
                   });
});
</script>
<div>
	<table id="summary_table" cellpadding="0" cellspacing="0" class="listing">
        <tr>
        	<th><?php echo $paginator->sort('Patient Name', 'sortable_name', array('class' => 'ajax'));?></th>
            <th style="width: 50%;">Test Name</th>
            <th style="">Status</th>
            <?php if(sizeof($providers) > 1): ?>
            <th><?php echo $paginator->sort('Provider', 'physician_first_name', array('class' => 'ajax'));?></th>
            <?php endif; ?>
            <th><?php echo $paginator->sort('Service Date', 'report_service_date', array('class' => 'ajax'));?></th>
        </tr>		
		<?php foreach($electronic_lab_results as $result): ?>
				
				<?php if ($result['EmdeonLabResult']['patient_id']): ?> 
		<tr class="clickable" rel="<?php echo $this->Html->url(array('controller' => 'patients', 'action' => 'index', 'task' => 'edit', 'patient_id' => $result['EmdeonLabResult']['patient_id'], 'view' => 'medical_information', 'view_tab' => 3, 'view_actions' => 'lab_results_electronic', 'view_task' => 'view_order', 'target_id_name' => 'lab_result_id',  'target_id' => $result['EmdeonLabResult']['lab_result_id'])); ?>">
				<?php else:?> 
		<tr class="clickable" rel="<?php echo $this->Html->url(array('controller' => 'reports', 'action' => 'unmatched_lab_reports', 'task' => 'view_order', 'lab_result_id' => $result['EmdeonLabResult']['lab_result_id'])); ?>">
				
				<?php endif;?> 
			<td>
				<?php echo htmlentities($result['EmdeonLabResult']['sortable_name']); echo (!$result['EmdeonLabResult']['patient_id']) ? ' <span style="color:red;font-size:26px;width:40px" class="orphan_lbl" id="azure" name="This is an Orphan Lab Result">*</span>':''; ?>
			</td>
			<td>
				<?php echo implode(', ', ($result['EmdeonLabResult']['_test_list'])); ?> 
			</td>
			<td>
				<?php echo $result['EmdeonLabResult']['status']; ?> 
			</td>			
			<?php if(sizeof($providers) > 1): ?>
			<td>
				<?php echo htmlentities($result['EmdeonLabResult']['physician_first_name']). ' ' .htmlentities($result['EmdeonLabResult']['physician_last_name']); ?>
			</td>
			<?php endif; ?>			
			<td>
				<?php echo __date("m/d/y", strtotime($result['EmdeonLabResult']['report_service_date'])); ?>
			</td>
		</tr>
		<?php endforeach;?> 
	</table>
	
	
	
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
            if($paginator->hasNext('Demo'))
            {
                echo $paginator->next('Next >>', array('model' => 'EmdeonLabResult', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>	
	
	
</div>
