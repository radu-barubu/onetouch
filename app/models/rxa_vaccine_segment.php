<?php

class RxaVaccineSegment extends AppModel 
{
	public $name = 'RxaVaccineSegment';
	public $useTable = 'rxa_vaccine_segment';
	public $primaryKey = 'id';
	
	
	function getSettings()
	{
		$rxa_vaccine_segment  = $this->find('first');
		$rxa_vaccine_segment = (object) $rxa_vaccine_segment['RxaVaccineSegment'];
		
		if(!$rxa_vaccine_segment ) {
			$rxa_vaccine_segment = new stdObject();
		}
		return $rxa_vaccine_segment;
	}
	
}	
?>	