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
     * @var \App\Db\Institution
     */
    protected $institution = null;


    /**
     * Constructor
     *
     * @param \App\Db\Institution $institution
     */
    public function __construct($institution)
    {
        $this->institution = $institution;
        $data = $this->institution->getData();
        parent::__construct($data->get('ldapHost'), $data->get('ldapBaseDn'), $data->get('ldapFilter'), $data->get('ldapPort'), $data->get('ldapTls'));
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
        $ldapData = $r->get('ldap');
        if (!$ldapData) return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, 'Error Connecting to LDAP Server.');


        if ($ldapData[0]['auedupersontype'][0] != $this->get('userType')) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, 'Invalid user');
        }

        // Update the user record with ldap data
        /** @var \App\Db\User $user */
        $user = \App\Db\User::getMapper()->findByUsername($r->getIdentity());
        if (!$user && isset($ldapData[0]['mail'][0])) {
            // Check if there is one by email
            $user = \App\Db\User::getMapper()->findByEmail($ldapData[0]['mail'][0]);
        }

        // Create the user record if none exists....
        if (!$user) {
            // TODO: Save any extra required data, IE: `auedupersonid` (Student/Staff number)
            $role = 'student';
            // role: 'staff', 'student'
            switch ($ldapData[0]['auedupersontype'][0]) {
                case 'staff':
                    $role = 'staff';
                    break;
                case 'student':
                    $role = 'student';
                    break;
            }
            // Create new user
            \App\Factory::createNewUser(
                $username,
                $ldapData[0]['mail'][0],
                $role,
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

//            if (!empty($ldapData[0]['auedupersonid'][0]))
//                $user->uid = $ldapData[0]['auedupersonid'][0];
            $user->save();
        }
        return $r;
    }

}