<?php
namespace App\Controller;


abstract class Iface extends \Dom\Renderer\Renderer
{

    /**
     * @var string
     */
    protected $pageTitle = '';
    
    /**
     * @var \App\Page\Iface
     */
    protected $page = null;


    /**
     * @param string $pageTitle
     * @param string|array $access
     */
    public function __construct($pageTitle = '')
    {
        $this->setPageTitle($pageTitle);
        $this->getPage();
    }

    /**
     * Get a new instance of the page to display the content in.
     *
     * @return \App\Page\Iface
     */
    public function getPage()
    {
        if (!$this->page) {
            if (!$this->getUser()) {
                $this->page = new \App\Page\PublicPage($this);
                return $this->page;
            }
            $acl = $this->getUser()->getAcl();
            if ($acl->hasRole(\App\Auth\Acl::ROLE_ADMIN)) {
                $this->page = new \App\Page\AdminPage($this);
            } else if ($acl->hasRole(\App\Auth\Acl::ROLE_CLIENT)) {
                $this->page = new \App\Page\ClientPage($this);
            } else if ($acl->hasRole(\App\Auth\Acl::ROLE_STAFF)) {
                $this->page = new \App\Page\StaffPage($this);
            } else if ($acl->hasRole(\App\Auth\Acl::ROLE_STUDENT)) {
                $this->page = new \App\Page\StudentPage($this);
            }
        }
        return $this->page;
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

}