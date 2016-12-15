<?php
namespace App\Auth\Adapter;

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
class UnimelbLdap extends \Tk\Auth\Adapter\Ldap
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
        if ($r->getCode() != Result::SUCCESS)
            return $r;

        $ldapData = $r->get('ldap');
        if (!$ldapData) return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, 'Error Connecting to LDAP Server.');

        if (!$this->institution) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, 'Invalid institution.');
        }

        // Update the user record with ldap data
        $iid = $this->institution->id;

        /** @var \App\Db\User $user */
        $user = \App\Db\User::getMapper()->findByUsername($r->getIdentity(), $iid);
        if (!$user && isset($ldapData[0]['mail'][0])) {
            $user = \App\Db\User::getMapper()->findByEmail($ldapData[0]['mail'][0], $iid);
        }

        // Create the user record if none exists....
        if (!$user) {
            $role = 'student';
            if (preg_match('/(staff|student)/', strtolower($ldapData[0]['auedupersontype'][0]))) {
                $role = strtolower($ldapData[0]['auedupersontype'][0]);
            }

            // Create new user
            \App\Factory::createNewUser(
                $iid,
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
            if (!empty($ldapData[0]['auedupersonid'][0]))
                $user->uid = $ldapData[0]['auedupersonid'][0];

            $user->setPassword($password);
            $user->save();
        }

        #$r = new Result(Result::SUCCESS, array('username' => $username, 'institutionId' => $iid), 'User Found!');
        $r = new Result(Result::SUCCESS, $user->id, 'User Found!');
        return $r;
    }

}