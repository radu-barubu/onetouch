<?php

$links = array();
$links['Practice Settings'] = 'practice_settings';
$links['Practice Profile'] = 'practice_profile';
$links['Practice Locations'] = 'practice_locations';
$links['Appointments'] = array('appointment_types', 'schedule_rooms', 'schedule_statuses', 'reminders');
$links['Encounters'] = array('encounter_types','superbill_settings');
$links['Letter Templates'] = 'letter_templates';
$links['Patient Portal'] = array('patient_portal','patient_portal_medical', 'patient_portal_surgical','patient_portal_social','patient_portal_family');

if ($this->QuickAcl->getAccessType("administration", "services") == 'W'):
	$links['Services'] = 'vendor_settings';
endif;

echo $this->element('links', array('links' => $links));

?>
