<?php
namespace App\Controller\User;


/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StudentManager extends Manager
{

    /**
     * Iface constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPageTitle('Student Manager');
        $this->editUrl = \Uni\Uri::createHomeUrl('/studentEdit.html');
    }

    /**
     *
     */
    public function initTable()
    {
        // Set List
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->getUser()->getInstitution()->id;
        $filter['role'] = \App\Db\User::ROLE_STUDENT;

        $users = \App\Db\UserMap::create()->findFiltered($filter, $this->table->makeDbTool('a.name'));
        $this->table->setList($users);
    }

    /**
     * @param \Tk\Ui\Admin\ActionPanel $actionPanel
     */
    protected function initActionPanel($actionPanel)
    {
        //$actionPanel->addButton(\Tk\Ui\Button::create('New Student', clone $this->editUrl, 'fa fa-user-plus'));
    }


}