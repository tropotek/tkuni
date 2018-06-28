-- ---------------------------------
-- Install SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


-- ----------------------------
--  user
-- ----------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `institution_id` INT(10) UNSIGNED DEFAULT NULL,
  `uid` VARCHAR(128) NOT NULL DEFAULT '',
  `username` VARCHAR(64) NOT NULL DEFAULT '',
  `password` VARCHAR(128) NOT NULL DEFAULT '',
  -- ROLES: 'admin', 'client', 'staff', 'student
  `role` VARCHAR(255) NOT NULL DEFAULT '',
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `displayName` VARCHAR(255) NOT NULL DEFAULT '',
  `email` VARCHAR(168) NOT NULL DEFAULT '',
  `notes` TEXT,
  `session_id` VARCHAR(70) NOT NULL DEFAULT '',
  `last_login` DATETIME,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `hash` VARCHAR(128) NOT NULL DEFAULT '',
  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  KEY `user_username` (`institution_id`, `username`),
  KEY `user_email` (`institution_id`, `email`),
  KEY `user_hash` (`institution_id`, `hash`)
) ENGINE=InnoDB;

-- ----------------------------
--  institution
-- ----------------------------
CREATE TABLE IF NOT EXISTS `institution` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `domain` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT,
  `logo` VARCHAR(255) NOT NULL DEFAULT '',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `hash` VARCHAR(128) NOT NULL DEFAULT '',
  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
--  UNIQUE KEY `inst_domain` (`domain`),
  UNIQUE KEY `inst_hash` (`hash`)
) ENGINE=InnoDB;

-- ----------------------------
--  subject Data Tables
-- ----------------------------
CREATE TABLE IF NOT EXISTS `subject` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `institution_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `code` VARCHAR(64) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT,
  `date_start` DATETIME NOT NULL,
  `date_end` DATETIME NOT NULL,
  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  KEY `subject_code_institution` (`code`, `institution_id`)
) ENGINE=InnoDB;

-- ----------------------------
-- For now we will assume that one user has one role in a subject, ie: coordinator, lecturer, student
-- User is enrolled in subject or coordinator of subject
-- ----------------------------
CREATE TABLE IF NOT EXISTS `subject_has_user` (
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `subject_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY `subject_has_user_key` (`user_id`, `subject_id`)
) ENGINE=InnoDB;


-- --------------------------------------------------------
--
--
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `subject_pre_enrollment` (
  `subject_id` int(10) unsigned NOT NULL DEFAULT '0',
  `email` VARCHAR(168) NOT NULL DEFAULT '',
  `uid` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`subject_id`, `email`)
) ENGINE=InnoDB;





-- ----------------------------
--  TEST DATA
-- ----------------------------
INSERT INTO `user` (`institution_id`, `username`, `password` ,`role` ,`name`, `email`, `active`, `hash`, `modified`, `created`)
VALUES
  (NULL, 'admin', MD5(CONCAT('password', MD5('10adminadmin'))), 'admin', 'Administrator', 'admin@example.com', 1, MD5('10adminadmin'), NOW(), NOW()),
  (NULL, 'unimelb', MD5(CONCAT('password', MD5('20unimelbclient'))), 'client', 'Unimelb Client', 'fvas@unimelb.edu.au', 1, MD5('20unimelbclient'), NOW(), NOW()),
  (1, 'staff', MD5(CONCAT('password', MD5('31staffstaff'))), 'staff', 'Unimelb Staff', 'staff@unimelb.edu.au', 1, MD5('31staffstaff'), NOW(), NOW()),
  (1, 'student', MD5(CONCAT('password', MD5('41studentstudent'))), 'student', 'Unimelb Student', 'student@unimelb.edu.au', 1, MD5('41studentstudent'), NOW(), NOW())
;

INSERT INTO `institution` (`user_id`, `name`, `email`, `description`, `logo`, `active`, `hash`, `modified`, `created`)
  VALUES
    (2, 'The University Of Melbourne', 'admin@unimelb.edu.au', 'This is a test institution for this app', '', 1, MD5('1'), NOW(), NOW())
;

INSERT INTO `subject` (`institution_id`, `name`, `code`, `email`, `description`, `date_start`, `date_end`, `modified`, `created`)
  VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  NOW(), DATE_ADD(NOW(), INTERVAL 190 DAY), NOW(), NOW() )
--  VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  NOW(), DATE_ADD(CURRENT_DATETIME, INTERVAL 190 DAY), NOW(), NOW() )
;

INSERT INTO `subject_has_user` (`user_id`, `subject_id`)
VALUES
  (3, 1),
  (4, 1)
;

INSERT INTO `subject_pre_enrollment` (`subject_id`, `email`)
VALUES
  (1, 'student@unimelb.edu.au')
;


-- Use this to upgrade the ems- to plg- naming convention if required
-- UPDATE _plugin SET `name` = REPLACE(`name`, 'ems-', 'plg-');
-- UPDATE _plugin_zone SET `plugin_name` = REPLACE(`plugin_name`, 'ems-', 'plg-');