CREATE TABLE IF NOT EXISTS `favorite_medical` (
  `diagnosis_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diagnosis` varchar(500) NOT NULL,
  `modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_user_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`diagnosis_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- copy any pre-existing favorites
INSERT INTO `favorite_medical` SELECT * FROM `favorite_diagnosis`;
