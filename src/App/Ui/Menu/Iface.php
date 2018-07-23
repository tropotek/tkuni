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
     *
     * @return \Dom\Template
     */
    public function show()
    {
        $template = $this->getTemplate();

        $template->setText('site-title', $this->getConfig()->get('site.title'));
        $template->insertText('username', $this->getUser()->getName());

        if ($this->getConfig()->isDebug()) {
            $template->setChoice('debug');
        }

        return $template;
    }

    /**
     * @return \App\Config
     */
    public function getConfig()
    {
        return \App\Config::getInstance();
    }

    /**
     * @return \Uni\Db\User|\Uni\Db\UserIface
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

}