<?php
namespace App\Controller\User;


/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StaffManager extends Manager
{

    /**
     * Iface constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPageTitle('Staff Manager');
        $this->editUrl = \App\Uri::createHomeUrl('/staffEdit.html');
    }

    /**
     *
     */
    public function initTable()
    {
        // Set List
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->getUser()->getInstitution()->id;
        $filter['role'] = \App\Db\User::ROLE_STAFF;

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