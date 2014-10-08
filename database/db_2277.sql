ALTER TABLE `patient_demographics` DROP `mother`;
ALTER TABLE  `patient_demographics` ADD  `custom_patient_identifier` VARBINARY( 50 ) NULL AFTER  `emergency_phone`;
