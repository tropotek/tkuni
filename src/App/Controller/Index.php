<?php
namespace App\Controller;

use Tk\Request;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Index extends \Uni\Controller\Index
{
    /**
     * @var null|\Tk\Table
     */
    protected $table = null;
    
    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        parent::doDefault($request);

        $this->table = $this->getConfig()->createTable('institution-list');
        $this->table = $this->getConfig()->createTable('institution-list');
        $this->table->setRenderer($this->getConfig()->createTableRenderer($this->table));

        $actionsCell = new \Tk\Table\Cell\Actions();
        $actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Login', \Tk\Uri::create(), 'fa  fa-sign-in', 'button-small soft')->setAttr('title', 'Institution Login'))
            ->setOnShow(function ($cell, $obj, $button) {
                /* @var $obj \Uni\Db\Institution */
                /* @var $button \Tk\Table\Cell\ActionButton */
                $button->setUrl($obj->getLoginUrl());
            });

        $this->table->appendCell($actionsCell);
        $this->table->appendCell(new \Tk\Table\Cell\Text('name'))->setUrl(\Tk\Uri::create('/institutionEdit.html'))
            ->setOnPropertyValue(function ($cell, $obj, $value) {
                /* @var $obj \Uni\Db\Institution */
                /* @var $cell \Tk\Table\Cell\Text */
                $cell->setUrl($obj->getLoginUrl());
                return $value;
            });
        $this->table->appendCell(new \Tk\Table\Cell\Text('description'))->addCss('key')->setCharacterLimit(150);

        $filter = $this->table->getFilterValues();
        $filter['active'] = true;
        $list = $this->getConfig()->getInstitutionMapper()->findFiltered($filter, $this->table->getTool());
        $this->table->setList($list);
    }


    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->insertText('site-title', $this->getConfig()->get('site.title'));

        $template->appendTemplate('table', $this->table->getRenderer()->show());


        return $template;
    }
}