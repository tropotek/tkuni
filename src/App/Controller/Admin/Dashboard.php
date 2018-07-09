<?php
namespace App\Controller\Admin;

use Tk\Request;
use Dom\Template;
use Bs\Controller\AdminIface;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Dashboard extends AdminIface
{

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Dashboard');
        $this->getCrumbs()->reset();
    }

    /**
     * @param Request $request
     * @return \Dom\Template|Template|string
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->getActionPanel()->setEnabled(false);

        
    }


}