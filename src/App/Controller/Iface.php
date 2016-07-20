<?php
namespace App\Controller;


abstract class Iface extends \Dom\Renderer\Renderer
{

    /**
     * @var string|array
     */
    protected $access = null;

    /**
     * @var string
     */
    protected $pageTitle = '';

    /**
     * @var string
     */
    protected $templatePath = '';
    
    /**
     * @var \App\Page\Iface
     */
    protected $page = null;


    /**
     * @param string $pageTitle
     * @param string|array $access
     */
    public function __construct($pageTitle = '', $access = null)
    {
        $this->setAccess($access);
        $this->setPageTitle($pageTitle);
        $this->templatePath = $this->getConfig()->getSitePath() . $this->getConfig()->get('template.public.path');
    }

    /**
     * Get a new instance of the page to display the content in.
     *
     * @return \App\Page\Iface
     */
    public function getPage()
    {
        if (!$this->page) {
            $this->page = new \App\Page\PublicPage($this);
        }
        return $this->page;
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
     * Add a role(s) that can access this page
     *
     * @param string|array $role
     * @return $this
     */
    public function setAccess($role)
    {
        $this->access = $role;
        return $this;
    }

    /**
     * Get the controllers roles
     *
     * @return string|array
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Can this user access this page
     *
     * @param \App\Db\User $user
     * @return bool
     */
    public function hasAccess($user)
    {
        if (empty($this->access)) return true;
        if (!$user) return false;
        if ($user->getAccess()->hasRole($this->access)) return true;
        return false;
    }

}