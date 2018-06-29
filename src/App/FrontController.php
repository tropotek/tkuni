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
     * @param Dispatcher $dispatcher
     * @param Resolver $resolver
     * @param \App\Config $config
     * @throws \Tk\Exception
     */
    public function __construct(Dispatcher $dispatcher, Resolver $resolver, $config)
    {
        parent::__construct($dispatcher, $resolver);

        // Init the plugins
        $config->getPluginFactory();

        // Initiate the email gateway
        $config->getEmailGateway();

        // Initiate the plugin API object
        $config->getPluginApi();

        $this->init();
    }

    /**
     * init Application front controller
     *
     * @throws \Tk\Exception
     */
    public function init()
    {
        $config = \App\Config::getInstance();
        $logger = $config->getLog();
        $request = $config->getRequest();
        $dispatcher = $this->getDispatcher();

        if (!$config->isCli()) {
            $matcher = new \Tk\Routing\UrlMatcher($config->get('site.routes'));
            $dispatcher->addSubscriber(new \Tk\Listener\RouteListener($matcher));
            $dispatcher->addSubscriber(new \Tk\Listener\PageHandler($dispatcher));
            $dispatcher->addSubscriber(new \Tk\Listener\ResponseHandler($config->getDomModifier()));
        }

        // Tk Listeners
        $dispatcher->addSubscriber(new \Tk\Listener\StartupHandler($logger, $request, $config->getSession()));

        // Exception Handling
        $dispatcher->addSubscriber(new \Tk\Listener\LogExceptionListener($logger, true));

        if (preg_match('|^/ajax/.+|', $request->getUri()->getRelativePath())) { // If ajax request
            $dispatcher->addSubscriber(new \Tk\Listener\JsonExceptionListener($config->isDebug()));
        } else {
            $dispatcher->addSubscriber(new \Tk\Listener\ExceptionListener($config->isDebug()));
        }

        if ($config->get('system.email.exception')) {
            $dispatcher->addSubscriber(new \Tk\Listener\ExceptionEmailListener(
                $config->getEmailGateway(),
                $config->get('system.email.exception'),
                $config->get('site.title')
            ));
        }

        $sh = new \Tk\Listener\ShutdownHandler($logger, $config->getScriptTime());
        $sh->setPageBytes($config->getDomFilterPageBytes());
        $dispatcher->addSubscriber($sh);

        // App Listeners
        $dispatcher->addSubscriber(new \App\Listener\AuthHandler());
        $dispatcher->addSubscriber(new \Uni\Listener\CrumbsHandler());
        $dispatcher->addSubscriber(new \App\Listener\NavRendererHandler());
        $dispatcher->addSubscriber(new \App\Listener\MasqueradeHandler());
        $dispatcher->addSubscriber(new \App\Listener\InstitutionHandler());
        $dispatcher->addSubscriber(new \Uni\Listener\ActionPanelHandler());
        $dispatcher->addSubscriber(new \App\Listener\PageTemplateHandler());

    }

}