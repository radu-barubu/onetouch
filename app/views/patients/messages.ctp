<?php 

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
if($encounter_id)
{
	$AddURL = $html->link('Add New', array('controller' => 'messaging', 'action' => 'inbox_outbox', 'task' => 'addnew', 'patient_id' => $patient_id, 'send_to' => $patient_user_id, 'encounter_id' => $encounter_id, 'redirect_to' => 'patient_charts'), array('escape' => false, 'class' => 'btn'));
}
else
{
	$AddURL = $html->link('Add New', array('controller' => 'messaging', 'action' => 'inbox_outbox', 'task' => 'addnew', 'patient_id' => $patient_id, 'send_to' => $patient_user_id, 'redirect_to' => 'patient_charts'), array('escape' => false, 'class' => 'btn'));
}
?>
<script language="javascript" type="text/javascript">
$(document).ready(function()
{
	initCurrentTabEvents('patient_message_area');
});
</script>
<div id="patient_message_area" class="tab_area">
<?php
if($task)
{
	extract($EditItem['MessagingMessage']);
	?>
	<div style="overflow: hidden;">
	<table cellpadding="0" cellspacing="0" class="form" width=70%>
	<tr>
		<td colspan="2"><table cellpadding="0" cellspacing="0" class="form" width=100%>
				<tr height=35>
					<td width="150"><label>Sent:</label></td>
					<td><?php echo __date("$global_date_format, g:i:s A", strtotime($created_timestamp)); ?></td>
					<td align=right><table cellpadding="0" cellspacing="0" class="form">
							<tr>
								<td width="150"><label>Status:</label></td>
								<td><?php echo $status ?></td>
							</tr>
						</table></td>
				</tr>
			</table></td>
	</tr>
	<tr height=35>
		<td width="150"><label>From:</label></td>
		<td><?php echo $EditItem['Sender']['firstname']." ".$EditItem['Sender']['lastname']; ?></td>
	</tr>
	<tr height=35>
		<td><label>To:</label></td>
		<td><?php echo $EditItem['Recipient']['firstname']." ".$EditItem['Recipient']['lastname']; ?></td>
	</tr>
	<tr height=35>
		<td><label>Type:</label></td>
		<td><?php echo $type ?></td>
	</tr>
	<tr height=35>
		<td><label>Patient</label></td>
		<td><?php extract($EditItem['Patient']); echo $first_name." ".$last_name; ?></td>
	</tr>
	<tr height=35>
		<td><label>Priority:</label></td>
		<td><?php echo $priority; ?></td>
	</tr>
	<tr height=35>
		<td><label>Subject:</label></td>
		<td><?php echo $subject ?></td>
	</tr>
	<?php
			if ($attachment)
			{
				?>
	<tr height=35>
		<td><label>Attachment:</label></td>
		<td><?php echo $html->link("$attachment", array('controller' => 'messaging', 'action' => 'inbox_outbox', 'task' => 'download_file', 'message_id' => $message_id)); ?></td>
	</tr>
	<?php
			}
			?>
	</table>
	<div class="title_area"></div>
	<table cellpadding="0" cellspacing="0" class="form" width=70%>
	<tr>
		<td><table width=100% cellpadding="3" cellspacing="3" class="form">
				<tr>
					<td>
			<?php 
			// you guys can perfect this later - Robert
			$message3="";
			$message2 = explode("\n", $message);
			foreach($message2 as $vl) {
			  if(strstr($vl, '&gt;&gt;')) 
			   $message3 .= '<font color=#A8A8A8 > '.$vl.'</font>';
			  else if(strstr($vl, '&gt; ')) 
			   $message3 .= '<font color=#707070> '.$vl.'</font>';				   
			  else
			   $message3 .= '<b>'.$vl.'</b>'; 
			}
			
			echo html_entity_decode(nl2br($message3)); 
			
			
			?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
    </table>
	</div>
	<div class="actions">
		<ul>
			<li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
		</ul>
	</div>
	<?php
}
else
{
?>
    <form id="frm" method="post" action=<?php echo $thisURL.'/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" class="listing">
		<tr>
		    <th width="15">
                <label for='master_chk' class='label_check_box'>
                <input type="checkbox" id="master_chk" class="master_chk" />
                </label>
                </th>
			<th width="175"><?php echo $paginator->sort('From', 'Sender.firstname', array('model' => 'MessagingMessage', 'class' => 'ajax'));?></th>
			<th width="215"><?php echo $paginator->sort('Type', 'type', array('model' => 'MessagingMessage', 'class' => 'ajax'));?></th>
			<th width="175"><?php echo $paginator->sort('Patient', 'Patient.first_name', array('model' => 'MessagingMessage', 'class' => 'ajax'));?></th>
			<th width="175"><?php echo $paginator->sort('Priority', 'priority', array('model' => 'MessagingMessage', 'class' => 'ajax'));?></th>
			<th width="175"><?php echo $paginator->sort('Status', 'status', array('model' => 'MessagingMessage', 'class' => 'ajax'));?></th>
			<th width="175"><?php echo $paginator->sort('Time', 'created_timestamp', array('model' => 'MessagingMessage', 'class' => 'ajax'));?></th>
		</tr>
		<?php
		$i = 0;
		foreach ($MessagingMessages as $MessagingMessage):
		?>
		<tr editlinkajax="<?php echo $html->url(array('action' => 'messages', 'task' => 'view', 'patient_id' => $patient_id, 'message_id' => $MessagingMessage['MessagingMessage']['message_id']), array('escape' => false)); ?>">
		<td class="ignore">
                <label for='child_chk<?php echo $MessagingMessage['MessagingMessage']['message_id']; ?>' class='label_check_box'>
                <input name="data[MessagingMessage][message_id][<?php echo $MessagingMessage['MessagingMessage']['message_id']; ?>]" id='child_chk<?php echo $MessagingMessage['MessagingMessage']['message_id']; ?>' type="checkbox" class="child_chk" value="<?php echo $MessagingMessage['MessagingMessage']['message_id']; ?>" /></label></td>
			<td width="175"><?php echo $MessagingMessage['Sender']['firstname']." ".$MessagingMessage['Sender']['lastname']; ?></td>
			<td width="215"><?php echo $MessagingMessage['MessagingMessage']['type']; ?></td>
			<td width="175"><?php echo $MessagingMessage['Patient']['first_name']." ".$MessagingMessage['Patient']['last_name']; ?></td>
			<td width="175"><?php echo $MessagingMessage['MessagingMessage']['priority']; ?></td>
			<td width="175"><?php echo $MessagingMessage['MessagingMessage']['status']; ?></td>
			<td width="175"><?php echo __date("$global_date_format, g:i:s A", strtotime($MessagingMessage['MessagingMessage']['created_timestamp'])); ?></td>
		</tr>
		<?php endforeach; ?>
	</table></form>
        <div class="paging"> 
		<?php echo $paginator->counter(array('model' => 'MessagingMessage', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('MessagingMessage') || $paginator->hasNext('MessagingMessage'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('MessagingMessage'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'MessagingMessage', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
					}
				?>
            <?php echo $paginator->numbers(array('model' => 'MessagingMessage', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('MessagingMessage'))
					{
						echo $paginator->next('Next >>', array('model' => 'MessagingMessage', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
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
