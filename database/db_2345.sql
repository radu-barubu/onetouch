-- Update menus for PE and ROS template
-- so that they inherit the ACL from
-- Preferences Templates menu

UPDATE 
	`system_menus` `s1`, 
	(SELECT `menu_id` FROM `system_menus`
      WHERE `menu_controller` = 'preferences' AND `menu_action` = 'templates')
	AS `s2`
SET `s1`.`menu_inherit` = `s2`.`menu_id`
   WHERE `s1`.`menu_controller` = 'preferences' AND `s1`.`menu_action` = 'ros_template';

UPDATE 
	`system_menus` `s1`, 
	(SELECT `menu_id` FROM `system_menus`
      WHERE `menu_controller` = 'preferences' AND `menu_action` = 'templates')
	AS `s2`
SET `s1`.`menu_inherit` = `s2`.`menu_id`
   WHERE `s1`.`menu_controller` = 'preferences' AND `s1`.`menu_action` = 'pe_template';

