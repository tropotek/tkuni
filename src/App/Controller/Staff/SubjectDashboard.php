<?php
namespace App\Controller\Staff;

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
     * Iface constructor.
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
        $this->setPageTitle('');

    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        $subject = $this->getConfig()->getSubject();
        if ($subject) {
            $template->insertText('code', $subject->code);
        }
        $url = \Uni\Uri::create('/');
        $subjectUserList = new \Uni\Ui\Table\User($this->getConfig()->getInstitutionId(), null, $this->getConfig()->getSubjectId(), $url);
        $template->appendTemplate('table', $subjectUserList->show());


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
    <div class="panel-heading"><i class="fa fa-fw fa-institution"></i> <span var="code"></span> Users</div>
    <div class="panel-body">
      
      <div var="table"></div>
      
    </div>
  </div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}