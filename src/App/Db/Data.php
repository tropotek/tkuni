<?php


namespace App\Db;

/**
 * Class Data
 * 
 * A database object to manage the data table values.
 * 
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Data extends \Tk\Collection
{
    
    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $table = '';
    
    /**
     * @var int
     */
    protected $foreignId = 0;
    
    /**
     * @var string
     */
    protected $foreignKey = '';
    
    
    
    /**
     * Data constructor.
     *
     * @param int $foreignId
     * @param string $foreignKey
     * @param string $table (optional) Default: `data`
     * @param \Tk\Db\Pdo|null $db
     */
    public function __construct($foreignId = 0, $foreignKey = 'system', $table = 'data', $db = null)
    {
        parent::__construct();
        $this->db = $db;
        $this->table = $table;
        $this->foreignId = $foreignId;
        $this->foreignKey = $foreignKey;
    }


    /**
     * Creates an instance of the Data object and loads that data from the DB
     * 
     * If the DB is null then the \App\Factory::getDb() is used.
     * 
     * @param int $foreignId
     * @param string $foreignKey
     * @param string $table
     * @param null $db
     * @return static
     */
    public static function create($foreignId = 0, $foreignKey = 'system', $table = 'data', $db = null)
    {
        if (!$db) {
            $db = \App\Factory::getDb();      // @dependency
        }
        $obj = new static($foreignId, $foreignKey, $table, $db);
        $obj->load();
        return $obj;
    }

    /**
     * Get the table name for queries
     * 
     * @return string
     */
    protected function getTable()
    {
        return $this->db->quoteParameter($this->table);
    }

    /**
     * Load this object with available data from the DB
     * 
     * @return $this
     */
    public function load()
    {
        $sql = sprintf('SELECT * FROM %s WHERE foreign_id = %d AND foreign_key = %s ', $this->getTable(), 
            (int)$this->foreignId, $this->db->quote($this->foreignKey));
        $stmt = $this->db->query($sql);
        $stmt->setFetchMode(\PDO::FETCH_OBJ);
        foreach ($stmt as $row) {
            $this->set($row->key, $row->value);
        }
        return $this;
    }

    /**
     * Save object data to the DB
     * 
     * @return $this
     */
    public function save()
    {
        foreach($this as $k => $v) {
            $this->dbSet($k, $v);
        }
        return $this;
    }

    /**
     * Set a single data value in the Database 
     * 
     * @param $key
     * @param $value
     * @return Data
     */
    protected function dbSet($key, $value)
    {
        if (is_array($value) || is_object($value)) {
            return $this;
        }
        if ($this->dbHas($key)) {
            $sql = sprintf('UPDATE %s SET value = %s WHERE %s = %s AND foreign_id = %d AND foreign_key = %s ',
                $this->getTable(), $this->db->quoteParameter('key'), $this->db->quote($value), $this->db->quote($key),
                (int)$this->foreignId, $this->db->quote($this->foreignKey) );
        } else {
            $sql = sprintf('INSERT INTO %s (foreign_id, foreign_key, %s, value) VALUES (%d, %s, %s, %s) ',
                $this->getTable(), $this->db->quoteParameter('key'), (int)$this->foreignId, $this->db->quote($this->foreignKey),
                $this->db->quote($key), $this->db->quote($value));
        }
        $this->db->exec($sql);
        return $this;
    }

    /**
     * Get a value from the database
     * 
     * @param $key
     * @return string
     */
    protected function dbGet($key)
    {
        $sql = sprintf('SELECT * FROM %s WHERE %s = %s AND foreign_id = %d AND foreign_key = %s ', $this->getTable(),   $this->db->quoteParameter('key'),
            $this->db->quote($key), (int)$this->foreignId, $this->db->quote($this->foreignKey));
        $row = $this->db->query($sql)->fetchObject();
        if ($row) {
            return $row->value;
        }
        return '';
    }

    /**
     * Check if a value exists in the DB
     * 
     * @param $key
     * @return bool
     */
    protected function dbHas($key)
    {
        $sql = sprintf('SELECT * FROM %s WHERE %s = %s AND foreign_id = %d AND foreign_key = %s ', $this->getTable(), $this->db->quoteParameter('key'),
            $this->db->quote($key), (int)$this->foreignId, $this->db->quote($this->foreignKey));
        $res = $this->db->query($sql);
        if ($res && $res->rowCount()) return true;
        return false;
    }

    /**
     * Remove a value from the DB
     * 
     * @param $key
     * @return $this
     */
    protected function dbDelete($key)
    {
        $sql = sprintf('DELETE FROM %s WHERE %s = %s AND foreign_id = %d AND foreign_key = %s ', $this->getTable(),  $this->db->quoteParameter('key'),
            $this->db->quote($key), (int)$this->foreignId, $this->db->quote($this->foreignKey));
        $this->db->exec($sql);
        return $this;
    }
    
    
}