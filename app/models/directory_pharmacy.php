<?php

class DirectoryPharmacy extends AppModel 
{
	var $name = 'DirectoryPharmacy';
	var $primaryKey = 'pharmacies_id';
    var $useTable = 'directory_pharmacies';
	
	public $actsAs = array(
		'Unique' => array('pharmacy_name')
	);
}

?>