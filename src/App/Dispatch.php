<?php
namespace App;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Dispatch extends \Uni\Dispatch
{


    /**
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function init()
    {
        parent::init();
        $dispatcher = $this->getDispatcher();


    }

}