UPDATE `appointment_reminders` SET `message`="You have a doctor appointment scheduled for [Date]. Please call us in advance if you need to reschedule it. Thank you." WHERE `messaging`="Pending";
UPDATE `setup_details` SET `message_5` = 'You have a health maintenance activity targeted [Date]. Please call our office for more information and to schedule an appointment if needed.' WHERE `setup_details`.`detail_id` = 1;
UPDATE `setup_details` SET `message_6` = 'You have a health maintenance activity targeted [Date]. Please call our office for more information and to schedule an appointment if needed.' WHERE `setup_details`.`detail_id` = 1;