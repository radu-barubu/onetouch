<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<div style="overflow: hidden;">
	<?php 
			$links = array(
				'Printable Forms' => 'printable_forms',
				'Online Forms' => $this->params['action'],
			);
			
			echo $this->element('links', array('links' => $links));
	?>	
	<?php include dirname(__FILE__) . DS . $this->action . '-' . $task .'.ctp'; ?>
</div>