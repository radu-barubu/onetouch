<?php

?>
			<div id='div_hx_obgyn'>
            <table id="table_hx_obgyn" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>ObGyn History</th>
                </tr>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th>Type</th>
							</tr>
							<?php foreach($hx_obgyn as $h): ?> 
							<tr>
								<td><?php echo Sanitize::html($h['PatientObGynHistory']['type']); ?></td>
							</tr>
							<?php endforeach;?> 
							<?php if(empty($hx_obgyn)): ?> 
							<tr>
								<td>None</td>
							</tr>
							<?php endif;?> 
							
						</table>
					</td>
				</tr>
            </table>
        	<div class="paging paging_hx_obgyn">
			<?php echo $paginator->counter(array('model' => 'PatientObGynHistory', 'format' => __('Display %start%-%end% of %count%', true))); ?>
            <?php
					if($paginator->hasPrev('PatientObGynHistory') || $paginator->hasNext('PatientObGynHistory'))
					{
						echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
					}
				?>
            <?php 
					if($paginator->hasPrev('PatientObGynHistory'))
					{
						echo $paginator->prev('<< Previous', array('model' => 'PatientObGynHistory', 'url' => array('controller'=>'encounters', 'action'=>'load_hx', 'hx_type' => 'obgyn')), null, array('class'=>'disabled')); 
					}
			?>
            <?php echo $paginator->numbers(array('model' => 'PatientObGynHistory', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('PatientObGynHistory'))
					{
						echo $paginator->next('Next >>', array('model' => 'PatientObGynHistory', 'url' => array('controller'=>'encounters', 'action'=>'load_hx', 'hx_type' => 'obgyn')), null, array('class'=>'disabled')); 
					}
				?>
			</div>
		</div>
<script type="text/javascript">
	$(function(){
		
	$('.paging_hx_obgyn a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_hx')+'/hx_type:obgyn/patient_id:'+<?php echo $patient_id;?>;
		
		$.get(thisHref,function(response) {
			$('#div_hx_obgyn').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});		
		
	});
</script>