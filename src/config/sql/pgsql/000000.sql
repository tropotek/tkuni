-- ---------------------------------
-- Install SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


-- DROP TABLE IF EXISTS institution CASCADE;
-- DROP TABLE IF EXISTS course CASCADE;
-- DROP TABLE IF EXISTS user_course;
--
-- DROP TABLE IF EXISTS "user" CASCADE;
-- DROP TABLE IF EXISTS role CASCADE;
-- DROP TABLE IF EXISTS user_role;




-- ----------------------------
--  institution
-- ----------------------------
CREATE TABLE IF NOT EXISTS institution (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  description TEXT,
  logo VARCHAR(255),
-- TODO: location information?
-- TODO: Contact information?
  active BOOLEAN,
  hash VARCHAR(255),
  del BOOLEAN DEFAULT FALSE,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW()
);

-- ----------------------------
--  user
-- ----------------------------
CREATE TABLE IF NOT EXISTS "user" (
  id SERIAL PRIMARY KEY,
  institution_id INTEGER NOT NULL DEFAULT 0,    -- 0 = site user not belonging to any institution....
  uid VARCHAR(64),
  username VARCHAR(64),
  password VARCHAR(64),
  name VARCHAR(255),
  email VARCHAR(255),
  active BOOLEAN,
  hash VARCHAR(255),
  notes TEXT,
  last_login TIMESTAMP DEFAULT NULL,
  del BOOLEAN DEFAULT FALSE,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  -- Cannot have a foreign key as it does not allow for a 0|null value which is required for local site users....
	-- FOREIGN KEY (institution_id) REFERENCES institution(id) ON DELETE CASCADE,
  CONSTRAINT uid UNIQUE (institution_id, uid),
  CONSTRAINT username UNIQUE (institution_id, username),
  CONSTRAINT email UNIQUE (institution_id, email),
  CONSTRAINT hash UNIQUE (institution_id, hash)
);

-- ----------------------------
--  role
-- ----------------------------
CREATE TABLE IF NOT EXISTS role (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  category VARCHAR(128) NOT NULL,
	description VARCHAR(255) NOT NULL,
  del BOOLEAN DEFAULT FALSE,
	CONSTRAINT name UNIQUE (name)
);

-- ----------------------------
--  user_role
-- ----------------------------
CREATE TABLE IF NOT EXISTS user_role (
	user_id INTEGER NOT NULL,
	role_id INTEGER NOT NULL,
	FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
	FOREIGN KEY (role_id) REFERENCES role(id)  ON DELETE CASCADE
);

-- ----------------------------
--  Course Data Tables
-- ----------------------------
CREATE TABLE IF NOT EXISTS course (
  id SERIAL PRIMARY KEY,
  institution_id INTEGER NOT NULL DEFAULT 0,
  name VARCHAR(255),
  code VARCHAR(64),
  email VARCHAR(255),
  description TEXT,
  start TIMESTAMP DEFAULT NOW(),
  finish TIMESTAMP DEFAULT NOW(),
  active BOOLEAN,
  del BOOLEAN DEFAULT FALSE,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  CONSTRAINT code_institution UNIQUE (code, institution_id)
);

-- ----------------------------
-- For now we will assume that one user has one role in a course, ie: coordinator, lecturer, student
-- ----------------------------
CREATE TABLE IF NOT EXISTS user_course_role (
  user_id INTEGER NOT NULL,
  course_id INTEGER NOT NULL,
	role_id INTEGER NOT NULL,
  CONSTRAINT user_course_role_key UNIQUE (user_id, course_id),
  FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
	FOREIGN KEY (role_id) REFERENCES role(id)  ON DELETE CASCADE
);


-- ----------------------------
--  TEST DATA
-- ----------------------------

INSERT INTO institution (name, email, description, logo, active, hash, modified, created)
VALUES ('The University Of Melbourne', 'admin@unimelb.edu.au', 'This is a test institution for this app', '', TRUE, MD5(CONCAT('admin@unimelb.edu.au', date_trunc('seconds', NOW()))), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()));

INSERT INTO "user" (institution_id, uid, username, password ,name, email, active, hash, modified, created)
VALUES
  (0, MD5(CONCAT('admin', NOW())), 'admin', MD5(CONCAT('password', MD5('admin'))), 'Administrator', 'admin@example.com', TRUE, MD5('0admin'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()) ),
  (1, MD5(CONCAT('unimelb', NOW())), 'unimelb', MD5(CONCAT('password', MD5('unimelb'))), 'Unimelb Client', 'fvas@unimelb.edu.au', TRUE, MD5('1unimelb'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (1, MD5(CONCAT('staff', NOW())), 'staff', MD5(CONCAT('password', MD5('staff'))), 'Unimelb Staff', 'staff@unimelb.edu.au', TRUE, MD5('1staff'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (1, MD5(CONCAT('student', NOW())), 'student', MD5(CONCAT('password', MD5('student'))), 'Unimelb Student', 'student@unimelb.edu.au', TRUE, MD5('1student'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  )
;

INSERT INTO role (name, category, description)
VALUES
  ('admin', 'site', 'System Administration Role'),
  ('client', 'site', 'Client role for institution client accounts'),
  ('eduser', 'site', 'Standard Education role, see course roles for more info.'),
  -- course roles, used in conjunction with the `eduser` role and user_course_role table to determine the user course permissions
  ('staff', 'course', 'Staff/Coordinator Role'),
  ('student', 'course', 'Student Role')
;

INSERT INTO user_role (user_id, role_id)
VALUES
  -- Site Admin
  (1, 1),
  -- Unimelb Client user
  (2, 2),
  -- Unimelb Staff
  (3, 3),
  -- Unimelb student
  (4, 3)
;

INSERT INTO course (institution_id, name, code, email, description, start, finish, active, modified, created)
    VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'course@unimelb.edu.au', '',  date_trunc('seconds', NOW()), date_trunc('seconds', (CURRENT_TIMESTAMP + (190 * interval '1 day')) ), true, date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()) )
;

INSERT INTO user_course_role (user_id, course_id, role_id)
VALUES
  -- Unimelb Staff
  (3, 1, 4),
  -- Unimelb student
  (4, 1, 5)
;


