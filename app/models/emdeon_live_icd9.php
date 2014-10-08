<?php

class EmdeonLiveIcd9 extends AppModel 
{
	public $useTable = false;
	public $useDbConfig = 'EmdeonIcd9';
	
	public $_schema = array(
		'icd_9_cm_code' => array('type' => 'string'),
		'description' => array('type' => 'string')
	);

}

?>