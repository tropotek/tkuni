<?php
namespace Uni\Listener;


/**
 * This object helps cleanup the structure of the controller code
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PageTemplateHandler extends \Bs\Listener\PageTemplateHandler
{

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function showPage(\Tk\Event\Event $event)
    {
        parent::showPage($event);
        $controller = $event->get('controller');
        if ($controller instanceof \Bs\Controller\Iface) {
            $page = $controller->getPage();
            if (!$page) return;
            $template = $page->getTemplate();
            /** @var \Uni\Db\User $user */
            $user = $controller->getUser();

            // Add anything to the page template here ...
            if ($user) {
                $template->setAttr('dashUrl', 'href', \Uni\Uri::createHomeUrl('/index.html'));
                $institutionId = $this->getConfig()->getInstitutionId();
                $subjectId = $this->getConfig()->getSubjectId();
                $js = <<<JS
config.subjectId = $subjectId;
config.institutionId = $institutionId;
JS;
                $template->appendJs($js, array('data-jsl-priority' => -1000));
            }

        }
    }


    /**
     * @return \Uni\Config|\Tk\Config
     */
    public function getConfig()
    {
        return \Uni\Config::getInstance();
    }

}