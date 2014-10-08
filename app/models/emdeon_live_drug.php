<?php

class EmdeonLiveDrug extends AppModel 
{
	public $useTable = false;
	public $useDbConfig = 'EmdeonDrug';
	
	public $_schema = array(
		'id' => array('type' => 'string'),
		'name' => array('type' => 'string')
	);

}

?>