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

}