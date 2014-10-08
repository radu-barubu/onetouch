<?php

class EmdeonLiveTestCode extends AppModel 
{
	public $useTable = false;
	public $useDbConfig = 'EmdeonTestCodes';
	
	public $_schema = array(
		'order_code' => array('type' => 'integer'),
		'description' => array('type' => 'string'),
		'has_aoe' => array('type' => 'string')
	);

}

?>