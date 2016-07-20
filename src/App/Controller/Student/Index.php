<?php
namespace App\Controller\Student;

use Tk\Request;
use Dom\Template;

/**
 *
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
        parent::__construct('Dashboard');
    }
    
    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        
        //throw new \Exception('This is a test');
        
        
        return $this->show();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();
        
        return $this->getPage()->setPageContent($template);
    }


    /**
     * DomTemplate magic method
     * @return Template
     */
    public function __makeTemplate()
    {
        $tplFile =  $this->getTemplatePath() . '/xtpl/index.xtpl';
        return \Dom\Loader::loadFile($tplFile);
    }


}