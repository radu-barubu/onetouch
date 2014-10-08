<?php

// Get date format
App::import('Model', 'PracticeProfile');
$pf = new PracticeProfile();
$pProfile = $pf->find('first');
$type_of_practice = $pProfile['PracticeProfile']['type_of_practice'];

?>
<?php if (empty($patient_checkin_id)) {
	 echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 105)); 

?>
                        <?php echo $html->link('Medical History', array('action' => 'hx_medical', 'patient_id'=> $patient_id), array('class' => 'btn section_btn')); ?>
			<?php echo $html->link('Surgical History', array('action' => 'hx_surgical', 'patient_id'=> $patient_id), array('class' => 'btn section_btn')); ?>
			<?php echo $html->link('Social History', array('action' => 'hx_social', 'patient_id'=> $patient_id), array('class' => 'btn section_btn')); ?>
			<?php echo $html->link('Family History', array('action' => 'hx_family', 'patient_id'=> $patient_id), array('class' => 'btn section_btn')); ?>     
			<?php	if ( intval($pProfile['PracticeProfile']['obgyn_feature_include_flag']) == 1 and $__patient['gender'] == "F"): ?>
			<?php echo $html->link('Ob/Gyn History', array('action' => 'hx_obgyn', 'patient_id'=> $patient_id), array('class' => 'btn section_btn')); ?>     
                        <?php endif;?> 
<?php } ?>
