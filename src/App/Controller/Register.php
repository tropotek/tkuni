<?php
namespace App\Controller;

use Dom\Exception;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Request;
use Tk\Auth\AuthEvents;
use Uni\Controller\Iface;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Register extends Iface
{
    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \App\Db\User
     */
    private $user = null;

    /**
     * @var \App\Db\Institution
     */
    private $institution = null;


    /**
     * Login constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Register New Account');
    }

    /**
     * @param Request $request
     * @throws \Tk\Exception
     * @throws \Exception
     * @throws \ReflectionException
     * @throws Form\Exception
     */
    public function doDefault(Request $request)
    {
        if (!$this->getConfig()->get('site.client.registration')) {
            \Tk\Alert::addError('User registration has been disabled on this site.');
            \Tk\Uri::create('/')->redirect();
        }
        if ($request->has('h')) {
            $this->doConfirmation($request);
        }

        $this->user = new \App\Db\User();
        $this->user->role = \App\Db\User::ROLE_USER;


        $this->form = \App\Config::createForm('register-account');
        $this->form->setRenderer(\App\Config::createFormRenderer($this->form));

        $this->form->addField(new Field\Input('name'));
        $this->form->addField(new Field\Input('email'));
        $this->form->addField(new Field\Input('username'));
        $this->form->addField(new Field\Password('password'));
        $this->form->addField(new Field\Password('passwordConf'))->setLabel('Password Confirm');
        $this->form->addField(new Event\Submit('register', array($this, 'doRegister')))->addCss('btn btn-lg btn-primary btn-ss');
        $this->form->addField(new Event\Link('forgotPassword', \Tk\Uri::create('/recover.html'), ''))->removeCss('btn btn-sm btn-default btn-once');

        $this->form->load(\App\Db\UserMap::create()->unmapForm($this->user));
        $this->form->execute();
    }


    /**
     * doLogin()
     *
     * @param \Tk\Form $form
     * @throws \Tk\Exception
     * @throws \ReflectionException
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function doRegister($form)
    {
        \App\Db\UserMap::create()->mapForm($form->getValues(), $this->user);
        \App\Db\InstitutionMap::create()->mapForm($form->getValues(), $this->institution);

        if (!$this->form->getFieldValue('password')) {
            $form->addFieldError('password', 'Please enter a password');
            $form->addFieldError('passwordConf');
        }
        // Check the password strength, etc....
        if (!preg_match('/.{6,32}/', $this->form->getFieldValue('password'))) {
            $form->addFieldError('password', 'Please enter a valid password');
            $form->addFieldError('passwordConf');
        }
        // Password validation needs to be here
        if ($this->form->getFieldValue('password') != $this->form->getFieldValue('passwordConf')) {
            $form->addFieldError('password', 'Passwords do not match.');
            $form->addFieldError('passwordConf');
        }
        
        $form->addFieldErrors($this->user->validate());
        $form->addFieldErrors($this->institution->validate());
        
        if ($form->hasErrors()) {
            return;
        }

        // TODO: in the email make sure the username (alternativly all account details) is clearly visible....

        $pass = '';
        if ($form->getFieldValue('password')) {
            $pass = $form->getFieldValue('password');
        }

        // Create a user and make a temp hash until the user activates the account
        $this->user->role = \App\Db\User::ROLE_CLIENT;
        $this->user->active = false;
        $this->user->setNewPassword($pass);
        $this->user->save();

        $this->institution->userId = $this->user->getId();
        $this->institution->active = false;
        $this->institution->save();

        // Fire the login event to allow developing of misc auth plugins
        $event = new \Tk\Event\Event();
        $event->set('form', $form);
        $event->set('user', $this->user);
        $event->set('pass', $this->form->getFieldValue('password'));
        $event->set('institution', $this->institution);
        \App\Config::getInstance()->getEventDispatcher()->dispatch(AuthEvents::REGISTER, $event);

        // Redirect with message to check their email
        \Tk\Alert::addSuccess('Your New Account Has Been Created.');
        $this->getConfig()->getSession()->set('h', $this->user->getHash());

        \Tk\Uri::create()->redirect();
    }

    /**
     * Activate the user account if not activated already, then trash the request hash....
     *
     *
     * @param Request $request
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function doConfirmation($request)
    {
        // Receive a users on confirmation and activate the user account.
        $hash = $request->get('h');
        if (!$hash) {
            throw new \InvalidArgumentException('Cannot locate user. Please contact administrator.');
        }
        /** @var \App\Db\User $user */
        $user = \App\Db\UserMap::create()->findByHash($hash);
        if (!$user || $user->role != \App\Db\User::ROLE_CLIENT) {
            throw new \InvalidArgumentException('Cannot locate user. Please contact administrator.');
        }
        if ($user->active == true) {
            \Tk\Alert::addSuccess('Account Already Active.');
            \Tk\Uri::create('/login.html')->redirect();
        }

        $institution = \App\Db\InstitutionMap::create()->findByUserId($user->id);

        $user->active = true;
        $user->save();

        $institution->active = true;
        $institution->save();
        
        $event = new \Tk\Event\Event();
        $event->set('request', $request);
        $event->set('user', $user);
        $event->set('institution', $institution);
        \App\Config::getInstance()->getEventDispatcher()->dispatch(AuthEvents::REGISTER_CONFIRM, $event);
        
        \Tk\Alert::addSuccess('Account Activation Successful.');
        \Tk\Uri::create('/login.html')->redirect();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        if ($this->getConfig()->get('site.client.registration')) {
            $template->setChoice('register');
        }

        if ($this->getConfig()->getSession()->getOnce('h')) {
            $template->setChoice('success');

        } else {
            $template->setChoice('form');
            // Render the form
            $template->insertTemplate('form', $this->form->getRenderer()->show());
        }

        return $template;
    }

}