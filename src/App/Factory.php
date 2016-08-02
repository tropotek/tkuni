<?php
namespace App;
use Tk\Db\Pdo;

/**
 * Class Factory
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Factory
{
    
    /**
     * @param string $sitePath
     * @param string $siteUrl
     * @return \Tk\Config
     */
    public static function getConfig($sitePath = '', $siteUrl = '')
    {
        return \Tk\Config::getInstance($sitePath, $siteUrl);
    }

    /**
     * @return \Tk\Request
     */
    public static function getRequest()
    {
        if (!self::getConfig()->getRequest()) {
            $obj = \Tk\Request::create();
            $obj->setAttribute('config', self::getConfig());;
            self::getConfig()->setRequest($obj);
        }
        return self::getConfig()->getRequest();
    }

    /**
     * @return \Tk\Cookie
     */
    public static function getCookie()
    {
        if (!self::getConfig()->getCookie()) {
            $obj = new \Tk\Cookie(self::getConfig()->getSiteUrl());
            self::getConfig()->setCookie($obj);
        }
        return self::getConfig()->getCookie();
    }

    /**
     * @return \Tk\Session
     */
    public static function getSession()
    {
        if (!self::getConfig()->getSession()) {
            $adapter = null;
            //$adapter = new \Tk\Session\Adapter\Database( self::getDb(), 'session', new \Tk\Encrypt());
            $obj = new \Tk\Session($adapter, self::getConfig(), self::getRequest(), self::getCookie());
            self::getConfig()->setSession($obj);
        }
        return self::getConfig()->getSession();
    }

    
    /**
     * getDb
     * Ways to get the db after calling this method
     *
     *  - \App\Factory::getDb()                 // Application level call
     *  - \Tk\Config::getInstance()->getDb()    //
     *  - \Tk\Db\Pdo::getInstance()             //
     *
     * Note: If you are creating a base lib then the DB really should be sent in via a param or method.
     *
     * @param string $name
     * @return mixed|Pdo
     */
    public static function getDb($name = 'default')
    {
        $config = self::getConfig();
        if (!$config->getDb() && $config->has('db.type')) {
            try {
                $pdo = Pdo::getInstance($name, $config->getGroup('db'));
                $logger = $config->getLog();
//                if ($logger && $config->isDebug()) {
//                    $pdo->setOnLogListener(function ($entry) use ($logger) {
//                        $logger->debug('[' . round($entry['time'], 4) . 'sec] ' . $entry['query']);
//                    });
//                }
                $config->setDb($pdo);
            } catch (\Exception $e) {
                error_log('<p>' . $e->getMessage() . '</p>');
                exit;
            }
            self::getConfig()->setDb($pdo);
        }
        return self::getConfig()->getDb();
    }
    
    /**
     * get a dom Modifier object
     * 
     * @return \Dom\Modifier\Modifier
     */
    public static function getDomModifier()
    {
        if (!self::getConfig()->getDomModifier()) {
            $dm = new \Dom\Modifier\Modifier();
            $dm->add(new \Dom\Modifier\Filter\UrlPath(self::getConfig()->getSiteUrl()));
            $dm->add(new \Dom\Modifier\Filter\JsLast());
            $dm->add(new \Dom\Modifier\Filter\Less(self::getConfig()));
            self::getConfig()->setDomModifier($dm);
        }
        return self::getConfig()->getDomModifier();
    }

    /**
     * getDomLoader
     * 
     * @return \Dom\Loader
     */
    public static function getDomLoader()
    {   
        if (!self::getConfig()->getDomLoader()) {
            $dl = \Dom\Loader::getInstance()->setParams(self::getConfig()->all());
            $dl->addAdapter(new \Dom\Loader\Adapter\DefaultLoader());
            if (self::getConfig()->getTemplatePath()) {
                $dl->addAdapter(new \Dom\Loader\Adapter\ClassPath(self::getConfig()->getTemplatePath() . '/xtpl'));
            }
            self::getConfig()->setDomLoader($dl);
        }
        return self::getConfig()->getDomLoader();
    }

    /**
     * @return \App\FrontController
     */
    public static function getFrontController()
    {
        if (!self::getConfig()->getFrontController()) {
            $obj = new \App\FrontController(self::getEventDispatcher(), self::getControllerResolver(), self::getConfig());
            self::getConfig()->setFrontController($obj);
        }
        return self::getConfig()->getFrontController();
    }


    /**
     * @return \Tk\EventDispatcher\EventDispatcher
     */
    public static function getEventDispatcher()
    {
        if (!self::getConfig()->getEventDispatcher()) {
            $obj = new \Tk\EventDispatcher\EventDispatcher(self::getConfig()->getLog());
            self::getConfig()->setEventDispatcher($obj);
        }
        return self::getConfig()->getEventDispatcher();
    }

    /**
     * @return \Tk\Controller\ControllerResolver
     */
    public static function getControllerResolver()
    {
        if (!self::getConfig()->getControllerResolver()) {
            $obj = new \Tk\Controller\ControllerResolver(self::getConfig()->getLog());
            self::getConfig()->setControllerResolver($obj);
        }
        return self::getConfig()->getControllerResolver();
    }
    
    
    /**
     * @return \Tk\Auth
     */
    public static function getAuth()
    {
        if (!self::getConfig()->getAuth()) {
            $obj = new \Tk\Auth(new \Tk\Auth\Storage\SessionStorage(self::getConfig()->getSession()));
            self::getConfig()->setAuth($obj);
        }
        return self::getConfig()->getAuth();
    }
    
    /**
     * A helper method to create an instance of an Auth adapter
     *
     *
     * @param string $class
     * @param array $submittedData
     * @return \Tk\Auth\Adapter\Iface
     * @throws \Tk\Auth\Exception
     */
    static function getAuthAdapter($class, $submittedData = array())
    {
        $config = self::getConfig();
        
        /** @var \Tk\Auth\Adapter\Iface $adapter */
        $adapter = null;
        switch ($class) {
//            case '\Tk\Auth\Adapter\Config':
//                $adapter = new \Tk\Auth\Adapter\Config(
//                    $config['system.auth.config.username'],
//                    $config['system.auth.config.password']);
//                break;
//            case '\Tk\Auth\Adapter\Ldap':
//                $adapter = new \Tk\Auth\Adapter\Ldap(
//                    $config['system.auth.ldap.host'],
//                    $config['system.auth.ldap.baseDn'],
//                    $config['system.auth.ldap.filter'],
//                    $config['system.auth.ldap.port'],
//                    $config['system.auth.ldap.tls']);
//                break;
            case '\Uni\Auth\LdapAdapter':
                if (!isset($submittedData['institutionId'])) return null;
                // TODO: we need to get the LDAP settings from the institution config table
//                $adapter = new \Uni\Auth\LdapAdapter(
//                    $config['system.auth.ldap.host'],
//                    $config['system.auth.ldap.baseDn'],
//                    $config['system.auth.ldap.filter'],
//                    $config['system.auth.ldap.port'],
//                    $config['system.auth.ldap.tls']);
                $institution = \App\Db\Institution::getMapper()->find($submittedData['institutionId']);
                if (!$institution || !$institution->getData()->get('ldapHost')) return null;
                $adapter = new \Uni\Auth\LdapAdapter($institution);
                break;
            case '\Tk\Auth\Adapter\DbTable':
                //if (isset($submittedData['institutionId'])) return null;
                $adapter = new \Tk\Auth\Adapter\DbTable(
                    $config->getDb(),
                    $config['system.auth.dbtable.tableName'],
                    $config['system.auth.dbtable.usernameColumn'],
                    $config['system.auth.dbtable.passwordColumn'],
                    $config['system.auth.dbtable.activeColumn']);
                //$adapter->setHashFunction($config['hash.function']);
                $adapter->setHashCallback(array(__CLASS__, 'hashPassword'));
                break;
//            case '\Tk\Auth\Adapter\Trapdoor':
//                $adapter = new \Tk\Auth\Adapter\Trapdoor();
//                break;
            default:
                if (class_exists($class))
                    $adapter = new $class();
        }
        if (!$adapter) {
            throw new \Tk\Auth\Exception('Cannot locate adapter class: ' . $class);
        }
        $adapter->replace($submittedData);
        return $adapter;
    }

    /**
     * @param $pwd
     * @param \App\Db\User $user (optional)
     * @return string
     */
    static public function hashPassword($pwd, $user = null)
    {
        if ($user) {    // Use salted password
            if (method_exists($user, 'getHash'))
                $pwd = $pwd . $user->getHash();
            else if ($user->hash)
                $pwd = $pwd . $user->hash;
        }
        $h = hash('md5', $pwd);
        return $h;
    }
    
    /**
     * Create a new user
     *
     * @param string $username
     * @param string $email
     * @param string $role
     * @param string $password
     * @param string $name
     * @param string $uid
     * @param bool $active
     * @return Db\User
     * @todo Save any extra required data, IE: `auedupersonid` (Student/Staff Number)
     */
    static function createNewUser($username, $email, $role, $password, $name = '', $uid = '', $active = true)
    {
        $user = new \App\Db\User();
        $user->uid = $uid;
        $user->username = $username;
        $user->name = $name;
        $user->email = $email;
        $user->role = $role;
        $user->setPassword($password);
        $user->active = $active;
        $user->save();

        return $user;
    }
    
}