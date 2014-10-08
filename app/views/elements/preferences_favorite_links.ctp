<?php

$_prset=$session->read('PracticeSetting');
$links3=array();
if ($_prset['PracticeSetting']['labs_setup'] == 'Standard') //don't show electronic labs, or e-Rx areas to avoid confusion
{
  $links2=array();
} else {
  $links2 = array(
     //'Lab Tests' => 'favorite_lab_tests',
    'Lab Test Codes' => 'favorite_test_codes',
    'Lab Test Groups' => 'favorite_test_groups');
  if ($_prset['PracticeSetting']['rx_setup'] == 'Electronic_Emdeon'){
     $links3=array(
      'Favorite Rx' => 'favorite_prescriptions',
      /*'Favorite Pharmacy' => 'favorite_pharmacy'*/
    );
  } 
}
$links1 = array('Common Complaints' => 'common_complaints',
    'Medical Diagnoses' => 'favorite_diagnoses',
    'Medical Hx' => 'favorite_medical',
    'Surgical Hx' => 'favorite_surgeries',
    'Macros' => 'favorite_macros');

$links = array_merge($links1,$links2,$links3);
echo $this->element('links', array('links' => $links));
?>
