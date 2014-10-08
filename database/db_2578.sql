ALTER TABLE `acls`
 ADD INDEX  (`role_id`);

ALTER TABLE `acls`
 ADD INDEX  (`menu_id`);

ALTER TABLE `appointment_reminders`
 ADD INDEX  (`patient_id`);

ALTER TABLE `clinical_alerts_management`
 ADD INDEX (`ca_alert_id`);

ALTER TABLE `emdeon_orderinsurance`
 ADD INDEX  (`patient_rel_to_insured`);

ALTER TABLE `encounter_addendum`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_assessment`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_assessment_summaries`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_chief_complaint`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_frequent_prescribed`
 ADD INDEX  (`referral_list_id`);

ALTER TABLE `encounter_master`
 ADD INDEX  (`patient_id`);

ALTER TABLE `encounter_master`
 ADD INDEX (`calendar_id`);

ALTER TABLE `encounter_physical_exam`
 ADD INDEX (`encounter_id`);

ALTER TABLE `encounter_physical_exam_details`
 ADD INDEX  (`physical_exam_id`);

ALTER TABLE `encounter_physical_exam_images`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_physical_exam_texts`
 ADD INDEX  (`physical_exam_id`);

ALTER TABLE `encounter_plan_free_text`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_plan_health_maintenance`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_plan_health_maintenance_enrollment`
 ADD INDEX  (`plan_id`);

ALTER TABLE `encounter_plan_labs`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_plan_procedures`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_plan_procedures`
 ADD INDEX  (`patient_id`);

ALTER TABLE `encounter_plan_radiology`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_plan_radiology`
 ADD INDEX  (`patient_id`);

ALTER TABLE `encounter_plan_referrals`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_plan_referrals`
 ADD INDEX  (`patient_id`);

ALTER TABLE `encounter_plan_rx`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_plan_status`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_point_of_care`
 ADD INDEX  (`patient_id`);

ALTER TABLE `encounter_point_of_care`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_superbill`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `encounter_vitals`
 ADD INDEX (`encounter_id`);

ALTER TABLE `form_data`
 ADD INDEX  (`form_template_id`);

ALTER TABLE `form_data`
 ADD INDEX (`patient_id`);

ALTER TABLE `health_maintenance_actions`
 ADD INDEX  (`plan_id`);

ALTER TABLE `messaging_messages`
 ADD INDEX  (`sender_id`);

ALTER TABLE `messaging_messages`
 ADD INDEX  (`recipient_id`);

ALTER TABLE `messaging_messages`
 ADD INDEX  (`patient_id`);

ALTER TABLE `messaging_phone_calls`
 ADD INDEX  (`patient_id`);

ALTER TABLE `patient_allergies`
 ADD INDEX  (`patient_id`);

ALTER TABLE `patient_guarantors`
 ADD INDEX  (`patient_id`);

ALTER TABLE `patient_lab_results`
 ADD INDEX (`patient_id`);

ALTER TABLE `patient_lab_results`
 ADD INDEX  (`encounter_id`);

ALTER TABLE `patient_medication_list`
 ADD INDEX  (`patient_id`);

ALTER TABLE `patient_medication_refills`
 ADD INDEX  (`medication_list_id`);

ALTER TABLE `patient_medication_refills`
 ADD INDEX  (`patient_id`);

ALTER TABLE `patient_orders`
 ADD INDEX  (`patient_id`);

ALTER TABLE `patient_problem_list`
    ADD INDEX  (`patient_id`);

ALTER TABLE `patient_reminders`
 ADD INDEX (`patient_id`);

ALTER TABLE `review_of_system_categories`
 ADD INDEX  (`template_id`);

ALTER TABLE `schedule_calendars`
 ADD INDEX  (`patient_id`);

ALTER TABLE `schedule_calendars`
 ADD INDEX  (`provider_id`);

ALTER TABLE `user_accounts`
 ADD INDEX  (`role_id`);

ALTER TABLE `user_locations`
 ADD INDEX  (`user_id`);
