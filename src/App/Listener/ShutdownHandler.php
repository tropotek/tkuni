<?php

namespace App\Listener;

use Psr\Log\LoggerInterface;
use Tk\EventDispatcher\SubscriberInterface;
use Tk\Event\ResponseEvent;

/**
 * Class ShutdownHandler
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ShutdownHandler implements SubscriberInterface
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

    /**
     * @param ResponseEvent $event
     */
    public function onShutdown(ResponseEvent $event)
    {
        if ($this->logger) {
            $this->logger->info('------------------------------------------------');
            $this->logger->info('Load Time: ' . round(\Tk\Config::scriptDuration(), 4) . ' sec');
            $this->logger->info('Peek Mem:  ' . self::bytes2String(memory_get_peak_usage(), 4));
            $this->logger->info('------------------------------------------------' . \PHP_EOL);
        }

    }

    /**
     * Convert a value from bytes to a human readable value
     *
     * @param int $bytes
     * @return string
     * @author http://php-pdb.sourceforge.net/samples/viewSource.php?file=twister.php
     */
    static function bytes2String($bytes, $round = 2)
    {
        $tags = array('b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $index = 0;
        while ($bytes > 999 && isset($tags[$index + 1])) {
            $bytes /= 1024;
            $index++;
        }
        $rounder = 1;
        if ($bytes < 10) {
            $rounder *= 10;
        }
        if ($bytes < 100) {
            $rounder *= 10;
        }
        $bytes *= $rounder;
        settype($bytes, 'integer');
        $bytes /= $rounder;
        if ($round > 0) {
            $bytes = round($bytes, $round);
            return sprintf('%.' . $round . 'f %s', $bytes, $tags[$index]);
        } else {
            return sprintf('%s %s', $bytes, $tags[$index]);
        }
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(\Tk\Kernel\KernelEvents::TERMINATE => 'onShutdown');
    }

}