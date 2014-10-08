<?php

class ClinicalAlertsManagement extends AppModel 
{
	public $name = 'ClinicalAlertsManagement';
	public $primaryKey = 'alert_id';
    public $useTable = 'clinical_alerts_management';

	public $belongsTo = array(
		'ClinicalAlert' => array(
			'className' => 'ClinicalAlert',
			'foreignKey' => 'ca_alert_id'
		)
	);
}

?>