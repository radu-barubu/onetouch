<?php

$links = array();
$links['Vendor Settings'] = 'vendor_settings';
$links['Patient Import'] = 'patient_import';
$links['Patient Export'] = 'patient_export';
echo $this->element('links', array('links' => $links));

?>