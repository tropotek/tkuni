<?php
namespace App\Controller;

use App\Alert;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Request;


/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * TODO: Only set this up to register institutional clients.
 *  Then they can purchase a service and manage their account.
 *
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
     * @var \Tk\EventDispatcher\EventDispatcher
     */
    private $dispatcher = null;
    

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Create New Account');
        $this->dispatcher = $this->getConfig()->getEventDispatcher();

        //throw new \Tk\Exception('Not Implemented Yet');
        
    }

    /**
     * @param Request $request
     * @return \App\Page\Iface
     */
    public function doDefault(Request $request)
    {
        if ($request->has('h')) {
            $this->doConfirmation($request);
        }
        if ($this->getUser()) {
            // Todo: Redirect to the users homepage
            \Tk\Uri::create($this->getUser()->getHomeUrl())->redirect();
        }

        $this->user = new \App\Db\User();
        $this->user->role = \App\Auth\Acl::ROLE_CLIENT;

        $this->institution = new \App\Db\Institution();
        
        $this->form = new Form('registerForm', $request);

        $this->form->addField(new Field\Input('name'))->setLabel('Institution Name');
        $this->form->addField(new Field\Input('email'));
        $this->form->addField(new Field\Input('username'));
        $this->form->addField(new Field\Password('password'));
        $this->form->addField(new Field\Password('passwordConf'));
        $this->form->addField(new Event\Button('register', array($this, 'doRegister')));

        $this->form->load(\App\Db\UserMap::create()->unmapForm($this->user));
        
        // Find and Fire submit event
        $this->form->execute();

        return $this->show();
    }


    /**
     * doLogin()
     *
     * @param \Tk\Form $form
     * @throws \Tk\Exception
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
        
        $form->addFieldErrors(\App\Db\UserValidator::create($this->user)->getErrors());
        $form->addFieldErrors(\App\Db\InstitutionValidator::create($this->institution)->getErrors());
        
        if ($form->hasErrors()) {
            return;
        }

        $pass = \App\Db\User::createPassword(10);

        // Create a user and make a temp hash until the user activates the account
        $this->user->getHash();
        $this->user->active = false;
        $this->user->password = \App\Factory::hashPassword($pass, $this->user);
        $this->user->save();

        $this->institution->ownerId = $this->user->id;
        $this->institution->active = false;
        $this->institution->save();

        // Fire the login event to allow developing of misc auth plugins
        $event = new \Tk\EventDispatcher\Event();
        $event->set('form', $form);
        $event->set('user', $this->user);
        $event->set('pass', $this->form->getFieldValue('password'));
        $event->set('institution', $this->institution);
        $event->set('templatePath', $this->getPage()->getTemplatePath());
        $this->dispatcher->dispatch('auth.onRegister', $event);

        // Redirect with message to check their email
        \App\Alert::addSuccess('Your New Account Has Been Created.');
        $this->getConfig()->getSession()->set('h', $this->user->getHash());

        \Tk\Uri::create()->redirect();
    }

    /**
     * Activate the user account if not activated already, then trash the request hash....
     * 
     * 
     * @param Request $request
     */
    public function doConfirmation($request)
    {
        // Receive a users on confirmation and activate the user account.
        $hash = $request->get('h');
        if (!$hash) {
            throw new \InvalidArgumentException('Cannot locate user. Please contact administrator.');
        }
        /** @var \App\Db\User $user */
        $user = \App\Db\User::getMapper()->findByHash($hash);
        if (!$user || $user->role != \App\Auth\Acl::ROLE_CLIENT) {
            throw new \InvalidArgumentException('Cannot locate user. Please contact administrator.');
        }
        if ($user->active == true) {
            \App\Alert::addSuccess('Account Already Active.');
            \Tk\Uri::create('/login.html')->redirect();
        }

        $institution = \App\Db\Institution::getMapper()->findByUserId($user->id);

        $user->getHash();
        $user->active = true;
        $user->save();

        $institution->active = true;
        $institution->save();
        
        $event = new \Tk\EventDispatcher\Event();
        $event->set('request', $request);
        $event->set('user', $user);
        $event->set('institution', $institution);
        $event->set('templatePath', $this->getTemplatePath());
        $this->dispatcher->dispatch('auth.onRegisterConfirm', $event);
        
        \App\Alert::addSuccess('Account Activation Successful.');
        \Tk\Uri::create('/login.html')->redirect();
    }


    public function show()
    {
        $template = $this->getTemplate();

        if ($this->getConfig()->getSession()->getOnce('h')) {
            $template->setChoice('success');
            
        } else {
            $template->setChoice('form');
            // Render the form
//            $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
//            $ren->show();
            // Render the form
            $fren = new \Tk\Form\Renderer\Dom($this->form);
            $template->insertTemplate($this->form->getId(), $fren->show()->getTemplate());
        }
        
        return $this->getPage()->setPageContent($template);
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $tplFile = $this->getPage()->getTemplatePath().'/xtpl/register.xtpl';
        return \Dom\Loader::loadFile($tplFile);
    }

}