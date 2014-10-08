CREATE TABLE IF NOT EXISTS `schedule_appointment_requests` (
  `appointment_request_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_date` date NOT NULL,
  `patient_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `encounter_id` int(11) NOT NULL,
  `return_time` varchar(10) NOT NULL,
  `return_period` varchar(45) NOT NULL,
  `status` varchar(25) NOT NULL,
  `priority` varchar(10) NOT NULL,
  PRIMARY KEY (`appointment_request_id`)
) ENGINE=InnoDB COMMENT='store appointment requests';

