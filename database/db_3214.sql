UPDATE `appointment_reminders` SET  `message` =  'You have an appointment scheduled on [Date] at [Time].' WHERE `message` = 'You have a doctor appointment scheduled on [Date] at [Time]. Please call us in advance if you need to reschedule it. Thank you.' AND `messaging` = 'Pending';

UPDATE  `setup_details` SET  `message_1` =  'You have a doctor appointment scheduled on [Date] at [Time].' WHERE  `setup_details`.`detail_id` =1;

UPDATE  `appointment_setup_details` SET  `message_1` =  'You have a doctor appointment scheduled on [Date] at [Time].' WHERE `appointment_setup_details`.`detail_id` =1;
