<?php

class EncounterPlanRxChanges extends AppModel {
  
	public $name = 'EncounterPlanRxChanges'; 
	public $primaryKey = 'encounter_plan_rx_changes_id';
	public $useTable = 'encounter_plan_rx_changes';
	
	public $belongsTo = array(
		'EncounterMaster' => array(
			'className' => 'EncounterMaster',
			'foreignKey' => 'encounter_id'
		)
	);  
  
  
}