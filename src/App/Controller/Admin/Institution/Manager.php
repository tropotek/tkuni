<?php
namespace App\Controller\Admin\Institution;

use Dom\Template;
use Tk\Form\Field;
use Tk\Request;
use \App\Controller\Iface;

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
        parent::__construct('Institution Manager');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        //$this->getBreadcrumbs()->reset()->init();
        
        $this->table = new \Tk\Table('InstitutionList');
        $this->table->setParam('renderer', \Tk\Table\Renderer\Dom\Table::create($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCellCss('key')->setUrl(\Tk\Uri::create('admin/institutionEdit.html'));
        $this->table->addCell(new OwnerCell('owner'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('description'))->setCharacterLimit(64);
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Button::getInstance('New Institution', 'fa fa-plus', \Tk\Uri::create('admin/institutionEdit.html')));
        $this->table->addAction(\Tk\Table\Action\Delete::getInstance());
        $this->table->addAction(\Tk\Table\Action\Csv::getInstance($this->getConfig()->getDb()));

        $users = \App\Db\InstitutionMap::create()->findFiltered($this->table->getFilterValues(), $this->table->makeDbTool('a.id'));
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
        <i class="fa fa-cogs fa-fw"></i> Actions
      </div>
      <div class="panel-body ">
        <div class="row">
          <div class="col-lg-12">
            <a href="javascript: window.history.back();" class="btn btn-default"><i class="fa fa-arrow-left"></i> <span>Back</span></a>
            <a href="/admin/userEdit.html" class="btn btn-default"><i class="fa fa-university"></i> <span>New Institution</span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  

  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-university fa-fw"></i> Institution
      </div>
      <div class="panel-body">
        <div var="table"></div>
      </div>
    </div>
  </div>

</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }


}


class OwnerCell extends \Tk\Table\Cell\Text
{

    public function __construct($property, $label = null)
    {
        parent::__construct($property, $label);
        $this->setOrderProperty('');
    }

    /**
     * @param \App\Db\Institution $obj
     * @param string $property
     * @return mixed
     */
    public function getPropertyValue($obj, $property)
    {
        //$val =  parent::getPropertyValue($obj, $property);
        $val =  '';
        $owner = $obj->getOwner();
        if ($owner) {
            $val = $owner->name;
        }
        return $val;
    }

}


