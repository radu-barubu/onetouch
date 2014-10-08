<?php

class PatientIdSegment extends AppModel 
{
	public $name = 'PatientIdSegment';
	public $useTable = 'patient_id_segment';
	public $primaryKey = 'id';
	
	
	function getSettings()
	{
		$patient_id_segment  = $this->find('first');
		$patient_id_segment = (object) $patient_id_segment['PatientIdSegment'];
		
		if(!$patient_id_segment ) {
			$patient_id_segment  = new stdObject();
		}
		return $patient_id_segment;
	}
	
}	
?>	