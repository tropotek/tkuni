<?php
namespace App\Listener;

use Tk\Event\Subscriber;
use Tk\Kernel\KernelEvents;
use Tk\Ui\Menu\Item;
use Bs\Ui\Menu;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 */
class NavRendererHandler implements Subscriber
{

    /**
     * @param \Tk\Event\GetResponseEvent $event
     */
    public function onRequest(\Tk\Event\GetResponseEvent $event)
    {
        $config = $this->getConfig();

        $dropdownMenu = $config->getMenuManager()->getMenu('nav-dropdown');
        $sideMenu = $config->getMenuManager()->getMenu('nav-side');

        $dropdownMenu->setAttr('style', 'visibility:hidden;');
        $sideMenu->setAttr('style', 'visibility:hidden;');

        $this->initDropdownMenu($dropdownMenu);
        $this->initSideMenu($sideMenu);

    }

    /**
     * @param Menu $menu
     */
    protected function initDropdownMenu($menu)
    {
        $menu->append(Item::create('Profile', \Uni\Uri::createHomeUrl('/profile.html'), 'fa fa-user'));
        $menu->append(Item::create('About', '#', 'fa fa-info-circle')
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#aboutModal'));
        switch ($this->getRoleType()) {
            case \Uni\Db\Role::TYPE_ADMIN:
                $menu->prepend(Item::create('Site Preview', \Uni\Uri::create('/index.html'), 'fa fa-home'))->getLink()->setAttr('target', '_blank');
                $menu->append(Item::create('Settings', \Uni\Uri::createHomeUrl('/settings.html'), 'fa fa-cogs'), 'Profile');
                break;
            case \Uni\Db\Role::TYPE_CLIENT:
                break;
            case \Uni\Db\Role::TYPE_STAFF:
                break;
            case \Uni\Db\Role::TYPE_STUDENT:

                break;
        }
        $menu->append(Item::create()->addCss('divider'));
        $menu->append(Item::create('Logout', '#', 'fa fa-sign-out')
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#logoutModal'));
        //vd($menu->__toString());
    }

    /**
     * @param Menu $menu
     */
    protected function initSideMenu($menu)
    {
        $user = $this->getConfig()->getUser();
        $menu->append(Item::create('Dashboard', \Uni\Uri::createHomeUrl('/index.html'), 'fa fa-dashboard'));
        switch ($this->getRoleType()) {
            case \Uni\Db\Role::TYPE_ADMIN:
                $menu->append(Item::create('Settings', \Uni\Uri::createHomeUrl('/settings.html'), 'fa fa-cogs'));
                //$menu->append(Item::create('Institutions', \Uni\Uri::createHomeUrl('/institutionManager.html'), 'fa fa-university'));
                if ($this->getConfig()->isDebug()) {
                    $sub = $menu->append(Item::create('Development', '#', 'fa fa-bug'));
                    $sub->append(Item::create('Events', \Uni\Uri::createHomeUrl('/dev/dispatcherEvents.html'), 'fa fa-empire'));
                    $sub->append(Item::create('Forms', \Uni\Uri::createHomeUrl('/dev/forms.html'), 'fa fa-rebel'));
                }
                break;
            case \Uni\Db\Role::TYPE_CLIENT:
                $menu->append(Item::create('Settings', \Uni\Uri::createHomeUrl('/settings.html'), 'fa fa-cogs'));
                break;
            case \Uni\Db\Role::TYPE_STUDENT:
            case \Uni\Db\Role::TYPE_STAFF:
                if(!$this->getConfig()->isSubjectUrl()) {
                    if ($user->getRole()->hasPermission(\Uni\Db\Permission::MANAGE_SUBJECT)) {
                        $menu->append(Item::create('Create Subject', \Uni\Uri::createHomeUrl('/subjectEdit.html'), 'fa fa-graduation-cap'));
                    }
                    if ($user->getRole()->hasPermission(\Uni\Db\Permission::MANAGE_STAFF)) {
                        $menu->append(Item::create('Staff', \Uni\Uri::createHomeUrl('/staffManager.html'), 'fa fa-user-md'));
                    }
                } else {
                    $subject = $this->getConfig()->getSubject();
                    $sub = $menu->append(Item::create($subject->getName(), '#', 'fa fa-cog'));
                    $sub->append(Item::create('Subject Dashboard', \Uni\Uri::createSubjectUrl('/index.html', $subject), 'fa fa-dashboard'));
                    if ($user->isStaff()) {
                        $sub->append(Item::create('Settings', \Uni\Uri::createSubjectUrl('/subjectEdit.html', $subject), 'fa fa-cogs'));
                    }
                }
                break;
        }
        //vd($menu->__toString());
    }




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

            foreach ($this->getConfig()->getMenuManager()->getMenuList() as $menu) {
                $renderer = \Tk\Ui\Menu\ListRenderer::create($menu);
                $tpl = $renderer->show();
                $template->replaceTemplate($menu->getName(), $tpl);
            }
        }
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Tk\Kernel\KernelEvents::REQUEST =>  array('onRequest', 0),
            \Tk\PageEvents::PAGE_SHOW =>  array('onShow', 0)
        );
    }

    /**
     * @return \Tk\Config|\Uni\Config
     */
    public function getConfig()
    {
        return \Uni\Config::getInstance();
    }

    /**
     * @return string
     */
    public function getRoleType()
    {
        $t = 'public';
        if ($this->getConfig()->getUser())
            $t = $this->getConfig()->getUser()->getRoleType();
        return $t;
    }
}