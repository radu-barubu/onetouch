<?php
	$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
	$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
	$thisURL = $this->Session->webroot . $this->params['url']['url'];
	$system_admin_access = (($this->Session->read("UserAccount.role_id") == EMR_Roles::SYSTEM_ADMIN_ROLE_ID)?true:false);
?>
<div style="overflow: hidden;">
    <?php 
			// Patient View of Forms Page ===============================================
			if($this->Session->read("UserAccount.role_id") == EMR_Roles::PATIENT_ROLE_ID): 
		?>
    	<?php		echo $this->element('patient_general_links', array('patient_id' => $patient_id)); ?>
			<?php 
					$links = array(
						'Printable Forms' => 'printable_forms',
						'Online Forms' => $this->params['action'],
					);
					echo $this->element('links', array('links' => $links));
			?>	
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
						</tr>
						<?php foreach($templates as $t): ?>
						<tr>
							<td class="clickable">
							<?php echo $this->Html->link($t['FormTemplate']['template_name'], 
							array('controller' => 'forms', 'action' => 'fill_up', $t['FormTemplate']['template_id'])); ?>
							</td>
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
						</tr>
						<?php foreach($formData as $d): ?>
						<tr>
							<td class="clickable">
							<?php echo $this->Html->link($d['FormTemplate']['template_name'] . ' (' . __date($global_date_format, strtotime($d['FormData']['created'])) .')', 
							array('controller' => 'forms', 'action' => 'view_data', $d['FormData']['form_data_id'])); ?>
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
					.delegate('td.clickable', 'click', function(evt){
						if (evt.target == this) {
							evt.preventDefault();
							evt.stopPropagation();

							var url = $(this).find('a').attr('href');

							window.location.href = url;
						}				
					});

			});
			</script>	
	
	
	
    <?php 
		
		// Admin View of Patient Forms =============================================
		else: 
		?>
    <?php
        $links = array('Forms' => $this->params['action']);
        echo $this->element('links', array('links' => $links));
    ?>
    
    <?php endif;?>



	
</div>