<?php
namespace App\Page;



/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AdminPage extends Iface
{

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $nav = \App\Ui\Menu\AdminMenu::create();
        $template->replaceTemplate('nav', $nav->show());

        return $template;
    }
    
    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
//    public function __makeTemplate()
//    {
//        return \Dom\Loader::loadFile($this->getConfig()->getSitePath() . $this->getConfig()->get('template.admin'));
//    }

}