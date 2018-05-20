<?php
namespace App\Controller\Institution;

use Dom\Template;
use Tk\Form\Field;
use Tk\Request;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends \Uni\Controller\AdminIface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var \Tk\Table\Cell\Actions
     */
    protected $actionsCell = null;

    /**
     * Manager constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->actionsCell = new \Tk\Table\Cell\Actions();
        \Uni\Ui\Crumbs::reset();
        //\App\Config::getInstance()->resetCrumbs();
    }

    /**
     * @return \Tk\Table\Cell\Actions
     */
    public function getActionsCell()
    {
        return $this->actionsCell;
    }


    /**
     * @param Request $request
     * @throws \Tk\Exception
     * @throws \Tk\Form\Exception
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Institution Manager');

        $this->actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Masquerade', \Tk\Uri::create(), 'fa  fa-user-secret', 'tk-masquerade'))
            ->setOnShow(function ($cell, $obj, $button) {
                /* @var $obj \App\Db\Institution */
                /* @var $button \Tk\Table\Cell\ActionButton */
                if (\App\Listener\MasqueradeHandler::canMasqueradeAs(\App\Config::getInstance()->getUser(), $obj->getOwner())) {
                    $button->setUrl(\Uni\Uri::create()->set(\App\Listener\MasqueradeHandler::MSQ, $obj->getOwner()->getHash()));
                }
            });
            
        $this->table = \App\Config::getInstance()->createTable('InstitutionList');
        $this->table->setRenderer(\App\Config::getInstance()->createTableRenderer($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell($this->actionsCell);
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Tk\Uri::create('admin/institutionEdit.html'));
        $this->table->addCell(new OwnerCell('owner'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('description'))->setCharacterLimit(64);
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        $this->table->addAction(\Tk\Table\Action\Delete::create());
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        $users = \App\Db\InstitutionMap::create()->findFiltered($this->table->getFilterValues(), $this->table->getTool('a.id'));
        $this->table->setList($users);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();
        $template->replaceTemplate('table', $this->table->getRenderer()->show());

        $this->getActionPanel()->add(\Tk\Ui\Button::create('New Institution',
            \Uni\Uri::createHomeUrl('/institutionEdit.html'), 'fa fa-university'));

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

  <div class="panel panel-default">
    <div class="panel-heading">
      <i class="fa fa-university fa-fw"></i> Institution
    </div>
    <div class="panel-body">
      <div var="table"></div>
    </div>
  </div>

</div>
HTML;

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
     * @throws \Tk\Db\Exception
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


