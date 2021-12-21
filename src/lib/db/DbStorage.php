<?php

namespace lib\db;

use lib\utils\Properties;

class DbStorage
{
    /**
     * @var Properties
     */
    protected $properties;
    /**
     * @var QueryParser
     */
    protected $queryParser;

    /**
     * @var array
     */
    protected $items = array();
    protected $keys;
    protected $extraTypes;
    protected $dataType;
    /**
     * @var mixed|null
     */
    protected mixed $table;

    /**
     * @param Properties $properties
     * @param QueryParser $queryParser
     */
    public function __construct($properties, $queryParser, $items = null)
    {
        $this->properties = $properties;
        $this->queryParser = $queryParser;
        $this->items = $items;
    }

    public function initialize($keys, $extraTypes, $dataType,$table = null)
    {
        $this->keys = $keys;
        $this->extraTypes = $extraTypes;
        $this->dataType = $dataType;
        $this->table = $table;
    }

    /**
     * @param string $query
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function query($query, $limit = -1, $skip = 0)
    {
        throw new \Exception();
    }

    /**
     * @param string $query
     * @return integer
     */
    public function count($query)
    {
        throw new \Exception();
    }

    /**
     * @param string $query
     * @param int $limit
     * @param int $skip
     * @param int $count
     * @return array|mixed
     */
    public function queryAndCount($query, $limit, $skip, &$count)
    {
        throw new \Exception();
    }

    /**
     * @param mixed $item
     * @param bool $param
     * @return void
     */
    public function save($item, $add)
    {
        throw new \Exception();
    }

    /**
     * @param array $foundedUsers
     * @param string|null $query
     * @return void
     */
    public function delete($foundedUsers, $query)
    {
        throw new \Exception();
    }
}