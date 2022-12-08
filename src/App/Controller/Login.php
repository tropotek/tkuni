<?php
namespace App\Controller;

use Tk\Db\Tool;
use Tk\Form\Field;
use Tk\Form\Event;
use Uni\Db\InstitutionMap;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Login extends \Uni\Controller\Login
{


    /**
     * @throws \Exception
     */
    protected function init()
    {
        parent::init();

        $this->form->removeField('username');
        $this->form->removeField('password');
        /** @var \Tk\Form\Field\InputGroup $f */
        $f = $this->form->appendField(Field\InputGroup::create('username'))->setRequired()->setLabel(null)->setAttr('placeholder', 'Username');
        $f->prepend('<span class="input-group-text input-group-addon"><i class="fa fa-user mx-auto"></i></span>');


        $f = $this->form->appendField(Field\InputGroup::create('password'))->setRequired()->setType('password')->setLabel(null)->setAttr('placeholder', 'Password');
        $f->prepend('<span class="input-group-text input-group-addon"><i class="fa fa-key mx-auto"></i></span>');

        $this->form->getField('login')->addCss('col-12');
        $f = $this->form->getField('forgotPassword');
        if ($f) {
            $f->addCss('');
        }

        // If this is an institution login page
        if ($this->getConfig()->getRequest()->getTkUri()->basename() == 'login.html') {
            $list = InstitutionMap::create()->findActive(Tool::create('', 2));
            if ($list->count() > 1)
                $this->form->appendField(new Event\Link('selectInstitution', \Tk\Uri::create('/')->setFragment('institutions'), ''))
                    ->removeCss('btn btn-sm btn-default btn-once')->addCss('tk-unstitutions-url');
        }
    }

    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-login-panel tk-login">

  <div var="form"></div>
  <div class="external row" choice="inst">
    <a href="/microsoftLogin.html" class="btn btn-lg btn-default col-12" choice="microsoft">Microsoft</a>
<!--    <a href="/googleLogin.html" class="btn btn-lg btn-warning col-12" choice="google">Google</a>-->
<!--    <a href="/githubLogin.html" class="btn btn-lg btn-default col-12" choice="github">Github</a>-->
  </div>
  

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}