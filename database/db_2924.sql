CREATE TABLE IF NOT EXISTS `health_maintenance_flowsheets` (
  `flowsheet_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `test_name` varchar(200) DEFAULT NULL,
  `test_type` varchar(25) DEFAULT NULL,
  `modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_user_id` int(11) NOT NULL,
  PRIMARY KEY (`flowsheet_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='health maintenance flow sheet' ;

CREATE TABLE IF NOT EXISTS `health_maintenance_flowsheet_data` (
  `flowsheet_data_id` int(11) NOT NULL AUTO_INCREMENT,
  `flowsheet_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `test_result_info` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`flowsheet_data_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='pull from health_maintenance_flowsheet' ;
