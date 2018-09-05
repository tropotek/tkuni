<?php
namespace App\Controller\Student;

use Tk\Request;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class SubjectDashboard extends \Uni\Controller\AdminIface
{

    /**
     * @var null|\Uni\Db\Subject
     */
    protected $subject = null;

    /**
     * SubjectDashboard constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->subject = $this->getConfig()->getSubject();
        $this->setPageTitle('Subject Dashboard');
        //$this->getCrumbs()->setVisible(false);
        $this->getActionPanel()->setVisible(false);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle($this->subject->getName());
        if ($this->subject) {
            $this->setPageTitle($this->subject->getName());
            $this->getTemplate()->insertText('code', $this->subject->getCode());
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->setAttr('content', 'data-panel-title', $this->subject->getCode() . ' Dashboard');

        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>

  <div class="tk-panel" data-panel-title="Subject Dashboard" data-panel-icon="fa fa-graduation-cap" var="content">
   <p>&nbsp;</p>
   <p>TODO: Add application content for the sutdent to access...</p>
   <p>&nbsp;</p>
   <p>&nbsp;</p>
   <p>&nbsp;</p>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}