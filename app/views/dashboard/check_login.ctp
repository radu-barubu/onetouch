<?php if ($this->Session->check('UserAccount')): ?>
<?php else:?> 
    window.location.href = '/';
<?php endif; ?>

