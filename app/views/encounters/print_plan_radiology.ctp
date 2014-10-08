<?php echo $this->element('print_rx_header'); ?>
		Procedure Name: <b><?php echo $plan_radiology['EncounterPlanRadiology']['procedure_name']; ?></b><br />
		<?php echo (!empty($plan_radiology['EncounterPlanRadiology']['number_of_views'])) ?  '<b>Views: </b>'.$plan_radiology['EncounterPlanRadiology']['number_of_views'].'<br>':''; ?>
		<?php echo (!empty($plan_radiology['EncounterPlanRadiology']['cpt'])) ?  '<b>CPT: </b>'.$plan_radiology['EncounterPlanRadiology']['cpt'].'<br>':''; ?>
		<?php echo (!empty($plan_radiology['EncounterPlanRadiology']['reason']))? 'Reason: '. $plan_radiology['EncounterPlanRadiology']['reason'].'<br>':''; ?>
		<?php if($plan_radiology['EncounterPlanRadiology']['body_site1']) {?><b>Body Site #1:</b> <?php echo $plan_radiology['EncounterPlanRadiology']['body_site1']; ?><br /><?php }?>
		<?php echo (!empty($plan_radiology['EncounterPlanRadiology']['priority'])) ?  '<b>Priority: </b>'.ucfirst($plan_radiology['EncounterPlanRadiology']['priority']).'<br>':''; ?>
 <?php echo (!empty($plan_radiology['EncounterPlanRadiology']['patient_instruction'])) ?  '<b>Patient Instruction: </b>'.ucfirst($plan_radiology['EncounterPlanRadiology']['patient_instruction']).'<br>':''; ?>
		<?php if($plan_radiology['EncounterPlanRadiology']['comment']) {?><b>Comment: </b> <?php echo $plan_radiology['EncounterPlanRadiology']['comment']; }?>	

<?php echo $this->element('print_rx_footer'); ?>
