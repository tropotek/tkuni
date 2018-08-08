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
        $this->setPageTitle($this->subject->name);
        //$this->getCrumbs()->setVisible(false);
        $this->getActionPanel()->setVisible(false);
    }


    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $subject = $this->getConfig()->getSubject();
        if ($subject) {
            $this->setPageTitle($subject->name);
            $this->getTemplate()->insertText('code', $subject->code);
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->insertText('code', $this->subject->getCode());

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

  <div class="panel panel-default">
    <div class="panel-heading"><i class="fa fa-fw fa-institution"></i> <span var="code">Subject Dashboard</span></div>
    <div class="panel-body">
      
      <p>&nbsp;</p>
      
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}