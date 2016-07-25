<?php
namespace App\Controller\Admin\User;

use Tk\Request;
use Dom\Template;
use Tk\Form\Field;
use App\Controller\Admin\Iface;

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
        parent::__construct('User Manager');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        $this->table = new \Tk\Table('tableOne');

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCellCss('key')->setUrl(\Tk\Uri::create('admin/userEdit.html'));
        $this->table->addCell(new \Tk\Table\Cell\Text('username'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('role'));
        $this->table->addCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        //$this->table->addCell(new \Tk\Table\Cell\Date('created'))->setFormat(\Tk\Table\Cell\Date::FORMAT_RELATIVE);
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        $list = array('-- Role --' => '', 'Admin' => \App\Auth\Access::ROLE_ADMIN, 'Client' => \App\Auth\Access::ROLE_CLIENT);
        $this->table->addFilter(new Field\Select('role', $list))->setLabel('');

        // Actions
        $this->table->addAction(\Tk\Table\Action\Button::getInstance('New User', 'fa fa-plus', \Tk\Uri::create('admin/userEdit.html')));
        $this->table->addAction(\Tk\Table\Action\Delete::getInstance());
        $this->table->addAction(\Tk\Table\Action\Csv::getInstance($this->getConfig()->getDb()));

        $filter = $this->table->getFilterValues();
        if (empty($filter['role']))
            $filter['role'] = array(\App\Auth\Access::ROLE_ADMIN, \App\Auth\Access::ROLE_CLIENT);

        $users = \App\Db\User::getMapper()->findFiltered($filter, $this->table->makeDbTool('a.name'));
        $this->table->setList($users);

        return $this->show();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        $ren = \Tk\Table\Renderer\Dom\Table::create($this->table);
        $ren->show();
        $template->replaceTemplate('table', $ren->getTemplate());

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
        <i class="fa fa-users fa-fw"></i> Users
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