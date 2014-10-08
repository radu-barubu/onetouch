<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$test_codes = array();

foreach ($items as $i) {
	$test_codes[] = $i['EmdeonFavoriteTestCode'];
}


?>
<div>
	<div class="paging">
		<?php echo $paginator->counter(array('model' => 'EmdeonFavoriteTestCode', 'format' => __('Display %start%-%end% of %count%', true))); ?>
		<?php
			if($paginator->hasPrev('EmdeonFavoriteTestCode') || $paginator->hasNext('EmdeonFavoriteTestCode'))
			{
				echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
			}
		?>
		<?php 
			if($paginator->hasPrev('EmdeonFavoriteTestCode'))
			{
				echo $paginator->prev('<< Previous', array('model' => 'EmdeonFavoriteTestCode', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
			}
		?>
		<?php echo $paginator->numbers(array('model' => 'EmdeonFavoriteTestCode', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
		<?php 
			if($paginator->hasNext('EmdeonFavoriteTestCode'))
			{
				echo $paginator->next('Next >>', array('model' => 'EmdeonFavoriteTestCode', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
			}
		?>
	</div>
	<script type="text/javascript">
		window.favoriteTestCode = <?php echo json_encode($test_codes); ?>;
	</script>
</div>
