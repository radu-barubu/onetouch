<?php

?>
			<div id='div_hx_surgical'>
            <table id="table_hx_surgical" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>Surgical History</th>
                </tr>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th>Surgery</th>
								<th>Type</th>
								<th>Hospitalization</th>
								<th>From</th>
								<th>To</th>
								<th>Reason</th>
								<th>Outcome</th>
							</tr>
							<?php foreach($hx_surgical as $h): ?> 
							<tr>
								<td><?php echo Sanitize::html($h['PatientSurgicalHistory']['surgery']); ?></td>
								<td><?php echo Sanitize::html($h['PatientSurgicalHistory']['type']); ?></td>
								<td><?php echo Sanitize::html($h['PatientSurgicalHistory']['hospitalization']); ?></td>
								<td><?php echo Sanitize::html($h['PatientSurgicalHistory']['date_from']); ?></td>
								<td><?php echo Sanitize::html($h['PatientSurgicalHistory']['date_to']); ?></td>
								<td><?php echo Sanitize::html($h['PatientSurgicalHistory']['reason']); ?></td>
								<td><?php echo Sanitize::html($h['PatientSurgicalHistory']['outcome']); ?></td>
							</tr>
							<?php endforeach;?> 
							<?php if(empty($hx_surgical)): ?> 
							<tr>
								<td colspan="6">None</td>
							</tr>
							<?php endif;?> 
						</table>
					</td>
				</tr>
            </table>
        	<div class="paging paging_hx_surgical">
			<?php echo $paginator->counter(array('model' => 'PatientSurgicalHistory', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('PatientSurgicalHistory') || $paginator->hasNext('PatientSurgicalHistory'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('PatientSurgicalHistory'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PatientSurgicalHistory', 'url' => array('controller'=>'encounters', 'action'=>'load_hx', 'hx_type' => 'surgical')), null, array('class'=>'disabled')); 
					}
			?>
            <?php echo $paginator->numbers(array('model' => 'PatientSurgicalHistory', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('PatientSurgicalHistory'))
					{
						echo $paginator->next('Next >>', array('model' => 'PatientSurgicalHistory', 'url' => array('controller'=>'encounters', 'action'=>'load_hx', 'hx_type' => 'surgical')), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		</div>
<script type="text/javascript">
	$(function(){
		
	$('.paging_hx_surgical a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_hx')+'/hx_type:surgical/patient_id:'+<?php echo $patient_id;?>;
		
		$.get(thisHref,function(response) {
			$('#div_hx_surgical').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});		
		
	});
</script>