<!--http://docs.google.com/viewer?pli=1 -->
<?php echo $this->FaxConnectionChecker->checkConnection(); ?>
<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
/*$tabs =  array(
	'Incoming Fax' => 'fax',
	'Outgoing Fax' => array('messaging'=> 'fax_outbox')
);

echo $this->element('tabs',array('tabs'=> $tabs));*/
?>
<div class="title_area">
    <div class="title_text">
        <?php
        echo $html->link('Incoming Fax', array('action' => 'fax', 'task' => $task, 'patient_id' => $patient_id));
        echo $html->link('Outgoing Fax', array('action' => 'fax_outbox', 'task' => $task, 'patient_id' => $patient_id), array('class' => 'active'));
        ?>
    </div>
</div>
<form id="frm_fax" method="post" action="" accept-charset="utf-8" enctype="multipart/form-data">
	<input type=hidden name="reply" id="reply" value="">
    <table cellpadding="0" cellspacing="0" class="form" width=70%>
		<tr height=35>
            <td width="150">
				<label>Fax ID:</label>
			</td>
			 <td width="150">
				<?php echo $fax['fax_id']; ?>       	
			</td>
        </tr>
        <tr height=35>
            <td width="150">
				<label>Sent:</label>
			</td>
			 <td>
				<?php echo __date("M j, Y", $fax['time']); ?>       	
			</td>
        </tr>
        <tr height=35>
            <td width="150">
            	<label>Recipient:</label>
            </td>
            <td>
            	<?php echo $fax['recipname']; ?>
            </td>
        </tr>
        <tr height=35>
            <td><label>Fax Number:</label></td>
            <td>
            	<?php echo $fax['faxno']; ?>
            </td>
        </tr>
        <tr height=35>
            <td><label>Status:</label></td>
            <td><?php echo @$fax['status'].' - '.@$fax['status_message']; ?></td>
        </tr>
	</table>
</form>
<div class="title_area"></div>

<iframe src="<?php echo $filename;?>#zoom=100&scrollbar=1&toolbar=1&navpanes=0" width="100%" style="min-width: 600px; min-height:600px;height:auto">
Download
</iframe>
