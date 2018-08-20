<?php
namespace App\Controller;

use Tk\Form\Field;


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
        if (!$this->getConfig()->isBootsrap4()) return;

        $this->form->getRenderer()->setFieldGroupRenderer(null);
        $this->form->removeCss('form-horizontal');

        $this->form->removeField('username');
        $this->form->removeField('password');
        /** @var \Tk\Form\Field\InputGroup $f */
        $f = $this->form->appendField(Field\InputGroup::create('username'))->setLabel(null)->setAttr('placeholder', 'Username');
        $f->prepend('<span class="input-group-text input-group-addon"><i class="fa fa-user mx-auto"></i></span>');


        $f = $this->form->appendField(Field\InputGroup::create('password'))->setType('password')->setLabel(null)->setAttr('placeholder', 'Password');
        $f->prepend('<span class="input-group-text input-group-addon"><i class="fa fa-key mx-auto"></i></span>');

        $this->form->getField('login')->addCss('col-12');
        $f = $this->form->getField('forgotPassword');
        if ($f) {
            $f->addCss('');
        }

    }

}