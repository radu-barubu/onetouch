ALTER TABLE `practice_settings` ADD COLUMN `hl7_report_dir` VARCHAR(100) DEFAULT "/home/medfusion/medfusion/testclient" AFTER `hl7_sftp_in_password` ;
ALTER TABLE `practice_settings` ADD COLUMN `hl7_report_client_id` VARCHAR(40) DEFAULT NULL AFTER `hl7_report_dir` ;
ALTER TABLE `practice_settings` ADD COLUMN `hl7_report_lab_logo` VARCHAR(100) DEFAULT "/img/labs/clearpointlogo.png" AFTER `hl7_report_client_id` ;
