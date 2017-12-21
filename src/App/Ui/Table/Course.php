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
class Course extends \Dom\Renderer\Renderer
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
     * @var \Tk\Url
     */
    protected $editUrl = null;


    /**
     * CourseTable constructor.
     *
     * @param int $institutionId
     * @param \Tk\Uri|null $editUrl
     */
    public function __construct($institutionId = 0, $editUrl = null)
    {
        $this->institutionId = $institutionId;
        $this->editUrl = $editUrl;
        $this->doDefault();
    }


    /**
     *
     * @return \Dom\Template|Template|string
     */
    public function doDefault()
    {
        $this->table = new \Tk\Table('CourseList');
        $this->table->setRenderer(\Tk\Table\Renderer\Dom\Table::create($this->table));

        //$this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl($this->editUrl);
        $this->table->addCell(new \Tk\Table\Cell\Text('code'));
        //$this->table->addCell(new \Tk\Table\Cell\Text('email'));
        //$this->table->addCell(new \Tk\Table\Cell\Date('dateStart'));
        $this->table->addCell(new \Tk\Table\Cell\Date('dateEnd'));

        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        //$this->table->addCell(new \Tk\Table\Cell\Date('created'))->setFormat(\Tk\Table\Cell\Date::FORMAT_RELATIVE);
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        //$this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Csv::getInstance(\App\Config::getInstance()->getDb()));

        // Set list
        $filter = $this->table->getFilterValues();
        if ($this->institutionId)
            $filter['institutionId'] = $this->institutionId;
        $users = \App\Db\CourseMap::create()->findFiltered($filter, $this->table->makeDbTool('a.id'));
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