<?php
namespace App\Controller;

use Tk\Request;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Index extends \Uni\Controller\Index
{
    
    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        parent::doDefault($request);
    }


    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        return $template;
    }
}