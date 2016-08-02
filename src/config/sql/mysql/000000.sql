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
  `uid` VARCHAR(128) NOT NULL DEFAULT '',
  `username` VARCHAR(64) NOT NULL DEFAULT '',
  `password` VARCHAR(128) NOT NULL DEFAULT '',
  -- ROLES: 'admin', 'client', 'staff', 'student
  `role` VARCHAR(255) NOT NULL DEFAULT '',
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `hash` VARCHAR(128) NOT NULL DEFAULT '',
  `notes` TEXT,
  `last_login` DATETIME,
  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  UNIQUE KEY `user_username` (`username`, `role`, `email`),
  UNIQUE KEY `user_hash` (`hash`)
) ENGINE=InnoDB;

-- ----------------------------
--  institution
-- ----------------------------
CREATE TABLE IF NOT EXISTS `institution` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `owner_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
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
  UNIQUE KEY `inst_domain` (`domain`),
  UNIQUE KEY `inst_hash` (`hash`)
) ENGINE=InnoDB;

-- ----------------------------
-- user_institution
-- User belongs to institution for `staff and `student` roles.
-- ----------------------------
CREATE TABLE IF NOT EXISTS `user_institution` (
	`user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`institution_id` INT(10) NOT NULL DEFAULT 0,
  -- TODO: Look into the best place for this info, as it has to do with LMS access more precisly, maybe the data table instead...
  `uid` VARCHAR(128) NOT NULL DEFAULT '',    -- A unique identifier for a specific institution (IE: staffId, studentId, etc...)
  UNIQUE KEY `ui_key` (`user_id`, `institution_id`)
) ENGINE=InnoDB;

-- ----------------------------
--  Course Data Tables
-- ----------------------------
CREATE TABLE IF NOT EXISTS `course` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `institution_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `code` VARCHAR(64) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT,
  `start` DATETIME NOT NULL,
  `finish` DATETIME NOT NULL,
  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  UNIQUE KEY `course_code_institution` (`code`, `institution_id`)
) ENGINE=InnoDB;

-- ----------------------------
-- For now we will assume that one user has one role in a course, ie: coordinator, lecturer, student
-- User is enrolled in course or coordinator of course
-- ----------------------------
CREATE TABLE IF NOT EXISTS `user_course` (
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `course_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY `user_course_key` (`user_id`, `course_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `data`
-- This is the replacement for the `settings` table
-- Use foreign_id = 0 and foreign_key = `system` for site settings (suggestion only)
-- Can be used for other object data using the foreign_id and foreign_key
-- foreign_key can be a class namespace or anything describing the data group
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `data` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `foreign_id` INT(10) NOT NULL DEFAULT 0,
  `foreign_key` VARCHAR(128) NOT NULL DEFAULT '',
  `key` VARCHAR(255) NOT NULL DEFAULT '',
  `value` TEXT,
  UNIQUE KEY `data_foreign_fields` (`foreign_id`, `foreign_key`, `key`)
) ENGINE=InnoDB;



-- ----------------------------
--  TEST DATA
-- ----------------------------
INSERT INTO `user` (`username`, `password` ,`role` ,`name`, `email`, `active`, `hash`, `modified`, `created`)
VALUES
  ('admin', MD5(CONCAT('password', MD5('adminadminadmin@example.com'))), 'admin', 'Administrator', 'admin@example.com', 1, MD5('adminadminadmin@example.com'), NOW() , NOW() ),
  ('unimelb', MD5(CONCAT('password', MD5('unimelbclientfvas@unimelb.edu.au'))), 'client', 'Unimelb Client', 'fvas@unimelb.edu.au', 1, MD5('unimelbclientfvas@unimelb.edu.au'), NOW() , NOW()  ),
  ('staff', MD5(CONCAT('password', MD5('staffstaffstaff@unimelb.edu.au'))), 'staff', 'Unimelb Staff', 'staff@unimelb.edu.au', 1, MD5('staffstaffstaff@unimelb.edu.au'), NOW() , NOW()  ),
  ('student', MD5(CONCAT('password', MD5('studentstudentstudent@unimelb.edu.au'))), 'student', 'Unimelb Student', 'student@unimelb.edu.au', 1, MD5('studentstudentstudent@unimelb.edu.au'), NOW() , NOW()  )
;

INSERT INTO `institution` (`owner_id`, `name`, `email`, `description`, `logo`, `active`, `hash`, `modified`, `created`)
  VALUES
    (2, 'The University Of Melbourne', 'admin@unimelb.edu.au', 'This is a test institution for this app', '', 1, MD5('1'), NOW() , NOW())
;

INSERT INTO `course` (`institution_id`, `name`, `code`, `email`, `description`, `start`, `finish`, `modified`, `created`)
    VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'course@unimelb.edu.au', '',  NOW(), DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 190 DAY), NOW() , NOW() )
;

INSERT INTO `user_course` (`user_id`, `course_id`)
VALUES
  (3, 1),
  (4, 1)
;

INSERT INTO `user_institution` (`user_id`, `institution_id`, `uid`)
VALUES
  (3, 1, 'staff_id'),
  (4, 1, 'student_id')
;

INSERT INTO `data` (`foreign_id`, `foreign_key`, `key`, `value`) VALUES
  (0, 'system', 'site.title', 'Tk2Uni Site'),
  (0, 'system', 'site.email', 'tkwiki@example.com'),
  (0, 'system', 'site.meta.keywords', ''),
  (0, 'system', 'site.meta.description', ''),
  (0, 'system', 'site.global.js', ''),
  (0, 'system', 'site.global.css', ''),
  (0, 'system', 'site.client.registration', 'site.client.registration'),
  (0, 'system', 'site.client.activation', 'site.client.activation')
;


