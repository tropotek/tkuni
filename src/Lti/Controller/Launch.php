<?php
namespace Lti\Controller;

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
class Launch extends Iface
{

    /**
     * @var \App\Db\Institution
     */
    protected $institution = null;


    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doLaunch(Request $request)
    {

        $this->institution = \App\Db\InstitutionMap::create()->findByDomain($request->getUri()->getHost());
        if ($this->institution) {
            return $this->doInsLaunch($request, $this->institution->getHash());
        }

        return $this->show();
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doInsLaunch(Request $request, $instHash)
    {
        if (!$this->institution)
            $this->institution = \App\Db\InstitutionMap::create()->findByHash($instHash);
        if (!$this->institution) {
            throw new \Tk\NotFoundHttpException('Institution not found.');
        }
        if (!$request->has('lti_version') || !$request->has('ext_lms')) {
            //throw new \Tk\NotFoundHttpException('LTI request not found.');
            return $this->show();
        }



        $tool = new \Lti\Provider(\App\Factory::getLtiDataConnector(), $this->institution, $this->getConfig()->getEventDispatcher());
        $tool->handleRequest();

        // TODO: Is this the best place for this error
        $msg = '';
        if ($tool->message) {
            $msg .= $tool->message . '<br/>';
        }
        if ($tool->reason) {
            $msg .= $tool->reason . '<br/>';
        }
        $this->getTemplate()->insertHtml('message', trim($msg, '<br/>'));

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
<div class="">
    <div class="col-lg-12">
      <div class="alert alert-danger" var="row">
        <!-- button class="close noblock" data-dismiss="alert">&times;</button -->
        <h4><i choice="icon" var="icon"></i> <strong var="title">LTI Access Error</strong></h4>
        <span var="message">Sorry, there was an error connecting you to the application</span>
      </div>
    </div>
</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }


}