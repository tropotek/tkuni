<?php
namespace App\Controller;

use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Request;
use Tk\Auth\AuthEvents;
use Bs\Controller\Iface;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Recover extends \Uni\Controller\Recover
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();

        if (!$this->getConfig()->isBootsrap4()) return;

        $this->form->removeField('account');

        $f = $this->form->appendField(Field\InputGroup::create('account'))->setRequired()->setLabel(null)->setAttr('placeholder', 'Username');
        $f->prepend('<span class="input-group-text"><i class="fa fa-user mx-auto"></i></span>');

        $this->form->getField('recover')->addCss('col-12');

    }



    public function show()
    {
        $template = parent::show();


        return $template;
    }


    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-login-panel tk-recover">
  <h4>Forgot your password?</h4>
  <p>
    Enter your username to recover your password.
  </p>
  <div var="form"></div>
  <div class="not-member" choice="register">
    <p>Not a member? <a href="/register.html">Register here</a></p>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}