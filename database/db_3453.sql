ALTER TABLE  `administration_point_of_care` ADD  `injection_unit` INT NOT NULL DEFAULT  '1' AFTER  `injection_dose` ;
ALTER TABLE  `encounter_point_of_care` ADD  `injection_unit` INT NOT NULL AFTER  `injection_dose` ; 


