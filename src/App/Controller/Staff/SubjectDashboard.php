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
     * @var \Uni\Table\User
     */
    protected $userTable = null;


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

        $this->userTable = \Uni\Table\User::create()->init();
        $this->userTable->findCell('name')->setUrl(\Uni\Uri::createSubjectUrl('/studentEdit.html'));
        $filter = array();
        $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        $filter['subjectId'] = $this->getConfig()->getSubjectId();
        $filter['type'] = \Uni\Db\Role::TYPE_STUDENT;
        $this->userTable->setList($this->userTable->findList($filter));

    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('table', $this->userTable->getRenderer()->show());
        $template->setAttr('table', 'data-panel-title', $this->getConfig()->getSubject()->code . ' Student List');

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

  <div class="tk-panel" data-panel-title="Student List" data-panel-icon="fa fa-users" var="table"></div>
  <!--<div class="panel panel-default">-->
    <!--<div class="panel-heading"><i class="fa fa-fw fa-users"></i> <span var="code"></span> Student List</div>-->
    <!--<div class="panel-body">-->
      <!--<div var="table"></div>-->
    <!--</div>-->
  <!--</div>-->
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}