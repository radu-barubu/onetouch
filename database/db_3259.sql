ALTER TABLE `form_template` ADD `published` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `template_content` ;
UPDATE `form_template` SET `published` = 1 WHERE 1 ;