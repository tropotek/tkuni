<?php
namespace App;

use Tk\Event\Dispatcher;
use Tk\Controller\Resolver;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class FrontController extends \Uni\FrontController
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
    }

    /**
     * init Application front controller
     *
     * @throws \Tk\Exception
     */
    public function init()
    {
        parent::init();
        $dispatcher = $this->getDispatcher();

        $dispatcher->addSubscriber(new \App\Listener\NavRendererHandler());
    }

}