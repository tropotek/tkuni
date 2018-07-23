<?php
namespace App\Controller\Student;

use Tk\Request;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Dashboard extends \Uni\Controller\AdminIface
{

    /**
     * Dashboard constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Dashboard');
        $this->getCrumbs()->setVisible(false);
        $this->getActionPanel()->setVisible(false);
        $this->getConfig()->unsetSubject();
    }

    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {



    }

    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="">

  <div class="panel panel-default">
    <div class="panel-heading">
      <i class="fa fa-university fa-fw"></i> TODO
    </div>
    <div class="panel-body">
      Things the institution student member should be able to do:
      <ul>
        <li>Edit profile</li>
        <li>Manage student subject data</li>
      </ul>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}