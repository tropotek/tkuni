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

    // Data fields
    const LTI_ENABLE = 'ltiEnable';
    const LTI_KEY = 'ltiKey';
    const LTI_SECRET = 'ltiSecret';
    const LTI_URL = 'ltiUrl';
    const LTI_CURRENT_KEY = 'ltiCurrentKey';
    const LTI_CURRENT_ID = 'ltiCurrentId';

    const LDAP_ENABLE = 'ldapEnable';
    const LDAP_HOST = 'ldapHost';
    const LDAP_TLS = 'ldapTls';
    const LDAP_PORT = 'ldapPort';
    const LDAP_BASE_DN = 'ldapBaseDn';
    const LDAP_FILTER = 'ldapFilter';

    const API_ENABLE = 'apiEnable';
    const API_KEY = 'apiKey';


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
    public $domain = '';

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
     * @var \IMSGlobal\LTI\ToolProvider\ToolConsumer
     */
    private $ltiConsumer = null;
    
    

    /**
     *
     */
    public function __construct()
    {
        $this->modified = \Tk\Date::create();
        $this->created = \Tk\Date::create();
    }

    /**
     *
     */
    public function save()
    {
        $this->getHash();

        // TODO LTI Consumer ??????????
        // Maybe we need a dispatcher hook here for extendability
        // Think this out but the LTI should be plugable.

        // unimelb_00002
        // 1f72a0bac401a3e375e737185817463c

        $lurl = \Tk\Uri::create('/lti/'.$this->getHash().'/launch.html')->toString();
        if ($this->domain)
            $lurl = \Tk\Uri::create('http://'.$this->domain.'/lti/launch.html')->toString();
        $this->getData()->set(self::LTI_URL, $lurl);
        $this->getData()->save();

        // Create the lti consumer
        // TODO: could this be simplefied?????? Using getLtiConsumer() ????
        if ($this->getData()->get(self::LTI_ENABLE) ) {
            if (!$this->getData()->has(self::LTI_CURRENT_KEY)) {
                $this->ltiConsumer = new \IMSGlobal\LTI\ToolProvider\ToolConsumer(null, \App\Factory::getLtiDataConnector());
                $this->ltiConsumer->setKey($this->getData()->get(self::LTI_KEY));
                if ($this->getData()->get(self::LTI_SECRET))
                    $this->ltiConsumer->secret = $this->getData()->get(self::LTI_SECRET);
                $this->ltiConsumer->enabled = true;
                $this->ltiConsumer->name = $this->name;
                $this->ltiConsumer->save();
                $this->getData()->set(self::LTI_CURRENT_KEY, $this->ltiConsumer->getKey());
                $this->getData()->set(self::LTI_CURRENT_ID, $this->ltiConsumer->getRecordId());
                $this->getData()->set(self::LTI_SECRET, $this->ltiConsumer->secret);
                $this->getData()->save();
            } else if ($this->getLtiConsumer()) {
                $this->getLtiConsumer()->name = $this->name;
                $this->getLtiConsumer()->enabled = true;
                if ($this->getData()->get(self::LTI_SECRET))
                    $this->getLtiConsumer()->secret = $this->getData()->get(self::LTI_SECRET);
                $this->getLtiConsumer()->save();
            }
        } else {
            if ($this->getData()->has(self::LTI_CURRENT_KEY)) {
                $this->ltiConsumer = $this->getLtiConsumer();
                $this->ltiConsumer->enabled = false;
                $this->ltiConsumer->save();

                // Should we have a delete option?
//                $this->getData()->remove(self::LTI_KEY);
//                $this->getData()->remove(self::LTI_SECRET);
//                $this->getData()->remove(self::LTI_CURRENT_KEY);
//                $this->getData()->remove(self::LTI_CURRENT_ID);
//                $this->getData()->save();
//                if ($this->ltiConsumer)
//                    $this->ltiConsumer->delete();
            }
        }
        parent::save();
    }

    /**
     *
     * @return \IMSGlobal\LTI\ToolProvider\ToolConsumer
     */
    public function getLtiConsumer()
    {
        if (!$this->ltiConsumer && $this->getData()->get(self::LTI_CURRENT_KEY)) {
            $this->ltiConsumer = new \IMSGlobal\LTI\ToolProvider\ToolConsumer($this->getData()->get(self::LTI_CURRENT_KEY), \App\Factory::getLtiDataConnector());
        }
        return $this->ltiConsumer;
    }

    /**
     * Get the user hash or generate one if needed
     *
     * @return string
     */
    public function getHash()
    {
        if (!$this->hash) {
            $this->hash = $this->generateHash();
        }
        return $this->hash;
    }

    /**
     * Helper method to generate user hash
     *
     * @return string
     * @throws \Tk\Exception
     */
    public function generateHash()
    {
        return hash('md5', sprintf('%s', $this->getVolatileId()));
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
            $this->owner = \App\Db\UserMap::create()->find($this->ownerId);
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
