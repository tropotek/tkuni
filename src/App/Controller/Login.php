<?php
namespace App\Controller;

use Tk\Request;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Auth;


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
     * @var \App\Db\Institution
     */
    protected $institution = null;
    

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct('Login');
    }

    /**
     * @return Form
     */
    private function init()
    {
        /** @var Auth $auth */
        if ($this->getUser()) {
            \Tk\Url::create($this->getUser()->getHomeUrl())->redirect();
        }
        if (!$this->form)
            $this->form = new Form('loginForm');
        $this->form->addField(new Field\Input('username'));
        $this->form->addField(new Field\Password('password'));

        $this->form->addField(new Event\Button('login', array($this, 'doLogin')));

    }

    /**
     *
     * @param Request $request
     * @return mixed
     */
    public function doDefault(Request $request)
    {
        $this->institution = \App\Db\InstitutionMap::create()->findByDomain($request->getUri()->getHost());
        if ($this->institution) {
            return $this->doInsLogin($request, $this->institution->getHash());
        }
        $this->init();
        $this->form->addField(new Event\Link('forgotPassword', \Tk\Uri::create('/recover.html')));

        // Find and Fire submit event
        $this->form->execute();

        return $this->show();
    }

    /**
     *
     * @param Request $request
     * @return mixed
     */
    public function doInsLogin(Request $request, $instHash)
    {

        if (!$this->institution)
            $this->institution = \App\Db\InstitutionMap::create()->findByHash($instHash);

        if (!$this->institution || !$this->institution->active ) {
            throw new \Tk\NotFoundHttpException('Institution not found.');
        }

        $this->init();
        $this->form->addField(new Field\Hidden('instHash', $instHash));
        $this->form->addField(new Event\Link('forgotPassword', \Tk\Uri::create('/recover.html')));

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
        $fren = new \Tk\Form\Renderer\Dom($this->form);
        $template->insertTemplate($this->form->getId(), $fren->show()->getTemplate());

        if ($this->institution) {
            if ($this->institution->getLogoUrl()) {
                $template->setChoice('instLogo');
                $template->setAttr('instLogo', 'src', $this->institution->getLogoUrl()->toString());
            }
            $template->insertText('instName', $this->institution->name);
            $template->setChoice('inst');
        }
        if ($this->getConfig()->get('site.client.registration')) {
            $template->setChoice('register');
        }

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

        if (!$form->getFieldValue('username') || !preg_match('/[a-z0-9_ -]{3,32}/i', $form->getFieldValue('username'))) {
            $form->addFieldError('username', 'Please enter a valid username');
        }
        if (!$form->getFieldValue('password') || !preg_match('/[a-z0-9_ -]{6,32}/i', $form->getFieldValue('password'))) {
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
            if (!$result->isValid()) {
                $form->addError( implode("<br/>\n", $result->getMessages()) );
            }

            $this->getConfig()->getEventDispatcher()->dispatch('auth.onLogin.success', $event);

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
        return \Dom\Loader::loadFile($this->getPage()->getTemplatePath().'/xtpl/public/login.xtpl');
    }

}