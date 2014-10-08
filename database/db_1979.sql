ALTER TABLE `encounter_master` ADD `encounter_type_id` INT NOT NULL DEFAULT '1';

CREATE TABLE `encounter_type` (
`encounter_type_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`encounter_type_desc` VARCHAR( 50 ) NOT NULL
);

INSERT INTO 
	`encounter_type` (`encounter_type_id` , `encounter_type_desc`)
VALUES 
	(NULL , 'Office Visit'), 
	(NULL , 'Phone Call');