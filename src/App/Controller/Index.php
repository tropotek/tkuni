<?php
namespace App\Controller;

use Tk\Request;
/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Index extends Iface
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Home');
    }

    /**
     * @param Request $request
     * @return \App\Page\Iface
     */
    public function doDefault(Request $request)
    {
        // on success email user confirmation
        $user = \App\Db\User::getMapper()->find(2);
        $body = \Dom\Loader::loadFile($this->getConfig()->getSitePath().'/html/purpose/xtpl/mail/account.registration.xtpl');
        $body->insertText('name', $user->name);
        $url = \Tk\Uri::create('/register.html')->set('h', $user->hash);
        $body->insertText('url', $url->toString());
        $body->setAttr('url', 'href', $url->toString());
        $subject = 'Account Registration Request.';

        //$message = new \Tk\Mail\Message($body->toString(true, true), $subject, \App\Factory::getConfig()->get('site.email'), $user->email);
        $message = new \Tk\Mail\Message($body, $subject, \App\Factory::getConfig()->get('site.email'), $user->email);
        $message->send();

        //vd('------------------');
        return $this->show();
    }



    public function show()
    {
        $template = $this->getTemplate();

        return $this->getPage()->setPageContent($template);
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $tplFile = $this->getPage()->getTemplatePath().'/xtpl/index.xtpl';
        return \Dom\Loader::loadFile($tplFile);
    }

}