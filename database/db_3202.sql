CREATE TABLE IF NOT EXISTS `patient_document_types` (
  `document_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `document_types` varchar(3000) NOT NULL,
  `modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_user_id` int(11) NOT NULL,
  PRIMARY KEY (`document_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
