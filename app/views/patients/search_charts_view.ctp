<form id="frmSearchChartsResult" method="post" accept-charset="utf-8">
    <table id="search_charts_results_table" cellpadding="0" cellspacing="0" class="listing">
    <tr>
        <th><?php echo $paginator->sort('Last Name', 'PatientDemographic.last_name', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('First Name', 'PatientDemographic.first_name', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
				<?php if(isset($this->data['location_id'])) { ?><th><?php echo $paginator->sort('Location', 'PatientDemographic.location_name', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th><?php } ?>
        <th><?php echo $paginator->sort('MRN', 'PatientDemographic.mrn', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <?php if ($custom_pt): ?>
        <th><?php echo $paginator->sort('Custom Patient ID', 'PatientDemographic.custom_patient_identifier', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <?php endif; ?>
        <th><?php echo $paginator->sort('Sex', 'PatientDemographic.gender', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('DOB', 'PatientDemographic.dob', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('Home Phone', 'PatientDemographic.home_phone', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('Cell Phone', 'PatientDemographic.cell_phone', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
        <th><?php echo $paginator->sort('Status', 'PatientDemographic.status', array('model' => 'PatientDemographic', 'class' => 'ajax'));?></th>
		<?php if(@$isProvider) { ?><th>Quick Visit</th><?php } ?>
    </tr>
    <?php
    $i = 0;
    foreach ($patient_demographics as $patient_demographic): 
    ?>
        <tr editlinkajax="<?php echo $html->url(array('action' => 'index', 'task' => 'edit', 'patient_id' => $patient_demographic['PatientDemographic']['patient_id'])); ?>">
            <td><?php echo $patient_demographic['PatientDemographic']['last_name']; ?></td>
            <td><?php echo $patient_demographic['PatientDemographic']['first_name']; ?></td>            
			<?php if(isset($this->data['location_id'])) { ?><td><?php echo $patient_demographic['PatientDemographic']['location_name']; ?></td><?php } ?>
            <td><?php echo $patient_demographic['PatientDemographic']['mrn']; ?></td>
            <?php if ($custom_pt): ?><td><?php echo $patient_demographic['PatientDemographic']['custom_patient_identifier']; ?></td><?php endif;?>            
            <td><?php echo $patient_demographic['PatientDemographic']['gender']; ?></td>
            <td><?php echo __date($global_date_format, strtotime($patient_demographic['PatientDemographic']['dob'])); ?></td>
            <td><?php echo $patient_demographic['PatientDemographic']['home_phone']; ?></td>
            <td><?php echo $patient_demographic['PatientDemographic']['cell_phone']; ?></td>
            <td><?php echo $patient_demographic['PatientDemographic']['status']; ?></td>
			<?php if(@$isProvider) { ?>
			<td class="quick-visit ignore"><?php 
                                // If patient is still in pending status, do not allow quick visit
                                if (strtolower($patient_demographic['PatientDemographic']['status']) == 'pending') {
                                        // instead, give the user a link to the patient demographic page
                                        // so the user can update the status
                                        echo $html->link($html->image('next.png'), array('controller' =>'patients', 'action' =>'index', 'task' => 'edit', 'patient_id' => $patient_demographic['PatientDemographic']['patient_id']), array('escape' => false, 'class' => 'quick_visit_link pending-patient btn')); 
                                
                                        
                                // Patient is not pending, proceed as normal
                                } else {
					if(isset($locations[$patient_demographic['PatientDemographic']['patient_id']]['ScheduleCalendar']['calendar_id'])) 				{
						echo $html->link($html->image('next.png'), array('controller' =>'encounters', 'action' =>'quick_encounter', 'patient_id' => $patient_demographic['PatientDemographic']['patient_id'],'location_id' => $locations[$patient_demographic['PatientDemographic']['patient_id']]['ScheduleCalendar']['location']), array('escape' => false, 'class' => 'quick_visit_link normal_link btn', 'status' => $patient_demographic['PatientDemographic']['status'], 'override' => 'false', 'id' => md5(microtime()))); 
					} else {
						echo $html->link($html->image('next.png'), '', array('patient_id' => $patient_demographic['PatientDemographic']['patient_id'],'style' => 'cursor:pointer;', 'id' => md5(microtime()), 'override' => 'false', 'status' => $patient_demographic['PatientDemographic']['status'],'class' => 'quick-visit quick_visit_link image_link btn no-location', 'escape'=> false ));
						echo '<span id="location_list_'.$patient_demographic['PatientDemographic']['patient_id'].'"></span>';
					}
                                }
                        
                        
				?>
			</td>			
			<?php } ?>
        </tr>
    <?php endforeach; ?>
	<?php if(empty($patient_demographics)) { ?>
		<tr><td colspan="8" align="center">No Records</td></tr>
	<?php } ?>
    </table>
</form>

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
        <?php echo $paginator->numbers(array('model' => 'PatientDemographic', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('PatientDemographic'))
            {
                echo $paginator->next('Next >>', array('model' => 'PatientDemographic', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
<div id="pending-dialog" title="Patient Pending Status">
    This patient is still in pending status. <br />
    
    <a href="" id="chart-link">Go to Patient chart</a>
    
</div>
<script type="text/javascript">
$(function(){
    var 
        $dialog = 
            $('#pending-dialog')
                .dialog({
                    modal: true,
                    autoOpen: false,
                    buttons: {
                        'Close': function(){
                            $(this).dialog('close');
                        }
                    }
                });
    
    
    $('.pending-patient').click(function(evt){
        evt.preventDefault();
        evt.stopPropagation();

        var url = $(this).attr('href');
        
        $dialog
            .find('#chart-link').attr('href', url)
            .end()
            .dialog('open');
        
    });
    
		$('.no-location')
			.click(function(evt){
				evt.preventDefault();
				var pId = $(this).attr('patient_id');
				show_locations(pId, 'location_list_'+pId);
			});
});
</script>
