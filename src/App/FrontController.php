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
     * @var \Tk\Config
     */
    protected $config = null;


    /**
     * Constructor.
     *
     * @param Dispatcher $dispatcher
     * @param Resolver $resolver
     * @param $config
     */
    public function __construct(Dispatcher $dispatcher, Resolver $resolver, $config)
    {
        parent::__construct($dispatcher, $resolver);
        $this->config = $config;
        
        $this->init();
    }

    /**
     * init Application front controller
     * 
     */
    public function init()
    {
        $logger = $this->config->getLog();

        // (kernel.init)
        $this->dispatcher->addSubscriber(new \Tk\Listener\StartupHandler($logger, $this->config->getRequest(), $this->config->getSession()));

        // (kernel.request)
        $matcher = new \Tk\Routing\UrlMatcher($this->config['site.routes']);
        $this->dispatcher->addSubscriber(new \Tk\Listener\RouteListener($matcher));
        $this->dispatcher->addSubscriber(new \App\Listener\AuthHandler());
        $this->dispatcher->addSubscriber(new \App\Listener\MasqueradeHandler());

        // (kernel.response)
        $this->dispatcher->addSubscriber(new \Tk\Listener\ResponseHandler(Factory::getDomModifier()));

        // (kernel.exception)
        $this->dispatcher->addSubscriber(new \Tk\Listener\ExceptionListener($logger));
        $this->dispatcher->addSubscriber(new \Tk\Listener\ExceptionEmailListener(\App\Factory::getEmailGateway(), $logger, $this->config->get('site.email'), $this->config->get('site.title')));

        // (kernel.terminate)
        $sh = new \Tk\Listener\ShutdownHandler($logger, $this->config->getScriptTime());
        $sh->setPageBytes(\App\Factory::getDomFilterPageBytes());
        $this->dispatcher->addSubscriber($sh);

    }
    
    
    
}