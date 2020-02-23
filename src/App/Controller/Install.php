<?php
namespace App\Controller;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Install extends \Uni\Controller\Install
{


    /**
     * @return \Tk\Uri
     */
    public function getRedirectUrl()
    {
        return \Tk\Uri::create();
    }


}