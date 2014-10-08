ALTER TABLE `practice_settings` ADD `reminder_notify_json` VARCHAR( 100 ) NULL DEFAULT NULL ;
ALTER TABLE `encounter_plan_labs` ADD `reminder_notify_json` VARCHAR( 100 ) NULL DEFAULT NULL ;
ALTER TABLE `encounter_plan_radiology` ADD `reminder_notify_json` VARCHAR( 100 ) NULL DEFAULT NULL ;
ALTER TABLE `encounter_plan_procedures` ADD `reminder_notify_json` VARCHAR( 100 ) NULL DEFAULT NULL ;
ALTER TABLE `encounter_plan_referrals` ADD `reminder_notify_json` VARCHAR( 100 ) NULL DEFAULT NULL ;

UPDATE practice_settings SET reminder_notify_json = '{"notify_frequency":"14","notify_frequency_type":"day","next_notifiy_date":""}';
