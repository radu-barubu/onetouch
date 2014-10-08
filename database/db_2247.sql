CREATE TABLE IF NOT EXISTS `patient_checkin_notes` (
  `patient_checkin_id` int(11) NOT NULL AUTO_INCREMENT,
  `calendar_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `allergies` varchar(1000) DEFAULT NULL,
  `medications` varchar(1000) DEFAULT NULL,
  `problem_list` varchar(1000) DEFAULT NULL,
  `checkin_complete` tinyint(1) NOT NULL DEFAULT '0',
  `modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`patient_checkin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;