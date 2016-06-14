<?php
namespace App\Controller\Admin\Course;

use Dom\Template;
use Tk\Form\Field;
use Tk\Request;
use \App\Controller\Admin\Iface;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends Iface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Course Manager', array('admin', 'coordinator'));
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        //$this->getBreadcrumbs()->reset()->init();
        
        $this->table = new \Tk\Table('CourseList');
        $this->table->setParam('renderer', \Tk\Table\Renderer\Dom\Table::create($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCellCss('key')->setUrl(\Tk\Uri::create('admin/courseEdit.html'));
        $this->table->addCell(new \Tk\Table\Cell\Text('code'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        $this->table->addCell(new \Tk\Table\Cell\Date('start'));
        $this->table->addCell(new \Tk\Table\Cell\Date('finish'));
        
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        //$this->table->addCell(new \Tk\Table\Cell\Date('created'))->setFormat(\Tk\Table\Cell\Date::FORMAT_RELATIVE);
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        $this->table->addAction(\Tk\Table\Action\Button::getInstance('New Course', 'fa fa-plus', \Tk\Uri::create('admin/courseEdit.html')));
        $this->table->addAction(\Tk\Table\Action\Delete::getInstance());
        $this->table->addAction(\Tk\Table\Action\Csv::getInstance());

        $users = \App\Db\Course::getMapper()->findFiltered($this->table->getFilterValues(), $this->table->makeDbTool('a.id'));
        $this->table->setList($users);

        return $this->show();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();
        $template->replaceTemplate('table', $this->table->getParam('renderer')->show());
        return $this->getPage()->setPageContent($template);
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="row">

  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-university fa-fw"></i> Course
      </div>
      <!-- /.panel-heading -->
      <div class="panel-body ">

        <div var="table"></div>

      </div>
      <!-- /.panel-body -->
    </div>
    <!-- /.panel -->
  </div>

</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }


}