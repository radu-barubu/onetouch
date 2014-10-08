CREATE TABLE IF NOT EXISTS `hl7_incoming_messages` (
`incoming_message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`record_type` varchar(75),
`record_id` int(10) unsigned,
`message_type` varchar(3),
`event_type` varchar(3),
`sending_application` varchar(75),
`message_text` varbinary(20000),
`log` varbinary(20000),
`modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`incoming_message_id`),
INDEX (`record_type`,`record_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `hl7_outgoing_messages` (
`outgoing_message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`record_type` varchar(75),
`record_id` int(10) unsigned,
`message_type` varchar(3),
`event_type` varchar(3),
`receiving_application` varchar(75),
`message_text` varbinary(20000),
`modified_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`outgoing_message_id`),
INDEX (`record_type`,`record_id`)
) ENGINE=InnoDB;
