<?php

class SpecimenSource extends AppModel 
{
	public $name = 'SpecimenSource';
	public $primaryKey = 'specimen_id';
	public $useTable = 'specimen_sources';
	public $order = "description";
}

?>