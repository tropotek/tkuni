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
        $this->dispatcher->addSubscriber(new Listener\StartupHandler($logger));

        // (kernel.request)
        $matcher = new \Tk\Routing\UrlMatcher($this->config['site.routes']);
        $this->dispatcher->addSubscriber(new \Tk\Listener\RouteListener($matcher));
        $this->dispatcher->addSubscriber(new \App\Listener\AuthHandler());
        $this->dispatcher->addSubscriber(new \App\Listener\MasqueradeHandler());

        // (kernel.controller)
        
        // (kernel.view)

        // (kernel.response)
        $this->dispatcher->addSubscriber(new Listener\ResponseHandler(Factory::getDomModifier()));

        // (kernel.finish_request)

        // (kernel.exception)
        $this->dispatcher->addSubscriber(new \Tk\Listener\ExceptionListener($logger));
        $this->dispatcher->addSubscriber(new \App\Listener\ExceptionEmailListener($logger));

        // (kernel.terminate)
        $this->dispatcher->addSubscriber(new Listener\ShutdownHandler($logger));

    }
    
    
    
}