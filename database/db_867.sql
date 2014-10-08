-- #867 Route the user back to display table after saving a new item in Outside Radiology.
ALTER TABLE `encounter_frequent_prescribed` CHANGE `referral_list_id` `referral_list_id` INT UNSIGNED NOT NULL;
