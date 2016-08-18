<?php
namespace App\Listener;

use Psr\Log\LoggerInterface;
use Tk\EventDispatcher\SubscriberInterface;
use Tk\Event\ControllerEvent;


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

    public function onStartup(ControllerEvent $event)
    {
        if ($this->logger) {
            $this->logger->info('------------------------------------------------');
            $this->logger->info('- Application');
            $this->logger->info('- ' . date('Y-m-d H:i:s'));
            $this->logger->info('- ' . $event->getRequest()->getMethod() . ': ' . $event->getRequest()->getUri());
            $this->logger->info('- ' . $event->getRequest()->getIp());
            $this->logger->info('- ' . $event->getRequest()->getUserAgent());
            $this->logger->info('- PHP: ' . \PHP_VERSION);
            $this->logger->info('------------------------------------------------');
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(\Tk\Kernel\KernelEvents::CONTROLLER => 'onStartup');
    }
}