<?php
namespace App\Listener;

use Tk\Event\Subscriber;
use Tk\Kernel\KernelEvents;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class CrumbsHandler implements Subscriber
{

    /**
     * @param \Tk\Event\ControllerEvent $event
     */
    public function onController(\Tk\Event\ControllerEvent $event)
    {
        $crumbs = \App\Factory::getCrumbs();
        if (!$crumbs) return;

        /** @var \App\Controller\Iface $controller */
        $controller = $event->getController();
        if ($controller instanceof \App\Controller\Iface) {
            // ignore adding crumbs if param in request URL
            if (\App\Factory::getRequest()->has(\App\Ui\Crumbs::CRUMB_IGNORE)) {
                return;
            }
            $title = $controller->getPageTitle();
            if ($title == '') {
                $title = 'Dashboard';
            }
            $crumbs->trimByTitle($title);
            $crumbs->addCrumb($title, \Tk\Uri::create());
        }
    }

    /**
     * @param \Tk\Event\Event $event
     */
    public function onShow(\Tk\Event\Event $event)
    {
        $controller = $event->get('controller');
        if ($controller instanceof \App\Controller\Iface) {
            /** @var \App\Page\Iface $page */
            $page = $controller->getPage();
            /** @var \App\Ui\Crumbs $crumbs */
            $crumbs = \App\Factory::getCrumbs();
            if (!$crumbs) return;

            $template = $page->getTemplate();
            $backUrl = $crumbs->getBackUrl();
            $js = <<<JS
config.backUrl = '$backUrl';
JS;
            $template->appendjs($js, array('data-jsl-priority' => '-999'));

            $js = <<<JS
jQuery(function($) {
  $('a.btn.back').attr('href', config.backUrl);
});
JS;
            $template->appendjs($js);

            if ($template->keyExists('var', 'breadcrumb')) {
                $template->replaceTemplate('breadcrumb', $crumbs->show());
                $template->setChoice('breadcrumb');
            }
        }
    }

    /**
     * @param \Tk\Event\RequestEvent $event
     */
    public function onFinishRequest(\Tk\Event\RequestEvent $event)
    {
        \App\Factory::saveCrumbs();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onController', 0),
            \Tk\PageEvents::CONTROLLER_SHOW =>  array('onShow', 0),
            KernelEvents::FINISH_REQUEST => 'onFinishRequest'
        );
    }

}