<?php
namespace Tk\Auth;


/**
 * Class Access
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 * 
 * @link http://www.phpclasses.org/browse/file/36422.html
 * @link http://stackoverflow.com/questions/9217223/access-control-architecture-in-a-php-application
 *
 * 
 */
abstract class Access implements AccessInterface
{
    
    /**
     * @var mixed
     */
    protected $context = null;
    
    /**
     * @var mixed
     */
    protected $resource = null;
    
    
    /**
     * Access constructor.
     *
     * @param mixed $context
     * @param mixed $resource
     */
    public function __construct($context, $resource)
    {
        $this->context = $context;
        $this->resource = $resource;
    }
    
    /**
     * A method to check a resource for access
     *
     * @param string|mixed $level
     * @return boolean
     */
    public function hasAccess($level)
    {
        // loop through the handlers for the requested resource
        
    }
    
    
    
}