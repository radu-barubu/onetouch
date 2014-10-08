<?php

$links = array(
	'Options' => 'patient_portal',
	'Medical Hx Favorites' => 'patient_portal_medical',
	'Surgical Hx Favorites' => 'patient_portal_surgical',
	'Social Hx Favorites' => 'patient_portal_social',
	'Family Hx Favorites' => 'patient_portal_family',
);

echo $this->element('links', array('links' => $links));

?>
