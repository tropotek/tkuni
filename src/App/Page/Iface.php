<?php
namespace App\Page;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Iface extends \Dom\Renderer\Renderer
{

    /**
     * @var \App\Controller\Iface
     */
    protected $controller = null;
    
    
    /**
     * Iface constructor.
     *
     * @param \App\Controller\Iface $controller
     */
    public function __construct(\App\Controller\Iface $controller)
    {
        $this->controller = $controller;
        $this->setPageHeading($this->getController()->getPageTitle());

        // TODO: Check this call should be here, or called externally????
        // It could lead to possible rendering issues....
        $this->show();
    }

    /**
     * 
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->controller->getTemplatePath();
    }

    /**
     *
     */
    public function showAlerts()
    {
        // Alert boxes
        $noticeTpl = $notice = \App\Alert::getInstance()->show()->getTemplate();
        $this->getTemplate()->insertTemplate('alerts', $noticeTpl);
    }

    /**
     * Set the page heading, should be set from main controller
     *
     * @param $heading
     * @return $this
     * @throws \Dom\Exception
     */
    public function setPageHeading($heading)
    {
        if (!$heading) return $this;
        /** @var \Dom\Template $template */
        $template = $this->getTemplate();
        $template->setTitleText($heading . ' - ' . $template->getTitleText());
        $template->insertText('pageHeading', $heading);
        $template->setChoice('pageHeading');
        return $this;
    }

    /**
     * Set the page Content
     *
     * @param string|\Dom\Template|\Dom\Renderer\Iface|\DOMDocument $content
     * @return PublicPage
     */
    public function setPageContent($content)
    {
        if (!$content) return $this;
        if ($content instanceof \Dom\Template) {
            $this->getTemplate()->appendTemplate('content', $content);
        } else if ($content instanceof \Dom\Renderer\Iface) {
            $this->getTemplate()->appendTemplate('content', $content->getTemplate());
        } else if ($content instanceof \DOMDocument) {
            $this->getTemplate()->insertDoc('content', $content);
        } else if (is_string($content)) {
            $this->template->insertHtml('content', $content);
        }
        return $this;
    }

    /**
     * @return \App\Controller\Iface
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the global config object.
     *
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return \Tk\Config::getInstance();
    }

}