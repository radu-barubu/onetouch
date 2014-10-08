CREATE TABLE IF NOT EXISTS `favorite_surgeries` (
`surgeries_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`surgeries` varchar(500) NOT NULL,
`modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`modified_user_id` int(11) NOT NULL DEFAULT '0',
`user_id` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`surgeries_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;