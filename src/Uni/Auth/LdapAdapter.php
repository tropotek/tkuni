<?php
namespace Uni\Auth;

use Tk\Auth\Result;

/**
 * LDAP Authentication adapter
 *
 * This adapter requires that the data values have been set
 * ```
 * $adapter->replace(array('username' => $value, 'password' => $password));
 * ```
 *
 *
 */
class LdapAdapter extends \Tk\Auth\Adapter\Ldap
{

    /**
     * Constructor
     *
     * @param string $host
     * @param string $baseDn
     * @param string $filter
     * @param int $port
     * @param bool $tls
     */
    public function __construct($host, $baseDn, $filter, $port = 389, $tls = false)
    {
        parent::__construct($host, $baseDn, $filter, $port, $tls);
    }


    /**
     * Authenticate the user
     *
     * @throws \Tk\Auth\Exception
     * @return Result
     */
    public function authenticate()
    {
        $username = $this->get('username');
        $password = $this->get('password');
        
        /** @var \Tk\Auth\Result $r */
        $r = parent::authenticate();
        $ldapData = $r->getParam('ldap');
        if (!$ldapData) return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, 'Error Connecting to LDAP Server.');;

        // Update the user record with ldap data
        $user = \App\Db\User::getMapper()->findByUsername($r->getIdentity());
        if (!$user && isset($ldapData[0]['mail'][0])) {
            // Check if there is one by email
            $user = \App\Db\User::getMapper()->findByEmail($ldapData[0]['mail'][0]);
        }

        // Create the user record if none exists....
        if (!$user) {
            // TODO: Save any extra required data, IE: `auedupersonid` (Student/Staff number)
            $roles = array('user');
            // role: 'staff', 'student' 'others'
            switch ($ldapData[0]['auedupersontype'][0]) {
                case 'staff':
                    $roles[] = 'staff';
                    break;
                case 'student':
                    $roles[] = 'student';
                    break;
            }
            // Create new user
            \App\Factory::createNewUser(
                $username,
                $ldapData[0]['mail'][0],
                $roles,
                $password,
                $ldapData[0]['displayname'][0],
                $ldapData[0]['auedupersonid'][0]
            );
        } else {
            // Update User info if record already exists
            $user->username = $username;
            if (!empty($ldapData[0]['mail'][0]))
                $user->email = $ldapData[0]['mail'][0];
            $user->setPassword($password);
            if (!empty($ldapData[0]['auedupersonid'][0]))
                $user->uid = $ldapData[0]['auedupersonid'][0];
            $user->save();
        }
        return $r;
    }

}