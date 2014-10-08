<?php echo $this->element('print_rx_header'); ?>
		Procedure Name: <b><?php echo $plan_procedures['EncounterPlanProcedure']['test_name']; ?></b><br />
		<?php echo (!empty($plan_procedures['EncounterPlanProcedure']['reason']))? 'Reason: '. $plan_procedures['EncounterPlanProcedure']['reason'].'<br />':''; ?>
		<?php if($plan_procedures['EncounterPlanProcedure']['body_site']) {?><b>Body Site:</b> <?php echo $plan_procedures['EncounterPlanProcedure']['body_site']; echo (!empty($plan_procedures['EncounterPlanProcedure']['laterality']))? ', '.$plan_procedures['EncounterPlanProcedure']['laterality']:''; ?><br /><?php }?>
                <?php echo (!empty($plan_procedures['EncounterPlanProcedure']['cpt']))? 'CPT: '. $plan_procedures['EncounterPlanProcedure']['cpt'].'<br />':''; ?>
		<?php if($plan_procedures['EncounterPlanProcedure']['comment']) {?><b>Comment: </b> <?php echo $plan_procedures['EncounterPlanProcedure']['comment']; }?>	
<?php echo $this->element('print_rx_footer'); ?>
