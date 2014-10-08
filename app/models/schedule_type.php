<?php

class ScheduleType extends AppModel 
{ 
	public $name = 'ScheduleType'; 
	public $primaryKey = 'appointment_type_id';
	public $useTable = 'schedule_appointment_types';
	public $belongsTo = array(
		'PracticeEncounterType' => array(
			'className' => 'PracticeEncounterType',
			'foreignKey' => 'encounter_type_id'
		),

	);		
	public function getScheduleType($appointment_type_id)
	{
		$conditions['ScheduleType.appointment_type_id'] = $appointment_type_id;
		$data = $this->find('first', array('conditions' => $conditions));
		
		if($data)
		{
			return $data['ScheduleType']['type'];
			
		}
		else
		{
			return '';
		}
	}
}

?>