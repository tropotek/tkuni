<?php
namespace App;

use Tk\Event\Dispatcher;
use Tk\Controller\Resolver;


/**
 * Class FrontController
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class FrontController extends \Tk\Kernel\HttpKernel
{

    /**
     * Constructor.
     *
     * @param Dispatcher $dispatcher
     * @param Resolver $resolver
     * @param $config
     * @throws \Tk\Exception
     */
    public function __construct(Dispatcher $dispatcher, Resolver $resolver, $config)
    {
        parent::__construct($dispatcher, $resolver);

        // initialise Dom Loader
        \App\Factory::getDomLoader();

        // Init the plugins
        \App\Factory::getPluginFactory();

        // Initiate the email gateway
        \App\Factory::getEmailGateway();

        // Initiate the plugin API object
        \App\Factory::getPluginApi();
        
        $this->init();
    }

    /**
     * init Application front controller
     * @throws \Tk\Exception
     */
    public function init()
    {
        $logger = $this->getConfig()->getLog();
        /** @var \Tk\Request $request */
        $request = $this->getConfig()->getRequest();


        // Tk Listeners
        $this->getDispatcher()->addSubscriber(new \Tk\Listener\StartupHandler($logger, $request, $this->getConfig()->getSession()));
        $matcher = new \Tk\Routing\UrlMatcher($this->getConfig()->get('site.routes'));
        $this->getDispatcher()->addSubscriber(new \Tk\Listener\RouteListener($matcher));
        $this->getDispatcher()->addSubscriber(new \Tk\Listener\PageHandler($this->getDispatcher()));
        $this->getDispatcher()->addSubscriber(new \Tk\Listener\ResponseHandler(Factory::getDomModifier()));

        // Exception Handling
        $this->getDispatcher()->addSubscriber(new \Tk\Listener\LogExceptionListener($logger));
        if (preg_match('|^/ajax/.+|', $request->getUri()->getRelativePath())) { // If ajax request
            $this->getDispatcher()->addSubscriber(new \Tk\Listener\JsonExceptionListener($this->getConfig()->isDebug()));
        } else {
            $this->getDispatcher()->addSubscriber(new \Tk\Listener\ExceptionListener($this->getConfig()->isDebug()));
        }
        if (!$this->getConfig()->isDebug()) {
            $this->getDispatcher()->addSubscriber(new \Tk\Listener\ExceptionEmailListener(\App\Factory::getEmailGateway(), $logger,
                $this->getConfig()->get('site.email'), $this->getConfig()->get('site.title')));
        }


        $sh = new \Tk\Listener\ShutdownHandler($logger, $this->getConfig()->getScriptTime());
        $sh->setPageBytes(\App\Factory::getDomFilterPageBytes());
        $this->getDispatcher()->addSubscriber($sh);

        // App Listeners
        $this->getDispatcher()->addSubscriber(new \App\Listener\AuthHandler());
        $this->getDispatcher()->addSubscriber(new \App\Listener\CrumbsHandler());
        $this->getDispatcher()->addSubscriber(new \App\Listener\MasqueradeHandler());
        $this->getDispatcher()->addSubscriber(new \App\Listener\InstitutionHandler());
        $this->getDispatcher()->addSubscriber(new \App\Listener\ActionPanelHandler());


    }

    /**
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return \App\Factory::getConfig();
    }
}