<?php
namespace App;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Config extends \Uni\Config
{

    /**
     * getFrontController
     *
     * @return \App\FrontController
     */
    public function getFrontController()
    {
        if (!$this->get('front.controller')) {
            $obj = new \App\FrontController($this->getEventDispatcher(), $this->getResolver(), $this);
            $this->set('front.controller', $obj);
        }
        return parent::get('front.controller');
    }

    /**
     * @return \Uni\PluginApi
     */
    public function getPluginApi()
    {
        if (!$this->get('plugin.api')) {
            $this->set('plugin.api', new \App\PluginApi());
        }
        return $this->get('plugin.api');
    }

    /**
     * A helper method to create an instance of an Auth adapter
     *
     * @param array $submittedData
     * @return \Tk\Auth\Adapter\Iface
     */
    public function getAuthDbTableAdapter($submittedData = array())
    {
        $adapter = new \App\Auth\Adapter\DbTable(
            $this->getDb(),
            \Tk\Db\Map\Mapper::$DB_PREFIX . str_replace(\Tk\Db\Map\Mapper::$DB_PREFIX, '', $this['system.auth.dbtable.tableName']),
            $this['system.auth.dbtable.usernameColumn'],
            $this['system.auth.dbtable.passwordColumn'],
            $this['system.auth.dbtable.activeColumn']);
        if (isset($submittedData['instHash'])) {
            $institution = \App\Db\InstitutionMap::create()->findByHash($submittedData['instHash']);
            $adapter->setInstitution($institution);
        }
        $adapter->setHashCallback(array(\Tk\Config::getInstance(), 'hashPassword'));
        $adapter->replace($submittedData);
        return $adapter;
    }

    /**
     * hashPassword
     *
     * @param $pwd
     * @param \App\Db\User $user (optional)
     * @return string
     * @throws \Tk\Exception
     */
    public function hashPassword($pwd, $user = null)
    {
        $salt = '';
        if ($user) {    // Use salted password
            if (method_exists($user, 'getHash'))
                $salt = $user->getHash();
            else if ($user->hash)
                $salt = $user->hash;
        }
        return $this->hash($pwd, $salt);
    }

    /**
     * Hash a string using the system config set algorithm
     *
     * @link http://php.net/manual/en/function.hash.php
     * @param string $str
     * @param string $salt (optional)
     * @param string $algo Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..)
     *
     * @return string
     */
    public function hash($str, $salt = '', $algo = 'md5')
    {
        if ($salt) $str .= $salt;
        if ($this->get('hash.function'))
            $algo = $this->get('hash.function');
        return hash($algo, $str);
    }




    // DI functions

    /**
     * Create a new user
     *
     * @param int $institutionId
     * @param string $username
     * @param string $email
     * @param string $role
     * @param string $password
     * @param string $name
     * @param string $uid
     * @param bool $active
     * @return Db\User
     * @throws \Tk\Exception
     */
    public static function createNewUser($institutionId, $username, $email, $role, $password = '', $name = '', $uid = '', $active = true)
    {
        $user = new \App\Db\User();
        $user->institutionId = $institutionId;
        $user->uid = $uid;
        $user->username = $username;
        $user->name = $name;
        $user->email = $email;
        $user->role = $role;
        if ($password)
            $user->setNewPassword($password);
        $user->active = $active;
        $user->save();

        return $user;
    }

}