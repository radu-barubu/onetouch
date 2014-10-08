<?php

class CommonOrderSegment extends AppModel 
{
	public $name = 'CommonOrderSegment';
	public $useTable = 'common_order_segment';
	public $primaryKey = 'id';
	
	
	function getSettings()
	{
		$common_order_segment  = $this->find('first');
		$common_order_segment = (object) $common_order_segment['CommonOrderSegment'];
		
		if(!$common_order_segment ) {
			$common_order_segment  = new stdObject();
		}
		return $common_order_segment;
	}
	
}	
?>	