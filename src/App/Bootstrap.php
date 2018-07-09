<?php
namespace App;


/**
 * @author Michael Mifsud <info@tropotek.com>  
 * @link http://www.tropotek.com/  
 * @license Copyright 2015 Michael Mifsud  
 */
class Bootstrap extends \Uni\Bootstrap
{

    /**
     * This will also load dependant objects into the config, so this is the DI object for now.
     *
     * @return \Uni\Config|\Bs\Config
     * @throws \Exception
     */
    public function init()
    {
        return parent::init();
    }

}
