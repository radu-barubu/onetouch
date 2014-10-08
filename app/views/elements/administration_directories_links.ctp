<?php

$links = array(
    'Lab Facilities' => 'lab_facilities',
    'Pharmacies' => 'pharmacies',
    'Referral List' => 'referral_list',
    'Insurance Companies' => 'insurance_companies'
);

echo $this->element('links', array('links' => $links));
?>