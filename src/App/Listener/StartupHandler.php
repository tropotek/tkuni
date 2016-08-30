<?php
namespace App\Listener;

use Psr\Log\LoggerInterface;
use Tk\EventDispatcher\SubscriberInterface;
use Tk\Event\KernelEvent;


/**
 * Class StartupHandler
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StartupHandler implements SubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * @param LoggerInterface $logger
     */
    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onStartup(KernelEvent $event)
    {
        $request = \App\Factory::getRequest();
        if ($this->logger) {
            $this->logger->info('------------------------------------------------');
            $this->logger->info('- Application');
            $this->logger->info('- ' . date('Y-m-d H:i:s'));
            $this->logger->info('- ' . $request->getMethod() . ': ' . $request->getUri());
            $this->logger->info('- ' . $request->getIp());
            $this->logger->info('- ' . $request->getUserAgent());
            if (\App\Factory::getSession()) {
                $this->logger->info('- Session ID: ' . \App\Factory::getSession()->getId());
                $this->logger->info('- Session Name: ' . \App\Factory::getSession()->getName());
            }

            $this->logger->info('- PHP: ' . \PHP_VERSION);
            $this->logger->info('------------------------------------------------');
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        //return array(\Tk\Kernel\KernelEvents::CONTROLLER => 'onStartup');
        return array(\Tk\Kernel\KernelEvents::INIT  => 'onStartup');
    }
}