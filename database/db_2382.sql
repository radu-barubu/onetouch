ALTER TABLE `encounter_plan_free_text` CHANGE `diagnosis` `diagnosis` VARCHAR( 500 ) NOT NULL;
ALTER TABLE `encounter_plan_free_text` CHANGE `free_text` `free_text` VARCHAR(10000) NOT NULL DEFAULT '';