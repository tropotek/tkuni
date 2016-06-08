<?php
namespace Tk\Auth;

/**
 * Interface RoleInterface
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
interface AccessInterface
{
    
    /**
     * A method to check a resource for access
     * 
     * @param string|mixed $level
     * @return boolean
     */
    public function hasAccess($level);
    
}