<?php

class PracticeEncounterType extends AppModel {
	const _DEFAULT = 1;
	const _PHONE = 2;
	
	public $name = 'PracticeEncounterType';
	public $primaryKey = 'encounter_type_id';
	public $validate = array(
    'name' => array(
        'rule' => 'notEmpty',
        'message' => 'Encounter type name cannot be left blank'
    )
	);
	
	public $hasMany = array(
		'PracticeEncounterTab' => array(
			'className' => 'PracticeEncounterTab',
			'foreignKey' => 'encounter_type_id',
			'dependent'    => true,
		),
		'ScheduleType' => array(
			'className' => 'ScheduleType',
			'foreignKey' => 'encounter_type_id',
			'dependent'    => false,
		),
	);	
	

}

?>