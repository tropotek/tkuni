<?php
namespace App\Controller;

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
 * TODO: Only allow admin and clients to recover passwords
 *
 *
 */
class Recover extends Iface
{

    /**
     * @var \Tk\Form
     */
    protected $form = null;

    /**
     * @var \Tk\Event\Dispatcher
     */
    private $dispatcher = null;


    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Recover Password');

        $this->form = new Form('loginForm', $request);

        $this->form->addField(new Field\Input('account'));
        $this->form->addField(new Event\Submit('recover', array($this, 'doRecover')));

        // Find and Fire submit event
        $this->form->execute();

    }

    /**
     * @param Form $form
     */
    public function doRecover($form)
    {
        
        if (!$form->getFieldValue('account')) {
            $form->addFieldError('account', 'Please enter a valid username or email');
        }
        
        if ($form->hasErrors()) {
            return;
        }
        
        // TODO: This should be made a bit more secure for larger sites.

        // For Admin and Client users only
        // TODO: Do an institution recovery system if possible.

        $account = $form->getFieldValue('account');

        /** @var \App\Db\User $user */
        $user = null;
        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            $user = \App\Db\UserMap::create()->findByEmail($account);
        } else {
            $user = \App\Db\UserMap::create()->findByUsername($account);
        }
        if (!$user) {
            $form->addFieldError('account', 'Please enter a valid username or email');
            return;
        }

        $newPass = $user->createPassword();
        $user->password = \App\Config::getInstance()->hashPassword($newPass, $user);
        $user->save();
        
        // Fire the login event to allow developing of misc auth plugins
        $event = new \Tk\Event\Event();
        $event->set('form', $form);
        $event->set('UserIface', $user);
        $event->set('password', $newPass);
        $event->set('templatePath', $this->getPage()->getTemplatePath());
        
        \App\Config::getInstance()->getEventDispatcher()->dispatch(AuthEvents::RECOVER, $event);
        
        \Tk\Alert::addSuccess('You new access details have been sent to your email address.');
        \Tk\Uri::create()->redirect();
        
    }


    public function show()
    {
        $template = parent::show();
        if ($this->getConfig()->get('site.client.registration')) {
            $template->setChoice('register');
        }
        return $template;
    }

}