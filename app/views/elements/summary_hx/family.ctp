<?php

?>
			<div id='div_hx_family'>
            <table id="table_hx_family" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>Family History</th>
                </tr>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th>Name</th>
								<th>Relationship</th>
								<th>Problem</th>
								<th>Comment</th>
								<th>Status</th>
							</tr>
							<?php foreach($hx_family as $h): ?> 
							<tr>
								<td><?php echo Sanitize::html($h['PatientFamilyHistory']['name']); ?></td>
								<td><?php echo Sanitize::html($h['PatientFamilyHistory']['relationship']); ?></td>
								<td><?php echo Sanitize::html($h['PatientFamilyHistory']['problem']); ?></td>
								<td><?php echo Sanitize::html($h['PatientFamilyHistory']['comment']); ?></td>  
								<td><?php echo Sanitize::html($h['PatientFamilyHistory']['status']); ?></td> 
							</tr>
							<?php endforeach;?> 
							<?php if(empty($hx_family)): ?> 
							<tr>
								<td colspan="5">None</td>
							</tr>
							<?php endif;?> 
							
						</table>
					</td>
				</tr>
            </table>
        	<div class="paging paging_hx_family">
			<?php echo $paginator->counter(array('model' => 'PatientFamilyHistory', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('PatientFamilyHistory') || $paginator->hasNext('PatientFamilyHistory'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('PatientFamilyHistory'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PatientFamilyHistory', 'url' => array('controller'=>'encounters', 'action'=>'load_hx', 'hx_type' => 'family')), null, array('class'=>'disabled')); 
					}
			?>
            <?php echo $paginator->numbers(array('model' => 'PatientFamilyHistory', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('PatientFamilyHistory'))
					{
						echo $paginator->next('Next >>', array('model' => 'PatientFamilyHistory', 'url' => array('controller'=>'encounters', 'action'=>'load_hx', 'hx_type' => 'family')), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		</div>
<script type="text/javascript">
	$(function(){
		
	$('.paging_hx_family a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_hx')+'/hx_type:family/patient_id:'+<?php echo $patient_id;?>;
		
		$.get(thisHref,function(response) {
			$('#div_hx_family').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});		
		
	});
</script>