-- ------------------------------------------------------
-- Author: Michael Mifsud
-- Date: 06/04/17
-- ------------------------------------------------------


-- --------------------------------------
-- Change all passwords to 'password' for debug mode
-- --------------------------------------
-- UPDATE `user` SET `display_name` = `name`;
-- UPDATE `user` SET `hash` = MD5(CONCAT(`id`, IFNULL(`institution_id`, 0), `username`, `email`));

UPDATE `user` SET `password` = MD5(CONCAT('password', `hash`));

-- --------------------------------------
-- Disable Domains for institutions
UPDATE `institution` SET `domain` = '';

-- Disable the LDAP plugin for institutions
DELETE FROM `plugin_zone` WHERE `plugin_name` LIKE 'plg-ldap' AND `zone_name` LIKE 'institution';

-- Do this so we keep the same key all the time

INSERT INTO _data (id, fid, fkey, `key`, value) VALUES (NULL, 0, 'plg-lti', 'lti.cert.pub', '-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEA03V2VfAqz3xl0RAPpY8D
Y5dAxukdiqmSwuy1/MxkqHux9K31uJdr+PWkUseqZynTflwqCAtWiGLCPGd1Z3Gh
Gd/k3aTA7/Z33CbomCNMpYx+jLueIxFxQmihX9q6CZDbDN0aN34PXWgeRQ2NKbG7
jXZ61xFu8ijJV7GMMkdPcM7aLVD7WT3y1NunavBYSR9QBlQ9+GECyCdkrMKGs0HP
LID4rbtVDRsczs9/2no4+i7AnTpEfGUbPfratKXKdMHHxYxtRP2ETkXqYGJHvZ3A
aZJJVtZUlrZ4eJ+qxY4yxKGXSWUVBJlfksxH7V9Mo/KCBTZPgutUtaowghArsxhe
uvXghVb/iQ4AjIiYdsfCt1IXpKLBFTLC3vl05XwSMjmJoCZMx5ilCMf1gGJ50Z+8
vUkW4kljTVuVZAXnfUMWX4HPC3z6Ga1PthINq2MEC74SjEY2+ZxnqSt0hQOCTxjq
y+g6Q4rc+gB3kwG5sfho60ANW9unvVhMyGeHwhNe8Swldg4enoXFOU0sfx7VXRIi
kZZQgVtrFpAakFYl+NL0IgbQRQ751q7lhBI5fWrds6vU1PJFKLG44Y1+eR6MmREj
1X7yhAp3ixG49BBOJsWUHHuiRSitkY0QaNCsYZ/2ZfhsqhsohYF5UyMe14yJ0RDn
g5nWXqbHLwk2usEQiQfvi4kCAwEAAQ==
-----END PUBLIC KEY-----
') ON DUPLICATE KEY UPDATE fid=0, fkey='plg-lti', `key`='lti.cert.pub';
INSERT INTO _data (id, fid, fkey, `key`, value) VALUES (NULL, 0, 'plg-lti', 'lti.cert.prv', '-----BEGIN PRIVATE KEY-----
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
OhW5C//FYn55IQLJpTRFj6rnEH2IBVQ5
-----END PRIVATE KEY-----
') ON DUPLICATE KEY UPDATE fid=0, fkey='plg-lti', `key`='lti.cert.prv';








