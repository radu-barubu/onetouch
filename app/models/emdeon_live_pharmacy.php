<?php

class EmdeonLivePharmacy extends AppModel 
{
	public $useTable = false;
	public $useDbConfig = 'EmdeonPharmacy';
	
	public $_schema = array(
		'pharmacy_id' => array('type' => 'string'),
		'name' => array('type' => 'string')
	);

}

?>