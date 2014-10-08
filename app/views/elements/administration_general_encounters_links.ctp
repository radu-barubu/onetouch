<?php

$links = array(
	'Types' => 'encounter_types',
	'Superbill Service Levels' => 'superbill_service_level',
	'Superbill Advanced Codes' => 'superbill_advanced',
);

echo $this->element('links', array('links' => $links));

?>
