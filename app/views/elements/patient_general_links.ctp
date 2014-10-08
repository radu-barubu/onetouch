<?php

App::import('Model', 'AdministrationForm');
App::import('Model', 'FormTemplate');
App::import('Model', 'FormData');

$role_id = $this->Session->read('UserAccount.role_id');
$dashboard_access = 'clinical';

App::import('Helper', 'QuickAcl');
$quickacl = new QuickAclHelper();

if($quickacl->getAccessType("dashboard", "patient_portal", '', array('role_id' => $role_id, 'emergency' => 0)) != 'NA')
{
	$dashboard_access = 'patient';
}

if($quickacl->getAccessType("dashboard", "non_clinical", '', array('role_id' => $role_id, 'emergency' => 0)) != 'NA')
{
	$dashboard_access = 'non_clinical';
}			

$administrationFormModel = new AdministrationForm();
$formTemplateModel = new FormTemplate();
$formDataModel = new FormData();


$hasPrintableForms = $administrationFormModel->find('count', array(
	'conditions' => array(
		'AdministrationForm.access_'.$dashboard_access => '1',		
	),
));
$hasOnlineForms = $formTemplateModel->find('count', array(
	'conditions' => array(
		'FormTemplate.template_version' => 0,
		'FormTemplate.access_'.$dashboard_access => '1',		
	),
));

$hasFormData = $formDataModel->find('count', array(
	'conditions' => array(
		'FormData.patient_id' => $patient_id,
	),
));

$compare_params = array('action');
$action = isset($action)? $action : $this->params['action'];

$links = array(
	'Appointment Request' => array('action' => 'patient_portal', 'patient_id'=> $patient_id),
	'General Information' => array('action' =>'general_information', 'task' => 'edit', 'patient_id'=> $patient_id),
	'History' => array('action' => 'hx_medical', 'patient_id'=> $patient_id),
	'Allergies' => array('action' => 'allergies', 'patient_id' => $patient_id),
	'Problem List' => array('action' => 'problem_list', 'patient_id'=> $patient_id),
	'Medication List' => array('action' => 'medication_list', 'patient_id'=> $patient_id),
	'Past Visits' => array('action' => 'past_visits', 'patient_id'=> $patient_id),
);

if ($hasPrintableForms || $hasOnlineForms || $hasFormData) {
	
	// If there is an online form available OR if the patient has online form data submitted
	if ($hasOnlineForms || $hasFormData) {
		$links['Forms'] = array('action' => 'online_forms');
	}
	
	if ($hasPrintableForms) {
		$links['Forms'] = array('action' => 'printable_forms' );
	}
	
}
	$links['Lab Results'] = array('action' => 'in_house_work_labs', 'patient_id' => $patient_id);
	
echo (!empty($patient_checkin_id) ) ? '<h2>Patient Check-In for '. $_SESSION['UserAccount']['firstname'] . ' ' . $_SESSION['UserAccount']['lastname'].'</h2>' : $this->element('links', array('links' => $links, 'compare_params' => $compare_params, 'action' => $action));

?>
