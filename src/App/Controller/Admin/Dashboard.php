<?php
namespace App\Controller\Admin;

use Tk\Request;
use Dom\Template;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Dashboard extends \Uni\Controller\AdminIface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     */
    public function __construct()
    {
        $this->setPageTitle('Dashboard');
        $this->getActionPanel()->setEnabled(false);
    }

    /**
     *
     * @param Request $request
     * @return \Dom\Template|Template|string
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {

        $this->table = \Uni\Table\Institution::create()->init();
        $this->table->setList($this->table->findList());

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('table', $this->table->getRenderer()->show());

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

  <div class="tk-panel" data-panel-title="Institution" data-panel-icon="fa fa-university" var="table"></div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}