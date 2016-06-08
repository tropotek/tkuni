-- ---------------------------------
-- Install SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


DROP TABLE IF EXISTS institution CASCADE;
DROP TABLE IF EXISTS course CASCADE;
DROP TABLE IF EXISTS user_course;

DROP TABLE IF EXISTS "user" CASCADE;
DROP TABLE IF EXISTS role CASCADE;
DROP TABLE IF EXISTS user_role;




-- ----------------------------
--  Institution Data Tables
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
--  User Data Tables
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
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  -- Cannot have a foreign key as it does not allow for a 0|null value which is required for local site users....
	-- FOREIGN KEY (institution_id) REFERENCES institution(id) ON DELETE CASCADE,
  CONSTRAINT uid UNIQUE (institution_id, uid),
  CONSTRAINT username UNIQUE (institution_id, username),
  CONSTRAINT email UNIQUE (institution_id, email),
  CONSTRAINT hash UNIQUE (institution_id, hash)
);


CREATE TABLE IF NOT EXISTS role (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  category VARCHAR(128) NOT NULL,
	description VARCHAR(50) NOT NULL,
	CONSTRAINT name UNIQUE (name)
);

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

-- For now we will assume that one user has one role in a course, ie: coordinator, lecturer, student
CREATE TABLE IF NOT EXISTS user_course (
  user_id INTEGER NOT NULL,
  course_id INTEGER NOT NULL,
	role_id INTEGER NOT NULL,
  CONSTRAINT user_course UNIQUE (user_id, course_id),
  FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
	FOREIGN KEY (role_id) REFERENCES role(id)  ON DELETE CASCADE
);


-- ----------------------------
--  TEST DATA
-- ----------------------------


-- NOTE: For reference only
-- ----------------------------------------
-- ALTER TABLE user_role
--   DROP CONSTRAINT user_role_user_id_fkey,
--   ADD CONSTRAINT user_role_user_id_fkey
--     FOREIGN KEY (user_id)
--     REFERENCES "user"(id)
--     ON DELETE CASCADE;

INSERT INTO institution (name, email, description, logo, active, hash, modified, created)
VALUES ('Test University', 'tuadmin@example.com', 'This is a test university providing services to its students', '', TRUE, MD5(CONCAT('tuadmin@example.com', date_trunc('seconds', NOW()))), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()));

INSERT INTO "user" (institution_id, uid, username, password ,name, email, active, hash, modified, created)
VALUES
  (0, MD5(CONCAT('admin', NOW())), 'admin', MD5(CONCAT('password', MD5('admin'))), 'Administrator', 'admin@example.com', TRUE, MD5('admin'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()) ),
  (1, MD5(CONCAT('coordinator', NOW())), 'coordinator', MD5(CONCAT('password', MD5('coordinator'))), 'Coordinator', 'cord@example.com', TRUE, MD5('coordinator'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (1, MD5(CONCAT('lecturer', NOW())), 'lecturer', MD5(CONCAT('password', MD5('lecturer'))), 'Lecturer', 'staff@example.com', TRUE, MD5('lecturer'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (1, MD5(CONCAT('student', NOW())), 'student', MD5(CONCAT('password', MD5('student'))), 'Student', 'student@example.com', TRUE, MD5('student'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (0, MD5(CONCAT('client', NOW())), 'client', MD5(CONCAT('password', MD5('client'))), 'Client', 'client@example.com', TRUE, MD5('client'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  )
;

INSERT INTO role (name, category, description)
VALUES
  ('admin', 'site', 'Administration Role'),
  ('client', 'site', 'Client User Role'),
  ('edu', 'site', 'Standard Education role, see course roles for more info.'),
  
  -- Course Roles
  ('coordinator', 'course', 'Coordinator Role'),
  ('lecturer', 'course', 'Lecturer Role'),
  ('student', 'course', 'Student Role')
;

INSERT INTO user_role (user_id, role_id)
VALUES
  -- Site Admin
  (1, 1),
  -- Institution Coordinator (user)
  (2, 3),
  -- Institution Lecturer (user)
  (3, 3),
  -- Institution Student (user)
  (4, 3),
  -- Client Site User (eg: An institution account to manage their subscription to the site's service)
  (5, 2)
;



