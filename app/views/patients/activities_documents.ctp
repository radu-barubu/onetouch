<table cellpadding="0" cellspacing="0" class="listing" border=1>
<tr>
	<th width="34%">Document Name</th>
	<th width="33%">Document Type</th>				
	<th width="15%">Service Date</th>
	<th width="15%">Status</th>
</tr>
<?php
$i = 0;
foreach ($patient_documents as $patient_document):
?>
	<tr >              
		<td class="<?php echo ($patient_document['PatientDocument']['attachment']!="")?'ignore':'';?>">
		<?php 
		if($patient_document['PatientDocument']['attachment']!="")
		{
		echo $html->link($patient_document['PatientDocument']['document_name'], array('action' => 'documents', 'task' => 'download_file', 'document_id' => $patient_document['PatientDocument']['document_id'])); 
		}
		else
		{
			echo $patient_document['PatientDocument']['document_name'];
		}
		?>
		</td>
		<td><?php echo $patient_document['PatientDocument']['document_type']; ?></td>					
		<td><?php echo __date($global_date_format, strtotime($patient_document['PatientDocument']['service_date'])); ?></td>
		<td><?php echo $patient_document['PatientDocument']['status']; ?></td>
	</tr>
<?php endforeach; ?>
</table>
