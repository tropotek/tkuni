<?php
namespace App\Ui\Menu;


/**s
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
abstract class Iface extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{

    /**
     * @return static
     */
    static function create()
    {
        return new static();
    }

    /**
     * @return \App\Config
     */
    public function getConfig()
    {
        return \App\Config::getInstance();
    }

    /**
     * @return \App\Db\User|\Uni\Db\UserIface
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

}