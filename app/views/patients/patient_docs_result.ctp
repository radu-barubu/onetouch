<?php 
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
$lab_result_link = $html->url(array('controller' => 'encounters', 'action' => 'lab_results_electronic', 'encounter_id' => $encounter_id));
?>
<script language="javascript" type="text/javascript">
    $(document).ready(function()
    {
			initCurrentTabEvents('documents_area');
	});
</script>
	<form id="frmEncounterDocumentsGrid" method="post" accept-charset="utf-8">
		<table cellpadding="0" cellspacing="0" class="listing">
		<tr>
             <th width="16.66%"><?php echo $paginator->sort('Document Name', 'document_name', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
             <th width="16.66%"><?php echo $paginator->sort('Comment', 'description', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>             
             <th width="16.66%"><?php echo $paginator->sort('Document Type', 'document_type', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>				
             <th width="16.66%"><?php echo $paginator->sort('Service Date', 'service_date', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
             <th width="16.66%" style="text-align:center;"><?php echo $paginator->sort('Reviewed', 'document_test_reviewed', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
             <th width="16.66%" style="text-align:center"><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientDocument', 'class' => 'ajax'));?></th>
        </tr>
		<?php $g = 0;
		
		foreach ($patient_documents as $PatientDocument_record):
		?>
		<tr style="cursor:pointer;">
		<?php
		//if($PatientDocument_record['PatientDocument']['document_type']=="Lab")
		//{
		 ?>
            <td  class="<?php echo ($PatientDocument_record['PatientDocument']['attachment']!="")?'ignore':'';?>">
			<div class="link_hash" style="float: left; margin-top: 3px; cursor: pointer;"><?php echo $html->image('valid_hash.png', array('alt' => '')); ?></div>&nbsp;&nbsp;
            <?php 
            if($PatientDocument_record['PatientDocument']['attachment']!="")
            {
             echo $html->link($PatientDocument_record['PatientDocument']['document_name'], array('action' => 'encounter_documents', 'task' => 'download_file', 'document_id' => $PatientDocument_record['PatientDocument']['document_id'])); 
             echo $this->Html->image("download.png", array(    "alt" => "Download",    'url' => array('action' => 'encounter_documents', 'task' => 'download_file', 'document_id' => $PatientDocument_record['PatientDocument']['document_id']) ));
             }
             else
             {
                  echo $PatientDocument_record['PatientDocument']['document_name'];
             }
			 ?>
             </td>
             <td><?php echo $PatientDocument_record['PatientDocument']['description'];?></td>
			 <td><?php echo $PatientDocument_record['PatientDocument']['document_type']; ?></td>
			 <td><?php echo __date($global_date_format, strtotime($PatientDocument_record['PatientDocument']['service_date'])); ?></td>
			 
			 <td style="text-align:center" class="ignore"><label for="reviewed<?php echo $g;?>" class="label_check_box_hx"><input type="checkbox" value="<?php echo $PatientDocument_record['PatientDocument']['document_id']; ?>" id="reviewed<?php echo $g;?>" onclick="update_reviewed(this);" <?php if($PatientDocument_record['PatientDocument']['document_test_reviewed']) { echo 'checked="checked"'; $display= 'display:block'; } else { $display= 'display:none'; } ?>  /></label><br />
			
			 <textarea placeholder="Comment (Optional)" onblur="update_comment(this)" class="text_comment"  style="<?php echo $display; ?>; margin-top:5px;" id="<?php echo $PatientDocument_record['PatientDocument']['document_id']; ?>"><?php if(!empty($PatientDocument_record['PatientDocument']['comment'])) echo $PatientDocument_record['PatientDocument']['comment']; ?></textarea>
			
			  </td>
             <td style="text-align:center"><?php echo $PatientDocument_record['PatientDocument']['status']; ?></td>
		<?php 
		//}
	 ?>
	</tr>
	<?php  
	$g++; 
	endforeach; ?>
       </table>
		</form>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientDocument', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientDocument') || $paginator->hasNext('PatientDocument'))
                    {
                        echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientDocument'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientDocument', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientDocument', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientDocument', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>
