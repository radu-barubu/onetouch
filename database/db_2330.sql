-- move patient_instruction data into education comment box
UPDATE `encounter_plan_advice_instructions` SET `patient_education_comment` = CONCAT(`patient_education_comment`, " ", `patient_instruction`);
-- remove it
ALTER TABLE `encounter_plan_advice_instructions`  DROP `patient_instruction`;
