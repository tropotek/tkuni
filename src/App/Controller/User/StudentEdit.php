<?php
namespace App\Controller\User;

use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StudentEdit extends Edit
{





    /**
     * Iface constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = \App\Uri::createHomeUrl('/studentManager.html');
    }

    public function setPageHeading()
    {
        $this->setPageTitle('Student Edit');
    }


}