CREATE TABLE IF NOT EXISTS `patient_portal_family_favorites` (
  `family_favorite_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `family_favorite_problem` varchar(50) NOT NULL,
  `family_favorite_question` varchar(200) NOT NULL,
  `modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`family_favorite_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

INSERT INTO `patient_portal_family_favorites` (`family_favorite_problem`, `family_favorite_question`, `modified_timestamp`, `modified_user_id`) VALUES
('Diabetes', 'Any family history of Diabetes?', '2013-12-26 01:33:42', 1),
('High Blood Pressure', 'Any family history of High Blood Pressure?', '2013-12-26 01:33:01', 1),
('Heart Disease', 'Any family history of Heart Disease?', '2013-12-26 01:33:23', 1);
