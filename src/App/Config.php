<?php
namespace App;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Config extends \Uni\Config
{

    /**
     * getFrontController
     *
     * @return \App\FrontController
     * @throws \Tk\Exception
     */
    public function getFrontController()
    {
        if (!$this->get('front.controller')) {
            $obj = new \App\FrontController($this->getEventDispatcher(), $this->getResolver(), $this);
            $this->set('front.controller', $obj);
        }
        return parent::get('front.controller');
    }

}