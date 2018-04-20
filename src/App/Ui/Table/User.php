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
     *  constructor.
     *
     * @param int $institutionId
     * @param null|array|string $role
     * @param int $subjectId
     * @param null|\Tk\Uri $editUrl
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
     *
     * @return \Dom\Template|Template|string
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
                if (\App\Listener\MasqueradeHandler::canMasqueradeAs(\App\Config::getInstance()->getUser(), $obj)) {
                    $button->setUrl(\Uni\Uri::create()->set(\App\Listener\MasqueradeHandler::MSQ, $obj->getHash()));
                }
            });
        $this->table->addCell($this->actionsCell);
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl($this->editUrl);

        if ($this->institutionId)
            $this->table->addCell(new \App\Table\Cell\UserSubjects('subject', $this->institutionId));

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

        $users = \App\Db\UserMap::create()->findFiltered($filter, $this->table->makeDbTool('a.name'));
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
