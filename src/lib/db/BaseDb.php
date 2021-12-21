<?php

namespace lib\db;

use lib\utils\Properties;

class BaseDb
{
    /**
     * @var DbStorage
     */
    protected $storage;
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string[]
     */
    protected $keys;
    protected $extraTypes;

    /**
     * @var mixed
     */
    protected $dataType;

    /**
     * @param DbStorage $storage
     * @param string $table
     * @param string[] $keys
     */
    public function __construct($storage, $table, $keys, $extraTypes, $dataType)
    {
        $storage->initialize($keys, $extraTypes, $dataType);
        $this->storage = $storage;
        $this->table = $table;
        $this->keys = $keys;
        $this->extraTypes = $extraTypes;
        $this->dataType = $dataType;
    }

    /**
     * @param string $query
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function query($query, $limit = -1, $skip = 0)
    {
        return $this->storage->query($query, $limit, $skip);
    }

    /**
     * @param string $query
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function count($query)
    {
        return $this->storage->count($query);
    }

    /**
     * @param string $query
     * @param integer $count
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function queryAndCount($query, $limit = -1, $skip = 0, &$count)
    {
        return $this->storage->queryAndCount($query, $limit, $skip, $count);
    }

    /**
     * @param mixed $item
     * @return void
     */
    public function update($item)
    {
        $byKey = array();
        foreach ($this->keys as $key) {
            $byKey[] = $item->$key;
        }
        $foundedUser = $this->getByKey($byKey);
        $this->storage->save($item, $foundedUser == null);
    }

    /**
     * @return string|null
     */
    public function buildByKeyQuery()
    {
        $args = func_get_args();
        if (sizeof($args) == 0) return null;
        $keyValues = array();
        if (is_array($args[0])) {
            $keyValues = $args[0];
        } else {
            $keyValues = $args;
        }
        $byKey = array();
        for ($i = 0; $i < sizoef($this->keys); $i++) {
            $key = $this->keys[$i];
            $value = $keyValues[$i];
            if (is_string($value)) {
                $byKey[] = "(" . $key . " eq '" . $value . "'" . ")";
            } else if (is_bool($value)) {
                $byKey[] = "(" . $key . " eq " . ($value ? "true" : "false") . ")";
            } else {
                $byKey[] = "(" . $key . " eq " . $value . ")";
            }
        }
        return join(" AND ", $byKey);
    }

    /**
     * @return mixed
     */
    public function getByKey()
    {
        $query = $this->buildByKeyQuery(func_get_args());
        $foundedUsers = $this->query($query, 1);
        if (sizeof($foundedUsers) == 0) {
            return null;
        }
        return $foundedUsers[0];
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        $query = $this->buildByKeyQuery(func_get_args());
        $foundedUsers = $this->query($query, 1);
        if ($foundedUsers == null) return;

        $this->storage->delete($foundedUsers, $query);
    }
}