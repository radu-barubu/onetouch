 <?php
if(count($patientallergy_items) > 0)
{
	?>
    <table id="table_patient_allergy" cellpadding="0" cellspacing="0"  width="100%">
        <tr>
            <th align=left>Allergies</th>
        </tr>
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
					<tr>
						<th>Agent</th><th>Type</th><th>Reactions</th>
					</tr>
						
					<?php
					foreach ($patientallergy_items as $patientallergy)
					{
						extract($patientallergy['PatientAllergy']);
						?>
									<tr>
										<td width="30%"><?php echo $agent; ?></td>												
										<td width="150px"><?php echo $type; ?></td>
										<td><?php echo $reaction1; ?></td>
									</tr>                      
						<?php
					}
					?>
				</table>
			</td>
		</tr>
    </table>
	<div class="paging paging_allergies">
	<?php echo $paginator->counter(array('model' => 'PatientAllergy', 'format' => __('Display %start%-%end% of %count%', true))); ?>
    <?php
			if($paginator->hasPrev('PatientAllergy') || $paginator->hasNext('PatientAllergy'))
			{
				echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
			}
		?>
    <?php 
			if($paginator->hasPrev('PatientAllergy'))
			{
				echo $paginator->prev('<< Previous', array('model' => 'PatientAllergy', 'url' => array('controller'=>'encounters', 'action'=>'load_allergies')), null, array('class'=>'disabled')); 
			}
	?>
    <?php echo $paginator->numbers(array('model' => 'PatientAllergy', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
    <?php 
			if($paginator->hasNext('PatientAllergy'))
			{
				echo $paginator->next('Next >>', array('model' => 'PatientAllergy', 'url' => array('controller'=>'encounters', 'action'=>'load_allergies')), null, array('class'=>'disabled')); 
			}
		?>
	</div>
<script type="text/javascript">
$(document).ready(function() {
    $('.paging_allergies a').click(function(){
      var thisHref = $(this).attr("href").replace('summary','load_allergies')+'/patient_id:'+<?php echo $patient_id;?>;
      $.get(thisHref,function(response) {
        $('#div_allergies').html(response);
				$('.small_table tr:nth-child(odd)').addClass("striped");
				if(typeof($ipad)==='object')$ipad.ready();
      }); 
      return false;
    });
}); 
</script>
<?php } ?>