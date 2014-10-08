<?php

class HealthMaintenanceAction extends AppModel 
{ 
	public $name = 'HealthMaintenanceAction';
	public $primaryKey = 'action_id';

	public $hasMany = array(
		'HealthMaintenanceSubAction' => array(
			'className' => 'HealthMaintenanceAction',
			'foreignKey' => 'main_action'
		)
	);
}


?>