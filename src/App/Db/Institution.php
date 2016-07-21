<?php
namespace App\Db;


/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Institution extends \Tk\Db\Map\Model
{

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $ownerId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $logo = '';

    /**
     * @var boolean
     */
    public $active = true;

    /**
     * @var string
     */
    public $hash = '';

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * @var User
     */
    private $owner = null;

    /**
     * @var Data
     */
    private $data = null;
    
    

    /**
     *
     */
    public function __construct()
    {
        $this->modified = \Tk\Date::create();
        $this->created = \Tk\Date::create();
    }

    /**
     * Get the institution data object
     *
     * @return Data
     */
    public function getData()
    {
        if (!$this->data)
            $this->data = Data::create($this->id, get_class($this));
        return $this->data;
    }

    /**
     * Returns null if no logo available
     *
     * @return \Tk\Uri|null
     */
    public function getLogoUrl()
    {
        if ($this->logo)
            return \Tk\Uri::create(\App\Factory::getConfig()->getDataUrl().$this->logo);
    }

    /**
     * Find this institutions owner user
     *
     * @return User
     */
    public function getOwner()
    {
        if (!$this->owner)
            $this->owner = \App\Db\User::getMapper()->find($this->ownerId);
        return $this->owner;
    }



}

class InstitutionValidator extends \Tk\Db\Map\Validator
{

    /**
     * Implement the validating rules to apply.
     *
     */
    protected function validate()
    {
        /** @var Course $obj */
        $obj = $this->getObject();

        if (!$obj->name) {
            $this->addError('name', 'Invalid field value.');
        }        
        if (!filter_var($obj->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Please enter a valid email address');
        }
        
        // TODO: Validate start and end dates

    }
}
