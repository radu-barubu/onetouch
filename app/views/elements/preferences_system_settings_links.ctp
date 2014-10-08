<?php



$links = array();
$links['Account'] = 'system_settings';

if(EMR_Roles::isCurrentRoleMedicalPersonnel()):
	$links['User Options'] = 'user_options';
endif; 

if((@$emergency_access_type && $emergency_access_user) or $_SESSION['UserAccount']['user_id'] == '1'):
	$links['Emergency Access'] = 'emergency_access';
endif;

echo $this->element('links', array('links' => $links));


?>