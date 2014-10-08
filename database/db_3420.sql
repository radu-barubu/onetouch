UPDATE `icd10` SET `description` = REPLACE(`description` , ',', ' -') WHERE `description` LIKE '%,%';
