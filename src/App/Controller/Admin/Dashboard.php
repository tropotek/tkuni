<?php
namespace App\Controller\Admin;

use Tk\Request;
use Dom\Template;
use App\Controller\Iface;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Dashboard extends Iface
{


    /**
     *
     * @param Request $request
     * @return \Dom\Template|Template|string
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Dashboard');
        
    }


}