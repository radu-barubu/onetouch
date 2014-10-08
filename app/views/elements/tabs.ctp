<div class="title_area">
	<div class="title_text">
	<?php foreach($tabs as $k => $v):?>
	<?php 
	 
	if(is_array($v)) {
		$controller = key($v);
		$action = $v[key($v)];
		
	} else  {
	
		$controller =  $this->params['controller'];
		$action = $v;
	}

	$selected = (($this->params['controller']==$controller && $this->params['action']==$action)? 'active" style="color:#fff !important"':'');
	
	$active = array();
	if($selected) {
		$active = array('class'=>'active');
	}
	?>
	
	<div class="title_item <?php echo $selected;?>"><?php echo $html->link($k,array('controller'=> $controller,'action'=> $action),$active);?></div>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<?php endforeach; ?>
	</div>
</div>