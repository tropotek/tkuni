-- ------------------------------------------------------
-- Author: Michael Mifsud
-- Date: 06/04/17
-- ------------------------------------------------------


-- --------------------------------------
-- Change all passwords to 'password' for debug mode
-- --------------------------------------
-- UPDATE `user` SET `display_name` = `name`;
-- UPDATE `user` SET `hash` = MD5(CONCAT(`id`, IFNULL(`institution_id`, 0), `username`, `email`));

UPDATE `user` SET `password` = MD5(CONCAT('password', `hash`));

-- --------------------------------------
-- Disable Domains for institutions
UPDATE `institution` SET `domain` = '';

-- Disable the LDAP plugin for institutions
DELETE FROM `plugin_zone` WHERE `plugin_name` LIKE 'plg-ldap' AND `zone_name` LIKE 'institution';







