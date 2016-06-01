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
        
        // Register Error handlers
//        ErrorHandler::register();
//        ExceptionHandler::register($this->config->isDebug());
//        if ($this->config->isDebug()) {
//            Debug::enable();
//        }

        // (kernel.init)
        $this->dispatcher->addSubscriber(new Listener\BootstrapHandler($this->config));
        
        
        // (kernel.request)
        $matcher = new \Tk\Routing\UrlMatcher($this->config['site.routes']);
        $this->dispatcher->addSubscriber(new \Tk\Listener\RouteListener($matcher));
        $this->dispatcher->addSubscriber(new Listener\StartupHandler($logger));

        
        // Auth events
        $this->dispatcher->addSubscriber(new \App\Listener\AuthHandler());
        
        // (kernel.controller)
//        $this->dispatcher->addSubscriber(new \Tk\Lti\Listener\LtiHandler());
//        $this->dispatcher->addSubscriber(new \App\Listener\AuthHandler());


        // (kernel.view)
//        $this->dispatcher->addSubscriber(new DomModifierHandler($this->config->getDomModifier()));
//        $this->dispatcher->addSubscriber(new StringResponseHandler());
//        $this->dispatcher->addSubscriber(new DomTemplateResponseHandler());


        // (kernel.response)
        $this->dispatcher->addSubscriber(new Listener\ResponseHandler(Factory::getDomModifier()));
//        $this->dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener('UTF-8'));
//        $this->dispatcher->addSubscriber(new HttpKernel\EventListener\StreamedResponseListener());


        // (kernel.finish_request)
        
        
        // (kernel.exception)
        $this->dispatcher->addSubscriber(new \Tk\Listener\ExceptionListener($logger));

        
        // (kernel.terminate)
        $this->dispatcher->addSubscriber(new Listener\ShutdownHandler($logger));
        
        
    }
    

    /**
     * Get the current script running time in seconds
     *
     * @return string
     */
    public static function scriptDuration()
    {
        return (string)(microtime(true) - \Tk\Config::getInstance()->getScripTime());
    }
    
    
    
}