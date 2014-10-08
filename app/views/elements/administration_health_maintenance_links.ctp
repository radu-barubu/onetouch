<?php

$links = array(
    'Health Maintenance Plans' => 'health_maintenance_plans',
    'Clinical Alerts' => 'clinical_alerts',
    'Patient Reminders' => 'patient_reminders',
    'Setup Details' => 'setup_details'
);

echo $this->element('links', array('links' => $links));
?>