<?php
if(count($patientmedication_items) > 0)
{
	?>
    <table id="table_patient_medication" cellpadding="0" cellspacing="0"  width="100%">
    <tr>
        <th align=left>Medications</th>
    </tr>
	<tr>
		<td>
			<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
					 	<tr>
						 	<th width="30%"><?php echo $paginator->sort('Medication', 'medication', array('model' => 'PatientMedicationList','class' => 'sort_meds','url'=> array('controller'=>'encounters','action'=>'load_medication_list')));?></th>
                                                        <th>Dosing</th>
                                                        <th width="10%;"><?php echo $paginator->sort('Start Date', 'start_date', array('model' => 'PatientMedicationList','class' => 'sort_meds','url'=> array('controller'=>'encounters','action'=>'load_medication_list')));?></th>
                                                        <th width="10%;"><?php echo $paginator->sort('End Date', 'end_date', array('model' => 'PatientMedicationList','class' => 'sort_meds','url'=> array('controller'=>'encounters','action'=>'load_medication_list')));?></th>
                                                        <th width="18%;"><?php echo $paginator->sort('Source', 'source', array('model' => 'PatientMedicationList','class' => 'sort_meds','url'=> array('controller'=>'encounters','action'=>'load_medication_list')));?></th>
                                                        <th width="10%;"><?php echo $paginator->sort('Status', 'status', array('model' => 'PatientMedicationList','class' => 'sort_meds','url'=> array('controller'=>'encounters','action'=>'load_medication_list')));?></th>
						</tr>
					
				<?php
				foreach ($patientmedication_items as $patientmedication)
				{
					extract($patientmedication['PatientMedicationList']);
					?>
										<tr class="medication-item clickable" id="medlist-<?php echo $medication_list_id;?>">
											<td><?php echo $medication; ?></td>												
                                                                                        <td><?php 
                                                                                            $dosing = trim($quantity .' ' . $unit . ' ' . $route . ' ' . $frequency);
                                                                                            
                                                                                            if ($dosing == '0') {
                                                                                                $dosing = 'Not specified';
                                                                                            } else {
                                                                                                $dosing = htmlentities($dosing);
                                                                                            }
                                                                                            
                                                                                            echo $dosing; ?></td>
                                                                                        <td><?php echo __date($global_date_format,strtotime($start_date))?></td>
                                                                                        <td><?php echo __date($global_date_format,strtotime($end_date))?></td>
                                                                                        <td><?php echo $source?></td>    
                                                                                        <td><?php echo $status?></td>
										</tr>                      
					<?php
				}
				?>
			</table>
			<div class="paging paging_meds"> <?php echo $paginator->counter(array('model' => 'PatientMedicationList', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('PatientMedicationList') || $paginator->hasNext('PatientMedicationList'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('PatientMedicationList'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PatientMedicationList', 'url' => array('controller'=>'encounters', 'action'=>'load_medication_list')), null, array('class'=>'disabled')); 
					}
			?>
            <?php echo $paginator->numbers(array('model' => 'PatientMedicationList', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('PatientMedicationList'))
					{
						echo $paginator->next('Next >>', array('model' => 'PatientMedicationList', 'url' => array('controller'=>'encounters', 'action'=>'load_medication_list')), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		</td>
	</tr>
    </table>
   
<script type="text/javascript">
$(document).ready(function() {
    // The div where <?php echo $paginator->numbers(); ?> is located
    
	$('.sort_meds').click(function(){
		var thisHref = $(this).attr("href");
			// The content div
			$.get(thisHref,function(response) {
				$('#div_meds').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
			});
			return false;
    });
    
    $('.paging_meds a').click(function(){
			var thisHref = $(this).attr("href");
			$.get(thisHref,function(response){
				$('#div_meds').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
			});
			return false;
    });

    $('.medication-item').each(function(){

        $(this).click(function(evt){
            evt.preventDefault();
            var 
                medListId = $(this).attr('id').split('-').pop(),
                medInfoLink = '<?php echo $this->Html->url(array(
                'controller' => 'patients',
                'action' => 'index',
                'task' => 'edit',
                'patient_id' => $patient_id,
                'view' => 'medical_information',
                'view_medications' => 1,
                'medication_list_id' => '***',
            )); ?>';

            window.location.href = medInfoLink.replace('***', medListId) ;

        });

    });    
    
}); 
</script>
<?php } ?>
