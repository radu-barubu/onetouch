<?php

$links = array(
	'User Accounts' => 'users',
	'User Roles' => 'user_roles',
	'User Groups' => 'user_groups',
	'User Locations' => 'user_locations'
);

echo $this->element('links', array('links' => $links));

?>