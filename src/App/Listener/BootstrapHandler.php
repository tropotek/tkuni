<?php
namespace App\Listener;

use Tk\EventDispatcher\SubscriberInterface;
use Tk\Event\KernelEvent;
use Tk\Kernel\KernelEvents;


/**
 * Class StartupHandler
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class BootstrapHandler implements SubscriberInterface
{
    /**
     * @var array|\Tk\Config
     */
    protected $config = null;

    /**
     * @param array|\Tk\Config
     */
    function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param KernelEvent $event
     */
    public function onInit(KernelEvent $event)
    {
        // initalise Dom Loader
        \App\Factory::getDomLoader();

        // Initiate the default database connection
        \App\Factory::getDb();
        
        // Init Auth
        $auth = \App\Factory::getAuth();
        if ($auth->getIdentity()) {
            $this->config->setUser(\App\Db\User::getMapper()->findByUsername($auth->getIdentity()));
        }
        
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::INIT => 'onInit');
    }
}