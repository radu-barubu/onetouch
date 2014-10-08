<?php
$tabs =  array(
	'Email Setup' => array('setup' => 'email_setup'),
	'General' => array('Adminitration'=> 'practice_profile'),
);

echo $this->element('tabs',array('tabs'=> $tabs));
?>
