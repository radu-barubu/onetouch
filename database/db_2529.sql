ALTER TABLE  `practice_settings` ADD  `hl7_engine` VARCHAR( 25 ) NULL AFTER  `faxage_tagname` ,
ADD  `hl7_customer_name` VARCHAR( 25 ) NULL AFTER  `hl7_engine` ,
ADD  `hl7_receiver` VARCHAR( 25 ) NULL AFTER  `hl7_customer_name`;
