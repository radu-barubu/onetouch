<?php


?>
<div >
<?php  
//patient portal patient_checkin_id 
if(!empty($patient_checkin_id)):
     //send back to dashboard, now finished check in process
     $linkto = array('controller' => 'dashboard', 'action' => 'patient_portal', 'patient_id' => $patient_id, 'checkin_complete' => $patient_checkin_id);  
?>
<div class="notice" style="margin-bottom:10px">
<table style="width:100%;">
  <tr>
    <td style="width:100px"><button class='btn' onclick="javascript:history.back()"><< Back</button></td>
    <td style="vertical-align:top">These are forms which the practice may want you to complete online. Check with the office. When finished, click the 'Next' button.
    </td>
    <td style="width:100px;"><button class="btn" onclick="location='<?php echo $this->Html->url($linkto); ?>';">Next >> </button></td>
  </tr>
</table>  
</div>
<?php else: ?>
<?php echo (empty($patient_checkin_id))? $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 111)):''; ?>
<?php endif; ?>  
			<div>
				<h2>Available Forms</h2>


				<?php if (empty($templates)): ?>
				<div class="notice">
					No available forms
				</div>
				<?php else:?>

				<div id="available-forms" class="pageable">
					<table cellpadding="0" cellspacing="0" class="listing">
						<tr>
							<th>Name</th>
							<th>&nbsp;</th>
							<th>&nbsp;</th>
						</tr>
						<?php foreach($templates as $t): ?>
						<tr>
							<td style="width:65%">
							<?php echo $t['FormTemplate']['template_name']. '</td>';
							echo '<td> ' . $this->Html->link('Read/Complete This Form', 
							array('controller' => 'dashboard', 'action' => 'online_forms', 'task' => 'fill_up', 'patient_checkin_id' => $patient_checkin_id, 'template_id' => $t['FormTemplate']['template_id']), array('class' => 'btn')); ?>
							</td>
							<!-- not sure if we want this option,but can re-enable later if desired
							<td >
							<?php echo $this->Html->link('Download as PDF to complete later', 
							array('controller' => 'dashboard', 'action' => 'online_forms', 'task' => 'get_pdf', 'template_id' => $t['FormTemplate']['template_id']), array('class' => 'btn')); ?>
							</td>	
							-->					
						</tr>
						<?php endforeach;?> 
					</table>
					<div class="paging">
							<?php echo $paginator->counter(array('model' => 'FormTemplate', 'format' => __('Display %start%-%end% of %count%', true))); ?>
							<?php
									if($paginator->hasPrev('FormTemplate') || $paginator->hasNext('FormTemplate'))
									{
											echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
									}
							?>
							<?php 
									if($paginator->hasPrev('FormTemplate'))
									{
											echo $paginator->prev('<< Previous', array('model' => 'FormTemplate', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
									}
							?>
							<?php echo $paginator->numbers(array('model' => 'FormTemplate', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
							<?php 

									if($paginator->hasNext('FormTemplate'))
									{
											echo $paginator->next('Next >>', array('model' => 'FormTemplate', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
									}
							?>
					</div>					
				</div>
					<?php endif;?> 




				<br />
				<br />

				<h2>Submitted Forms</h2>
				<?php if (empty($formData)): ?>
				<div class="notice">
					No submitted forms
				</div>
				<?php else:?>

				<div id="submitted-forms" class="pageable">
					<table cellpadding="0" cellspacing="0" class="listing">
						<tr>
							<th>Name</th>
							<th>Completed By</th>
							<th>Date</th>
						</tr>
						<?php foreach($formData as $d): ?>
						<tr class="clickable">
							<td>
							<?php echo $this->Html->link($d['FormTemplate']['template_name'], 
							array('controller' => 'dashboard', 'action' => 'online_forms', 'patient_checkin_id' => $patient_checkin_id, 'task' => 'view_data', 'data_id' => $d['FormData']['form_data_id'])); ?>
							</td>
							<td>
								<?php if ($d['UserAccount']): ?> 
								<?php		if ($d['UserAccount']['patient_id'] && $d['UserAccount']['patient_id'] == $d['FormData']['patient_id'] ): ?> 
								Patient
								<?php else: ?>
								<?php		echo htmlentities($d['UserAccount']['firstname'] . ' ' . $d['UserAccount']['lastname']);  ?> 
								<?php endif;?> 
								<?php endif;?>
								&nbsp;
							</td>
							<td>
								<?php echo __date($global_date_format, strtotime($d['FormData']['created'])); ?>
							</td>							
						</tr>
						<?php endforeach;?> 
					</table>			
					<div class="paging">
							<?php echo $paginator->counter(array('model' => 'FormData', 'format' => __('Display %start%-%end% of %count%', true))); ?>
							<?php
									if($paginator->hasPrev('FormData') || $paginator->hasNext('FormData'))
									{
											echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
									}
							?>
							<?php 
									if($paginator->hasPrev('FormData'))
									{
											echo $paginator->prev('<< Previous', array('model' => 'FormData', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
									}
							?>
							<?php echo $paginator->numbers(array('model' => 'FormData', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
							<?php 

									if($paginator->hasNext('FormData'))
									{
											echo $paginator->next('Next >>', array('model' => 'FormData', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
									}
							?>
					</div>					
				</div>
				<?php endif;?> 	

			</div>
			<?php echo $this->Html->image('ajax_loaderback.gif', array('alt' => 'Loading', 'id' => 'img-loading')); ?> 
			<script type="text/javascript">
			$(function(){
				var $imgLoading = $('#img-loading').css('margin-left', '20px').remove();


				$('.pageable')
					.delegate('.paging a', 'click', function(evt){
						evt.preventDefault();

						var
							self = this,
							$pageable = $(this).closest('.pageable'),
							url = $(this).attr('href')
						;

						$pageable.prev().append($imgLoading.clone());

						$.get(url, function(html){
							var 
								$html = $(html),
								content = $html.find('#' + $pageable.attr('id')).html()
							;
							$pageable
								.html(content)
								.prev()
									.find('img').remove();

						});
					})
					.delegate('tr.clickable', 'click', function(evt){
							evt.preventDefault();
							evt.stopPropagation();

							var url = $(this).find('a').attr('href');

							window.location.href = url;
					});

			});
			</script>	
	
	
	




	
</div>
