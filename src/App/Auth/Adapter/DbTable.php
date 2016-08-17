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
     * @param $username
     * @return \stdClass
     */
//    protected function getUser($username)
//    {
//        $active = '';
//        if ($this->activeColumn) {
//            $active = 'AND '.$this->db->quoteParameter($this->activeColumn).' = 1';
//        }
//        $sql = sprintf('SELECT * FROM %s WHERE %s = %s %s LIMIT 1',
//            $this->db->quoteParameter($this->tableName),
//            $this->db->quoteParameter($this->usernameColumn),
//            $this->db->quote($username),
//            $active
//        );
//
//        $stmt = $this->db->prepare($sql);
//        if (!$stmt->execute()) {
//            $errorInfo = $this->db->errorInfo();
//            $e = new \Tk\Db\Exception($errorInfo[2]);
//            $e->setDump('Dump: ' . print_r($this->db->getLastLog(), true));
//        }
//
//        return $stmt->fetchObject();
//    }

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
            $user = $this->getUser($username);
            vd($user, $this->hashPassword($password, $user), $user->{$this->passwordColumn});
            // TODO: The password should be modified/hashed before it is sent to the adapter for processing ???
            if ($user && $this->hashPassword($password, $user) == $user->{$this->passwordColumn}) {
                return new Result(Result::SUCCESS, array('username' => $username, 'institutionId' => $user->institution_id));
            }
        } catch (\Exception $e) {
            throw new \Tk\Auth\Exception('The supplied parameters failed to produce a valid sql statement, please check table and column names for validity.', 0, $e);
        }
        return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $username, 'Invalid username or password.');
    }


}
