
CREATE TABLE IF NOT EXISTS `administration_point_of_care_categories` (
  `point_of_care_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `point_of_care_type` varchar(20) NOT NULL,
  `point_of_care_category` varchar(10000) NOT NULL,
  `modified_timestamp` timestamp NOT NULL,
  `modified_user_id` int(11) NOT NULL,
  PRIMARY KEY (`point_of_care_category_id`)
) ;

ALTER TABLE `administration_point_of_care` ADD `category` VARCHAR( 100 ) NOT NULL AFTER `order_type` ;

