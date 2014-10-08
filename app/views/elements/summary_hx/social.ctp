<?php

?>
			<div id='div_hx_social'>
            <table id="table_hx_social" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>Social History</th>
                </tr>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th>Type</th>
								<th>Routine</th>
								<th>Substance</th>
								<th>Comment</th>
								<th>Status</th>
							</tr>
							<?php foreach($hx_social as $h): ?> 
							<tr>
								 <td><?php echo Sanitize::html($h['PatientSocialHistory']['type']); ?></td>
								 <td><?php echo Sanitize::html($h['PatientSocialHistory']['routine']); ?></td>
								 <td><?php echo Sanitize::html($h['PatientSocialHistory']['substance']); ?></td>
								 <td><?php echo Sanitize::html($h['PatientSocialHistory']['comment']); ?></td>  
								 <td>
									 <?php 
									 
										switch ($h['PatientSocialHistory']['type']) {
											
											case 'Marital Status':
												echo Sanitize::html($h['PatientSocialHistory']['marital_status']);
												break;

											case 'Occupation':
												echo Sanitize::html($h['PatientSocialHistory']['occupation']);
												break;
											
											case 'Living Arrangement':
												echo Sanitize::html($h['PatientSocialHistory']['living_arrangement']);
												break;
											
											case 'Activities':
												echo Sanitize::html($h['PatientSocialHistory']['routine_status']);
												break;

											case 'Pets':
												$petsline = str_replace("|", ", ", $h['PatientSocialHistory']['pets'] );
												echo Sanitize::html(str_replace(", ", " ", $petsline));
												break;
											
											default:
												echo ($h['PatientSocialHistory']['consumption_status']!="") ? Sanitize::html($h['PatientSocialHistory']['consumption_status']) : Sanitize::html($h['PatientSocialHistory']['smoking_status']);								 												
												break;
										}
									 ?> &nbsp;
								 </td>
							</tr>
							<?php endforeach;?> 
							<?php if(empty($hx_social)): ?> 
							<tr>
								<td colspan="5">None</td>
							</tr>
							<?php endif;?> 
							
						</table>
					</td>
				</tr>
            </table>
        	<div class="paging paging_hx_social">
			<?php echo $paginator->counter(array('model' => 'PatientSocialHistory', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('PatientSocialHistory') || $paginator->hasNext('PatientSocialHistory'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('PatientSocialHistory'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PatientSocialHistory', 'url' => array('controller'=>'encounters', 'action'=>'load_hx', 'hx_type' => 'social')), null, array('class'=>'disabled')); 
					}
			?>
            <?php echo $paginator->numbers(array('model' => 'PatientSocialHistory', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('PatientSocialHistory'))
					{
						echo $paginator->next('Next >>', array('model' => 'PatientSocialHistory', 'url' => array('controller'=>'encounters', 'action'=>'load_hx', 'hx_type' => 'social')), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		</div>
<script type="text/javascript">
	$(function(){
		
	$('.paging_hx_social a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_hx')+'/hx_type:social/patient_id:'+<?php echo $patient_id;?>;
		
		$.get(thisHref,function(response) {
			$('#div_hx_social').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});		
		
	});
</script>