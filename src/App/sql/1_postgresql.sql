-- ---------------------------------
-- Install SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------

DROP TABLE IF EXISTS "course" CASCADE;
DROP TABLE IF EXISTS "user_course";

DROP TABLE IF EXISTS "user" CASCADE;
DROP TABLE IF EXISTS "role" CASCADE;
DROP TABLE IF EXISTS "user_role";

DROP TABLE IF EXISTS "lti_consumer" CASCADE;
DROP TABLE IF EXISTS "lti_context" CASCADE;
DROP TABLE IF EXISTS "lti_user";
DROP TABLE IF EXISTS "lti_nonce";
DROP TABLE IF EXISTS "lti_share_key";


-- -------------------------------------------------
-- LTI TABLES
-- -------------------------------------------------
CREATE TABLE IF NOT EXISTS lti_consumer (
  consumer_key varchar(255) NOT NULL,
  name varchar(45) NOT NULL,
  secret varchar(32) NOT NULL,
  lti_version varchar(12) DEFAULT NULL,
  consumer_name varchar(255) DEFAULT NULL,
  consumer_version varchar(255) DEFAULT NULL,
  consumer_guid varchar(255) DEFAULT NULL,
  css_path varchar(255) DEFAULT NULL,
  protected BOOLEAN NOT NULL,
  enabled BOOLEAN NOT NULL,
  enable_from timestamp DEFAULT NULL,
  enable_until timestamp DEFAULT NULL,
  last_access timestamp DEFAULT NULL,
  created timestamp NOT NULL,
  updated timestamp NOT NULL,
  CONSTRAINT lti_consumer_PK PRIMARY KEY (consumer_key)
);

CREATE TABLE IF NOT EXISTS lti_context (
  consumer_key varchar(255) NOT NULL,
  context_id varchar(255) NOT NULL,
  lti_context_id varchar(255) DEFAULT NULL,
  lti_resource_id varchar(255) DEFAULT NULL,
  title varchar(255) NOT NULL,
  settings varchar(4000),
  primary_consumer_key varchar(255) DEFAULT NULL,
  primary_context_id varchar(255) DEFAULT NULL,
  share_approved BOOLEAN DEFAULT NULL,
  created timestamp NOT NULL,
  updated timestamp NOT NULL,
  CONSTRAINT lti_context_PK PRIMARY KEY (consumer_key, context_id)
);

CREATE TABLE IF NOT EXISTS lti_user (
  consumer_key varchar(255) NOT NULL,
  context_id varchar(255) NOT NULL,
  user_id varchar(255) NOT NULL,
  lti_result_sourcedid varchar(255) NOT NULL,
  created timestamp NOT NULL,
  updated timestamp NOT NULL,
  CONSTRAINT lti_user_PK PRIMARY KEY (consumer_key, context_id, user_id)
);

CREATE TABLE IF NOT EXISTS lti_nonce (
  consumer_key varchar(255) NOT NULL,
  value varchar(32) NOT NULL,
  expires timestamp NOT NULL,
  CONSTRAINT lti_nonce_PK PRIMARY KEY (consumer_key, value)
);

CREATE TABLE IF NOT EXISTS lti_share_key (
  share_key_id varchar(32) NOT NULL,
  primary_consumer_key varchar(255) NOT NULL,
  primary_context_id varchar(255) NOT NULL,
  auto_approve BOOLEAN NOT NULL,
  expires timestamp NOT NULL,
  CONSTRAINT lti_share_key_PK PRIMARY KEY (share_key_id)
);

ALTER TABLE lti_context
ADD CONSTRAINT lti_context_consumer_FK1 FOREIGN KEY (consumer_key)
REFERENCES lti_consumer (consumer_key);

ALTER TABLE lti_context
ADD CONSTRAINT lti_context_context_FK1 FOREIGN KEY (primary_consumer_key, primary_context_id)
REFERENCES lti_context (consumer_key, context_id);

ALTER TABLE lti_user
ADD CONSTRAINT lti_user_context_FK1 FOREIGN KEY (consumer_key, context_id)
REFERENCES lti_context (consumer_key, context_id);

ALTER TABLE lti_nonce
ADD CONSTRAINT lti_nonce_consumer_FK1 FOREIGN KEY (consumer_key)
REFERENCES lti_consumer (consumer_key);

ALTER TABLE lti_share_key
ADD CONSTRAINT lti_share_key_context_FK1 FOREIGN KEY (primary_consumer_key, primary_context_id)
REFERENCES lti_context (consumer_key, context_id);


--  


CREATE TABLE IF NOT EXISTS institution_lti (
  institution_id INTEGER NOT NULL,
  lti_consumer_key varchar(255),
  CONSTRAINT institution_lti_PK PRIMARY KEY (institution_id, lti_consumer_key),
  FOREIGN KEY (institution_id) REFERENCES institution(institution_id) ON DELETE CASCADE,
  FOREIGN KEY (lti_consumer_key) REFERENCES lti_consumer(consumer_key) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS course_lti (
  course_id INTEGER NOT NULL,
  lti_consumer_key varchar(255),
  lti_context_id varchar(255),
  CONSTRAINT course_id_PK PRIMARY KEY (course_id, lti_consumer_key, lti_context_id),
  FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE,
  FOREIGN KEY (lti_consumer_key) REFERENCES lti_context(consumer_key) ON DELETE CASCADE,
  FOREIGN KEY (lti_context_id) REFERENCES lti_context(context_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_lti (
  user_id INTEGER NOT NULL,
  lti_consumer_key varchar(255),
  lti_context_id varchar(255),
  lti_user_id varchar(255),
  CONSTRAINT user_id_PK PRIMARY KEY (user_id, lti_consumer_key, lti_context_id, lti_user_id),
  FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
  FOREIGN KEY (lti_consumer_key) REFERENCES lti_user(consumer_key) ON DELETE CASCADE,
  FOREIGN KEY (lti_context_id) REFERENCES lti_user(context_id) ON DELETE CASCADE,
  FOREIGN KEY (lti_user_id) REFERENCES lti_user(user_id) ON DELETE CASCADE
);



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
  del BOOLEAN DEFAULT FALSE,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW()
);



-- ----------------------------
--  Course Data Tables
-- ----------------------------
CREATE TABLE IF NOT EXISTS course (
  id SERIAL PRIMARY KEY,
  institution_id INTEGER NOT NULL DEFAULT 0,
  name VARCHAR(255),
  code VARCHAR(255),
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

CREATE TABLE IF NOT EXISTS user_course (
  user_id INTEGER NOT NULL,
  course_id INTEGER NOT NULL,
  FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE
);


-- ----------------------------
--  User Data Tables
-- ----------------------------
CREATE TABLE IF NOT EXISTS "user" (
  id SERIAL PRIMARY KEY,
  uid VARCHAR(64),
  username VARCHAR(64),
  password VARCHAR(64),
  name TEXT,
  email TEXT,
  active BOOLEAN,
  hash TEXT,
  notes TEXT,
  last_login TIMESTAMP DEFAULT NULL,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  CONSTRAINT uid UNIQUE (uid),
  CONSTRAINT username UNIQUE (username),
  CONSTRAINT email UNIQUE (email),
  CONSTRAINT hash UNIQUE (hash)
);

CREATE TABLE IF NOT EXISTS role (
  id SERIAL PRIMARY KEY,
	name VARCHAR(50) NOT NULL,
	description VARCHAR(50) NOT NULL,
	CONSTRAINT name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS user_role (
	user_id INTEGER NOT NULL,
	role_id INTEGER NOT NULL,
	FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
	FOREIGN KEY (role_id) REFERENCES role(id)  ON DELETE CASCADE
);


-- NOTE: For reference only
-- ----------------------------------------
-- ALTER TABLE user_role
--   DROP CONSTRAINT user_role_user_id_fkey,
--   ADD CONSTRAINT user_role_user_id_fkey
--     FOREIGN KEY (user_id)
--     REFERENCES "user"(id)
--     ON DELETE CASCADE;

INSERT INTO "user" (uid, username ,password ,name, email, active, hash, modified ,created)
VALUES
  (MD5(CONCAT('admin', NOW())), 'admin', MD5(CONCAT('password', MD5('admin'))), 'Administrator', 'admin@example.com', TRUE, MD5('admin'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()) ),
  (MD5(CONCAT('coordinator', NOW())), 'coordinator', MD5(CONCAT('password', MD5('coordinator'))), 'Coordinator', 'cord@example.com', TRUE, MD5('coordinator'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (MD5(CONCAT('staff', NOW())), 'staff', MD5(CONCAT('password', MD5('staff'))), 'Staff', 'staff@example.com', TRUE, MD5('staff'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (MD5(CONCAT('student', NOW())), 'student', MD5(CONCAT('password', MD5('student'))), 'Student', 'student@example.com', TRUE, MD5('student'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (MD5(CONCAT('user', NOW())), 'user', MD5(CONCAT('password', MD5('user'))), 'User', 'user@example.com', TRUE, MD5('user'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  )
;

INSERT INTO "role" ("name", description)
VALUES
  ('admin', 'Administration role'),
  ('coordinator', 'Coordinator role'),
  ('staff', 'Staff role'),
  ('student', 'Student role'),
  ('user', 'User role')
;

INSERT INTO user_role (user_id, role_id)
VALUES
  (1, 1),
  (1, 2),
  (1, 3),
  (1, 4),
  (1, 5),
  
  (2, 2),
  (2, 3),
  (2, 4),
  (2, 5),
  
  (3, 3),
  (3, 4),
  (3, 5),
  
  (4, 4),
  (4, 5),
  
  (5, 5)
;

-- ----------------------------
--  {NEW} Data Tables
-- ----------------------------

-- Build your tables here



