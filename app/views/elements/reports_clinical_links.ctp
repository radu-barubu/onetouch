<?php

$links = array(
    'Patient Lists' => 'patient_lists',
    'Public Health Surveillance' => 'public_health_surveillance',
    'Immunization Registries' => 'immunization_registries',
);

echo $this->element('links', array('links' => $links));
?>