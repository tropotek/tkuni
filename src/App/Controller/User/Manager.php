<?php
namespace App\Controller\User;

use Tk\Db\Exception;
use Tk\Request;
use Dom\Template;
use Tk\Form\Field;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends \Uni\Controller\AdminIface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var \App\Db\Subject
     */
    protected $subject = null;

    /**
     * @var null|\Tk\Uri
     */
    protected $editUrl = null;

    /**
     * @var \Tk\Table\Cell\Actions
     */
    protected $actionsCell = null;


    /**
     * @throws \Tk\Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPageHeading();
        $this->actionsCell = new \Tk\Table\Cell\Actions();
        \Uni\Ui\Crumbs::reset();
    }

    /**
     * @return \Tk\Table\Cell\Actions
     */
    public function getActionsCell()
    {
        return $this->actionsCell;
    }

    /**
     * @param Request $request
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     * @throws \Tk\Form\Exception
     */
    public function doDefault(Request $request)
    {

        if (!$this->editUrl)
            $this->editUrl = \Uni\Uri::createHomeUrl('/userEdit.html');

        if ($request->has('subjectId'))
            $this->subject = \App\Db\SubjectMap::create()->find($request->get('subjectId'));

        $this->actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Masquerade',
            \Tk\Uri::create(), 'fa  fa-user-secret', 'tk-masquerade'))
            ->setOnShow(function($cell, $obj, $button) {
                /* @var $obj \App\Db\User */
                /* @var $button \Tk\Table\Cell\ActionButton */
                if (\App\Listener\MasqueradeHandler::canMasqueradeAs(\App\Config::getInstance()->getUser(), $obj)) {
                    $button->setUrl(\Uni\Uri::create()->set(\App\Listener\MasqueradeHandler::MSQ, $obj->getHash()));
                } else {
                    $button->setAttr('disabled', 'disabled')->addCss('disabled');
                }
            });

        $this->table = \App\Config::getInstance()->createTable('UserList');
        $this->table->setRenderer(\App\Config::getInstance()->createTableRenderer($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell($this->actionsCell);
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(clone $this->editUrl);
        $this->table->addCell(new \Tk\Table\Cell\Text('username'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('role'));
        $this->table->addCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('lastLogin'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Button::getInstance('New User', 'fa fa-plus', \Uni\Uri::createHomeUrl('/userEdit.html'));
        //$this->table->addAction(\Tk\Table\Action\Delete::create());
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        $this->initTable();

        $this->initActionPanel($this->getActionPanel());
    }

    public function initTable()
    {
        if ($this->getUser()->hasRole(array(\App\Db\User::ROLE_CLIENT, \App\Db\User::ROLE_STAFF))) {
            $list = array('-- Role --' => '', 'Staff' => \App\Db\User::ROLE_STAFF, 'Student' => \App\Db\User::ROLE_STUDENT);
            try {
                $this->table->addFilter(new Field\Select('role', $list))->setLabel('');
            } catch (\Tk\Form\Exception $e) {
            }
        }

        try {
            $filter = $this->table->getFilterValues();
        } catch (\Exception $e) {
        }
        if ($this->getUser()->getInstitution())
            $filter['institutionId'] = $this->getUser()->getInstitution()->id;

        if (empty($filter['role'])) {
            $filter['role'] = $this->getUser()->role;
            if ($this->getUser()->hasRole(array(\App\Db\User::ROLE_CLIENT, \App\Db\User::ROLE_STAFF))) {
                $filter['role'] = array(\App\Db\User::ROLE_STAFF, \App\Db\User::ROLE_STUDENT);
            }
        }

        try {
            $users = \App\Db\UserMap::create()->findFiltered($filter, $this->table->makeDbTool('a.name'));
        } catch (Exception $e) {
        }
        $this->table->setList($users);
    }

    /**
     * @param \Tk\Ui\Admin\ActionPanel $actionPanel
     */
    protected function initActionPanel($actionPanel)
    {
        $actionPanel->addButton(\Tk\Ui\Button::create('New User', clone $this->editUrl, 'fa fa-user-plus'));
    }

    /**
     *
     */
    protected function setPageHeading()
    {
        switch($this->getUser()->role) {
            case \App\Db\User::ROLE_ADMIN:
                $this->setPageTitle('Administration Manager');
                break;
            case \App\Db\User::ROLE_CLIENT:
            case \App\Db\User::ROLE_STAFF:
                $this->setPageTitle('Staff/Student Manager');
                break;
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->replaceTemplate('table', $this->table->getRenderer()->show());

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
    <div class="panel-heading"><i class="fa fa-users fa-fw"></i> <span var="panelTitle">Users</span></div>
    <div class="panel-body">
      <div var="table"></div>
    </div>
  </div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}