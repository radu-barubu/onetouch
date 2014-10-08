<?php

class DirectoryLabFacility extends AppModel 
{
	var $name = 'DirectoryLabFacility';
	var $primaryKey = 'lab_facilities_id';
    var $useTable = 'directory_lab_facilities';
	
	public $actsAs = array(
		'Unique' => array('lab_facility_name')
	);
}

?>