<?php
namespace App\Controller\Client;

use Tk\Request;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Dashboard extends \Uni\Controller\AdminIface
{

    /**
     * Iface constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Dashboard');
        //$this->getCrumbs()->setVisible(false);
        $this->getActionPanel()->setVisible(false);
    }

    /**
     * @var \Uni\Table\User
     */
    protected $userTable = null;


    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('');

        $this->userTable = \Uni\Table\User::create()->init();
        $this->userTable->findCell('name')->setUrl(\Uni\Uri::createHomeUrl('/staffEdit.html'));
        $filter = array();
        $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        $filter['type'] = \Uni\Db\Role::TYPE_COORDINATOR;
        $this->userTable->setList($this->userTable->findList($filter));

    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('table', $this->userTable->getRenderer()->show());

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
<div>
  <div class="tk-panel" data-panel-title="Staff List" data-panel-icon="fa fa-institution" var="table"></div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}