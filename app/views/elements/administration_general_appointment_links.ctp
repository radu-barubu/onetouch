<?php

$links = array(
	'Types' => 'appointment_types',
	'Rooms' => 'schedule_rooms',
	'Status' => 'schedule_statuses',
	'Reminders' => 'reminders'
);

echo $this->element('links', array('links' => $links));

?>