<?php

?>
			<div id='div_hx_medical'>
            <table id="table_hx_medical" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>Medical History</th>
                </tr>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th>Diagnosis</th>
								<th>Start Date</th>
								<th>End Date</th>
								<th>Occurrence</th>
								<th>Comment</th>
								<th>Status</th>
							</tr>
							<?php foreach($hx_medical as $h): ?> 
							<tr>
								<td><?php echo Sanitize::html($h['PatientMedicalHistory']['diagnosis']); ?></td>
								<td>
									<?php 
										$ts = strtotime($h['PatientMedicalHistory']['start_year'].'-'.$h['PatientMedicalHistory']['start_month'].'-01');
										if ($ts) {
											echo __date('F Y', $ts);
										}
									?> &nbsp;
								</td>
								<td>
									<?php 
										$ts = strtotime($h['PatientMedicalHistory']['end_year'].'-'.$h['PatientMedicalHistory']['end_month'].'-01');
										if ($ts) {
											echo __date('F Y', $ts);
										}
									?> &nbsp;
								</td>
								<td>
									<?php echo Sanitize::html($h['PatientMedicalHistory']['occurrence']); ?>
									&nbsp;
								</td>
								<td>
									<?php echo Sanitize::html($h['PatientMedicalHistory']['comment']); ?>
									&nbsp;
								</td>
								<td>
									<?php echo Sanitize::html($h['PatientMedicalHistory']['status']); ?>
									&nbsp;
								</td>
							</tr>
							<?php endforeach;?> 
							<?php if(empty($hx_medical)): ?> 
							<tr>
								<td colspan="6">None</td>
							</tr>
							<?php endif;?> 
							
						</table>
					</td>
				</tr>
            </table>
        	<div class="paging paging_hx_medical">
			<?php echo $paginator->counter(array('model' => 'PatientMedicalHistory', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('PatientMedicalHistory') || $paginator->hasNext('PatientMedicalHistory'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('PatientMedicalHistory'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PatientMedicalHistory', 'url' => array('controller'=>'encounters', 'action'=>'load_hx', 'hx_type' => 'medical')), null, array('class'=>'disabled')); 
					}
			?>
            <?php echo $paginator->numbers(array('model' => 'PatientMedicalHistory', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('PatientMedicalHistory'))
					{
						echo $paginator->next('Next >>', array('model' => 'PatientMedicalHistory', 'url' => array('controller'=>'encounters', 'action'=>'load_hx', 'hx_type' => 'medical')), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		</div>
<script type="text/javascript">
	$(function(){
		
	$('.paging_hx_medical a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_hx')+'/hx_type:medical/patient_id:'+<?php echo $patient_id;?>;
		
		$.get(thisHref,function(response) {
			$('#div_hx_medical').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});		
		
	});
</script>