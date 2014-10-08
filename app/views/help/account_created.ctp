<?php


$username = $this->Session->read('new_username');
$this->Session->delete('new_username');
?>
<div>
    <form id="frm_new_reg" class="login" method="post" accept-charset="utf-8">
        <p>
            You successfully created your account.
        </p>
        <p>
            You may login with the user name <strong><?php echo $username; ?></strong>
            using the password you provided during registration.
        </p>
        
        <p style="text-align: center;">
            <?php echo $this->Html->link('Click here to login', array('controller' => 'administration', 'action' => 'login', 'who' => $username), array('class' => 'btn no-float')); ?> 
        </p>
    </form>
</div>
