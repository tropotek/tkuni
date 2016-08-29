-- ---------------------------------
-- Install LTI SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------

CREATE TABLE lti2_consumer (
  consumer_pk INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  consumer_key256 VARCHAR(256) NOT NULL,
  consumer_key TEXT DEFAULT NULL,
  secret VARCHAR(1024) NOT NULL,
  lti_version VARCHAR(10) DEFAULT NULL,
  consumer_name VARCHAR(255) DEFAULT NULL,
  consumer_version VARCHAR(255) DEFAULT NULL,
  consumer_guid VARCHAR(1024) DEFAULT NULL,
  profile TEXT DEFAULT NULL,
  tool_proxy TEXT DEFAULT NULL,
  settings TEXT DEFAULT NULL,
  protected TINYINT(1) NOT NULL,
  enabled TINYINT(1) NOT NULL,
  enable_from TIMESTAMP DEFAULT NULL,
  enable_until TIMESTAMP DEFAULT NULL,
  last_access DATE DEFAULT NULL,
  created TIMESTAMP NOT NULL,
  updated TIMESTAMP NOT NULL,
  PRIMARY KEY (consumer_pk)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE lti2_consumer
  ADD UNIQUE INDEX lti2_consumer_consumer_key_UNIQUE (consumer_key256 ASC);

CREATE TABLE lti2_tool_proxy (
  tool_proxy_pk INT(11) NOT NULL AUTO_INCREMENT,
  tool_proxy_id VARCHAR(32) NOT NULL,
  consumer_pk INT(11) NOT NULL,
  tool_proxy TEXT NOT NULL,
  created TIMESTAMP NOT NULL,
  updated TIMESTAMP NOT NULL,
  PRIMARY KEY (tool_proxy_pk)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE lti2_tool_proxy
  ADD CONSTRAINT lti2_tool_proxy_lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
REFERENCES lti2_consumer (consumer_pk);

ALTER TABLE lti2_tool_proxy
  ADD INDEX lti2_tool_proxy_consumer_id_IDX (consumer_pk ASC);

ALTER TABLE lti2_tool_proxy
  ADD UNIQUE INDEX lti2_tool_proxy_tool_proxy_id_UNIQUE (tool_proxy_id ASC);

CREATE TABLE lti2_nonce (
  consumer_pk INT(11) NOT NULL,
  value VARCHAR(32) NOT NULL,
  expires TIMESTAMP NOT NULL,
  PRIMARY KEY (consumer_pk, value)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE lti2_nonce
  ADD CONSTRAINT lti2_nonce_lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
REFERENCES lti2_consumer (consumer_pk);

CREATE TABLE lti2_context (
  context_pk INT(11) NOT NULL AUTO_INCREMENT,
  consumer_pk INT(11) NOT NULL,
  lti_context_id VARCHAR(255) NOT NULL,
  settings TEXT DEFAULT NULL,
  created TIMESTAMP NOT NULL,
  updated TIMESTAMP NOT NULL,
  PRIMARY KEY (context_pk)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE lti2_context
  ADD CONSTRAINT lti2_context_lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
REFERENCES lti2_consumer (consumer_pk);

ALTER TABLE lti2_context
  ADD INDEX lti2_context_consumer_id_IDX (consumer_pk ASC);

CREATE TABLE lti2_resource_link (
  resource_link_pk INT(11) AUTO_INCREMENT,
  context_pk INT(11) DEFAULT NULL,
  consumer_pk INT(11) DEFAULT NULL,
  lti_resource_link_id VARCHAR(255) NOT NULL,
  settings TEXT,
  primary_resource_link_pk INT(11) DEFAULT NULL,
  share_approved TINYINT(1) DEFAULT NULL,
  created TIMESTAMP NOT NULL,
  updated TIMESTAMP NOT NULL,
  PRIMARY KEY (resource_link_pk)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE lti2_resource_link
  ADD CONSTRAINT lti2_resource_link_lti2_context_FK1 FOREIGN KEY (context_pk)
REFERENCES lti2_context (context_pk);

ALTER TABLE lti2_resource_link
  ADD CONSTRAINT lti2_resource_link_lti2_resource_link_FK1 FOREIGN KEY (primary_resource_link_pk)
REFERENCES lti2_resource_link (resource_link_pk);

ALTER TABLE lti2_resource_link
  ADD INDEX lti2_resource_link_consumer_pk_IDX (consumer_pk ASC);

ALTER TABLE lti2_resource_link
  ADD INDEX lti2_resource_link_context_pk_IDX (context_pk ASC);

CREATE TABLE lti2_user_result (
  user_pk INT(11) AUTO_INCREMENT,
  resource_link_pk INT(11) NOT NULL,
  lti_user_id VARCHAR(255) NOT NULL,
  lti_result_sourcedid VARCHAR(1024) NOT NULL,
  created TIMESTAMP NOT NULL,
  updated TIMESTAMP NOT NULL,
  PRIMARY KEY (user_pk)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE lti2_user_result
  ADD CONSTRAINT lti2_user_result_lti2_resource_link_FK1 FOREIGN KEY (resource_link_pk)
REFERENCES lti2_resource_link (resource_link_pk);

ALTER TABLE lti2_user_result
  ADD INDEX lti2_user_result_resource_link_pk_IDX (resource_link_pk ASC);

CREATE TABLE lti2_share_key (
  share_key_id VARCHAR(32) NOT NULL,
  resource_link_pk INT(11) NOT NULL,
  auto_approve TINYINT(1) NOT NULL,
  expires TIMESTAMP NOT NULL,
  PRIMARY KEY (share_key_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE lti2_share_key
  ADD CONSTRAINT lti2_share_key_lti2_resource_link_FK1 FOREIGN KEY (resource_link_pk)
REFERENCES lti2_resource_link (resource_link_pk);

ALTER TABLE lti2_share_key
  ADD INDEX lti2_share_key_resource_link_pk_IDX (resource_link_pk ASC);