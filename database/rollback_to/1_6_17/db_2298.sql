DELETE FROM `clinical_quality_measures` WHERE `func` = 'AdultWeightPopulation2';
UPDATE `clinical_quality_measures` SET `func` = 'AdultWeight', `measure_name` = 'Adult Weight Screening and Follow-Up' WHERE `code` = 'NQF 0421';
