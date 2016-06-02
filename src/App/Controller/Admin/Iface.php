<?php
namespace App\Controller\Admin;


abstract class Iface extends \App\Controller\Iface
{
    
    /**
     * @param string $pageTitle
     * @param string $access
     */
    public function __construct($pageTitle = '', $access = 'admin')
    {
        parent::__construct($pageTitle, $access);
        $this->templatePath = $this->getConfig()->getSitePath() . $this->getConfig()->get('template.admin.path');
    }
    
    /**
     * Get a new instance of the page to display the content in.
     *
     * @return \App\Page\Iface
     */
    public function getPage()
    {
        if (!$this->page) {
            $this->page = new \App\Page\AdminPage($this);
        }
        return $this->page;
    }

}