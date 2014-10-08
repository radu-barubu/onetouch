<?php
$page = (isset($this->params['named']['page'])) ? $this->params['named']['page'] : "";
?>
<div id="appointment_area" class="tab_area">
	<form id="frm" method="post" action="<?php echo $html->url(array('action' => 'appointment_reminders', 'task' => 'delete')); ?>" accept-charset="utf-8" enctype="multipart/form-data">
    <table id="appointment_table" cellpadding="0" cellspacing="0" class="listing">
        <tr>
        	<th width="15">
            <label  class="label_check_box">
            <input type="checkbox" class="master_chk" />
            </label>
            </th>
            <th width="10%"><?php echo $paginator->sort('Date', 'AppointmentReminder.appointment_call_date', array('class' => 'ajax'));?></th>
            <th><?php echo $paginator->sort('Patient', 'AppointmentReminder.patient_full_name', array('class' => 'ajax'));?></th>
            <th><?php echo $paginator->sort('Subject', 'AppointmentReminder.subject', array('class' => 'ajax'));?></th>            
            <th><?php echo $paginator->sort('Type', 'AppointmentReminder.type', array('class' => 'ajax'));?></th>
            <th><?php echo $paginator->sort('Messaging', 'AppointmentReminder.messaging', array('class' => 'ajax'));?></th>
            <th><?php echo $paginator->sort('Postcard', 'AppointmentReminder.postcard', array('class' => 'ajax'));?></th>
        </tr>
        <?php
        $i = 0;
    
        foreach ($patient_reminders as $patient_reminder):
            $class = null;
            if ($i++ % 2 == 0) {
                $class = 'altrow';
            } 
       ?>
            <tr class="<?php echo $class;?> clickable" reminder_id="<?php echo $patient_reminder['AppointmentReminder']['reminder_id'] ?>">
                <td class="ignore">
                <label class="label_check_box">
                <input name="data[AppointmentReminder][reminder_id][<?php echo $patient_reminder['AppointmentReminder']['reminder_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $patient_reminder['AppointmentReminder']['reminder_id']; ?>" />
                </label>
                </td>
                <td><?php echo __date($global_date_format, strtotime($patient_reminder['AppointmentReminder']['appointment_call_date'])); ?></td>
                <td><?php echo $patient_reminder['AppointmentReminder']['patient_full_name']; ?> (<?php echo __date($global_date_format, strtotime($patient_reminder['AppointmentReminder']['patient_dob'])); ?>)</td>
                <td><?php echo $patient_reminder['AppointmentReminder']['subject']; ?></td>
                <td><?php echo $patient_reminder['AppointmentReminder']['type']; ?></td>
                <td><?php echo $patient_reminder['AppointmentReminder']['messaging'];?></td>                
                <td><?php echo $patient_reminder['AppointmentReminder']['postcard']; ?></td>
            </tr>
        <?php endforeach; 
				
		if(empty($patient_reminders)) {
		?>
			<tr>
				<td class="ignore" colspan="8" align="center">No Reminder</td>
			</tr>
		<?php } ?>
    </table>
    </form>
    <div style="width: auto; float: left;">
        <div class="actions">
            <ul>
                <li><?php echo $html->link(__('Add New', true), array('action' => 'appointment_reminders', 'task' => 'addnew')); ?></li>
                <li><a href="javascript: void(0);" onclick="deleteData();">Delete Selected</a></li>
                <li><a href="javascript:void(0);" id="print_postcards" class="btn section_btn" onclick="print_postcards('<?php echo $page; ?>')">Print Postcards</a></li>
                <li><a href="javascript: void(0);" id="export_data" onclick="exportData('<?php echo $page; ?>')">Export Data</a></li>
            </ul>
        </div>
    </div>
    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'AppointmentReminder', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('AppointmentReminder') || $paginator->hasNext('AppointmentReminder'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('AppointmentReminder'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'AppointmentReminder', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'AppointmentReminder', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('AppointmentReminder'))
            {
                echo $paginator->next('Next >>', array('model' => 'AppointmentReminder', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>
</div>