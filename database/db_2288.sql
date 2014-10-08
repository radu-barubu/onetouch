-- Create Practice Encounter Type table
CREATE TABLE `practice_encounter_types` (
`encounter_type_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 100 ) NOT NULL ,
`readonly` tinyint(1) NOT NULL DEFAULT '0',
`created_timestamp` TIMESTAMP NULL DEFAULT NULL ,
`modified_timestamp` TIMESTAMP NULL DEFAULT NULL ,
`modified_user_id` INT NOT NULL DEFAULT '0'
) ;

-- Insert Default and Phone encounter types
INSERT INTO `practice_encounter_types` (
`encounter_type_id` ,
`name` ,
`readonly`,
`created_timestamp` ,
`modified_timestamp` ,
`modified_user_id`
)
VALUES 
 (1 , 'Default', 1, NULL , NULL, '0' ),
 (2 , 'Phone', 1, NULL , NULL, '0' );

-- Add field to association encounter tabs to with encounter types
ALTER TABLE `practice_encounter_tabs` ADD `encounter_type_id` INT NOT NULL DEFAULT '1';

-- Add field to association appointment types to with encounter types
ALTER TABLE `schedule_appointment_types` ADD `encounter_type_id` INT NOT NULL DEFAULT '1';

-- Create tabs for Phone encounter type
INSERT INTO `practice_encounter_tabs` (`tab`, `order`, `modified_timestamp`, `modified_user_id`, `hide`, `encounter_type_id`) VALUES
('Summary', 0, '2013-02-02 14:32:58', 210, 0, 2),
('CC', 1, '2013-02-02 14:32:58', 210, 0, 2),
('HPI', 2, '2013-02-02 14:32:58', 210, 0, 2),
('HX', 3, '2013-02-02 14:32:58', 210, 1, 2),
('Meds & Allergy', 4, '2013-02-02 14:32:58', 210, 0, 2),
('ROS', 5, '2013-02-02 14:32:59', 210, 1, 2),
('Vitals', 6, '2013-02-02 14:32:59', 210, 1, 2),
('PE', 7, '2013-02-02 14:32:59', 210, 1, 2),
('POC', 8, '2013-02-02 14:32:59', 210, 1, 2),
('Results', 9, '2013-02-02 14:32:59', 210, 0, 2),
('Assessment', 10, '2013-02-02 14:32:59', 210, 0, 2),
('Plan', 11, '2013-02-02 14:32:59', 210, 1, 2),
('Superbill', 12, '2013-02-02 14:32:59', 210, 0, 2);

-- Associate Phone Appointment Type to Phone Encounter type
UPDATE `schedule_appointment_types` SET `encounter_type_id` = 2 WHERE `type` LIKE '%Phone%';
