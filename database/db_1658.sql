ALTER TABLE `practice_profile` ADD COLUMN `obgyn_feature_include_flag` TINYINT(1) NOT NULL DEFAULT 0 AFTER `payment_option` ;

ALTER TABLE 
	`user_accounts` 
ADD 
	`override_practice_name` VARCHAR( 50 ) NULL DEFAULT NULL ,
ADD 
	`override_practice_type` VARCHAR( 50 ) NULL DEFAULT NULL ,
ADD 
	`override_practice_logo` VARCHAR( 2000 ) NULL DEFAULT NULL ,
ADD 
	`override_obgyn_feature` TINYINT( 1 ) NOT NULL DEFAULT '0';