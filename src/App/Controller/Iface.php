<?php
namespace App\Controller;


abstract class Iface extends \Tk\Controller\Iface
{

    
    /**
     * Iface constructor.
     */
    public function __construct() { }

    /**
     * Get a new instance of the page to display the content in.
     *
     * @return \Dom\Template
     */
    public function getPage()
    {
        $role = $this->getConfig()->getRequest()->getAttribute('role');
        if (is_array($role)) $role = current($role);

        if (!$this->page) {
            switch($role) {
                case \App\Db\User::ROLE_ADMIN:
                case 'client':
                    $this->page = new \App\Page\AdminPage($this);
                    break;
                case \App\Db\User::ROLE_STAFF:
                    $this->page = new \App\Page\StaffPage($this);
                    break;
                case \App\Db\User::ROLE_STUDENT:
                    $this->page = new \App\Page\StudentPage($this);
                    break;
                default:
                    $this->page = new \App\Page\PublicPage($this);
                    break;
            }
        }
        return $this->page;
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
     * DomTemplate magic method example
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div></div>
HTML;
        return \Dom\Loader::load($html);
        // OR FOR A FILE
        //return \Dom\Loader::loadFile($this->getTemplatePath().'/public.xtpl');
    }

}