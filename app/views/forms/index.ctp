<?php


?>

<div>
	<h2>Forms</h2>
	
	
	<?php if (empty($templates)): ?>
	<div class="notice">
		No available forms
	</div>
	<?php else:?>
	
	<table cellpadding="0" cellspacing="0" class="listing">
		<tr>
			<th>Name</th>
		</tr>
		<?php foreach($templates as $t): ?>
		<tr>
			<td class="clickable">
			<?php echo $this->Html->link($t['FormTemplate']['name'], 
			array('controller' => 'forms', 'action' => 'fill_up', $t['FormTemplate']['template_id'])); ?>
			</td>
		</tr>
		<?php endforeach;?> 
	</table>
	<?php endif;?> 
	
	
	<br />
	<br />
	
	<h2>Submitted Forms</h2>
	<?php if (empty($formData)): ?>
	<div class="notice">
		No submitted forms
	</div>
	<?php else:?>
	
	<table cellpadding="0" cellspacing="0" class="listing">
		<tr>
			<th>Name</th>
		</tr>
		<?php foreach($formData as $d): ?>
		<tr>
			<td class="clickable">
			<?php echo $this->Html->link($d['FormTemplate']['name'] . ' (' . __date($global_date_format, strtotime($d['FormData']['created'])) .')', 
			array('controller' => 'forms', 'action' => 'view_data', $d['FormData']['data_id'])); ?>
			</td>
		</tr>
		<?php endforeach;?> 
	</table>
	<?php endif;?> 	
	
</div>
<script type="text/javascript">
$(function(){
	
	$('td.clickable').click(function(evt){
		if (evt.target == this) {
			evt.preventDefault();
			evt.stopPropagation();

			var url = $(this).find('a').attr('href');
			
			window.location.href = url;
		}
		
		
	});
	
});
</script>