ALTER TABLE  `user_accounts` ADD  `rss_feed` TINYINT( 1 ) NOT NULL DEFAULT  '1' AFTER  `new_lab_notify`;
ALTER TABLE  `user_accounts` ADD  `rss_file` VARCHAR( 125 ) AFTER `rss_feed`;

UPDATE `user_accounts` SET `rss_file` = 'http://www.medscape.com/cx/rssfeeds/2700.xml' WHERE `role_id` != 8;

DROP TABLE IF EXISTS `rss_feeds` ;
CREATE TABLE IF NOT EXISTS `rss_feeds` (
  `rss_id` int(11) NOT NULL AUTO_INCREMENT,
  `rss_name` varchar(125) NOT NULL,
  `rss_file` varchar(125) NOT NULL,
  PRIMARY KEY (`rss_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

INSERT INTO `rss_feeds` (`rss_id`, `rss_name`, `rss_file`) VALUES
(1, 'Allergy & Clinical Immunology', 'http://www.medscape.com/cx/rssfeeds/2667.xml'),
(2, 'Anesthesiology', 'http://www.medscape.com/cx/rssfeeds/4319.xml'),
(3, 'Business of Medicine', 'http://www.medscape.com/cx/rssfeeds/2668.xml'),
(4, 'Cardiology', 'http://www.medscape.com/cx/rssfeeds/2669.xml'),
(5, 'Critical Care', 'http://www.medscape.com/cx/rssfeeds/2670.xml'),
(6, 'Dermatology', 'http://www.medscape.com/cx/rssfeeds/2671.xml'),
(7, 'Diabetes & Endocrinology', 'http://www.medscape.com/cx/rssfeeds/2672.xml'),
(8, 'Emergency Medicine', 'http://www.medscape.com/cx/rssfeeds/2673.xml'),
(9, 'Family Medicine', 'http://www.medscape.com/cx/rssfeeds/2674.xml'),
(10, 'Gastroenterology', 'http://www.medscape.com/cx/rssfeeds/2675.xml'),
(11, 'General Surgery', 'http://www.medscape.com/cx/rssfeeds/2676.xml'),
(12, 'HIV/AIDS', 'http://www.medscape.com/cx/rssfeeds/2677.xml'),
(13, 'Infectious Diseases', 'http://www.medscape.com/cx/rssfeeds/2679.xml'),
(14, 'Internal Medicine', 'http://www.medscape.com/cx/rssfeeds/2680.xml'),
(15, 'Medscape Today', 'http://www.medscape.com/cx/rssfeeds/2682.xml'),
(16, 'Nephrology', 'http://www.medscape.com/cx/rssfeeds/2683.xml'),
(17, 'Neurology', 'http://www.medscape.com/cx/rssfeeds/2684.xml'),
(18, 'Nursing', 'http://www.medscape.com/cx/rssfeeds/2685.xml'),
(19, 'Ob/Gyn & Women''s Health', 'http://www.medscape.com/cx/rssfeeds/2686.xml'),
(20, 'Oncology', 'http://www.medscape.com/cx/rssfeeds/2678.xml'),
(21, 'Ophthalmology', 'http://www.medscape.com/cx/rssfeeds/2687.xml'),
(22, 'Orthopedics', 'http://www.medscape.com/cx/rssfeeds/2688.xml'),
(23, 'Pathology & Lab Medicine', 'http://www.medscape.com/cx/rssfeeds/2689.xml'),
(24, 'Pediatrics', 'http://www.medscape.com/cx/rssfeeds/2690.xml'),
(25, 'Pharmacists', 'http://www.medscape.com/cx/rssfeeds/2691.xml'),
(26, 'Plastic Surgery & Aesthetic Medicine', 'http://www.medscape.com/cx/rssfeeds/4331.xml'),
(27, 'Psychiatry & Mental Health', 'http://www.medscape.com/cx/rssfeeds/2692.xml'),
(28, 'Public Health & Prevention', 'http://www.medscape.com/cx/rssfeeds/2693.xml'),
(29, 'Pulmonary Medicine', 'http://www.medscape.com/cx/rssfeeds/2694.xml'),
(30, 'Radiology', 'http://www.medscape.com/cx/rssfeeds/2695.xml'),
(31, 'Rheumatology', 'http://www.medscape.com/cx/rssfeeds/2696.xml'),
(32, 'Transplantation', 'http://www.medscape.com/cx/rssfeeds/2697.xml'),
(33, 'Urology', 'http://www.medscape.com/cx/rssfeeds/2698.xml'),
(34, 'Medscape Medical News (All Specialties)', 'http://www.medscape.com/cx/rssfeeds/2700.xml');

