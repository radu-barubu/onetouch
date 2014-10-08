ALTER TABLE `patient_disclosure` ADD `visit_time_count` INT NOT NULL DEFAULT '0' AFTER `visit_summary` ,
ADD `visit_time_unit` VARCHAR( 10 ) NOT NULL AFTER `visit_time_count` ;
