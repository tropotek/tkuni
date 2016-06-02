<?php
namespace App\Controller;

use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Auth;
use Tk\Auth\Result;


/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Login extends Iface
{

    /**
     * @var Form
     */
    protected $form = null;
    

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct('Login');
    }
    
    /**
     *
     * @param Request $request
     * @return Template
     */
    public function doDefault(Request $request)
    {
        /** @var Auth $auth */
        if ($this->getUser()) {
            // Todo: Redirect to the users homepage
            \Tk\Uri::create('/admin/index.html')->redirect();
        }

        $this->form = new Form('loginForm');

        $this->form->addField(new Field\Input('username'));
        $this->form->addField(new Field\Password('password'));
        $this->form->addField(new Field\Checkbox('remember'));
        $this->form->addField(new Event\Button('login', array($this, 'doLogin')));
        
        // Find and Fire submit event
        $this->form->execute();

        return $this->show();
    }

    /**
     * show()
     *
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        // Render the form
        $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
        $ren->show();
        
        return $this->getPage()->setPageContent($template);
    }

    /**
     * doLogin()
     *
     * @param \Tk\Form $form
     * @throws \Tk\Exception
     */
    public function doLogin($form)
    {
        /** @var Auth $auth */
        $auth = \App\Factory::getAuth();

        if (!$form->getFieldValue('username') || !preg_match('/[a-z0-9_ -]{4,32}/i', $form->getFieldValue('username'))) {
            $form->addFieldError('username', 'Please enter a valid username');
        }
        if (!$form->getFieldValue('password') || !preg_match('/[a-z0-9_ -]{4,32}/i', $form->getFieldValue('password'))) {
            $form->addFieldError('password', 'Please enter a valid password');
        }
        
        if ($form->hasErrors()) {
            return;
        }

        try {
            // Fire the login event to allow developing of misc auth plugins
            $event = new \App\Event\AuthEvent($auth, $form->getValues());
            $this->getConfig()->getEventDispatcher()->dispatch('auth.onLogin', $event);
            
            $result = $event->getResult();
            if (!$result) {
                $form->addError('Invalid Username or password.');
                return;
            }
            $form->addError( implode("<br/>\n", $result->getMessages()) );
        } catch (\Exception $e) {
            $form->addError($e->getMessage());
        }
    }
    
    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        return \Dom\Loader::loadFile($this->getTemplatePath().'/xtpl/login.xtpl');
    }

}