CREATE TABLE IF NOT EXISTS `patient_portal_medical_favorites` (
  `diagnosis_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diagnosis` varchar(500) NOT NULL,
  `modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_user_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`diagnosis_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `patient_portal_surgical_favorites` (
  `surgeries_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `surgeries` varchar(500) NOT NULL,
  `modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_user_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`surgeries_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `patient_portal_surgical_favorites` (`surgeries`, `modified_timestamp`, `modified_user_id`, `user_id`) VALUES
( 'Appendectomy (Appendix Removed)', '2013-09-23 03:09:04', 1, 1),
( 'Cholecystectomy (Gallbladder Removal)', '2013-09-23 03:09:34', 1, 1),
( 'Cataract surgery', '2013-09-23 03:09:49', 1, 1),
( 'Cesarean section', '2013-09-23 03:10:01', 1, 1),
( 'Coronary Artery Bypass', '2013-09-23 03:10:25', 1, 1),
( 'Hemorrhoidectomy (Hemorrhoid Removal)', '2013-09-23 03:10:42', 1, 1),
( 'Hysterectomy', '2013-09-23 03:10:51', 1, 1),
( 'Back Surgery', '2013-09-23 03:11:05', 1, 1),
( 'Mastectomy', '2013-09-23 03:11:14', 1, 1),
( 'Shoulder Surgery', '2013-09-23 03:11:48', 1, 1),
( 'Hip Surgery', '2013-09-23 03:11:56', 1, 1),
( 'Knee Surgery', '2013-09-23 03:12:03', 1, 1);

INSERT INTO `patient_portal_medical_favorites` ( `diagnosis`, `modified_timestamp`, `modified_user_id`, `user_id`) VALUES
( 'High Blood Pressure', '2013-09-23 03:00:28', 1, 1),
( 'Diabetes', '2013-09-23 03:00:34', 1, 1),
( 'Allergies', '2013-09-23 03:04:42', 1, 1),
( 'Asthma', '2013-09-23 03:04:56', 1, 1),
( 'COPD', '2013-09-23 03:05:04', 1, 1),
( 'Kidney Disease', '2013-09-23 03:05:21', 1, 1),
( 'Cancer', '2013-09-23 03:05:38', 1, 1),
( 'Heart Disease', '2013-09-23 03:05:52', 1, 1),
('High Cholesterol', '2013-09-23 03:06:09', 1, 1),
( 'Anxiety', '2013-09-23 03:07:17', 1, 1),
( 'Stroke', '2013-09-23 03:16:01', 1, 1);
