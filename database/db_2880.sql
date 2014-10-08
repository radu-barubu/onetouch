ALTER TABLE  `encounter_plan_labs` CHANGE  `priority`  `priority` VARCHAR( 100 ) NOT NULL DEFAULT  'routine';
ALTER TABLE  `encounter_plan_radiology` CHANGE  `priority`  `priority` VARCHAR( 100 )  NOT NULL DEFAULT  'routine';
