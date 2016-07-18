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
     * Set the page heading, should be set from main controller
     *
     * @return $this
     * @throws \Dom\Exception
     */
    protected function initPage()
    {
        /** @var \Dom\Template $template */
        $template = $this->getTemplate();


        if ($this->getConfig()->get('site.title')) {
            $template->setAttr('siteName', 'title', $this->getConfig()->get('site.title'));
            $template->setTitleText(trim($template->getTitleText() . ' - ' . $this->getConfig()->get('site.title'), '- '));
        }
        if ($this->getController()->getPageTitle()) {
            $template->setTitleText(trim($this->getController()->getPageTitle() . ' - ' . $template->getTitleText(), '- '));
            $template->insertText('pageHeading', $this->getController()->getPageTitle());
            $template->setChoice('pageHeading');
        }
        if ($this->getConfig()->isDebug()) {
            $template->setTitleText(trim('DEBUG: ' . $template->getTitleText(), '- '));
        }

        if ($this->controller->getUser()) {
            $template->setChoice('logout');
        } else {
            $template->setChoice('login');
        }

        if (\App\Alert::hasMessages()) {
            $noticeTpl = \App\Alert::getInstance()->show()->getTemplate();
            $template->replaceTemplate('alerts', $noticeTpl)->setChoice('alerts');
            $template->setChoice('alerts');
        }

        $siteUrl = $this->getConfig()->getSiteUrl();
        $dataUrl = $this->getConfig()->getDataUrl();

        $js = <<<JS

var config = {
  siteUrl : '$siteUrl',
  dataUrl : '$dataUrl',
  themeUrl: '' 
};
JS;
        $template->appendJs($js, ['data-jsl-priority' => -1000]);


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