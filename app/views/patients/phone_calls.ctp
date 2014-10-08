<?php 

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if($encounter_id)
{
	$AddURL = $html->link('Add New', array('controller' => 'messaging', 'action' => 'phone_calls', 'task' => 'addnew', 'patient_id' => $patient_id, 'encounter_id' => $encounter_id), array('escape' => false, 'class' => 'btn'));
}
else
{
	$AddURL = $html->link('Add New', array('controller' => 'messaging', 'action' => 'phone_calls', 'task' => 'addnew', 'patient_id' => $patient_id), array('escape' => false, 'class' => 'btn'));
}
?>
<script language="javascript" type="text/javascript">

$(document).ready(function()
{
	initCurrentTabEvents('patient_phone_call_area');
});
</script>
<div id="patient_phone_call_area" class="tab_area">
<?php
if($task)
{
	extract($EditItem['MessagingPhoneCall']);
	extract($EditItem['Patient']);
    $patient_id = $EditItem['MessagingPhoneCall']['patient_id'];
    $location_id = $EditItem['MessagingPhoneCall']['location_id'];
     $documented_by= $EditItem['MessagingPhoneCall']['documented_by'];
	switch($call)
	{
		case "Home": $call = "Home Phone"; $phone = $home_phone; break;
		case "Work": $call = "Work Phone"; $phone = $work_phone; break;
		case "Mobile": $call = "Mobile Phone"; $phone = $cell_phone; break;
		default: $call = ""; $phone = ""; break;
	}
	?>
	<div style="overflow: hidden;">
	<table cellpadding="0" cellspacing="0" class="form" width=70%>
	<tr height=35>
		<td width="150"><label>Documented By:</label></td>
		<td><?php echo $documented_by; ?></td>
	</tr>
	<tr height=35>
		<td width="150"><label>Patient:</label></td>
		<td><?php echo $first_name." ".$last_name; ?></td>
	</tr>
	<tr height=35>
		<td><label>Call Phone:</label></td>
		<td><?php echo $call." ".$phone; ?></td>
	</tr>
	<tr height=35>
		<td><label>Date & Time:</label></td>
		<td><?php echo __date("$global_date_format, g:i:s A", strtotime($date." ".$time)); ?></td>
	</tr>
	<tr height=35>
		<td><label>Type:</label></td>
		<td><?php echo $type; ?></td>
	</tr>
	<tr height=35>
		<td><label>Caller:</label></td>
		<td><?php echo $caller_receiver; ?></td>
	</tr>
	<tr height=35>
		<td><label>Comment:</label></td>
		<td><?php echo $comment; ?></td>
	</tr>
	<tr height=35>
		<td><label>Status:</label></td>
		<td><?php echo $status; ?></td>
	</tr>
    </table>
	</div>
	<div class="actions">
		<ul>
			<li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
            <li><a href="<?php echo $this->Session->webroot.'encounters/phone_encounter/patient_id:'.$patient_id.'/location_id:'.$location_id; ?>">Telephone Encounter</a></li>

		</ul>
	</div>
	<?php
}
else
{
?>
    <form id="frm" method="post" action="<?php echo $thisURL.'/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" class="listing">
		<tr>
		<th width="15">
                <label for='master_chk' class='label_check_box'>
                <input type="checkbox" id="master_chk" class="master_chk" />
                </label>
                </th>
       <!--<th><?php echo $paginator->sort('Patient', 'Patient.first_name', array('model' => 'MessagingPhoneCall', 'class' => 'ajax'));?></th>-->
            <th width="12%"><?php echo $paginator->sort('Date', 'date', array('model' => 'MessagingPhoneCall', 'class' => 'ajax'));?></th>
            <th width="12%"><?php echo $paginator->sort('Type', 'type', array('model' => 'MessagingPhoneCall', 'class' => 'ajax'));?></th>
            <th width="12%"><?php echo $paginator->sort('Caller', 'caller_receiver', array('model' => 'MessagingPhoneCall', 'class' => 'ajax'));?></th>
            <th width="51%"><?php echo $paginator->sort('Comment', 'comment', array('model' => 'MessagingPhoneCall', 'class' => 'ajax'));?></th>
            <th width="12%"><?php echo $paginator->sort('Status', 'status', array('model' => 'MessagingPhoneCall', 'class' => 'ajax'));?></th>

		</tr>
		<?php
		$i = 0;
		foreach ($MessagingPhoneCalls as $MessagingPhoneCall):
		?>
		<tr editlinkajax="<?php echo $html->url(array('action' => 'phone_calls', 'task' => 'view', 'patient_id' => $patient_id, 'phone_call_id' => $MessagingPhoneCall['MessagingPhoneCall']['phone_call_id']), array('escape' => false)); ?>">
		<td class="ignore">
                    <label for='child_chk<?php echo $MessagingPhoneCall['MessagingPhoneCall']['phone_call_id']; ?>' class='label_check_box'>
                    <input name="data[MessagingPhoneCall][phone_call_id][<?php echo $MessagingPhoneCall['MessagingPhoneCall']['phone_call_id']; ?>]" id='child_chk<?php echo $MessagingPhoneCall['MessagingPhoneCall']['phone_call_id']; ?>' type="checkbox" class="child_chk" value="<?php echo $MessagingPhoneCall['MessagingPhoneCall']['phone_call_id']; ?>" />
                    </label>
                    </td>
			    <!--<td><?php echo $MessagingPhoneCall['Patient']['first_name']." ".$MessagingPhoneCall['Patient']['last_name']; ?></td>-->
                    <td><?php echo __date($global_date_format, strtotime($MessagingPhoneCall['MessagingPhoneCall']['date'])); ?></td>
                    <td><?php echo $MessagingPhoneCall['MessagingPhoneCall']['type']; ?></td>
                    <td><?php echo $MessagingPhoneCall['MessagingPhoneCall']['caller_receiver']; ?></td>
                    <td><?php echo $MessagingPhoneCall['MessagingPhoneCall']['comment']; ?></td>
                    <td><?php echo $MessagingPhoneCall['MessagingPhoneCall']['status']; ?></td>

		</tr>
		<?php endforeach; ?>
	</table></form>
	
		<div class="paging">
			<?php echo $paginator->counter(array('model' => 'MessagingPhoneCall', 'format' => __('Display %start%-%end% of %count%', true))); ?>
			<?php
				if($paginator->hasPrev('MessagingPhoneCall') || $paginator->hasNext('MessagingPhoneCall'))
				{
					echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
				}
			?>
			<?php 
				if($paginator->hasPrev('MessagingPhoneCall'))
				{
					echo $paginator->prev('<< Previous', array('model' => 'MessagingPhoneCall', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
				}
			?>
			<?php echo $paginator->numbers(array('model' => 'MessagingPhoneCall', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
			<?php 
				if($paginator->hasNext('MessagingPhoneCall'))
				{
					echo $paginator->next('Next >>', array('model' => 'MessagingPhoneCall', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
				}
			?>
		</div>
    <?php if($this->QuickAcl->getAccessType("messaging", "phone_calls") != 'NA'): ?>
	<div class="actions">
		<ul>
			<li><?php echo $AddURL; ?></li>
			<li><a href="javascript:void(0);" onclick="deleteData('frm', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
		</ul>
	</div>
    <?php endif; ?>
<?php
}
?>
</div>
