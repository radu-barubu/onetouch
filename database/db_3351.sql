CREATE TABLE IF NOT EXISTS `encounter_plan_rx_changes` (
  `encounter_plan_rx_changes_id` int(11) NOT NULL AUTO_INCREMENT,
  `encounter_id` int(11) NOT NULL DEFAULT '0',
  `medication_list_id` int(11) NOT NULL DEFAULT '0',
  `medication_details` varchar(500) NOT NULL,
  `medication_status` varchar(20) NOT NULL,
  `modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`encounter_plan_rx_changes_id`)
) ENGINE=InnoDB;
