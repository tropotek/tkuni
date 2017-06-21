<?php
namespace App\Controller\Staff;

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
        <li>Manage Course setup and data</li>
        <li>Manage Student accounts</li>
        <li>Manage student course enrollments</li>
      </ul>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}