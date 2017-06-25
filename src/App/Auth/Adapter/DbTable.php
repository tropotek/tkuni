<?php
namespace App\Auth\Adapter;

use Tk\Auth\Result;

/**
 * A DB table authenticator adaptor
 *
 * This adapter requires that the data values have been set
 * ```
 * $adapter->replace(array('username' => $value, 'password' => $password));
 * ```
 *
 */
class DbTable extends \Tk\Auth\Adapter\DbTable
{

    /**
     * @var \App\Db\Institution
     */
    protected $institution = null;

    /**
     * @param \App\Db\Institution $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }


    /**
     *
     * @return Result
     * @throws \Tk\Auth\Exception if answering the authentication query is impossible
     */
    public function authenticate()
    {
        $username = $this->get('username');
        $password = $this->get('password');

        if (!$username || !$password) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, $username, 'No username or password.');
        }

        try {
            $iid = 0;
            if ($this->institution)
                $iid = $this->institution->getId();
            
            /* @var \App\Db\User $user */
            $user = \App\Db\UserMap::create()->findByUsername($username, $iid);
            //$user = $this->getUser($username);

            if ($user && !$user->password) {
                // Then the user has reset the password or it is their first login?
                // WHAT TO DO????? (Send email with new validation link, same as forgo password request...)

                // 1. Email a reset password message to user with unique link to reset password page (The link should expire within 60 min and/or after they submit the form)
                throw new \Tk\Exception('Please validate your account first.');
                //  It may not get to this point yet...
            }
            
            if ($user && $this->hashPassword($password, $user) == $user->{$this->passwordColumn}) {
                return new Result(Result::SUCCESS, $user->id);
            }
        } catch (\Exception $e) {
            \Tk\Log::warning('The supplied parameters failed to produce a valid sql statement, please check table and column names for validity.');
            throw new \Tk\Auth\Exception('Authentication server error.');
        }
        return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $username, 'Invalid username or password.');
    }


}
