ALTER TABLE `icd9` ADD COLUMN `citation_count` INT NOT NULL DEFAULT 0 AFTER `description` ;
ALTER TABLE `cpt4` ADD COLUMN `citation_count` INT NOT NULL DEFAULT 0 AFTER `description` ;
ALTER TABLE `meds_list` ADD COLUMN `citation_count` INT NOT NULL DEFAULT 0 ;
ALTER TABLE `ros_symptoms` ADD COLUMN `citation_count` INT NOT NULL DEFAULT 0 ;
ALTER TABLE  `autocomplete_options` ADD  `autocomplete_orderby` VARCHAR( 15 ) NULL;
UPDATE  `autocomplete_options` SET  `autocomplete_orderby` =  'citation_count' WHERE  `autocomplete_id` =1;
ALTER TABLE `autocomplete_cache` ADD COLUMN `citation_count` INT NOT NULL DEFAULT 0 ;
ALTER TABLE `patient_demographics` ADD COLUMN `citation_count` INT NULL DEFAULT 0 AFTER `kareo_id`;