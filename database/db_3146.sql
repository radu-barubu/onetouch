CREATE TABLE IF NOT EXISTS `patient_portal_social_favorites` (
  `social_favorite_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `social_favorite_type` varchar(100) NOT NULL,
  `social_favorite_subtype` varchar(100) NOT NULL,
  `social_favorite_question` varchar(200) NOT NULL,
  `modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`social_favorite_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

INSERT INTO `patient_portal_social_favorites` ( `social_favorite_type`, `social_favorite_subtype`, `social_favorite_question`, `modified_timestamp`, `modified_user_id`) VALUES
( 'Consumption', 'Tobacco', 'Do you smoke?', '2013-12-06 22:43:54', 1),
( 'Consumption', 'Recreational Drugs', 'Do you use any illegal drugs?', '2013-12-06 22:44:30', 1),
( 'Consumption', 'Alcohol', 'Do you drink Alcohol?', '2013-12-06 22:44:48', 1),
( 'Marital Status', '', 'What is your marital status?', '2013-12-06 22:45:29', 1),
( 'Occupation', '', 'What is your occupation?', '2013-12-06 22:45:50', 1);
