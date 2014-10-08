<?php echo $this->element('print_rx_header'); ?>
		Test Name: <b><?php echo $plan_labs['EncounterPlanLab']['test_name']; ?></b><br />
		<?php echo (!empty($plan_labs['EncounterPlanLab']['reason']))? 'Reason: '. $plan_labs['EncounterPlanLab']['reason'].'<br />':''; ?>
		<?php echo (!empty($plan_labs['EncounterPlanLab']['cpt']))? 'CPT: '. $plan_labs['EncounterPlanLab']['cpt'].'<br />':''; ?>
		<?php echo (!empty($plan_labs['EncounterPlanLab']['priority']))? '<b>Priority: </b>'.ucfirst($plan_labs['EncounterPlanLab']['priority']).'<br />':''; ?>
		<?php echo (!empty($plan_labs['EncounterPlanLab']['patient_instruction']))? '<b>Patient Instruction: </b>'. $plan_labs['EncounterPlanLab']['patient_instruction'].' <br />':''; ?>
		<?php if($plan_labs['EncounterPlanLab']['comment']){?><b>Comment: </b> <?php echo $plan_labs['EncounterPlanLab']['comment']; }?>	
<?php echo $this->element('print_rx_footer'); ?>
