ALTER TABLE `emdeon_lab_results` CHANGE `unique_id` `unique_id` VARCHAR(150);
ALTER TABLE `practice_settings` ADD COLUMN `macpractice_host` VARCHAR(50) DEFAULT NULL AFTER `emdeon_password` ;
ALTER TABLE `practice_settings` ADD COLUMN `macpractice_port` VARCHAR(10) DEFAULT NULL AFTER `macpractice_host` ;
ALTER TABLE `practice_settings` ADD COLUMN `macpractice_password` VARCHAR(40) DEFAULT NULL AFTER `macpractice_port` ;
