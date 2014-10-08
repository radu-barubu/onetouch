<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$currentDomain = 'https://' . $hostName;

$action = $this->Html->url(array(
    'controller' => 'administration',
    'action' => 'login',
));


$url = $currentDomain . $action;


?>
Congratulations! Your patient portal account has been successfully created!
<p>You may login with the username <strong><?php echo $username; ?></strong> and the password you provided during registration.
<p>To login, use the following link: <a href="<?php echo $url ?>"><?php echo $url; ?></a>

