CREATE TABLE IF NOT EXISTS `favorite_macros` (
`macro_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`user_id` int(11) NOT NULL DEFAULT '0',
`macro_abbreviation` varchar(5) NOT NULL,
`macro_text` varchar(20000) NOT NULL,
`modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`modified_user_id` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`macro_id`)
) ENGINE=InnoDB;
