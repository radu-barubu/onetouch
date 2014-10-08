CREATE TABLE IF NOT EXISTS `administration_superbill_advanced` (
  `advanced_level_id` int(11) NOT NULL AUTO_INCREMENT,
  `advanced_level_code` varchar(5) NOT NULL,
  `advanced_level_description` varchar(150) DEFAULT NULL,
  `modified_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`advanced_level_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Encounter Superbill Advanced Codes'  ;

INSERT INTO `administration_superbill_advanced` (`advanced_level_code`, `advanced_level_description`, `modified_timestamp`, `modified_user_id`) VALUES
( '99050', 'After posted hours', '2013-03-26 21:47:34', 1),
( '99051', 'Evening/weekend appointment',  '2013-03-26 21:47:11', 1),
('G0180', 'Home health certification',  '2013-03-26 21:48:00', 1),
( 'G0179', 'Home health recertification',  '2013-03-26 21:48:29', 1),
( '99024', 'Post-op follow-up',  '2013-03-26 21:48:47', 1),
( '99354', 'Prolonged/30-74 min',  '2013-03-26 21:48:47', 1),
( '99080', 'Special reports/forms',  '2013-03-26 21:48:47', 1),
( '99455', 'Disability/Workers comp',  '2013-03-26 21:48:47', 1),
( '99406', 'Smoking Cessation Counciling 3-10 min',  '2013-03-26 21:48:47', 1),
( '99407', 'Smoking Cessation Counciling 11+ min',  '2013-03-26 21:48:47', 1);



