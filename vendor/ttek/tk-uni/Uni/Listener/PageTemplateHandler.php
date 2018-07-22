<?php
namespace Uni\Listener;

use Tk\Event\Subscriber;
use Tk\Kernel\KernelEvents;

/**
 * This object helps cleanup the structure of the controller code
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PageTemplateHandler implements Subscriber
{

    /**
     * @param \Tk\Event\Event $event
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function onPageShow(\Tk\Event\Event $event)
    {
        $controller = $event->get('controller');
        if ($controller instanceof \Bs\Controller\Iface) {
            $page = $controller->getPage();
            if (!$page) return;
            $template = $page->getTemplate();
            /** @var \Bs\Db\User $user */
            $user = $controller->getUser();


            // Add anything to the page template here ...
            if ($user) {
                $template->setAttr('dashUrl', 'href', \Uni\Uri::createHomeUrl('/index.html'));
                $institutionId = $this->getConfig()->getInstitutionId();
                $subjectId = $this->getConfig()->getSubjectId();
                $role = $user->getRole();
                $js = <<<JS
config.subjectId = $subjectId;
config.institutionId = $institutionId;
config.role = '$role';
JS;
                $template->appendJs($js, array('data-jsl-priority' => -1000));
            }

        }
    }


    /**
     * @return \App\Config|\Tk\Config
     */
    public function getConfig()
    {
        return \App\Config::getInstance();
    }
    
    /**
     * getSubscribedEvents
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Tk\PageEvents::PAGE_SHOW =>  array('onPageShow', 0)
        );
    }
}