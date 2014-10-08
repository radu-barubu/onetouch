ALTER TABLE `emdeon_prescriptions` ADD `rxnorm` varchar(500) NOT NULL DEFAULT '' AFTER `drug_id`;
ALTER TABLE `patient_allergies` ADD `rxnorm` VARCHAR( 500 ) NULL AFTER `agent`;

ALTER TABLE `emdeon_order_diagnosis` ADD `icd_10_cm_code` VARCHAR(30) NOT NULL DEFAULT '' AFTER `icd_9_cm_code`;
ALTER TABLE `emdeon_prescriptions` ADD `icd_10_cm_code` VARCHAR(30) NOT NULL DEFAULT '' AFTER `icd_9_cm_code`;
ALTER TABLE `emdeon_favorite_prescriptions` ADD `icd_10_cm_code` VARCHAR(30) NOT NULL DEFAULT '' AFTER `icd_9_cm_code`;

ALTER TABLE `emdeon_prescriptions` ADD `encounter_id` INT(11) NOT NULL DEFAULT '0' AFTER `rx_unique_id`;

ALTER TABLE `emdeon_favorite_prescriptions` ADD `user_id` INT(11) NOT NULL DEFAULT '0' AFTER `rx_preference_id`;

ALTER TABLE `patient_preferences` ADD `emdeon_pharmacy_id` INT(11) NOT NULL DEFAULT '0' AFTER `pcp`;

ALTER TABLE `emdeon_favorite_prescriptions` ADD `deacode` INT(11) NOT NULL DEFAULT '0' AFTER `prescriber_id`;