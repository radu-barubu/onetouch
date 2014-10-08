<?php

class filter {
	
	/**
	 * 
	 * Filter  numeric inputs, does not take zeros. 
	 * 
	 * @param $input
	 * @param $_strict_message
	 */
	function integer($input, $_strict_message = null)
	{
		if(is_numeric($input)) {
			settype($input ,'integer');
		}
		if(($input && !is_integer($input) && $_strict_message) || (!$input && $_strict_message)) {
			//throw error
			$controller =& new Controller();
			$error = $controller->cakeError('filter', array(
				'message'=> $_strict_message
			));
		} else if(!is_integer($input)) {
			$input = false;
		}
		
		return $input;
	}
}