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
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `notes` TEXT,
  `last_login` TIMESTAMP,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `hash` VARCHAR(128) NOT NULL DEFAULT '',
  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` TIMESTAMP NOT NULL,
  `created` TIMESTAMP NOT NULL,
  UNIQUE KEY `user_username` (`institution_id`, `username`),
  UNIQUE KEY `user_email` (`institution_id`, `email`),
  UNIQUE KEY `user_hash` (`institution_id`, `hash`)
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
  `modified` TIMESTAMP NOT NULL,
  `created` TIMESTAMP NOT NULL,
--  UNIQUE KEY `inst_domain` (`domain`),
  UNIQUE KEY `inst_hash` (`hash`)
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
  `start` TIMESTAMP NOT NULL,
  `finish` TIMESTAMP NOT NULL,
  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` TIMESTAMP NOT NULL,
  `created` TIMESTAMP NOT NULL,
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
--
--
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `enrollment` (
  `course_id` int(10) unsigned NOT NULL DEFAULT '0',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `uid` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`course_id`, `email`)
) ENGINE=InnoDB;




-- ----------------------------
--  TEST DATA
-- ----------------------------
INSERT INTO `user` (`institution_id`, `username`, `password` ,`role` ,`name`, `email`, `active`, `hash`, `modified`, `created`)
VALUES
  (NULL, 'admin', MD5(CONCAT('password', MD5('adminadminadmin@example.com'))), 'admin', 'Administrator', 'admin@example.com', 1, MD5('adminadminadmin@example.com'), NOW() , NOW() ),
  (NULL, 'unimelb', MD5(CONCAT('password', MD5('unimelbclientfvas@unimelb.edu.au'))), 'client', 'Unimelb Client', 'fvas@unimelb.edu.au', 1, MD5('unimelbclientfvas@unimelb.edu.au'), NOW() , NOW()  ),
  (1, 'staff', MD5(CONCAT('password', MD5('staffstaffstaff@unimelb.edu.au'))), 'staff', 'Unimelb Staff', 'staff@unimelb.edu.au', 1, MD5('staffstaffstaff@unimelb.edu.au'), NOW() , NOW()  ),
  (1, 'student', MD5(CONCAT('password', MD5('studentstudentstudent@unimelb.edu.au'))), 'student', 'Unimelb Student', 'student@unimelb.edu.au', 1, MD5('studentstudentstudent@unimelb.edu.au'), NOW() , NOW()  )
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

INSERT INTO `enrollment` (`course_id`, `email`)
VALUES
  (1, 'student@unimelb.edu.au')
;

