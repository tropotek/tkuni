<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace App\Helper;

/**
 * Validator superclass for form validation
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
abstract class Validator
{
    /**
     * Validate an email
     * @match name@domain.com, name-name@domain.com
     * @no-match name@domain, name@domain.com
     */
    const REG_EMAIL = '/^[0-9a-zA-Z\-\._]*@[0-9a-zA-Z\-]([-.]?[0-9a-zA-Z])*$/';
    /**
     * Validate an email
     *
     * @match regexlib.com | this.is.a.museum | 3com.com
     * @no-match notadomain-.com | helloworld.c | .oops.org
     */
    const REG_DOMAIN = '/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/';
    /**
     * Check http/https urls with this
     * @match http://www.domain.com
     * @no-match www.domain.com
     */
    const REG_URL = '/^[a-z0-9]{2,8}:\/\/(www\.)?[\S]+$/i';
    /**
     * IP V4 check
     *
     * @match 255.255.255.255
     * @no-match domain.com, 233.233.233.0/24
     */
    const REG_IPV4 = '/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/';
    /**
     * Extract flash video urls with this expresion
     */
    const REG_FLASH_VIDEO = '/<embed[^>]*src=\"?([^\"]*)\"?([^>]*alt=\"?([^\"]*)\"?)?[^>]*>/i';
    /**
     * Validate a username
     *
     * @match Name, name@domain.com
     * @no-match *username
     */
    const REG_USERNAME = '/^[a-zA-Z0-9_@ \.\-]{3,64}$/i';
    /**
     * Validate a password
     *
     * @match Name, name@domain.com
     * @no-match *username
     */
    const REG_PASSWORD = '/^.{6,64}$/i';
    /**
     * @var bool
     */
    protected $done = false;
    /**
     * @var mixed
     */
    protected $obj = null;
    /**
     * @var array
     */
    protected $errors = array();

    /**
     *
     * @param mixed $obj
     */
    private function __construct($obj)
    {
        $this->obj = $obj;

    }

    /**
     * Create a new validation object
     *
     * @param $obj
     * @return static
     */
    static function create($obj)
    {
        return new static($obj);
    }

    /**
     * Return the object that was passed to the constructor
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->obj;
    }

    /**
     * setErrors
     *
     * @param array $errors
     */
    public function addErrorList($errors)
    {
        foreach ($errors as $name => $msg) {
            if (is_array($msg)) {
                foreach ($msg as $str) {
                    $this->addError($name, $str);
                }
            } else {
                $this->addError($name, $msg);
            }
        }
    }

    /**
     * Adds an error message to the array
     *
     * @param string $var
     * @param string $msg
     */
    protected function addError($var, $msg)
    {
        if (!array_key_exists($var, $this->errors)) {
            $this->errors[$var] = array();
        }
        $this->errors[$var][] = $msg;
    }

    /**
     * Return the error map.
     *
     * @return array
     */
    public function getErrors()
    {
        $this->isValid();
        return $this->errors;
    }

    /**
     * Returns true is string valid, false if not
     *
     * @return bool
     */
    final function isValid()
    {
        if (!$this->done) {
            $this->done = true;
            $this->validate();
        }

        if (count($this->errors)) {
            return false;
        }
        return true;
    }

    /**
     * Implement the validating rules to apply.
     *
     */
    abstract protected function validate();


}