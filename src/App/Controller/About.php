<?php
namespace App\Controller;

use Tk\Request;
use Uni\Controller\Iface;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class About extends Iface
{

    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('About Us');
        // TODO:
    }
}