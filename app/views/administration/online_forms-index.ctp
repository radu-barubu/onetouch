<?php
$role_id = $this->Session->read("UserAccount.role_id");
$isAdmin = ($role_id == EMR_Roles::SYSTEM_ADMIN_ROLE_ID || $role_id == EMR_Roles::PRACTICE_ADMIN_ROLE_ID ) ;


$patient_id = isset($this->params['named']['patient_id']) ? $this->params['named']['patient_id'] : '';

?>
<div>
	<h2>Form Templates <?php echo $this->Html->image('ajax_loaderback.gif', array('alt' => 'Loading', 'id' => 'img-loading')); ?> </h2>
	<div id="template-list-area">
		<?php if ($isAdmin): ?> 

				<?php if (empty($templates)): ?>
				<div class="notice">
					No available form templates
				<?php echo $this->Html->link('Add Sample Forms', array(
					'controller' => 'administration',
					'action' => 'online_forms',
					'task' => 'add_sample'
				)); ?> 
				</div>
				<?php else:?>

				<form id="template_form" action="<?php echo $this->Html->url(array('controller' => 'administration', 'action' => 'online_forms', 'task' => 'mass_delete')); ?>" method="post">
					<table cellpadding="0" cellspacing="0" class="listing">
						<tr>
							<th class="ignore" style="width: 40px;"><label for="check-all" class="label_check_box"><input type="checkbox" id="check-all" name="check-all" value="1" /></label></th>
							<th>Name</th>
							<th>&nbsp;</th>
							<th>&nbsp;</th>
						</tr>
						<?php foreach($templates as $t): ?>
						<tr editlink="<?php echo $html->url(array('controller' => 'administration', 'action' => 'online_forms' ,'task' => 'view', 'template_id' => $t['FormTemplate']['template_id']));?>">
							<td class="ignore"><label for="template-<?php echo $t['FormTemplate']['template_id']; ?>" class="label_check_box"><input type="checkbox" id="template-<?php echo $t['FormTemplate']['template_id']; ?>" name="template_id[]" value="<?php echo $t['FormTemplate']['template_id']; ?>" class="template-chk"/></label></td>
							<td>
							<?php echo $t['FormTemplate']['template_name']; //$this->Html->link($t['FormTemplate']['template_name'], 
							//array('controller' => 'administration', 'action' => 'online_forms', 'task' => 'edit', 'template_id' => $t['FormTemplate']['template_id'])); ?>
							</td>
							<td>
							<?php //echo $this->Html->link('View/Edit', 
							//array('controller' => 'administration', 'action' => 'online_forms' ,'task' => 'view', 'template_id' => $t['FormTemplate']['template_id'])); ?>
							</td>
							<td>
							<?php echo $this->Html->link('Download PDF', 
							array('controller' => 'administration', 'action' => 'online_forms' ,'task' => 'get_pdf', 'template_id' => $t['FormTemplate']['template_id'])); ?>
							</td>
						</tr>
						<?php endforeach;?> 
					</table>
				</form>

				<?php endif;?> 
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
				<br style="clear: left;"/>
					<div style="width: auto; float: left;">
						<div class="actions">
							<ul>
								<li><?php echo $html->link(__('Add New', true), array('controller' => 'administration', 'action' => 'online_forms', 'task' => 'add')); ?></li>
								<?php if ($templates): ?> 
								<li><input type="button" name="delete-selected" value="Delete Selected" id="delete-selected" class="btn"/></li>
								<?php endif; ?> 
							</ul>
						</div>
					</div>

		<?php else: ?>
				<table cellpadding="0" cellspacing="0" class="listing">
					<tr>
						<th>Name</th>
						<th>&nbsp;</th>
					</tr>
					<?php foreach($templates as $t): ?>
					<tr>
						<td class="clickable">
						<?php echo $this->Html->link($t['FormTemplate']['template_name'], 
						array('controller' => 'administration', 'action' => 'online_forms' ,'task' => 'fill_up', 'template_id' => $t['FormTemplate']['template_id'], 'patient_id' => $patient_id)); ?>
						</td>
						<td class="clickable">
						<?php echo $this->Html->link('Download PDF', 
						array('controller' => 'administration', 'action' => 'online_forms' ,'task' => 'get_pdf', 'template_id' => $t['FormTemplate']['template_id'])); ?>
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
		<?php endif; ?>		
				
	</div>
	<script type="text/javascript">
		$(function(){
			var 
				$contentArea = $('#template-list-area'),
				$imgLoading = $('#img-loading').css('margin-left', '20px').hide();
			;
			$contentArea
				.delegate('.paging a', 'click', function(evt){
					evt.preventDefault();

					var
						url = $(this).attr('href')
					;

					$imgLoading.show();

					$.get(url, function(html){
						var 
							$html = $(html),
							content = $html.find('#template-list-area').html()
						;
						$contentArea
							.html(content)
							
							$imgLoading.hide();

              $("table.listing tr:nth-child(odd)").not('.controller-row').addClass("striped");
            $("table.listingDis tr:nth-child(odd)").not('.controller-row').addClass("striped");
              $("div.message").addClass("notice");
              $('#loading p').addClass('ui-corner-bl ui-corner-br');

            $("table.listing tr td").not('table.listing tr td.ignore').not('table.listing tr:first td').each(function()
            {
              $(this).click(function()
              {
                var edit_url = $(this).parent().attr("editlink");

                if (typeof edit_url  != "undefined") 
                {
                  $(this).parent().css("background", "#FDF5C8");
                  window.location = edit_url;
                }
              });

              $(this).css("cursor", "pointer");
            });




					});
				})			
				.delegate('td.clickable', 'click',function(evt){
					if (evt.target == this) {
						evt.preventDefault();
						evt.stopPropagation();

						var url = $(this).find('a').attr('href');

						window.location.href = url;
					}					
				})
				.delegate('#check-all', 'click', function(evt){
					
					if ($(this).is(':checked')) {
						$('.template-chk').attr('checked', 'checked');
					} else {
						$('.template-chk').removeAttr('checked');
					}
					
					
				})
				.delegate('#delete-selected', 'click', function(){
					
					var $templates = $('.template-chk:checked');
					
					if (!$templates.length) {
						return false;
					}
					
					$('#template_form').submit();
					
				})
				
				
				
				
		});
	</script>
</div>


