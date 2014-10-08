ALTER TABLE `patient_medication_list` ADD `emdeon_medication_id` INT NOT NULL AFTER `dosespot_medication_id`;
ALTER TABLE `patient_medication_list` ADD `emdeon_drug_id` INT( 11 ) NOT NULL AFTER `plan_rx_id`;
ALTER TABLE  `patient_allergies` ADD  `allergy_id_emdeon` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER  `dosespot_allergy_id`;
DROP TABLE IF EXISTS `emdeon_favorite_pharmacy`;
CREATE TABLE IF NOT EXISTS `emdeon_favorite_pharmacy` (
  `favorite_pharmacy_id` int(11) NOT NULL AUTO_INCREMENT,
  `prescriber_id` varchar(15) NOT NULL,
  `pharmacy_id` varchar(100) NOT NULL,
  `pharmacy_orgpreference` varchar(100) NOT NULL,
  `pharmacy_name` varchar(200) NOT NULL,
  `pharmacy_address_1` varbinary(173) NOT NULL,
  `pharmacy_address_2` varbinary(173) NOT NULL,
  `pharmacy_city` varbinary(173) NOT NULL,
  `pharmacy_phone` varbinary(150) NOT NULL,
  `pharmacy_state` varbinary(19) NOT NULL,
  `pharmacy_zip` varbinary(117) NOT NULL,
  `modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_user_id` int(11) NOT NULL,
  PRIMARY KEY (`favorite_pharmacy_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

