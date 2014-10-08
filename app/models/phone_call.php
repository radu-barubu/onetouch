<?php

class PhoneCall extends AppModel 
{ 
	public $name = 'PhoneCall'; 
	public $primaryKey = 'id';
	
	public $belongsTo = array(
		'Patient' => array(
			'className' => 'PatientDemographic',
			'foreignKey' => 'patient_id'
		)
	);
}


?>