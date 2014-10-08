CREATE TABLE `administration_prescription_auth` (
`prescription_auth_id` INT NOT NULL AUTO_INCREMENT ,
`prescribing_user_id` INT NOT NULL DEFAULT '0',
`authorized_user_id` INT NOT NULL DEFAULT '0',
PRIMARY KEY ( `prescription_auth_id` )
) ENGINE = InnoDB;