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
  displayName VARCHAR(255) NOT NULL DEFAULT '',
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
  user_id INTEGER NOT NULL DEFAULT 0,
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
  FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
  CONSTRAINT inst_hash UNIQUE (hash)
);

-- ----------------------------
--  subject Data Tables
-- ----------------------------
CREATE TABLE IF NOT EXISTS subject (
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
  CONSTRAINT subject_code_institution UNIQUE (code, institution_id)
);

-- ----------------------------
-- For now we will assume that one user has one role in a subject, ie: coordinator, lecturer, student
-- User is enrolled in subject or coordinator of subject
-- ----------------------------
CREATE TABLE IF NOT EXISTS subject_has_user (
  user_id INTEGER NOT NULL,
  subject_id INTEGER NOT NULL,
  CONSTRAINT subject_has_user_key UNIQUE (user_id, subject_id),
  FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subject(id) ON DELETE CASCADE
);

-- --------------------------------------------------------
--
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS subject_pre_enrollment (
  subject_id INTEGER NOT NULL DEFAULT '0',
  email VARCHAR(255) NOT NULL DEFAULT '',
  uid VARCHAR(64) NOT NULL DEFAULT '',
  PRIMARY KEY (subject_id, email)
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

INSERT INTO institution (user_id, name, email, description, logo, active, hash, modified, created)
  VALUES
    (2, 'The University Of Melbourne', 'admin@unimelb.edu.au', 'This is a test institution for this app', '', 1, MD5('1'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()))
;

INSERT INTO subject (institution_id, name, code, email, description, start, finish, modified, created)
    VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  date_trunc('seconds', NOW()), date_trunc('seconds', (CURRENT_TIMESTAMP + (190 * interval '1 day')) ), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()) )
;

INSERT INTO subject_has_user (user_id, subject_id)
VALUES
  (3, 1),
  (4, 1)
;

INSERT INTO subject_pre_enrollment (subject_id, email)
VALUES
  (1, 'student@unimelb.edu.au')
;
