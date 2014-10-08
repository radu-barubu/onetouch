ALTER TABLE `messaging_messages`
CHANGE `created_timestamp` `created_timestamp` TIMESTAMP NULL DEFAULT NULL ,
CHANGE `modified_timestamp` `modified_timestamp` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;
