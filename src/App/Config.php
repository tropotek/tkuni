<?php
namespace App;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 */
class Config extends \Uni\Config
{


    /**
     * @param \Tk\Event\Dispatcher $dispatcher
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function setupDispatcher($dispatcher)
    {
        \App\Dispatch::create($dispatcher);
    }

    /**
     * @return \Bs\Listener\PageTemplateHandler
     */
    public function getPageTemplateHandler()
    {
        if (!$this->get('page.template.handler')) {
            $this->set('page.template.handler', new \App\Listener\PageTemplateHandler());
        }
        return $this->get('page.template.handler');
    }


    /**
     * @return bool
     */
    public function isBootsrap4()
    {
        return !preg_match('|\/admin$|', $this->get('system.theme.admin'));
        //return ($this->get('template.css.framework') == \Bs\Config::CSS_FW_BOOTSTRAP_4);
    }

    /**
     * @param $form
     * @return \Tk\Form\Renderer\Dom
     */
    public function createFormRenderer($form)
    {
        $obj = \Tk\Form\Renderer\Dom::create($form);
        $obj->setFieldGroupRenderer($this->getFormFieldGroupRenderer($form));
        //$form->addCss('form-horizontal');
        //$obj->setFieldGroupRenderer(null);
        return $obj;
    }

}