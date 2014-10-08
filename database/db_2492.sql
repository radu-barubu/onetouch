ALTER TABLE `encounter_plan_referrals` ADD `assessment_diagnosis` VARCHAR( 200 ) NOT NULL AFTER `diagnosis` ;

UPDATE `encounter_plan_referrals` SET `assessment_diagnosis` = `diagnosis` WHERE 1;