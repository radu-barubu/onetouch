<?php

class assessment {
	
	public static function options($get_option = null)
	{
		$mods = array(
			'new_4' => 'New Dx, work-up needed (4 pts)',
			'new_3' => 'New Dx, NO work-up (3 pts)',
			'minor_2' => 'Self-Limited/Minor Dx (1 pt)',
			'est_2' => 'Established Dx, UNcontrolled (2 pts)',
			'est_1' => 'Established Dx, stable (1 pt)'
		);
		
		if($get_type && isset($mods[$get_type])) {
			
			return $mods[$get_type];
		}
		return $mods;
	}
	
}