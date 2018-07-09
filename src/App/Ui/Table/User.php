<?php
namespace App\Ui\Table;

use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class User extends \Dom\Renderer\Renderer
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var int
     */
    protected $institutionId = 0;

    /**
     * @var int
     */
    protected $subjectId = 0;

    /**
     * @var null|array|string
     */
    protected $role = null;

    /**
     * @var null|\Tk\Uri
     */
    protected $editUrl = null;

    /**
     * @var \Tk\Table\Cell\Actions
     */
    protected $actionsCell = null;


    /**
     * @param int $institutionId
     * @param null|array|string $role
     * @param int $subjectId
     * @param null|\Tk\Uri $editUrl
     * @throws \Tk\Db\Exception
     */
    public function __construct($institutionId = 0, $role = null, $subjectId = 0, $editUrl = null)
    {
        $this->institutionId = $institutionId;
        $this->role = $role;
        $this->subjectId = $subjectId;
        $this->editUrl = $editUrl;
        $this->doDefault();
    }


    /**
     * @return \Dom\Template|Template|string
     * @throws \Tk\Db\Exception
     * @throws \Exception
     */
    public function doDefault()
    {
        $this->table = new \Tk\Table('StaffList');
        $this->table->setRenderer(\Tk\Table\Renderer\Dom\Table::create($this->table));

        $this->actionsCell = new \Tk\Table\Cell\Actions();
        $this->actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Masquerade', \Tk\Uri::create(), 'fa  fa-user-secret', 'tk-masquerade'))
            ->setOnShow(function ($cell, $obj, $button) {
                /* @var $obj \App\Db\User */
                /* @var $button \Tk\Table\Cell\ActionButton */
                if (\Uni\Listener\MasqueradeHandler::canMasqueradeAs(\App\Config::getInstance()->getUser(), $obj)) {
                    $button->setUrl(\Uni\Uri::create()->set(\Uni\Listener\MasqueradeHandler::MSQ, $obj->getHash()));
                }
            });
        $this->table->addCell($this->actionsCell);
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl($this->editUrl);


        //    $this->table->addCell(new \App\Table\Cell\UserSubjects('subject', $this->institutionId));
        if ($this->institutionId) {
            $this->table->addCell(new \Tk\Table\Cell\Text('subject'))
                ->setOnPropertyValue(function ($csll, $obj, $value) {
                    $list = \App\Db\SubjectMap::create()->findByUserId($obj->id, $this->institutionId, \Tk\Db\Tool::create('a.name'));
                    $val = '';
                    /** @var \App\Db\Subject $subject */
                    foreach ($list as $subject) {
                        $val .= $subject->code . ', ';
                    }
                    if ($val)
                        $val = rtrim($val, ', ');
                    return $val;
                });
        }
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));

        if (!$this->role)
            $this->table->addCell(new \Tk\Table\Cell\Text('role'));

        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        //$this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Csv::getInstance(\App\Config::getInstance()->getDb()));

        // Set list
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->institutionId;
        $filter['subjectId'] = $this->subjectId;
        $filter['role'] = $this->role;

        $users = \App\Db\UserMap::create()->findFiltered($filter, $this->table->getTool('a.name'));
        $this->table->setList($users);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->table->getRenderer()->show();
        $this->setTemplate($this->table->getRenderer()->getTemplate());
        return $this->table->getRenderer()->getTemplate();
    }

}
