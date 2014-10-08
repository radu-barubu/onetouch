ALTER TABLE `practice_encounter_tabs` ADD `name` VARCHAR( 100 ) NOT NULL ;
UPDATE `practice_encounter_tabs` SET `name` = `tab` WHERE 1;
ALTER TABLE `practice_encounter_tabs` ADD `sub_headings` VARCHAR( 1000 ) NULL DEFAULT NULL ;
UPDATE `practice_encounter_tabs` SET `sub_headings` = '{"Medical History":{"name":"Medical History","hide":"0"},"Surgical History":{"name":"Surgical History","hide":"0"},"Social History":{"name":"Social History","hide":"0"},"Family History":{"name":"Family History","hide":"0"},"Ob\/Gyn History":{"name":"Ob\/Gyn History","hide":"0"}}' WHERE `tab` = 'HX';
