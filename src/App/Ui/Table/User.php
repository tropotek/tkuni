<?php
namespace App\Ui\Table;

use Dom\Template;

/**
 * Class CourseTable
 *
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
    protected $courseId = 0;

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
     * CourseTable constructor.
     * @param int $institutionId
     * @param null|array|string $role
     * @param int $courseId
     * @param null|\Tk\Uri $editUrl
     */
    public function __construct($institutionId = 0, $role = null, $courseId = 0, $editUrl = null)
    {
        $this->institutionId = $institutionId;
        $this->role = $role;
        $this->courseId = $courseId;
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
        $this->table->setParam('renderer', \Tk\Table\Renderer\Dom\Table::create($this->table));


        $this->actionsCell = new \Tk\Table\Cell\Actions();
        $this->actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Masquerade', \Tk\Uri::create(), 'fa  fa-user-secret', 'tk-masquerade'))
            ->setOnShow(function ($obj, $button, $cell) {
                /* @var $obj \App\Db\User */
                /* @var $button \Tk\Table\Cell\ActionButton */
                if (\App\Listener\MasqueradeHandler::canMasqueradeAs(\App\Factory::getUser(), $obj)) {
                    $button->setUrl(\App\Uri::create()->set(\App\Listener\MasqueradeHandler::MSQ, $obj->getId()));
                }
            });
        $this->table->addCell($this->actionsCell);
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl($this->editUrl);

        if ($this->institutionId)
            $this->table->addCell(new \App\Table\Cell\UserCourses('course', $this->institutionId));

        $this->table->addCell(new \Tk\Table\Cell\Text('email'));

        if (!$this->role)
            $this->table->addCell(new \Tk\Table\Cell\Text('role'));

        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        //$this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Csv::getInstance(\App\Factory::getConfig()->getDb()));

        // Set list
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->institutionId;
        $filter['courseId'] = $this->courseId;
        $filter['role'] = $this->role;

        $users = \App\Db\UserMap::create()->findFiltered($filter, $this->table->makeDbTool('a.name'));
        $this->table->setList($users);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->table->getParam('renderer')->show();
        $this->setTemplate($this->table->getParam('renderer')->getTemplate());
        return $this->table->getParam('renderer')->getTemplate();
    }

}
