<?php
namespace App\Controller\Mentor;

use Bs\Db\UserIface;
use Dom\Loader;
use Exception;
use Tk\Request;
use Dom\Template;
use Tk\Ui\Dialog\AjaxSelect;
use Uni\Controller\AdminIface;
use Uni\Table\User;
use Uni\Uri;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Dashboard extends AdminIface
{

    /**
     * @var User
     */
    protected $userTable = null;

    /**
     * @var AjaxSelect
     */
    protected $userSelect = null;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Mentor Dashboard');
        $this->getConfig()->unsetSubject();

    }

    /**
     * @param Request $request
     * @throws Exception
     */
    public function doDefault(Request $request)
    {

        $this->userSelect = AjaxSelect::create('Select User');


        $this->userTable = User::create()->setEditUrl(Uri::createHomeUrl('/mentor/studentView.html'))->init();
        $this->userTable->removeAction('delete');

        $btn = $this->userTable->appendAction(\Tk\Table\Action\Link::createLink('Add Student', '#', 'fa fa-user'));
        $btn->setAttr('data-target','#' . $this->userSelect->getId());
        $btn->setAttr('data-toggle','modal');

        $this->userTable->appendAction(\Tk\Table\Action\Delete::create('Remove')->setIcon('fa fa-trash')
            ->addOnDelete(function (\Tk\Table\Action\Delete $action, $obj) {
            /** @var $obj \Uni\Db\User */
            $obj->getConfig()->getUserMapper()->removeMentor($obj->getAuthUser()->getId(), $obj->getId());
            return false;
        })->setAttr('title', 'Remove Student Confirmation.')->setConfirmStr('Are you sure you want to remove the selected student(s) from your mentor list?'));

        //$this->userTable->removeCell('id');
        $this->userTable->removeCell('actions');
        $this->userTable->removeCell('active');
        $this->userTable->removeCell('lastLogin');
        $this->userTable->findCell('nameFirst')->addOnPropertyValue(function ($cell, $obj, $value) {
            /** @var UserIface $obj */
            return $obj->getName();
        });

        $filter = array();
        $filter['mentorId'] = $this->getAuthUser()->getId();
        $filter['active'] = true;
        $list = $this->userTable->findList($filter);
        $this->userTable->setList($list);

        $this->userTable->execute();



        $this->userSelect->addOnAjax(function (AjaxSelect $dialog) use ($list) {
            $arr = array();
            $studentList = $dialog->getConfig()->getUserMapper()->findFiltered(array(
                'institutionId' => $dialog->getConfig()->getInstitutionId(),
                'active' => true,
                'exclude' => $list->toArray('id')
            ));
            foreach ($studentList as $user) {
                $arr[] = array('id' => $user->getId(), 'name' => $user->getName());
            }
            return $arr;
        });
        $this->userSelect->addOnSelect(function (AjaxSelect $dialog) {
            if ($dialog->getSelectedId())
                $dialog->getConfig()->getUserMapper()->addMentor($dialog->getAuthUser()->getId(), $dialog->getSelectedId());
        });
        $this->userSelect->execute();





    }

    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('panel', $this->userTable->getRenderer()->show());
        $template->appendBodyTemplate($this->userSelect->show());

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
<div class="">

  <div class="tk-panel" data-panel-title="My Students" data-panel-icon="fa fa-user-md" var="panel"></div>

</div>
HTML;

        return Loader::load($xhtml);
    }


}