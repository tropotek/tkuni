<?php
namespace App;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Uri extends \Tk\Uri
{

    /**
     * A static factory method to facilitate inline calls
     *
     * <code>
     *   \Tk\Uri::create('http://example.com/test');
     * </code>
     *
     * @param $spec
     * @return Uri
     */
    public static function createHomeUrl($spec = null)
    {
        if ($spec instanceof Uri)
            return $spec;
        $home = '';
        $user = \App\Factory::getConfig()->getUser();
        if ($user instanceof \App\Db\User)
            $home = dirname($user->getHomeUrl());
        return new static($home . '/' . trim($spec,'/'));
    }


}