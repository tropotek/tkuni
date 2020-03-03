<?php
namespace App\Controller\Mentor;

use Tk\Request;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StudentView extends \Uni\Controller\AdminIface
{

    /**
     * @var \Uni\Table\User
     */
    protected $user = null;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Student View');
        $this->getConfig()->unsetSubject();

    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->user = $this->getConfig()->getUserMapper()->find($request->get('userId'));


    }

    public function show()
    {
        $template = parent::show();

        //$template->appendTemplate('table', $this->userTable->getRenderer()->show());

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

  <div class="tk-panel" data-panel-icon="fa fa-user" var="panel"></div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}