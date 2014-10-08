-- #293 Kareo
ALTER TABLE `patient_insurance_info` ADD `kareo_insurance_id` INT NULL DEFAULT NULL AFTER `use_default` ;
ALTER TABLE `practice_settings` ADD `kareo_schedule_adjust_time` TINYINT NOT NULL DEFAULT '0' AFTER `kareo_practice_name` ;