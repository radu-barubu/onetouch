<?php

$links = array(
    'Unmatched Lab Reports' => 'unmatched_lab_reports',
    'Unmatched Rx Refill Requests' => 'unmatched_rxrefill_requests'
);

echo $this->element('links', array('links' => $links));
?>