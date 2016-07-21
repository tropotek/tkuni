<?php
namespace App\Ui;

use Dom\Template;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;
use Tk\Request;

/**
 * Class CourseTable
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class CourseTable extends \Dom\Renderer\Renderer
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
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault()
    {
        $this->table = new \Tk\Table('CourseList');
        $this->table->setParam('renderer', \Tk\Table\Renderer\Dom\Table::create($this->table));

        //$this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCellCss('key')->setUrl($this->editUrl);
        $this->table->addCell(new \Tk\Table\Cell\Text('code'));
        //$this->table->addCell(new \Tk\Table\Cell\Text('email'));
        //$this->table->addCell(new \Tk\Table\Cell\Date('start'));
        $this->table->addCell(new \Tk\Table\Cell\Date('finish'));

        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        //$this->table->addCell(new \Tk\Table\Cell\Date('created'))->setFormat(\Tk\Table\Cell\Date::FORMAT_RELATIVE);
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        //$this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Csv::getInstance(\App\Factory::getConfig()->getDb()));

        // Set list
        $filter = $this->table->getFilterValues();
        if ($this->institutionId)
            $filter['institutionId'] = $this->institutionId;
        $users = \App\Db\Course::getMapper()->findFiltered($filter, $this->table->makeDbTool('a.id'));
        $this->table->setList($users);

    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $this->table->getParam('renderer')->show();
        $this->setTemplate($this->table->getParam('renderer')->getTemplate());
        return $this->table->getParam('renderer')->getTemplate();
    }

}