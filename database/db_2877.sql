ALTER TABLE  `administration_point_of_care` ADD  `modifier` VARCHAR( 10 ) NULL AFTER  `provider_vac_code`;
ALTER TABLE  `encounter_point_of_care` ADD  `modifier` VARCHAR( 10 ) NULL AFTER  `provider_vac_code`;
