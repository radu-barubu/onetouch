        <?php
		//if(count($patientproblem_items) > 0)
		//{
			?>
            <table id="table_patient_problem" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>Problem List</th>
                </tr>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" class="small_table paging_problem_list" style="width: 100%;">
							<tr>
								<th width="500"><?php echo $paginator->sort('Diagnosis', 'diagnosis', array('model' => 'PatientProblemList', 'class' => 'ajax'));?></th>
								<th width="100"><?php echo $paginator->sort('ICD Code', 'icd_code', array('model' => 'PatientProblemList', 'class' => 'ajax'));?></th>
								<th width="100"><?php echo $paginator->sort('Start Date', 'start_date', array('model' => 'PatientProblemList', 'class' => 'ajax'));?></th>
								<th width="100"><?php echo $paginator->sort('End Date', 'end_date', array('model' => 'PatientProblemList', 'class' => 'ajax'));?></th>
								<th><?php echo $paginator->sort('Occurrence', 'occurrence', array('model' => 'PatientProblemList', 'class' => 'ajax'));?></th>
							</tr>								
							<?php
                                                if(count($patientproblem_items) == 0)
                                                {
                                                 print '<tr><td colspan=5>None</td></tr>';
                                                }
                                                else
                                                {
							foreach ($patientproblem_items as $patientproblem)
							{
								extract($patientproblem['PatientProblemList']);
								?>
											<tr>
												<td><?php $str_arr=explode("[",$diagnosis); echo $str_arr[0]; ?></td>												
												<td><?php echo $icd_code; ?></td>
												<td><?php if($start_date && $start_date!="0000-00-00") echo __date("m/d/Y", strtotime($start_date)); ?></td>
												<td><?php if($end_date && $end_date!="0000-00-00") echo __date("m/d/Y", strtotime($end_date)); ?></td>
												<td><?php echo $occurrence; ?></td>
											</tr>                      
								<?php
							}
						}
							?>
						</table>
						<div class="paging paging_problem_list"> <?php echo $paginator->counter(array('model' => 'PatientProblemList', 'format' => __('Display %start%-%end% of %count%', true))); ?>
						<?php
								if($paginator->hasPrev('PatientProblemList') || $paginator->hasNext('PatientProblemList'))
								{
									echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
								}
							?>
						<?php 
								if($paginator->hasPrev('PatientProblemList'))
								{
									echo $paginator->prev('<< Previous', array('model' => 'PatientProblemList', 'url' => array('controller'=>'encounters', 'action'=>'load_problem_list')), null, array('class'=>'disabled')); 
								}
						?>
						<?php echo $paginator->numbers(array('model' => 'PatientProblemList', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
						<?php 
								if($paginator->hasNext('PatientProblemList'))
								{
									echo $paginator->next('Next >>', array('model' => 'PatientProblemList', 'url' => array('controller'=>'encounters', 'action'=>'load_problem_list')), null, array('class'=>'disabled')); 
								}
							?>
						</div>
					</td>
				</tr>
            </table>            
<script type="text/javascript">
$(document).ready(function() {
<?php if(count($patientproblem_items) == 0) {?>
	
	$(".paging_problem_list a").each(function(i){
	
			$(this).attr("href","javascript:void(0)");
	
	});
	<?php } else {?>
	$('.paging_problem_list a').click(function(){
		var thisHref = $(this).attr("href").replace('summary','load_problem_list')+'/patient_id:'+<?php echo $patient_id;?>;
		$.get(thisHref,function(response) {
			$('#div_problem_list').html(response);
			$('.small_table tr:nth-child(odd)').addClass("striped");
			if(typeof($ipad)==='object')$ipad.ready();
		});
		return false;
	});
	<?php }?>
});
</script>
<?php 
//}
	?>
