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
    static $LTI_DB_PREFIX = '';

    /**
     * @var \Tk\Config
     */
    static $config = null;


    /**
     * getConfig
     *
     * @param string $sitePath
     * @param string $siteUrl
     * @return \Tk\Config
     */
    public static function getConfig($sitePath = '', $siteUrl = '')
    {
        if (!self::$config) {
            self::$config = \Tk\Config::getInstance($sitePath, $siteUrl);
            include(self::$config->getSrcPath() . '/config/config.php');
        }
        return self::$config;
    }

    /**
     * getRequest
     *
     * @return \IMSGlobal\LTI\ToolProvider\DataConnector\DataConnector_pdo
     */
    public static function getLtiDataConnector()
    {
        if (!self::getConfig()->getLtiDataConnector()) {
            $obj = \IMSGlobal\LTI\ToolProvider\DataConnector\DataConnector::getDataConnector(self::$LTI_DB_PREFIX, self::getDb(), 'pdo');
            self::getConfig()->setLtiDataConnector($obj);
        }
        return self::getConfig()->getLtiDataConnector();
    }

    /**
     * getRequest
     *
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
     * getCookie
     *
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
     * getSession
     *
     * @return \Tk\Session
     */
    public static function getSession()
    {
        if (!self::getConfig()->getSession()) {
            $adapter = null;
            $adapter = new \Tk\Session\Adapter\Database(self::getDb(), new \Tk\Encrypt());
            $obj = \Tk\Session::getInstance($adapter, self::getConfig(), self::getRequest(), self::getCookie());
            self::getConfig()->setSession($obj);
        }
        return self::getConfig()->getSession();
    }

    /**
     * getPluginFactory
     *
     * @return \Tk\Plugin\Factory
     */
    public static function getPluginFactory()
    {
        if (!self::getConfig()->getPluginFactory()) {
            self::getConfig()->setPluginFactory(\Tk\Plugin\Factory::getInstance(self::getDb(), self::getConfig()->getPluginPath(), self::getEventDispatcher()));
        }
        return self::getConfig()->getPluginFactory();
    }

    /**
     * getEmailGateway
     *
     * @return \Tk\Mail\Gateway
     */
    public static function getEmailGateway()
    {
        if (!self::getConfig()->getEmailGateway()) {
            $gateway = new \Tk\Mail\Gateway(self::getConfig());
            $gateway->setDispatcher(self::getEventDispatcher());
            self::getConfig()->setEmailGateway($gateway);
        }
        return self::getConfig()->getEmailGateway();
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
//                $logger = $config->getLog();
//                if ($config->getLog() && $config->isDebug()) {
//                    $pdo->setOnLogListener(function ($entry) use ($config->getLog()) {
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
            $config = self::getConfig();
            $dm->add(new \Dom\Modifier\Filter\UrlPath($config->getSiteUrl()));
            $dm->add(new \Dom\Modifier\Filter\JsLast());
            $dm->add(new \Dom\Modifier\Filter\Less($config->getSitePath(), $config->getSiteUrl(), $config->getCachePath(),
                array('siteUrl' => $config->getSiteUrl(), 'dataUrl' => $config->getDataUrl(), 'templateUrl' => $config->getTemplateUrl())));
            if (self::getConfig()->isDebug()) {
                $dm->add(self::getDomFilterPageBytes());
            }
            self::getConfig()->setDomModifier($dm);
        }
        return self::getConfig()->getDomModifier();
    }

    /**
     * @return \Dom\Modifier\Filter\PageBytes
     */
    public static function getDomFilterPageBytes()
    {
        if (!self::getConfig()->getDomFilterPageBytes()) {
            $obj = new \Dom\Modifier\Filter\PageBytes(self::getConfig()->getSitePath());
            self::getConfig()->setDomFilterPageBytes($obj);
        }
        return self::getConfig()->getDomFilterPageBytes();
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
     * getFrontController
     *
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
     * getEventDispatcher
     *
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
     * getControllerResolver
     *
     * @return \Tk\Controller\Resolver
     */
    public static function getControllerResolver()
    {
        if (!self::getConfig()->getControllerResolver()) {
            $obj = new \Tk\Controller\Resolver(self::getConfig()->getLog());
            self::getConfig()->setControllerResolver($obj);
        }
        return self::getConfig()->getControllerResolver();
    }
    
    
    /**
     * getAuth
     *
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
            case '\App\Auth\Adapter\UnimelbLdap':
                if (!isset($submittedData['instHash'])) return null;
                $institution = \App\Db\InstitutionMap::create()->findByHash($submittedData['instHash']);
                if (!$institution || !$institution->getData()->get(\App\Db\InstitutionData::LDAP_ENABLE)) return null;
                $adapter = new \App\Auth\Adapter\UnimelbLdap($institution);
                break;
            case '\App\Auth\Adapter\DbTable':
                $adapter = new \App\Auth\Adapter\DbTable(
                    $config->getDb(),
                    \Tk\Db\Map\Mapper::$DB_PREFIX . str_replace(\Tk\Db\Map\Mapper::$DB_PREFIX, '', $config['system.auth.dbtable.tableName']),
                    $config['system.auth.dbtable.usernameColumn'],
                    $config['system.auth.dbtable.passwordColumn'],
                    $config['system.auth.dbtable.activeColumn']);
                $adapter->setHashCallback(array(__CLASS__, 'hashPassword'));
                break;
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
     * hashPassword
     *
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
        $h = self::hash($pwd);
        return $h;
    }

    /**
     * Hash a string using the system config set algorithm
     *
     * @link http://php.net/manual/en/function.hash.php
     * @param string $pwd
     * @param \App\Db\User $user (optional)
     * @return string
     */
    static public function hash($pwd, $user = null)
    {
        if (self::getConfig()->get('hash.function'))
            return hash(self::getConfig()->get('hash.function'), $pwd);
        return hash('md5', $pwd);
    }
    
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
     * @todo Save any extra required data, IE: `auedupersonid` (Student/Staff Number)
     */
    static function createNewUser($institutionId, $username, $email, $role, $password, $name = '', $uid = '', $active = true)
    {
        $user = new \App\Db\User();
        $user->institutionId = $institutionId;
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

    /**
     * Helper Method
     * Make a default HTML template to create HTML emails
     * usage:
     *  $message->setBody($message->createHtmlTemplate($bodyStr));
     *
     * @param string $body
     * @param bool $showFooter
     * @return string
     * @todo: Probably not the best place for this..... Dependant on the App
     */
    static function createMailTemplate($body, $showFooter = true)
    {
        $request = self::getRequest();

        $foot = '';
        if (!self::getConfig()->isCli() && $showFooter) {
            $foot .= sprintf('<i>Page:</i> <a href="%s">%s</a><br/>', $request->getUri()->toString(), $request->getUri()->toString());
            if ($request->getReferer()) {
                $foot .= sprintf('<i>Referer:</i> <span>%s</span><br/>', $request->getReferer()->toString());
            }
            $foot .= sprintf('<i>IP Address:</i> <span>%s</span><br/>', $request->getIp());
            $foot .= sprintf('<i>User Agent:</i> <span>%s</span>', $request->getUserAgent());
        }

        $defaultHtml = sprintf('
<html>
<head>
  <title>Email</title>

<style type="text/css">
body {
  font-family: arial,sans-serif;
  font-size: 80%%;
  padding: 5px;
  background-color: #FFF;
}
table {
  font-size: 0.9em;
}
th, td {
  vertical-align: top;
}
table {

}
th {
  text-align: left;
}
td {
  padding: 4px 5px;
}
.content {
  padding: 0px 0px 0px 20px;
}
p {
  margin: 0px 0px 10px 0px;
  padding: 0px;
}
</style>
</head>
<body>
  <div class="content">%s</div>
  <p>&#160;</p>
  <hr />
  <div class="footer">
    <p>
      %s
    </p>
  </div>
</body>
</html>', $body, $foot);

        return $defaultHtml;
    }
}