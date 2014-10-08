<?php

$links = array(
    'Labs' => 'in_house_work_labs',
    'Radiology' => 'in_house_work_radiology',
    'Procedures' => 'in_house_work_procedures',
    'Immunizations' => 'in_house_work_immunizations',
    'Injections' => 'in_house_work_injections',
    'Meds' => 'in_house_work_meds',
    'Supplies' => 'in_house_work_supplies'
);

echo $this->element('links', array('links' => $links));
?>