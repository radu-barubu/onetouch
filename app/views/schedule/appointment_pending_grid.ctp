<div id="appointment_area" class="tab_area">
    <table id="appointment_table" cellpadding="0" cellspacing="0" class="listing">
        <tr>
            <th width="10%" nowrap="nowrap"><?php echo $paginator->sort('Request Date', 'ScheduleAppointmentRequest.request_date', array('class' => 'ajax'));?></th>
	    <th  nowrap="nowrap"><?php echo $paginator->sort('Patient', 'ScheduleAppointmentRequest.patient_full_name', array('class' => 'ajax'));?></th>
	   <th  nowrap="nowrap"><?php echo $paginator->sort('Provider', 'ScheduleAppointmentRequest.provider_full_name', array('class' => 'ajax'));?></th>
	<th  nowrap="nowrap"><?php echo $paginator->sort('Encounter Date', 'ScheduleAppointmentRequest.encounter_date', array('class' => 'ajax'));?></th>
	    <th  nowrap="nowrap"><?php echo $paginator->sort('Appointment Request', 'ScheduleAppointmentRequest.requested_followup', array('class' => 'ajax'));?></th>
	   <th width="10%" nowrap="nowrap"><?php echo $paginator->sort('Status', 'ScheduleAppointmentRequest.status', array('class' => 'ajax'));?></th>
	<th width="10%" nowrap="nowrap"><?php echo $paginator->sort('Priority', 'ScheduleAppointmentRequest.priority', array('class' => 'ajax'));?></th>
        </tr>
        <?php
        $i = 0;
    
        foreach ($ScheduleAppointmentRequest as $scheduleReq):
            $class = null;
            if ($i++ % 2 == 0) {
                $class = 'altrow';
            } 
		$sched=$html->url(array('controller'=>'schedule','action' => 'index', 'task' => 'addnew', 'patient_id' => $scheduleReq['ScheduleAppointmentRequest']['patient_id'], 'appointment_request_id' => $scheduleReq['ScheduleAppointmentRequest']['appointment_request_id']));
       ?>
            <tr class="<?php echo $class;?> clickable" editlink="<?php echo $sched; ?>" style="height:auto;">
                <td class="ignore" ><?php echo __date($global_date_format, strtotime($scheduleReq['ScheduleAppointmentRequest']['request_date'])); ?></td>
		<td class="ignore" ><span itemid="<?php echo $scheduleReq['ScheduleAppointmentRequest']['appointment_request_id']; ?>"  class="hasDetails"><?php echo $scheduleReq['ScheduleAppointmentRequest']['patient_full_name']."  (" . __date($global_date_format, strtotime($scheduleReq['ScheduleAppointmentRequest']['patient_dob'])).")"; ?></span></td>
		<td class="ignore" ><?php echo $scheduleReq['ScheduleAppointmentRequest']['provider_full_name'];?></td>
		<td class="ignore" ><?php echo  __date($global_date_format, strtotime($scheduleReq['EncounterMaster']['encounter_date'])); ?></td> 
		<td class="ignore" ><?php echo $scheduleReq['ScheduleAppointmentRequest']['requested_followup']; ?></td>
                <td class="ignore" ><?php echo $scheduleReq['ScheduleAppointmentRequest']['status']; ?></td>
		<td class="ignore" ><?php echo $scheduleReq['ScheduleAppointmentRequest']['priority']; ?></td>
            </tr>
        <?php endforeach; 
				
			  if(empty($ScheduleAppointmentRequest)) {
		?>
			<tr>
				<td class="ignore" colspan="8" align="center">No Pending Appointments</td>
			</tr>
		<?php } ?>
    </table>
        <div class="paging">
            <?php echo $paginator->counter(array('model' => 'ScheduleAppointmentRequest', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
                if($paginator->hasPrev('ScheduleAppointmentRequest') || $paginator->hasNext('ScheduleAppointmentRequest'))
                {
                    echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                }
            ?>
            <?php 
                if($paginator->hasPrev('ScheduleAppointmentRequest'))
                {
                    echo $paginator->prev('<< Previous', array('model' => 'ScheduleAppointmentRequest', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
            <?php echo $paginator->numbers(array('model' => 'ScheduleAppointmentRequest', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
                if($paginator->hasNext('ScheduleAppointmentRequest'))
                {
                    echo $paginator->next('Next >>', array('model' => 'ScheduleAppointmentRequest', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                }
            ?>
    </div>
</div>
