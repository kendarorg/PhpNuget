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
    public function __construct($storage, $table, $keys,$extraTypes,$dataType)
    {
        $storage->initialize($keys,$extraTypes,$dataType);
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
    public function query($query,$limit = -1, $skip = 0){
        return $this->storage->query($query,$limit,$skip);
    }

    /**
     * @param string $query
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function count($query){
        return $this->storage->count($query);
    }

    /**
     * @param string $query
     * @param integer $count
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function queryAndCount($query,$limit = -1, $skip = 0,&$count){
        return $this->storage->queryAndCount($query,$limit,$skip,$count);
    }

    /**
     * @param mixed $item
     * @return void
     */
    public function update($item){

    }

    /**
     * @return mixed
     */
    public function getByKey(){
        $keys = func_get_args();
    }
}