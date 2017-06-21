<?php
namespace App\Event;



/**
 * Class
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class DbEvent extends \Tk\Event\Event
{

    /**
     * @var \Tk\Db\Map\Model
     */
    protected $model = null;

    /**
     * @var \App\Db\Mapper
     */
    protected $mapper = null;

    /**
     * @var bool
     */
    private $queryStopped = false;


    /**
     * DbEvent constructor.
     *
     * @param \Tk\Db\ModelInterface $model
     * @param $mapper
     */
    public function __construct($model, $mapper)
    {
        parent::__construct();
        $this->model = $model;
        $this->mapper = $mapper;
    }

    /**
     * @return \Tk\Db\Map\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return get_class($this->model);
    }

    /**
     * @return \App\Db\Mapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return string
     */
    public function getMapperClass()
    {
        return get_class($this->mapper);
    }

    /**
     * Return true if the query executed after this event should be stopped
     *
     * @return bool Whether propagation was already stopped for this event.
     */
    public function isQueryStopped()
    {
        return $this->queryStopped;
    }

    /**
     * Stops the main query from being executed
     *
     * @return $this
     */
    public function stopQuery()
    {
        $this->queryStopped = true;
        return $this;
    }

}