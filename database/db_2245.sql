UPDATE `patient_demographics` SET `dosespot_patient_id`=NULL WHERE des_decrypt(`dosespot_patient_id`) like '%SingleSignOnClinicId';
