<?php
namespace App\Controller\Ui\User;

use Tk\Request;
use Dom\Template;
use Tk\Form\Field;
use \App\Controller\Iface;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends Iface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var \App\Db\Course
     */
    protected $course = null;
    

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('User Manager');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        $this->setPageHeading();

        if ($request->has('courseId'))
            $this->course = \App\Db\CourseMap::create()->find($request->get('courseId'));

        $this->table = new \Tk\Table('tableOne');

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCellCss('key')->setUrl(\App\Uri::createHomeUrl('/userEdit.html'));
        $this->table->addCell(new \Tk\Table\Cell\Text('username'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('role'));
        $this->table->addCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        if ($this->getUser()->hasRole(array(\App\Auth\Acl::ROLE_CLIENT, \App\Auth\Acl::ROLE_STAFF))) {
            $list = array('-- Role --' => '', 'Staff' => \App\Auth\Acl::ROLE_STAFF, 'Student' => \App\Auth\Acl::ROLE_STUDENT);
            $this->table->addFilter(new Field\Select('role', $list))->setLabel('');
        }

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Button::getInstance('New User', 'fa fa-plus', \App\Uri::createHomeUrl('/userEdit.html'));
        $this->table->addAction(\Tk\Table\Action\Delete::getInstance());
        $this->table->addAction(\Tk\Table\Action\Csv::getInstance($this->getConfig()->getDb()));

        $filter = $this->table->getFilterValues();
        if ($this->getUser()->hasRole(\App\Auth\Acl::ROLE_STAFF) || $this->getUser()->hasRole(\App\Auth\Acl::ROLE_STUDENT)) {
            $filter['institutionId'] = $this->getUser()->getInstitution()->id;
        }
        if (empty($filter['role'])) {
            $filter['role'] = $this->getUser()->role;
            if ($this->getUser()->hasRole(array(\App\Auth\Acl::ROLE_CLIENT, \App\Auth\Acl::ROLE_STAFF))) {
                $filter['role'] = array(\App\Auth\Acl::ROLE_STAFF, \App\Auth\Acl::ROLE_STUDENT);
            }
        }

        $users = \App\Db\UserMap::create()->findFiltered($filter, $this->table->makeDbTool('a.name'));
        $this->table->setList($users);

        return $this->show();
    }

    /**
     *
     */
    protected function setPageHeading()
    {
        switch($this->getUser()->role) {
            case \App\Auth\Acl::ROLE_ADMIN:
                $this->setPageTitle('Administration Manager');
                break;
            case \App\Auth\Acl::ROLE_CLIENT:
            case \App\Auth\Acl::ROLE_STAFF:
                $this->setPageTitle('Staff/Student Manager');
                break;
        }
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        $ren = \Tk\Table\Renderer\Dom\Table::create($this->table);
        $ren->show();
        $template->replaceTemplate('table', $ren->getTemplate());

        $template->setAttr('new', 'href', \App\Uri::createHomeUrl('/userEdit.html'));
        $template->setChoice($this->getUser()->role);

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

  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-cogs fa-fw"></i> Actions
      </div>
      <div class="panel-body ">
        <div class="row">
          <div class="col-lg-12">
            <a href="javascript: window.history.back();" class="btn btn-default"><i class="fa fa-arrow-left"></i> <span>Back</span></a>
            <a href="/userEdit.html" class="btn btn-default" var="new"><i class="fa fa-user-plus"></i> <span>New User</span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-users fa-fw"></i> <span var="panelTitle">Users</span>
      </div>
      <div class="panel-body">
        <div var="table"></div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-12" choice="staff">
    <div class="panel panel-default">
      <div class="panel-body ">
        <div class="row">
          <div class="col-lg-12">
            <p>TODO: Add the ability to assign staff members to courses.</p>
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