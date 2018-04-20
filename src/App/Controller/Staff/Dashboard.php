<?php
namespace App\Controller\Staff;

use Tk\Request;
use Dom\Template;
use Uni\Controller\Iface;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Dashboard extends Iface
{
    
    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Dashboard');
        
        
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
    <div class="panel-body ">
      Things the institution staff member should be able to do:
      <ul>
        <li>Edit profile</li>
        <li>Manage Subject setup and data</li>
        <li>Manage Student accounts</li>
        <li>Manage student ssubject enrollments</li>
      </ul>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}