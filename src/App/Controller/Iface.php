<?php
namespace App\Controller;


abstract class Iface extends \Tk\Controller\Iface
{


    /**
     * Get a new instance of the page to display the content in.
     *
     * @return \Tk\Controller\Page
     */
    public function getPage()
    {
        $role = $this->getConfig()->getRequest()->getAttribute('role');
        if (is_array($role)) $role = current($role);

        if (!$this->page) {
            switch($role) {
                case \App\Db\User::ROLE_ADMIN:
                case 'client':
                    $this->page = new \App\Page\AdminPage();
                    break;
                case \App\Db\User::ROLE_STAFF:
                    $this->page = new \App\Page\StaffPage();
                    break;
                case \App\Db\User::ROLE_STUDENT:
                    $this->page = new \App\Page\StudentPage();
                    break;
                default:
                    $this->page = new \App\Page\PublicPage();
                    break;
            }
            $this->page->setController($this);
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