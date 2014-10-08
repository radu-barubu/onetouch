<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$currentDomain = 'https://' . $hostName;

$action = $this->Html->url(array(
    'controller' => 'help',
    'action' => 'confirm_registration',
    'token' => $token,
));


$url = $currentDomain . $action;


?>
We received a patient portal registration request to this address. 

<p>In order to confirm your email address and continue the registration process, please <a href="<?php echo $url ?>">click here</a> or copy and paste the following link into your browser address bar:</p>

<a href="<?php echo $url ?>"><?php echo $url; ?></a>

