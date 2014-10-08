UPDATE `acls` SET `acl_read` = 0, `acl_write` = 0 WHERE `role_id` = 8 AND `menu_id` = 54;

INSERT INTO `system_menus` (
`menu_id` ,
`menu_name` ,
`menu_controller` ,
`menu_action` ,
`menu_variation` ,
`menu_url` ,
`menu_options` ,
`menu_group` ,
`group_options` ,
`menu_parent` ,
`menu_inherit` ,
`menu_show` ,
`menu_show_roles` ,
`menu_enable_link` ,
`system_admin_only` ,
`modified_timestamp` ,
`modified_user_id`
)
VALUES (
NULL , 'Contact Us', 'help', 'contact_us', '', '', 'R,NA', '0', '[]', '50', '0', '1', '1', '1', '0', '0000-00-00 00:00:00', '0'
);


INSERT INTO `acls` (
`menu_id`,
`role_id`,
`acl_read`,
`acl_write`,
`modified_timestamp`,
`modified_user_id`
)
VALUES 
(122, 8 , 0, 0, NOW(), 1),
(124, 8 , 1, 1, NOW(), 1),
(124, 1 , 0, 0, NOW(), 1),
(124, 2 , 0, 0, NOW(), 1),
(124, 3 , 0, 0, NOW(), 1),
(124, 4 , 0, 0, NOW(), 1),
(124, 5 , 0, 0, NOW(), 1),
(124, 6 , 0, 0, NOW(), 1),
(124, 7 , 0, 0, NOW(), 1),
(124, 9 , 0, 0, NOW(), 1),
(124, 10 , 0, 0, NOW(), 1),
(124, 11 , 0, 0, NOW(), 1),
(124, 12 , 0, 0, NOW(), 1),
(124, 13 , 0, 0, NOW(), 1),
(124, 14 , 0, 0, NOW(), 1);

