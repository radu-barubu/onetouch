<?php

class EmdeonLiveDrugFormulary extends AppModel 
{
	public $useTable = false;
	public $useDbConfig = 'EmdeonDrugFormulary';
	
	public $_schema = array(
		'drug_id' => array('type' => 'string'),
		'name' => array('type' => 'string')
	);

}

?>
