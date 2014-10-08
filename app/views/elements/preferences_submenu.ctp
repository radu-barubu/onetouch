<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!isset($currentAction)) {
    $currentAction = 'favorite_diagnosis';
}

$links = array(
    'Common Complaints' => 'common_complaints',
    'Diagnosis' => 'favorite_diagnosis',
    'Lab Test' => 'favorite_lab_tests',
    'Test Codes' => 'favorite_test_codes',
    'Test Groups' => 'favorite_test_groups',
    'Prescriptions' => 'favorite_prescriptions',
    
    
);

?>
			 <div class="title_text">
                             <?php foreach($links as $title => $action): ?> 
                                <?php if ($action == $currentAction): ?> 
                                <div class="title_item active"><?php echo $title; ?></div>
                                <?php else: ?> 
			 	<?php echo $html->link($title, array('action' => $action)); ?>
                                <?php endif;?> 
                             <?php endforeach;?> 
			 </div>