<?php
namespace App\Controller;

use Tk\Request;
/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Index extends Iface
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Home');
    }

    /**
     * @param Request $request
     * @return \App\Page\Iface
     */
    public function doDefault(Request $request)
    {
        // TODO: 
        
        
        
        
        

        return $this->show();
    }



    public function show()
    {
        $page = new \App\Page\PublicPage($this);
        $template = $this->getTemplate();
        return $page->setPageContent($template);
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $tplFile = $this->getTemplatePath().'/xtpl/index.xtpl';
        return \Dom\Loader::loadFile($tplFile);
    }

}