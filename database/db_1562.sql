ALTER TABLE `physical_exam_specifiers` ADD `preselect_flag` TINYINT(1) NOT NULL DEFAULT 0 AFTER `order` ;

--requires db changes from ticket 1538
ALTER TABLE `user_accounts` ADD `default_template_ros` INT UNSIGNED AFTER `dragon_license` ;
ALTER TABLE `user_accounts` ADD `default_template_pe` INT UNSIGNED AFTER `default_template_ros` ;
ALTER TABLE `review_of_system_templates` DROP `default` ;
ALTER TABLE `physical_exam_templates` DROP `default` ;
