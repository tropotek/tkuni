-- ---------------------------------
-- Install SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


-- ----------------------------
--  user
-- ----------------------------
CREATE TABLE IF NOT EXISTS "user" (
  id SERIAL PRIMARY KEY,
  institution_id INTEGER DEFAULT NULL,
  uid VARCHAR(128) NOT NULL DEFAULT '',
  username VARCHAR(64) NOT NULL DEFAULT '',
  password VARCHAR(128) NOT NULL DEFAULT '',
  -- ROLES: 'admin', 'client', 'staff', 'student
  role VARCHAR(64) NOT NULL DEFAULT '',
  name VARCHAR(255) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  active NUMERIC(1) NOT NULL DEFAULT 1,
  hash VARCHAR(255) NOT NULL DEFAULT '',
  notes TEXT,
  last_login TIMESTAMP DEFAULT NULL,
  del NUMERIC(1) NOT NULL DEFAULT 0,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),

  CONSTRAINT user_username UNIQUE (institution_id, username),
  CONSTRAINT user_email UNIQUE (institution_id, email),
  CONSTRAINT user_hash UNIQUE (institution_id, hash)
);

-- ----------------------------
--  institution
-- ----------------------------
CREATE TABLE IF NOT EXISTS institution (
  id SERIAL PRIMARY KEY,
  owner_id INTEGER NOT NULL DEFAULT 0,
  name VARCHAR(255) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  domain VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT,
  logo VARCHAR(255) NOT NULL DEFAULT '',
-- TODO: location information?
-- TODO: Contact information?
  active NUMERIC(1) NOT NULL DEFAULT 1,
  hash VARCHAR(255) NOT NULL DEFAULT '',
  del NUMERIC(1) NOT NULL DEFAULT 0,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  FOREIGN KEY (owner_id) REFERENCES "user"(id) ON DELETE CASCADE,
  CONSTRAINT inst_hash UNIQUE (hash)
);

-- ----------------------------
--  Course Data Tables
-- ----------------------------
CREATE TABLE IF NOT EXISTS course (
  id SERIAL PRIMARY KEY,
  institution_id INTEGER NOT NULL DEFAULT 0,
  name VARCHAR(255) NOT NULL DEFAULT '',
  code VARCHAR(64) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT,
  start TIMESTAMP DEFAULT NOW(),
  finish TIMESTAMP DEFAULT NOW(),
  del NUMERIC(1) NOT NULL DEFAULT 0,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  CONSTRAINT course_code_institution UNIQUE (code, institution_id)
);

-- ----------------------------
-- For now we will assume that one user has one role in a course, ie: coordinator, lecturer, student
-- User is enrolled in course or coordinator of course
-- ----------------------------
CREATE TABLE IF NOT EXISTS user_course (
  user_id INTEGER NOT NULL,
  course_id INTEGER NOT NULL,
  CONSTRAINT user_course_key UNIQUE (user_id, course_id),
  FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE
);

-- --------------------------------------------------------
--
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS enrollment (
  course_id INTEGER NOT NULL DEFAULT '0',
  email VARCHAR(255) NOT NULL DEFAULT '',
  uid VARCHAR(64) NOT NULL DEFAULT '',
  PRIMARY KEY (course_id, email)
);


-- ----------------------------
--  TEST DATA
-- ----------------------------
INSERT INTO "user" (institution_id, username, password ,role ,name, email, active, hash, modified, created)
VALUES
  (NULL, 'admin', MD5(CONCAT('password', MD5('adminadminadmin@example.com'))), 'admin', 'Administrator', 'admin@example.com', 1, MD5('adminadminadmin@example.com'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()) ),
  (NULL, 'unimelb', MD5(CONCAT('password', MD5('unimelbclientfvas@unimelb.edu.au'))), 'client', 'Unimelb Client', 'fvas@unimelb.edu.au', 1, MD5('unimelbclientfvas@unimelb.edu.au'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (1, 'staff', MD5(CONCAT('password', MD5('staffstaffstaff@unimelb.edu.au'))), 'staff', 'Unimelb Staff', 'staff@unimelb.edu.au', 1, MD5('staffstaffstaff@unimelb.edu.au'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (1, 'student', MD5(CONCAT('password', MD5('studentstudentstudent@unimelb.edu.au'))), 'student', 'Unimelb Student', 'student@unimelb.edu.au', 1, MD5('studentstudentstudent@unimelb.edu.au'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  )
;

INSERT INTO institution (owner_id, name, email, description, logo, active, hash, modified, created)
  VALUES
    (2, 'The University Of Melbourne', 'admin@unimelb.edu.au', 'This is a test institution for this app', '', 1, MD5('1'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()))
;

INSERT INTO course (institution_id, name, code, email, description, start, finish, modified, created)
    VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'course@unimelb.edu.au', '',  date_trunc('seconds', NOW()), date_trunc('seconds', (CURRENT_TIMESTAMP + (190 * interval '1 day')) ), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()) )
;

INSERT INTO user_course (user_id, course_id)
VALUES
  (3, 1),
  (4, 1)
;

INSERT INTO enrollment (course_id, email)
VALUES
  (1, 'student@unimelb.edu.au')
;

-- INSERT INTO data (foreign_id, foreign_key, key, value) VALUES
-- --   (0, 'system', 'site.title', 'Tk2Uni Site'),
-- --   (0, 'system', 'site.email', 'tkwiki@example.com'),
--   (0, 'system', 'site.meta.keywords', ''),
--   (0, 'system', 'site.meta.description', ''),
--   (0, 'system', 'site.global.js', ''),
--   (0, 'system', 'site.global.css', '')
--   ,
--   (0, 'system', 'site.client.registration', 'site.client.registration'),
--   (0, 'system', 'site.client.activation', 'site.client.activation')
;


