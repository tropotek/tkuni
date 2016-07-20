<?php
namespace App\Controller;

use Tk\Request;
use Dom\Template;

/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Logout extends Iface
{

    /**
     *
     * @param Request $request
     * @return Template
     */
    public function doDefault(Request $request)
    {
        
        $event = new \App\Event\AuthEvent($this->getConfig()->getAuth());
        $this->getConfig()->getEventDispatcher()->dispatch('auth.onLogout', $event);
        
        \Tk\Uri::create('/index.html')->redirect();
    }

    /**
     * Execute the renderer.
     *
     * @return mixed
     */
    public function show()
    {
        // TODO: Implement show() method.
    }
}