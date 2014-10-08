ALTER TABLE `patient_documents` ADD `document_test_reviewed` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `patient_documents` ADD `comment` VARCHAR( 255 ) NOT NULL;
