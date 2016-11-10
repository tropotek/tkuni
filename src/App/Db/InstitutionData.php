<?php
namespace App\Db;


/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class InstitutionData
{

    const LTI_ENABLE = 'inst.lti.enable';
    const LTI_KEY = 'inst.lti.key';
    const LTI_SECRET = 'inst.lti.secret';
    const LTI_URL = 'inst.lti.url';
    const LTI_CURRENT_KEY = 'inst.lti.currentKey';
    const LTI_CURRENT_ID = 'inst.lti.currentId';

    const LDAP_ENABLE = 'inst.ldap.enable';
    const LDAP_HOST = 'inst.ldap.host';
    const LDAP_TLS = 'inst.ldap.tls';
    const LDAP_PORT = 'inst.ldap.port';
    const LDAP_BASE_DN = 'inst.ldap.baseDn';
    const LDAP_FILTER = 'inst.ldap.filter';

    const API_ENABLE = 'inst.api.enable';
    const API_KEY = 'inst.api.key';


}