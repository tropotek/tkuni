-- ---------------------------------
-- Install SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------



-- ----------------------------
--  TEST DATA
-- ----------------------------

INSERT INTO `institution` (`user_id`, `name`, `email`, `description`, `logo`, `active`, `hash`, `modified`, `created`)
VALUES
  (2, 'The University Of Melbourne', 'admin@unimelb.edu.au', 'The University Of Melbourne', '', 1, MD5('1'), NOW(), NOW())
;

INSERT INTO `user` (`role_id`, `institution_id`, `username`, `password` ,`name`, `email`, `active`, `hash`, `modified`, `created`)
VALUES
  (1, NULL, 'admin', MD5(CONCAT('password', MD5('10admin'))), 'Administrator', 'admin@example.com', 1, MD5('10admin'), NOW(), NOW()),
  (2, NULL, 'unimelb', MD5(CONCAT('password', MD5('20unimelb'))), 'The University Of Melbourne', 'fvas@unimelb.edu.au', 1, MD5('20unimelb'), NOW(), NOW()),
  (3, 1, 'staff', MD5(CONCAT('password', MD5('31staff'))), 'Unimelb Staff', 'staff@unimelb.edu.au', 1, MD5('31staff'), NOW(), NOW()),
  (4, 1, 'student', MD5(CONCAT('password', MD5('41student'))), 'Unimelb Student', 'student@unimelb.edu.au', 1, MD5('41student'), NOW(), NOW())
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

