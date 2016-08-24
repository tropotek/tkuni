<?php
namespace App\Controller\Client;

use Tk\Request;
use Dom\Template;
use \App\Controller\Iface;

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
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="row">

  <div class="" var="editPanel">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-university fa-fw"></i> TODO
      </div>
      <div class="panel-body ">
        <div class="row">
          <div class="col-lg-12">
            Things the Client should be able to do:
            <ul>
              <li>Edit Client profile</li>
              <li>Manage/Pay account online (future)</li>
              <li>Create/Modify institutions belonging to the institution</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  
</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }


}