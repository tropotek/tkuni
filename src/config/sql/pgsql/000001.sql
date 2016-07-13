-- ---------------------------------
-- Install LTI SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------

-- DROP TABLE IF EXISTS institution_lti;
-- DROP TABLE IF EXISTS course_lti;
-- DROP TABLE IF EXISTS user_lti;
--
-- DROP TABLE IF EXISTS lti_consumer CASCADE;
-- DROP TABLE IF EXISTS lti_context CASCADE;
-- DROP TABLE IF EXISTS lti_user;
-- DROP TABLE IF EXISTS lti_nonce;
-- DROP TABLE IF EXISTS lti_share_key;


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
  FOREIGN KEY (institution_id) REFERENCES institution(id) ON DELETE CASCADE,
  FOREIGN KEY (lti_consumer_key) REFERENCES lti_consumer(consumer_key) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS course_lti (
  course_id INTEGER NOT NULL,
  lti_consumer_key varchar(255),
  lti_context_id varchar(255),
  CONSTRAINT course_id_PK PRIMARY KEY (course_id, lti_consumer_key, lti_context_id),
  FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
  FOREIGN KEY (lti_consumer_key) REFERENCES lti_context(consumer_key) ON DELETE CASCADE,
  FOREIGN KEY (lti_context_id) REFERENCES lti_context(context_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_lti (
  user_id INTEGER NOT NULL,
  lti_consumer_key varchar(255),
  lti_context_id varchar(255),
  lti_user_id varchar(255),
  CONSTRAINT user_id_PK PRIMARY KEY (user_id, lti_consumer_key, lti_context_id, lti_user_id),
  FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
  FOREIGN KEY (lti_consumer_key) REFERENCES lti_user(consumer_key) ON DELETE CASCADE,
  FOREIGN KEY (lti_context_id) REFERENCES lti_user(context_id) ON DELETE CASCADE,
  FOREIGN KEY (lti_user_id) REFERENCES lti_user(user_id) ON DELETE CASCADE
);



