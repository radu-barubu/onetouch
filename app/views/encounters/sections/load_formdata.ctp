        <?php
		if(count($formdata_items) > 0)
		{
			?>
            <table id="table_formdata" cellpadding="0" cellspacing="0"  width="100%">
                <tr>
                    <th align=left>Patient Forms</th>
                </tr>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" class="small_table" style="width: 100%;">
							<tr>
								<th>Form</th>
								<th>Completed By</th>
								<th>Date</th>
              <?php if($encounter_id): ?>
								<th style="width: 50px;">&nbsp;</th>
              <?php endif;?>
							</tr>
								<?php 	$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
								 ?> 
								
							<?php
							foreach ($formdata_items as $f)
							{
								?>
											<tr class="clickable">
												<td><?php echo $this->Html->link($f['FormTemplate']['template_name'], array('controller' => 'forms', 'action' => 'view_html_data', 'data_id' => $f['FormData']['form_data_id']), array('class' => 'formdata-link')); ?></td>												
							<td>
								<?php if ($f['UserAccount']): ?> 
								<?php		if ($f['UserAccount']['patient_id'] && $f['UserAccount']['patient_id'] == $f['FormData']['patient_id'] ): ?> 
								Patient
								<?php else: ?>
								<?php		echo htmlentities($f['UserAccount']['firstname'] . ' ' . $f['UserAccount']['lastname']);  ?> 
								<?php endif;?> 
								<?php endif;?>
								&nbsp;
							</td>
							<td>
								<?php echo __date($global_date_format, strtotime($f['FormData']['created'])); ?>
							</td>
              <?php if($encounter_id): ?>
							<td>
								<?php echo $this->Html->link('Import', 
									array(
										'controller' => 'encounters',
										'action' => 'hpi_data',
										'task' => 'import_form_data',
										'encounter_id' => $encounter_id,
										'form_data_id' => $f['FormData']['form_data_id'],
									), 
									array(
										'class' => 'import-form-data btn'
									)); ?>
							</td>							
              <?php endif;?>
											</tr>                      
                  
								<?php
							}
							?>
						</table>
					</td>
				</tr>
            </table>
        	<div class="paging paging_formdata">
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
						echo $paginator->prev('<< Previous', array('model' => 'FormData', 'url' => array('controller'=>'encounters', 'action'=>'load_formdata')), null, array('class'=>'disabled')); 
					}
			?>
            <?php echo $paginator->numbers(array('model' => 'FormData', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
            <?php 
					if($paginator->hasNext('FormData'))
					{
						echo $paginator->next('Next >>', array('model' => 'FormData', 'url' => array('controller'=>'encounters', 'action'=>'load_formdata')), null, array('class'=>'disabled')); 
					}
				?>
			</div>
            <?php
		}
		?>
