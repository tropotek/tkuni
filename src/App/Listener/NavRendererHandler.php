<?php
namespace App\Listener;

use Tk\Event\Subscriber;
use Tk\Kernel\KernelEvents;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class NavRendererHandler implements Subscriber
{

    /**
     * @param \Tk\Event\Event $event
     */
    public function onShow(\Tk\Event\Event $event)
    {
        $controller = $event->get('controller');
        if ($controller instanceof \Bs\Controller\Iface) {
            /** @var \Uni\Page $page */
            $page = $controller->getPage();
            $template = $page->getTemplate();
            if ($template->keyExists('var', 'nav')) {
                $role = $controller->getRequest()->getAttribute('role');
                if (is_array($role)) $role = current($role);
                $nav = $this->createNavbar($role);
                if ($nav) {
                    $template->replaceTemplate('nav', $nav->show());
                }
            }
        }
    }


    /**
     * @param $role
     * @return null|\App\Ui\Menu\Iface
     */
    protected function createNavbar($role)
    {
        $nav = null;
        switch ($role) {
            case \Uni\Db\User::ROLE_ADMIN:
                $nav = \App\Ui\Menu\AdminMenu::create();
                break;
            case \Uni\Db\User::ROLE_CLIENT:
                $nav = \App\Ui\Menu\ClientMenu::create();
                break;
            case \Uni\Db\User::ROLE_STAFF:
                $nav = \App\Ui\Menu\StaffMenu::create();
                break;
            case \Uni\Db\User::ROLE_STUDENT:
                $nav = \App\Ui\Menu\StudentMenu::create();
                break;
        }
        return $nav;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Tk\PageEvents::PAGE_SHOW =>  array('onShow', 0)
        );
    }

}