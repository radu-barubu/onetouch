<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php if ($importErrors): ?> 
<div class="notice">
	<ul>
		<?php foreach ($importErrors as $e): ?>
		<li><?php echo htmlentities($e); ?></li>
		<?php endforeach;?>
	</ul>
</div>
<?php else: ?>


<?php //pr($patientData);?> 

<form id="patient-import" action="<?php echo $this->here; ?>" method="post">
	<table class="listing" cellspacing="0" cellpadding="0" >
		<tr class="striped">
			<th removeonread="true" style="width: 50px;">
				<label class="label_check_box">
					<input class="master_chk" type="checkbox">
				</label>
			</th>
			<th style="width: 250px;">
				Patient Name
			</th>
			<th style="width: 150px;">
				DOB
			</th>
			<th>
				Import Status
			</th>
		</tr>
		<?php 

		$ct = 0;
		foreach($patientData as $pData): ?>

		<?php 

			if (!isset($pData['details']['first_name'])) {
				$pData['details']['first_name'] = '';
			}


			if (!isset($pData['details']['last_name'])) {
				$pData['details']['last_name'] = '';
			}

			if (!isset($pData['details']['dob'])) {
				$pData['details']['dob'] = '';
			}

		?> 

		<tr class="<?php echo ($ct++%2) ? 'striped' : '' ?>">
			<td>
				<label class="label_check_box">
					<input type="checkbox" value="<?php echo $pData['data_id'] ?>" class="child_chk" name="pData[]" <?php echo ($pData['importable']) ? '' :'disabled="disabled"'; ?> <?php echo ($pData['importable'] && empty($pData['errors'])) ? 'checked="checked"' : ''; ?>/>
				</label>			
			</td>
			<td>
				<?php echo htmlentities($pData['details']['first_name'] . ' ' . $pData['details']['last_name']); ?> &nbsp;
			</td>
			<td>
				<?php echo __date($global_date_format, strtotime($pData['details']['dob'])); ?>
			</td>
			<td>
				<?php if ($pData['errors']): ?> 
				<span style="color: red;"><?php echo implode('<br />', $pData['errors']); ?></span>
				<?php else:?>
				<span style="color: green;">READY for import</span>
				<?php endif;?> 
			</td>
		</tr>
		<?php endforeach; ?> 
	</table>
	
	<input type="hidden" name="filename" value="<?php echo htmlentities($filename); ?>" />
	<input type="hidden" name="page" value="<?php echo $paginator->current(); ?>" />
	
	<br />
	<input type="submit" value="Import Selected Data" class="btn"/>
	<?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...', 'style' => 'display: none;', 'id' => 'import-processing')); ?>
	
</form>


<?php

$paginator->options(array('url' => array_merge($this->passedArgs, array('task' => 'browse_import', 'file' => $filename))));
?>

    <div class="paging">
        <?php echo $paginator->counter(array('model' => 'PatientImport', 'format' => __('Display %start%-%end% of %count%', true))); ?>
        <?php
            if($paginator->hasPrev('PatientImport') || $paginator->hasNext('PatientImport'))
            {
                echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
            }
        ?>
        <?php 
            if($paginator->hasPrev('PatientImport'))
            {
                echo $paginator->prev('<< Previous', array('model' => 'PatientImport', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
        <?php echo $paginator->numbers(array('model' => 'PatientImport', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
        <?php 
            if($paginator->hasNext('PatientImport'))
            {
                echo $paginator->next('Next >>', array('model' => 'PatientImport', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
            }
        ?>
    </div>

		<br />
		<br />
		<br />
		<form id="mass-import" action="" method="post">
			<h2>Bulk Action</h2>
			<p>This will apply to all records read from the file</p>
			<label class="label_check_box">
				<input type="radio" value="all" name="bulk" class="mass_action"/> Insert ALL records from file INCLUDING Duplicates
			</label>					
			
			<label class="label_check_box">
				<input type="radio" value="unique" name="bulk" class="mass_action" checked="checked" /> Insert ONLY UNIQUE records from file
			</label>					
			<input type="hidden" name="filename" value="<?php echo htmlentities($filename); ?>" />
			<input type="hidden" name="page" value="<?php echo $paginator->current(); ?>" />
			&nbsp; 
			<input type="submit" value="Continue Bulk Action" class="btn" style="float: none"/>
			
			
			<div id="mass-import-processing">
				<br />
				<?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?>
				Importing records. This might take a while ....
			</div>
	
			
		</form>



<?php endif; ?>

<script type="text/javascript">
initAutoLogoff();	
	$(function(){
		$('.master_chk').click(function(evt){
			
			if ($(this).is(':checked')) {
				$('.child_chk').each(function(){
					
					if (!$(this).attr('disabled')) {
						$(this).attr('checked', 'checked');
					}
					
				});
			} else {
				$('.child_chk').removeAttr('checked');
			}
			
		});
		
		
		$('#patient-import').submit(function(evt){
			evt.preventDefault();
			var self = this;
			
			if (!$('.child_chk:checked').length) {
				return false;
			}
		
			$('#import-processing').show();

			// Disable autologoff timer
			if(logouttimer_id) {
							window.clearTimeout(logouttimer_id);
			}
			if(countdown_id) {
							window.clearTimeout(countdown_id);
			}			
			
			$.post(currentUrl+'/task:import_patient_data', $(self).serialize(), function(html){
				var importHtml = html 

				var url = $(self).attr('action');
				
				$.get(url, function(html){
					$('#import-data').html(importHtml + html);
					initAutoLogoff();
					
				});
			});
		
		});		
		
		$('#mass-import-processing').hide();
		$('#mass-import').submit(function(evt){
			evt.preventDefault();
			var self = this;
			
			if (!$('.mass_action:checked').length) {
				return false;
			}
		
			$('#patient-import, div.paging').remove();
		
			$('#mass-import-processing').show();

			// Disable autologoff timer
			if(logouttimer_id) {
							window.clearTimeout(logouttimer_id);
			}
			if(countdown_id) {
							window.clearTimeout(countdown_id);
			}			
			
			
			$.post(currentUrl+'/task:mass_import', $(self).serialize(), function(html){
					$('#import-data').html(html);
			});
			
		 
		 
		});				
		
		
		
	});
	
</script>