<?php
namespace App\Controller\User;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StaffEdit extends Edit
{





    /**
     * Iface constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = \Uni\Uri::createHomeUrl('/staffManager.html');
    }

    public function setPageHeading()
    {
        $this->setPageTitle('Staff Edit');
    }


}