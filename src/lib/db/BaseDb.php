<?php

namespace lib\db;

use lib\utils\Properties;

class BaseDb
{
    /**
     * @var DbStorage
     */
    private $storage;
    /**
     * @var string
     */
    private $table;

    /**
     * @var string[]
     */
    private $keys;
    private $extraTypes;

    /**
     * @var mixed
     */
    private $dataType;

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
        $this->storage->query($query,$this->keys,$limit);
        return [];
    }

    /**
     * @param string $query
     * @param integer $count
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function queryAndCount($query,&$count,$limit = -1, $skip = 0){
        return [];
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