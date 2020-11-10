<?php
namespace App\Controller\Client;

use Tk\Request;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Dashboard extends \Uni\Controller\AdminIface
{

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
        $this->setPageTitle('Dashboard');
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

        $this->userTable = \Uni\Table\User::create()->setEditUrl(\Uni\Uri::createHomeUrl('/staffUserEdit.html'))->init();

        $this->userTable->removeCell('id');
        $this->userTable->removeCell('created');
        //$this->userTable->removeFilter('keywords');
        $this->userTable->removeAction('delete');
        $this->userTable->removeAction('csv');

        $this->userTable->appendCell(new \Tk\Table\Cell\Text('role'), 'uid')
            ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, $obj, $value) {
                /** @var $obj \Uni\Db\User */
                $value ='';
                if ($obj->isCoordinator()) {
                    $value .= 'Coordinator, ';
                } else if ($obj->isLecturer()) {
                    $value .= 'Lecturer, ';
                }
                if ($obj->isMentor()) {
                    $value .= 'Mentor, ';
                }
                if (!$value) {
                    $value = 'Staff';
                }
                return trim($value, ', ');
            });

        $filter = array();
        $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        $filter['type'] = \Uni\Db\User::TYPE_STAFF;
        //$this->userTable->setList($this->userTable->findList($filter));
        //$this->userTable->resetSessionTool();
        //$this->userTable->setList($this->userTable->findList($filter, $this->userTable->getTool('IF(ISNULL(last_login),1,0), last_login DESC')));
        $this->userTable->setList($this->userTable->findList($filter, $this->userTable->getTool('IF(ISNULL(last_login),1,0), last_login DESC')));

    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('table', $this->userTable->show());

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
<div class="tk-panel" data-panel-title="Staff List" data-panel-icon="fa fa-institution" var="table"></div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}