ALTER TABLE `encounter_point_of_care` ADD `procedure_administered_by` VARCHAR( 100 ) NOT NULL AFTER `procedure_unit` ;
ALTER TABLE `encounter_point_of_care` ADD `drug_administered_by` VARCHAR( 100 ) NOT NULL AFTER `drug_route` ;
