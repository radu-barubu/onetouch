<?php

$links = array(
    'Stage 1 Report' => 'stage_1_report',
    'Automatic Measures' => 'automatic_measures',
    'Clinical Quality Measures' => 'clinical_quality_measures',
);

echo $this->element('links', array('links' => $links));
?>