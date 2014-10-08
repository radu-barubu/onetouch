ALTER TABLE `patient_ob_gyn_history` ADD `abnormal_pap_smear_text` VARCHAR( 1000 ) NULL DEFAULT NULL AFTER `abnormal_pap_smear_date` ;

ALTER TABLE `patient_ob_gyn_history` ADD `abnormal_irregular_bleeding_text` VARCHAR( 1000 ) NULL DEFAULT NULL AFTER `abnormal_irregular_bleeding_date` ;

ALTER TABLE `patient_ob_gyn_history` CHANGE `age_started_period` `age_started_period` INT( 2 ) NULL DEFAULT NULL ;

ALTER TABLE `patient_ob_gyn_history` ADD `deliveries` VARCHAR( 10000 ) NULL DEFAULT NULL ;