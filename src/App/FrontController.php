<?php
namespace App;

use Tk\EventDispatcher\EventDispatcher;
use Tk\Controller\ControllerResolver;


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
     * @param EventDispatcher $dispatcher
     * @param ControllerResolver $resolver
     * @param $config
     */
    public function __construct(EventDispatcher $dispatcher, ControllerResolver $resolver, $config)
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
        $this->dispatcher->addSubscriber(new \Ts\Listener\StartupHandler($logger, $this->config->getRequest(), $this->config->getSession()));

        // (kernel.request)
        $matcher = new \Tk\Routing\UrlMatcher($this->config['site.routes']);
        $this->dispatcher->addSubscriber(new \Tk\Listener\RouteListener($matcher));
        $this->dispatcher->addSubscriber(new \App\Listener\AuthHandler());
        $this->dispatcher->addSubscriber(new \App\Listener\MasqueradeHandler());

        // (kernel.response)
        $this->dispatcher->addSubscriber(new \Ts\Listener\ResponseHandler(Factory::getDomModifier()));

        // (kernel.exception)
        $this->dispatcher->addSubscriber(new \Tk\Listener\ExceptionListener($logger));
        $this->dispatcher->addSubscriber(new \Ts\Listener\ExceptionEmailListener(\App\Factory::getEmailGateway(), $logger, $this->config->get('site.email'), $this->config->get('site.title')));

        // (kernel.terminate)
        $sh = new \Ts\Listener\ShutdownHandler($logger, $this->config->getScriptTime());
        $sh->setPageBytes(\App\Factory::getDomFilterPageBytes());
        $this->dispatcher->addSubscriber($sh);

    }
    
    
    
}