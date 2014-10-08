ALTER TABLE  `messaging_phone_calls` ADD  `home_phone_number` VARCHAR( 20 ) NULL AFTER  `call` ,
ADD  `work_phone_number` VARCHAR( 20 ) NULL AFTER  `home_phone_number` ,
ADD  `mobile_phone_number` VARCHAR( 20 ) NULL AFTER  `work_phone_number` ,
ADD  `other_phone_number` VARCHAR( 20 ) NULL AFTER  `mobile_phone_number`;
