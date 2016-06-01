<?php
namespace App\Controller;


abstract class Iface extends \Dom\Renderer\Renderer
{
    
    /**
     * @var array
     */
    protected $access = array();

    /**
     * @var string
     */
    protected $pageTitle = '';

    /**
     * @var string
     */
    protected $templatePath = '';


    /**
     * @param string $pageTitle
     * @param string $access
     */
    public function __construct($pageTitle = '', $access = '')
    {
        $this->setAccess($access);
        $this->setPageTitle($pageTitle);
        $this->templatePath = $this->getConfig()->getSitePath() . $this->getConfig()->get('template.public.path');
    }

    /**
     * 
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     *
     * @param string $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    /**
     * Get the global config object.
     *
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return \Tk\Config::getInstance();
    }

    
    

    /**
     * Get the currently logged in user
     *
     * @return \App\Db\User
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }
    
    /**
     * Add a role that can access this page
     *
     * @param $role
     * @return $this
     */
    public function setAccess($role)
    {
        $this->access = $role;
        return $this;
    }

    /**
     * Can this user access this page
     *
     * @param \App\Db\User $user
     * @return bool
     */
    public function hasAccess($user)
    {
        if (!$this->access) return true;
        if (!$user) return false;
        if ($user->hasRole($this->access)) return true;
        return false;
    }

    /**
     * Call this to check the current logged in user has access to this page.
     *
     */
    public function checkAccess()
    {
        if (!$this->access) return;
        /** @var \App\Db\User $user **/
        $user = $this->getUser();
        if (!$user) {
            \Tk\Uri::create('/login.html')->redirect();
        } else if (!$this->hasAccess($user)) {
            // Could redirect to a authentication error page...
            // Could cause a loop if the permissions are stuffed
            \App\Alert::getInstance()->addWarning('You do not have access to the requested page.');
            \Tk\Uri::create('/'.$this->access.'/index.html')->redirect();
        }
    }


}