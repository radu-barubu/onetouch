ALTER TABLE `hl7_outgoing_messages` ADD `event_timestamp` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `hl7_outgoing_messages` ADD `version` VARCHAR(10) NULL DEFAULT NULL;
ALTER TABLE `hl7_incoming_messages` ADD `event_timestamp` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `hl7_incoming_messages` ADD `version` VARCHAR(10) NULL DEFAULT NULL;