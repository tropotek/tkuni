-- ---------------------------------
-- Install LTI SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


-- --------------------------------------------------------
--
-- Table structure for table `lti_consumer`
--
CREATE TABLE IF NOT EXISTS `lti_consumer` (
  `consumer_key` varchar(50) NOT NULL,
  `name` varchar(45) NOT NULL,
  `secret` varchar(32) NOT NULL,
  `lti_version` varchar(12) DEFAULT NULL,
  `consumer_name` varchar(255) DEFAULT NULL,
  `consumer_version` varchar(255) DEFAULT NULL,
  `consumer_guid` varchar(255) DEFAULT NULL,
  `css_path` varchar(255) DEFAULT NULL,
  `protected` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `enable_from` datetime DEFAULT NULL,
  `enable_until` datetime DEFAULT NULL,
  `last_access` date DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`consumer_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
--
-- Table structure for table `lti_context`
--
CREATE TABLE IF NOT EXISTS `lti_context` (
  `consumer_key` varchar(50) NOT NULL,
  `context_id` varchar(50) NOT NULL,
  `lti_context_id` varchar(50) DEFAULT NULL,
  `lti_resource_id` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `settings` text,
  `primary_consumer_key` varchar(50) DEFAULT NULL,
  `primary_context_id` varchar(50) DEFAULT NULL,
  `share_approved` tinyint(1) DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`consumer_key`,`context_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
--
-- Table structure for table `lti_nonce`
--
CREATE TABLE IF NOT EXISTS `lti_nonce` (
  `consumer_key` varchar(50) NOT NULL,
  `value` varchar(32) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`consumer_key`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
--
-- Table structure for table `lti_share_key`
--
CREATE TABLE IF NOT EXISTS `lti_share_key` (
  `share_key_id` varchar(32) NOT NULL,
  `primary_consumer_key` varchar(50) NOT NULL,
  `primary_context_id` varchar(50) NOT NULL,
  `auto_approve` tinyint(1) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`share_key_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
--
-- Table structure for table `lti_user`
--
CREATE TABLE IF NOT EXISTS `lti_user` (
  `consumer_key` varchar(50) NOT NULL,
  `context_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `lti_result_sourcedid` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`consumer_key`,`context_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--  


-- --------------------------------------------------------
--
-- Table structure for table `institution_lti`
--
CREATE TABLE IF NOT EXISTS `institution_lti` (
  `institution_id` INT(10) NOT NULL DEFAULT 0,
  `lti_consumer_key` varchar(255) NOT NULL,
  PRIMARY KEY (`institution_id`,`lti_consumer_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
--
-- Table structure for table `course_lti`
--
CREATE TABLE IF NOT EXISTS `course_lti` (
  `course_id` INT(10) NOT NULL DEFAULT 0,
  `lti_consumer_key` varchar(255) NOT NULL,
  `lti_context_id` varchar(255) NOT NULL,
  PRIMARY KEY (`course_id`, `lti_consumer_key`, `lti_context_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
--
-- Table structure for table `user_lti`
--
CREATE TABLE IF NOT EXISTS `user_lti` (
  `user_id` INT(10) NOT NULL DEFAULT 0,
  `lti_consumer_key` varchar(255) NOT NULL,
  `lti_context_id` varchar(255) NOT NULL,
  `lti_user_id` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`, `lti_consumer_key`, `lti_context_id`, `lti_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



