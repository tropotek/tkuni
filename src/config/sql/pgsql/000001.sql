-- ---------------------------------
-- Install LTI SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------

-- TODO: Have to convert this for pgsql from mysql

CREATE TABLE _lti2_consumer (
  consumer_pk int(11) NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  consumer_key256 varchar(256) NOT NULL,
  consumer_key text DEFAULT NULL,
  secret varchar(1024) NOT NULL,
  lti_version varchar(10) DEFAULT NULL,
  consumer_name varchar(255) DEFAULT NULL,
  consumer_version varchar(255) DEFAULT NULL,
  consumer_guid varchar(1024) DEFAULT NULL,
  profile text DEFAULT NULL,
  tool_proxy text DEFAULT NULL,
  settings text DEFAULT NULL,
  protected tinyint(1) NOT NULL,
  enabled tinyint(1) NOT NULL,
  enable_from datetime DEFAULT NULL,
  enable_until datetime DEFAULT NULL,
  last_access date DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (consumer_pk)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE _lti2_consumer
  ADD UNIQUE INDEX _lti2_consumer_consumer_key_UNIQUE (consumer_key256 ASC);

CREATE TABLE _lti2_tool_proxy (
  tool_proxy_pk int(11) NOT NULL AUTO_INCREMENT,
  tool_proxy_id varchar(32) NOT NULL,
  consumer_pk int(11) NOT NULL,
  tool_proxy text NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (tool_proxy_pk)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE _lti2_tool_proxy
  ADD CONSTRAINT _lti2_tool_proxy__lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
REFERENCES _lti2_consumer (consumer_pk);

ALTER TABLE _lti2_tool_proxy
  ADD INDEX _lti2_tool_proxy_consumer_id_IDX (consumer_pk ASC);

ALTER TABLE _lti2_tool_proxy
  ADD UNIQUE INDEX _lti2_tool_proxy_tool_proxy_id_UNIQUE (tool_proxy_id ASC);

CREATE TABLE _lti2_nonce (
  consumer_pk int(11) NOT NULL,
  value varchar(32) NOT NULL,
  expires datetime NOT NULL,
  PRIMARY KEY (consumer_pk, value)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE _lti2_nonce
  ADD CONSTRAINT _lti2_nonce__lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
REFERENCES _lti2_consumer (consumer_pk);

CREATE TABLE _lti2_context (
  context_pk int(11) NOT NULL AUTO_INCREMENT,
  consumer_pk int(11) NOT NULL,
  lti_context_id varchar(255) NOT NULL,
  settings text DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (context_pk)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE _lti2_context
  ADD CONSTRAINT _lti2_context__lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
REFERENCES _lti2_consumer (consumer_pk);

ALTER TABLE _lti2_context
  ADD INDEX _lti2_context_consumer_id_IDX (consumer_pk ASC);

CREATE TABLE _lti2_resource_link (
  resource_link_pk int(11) AUTO_INCREMENT,
  context_pk int(11) DEFAULT NULL,
  consumer_pk int(11) DEFAULT NULL,
  lti_resource_link_id varchar(255) NOT NULL,
  settings text,
  primary_resource_link_pk int(11) DEFAULT NULL,
  share_approved tinyint(1) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (resource_link_pk)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE _lti2_resource_link
  ADD CONSTRAINT _lti2_resource_link__lti2_context_FK1 FOREIGN KEY (context_pk)
REFERENCES _lti2_context (context_pk);

ALTER TABLE _lti2_resource_link
  ADD CONSTRAINT _lti2_resource_link__lti2_resource_link_FK1 FOREIGN KEY (primary_resource_link_pk)
REFERENCES _lti2_resource_link (resource_link_pk);

ALTER TABLE _lti2_resource_link
  ADD INDEX _lti2_resource_link_consumer_pk_IDX (consumer_pk ASC);

ALTER TABLE _lti2_resource_link
  ADD INDEX _lti2_resource_link_context_pk_IDX (context_pk ASC);

CREATE TABLE _lti2_user_result (
  user_pk int(11) AUTO_INCREMENT,
  resource_link_pk int(11) NOT NULL,
  lti_user_id varchar(255) NOT NULL,
  lti_result_sourcedid varchar(1024) NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (user_pk)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE _lti2_user_result
  ADD CONSTRAINT _lti2_user_result__lti2_resource_link_FK1 FOREIGN KEY (resource_link_pk)
REFERENCES _lti2_resource_link (resource_link_pk);

ALTER TABLE _lti2_user_result
  ADD INDEX _lti2_user_result_resource_link_pk_IDX (resource_link_pk ASC);

CREATE TABLE _lti2_share_key (
  share_key_id varchar(32) NOT NULL,
  resource_link_pk int(11) NOT NULL,
  auto_approve tinyint(1) NOT NULL,
  expires datetime NOT NULL,
  PRIMARY KEY (share_key_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE _lti2_share_key
  ADD CONSTRAINT _lti2_share_key__lti2_resource_link_FK1 FOREIGN KEY (resource_link_pk)
REFERENCES _lti2_resource_link (resource_link_pk);

ALTER TABLE _lti2_share_key
  ADD INDEX _lti2_share_key_resource_link_pk_IDX (resource_link_pk ASC);