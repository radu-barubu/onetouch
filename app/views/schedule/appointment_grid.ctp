<?php
$patient_mode = (isset($this->params['named']['patient_mode'])) ? $this->params['named']['patient_mode'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
?>

<?php if($patient_mode == 1): ?>
<script language="javascript" type="text/javascript">
	$(document).ready(function()
	{
		initCurrentTabEvents('appointment_area');
		
		$('#appointment_area').delegate('tr.clickable', 'click', function(evt){
            var appointment_id = $(this).attr('rel');
            
            window.location.href = '<?php echo $this->Html->url(array('controller' => 'schedule', 'action' => 'index')); ?>/index/appointment:' + appointment_id;
        })
	});
</script>
<?php endif; ?>
<?php if( $patient_id ){?>
	<span style="float:right; margin-bottom:8px;" >
        <ul><li><a href="<?php echo $html->url(array('controller'=>'schedule','action' => 'index', 'task' => 'addnew', 'patient_id' => $patient_id)); ?>" class="btn">Add New</a></li></ul>
    </span>
<?php }?>	

<div id="appointment_area" class="tab_area">
    <table id="appointment_table" cellpadding="0" cellspacing="0" class="listing">
        <tr>
            <th width="10%" nowrap="nowrap"><?php echo $paginator->sort('Date', 'ScheduleCalendar.date', array('class' => 'ajax'));?></th>
            <th width="10%" nowrap="nowrap"><?php echo $paginator->sort('Time', 'ScheduleCalendar.starttime', array('class' => 'ajax'));?></th>
            <th nowrap="nowrap"><?php echo $paginator->sort('Patient', 'ScheduleCalendar.patient_full_name', array('class' => 'ajax'));?></th>
            <th nowrap="nowrap"><?php echo $paginator->sort('Reason', 'ScheduleCalendar.reason_for_visit', array('class' => 'ajax'));?></th>            
            <th nowrap="nowrap"><?php echo $paginator->sort('Type', 'ScheduleType.type', array('class' => 'ajax'));?></th>
            <th nowrap="nowrap"><?php echo $paginator->sort('Provider', 'ScheduleCalendar.provider_full_name', array('class' => 'ajax'));?></th>
            <th nowrap="nowrap"><?php echo $paginator->sort('Room', 'ScheduleRoom.room', array('class' => 'ajax'));?></th>            
            <th nowrap="nowrap"><?php echo $paginator->sort('Status', 'ScheduleStatus.status', array('class' => 'ajax'));?></th>
        </tr>
        <?php
        $i = 0;
    
        foreach ($ScheduleCalendar as $schedule):
            $class = null;
            if ($i++ % 2 == 0) {
                $class = 'altrow';
            } 
       ?>
            <tr class="<?php echo $class;?> clickable" rel="<?php echo $schedule['ScheduleCalendar']['calendar_id'] ?>" style="height:auto;">
                <td class="ignore" ><?php echo __date($global_date_format, strtotime($schedule['ScheduleCalendar']['date'])); ?></td>
                <td class="ignore" ><?php echo __date($global_time_format, strtotime($schedule['ScheduleCalendar']['starttime'])); ?></td>
                <td class="ignore" ><span itemid="<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" patientid="<?php echo $schedule['ScheduleCalendar']['patient_id']; ?>" scheduleid="<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" class="hasDetails"><?php echo $schedule['ScheduleCalendar']['patient_firstname']." ".$schedule['ScheduleCalendar']['patient_lastname']. " (" . __date($global_date_format, strtotime($schedule['ScheduleCalendar']['patient_dob'])).")"; ?></span></td>
                <td class="ignore" width="200" style="word-wrap:break-word"><div style="width:180px; overflow:auto;"><?php echo $schedule['ScheduleCalendar']['reason_for_visit']; ?></div></td>                
                <td class="ignore" ><?php echo $schedule['ScheduleType']['type']; ?></td>
                <td class="ignore" ><?php echo $schedule['ScheduleCalendar']['provider_full_name'];?></td>                
                <td class="ignore" ><span itemid="<?php echo $schedule['ScheduleCalendar']['calendar_id']; ?>" class="updateable"><?php echo $schedule['ScheduleRoom']['room']; ?></span></td>                
                <td class="ignore" ><?php echo $schedule['ScheduleStatus']['status']; ?></td>
            </tr>
        <?php endforeach; 
				
			  if(empty($ScheduleCalendar)) {
		?>
			<tr>
				<td class="ignore" colspan="8" align="center">No Appointments</td>
			</tr>
		<?php } ?>
    </table>
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'ScheduleCalendar', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('ScheduleCalendar') || $paginator->hasNext('ScheduleCalendar'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('ScheduleCalendar'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'ScheduleCalendar', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'ScheduleCalendar', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('ScheduleCalendar'))
                {
                    echo $paginator->next('Next >>', array('model' => 'ScheduleCalendar', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
    </div>
</div>
