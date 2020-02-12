-- ---------------------------------
-- Install SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------



-- ----------------------------
--  TEST DATA
-- ----------------------------

# INSERT INTO `institution` (`user_id`, `name`, `email`, `description`, `logo`, `active`, `hash`, `modified`, `created`)
# VALUES
#   (2, 'The University Of Melbourne', 'admin@unimelb.edu.au', 'The University Of Melbourne', '', 1, MD5('1'), NOW(), NOW())
# ;
INSERT INTO institution (user_id, name, email, phone, domain, description, logo, feature, street, city, state, postcode, country, address, map_lat, map_lng, map_zoom, active, del, hash, modified, created) VALUES
(2, 'The University Of Melbourne', 'admin@unimelb.edu.au', '', '', '<p>The University Of Melbourne</p>', '', '', '250 Princes Highway', 'Werribee', 'Victoria', '3030', 'Australia', '250 Princes Hwy, Werribee VIC 3030, Australia', -37.88916600, 144.69314774, 18.00, 1, 0, MD5('1'), NOW(), NOW())
;

INSERT INTO `user` (`role_id`, `institution_id`, `username`, `password` ,`name_first`, `name_last`, `email`, `active`, `hash`, `modified`, `created`)
VALUES
  (1, NULL, 'admin', MD5(CONCAT('password', MD5('10admin'))), 'Administrator', '', 'admin@example.com', 1, MD5('10admin'), NOW(), NOW()),
  (2, NULL, 'unimelb', MD5(CONCAT('password', MD5('20unimelb'))), 'The University Of Melbourne', '', 'fvas@unimelb.edu.au', 1, MD5('20unimelb'), NOW(), NOW()),
  (5, 1, 'staff', MD5(CONCAT('password', MD5('31staff'))), 'Staff', 'Unimelb', 'staff@unimelb.edu.au', 1, MD5('31staff'), NOW(), NOW()),
  (4, 1, 'student', MD5(CONCAT('password', MD5('41student'))), 'Student', 'Unimelb', 'student@unimelb.edu.au', 1, MD5('41student'), NOW(), NOW())
;

INSERT INTO `subject` (`institution_id`, `course_id`, `name`, `code`, `email`, `description`, `date_start`, `date_end`, `modified`, `created`)
  VALUES (1, 1, 'Poultry Test Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  NOW(), DATE_ADD(NOW(), INTERVAL 190 DAY), NOW(), NOW() )
--  VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  NOW(), DATE_ADD(CURRENT_DATETIME, INTERVAL 190 DAY), NOW(), NOW() )
;

INSERT INTO `subject_has_user` (`user_id`, `subject_id`)
VALUES
  (4, 1)
;

INSERT INTO `course_has_user` (`user_id`, `course_id`)
VALUES
  (3, 1)
;

# INSERT INTO `subject_pre_enrollment` (`subject_id`, `email`)
# VALUES
#   (1, 'student@unimelb.edu.au')
# ;


